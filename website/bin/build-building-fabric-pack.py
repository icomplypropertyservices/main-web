#!/usr/bin/env python3
"""Build the BUILDING fabric / repairs job-pack overlay.

Reads unique briefs from building_fabric_copy.py and the 1,753-job master CSV.
Writes website/data/job-packs/building-fabric.json.

Packs cannot add or remove master slugs. This lane only overlays copy.
"""
from __future__ import annotations

import csv
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MASTER = ROOT / "data" / "job-types-master.csv"
OUT = ROOT / "data" / "job-packs" / "building-fabric.json"

sys.path.insert(0, str(ROOT / "bin"))
from building_fabric_copy import BRIEFS  # noqa: E402

LANE_SERVICES = {
    "brickwork",
    "roofing",
    "rendering",
    "damp-proofing",
    "plastering",
    "dry-lining",
    "insulation",
    "windows-doors",
    "building-maintenance",
    "building-surveys",
    "joinery",
    "carpentry",
}

POUND = re.compile(r"£\s*\d")
BANNED = ("sits under our", "not a generic package", "typical north west stock includes")

SERVICE_LABEL = {
    "brickwork": "Brickwork",
    "roofing": "Roofing",
    "rendering": "Rendering",
    "damp-proofing": "Damp proofing",
    "plastering": "Plastering",
    "dry-lining": "Dry lining",
    "insulation": "Insulation",
    "windows-doors": "Windows and doors",
    "building-maintenance": "Maintenance",
    "building-surveys": "Surveys",
    "joinery": "Joinery",
    "carpentry": "Carpentry",
}


def load_master() -> list[dict]:
    with MASTER.open(newline="", encoding="utf-8") as fh:
        return list(csv.DictReader(fh))


def meta_desc(name: str, intro: str) -> str:
    first = intro.split(". ")[0].strip().rstrip(".")
    candidates = [
        f"{name} in the North West. {first}. POA from Stockport.",
        f"{first}. POA from Stockport.",
        f"{first}.",
    ]
    clause = first
    while ", " in clause:
        clause = clause.rsplit(", ", 1)[0].strip()
        candidates.append(f"{clause}. POA from Stockport.")
        candidates.append(f"{name}. {clause}. POA from Stockport.")
    if " — " in first:
        head = first.split(" — ")[0].strip()
        candidates.append(f"{head}. POA from Stockport.")
    candidates.append(f"{name} across the North West. Written POA from Stockport after a look.")
    for text in candidates:
        if 70 <= len(text) <= 160 and text.endswith("."):
            return text
    fallback = f"{name}. Written POA from Stockport after we have seen the building."
    return fallback if len(fallback) <= 160 else fallback[:157].rsplit(" ", 1)[0] + "."


def seo_title(name: str, service: str) -> str:
    label = SERVICE_LABEL.get(service, "Fabric repairs")
    titled = f"{name} | {label} | iComply"
    if len(titled) <= 68:
        return titled
    short = f"{name} | iComply"
    if len(short) <= 68:
        return short
    return short[:65].rsplit(" ", 1)[0].rstrip("| ") + " | iComply"


def compose(row: dict, brief: dict) -> dict:
    name = row["name"].strip()
    slug = row["slug"].strip()
    service = row["service"].strip()
    intro = brief["intro"].strip()
    body = brief["body"].strip()
    if "iComply" not in intro and "iComply" not in body:
        body = body.rstrip() + " iComply quotes it from Stockport."
    faqs = [[q, a] for q, a in brief["faqs"]]
    faqs.append([
        f"How is {name} priced?",
        (
            f"Price on application after we know {brief['driver']}. "
            "iComply does not publish a catalogue £ figure for this job."
        ),
    ])
    focus = list(brief["focus"]) + ["Written POA after a look — no catalogue £ figure"]
    searches = list(brief["searches"]) + [f"{name} Stockport"]
    return {
        "slug": slug,
        "name": name,
        "service": service,
        "lane": "building-fabric",
        "seo_title": seo_title(name, service),
        "h1": name,
        "intro": intro,
        "body": body,
        "meta_desc": meta_desc(name, intro),
        "seo_keywords": f"{name}, {service}, fabric repairs, North West, Stockport, iComply",
        "focus_points": focus,
        "faq": faqs,
        "secondaries": searches,
    }


