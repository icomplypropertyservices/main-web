#!/usr/bin/env python3
"""Add per-postcode-district ward lists to data/town-geo-slim.json (index 8).

Source: postcodes.io /outcodes reverse lookup (ONS/OS open data) around each town's lat/lon,
reusing data/town-geo.json districts where we already have them. Resumable via /tmp cache.
Slim row: [council, county, outcodes, wards, constituency, lat, lng, census, districts]
districts = [[outcode, [wards<=5], council], ...] (max 4)
"""
import json, sys, time, urllib.request, concurrent.futures as cf
from pathlib import Path
D = Path(__file__).resolve().parent.parent / 'data'
CACHE = Path('/tmp/slim-districts-cache.json')
NONGEO = {'BX1', 'BX2', 'BX3', 'BX4', 'BX5', 'BX9', 'GIR', 'XX', 'BF1'}
slim = json.load(open(D / 'town-geo-slim.json'))
full = json.load(open(D / 'town-geo.json'))['towns']
cache = json.loads(CACHE.read_text()) if CACHE.exists() else {}

def fetch(item):
    slug, row = item
    lat, lng = row[5], row[6]
    for radius in (6000, 15000):
        try:
            r = json.loads(urllib.request.urlopen(f'https://api.postcodes.io/outcodes?lon={lng}&lat={lat}&limit=8&radius={radius}', timeout=30).read())['result'] or []
        except Exception:
            time.sleep(1); r = []
        r = [o for o in r if o['outcode'] not in NONGEO]
        if r:
            council = row[0] or ''
            keep = [o for o in r if council in (o.get('admin_district') or [])] or r
            return slug, [[o['outcode'], (o.get('admin_ward') or [])[:5], (o.get('admin_district') or [''])[0]] for o in keep[:4]]
    return slug, []

todo = []
for slug, row in slim.items():
    if slug in full and full[slug].get('districts'):
        cache[slug] = [[d['oc'], d['wards'][:5], d.get('council', '')] for d in full[slug]['districts'][:4]]
    elif slug not in cache and row[5] is not None:
        todo.append((slug, row))
print('todo', len(todo), flush=True)
with cf.ThreadPoolExecutor(4) as ex:
    for i, (slug, ds) in enumerate(ex.map(fetch, todo)):
        cache[slug] = ds
        if i % 250 == 0:
            CACHE.write_text(json.dumps(cache)); print(i, flush=True)
CACHE.write_text(json.dumps(cache))
for slug, row in slim.items():
    while len(row) < 9:
        row.append(None)
    row[8] = [[o, [w for w in ws if 'tunstall' not in w.lower()], c] for o, ws, c in cache.get(slug, [])]
json.dump(slim, open(D / 'town-geo-slim.json', 'w'), separators=(',', ':'), ensure_ascii=False)
print('done', sum(1 for r in slim.values() if r[8]), 'of', len(slim))
