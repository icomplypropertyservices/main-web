#!/usr/bin/env python3
"""Build the asbestos survey + awareness job catalogue.

Survey pages scope management, refurbishment, reinspection and sampling visits.
Awareness pages are dutyholder briefings beside a survey. Neither lane is
licensed removal, and neither claims a training-body or laboratory badge.

Usage: python3 website/bin/build-asbestos-jobs.py
"""
from __future__ import annotations

import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "data" / "asbestos-jobs.json"

SURVEY_LIMIT = (
    "Licensed asbestos removal is by others. If the record says removal is required, "
    "appoint a suitable licensed contractor. Quotes are POA after age, access and the "
    "planned works are known. This page does not state a laboratory or surveyor accreditation."
)
AWARE_LIMIT = (
    "This briefing is not an accredited training certificate and does not replace a "
    "management or refurbishment survey. Licensed asbestos removal is by others. "
    "Quotes are POA. This page does not state a training-body approval."
)


def job(
    slug: str,
    name: str,
    lane: str,
    intro: str,
    body: str,
    meta: str,
    focus: list[str],
    faqs: list[list[str]],
    related: str,
) -> dict:
    limit = SURVEY_LIMIT if lane == "survey" else AWARE_LIMIT
    title_lane = "Asbestos survey" if lane == "survey" else "Asbestos awareness"
    return {
        "slug": slug,
        "name": name,
        "lane": lane,
        "service": "asbestos-survey",
        "related": related,
        "seo_title": f"{name} | {title_lane} North West",
        "h1": name,
        "intro": intro,
        "body": body.rstrip() + " " + limit,
        "meta_desc": meta,
        "seo_keywords": f"{name}, asbestos {lane}, North West, Stockport, Manchester, POA",
        "focus_points": focus + ["POA — licensed removal is by others"],
        "faq": faqs,
    }


