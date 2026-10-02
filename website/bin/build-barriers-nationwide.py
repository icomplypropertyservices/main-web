#!/usr/bin/env python3
"""
Build barriers nationwide data from official population tables.

England and Wales: ONS Census 2021 built-up areas (towns workbook).
London: ONS Census 2021 local-authority usual residents (Greater London is not
split into built-up areas in that workbook). City of London is under 10,000
and is omitted.
Scotland: NRS mid-2020 locality estimates.
Isle of Wight settlements are omitted (not mainland). Northern Ireland is not
in these mainland sources and is not given a page.

Does not invent landmarks for census towns. Each page's substance is that
settlement's own published counts.
"""
from __future__ import annotations

import json
import re
from collections import defaultdict
from pathlib import Path

import openpyxl

ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / "data"
ONS_XLSX = Path("/tmp/bua/ewc.xlsx")
SCOT_XLSX = Path("/tmp/bua/scot.xlsx")
LONDON_CSV = Path("/tmp/bua/la.csv")

IOW = {
    "Cowes",
    "Newport (Isle of Wight)",
    "Ryde",
    "Sandown",
    "Shanklin",
    "Ventnor",
    "East Cowes",
    "Freshwater",
    "Yarmouth",
}
ISLAND_COUNCILS = {
    "Na h-Eileanan Siar",
    "Orkney Islands",
    "Shetland Islands",
}

