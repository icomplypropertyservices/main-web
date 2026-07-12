#!/usr/bin/env python3
"""Configure UK shipping rates and shop location for iComply Supplys."""

from __future__ import annotations

import json
import traceback
from datetime import datetime, timezone
from pathlib import Path

from shopify_client import api, graphql

ROOT = Path(__file__).resolve().parent
SHIPPING_NOTES = ROOT / "storefront" / "shipping_notes.json"
RESULT_PATH = ROOT / "shipping_setup_result.json"
MANUAL_PATH = ROOT / "SHIPPING_SETUP_MANUAL.md"

PROFILE_ID = "gid://shopify/DeliveryProfile/146284904780"
LOCATION_GROUP_ID = "gid://shopify/DeliveryLocationGroup/146243584332"
LOCATION_REST_ID = 123701035340
LOCATION_GQL_ID = "gid://shopify/Location/123701035340"

TARGET_LOCATION = {
    "name": "iComply Supplys — Stockport",
    "address1": "17 Woodlands Park Road",
    "address2": "Offerton",
    "city": "Stockport",
    "zip": "SK2 5DE",
    "country_code": "GB",
    "province_code": None,
    "phone": "07517806082",
}


def load_notes() -> dict:
    return json.loads(SHIPPING_NOTES.read_text(encoding="utf-8"))


def get_profile() -> dict:
    q = """
    query {
      deliveryProfile(id: "%s") {
        id
        name
        default
        profileLocationGroups {
          locationGroup { id }
          locationGroupZones(first: 25) {
            edges {
              node {
                zone { id name countries { code { countryCode } provinces { code } } }
                methodDefinitions(first: 50) {
                  edges {
                    node {
                      id
                      name
                      active
                      methodConditions {
                        field
                        operator
                        conditionCriteria {
                          __typename
                          ... on MoneyV2 { amount currencyCode }
                          ... on Weight { unit value }
                        }
                      }
                      rateProvider {
                        __typename
                        ... on DeliveryRateDefinition {
                          id
                          price { amount currencyCode }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    """ % PROFILE_ID
    return graphql(q)["data"]["deliveryProfile"]


def summarize_profile(profile: dict) -> list[dict]:
    out = []
    for plg in profile.get("profileLocationGroups") or []:
        for edge in plg["locationGroupZones"]["edges"]:
            node = edge["node"]
            methods = []
            for me in node["methodDefinitions"]["edges"]:
                m = me["node"]
                rp = m.get("rateProvider") or {}
                price = None
                if rp.get("__typename") == "DeliveryRateDefinition" and rp.get("price"):
                    price = rp["price"]
                methods.append(
                    {
                        "id": m["id"],
                        "name": m["name"],
                        "active": m["active"],
                        "price": price,
                        "conditions": m.get("methodConditions") or [],
                    }
                )
            out.append(
                {
                    "zone_id": node["zone"]["id"],
                    "zone_name": node["zone"]["name"],
                    "methods": methods,
                }
            )
    return out


