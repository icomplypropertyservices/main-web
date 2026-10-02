#!/usr/bin/env python3
"""Build the Legionella / water hygiene job lane (130 jobs).

Writes website/data/job-types-legionella.json and thin keyword stubs.
Does not overwrite an existing keyword stub (the restored hubs stay).
Stubs under website/pages/keywords/*.php are gitignored. The router
serves /pages/keywords/{slug} from this catalogue, so a clean checkout
does not need the stub files.

Usage (repo root): python3 website/bin/build-legionella-job-pages.py
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(Path(__file__).resolve().parent))

from legionella_job_facts import FACTS, GROUPS  # noqa: E402

EXPECTED = 130
SERVICE = "legionella-risk-assessment"
BANNED = (
    "ukas",
    "£",
    "fixed-price",
    "fixed price",
    "guaranteed legionella",
    "disease-free",
    "100%",
    "niceic",
    "we are accredited",
    "licensed removal",
)


def expand(row: dict) -> dict:
    name = row["name"]
    who = row["who"]
    does = row["does"].rstrip(".")
    caveat = row["caveat"].rstrip(".")
    note = row["note"].rstrip(".")
    sees = row["sees"]
    if len(sees) != 3:
        raise SystemExit(f"{row['slug']}: expected 3 see-points")
    see_line = f"{sees[0]}; {sees[1]}; and {sees[2]}"
    intro = (
        f"{name} is scoped water-hygiene work for {who}. {does}. "
        f"The quote is price on application once access and the water system are clear. "
        f"We do not publish a fee for {name}."
    )
    body = " ".join(
        [
            f"{does}.",
            f"On this visit we pay attention to {see_line}.",
            f"{caveat}.",
            f"{note}.",
            "HSE Approved Code of Practice L8 and HSG274 are the usual references. "
            "This page is not legal advice and not a medical opinion.",
            "A temperature, a flush or a sample is evidence at a point in time. "
            "It does not mean the building stays free of risk.",
            "Coverage is Greater Manchester and the wider North West from Stockport. "
            "Tell us the postcode, who uses the building, and whether hot or cold water is stored.",
            "Sampling, tank cleaning and any chemical step are separate lines when they are justified. "
            "We do not invent a laboratory badge or a per-sample price on this page.",
        ]
    )
    focus = [
        see_line[0].upper() + see_line[1:],
        caveat,
        "Written note for the dutyholder file — not a catalogue certificate",
        "Price on application after scope — no published fee",
    ]
    faqs = []
    for question, answer in row["faqs"]:
        faqs.append([question, answer])
    if len(faqs) != 2:
        raise SystemExit(f"{row['slug']}: expected 2 FAQs")
    meta = (
        f"{name} for {who}. North West water hygiene from Stockport. "
        f"Price on application. No catalogue fee."
    )
    return {
        "slug": row["slug"],
        "name": name,
        "service": SERVICE,
        "family": "legionella",
        "group": row["group"],
        "related": row["related"],
        "seo_title": f"{name} | Legionella and water hygiene",
        "h1": name,
        "meta_desc": meta,
        "intro": intro,
        "body": body,
        "faq": faqs,
        "focus_points": focus,
        "seo_keywords": f"{name}, legionella, water hygiene, North West, Stockport, POA",
    }


def main() -> int:
    if len(FACTS) != EXPECTED:
        print(f"FACTS has {len(FACTS)}, expected {EXPECTED}", file=sys.stderr)
        return 1
    slugs = [row["slug"] for row in FACTS]
    if len(set(slugs)) != EXPECTED:
        print("duplicate slug in FACTS", file=sys.stderr)
        return 1
    group_keys = {g["key"] for g in GROUPS}
    jobs = []
    seen_does = {}
    seen_note = {}
    seen_q = {}
    for row in FACTS:
        if row["group"] not in group_keys:
            print(f"unknown group {row['group']} on {row['slug']}", file=sys.stderr)
            return 1
        if row["related"] not in slugs:
            print(f"related missing for {row['slug']}: {row['related']}", file=sys.stderr)
            return 1
        does_key = row["does"].strip().lower()
        note_key = row["note"].strip().lower()
        if does_key in seen_does:
            print(f"duplicate does: {row['slug']} == {seen_does[does_key]}", file=sys.stderr)
            return 1
        if note_key in seen_note:
            print(f"duplicate note: {row['slug']} == {seen_note[note_key]}", file=sys.stderr)
            return 1
        seen_does[does_key] = row["slug"]
        seen_note[note_key] = row["slug"]
        for question, _answer in row["faqs"]:
            qk = question.strip().lower()
            if qk in seen_q:
                print(f"duplicate FAQ: {row['slug']} == {seen_q[qk]}", file=sys.stderr)
                return 1
            seen_q[qk] = row["slug"]
        job = expand(row)
        blob = json.dumps(job).lower()
        for banned in BANNED:
            if banned in blob:
                print(f"banned {banned!r} in {row['slug']}", file=sys.stderr)
                return 1
        if "price on application" not in job["intro"].lower():
            print(f"missing POA intro {row['slug']}", file=sys.stderr)
            return 1
        jobs.append(job)

    payload = {
        "count": EXPECTED,
        "lane": "legionella",
        "service": SERVICE,
        "pricing": "POA",
        "groups": GROUPS,
        "jobs": jobs,
    }
    data_path = ROOT / "website" / "data" / "job-types-legionella.json"
    data_path.write_text(
        json.dumps(payload, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )
    stub_dir = ROOT / "website" / "pages" / "keywords"
    written = 0
    skipped = 0
    for job in jobs:
        slug = job["slug"]
        dest = stub_dir / f"{slug}.php"
        if dest.exists():
            skipped += 1
            continue
        dest.write_text(
            "<?php\n"
            "/** Legionella / water hygiene job — python3 website/bin/build-legionella-job-pages.py */\n"
            "require_once __DIR__ . '/../../includes/render.php';\n"
            f"renderKeywordPage({slug!r});\n",
            encoding="utf-8",
        )
        written += 1
    print(f"Wrote {EXPECTED} jobs → {data_path}")
    print(f"stubs written={written} skipped_existing={skipped}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