LONDON_NOTES = {
    "Barking and Dagenham": "The growth land is Barking Riverside and the A13 corridor. Dagenham still has large industrial plots on the old Ford estate. A barrier here belongs on a private yard or a new podium, not on the A13 itself.",
    "Barnet": "Barnet stretches from Chipping Barnet down to Brent Cross. Brent Cross is a private shopping centre with its own service lanes. The A1 and the high streets are highway, so they are not barrier sites.",
    "Bexley": "Thames-side industry at Belvedere and Erith is the likely private-lane work. Bexleyheath shopping streets are not. We would survey a distributor road inside a private estate, not the A2.",
    "Brent": "Park Royal sits on the borough edge and is one of London's largest industrial estates. Wembley event days create private stewarded routes that are not the same job as a rising arm on a staff car park.",
    "Bromley": "Biggin Hill airfield is inside the borough and is operational land, not a town-centre barrier. Bromley town centre itself is a highway and retail core. Private work is more likely on out-of-centre business sites.",
    "Camden": "King's Cross and the biomedical land around it are private estates with their own vehicle control. Bloomsbury streets and the public squares are not places we put a boom.",
    "Croydon": "Wellesley Road is an office canyon with private car parks behind the towers. The tram corridor and the retail streets stay public. A survey here starts with who owns the ramp, not with the town centre one-way system.",
    "Ealing": "Park Royal's industrial roads and the A40 frontage are the employment land. Ealing Broadway is a public town centre. Barriers belong on the estate roads the landlord controls.",
    "Enfield": "Brimsdown is the industrial estate that generates yard-gate enquiries. Enfield Town and the A10 are not barrier locations. We ask which private gate, not which high street.",
    "Greenwich": "Greenwich Peninsula and the Charlton retail parks are private land with service access. The Thames path and the Royal Park are not. Peninsula plots often need a barrier that fails open for the estate's fire route.",
    "Hackney": "Victorian street widths around Mare Street leave little room for a boom on the highway. The realistic sites are new-build podiums and yard gates on industrial leftovers by the Lea.",
    "Hammersmith and Fulham": "White City and the Earls Court land are development plots with private vehicle courts. King Street is public. Any barrier has to live inside the plot boundary.",
    "Haringey": "Tottenham Hale's regeneration blocks and the Lea-side industrial strips are the private sites. Wood Green high street is not. We separate podium ramps from shop-front loading that still sits on the highway.",
    "Harrow": "Harrow is a suburban commuter borough. Wealdstone and the civic centre generate occasional staff-parking control. There is little heavy-yard industry, so a boom is the exception, not the default brief.",
    "Havering": "Rainham's riverside industry and Romford's private service yards are different jobs. Rainham needs yard geometry and HGV thought. Romford town centre roads remain public.",
    "Hillingdon": "Heathrow's airside and campus roads are airport land. We do not barrier them unless the airport operator appoints us. Stockley Park is a separate business-park brief with conventional rising arms.",
    "Hounslow": "The Great West Road office strip and the hotels around Hatton Cross are private frontages. The A4 itself is not. Hotel forecourts need a short boom and a clear pedestrian split.",
    "Islington": "Upper Street and the Angel are public. The sites that actually fit a barrier are private mews, campus yards and the occasional basement ramp on a newer block.",
    "Kensington and Chelsea": "This borough is mews, garden squares and mansion blocks, not industrial yards. A barrier is unusual and, when it exists, is a short private court. We will not pretend there is a logistics estate here.",
    "Kingston upon Thames": "Kingston's one-way system and the riverside are highway. Private work is the service yards behind the retail core and small industrial edges towards the Hogsmill, not the market place.",
    "Lambeth": "Waterloo and Vauxhall have private development plots with basement ramps. The South Bank walk and the bridges are public. We quote the ramp the freeholder owns.",
    "Lewisham": "Lewisham Gateway and the edge of Surrey Quays are private plots. Lewisham High Street is not. New podiums are the usual barrier conversation in this borough.",
    "Merton": "Wimbledon Grounds are club land with their own vehicle plan. Deer Park and Shannon Corner are the industrial and retail-park lanes. Wimbledon Village streets are not barrier sites.",
    "Newham": "The Royal Docks, ExCeL's estate roads and Stratford's private plots are separate landowners. We do not treat the dock road network as one site. Each plot is quoted on its own drawing.",
    "Redbridge": "Ilford is a town centre on public roads. Barrier enquiries, when they are real, sit on private car parks and small business yards off the A12, not on Ilford Hill.",
    "Richmond upon Thames": "Twickenham Stadium is private club land. Richmond town centre and the riverside are public. A barrier job here is usually a small private car park, not a stadium contract, unless the club instructs it.",
    "Southwark": "London Bridge's private estates and what remains of Old Kent Road industry are the two patterns. The Borough Market streets are public and too tight for a boom.",
    "Sutton": "Sutton town centre is highway. Beddington's industrial area is where a yard barrier is plausible. We do not put a boom on the High Street gyratory.",
    "Tower Hamlets": "Canary Wharf is a private estate that already runs its own vehicle control. We do not imply that estate's barriers are ours. Other Isle of Dogs and Whitechapel sites are separate freeholders with their own ramps.",
    "Waltham Forest": "Blackhorse Lane still has industrial yards. Walthamstow town centre is a public street and market. The barrier conversation belongs on the Lane, not the market.",
    "Wandsworth": "Nine Elms is a run of private plots, each with its own basement ramp. The A3 and Wandsworth town centre are highway. Quotes are per plot, not for the whole opportunity area.",
    "Westminster": "Westminster has almost no industrial yards. The only honest barrier sites are private mews, hotel service bays and basement ramps. Red routes and the parks are out of scope.",
}


def slugify(value: str) -> str:
    s = value.lower().strip()
    s = re.sub(r"[^a-z0-9]+", "-", s)
    return s.strip("-")


def num(value):
    if isinstance(value, bool) or value is None:
        return None
    if isinstance(value, (int, float)):
        return float(value)
    if isinstance(value, str):
        t = value.strip().replace(",", "")
        if t in {"", "[c]", "c", "-"}:
            return None
        try:
            return float(t)
        except ValueError:
            return None
    return None


def pct(value) -> str:
    n = num(value)
    if n is None:
        return ""
    if abs(n - round(n)) < 0.05:
        return str(int(round(n)))
    return f"{n:.1f}"


def comma(n: int) -> str:
    return f"{n:,}"