SURVEY = [
    job(
        "asbestos-survey",
        "Asbestos Survey",
        "survey",
        "An asbestos survey records where asbestos-containing materials may be, so a dutyholder can manage them. iComply scopes management and refurbishment-style surveys for North West property. Price on application.",
        "Survey type follows whether the building is in normal occupation or about to be opened up. Sampling, where the scope needs it, is agreed before the visit.",
        "Asbestos survey for management or refurbishment across Greater Manchester. POA. No invented licences or prices.",
        ["Management versus refurbishment scoped honestly", "Sampling only where the visit needs it", "Register-style notes for the dutyholder file"],
        [
            ["Do you remove asbestos?", "Not on this service. If removal is required we say so and you appoint a suitable licensed contractor."],
            ["Which survey do I need?", "A management survey is for normal occupation. A refurbishment or demolition survey is for intrusive works."],
        ],
        "asbestos-management-survey",
    ),
    job(
        "asbestos-management-survey",
        "Asbestos Management Survey",
        "survey",
        "A management survey is the usual starting point for the duty to manage asbestos during normal occupation. iComply scopes these for commercial units, common parts and workplaces. POA.",
        "The visit looks at reasonably accessible materials that day-to-day use or maintenance could disturb. It is not a demolition survey. Areas that cannot be seen are recorded as such.",
        "Asbestos management survey for occupied North West buildings. POA. Duty to manage, no fake licence claims.",
        ["Normal occupation and maintenance access", "Inaccessible areas called out honestly", "Register-style output for the dutyholder"],
        [["Will you open up every void?", "Not on a management survey. Intrusive opening belongs on a refurbishment or demolition survey."]],
        "asbestos-refurbishment-survey",
    ),
    job(
        "asbestos-refurbishment-survey",
        "Asbestos Refurbishment Survey",
        "survey",
        "A refurbishment survey is more intrusive and is used before strip-out, major works or demolition. iComply quotes these as POA once the rooms and the planned work are known.",
        "You need this when a management survey is not enough because works will open walls, floors or plant. Access and making-good expectations are agreed before anyone starts.",
        "Asbestos refurbishment survey before works in the North West. POA after the opening-up is known.",
        ["Matched to the planned refurbishment or strip", "Intrusive access agreed in writing", "Not sold as a cheap add-on to a management visit"],
        [["Can you do this while staff are in the building?", "Sometimes, room by room, if access is safe. We say when an area needs to be empty."]],
        "asbestos-management-survey",
    ),
    job(
        "asbestos-testing",
        "Asbestos Testing",
        "survey",
        "Asbestos testing means sampling suspect material during a scoped survey so you know what you are managing. iComply includes testing when the visit needs it. POA.",
        "A sample is only useful if the location is recorded and the result goes into the management file. We do not scrape decorative coatings without a reason, and we do not accept posted debris.",
        "Asbestos testing and sampling as part of a scoped survey. North West. POA.",
        ["Suspect materials recorded before sampling", "Results for the management file", "Sample count agreed in the quote"],
        [
            ["Can I post you a bit of tile?", "Uncontrolled sampling can spread fibres. Arrange a scoped visit instead of posting debris."],
            ["Is every textured coating asbestos?", "No. Only analysis tells you, and only when the survey scope includes a sample."],
        ],
        "asbestos-sample-testing",
    ),
    job(
        "asbestos-sample-testing",
        "Asbestos Sample Testing",
        "survey",
        "Asbestos sample testing is the analysis step for materials collected during a scoped survey. Quoted POA against an agreed sample count. No postal DIY kit.",
        "Each sample should have a location and a result that feeds the register. The analysis route is confirmed at quote time. This is not a consumer kit with a published price.",
        "Asbestos sample testing arranged with a scoped survey. North West. POA.",
        ["Samples tied to rooms and materials", "Results written back to the register", "Count agreed before the visit"],
        [["How many samples will you take?", "As few as needed to answer the survey question. The number is in the scope, not a surprise add-on."]],
        "asbestos-testing",
    ),
    job(
        "asbestos-inspection",
        "Asbestos Inspection",
        "survey",
        "An asbestos inspection can mean a re-inspection of known materials or a first look that leads to a full survey. iComply will say which you need. POA.",
        "Re-inspections check the condition of items already on a register. A first visit to an unknown building is usually a survey, not a ten-minute glance sold as the same thing.",
        "Asbestos inspection or re-inspection of known materials. North West. POA.",
        ["Re-inspection versus full survey explained", "Condition notes for an existing register", "Not a shortcut before opening a wall"],
        [["Is an inspection enough before I knock a wall down?", "No. Intrusive works need the right survey type, not a visual glance."]],
        "asbestos-survey",
    ),
    job(
        "landlord-asbestos-survey",
        "Landlord Asbestos Survey",
        "survey",
        "Landlords of non-domestic parts and many blocks need to manage asbestos in common areas. iComply scopes landlord asbestos surveys from Stockport — management or refurbishment, quoted POA.",
        "A purely domestic house is a different duty from a conversion with shared halls. We ask which you have before naming the survey type. There is no published landlord-pack price.",
        "Landlord asbestos survey for common parts and rented stock in the North West. POA.",
        ["Common parts versus a purely domestic let", "File-ready notes for agents", "Survey type matched to planned works"],
        [["Does every rented terrace need a full survey?", "Not automatically. Duty depends on the building. Describe the property and we will say what is proportionate."]],
        "commercial-asbestos-survey",
    ),
    job(
        "commercial-asbestos-survey",
        "Commercial Asbestos Survey",
        "survey",
        "Commercial asbestos surveys support the duty to manage in workplaces, shops and industrial units. iComply quotes POA after floor area, era and access are known.",
        "Older retail and industrial stock around Greater Manchester often still has historic materials. The survey is how maintenance stops guessing. We still do not claim licensed removal.",
        "Commercial asbestos survey across Greater Manchester and the North West. POA.",
        ["Workplaces, shops and industrial units", "Management or refurbishment matched to the job", "Access and shutdown agreed first"],
        [["Can you survey a live warehouse?", "Often yes if safe routes are agreed. Some plant rooms need a shutdown, and we say so before attending."]],
        "landlord-asbestos-survey",
    ),
    job(
        "asbestos-reinspection",
        "Asbestos Reinspection",
        "survey",
        "An asbestos reinspection checks the condition of materials already on a register, on the interval your management plan sets. iComply scopes the visit from the existing record. POA.",
        "This is not a first survey of an unknown building. We need the current register, the last inspection date and access to the same locations. Deterioration is noted; new intrusive opening is a different survey.",
        "Asbestos reinspection of a known register across the North West. POA. Not a first survey.",
        ["Uses the register you already hold", "Condition change recorded against known items", "Does not pretend to be a refurbishment survey"],
        [["How often is a reinspection?", "The interval follows risk and your management plan. We do not invent a calendar date for marketing."]],
        "asbestos-inspection",
    ),
    job(
        "refurbishment-asbestos-survey-support",
        "Refurbishment Asbestos Survey Support",
        "survey",
        "Refurbishment asbestos survey support lines the survey up with the rooms and trades that will open the building. iComply quotes it POA once drawings or a room list exist.",
        "Contractors need to know which areas are in the intrusive scope and which are still management-only. We record that split. We do not start strip-out, and we do not remove materials.",
        "Refurbishment asbestos survey support before strip-out in the North West. POA.",
        ["Scope tied to the refurbishment drawings", "Rooms in and out of the intrusive survey listed", "Making-good expectations agreed first"],
        [["Can this wait until the strip starts?", "No. The survey has to be agreed before opening-up, not discovered halfway through."]],
        "asbestos-refurbishment-survey",
    ),
    job(
        "demolition-asbestos-survey-liaison",
        "Demolition Asbestos Survey Liaison",
        "survey",
        "Demolition asbestos survey liaison is the scoping conversation before a building comes down. iComply helps the dutyholder describe what a fully intrusive survey must cover. POA.",
        "Demolition needs a different survey from day-to-day management. We liaise on access, isolation and the areas that will be disturbed. Licensed removal, if the survey requires it, is appointed separately.",
        "Demolition asbestos survey liaison for North West sites. POA. Removal is by others.",
        ["Scoped to demolition, not occupation", "Access and isolation listed before the visit", "Removal stays with a licensed contractor"],
        [["Do you demolish or remove the materials?", "No. This page is survey liaison only. Removal and demolition are other contractors."]],
        "asbestos-refurbishment-survey",
    ),
    job(
        "landlord-asbestos-register-update",
        "Landlord Asbestos Register Update",
        "survey",
        "A landlord asbestos register update writes new survey or reinspection notes into the file agents and contractors actually use. iComply quotes the update POA from the papers you already hold.",
        "An update is only as good as the visit behind it. If the building has never been surveyed, we say so and scope a survey instead of editing a blank register.",
        "Landlord asbestos register update for North West portfolios. POA. Needs a real survey behind it.",
        ["Existing register reviewed first", "New notes tied to rooms already identified", "A missing survey is not papered over"],
        [["Can you update a register with no previous survey?", "No. We scope a survey first. A register with nothing behind it is not an update."]],
        "landlord-asbestos-survey",
    ),
    job(
        "void-asbestos-watch-point",
        "Void Asbestos Watch Point",
        "survey",
        "A void asbestos watch point is a short flag during an empty property, before kitchen, bathroom or rewire trades open finishes. iComply says whether you need a survey, not a guess. POA.",
        "The watch point does not replace a management or refurbishment survey. It stops a void team treating suspect boards, tiles or coatings as ordinary waste.",
        "Void asbestos watch point before strip-out in North West lets. POA. Not a full survey.",
        ["Used on empty lets before trades start", "Flags suspect finishes for a proper survey", "Does not clear a building for opening-up"],
        [["Is a watch point enough to rip out a kitchen?", "No. Opening finishes needs the right survey type agreed first."]],
        "asbestos-survey",
    ),
    job(
        "artex-sample-advice",
        "Artex Sample Advice",
        "survey",
        "Artex sample advice tells you whether a textured coating should be included in a scoped asbestos survey. iComply does not sell a postal kit and does not ask you to scrape a sample. POA.",
        "Not every textured coating contains asbestos. Only analysis of a properly taken sample answers it, and that sample belongs inside a survey scope with the location recorded.",
        "Artex asbestos sample advice for North West property. POA. No postal DIY sampling.",
        ["Textured coatings assessed in survey scope", "No posted fragments", "Result written to the management file"],
        [["Can I send a piece of coating in the post?", "No. Arrange a scoped visit. Uncontrolled sampling can spread fibres."]],
        "asbestos-testing",
    ),
    job(
        "garage-asbestos-advice",
        "Garage Asbestos Advice",
        "survey",
        "Garage asbestos advice covers cement sheets, old flue pipes and stored boards that often sit outside the main house survey. iComply scopes a look at the garage you actually have. POA.",
        "A domestic garage is not automatically the same duty as a workplace. We say what is proportionate, and we do not treat cement sheet as something to break up on site.",
        "Garage asbestos advice for North West homes and blocks. POA. No breakage or removal.",
        ["Cement sheet, flues and stored boards", "Separate from the main-house survey when needed", "No on-site breakage"],
        [["Will you take the garage roof off?", "No. This is advice and survey scope. Removal of cement sheet is by others if it is required."]],
        "asbestos-survey",
    ),
    job(
        "communal-riser-asbestos-check",
        "Communal Riser Asbestos Check",
        "survey",
        "A communal riser asbestos check looks at service risers in blocks where cables, pipes and old boards meet. iComply scopes it when maintenance or a rewire will enter those cupboards. POA.",
        "Risers are easy to miss on a walk around occupied flats. If the cupboard cannot be opened safely, that limit is written down instead of assumed clear.",
        "Communal riser asbestos check for North West blocks. POA before cable or pipe works.",
        ["Service cupboards and risers", "Tied to planned maintenance access", "Inaccessible risers recorded, not assumed clear"],
        [["Do you open every riser in the block?", "Only the risers in the agreed scope. A full refurbishment survey is a different visit."]],
        "common-parts-asbestos-survey",
    ),
    job(
        "asbestos-before-rewire-advice",
        "Asbestos Before Rewire Advice",
        "survey",
        "Asbestos before rewire advice is the survey conversation before electricians chase walls, lift boards or drill ceilings. iComply scopes it POA from the rewire drawing or a room list.",
        "A management survey for occupation may not be enough once cables are being routed. We say when a refurbishment-style survey is the right visit, and the electrical work stays a separate quote.",
        "Asbestos advice before a rewire in the North West. POA. Electrical works are quoted separately.",
        ["Asked before chasing and drilling", "Survey type matched to the rewire scope", "Electrical installation remains a separate job"],
        [["Can the electrician just be careful?", "Care is not a survey. If finishes will be opened, the survey type has to match that work."]],
        "asbestos-refurbishment-survey",
    ),
    job(
        "asbestos-management-plan-review",
        "Asbestos Management Plan Review",
        "survey",
        "An asbestos management plan review reads the plan a dutyholder already has and says whether the actions, reinspection interval and responsibilities still match the building. POA.",
        "A plan review is not a new survey by another name. If the plan has no survey behind it, or the building has changed, we say a survey is required.",
        "Asbestos management plan review for North West dutyholders. POA. Not a substitute survey.",
        ["Existing plan and register read together", "Responsibilities and intervals checked", "Gaps flagged instead of filled with guesses"],
        [["Will you rewrite the legal duty?", "No. We review the plan you hold and say what is missing. This is not legal advice."]],
        "asbestos-management-survey",
    ),
    job(
        "common-parts-asbestos-survey",
        "Common Parts Asbestos Survey",
        "survey",
        "A common parts asbestos survey covers halls, stairs, cupboards and plant that several occupiers share. iComply scopes it for conversions and blocks from Stockport. POA.",
        "Inside a single let the duty is often different. We keep the survey on the shared areas unless you have asked for flats as well, and we list which doors were opened.",
        "Common parts asbestos survey for North West blocks and conversions. POA.",
        ["Halls, stairs, cupboards and plant", "Shared areas listed separately from flats", "Access that was refused is written down"],
        [["Does this include every flat?", "Only if the scope says so. Common parts and dwellings are different visits."]],
        "landlord-asbestos-survey",
    ),
    job(
        "pre-purchase-asbestos-survey",
        "Pre-Purchase Asbestos Survey",
        "survey",
        "A pre-purchase asbestos survey is scoped for a buyer or their solicitor before exchange, so historic materials are not a surprise after completion. iComply quotes POA from age, area and access the vendor will allow.",
        "This is not a building survey and not a guarantee the property is clear. Inaccessible voids stay inaccessible unless a refurbishment survey is agreed. Removal is not part of the purchase visit.",
        "Pre-purchase asbestos survey in the North West. POA. Not a building survey and not removal.",
        ["Timed around access the vendor allows", "Limits of a pre-purchase visit stated", "Not a RICS building survey"],
        [["Will this satisfy a mortgage valuation?", "Not by itself. It is an asbestos survey scope, not a valuation or a structural report."]],
        "asbestos-survey",
    ),
    job(
        "asbestos-survey-report",
        "Asbestos Survey Report",
        "survey",
        "An asbestos survey report is the written record of the survey that was scoped — locations, limits and what the dutyholder should do next. iComply issues that with the visit. POA.",
        "We do not sell a report with no visit behind it, and we do not rebadge someone else's survey as ours. If you already hold a report, a plan review or reinspection may be the right next step.",
        "Asbestos survey report for North West dutyholders. POA. Issued with the scoped visit.",
        ["Written with the survey, not instead of it", "Limits and no-access areas included", "Next step is manage, monitor or appoint removal by others"],
        [["Can you stamp an old report?", "No. A report follows a visit we scoped. An old report can be reviewed, not restamped."]],
        "asbestos-survey",
    ),
]

