#!/usr/bin/env python3
"""Build the security dual-269 P0 pack: copy JSON, JS data, keyword/job stubs.

Towns: website/data/area-hub-allowlist-50mi-dual.csv (269).
Intents: the 67 P0 rows. P1 (133) is recorded as follow-on and is not emitted.
"""
from __future__ import annotations

import csv
import json
import re
import zlib
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
TOWN_CSV = ROOT / "website/data/area-hub-allowlist-50mi-dual.csv"
INTENT_CSV = Path(
    "/home/ubuntu/.cursor/projects/workspace/uploads/SECURITY-DUAL-RING-INTENTS_603e.csv"
)
if not INTENT_CSV.is_file():
    INTENT_CSV = ROOT / "website/data/security-dual-ring-intents.csv"

OUT_JSON = ROOT / "website/data/security-dual-ring-p0.json"
OUT_JS = ROOT / "netlify/lib/security-dual-ring-pack.js"
KW_DIR = ROOT / "website/pages/keywords"
JOB_DIR = ROOT / "website/pages/jobs"
NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"
PHONE = "07517806082"

NAMES = {
    "cctv-installation": "CCTV Installation",
    "cctv-near-me": "CCTV Near Me",
    "cctv-repair": "CCTV Repair",
    "cctv-maintenance": "CCTV Maintenance",
    "cctv-engineer": "CCTV Engineer",
    "cctv-engineers-near-me": "CCTV Engineers Near Me",
    "security-camera-installation": "Security Camera Installation",
    "commercial-cctv-system": "Commercial CCTV System",
    "domestic-cctv-installation": "Domestic CCTV Installation",
    "access-control-installation": "Access Control Installation",
    "access-control-system": "Access Control System",
    "access-control-near-me": "Access Control Near Me",
    "access-control-repair": "Access Control Repair",
    "access-control-maintenance": "Access Control Maintenance",
    "access-control-engineers": "Access Control Engineers",
    "commercial-access-control": "Commercial Access Control",
    "door-access-control": "Door Access Control",
    "burglar-alarm-installation": "Burglar Alarm Installation",
    "burglar-alarm-system": "Burglar Alarm System",
    "intruder-alarm-installation": "Intruder Alarm Installation",
    "intruder-alarm-near-me": "Intruder Alarm Near Me",
    "intruder-alarm-repair": "Intruder Alarm Repair",
    "intruder-alarm-maintenance": "Intruder Alarm Maintenance",
    "security-alarm-installation": "Security Alarm Installation",
    "commercial-intruder-alarm": "Commercial Intruder Alarm",
    "domestic-burglar-alarm": "Domestic Burglar Alarm",
    "door-entry-system": "Door Entry System",
    "door-entry-installation": "Door Entry Installation",
    "door-entry-repair": "Door Entry Repair",
    "door-entry-maintenance": "Door Entry Maintenance",
    "door-entry-engineers-near-me": "Door Entry Engineers Near Me",
    "video-door-entry": "Video Door Entry",
    "anpr-cctv-system": "ANPR CCTV System",
    "cctv-monitoring": "CCTV Monitoring",
    "intruder-alarm-monitoring": "Intruder Alarm Monitoring",
    "alarm-monitoring-setup": "Alarm Monitoring Setup",
    "monitored-intruder-alarm": "Monitored Intruder Alarm",
    "emergency-cctv-repair": "Emergency CCTV Repair",
    "emergency-access-control-repair": "Emergency Access Control Repair",
    "emergency-alarm-call-out": "Emergency Alarm Call-out",
    "24-hour-cctv-engineer": "24-hour CCTV Engineer",
    "same-day-cctv-repair": "Same-day CCTV Repair",
    "same-day-alarm-repair": "Same-day Alarm Repair",
    "cctv-servicing": "CCTV Servicing",
    "access-control-servicing": "Access Control Servicing",
    "intruder-alarm-servicing": "Intruder Alarm Servicing",
    "burglar-alarm-service": "Burglar Alarm Service",
    "door-entry-servicing": "Door Entry Servicing",
    "intercom-installation": "Intercom Installation",
    "intercom-near-me": "Intercom Near Me",
    "intercom-repair": "Intercom Repair",
    "video-intercom": "Video Intercom",
    "paxton-access-control": "Paxton Access Control",
    "hikvision-cctv-installation": "Hikvision CCTV Installation",
    "ip-cctv-system": "IP CCTV System",
    "wireless-cctv": "Wireless CCTV",
    "maglock-installation": "Maglock Installation",
    "fob-access-control": "Fob Access Control",
    "security-systems-near-me": "Security Systems Near Me",
    "security-system-installation": "Security System Installation",
    "anpr-installation": "ANPR Installation",
    "anpr-near-me": "ANPR Near Me",
    "access-control-emergency": "Access Control Emergency",
    "intruder-alarm-emergency": "Intruder Alarm Emergency",
    "door-entry-near-me": "Door Entry Near Me",
    "cctv-installers-near-me": "CCTV Installers Near Me",
    "alarm-installers-near-me": "Alarm Installers Near Me",
}

