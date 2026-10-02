#!/usr/bin/env python3
"""Build website/data/aov-towns.json from a GeoNames cities5000 dump.

Source file (not committed): cities5000.txt
  https://download.geonames.org/export/dump/cities5000.zip
Admin names: admin2Codes.txt from the same dump site.

Rule: populated places and admin seats in England, Wales and mainland
Scotland with a GeoNames population greater than 10,000.

Excluded:
  - Northern Ireland
  - Isle of Wight, Anglesey, Na h-Eileanan Siar
  - names containing "island" or "isle of"
  - GeoNames sections of a place (PPLX) and district localities (PPLL/PPLS),
    except Thornton-Cleveleys and Deeside, which are the names people use

Usage:
  python3 website/bin/build-aov-town-data.py /tmp/geonames/cities5000.txt /tmp/geonames/admin2Codes.txt
"""
from __future__ import annotations

import json
import math
import re
import sys
from collections import defaultdict
from pathlib import Path

STOCKPORT = (53.40979, -2.15761)  # GeoNames 2636882 Stockport; straight-line distances use this point
ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "data" / "aov-towns.json"

SKIP_ADMIN = {("ENG", "G2"), ("WLS", "X1"), ("SCT", "W8")}
PLLC_KEEP = {"Thornton-Cleveleys", "Deeside"}
KEEP_FEATURE = {"PPL", "PPLA", "PPLA2", "PPLA3", "PPLA4", "PPLC", "PPLL"}
NATION = {"ENG": "England", "WLS": "Wales", "SCT": "Scotland"}

# Same gazetteer name inside one county. geoname id -> public label / slug.
LABEL_OVERRIDE = {
    2647261: "Hayes, Hillingdon",
    2647262: "Hayes, Bromley",
}
SLUG_OVERRIDE = {
    2647262: "hayes-bromley",
}