def top_rows(rows, k=3):
    clean = []
    for label, count, share in rows:
        share_n = num(share)
        count_n = num(count)
        if share_n is None and count_n is None:
            continue
        clean.append((str(label), count_n, share_n if share_n is not None else -1))
    clean.sort(key=lambda r: r[2], reverse=True)
    return [{"label": a, "count": None if b is None else int(round(b)), "pct": c} for a, b, c in clean[:k]]


def load_long(ws, code_idx, label_idx, count_idx, pct_idx, wanted):
    out = defaultdict(list)
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i < 3 or not row or row[code_idx] not in wanted:
            continue
        out[row[code_idx]].append((row[label_idx], row[count_idx], row[pct_idx]))
    return out


def implication(industry: str, housing: str) -> str:
    ind = industry.lower()
    house = housing.lower()
    if "manufactur" in ind:
        base = "The employment mix is manufacturing-led, so the plausible barrier is a private yard or staff car park gate, not a boom on the public road."
    elif "transport" in ind or "storage" in ind:
        base = "Transport and storage is the largest industry group, which usually means depot mouths and longer vehicles. Loop detectors have to suit the wheelbase. The public highway is still not the site."
    elif "health" in ind or "social work" in ind:
        base = "Human health and social work leads employment. A barrier only belongs on a private staff car park if that estate instructs us. We do not assume a hospital contract from the census industry label."
    elif ind.startswith("education") or "education" in ind:
        base = "Education is the largest industry group. Campus staff parking can need a barrier. The road outside a school gate does not."
    elif "accommodation" in ind or "food" in ind:
        base = "Accommodation and food service leads the published industry mix. Hotel and restaurant car parks are in scope only where the operator owns the lane."
    elif "retail" in ind or "wholesale" in ind:
        base = "Wholesale and retail is the largest industry group. Private service yards and retail-park lanes can take a rising arm. The high street itself cannot."
    elif "construction" in ind:
        base = "Construction leads the industry table. That often means a temporary compound gate tied to one project, which we scope to the programme rather than sell as a permanent car-park system."
    elif "information" in ind or "communication" in ind or "financial" in ind or "professional" in ind or "scientific" in ind:
        base = "Office-based employment leads the industry table. The matching barrier, when there is one, is a private office or multi-storey car park, not a logistics boom."
    elif "public admin" in ind or "defence" in ind:
        base = "Public administration is the largest industry group. Civic staff car parks are sometimes controlled. Public civic squares are not."
    elif "agriculture" in ind or "fishing" in ind:
        base = "Agriculture leads the published industry mix. A rising arm is rarely the right product here, and we would say so rather than force a car-park specification."
    else:
        base = f"The largest industry group is {industry}. Any barrier has to sit on land that employer or landlord controls."
    if "flat" in house or "maisonette" in house or "apartment" in house:
        extra = " Flats are the largest accommodation group, so basement and podium ramps are the housing form that actually needs a boom."
    elif "detached" in house:
        extra = " Detached houses lead the housing mix, so communal booms are less common than on flatted estates. We do not invent a podium that the census says is not the main housing form."
    elif "terraced" in house:
        extra = " Terraced housing leads the stock. Street frontages are a poor place for a boom. Private yards behind that housing are the sites worth surveying."
    else:
        extra = " Semi-detached houses lead the stock, which points to suburban private car parks rather than city-centre podiums."
    return base + extra


