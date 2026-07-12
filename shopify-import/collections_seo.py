#!/usr/bin/env python3
"""Improve custom collection body_html SEO for iComply Supplys fire categories."""

from __future__ import annotations

import json
import re
import time
from pathlib import Path

from shopify_client import api

ROOT = Path(__file__).parent
OUT = ROOT / "collections_seo_result.json"

# 80–150 words UK trade SEO copy per fire category (and service packages).
# body_html uses simple semantic markup for collection landing pages.
SEO_COPY: dict[str, str] = {
    "fire-alarm-control-panels": """
<p>Browse UK trade <strong>fire alarm control panels</strong> from iComply Supplys — conventional and addressable panels for commercial, multi-occupancy and light industrial premises. Specify Kentec, Advanced, C-TEC, Haes, Morley-IAS and related EN54 panel families with clear zone or loop counts for installers, fire engineers and facilities teams.</p>
<p>Choose single- and multi-loop addressable panels for networked sites, or compact conventional panels for landlord systems, HMOs, retail and small offices. Our fire panel range supports day-to-day replacements, new builds and system upgrades. Confirm protocol, expander options and cause-and-effect requirements when ordering. Guide trade pricing is shown online — verify live stock and distributor rates for larger project schedules. Designed for BAFE-aware UK trade supply.</p>
""".strip(),
    "smoke-detectors": """
<p>Shop <strong>smoke detectors</strong> for conventional and addressable fire systems — optical, ionisation (where still specified) and modern multi-criteria heads trusted on UK commercial projects. iComply Supplys stocks Apollo Series 65, Orbis, XP95 and Discovery-style optical devices plus compatible trade alternatives for offices, corridors, retail and residential blocks.</p>
<p>Optical photoelectric sensing remains the default choice for most rooms; select ionisation only where the fire risk assessment still calls for it. Bases are typically sold separately so you can match diode, isolator or deep mounting options to the loop design. Order by protocol and series to keep spares consistent across the site. Guide trade prices apply — confirm stock before large roll-outs.</p>
""".strip(),
    "heat-detectors": """
<p>Specify <strong>heat detectors</strong> for kitchens, plant rooms, boiler cupboards, dusty workshops and other areas where smoke detection is unsuitable. Our UK trade range covers rate-of-rise and fixed-temperature heads for conventional and addressable systems, including Apollo and Hochiki family devices commonly used on commercial fire alarms.</p>
<p>Fixed-temperature detectors suit high ambient heat sources; rate-of-rise units respond to rapid temperature increase while reducing nuisance alarms from gradual kitchen heat. Pair with the correct detector base and cable class for the zone or loop. Ideal for installers replacing failed heads or completing multi-zone conventional panels. Guide trade pricing online — confirm temperature rating and protocol before you order bulk quantities.</p>
""".strip(),
    "multi-sensor-detectors": """
<p>Select <strong>multi-sensor detectors</strong> that combine smoke and heat (or multi-criteria sensing) for improved detection performance and fewer false alarms. iComply Supplys supplies addressable multi-sensor heads widely specified on UK offices, hotels, education and multi-use buildings where a single-mode detector is not enough.</p>
<p>Multi-criteria devices help fire engineers balance early warning with nuisance-alarm control in corridors, open-plan floors and mixed-risk rooms. Match protocol (XP95, Discovery, Hochiki ESP and equivalents) to the panel and keep bases consistent across the loop. Suitable for new addressable installs and phased upgrades. Guide trade prices are indicative — verify firmware/protocol variants and live stock with our team for project BOMs.</p>
""".strip(),
    "manual-call-points": """
<p>Order red <strong>manual call points</strong> (MCPs) for conventional and addressable fire alarm systems — break-glass and resettable styles used across UK commercial, residential common parts and industrial sites. Our trade selection covers surface and flush options with the back-boxes, covers and glass/element spares installers need on every job.</p>
<p>Specify addressable MCPs on loop systems or conventional call points on zoned panels; confirm weatherproof or IP-rated units for plant rooms and external exits. Resettable elements reduce ongoing glass stock on high-traffic sites. Compatible with leading panel brands when protocol and resistor values are matched correctly. Guide trade pricing shown — confirm addressable protocol and mounting accessories before large site packs.</p>
""".strip(),
    "sounders-beacons": """
<p>Equip sites with <strong>sounders, beacons and visual alarm devices (VADs)</strong> that meet modern UK fire alarm notification needs. Wall sounders, base sounders, combined sounder-beacons and LED VADs support BS 5839-style designs for open-plan offices, corridors, plant areas and accessibility-aware alarm coverage.</p>
<p>Choose conventional or addressable devices to suit the panel, and check sound output (dB), flash rate and EN54-23 VAD categories for the space. Base sounders free wall space on detector circuits; dedicated wall units suit stair cores and large rooms. Ideal for new installs, system extensions and defective device swaps. Guide trade prices online — confirm loop load, tone set and protocol before bulk ordering for multi-floor projects.</p>
""".strip(),
    "bases-mounting": """
<p>Complete detector installs with the right <strong>bases and mounting accessories</strong> — standard, diode, isolator, relay and deep bases for Apollo, Hochiki and related detector families. Correct base selection keeps conventional and addressable loops reliable, simplifies isolator zoning and supports neat ceiling finishes on commercial fit-outs.</p>
<p>Use diode bases where required on conventional circuits, isolator bases to protect addressable loops from short circuits, and deep bases for surface cabling or retrofit ceilings. Keep base series matched to the detector head to avoid callback issues. Stock common bases alongside detectors for one-drop site deliveries. Guide trade pricing applies — confirm part numbers against the detector datasheet before you order project quantities.</p>
""".strip(),
    "interfaces-modules": """
<p>Integrate plant, doors and ancillaries with <strong>interfaces and modules</strong> for addressable fire systems — input/output units, zone monitors, sounder controllers, switch monitors and loop interfaces used daily by UK fire installers. Bridge conventional zones, door holders, plant shut-down and third-party equipment into the cause-and-effect strategy.</p>
<p>Select single- and multi-way I/O modules sized to the panel protocol (Apollo, Hochiki and compatible ecosystems). Zone monitor units bring conventional detection onto addressable loops during phased upgrades. DIN-rail and boxed formats suit plant rooms and risers. Guide trade prices are indicative — verify protocol, isolation and power requirements against the fire strategy drawings before procurement.</p>
""".strip(),
    "batteries-power": """
<p>Keep fire systems online with <strong>sealed lead-acid batteries and 24V power supplies</strong> sized for control panels, door holders and ancillary fire equipment. iComply Supplys offers common VRLA capacities used on UK conventional and addressable panels, plus power supply units for sounder circuits and hold-open devices.</p>
<p>Match battery voltage and Ah rating to the panel standby calculation (typically 24V pairs) and replace ageing cells during planned maintenance to avoid overnight discharge faults. Use EN-compliant PSUs where the design requires monitored power for door release or plant interfaces. Suitable for service engineers and installers stocking vans or completing panel upgrades. Guide trade pricing online — confirm capacity, terminals and PSU monitoring features before large service kits.</p>
""".strip(),
    "cables-accessories": """
<p>Run compliant circuits with <strong>fire-resistant cable and accessories</strong> — standard and enhanced fire cable, glands, junctions, clips and ancillaries for BS 5839 fire alarm and related life-safety wiring. Trade lengths and accessories help electricians and fire contractors complete new installs, alterations and remedial works without project delays.</p>
<p>Choose standard fire cable for many internal alarm circuits and enhanced performance cable where the risk assessment or specification demands longer survival under fire conditions. Pair with correct glands, jointing and support to maintain integrity. Suitable for detection loops, sounder circuits and panel mains where specified. Guide trade prices apply — confirm core count, CSA, colour and enhanced/standard grade against the project cable schedule.</p>
""".strip(),
    "emergency-lighting-products": """
<p>Specify <strong>emergency lighting products</strong> for UK commercial and multi-occupancy buildings — maintained and non-maintained luminaires, exit signs and test-friendly fittings that support escape route and open-area coverage. iComply Supplys supplies trade emergency lighting for new builds, refurbishments and planned replacements alongside fire system works.</p>
<p>Select bulkhead, downlight and exit legend formats to suit corridors, stairwells, plant rooms and final exits. Self-test and manual-test options help facilities teams meet routine inspection duties. Compatible with standard electrical install practices and common mounting substrates. Ideal for contractors delivering combined fire and emergency lighting packages. Guide trade pricing shown — confirm duration (e.g. 3-hour), IP rating and legend type before site bulk orders.</p>
""".strip(),
    "service-packages": """
<p>Book <strong>service packages</strong> from the iComply network — installation support, planned fire alarm servicing, emergency lighting tests and certification-focused packages for landlords, managing agents and commercial occupiers across the UK. Pair product supply with competent on-site work for a single trade pathway from specification to sign-off.</p>
<p>Packages cover routine maintenance visits, reactive call-outs where available, and documentation suited to duty-holder records. Ideal when you need panels, devices and labour coordinated rather than sourcing parts alone. Scope, site survey and compliance outcomes vary by premises and system type — discuss building size, existing kit and access before confirming. Online descriptions are guides; final quotations reflect the site survey and current standards applicable to your property portfolio.</p>
""".strip(),
}


