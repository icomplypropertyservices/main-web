#!/usr/bin/env python3
"""After static export: an internal keyword/service x town link whose target is not an exported page,
a sitemap URL or a _redirects source is pointed at that town's area page (or the family hub), else plain text (QM rule broken_internal_link). Never removes pages."""
import os, re, sys, glob
dist = sys.argv[1] if len(sys.argv) > 1 else 'dist'
known = set()
for root, _, files in os.walk(dist):
    for f in files:
        if f.endswith(('.php', '.html')):
            p = '/' + os.path.relpath(os.path.join(root, f), dist).replace(os.sep, '/')
            p = re.sub(r'(/index)?\.(php|html)$', '', p) or '/'
            known.add(p.rstrip('/') or '/')
    known.add('/' + os.path.relpath(root, dist).replace(os.sep, '/').strip('./'))
for sm in glob.glob(f'{dist}/sitemap*.xml') + glob.glob(f'{dist}/sitemaps/*.xml'):
    for u in re.findall(r'<loc>https?://[^/<]+([^<]*)</loc>', open(sm, errors='ignore').read()):
        known.add(u.rstrip('/') or '/')
parents = {k.rsplit('/', 1)[0] for k in known if k.count('/') >= 3}
rd = f'{dist}/_redirects'
if os.path.exists(rd):
    for l in open(rd, errors='ignore'):
        p = l.split()
        if p and p[0].startswith('/'):
            known.add(p[0].rstrip('/') or '/')
A = re.compile(r'<a\b[^>]*\bhref="(?:https?://(?:www\.)?icomplypropertyservices\.co\.uk)?(/pages/[^"#?]*)"[^>]*>(.*?)</a>', re.S | re.I)
pages = changed = links = 0
for root, _, files in os.walk(dist):
    for f in files:
        if not f.endswith(('.php', '.html')):
            continue
        fp = os.path.join(root, f)
        s = open(fp, encoding='utf-8', errors='replace').read()
        n = [0]
        def fix(m):
            k = re.sub(r'\.php$', '', m.group(1)).rstrip('/') or '/'
            # Only a keyword/service x town link whose family is published but whose town is not
            # (not exported, not in the sitemap, not a _redirects source). Edge-only hubs stay linked.
            if k in known or '*' in k or k.count('/') < 3 or k.rsplit('/', 1)[0] not in parents:
                return m.group(0)
            n[0] += 1
            town = k.rsplit('/', 1)[1]
            for alt in ('/pages/areas/' + town, k.rsplit('/', 1)[0]):
                if alt in known:
                    return m.group(0).replace(m.group(1), alt, 1)
            return m.group(2)
        t = A.sub(fix, s)
        pages += 1
        if n[0]:
            changed += 1; links += n[0]
            open(fp, 'w', encoding='utf-8').write(t)
print(f'unlink-dead-internal: {pages} pages scanned, {links} dead links unlinked on {changed} pages, {len(known)} known targets')
