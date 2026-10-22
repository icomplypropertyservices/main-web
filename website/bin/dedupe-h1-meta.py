#!/usr/bin/env python3
"""After export: pages that share an H1 (same-name towns such as ashford / ashford-kent) get the place
qualifier from their slug; pages that share a meta description get one built from their own H1.
QM #156 h1_duplicate / meta_duplicate. Only touches duplicates."""
import os, re, sys, collections, html
dist = sys.argv[1] if len(sys.argv) > 1 else 'dist'
H1 = re.compile(r'(<h1\b[^>]*>)(.*?)(</h1>)', re.S | re.I)
MD = re.compile(r'(<meta\s+name="description"\s+content=")([^"]*)(")', re.I)
files = []
for root, _, fs in os.walk(dist):
    for f in fs:
        if f.endswith('.php') and '/assets' not in root:
            files.append(os.path.join(root, f))
h1g = collections.defaultdict(list); mdg = collections.defaultdict(list)
for fp in files:
    s = open(fp, encoding='utf-8', errors='replace').read()
    m = H1.search(s)
    if m:
        h1g[re.sub(r'<[^>]+>|\s+', ' ', m.group(2)).strip().lower()].append(fp)
    m = MD.search(s)
    if m and m.group(2).strip():
        mdg[m.group(2).strip()].append(fp)
title = lambda t: ' '.join(w.capitalize() for w in t.split('-'))
fix_h1 = {}
for k, fps in h1g.items():
    if len(fps) < 2 or not k:
        continue
    jobs = [fp for fp in fps if '/pages/jobs/' in fp]
    if jobs and len(jobs) < len(fps):
        for fp in jobs: fix_h1[fp] = ': job scope & booking'   # job lane vs keyword guide of the same name
        fps = [fp for fp in fps if fp not in jobs]
        if len(fps) < 2: continue
    slugs = {fp: os.path.basename(fp)[:-4] for fp in fps}
    base = min(slugs.values(), key=len)
    for fp, sl in slugs.items():
        if sl == base:
            continue
        suf = sl[len(base) + 1:] if sl.startswith(base + '-') else sl
        fix_h1[fp] = ' built-up area' if suf == base else ', ' + title(suf)
def mk_meta(h1):
    h1 = html.unescape(re.sub(r'<[^>]+>|\s+', ' ', h1)).strip()
    body = f'{h1} from our Stockport team. Same/next-day visits, certificates emailed and a free written quote. Call 07517 806082.'
    for extra in ('', ' Call today.'):
        pass
    if len(body) > 155:
        body = f'{h1}. Same/next-day visits and a free written quote. Call 07517 806082.'
    if len(body) > 155:
        body = body[:152].rsplit(' ', 1)[0] + '…'
    while len(body) < 140:
        body = body[:-1] + ' today.' if body.endswith('.') else body + '.'
    return body[:155]
fix_md = set(fp for fps in mdg.values() if len(fps) > 1 for fp in fps)
n1 = n2 = 0
for fp in set(fix_h1) | fix_md:
    s = open(fp, encoding='utf-8', errors='replace').read()
    if fp in fix_h1:
        q = fix_h1[fp]
        s = H1.sub(lambda m: m.group(1) + m.group(2).rstrip() + html.escape(q) + m.group(3), s, count=1); n1 += 1
    if fp in fix_md:
        m = H1.search(s)
        if m:
            meta = html.escape(mk_meta(m.group(2)), quote=True)
            s = MD.sub(lambda mm: mm.group(1) + meta + mm.group(3), s, count=1); n2 += 1
    open(fp, 'w', encoding='utf-8').write(s)
print(f'dedupe-h1-meta: {len(files)} pages, {n1} H1s qualified, {n2} metas rewritten')
