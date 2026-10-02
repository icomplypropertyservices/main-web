#!/usr/bin/env python3
"""Build website/data/barriers-towns.json for non-production barrier pages.

Sources (not committed; pass paths if they are not in /tmp):
  - ONS Census 2021 built-up areas, England (excluding London) and Wales
    Table 1c / 1d usual resident population
  - ONS Census 2021 usual residents by local authority (London boroughs + London region)
  - Scottish Government urban-rural classification 2020 settlement lookup
    (NRS mid-2020 settlement populations)

Rule: UK mainland places with population strictly greater than 10,000.
Mainland means the island of Great Britain. Northern Ireland, the Isle of Man,
the Channel Islands, and offshore-island settlements are excluded.
Greater London is not split into built-up areas, so each London borough over
10,000 is included, plus one Greater London overview page.
"""
from __future__ import annotations

import argparse
import csv
import json
import re
from collections import Counter
from pathlib import Path

import openpyxl

ROOT = Path(__file__).resolve().parents[1]
AREAS_PATH = ROOT / "data" / "areas.json"
OUT_PATH = ROOT / "data" / "barriers-towns.json"

# Exact BUA / settlement names that sit off the island of Great Britain.
ISLAND_EXCLUDE = {
    "Newport (Isle of Wight)": "Isle of Wight, not UK mainland",
    "Cowes": "Isle of Wight, not UK mainland",
    "Ryde": "Isle of Wight, not UK mainland",
    "Sandown": "Isle of Wight, not UK mainland",
    "Sheerness": "Isle of Sheppey, not UK mainland",
    "Minster (Swale)": "Isle of Sheppey, not UK mainland",
    "Canvey Island": "Canvey Island, not UK mainland",
    "South Hayling": "Hayling Island, not UK mainland",
    "Holyhead": "Anglesey / Holy Island, not UK mainland",
}

# Display name, and the official settlement label when it differs.
SCOTLAND_DISPLAY = {
    "Greater Glasgow": "Glasgow",
    "Aberdeen, Milltimber, and Peterculter": "Aberdeen",
    "Coatbridge, Aidrie, Chapelhall and Bargeddie": "Coatbridge, Airdrie, Chapelhall and Bargeddie",
}


def split_place(name: str) -> tuple[str, str | None]:
    match = re.fullmatch(r"(.+?)\s+\((.+)\)", name)
    if not match:
        return name, None
    return match.group(1).strip(), match.group(2).strip()


def duplicate_label(place: str, district: str | None, region: str) -> str:
    if not district or district.lower().strip(".") == place.lower():
        return f"{place}, {region}"
    return f"{place}, {district}"


def slugify(name: str) -> str:
    s = name.lower().strip()
    s = re.sub(r"[^a-z0-9]+", "-", s)
    return s.strip("-")


def size_class(pop: int) -> str:
    if pop >= 200_000:
        return "major"
    if pop >= 75_000:
        return "large"
    if pop >= 20_000:
        return "medium"
    return "small"


def parse_int(value) -> int | None:
    if isinstance(value, bool):
        return None
    if isinstance(value, (int, float)):
        return int(value)
    if isinstance(value, str):
        cleaned = value.replace(",", "").strip()
        if cleaned.isdigit():
            return int(cleaned)
    return None


def load_bua(path: Path, sheet: str) -> list[dict]:
    wb = openpyxl.load_workbook(path, read_only=True, data_only=True)
    ws = wb[sheet]
    rows = []
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i < 3 or not row or row[5] is None:
            continue
        pop = parse_int(row[7])
        if pop is None:
            continue
        rows.append(
            {
                "nation": str(row[1]).strip(),
                "region": str(row[3]).strip(),
                "code": str(row[4]).strip(),
                "name": str(row[5]).strip(),
                "population": pop,
            }
        )
    return rows


def load_london(path: Path) -> tuple[dict | None, list[dict]]:
    with path.open(newline="", encoding="latin-1") as handle:
        rows = list(csv.reader(handle))
    header_at = next(i for i, row in enumerate(rows) if row and row[0] == "Area code")
    london = None
    boroughs = []
    for row in rows[header_at + 1 :]:
        if len(row) < 5:
            continue
        code, name, area_type, _pop2011, pop2021 = row[:5]
        pop = parse_int(pop2021)
        if pop is None:
            continue
        if code == "E12000007":
            london = {
                "name": "London",
                "settlement_name": "Greater London",
                "nation": "England",
                "region": "London",
                "code": code,
                "population": pop,
                "population_year": 2021,
                "population_basis": "region",
                "population_source": "ONS Census 2021 usual residents, London region",
            }
        elif code.startswith("E090000") and area_type == "Local Authority":
            boroughs.append(
                {
                    "name": name.strip(),
                    "settlement_name": name.strip(),
                    "nation": "England",
                    "region": "London",
                    "code": code,
                    "population": pop,
                    "population_year": 2021,
                    "population_basis": "local-authority",
                    "population_source": "ONS Census 2021 usual residents, London borough",
                }
            )
    return london, boroughs


def load_scotland(path: Path) -> list[dict]:
    with path.open(newline="", encoding="latin-1") as handle:
        rows = list(csv.reader(handle))
    header = rows[0]
    if header[0] != "SETT_CODE":
        raise SystemExit(f"Unexpected Scotland header: {header}")
    out = []
    for row in rows[1:]:
        if len(row) < 3:
            continue
        pop = parse_int(row[2])
        if pop is None:
            continue
        official = row[1].strip()
        out.append(
            {
                "name": SCOTLAND_DISPLAY.get(official, official),
                "settlement_name": official,
                "nation": "Scotland",
                "region": "Scotland",
                "code": row[0].strip(),
                "population": pop,
                "population_year": 2020,
                "population_basis": "settlement",
                "population_source": "NRS mid-2020 settlement estimate (Scottish Government urban-rural lookup)",
            }
        )
    return out


