#!/usr/bin/env python3
"""
Build website/data/aov-nationwide-areas.json.

Towns and cities with Census 2021 usual resident population greater than 10,000.

Sources (downloaded at run time, not committed):
- ONS Census 2021 built-up areas, tables 1c and 1d
  (England excluding London, and Wales).
- ONS Census 2021 TS001 usual residents for London boroughs
  (household + communal). ONS does not publish London settlements
  the same way as other built-up areas.

Scotland uses NRS Census 2022 localities (the named towns and cities),
usual resident popcount greater than 10,000.
Northern Ireland is not on the UK mainland, so it is not included.

Usage:
  python3 website/bin/build-aov-pop10k-areas.py
"""
from __future__ import annotations

import csv
import json
import re
import urllib.request
import zipfile
from collections import defaultdict
from pathlib import Path
from xml.etree import ElementTree as ET

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / "website" / "data" / "aov-nationwide-areas.json"
AREAS = ROOT / "website" / "data" / "areas.json"
CACHE = Path("/tmp/ons-aov-pop10k")
THRESHOLD = 10000

BUA_XLSX_URL = (
    "https://www.ons.gov.uk/file?uri=/peoplepopulationandcommunity/housing/"
    "datasets/townsandcitiescharacteristicsofbuiltupareasenglandandwalescensus2021/"
    "2021/townsandcitiescharacteristicsofbuiltupareasenglandandwalescensus2021.xlsx"
)
TS001_URL = "https://static.ons.gov.uk/datasets/TS001-2021-3.csv"
LOCALITY_URL = (
    "https://maps.gov.scot/server/rest/services/NRS/Census2022/MapServer/6/query"
    "?where=popcount%3E10000&outFields=code%2Cname%2Cpopcount"
    "&returnGeometry=false&orderByFields=name&resultRecordCount=1000"
    "&resultOffset={offset}&f=pjson"
)
NS = "{http://schemas.openxmlformats.org/spreadsheetml/2006/main}"


def download(url: str, dest: Path) -> None:
    dest.parent.mkdir(parents=True, exist_ok=True)
    if dest.is_file() and dest.stat().st_size > 0:
        return
    request = urllib.request.Request(url, headers={"User-Agent": "icomply-aov-areas/1.0"})
    with urllib.request.urlopen(request) as response, dest.open("wb") as handle:
        handle.write(response.read())


def area_slug(name: str) -> str:
    slug = re.sub(r"[^a-z0-9]+", "-", name.lower().strip())
    return slug.strip("-")


def norm_key(value: str) -> str:
    return re.sub(r"[^a-z0-9]+", "", value.lower())


def load_shared_strings(book: zipfile.ZipFile) -> list[str]:
    root = ET.fromstring(book.read("xl/sharedStrings.xml"))
    strings: list[str] = []
    for si in root.findall(f"{NS}si"):
        strings.append("".join((node.text or "") for node in si.iter(f"{NS}t")))
    return strings


def sheet_rows(book: zipfile.ZipFile, sheet: str, strings: list[str], columns: set[str]) -> dict[int, dict[str, str]]:
    root = ET.fromstring(book.read(sheet))
    rows: dict[int, dict[str, str]] = {}
    for cell in root.iter(f"{NS}c"):
        ref = cell.attrib.get("r", "")
        col = "".join(ch for ch in ref if ch.isalpha())
        if col not in columns:
            continue
        row = int("".join(ch for ch in ref if ch.isdigit()))
        kind = cell.attrib.get("t")
        value_node = cell.find(f"{NS}v")
        if kind == "s" and value_node is not None and value_node.text:
            value = strings[int(value_node.text)]
        elif value_node is not None and value_node.text:
            value = value_node.text
        else:
            value = ""
        rows.setdefault(row, {})[col] = value
    return rows


def bua_records(book: zipfile.ZipFile, strings: list[str], sheet: str, country: str) -> list[dict]:
    rows = sheet_rows(book, sheet, strings, {"D", "E", "F", "H"})
    records = []
    for row_no, cols in rows.items():
        if row_no < 4:
            continue
        bua_name = (cols.get("F") or "").strip()
        if not bua_name:
            continue
        try:
            population = int(float(cols.get("H") or 0))
        except ValueError:
            continue
        if population <= THRESHOLD:
            continue
        records.append(
            {
                "bua_name": bua_name,
                "code": (cols.get("E") or "").strip(),
                "region": (cols.get("D") or "").strip(),
                "country": country,
                "population": population,
                "source": "ons-bua-census-2021",
            }
        )
    return records


def split_bua_name(bua_name: str) -> tuple[str, str | None]:
    match = re.match(r"^(.*?)\s*\((.*)\)\s*$", bua_name)
    if not match:
        return bua_name.strip(), None
    return match.group(1).strip(), match.group(2).strip()


def display_names(records: list[dict], area_spellings: dict[str, str]) -> None:
    groups: dict[str, list[dict]] = defaultdict(list)
    for record in records:
        short, qual = split_bua_name(record["bua_name"])
        record["short"] = short
        record["qualifier"] = qual
        groups[short].append(record)
    for short, group in groups.items():
        for record in group:
            qual = record["qualifier"]
            if len(group) == 1:
                record["name"] = short
            elif qual and norm_key(qual) == norm_key(short):
                record["name"] = short
            elif qual:
                record["name"] = f"{short} ({qual})"
            else:
                record["name"] = short
            spelling = area_spellings.get(norm_key(record["name"]))
            if spelling and len(group) == 1:
                record["name"] = spelling


