#!/usr/bin/env python3
"""Build website/data/uk-mainland-towns-10k.json.

Sources (see website/data/UK-MAINLAND-TOWNS-10K.md):

1. GeoNames cities5000 — GB populated places whose population field is
   greater than 10,000. Neighbourhood feature codes PPLX and PPLL are
   dropped. Northern Ireland and offshore island authorities are dropped.
2. London boroughs — ONS treats Greater London built-up areas as boroughs.
   Names and 2022 ONS population estimates are read from the Wikipedia
   "List of London boroughs" table (saved HTML). The City of London is
   omitted because its resident population is under 10,000. Boroughs
   already present in GeoNames are not duplicated; their population is
   updated to the borough estimate.

This does not use website/data/areas.json. That file is a North West
marketing list and includes places under 10,000.

Usage:
  python3 website/bin/build-uk-mainland-towns.py \
    --geonames /tmp/towns/cities5000.txt \
    --admin1 /tmp/towns/admin1CodesASCII.txt \
    --admin2 /tmp/towns/admin2Codes.txt \
    --london-html /tmp/towns/london.html
"""
from __future__ import annotations

import argparse
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
OUT_JSON = ROOT / "website" / "data" / "uk-mainland-towns-10k.json"
OUT_MD = ROOT / "website" / "data" / "UK-MAINLAND-TOWNS-10K.md"

ISLAND_ADMIN = {
    "Isle of Wight",
    "Anglesey",
    "Eilean Siar",
    "Na h-Eileanan Siar",
    "Shetland Islands",
    "Orkney Islands",
    "Isles of Scilly",
}

PREFIXES = (
    "Royal Borough of ",
    "London Borough of ",
    "Metropolitan Borough of ",
    "City and Borough of ",
    "City and County of ",
    "Borough of ",
    "City of ",
    "County of ",
    "District of ",
    "The ",
    "Sir ",
)
SUFFIXES = (" County Borough", " county borough", " Council")

NORTH_EAST = {
    "County Durham", "Darlington", "Gateshead", "Hartlepool", "Middlesbrough",
    "Newcastle upon Tyne", "North Tyneside", "Northumberland", "Redcar and Cleveland",
    "South Tyneside", "Stockton-on-Tees", "Sunderland",
}
NORTH_WEST = {
    "Blackburn with Darwen", "Blackpool", "Bolton", "Bury", "Cheshire East",
    "Cheshire West and Chester", "Cumbria", "Halton", "Knowsley", "Lancashire",
    "Liverpool", "Manchester", "Oldham", "Rochdale", "Salford", "Sefton",
    "St. Helens", "St Helens", "Stockport", "Tameside", "Trafford", "Warrington",
    "Wigan", "Wirral",
}
YORKSHIRE = {
    "Barnsley", "Bradford", "Calderdale", "Doncaster", "East Riding of Yorkshire",
    "Kingston upon Hull", "Kirklees", "Leeds", "North East Lincolnshire",
    "North Lincolnshire", "North Yorkshire", "Rotherham", "Sheffield", "Wakefield",
    "York",
}
EAST_MIDLANDS = {
    "Derby", "Derbyshire", "Leicester", "Leicestershire", "Lincolnshire",
    "North Northamptonshire", "Nottingham", "Nottinghamshire", "Rutland",
    "West Northamptonshire",
}
WEST_MIDLANDS = {
    "Birmingham", "Coventry", "Dudley", "Herefordshire", "Sandwell", "Shropshire",
    "Solihull", "Staffordshire", "Stoke-on-Trent", "Telford and Wrekin", "Walsall",
    "Warwickshire", "Wolverhampton", "Worcestershire",
}
EAST = {
    "Bedford", "Cambridgeshire", "Central Bedfordshire", "Essex", "Hertfordshire",
    "Luton", "Norfolk", "Peterborough", "Southend-on-Sea", "Suffolk", "Thurrock",
}
LONDON = {"Greater London"}
SOUTH_EAST = {
    "Bracknell Forest", "Brighton and Hove", "Buckinghamshire", "East Sussex",
    "Hampshire", "Isle of Wight", "Kent", "Medway", "Milton Keynes", "Oxfordshire",
    "Portsmouth", "Reading", "Slough", "Southampton", "Surrey", "West Berkshire",
    "West Sussex", "Windsor and Maidenhead", "Wokingham",
}
SOUTH_WEST = {
    "Bath and North East Somerset", "Bournemouth, Christchurch and Poole",
    "Bristol", "Cornwall", "Devon", "Dorset", "Gloucestershire", "North Somerset",
    "Plymouth", "Somerset", "South Gloucestershire", "Swindon", "Torbay", "Wiltshire",
}
SCOTLAND_NE = {"Aberdeen City", "Aberdeenshire", "Moray"}
SCOTLAND_HIGHLANDS = {"Highland", "Argyll and Bute"}
SCOTLAND_EAST = {"Angus", "Dundee City", "Perth and Kinross", "Fife", "Clackmannanshire", "Stirling", "Falkirk"}
SCOTLAND_CENTRAL = {
    "Edinburgh", "East Lothian", "Midlothian", "West Lothian", "Scottish Borders",
}
SCOTLAND_WEST = {
    "Glasgow City", "East Dunbartonshire", "East Renfrewshire", "East Ayrshire",
    "Inverclyde", "North Ayrshire", "North Lanarkshire", "Renfrewshire",
    "South Ayrshire", "South Lanarkshire", "West Dunbartonshire",
}
SCOTLAND_SOUTH = {"Dumfries and Galloway"}