def try_update_location() -> dict:
    result = {"attempted": True, "success": False, "method": None, "before": None, "after": None, "error": None}
    try:
        before = api("GET", f"/locations/{LOCATION_REST_ID}.json")
        result["before"] = before.get("location")
    except Exception as e:
        result["error"] = f"GET location failed: {e}"
        return result

    # REST PUT
    body = {
        "location": {
            "id": LOCATION_REST_ID,
            "name": TARGET_LOCATION["name"],
            "address": {
                "address1": TARGET_LOCATION["address1"],
                "address2": TARGET_LOCATION["address2"],
                "city": TARGET_LOCATION["city"],
                "zip": TARGET_LOCATION["zip"],
                "country_code": TARGET_LOCATION["country_code"],
                "phone": TARGET_LOCATION["phone"],
            },
        }
    }
    # Shopify location update uses top-level address fields in some versions
    body_flat = {
        "location": {
            "id": LOCATION_REST_ID,
            "name": TARGET_LOCATION["name"],
            "address1": TARGET_LOCATION["address1"],
            "address2": TARGET_LOCATION["address2"],
            "city": TARGET_LOCATION["city"],
            "zip": TARGET_LOCATION["zip"],
            "country_code": TARGET_LOCATION["country_code"],
            "phone": TARGET_LOCATION["phone"],
        }
    }
    try:
        after = api("PUT", f"/locations/{LOCATION_REST_ID}.json", body_flat)
        loc = after.get("location") or {}
        result["method"] = "REST PUT /locations/{id}.json"
        result["after"] = loc
        result["success"] = (
            loc.get("name") == TARGET_LOCATION["name"]
            or (loc.get("address1") or "") == TARGET_LOCATION["address1"]
        )
        if result["success"]:
            return result
        result["error"] = f"REST returned unexpected payload: {json.dumps(loc)[:500]}"
    except Exception as e:
        result["error"] = f"REST PUT failed: {e}"

    # GraphQL locationEdit
    try:
        mut = """
        mutation locationEdit($id: ID!, $input: LocationEditInput!) {
          locationEdit(id: $id, input: $input) {
            location {
              id
              name
              address { address1 address2 city zip country phone }
            }
            userErrors { field message }
          }
        }
        """
        variables = {
            "id": LOCATION_GQL_ID,
            "input": {
                "name": TARGET_LOCATION["name"],
                "address": {
                    "address1": TARGET_LOCATION["address1"],
                    "address2": TARGET_LOCATION["address2"],
                    "city": TARGET_LOCATION["city"],
                    "zip": TARGET_LOCATION["zip"],
                    "countryCode": "GB",
                    "phone": TARGET_LOCATION["phone"],
                },
            },
        }
        g = graphql(mut, variables)
        payload = g["data"]["locationEdit"]
        if payload.get("userErrors"):
            result["error"] = (result.get("error") or "") + f" | GraphQL userErrors: {payload['userErrors']}"
        else:
            result["method"] = "GraphQL locationEdit"
            result["after"] = payload.get("location")
            result["success"] = True
            result["error"] = None
    except Exception as e:
        result["error"] = (result.get("error") or "") + f" | GraphQL locationEdit failed: {e}"

    return result


def money(amount: float) -> dict:
    return {"amount": f"{amount:.2f}", "currencyCode": "GBP"}


def build_uk_method_definitions_to_create() -> list[dict]:
    """Price-based tiered rates for UK mainland from shipping_notes.json."""
    methods: list[dict] = []

    # Collection is local pickup — handled separately if possible.
    # Standard tiers (basket value). Uses priceConditionsToCreate (not methodConditionsToCreate).
    standard = [
        ("Standard tracked (UK mainland) — under £50", 5.95, None, 49.99),
        ("Standard tracked (UK mainland) — £50–£149.99", 7.95, 50.0, 149.99),
        ("Standard tracked (UK mainland) — £150–£399.99", 9.95, 150.0, 399.99),
        ("Free standard shipping (orders £400+)", 0.0, 400.0, None),
    ]
    express = [
        ("Express next working day (UK mainland) — under £50", 9.95, None, 49.99),
        ("Express next working day (UK mainland) — £50–£149.99", 11.95, 50.0, 149.99),
        ("Express next working day (UK mainland) — £150–£399.99", 14.95, 150.0, 399.99),
        ("Express next working day (UK mainland) — £400+", 9.95, 400.0, None),
    ]
    for name, price, min_v, max_v in standard + express:
        conds = []
        if min_v is not None:
            conds.append(
                {"criteria": money(min_v), "operator": "GREATER_THAN_OR_EQUAL_TO"}
            )
        if max_v is not None:
            conds.append(
                {"criteria": money(max_v), "operator": "LESS_THAN_OR_EQUAL_TO"}
            )
        methods.append(
            {
                "name": name,
                "description": "UK mainland. Highlands, islands & NI by quote.",
                "active": True,
                "rateDefinition": {"price": money(price)},
                "priceConditionsToCreate": conds,
            }
        )
    return methods


def find_uk_zone(profile: dict) -> dict | None:
    for plg in profile.get("profileLocationGroups") or []:
        for edge in plg["locationGroupZones"]["edges"]:
            node = edge["node"]
            name = (node["zone"].get("name") or "").lower()
            if "united kingdom" in name or name == "uk" or "uk mainland" in name:
                return node
    return None


