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
# SEO 22 Oct: services where the family photo is a loose fit get their own credited Commons photo.
SVC_ALT = {'landscaping': 'Planted garden border with bark mulch', 'building-surveys': 'Thermal imaging survey of a church building', 'facilities-management': 'Maintenance worker servicing a washroom'}
for sv in SVC_ALT:
    rel = f'/assets/images/families/{sv}-photo.jpg'
    if rel in man:
        v = man[rel]['variants']; c = cred.get(rel, {})
        fp_photo[sv] = dict(src=v[-1]['webp'], srcset=', '.join(f"{x['webp']} {x['w']}w" for x in v), alt=SVC_ALT[sv],
                            credit=f"Photo: {c.get('artist', '')}, {c.get('license', '')}, via Wikimedia Commons")
# QM #158 r2: otherwise a switched image becomes the page's OWN service photo (hero service), not the family photo.
def svc_photo(sv):
    rel = f'/assets/images/services/{sv}.jpg'
    if rel not in man or rel not in cred: return None
    v = man[rel]['variants']; c = cred[rel]
    return dict(src=v[-1]['webp'], srcset=', '.join(f"{x['webp']} {x['w']}w" for x in v), alt=sv.replace('-', ' ').capitalize() + ' work',
                credit=f"Photo: {c.get('artist', '')}, {c.get('license', '')}, via Wikimedia Commons")
def caption(ph):
    return ('<span class="pf-credit fam-credit" style="display:block;font-size:.8rem;color:#555555;margin:.3rem 0 .6rem">'
            + ph['credit'].replace('&', '&amp;').replace('<', '&lt;') + ' · <a href="/pages/photo-credits">Photo credits</a></span>')
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
            hm = re.search(r'/assets/images/services/([a-z0-9-]+)\.(?:jpg|png|webp)', open(fp, encoding='utf-8', errors='replace').read())
            if hm and hm.group(1) in dir2fam: pf = dir2fam[hm.group(1)] if dir2fam[hm.group(1)] in ALT else pf
            sv = hm.group(1) if hm and hm.group(1) in SVC_ALT else next((k for k in SVC_ALT if k in u), None)
            if sv is None and hm:
                hb = re.sub(r'-photo$', '', hm.group(1))
                if hb not in fp_photo:
                    sp = svc_photo(hb)
                    if sp: fp_photo[hb] = sp
                sv = hb if hb in fp_photo else None
            if pf in ALT: files.append((fp, pf, sv))
use = collections.defaultdict(collections.Counter)
for fp, pf, _ in files:
    s = open(fp, encoding='utf-8', errors='replace').read()
    for m in IMG.finditer(s):
        for k in tag_keys(m.group(0)): use[k][pf] += 1
owner = {k: c.most_common(1)[0][0] for k, c in use.items()}
changed = tags = 0
for fp, pf, sv in files:
    s = open(fp, encoding='utf-8', errors='replace').read(); ph = fp_photo.get(sv) or fp_photo[pf]; n = [0]
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
        return t[:-1].rstrip('/').rstrip() + f' width="1280" height="823" data-credit="{ph["credit"]}">' + '<!--famcap-->'
    s2 = IMG.sub(fix, s)
    # Visible credit under each switched photo: replace the figure's own caption if it has one, else add a line.
    def cap(mm):
        return mm.group(1) + '<figcaption style="font-size:.8rem;color:#555555;margin-top:.3rem">' + caption(ph)[caption(ph).index('>') + 1:-7] + '</figcaption>'
    s2 = re.sub(r'(<!--famcap-->(?:(?!</figure>)[\s\S]){0,400}?)<figcaption\b[^>]*>[\s\S]*?</figcaption>', lambda mm: cap(mm).replace('<!--famcap-->', '', 1), s2)
    s2 = s2.replace('<!--famcap-->', caption(ph))
    if n[0]:
        open(fp, 'w', encoding='utf-8').write(s2); changed += 1; tags += n[0]
print(f'family-images: {len(files)} family pages, {changed} pages / {tags} image tags switched to own-family photos')
