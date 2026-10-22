#!/usr/bin/env python3
"""QM 22 Oct: every service must show a genuinely different photo, judged visually, not by bytes.
Compares every services/*.jpg, families/*-photo.jpg and keywords/*.jpg pairwise with
 (1) dHash 16x16 on the full image and on centre crops (80%, 60%) - tolerant threshold, catches re-encodes/resizes;
 (2) ORB features + RANSAC homography inliers - catches recrops, rescales, flips-free reframing.
Pairs of different services that look the same fail. Same-service variants (-photo, -2, -context, family crops
<fam>-photo-<x>) are one service. Deliberate shares go in data/service-photo-shared.json {"groups": [[slug,...]]}."""
import itertools, json, os, re, sys
import cv2, numpy as np
root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
img = os.path.join(root, 'assets/images')
sp = os.path.join(root, 'data/service-photo-shared.json')
groups = [set(g) for g in (json.load(open(sp))['groups'] if os.path.exists(sp) else [])]
files = []
for sub in ('services', 'families', 'keywords', 'heroes'):
    d = os.path.join(img, sub)
    for f in sorted(os.listdir(d)) if os.path.isdir(d) else []:
        if f.lower().endswith(('.jpg', '.jpeg')): files.append((sub, f[:-4] if f.endswith('.jpg') else f[:-5], os.path.join(d, f)))
KW = {k: v.get('service') for k, v in json.load(open(os.path.join(root, 'data/keywords.json'))).items() if isinstance(v, dict)}
FAMS = json.load(open(os.path.join(root, 'data/service-image-families.json')))['families']
D2F = {d: f for f, ds in FAMS.items() for d in ds}
def svc(sub, s):
    if sub == 'keywords' and KW.get(s): s = KW[s]
    if sub == 'families': s = re.sub(r'-photo(-[a-z]+)?$', '', s) + '#fam'
    return re.sub(r'-(photo|context|callpoint|[0-9]+)$', '', s)
def dh(g):
    r = cv2.resize(g, (17, 16), interpolation=cv2.INTER_AREA).astype(int)
    return (r[:, 1:] > r[:, :-1]).flatten()
orb = cv2.ORB_create(600)
F = {}
for sub, s, p in files:
    g = cv2.imread(p, cv2.IMREAD_GRAYSCALE)
    if g is None: continue
    h, w = g.shape; sc = 640 / max(h, w); g = cv2.resize(g, (int(w * sc), int(h * sc)), interpolation=cv2.INTER_AREA)
    crops = [g]
    for c in (0.8, 0.6):
        H, W = g.shape; y, x = int(H * (1 - c) / 2), int(W * (1 - c) / 2); crops.append(g[y:H - y, x:W - x])
    kp, de = orb.detectAndCompute(g, None)
    F[(sub, s)] = ([dh(c) for c in crops], kp, de)
bf = cv2.BFMatcher(cv2.NORM_HAMMING)
def inliers(a, b):
    _, ka, da = F[a]; _, kb, db = F[b]
    if da is None or db is None or len(ka) < 30 or len(kb) < 30: return 0
    ms = [m for m, n in (x for x in bf.knnMatch(da, db, k=2) if len(x) == 2) if m.distance < 0.75 * n.distance]
    if len(ms) < 12: return len(ms) // 3
    A = np.float32([ka[m.queryIdx].pt for m in ms]); B = np.float32([kb[m.trainIdx].pt for m in ms])
    _, mask = cv2.findHomography(A, B, cv2.RANSAC, 6.0)
    return int(mask.sum()) if mask is not None else 0
fails = []
keys = list(F)
for a, b in itertools.combinations(keys, 2):  # ORB only on pairs whose best crop dHash is within 95/256
    sa, sb = svc(*a), svc(*b)
    if sa == sb or sa.replace('#fam', '') == sb.replace('#fam', ''): continue
    # a family photo stands for all of its member services (e.g. gas family photo = heating)
    fa = sa[:-4] if sa.endswith('#fam') else None; fb = sb[:-4] if sb.endswith('#fam') else None
    if (fa and D2F.get(sb) == fa) or (fb and D2F.get(sa) == fb) or (fa and fb is None and fa == D2F.get(sb)): continue
    if any(a[1] in g and b[1] in g for g in groups): continue
    hd = min(int((x != y).sum()) for x in F[a][0] for y in F[b][0])
    il = (inliers(a, b) if hd <= 95 else 0) if hd > 40 else 99
    if hd <= 40 or il >= 40: fails.append((hd, il, f'{a[0]}/{a[1]}', f'{b[0]}/{b[1]}'))
for x in sorted(fails): print('FAIL visually same photo for different services: dhash=%d orb_inliers=%d %s ~ %s' % x)
print(f"check-photo-visual-dupes: {'FAIL' if fails else 'OK'} photos={len(F)} pairs={len(fails)}")
sys.exit(1 if fails else 0)
