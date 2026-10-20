#!/usr/bin/env python3
"""Barrier town copy: no builder/meta wording, and the housing-mix line follows the
census lead accommodation type. Usage: check-barriers-copy.py [dist]"""
import json, pathlib, re, sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
BANNED = re.compile(r"does not invent|do not invent|inventing|this readout|this page|do not assume|guessed|census label", re.I)
LINES = {
    "flat": "Flats are the largest accommodation group",
    "semi": "Semi-detached houses lead the stock",
    "detached": "Detached houses lead the housing mix",
    "terraced": "Terraced housing leads the stock",
}
fails = []
rows = json.loads((ROOT / "data/barriers-places.json").read_text())
for r in rows:
    text = r.get("reading", "")
    if BANNED.search(text):
        fails.append(f"{r['slug']}: builder wording: {BANNED.search(text).group(0)}")
    houses = r.get("housing") or []
    if houses:
        lead = houses[0]["label"].lower()
        key = ("flat" if re.search(r"flat|maisonette|apartment", lead) else "semi" if "semi-detached" in lead
               else "detached" if "detached" in lead else "terraced" if "terraced" in lead else None)
        for k, line in LINES.items():
            present = line in text
            if k == key and not present:
                fails.append(f"{r['slug']}: lead housing '{lead}' but no '{line}'")
            if k != key and present:
                fails.append(f"{r['slug']}: says '{line}' but lead housing is '{lead}'")
pages = 0
if len(sys.argv) > 1:
    d = pathlib.Path(sys.argv[1]) / "pages/barriers"
    for f in sorted(d.glob("*.php"))[:400] if d.is_dir() else []:
        pages += 1
        body = re.sub(r"<script.*?</script>|<[^>]+>", " ", f.read_text(errors="ignore"), flags=re.S)
        m = re.search(r"does not invent|do not invent|this readout|do not assume", body, re.I)
        if m:
            fails.append(f"{f.name}: rendered builder wording '{m.group(0)}'")
for x in fails[:20]:
    print("FAIL", x)
print(f"check-barriers-copy: {'FAIL' if fails else 'OK'} rows={len(rows)} pages={pages} fails={len(fails)}")
sys.exit(1 if fails else 0)
