#!/usr/bin/env python3
"""Build the Manchester electrical P0 content spec (89 intents × GM-core 60).

Writes website/assets/matrix/manchester-electrical-p0.json
and inserts any missing keyword hubs into website/data/keywords.json.
"""
from __future__ import annotations

import csv
import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
CSV = Path("/home/ubuntu/.cursor/projects/workspace/uploads/MANCHESTER-ELECTRICAL-INTENTS_b24c.csv")
OUT = ROOT / "website/assets/matrix/manchester-electrical-p0.json"
KEYWORDS = ROOT / "website/data/keywords.json"

NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"
PHONE = "07517806082"

TOWNS = [
    ("altrincham", "Altrincham", "Trafford market town with terraces, flats and retail units around the interchange"),
    ("ashton-under-lyne", "Ashton-under-Lyne", "Tameside town with a market, older housing and commercial streets off the ring road"),
    ("atherton", "Atherton", "Wigan-side town of brick terraces, semis and small industrial yards"),
    ("bolton", "Bolton", "large borough of mills, terraces, new housing and town-centre commercial stock"),
    ("bramhall", "Bramhall", "Stockport suburb of semis and detached houses with a village centre"),
    ("bury", "Bury", "borough of stone terraces, retail parks and mixed suburban housing"),
    ("cadishead", "Cadishead", "Salford west village beside Irlam, with houses and low industrial units"),
    ("chadderton", "Chadderton", "Oldham district of inter-war housing, shops and light industry"),
    ("cheadle", "Cheadle", "Stockport suburb of semis, parades of shops and small offices"),
    ("cheadle-hulme", "Cheadle Hulme", "residential Stockport area with a station, schools and family housing"),
    ("chorlton", "Chorlton", "south Manchester neighbourhood of terraces, conversions and busy high-street units"),
    ("denton", "Denton", "Tameside town of terraces, estates and industrial estates toward Hyde"),
    ("didsbury", "Didsbury", "south Manchester suburb of larger houses, flats and village shops"),
    ("droylsden", "Droylsden", "Tameside town tight against east Manchester, with terraces and local shops"),
    ("dukinfield", "Dukinfield", "Tameside town of terraces and industrial buildings beside the canal"),
    ("eccles", "Eccles", "Salford town with a centre, older housing and sites toward the ship canal"),
    ("failsworth", "Failsworth", "Oldham-edge town on the Manchester road, terraces and retail units"),
    ("farnworth", "Farnworth", "Bolton town of terraces, estates and local commercial streets"),
    ("hazel-grove", "Hazel Grove", "Stockport suburb along the A6, semis and a long shopping street"),
    ("heywood", "Heywood", "Rochdale town of terraces, estates and industrial units"),
    ("horwich", "Horwich", "west Bolton town beside the moor, with housing and a retail park"),
    ("hyde", "Hyde", "Tameside town of terraces, a centre and industrial sites"),
    ("irlam", "Irlam", "Salford west town with housing estates and employment sites"),
    ("kearsley", "Kearsley", "Bolton town of stone terraces between Farnworth and the Irwell"),
    ("lees", "Lees", "Oldham village edge with stone cottages, semis and local shops"),
    ("leigh", "Leigh", "Wigan town of terraces, a centre and suburban estates"),
    ("little-lever", "Little Lever", "small Bolton town of housing between Moses Gate and Radcliffe"),
    ("littleborough", "Littleborough", "Rochdale Pennine town of stone houses and a high street"),
    ("manchester", "Manchester", "the primary hub city, from the centre out to Chorlton, Didsbury and Wythenshawe"),
    ("marple", "Marple", "Stockport town toward the Goyt, with stone houses and a centre"),
    ("middleton", "Middleton", "Rochdale town north of Manchester, estates, terraces and local industry"),
    ("milnrow", "Milnrow", "Rochdale town beside the M62, stone housing and small commercial units"),
    ("mossley", "Mossley", "Tameside Pennine town of hillside terraces and mills"),
    ("oldham", "Oldham", "borough of terraces, town-centre commercial stock and mill conversions"),
    ("pendlebury", "Pendlebury", "Salford area between Swinton and the Irwell, housing and local shops"),
    ("prestwich", "Prestwich", "Bury suburb of semis, flats and a long high street toward Manchester"),
    ("radcliffe", "Radcliffe", "Bury town of terraces, estates and sites along the Irwell"),
    ("rochdale", "Rochdale", "borough town of stone buildings, housing estates and a commercial centre"),
    ("romiley", "Romiley", "Stockport town toward Marple, semis and a compact centre"),
    ("royton", "Royton", "Oldham town of terraces and suburban housing north of the town centre"),
    ("saddleworth", "Saddleworth", "Oldham Pennine villages, stone houses and converted mills"),
    ("sale", "Sale", "Trafford town of semis, a centre and waterside housing"),
    ("salford", "Salford", "city of quays, terraces, estates and commercial stock beside Manchester"),
    ("shaw", "Shaw", "Oldham and Rochdale edge town of terraces and local industry"),
    ("stalybridge", "Stalybridge", "Tameside town of stone streets, a centre and hillside housing"),
    ("stockport", "Stockport", "the borough where the iComply base sits, from the centre out to Offerton and the Heatons"),
    ("stretford", "Stretford", "Trafford town beside Old Trafford, terraces, flats and retail"),
    ("swinton", "Swinton", "Salford town of civic buildings, terraces and suburban streets"),
    ("tameside", "Tameside", "the borough covering Ashton, Hyde, Stalybridge, Dukinfield, Denton and Droylsden"),
    ("trafford", "Trafford", "the borough covering Altrincham, Sale, Stretford and Urmston"),
    ("tyldesley", "Tyldesley", "Wigan town of terraces between Atherton and the East Lancs road"),
    ("uppermill", "Uppermill", "Saddleworth village with stone buildings along the canal"),
    ("urmston", "Urmston", "Trafford town of semis and a centre west of Stretford"),
    ("walkden", "Walkden", "Salford town of housing estates and a shopping centre"),
    ("westhoughton", "Westhoughton", "Bolton town of housing between the M61 and Atherton"),
    ("whitefield", "Whitefield", "Bury suburb on the Manchester road, semis and parades of shops"),
    ("wigan", "Wigan", "borough town of terraces, a centre and wide suburban housing"),
    ("withington", "Withington", "south Manchester neighbourhood of terraces, conversions and student lets"),
    ("worsley", "Worsley", "Salford village area of older houses and newer estates"),
    ("wythenshawe", "Wythenshawe", "south Manchester district of estates, a centre and commercial units"),
]