def shingles(text: str, n: int = 6) -> set[str]:
    words = re.findall(r"[a-z0-9']+", text.lower())
    return {" ".join(words[i:i + n]) for i in range(len(words) - n + 1)}


def validate(jobs: list[dict]) -> list[str]:
    errors: list[str] = []
    intros: dict[str, str] = {}
    bodies: dict[str, str] = {}
    titles: dict[str, str] = {}
    h1s: dict[str, str] = {}
    answers: dict[str, str] = {}
    intro_sh: dict[str, set[str]] = {}
    for job in jobs:
        slug = job["slug"]
        blob = " ".join([
            job["intro"], job["body"], job["meta_desc"],
            " ".join(a for _, a in job["faq"]),
        ])
        if POUND.search(blob):
            errors.append(f"{slug}: invented £")
        low = blob.lower()
        for phrase in BANNED:
            if phrase in low:
                errors.append(f"{slug}: banned phrase {phrase}")
        if "POA" not in blob and "price on application" not in low:
            errors.append(f"{slug}: no POA")
        if "icomply" not in low:
            errors.append(f"{slug}: no brand")
        if len(job["intro"]) < 100:
            errors.append(f"{slug}: intro short ({len(job['intro'])})")
        if len(job["body"]) < 240:
            errors.append(f"{slug}: body short ({len(job['body'])})")
        if not 70 <= len(job["meta_desc"]) <= 165:
            errors.append(f"{slug}: meta length {len(job['meta_desc'])}")
        if re.search(r"\b(a|an|the|we|to|of|and|or|while|so)\.$", job["meta_desc"], re.I):
            errors.append(f"{slug}: meta ends mid-thought ({job['meta_desc'][-40:]})")
        if len(job["seo_title"]) > 70:
            errors.append(f"{slug}: title length {len(job['seo_title'])}")
        if job["name"].lower() not in job["h1"].lower():
            errors.append(f"{slug}: h1 missing name")
        if len(job["faq"]) < 3:
            errors.append(f"{slug}: faq count")
        if len(job["focus_points"]) < 4:
            errors.append(f"{slug}: focus count")
        for field, store in (
            ("intro", intros), ("body", bodies), ("seo_title", titles), ("h1", h1s),
        ):
            value = job[field]
            if value in store:
                errors.append(f"{slug}: duplicate {field} with {store[value]}")
            else:
                store[value] = slug
        for q, a in job["faq"]:
            if len(a) < 40:
                errors.append(f"{slug}: short answer {q}")
            if a in answers:
                errors.append(f"{slug}: duplicate answer with {answers[a]}")
            else:
                answers[a] = slug
        intro_sh[slug] = shingles(job["intro"])
    slugs = list(intro_sh)
    for i, left in enumerate(slugs):
        a = intro_sh[left]
        if not a:
            continue
        for right in slugs[i + 1:]:
            b = intro_sh[right]
            if not b:
                continue
            overlap = len(a & b) / min(len(a), len(b))
            if overlap >= 0.45:
                errors.append(f"intro overlap {overlap:.2f}: {left} ~ {right}")
                if len(errors) > 40:
                    return errors
    return errors


def main() -> int:
    rows = load_master()
    lane = [r for r in rows if r["service"] in LANE_SERVICES]
    lane_slugs = {r["slug"] for r in lane}
    brief_slugs = set(BRIEFS)
    missing = sorted(lane_slugs - brief_slugs)
    extra = sorted(brief_slugs - lane_slugs)
    if missing or extra:
        print(f"FAIL briefs missing={len(missing)} extra={len(extra)}")
        for slug in missing[:12]:
            print("  missing", slug)
        for slug in extra[:12]:
            print("  extra", slug)
        return 1
    jobs = [compose(r, BRIEFS[r["slug"]]) for r in lane]
    jobs.sort(key=lambda j: j["slug"])
    errors = validate(jobs)
    if errors:
        print(f"FAIL quality {len(errors)}")
        for err in errors[:30]:
            print(" ", err)
        return 1
    payload = {
        "lane": "building-fabric",
        "note": "Quality overlay for BUILDING fabric and repairs. Does not change the 1,753 master count. No catalogue £ prices.",
        "count": len(jobs),
        "services": sorted(LANE_SERVICES),
        "jobs": jobs,
    }
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"wrote {OUT} jobs={len(jobs)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
