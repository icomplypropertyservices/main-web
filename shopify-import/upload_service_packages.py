#!/usr/bin/env python3
"""Upload iComply service packages to Shopify store."""

from __future__ import annotations

import base64
import json
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

from shopify_client import get_token as _shared_get_token, get_store

API_VERSION = "2024-10"
OUT_DIR = Path(__file__).resolve().parent
IMG_DIR = OUT_DIR / "images"

# Brand colours
NAVY = (10, 37, 64)
ORANGE = (255, 107, 0)
WHITE = (255, 255, 255)
LIGHT = (245, 247, 250)
MUTED = (100, 116, 139)

PRODUCTS = [
    {
        "handle": "electrical-compliance-package",
        "title": "Electrical Compliance Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "electrical", "EICR", "compliance"],
        "price": "149.00",
        "sku": "IC-ELEC-PKG",
        "subtitle": "EICR · PAT · Installation",
        "body_html": """
<p><strong>Professional electrical compliance for commercial and residential properties across Greater Manchester and the North West.</strong></p>
<ul>
<li>EICR (Electrical Installation Condition Report)</li>
<li>PAT testing for workplaces and landlords</li>
<li>Fault finding, remedial works and certification</li>
<li>Commercial &amp; domestic installations</li>
<li>EV charger installation (on request)</li>
</ul>
<p>Fixed-price quotes. Local engineers. National standards.</p>
<p><em>Price shown is a typical starting point for a standard domestic EICR. Commercial and multi-circuit works quoted separately. Free quote within 2 hours.</em></p>
""",
    },
    {
        "handle": "fire-alarm-service-package",
        "title": "Fire Alarm Service Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "fire-alarms", "BS5839", "compliance"],
        "price": "299.00",
        "sku": "IC-FIRE-PKG",
        "subtitle": "Install · Service · BS 5839",
        "body_html": """
<p><strong>Fire alarm installation, servicing and certification to BS 5839.</strong></p>
<ul>
<li>Addressable &amp; conventional systems</li>
<li>Wireless fire detection options</li>
<li>Brands: Kentec, Advanced, C-Tec, Hochiki, Apollo</li>
<li>Periodic testing, loop testing &amp; battery replacement</li>
<li>Full BS 5839 commissioning certificates</li>
<li>Landlord and commercial fire alarm certificates</li>
</ul>
<p>Serving Manchester, Stockport, Bolton, Oldham, Rochdale, Wigan, Preston, Liverpool and 140+ towns.</p>
<p><em>Starting price for a small system service visit. Full installs and multi-zone systems quoted after site survey.</em></p>
""",
    },
    {
        "handle": "emergency-lighting-package",
        "title": "Emergency Lighting Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "emergency-lighting", "BS5266", "compliance"],
        "price": "199.00",
        "sku": "IC-EML-PKG",
        "subtitle": "BS 5266 · Testing · Certs",
        "body_html": """
<p><strong>Emergency lighting installation, testing and certification to BS 5266.</strong></p>
<ul>
<li>New emergency lighting design &amp; installation</li>
<li>Monthly / annual testing programmes</li>
<li>Remedial works and bulkhead/exit sign upgrades</li>
<li>Landlord and commercial certification</li>
<li>Maintenance contracts available</li>
</ul>
<p><em>Starting price for a standard small-premises test and certificate. Larger sites quoted individually.</em></p>
""",
    },
    {
        "handle": "aov-air-handling-package",
        "title": "AOV & Air Handling Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "aov", "smoke-vents", "BS9991"],
        "price": "349.00",
        "sku": "IC-AOV-PKG",
        "subtitle": "Smoke vents · BS 9991",
        "body_html": """
<p><strong>Automatic Opening Vent (AOV) and air handling systems — install, maintain and certify.</strong></p>
<ul>
<li>AOV installation and commissioning</li>
<li>Smoke vent system servicing</li>
<li>BS 9991 aligned works</li>
<li>Fault diagnosis and actuator replacement</li>
<li>Planned maintenance contracts</li>
</ul>
<p><em>Starting price for inspection/service of a typical AOV system. New installs quoted after survey.</em></p>
""",
    },
    {
        "handle": "nurse-call-systems-package",
        "title": "Nurse Call Systems Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "nurse-call", "care-homes", "HTM0803"],
        "price": "499.00",
        "sku": "IC-NURSE-PKG",
        "subtitle": "Care homes · HTM 08-03",
        "body_html": """
<p><strong>Nurse call system installation and maintenance for care environments.</strong></p>
<ul>
<li>New nurse call system design &amp; install</li>
<li>HTM 08-03 aligned solutions</li>
<li>System upgrades and extensions</li>
<li>Planned maintenance and call-out support</li>
<li>Ideal for care homes and healthcare facilities</li>
</ul>
<p><em>Starting price for assessment/service visit. Full system installs quoted to room count and specification.</em></p>
""",
    },
    {
        "handle": "gas-safety-package",
        "title": "Gas Safety Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "gas", "CP12", "landlord"],
        "price": "89.00",
        "sku": "IC-GAS-PKG",
        "subtitle": "CP12 · Boiler · Landlord",
        "body_html": """
<p><strong>Gas safety certificates, boiler servicing and landlord compliance.</strong></p>
<ul>
<li>Landlord Gas Safety Record (CP12)</li>
<li>Boiler servicing and safety checks</li>
<li>Gas appliance inspections</li>
<li>Remedial gas works (qualified engineers)</li>
<li>Ideal for landlords and managing agents</li>
</ul>
<p><em>Starting price for a standard single-appliance landlord gas safety certificate. Multi-appliance / commercial quoted separately.</em></p>
""",
    },
    {
        "handle": "intruder-alarm-package",
        "title": "Intruder Alarm Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "intruder-alarm", "security", "BS4737"],
        "price": "249.00",
        "sku": "IC-INTR-PKG",
        "subtitle": "Install · BS 4737",
        "body_html": """
<p><strong>Intruder and burglar alarm installation for homes and businesses.</strong></p>
<ul>
<li>Wired and wireless systems</li>
<li>BS 4737 aligned installations</li>
<li>Commercial and residential options</li>
<li>Monitoring-ready setups</li>
<li>Service and maintenance available</li>
</ul>
<p><em>Starting price for a small premises install. Larger commercial systems quoted after survey.</em></p>
""",
    },
    {
        "handle": "cctv-systems-package",
        "title": "CCTV Systems Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "cctv", "surveillance", "security"],
        "price": "349.00",
        "sku": "IC-CCTV-PKG",
        "subtitle": "IP CCTV · Commercial",
        "body_html": """
<p><strong>IP CCTV design, installation and maintenance for commercial and residential sites.</strong></p>
<ul>
<li>IP camera systems and NVR setups</li>
<li>Commercial video surveillance</li>
<li>Remote viewing configuration</li>
<li>System upgrades and expansions</li>
<li>Ongoing support packages</li>
</ul>
<p><em>Starting price for a small multi-camera package. Camera count, storage and networking quoted to site.</em></p>
""",
    },
    {
        "handle": "access-control-package",
        "title": "Access Control Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "access-control", "security", "biometric"],
        "price": "399.00",
        "sku": "IC-ACCESS-PKG",
        "subtitle": "Doors · Cards · Biometric",
        "body_html": """
<p><strong>Door access control systems for offices, blocks and commercial sites.</strong></p>
<ul>
<li>Card, fob and keypad systems</li>
<li>Biometric access options</li>
<li>Multi-door and multi-site setups</li>
<li>Integration with door entry where required</li>
<li>Install, programme and support</li>
</ul>
<p><em>Starting price for a single-door access control install. Multi-door systems quoted after survey.</em></p>
""",
    },
    {
        "handle": "door-entry-package",
        "title": "Door Entry Systems Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "door-entry", "video-entry", "apartments"],
        "price": "299.00",
        "sku": "IC-DOOR-PKG",
        "subtitle": "Audio · Video · Apartments",
        "body_html": """
<p><strong>Audio and video door entry for houses, apartments and commercial buildings.</strong></p>
<ul>
<li>Video door entry systems</li>
<li>Audio door entry</li>
<li>Apartment / multi-tenant systems</li>
<li>Panel upgrades and handset replacements</li>
<li>Integration with access control</li>
</ul>
<p><em>Starting price for a basic single-dwelling video entry install. Multi-flat systems quoted to handset count.</em></p>
""",
    },
    {
        "handle": "intercoms-package",
        "title": "Intercom Systems Package",
        "product_type": "Service Package",
        "vendor": "iComply Property Services",
        "tags": ["service-package", "intercoms", "video-intercom", "multi-tenant"],
        "price": "249.00",
        "sku": "IC-INT-PKG",
        "subtitle": "Audio · Video · Multi-tenant",
        "body_html": """
<p><strong>Intercom installation for residential blocks and commercial premises.</strong></p>
<ul>
<li>Audio and video intercoms</li>
<li>Multi-tenant systems</li>
<li>System repairs and upgrades</li>
<li>Integration with door release and access control</li>
<li>Maintenance options available</li>
</ul>
<p><em>Starting price for a standard intercom install/service. Larger multi-tenant systems quoted after survey.</em></p>
""",
    },
]