AWARENESS = [
    job(
        "asbestos-awareness-briefing",
        "Asbestos Awareness Briefing",
        "awareness",
        "An asbestos awareness briefing tells contractors and site staff where the register is and when to stop before they disturb finishes. iComply delivers it beside a survey, from Stockport. POA.",
        "The briefing uses your building and your register. It is a site conversation for people who might drill, chase or lift boards. It is not a classroom course and it does not authorise anyone to work on asbestos materials.",
        "Asbestos awareness briefing for North West sites. POA. Not an accredited training certificate.",
        ["Uses the register for this building", "Stop-work points agreed with the dutyholder", "Sits beside a survey, not instead of one"],
        [
            ["Is this a training certificate?", "No. This briefing is not an accredited training certificate."],
            ["Does the briefing clear the building for strip-out?", "No. It does not replace a management or refurbishment survey."],
        ],
        "asbestos-survey",
    ),
    job(
        "hmo-asbestos-awareness-pack",
        "HMO Asbestos Awareness Pack",
        "awareness",
        "An HMO asbestos awareness pack is a short briefing for landlords, agents and the trades who enter a shared house. iComply builds it from the survey and register you hold. POA.",
        "Shared houses collect lots of small jobs — locks, kitchens, alarms — and each one can meet old finishes. The pack says which areas are already recorded and who to call before opening them.",
        "HMO asbestos awareness pack for North West landlords. POA. Not a training certificate.",
        ["Written for the HMO you actually manage", "Trades pointed at the register before small jobs", "Does not replace the survey"],
        [["Is this an HMO licence document?", "No. It is an awareness pack for the people who work in the house. Licensing is a separate council process."]],
        "asbestos-awareness-briefing",
    ),
    job(
        "landlord-asbestos-awareness-pack",
        "Landlord Asbestos Awareness Pack",
        "awareness",
        "A landlord asbestos awareness pack explains, in plain English, what the current register means for day-to-day repairs. iComply prepares it after a survey. POA.",
        "Agents can hand the pack to contractors with the keys. It names the dutyholder contact and the rooms that must not be drilled until the survey type is checked. It is not a removal method.",
        "Landlord asbestos awareness pack for North West portfolios. POA. Briefing only.",
        ["Plain-English notes from the register", "Contractor handover with the keys", "No removal instructions"],
        [["Can my handyman follow this and take tiles off?", "No. The pack tells people when to stop. It is not a method for disturbing materials."]],
        "asbestos-awareness-briefing",
    ),
    job(
        "contractor-asbestos-awareness-visit",
        "Contractor Asbestos Awareness Visit",
        "awareness",
        "A contractor asbestos awareness visit is a toolbox-style briefing on site before other trades start. iComply walks the areas in the survey and the limits. POA.",
        "Electricians, joiners and decorators hear the same constraints: where the register applies, what was not accessed, and who stops the job. We do not supervise licensed removal.",
        "Contractor asbestos awareness visit before North West works. POA. Not licensed removal supervision.",
        ["Delivered to the trades who will be on site", "No-access areas repeated out loud", "Stop points agreed before tools come out"],
        [["Will you stay and watch the strip-out?", "No. The visit is a briefing. Ongoing removal is a licensed contractor's work."]],
        "asbestos-awareness-briefing",
    ),
    job(
        "facilities-asbestos-awareness-pack",
        "Facilities Asbestos Awareness Pack",
        "awareness",
        "A facilities asbestos awareness pack is for in-house maintenance teams who raise their own jobs. iComply aligns it to the management plan and the register. POA.",
        "The pack covers how a helpdesk job should pause when it meets a recorded material or an area the survey could not see. It does not certify the team, and it does not replace reinspection.",
        "Facilities asbestos awareness pack for North West estates. POA. Not a staff certificate.",
        ["Helpdesk jobs paused against the register", "Written for the team you employ", "Reinspection stays a separate survey"],
        [["Do staff become qualified after the pack?", "No. This is not an accredited training certificate and it does not authorise work on asbestos materials."]],
        "asbestos-management-plan-review",
    ),
    job(
        "letting-agent-asbestos-awareness",
        "Letting Agent Asbestos Awareness",
        "awareness",
        "Letting agent asbestos awareness is a briefing for negotiators and property managers who instruct repairs. iComply keeps it specific to the registers you hold. POA.",
        "The aim is that an agent does not raise a 'make good' job on a recorded finish. It is an internal briefing, not a survey of every let, and not legal advice on the duty to manage.",
        "Letting agent asbestos awareness briefing in the North West. POA. Not legal advice.",
        ["Aimed at people who instruct repairs", "Tied to registers the agency already holds", "Does not survey every property in one visit"],
        [["Is this legal advice for our agency?", "No. It is a practical briefing. Dutyholder decisions stay with you and your advisers."]],
        "landlord-asbestos-awareness-pack",
    ),
    job(
        "maintenance-team-asbestos-awareness",
        "Maintenance Team Asbestos Awareness",
        "awareness",
        "Maintenance team asbestos awareness is a site briefing for the people who change lamps, ease doors and paint stairs. iComply uses the common-parts register. POA.",
        "Routine jobs are where finishes get damaged by habit. The briefing marks which cupboards and landings are already on the register and which jobs need a survey before they start.",
        "Maintenance team asbestos awareness for North West blocks. POA. Briefing, not a certificate.",
        ["Routine repair tasks called out", "Common-parts register used on the day", "New opening-up still needs a survey"],
        [["Can the team patch a damaged board after this?", "No. Damage to a recorded material is a stop point. Removal or repair of it is by others."]],
        "communal-area-asbestos-awareness",
    ),
    job(
        "refurbishment-asbestos-awareness-briefing",
        "Refurbishment Asbestos Awareness Briefing",
        "awareness",
        "A refurbishment asbestos awareness briefing is given to the site team after a refurbishment survey, before strip-out starts. iComply quotes it with that survey. POA.",
        "Everyone hears which rooms are in the intrusive survey and which are not. The briefing does not authorise removal and does not replace the survey report.",
        "Refurbishment asbestos awareness briefing before North West strip-out. POA.",
        ["Follows a refurbishment survey", "Room limits repeated to the site team", "Strip-out method is not included"],
        [["Can we start removing finishes after the briefing?", "No. The briefing does not replace the survey and it is not a removal instruction."]],
        "refurbishment-asbestos-survey-support",
    ),
    job(
        "void-works-asbestos-awareness",
        "Void Works Asbestos Awareness",
        "awareness",
        "Void works asbestos awareness is a briefing for the empty-property team before kitchens, bathrooms and decorations start. iComply ties it to the watch point or survey. POA.",
        "Voids move quickly. The briefing is the pause: which finishes stay, which need a survey, and who signs that trades can enter. It is not clearance to skip suspect materials.",
        "Void works asbestos awareness for North West lets. POA. Not clearance to strip.",
        ["Aimed at void turnaround teams", "Linked to a watch point or survey", "No skip-and-hope instruction"],
        [["Does this let me clear the kitchen today?", "Only where a suitable survey already covers that opening-up. The briefing itself is not that survey."]],
        "void-asbestos-watch-point",
    ),
    job(
        "asbestos-register-briefing",
        "Asbestos Register Briefing",
        "awareness",
        "An asbestos register briefing shows dutyholders and contractors how to read the register they already have. iComply runs it on site or with the file in front of us. POA.",
        "People often hold a register and still drill the wrong ceiling. The briefing walks the entries, the no-access notes and the phone number to use when a job changes. It does not create a register from nothing.",
        "Asbestos register briefing for North West dutyholders. POA. Needs a register to brief.",
        ["Walks the entries you already hold", "No-access notes included", "A missing register means a survey, not a briefing"],
        [["We have no register yet. Can you brief the team anyway?", "No. We scope a survey first. A briefing with no register would be guesswork."]],
        "landlord-asbestos-register-update",
    ),
    job(
        "communal-area-asbestos-awareness",
        "Communal Area Asbestos Awareness",
        "awareness",
        "Communal area asbestos awareness is a briefing for cleaners, caretakers and contractors who only see halls, stairs and cupboards. iComply uses the common-parts survey. POA.",
        "The briefing stays on shared routes. It does not describe how to take samples, and it does not extend into flats unless those were in the survey.",
        "Communal area asbestos awareness for North West blocks. POA. Halls and stairs, not a flat survey.",
        ["Cleaners, caretakers and visiting trades", "Limited to surveyed common parts", "Flat interiors excluded unless scoped"],
        [["Does the caretaker need a certificate afterwards?", "No. This briefing is not an accredited training certificate."]],
        "common-parts-asbestos-survey",
    ),
    job(
        "shop-fit-asbestos-awareness",
        "Shop Fit Asbestos Awareness",
        "awareness",
        "Shop fit asbestos awareness is a briefing before a retail unit is stripped or refitted. iComply gives it once the survey for that unit is in hand. POA.",
        "Shop fits often inherit old ceiling tiles and floor finishes from the previous tenant. The briefing tells the fit-out team which of those are already recorded and when to stop. It is not the survey.",
        "Shop fit asbestos awareness before North West retail refits. POA.",
        ["Timed before the shop fit starts", "Previous-tenant finishes called out", "Fit-out removal of recorded materials is by others"],
        [["Can the shopfitter lift the old floor tiles after this?", "No. The briefing is not permission to lift recorded finishes. That decision follows the survey, and removal is by others."]],
        "commercial-asbestos-survey",
    ),
    job(
        "care-premises-asbestos-awareness",
        "Care Premises Asbestos Awareness",
        "awareness",
        "Care premises asbestos awareness is a briefing for maintenance and estates staff in homes and clinics, using the register and the areas residents do not give easy access to. POA.",
        "Works in occupied care buildings need a calm stop-point, not a noisy survey sold as a chat. The briefing tells staff which jobs wait. It is not clinical training and not a certificate.",
        "Care premises asbestos awareness for North West homes and clinics. POA. Not clinical training.",
        ["Estates and maintenance staff", "Occupied buildings and limited access noted", "Not a care qualification"],
        [["Will this count as staff training for the regulator?", "No. This briefing is not an accredited training certificate and it does not replace your survey or your training plan."]],
        "asbestos-awareness-briefing",
    ),
    job(
        "asbestos-awareness-before-rewire",
        "Asbestos Awareness Before Rewire",
        "awareness",
        "Asbestos awareness before a rewire is the briefing the electrical team gets after the pre-rewire survey advice. iComply keeps the two visits separate. POA.",
        "Chasing and drilling are named as stop points where the survey has not cleared that route. The briefing does not design the rewire and does not tell anyone to cut into finishes.",
        "Asbestos awareness before a North West rewire. POA. Briefing after survey advice.",
        ["Given to the electrical team", "Follows survey advice, does not replace it", "No instruction to chase or drill"],
        [["Can this replace the survey before a rewire?", "No. It does not replace a management or refurbishment survey."]],
        "asbestos-before-rewire-advice",
    ),
    job(
        "asbestos-awareness-before-strip-out",
        "Asbestos Awareness Before Strip-Out",
        "awareness",
        "Asbestos awareness before strip-out is the last briefing before other contractors open a surveyed area. iComply delivers it from the refurbishment survey. POA.",
        "The team hears the boundary of the survey and the materials that stay in place until a licensed contractor is appointed. We do not demonstrate removal and we do not stay on as the removal contractor.",
        "Asbestos awareness before strip-out in the North West. POA. Not a removal demonstration.",
        ["Held after the refurbishment survey", "Boundary of the survey repeated", "Licensed removal remains another contractor"],
        [["Will you show the team how to take the boards down?", "No. This is not a removal demonstration. Licensed removal is by others."]],
        "demolition-asbestos-survey-liaison",
    ),
]


