#!/usr/bin/env python3
"""Slim geo for the edge keyword x town renderer (UK TOP 5000 + mainland 10k).

slug -> [council, county, [outcodes<=4], [wards<=4], constituency, lat, lng, census|null]
census = [flats_pct, social_pct, top_industry, top_industry_pct] from barriers-places (ONS 2021).
Source: postcodes.io (ONSPD). Re-run: python3 website/bin/build-town-geo-slim.py
"""
import json, time, urllib.request, concurrent.futures as cf
from pathlib import Path
D = Path(__file__).resolve().parent.parent / 'data'
API = 'https://api.postcodes.io'
NONGEO = set('BS98 BS99 CM92 CM98 CM99 CR44 CR90 DH98 DH99 E98 EC50 IV99 L70 L80 LS88 LS98 LS99 M60 M61 M99 N1P N81 NE82 NE83 NE85 NE88 NE92 NE98 NE99 NG70 NG80 NG90 NW1W NW26 PR11 SE1P SO97 ST55 SW99 WC99 BN50 BN51 BN52 BN88 BN91 BN95 BN99 B99 CF91 CF95 CF99 CH25 CH26 CH27 CH28 CH29 CH30 CH31 CH32 CH33 CH34 DN55 G58 G79 LE21 LE41 LE55 LE87 LE94 LE95 ME99 MK77 NN99 NR99 OL95 S95 S96 S97 S98 S99 SA80 SA99 SL60 SN38 SN99 SR43 SR88 WD99 WR78 WR99 WV98 WV99 YO90 YO91 BB94 BD98 BD99 BL11 BL78 FY0 GU95 IP98 GIR E1W'.split())

def req(path, body=None):
    for i in range(4):
        try:
            r = urllib.request.Request(API + path, data=json.dumps(body).encode() if body else None, headers={'Content-Type': 'application/json'})
            with urllib.request.urlopen(r, timeout=40) as f:
                return json.load(f).get('result')
        except Exception:
            time.sleep(1 + 2 * i)
    return None

towns = {}
for t in json.load(open(D / 'uk-top5000-towns.json'))['towns']:
    towns[t['slug']] = (t['lat'], t['lng'])
for t in json.load(open(D / 'uk-mainland-towns-10k.json'))['towns']:
    towns.setdefault(t['slug'], (t['lat'], t['lng']))
import re, urllib.parse
def slugify(n):
    return re.sub(r'[^a-z0-9]+', '-', n.lower().replace('&', 'and')).strip('-')
# Matrix areas (Greater Manchester / North West suburbs) that are not on the town lists.
EXTRA_GM = ['Brinnington', 'Castleton', 'Davyhulme', 'Dialstone', 'Edgeley', 'Gatley', 'Heaton Mersey', 'Heaton Norris', 'Newhey', 'Reddish']
for n in json.load(open(D / 'areas.json')) + EXTRA_GM:
    sl = slugify(n)
    if sl in towns:
        continue
    cands = req('/places?' + urllib.parse.urlencode({'q': n, 'limit': 30})) or []
    best = None
    for c in cands:
        if (c.get('name_1') or '').lower() != n.lower() or c.get('country') != 'England':
            continue
        score = (0 if c.get('region') == 'North West' else 10) + {'Town': 0, 'Suburban Area': 1, 'Other Settlement': 2, 'Village': 3}.get(c.get('local_type'), 5)
        if best is None or score < best[0]:
            best = (score, c)
    if best and best[1].get('region') == 'North West':
        towns[sl] = (best[1]['latitude'], best[1]['longitude'])
census = {}
for r in json.load(open(D / 'barriers-places.json')):
    def pct(rows, needle):
        for x in rows or []:
            if needle.lower() in x['label'].lower():
                return round(float(x['pct']), 1)
        return None
    ind = (r.get('industry') or [None])[0]
    census[r['slug']] = [pct(r.get('housing'), 'Flat'), pct(r.get('tenure'), 'social'),
                         ind['label'].split(';')[0] if ind else None, round(float(ind['pct']), 1) if ind else None]

slugs = list(towns)
rev = {}
for i in range(0, len(slugs), 100):
    chunk = slugs[i:i + 100]
    res = req('/postcodes', {'geolocations': [{'latitude': towns[s][0], 'longitude': towns[s][1], 'limit': 1, 'radius': 2000} for s in chunk]}) or []
    for s, row in zip(chunk, res):
        pc = (row.get('result') or [None])[0] if row else None
        if pc:
            rev[s] = pc

def ocs(s):
    lat, lng = towns[s]
    r = req(f'/outcodes?lon={lng}&lat={lat}&limit=6&radius=6000') or []
    return s, [o for o in r if o['outcode'] not in NONGEO][:4]

out = {}
with cf.ThreadPoolExecutor(6) as ex:
    for s, oc in ex.map(ocs, slugs):
        pc = rev.get(s)
        if not pc and not oc:
            continue
        wards = []
        for o in oc[:2]:
            for w in o.get('admin_ward') or []:
                if w not in wards and 'tunstall' not in w.lower():
                    wards.append(w)
        council = (pc or {}).get('admin_district') or ''
        county = (pc or {}).get('admin_county') or ''
        out[s] = [council, county, [o['outcode'] for o in oc], wards[:4], (pc or {}).get('parliamentary_constituency') or '',
                  round(towns[s][0], 4), round(towns[s][1], 4), census.get(s)]
json.dump(out, open(D / 'town-geo-slim.json', 'w'), separators=(',', ':'), ensure_ascii=False)
print('towns', len(slugs), 'geo', len(out), 'census', sum(1 for v in out.values() if v[7]))