def try_delivery_profile_update(profile: dict) -> dict:
    result = {
        "attempted": True,
        "success": False,
        "mutation": "deliveryProfileUpdate",
        "uk_zone_id": None,
        "created_methods": [],
        "errors": [],
        "response": None,
    }
    uk = find_uk_zone(profile)
    if not uk:
        result["errors"].append("UK shipping zone not found on default delivery profile")
        return result

    zone_id = uk["zone"]["id"]
    result["uk_zone_id"] = zone_id
    methods = build_uk_method_definitions_to_create()

    # Skip creating duplicates if rates already exist with same names
    existing_names = {m["node"]["name"] for m in uk["methodDefinitions"]["edges"]}
    to_create = [m for m in methods if m["name"] not in existing_names]
    if not to_create and uk["methodDefinitions"]["edges"]:
        result["success"] = True
        result["created_methods"] = list(existing_names)
        result["errors"].append("Rates already present; skipped create")
        return result

    mut = """
    mutation deliveryProfileUpdate($id: ID!, $profile: DeliveryProfileInput!) {
      deliveryProfileUpdate(id: $id, profile: $profile) {
        profile {
          id
          name
          profileLocationGroups {
            locationGroupZones(first: 10) {
              edges {
                node {
                  zone { id name }
                  methodDefinitions(first: 50) {
                    edges {
                      node {
                        id name active
                        methodConditions {
                          field operator
                          conditionCriteria {
                            __typename
                            ... on MoneyV2 { amount currencyCode }
                          }
                        }
                        rateProvider {
                          __typename
                          ... on DeliveryRateDefinition {
                            price { amount currencyCode }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
        userErrors { field message }
      }
    }
    """
    # Delete existing UK methods (profile-level field), then create tiered rates
    delete_ids = []
    seen = set()
    for me in uk["methodDefinitions"]["edges"]:
        base = me["node"]["id"].split("?")[0]
        if base not in seen:
            seen.add(base)
            delete_ids.append(base)

    profile_input: dict = {
        "locationGroupsToUpdate": [
            {
                "id": LOCATION_GROUP_ID,
                "zonesToUpdate": [
                    {
                        "id": zone_id,
                        "methodDefinitionsToCreate": to_create or methods,
                    }
                ],
            }
        ]
    }
    if delete_ids and (to_create or methods) == methods:
        # Full replace when creating full set
        profile_input["methodDefinitionsToDelete"] = delete_ids

    variables = {"id": PROFILE_ID, "profile": profile_input}
    try:
        g = graphql(mut, variables)
        payload = g["data"]["deliveryProfileUpdate"]
        result["response"] = {
            "userErrors": payload.get("userErrors"),
            "profile_id": (payload.get("profile") or {}).get("id"),
        }
        if payload.get("userErrors"):
            result["errors"].extend(
                [f"{e.get('field')}: {e.get('message')}" for e in payload["userErrors"]]
            )
            return try_simple_rates(zone_id, result)
        result["success"] = True
        for plg in (payload.get("profile") or {}).get("profileLocationGroups") or []:
            for edge in plg["locationGroupZones"]["edges"]:
                if edge["node"]["zone"]["id"] == zone_id:
                    for me in edge["node"]["methodDefinitions"]["edges"]:
                        result["created_methods"].append(me["node"]["name"])
        return result
    except Exception as e:
        result["errors"].append(f"deliveryProfileUpdate failed: {e}")
        return try_simple_rates(zone_id, result)


def try_simple_rates(zone_id: str, result: dict) -> dict:
    """Fallback: create a few simple flat rates without price conditions."""
    simple = [
        {"name": "Standard tracked (UK mainland)", "active": True, "rateDefinition": {"price": money(5.95)}},
        {"name": "Express next working day (UK mainland)", "active": True, "rateDefinition": {"price": money(9.95)}},
        {"name": "Free standard shipping (orders £400+)", "active": True, "rateDefinition": {"price": money(0.0)}},
    ]
    mut = """
    mutation deliveryProfileUpdate($id: ID!, $profile: DeliveryProfileInput!) {
      deliveryProfileUpdate(id: $id, profile: $profile) {
        profile { id name }
        userErrors { field message }
      }
    }
    """
    variables = {
        "id": PROFILE_ID,
        "profile": {
            "locationGroupsToUpdate": [
                {
                    "id": LOCATION_GROUP_ID,
                    "zonesToUpdate": [
                        {
                            "id": zone_id,
                            "methodDefinitionsToCreate": simple,
                        }
                    ],
                }
            ]
        },
    }
    try:
        g = graphql(mut, variables)
        payload = g["data"]["deliveryProfileUpdate"]
        result["fallback"] = "simple_flat_rates"
        result["response"] = {
            "userErrors": payload.get("userErrors"),
            "profile_id": (payload.get("profile") or {}).get("id"),
        }
        if payload.get("userErrors"):
            result["errors"].extend(
                [f"simple: {e.get('field')}: {e.get('message')}" for e in payload["userErrors"]]
            )
            result["success"] = False
        else:
            result["success"] = True
            result["created_methods"] = [m["name"] for m in simple]
            result["errors"].append(
                "Tiered price conditions not applied via API; created simple flat rates only"
            )
    except Exception as e:
        result["errors"].append(f"simple rates failed: {e}")
        result["success"] = False
    return result


