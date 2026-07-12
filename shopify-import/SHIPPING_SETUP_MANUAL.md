# Shipping setup — iComply Supplys UK

**Status (API):** Applied live via GraphQL `deliveryProfileUpdate` + `locationEdit` + `locationLocalPickupEnable`  
(see `shipping_setup_result.json`). This document is the human-readable rate card and fallback Admin guide.

REST `price_based_shipping_rates` is deprecated (HTTP 406). Use Admin UI only if you need to edit rates by hand.

**Store:** icomply-supplys.myshopify.com  
**Origin / warehouse:** 17 Woodlands Park Road, Offerton, Stockport SK2 5DE, GB  
**Quotes:** 07517806082 · icomplypropertyservices@gmail.com

Source of truth: `storefront/shipping_notes.json`

---

## 1. Location

Admin → **Settings → Locations**

| Field | Value |
|---|---|
| Name | `iComply Supplys — Stockport` |
| Address | `17 Woodlands Park Road` |
| Address line 2 | `Offerton` |
| City | `Stockport` |
| Postcode | `SK2 5DE` |
| Country | United Kingdom |
| Phone | `07517806082` |

Enable **Local pickup** / collection at this location if offered in your plan.

---

## 2. Shipping zone

Admin → **Settings → Shipping and delivery** → General profile

1. Keep or rename zone **United Kingdom** (or create **UK Mainland**).
2. Countries: **United Kingdom**.
3. Prefer mainland-only messaging in rate names. For Highlands / Islands / NI / IoM / Channel Islands:
   - either exclude them from this zone, **or**
   - leave them in and use “quote only” messaging (see coverage notes below).
4. Optional: separate zones later for NI / Highlands & Islands / EU / Rest of world.

### Coverage (from shipping notes)

- **Included (mainland rates):** England, Wales, Scotland mainland
- **Quote only:** Northern Ireland, Scottish Highlands & Islands, Isle of Man, Channel Islands, Other non-mainland UK
- Note: Checkout rates apply to UK mainland postcodes. Remote and offshore destinations require manual quote before acceptance.

---

## 3. Free collection (local pickup)

**Name:** Collection  
**Price:** £0.00 (free)  
**ETA:** {'min': 0, 'max': 1} working days  
**Description:** Customer collection by prior arrangement from Stockport (Offerton) when order is marked ready.

Conditions:
- Must book collection slot by phone or email
- Photo ID / order number on pickup
- Free of charge when stock reserved

In Admin: enable **Local pickup** on the Stockport location (preferred), **or** add a £0.00 rate named `Collection — Stockport (by arrangement)` on the UK zone.

---

## 4. Standard tracked (UK mainland) — price-based tiers

**Service:** Standard tracked (UK mainland)  
**ETA:** 2–4 working days  
**Description:** Economy tracked parcel service for small to medium consignments.

Create **price-based rates** (order value / basket subtotal) on the UK zone:

| Rate name | Min order (GBP) | Max order (GBP) | Shipping price |
|---|---:|---:|---:|
| Standard tracked (UK mainland) | 0.00 | 49.99 | £5.95 |
| Standard tracked (UK mainland) | 50.00 | 149.99 | £7.95 |
| Standard tracked (UK mainland) | 150.00 | 399.99 | £9.95 |
| Standard tracked (UK mainland) — Suggested free standard carriage threshold for standard parcels | 400.00 | ∞ | £0.00 |

### Exact rates to enter

| Rate name | Condition | Price |
|---|---|---:|
| Standard tracked (UK mainland) — under £50 | £0.00 – £49.99 | **£5.95** |
| Standard tracked (UK mainland) — £50–£149.99 | £50.00 – £149.99 | **£7.95** |
| Standard tracked (UK mainland) — £150–£399.99 | £150.00 – £399.99 | **£9.95** |
| Free standard shipping (orders £400+) | £400.00 and above | **£0.00** |

> **Free shipping over £400** is implemented as the £0.00 standard tier above. Do not also enable a second conflicting free-shipping discount unless intentional.

### Optional weight-based alternatives (if you prefer weight over basket value)

| Max weight (kg) | Shipping price |
|---:|---:|
| 2 | £4.95 |
| 5 | £6.95 |
| 10 | £8.95 |
| 20 | £12.95 |
| 30 | £18.95 |

---

## 5. Express next working day (UK mainland)

**Service:** Express next working day (UK mainland)  
**Cut-off:** 13:00 (Europe/London)  
**ETA:** next working day when ordered before cut-off and stock available  
**Description:** Next working day tracked service when ordered before cut-off and stock available.

### Exact rates to enter

| Rate name | Condition | Price |
|---|---|---:|
| Express next working day — under £50 | £0.00 – £49.99 | **£9.95** |
| Express next working day — £50–£149.99 | £50.00 – £149.99 | **£11.95** |
| Express next working day — £150–£399.99 | £150.00 – £399.99 | **£14.95** |
| Express next working day — £400+ | £400.00 and above | **£9.95** |

### Optional weight-based alternatives

| Max weight (kg) | Shipping price |
|---:|---:|
| 2 | £8.95 |
| 5 | £10.95 |
| 10 | £13.95 |
| 20 | £18.95 |
| 30 | £26.95 |

---

## 6. Oversize / trade notes (not auto-rated)

- Panel / bulky surcharge suggestion: **£15.00**
- Pallet quote threshold: **30 kg**
- Cable drums: Cable drums and long lengths may require pallet or specialist carrier — quote manually.

