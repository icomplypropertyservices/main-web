#!/usr/bin/env python3
"""Build website/data/job-types-plumbing.json from unique plumbing briefs.

Plumbing-only subset of the sec-water-plumb family (130 jobs).
Does not emit security or water pages.
"""
from __future__ import annotations

import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "bin"))

from plumbing_briefs_a import BRIEFS_A  # noqa: E402
from plumbing_briefs_b import BRIEFS_B  # noqa: E402
from plumbing_briefs_c import BRIEFS_C  # noqa: E402

# Exact plumbing slug set from job-types-sec-water-plumb (family=plumbing).
CANONICAL = [
    "bathroom-plumbing",
    "bathroom-waste-replacement",
    "blocked-waste-support",
    "cold-water-storage-lid",
    "condensate-pipe-re-run",
    "cylinder-drain-down-support",
    "dishwasher-connection",
    "emergency-plumber",
    "frozen-pipe-thaw-support",
    "immersion-heater-replace",
    "kitchen-mixer-replace",
    "kitchen-plumbing",
    "kitchen-waste-trap",
    "landlord-plumbing-call-out",
    "landlord-plumbing-repairs",
    "leak-detection-support",
    "outside-tap-installation",
    "outside-tap-with-isolation",
    "overflow-repair",
    "pipework-replacement",
    "plumbing-services",
    "radiator-installation",
    "shower-mixer-replacement",
    "shower-pump-installation",
    "shower-waste-pump",
    "stopcock-replacement",
    "tap-replacement",
    "toilet-installation",
    "washing-machine-plumbing",
    "water-softener-installation-support",
    "wc-fill-valve-replace",
    "wc-pan-connector",
    "burst-pipe-repair",
    "no-water-call-out",
    "low-water-pressure-visit",
    "internal-stop-tap-replace",
    "rising-main-repair",
    "leaking-tap-repair",
    "dripping-mixer-tap",
    "basin-tap-replacement",
    "bath-tap-replacement",
    "outdoor-tap-isolation",
    "washing-machine-leak-repair",
    "dishwasher-leak-repair",
    "toilet-repair",
    "toilet-cistern-repair",
    "wc-flush-mechanism",
    "blocked-toilet-clearance",
    "blocked-sink-unblock",
    "blocked-bath-waste",
    "blocked-shower-waste",
    "soil-stack-repair",
    "waste-pipe-replacement",
    "bottle-trap-replace",
    "electric-shower-isolation-support",
    "shower-tray-waste",
    "shower-valve-service",
    "bath-waste-replace",
    "basin-waste-replace",
    "overflow-drip-trace",
    "loft-tank-ballvalve",
    "ballvalve-replacement",
    "header-tank-repair",
    "gravity-hot-water-fault",
    "combi-no-hot-water-plumbing",
    "condensate-blockage-clear",
    "radiator-valve-replace",
    "trv-replacement-plumbing",
    "leaking-radiator-valve",
    "pipework-trace-and-repair",
    "copper-pipe-repair",
    "plastic-pipe-repair",
    "underfloor-leak-trace",
    "ceiling-leak-trace",
    "void-property-plumbing-check",
    "hmo-bathroom-plumbing",
    "tenant-damage-plumbing-repair",
    "end-of-tenancy-plumbing-snag",
    "landlord-leak-call-out",
    "emergency-leak-isolation",
    "after-hours-plumber-visit",
    "flexible-hose-replace",
    "appliance-isolation-valve",
    "garden-tap-repair",
    "frost-damage-pipe-repair",
    "lead-pipe-advice",
    "seized-stop-tap-free",
    "mains-water-leak-liaison",
    "internal-mains-repair",
    "kitchen-sink-install-plumbing",
    "utility-sink-plumbing",
    "disabled-wc-plumbing-support",
    "urinal-outlet-repair",
    "commercial-washroom-plumbing",
    "office-kitchen-plumbing",
    "care-home-outlet-repair",
    "school-washroom-plumbing",
    "retail-staff-toilet-repair",
    "landlord-portfolio-plumbing-visit",
    "floorboard-lift-for-leak",
    "boxing-in-pipework",
    "pipe-insulation-after-repair",
    "pressure-reducing-valve",
    "check-valve-replace",
    "scale-build-up-tap-service",
    "kitchen-mixer-install",
    "monobloc-tap-install",
    "pillar-tap-replace",
    "thermostatic-shower-install",
    "power-shower-plumbing",
    "saniflo-fault-support",
    "macerator-repair-support",
    "towel-rail-plumbing",
    "bathroom-radiator-plumbing",
    "second-bathroom-first-fix",
    "loft-conversion-plumbing-first-fix",
    "extension-plumbing-first-fix",
    "kitchen-relocation-plumbing",
    "appliance-reconnect-after-move",
    "tenant-left-taps-dripping",
    "no-hot-water-landlord-visit",
    "communal-bin-store-tap",
    "washer-replace-visit",
    "isolation-valve-seized-free",
    "hot-water-cylinder-plumbing-support",
    "unvented-cylinder-plumbing-support",
    "immersion-isolate-and-replace",
    "overflow-warning-pipe-repair",
    "bath-mixer-replace",
    "shower-diverter-repair",
]