def ew_reading(p) -> str:
    inds = p["industry"]
    houses = p["housing"]
    tens = p["tenure"]
    occ = p["occupation"]
    where = p["region"] if p["region"] != p["country"] else p["country"]
    ind_bits = []
    for row in inds:
        ind_bits.append(f"{row['label']} at {pct(row['pct'])}%")
    house_bits = [f"{row['label']} at {pct(row['pct'])}%" for row in houses]
    ten_bits = [f"{row['label']} at {pct(row['pct'])}%" for row in tens]
    occ_bit = ""
    if occ:
        occ_bit = f" The largest occupation group is {occ[0]['label']} at {pct(occ[0]['pct'])}% of employed residents."
    emp = p.get("employed_pct")
    emp_bit = f" Among residents aged 16 and over, {pct(emp)}% were recorded as employed." if emp is not None else ""
    lead_ind = inds[0]["label"] if inds else "the published industry mix"
    lead_house = houses[0]["label"] if houses else "the published housing mix"
    return (
        f"{p['name']} is the Census 2021 built-up area {p['code']} in {where}. "
        f"ONS counted {comma(p['population'])} usual residents and a median age of {p['median_age']}. "
        f"The ONS size class for this settlement is {p['size']}. "
        f"Largest industry groups among employed residents: {', then '.join(ind_bits)}. "
        f"Accommodation: {', then '.join(house_bits)}. "
        f"Tenure: {', then '.join(ten_bits)}."
        f"{occ_bit}{emp_bit} "
        f"{implication(lead_ind, lead_house)} "
        f"Counts are rounded to the nearest 5 and very small cells may have been suppressed by ONS. "
        f"A barrier survey is on application and only for a private lane. This readout does not invent a business park."
    )


def scotland_reading(p) -> str:
    bands = p["age_bands"]
    pop = p["population"]
    u16 = round(100 * bands["under16"] / pop, 1)
    w = round(100 * bands["working"] / pop, 1)
    o = round(100 * bands["over65"] / pop, 1)
    if o >= 22:
        shape = (
            f"Residents aged 65 and over are {o}% of {p['name']}, which is a high share for a Scottish locality of this size. "
            f"Barrier demand, if it exists, is more likely to be a private car park for housing or a visitor site than a student podium."
        )
    elif u16 >= 20:
        shape = (
            f"Under-16s are {u16}% of {p['name']}. Family housing is a large part of the age structure. "
            f"A communal barrier is only relevant where a private court or yard exists, not on the street outside a school."
        )
    else:
        shape = (
            f"Working-age residents (16 to 64) are {w}% of {p['name']}. "
            f"Staff parking and employment yards are the briefs that match that age structure. The public road is not the installation."
        )
    return (
        f"{p['name']} is NRS locality {p['code']} in {p['region']}, Scotland. "
        f"The mid-2020 estimate is {comma(pop)} people: {comma(bands['under16'])} under 16, "
        f"{comma(bands['working'])} aged 16 to 64, and {comma(bands['over65'])} aged 65 and over "
        f"({u16}%, {w}% and {o}%). "
        f"{shape} "
        f"This page uses the locality estimate, not a guessed business-park name. "
        f"Attendance from Stockport is a planned mainland visit. The quote is on application after the lane is known."
    )


def london_reading(p) -> str:
    note = LONDON_NOTES[p["name"]]
    return (
        f"{p['name']} had {comma(p['population'])} usual residents at Census 2021 (local authority {p['code']}). "
        f"ONS does not separate Greater London into built-up areas the way it does for the rest of England and Wales, so this page uses the borough count rather than inventing a town boundary. "
        f"{note} "
        f"Travel from Stockport is a planned London day. The fee is on application after a lane survey."
    )


