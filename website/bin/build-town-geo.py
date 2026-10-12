#!/usr/bin/env python3
"""Build data/town-geo.json for AOV + barrier town pages.

Public facts only, from postcodes.io (ONS Postcode Directory + OS Open Names):
local authority, county, constituency, postcode districts, wards, lat/lon.
A town that cannot be matched with confidence gets no geo block (the page
falls back to its existing copy). Re-run: python3 website/bin/build-town-geo.py
"""
import json, re, sys, time, urllib.parse, urllib.request, concurrent.futures as cf
from pathlib import Path
D = Path(__file__).resolve().parent.parent / 'data'
API = 'https://api.postcodes.io'
NONGEO = set('BS98 BS99 CM92 CM98 CM99 CR44 CR90 DH98 DH99 E98 EC50 IV99 L70 L80 LS88 LS98 LS99 M60 M61 M99 N1P N81 NE82 NE83 NE85 NE88 NE92 NE98 NE99 NG70 NG80 NG90 NW1W NW26 PR11 SE1P SO97 ST55 SW99 WC99 BN50 BN51 BN52 BN88 BN91 BN95 BN99 B99 CF91 CF95 CF99 CH25 CH26 CH27 CH28 CH29 CH30 CH31 CH32 CH33 CH34 DN55 G58 G79 LE21 LE41 LE55 LE87 LE94 LE95 ME99 MK77 NN99 NR99 OL95 S95 S96 S97 S98 S99 SA80 SA99 SL60 SN38 SN99 SR43 SR88 WD99 WR78 WR99 WV98 WV99 YO90 YO91 BB94 BD98 BD99 BL11 BL78 FY0 GU95 IP98 GIR E1W'.split())
RANK = {'City': 0, 'Town': 1, 'Other Settlement': 2, 'Suburban Area': 3, 'Village': 4, 'Hamlet': 6}

def get(path):
    for i in range(4):
        try:
            with urllib.request.urlopen(API + path, timeout=30) as r:
                return json.load(r).get('result') or []
        except Exception:
            time.sleep(1 + i * 2)
    return []

def norm(s):
    return re.sub(r'[^a-z0-9]+', ' ', s.lower().replace('&', 'and')).strip()

def towns():
    out = {}
    for r in json.load(open(D / 'uk-mainland-towns-10k.json'))['towns']:
        out[r['slug']] = {'name': r['name'], 'country': r['nation'], 'region': r['region'], 'lat': r['lat'], 'lon': r['lng'], 'county': r.get('county', '')}
    for r in json.load(open(D / 'uk-towns-10k.json')):
        out.setdefault(r['slug'], {'name': r['name'], 'country': r['nation'], 'region': r['region']})
    for r in json.load(open(D / 'barriers-places.json')):
        out.setdefault(r['slug'], {'name': r['name'], 'country': r['country'], 'region': r['region'], 'kind': r.get('kind')})
    return out

def by_coord(t):
    lat, lon = t['lat'], t['lon']
    pcs = get(f'/postcodes?lon={lon}&lat={lat}&limit=1&radius=2000') or get(f'/postcodes?lon={lon}&lat={lat}&limit=1&radius=10000')
    if not pcs:
        return None
    pc = pcs[0]
    c = {'latitude': lat, 'longitude': lon, 'local_type': 'Town',
         'district_borough': pc.get('admin_district') or '', 'district_borough_type': '',
         'county_unitary': pc.get('admin_county') or '', 'county_unitary_type': 'County' if pc.get('admin_county') else '',
         'region': pc.get('region') or pc.get('country') or '', 'outcode': pc.get('outcode') or ''}
    if pc.get('country') in ('Wales', 'Scotland'):
        c['county_unitary'] = pc.get('admin_district') or ''
        c['district_borough'] = ''
    return c