BLOCKS = [
    "An enquiry for {intent} in {town} starts with the building. Say whether it is a house, a flat, an HMO, a shop, an office or a light-industrial unit, and whether people will still be inside when the electrician attends. {local} The scope for {intent} is written down before a date is offered. The quote is price on application. Travel from {nap} is part of that written quote. Call {phone} with the {town} postcode.",
    "Landlords and letting agents ask for {intent} in {town} when a tenancy is changing, a licence file is thin, or a report has come back unsatisfactory. We read what you already hold, including any previous certificate, and we say what the next visit can and cannot prove. Remedial work, if the inspection finds it, is quoted separately and stays price on application. Nothing on this page is a fixed call-out fee.",
    "Commercial and facilities enquiries for {intent} in {town} need access notes: which floor, whether a board is in a locked riser, and whether the supply can be isolated. Occupied offices, shops and warehouses are booked around the people who work there. The electrician leaves the certificate, test sheet or written note with the person who instructed the visit. iComply arranges that from Stockport rather than from a national script.",
    "Domestic work described as {intent} in {town} covers the fixed installation that is in the agreed scope: consumer unit, circuits, accessories and any rewire sections that were written down. We do not treat a photograph of a fuse board as a full survey. If the property needs a longer look, the quote says so before anyone is dispatched. Homeowners and landlords get the same rule: price on application, scope first.",
    "Faults that lead people to search {intent} in {town} include lost power, a device that will not reset, a burning smell, or a circuit that trips when a particular load is used. If it is safe to do so, leave the affected circuit off and keep clear of water and damaged accessories. The first task on site is to make the installation safe. A permanent repair is a separate line in the quote when the fault is not a simple local failure.",
    "Board changes linked to {intent} in {town} are specified against the installation that is there now, not against a generic consumer-unit package. That includes the enclosure, the way circuits are identified, and whether RCD or RCBO protection and surge protection are required for that board. Labelling and testing happen before the board is left energised. The figure is price on application after we have seen the board or clear photographs of it.",
    "Rewire and installation searches under {intent} in {town} are scoped against the containment, the finishes and the rooms that are actually in the job. A full house rewire is not the same visit as a kitchen circuit or a single new socket. Where the work is notifiable under Part P of the Building Regulations, notification sits inside the agreed scope. We do not describe that notification as a scheme badge.",
    "Testing and certification for {intent} in {town} follows BS 7671 for the fixed installation. An Electrical Installation Condition Report, an Electrical Installation Certificate or a Minor Electrical Installation Works Certificate is used only when that is the right form for the work that was done. Portable appliance testing is a different exercise and is not a substitute for an installation report. Reports name the property in {town}.",
    "Three-phase and higher-load enquiries for {intent} in {town} are common on workshops, small factories, commercial kitchens and some EV or plant supplies. We confirm the supply that is already in the building before talking about a new board or a new circuit. The quote states what is included and what the distribution network operator would have to change. That distinction is price on application and is not guessed from the street.",
    "EV charger, PAT and landlord-file work under the {intent} heading in {town} is booked as its own scope. A charger installation is not an EICR. A PAT round is not a rewire. A landlord electrical file may need the report plus any remedial evidence the report itself asks for. Each of those is quoted separately, price on application, after the appliance count or the property type is known.",
    "On the day of {intent} in {town} the electrician works to the written scope, isolates where the test method requires it, and reinstates circuits that were part of the visit. Power may be off for a period. Tell tenants or staff beforehand. If something outside the scope is found, it is photographed or noted and quoted afterwards. It is not added to the bill without an instruction.",
    "Coverage for {intent} reaches {town} from the Stockport base at {nap}. Manchester is the primary hub city for this electrical pack, and {town} is one of the sixty Greater Manchester places on the matrix. If the site sits just outside the {town} boundary, say so. Nearby postcodes are still quoted as one visit when the travel is sensible. The phone number is {phone}.",
    "What we will not do on a {intent} page for {town} is invent a membership. iComply does not claim NICEIC, NAPIT or Elecsa registration. If you need a named scheme contractor and we cannot show that registration, we say so before you book. {intent} in {town} is quoted price on application after the scope is written.",
    "Paperwork after {intent} in {town} is for the instructing client: the report or certificate, any schedule of test results that belongs with it, and a short note of what was outside the visit. Landlords can pass that pack to an agent. Facilities managers can file it against the building. We do not keep the only copy. Ask for a duplicate if the original was issued to someone else on site.",
    "A useful {intent} enquiry from {town} includes the postcode, a contact who can open the building, the age of the installation if you know it, and whether there has been a flood, a fire, or an unsatisfactory report. Photographs of the consumer unit, any damaged accessory, and the supply head help the scope. They do not replace the look on site when the work is a test or a rewire.",
    "Related electrical work sits beside {intent}. The parent service page is the electrical hub, and the related guide linked from this page is {related}. Area pages for {town} list the other services in that town. Use those links rather than a second copy of the same enquiry. If you need several trades, each one is quoted on its own scope. This page stays on the electrical work.",
]