def try_rest_shipping_rates(profile: dict) -> dict:
    """Legacy REST price_based_shipping_rates — often read-only now."""
    result = {"attempted": True, "success": False, "errors": [], "notes": []}
    uk = None
    try:
        zones = api("GET", "/shipping_zones.json").get("shipping_zones") or []
        for z in zones:
            if z.get("name") == "United Kingdom" or z.get("id") == 709696815436:
                uk = z
                break
        result["uk_zone_rest"] = {
            "id": uk.get("id") if uk else None,
            "name": uk.get("name") if uk else None,
            "price_based": (uk or {}).get("price_based_shipping_rates"),
            "weight_based": (uk or {}).get("weight_based_shipping_rates"),
        }
    except Exception as e:
        result["errors"].append(f"GET shipping_zones failed: {e}")
        return result

    if not uk:
        result["errors"].append("UK zone not found in REST shipping_zones")
        return result

    # Historical REST endpoints for creating rates (may 404 / 406)
    attempts = [
        (
            "POST",
            f"/shipping_zones/{uk['id']}/price_based_shipping_rates.json",
            {
                "price_based_shipping_rate": {
                    "name": "Standard tracked (UK mainland)",
                    "price": "5.95",
                    "shipping_zone_id": uk["id"],
                    "min_order_subtotal": "0.00",
                    "max_order_subtotal": "49.99",
                }
            },
        ),
    ]
    for method, path, body in attempts:
        try:
            r = api(method, path, body)
            result["notes"].append({"path": path, "response": r})
            result["success"] = True
        except Exception as e:
            result["errors"].append(f"{method} {path}: {e}")
    return result


def try_local_pickup() -> dict:
    """Enable free collection. pickupTime is DeliveryLocalPickupTime enum."""
    result = {"attempted": True, "success": False, "errors": [], "response": None}
    mut = """
    mutation enablePickup($pickup: DeliveryLocationLocalPickupEnableInput!) {
      locationLocalPickupEnable(localPickupSettings: $pickup) {
        localPickupSettings {
          instructions
          pickupTime
        }
        userErrors { field message }
      }
    }
    """
    variables = {
        "pickup": {
            "locationId": LOCATION_GQL_ID,
            "pickupTime": "TWENTY_FOUR_HOURS",
            "instructions": (
                "Free collection by prior arrangement from iComply Supplys — Stockport "
                "(17 Woodlands Park Road, Offerton, Stockport SK2 5DE). "
                "Book a slot: 07517806082 or icomplypropertyservices@gmail.com. "
                "Bring photo ID and order number. Usually ready same or next working day once stock reserved."
            ),
        },
    }
    try:
        g = graphql(mut, variables)
        payload = (g.get("data") or {}).get("locationLocalPickupEnable")
        result["response"] = payload
        if not payload:
            result["errors"].append(f"Unexpected GraphQL response: {g}")
            return result
        if payload.get("userErrors"):
            result["errors"].extend(
                [f"{e.get('field')}: {e.get('message')}" for e in payload["userErrors"]]
            )
            return result
        result["success"] = True
    except Exception as e:
        result["errors"].append(f"locationLocalPickupEnable failed: {e}")
    return result


