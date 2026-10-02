#!/usr/bin/env python3
"""Build the CCTV job-lane catalogue and insert any missing keyword rows.

Reads website/data/keywords.json, adds the ten CCTV jobs that were on the
master list but not yet in the keyword catalogue, then writes
website/data/cctv-jobs.json from every service=cctv row.

Does not rewrite existing keyword rows and does not reformat keywords.json.
"""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
KEYWORDS = ROOT / "website" / "data" / "keywords.json"
CATALOGUE = ROOT / "website" / "data" / "cctv-jobs.json"
EXPECTED = 73

NEW_JOBS: dict[str, dict] = {
    "anpr-car-park-camera": {
        "name": "ANPR Car Park Camera",
        "service": "cctv",
        "related": "anpr-cctv-system",
        "intro": "An ANPR car park camera is a dedicated plate-capture view at an entrance, exit or barrier, not a wide overview camera asked to read plates as an afterthought. Icomply survey approach speed, camera height, shutter and lighting, then install a lane camera that can support authorised lists or incident review.",
        "body": "Car park plates fail when the camera looks across headlights, sits too high, or shares a recorder stream sized for a general view. We set a lane-specific camera, infrared or white-light fill where night capture needs it, and a retention period that matches why you store vehicle data. Barrier and intercom integration is scoped only when the existing controller can accept it. Coverage is Greater Manchester and the wider North West from our Stockport base. Written quotes follow the survey. We do not publish a catalogue fee for lane cameras.",
        "meta_desc": "ANPR car park camera surveys and installs for entry and exit lanes. Plate capture, lighting and retention scoped across Greater Manchester and the North West.",
        "focus_points": [
            "Lane survey for height, angle, speed and headlight washout",
            "Dedicated plate camera rather than a reused overview view",
            "Night fill and shutter settings checked on site",
            "Retention and purpose noted before vehicle data is stored",
        ],
        "faq": [
            [
                "Will an existing car park camera read plates?",
                "Sometimes on a slow approach in daylight. We still survey it. If the angle or shutter cannot hold a plate, we specify a lane camera instead of promising recognition from an overview.",
            ],
            [
                "Can the camera open a barrier?",
                "Only where the barrier controller accepts an authorised-list signal. We confirm that on the survey and quote the interface separately from the camera.",
            ],
            [
                "How do you price an ANPR lane?",
                "After the survey. Lane count, night lighting, civils and whether a barrier interface is required all change the quote. No made-up starting price.",
            ],
        ],
        "seo_keywords": "ANPR car park camera, car park number plate camera, entry lane ANPR, car park CCTV Stockport, ANPR Manchester, plate capture camera North West",
    },
    "cctv-after-network-change": {
        "name": "CCTV After Network Change",
        "service": "cctv",
        "related": "cctv-remote-viewing-setup",
        "intro": "CCTV after a network change is the recommission that follows a new firewall, broadband provider, VLAN or Wi-Fi swap, when cameras and remote viewing stop talking to the recorder. Icomply trace IP, PoE and port forwarding and put the system back into a known working state.",
        "body": "A router replacement is a common reason a previously stable NVR disappears from the phone app. We record the camera and recorder addresses, check the PoE switch, DNS and any VPN the client actually uses, then restore remote viewing without leaving old ports open. If the new network cannot carry the camera bitrate we say so and quote a switch or recorder change rather than masking the fault. Stockport engineers cover Greater Manchester, Cheshire, Lancashire and Merseyside. The visit is quoted from what changed, not from a fixed menu.",
        "meta_desc": "CCTV recommission after a router, firewall or broadband change. IP, PoE and remote viewing restored across the North West.",
        "focus_points": [
            "Address map for cameras, recorder and viewing PCs",
            "PoE switch and VLAN checks after the network cutover",
            "Remote viewing restored with the ports you actually need",
            "Written note of what was changed on site",
        ],
        "faq": [
            [
                "The cameras worked before the new router. Can you reconnect them?",
                "Usually yes. We need the recorder password and access to the new router. If addresses were never recorded we rediscover the cameras on the local network.",
            ],
            [
                "Do you need the IT contractor on site?",
                "Helpful when VLANs or a managed firewall are in play. For a straight broadband swap we can often restore viewing with the person who holds the router login.",
            ],
            [
                "Is this a call-out with a published fee?",
                "No. We quote from the site and what changed. Multi-site estates and locked-down firewalls take longer than a single NVR behind a home router.",
            ],
        ],
        "seo_keywords": "CCTV after network change, CCTV not working after new router, reconnect NVR remote viewing, CCTV firewall change, CCTV Stockport, CCTV Manchester",
    },
    "cctv-export-for-incident": {
        "name": "CCTV Export for Incident",
        "service": "cctv",
        "related": "cctv-recording",
        "intro": "A CCTV export for an incident is a time-bounded clip, stills and a note of which camera and recorder produced them, saved in a format someone else can open. Icomply attend when staff cannot export, the player is missing, or the footage has to be handed to an insurer or the police.",
        "body": "Exports fail when the incident window has already overwritten, the operator account cannot download, or the file is a proprietary bundle with no player. We check retention first, then export the relevant cameras with a readable player or an open format where the recorder allows it, and label the disc or download with camera name, time zone and who produced it. We do not promise footage that the recorder has already deleted. Chain-of-custody wording is factual: what was exported, when, and from which device. Quotes follow the number of cameras and whether we have to recover a failing drive.",
        "meta_desc": "CCTV incident export and stills from NVRs and DVRs. Readable clips for insurers and police, scoped across Greater Manchester and the North West.",
        "focus_points": [
            "Confirm the incident is still inside the retention window",
            "Export with a player or a format the recipient can open",
            "Camera name, time and recorder noted on the handover",
            "Failing drives flagged before anyone relies on the clip",
        ],
        "faq": [
            [
                "Can you recover footage that has already overwritten?",
                "If the recorder has reused that disk space, the pictures are gone. We check the timeline before attempting an export and tell you straight away.",
            ],
            [
                "Will the police be able to play the file?",
                "We export with the recorder's player or a widely playable file where the unit allows it, and we test playback before we leave.",
            ],
            [
                "How quickly can you attend?",
                "Incident exports are prioritised when the retention window is short. Call with the site postcode and the approximate time of the event.",
            ],
        ],
        "seo_keywords": "CCTV export for incident, download CCTV footage, NVR clip for police, CCTV stills for insurer, CCTV evidence export Stockport, CCTV footage Manchester",
    },
    "cctv-ir-illuminator": {
        "name": "CCTV IR Illuminator",
        "service": "cctv",
        "related": "external-cctv-installation",
        "intro": "A CCTV IR illuminator is an extra infrared lamp for yards, loading areas and long driveways where the camera's built-in LEDs fade out before the view does. Icomply match beam angle and range to the lens so the picture is even rather than a white hotspot.",
        "body": "Built-in IR is often enough for a doorway and useless across a car park. We measure the view, choose a lamp that covers it without blasting the near field, and power it from a suitable supply or PoE injector where the product allows. Aim, photocell threshold and camera exposure are set at night or with the scene darkened, not from a daytime guess. If white light is a better fit for colour recognition we say so. Installs are quoted after we see mounting height, cable route and the camera already on site.",
        "meta_desc": "CCTV infrared illuminators for yards and long views. Beam angle matched to the camera across Greater Manchester and the North West.",
        "focus_points": [
            "Range and beam angle matched to the lens, not a generic lamp",
            "Mount and power planned with the existing camera",
            "Night aim so the near field is not washed out",
            "White-light option discussed when colour detail matters",
        ],
        "faq": [
            [
                "Why is the image bright close up and black further away?",
                "The camera's own LEDs are lighting the near field and exposing for that. A separate illuminator, or a change of exposure, is how we even the view out.",
            ],
            [
                "Do IR lamps work in heavy rain and fog?",
                "They help, but heavy moisture still scatters infrared. We set expectations from the site rather than quoting a brochure range.",
            ],
            [
                "Can you add a lamp without changing the camera?",
                "Often yes, if there is a mount and a safe power source. We confirm voltage and weather rating on the survey.",
            ],
        ],
        "seo_keywords": "CCTV IR illuminator, infrared lamp for CCTV, yard camera lighting, external CCTV night vision, IR illuminator Stockport, CCTV lighting Manchester",
    },
    "cctv-privacy-masking": {
        "name": "CCTV Privacy Masking",
        "service": "cctv",
        "related": "cctv-compliance",
        "intro": "CCTV privacy masking blocks parts of a live or recorded view, such as a neighbour's window or a private garden, so the camera can still cover your entrance. Icomply set masks on the recorder or camera and check them after any zoom or preset change.",
        "body": "A mask that looks right on a wide shot can slip when someone later zooms the lens or recalls a PTZ preset. We apply privacy zones for the views you must not record, document which cameras are masked, and tell you if a physical resite is more reliable than a software block. Masks are not a substitute for a camera that should never have pointed that way. Where the system cannot mask cleanly we recommend a different lens or mounting position. The work is quoted from the number of cameras and whether we can reach them safely.",
        "meta_desc": "CCTV privacy masking for windows, gardens and neighbouring property. Zones set and checked across Greater Manchester and the North West.",
        "focus_points": [
            "Privacy zones on the cameras that overlook private space",
            "Masks rechecked after zoom, focus or PTZ preset changes",
            "Resite recommended when a software mask is not reliable",
            "Short note of which views are blocked and why",
        ],
        "faq": [
            [
                "Does a privacy mask delete footage already recorded?",
                "No. It changes what is captured from the moment it is applied. Earlier footage, if it is still on the recorder, is a separate retention question.",
            ],
            [
                "Can staff turn the mask off?",
                "We set operator rights so a mask is not a casual toggle. If the recorder cannot lock that down we say so.",
            ],
            [
                "Is masking enough for a camera aimed at a neighbour?",
                "Only if the mask holds. If the camera can be moved or zoomed past it, we recommend a different position.",
            ],
        ],
        "seo_keywords": "CCTV privacy masking, privacy zone CCTV, block neighbour window on camera, CCTV privacy screen, privacy mask Stockport, CCTV GDPR masking Manchester",
    },
    "cctv-remote-user-training": {
        "name": "CCTV Remote User Training",
        "service": "cctv",
        "related": "remote-cctv-access",
        "intro": "CCTV remote user training shows the people who actually open the app how to view cameras, export a clip and spot a full disk, without sharing the installer password. Icomply run a short handover on the recorder and phones your team already uses.",
        "body": "Most 'the CCTV is down' calls are a viewer who cannot find playback or who is logged into an old app. We create named operator accounts, walk through live view, search and export, and leave a one-page note of what not to change. Installer and admin passwords stay with the site owner, not in a group chat. Training is on the system you have, including Hikvision, Dahua, Axis and other recorders we can log into. It is quoted as a visit, not as a packaged course fee, and can sit on the end of an install or remote-viewing setup.",
        "meta_desc": "CCTV remote viewing training for site staff. Live view, playback and export on your recorder, from Stockport across the North West.",
        "focus_points": [
            "Named operator logins instead of a shared installer password",
            "Live view, playback and a test export on your app",
            "What staff should leave alone on the recorder",
            "One-page handover your team can keep on site",
        ],
        "faq": [
            [
                "Do you train people who were not at the original install?",
                "Yes. New managers and security staff are a common reason to book a refresher on the system already in place.",
            ],
            [
                "Can this be done on a video call?",
                "For a simple app login, sometimes. Export paths and recorder settings are more reliable with an engineer on site.",
            ],
            [
                "Will you write down the admin password?",
                "The site owner keeps admin credentials. Operators get their own login so a leaver can be removed without a full reset.",
            ],
        ],
        "seo_keywords": "CCTV user training, remote viewing training, NVR handover training, CCTV app training Stockport, teach staff CCTV playback, CCTV training Manchester",
    },
    "cctv-switch-and-poe-check": {
        "name": "CCTV Switch and PoE Check",
        "service": "cctv",
        "related": "cctv-cabling-installation",
        "intro": "A CCTV switch and PoE check finds cameras that reboot, drop offline overnight or never get enough power because the switch budget or the cable run is wrong. Icomply measure the PoE load and the links before anyone condemns the cameras.",
        "body": "A 16-camera switch with a small power budget will brown out the furthest infrared cameras at night when the LEDs draw more. We list which ports are up, which cameras are under-voltage, and whether the fault is the switch, a damaged pair, or a camera that needs its own injector. Spare ports and uplink capacity are noted so the next camera add does not repeat the fault. If the switch has to be replaced we quote the model against the load, not a guess from the box size. Greater Manchester and North West coverage from Stockport.",
        "meta_desc": "CCTV PoE switch checks for cameras that drop offline or reboot. Power budget and cabling tested across the North West.",
        "focus_points": [
            "PoE budget compared with the cameras actually connected",
            "Port-by-port note of links that drop or under-power",
            "Cable faults separated from a weak switch",
            "Replacement quoted only when the load needs it",
        ],
        "faq": [
            [
                "Cameras go offline at night. Is that the switch?",
                "Often the infrared LEDs push a marginal PoE port over its limit. We measure that rather than swapping cameras at random.",
            ],
            [
                "Can you use our existing IT switch?",
                "If it has enough PoE budget and the CCTV VLAN is intentional, yes. A general office switch is a frequent cause of drops.",
            ],
            [
                "Do you replace the switch on the first visit?",
                "Only when we have confirmed it cannot carry the load and you have accepted the quote. The check itself is the first piece of work.",
            ],
        ],
        "seo_keywords": "CCTV PoE switch check, cameras dropping offline, PoE budget CCTV, CCTV switch replacement, PoE camera fault Stockport, CCTV network switch Manchester",
    },
    "gdpr-camera-siting-review": {
        "name": "GDPR Camera Siting Review",
        "service": "cctv",
        "related": "cctv-compliance",
        "intro": "A GDPR camera siting review walks the views you record and flags cameras that cover neighbours, public pavements beyond your boundary, or staff areas without a clear purpose. Icomply mark what to mask, resite or remove, and what signage should say.",
        "body": "UK sites use the Data Protection Act and UK GDPR when CCTV identifies people. We are not a law firm and we do not issue a certificate that makes a system 'GDPR approved'. We do record each camera's aim, whether it needs a privacy mask, and whether the stated purpose still matches the view. You get a short schedule you can keep with your CCTV policy, plus a quote for any resites or masks you want us to carry out. Signage wording is practical: who operates the system and how to ask for footage. Stockport engineers cover the North West.",
        "meta_desc": "GDPR camera siting review for business and landlord CCTV. Aims, masks and signage noted. No invented compliance certificate. North West.",
        "focus_points": [
            "Each camera aim checked against the reason it was installed",
            "Neighbours, gardens and excessive public coverage flagged",
            "Mask, resite or remove recommended per camera",
            "No fake GDPR certificate — a written schedule you can file",
        ],
        "faq": [
            [
                "Do you certify that our CCTV is GDPR compliant?",
                "No. Compliance depends on your purpose, policy, retention and how you handle requests. We review siting and signage and write down what we found.",
            ],
            [
                "We film the pavement outside the shop. Is that a problem?",
                "It can be, if the view goes further than you need to protect the premises. We show you the frame and the options to narrow it.",
            ],
            [
                "Can you do the masks in the same visit?",
                "Where the recorder supports them and the cameras are reachable, yes. That work is quoted from the review, not assumed.",
            ],
        ],
        "seo_keywords": "GDPR camera siting review, CCTV GDPR survey, CCTV privacy review, camera position data protection, CCTV signage review Stockport, GDPR CCTV Manchester",
    },
    "landlord-common-part-cctv": {
        "name": "Landlord Common Part CCTV",
        "service": "cctv",
        "related": "landlord-cctv-installation",
        "intro": "Landlord common-part CCTV covers entrances, bin stores, car parks and stair lobbies in blocks and HMOs, not cameras inside tenants' homes. Icomply design those shared views with signage, a named operator and a retention period a managing agent can explain.",
        "body": "Block cameras fail audits when they point at front doors from an angle that looks into flats, or when nobody knows who can export footage. We place cameras on the common parts you control, keep views out of dwellings, and set remote access for the agent rather than every resident. Door-entry and access-control integration is optional and quoted only when those systems are already on site. Install, repair and drive changes are the same Stockport team. You receive a written quote after we see risers, power and where the recorder can live.",
        "meta_desc": "CCTV for landlord common parts: entrances, stairs, bin stores and car parks. Signage and retention discussed. North West blocks and HMOs.",
        "focus_points": [
            "Entrances, stairs, bin stores and car parks — not inside homes",
            "Views kept out of flat interiors",
            "Agent access for playback and export, with a named operator",
            "Signage and retention agreed before the system goes live",
        ],
        "faq": [
            [
                "Can we put cameras in communal hallways?",
                "Usually yes, where the landlord controls that space and residents are told. We still keep the aim off private rooms and letterbox interiors where we can.",
            ],
            [
                "Should every tenant have the app?",
                "We recommend the managing agent or a named landlord login. Wider access is a policy choice we will set up only if you confirm it.",
            ],
            [
                "Do you cover HMOs and purpose-built blocks?",
                "Yes. Both are common across Greater Manchester. The survey confirms riser space, power and a secure place for the recorder.",
            ],
        ],
        "seo_keywords": "landlord common part CCTV, HMO hallway cameras, block entrance CCTV, communal CCTV installation, landlord CCTV Stockport, apartment CCTV Manchester",
    },
    "loading-bay-camera-add": {
        "name": "Loading Bay Camera Add",
        "service": "cctv",
        "related": "warehouse-cctv",
        "intro": "A loading bay camera add is one or more extra views on a dock, yard door or goods-in lane, using the recorder you already have where it still has channels and disk. Icomply check capacity first so the new camera does not shorten retention on the rest of the site.",
        "body": "Goods-in disputes need a camera that sees the vehicle, the door and the threshold, not a dome lost in the roof steel. We test spare NVR channels, PoE ports and days of recording at the current settings, then quote a camera, cabling and a drive increase if the maths needs it. External housings and IR are specified for the bay lighting. If the recorder is full we say whether a larger unit is the honest next step. Warehouses and trade counters across Greater Manchester and the North West are covered from Stockport.",
        "meta_desc": "Add loading bay and goods-in CCTV cameras to an existing recorder. Channel, PoE and retention checked first. North West warehouses.",
        "focus_points": [
            "Spare recorder channels and disk checked before the add",
            "Camera aimed at the vehicle, door and threshold",
            "External housing and IR matched to bay lighting",
            "Drive upgrade quoted when retention would otherwise fall",
        ],
        "faq": [
            [
                "Can you add a bay camera to our current NVR?",
                "If a channel, a PoE port and enough disk are free, yes. If not, we quote the missing part rather than squeezing the picture on.",
            ],
            [
                "Will this wipe the existing cameras?",
                "No. We add the view and confirm the other channels still record. Retention days may fall if the disk was already full, which is why we check it first.",
            ],
            [
                "Do you cover yards as well as the internal dock?",
                "Yes. External approaches and internal thresholds are both in scope. The survey picks the housings.",
            ],
        ],
        "seo_keywords": "loading bay CCTV camera, goods-in camera add, dock CCTV installation, warehouse camera add-on, loading bay CCTV Stockport, yard camera Manchester",
    },
}


