#!/usr/bin/env python3
"""After export: a page only shows photos owned by its own service family (QM #156 img_reused_across_families).
Page family comes from the URL, using data/service-image-families.json. An image's owner family is the family
with the most pages showing it. On a page of another family, that <img>/<source> switches to the family's own
credited Commons photo (assets/images/families/<family>-photo.jpg, credits in data/hero-image-credits.json).
Pages with no family and brand/logo images are left alone."""
import os, re, sys, json, collections
dist = sys.argv[1] if len(sys.argv) > 1 else 'dist'
root = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')
fam = json.load(open(os.path.join(root, 'data/service-image-families.json')))['families']
man = json.load(open(os.path.join(root, 'data/image-manifest.json')))
cred = {c['file']: c for c in json.load(open(os.path.join(root, 'data/hero-image-credits.json')))}
dir2fam = {d: f for f, ds in fam.items() for d in ds}
famkeys = sorted(dir2fam, key=len, reverse=True)
def page_family(u):
    s = u.lower()
    for k in famkeys:
        if k.replace('-photo', '') in s: return dir2fam[k]
    for f in fam:
        if f in s: return f
    if 'aov' in s or 'smoke' in s: return 'fire'
    if 'roof' in s: return 'roofing'
    return None
ALT = {'fire': 'Fire alarm manual call point', 'gas': 'Wall-mounted gas boiler in a kitchen', 'security': 'CCTV cameras on a building',
       'electrical': 'Consumer unit with RCD protection', 'building': 'Building under renovation with scaffolding', 'compliance': 'Asbestos warning label'}
fp_photo = {}
for f in ALT:
    rel = f'/assets/images/families/{f}-photo.jpg'
    v = man[rel]['variants']; c = cred.get(rel, {})
    fp_photo[f] = dict(src=v[-1]['webp'], srcset=', '.join(f"{x['webp']} {x['w']}w" for x in v), alt=ALT[f],
                       credit=f"Photo: {c.get('artist', '')}, {c.get('license', '')}, via Wikimedia Commons")
OPT2SRC = {}
for _k, _v in man.items():
    for _x in _v.get('variants', []):
        OPT2SRC.setdefault(_x['webp'].rsplit('/', 1)[1].split('-')[0], _k)
IMG = re.compile(r'<(img|source)\b[^>]*>', re.I)
SRC = re.compile(r'\b(src|data-src)="([^"]+)"', re.I)
SRCSET = re.compile(r'\bsrcset="([^"]+)"', re.I)
def key(s):
    s = s.split('?')[0].split('#')[0]
    s = re.sub(r'^https?://[^/]+', '', s)
    return s if s.startswith('/assets/') and 'brand/' not in s and 'logo' not in s and '/families/' not in s else None
def tag_keys(t):
    ks = [key(m.group(2)) for m in SRC.finditer(t)]
    for m in SRCSET.finditer(t):
        ks += [key(p.strip().split(' ')[0]) for p in m.group(1).split(',') if p.strip()]
    return {k for k in ks if k}
files = []
for r, _, fs in os.walk(dist):
    if '/assets' in r: continue
    for f in fs:
        if f.endswith(('.php', '.html')):
            fp = os.path.join(r, f); u = '/' + os.path.relpath(fp, dist).rsplit('.', 1)[0]
            u = re.sub(r'/index$', '', u) or '/'
            pf = page_family(u)
            # The hero service photo's family wins over URL words (e.g. a windows-doors page whose URL says fire-door).
            hm = None
            _raw = open(fp, encoding='utf-8', errors='replace').read()
            _b = _raw.find('<body')
            _fig = re.search(r'<figure\b.*?</figure>', _raw[_b:] if _b >= 0 else '', re.S | re.I)  # hero figure only, never og:image/head
            if _fig:
                hm = re.search(r'/assets/images/services/([a-z0-9-]+)\.(?:jpg|png|webp)', _fig.group(0))
                if not hm:
                    _o = re.search(r'/assets/images/opt/([0-9a-f]{12})-\d+\.webp', _fig.group(0))
                    _src = OPT2SRC.get(_o.group(1), '') if _o else ''
                    hm = re.search(r'/assets/images/services/([a-z0-9-]+)\.(?:jpg|png|webp)', _src)
            if hm and hm.group(1) in dir2fam: pf = dir2fam[hm.group(1)] if dir2fam[hm.group(1)] in ALT else pf
            if pf in ALT: files.append((fp, pf))
use = collections.defaultdict(collections.Counter)
for fp, pf in files:
    s = open(fp, encoding='utf-8', errors='replace').read()
    for m in IMG.finditer(s):
        for k in tag_keys(m.group(0)): use[k][pf] += 1
owner = {k: c.most_common(1)[0][0] for k, c in use.items()}
changed = tags = 0
for fp, pf in files:
    s = open(fp, encoding='utf-8', errors='replace').read(); ph = fp_photo[pf]; n = [0]
    def fix(m):
        t = m.group(0)
        if not any(owner.get(k) != pf for k in tag_keys(t)): return t
        n[0] += 1
        if m.group(1).lower() == 'source':
            return SRCSET.sub(f'srcset="{ph["srcset"]}"', t)
        t = SRC.sub(lambda x: f'{x.group(1)}="{ph["src"]}"', t)
        t = SRCSET.sub(f'srcset="{ph["srcset"]}"', t)
        t = re.sub(r'\balt="[^"]*"', f'alt="{ph["alt"]}"', t, count=1)
        t = re.sub(r'\s(width|height)="\d+"', '', t)
        return t[:-1].rstrip('/').rstrip() + f' width="1280" height="823" data-credit="{ph["credit"]}">'
    s2 = IMG.sub(fix, s)
    if n[0]:
        open(fp, 'w', encoding='utf-8').write(s2); changed += 1; tags += n[0]
print(f'family-images: {len(files)} family pages, {changed} pages / {tags} image tags switched to own-family photos')