def word_count(html: str) -> int:
    text = re.sub(r"<[^>]+>", " ", html)
    text = re.sub(r"&[a-zA-Z]+;", " ", text)
    words = [w for w in re.split(r"\s+", text.strip()) if w]
    return len(words)


def load_all_custom_collections() -> list[dict]:
    data = api("GET", "/custom_collections.json?limit=250")
    return list(data.get("custom_collections") or [])


def update_collection(col: dict, body_html: str) -> dict:
    cid = col["id"]
    payload = {
        "custom_collection": {
            "id": cid,
            "body_html": body_html,
            "published": True,
        }
    }
    resp = api("PUT", f"/custom_collections/{cid}.json", payload)
    time.sleep(0.6)  # stay under Shopify 2 req/s bucket
    updated = resp.get("custom_collection") or {}
    return {
        "id": cid,
        "handle": col.get("handle"),
        "title": col.get("title"),
        "action": "updated",
        "published": True,
        "published_at": updated.get("published_at") or col.get("published_at"),
        "word_count": word_count(body_html),
        "body_html": body_html,
        "had_seo_copy": col.get("handle") in SEO_COPY,
    }


def main() -> None:
    collections = load_all_custom_collections()
    print(f"Loaded {len(collections)} custom collections")

    results: list[dict] = []
    for col in collections:
        handle = col.get("handle") or ""
        title = col.get("title") or ""
        body = SEO_COPY.get(handle)
        if not body:
            # Homepage / non-fire collections: ensure published without rewriting body
            already_pub = bool(col.get("published_at"))
            print(f"  SKIP seo (no template): {handle} — published={already_pub}")
            if already_pub:
                results.append(
                    {
                        "id": col["id"],
                        "handle": handle,
                        "title": title,
                        "action": "skipped_already_published",
                        "published": True,
                        "published_at": col.get("published_at"),
                        "word_count": word_count(col.get("body_html") or ""),
                        "body_html": col.get("body_html") or "",
                        "had_seo_copy": False,
                        "note": "No fire-category SEO template; already published",
                    }
                )
            else:
                resp = api(
                    "PUT",
                    f"/custom_collections/{col['id']}.json",
                    {"custom_collection": {"id": col["id"], "published": True}},
                )
                time.sleep(0.6)
                updated = resp.get("custom_collection") or {}
                results.append(
                    {
                        "id": col["id"],
                        "handle": handle,
                        "title": title,
                        "action": "published_only",
                        "published": True,
                        "published_at": updated.get("published_at") or col.get("published_at"),
                        "word_count": word_count(col.get("body_html") or ""),
                        "body_html": col.get("body_html") or "",
                        "had_seo_copy": False,
                        "note": "No dedicated SEO template; published=true enforced",
                    }
                )
            continue

        wc = word_count(body)
        print(f"  Updating {handle} ({wc} words)...")
        if wc < 80 or wc > 150:
            print(f"    WARN: word count {wc} outside 80-150")
        entry = update_collection(col, body)
        results.append(entry)
        print(f"    OK id={entry['id']} published_at={entry.get('published_at')}")

    # Coverage report for planned handles
    live_handles = {c.get("handle") for c in collections}
    missing_templates = sorted(h for h in live_handles if h not in SEO_COPY)
    unused_templates = sorted(h for h in SEO_COPY if h not in live_handles)

    summary = {
        "store": "icomply-supplys.myshopify.com",
        "total_custom_collections": len(collections),
        "seo_updated": sum(1 for r in results if r["action"] == "updated"),
        "published_only": sum(
            1 for r in results if r["action"] in ("published_only", "skipped_already_published")
        ),
        "all_published": all(r.get("published") for r in results),
        "word_count_ok": all(
            80 <= r["word_count"] <= 150
            for r in results
            if r.get("had_seo_copy")
        ),
        "missing_templates_for_live": missing_templates,
        "unused_templates": unused_templates,
        "collections": results,
    }

    OUT.write_text(json.dumps(summary, indent=2), encoding="utf-8")
    print(f"\nSaved {OUT}")
    print(
        f"SEO updated: {summary['seo_updated']} | "
        f"all_published: {summary['all_published']} | "
        f"word_count_ok: {summary['word_count_ok']}"
    )


if __name__ == "__main__":
    main()
