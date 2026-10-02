#!/usr/bin/env python3
"""Merge barrier manufacturers into website/data/manufacturers.json.

Does not rebuild the catalogue. CAME is the only partner.
Other brands are equipment iComply services or replaces.
Prices are POA. Safe to re-run.
"""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PATH = ROOT / "data" / "manufacturers.json"


def product(slug: str, pid: str, title: str, blurb: str, badge: str = "") -> dict:
    return {
        "id": f"{slug}-{pid}",
        "title": title,
        "blurb": blurb,
        "price": "POA",
        "handle": f"{slug}-{pid}",
        "shopify_product_id": "",
        "image": f"/assets/images/manufacturers/{slug}.jpg",
        "badge": badge,
    }


def entry(
    name: str,
    slug: str,
    blurb: str,
    seo_desc: str,
    seo_keywords: str,
    products: list,
    *,
    partner: bool = False,
    featured: bool = False,
) -> dict:
    row = {
        "name": name,
        "slug": slug,
        "services": ["barriers"],
        "blurb": blurb,
        "seo_title": f"{name} Vehicle Barriers | Service & Quote",
        "seo_desc": seo_desc,
        "seo_keywords": seo_keywords,
        "products": products,
        "featured": featured,
    }
    if partner:
        row["partner"] = True
    return row