DETAILS = {
    "cctv-installation": "A new CCTV installation is a camera and recorder design for the doors, yards and rooms you need to see. Retention, night view and signage are written down before any cable is clipped.",
    "cctv-near-me": "CCTV near me is that same design, booked from the Stockport workshop for the town on the page. Travel is part of the quote, not a line added after the visit.",
    "cctv-repair": "CCTV repair is for a camera, recorder, disk or viewing path that has already stopped earning its keep. The fault is named before parts are swapped.",
    "cctv-maintenance": "CCTV maintenance is a planned visit that checks pictures, time, storage and the leads that fail quietly between incidents.",
    "cctv-engineer": "A CCTV engineer visit is a named attendance for design, fault-finding or a handover. It is not a call-centre script with a town name pasted on.",
    "cctv-engineers-near-me": "CCTV engineers near me means an engineer who can actually reach that town from Stockport, with the scope agreed before the van is booked.",
    "security-camera-installation": "Security camera installation is the camera side of a CCTV job: position, lens, mount and the cable or link back to the recorder.",
    "commercial-cctv-system": "A commercial CCTV system is sized for shops, warehouses, yards and offices, including who on the staff may view and export footage.",
    "domestic-cctv-installation": "Domestic CCTV installation is for a home, a rented house or a small block, with privacy for neighbours treated as part of the design.",
    "access-control-installation": "Access control installation is the doors, readers, controller and credentials for a building that needs to know who came in.",
    "access-control-system": "An access control system page is the overview: which doors, which tokens, and how fire release is proved on the doors that must open.",
    "access-control-near-me": "Access control near me is that door survey, arranged from Stockport for the town named on the page.",
    "access-control-repair": "Access control repair is a door that will not lock, will not release, or has stopped reading the token people still carry.",
    "access-control-maintenance": "Access control maintenance checks readers, power supplies, door loops and the fire-release path before a resident is stuck.",
    "access-control-engineers": "Access control engineers attend for surveys, faults and handovers. The visit is quoted from the doors, not from a day-rate menu.",
    "commercial-access-control": "Commercial access control covers offices, shops and shared stairs where staff and visitors need different rights.",
    "door-access-control": "Door access control is the single-door or multi-door decision: maglock or strike, reader, supply and what happens on fire alarm.",
    "burglar-alarm-installation": "Burglar alarm installation is a detection and panel design for the openings and rooms you actually use, with a written handover.",
    "burglar-alarm-system": "A burglar alarm system page explains the panel, detectors and how the system is set and unset by the people who live or work there.",
    "intruder-alarm-installation": "Intruder alarm installation follows the same practical scope as a burglar alarm: devices, panel, sounders and a user handover.",
    "intruder-alarm-near-me": "Intruder alarm near me is that installation or repair, booked against the town rather than a national script.",
    "intruder-alarm-repair": "Intruder alarm repair is for false alarms, dead devices, a panel that will not set, or a sounder that has failed.",
    "intruder-alarm-maintenance": "Intruder alarm maintenance is a planned test of devices, supplies and the log, so the next activation is not the first test.",
    "security-alarm-installation": "Security alarm installation is the install search for an intruder or burglar system. The survey names the openings before devices are fitted.",
    "commercial-intruder-alarm": "A commercial intruder alarm is specified for a shop, warehouse or office, including who holds codes and how the bell is sited.",
    "domestic-burglar-alarm": "A domestic burglar alarm is specified for a house or flat, with a setting routine the household can actually follow.",
    "door-entry-system": "A door entry system lets a flat or office speak to the door and release it. The survey covers the panel, handsets and cable.",
    "door-entry-installation": "Door entry installation is the new panel, handsets and lock release, including how the door behaves if the fire alarm sounds.",
    "door-entry-repair": "Door entry repair is a silent handset, a panel that will not call, or a release that no longer unlocks the door.",
    "door-entry-maintenance": "Door entry maintenance tests calls, releases and power before a resident discovers the fault on a wet night.",
    "door-entry-engineers-near-me": "Door entry engineers near me is a local attendance for that panel and those handsets, quoted from Stockport.",
    "video-door-entry": "Video door entry adds a picture at the door to the call and release. The camera view and the lock are specified together.",
    "anpr-cctv-system": "An ANPR CCTV system is a plate-capture camera and recorder, not a wide overview asked to read plates as an afterthought.",
    "cctv-monitoring": "CCTV monitoring on this page means live view, named app users and alert rules. It is not a claim that we run a watching centre.",
    "intruder-alarm-monitoring": "Intruder alarm monitoring here means how the system signals and who is told. It is not a claim of police response.",
    "alarm-monitoring-setup": "Alarm monitoring setup is the configuration visit: communicators, app users and what a third-party centre would need if you appoint one.",
    "monitored-intruder-alarm": "A monitored intruder alarm search usually wants a centre. We describe the options honestly and do not pretend to be that centre.",
    "emergency-cctv-repair": "Emergency CCTV repair is the call when recording has stopped and the site cannot wait for a routine slot.",
    "emergency-access-control-repair": "Emergency access control repair is a door that will not secure, or will not release, and needs an engineer as soon as one is free.",
    "emergency-alarm-call-out": "An emergency alarm call-out is a panel that will not set, a bell that will not stop, or a system that has gone silent.",
    "24-hour-cctv-engineer": "A 24-hour CCTV engineer search is an out-of-hours fault. We say whether someone can attend. We do not invent a night desk.",
    "same-day-cctv-repair": "Same-day CCTV repair is offered when the diary can take it. The page does not promise a clock-start.",
    "same-day-alarm-repair": "Same-day alarm repair is the same honest offer for an intruder or burglar panel that has failed today.",
    "cctv-servicing": "CCTV servicing is the scheduled health check: pictures, disks, time sync and the accounts that can still sign in.",
    "access-control-servicing": "Access control servicing tests readers, supplies, door position and fire release on the doors in the scope.",
    "intruder-alarm-servicing": "Intruder alarm servicing walks the devices and the panel log, and leaves a note of what was tested.",
    "burglar-alarm-service": "Burglar alarm service is that planned visit under the wording people type when they mean a service call.",
    "door-entry-servicing": "Door entry servicing proves each call button, handset and release that is still in use.",
    "intercom-installation": "Intercom installation is a speech (and sometimes video) path between a door and the rooms that need to answer.",
    "intercom-near-me": "Intercom near me is that installation or repair for the town on the page, surveyed from Stockport.",
    "intercom-repair": "Intercom repair is a dead handset, a humming speech path, or a call button that no longer rings.",
    "video-intercom": "A video intercom is specified when the door needs a picture as well as speech. The view and the lock release are one survey.",
    "paxton-access-control": "Paxton access control means Net2 and related Paxton door controllers already on site, or specified because you asked for that range.",
    "hikvision-cctv-installation": "Hikvision CCTV installation is a camera and recorder job using Hikvision equipment you asked for or already own.",
    "ip-cctv-system": "An IP CCTV system uses network cameras and a recorder or server, with addresses and PoE planned rather than discovered after the ceiling is closed.",
    "wireless-cctv": "Wireless CCTV is chosen where a cable run is unreasonable. The link is surveyed for distance and interference, not assumed.",
    "maglock-installation": "Maglock installation covers the lock, armature, brackets, supply and fire release. It is a pedestrian door, not a car-park barrier.",
    "fob-access-control": "Fob access control is the token, the reader and the rights behind them: who may pass which door, and how a lost fob is removed.",
    "security-systems-near-me": "Security systems near me is the local search that may mean CCTV, access control or an intruder alarm. The quote names which one.",
    "security-system-installation": "Security system installation is the head page for a new CCTV, access or intruder scope. Mixed sites are written as separate sections of one survey.",
    "anpr-installation": "ANPR installation is the lane camera, the lighting and the recorder retention for plate capture.",
    "anpr-near-me": "ANPR near me is that lane survey for the town on the page. Overview cameras are not promised as plate readers.",
    "access-control-emergency": "Access control emergency is the urgent door fault: a lock that will not hold, or a release that will not free the door.",
    "intruder-alarm-emergency": "Intruder alarm emergency is the urgent panel or bell fault. Attendance is offered when an engineer is free.",
    "door-entry-near-me": "Door entry near me is a local panel, handset or release visit for the town named in the search.",
    "cctv-installers-near-me": "CCTV installers near me is the install search with a local town. The design still starts with a survey.",
    "alarm-installers-near-me": "Alarm installers near me is the local install search for an intruder or burglar system.",
}

