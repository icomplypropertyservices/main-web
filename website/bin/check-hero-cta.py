#!/usr/bin/env python3
"""One rendered page per P0 family must carry the shared under-H1 CTA.

Also checks the three edge renderers insert heroCta immediately after the H1,
and that /shop/fire/ no longer shows 07471 or info@.
"""
from __future__ import annotations

import json
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
INDEX = ROOT / "website/data/seo-lock/p0-index.json"
SHOP = ROOT / "website/shop/fire/index.html"
RENDERERS = [
    ROOT / "netlify/edge-functions/town-matrix.js",
    ROOT / "netlify/lib/variant-matrix.js",
    ROOT / "netlify/lib/nationwide-p0.js",
]


def fail(message: str) -> None:
    print(f"[FAIL] {message}")
    sys.exit(1)


def main() -> None:
    rows = json.loads(INDEX.read_text())["rows"]
    sample = {}
    for slug, meta in rows.items():
        family = meta.get("family") or "general-building"
        sample.setdefault(family, slug)
    shop = SHOP.read_text(errors="replace")
    if "07471" in shop or "info@" in shop:
        fail("shop/fire still contains 07471 or info@")
    if "</h1><div class=\"hero-cta\" data-cta=\"hero\">" not in shop:
        fail("shop/fire CTA is not directly after the H1")
    if "07517 806082" not in shop or "tel:07517806082" not in shop:
        fail("shop/fire is missing 07517 806082")
    for path in RENDERERS:
        text = path.read_text()
        if "heroCta(" not in text:
            fail(f"{path.name} does not call heroCta")
        if "</h1>${heroCta(" not in text:
            fail(f"{path.name} does not place heroCta directly after the H1")
    script = r"""
import { renderTradesHub } from "./netlify/lib/trades-lock.js";
const samples = JSON.parse(process.argv[1]);
let fail = 0;
for (const [family, slug] of Object.entries(samples)) {
  const html = renderTradesHub(slug) || "";
  const ok = html.includes('data-cta="hero"')
    && html.includes("</h1><div class=\"hero-cta\" data-cta=\"hero\">")
    && html.includes("tel:07517806082")
    && html.includes("https://wa.me/447517806082")
    && !html.includes("07471")
    && !html.includes("info@")
    && !/noindex/i.test(html);
  if (!ok) {
    console.error("FAIL family", family, slug);
    fail++;
  } else {
    console.log("PASS", family, slug);
  }
}
process.exit(fail ? 1 : 0);
"""
    proc = subprocess.run(
        ["node", "--input-type=module", "-e", script, json.dumps(sample)],
        cwd=ROOT,
        text=True,
        capture_output=True,
    )
    sys.stdout.write(proc.stdout)
    sys.stderr.write(proc.stderr)
    if proc.returncode != 0:
        fail("one or more family hubs failed the hero CTA check")
    print(f"[PASS] hero CTA on {len(sample)} families and shop/fire")


if __name__ == "__main__":
    main()
