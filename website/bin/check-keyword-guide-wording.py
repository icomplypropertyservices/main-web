#!/usr/bin/env python3
"""Whole-build ban on 'keyword guide(s)' (any case) in customer-visible text and metas.
Covers every exported page, including the keywords hub pages (no skip list).
Usage: check-keyword-guide-wording.py dist"""
import pathlib, re, sys

dist = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else "dist")
BAN = re.compile(r"\bkeyword guides?\b", re.I)
fails, pages = [], 0
for f in dist.rglob("*"):
    if f.suffix not in (".php", ".html") or not f.is_file():
        continue
    rel = str(f.relative_to(dist))
    pages += 1
    raw = f.read_text(errors="ignore")
    text = re.sub(r"<script.*?</script>|<style.*?</style>|<!--.*?-->", " ", raw, flags=re.S)
    meta = " ".join(re.findall(r'<(?:meta|img)[^>]+(?:content|alt)="([^"]*)"', raw))
    text = re.sub(r"<[^>]+>", " ", text) + " " + meta
    m = BAN.search(text)
    if m:
        fails.append(f"{rel}: '{' '.join(text[max(0, m.start()-50):m.end()+30].split())}'")
for x in fails[:20]:
    print("FAIL", x)
print(f"check-keyword-guide-wording: {'FAIL' if fails else 'OK'} pages={pages} fails={len(fails)}")
sys.exit(1 if fails or pages == 0 else 0)