def write_manual(notes: dict, api_results: dict) -> None:
    origin = notes.get("origin") or {}
    contact = notes.get("contact_for_quotes") or {}
    services = {s["code"]: s for s in notes.get("services") or []}
    coll = services.get("collection", {})
    std = services.get("standard", {})
    exp = services.get("express", {})

    lines = [
        "# Shipping setup — iComply Supplys UK (manual Admin steps)",
        "",
        "Shopify’s Admin REST/GraphQL **delivery profile** mutations are restricted for many apps "
        "(especially custom apps with client-credentials). If API rate creation failed, complete setup in Admin.",
        "",
        f"**Store:** icomply-supplys.myshopify.com  ",
        f"**Origin / warehouse:** {origin.get('address')}, {origin.get('locality')}, "
        f"{origin.get('city')} {origin.get('postcode')}, {origin.get('country')}  ",
        f"**Quotes:** {contact.get('phone')} · {contact.get('email')}",
        "",
        "Source of truth: `storefront/shipping_notes.json`",
        "",
        "---",
        "",
        "## 1. Location",
        "",
        "Admin → **Settings → Locations**",
        "",
        "| Field | Value |",
        "|---|---|",
        "| Name | `iComply Supplys — Stockport` |",
        "| Address | `17 Woodlands Park Road` |",
        "| Address line 2 | `Offerton` |",
        "| City | `Stockport` |",
        "| Postcode | `SK2 5DE` |",
        "| Country | United Kingdom |",
        "| Phone | `07517806082` |",
        "",
        "Enable **Local pickup** / collection at this location if offered in your plan.",
        "",
        "---",
        "",
        "## 2. Shipping zone",
        "",
        "Admin → **Settings → Shipping and delivery** → General profile",
        "",
        "1. Keep or rename zone **United Kingdom** (or create **UK Mainland**).",
        "2. Countries: **United Kingdom**.",
        "3. Prefer mainland-only messaging in rate names. For Highlands / Islands / NI / IoM / Channel Islands:",
        "   - either exclude them from this zone, **or**",
        "   - leave them in and use “quote only” messaging (see coverage notes below).",
        "4. Optional: separate zones later for NI / Highlands & Islands / EU / Rest of world.",
        "",
        "### Coverage (from shipping notes)",
        "",
        f"- **Included (mainland rates):** {', '.join((notes.get('coverage') or {}).get('included') or [])}",
        f"- **Quote only:** {', '.join((notes.get('coverage') or {}).get('quote_only') or [])}",
        f"- Note: {(notes.get('coverage') or {}).get('notes', '')}",
        "",
        "---",
        "",
        "## 3. Free collection (local pickup)",
        "",
        f"**Name:** {coll.get('name', 'Collection')}  ",
        f"**Price:** £{coll.get('price_gbp', 0):.2f} (free)  ",
        f"**ETA:** {coll.get('eta_working_days', {})} working days  ",
        f"**Description:** {coll.get('description', '')}",
        "",
        "Conditions:",
    ]
    for c in coll.get("conditions") or []:
        lines.append(f"- {c}")
    lines += [
        "",
        "In Admin: enable **Local pickup** on the Stockport location (preferred), **or** add a £0.00 rate named "
        "`Collection — Stockport (by arrangement)` on the UK zone.",
        "",
        "---",
        "",
        "## 4. Standard tracked (UK mainland) — price-based tiers",
        "",
        f"**Service:** {std.get('name')}  ",
        f"**ETA:** {std.get('eta_working_days', {}).get('min')}–{std.get('eta_working_days', {}).get('max')} working days  ",
        f"**Description:** {std.get('description', '')}",
        "",
        "Create **price-based rates** (order value / basket subtotal) on the UK zone:",
        "",
        "| Rate name | Min order (GBP) | Max order (GBP) | Shipping price |",
        "|---|---:|---:|---:|",
    ]
    for r in std.get("rate_suggestions") or []:
        max_v = "∞" if r.get("basket_max_gbp") is None else f"{r['basket_max_gbp']:.2f}"
        note = f" — {r['note']}" if r.get("note") else ""
        lines.append(
            f"| {std.get('name')}{note if r.get('shipping_gbp') == 0 else ''} | "
            f"{r['basket_min_gbp']:.2f} | {max_v} | £{r['shipping_gbp']:.2f} |"
        )
    # Clearer explicit rows matching notes
    lines += [
        "",
        "### Exact rates to enter",
        "",
        "| Rate name | Condition | Price |",
        "|---|---|---:|",
        "| Standard tracked (UK mainland) — under £50 | £0.00 – £49.99 | **£5.95** |",
        "| Standard tracked (UK mainland) — £50–£149.99 | £50.00 – £149.99 | **£7.95** |",
        "| Standard tracked (UK mainland) — £150–£399.99 | £150.00 – £399.99 | **£9.95** |",
        "| Free standard shipping (orders £400+) | £400.00 and above | **£0.00** |",
        "",
        "> **Free shipping over £400** is implemented as the £0.00 standard tier above. "
        "Do not also enable a second conflicting free-shipping discount unless intentional.",
        "",
        "### Optional weight-based alternatives (if you prefer weight over basket value)",
        "",
        "| Max weight (kg) | Shipping price |",
        "|---:|---:|",
    ]
    for w in std.get("weight_based_alternatives") or []:
        lines.append(f"| {w['max_kg']} | £{w['shipping_gbp']:.2f} |")

    lines += [
        "",
        "---",
        "",
        "## 5. Express next working day (UK mainland)",
        "",
        f"**Service:** {exp.get('name')}  ",
        f"**Cut-off:** {exp.get('cut_off_local', '13:00')} ({exp.get('timezone', 'Europe/London')})  ",
        f"**ETA:** next working day when ordered before cut-off and stock available  ",
        f"**Description:** {exp.get('description', '')}",
        "",
        "### Exact rates to enter",
        "",
        "| Rate name | Condition | Price |",
        "|---|---|---:|",
        "| Express next working day — under £50 | £0.00 – £49.99 | **£9.95** |",
        "| Express next working day — £50–£149.99 | £50.00 – £149.99 | **£11.95** |",
        "| Express next working day — £150–£399.99 | £150.00 – £399.99 | **£14.95** |",
        "| Express next working day — £400+ | £400.00 and above | **£9.95** |",
        "",
        "### Optional weight-based alternatives",
        "",
        "| Max weight (kg) | Shipping price |",
        "|---:|---:|",
    ]
    for w in exp.get("weight_based_alternatives") or []:
        lines.append(f"| {w['max_kg']} | £{w['shipping_gbp']:.2f} |")

    over = notes.get("oversize_and_trade") or {}
    lines += [
        "",
        "---",
        "",
        "## 6. Oversize / trade notes (not auto-rated)",
        "",
        f"- Panel / bulky surcharge suggestion: **£{over.get('panel_or_bulky_surcharge_gbp', 15):.2f}**",
        f"- Pallet quote threshold: **{over.get('pallet_quote_threshold_kg', 30)} kg**",
        f"- Cable drums: {over.get('cable_drum_note', '')}",
        "",
        "Trade account notes:",
    ]
    for t in over.get("trade_account_notes") or []:
        lines.append(f"- {t}")

    lines += [
        "",
        "---",
        "",
        "## 7. Checkout test checklist",
        "",
        "1. Add products so basket is **£40** → expect Standard **£5.95**, Express **£9.95**.",
        "2. Basket **£100** → Standard **£7.95**, Express **£11.95**.",
        "3. Basket **£200** → Standard **£9.95**, Express **£14.95**.",
        "4. Basket **£400+** → Standard **Free (£0)**, Express **£9.95**.",
        "5. Confirm **Collection** / local pickup appears with **£0**.",
        "6. Confirm VAT treatment of shipping matches UK tax settings.",
        "",
        "---",
        "",
        "## 8. API attempt summary",
        "",
        "```json",
        json.dumps(api_results, indent=2, default=str)[:6000],
        "```",
        "",
        "If rates were created via API, still verify tier conditions in Admin — "
        "Shopify may require manual condition UI for some price-based definitions.",
        "",
    ]
    MANUAL_PATH.write_text("\n".join(lines), encoding="utf-8")