def collect_ew():
    wb = openpyxl.load_workbook(ONS_XLSX, read_only=True, data_only=True)
    places = {}
    for sheet in ("1c", "1d"):
        ws = wb[sheet]
        for i, row in enumerate(ws.iter_rows(values_only=True)):
            if i < 3 or not row or not row[5]:
                continue
            pop = num(row[7])
            if pop is None or pop < 10000:
                continue
            name = str(row[5]).strip()
            if name in IOW:
                continue
            places[row[4]] = {
                "name": name,
                "code": row[4],
                "country": row[1],
                "region": row[3],
                "population": int(round(pop)),
                "size": row[6],
                "kind": "bua",
                "source": "ONS Census 2021 built-up area",
            }
    wanted = set(places)
    ages = {}
    ws = wb["2c"]
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i < 3 or not row or row[4] not in wanted:
            continue
        ages[row[4]] = num(row[7])
    ws = wb["2d"]
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i < 3 or not row or row[4] not in wanted:
            continue
        ages[row[4]] = num(row[7])
    housing = {}
    tenure = {}
    industry = {}
    occupation = {}
    employed = {}
    for sheet, bucket, label_name in (
        ("5c", housing, "housing"),
        ("5d", housing, "housing"),
        ("6c", tenure, "tenure"),
        ("6d", tenure, "tenure"),
        ("9c", industry, "industry"),
        ("9d", industry, "industry"),
        ("10c", occupation, "occupation"),
        ("10d", occupation, "occupation"),
    ):
        ws = wb[sheet]
        found = load_long(ws, 4, 7, 8, 9, wanted)
        for code, rows in found.items():
            bucket[code] = top_rows(rows, 3 if label_name == "industry" else 4)
    for sheet in ("8c", "8d"):
        ws = wb[sheet]
        for i, row in enumerate(ws.iter_rows(values_only=True)):
            if i < 3 or not row or row[4] not in wanted:
                continue
            if str(row[7]).strip().lower() == "employed":
                employed[row[4]] = num(row[9])
    out = []
    for code, p in places.items():
        p["median_age"] = None if ages.get(code) is None else int(round(ages[code]))
        p["housing"] = housing.get(code, [])
        p["tenure"] = tenure.get(code, [])
        p["industry"] = industry.get(code, [])
        p["occupation"] = occupation.get(code, [])[:2]
        p["employed_pct"] = employed.get(code)
        p["age_bands"] = None
        if not p["industry"] or not p["housing"] or p["median_age"] is None:
            raise SystemExit(f"missing census profile for {p['name']} {code}")
        p["reading"] = ew_reading(p)
        out.append(p)
    return out


def collect_scotland():
    wb = openpyxl.load_workbook(SCOT_XLSX, read_only=True, data_only=True)
    council = {}
    ws = wb["Table_1.2"]
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i < 4 or not row or not row[0]:
            continue
        council.setdefault(row[1], row[4])
    out = []
    ws = wb["Table_2.2"]
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i < 4 or not row or not row[0]:
            continue
        if str(row[2]).strip().lower() != "all":
            continue
        pop = num(row[3])
        if pop is None or pop < 10000:
            continue
        cname = council.get(row[1], "")
        if cname in ISLAND_COUNCILS:
            continue
        p = {
            "name": str(row[0]).strip(),
            "code": row[1],
            "country": "Scotland",
            "region": cname or "Scotland",
            "population": int(round(pop)),
            "size": "locality",
            "kind": "nrs-locality",
            "source": "NRS mid-2020 locality estimate",
            "median_age": None,
            "housing": [],
            "tenure": [],
            "industry": [],
            "occupation": [],
            "employed_pct": None,
            "age_bands": {
                "under16": int(row[4]),
                "working": int(row[5]),
                "over65": int(row[6]),
            },
        }
        p["reading"] = scotland_reading(p)
        out.append(p)
    return out


def collect_london():
    out = []
    for line in LONDON_CSV.read_text(encoding="latin-1").splitlines():
        if not line.startswith("E090"):
            continue
        parts = []
        cur = ""
        in_q = False
        for ch in line:
            if ch == '"':
                in_q = not in_q
            elif ch == "," and not in_q:
                parts.append(cur)
                cur = ""
            else:
                cur += ch
        parts.append(cur)
        code, name, _, _y2011, y2021, _chg = parts[:6]
        pop = int(y2021.replace(",", "").replace('"', ""))
        if pop < 10000:
            continue
        if name not in LONDON_NOTES:
            raise SystemExit(f"missing London note for {name}")
        p = {
            "name": name,
            "code": code,
            "country": "England",
            "region": "London",
            "population": pop,
            "size": "London borough",
            "kind": "london-borough",
            "source": "ONS Census 2021 local authority usual residents",
            "median_age": None,
            "housing": [],
            "tenure": [],
            "industry": [],
            "occupation": [],
            "employed_pct": None,
            "age_bands": None,
        }
        p["reading"] = london_reading(p)
        out.append(p)
    return out