INCLUDES = {
    "cctv-installation": ["camera positions", "lens and night view", "recorder retention", "cable routes", "remote-viewing accounts", "signage and privacy masks"],
    "cctv-near-me": ["town travel from Stockport", "camera positions", "recorder choice", "viewing accounts", "signage", "a written POA quote"],
    "cctv-repair": ["dead cameras", "recorder faults", "full disks", "PoE and power", "app login failures", "incident export"],
    "cctv-maintenance": ["picture checks", "time sync", "disk health", "IR lamps", "account review", "a written service note"],
    "cctv-engineer": ["a named fault or design", "site access", "camera and recorder checks", "handover notes", "viewing accounts", "a POA quote before attendance"],
    "cctv-engineers-near-me": ["local attendance", "fault or design scope", "recorder access", "camera checks", "handover", "travel included in the quote"],
    "security-camera-installation": ["mount and height", "lens choice", "night performance", "cable or link", "recorder channel", "privacy mask"],
    "commercial-cctv-system": ["shop floor and yard views", "staff viewing rights", "retention period", "export for incidents", "signage", "multi-user accounts"],
    "domestic-cctv-installation": ["door and garden views", "neighbour privacy", "recorder in the home", "phone viewing", "signage where needed", "a simple handover"],
    "access-control-installation": ["doors in scope", "readers", "controller", "tokens or fobs", "power supply", "fire release"],
    "access-control-system": ["door list", "credential types", "time rules", "fire release", "lost-token removal", "a user handover"],
    "access-control-near-me": ["local door survey", "readers and locks", "controller", "fire release", "tokens", "travel in the quote"],
    "access-control-repair": ["doors that will not lock", "doors that will not release", "dead readers", "power supplies", "token faults", "fire-release checks"],
    "access-control-maintenance": ["reader test", "supply test", "door loop", "fire release", "token sample", "a service note"],
    "access-control-engineers": ["door survey", "fault finding", "reader and lock checks", "fire release", "handover", "POA before the visit"],
    "commercial-access-control": ["staff and visitor rights", "office or shop doors", "time zones", "fire release", "lost fobs", "a written door list"],
    "door-access-control": ["maglock or strike", "reader", "supply", "brackets", "fire release", "door closer"],
    "burglar-alarm-installation": ["openings", "detectors", "panel", "sounders", "user codes", "a walk-round handover"],
    "burglar-alarm-system": ["panel type", "detector list", "setting routine", "sounders", "codes", "what the system does not cover"],
    "intruder-alarm-installation": ["doors and rooms", "detectors", "panel", "sounders", "codes", "handover"],
    "intruder-alarm-near-me": ["local survey", "devices", "panel", "sounders", "codes", "travel in the quote"],
    "intruder-alarm-repair": ["false alarms", "devices that will not seal", "panel that will not set", "sounder faults", "power faults", "a written cause"],
    "intruder-alarm-maintenance": ["device tests", "panel log", "standby supply", "sounder test", "code check", "a service note"],
    "security-alarm-installation": ["openings to protect", "detector choice", "panel location", "sounders", "user training", "a POA quote"],
    "commercial-intruder-alarm": ["shop or warehouse openings", "who holds codes", "bell siting", "setting routine", "device list", "handover to the manager"],
    "domestic-burglar-alarm": ["house or flat layout", "entry route", "a setting routine the household will use", "sounders", "codes", "pet or hallway notes"],
    "door-entry-system": ["entrance panel", "handsets", "cable", "lock release", "trades buttons", "fire release where required"],
    "door-entry-installation": ["new panel", "handsets", "cable routes", "lock release", "power", "fire interface"],
    "door-entry-repair": ["silent handsets", "panels that will not call", "releases that stay locked", "broken buttons", "power faults", "a proved call before we leave"],
    "door-entry-maintenance": ["call test", "release test", "handset audio", "power", "button wear", "a service note"],
    "door-entry-engineers-near-me": ["local panel attendance", "handsets", "release", "cable faults", "handover", "travel in the quote"],
    "video-door-entry": ["door camera view", "monitor", "speech", "lock release", "night view at the door", "privacy of the camera aim"],
    "anpr-cctv-system": ["lane camera", "height and angle", "night lighting", "shutter", "retention of plate images", "a separate note if a barrier is also on site"],
    "cctv-monitoring": ["live-view layout", "named users", "alert rules", "who may export", "bandwidth", "no watching-centre claim"],
    "intruder-alarm-monitoring": ["how the panel signals", "who is called", "app or communicator", "what is not police response", "a written description", "your own monitoring contract if you want one"],
    "alarm-monitoring-setup": ["communicator", "app users", "site list a centre would need", "test signals", "contact list", "honest limits"],
    "monitored-intruder-alarm": ["panel and communicator", "who receives signals", "what iComply does not operate", "app alerts", "a survey of the existing panel", "a POA quote"],
    "emergency-cctv-repair": ["recorder down", "cameras dark", "disk full", "no remote view", "an engineer if one is free", "a POA quote on the call"],
    "emergency-access-control-repair": ["door not secure", "door not releasing", "dead reader", "supply failed", "fire release checked", "attendance when an engineer is free"],
    "emergency-alarm-call-out": ["panel will not set", "bell will not stop", "system silent", "false alarms in a run", "cause note", "attendance when free"],
    "24-hour-cctv-engineer": ["out-of-hours call", "honest yes or no on attendance", "recorder or camera fault", "no night-desk claim", "POA", "follow-up if the diary is full"],
    "same-day-cctv-repair": ["today's fault", "diary check", "camera or recorder", "no clock-start promise", "POA", "a note of what was restored"],
    "same-day-alarm-repair": ["today's panel fault", "diary check", "bell or device", "no clock-start promise", "POA", "a cause note"],
    "cctv-servicing": ["scheduled picture check", "disk health", "time", "accounts", "IR and focus", "service note"],
    "access-control-servicing": ["scheduled reader test", "supplies", "door position", "fire release", "sample token", "service note"],
    "intruder-alarm-servicing": ["scheduled device walk", "panel log", "standby", "sounder", "codes", "service note"],
    "burglar-alarm-service": ["planned service visit", "devices", "panel", "sounders", "log", "note left with the keyholder"],
    "door-entry-servicing": ["call buttons in use", "handsets", "release", "audio", "power", "service note"],
    "intercom-installation": ["door station", "handsets", "cable", "speech quality", "lock release if fitted", "handover"],
    "intercom-near-me": ["local survey", "door station", "handsets", "cable", "release", "travel in the quote"],
    "intercom-repair": ["dead handsets", "hum or no speech", "call button", "power", "cable faults", "a proved call"],
    "video-intercom": ["door picture", "monitor", "speech", "release", "night view", "camera aim"],
    "paxton-access-control": ["Net2 or the Paxton controller on site", "doors", "tokens", "fire release", "software access", "no dealer-badge claim"],
    "hikvision-cctv-installation": ["Hikvision cameras asked for or already fitted", "recorder", "retention", "viewing accounts", "signage", "no dealer-badge claim"],
    "ip-cctv-system": ["network cameras", "PoE switch", "addresses", "recorder", "remote viewing", "VLAN or separation where the IT setup needs it"],
    "wireless-cctv": ["link distance", "interference", "power at the camera", "recorder", "a cable alternative if the link is weak", "retention"],
    "maglock-installation": ["maglock and armature", "brackets", "supply", "fire release", "fail-safe or fail-secure chosen for that door", "not a barrier arm"],
    "fob-access-control": ["fobs or cards", "readers", "who may pass", "removing a lost fob", "fire release", "a token list"],
    "security-systems-near-me": ["CCTV if that is the need", "access control if that is the need", "an intruder alarm if that is the need", "one written scope", "Stockport travel", "POA"],
    "security-system-installation": ["CCTV section", "access section", "intruder section", "only the sections you need", "one survey", "separate quotes if the trades should not be bundled"],
    "anpr-installation": ["lane camera", "lighting", "angle and height", "recorder retention", "shutter", "barrier work excluded"],
    "anpr-near-me": ["local lane survey", "plate camera", "lighting", "retention", "not an overview camera", "travel in the quote"],
    "access-control-emergency": ["urgent lock fault", "urgent release fault", "reader down", "fire release", "engineer if free", "POA on the call"],
    "intruder-alarm-emergency": ["urgent panel fault", "bell fault", "engineer if free", "no night-desk claim", "cause note", "POA"],
    "door-entry-near-me": ["local panel", "handsets", "release", "cable", "travel in the quote", "POA"],
    "cctv-installers-near-me": ["local install survey", "cameras", "recorder", "cabling", "signage", "handover"],
    "alarm-installers-near-me": ["local alarm survey", "detectors", "panel", "sounders", "codes", "handover"],
}