def _insert_members(text: str, members: list[str]) -> str:
    stripped = text.rstrip()
    if not stripped.endswith("}"):
        raise SystemExit("keywords.json does not end with }")
    body = stripped[:-1].rstrip()
    if body.endswith(","):
        body = body[:-1].rstrip()
    addition = ",\n" + ",\n".join(members) + "\n}\n"
    return body + addition


def main() -> None:
    raw = KEYWORDS.read_text(encoding="utf-8")
    data = json.loads(raw)
    if not isinstance(data, dict):
        raise SystemExit("keywords.json is not an object")

    missing = [slug for slug in NEW_JOBS if slug not in data]
    if missing:
        members = []
        for slug in missing:
            blob = json.dumps({slug: NEW_JOBS[slug]}, indent=4, ensure_ascii=False)
            members.append(blob.strip()[1:-1].strip())
            data[slug] = NEW_JOBS[slug]
        KEYWORDS.write_text(_insert_members(raw, members), encoding="utf-8")
        # Re-read so the catalogue matches disk, including key order.
        data = json.loads(KEYWORDS.read_text(encoding="utf-8"))

    jobs = []
    for slug, row in data.items():
        if not isinstance(row, dict) or row.get("service") != "cctv":
            continue
        item = {"slug": slug}
        item.update(row)
        jobs.append(item)
    jobs.sort(key=lambda row: row["slug"])
    if len(jobs) != EXPECTED:
        raise SystemExit(f"CCTV keyword rows {len(jobs)} !== {EXPECTED}")

    catalogue = {
        "count": EXPECTED,
        "service": "cctv",
        "lane": "pages/services/cctv",
        "jobs": jobs,
    }
    CATALOGUE.write_text(json.dumps(catalogue, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"cctv_jobs={len(jobs)} inserted={len(missing)} -> {CATALOGUE.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
