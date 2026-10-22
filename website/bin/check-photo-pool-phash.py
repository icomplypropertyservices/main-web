#!/usr/bin/env python3
"""No two photos in the service, AOV, air-handling and page-set pools may be the same picture.
Fails when two files of different services are a perceptual match (64-bit dHash distance <= 6)
or are credited to the same Commons source file. X.jpg, X-photo.jpg and X-context.jpg of one
service may share a source. Usage: python3 website/bin/check-photo-pool-phash.py"""
import json, os, re, sys, itertools
from PIL import Image
root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
def dhash(p):
    im = Image.open(p).convert('L').resize((9, 8), Image.LANCZOS)
    px = list(im.tobytes()); return sum(1 << i for i in range(64) if px[(i // 8) * 9 + i % 8] > px[(i // 8) * 9 + i % 8 + 1])
def stem(f):
    return re.sub(r'(?:-(?:photo|context|site))+$', '', os.path.basename(f).rsplit('.', 1)[0])
pools = json.load(open(os.path.join(root, 'data/service-image-pools.json')))
files = set()
for entry in pools.values():
    if not isinstance(entry, dict): continue
    for k, v in entry.items():
        if isinstance(v, list): files.update(v)
        elif isinstance(v, dict): files.update(x for l in v.values() for x in l)
files = sorted(f for f in files if os.path.exists(os.path.join(root, f.lstrip('/'))))
h = {f: dhash(os.path.join(root, f.lstrip('/'))) for f in files}
fail = 0
for a, b in itertools.combinations(files, 2):
    if stem(a) == stem(b): continue
    d = bin(h[a] ^ h[b]).count('1')
    if d <= 6: print(f'FAIL phash {d}: {a} ~ {b}'); fail += 1
src = {}
for c in json.load(open(os.path.join(root, 'data/hero-image-credits.json'))):
    if c.get('file') not in h: continue
    key = re.sub(r'^https?://commons\.wikimedia\.org/wiki/', '', c.get('source', '')).replace('%3A', ':').replace('_', ' ').lower()
    for other in src.get(key, []):
        if stem(other) != stem(c['file']): print(f'FAIL same source {key}: {c["file"]} and {other}'); fail += 1
    src.setdefault(key, []).append(c['file'])
print(f'check-photo-pool-phash: {len(files)} pool photos, {fail} fails'); sys.exit(1 if fail else 0)