def bua_record(row: dict) -> dict:
    return {
        "name": row["name"],
        "settlement_name": row["name"],
        "nation": row["nation"],
        "region": row["region"],
        "code": row["code"],
        "population": row["population"],
        "population_year": 2021,
        "population_basis": "built-up-area",
        "population_source": "ONS Census 2021 usual residents, built-up area",
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--ons-xlsx", type=Path, default=Path("/tmp/ons-bua-2021.xlsx"))
    parser.add_argument("--ons-la", type=Path, default=Path("/tmp/ons-la-pop.csv"))
    parser.add_argument("--scotland", type=Path, default=Path("/tmp/scotland-settlements.csv"))
    args = parser.parse_args()

    england = load_bua(args.ons_xlsx, "1c")
    wales = load_bua(args.ons_xlsx, "1d")
    bua = england + wales
    london, boroughs = load_london(args.ons_la)
    scotland = load_scotland(args.scotland)
    if london is None:
        raise SystemExit("London region row E12000007 not found")

    areas = json.loads(AREAS_PATH.read_text())
    northwest = {slugify(name) for name in areas}

    excluded = []
    selected = []
    for row in bua:
        if row["population"] <= 10_000:
            continue
        reason = ISLAND_EXCLUDE.get(row["name"])
        if reason:
            excluded.append(
                {
                    "name": row["name"],
                    "nation": row["nation"],
                    "region": row["region"],
                    "population": row["population"],
                    "reason": reason,
                }
            )
            continue
        selected.append(bua_record(row))

    noted_under = []
    for row in boroughs:
        if row["population"] <= 10_000:
            noted_under.append(
                {
                    "name": row["name"],
                    "nation": "England",
                    "region": "London",
                    "population": row["population"],
                    "reason": "Usual resident population is not over 10,000",
                }
            )
            continue
        selected.append(row)

    selected.append(london)

    for row in scotland:
        if row["population"] <= 10_000:
            continue
        lowered = row["settlement_name"].lower()
        if any(k in lowered for k in ("stornoway", "kirkwall", "lerwick", "portree", "rothesay", "brodick")):
            excluded.append(
                {
                    "name": row["name"],
                    "nation": "Scotland",
                    "region": "Scotland",
                    "population": row["population"],
                    "reason": "Scottish island settlement, not mainland",
                }
            )
            continue
        selected.append(row)

    place_counts: Counter[str] = Counter()
    for row in selected:
        place, district = split_place(row["name"])
        row["place_name"] = place
        row["district"] = district
        place_counts[place] += 1

    for row in selected:
        place = row["place_name"]
        district = row["district"]
        if place_counts[place] == 1:
            row["name"] = place
            row["slug"] = slugify(place)
        else:
            row["name"] = duplicate_label(place, district, row["region"])
            row["slug"] = slugify(row["name"])
        row["size_class"] = size_class(row["population"])
        on_round = slugify(place) in northwest and row["region"] == "North West"
        row["northwest_round"] = on_round
        row["mainland"] = True

    slugs = [row["slug"] for row in selected]
    dupes = [slug for slug, count in Counter(slugs).items() if count > 1]
    if dupes:
        raise SystemExit(f"Duplicate slugs: {dupes}")

    selected.sort(key=lambda row: (row["nation"], row["region"], row["name"].lower()))

    by_nation = Counter(row["nation"] for row in selected)
    by_basis = Counter(row["population_basis"] for row in selected)
    by_class = Counter(row["size_class"] for row in selected)
    meta = {
        "status": "non-prod",
        "publish": False,
        "threshold": "population > 10000",
        "mainland": (
            "Island of Great Britain only. Excludes Northern Ireland, the Isle of Man, "
            "the Channel Islands, and offshore-island settlements (Isle of Wight, "
            "Sheppey, Canvey, Hayling, Anglesey / Holy Island, Scottish islands)."
        ),
        "counts": {
            "pages": len(selected),
            "by_nation": dict(sorted(by_nation.items())),
            "by_population_basis": dict(sorted(by_basis.items())),
            "by_size_class": dict(sorted(by_class.items())),
            "northwest_round": sum(1 for row in selected if row["northwest_round"]),
            "excluded_islands_over_10k": len(excluded),
            "noted_under_10k": len(noted_under),
            "england_wales_bua_candidates_over_10k": sum(1 for row in bua if row["population"] > 10_000),
            "scotland_settlements_over_10k": sum(1 for row in scotland if row["population"] > 10_000),
            "london_boroughs_over_10k": sum(1 for row in boroughs if row["code"] != "E09000001" and row["population"] > 10_000),
        },
        "sources": [
            "ONS Census 2021 built-up areas, England excluding London, and Wales (tables 1c and 1d)",
            "ONS Census 2021 usual resident population for the London region and London boroughs",
            "Scottish Government urban-rural classification 2020 settlement lookup (mid-2020 populations)",
        ],
        "excluded_islands_over_10k": sorted(excluded, key=lambda row: row["name"]),
        "noted_under_10k": noted_under,
    }

    payload = {"meta": meta, "towns": selected}
    OUT_PATH.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n")
    print(json.dumps(meta["counts"], indent=2))
    print(f"excluded islands: {len(excluded)}; under 10k noted: {len(noted_under)}")
    for row in excluded + noted_under:
        print(f"  - {row['name']} ({row['population']}): {row['reason']}")
    print(f"wrote {OUT_PATH} ({len(selected)} towns)")


if __name__ == "__main__":
    main()