CLUSTERS = {
    "taps": "Tap and mixer repairs use WRAS-listed fittings where a fitting is replaced. We isolate locally when a valve exists, and at the stopcock when it does not.",
    "wastes": "Waste work is the trap, the branch and the joint into the stack or gully. We do not jet the public sewer on these visits.",
    "leaks": "Leak work starts by isolating the circuit that is actually wet, then repairing the failed pipe or joint and checking it dry.",
    "supply": "Supply work is the property side of the water-company stop tap: the internal stopcock, the rising main and outdoor bibs with backflow protection.",
    "hotwater": "Hot-water work on this lane is the cylinder, the feed and the immersion. Gas burner and gas-valve work is a Gas Safe registered engineer, not this page.",
    "heat": "Heating plumbing here is valves, rails and condensate drainage. We do not service the gas boiler.",
    "showers": "Shower plumbing is the valve, the waste and the water feed. Electric-shower electrics stay with an electrician.",
    "appliances": "Appliance plumbing is the isolation valve, the hose and the waste. We do not repair the machine.",
    "toilets": "WC work is the fill, the flush, the pan connector and the close-couple joint. We do not move a soil stack on a repair.",
    "landlord": "Landlord plumbing is the repair that was reported, written up per address. We do not invent a compliance certificate the job does not produce.",
    "firstfix": "First fix is tested pipework and wastes left capped or connected to the agreed point. We do not sign off Building Control.",
    "commercial": "Light-commercial plumbing is the failed outlet in a washroom, tea point or welfare room. It is not a water-hygiene sampling visit.",
    "valves": "Valve and cistern work uses the fitting that matches the pressure and the cistern. We do not cap an overflow to hide a passing valve.",
}


def die(msg: str) -> None:
    print(msg, file=sys.stderr)
    raise SystemExit(1)


def meta_with_poa(name: str, first: str) -> str:
    suffix = " Price on application."
    base = f"{name} across the North West. "
    sentence = first.strip().rstrip(".")
    full = base + sentence + "."
    if len(full) + len(suffix) <= 160:
        return full + suffix
    room = 160 - len(suffix) - len(base) - 1
    window = sentence[: max(room, 0)]
    cut = window
    for sep in (",", ";", ":"):
        if sep in window:
            cut = window.rsplit(sep, 1)[0]
            break
    else:
        cut = window.rsplit(" ", 1)[0]
    cut = cut.rstrip(" .,;:")
    dangling = {"a", "an", "the", "and", "or", "for", "to", "of", "on", "in", "with", "from"}
    while cut and cut.split()[-1].lower() in dangling:
        cut = cut.rsplit(" ", 1)[0]
    if not cut:
        cut = sentence.split(",")[0].strip()
    return base + cut + "." + suffix


