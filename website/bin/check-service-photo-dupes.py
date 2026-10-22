#!/usr/bin/env python3
"""QM #158: every service shows a genuinely different real photo, judged the way a person would.
Images: assets/images/services/**/*.jpg, families/*-photo.jpg and keyword hero images (service from keywords.json).
1. Byte-identical files used for different services fail.
2. Crop/re-encode/resize/flip tolerant: ORB features + RANSAC homography. Two images of DIFFERENT services fail when
   >= MIN_INLIERS geometrically consistent feature matches exist (also tried against the mirrored image).
Variants of one service (-photo, -context, -callpoint, -N, b1/<svc>-N) count as the same service; a family photo
counts as the same service for members of that family."""
import collections, hashlib, itertools, json, os, re, sys
import cv2, numpy as np
MIN_INLIERS = 80  # calibrated: real recrops of one photo give 190-400 inliers, different photos <= 40
root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
img = os.path.join(root, 'assets/images')
base = lambda s: 'aov-air-handling' if s.startswith('aov-') else re.sub(r'(-(photo|context|callpoint|[0-9]+))+$', '', s)
items = {}
for r, _, fs in os.walk(os.path.join(img, 'services')):
    for f in fs:
        if f.endswith('.jpg'): items[os.path.relpath(os.path.join(r, f), img)] = base(f[:-4])
for f in os.listdir(os.path.join(img, 'families')):
    if f.endswith('-photo.jpg'): items['families/' + f] = 'family:' + f[:-10]
kw = json.load(open(os.path.join(root, 'data/keywords.json')))
for f in os.listdir(os.path.join(img, 'keywords')):
    s = (kw.get(f[:-4]) or {}).get('service') if f.endswith('.jpg') else None
    if s: items['keywords/' + f] = base(s)
fam = json.load(open(os.path.join(root, 'data/service-image-families.json')))['families']
famof = collections.defaultdict(set)
for k, v in fam.items():
    for s in v: famof[base(s)].add(k)
def same(a, b):
    if a == b: return True
    for x, y in ((a, b), (b, a)):
        if x.startswith('family:'):
            f = x[7:]
            if f == y or f in famof.get(y, ()) or base(f) == y: return True
    return a.startswith('family:') and b.startswith('family:') and a == b
# 1. byte-identical
by = collections.defaultdict(set)
for rel, s in items.items(): by[hashlib.md5(open(os.path.join(img, rel), 'rb').read()).hexdigest()].add((rel, s))
fails = []
uniq = {}
for h, group in by.items():
    g = sorted(group)
    for (ra, sa), (rb, sb) in itertools.combinations(g, 2):
        if not same(sa, sb): fails.append(('identical', ra, sa, rb, sb))
    uniq[g[0][0]] = {s for _, s in g}
# 2. ORB + homography on unique images
orb = cv2.ORB_create(1500)
bf = cv2.BFMatcher(cv2.NORM_HAMMING)
def feats(rel):
    im = cv2.imread(os.path.join(img, rel), cv2.IMREAD_GRAYSCALE)
    if im is None: return None
    s = 640 / max(im.shape); im = cv2.resize(im, None, fx=s, fy=s) if s < 1 else im
    k, d = orb.detectAndCompute(im, None); km, dm = orb.detectAndCompute(cv2.flip(im, 1), None)
    return (k, d), (km, dm)
F = {r: feats(r) for r in uniq}
def inliers(A, B):
    (ka, da), (kb, db) = A, B
    if da is None or db is None or len(ka) < 10 or len(kb) < 10: return 0
    good = [m for m, n in (p for p in bf.knnMatch(da, db, k=2) if len(p) == 2) if m.distance < 0.75 * n.distance]
    if len(good) < MIN_INLIERS: return len(good)
    src = np.float32([ka[m.queryIdx].pt for m in good]); dst = np.float32([kb[m.trainIdx].pt for m in good])
    _, mask = cv2.findHomography(src, dst, cv2.RANSAC, 5.0)
    return int(mask.sum()) if mask is not None else 0
near = []
for a, b in itertools.combinations(sorted(F), 2):
    if F[a] is None or F[b] is None: continue
    if all(same(x, y) for x in uniq[a] for y in uniq[b]): continue
    n = max(inliers(F[a][0], F[b][0]), inliers(F[a][1], F[b][0]))
    if n >= 30: near.append((n, a, b))
    if n >= MIN_INLIERS: fails.append((f'inliers={n}', a, sorted(uniq[a]), b, sorted(uniq[b])))
for x in sorted(near, reverse=True)[:5]: print('INFO closest pair (eyeball):', x)
for x in fails: print('FAIL same photo for different services:', x)
print(f"check-service-photo-dupes: {'FAIL' if fails else 'OK'} images={len(items)} unique={len(uniq)} pairs={len(fails)}")
sys.exit(1 if fails else 0)
