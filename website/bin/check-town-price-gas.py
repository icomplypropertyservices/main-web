#!/usr/bin/env python3
"""G9 + G12 gate on the rendered x-town sample (render-town-sample.mjs output).

- G9: a page that shows "from £160" (EICR list price) must not also say "price on application".
- G12: the only gas wording is "carried out by Gas Safe registered engineers".
  No page may say "iComply is (not) Gas Safe registered", and every "Gas Safe registered"
  must read "... by Gas Safe registered engineers".

usage: python3 website/bin/check-town-price-gas.py /tmp/town-sample
"""
import html
import pathlib
import re
import sys

root = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else "/tmp/town-sample")
files = sorted(p for p in root.rglob("*.html"))
if not files:
    print(f"check-town-price-gas: no HTML under {root}")
    sys.exit(1)

fail = 0
priced = gas_pages = 0
for f in files:
    raw = f.read_text(encoding="utf-8", errors="replace")
    text = html.unescape(re.sub(r"<[^>]+>", " ", raw))
    text = re.sub(r"\s+", " ", text)
    if re.search(r"from £160\b", text):
        priced += 1
        if re.search(r"price on application", text, re.I):
            fail += 1
            print(f"FAIL POA on a from-£160 page {f.name}")
    if re.search(r"iComply is (not )?Gas Safe registered", text, re.I):
        fail += 1
        print(f"FAIL company Gas Safe line {f.name}")
    for m in re.finditer(r"Gas Safe registered", text):
        gas_pages += 1
        before = text[max(0, m.start() - 4):m.start()]
        after = text[m.end():m.end() + 10]
        if not before.endswith("by ") or not after.startswith(" engineer"):
            fail += 1
            print(f"FAIL gas wording {f.name}: ...{text[max(0, m.start() - 40):m.end() + 20]}...")
            break

if fail:
    print(f"check-town-price-gas: {fail} failure(s)")
    sys.exit(1)
print(f"check-town-price-gas: OK ({len(files)} pages; {priced} from-£160 pages with 0 POA; gas wording OK)")
