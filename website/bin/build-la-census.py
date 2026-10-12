#!/usr/bin/env python3
"""ONS Census 2021 TS044 (accommodation) + TS054 (tenure) by local authority (England & Wales),
from Nomis. Used only as a labelled whole-council fallback when a town has no built-up-area figures.
Re-run: python3 website/bin/build-la-census.py"""
import csv, io, json, urllib.request
from pathlib import Path
D = Path(__file__).resolve().parent.parent / 'data'
B = 'https://www.nomisweb.co.uk/api/v01/dataset/'
def rows(ds, dim):
    url = f'{B}{ds}.data.csv?geography=TYPE424&measures=20100&select=geography_name,geography_code,{dim}_name,obs_value'
    return list(csv.DictReader(io.StringIO(urllib.request.urlopen(url, timeout=60).read().decode())))
out = {}
for r in rows('NM_2062_1', 'c2021_acctype_9'):
    o = out.setdefault(r['GEOGRAPHY_NAME'], {'code': r['GEOGRAPHY_CODE'], 'housing': {}, 'tenure': {}})
    o['housing'][r['C2021_ACCTYPE_9_NAME']] = int(r['OBS_VALUE'])
for r in rows('NM_2072_1', 'c2021_tenure_9'):
    out.setdefault(r['GEOGRAPHY_NAME'], {'code': r['GEOGRAPHY_CODE'], 'housing': {}, 'tenure': {}})['tenure'][r['C2021_TENURE_9_NAME']] = int(r['OBS_VALUE'])
res = {}
for name, o in out.items():
    h, t = o['housing'], o['tenure']
    ht, tt = h.get('Total') or 0, t.get('Total: All households') or 0
    if not ht or not tt:
        continue
    flats = sum(v for k, v in h.items() if k.startswith(('In a purpose-built', 'Part of a converted', 'Part of another', 'In a commercial')))
    pc = lambda v, tot: round(100 * v / tot, 1)
    res[name] = {'code': o['code'], 'households': tt,
        'housing': [['Detached', h.get('Detached', 0), pc(h.get('Detached', 0), ht)], ['Semi-detached', h.get('Semi-detached', 0), pc(h.get('Semi-detached', 0), ht)],
                    ['Terraced', h.get('Terraced', 0), pc(h.get('Terraced', 0), ht)], ['Flat, maisonette or apartment', flats, pc(flats, ht)]],
        'tenure': [['Owned', t.get('Owned', 0), pc(t.get('Owned', 0), tt)], ['Social rented', t.get('Social rented', 0), pc(t.get('Social rented', 0), tt)],
                   ['Private rented', t.get('Private rented', 0), pc(t.get('Private rented', 0), tt)]]}
json.dump(res, open(D / 'la-census.json', 'w'), separators=(',', ':'))
print(len(res))