INTRO = (
    "People searching for {intent} in {town} are usually a landlord, a managing agent or a facilities lead who wants the electrical visit scoped before anyone attends. "
    "iComply Property Services arranges qualified electricians for that work from {nap}. "
    "Call {phone} with the postcode in {town}, the property type, and what has failed or which certificate you need. "
    "The quote for {intent} is price on application. This page does not publish a catalogue fee."
)

CLOSER = (
    "To book {intent} in {town}, call {phone} or use the contact form and include the postcode. "
    "The reply names the scope and the price on application figure before a date is fixed. "
    "The base address, when you need it on a purchase order, is {nap}."
)

FAQS = [
    (
        "Do you cover {town} for {intent}?",
        "Yes. {town} is on the Greater Manchester electrical matrix. Visits are arranged from {nap}. The quote is price on application once the building and the scope are known.",
    ),
    (
        "How is {intent} priced in {town}?",
        "Price on application. Access, the condition of the installation and the agreed scope change the visit, so this page does not print a fixed fee for {intent} in {town}.",
    ),
    (
        "Who carries out {intent} in {town}?",
        "Qualified electricians, with the electrical work judged against BS 7671. Where Part P notification applies, it is part of the scope. iComply does not claim NICEIC, NAPIT or Elecsa membership.",
    ),
    (
        "What should I send before the {town} visit?",
        "The postcode, access notes, and any previous report or photographs of the consumer unit. Say if the building is occupied. We confirm the {intent} scope in writing before attendance.",
    ),
]

ACRONYMS = {
    "Eicr": "EICR",
    "Pat": "PAT",
    "Ev": "EV",
    "Bs": "BS",
    "Nic": "NIC",
    "Hmo": "HMO",
    "Rcbo": "RCBO",
    "Rcd": "RCD",
}