def match(slug, t):
    if 'lat' in t:
        c = by_coord(t)
        return finish(t, c) if c else None
    name = t['name']
    base = re.sub(r'\s*\(.*?\)', '', name).strip()
    hint = (re.search(r'\((.*?)\)', name) or [None, ''])[1]
    cands = get('/places?' + urllib.parse.urlencode({'q': base, 'limit': 100}))
    best = None
    lb = t.get('kind') == 'london-borough' or t['region'] == 'London'
    if lb and not any(norm(c.get('name_1') or '') == norm(base) for c in cands):
        cands = cands + get('/places?' + urllib.parse.urlencode({'q': base.split(' and ')[0], 'limit': 100}))
    for c in cands:
        if lb and norm(c.get('district_borough') or '') == norm(base) and (c.get('country') == 'England'):
            score = RANK.get(c.get('local_type'), 5) * 10 - 40
            if best is None or score < best[0]:
                best = (score, c)
            continue
        if norm(c.get('name_1') or '') != norm(base) and norm(c.get('name_2') or '') != norm(base):
            continue
        if (c.get('country') or '') != t['country']:
            continue
        score = RANK.get(c.get('local_type'), 5) * 10
        hay = norm(' '.join(str(c.get(k) or '') for k in ('district_borough', 'county_unitary', 'region')))
        for part in re.split(r',\s*', t['region']):
            if part and norm(part) in hay:
                score -= 15
        if hint and norm(hint) in hay:
            score -= 30
        area = (c['max_eastings'] - c['min_eastings']) * (c['max_northings'] - c['min_northings'])
        score -= min(area / 4e6, 8)
        if best is None or score < best[0]:
            best = (score, c)
    if not best:
        return None
    return finish(t, best[1])

def finish(t, c):
    REG = {'Eastern': 'East of England', 'Yorkshire and the Humber': 'Yorkshire and The Humber'}
    if c.get('region') in REG:
        c['region'] = REG[c['region']]
    if 'lat' not in t and t['country'] == 'England' and t['region'] not in ('London',) and c.get('region') and c['region'] != t['region'] and not (t['region'] == 'Yorkshire and The Humber' and 'Yorkshire' in c['region']) and not (t['region'] == 'East of England' and c['region'] == 'East of England'):
        return None
    lat, lon = c['latitude'], c['longitude']
    ocs = get(f'/outcodes?lon={lon}&lat={lat}&limit=12&radius=8000')
    district = c.get('district_borough') or ''
    ocs = [o for o in ocs if o['outcode'] not in NONGEO]
    keep = [o for o in ocs if district in (o.get('admin_district') or [])] or ocs
    keep = keep[:6]
    wards = []
    for o in keep[:2]:
        for w in o.get('admin_ward') or []:
            if w not in wards:
                wards.append(w)
    cons = []
    for o in keep[:3]:
        for p in o.get('parliamentary_constituency') or []:
            if p not in cons:
                cons.append(p)
    return {
        'lat': round(lat, 4), 'lon': round(lon, 4),
        'local_type': c.get('local_type'),
        'district': district, 'district_type': c.get('district_borough_type'),
        'county': (c.get('county_unitary') or '').split(' - ')[-1], 'county_type': c.get('county_unitary_type') or '',
        'ons_region': c.get('region') or '',
        'outcode': c.get('outcode') or '',
        'outcodes': [o['outcode'] for o in keep],
        'wards': wards[:10],
        'districts': [{'oc': o['outcode'], 'wards': (o.get('admin_ward') or [])[:6], 'council': (o.get('admin_district') or [''])[0]} for o in keep],
        'constituencies': cons[:3],
    }

def main():
    t = towns()
    res = {}
    with cf.ThreadPoolExecutor(8) as ex:
        for slug, g in zip(t, ex.map(lambda s: match(s, t[s]), t)):
            if g:
                res[slug] = g
    json.dump({'_note': 'postcodes.io (ONSPD + OS Open Names), built ' + time.strftime('%Y-%m-%d') + '. Facts shown on AOV/barrier town pages.', 'towns': res},
              open(D / 'town-geo.json', 'w'), separators=(',', ':'), ensure_ascii=False)
    print(f'matched {len(res)}/{len(t)}')
    print('unmatched sample:', [t[s]['name'] for s in t if s not in res][:40])

main()