def clean_admin(name: str) -> str:
    name = name.strip()
    changed = True
    while changed:
        changed = False
        for pre in PREFIXES:
            if name.startswith(pre):
                name = name[len(pre):]
                changed = True
    for suf in SUFFIXES:
        if name.endswith(suf):
            name = name[: -len(suf)]
    return name.strip()


def slugify(value: str) -> str:
    value = value.lower().strip()
    value = re.sub(r"[^a-z0-9]+", "-", value)
    return value.strip("-")


def region_for(nation: str, county: str) -> str:
    if nation == "Wales":
        return "Wales"
    if nation == "Scotland":
        if county in SCOTLAND_NE:
            return "North East Scotland"
        if county in SCOTLAND_HIGHLANDS:
            return "Highlands and Argyll"
        if county in SCOTLAND_EAST:
            return "East and Central Scotland"
        if county in SCOTLAND_CENTRAL:
            return "South East Scotland"
        if county in SCOTLAND_WEST:
            return "West Central Scotland"
        if county in SCOTLAND_SOUTH:
            return "South Scotland"
        return "Scotland"
    if county in LONDON:
        return "London"
    if county in NORTH_EAST:
        return "North East"
    if county in NORTH_WEST:
        return "North West"
    if county in YORKSHIRE:
        return "Yorkshire and the Humber"
    if county in EAST_MIDLANDS:
        return "East Midlands"
    if county in WEST_MIDLANDS:
        return "West Midlands"
    if county in EAST:
        return "East of England"
    if county in SOUTH_EAST:
        return "South East"
    if county in SOUTH_WEST:
        return "South West"
    raise SystemExit(f"Unmapped county: {nation} / {county}")


def load_codes(path: Path) -> dict[str, str]:
    out: dict[str, str] = {}
    for line in path.read_text(encoding="utf-8").splitlines():
        parts = line.split("\t")
        if len(parts) >= 2:
            out[parts[0]] = parts[1]
    return out


def parse_geonames(geonames: Path, admin1: dict[str, str], admin2: dict[str, str]) -> list[dict]:
    towns = []
    for line in geonames.read_text(encoding="utf-8").splitlines():
        p = line.split("\t")
        if len(p) < 15 or p[8] != "GB":
            continue
        pop = int(p[14] or 0)
        if pop <= 10000:
            continue
        feature = p[7]
        if feature in ("PPLX", "PPLL"):
            continue
        a1, a2 = p[10], p[11]
        nation = admin1.get(f"GB.{a1}", a1)
        raw_county = admin2.get(f"GB.{a1}.{a2}", a2)
        county = clean_admin(raw_county)
        if nation == "Northern Ireland":
            continue
        if county in ISLAND_ADMIN or raw_county in ISLAND_ADMIN:
            continue
        if p[1].lower().startswith("isle of"):
            continue
        # Greater London neighbourhoods are not towns. Borough seats are kept
        # and completed from the ONS borough list below.
        if county == "Greater London" and feature == "PPL":
            continue
        towns.append({
            "name": p[1],
            "slug": slugify(p[2] or p[1]),
            "population": pop,
            "nation": "England" if nation == "England" else nation,
            "county": county,
            "region": region_for(nation, county),
            "lat": round(float(p[4]), 5),
            "lng": round(float(p[5]), 5),
            "feature": feature,
            "source": "geonames-cities5000",
            "geoname_id": int(p[0]),
        })
    return towns