def service_for(slug: str) -> str:
    if slug.startswith("intercom") or slug == "video-intercom":
        return "intercoms"
    if "door-entry" in slug or slug == "video-door-entry":
        return "door-entry"
    if any(
        token in slug
        for token in (
            "burglar",
            "intruder",
            "security-alarm",
            "alarm-monitoring",
            "monitored-intruder",
            "alarm-installers",
            "same-day-alarm",
            "emergency-alarm",
        )
    ):
        return "intruder-alarm"
    if any(token in slug for token in ("access-control", "door-access", "paxton", "maglock", "fob-access")):
        return "access-control"
    return "cctv"


SERVICE_NAME = {
    "cctv": "CCTV",
    "access-control": "access control",
    "intruder-alarm": "intruder alarms",
    "door-entry": "door entry",
    "intercoms": "intercoms",
}

SERVICE_HREF = {
    "cctv": "/pages/services/cctv",
    "access-control": "/pages/services/access-control",
    "intruder-alarm": "/pages/services/intruder-alarm",
    "door-entry": "/pages/services/door-entry",
    "intercoms": "/pages/services/intercoms",
}

HONEST = {
    "cctv": "Camera design is discussed against BS EN 62676 practice, including signage and privacy masks where a view would cover neighbours. iComply does not invent a CCTV certification badge.",
    "access-control": "Door hardware is chosen for the door in front of us, including fire release where the door must open on alarm. iComply does not claim a scheme badge for access control.",
    "intruder-alarm": "Intruder and burglar alarm design is discussed against BS EN 50131 practice. iComply does not issue a graded-system certificate and does not claim NSI or SSAIB membership.",
    "door-entry": "Door entry work covers the panel, handsets, cabling and release on the entrance you have. iComply does not invent a product approval you were not shown.",
    "intercoms": "Intercom work covers speech, and video where the spec includes it, plus the cable and the release if one is fitted. iComply does not invent a product approval.",
}