def main() -> None:
    result: dict = {
        "timestamp_utc": datetime.now(timezone.utc).isoformat(),
        "store": "icomply-supplys.myshopify.com",
        "source": str(SHIPPING_NOTES),
        "location_update": {},
        "delivery_profile_before": [],
        "delivery_profile_after": [],
        "rates_api": {},
        "rest_shipping_rates": {},
        "local_pickup": {},
        "manual_doc": None,
        "summary": [],
    }
    notes = load_notes()
    result["rates_from_notes"] = {
        "collection": 0.0,
        "standard_tiers": (next(s for s in notes["services"] if s["code"] == "standard"))[
            "rate_suggestions"
        ],
        "express_tiers": (next(s for s in notes["services"] if s["code"] == "express"))[
            "rate_suggestions"
        ],
        "free_standard_over_gbp": 400,
    }

    print("== Location update ==")
    loc = try_update_location()
    result["location_update"] = loc
    print("  success:", loc.get("success"), "method:", loc.get("method"), "error:", loc.get("error"))

    print("== Delivery profile (before) ==")
    try:
        profile = get_profile()
        result["delivery_profile_before"] = summarize_profile(profile)
        for z in result["delivery_profile_before"]:
            print(f"  zone {z['zone_name']}: {len(z['methods'])} methods")
    except Exception as e:
        result["summary"].append(f"profile read failed: {e}")
        profile = None
        print("  ERROR", e)
        traceback.print_exc()

    print("== Create UK rates via GraphQL deliveryProfileUpdate ==")
    if profile:
        rates = try_delivery_profile_update(profile)
        result["rates_api"] = rates
        print("  success:", rates.get("success"), "errors:", rates.get("errors"))
        print("  methods:", rates.get("created_methods"))
    else:
        result["rates_api"] = {"attempted": False, "success": False, "errors": ["no profile"]}

    print("== REST shipping rate attempt ==")
    rest = try_rest_shipping_rates(profile or {})
    result["rest_shipping_rates"] = rest
    print("  success:", rest.get("success"), "errors:", rest.get("errors")[:3] if rest.get("errors") else [])

    print("== Local pickup ==")
    pickup = try_local_pickup()
    result["local_pickup"] = pickup
    print("  success:", pickup.get("success"), "errors:", pickup.get("errors"))

    print("== Delivery profile (after) ==")
    try:
        profile2 = get_profile()
        result["delivery_profile_after"] = summarize_profile(profile2)
        for z in result["delivery_profile_after"]:
            print(f"  zone {z['zone_name']}: {len(z['methods'])} methods -> {[m['name'] for m in z['methods']]}")
    except Exception as e:
        result["summary"].append(f"profile re-read failed: {e}")
        print("  ERROR", e)

    # Always write manual guide with exact rates
    api_snapshot = {
        "location_update": {
            "success": loc.get("success"),
            "method": loc.get("method"),
            "error": loc.get("error"),
        },
        "rates_api": {
            "success": result["rates_api"].get("success"),
            "errors": result["rates_api"].get("errors"),
            "created_methods": result["rates_api"].get("created_methods"),
        },
        "local_pickup": {
            "success": pickup.get("success"),
            "errors": pickup.get("errors"),
        },
        "rest_shipping_rates": {
            "success": rest.get("success"),
            "errors": rest.get("errors"),
        },
    }
    write_manual(notes, api_snapshot)
    result["manual_doc"] = str(MANUAL_PATH)

    rates_ok = bool(result["rates_api"].get("success"))
    loc_ok = bool(loc.get("success"))
    pickup_ok = bool(pickup.get("success"))

    if rates_ok:
        result["summary"].append("UK shipping rates created/updated via GraphQL deliveryProfileUpdate")
    else:
        result["summary"].append(
            "API could not fully create tiered shipping rates — use SHIPPING_SETUP_MANUAL.md"
        )
    if loc_ok:
        result["summary"].append("Location updated to iComply Supplys — Stockport")
    else:
        result["summary"].append("Location update failed or partial — set address in Admin")
    if pickup_ok:
        result["summary"].append("Local pickup enabled for free collection")
    else:
        result["summary"].append("Local pickup not enabled via API — enable in Admin")
    result["summary"].append("SHIPPING_SETUP_MANUAL.md written with exact rates from shipping_notes.json")

    RESULT_PATH.write_text(json.dumps(result, indent=2, default=str), encoding="utf-8")
    print("\nWrote", RESULT_PATH)
    print("Wrote", MANUAL_PATH)
    print("SUMMARY:")
    for s in result["summary"]:
        print(" -", s)


if __name__ == "__main__":
    main()