ENGLAND_REGION = {
    "Barnsley": "Yorkshire and the Humber",
    "Bath and North East Somerset": "South West",
    "Bedford": "East of England",
    "Birmingham": "West Midlands",
    "Blackburn with Darwen": "North West",
    "Blackpool": "North West",
    "Bolton": "North West",
    "Bournemouth, Christchurch and Poole": "South West",
    "Bracknell Forest": "South East",
    "Bradford": "Yorkshire and the Humber",
    "Brighton and Hove": "South East",
    "Bristol": "South West",
    "Buckinghamshire": "South East",
    "Bury": "North West",
    "Calderdale": "Yorkshire and the Humber",
    "Cambridgeshire": "East of England",
    "Central Bedfordshire": "East of England",
    "Cheshire East": "North West",
    "Cheshire West and Chester": "North West",
    "Cornwall": "South West",
    "County Durham": "North East",
    "Coventry": "West Midlands",
    "Cumbria": "North West",
    "Darlington": "North East",
    "Derby": "East Midlands",
    "Derbyshire": "East Midlands",
    "Devon": "South West",
    "Doncaster": "Yorkshire and the Humber",
    "Dorset": "South West",
    "Dudley": "West Midlands",
    "East Riding of Yorkshire": "Yorkshire and the Humber",
    "East Sussex": "South East",
    "Essex": "East of England",
    "Gateshead": "North East",
    "Gloucestershire": "South West",
    "Greater London": "Greater London",
    "Halton": "North West",
    "Hampshire": "South East",
    "Hartlepool": "North East",
    "Herefordshire": "West Midlands",
    "Hertfordshire": "East of England",
    "Kent": "South East",
    "Kingston upon Hull": "Yorkshire and the Humber",
    "Kirklees": "Yorkshire and the Humber",
    "Knowsley": "North West",
    "Lancashire": "North West",
    "Leeds": "Yorkshire and the Humber",
    "Leicester": "East Midlands",
    "Leicestershire": "East Midlands",
    "Lincolnshire": "East Midlands",
    "Liverpool": "North West",
    "Luton": "East of England",
    "Manchester": "North West",
    "Medway": "South East",
    "Middlesbrough": "North East",
    "Milton Keynes": "South East",
    "Newcastle upon Tyne": "North East",
    "Norfolk": "East of England",
    "North East Lincolnshire": "Yorkshire and the Humber",
    "North Lincolnshire": "Yorkshire and the Humber",
    "North Northamptonshire": "East Midlands",
    "North Somerset": "South West",
    "North Tyneside": "North East",
    "North Yorkshire": "Yorkshire and the Humber",
    "Northumberland": "North East",
    "Nottingham": "East Midlands",
    "Nottinghamshire": "East Midlands",
    "Oldham": "North West",
    "Oxfordshire": "South East",
    "Peterborough": "East of England",
    "Plymouth": "South West",
    "Portsmouth": "South East",
    "Reading": "South East",
    "Redcar and Cleveland": "North East",
    "Rochdale": "North West",
    "Rotherham": "Yorkshire and the Humber",
    "Rutland": "East Midlands",
    "Salford": "North West",
    "Sandwell": "West Midlands",
    "Sefton": "North West",
    "Sheffield": "Yorkshire and the Humber",
    "Shropshire": "West Midlands",
    "Slough": "South East",
    "Solihull": "West Midlands",
    "Somerset": "South West",
    "South Gloucestershire": "South West",
    "South Tyneside": "North East",
    "Southampton": "South East",
    "Southend-on-Sea": "East of England",
    "St Helens": "North West",
    "Staffordshire": "West Midlands",
    "Stockport": "North West",
    "Stockton-on-Tees": "North East",
    "Stoke-on-Trent": "West Midlands",
    "Suffolk": "East of England",
    "Sunderland": "North East",
    "Surrey": "South East",
    "Swindon": "South West",
    "Tameside": "North West",
    "Telford and Wrekin": "West Midlands",
    "Thurrock": "East of England",
    "Torbay": "South West",
    "Trafford": "North West",
    "Wakefield": "Yorkshire and the Humber",
    "Walsall": "West Midlands",
    "Warrington": "North West",
    "Warwickshire": "West Midlands",
    "West Berkshire": "South East",
    "West Northamptonshire": "East Midlands",
    "West Sussex": "South East",
    "Wigan": "North West",
    "Wiltshire": "South West",
    "Windsor and Maidenhead": "South East",
    "Wirral": "North West",
    "Wokingham": "South East",
    "Wolverhampton": "West Midlands",
    "Worcestershire": "West Midlands",
    "York": "Yorkshire and the Humber",
}

WALES_REGION = {
    "Flintshire": "North Wales",
    "Wrexham": "North Wales",
    "Conwy": "North Wales",
    "Denbighshire": "North Wales",
    "Gwynedd": "North Wales",
    "Powys": "Mid Wales",
    "Ceredigion": "Mid Wales",
}

SCOTLAND_REGION = {
    "Highland": "North Scotland",
    "Moray": "North Scotland",
    "Aberdeenshire": "North Scotland",
    "Aberdeen City": "North Scotland",
    "Angus": "East Scotland",
    "Dundee City": "East Scotland",
    "Fife": "East Scotland",
    "Perth and Kinross": "East Scotland",
    "Stirling": "East Scotland",
    "Clackmannanshire": "East Scotland",
    "Falkirk": "East Scotland",
    "Edinburgh": "East Scotland",
    "Midlothian": "East Scotland",
    "East Lothian": "East Scotland",
    "West Lothian": "East Scotland",
    "The Scottish Borders": "East Scotland",
    "Dumfries and Galloway": "South Scotland",
    "Glasgow City": "West Scotland",
    "North Lanarkshire": "West Scotland",
    "South Lanarkshire": "West Scotland",
    "East Ayrshire": "West Scotland",
    "North Ayrshire": "West Scotland",
    "South Ayrshire": "West Scotland",
    "Renfrewshire": "West Scotland",
    "East Renfrewshire": "West Scotland",
    "Inverclyde": "West Scotland",
    "East Dunbartonshire": "West Scotland",
    "West Dunbartonshire": "West Scotland",
    "Argyll and Bute": "West Scotland",
}