MONITORING = (
    "Remote viewing, named app users and alert rules can be configured. "
    "iComply does not operate an alarm receiving centre, does not claim police response, "
    "and does not claim a police URN, NSI membership or SSAIB membership. "
    "A third-party monitoring contract is something you may ask about and arrange yourself. "
    "It is not described on this page as a centre we run."
)
EMERGENCY = (
    "Emergency, same-day and 24-hour wording is answered honestly. "
    "If an engineer is free, we say so and quote the visit. "
    "This page does not promise a manned night desk, a clock-start, or attendance on every call."
)
BRAND = (
    "The brand name describes equipment already on site or equipment you asked for. "
    "It is not a claim of dealer status, a partnership badge, or a scheme membership."
)
ANPR = (
    "ANPR here is a camera and recorder job for plate capture. "
    "A barrier arm or vehicle gate is a different product and is not part of this quote. "
    "If the lane needs both, they are separate surveys."
)


def action_for(slug: str) -> str:
    if "monitor" in slug:
        return "monitoring"
    if slug.startswith("emergency") or "24-hour" in slug or "same-day" in slug or slug.endswith("-emergency"):
        return "emergency"
    if "repair" in slug or "call-out" in slug:
        return "repair"
    if "maintenance" in slug or "servicing" in slug or slug.endswith("-service"):
        return "maintenance"
    if "near-me" in slug or "engineer" in slug:
        return "near"
    if slug in ("paxton-access-control", "hikvision-cctv-installation"):
        return "brand"
    if "anpr" in slug:
        return "anpr"
    if any(token in slug for token in ("wireless", "ip-cctv", "video-", "maglock", "fob-access")):
        return "product"
    if "commercial" in slug or "domestic" in slug:
        return "segment"
    return "install"


def who_for(slug: str) -> str:
    if "commercial" in slug:
        return "shops, warehouses, offices and managing agents"
    if "domestic" in slug:
        return "homeowners, landlords and small blocks"
    if "door-entry" in slug or "intercom" in slug or "maglock" in slug or "fob" in slug:
        return "landlords, block managers and commercial occupiers"
    return "landlords, letting agents and commercial occupiers"


def honest_for(slug: str, service: str, action: str) -> str:
    parts = [HONEST[service]]
    if action == "monitoring" or "monitor" in slug:
        parts.append(MONITORING)
    if action == "emergency":
        parts.append(EMERGENCY)
    if action == "brand":
        parts.append(BRAND)
    if action == "anpr" or "anpr" in slug:
        parts.append(ANPR)
    if slug in ("security-systems-near-me", "security-system-installation"):
        parts.append(
            "CCTV, access control and intruder alarms are named separately in the quote. "
            "A mixed enquiry is not collapsed into one vague package."
        )
    return " ".join(parts)


def word_count(text: str) -> int:
    return len(re.findall(r"[A-Za-z0-9']+", text))


