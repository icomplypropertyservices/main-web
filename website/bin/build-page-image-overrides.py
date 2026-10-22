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
# Job hubs catalogued without a service would fall back to building-maintenance photos.
# Route each to the trade its slug names.
JOB_RULES = [
    (r'barrier|came-gard|vehicle-barrier', 'type-keywords-barriers'),
    (r'aov|smoke-vent|smoke-control|smoke-damper', 'aov'),
    (r'fire-alarm|bs-5839|false-alarm|c-tec|gent-|kentec|morley|fire-alarms', 'type-keywords-fire-alarms'),
    (r'^fra|fire-risk|hmo-fire', 'type-keywords-fire-risk-assessments'),
    (r'gas', 'type-keywords-gas-systems'),
    (r'maglock|access-control', 'type-keywords-access-control'),
    (r'hmo|landlord', 'type-keywords-smoke-co-alarms'),
]
import glob
job_slugs = set(json.load(open(f'{D}/nationwide-p0-jobs.json')))
job_slugs |= set(json.load(open(f'{D}/close-q/job-hubs.json')).get('hubs', {}))
for f in glob.glob(f'{D}/job-types-*.json'):
    doc = json.load(open(f))
    items = doc.get('jobs', doc) if isinstance(doc, dict) else doc
    job_slugs |= {x if isinstance(x, str) else x.get('slug', '') for x in items} if isinstance(items, list) else set(items)
job_slugs |= {k for k, r in rows.items() if isinstance(r, dict) and r.get('kind') == 'job'} if isinstance(rows, dict) else set()
for slug in sorted(s for s in job_slugs if s and not s.startswith('_')):
    row = rows.get(slug) if isinstance(rows, dict) and isinstance(rows.get(slug), dict) else {}
    own = row.get('service') or fb.get(slug) or ''
    if own and own not in ('building-maintenance', 'aov') and pool_ok(f'type-jobs-{own}'):
        continue  # already has its own job photos
    svc = ks.get(slug) or fb.get(slug) or ''
    key = ''
    if svc == 'aov':
        key = 'aov'
    elif svc and pool_ok(f'type-keywords-{svc}') and svc != 'building-maintenance':
        key = f'type-keywords-{svc}'
    else:
        for pat, k in JOB_RULES:
            if re.search(pat, slug) and (k == 'aov' or pool_ok(k)):
                key = k
                break
    if key:
        out[f'jobs/{slug}'] = key

MANUAL_JOBS = {
    'bs-5839-maintenance': 'type-keywords-fire-alarms', 'fire-alarms': 'type-keywords-fire-alarms',
    'fra': 'type-keywords-fire-risk-assessments', 'fra-other': 'type-keywords-fire-risk-assessments',
    'hmo': 'type-keywords-smoke-co-alarms', 'hmo-fire-safety': 'type-keywords-fire-risk-assessments',
    'hmo-occupancy': 'type-keywords-smoke-co-alarms', 'landlord-bundle': 'type-keywords-smoke-co-alarms', 'landlord-compliance': 'type-keywords-smoke-co-alarms', 'fire-risk-assessment': 'type-keywords-fire-risk-assessments',
}
# jobs with their own curated page-types/jobs photos keep them
KEEP_OWN_JOB_PHOTOS = {'eicr', 'addressable-fire-alarm-installation', 'aov-service', 'colt-aov-service', 'se-controls-aov-service'}
for slug in KEEP_OWN_JOB_PHOTOS:
    out.pop(f'jobs/{slug}', None)
out.update({'jobs/eicr': 'type-jobs-eicr', 'jobs/addressable-fire-alarm-installation': 'type-jobs-addressable-fire-alarm', 'jobs/aov-service': 'type-jobs-aov-service'})
for slug, key in MANUAL_JOBS.items():
    if key == 'aov' or pool_ok(key):
        out[f'jobs/{slug}'] = key
here = os.path.join(os.path.dirname(__file__), '..', 'pages', 'commercial')
for name in sorted(os.listdir(here)) if os.path.isdir(here) else []:
    slug = name[:-4] if name.endswith('.php') else name
    f = family(slug.replace('commercial-', ''))
    if f and pool_ok(f'type-keywords-{f}'):
        out[f'commercial/{slug}'] = f'type-keywords-{f}'
json.dump(out, open(f'{D}/page-image-pool-overrides.json', 'w'), indent=1, sort_keys=True)
print(len(out), 'overrides')
