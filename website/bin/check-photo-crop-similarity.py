#!/usr/bin/env python3
"""Crop-tolerant similarity check across every service photo.

Re-encoded, resized or recropped copies of one service's photo on another service are caught by
matching ORB keypoints and keeping only matches consistent with a single homography (RANSAC).
Two photos owned by different services fail when they share >= MIN_INLIERS geometric inliers.
A 20%-crop, re-encoded copy scores ~128 inliers; unrelated photos stay under 40.
Usage: python3 website/bin/check-photo-crop-similarity.py [--min-inliers N]
"""
import json, os, sys, glob, itertools
from multiprocessing import Pool
import cv2, numpy as np
ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
MIN_INLIERS = int(sys.argv[sys.argv.index('--min-inliers') + 1]) if '--min-inliers' in sys.argv else 60
owners = json.load(open(f'{ROOT}/data/image-owners.json'))
pools = json.load(open(f'{ROOT}/data/service-image-pools.json'))
files = set()
def walk(v):
    if isinstance(v, str) and v.startswith('/assets/') and v.lower().endswith(('.jpg', '.jpeg', '.png')): files.add(v)
    elif isinstance(v, dict): [walk(x) for x in v.values()]
    elif isinstance(v, list): [walk(x) for x in v]
walk(pools)
for d in ('services', 'page-types', 'manufacturers'):
    for p in glob.glob(f'{ROOT}/assets/images/{d}/**/*.jp*g', recursive=True):
        files.add('/' + os.path.relpath(p, ROOT).replace(os.sep, '/'))
files = sorted(f for f in files if os.path.exists(ROOT + f))
def stem(f):
    b = os.path.basename(f).rsplit('.', 1)[0]
    for suf in ('-photo', '-context', '-site', '-2', '-3'):
        if b.endswith(suf): b = b[: -len(suf)]
    return b.rstrip('-0123456789') or b
def owner(f):
    # page-type photos are all owned by a page type, so judge them by the service their file names
    if '/page-types/' in f: return stem(f)
    if '--' in os.path.basename(f): return os.path.basename(f).rsplit('.', 1)[0].split('--')[1]
    return owners.get(f) or stem(f)
def feats(f):
    im = cv2.imread(ROOT + f, cv2.IMREAD_GRAYSCALE)
    if im is None: return f, None
    s = 640 / max(im.shape); im = cv2.resize(im, None, fx=s, fy=s) if s < 1 else im
    kp, des = cv2.ORB_create(800).detectAndCompute(im, None)
    return f, (np.float32([k.pt for k in kp]), des)
F = {}
def match(pair):
    a, b = pair; (pa, da), (pb, db) = F[a], F[b]
    if da is None or db is None or len(da) < 20 or len(db) < 20: return None
    m = cv2.BFMatcher(cv2.NORM_HAMMING).knnMatch(da, db, k=2)
    good = [x[0] for x in m if len(x) == 2 and x[0].distance < 0.75 * x[1].distance]
    if len(good) < MIN_INLIERS: return None
    H, mask = cv2.findHomography(pa[[g.queryIdx for g in good]], pb[[g.trainIdx for g in good]], cv2.RANSAC, 6.0)
    n = int(mask.sum()) if mask is not None else 0
    return (a, b, n) if n >= MIN_INLIERS else None
def init(d):
    global F; F = d
if __name__ == '__main__':
    with Pool() as p: F = {f: v for f, v in p.map(feats, files) if v is not None}
    pairs = [(a, b) for a, b in itertools.combinations(sorted(F), 2) if owner(a) != owner(b) and stem(a) != stem(b)]
    with Pool(initializer=init, initargs=(F,)) as p: hits = [h for h in p.imap_unordered(match, pairs, chunksize=200) if h]
    for a, b, n in sorted(hits, key=lambda h: -h[2]):
        print(f'FAIL crop-similar inliers={n}: {a} ({owner(a)}) ~ {b} ({owner(b)})')
    print(f'check-photo-crop-similarity: {len(F)} photos, {len(pairs)} cross-service pairs, {len(hits)} fails')
    sys.exit(1 if hits else 0)