def build_paragraphs(spec: dict, surface: str) -> list[str]:
    name = spec["name"]
    detail = spec["detail"]
    inc = spec["includes"]
    fam = spec["family"]
    who = spec["who"]
    honest = spec["honest"]
    action = spec["action"]
    guide = "This guide" if surface == "keyword" else "This job"
    visit = "the written scope" if surface == "keyword" else "the visit"
    i0, i1, i2, i3, i4, i5 = inc
    joined = f"{i0}, {i1}, {i2}, {i3}, {i4} and {i5}"
    opening = (
        f"{name} is arranged by iComply Property Services from the workshop at {NAP}. "
        f"{detail} {guide} is for {who}. "
        f"The work is price on application after the building, the existing equipment and the access are known. "
        f"Nothing on this page is a fixed fee, a day rate or a pound figure."
    )
    if surface == "job":
        opening = (
            f"{name} is the job of attending, not only a definition. {detail} "
            f"The engineer works to {visit} for {who}. "
            f"Call {PHONE} or use the contact form. The reply quotes the work as price on application "
            f"and names what is in scope before a date is fixed. The workshop is {NAP}."
        )
    paras = [
        opening,
        (
            f"The scope for {name} is written as six practical items: {joined}. "
            f"Each item is confirmed against the site. {i0.capitalize()} is agreed first, because the rest of the job hangs on it. "
            f"{i1.capitalize()} is checked on site rather than copied from a previous quote. "
            f"{i2.capitalize()} is included when the survey shows it is required, and left out when it is not. "
            f"iComply does not pad {name} with equipment you did not ask for and do not need."
        ),
        (
            f"{i3.capitalize()} is part of {name} when the building needs it. "
            f"{i4.capitalize()} is recorded in the handover so the next person is not guessing. "
            f"{i5.capitalize()} is the item that is most often missed when a job is sold as a box of parts. "
            f"{guide} keeps that item in the survey for {fam}. "
            f"If one of these six items does not apply, the quote says so in writing instead of leaving a blank."
        ),
        (
            f"People asking for {name} are usually {who}. "
            f"A landlord, an agent or a facilities lead can instruct the job. "
            f"The person who meets the engineer needs the keys, the codes and, where a recorder or controller already exists, the login. "
            f"Occupied buildings are booked around the people who live or work there. "
            f"Void and empty units are booked around access from the agent. "
            f"{name} is not sold as a remote product with no one on site when the work needs a door, a panel or a camera to be seen."
        ),
        (
            f"The survey for {name} can start from photos and a postcode, and it finishes on site when the hardware has to be handled. "
            f"We note the existing {fam} equipment, what still works, and what has failed. "
            f"A system fitted by someone else can still be repaired or extended when the parts and the passwords allow it. "
            f"If the existing kit cannot do {i1}, we say so and quote a replacement of that part rather than promising the old unit will cope. "
            f"Cabling, power and mounting are looked at before anyone talks about a new head-end."
        ),
        (
            f"On the day, {visit} for {name} follows the list you accepted. "
            f"The engineer deals with {i0} and {i1}, then proves {i2}. "
            f"Results are written in ordinary language: what was found, what was changed, and what was left as it was. "
            f"Passwords stay with the instructing client. "
            f"We do not leave a shared installer login as the only way to see the system. "
            f"If a follow-on visit is needed, it is a new quote, not a surprise return."
        ),
        (
            f"Handover for {name} includes the practical points {who} ask for later: how to set or view the system, who has a token or an account, and where the records are. "
            f"{i3.capitalize()} and {i4} are shown to the person on site, not only written on a delivery note. "
            f"Signage, privacy and fire release are called out when they apply to this {fam} job. "
            f"The note names the building. It is not a generic certificate with the town filled in afterwards."
        ),
        (
            f"Limits are part of the page, not a footnote. {honest} "
            f"Grade claims, scheme badges and police-response wording are not invented to decorate {name}. "
            f"If a standard is relevant, it is named as the practice we design against, not as a badge iComply prints for itself. "
            f"Gas work is not part of this security job. Where a site also needs a gas visit, that visit is carried out by Gas Safe registered engineers and is quoted separately."
        ),
        (
            f"Price on application means the quote follows the survey. "
            f"Camera count, door count, cable runs, night lighting, access equipment and whether the building is occupied all change the figure for {name}. "
            f"Travel from {NAP} is inside that quote for towns on the Manchester and Burnley dual ring. "
            f"There is no catalogue fee on this page and no 'from' price. "
            f"A multi-site estate is quoted per site or as a programme, and the email says which."
        ),
        (
            f"Coverage for {name} is the dual ring: towns within 50 miles of Manchester together with towns within 50 miles of Burnley, including the Greater Manchester core. "
            f"That set is 269 towns. It is not the UK top-5000 list used for nationwide fire, barrier and AOV pages, and it is not shrunk to the 60-town core. "
            f"Each town has its own page so the local paragraph can name the county, the published population where we have one, and the miles from Manchester and Burnley. "
            f"The parent page stays this hub. Town pages link back here and to the {fam} service page."
        ),
        (
            f"Related work sits beside {name} but is not silently included. "
            f"CCTV, access control, door entry, intercoms and intruder alarms are separate services with their own hubs. "
            f"A car-park barrier is not an ANPR camera and is not a maglock. "
            f"If your site needs more than one of those, the survey splits them so the quote is readable. "
            f"{guide} links to the parent service and to a related security hub rather than to a dead end."
        ),
        (
            f"To ask for {name}, send the postcode, the building type, what is already fitted, and whether the issue is a new install, a fault or a planned service. "
            f"Photos of the recorder, panel, door or camera help. "
            f"Phone {PHONE} or email info@icomplypropertyservices.co.uk. "
            f"The reply is a price-on-application quote for {joined}. "
            f"Work starts after you accept that quote. "
            f"{'The town page adds the local paragraph for the building you named.' if surface == 'keyword' else 'Bring door codes, recorder or panel passwords, and the name of the person who can stay while the job is done.'}"
        ),
        (
            f"After {name}, the useful test is simple. "
            f"For cameras, can the right person export a clip from the day of the visit. "
            f"For doors, does the token open the door and does fire release let it go. "
            f"For alarms, can the keyholder set and unset, and does the sounder run when it should. "
            f"For intercoms and door entry, can the handset call the door and release it. "
            f"If that test fails, the job is not finished, even if the equipment is on the wall. "
            f"That standard is how {i5} is judged on this {action} brief for {fam}."
        ),
    ]
    return paras


