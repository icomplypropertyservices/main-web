#!/usr/bin/env python3
"""Add Scottish council areas to data/la-census.json from Scotland's Census 2022 (NRS).

Sources (all public):
- Output-area topic tables UV402 (accommodation type, households) and UV404 (tenure, households)
  from https://www.scotlandscensus.gov.uk/documents/2022-output-area-data/ (Census-2022-Output-Area-v1.zip)
- OA22 -> council area code from the NRS Census2022 MapServer layer 3 (maps.gov.scot)
- council code -> name via postcodes.io (one master postcode per council)
OA counts are summed to council level; NRS applies small cell perturbation, so totals are approximate.
"""
import csv, io, json, sys, urllib.parse, urllib.request, zipfile
from collections import defaultdict
from pathlib import Path

D = Path(__file__).resolve().parent.parent / 'data'
ZIP_URL = 'https://www.scotlandscensus.gov.uk/media/zz85kfinmf97whklasd98gfkadft5hj4f_Topic2H_20241120_1747/Census-2022-Output-Area-v1.zip'
MS = 'https://maps.gov.scot/server/rest/services/NRS/Census2022/MapServer/3/query?'
zpath = Path(sys.argv[1]) if len(sys.argv) > 1 else Path('/tmp/sc-oa.zip')
if not zpath.exists():
    urllib.request.urlretrieve(ZIP_URL, zpath)

def get(u):
    return json.loads(urllib.request.urlopen(u, timeout=60).read())

oa, master, off = {}, {}, 0
while True:
    f = get(MS + urllib.parse.urlencode({'f': 'json', 'where': '1=1', 'outFields': 'code,council,masterpc', 'returnGeometry': 'false',
                                         'resultOffset': off, 'resultRecordCount': 1000, 'orderByFields': 'objectid'})).get('features', [])
    for x in f:
        a = x['attributes']; oa[a['code']] = a['council']; l = master.setdefault(a['council'], [])
        if len(l) < 8: l.append(a['masterpc'])
    if len(f) < 1000:
        break
    off += 1000
names = {}
for code, pcs in master.items():
    for pc in pcs:
        try:
            names[code] = get('https://api.postcodes.io/postcodes/' + urllib.parse.quote(pc))['result']['admin_district']
            break
        except Exception:
            continue

def table(name):
    rows = list(csv.reader(io.TextIOWrapper(zipfile.ZipFile(zpath).open(name), 'utf-8-sig')))
    hdr = next(r for r in rows if r and r[0] == '')
    agg = defaultdict(lambda: [0] * (len(hdr) - 1))
    for r in rows:
        if r and r[0] in oa:
            for i, v in enumerate(r[1:]):
                agg[oa[r[0]]][i] += int(v) if v.strip().isdigit() else 0
    return hdr[1:], agg

h1, acc = table('UV402 - Accommodation type - Households.csv')
h2, ten = table('UV404 - Household tenure - Households.csv')
la = json.load(open(D / 'la-census.json'))
for code, v in acc.items():
    tot = v[0]
    pick = lambda hdr, vals, lab: vals[hdr.index(lab)]
    housing = [[lab, pick(h1, v, col), round(100 * pick(h1, v, col) / tot, 1)] for lab, col in [
        ('Detached', 'Whole house or bungalow: Detached'), ('Semi-detached', 'Whole house or bungalow: Semi-detached'),
        ('Terraced', 'Whole house or bungalow: Terraced (including end-terrace)'), ('Flat, maisonette or apartment', 'Flat, maisonette or apartment: Total')]]
    t = ten[code]
    tenure = [[lab, pick(h2, t, col), round(100 * pick(h2, t, col) / tot, 1)] for lab, col in [
        ('Owned', 'Owned: Total'), ('Social rented', 'Social Rented: Council (LA) or Housing Association/ Registered Social Landlord'), ('Private rented', 'Private rented: Total')]]
    la[names[code]] = {'code': code, 'households': tot, 'housing': housing, 'tenure': tenure,
                       'source': "Scotland's Census 2022 (NRS) UV402/UV404, output areas summed to council"}
json.dump(la, open(D / 'la-census.json', 'w'), ensure_ascii=False, indent=0)
print('scottish councils', len(acc), sorted(names.values())[:5])