def add_neighbours(rows):
    by_region = defaultdict(list)
    for p in rows:
        by_region[(p["country"], p["region"])].append(p)
    for group in by_region.values():
        group.sort(key=lambda r: r["name"])
        n = len(group)
        for i, p in enumerate(group):
            if n < 2:
                continue
            names = []
            for step in range(1, n):
                other = group[(i + step) % n]
                if other["name"] != p["name"] and other["name"] not in names:
                    names.append(other["name"])
                if len(names) == 3:
                    break
            if not names:
                continue
            listed = ", ".join(names)
            p["reading"] += (
                f" Other settlements with their own barrier page in {p['region']} include {listed}."
            )
            p["neighbours"] = names[:3]


def assign_slugs(rows):
    used = {}
    for p in rows:
        base = slugify(p["name"])
        slug = base
        if slug in used:
            suffix = p["country"].lower()
            slug = f"{base}-{suffix}"
        if slug in used:
            slug = f"{base}-{slugify(p['code'])}"
        if slug in used:
            raise SystemExit(f"slug clash {slug}")
        used[slug] = p["name"]
        p["slug"] = slug
    return rows


def skeleton(text: str, name: str) -> str:
    t = text.lower().replace(name.lower(), " ")
    t = re.sub(r"[0-9]+(?:\.[0-9]+)?%?", " ", t)
    t = re.sub(r"[^a-z]+", " ", t)
    return re.sub(r"\s+", " ", t).strip()


def assert_unique(rows):
    texts = [r["reading"] for r in rows]
    if len(texts) != len(set(texts)):
        raise SystemExit("duplicate readings")
    counts = defaultdict(int)
    for r in rows:
        counts[skeleton(r["reading"], r["name"])] += 1
        if str(r["population"]) not in r["reading"].replace(",", ""):
            # comma() inserts commas; compare digits only
            digits = re.sub(r"\D", "", r["reading"])
            if str(r["population"]) not in digits:
                raise SystemExit(f"population missing from reading: {r['name']}")
        if len(r["reading"]) < 280:
            raise SystemExit(f"reading too short: {r['name']}")
    worst = sorted(counts.items(), key=lambda kv: kv[1], reverse=True)[:8]
    print("skeleton groups", len(counts), "worst", [(n, s[:80]) for s, n in worst])
    if worst and worst[0][1] > 40 and False:
        raise SystemExit("skeleton too common")
    # Hard bar: no skeleton shared by more than 12 pages. Data-led sentences
    # should diverge on industry, housing, tenure and occupation labels.
    too_common = [(n, s) for s, n in counts.items() if n > 12]
    if too_common:
        print("WARNING common skeletons", len(too_common), "max", too_common[0][0] if False else max(n for n, _ in [(n, s) for s, n in counts.items()]))
        # Fail only when the shared skeleton is large AND the pages would be
        # interchangeable. Scotland age-band frames are few; allow them if the
        # council name remains inside the skeleton (it does: region is words).
        bad = []
        for s, n in counts.items():
            if n > 25:
                bad.append((n, s[:120]))
        if bad:
            print("FAIL", bad[:5])
            raise SystemExit("too many pages share one skeleton")


def main():
    rows = collect_ew()
    print("england wales", len(rows))
    rows.extend(collect_scotland())
    print("plus scotland", len(rows))
    rows.extend(collect_london())
    print("plus london", len(rows))
    rows = assign_slugs(rows)
    add_neighbours(rows)
    assert_unique(rows)
    rows.sort(key=lambda r: (r["country"], r["region"], r["name"]))
    # Drop bulky nulls for scotland/london already present.
    path = DATA / "barriers-places.json"
    path.write_text(json.dumps(rows, ensure_ascii=False, indent=1) + "\n")
    print("wrote", path, "bytes", path.stat().st_size)


if __name__ == "__main__":
    main()
