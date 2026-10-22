#!/usr/bin/env python3
"""Route keyword, job and commercial pages to the photo pool of the trade their slug names.

A keyword such as "pat-testing-landlord" is catalogued under the electrical service, but the page
is about PAT testing, so it should show PAT testing photos rather than the generic electrical set.
The trade is read from the slug with the same family names used in service-image-families.json.
Writes data/page-image-pool-overrides.json, read by includes/image-pool.php and netlify/lib/trades-lock.js.
"""
import json, os, re
D = os.path.join(os.path.dirname(__file__), '..', 'data')
fam = json.load(open(f'{D}/service-image-families.json'))['families']
pools = json.load(open(f'{D}/service-image-pools.json'))
dir2fam = {d: f for f, ds in fam.items() for d in ds}
keys = sorted(dir2fam, key=len, reverse=True)

def family(slug):
    s = slug.lower()
    for k in keys:
        if k.replace('-photo', '') in s:
            return dir2fam[k].replace('-photo', '')
    for f in fam:
        if f in s:
            return f.replace('-photo', '')
    return None

def pool_ok(key):
    own = (pools.get(key) or {}).get('own') or []
    return len(own) >= 2

ks = json.load(open(f'{D}/keyword-services.json'))
fb = json.load(open(f'{D}/keyword-service-fallback.json'))
p0 = json.load(open(f'{D}/seo-lock/p0-index.json'))
rows = p0.get('rows', p0) if isinstance(p0, dict) else {}
out = {}
for slug in sorted(set(ks) | set(fb) | set(rows)):
    f = family(slug)
    if not f:
        continue
    row = rows.get(slug) if isinstance(rows.get(slug), dict) else {}
    service = ks.get(slug) or row.get('service') or fb.get(slug) or ''
    kind = row.get('kind', '')
    if f == service:
        continue
    key = f'type-keywords-{f}'
    if pool_ok(key):
        out[f'{"jobs" if kind == "job" else "keywords"}/{slug}'] = key
here = os.path.join(os.path.dirname(__file__), '..', 'pages', 'commercial')
for name in sorted(os.listdir(here)) if os.path.isdir(here) else []:
    slug = name[:-4] if name.endswith('.php') else name
    f = family(slug.replace('commercial-', ''))
    if f and pool_ok(f'type-keywords-{f}'):
        out[f'commercial/{slug}'] = f'type-keywords-{f}'
json.dump(out, open(f'{D}/page-image-pool-overrides.json', 'w'), indent=1, sort_keys=True)
print(len(out), 'overrides')
