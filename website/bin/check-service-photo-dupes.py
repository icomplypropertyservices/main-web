#!/usr/bin/env python3
"""QM #158: no photo may serve two different services under different names (renamed duplicates / recrops).
dHash (16x16 grey) of every assets/images/services/*.jpg; pairs within Hamming 20/256 for different services fail,
unless both slugs are listed together in data/service-photo-shared.json (deliberate, e.g. a -photo alias)."""
import itertools, json, os, sys
from PIL import Image
root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
d = os.path.join(root, 'assets/images/services')
allow = json.load(open(os.path.join(root, 'data/service-photo-shared.json'))) if os.path.exists(os.path.join(root, 'data/service-photo-shared.json')) else {"groups": []}
groups = [set(g) for g in allow["groups"]]
def dh(p):
    im = Image.open(p).convert('L').resize((17, 16), Image.LANCZOS); px = list(im.getdata())
    return sum(1 << i for i in range(256) if px[(i // 16) * 17 + i % 16] > px[(i // 16) * 17 + i % 16 + 1])
H = {f[:-4]: dh(os.path.join(d, f)) for f in sorted(os.listdir(d)) if f.endswith('.jpg')}
base = lambda s: s[:-6] if s.endswith('-photo') else s
fails = []
for a, b in itertools.combinations(H, 2):
    if base(a) == base(b) or any(a in g and b in g for g in groups): continue
    dist = bin(H[a] ^ H[b]).count('1')
    if dist <= 20: fails.append((dist, a, b))
for x in sorted(fails): print('FAIL same photo for different services:', x)
print(f"check-service-photo-dupes: {'FAIL' if fails else 'OK'} photos={len(H)} pairs={len(fails)}")
sys.exit(1 if fails else 0)
