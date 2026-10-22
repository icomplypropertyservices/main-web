#!/usr/bin/env python3
"""Fail if any built file shows an email address other than icomplypropertyservices@gmail.com.

Covers pages (.php/.html/.htm), JSON-LD "email" properties, mailto: links,
vCards (.vcf), text/JSON/XML assets and PDF text (via pdftotext)."""
import os, re, sys, json, collections, subprocess, shutil
from urllib.parse import unquote
dist = sys.argv[1] if len(sys.argv) > 1 else 'dist'
OK = 'icomplypropertyservices@gmail.com'
RX = re.compile(r'[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}')
IGN = re.compile(r'\.(png|jpe?g|webp|svg|gif|js|css)$|@\d+x\.|sentry|w3\.org|schema\.org', re.I)
LD = re.compile(r'<script[^>]*application/ld\+json[^>]*>(.*?)</script>', re.S | re.I)
MAILTO = re.compile(r'mailto:([^"\'?>\s]*)', re.I)
TEXT = ('.php', '.html', '.htm', '.vcf', '.txt', '.json', '.xml', '.webmanifest', '.ics')
bad = collections.Counter(); ex = {}
n = collections.Counter()
have_pdftotext = shutil.which('pdftotext')

def flag(e, p):
    e = unquote(e).strip().lower()
    if e and e != OK and not IGN.search(e):
        bad[e] += 1; ex.setdefault(e, p)

def ld_emails(o):
    if isinstance(o, dict):
        for k, v in o.items():
            if k.lower() == 'email' and isinstance(v, str):
                yield v.replace('mailto:', '')
            else:
                yield from ld_emails(v)
    elif isinstance(o, list):
        for v in o:
            yield from ld_emails(v)

for root, _, fs in os.walk(dist):
    for f in fs:
        p = os.path.join(root, f); low = f.lower()
        if low.endswith('.pdf'):
            n['pdf'] += 1
            if not have_pdftotext:
                print('FAIL pdftotext not installed; cannot check', p); bad['<pdf-unchecked>'] += 1; continue
            txt = subprocess.run(['pdftotext', '-q', p, '-'], capture_output=True, text=True, errors='replace').stdout
        elif low.endswith(TEXT):
            n['vcf' if low.endswith('.vcf') else 'text'] += 1
            txt = open(p, encoding='utf-8', errors='replace').read()
        else:
            continue
        for e in set(RX.findall(txt)):
            flag(e, p)
        for e in set(MAILTO.findall(txt)):
            flag(e, p + ' (mailto)')
        for blob in LD.findall(txt):
            try:
                for e in ld_emails(json.loads(blob)):
                    flag(e, p + ' (JSON-LD email)')
            except Exception:
                for e in re.findall(r'"email"\s*:\s*"([^"]*)"', blob):
                    flag(e, p + ' (JSON-LD email)')
for e, c in bad.most_common():
    print(f'FAIL {e} in {c} files, e.g. {ex.get(e, "")}')
print(f'check-only-gmail: {"FAIL" if bad else "OK"} text={n["text"]} vcf={n["vcf"]} pdf={n["pdf"]} other_addresses={len(bad)}')
sys.exit(1 if bad else 0)
