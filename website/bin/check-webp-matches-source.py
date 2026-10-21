#!/usr/bin/env python3
"""Every optimised WebP in data/image-manifest.json must show its source photo.
Perceptual hash (64-bit dHash) of each WebP vs its source JPG/PNG; fail if distance > 10.
Also fails when one opt hash serves two different source files, or a manifest WebP is missing.
Usage: python3 website/bin/check-webp-matches-source.py"""
import json, os, sys
from PIL import Image
root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
def dhash(p):
    im = Image.open(p).convert('L').resize((9, 8), Image.LANCZOS)
    px = list(im.tobytes()); return sum(1 << i for i in range(64) if px[(i // 8) * 9 + i % 8] > px[(i // 8) * 9 + i % 8 + 1])
m = json.load(open(os.path.join(root, 'data/image-manifest.json')))
fail = n = 0; owner = {}
for src, v in m.items():
    sp = os.path.join(root, src.lstrip('/'))
    if not os.path.exists(sp): continue
    sh = dhash(sp)
    for x in v.get('variants', []):
        wp = os.path.join(root, x['webp'].lstrip('/')); n += 1
        key = x['webp'].rsplit('/', 1)[1].split('-')[0]
        if owner.setdefault(key, sp) != sp and open(owner[key], 'rb').read() != open(sp, 'rb').read():
            print(f'FAIL shared opt {key}: {src} and {owner[key]}'); fail += 1
        if not os.path.exists(wp): print(f'FAIL missing {x["webp"]} for {src}'); fail += 1; continue
        d = bin(sh ^ dhash(wp)).count('1')
        if d > 10: print(f'FAIL {x["webp"]} distance {d} from {src}'); fail += 1
print(f'check-webp-matches-source: {n} webps, {fail} fails'); sys.exit(1 if fail else 0)