def scotland_localities() -> list[dict]:
    """NRS Census 2022 localities. These are the named towns inside settlements."""
    rows: list[dict] = []
    offset = 0
    while True:
        dest = CACHE / f"scotland-localities-{offset}.json"
        download(LOCALITY_URL.format(offset=offset), dest)
        payload = json.loads(dest.read_text(encoding="utf-8"))
        features = payload.get("features") or []
        for feature in features:
            attrs = feature.get("attributes") or {}
            name = str(attrs.get("name") or "").strip()
            try:
                population = int(attrs.get("popcount") or 0)
            except (TypeError, ValueError):
                continue
            code = str(attrs.get("code") or "").strip()
            if not name or population <= THRESHOLD:
                continue
            rows.append(
                {
                    "bua_name": name,
                    "name": name,
                    "code": code,
                    "region": "Scotland",
                    "country": "Scotland",
                    "population": population,
                    "source": "nrs-census-2022-locality",
                    "short": name,
                    "qualifier": None,
                }
            )
        if not payload.get("exceededTransferLimit"):
            break
        offset += len(features)
        if not features:
            break
    return rows


def london_boroughs() -> list[dict]:
    totals: dict[str, dict] = {}
    with (CACHE / "ts001.csv").open(newline="") as handle:
        for row in csv.DictReader(handle):
            code = row["Lower Tier Local Authorities Code"]
            if not code.startswith("E09"):
                continue
            name = row["Lower Tier Local Authorities"].strip()
            bucket = totals.setdefault(code, {"name": name, "population": 0})
            bucket["population"] += int(row["Observation"])
    records = []
    for code, bucket in totals.items():
        if bucket["population"] <= THRESHOLD:
            continue
        records.append(
            {
                "bua_name": bucket["name"],
                "name": bucket["name"],
                "code": code,
                "region": "London",
                "country": "England",
                "population": bucket["population"],
                "source": "ons-ts001-census-2021-london-borough",
                "short": bucket["name"],
                "qualifier": None,
            }
        )
    return records


def main() -> None:
    download(BUA_XLSX_URL, CACHE / "bua.xlsx")
    download(TS001_URL, CACHE / "ts001.csv")
    area_spellings = {norm_key(name): name for name in json.loads(AREAS.read_text())}
    with zipfile.ZipFile(CACHE / "bua.xlsx") as book:
        strings = load_shared_strings(book)
        records = bua_records(book, strings, "xl/worksheets/sheet6.xml", "England")
        records += bua_records(book, strings, "xl/worksheets/sheet7.xml", "Wales")
    display_names(records, area_spellings)
    records.extend(london_boroughs())
    records.extend(scotland_localities())

    slugs: dict[str, str] = {}
    for record in records:
        slug = area_slug(record["name"])
        if slug in slugs:
            raise SystemExit(f"slug collision {slug}: {slugs[slug]} vs {record['name']}")
        slugs[slug] = record["name"]
        if record["population"] <= THRESHOLD:
            raise SystemExit(f"population at or under threshold: {record}")

    area_names = json.loads(AREAS.read_text())
    by_short: dict[str, list[dict]] = defaultdict(list)
    for record in records:
        by_short[record["short"]].append(record)
    for area_name in area_names:
        if any(record["name"] == area_name for record in records):
            continue
        north_west = [record for record in by_short.get(area_name, []) if record["region"] == "North West"]
        if len(north_west) != 1:
            continue
        alias_slug = area_slug(area_name)
        if alias_slug in slugs:
            continue
        slugs[alias_slug] = north_west[0]["name"]
        north_west[0].setdefault("aliases", []).append(area_name)

    records.sort(key=lambda row: row["name"].lower())
    towns = []
    for record in records:
        towns.append(
            {
                "name": record["name"],
                "slug": area_slug(record["name"]),
                "population": record["population"],
                "country": record["country"],
                "region": record["region"],
                "code": record["code"],
                "source": record["source"],
                "bua_name": record["bua_name"],
                "aliases": record.get("aliases", []),
            }
        )
    payload = {
        "threshold": THRESHOLD,
        "rule": "UK mainland towns and cities with usual resident population greater than 10000. England and Wales use Census 2021 built-up areas. Scotland uses Census 2022 localities.",
        "sources": [
            {
                "id": "ons-bua-census-2021",
                "note": "ONS Census 2021 built-up area usual residents, tables 1c (England excluding London) and 1d (Wales). Counts are the published ONS-rounded figures.",
            },
            {
                "id": "ons-ts001-census-2021-london-borough",
                "note": "ONS Census 2021 TS001 usual residents (household plus communal establishment) for London boroughs. ONS does not identify individual London settlements in the built-up area tables.",
            },
            {
                "id": "nrs-census-2022-locality",
                "note": "NRS Census 2022 locality usual residents (popcount). Localities are the named towns and cities. A wider settlement that only passes 10,000 by joining neighbouring villages is not added on its own.",
            },
        ],
        "not_in_this_slice": "Northern Ireland is not on the UK mainland, so it is not included.",
        "towns": towns,
    }
    OUT.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n")
    print(f"Wrote {len(towns)} towns to {OUT}")


if __name__ == "__main__":
    main()