def parse_london(html_path: Path) -> list[dict]:
    html = html_path.read_text(encoding="utf-8", errors="replace")
    rows = re.findall(r"<tr[^>]*>(.*?)</tr>", html, flags=re.S)
    boroughs = []
    for row in rows:
        text = re.sub(r"<[^>]+>", " ", row)
        text = re.sub(r"\s+", " ", text).strip()
        if "Borough Council" not in text and not text.startswith("City of London"):
            continue
        name_match = re.match(r"([A-Z][^0-9\[]+?)\s+(?:Inner|Outer|Sui generis|\[)", text)
        if not name_match:
            continue
        name = name_match.group(1).strip(" .")
        if name.startswith("City of London"):
            continue
        pops = re.findall(r"(\d{2,3}(?:,\d{3})+)", text)
        if not pops:
            continue
        population = int(pops[-1].replace(",", ""))
        if population <= 10000:
            continue
        lat = lng = None
        dec = re.search(r"(\d+\.\d+)°N\s+(\d+\.\d+)°([EW])", text)
        if dec:
            lat = round(float(dec.group(1)), 5)
            lng = round(float(dec.group(2)), 5)
            if dec.group(3) == "W":
                lng = -lng
        boroughs.append({
            "name": name,
            "slug": slugify(name),
            "population": population,
            "nation": "England",
            "county": "Greater London",
            "region": "London",
            "lat": lat if lat is not None else 51.5074,
            "lng": lng if lng is not None else -0.1278,
            "feature": "LONDON_BOROUGH",
            "source": "ons-london-borough-2022",
        })
    return boroughs


def assign_slugs(towns: list[dict]) -> list[dict]:
    used: dict[str, int] = {}
    for town in towns:
        base = town["slug"] or slugify(town["name"])
        if base not in used:
            town["slug"] = base
            used[base] = 1
            continue
        county_bit = town["county"]
        if county_bit.lower() == town["name"].lower():
            county_bit = town["nation"]
        alt = slugify(f"{town['name']}-{county_bit}")
        if alt in used:
            alt = slugify(f"{town['name']}-{county_bit}-{town['nation']}")
        n = 2
        candidate = alt
        while candidate in used:
            n += 1
            candidate = f"{alt}-{n}"
        town["slug"] = candidate
        used[candidate] = 1
    return towns


def merge_london(towns: list[dict], boroughs: list[dict]) -> tuple[list[dict], int]:
    by_slug = {t["slug"]: t for t in towns}
    added = 0
    for borough in boroughs:
        existing = None
        for town in towns:
            if town["county"] != "Greater London":
                continue
            n = town["name"].lower()
            b = borough["name"].lower()
            if n == b or n.startswith(b + " ") or b.startswith(n + " "):
                existing = town
                break
        if existing:
            existing["population"] = borough["population"]
            existing["population_note"] = "Updated to ONS 2022 London borough estimate"
            existing["source"] = "geonames-cities5000+ons-london-borough-2022"
            if borough.get("lat") is not None:
                existing["lat"] = borough["lat"]
                existing["lng"] = borough["lng"]
            continue
        if borough["slug"] in by_slug:
            continue
        towns.append(borough)
        by_slug[borough["slug"]] = borough
        added += 1
    return towns, added