def fit_meta(text: str) -> str:
    text = re.sub(r"\s+", " ", text).strip()
    if "POA" not in text:
        text = text.rstrip(".") + ". Request a quote — POA."
    guard = 0
    while len(text) < 140 and guard < 4:
        text = text.rstrip(".") + ". Request a quote — POA."
        guard += 1
    if len(text) > 160:
        cut = text[:160]
        if " " in cut:
            cut = cut.rsplit(" ", 1)[0]
        text = cut.rstrip(" ,;:—-")
        if "POA" not in text:
            suffix = " POA."
            room = 160 - len(suffix)
            text = text[:room].rsplit(" ", 1)[0].rstrip(" ,;:—-") + suffix
    if len(text) > 160:
        text = text[:160].rsplit(" ", 1)[0]
    return text


def title_hub(name: str) -> str:
    long = f"{name} | iComply Property Services"
    if len(long) <= 65:
        return long
    return f"{name} — iComply"


def load_images() -> dict[str, list[str]]:
    manifest = json.loads((ROOT / "website/data/image-manifest.json").read_text())
    pools: dict[str, list[str]] = {key: [] for key in SERVICE_NAME}
    for path in manifest:
        if not path.endswith(".jpg") or "-photo" in path:
            continue
        if "/keywords/" not in path and "/services/" not in path:
            continue
        low = path.lower()
        if "cctv" in low or "anpr" in low or "hikvision" in low:
            pools["cctv"].append(path)
        if "access-control" in low or "paxton" in low or "maglock" in low:
            pools["access-control"].append(path)
        if "intruder" in low or "burglar" in low:
            pools["intruder-alarm"].append(path)
        if "door-entry" in low:
            pools["door-entry"].append(path)
        if "intercom" in low:
            pools["intercoms"].append(path)
    for key, pool in pools.items():
        pool = sorted(set(pool))
        fallback = f"/assets/images/services/{key}.jpg"
        if fallback not in pool:
            pool.insert(0, fallback)
        if len(pool) < 3:
            raise SystemExit(f"image pool short for {key}: {pool}")
        pools[key] = pool
    return pools


def pick_images(pools: dict[str, list[str]], service: str, slug: str, name: str) -> list[dict]:
    pool = pools[service]
    start = zlib.crc32(slug.encode()) % len(pool)
    chosen = []
    i = start
    while len(chosen) < 3:
        src = pool[i % len(pool)]
        if src not in chosen:
            chosen.append(src)
        i += 1
    alts = [
        f"{name} survey photography — iComply Property Services",
        f"{name} equipment — Stockport workshop",
        f"{SERVICE_NAME[service]} installation photography for {name}",
    ]
    return [{"src": src, "alt": alt} for src, alt in zip(chosen, alts)]


def load_towns() -> dict[str, dict]:
    rows = []
    with TOWN_CSV.open(newline="") as handle:
        for row in csv.DictReader(handle):
            slug = (row.get("slug") or "").strip()
            if not slug:
                continue
            def num(key: str):
                raw = (row.get(key) or "").strip()
                if raw == "":
                    return None
                return float(raw) if "." in raw else int(raw)

            pop = num("population")
            rows.append(
                {
                    "slug": slug,
                    "name": (row.get("name") or slug).strip(),
                    "county": (row.get("county") or "").strip(),
                    "population": int(pop) if isinstance(pop, float) and pop.is_integer() else pop,
                    "mi_manchester": num("mi_manchester"),
                    "mi_burnley": num("mi_burnley"),
                    "bucket": (row.get("bucket") or "").strip(),
                    "circles": (row.get("circles") or "").strip(),
                }
            )
    if len(rows) != 269:
        raise SystemExit(f"expected 269 towns, got {len(rows)}")
    by_county: dict[str, list[str]] = {}
    for row in rows:
        by_county.setdefault(row["county"] or row["slug"], []).append(row["slug"])
    towns = {}
    slugs = [row["slug"] for row in rows]
    for index, row in enumerate(rows):
        near = [s for s in by_county.get(row["county"] or row["slug"], []) if s != row["slug"]][:4]
        if len(near) < 3:
            for step in range(1, 8):
                candidate = slugs[(index + step) % len(slugs)]
                if candidate not in near and candidate != row["slug"]:
                    near.append(candidate)
                if len(near) >= 4:
                    break
        row["near"] = near
        towns[row["slug"]] = row
    return towns


def load_p0() -> list[dict]:
    found = []
    with INTENT_CSV.open(newline="") as handle:
        for row in csv.DictReader(handle):
            if (row.get("intent_tier") or "").strip() != "P0":
                continue
            slug = (row.get("slug") or "").strip()
            found.append(
                {
                    "slug": slug,
                    "kind": (row.get("kind") or "").strip(),
                    "status": (row.get("status") or "").strip(),
                    "rank": int(row["rank"]),
                }
            )
    if len(found) != 67:
        raise SystemExit(f"expected 67 P0 intents, got {len(found)}")
    missing = [row["slug"] for row in found if row["slug"] not in NAMES or row["slug"] not in DETAILS or row["slug"] not in INCLUDES]
    if missing:
        raise SystemExit("missing copy for " + ", ".join(missing))
    return found