def main() -> None:
    jobs = SURVEY + AWARENESS
    slugs = [j["slug"] for j in jobs]
    if len(slugs) != len(set(slugs)):
        raise SystemExit("duplicate asbestos job slugs")
    names = [j["name"] for j in jobs]
    if len(names) != len(set(names)):
        raise SystemExit("duplicate asbestos job names")
    metas = [j["meta_desc"] for j in jobs]
    if len(metas) != len(set(metas)):
        raise SystemExit("duplicate asbestos meta descriptions")
    titles = [j["seo_title"] for j in jobs]
    if len(titles) != len(set(titles)):
        raise SystemExit("duplicate asbestos seo titles")
    known = set(slugs)
    for j in jobs:
        if j["related"] not in known:
            raise SystemExit(f"related slug missing: {j['slug']} -> {j['related']}")
        if j["lane"] not in ("survey", "awareness"):
            raise SystemExit(f"bad lane {j['slug']}")
        if "POA" not in j["body"] or "POA" not in j["meta_desc"]:
            raise SystemExit(f"missing POA {j['slug']}")
        if "£" in json.dumps(j):
            raise SystemExit(f"price character in {j['slug']}")
        for banned in ("UKATA", "IATP", "UKAS", "BOHS"):
            if banned in json.dumps(j):
                raise SystemExit(f"{banned} in {j['slug']}")
        if j["lane"] == "awareness":
            if "not an accredited training certificate" not in j["body"]:
                raise SystemExit(f"awareness limit missing {j['slug']}")
            if "does not replace" not in j["body"]:
                raise SystemExit(f"awareness survey limit missing {j['slug']}")
        else:
            if "Licensed asbestos removal is by others" not in j["body"]:
                raise SystemExit(f"survey removal limit missing {j['slug']}")
    payload = {
        "count": len(jobs),
        "service": "asbestos-survey",
        "lanes": {
            "survey": sum(1 for j in jobs if j["lane"] == "survey"),
            "awareness": sum(1 for j in jobs if j["lane"] == "awareness"),
        },
        "notes": "Survey and awareness only. POA. Licensed removal is by others. No training-body or laboratory claims.",
        "jobs": jobs,
    }
    OUT.write_text(json.dumps(payload, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")
    print(f"Wrote {payload['count']} jobs ({payload['lanes']}) → {OUT}")


if __name__ == "__main__":
    main()