def clean_county(name: str) -> str:
    name = name.strip()
    for pre in (
        "City and County of ",
        "City and Borough of ",
        "Metropolitan Borough of ",
        "Royal Borough of ",
        "Borough of ",
        "City of ",
        "District of ",
        "County of ",
    ):
        if name.startswith(pre):
            name = name[len(pre):]
    name = re.sub(r"\s+[Cc]ounty [Bb]orough$", "", name)
    name = re.sub(r"\s+Council$", "", name)
    name = re.sub(r"^Sir ", "", name)
    name = name.replace("St. ", "St ")
    return name.strip()


def slugify(value: str) -> str:
    value = value.lower().replace("'", "").replace("’", "")
    value = re.sub(r"[^a-z0-9]+", "-", value)
    return value.strip("-")


def haversine_km(lat1: float, lon1: float, lat2: float, lon2: float) -> float:
    r = 6371.0
    p1, p2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlmb = math.radians(lon2 - lon1)
    a = math.sin(dphi / 2) ** 2 + math.cos(p1) * math.cos(p2) * math.sin(dlmb / 2) ** 2
    return 2 * r * math.asin(math.sqrt(a))


def bearing_name(lat1: float, lon1: float, lat2: float, lon2: float) -> str:
    y = math.sin(math.radians(lon2 - lon1)) * math.cos(math.radians(lat2))
    x = (
        math.cos(math.radians(lat1)) * math.sin(math.radians(lat2))
        - math.sin(math.radians(lat1)) * math.cos(math.radians(lat2)) * math.cos(math.radians(lon2 - lon1))
    )
    deg = (math.degrees(math.atan2(y, x)) + 360) % 360
    names = ["north", "north-east", "east", "south-east", "south", "south-west", "west", "north-west"]
    return names[int((deg + 22.5) // 45) % 8]


def band_for(pop: int) -> str:
    if pop >= 200000:
        return "city"
    if pop >= 75000:
        return "large"
    if pop >= 20000:
        return "town"
    return "small"


def region_for(nation: str, county: str) -> str:
    if nation == "England":
        region = ENGLAND_REGION.get(county)
    elif nation == "Wales":
        region = WALES_REGION.get(county, "South Wales")
    else:
        region = SCOTLAND_REGION.get(county)
    if not region:
        raise SystemExit(f"No region for {nation} / {county}")
    return region


def load_admin(path: Path) -> dict[str, str]:
    admin: dict[str, str] = {}
    for line in path.read_text(encoding="utf-8").splitlines():
        parts = line.split("\t")
        if len(parts) >= 2:
            admin[parts[0]] = parts[1]
    return admin


def load_places(cities: Path, admin: dict[str, str]) -> list[dict]:
    places = []
    for line in cities.read_text(encoding="utf-8").splitlines():
        p = line.split("\t")
        if len(p) < 15 or p[8] != "GB" or p[6] != "P":
            continue
        pop = int(p[14] or 0)
        if pop <= 10000:
            continue
        if p[10] not in NATION:
            continue
        if (p[10], p[11]) in SKIP_ADMIN:
            continue
        low = p[1].lower()
        if "isle of" in low or "island" in low:
            continue
        if p[7] == "PPLX":
            continue
        if p[7] in ("PPLL", "PPLS") and p[1] not in PLLC_KEEP:
            continue
        if p[7] not in KEEP_FEATURE:
            continue
        gid = int(p[0])
        county = clean_county(admin.get(f"GB.{p[10]}.{p[11]}", p[11]))
        nation = NATION[p[10]]
        lat, lon = float(p[4]), float(p[5])
        places.append({
            "geoname_id": gid,
            "name": p[1],
            "population": pop,
            "nation": nation,
            "county": county,
            "region": region_for(nation, county),
            "lat": round(lat, 5),
            "lon": round(lon, 5),
            "band": band_for(pop),
            "km": int(round(haversine_km(STOCKPORT[0], STOCKPORT[1], lat, lon))),
            "bearing": bearing_name(STOCKPORT[0], STOCKPORT[1], lat, lon),
        })
    return places


def assign_slugs(places: list[dict]) -> None:
    groups: dict[str, list[dict]] = defaultdict(list)
    for place in places:
        groups[slugify(place["name"])].append(place)
    used: set[str] = set()
    for base, group in groups.items():
        group.sort(key=lambda row: (-row["population"], row["geoname_id"]))
        for index, place in enumerate(group):
            if place["geoname_id"] in SLUG_OVERRIDE:
                slug = SLUG_OVERRIDE[place["geoname_id"]]
            elif index == 0:
                slug = base
            else:
                suffix = slugify(place["county"])
                slug = f"{base}-{suffix}"
                if slug in used:
                    slug = f"{slug}-{place['geoname_id']}"
            if slug in used:
                raise SystemExit(f"Duplicate slug {slug}")
            used.add(slug)
            place["slug"] = slug
            if place["geoname_id"] in LABEL_OVERRIDE:
                place["label"] = LABEL_OVERRIDE[place["geoname_id"]]
            elif len(group) > 1 and place["county"].lower() == place["name"].lower():
                place["label"] = f"{place['name']}, {place['nation']}"
            elif len(group) > 1:
                place["label"] = f"{place['name']}, {place['county']}"
            else:
                place["label"] = place["name"]
        if len(group) > 1:
            for place in group:
                place["also"] = [
                    {"slug": other["slug"], "label": other["label"], "population": other["population"]}
                    for other in group
                    if other is not place
                ]


def assign_neighbours(places: list[dict]) -> None:
    for place in places:
        scored = []
        for other in places:
            if other is place:
                continue
            km = haversine_km(place["lat"], place["lon"], other["lat"], other["lon"])
            scored.append((km, other))
        scored.sort(key=lambda item: (item[0], item[1]["slug"]))
        place["near"] = [
            {"slug": other["slug"], "name": other["label"], "km": round(km, 1)}
            for km, other in scored[:3]
        ]


def main() -> None:
    if len(sys.argv) != 3:
        raise SystemExit("usage: build-aov-town-data.py cities5000.txt admin2Codes.txt")
    places = load_places(Path(sys.argv[1]), load_admin(Path(sys.argv[2])))
    assign_slugs(places)
    assign_neighbours(places)
    places.sort(key=lambda row: (row["name"].lower(), row["slug"]))
    for place in places:
        if place["population"] <= 10000:
            raise SystemExit("population filter failed")
        if place["nation"] not in ("England", "Wales", "Scotland"):
            raise SystemExit("nation filter failed")
    payload = {
        "source": "GeoNames cities5000 (https://www.geonames.org/) — CC-BY 4.0",
        "retrieved": "2026-10-02",
        "distance_from": "Stockport (GeoNames 2636882, 53.40979, -2.15761). Kilometres are straight-line, not road miles.",
        "rule": (
            "England, Wales and mainland Scotland. GeoNames population greater than 10000. "
            "Excludes Northern Ireland, the Isle of Wight, Anglesey, Na h-Eileanan Siar, "
            "names containing island or isle of, GeoNames place-sections (PPLX), and "
            "district localities except Thornton-Cleveleys and Deeside."
        ),
        "count": len(places),
        "towns": places,
    }
    OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(f"Wrote {OUT} towns={len(places)}")


if __name__ == "__main__":
    main()