def display_name(slug: str) -> str:
    name = slug.replace("-", " ")
    name = re.sub(r"\b3 phase\b", "3 Phase", name)
    name = name.title() if not name.startswith("3 ") else "3 Phase" + name[7:].title()
    if slug.startswith("3-"):
        name = "3" + name[1:]
    for src, dst in ACRONYMS.items():
        name = re.sub(rf"\b{src}\b", dst, name)
    name = name.replace("24 Hour", "24 Hour")
    return name


def angle_for(slug: str, name: str, notes: str) -> str:
    notes = re.sub(r"\s+", " ", notes).strip()
    lead = {
        "nic-electrician": (
            f"{name} is a scheme-search phrase. iComply does not claim NICEIC, NIC, NAPIT or Elecsa membership, and this page does not show a scheme badge. "
            "Ask what registration the attending electrician can show. If you need a named scheme contractor and that registration cannot be shown, we say so before you book."
        ),
        "cheap-electrician": (
            f"{name} is a price-led search. We do not publish a cheap fixed fee, and we do not win the job by skipping tests or certificates. "
            "The quote is price on application after the scope is clear. A lower number that leaves the installation untested is not what this page is selling."
        ),
        "part-p-electrician": (
            f"{name} refers to Part P of the Building Regulations for notifiable electrical work in dwellings. "
            "Notification, where it applies, is part of the agreed scope. It is not a claim that iComply holds a competent-person scheme registration."
        ),
        "part-p-certified": (
            f"{name} is read here as Part P and Building Regulations wording for notifiable domestic electrical work. "
            "The certificate that is issued matches the work completed. We do not invent a scheme membership to support the phrase."
        ),
        "bs-7671-electrician": (
            f"{name} means electrical work judged against BS 7671, the IET Wiring Regulations, including inspection, testing and the certificates that match the job. "
            "Quoting the standard is not a scheme badge."
        ),
        "eicr": (
            f"{name} on this matrix is an Electrical Installation Condition Report on the fixed wiring. "
            "The published EICR list price is £249 for a typical North West 6-bed HMO. Other domestic sizes and commercial EICRs stay price on application. Remedials are quoted after the report."
        ),
    }.get(slug)
    if lead is None:
        if "cost" in slug or "price" in slug or slug.startswith("cheap"):
            lead = (
                f"{name} is a cost search. The figure is price on application after we know the property, the access and the scope. "
                "This page does not invent a pound rate, a call-out fee or a package price."
            )
        elif "three-phase" in slug or slug.startswith("3-phase"):
            lead = (
                f"{name} is three-phase electrical work: supply, board, circuit or test as the scope says. "
                "Workshops, commercial plant and some EV loads are typical. The existing supply is confirmed before any upgrade is quoted, price on application."
            )
        elif "rewire" in slug:
            lead = (
                f"{name} is rewire work on the fixed installation, full or partial as agreed. "
                "Containment, finishes and which rooms are in the job are written down first. The quote is price on application. Notifiable work is notified where Part P requires it."
            )
        elif "eicr" in slug or "condition-report" in slug:
            lead = (
                f"{name} is inspection and testing of the fixed electrical installation, with the report issued to the instructing client. "
                "Codes and any remedials are explained from the written report. Pricing other than the published HMO list on the main EICR page is price on application."
            )
        elif "pat" in slug or "portable-appliance" in slug:
            lead = (
                f"{name} is in-service inspection and testing of portable appliances, with labelling and a register. "
                "It is not an EICR. The quote is price on application after the appliance count is known. Failed items are quoted as separate remedials."
            )
        elif "ev" in slug or "charger" in slug:
            lead = (
                f"{name} is the installation of electric-vehicle charging equipment on a supply that can take it. "
                "The survey checks the board, the earthing and the cable route. The quote is price on application. Charger hardware is only specified when it suits the installation."
            )
        elif "landlord" in slug or "hmo" in slug:
            lead = (
                f"{name} is landlord electrical work: the report, the certificate and any remedials the file needs. "
                "England private-rented electrical safety duties should be read from current official guidance. This page is the commercial route to book the visit, price on application."
            )
        elif "consumer" in slug or "fuse" in slug or "board" in slug or "rcbo" in slug:
            lead = (
                f"{name} is consumer-unit or fuse-board work: replacement, upgrade or a specified device layout. "
                "The board is identified, labelled and tested before it is left energised. The quote is price on application after the existing board is known."
            )
        elif "manchester" in slug or slug.startswith("electrician-"):
            lead = (
                f"{name} is a place-led search inside this Greater Manchester electrical pack. "
                "Manchester is the primary hub city and the Stockport base is {NAP}. The visit is still scoped to the building, price on application."
            ).replace("{NAP}", NAP)
        else:
            lead = (
                f"{name} is electrical work for homes, landlords and commercial buildings across Greater Manchester. "
                "The visit is scoped against BS 7671 and quoted price on application. Qualified electricians attend from the Stockport base."
            )
    del notes
    return f"{lead} The commercial line for {name} does not change by town: price on application, scope in writing, paperwork to the instructing client."