def get_token() -> str:
    return _shared_get_token(force=True)


def api(token: str, method: str, path: str, body: dict | None = None) -> dict:
    store = get_store()
    url = f"https://{store}/admin/api/{API_VERSION}{path}"
    data = None if body is None else json.dumps(body).encode()
    req = urllib.request.Request(
        url,
        data=data,
        method=method,
        headers={
            "X-Shopify-Access-Token": token,
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
    )
    try:
        with urllib.request.urlopen(req, timeout=90) as resp:
            raw = resp.read().decode()
            return json.loads(raw) if raw else {}
    except urllib.error.HTTPError as e:
        err = e.read().decode(errors="replace")
        raise RuntimeError(f"{method} {path} -> {e.code}: {err}") from e


def load_font(size: int) -> ImageFont.ImageFont:
    candidates = [
        r"C:\Windows\Fonts\segoeui.ttf",
        r"C:\Windows\Fonts\arial.ttf",
        r"C:\Windows\Fonts\calibri.ttf",
    ]
    for path in candidates:
        if Path(path).exists():
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def make_image(title: str, subtitle: str, sku: str, out_path: Path) -> Path:
    w, h = 1200, 1200
    img = Image.new("RGB", (w, h), LIGHT)
    draw = ImageDraw.Draw(img)

    # Top navy bar
    draw.rectangle([0, 0, w, 220], fill=NAVY)
    # Orange accent
    draw.rectangle([0, 220, w, 232], fill=ORANGE)
    # Bottom bar
    draw.rectangle([0, h - 120, w, h], fill=NAVY)

    font_brand = load_font(42)
    font_title = load_font(64)
    font_sub = load_font(36)
    font_small = load_font(28)

    draw.text((60, 70), "iCOMPLY SUPPLYS", fill=WHITE, font=font_brand)
    draw.text((60, 130), "Service Package", fill=ORANGE, font=font_small)

    # Title wrapping
    words = title.split()
    lines: list[str] = []
    cur = ""
    for word in words:
        test = f"{cur} {word}".strip()
        bbox = draw.textbbox((0, 0), test, font=font_title)
        if bbox[2] - bbox[0] > w - 120 and cur:
            lines.append(cur)
            cur = word
        else:
            cur = test
    if cur:
        lines.append(cur)

    y = 360
    for line in lines[:4]:
        draw.text((60, y), line, fill=NAVY, font=font_title)
        y += 80

    draw.text((60, y + 20), subtitle, fill=MUTED, font=font_sub)
    draw.text((60, h - 80), f"SKU {sku}  ·  North West UK", fill=WHITE, font=font_small)

    # Decorative circle
    draw.ellipse([w - 320, 280, w - 80, 520], outline=ORANGE, width=8)

    IMG_DIR.mkdir(parents=True, exist_ok=True)
    img.save(out_path, "PNG", optimize=True)
    return out_path


def upload_image(token: str, product_id: int, image_path: Path, alt: str) -> None:
    b64 = base64.b64encode(image_path.read_bytes()).decode()
    api(
        token,
        "POST",
        f"/products/{product_id}/images.json",
        {
            "image": {
                "attachment": b64,
                "filename": image_path.name,
                "alt": alt,
            }
        },
    )


def find_existing(token: str) -> dict[str, dict]:
    existing: dict[str, dict] = {}
    page_info = None
    # First page
    data = api(token, "GET", "/products.json?limit=250&fields=id,title,handle")
    for p in data.get("products", []):
        existing[p["handle"]] = p
    return existing


def delete_product(token: str, product_id: int) -> None:
    api(token, "DELETE", f"/products/{product_id}.json")


def create_product(token: str, spec: dict, image_path: Path) -> dict:
    payload = {
        "product": {
            "title": spec["title"],
            "body_html": spec["body_html"].strip(),
            "vendor": spec["vendor"],
            "product_type": spec["product_type"],
            "tags": ", ".join(spec["tags"]),
            "status": "active",
            "handle": spec["handle"],
            "variants": [
                {
                    "price": spec["price"],
                    "sku": spec["sku"],
                    "inventory_management": None,
                    "requires_shipping": False,
                    "taxable": True,
                    "option1": "Default Title",
                }
            ],
            "options": [{"name": "Title", "values": ["Default Title"]}],
        }
    }
    created = api(token, "POST", "/products.json", payload)["product"]
    upload_image(token, created["id"], image_path, spec["title"])
    return created


def main() -> int:
    print("Getting access token...")
    token = get_token()
    print("Token OK")

    existing = find_existing(token)
    print(f"Existing products: {len(existing)}")

    # Delete test products
    for handle, p in list(existing.items()):
        title = (p.get("title") or "").lower()
        if "api connection test" in title or "delete me" in title:
            print(f"Deleting test product {p['id']} ({p['title']})")
            delete_product(token, p["id"])
            existing.pop(handle, None)

    results = []
    for spec in PRODUCTS:
        img_path = IMG_DIR / f"{spec['handle']}.png"
        print(f"\n== {spec['title']} ==")
        print(f"  Generating image -> {img_path.name}")
        make_image(spec["title"], spec["subtitle"], spec["sku"], img_path)

        if spec["handle"] in existing:
            pid = existing[spec["handle"]]["id"]
            print(f"  Already exists id={pid} — updating image + fields")
            api(
                token,
                "PUT",
                f"/products/{pid}.json",
                {
                    "product": {
                        "id": pid,
                        "title": spec["title"],
                        "body_html": spec["body_html"].strip(),
                        "vendor": spec["vendor"],
                        "product_type": spec["product_type"],
                        "tags": ", ".join(spec["tags"]),
                        "status": "active",
                    }
                },
            )
            # replace image
            imgs = api(token, "GET", f"/products/{pid}/images.json").get("images", [])
            for im in imgs:
                try:
                    api(token, "DELETE", f"/products/{pid}/images/{im['id']}.json")
                except Exception as ex:
                    print(f"  warn delete image: {ex}")
            upload_image(token, pid, img_path, spec["title"])
            # update variant price if present
            full = api(token, "GET", f"/products/{pid}.json")["product"]
            if full.get("variants"):
                vid = full["variants"][0]["id"]
                api(
                    token,
                    "PUT",
                    f"/variants/{vid}.json",
                    {
                        "variant": {
                            "id": vid,
                            "price": spec["price"],
                            "sku": spec["sku"],
                            "requires_shipping": False,
                        }
                    },
                )
            results.append({"action": "updated", "id": pid, "title": spec["title"], "handle": spec["handle"]})
        else:
            created = create_product(token, spec, img_path)
            print(f"  Created id={created['id']}")
            results.append(
                {
                    "action": "created",
                    "id": created["id"],
                    "title": created["title"],
                    "handle": created["handle"],
                }
            )
        time.sleep(0.4)  # gentle rate limit

    count = api(token, "GET", "/products/count.json").get("count")
    print(f"\nDone. Store product count: {count}")
    (OUT_DIR / "upload-results.json").write_text(json.dumps(results, indent=2), encoding="utf-8")
    print(f"Results saved to {OUT_DIR / 'upload-results.json'}")
    for r in results:
        print(f"  [{r['action']}] {r['title']} -> https://admin.shopify.com/store/icomply-supplys/products/{r['id']}")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        raise