# Display names are what by_service stores. They must areaSlug() to `slug`.
# Hörmann is stored as Hormann in by_service because PHP areaSlug('Hörmann') is h-rmann.
BRANDS = [
    entry(
        "CAME",
        "came",
        "iComply Property Services is a CAME partner. New rising-arm barriers are specified from the CAME Gard range: Gard 4 and Gard 8 for straight booms, Gard PT and Gard PX where the arm must fold under a soffit. We survey the private lane, set the safety devices the CAME manual requires, and commission the logic. Other names on the barriers hub are brands we service or replace. They are not partnerships. Quotes are on application after the lane is seen. Attendance is planned from Stockport SK2.",
        "CAME partner for Gard rising-arm barriers. Private lanes, UK towns over 10,000. Price on application.",
        "CAME partner, CAME Gard barrier, Gard 4, Gard 8, Gard PT, rising arm barrier",
        [
            product("came", "gard-4-8", "CAME Gard 4 and Gard 8 rising arm", "Straight-arm CAME Gard cabinets. Boom length and duty decide Gard 4 or Gard 8. Quoted on application after the lane survey.", "Partner"),
            product("came", "gard-pt-px", "CAME Gard PT and Gard PX articulated boom", "Folding CAME boom for a low soffit. Specified only when a straight arm does not clear the structure. Price on application.", "Partner"),
        ],
        partner=True,
        featured=True,
    ),
    entry(
        "FAAC",
        "faac",
        "FAAC barriers we meet in the UK are usually the 615, the 620 or the B680H hybrid. iComply services and replaces them. We are not a FAAC partner. The barrier partnership on this site is CAME. A 620 spare is not a B680H spare. If the lane suits a CAME Gard better than another FAAC cabinet, both options are priced on application.",
        "FAAC 615, 620 and B680H barrier service and replacement. Not a FAAC partnership. Price on application.",
        "FAAC barrier, FAAC 620, FAAC 615, FAAC B680H",
        [
            product("faac", "615-620", "FAAC 615 and 620 barrier", "Electromechanical FAAC cabinets. Parts are identified from the model plate before anything is ordered. Price on application."),
            product("faac", "b680h", "FAAC B680H hybrid barrier", "Hybrid FAAC cabinet with its own spare path, separate from a 620. Price on application."),
        ],
    ),
    entry(
        "BFT",
        "bft",
        "BFT Moovi barriers are serviced as Moovi 30, 50 or 60. The number tracks boom length. iComply repairs and replaces them and does not claim a BFT partnership. Spring balance and limit settings are checked against the real horizontal stop. Price on application.",
        "BFT Moovi 30, 50 and 60 barrier repair and replacement. Price on application.",
        "BFT Moovi barrier, Moovi 30, Moovi 50, Moovi 60",
        [
            product("bft", "moovi-30", "BFT Moovi 30 barrier", "Shorter Moovi arm. Boom, spring and limits matched to that model. Price on application."),
            product("bft", "moovi-50-60", "BFT Moovi 50 and Moovi 60 barrier", "Longer Moovi arms. Wind exposure and spring balance are part of the survey. Price on application."),
        ],
    ),
    entry(
        "Nice",
        "nice",
        "Nice Wide M and Wide L are the rising arms we identify under the Nice badge. iComply services them. We are not a Nice partner. A Wide L is not a Wide M with a longer sticker. Replacement onto CAME Gard is offered when that is the cleaner supported route. Price on application.",
        "Nice Wide M and Wide L barrier service. Not a Nice partnership. Price on application.",
        "Nice Wide barrier, Nice Wide M, Nice Wide L",
        [
            product("nice", "wide-m", "Nice Wide M barrier", "Nice Wide M cabinet and boom, identified before parts are ordered. Price on application."),
            product("nice", "wide-l", "Nice Wide L barrier", "Longer Nice Wide L lane. Not quoted as a Wide M. Price on application."),
        ],
    ),
    entry(
        "Magnetic Autocontrol",
        "magnetic-autocontrol",
        "Magnetic Autocontrol Access and Parking barriers are higher-duty arms, often on wider lanes. Magnetic sits in the wider FAAC group, but the hardware is not interchangeable with a FAAC 620. iComply services the badge on the cabinet and does not claim a Magnetic partnership. Price on application.",
        "Magnetic Autocontrol Access and Parking barrier service. Not a FAAC 620 spare. Price on application.",
        "Magnetic Autocontrol barrier, Magnetic Access, Magnetic Parking barrier",
        [
            product("magnetic-autocontrol", "access", "Magnetic Autocontrol Access barrier", "Access-range cabinet, boom and spring pack identified separately from Parking. Price on application."),
            product("magnetic-autocontrol", "parking", "Magnetic Autocontrol Parking barrier", "Parking-range arm for higher duty. Loop geometry is surveyed for the vehicles that actually use the lane. Price on application."),
        ],
    ),
    entry(
        "Automatic Systems",
        "automatic-systems",
        "Automatic Systems BL barriers are serviced as the boom mechanism. A pay station or ticketing head on the same lane is a different contract and is not absorbed into a barrier call-out. iComply is not an Automatic Systems partner. Where a full replacement is the better route, CAME is the partnership we specify. Price on application.",
        "Automatic Systems BL barrier repair, separate from pay stations. Price on application.",
        "Automatic Systems barrier, BL barrier, BL229",
        [
            product("automatic-systems", "bl", "Automatic Systems BL barrier", "BL boom, safety devices and open/close command. The pay station is out of scope. Price on application."),
            product("automatic-systems", "bl229", "Automatic Systems BL229 barrier", "BL229 identified from the cabinet label before parts. Price on application."),
        ],
    ),
    entry(
        "Beninca",
        "beninca",
        "Beninca EVA.5 and EVA.7 barriers are repaired when the board revision is known. iComply is not a Beninca partner. Water-damaged cabinets are quoted as replacement, with a CAME Gard as the partner alternative. Price on application.",
        "Beninca EVA.5 and EVA.7 barrier repair. Price on application.",
        "Beninca EVA barrier, EVA.5, EVA.7",
        [
            product("beninca", "eva5", "Beninca EVA.5 barrier", "EVA.5 boom, spring or board, matched to that revision. Price on application."),
            product("beninca", "eva7", "Beninca EVA.7 barrier", "EVA.7 lane. Not ordered as an EVA.5. Price on application."),
        ],
    ),
    entry(
        "Roger Technology",
        "roger-technology",
        "Roger Technology Bionik barriers use a brushless motor and a digital controller. iComply connects with the Roger programmer rather than forcing a generic remote. We are not a Roger partner. A dead controller can be priced beside a CAME Gard. Price on application.",
        "Roger Technology Bionik brushless barrier service. Price on application.",
        "Roger Technology barrier, Bionik barrier, BI series barrier",
        [
            product("roger-technology", "bionik", "Roger Technology Bionik barrier", "Brushless Bionik cabinet. Spares follow the BI series on the label. Price on application."),
            product("roger-technology", "programmer", "Roger Technology barrier programmer session", "On-site programming with the Roger tool for that controller. Price on application."),
        ],
    ),
    entry(
        "DEA System",
        "dea-system",
        "DEA STOP and PASS barriers are serviced as separate model families. Board generations do not share one programming lead. iComply is not a DEA partner. A CAME Gard is the comparison we can specify when the cabinet is finished. Price on application.",
        "DEA STOP and PASS barrier repair or replacement. Price on application.",
        "DEA barrier, DEA STOP, DEA PASS",
        [
            product("dea-system", "stop", "DEA STOP barrier", "DEA STOP cabinet identified from the plate before parts. Price on application."),
            product("dea-system", "pass", "DEA PASS barrier", "DEA PASS lane. Not quoted as a STOP. Price on application."),
        ],
    ),
    entry(
        "Gibidi",
        "gibidi",
        "Gibidi PASS barriers are identified before parts are ordered. iComply does not claim a Gibidi partnership and does not pretend every boom is on the van. A CAME replacement is a separate price on application when the spare lead time is the problem.",
        "Gibidi PASS barrier identification and repair. Price on application.",
        "Gibidi PASS barrier, Gibidi barrier repair",
        [
            product("gibidi", "pass", "Gibidi PASS barrier", "PASS model, boom section and limit type confirmed first. Price on application."),
            product("gibidi", "boom", "Gibidi PASS boom", "Replacement boom only when the section matches the cabinet. Price on application."),
        ],
    ),
    entry(
        "Cardin",
        "cardin",
        "Cardin 24-volt barriers are serviced on smaller private car parks. Radio faults are separated from motor faults before a cabinet is condemned. iComply is not a Cardin partner. An obsolete board is priced against a CAME Gard. Price on application.",
        "Cardin 24V barrier repair and radio faults. Price on application.",
        "Cardin barrier, Cardin 24V barrier",
        [
            product("cardin", "barrier", "Cardin 24V barrier", "Board generation checked before a like-for-like repair. Price on application."),
            product("cardin", "receiver", "Cardin radio receiver", "Receiver and fob fault-finding when the loop still opens the lane. Price on application."),
        ],
    ),
    entry(
        "Tau",
        "tau",
        "Tau barriers, including RBLO-style arms, are programmed from the Tau board sticker. A CAME sequence is not written onto a Tau controller. iComply is not a Tau partner. Gearbox failure is compared with a CAME Gard replacement. Price on application.",
        "Tau barrier service. Not programmed as a CAME. Price on application.",
        "Tau barrier, Tau RBLO, Tau barrier repair",
        [
            product("tau", "barrier", "Tau barrier cabinet", "Model taken from the board sticker before programming or parts. Price on application."),
            product("tau", "boom", "Tau barrier boom", "Boom matched to the Tau socket. Not a casual swap with a CAME arm. Price on application."),
        ],
    ),
    entry(
        "King Gates",
        "king-gates",
        "King Gates is often a gate motor and sometimes a short-lane barrier. Powered gates are assessed against BS EN 12453. A rising arm follows the barrier manufacturer's instructions and the site risk assessment. iComply does not claim a King Gates partnership. A true rising arm is specified as CAME. Price on application.",
        "King Gates barriers and gate motors, scoped separately. Price on application.",
        "King Gates barrier, King Gates gate motor, BS EN 12453",
        [
            product("king-gates", "barrier", "King Gates barrier", "Rising-arm scope only, kept separate from a swinging-gate test. Price on application."),
            product("king-gates", "gate-motor", "King Gates gate motor", "Powered-gate motor service under gate safety practice, not a barrier checklist. Price on application."),
        ],
    ),
    entry(
        "Hörmann",
        "hormann",
        "Hörmann SH barriers are industrial rising arms. They are not Hörmann dock levellers or sectional doors, and the spares are not shared. iComply is not a Hörmann partner. A CAME Gard swap is only offered when the measured lane fits that arm. Price on application.",
        "Hörmann SH barrier service, distinct from Hörmann doors. Price on application.",
        "Hormann barrier, Hörmann SH barrier, industrial barrier",
        [
            product("hormann", "sh", "Hörmann SH barrier", "SH barrier cabinet, boom and safety devices. Dock equipment is a different visit. Price on application."),
            product("hormann", "boom", "Hörmann SH boom", "Replacement boom for the SH model on the label. Price on application."),
        ],
    ),
    entry(
        "ELKA",
        "elka",
        "ELKA ES barriers are specified by boom length, including long ES arms for wide lanes. A short-lane spring is not fitted to a long arm. iComply is not an ELKA partner. A CAME Gard is not forced onto a lane that is too wide for it. Price on application.",
        "ELKA ES barrier service by boom length. Price on application.",
        "ELKA barrier, ELKA ES, ELKA ES 80",
        [
            product("elka", "es", "ELKA ES barrier", "ES model matched to the measured boom length and footing. Price on application."),
            product("elka", "es-long", "ELKA long-lane ES barrier", "Longer ES arm, spring pack and loop for the whole vehicle. Price on application."),
        ],
    ),
    entry(
        "Ditec",
        "ditec",
        "Ditec Qik barriers are serviced from the Ditec label. Being in the ASSA ABLOY automation family does not make the barrier a door-lock job, and it is not the CAME partnership. iComply can repair the Qik or replace it with a CAME Gard when asked to standardise. Both prices are on application.",
        "Ditec Qik barrier repair. Not an ASSA door partnership. Price on application.",
        "Ditec Qik barrier, Ditec barrier repair",
        [
            product("ditec", "qik", "Ditec Qik barrier", "Qik cabinet and board from the Ditec label. Price on application."),
            product("ditec", "boom", "Ditec Qik boom", "Boom for that Qik model. Not an ASSA door part. Price on application."),
        ],
    ),
    entry(
        "Fadini",
        "fadini",
        "Fadini Bayt 980 barriers are older Italian arms. Parts are confirmed for that socket before a boom is promised. A non-matching arm is refused. iComply is not a Fadini partner. The supportable replacement is a CAME Gard. Price on application.",
        "Fadini Bayt 980 barrier repair where parts exist. Price on application.",
        "Fadini Bayt barrier, Bayt 980",
        [
            product("fadini", "bayt-980", "Fadini Bayt 980 barrier", "Bayt 980 identification and repair when the part still exists. Price on application."),
            product("fadini", "boom", "Fadini Bayt boom", "Boom only when the socket matches. Price on application."),
        ],
    ),
    entry(
        "Centurion",
        "centurion",
        "Centurion SECTOR barriers use battery-backed low-voltage logic. A flat battery can look like a dead motor, so both are tested. iComply is not a Centurion partner. Spares lead time is part of the quote. A CAME Gard is priced when one supported range is the goal. Price on application.",
        "Centurion SECTOR barrier battery and motor diagnosis. Price on application.",
        "Centurion SECTOR barrier, Centurion barrier",
        [
            product("centurion", "sector", "Centurion SECTOR barrier", "SECTOR cabinet, battery and motor tested separately. Price on application."),
            product("centurion", "battery", "Centurion SECTOR battery", "Battery and charging check before the gearbox is condemned. Price on application."),
        ],
    ),
    entry(
        "Genius",
        "genius",
        "Genius Rainbow barriers are in the FAAC group and are still not a FAAC 620. Boards, booms and receivers stay with the Genius label. iComply is not a Genius partner. When support for that revision has ended, a CAME Gard is the partner replacement. Price on application.",
        "Genius Rainbow barrier service. Not a FAAC 620. Price on application.",
        "Genius Rainbow barrier, Genius barrier repair",
        [
            product("genius", "rainbow", "Genius Rainbow barrier", "Rainbow cabinet identified from the board, not from the FAAC group name. Price on application."),
            product("genius", "receiver", "Genius Rainbow receiver", "Receiver and boom for that Rainbow revision. Price on application."),
        ],
    ),
    entry(
        "SEA",
        "sea",
        "SEA Sprint and SEA Storm are different duties. Storm is the heavier of the two. iComply records which model is installed before changing loops or readers. A Sprint is not sped up to do an HGV job. We are not an SEA partner. Price on application.",
        "SEA Sprint and Storm barrier service. Price on application.",
        "SEA barrier, SEA Sprint, SEA Storm",
        [
            product("sea", "sprint", "SEA Sprint barrier", "Sprint cabinet for the duty it was built for. Price on application."),
            product("sea", "storm", "SEA Storm barrier", "Heavier Storm lane. Not quoted as a Sprint. Price on application."),
        ],
    ),
    entry(
        "V2",
        "v2",
        "V2 ZARISS barriers are identified from the label and the board before parts are ordered. iComply does not fold V2 into CAME or FAAC. If the label is missing, we photograph the board rather than guess. A CAME Gard is the partner alternative when identification fails. Price on application.",
        "V2 ZARISS barrier identification and service. Price on application.",
        "V2 barrier, V2 ZARISS",
        [
            product("v2", "zariss", "V2 ZARISS barrier", "ZARISS label and controller confirmed before parts. Price on application."),
            product("v2", "board", "V2 ZARISS board identification", "Board photograph and identification when the label is missing. Price on application."),
        ],
    ),
    entry(
        "Aprimatic",
        "aprimatic",
        "Aprimatic ZT barriers differ by boom length and board. iComply is not an Aprimatic dealer. Handset faults are tested against the safety inputs before another remote is added. A CAME replacement is a separate quote. Price on application.",
        "Aprimatic ZT barrier repair. Price on application.",
        "Aprimatic barrier, Aprimatic ZT",
        [
            product("aprimatic", "zt", "Aprimatic ZT barrier", "ZT length and board matched before a parts visit. Price on application."),
            product("aprimatic", "handset", "Aprimatic ZT handset", "Handset or receiver work only when the receiver still learns. Price on application."),
        ],
    ),
    entry(
        "Proteco",
        "proteco",
        "Proteco Strike barriers are quoted from the model on the cabinet, not from a catalogue price. iComply is not a Proteco partner. An unsupported Strike board is not patched forever. The next service cycle can be a CAME Gard. Price on application for either path.",
        "Proteco Strike barrier repair. Price on application.",
        "Proteco Strike barrier, Proteco barrier",
        [
            product("proteco", "strike", "Proteco Strike barrier", "Strike model repair when the board is still supported. Price on application."),
            product("proteco", "boom", "Proteco Strike boom", "Boom for that Strike model. Price on application."),
        ],
    ),
    entry(
        "Life Home Integration",
        "life-home-integration",
        "Life Supra is a vehicle rising-arm barrier. It is not a fire-alarm, AOV or life-safety product, and the similar name causes the wrong trade to be booked. iComply reads the Life label and orders Life parts. We are not a Life partner. A new rising arm is specified as CAME when replacement is the instruction. Price on application.",
        "Life Supra vehicle barrier. Not a fire or AOV product. Price on application.",
        "Life Supra barrier, Life barrier, vehicle barrier",
        [
            product("life-home-integration", "supra", "Life Supra barrier", "Life Supra vehicle barrier, booked separately from fire systems. Price on application."),
            product("life-home-integration", "boom", "Life Supra boom", "Supra boom ordered as a Life part. Price on application."),
        ],
    ),
    entry(
        "DoorHan",
        "doorhan",
        "DoorHan Barrier PRO is serviced as a barrier, not as a DoorHan sectional door. The springs are different parts. iComply is not a DoorHan partner. Footings and loops on older imports are checked rather than trusted. CAME Gard is the partner range when one supported cabinet is wanted. Price on application.",
        "DoorHan Barrier PRO service, separate from DoorHan doors. Price on application.",
        "DoorHan barrier, Barrier PRO",
        [
            product("doorhan", "barrier-pro", "DoorHan Barrier PRO", "Barrier PRO cabinet, not the door range. Price on application."),
            product("doorhan", "boom", "DoorHan Barrier PRO boom", "Boom and spring for the barrier, not a door torsion spring. Price on application."),
        ],
    ),
    entry(
        "LiftMaster",
        "liftmaster",
        "LiftMaster on a UK site is often a gate or garage operator, not a rising-arm barrier. iComply confirms which machine is installed before quoting. We are not a LiftMaster partner. New rising arms are specified as CAME unless you instruct otherwise. Parts lead time is stated rather than guessed. Price on application.",
        "LiftMaster barrier or gate operator identification. Price on application.",
        "LiftMaster barrier, LiftMaster gate operator",
        [
            product("liftmaster", "barrier", "LiftMaster barrier operator", "Confirmed barrier operator, with parts lead time in the quote. Price on application."),
            product("liftmaster", "gate", "LiftMaster gate operator", "Gate operator scope, kept separate from a rising-arm specification. Price on application."),
        ],
    ),
]