def main() -> None:
    rows = list(csv.DictReader(CSV.open()))
    p0 = [r for r in rows if r["intent_tier"] == "P0"]
    if len(p0) != 89:
        raise SystemExit(f"expected 89 P0 rows, got {len(p0)}")
    intents = []
    for i, row in enumerate(p0):
        slug = row["slug"].strip()
        name = display_name(slug)
        related = p0[(i + 1) % len(p0)]["slug"].strip()
        if related == slug:
            related = "eicr"
        intents.append({
            "slug": slug,
            "name": name,
            "kind": row["kind"].strip(),
            "status": row["status"].strip(),
            "related": related,
            "angle": angle_for(slug, name, row["notes"].strip()),
        })
    towns = [{"slug": s, "name": n, "fact": f} for s, n, f in TOWNS]
    if len(towns) != 60:
        raise SystemExit(f"expected 60 towns, got {len(towns)}")
    spec = {
        "nap": NAP,
        "phone": PHONE,
        "service_path": "/pages/services/electrical",
        "images": [
            "/assets/images/services/electrical.jpg",
            "/assets/images/services/electrical-photo.jpg",
            "/assets/images/keywords/electrical-installation.jpg",
            "/assets/images/keywords/eicr.jpg",
            "/assets/images/keywords/emergency-electrician.jpg",
            "/assets/images/keywords/consumer-unit-upgrade.jpg",
            "/assets/images/keywords/pat-testing.jpg",
            "/assets/images/keywords/ev-charger-installation.jpg",
        ],
        "intro": INTRO,
        "closer": CLOSER,
        "blocks": BLOCKS,
        "faqs": [{"q": q, "a": a} for q, a in FAQS],
        "towns": towns,
        "intents": intents,
    }
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(json.dumps(spec, ensure_ascii=False, indent=2) + "\n")
    print(f"wrote {OUT} intents={len(intents)} towns={len(towns)} blocks={len(BLOCKS)}")

    # Insert missing keyword records without rewriting the whole catalogue.
    existing = set(re.findall(r'^    "([a-z0-9\-]+)": \{', KEYWORDS.read_text(), re.M))
    missing = [item for item in intents if item["slug"] not in existing]
    if not missing:
        print("keywords.json already has every P0 slug")
        return
    chunks = []
    for item in missing:
        record = {
            "name": item["name"],
            "service": "electrical",
            "related": item["related"] if item["related"] in existing or item["related"] in {m["slug"] for m in missing} else "eicr",
            "intro": item["angle"],
            "body": (
                f"{item['name']} is arranged from {NAP} for Greater Manchester. "
                "The quote is price on application after the scope is written. "
                "Qualified electricians work to BS 7671. "
                "iComply does not claim NICEIC, NAPIT or Elecsa membership."
            ),
            "meta_desc": f"{item['name']} across Greater Manchester. Price on application from Stockport. Call {PHONE}.",
            "focus_points": [
                "Price on application after scope",
                "BS 7671 and Part P notification where it applies",
                "Greater Manchester cover from Stockport",
                "No invented scheme badge",
            ],
            "faq": [
                [f"How is {item['name']} priced?", "Price on application after the building and the scope are known."],
                ["Do you claim NICEIC membership?", "No. Ask what registration the attending electrician can show."],
                ["Where are you based?", NAP],
            ],
        }
        blob = json.dumps(record, ensure_ascii=False, indent=4)
        blob = "\n".join("    " + line if line else line for line in blob.splitlines())
        chunks.append(f'    "{item["slug"]}": {blob}')
    text = KEYWORDS.read_text().rstrip()
    if not text.endswith("}"):
        raise SystemExit("keywords.json did not end with }")
    text = text[:-1].rstrip()
    if text.endswith(","):
        text = text[:-1]
    addition = ",\n" + ",\n".join(chunks) + "\n}\n"
    KEYWORDS.write_text(text + addition)
    print(f"inserted {len(missing)} keyword records")


if __name__ == "__main__":
    main()
