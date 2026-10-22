#!/usr/bin/env python3
"""Fail if any built page shows an email address other than icomplypropertyservices@gmail.com."""
import os, re, sys, collections
dist = sys.argv[1] if len(sys.argv) > 1 else 'dist'
OK = 'icomplypropertyservices@gmail.com'
RX = re.compile(r'[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}')
IGN = re.compile(r'\.(png|jpe?g|webp|svg|gif|js|css)$|@\d+x\.|sentry|w3\.org|schema\.org', re.I)
bad = collections.Counter(); ex = {}
pages = 0
for root, _, fs in os.walk(dist):
    for f in fs:
        if not f.endswith(('.php', '.html', '.htm')):
            continue
        pages += 1
        p = os.path.join(root, f)
        for e in set(RX.findall(open(p, encoding='utf-8', errors='replace').read())):
            if e.lower() != OK and not IGN.search(e):
                bad[e.lower()] += 1; ex.setdefault(e.lower(), p)
for e, n in bad.most_common():
    print(f'FAIL {e} on {n} pages, e.g. {ex[e]}')
print(f'check-only-gmail: {"FAIL" if bad else "OK"} pages={pages} other_addresses={len(bad)}')
sys.exit(1 if bad else 0)