def faqs_for(spec: dict) -> list[dict]:
    name = spec["name"]
    inc = spec["includes"]
    return [
        {
            "q": f"How much does {name} cost?",
            "a": (
                f"{name} is price on application. iComply does not publish a fee, a day rate or a from-price for this work. "
                f"The quote follows the survey and includes travel from {NAP} for towns on the dual ring. "
                f"Camera counts, door counts, cable runs and occupied access all change the figure."
            ),
        },
        {
            "q": f"What does {name} include?",
            "a": (
                f"The written scope covers {inc[0]}, {inc[1]}, {inc[2]}, {inc[3]}, {inc[4]} and {inc[5]}. "
                f"Items that do not apply to the building are marked as excluded rather than left vague."
            ),
        },
        {
            "q": f"Do you take over equipment you did not install?",
            "a": (
                f"Yes, when the hardware and the logins allow it. {spec['detail']} "
                f"If a unit cannot do the job, the quote says so instead of promising a repair that will not hold."
            ),
        },
        {
            "q": f"Are you NSI or SSAIB registered for {name}?",
            "a": (
                f"No. iComply does not claim NSI or SSAIB membership, a police URN, or a graded-system certificate on this page. "
                f"{spec['honest']}"
            ),
        },
        {
            "q": f"How do I book {name}?",
            "a": (
                f"Phone {PHONE} or email info@icomplypropertyservices.co.uk with the postcode, the building and whether you need an install, a repair or a service. "
                f"You receive a price-on-application quote before a date is fixed. The workshop address is {NAP}."
            ),
        },
    ]


def related_slug(slug: str, service: str, p0_slugs: list[str]) -> str:
    same = [item for item in p0_slugs if item != slug and service_for(item) == service]
    if same:
        return same[0]
    others = [item for item in p0_slugs if item != slug]
    return others[0]


def write_stub(path: Path, body: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    if path.exists() and "securityDualRingRender" in path.read_text():
        return
    path.write_text(body)


def main() -> None:
    towns = load_towns()
    p0 = load_p0()
    pools = load_images()
    p0_slugs = [row["slug"] for row in p0]
    intents = {}
    for row in p0:
        slug = row["slug"]
        service = service_for(slug)
        action = action_for(slug)
        spec = {
            "name": NAMES[slug],
            "detail": DETAILS[slug],
            "includes": INCLUDES[slug],
            "family": SERVICE_NAME[service],
            "who": who_for(slug),
            "action": action,
            "honest": honest_for(slug, service, action),
        }
        paragraphs = build_paragraphs(spec, "keyword")
        job_paragraphs = build_paragraphs(spec, "job") if row["kind"] == "both" else []
        body = " ".join(paragraphs)
        if word_count(body) < 800:
            raise SystemExit(f"{slug} keyword body {word_count(body)} words")
        if job_paragraphs and word_count(" ".join(job_paragraphs)) < 800:
            raise SystemExit(f"{slug} job body short")
        banned = re.compile(r"£|\bapproved subcontractors?\b|\bb\d{4,}\b", re.I)
        blob = body + " " + " ".join(job_paragraphs)
        if banned.search(blob):
            raise SystemExit(f"banned wording in {slug}")
        meta = fit_meta(
            f"{NAMES[slug]} for {who_for(slug)} across the Manchester and Burnley ring. "
            f"{SERVICE_NAME[service]} surveyed from Stockport. Request a quote — POA."
        )
        if not 140 <= len(meta) <= 160:
            raise SystemExit(f"meta length {len(meta)} for {slug}: {meta}")
        intents[slug] = {
            "name": NAMES[slug],
            "kind": row["kind"],
            "status": row["status"],
            "rank": row["rank"],
            "service": service,
            "service_name": SERVICE_NAME[service],
            "service_href": SERVICE_HREF[service],
            "related": related_slug(slug, service, p0_slugs),
            "action": action,
            "images": pick_images(pools, service, slug, NAMES[slug]),
            "paragraphs": paragraphs,
            "job_paragraphs": job_paragraphs,
            "faqs": faqs_for(spec),
            "title_hub": title_hub(NAMES[slug]),
            "meta_hub": meta,
            "extra_links": (
                [
                    {"href": "/pages/services/access-control", "label": "Access control"},
                    {"href": "/pages/services/intruder-alarm", "label": "Intruder alarms"},
                ]
                if slug in ("security-systems-near-me", "security-system-installation")
                else []
            ),
        }
        if row["status"] == "create" or not (KW_DIR / f"{slug}.php").exists():
            write_stub(
                KW_DIR / f"{slug}.php",
                "<?php\n"
                "/** Security dual-ring P0 keyword hub. */\n"
                "require_once __DIR__ . '/../../includes/render.php';\n"
                f"renderKeywordPage({slug!r});\n",
            )
        if row["kind"] == "both":
            write_stub(
                JOB_DIR / f"{slug}.php",
                "<?php\n"
                "/** Security dual-ring P0 job hub. */\n"
                "require_once __DIR__ . '/../../config.php';\n"
                "require_once SITE_ROOT . '/includes/security-dual-ring.php';\n"
                f"securityDualRingRender('job', {slug!r}, '');\n",
            )

    created = sum(1 for row in p0 if row["status"] == "create")
    both = sum(1 for row in p0 if row["kind"] == "both")
    keyword_only = sum(1 for row in p0 if row["kind"] == "keyword")
    payload = {
        "nap": NAP,
        "phone": PHONE,
        "town_count": len(towns),
        "p0_count": len(intents),
        "follow_on": "P1 is 133 security intents. Hubs and dual-269 town pages for P1 are a follow-on wave and are not in this pack.",
        "counts": {"create": created, "both": both, "keyword_only": keyword_only},
        "towns": towns,
        "intents": intents,
    }
    OUT_JSON.write_text(json.dumps(payload, ensure_ascii=False, separators=(",", ":")) + "\n")
    OUT_JS.parent.mkdir(parents=True, exist_ok=True)
    OUT_JS.write_text("export const securityDualRingPack = " + json.dumps(payload, ensure_ascii=False, separators=(",", ":")) + ";\n")
    print(f"towns={len(towns)} p0={len(intents)} create={created} both={both} keyword_only={keyword_only}")
    print(f"json_bytes={OUT_JSON.stat().st_size} js_bytes={OUT_JS.stat().st_size}")


if __name__ == "__main__":
    main()