def main() -> None:
    briefs = BRIEFS_A + BRIEFS_B + BRIEFS_C
    by_slug = {}
    for row in briefs:
        slug = row["slug"]
        if slug in by_slug:
            die(f"duplicate brief {slug}")
        for key in ("name", "cluster", "note", "work", "limit"):
            if not str(row.get(key, "")).strip():
                die(f"{slug} missing {key}")
        if row["cluster"] not in CLUSTERS:
            die(f"{slug} unknown cluster {row['cluster']}")
        if len(row["note"]) < 120:
            die(f"{slug} note too short ({len(row['note'])})")
        blob = row["note"] + row["work"] + row["limit"]
        if "£" in blob:
            die(f"{slug} contains £")
        by_slug[slug] = row

    missing = [s for s in CANONICAL if s not in by_slug]
    extra = [s for s in by_slug if s not in set(CANONICAL)]
    if missing or extra or len(CANONICAL) != 130:
        die(f"slug mismatch missing={missing} extra={extra} canonical={len(CANONICAL)} briefs={len(by_slug)}")

    notes = [by_slug[s]["note"] for s in CANONICAL]
    if len(set(notes)) != 130:
        die("notes are not unique")

    clusters: dict[str, list[str]] = {}
    for slug in CANONICAL:
        clusters.setdefault(by_slug[slug]["cluster"], []).append(slug)

    jobs = []
    for slug in CANONICAL:
        row = by_slug[slug]
        peers_src = clusters[row["cluster"]]
        idx = peers_src.index(slug)
        related = peers_src[(idx + 1) % len(peers_src)]
        if related == slug:
            die(f"{slug} cluster has no sibling")
        peers = []
        for step in range(1, 4):
            peer = peers_src[(idx + step) % len(peers_src)]
            if peer != slug and peer not in peers:
                peers.append(peer)
        name = row["name"]
        seo_title = f"{name} | North West plumbing"
        h1 = f"{name} across the North West"
        first = row["note"].split(". ")[0].strip().rstrip(".")
        meta = meta_with_poa(name, first)
        intro = (
            f"{name} is scoped from our Stockport base for the property you actually have. "
            f"{row['note']} The figure is price on application after that scope, not a catalogue price."
        )
        body = (
            f"{row['work']} {row['limit']} {CLUSTERS[row['cluster']]} "
            f"Tell us the postcode, what is leaking or blocked, and whether the stopcock is already shut. "
            f"Related plumbing guides are linked on this page, and the full list sits on the plumbing service hub."
        )
        faq = [
            [
                f"What does a {name} visit cover?",
                f"{row['work']} {row['note']}",
            ],
            [
                f"What is out of scope for {name}?",
                row["limit"],
            ],
            [
                f"How is {name} priced?",
                "Price on application after we confirm access, parts and whether the stopcock holds. We do not publish a catalogue price on this page.",
            ],
        ]
        focus = [
            row["note"].split(". ")[0].rstrip(".") + ".",
            row["work"].split(". ")[0].rstrip(".") + ".",
            row["limit"],
            "Price on application from Stockport — Greater Manchester, Cheshire, Lancashire and Merseyside.",
        ]
        jobs.append(
            {
                "slug": slug,
                "name": name,
                "service": "plumbing",
                "family": "plumbing",
                "cluster": row["cluster"],
                "related": related,
                "peers": peers,
                "seo_title": seo_title,
                "h1": h1,
                "meta_desc": meta,
                "intro": intro,
                "body": body,
                "note": row["note"],
                "faq": faq,
                "focus_points": focus,
                "secondaries": [
                    f"{name} Stockport",
                    f"{name} Greater Manchester",
                    f"{name} landlord",
                ],
                "seo_keywords": f"{name}, plumbing, North West, Stockport, price on application",
                "plumbing_lane": True,
            }
        )

    titles = [j["seo_title"] for j in jobs]
    h1s = [j["h1"] for j in jobs]
    metas = [j["meta_desc"] for j in jobs]
    intros = [j["intro"] for j in jobs]
    bodies = [j["body"] for j in jobs]
    for label, vals in (
        ("title", titles),
        ("h1", h1s),
        ("meta", metas),
        ("intro", intros),
        ("body", bodies),
    ):
        if len(set(vals)) != 130:
            die(f"duplicate {label}")

    out = {
        "count": 130,
        "family": "plumbing",
        "source": "subset of sec-water-plumb plumbing family",
        "non_prod": True,
        "jobs": jobs,
    }
    dest = ROOT / "data" / "job-types-plumbing.json"
    dest.write_text(json.dumps(out, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"wrote {dest} jobs={len(jobs)}")


if __name__ == "__main__":
    main()