Trade account notes:
- Approved trade accounts may receive negotiated free-carriage thresholds
- Site deliveries need site contact name and mobile number
- Failed delivery / excess waiting time may be recharged at carrier cost

---

## 7. Checkout test checklist

1. Add products so basket is **£40** → expect Standard **£5.95**, Express **£9.95**.
2. Basket **£100** → Standard **£7.95**, Express **£11.95**.
3. Basket **£200** → Standard **£9.95**, Express **£14.95**.
4. Basket **£400+** → Standard **Free (£0)**, Express **£9.95**.
5. Confirm **Collection** / local pickup appears with **£0**.
6. Confirm VAT treatment of shipping matches UK tax settings.

---

## 8. API attempt summary

```json
{
  "location_update": {
    "attempted": true,
    "success": true,
    "method": "GraphQL locationEdit",
    "after": {
      "id": 123701035340,
      "name": "iComply Supplys \u2014 Stockport",
      "address1": "17 Woodlands Park Road",
      "address2": "Offerton",
      "city": "Stockport",
      "zip": "SK2 5DE",
      "country_code": "GB",
      "phone": "07517806082"
    }
  },
  "rates_api": {
    "success": true,
    "created_methods": [
      "Standard tracked (UK mainland) \u2014 under \u00a350",
      "Standard tracked (UK mainland) \u2014 \u00a350\u2013\u00a3149.99",
      "Standard tracked (UK mainland) \u2014 \u00a3150\u2013\u00a3399.99",
      "Free standard shipping (orders \u00a3400+)",
      "Express next working day (UK mainland) \u2014 under \u00a350",
      "Express next working day (UK mainland) \u2014 \u00a350\u2013\u00a3149.99",
      "Express next working day (UK mainland) \u2014 \u00a3150\u2013\u00a3399.99",
      "Express next working day (UK mainland) \u2014 \u00a3400+"
    ],
    "methods_detail": [
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961774412",
        "name": "Standard tracked (UK mainland) \u2014 under \u00a350",
        "active": true,
        "price": {
          "amount": "5.95",
          "currencyCode": "GBP"
        },
        "conditions": [
          {
            "field": "TOTAL_PRICE",
            "operator": "GREATER_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "0.0",
              "currencyCode": "GBP"
            }
          },
          {
            "field": "TOTAL_PRICE",
            "operator": "LESS_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "49.99",
              "currencyCode": "GBP"
            }
          }
        ]
      },
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961807180",
        "name": "Standard tracked (UK mainland) \u2014 \u00a350\u2013\u00a3149.99",
        "active": true,
        "price": {
          "amount": "7.95",
          "currencyCode": "GBP"
        },
        "conditions": [
          {
            "field": "TOTAL_PRICE",
            "operator": "GREATER_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "50.0",
              "currencyCode": "GBP"
            }
          },
          {
            "field": "TOTAL_PRICE",
            "operator": "LESS_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "149.99",
              "currencyCode": "GBP"
            }
          }
        ]
      },
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961839948",
        "name": "Standard tracked (UK mainland) \u2014 \u00a3150\u2013\u00a3399.99",
        "active": true,
        "price": {
          "amount": "9.95",
          "currencyCode": "GBP"
        },
        "conditions": [
          {
            "field": "TOTAL_PRICE",
            "operator": "GREATER_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "150.0",
              "currencyCode": "GBP"
            }
          },
          {
            "field": "TOTAL_PRICE",
            "operator": "LESS_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "399.99",
              "currencyCode": "GBP"
            }
          }
        ]
      },
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961872716",
        "name": "Free standard shipping (orders \u00a3400+)",
        "active": true,
        "price": {
          "amount": "0.0",
          "currencyCode": "GBP"
        },
        "conditions": [
          {
            "field": "TOTAL_PRICE",
            "operator": "GREATER_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "400.0",
              "currencyCode": "GBP"
            }
          }
        ]
      },
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961905484",
        "name": "Express next working day (UK mainland) \u2014 under \u00a350",
        "active": true,
        "price": {
          "amount": "9.95",
          "currencyCode": "GBP"
        },
        "conditions": [
          {
            "field": "TOTAL_PRICE",
            "operator": "GREATER_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "0.0",
              "currencyCode": "GBP"
            }
          },
          {
            "field": "TOTAL_PRICE",
            "operator": "LESS_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "49.99",
              "currencyCode": "GBP"
            }
          }
        ]
      },
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961938252",
        "name": "Express next working day (UK mainland) \u2014 \u00a350\u2013\u00a3149.99",
        "active": true,
        "price": {
          "amount": "11.95",
          "currencyCode": "GBP"
        },
        "conditions": [
          {
            "field": "TOTAL_PRICE",
            "operator": "GREATER_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "50.0",
              "currencyCode": "GBP"
            }
          },
          {
            "field": "TOTAL_PRICE",
            "operator": "LESS_THAN_OR_EQUAL_TO",
            "conditionCriteria": {
              "__typename": "MoneyV2",
              "amount": "149.99",
              "currencyCode": "GBP"
            }
          }
        ]
      },
      {
        "id": "gid://shopify/DeliveryMethodDefinition/1363961971020",
        "name": "Express next working day (
```

If rates were created via API, still verify tier conditions in Admin — Shopify may require manual condition UI for some price-based definitions.