def write_markdown(meta: dict, towns: list[dict]) -> None:
    by_nation: dict[str, int] = {}
    by_region: dict[str, int] = {}
    for town in towns:
        by_nation[town["nation"]] = by_nation.get(town["nation"], 0) + 1
        by_region[town["region"]] = by_region.get(town["region"], 0) + 1
    lines = [
        "# UK mainland towns with population over 10,000",
        "",
        "Filter used for AOV and Barriers town pages. Threshold is **greater than 10,000** usual or gazetteer population.",
        "",
        "## Sources",
        "",
        "1. **GeoNames `cities5000`** — [download.geonames.org/export/dump/cities5000.zip](https://download.geonames.org/export/dump/cities5000.zip), country code `GB`, `population` field greater than 10,000. Admin names from `admin1CodesASCII.txt` and `admin2Codes.txt`. Feature codes kept: `PPL`, `PPLA`, `PPLA2`, `PPLA3`, `PPLA4`, `PPLC`. Dropped: `PPLX` (section of a populated place) and `PPLL` (populated locality), which are neighbourhoods rather than towns.",
        "2. **London boroughs** — ONS Census 2021 built-up area method does not split Greater London into the same town geography; boroughs are the settlement units ([ONS towns and cities, Census 2021](https://www.ons.gov.uk/peoplepopulationandcommunity/housing/articles/townsandcitiescharacteristicsofbuiltupareasenglandandwales/census2021)). Borough names and **2022 ONS population estimates** are taken from the Wikipedia table [List of London boroughs](https://en.wikipedia.org/wiki/List_of_London_boroughs). The City of London is excluded (resident population under 10,000). Boroughs already in GeoNames are not given a second page; the borough estimate replaces the GeoNames figure.",
        "3. **Mainland** — England, Scotland and Wales only. Excluded: Northern Ireland; Isle of Wight; Isles of Scilly; Anglesey; Shetland; Orkney; Eilean Siar / Na h-Eileanan Siar; names beginning with “Isle of”.",
        "",
        "`website/data/areas.json` is **not** this list. It is a North West marketing list of about 168 places and includes towns under 10,000.",
        "",
        "## Counts",
        "",
        f"- Towns: **{meta['towns']}**",
        f"- England: {by_nation.get('England', 0)}",
        f"- Scotland: {by_nation.get('Scotland', 0)}",
        f"- Wales: {by_nation.get('Wales', 0)}",
        f"- London borough rows added beyond GeoNames: {meta['london_boroughs_added']}",
        "",
        "### By region",
        "",
    ]
    for region, count in sorted(by_region.items(), key=lambda item: (-item[1], item[0])):
        lines.append(f"- {region}: {count}")
    lines += [
        "",
        "## Slugs",
        "",
        "Slugs follow the site rule in `areaSlug()`: lower case, non-alphanumeric characters become hyphens. If two towns share a name, the county (or the nation, when the county name matches the town) is appended, for example Newport in Wales and Newport in Telford and Wrekin.",
        "",
        "## Rebuild",
        "",
        "```bash",
        "python3 website/bin/build-uk-mainland-towns.py \\",
        "  --geonames /tmp/towns/cities5000.txt \\",
        "  --admin1 /tmp/towns/admin1CodesASCII.txt \\",
        "  --admin2 /tmp/towns/admin2Codes.txt \\",
        "  --london-html /tmp/towns/london.html",
        "```",
        "",
    ]
    OUT_MD.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--geonames", type=Path, required=True)
    parser.add_argument("--admin1", type=Path, required=True)
    parser.add_argument("--admin2", type=Path, required=True)
    parser.add_argument("--london-html", type=Path, required=True)
    args = parser.parse_args()
    admin1 = load_codes(args.admin1)
    admin2 = load_codes(args.admin2)
    towns = parse_geonames(args.geonames, admin1, admin2)
    boroughs = parse_london(args.london_html)
    towns, added = merge_london(towns, boroughs)
    towns = assign_slugs(towns)
    towns.sort(key=lambda row: (row["name"].lower(), row["slug"]))
    for town in towns:
        if town["population"] <= 10000:
            raise SystemExit(f"Population filter failed for {town['name']}")
        if town["nation"] not in ("England", "Scotland", "Wales"):
            raise SystemExit(f"Nation filter failed for {town['name']}")
    meta = {
        "threshold": "population > 10000",
        "mainland": "England, Scotland and Wales; Northern Ireland and listed offshore islands excluded",
        "towns": len(towns),
        "london_boroughs_added": added,
        "london_boroughs_parsed": len(boroughs),
        "built": "2026-10-02",
    }
    payload = {"meta": meta, "towns": towns}
    OUT_JSON.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    write_markdown(meta, towns)
    print(f"Wrote {len(towns)} towns ({added} London boroughs added) → {OUT_JSON}")


if __name__ == "__main__":
    main()