def main() -> None:
    data = json.loads(PATH.read_text(encoding="utf-8"))
    catalog = data["catalog"]
    taken = []
    for row in BRANDS:
        slug = row["slug"]
        if slug in catalog:
            taken.append(slug)
    if taken:
        raise SystemExit("slug already in catalogue: " + ", ".join(taken))

    names = []
    for row in BRANDS:
        catalog[row["slug"]] = row
        # by_service label must slug to the catalogue key under PHP areaSlug.
        label = "Hormann" if row["slug"] == "hormann" else row["name"]
        names.append(label)

    # DoorKing already exists for door entry. Append barriers. Keep the enriched blurb.
    door = catalog.get("doorking")
    if not door:
        raise SystemExit("doorking catalogue entry missing")
    services = list(door.get("services") or [])
    if "barriers" not in services:
        services.append("barriers")
    door["services"] = services
    sentence = (
        " On vehicle gates already installed, we service DoorKing operators as gate control. "
        "That is not a DoorKing partnership. The rising-arm partnership is CAME."
    )
    blurb = door.get("blurb") or ""
    if "rising-arm partnership is CAME" not in blurb:
        door["blurb"] = blurb.rstrip() + sentence
    existing_ids = {p.get("id") for p in door.get("products") or []}
    extra = [
        product(
            "doorking",
            "vehicle-gate-service",
            "DoorKing vehicle gate service",
            "Service visit for a DoorKing vehicle gate operator already on a private site. Not a rising-arm barrier. Price on application.",
        ),
        product(
            "doorking",
            "gate-safety-check",
            "DoorKing gate safety device check",
            "Safety-device check on a DoorKing gate, scoped separately from door-entry handsets. Price on application.",
        ),
    ]
    products = list(door.get("products") or [])
    for item in extra:
        if item["id"] not in existing_ids:
            products.append(item)
    door["products"] = products
    if "DoorKing" not in names:
        names.append("DoorKing")

    data.setdefault("by_service", {})["barriers"] = names
    data.setdefault("images_by_service", {})["barriers"] = [row["slug"] for row in BRANDS] + ["doorking"]
    data.setdefault("seo_keywords", {})["barriers"] = (
        "vehicle barrier, rising arm barrier, CAME partner, CAME Gard, barrier servicing, "
        "private car park barrier, UK towns over 10000, price on application"
    )

    for row in catalog.values():
        if "barriers" not in (row.get("services") or []):
            continue
        if row["slug"] != "doorking":
            for item in row.get("products") or []:
                if str(item.get("price", "")).startswith("From"):
                    raise SystemExit(f"From-price on {row['slug']} {item.get('id')}")
        if row.get("partner") and row["slug"] != "came":
            raise SystemExit("partner set on " + row["slug"])

    if not catalog["came"].get("partner"):
        raise SystemExit("CAME partner flag missing")

    PATH.write_text(json.dumps(data, indent=4, ensure_ascii=False), encoding="utf-8")
    print(f"barriers brands in by_service: {len(names)}")
    print("partner:", catalog["came"]["name"])


if __name__ == "__main__":
    main()
