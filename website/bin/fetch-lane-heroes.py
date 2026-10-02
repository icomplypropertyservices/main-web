#!/usr/bin/env python3
"""Download distinct Wikimedia Commons photos for AOV and barrier hero pools.

License filter: Public Domain, CC0, CC BY, CC BY-SA only (no NC / ND).
Same sources the site already uses via bin/download-relevant-stock.php.
"""
from __future__ import annotations

import hashlib
import json
import os
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
UA = "IcomplyHeroAudit/1.0 (https://icomplypropertyservices.co.uk; distinct heroes)"
MIN_BYTES = 20000

LANE_QUERIES = {
    "aov-air-handling": [
        "air handling unit",
        "rooftop HVAC unit",
        "ventilation duct",
        "centrifugal fan industrial",
        "smoke exhaust fan",
        "window chain actuator",
        "linear actuator",
        "industrial electric motor",
        "roof hatch",
        "smoke vent",
    ],
    "barriers": [
        "boom barrier",
        "barrier arm gate",
        "parking barrier",
        "vehicle gate barrier",
        "automatic barrier",
        "rising arm barrier",
        "car park barrier",
    ],
}

# Card photos. Each query is fetched separately so the seven AOV kits and five
# barrier packs do not share a file.
PRODUCT_QUERIES = {
    "aov-ctrl": ["fire alarm control panel", "fire alarm panel", "control panel electrical cabinet"],
    "aov-sensor": ["optical smoke detector", "smoke alarm detector", "smoke detector"],
    "aov-act": ["window actuator", "electric window opener", "chain actuator window"],
    "aov-act-hvy": ["linear actuator", "electric linear actuator", "window opener mechanism"],
    "aov-motor": ["electric motor industrial", "induction motor", "electric motor"],
    "aov-motor-hvy": ["air handling unit fan", "centrifugal fan", "industrial ventilation fan"],
    "aov-kit-1m2": ["roof hatch", "smoke vent roof", "rooftop skylight hatch"],
    "bar-5m-std": ["boom barrier", "parking barrier", "barrier gate"],
    "bar-5m-videx": ["parking barrier gate", "ticket barrier parking", "car park barrier"],
    "bar-5m-paxton": ["automatic barrier arm", "barrier arm gate", "security barrier gate"],
    "bar-5m-gsm": ["vehicle gate barrier", "entrance barrier", "toll barrier"],
    "bar-5m-allin": ["rising arm barrier", "parking lot barrier", "closed barrier gate"],
}

AOV_SEEDS = [
    "/assets/images/services/aov-air-handling.jpg",
    "/assets/images/services/aov-air-handling-photo.jpg",
]


def api(params: dict) -> dict:
    params = dict(params)
    params["format"] = "json"
    url = "https://commons.wikimedia.org/w/api.php?" + urllib.parse.urlencode(params)
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=40) as resp:
        return json.load(resp)


def license_ok(name: str) -> bool:
    n = (name or "").lower().replace("creative commons", "cc")
    if n.strip() == "":
        return False
    if "nc" in n or "noncommercial" in n or "non-commercial" in n:
        return False
    if "-nd" in n or "noderiv" in n or "no deriv" in n:
        return False
    if "public domain" in n or n.strip() in {"pd", "cc0"} or "cc0" in n:
        return True
    if n.startswith("cc by") or "cc by" in n or n.startswith("cc-by"):
        return True
    return False


def title_ok(title: str) -> bool:
    t = title.lower()
    bad = ("logo", "icon", "flag", "map", "diagram", "coat of arms", "signature", "svg", "pictogram", "symbol", "ceiling fan", "helicopter")
    return not any(b in t for b in bad)


def search(query: str, limit: int = 20) -> list[dict]:
    data = api(
        {
            "action": "query",
            "generator": "search",
            "gsrsearch": query,
            "gsrnamespace": "6",
            "gsrlimit": str(limit),
            "prop": "imageinfo",
            "iiprop": "url|mime|size|extmetadata",
            "iiurlwidth": "1400",
        }
    )
    pages = (data.get("query") or {}).get("pages") or {}
    out = []
    for page in pages.values():
        info = (page.get("imageinfo") or [None])[0]
        if not info:
            continue
        meta = info.get("extmetadata") or {}
        lic = ((meta.get("LicenseShortName") or {}).get("value") or "")
        title = page.get("title") or ""
        thumb = info.get("thumburl") or ""
        mime = info.get("mime") or ""
        if not thumb or not title_ok(title) or not license_ok(lic):
            continue
        if mime not in ("image/jpeg", "image/png", "image/webp") and not thumb.lower().endswith((".jpg", ".jpeg", ".png")):
            continue
        out.append(
            {
                "title": title,
                "thumb": thumb,
                "license": lic,
                "artist": ((meta.get("Artist") or {}).get("value") or "")[:240],
                "page": "https://commons.wikimedia.org/wiki/" + urllib.parse.quote(title.replace(" ", "_")),
            }
        )
    return out


def download(url: str) -> bytes | None:
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    try:
        with urllib.request.urlopen(req, timeout=40) as resp:
            data = resp.read()
    except Exception:
        return None
    if len(data) < MIN_BYTES:
        return None
    if not (data.startswith(b"\xff\xd8") or data.startswith(b"\x89PNG")):
        return None
    return data


def ext_for(data: bytes) -> str:
    return ".png" if data.startswith(b"\x89PNG") else ".jpg"


def main() -> None:
    seen_hash: dict[str, str] = {}
    credits: list[dict] = []

    def store(rel_no_ext: str, item: dict) -> str | None:
        data = download(item["thumb"])
        if data is None:
            return None
        digest = hashlib.md5(data).hexdigest()
        if digest in seen_hash:
            return None
        rel = rel_no_ext + ext_for(data)
        dest = os.path.join(ROOT, rel.lstrip("/"))
        os.makedirs(os.path.dirname(dest), exist_ok=True)
        with open(dest, "wb") as fh:
            fh.write(data)
        seen_hash[digest] = rel
        credits.append(
            {
                "file": rel,
                "source": item["page"],
                "title": item["title"],
                "license": item["license"],
                "artist": item["artist"],
            }
        )
        print("saved", rel, item["license"], item["title"][:70])
        return rel

    pools: dict[str, list[str]] = {}
    for lane, queries in LANE_QUERIES.items():
        files: list[str] = []
        if lane == "aov-air-handling":
            for seed in AOV_SEEDS:
                path = os.path.join(ROOT, seed.lstrip("/"))
                if not os.path.isfile(path):
                    continue
                data = open(path, "rb").read()
                digest = hashlib.md5(data).hexdigest()
                if digest in seen_hash or len(data) < MIN_BYTES:
                    continue
                seen_hash[digest] = seed
                files.append(seed)
                credits.append(
                    {
                        "file": seed,
                        "source": "existing site asset",
                        "title": os.path.basename(seed),
                        "license": "already used on icomplypropertyservices.co.uk",
                        "artist": "",
                    }
                )
        existing = sorted(
            p for p in os.listdir(os.path.join(ROOT, "assets/images/lanes", lane))
            if p.lower().endswith((".jpg", ".jpeg", ".png"))
        ) if os.path.isdir(os.path.join(ROOT, "assets/images/lanes", lane)) else []
        for name in existing:
            rel = f"/assets/images/lanes/{lane}/{name}"
            path = os.path.join(ROOT, rel.lstrip("/"))
            data = open(path, "rb").read()
            digest = hashlib.md5(data).hexdigest()
            if digest in seen_hash:
                continue
            seen_hash[digest] = rel
            if rel not in files:
                files.append(rel)
        if len(files) >= 24:
            print(lane, "reused", len(files))
            pools[lane] = files
            continue
        candidates: list[dict] = []
        for q in queries:
            try:
                candidates.extend(search(q, 12))
            except Exception as exc:
                print("search failed", q, exc)
        # de-dupe candidate URLs
        uniq = []
        seen_url = set()
        for c in candidates:
            if c["thumb"] in seen_url:
                continue
            seen_url.add(c["thumb"])
            uniq.append(c)
        n = 0
        lane_dir = os.path.join(ROOT, "assets/images/lanes", lane)
        if os.path.isdir(lane_dir):
            for name in os.listdir(lane_dir):
                stem = os.path.splitext(name)[0]
                if stem.isdigit():
                    n = max(n, int(stem))
        for item in uniq:
            if len(files) >= 24:
                break
            n += 1
            rel = store(f"/assets/images/lanes/{lane}/{n:02d}", item)
            if rel:
                files.append(rel)
        if len(files) < 21:
            raise SystemExit(f"{lane} pool too small: {len(files)} (need >= 21)")
        pools[lane] = files
        print(lane, "pool", len(files))

    products: dict[str, str] = {}
    for key, queries in PRODUCT_QUERIES.items():
        saved = None
        for query in queries:
            try:
                found = search(query, 15)
            except Exception as exc:
                print("product search failed", key, query, exc)
                found = []
            for item in found:
                saved = store(f"/assets/images/products/{key}", item)
                if saved:
                    break
            if saved:
                break
        if not saved:
            raise SystemExit(f"no product image for {key}")
        products[key] = saved

    # Alt hatch keys must not repeat the kit photo.
    products["aov-kit-1m2_alt_hatch"] = products["aov-kit-1m2"]
    # Force a distinct file for the alt if the kit file was reused above.
    # Search once more and skip the hash already stored for the kit.
    kit_hash = None
    kit_path = os.path.join(ROOT, products["aov-kit-1m2"].lstrip("/"))
    kit_hash = hashlib.md5(open(kit_path, "rb").read()).hexdigest()
    for item in search("smoke vent roof", 12):
        data = download(item["thumb"])
        if not data:
            continue
        digest = hashlib.md5(data).hexdigest()
        if digest == kit_hash or digest in seen_hash:
            continue
        rel = "/assets/images/products/aov-kit-1m2-hatch" + ext_for(data)
        dest = os.path.join(ROOT, rel.lstrip("/"))
        with open(dest, "wb") as fh:
            fh.write(data)
        seen_hash[digest] = rel
        credits.append(
            {
                "file": rel,
                "source": item["page"],
                "title": item["title"],
                "license": item["license"],
                "artist": item["artist"],
            }
        )
        products["aov-kit-1m2_alt_hatch"] = rel
        products["aov-kit-1m2_shopify_hatch"] = rel
        break
    else:
        products["aov-kit-1m2_shopify_hatch"] = products["aov-act"]

    aov_map = {k: products[k] for k in [
        "aov-ctrl", "aov-sensor", "aov-act", "aov-act-hvy", "aov-motor", "aov-motor-hvy",
        "aov-kit-1m2", "aov-kit-1m2_alt_hatch", "aov-kit-1m2_shopify_hatch",
    ]}
    bar_map = {k: products[k] for k in [
        "bar-5m-std", "bar-5m-videx", "bar-5m-paxton", "bar-5m-gsm", "bar-5m-allin",
    ]}
    # Primary card keys must be unique.
    prim_aov = ["aov-ctrl", "aov-sensor", "aov-act", "aov-act-hvy", "aov-motor", "aov-motor-hvy", "aov-kit-1m2"]
    if len({aov_map[k] for k in prim_aov}) != len(prim_aov):
        raise SystemExit("AOV product srcs are not unique")
    if len(set(bar_map.values())) != len(bar_map):
        raise SystemExit("barrier product srcs are not unique")

    with open(os.path.join(ROOT, "data/aov-kit-cdn-images.json"), "w", encoding="utf-8") as fh:
        json.dump(aov_map, fh, indent=2)
        fh.write("\n")
    with open(os.path.join(ROOT, "data/bar-5m-came-gard-images.json"), "w", encoding="utf-8") as fh:
        json.dump(bar_map, fh, indent=2)
        fh.write("\n")

    manifest = {
        "max_per_lane": 8,
        "cluster_size": 8,
        "note": "Same primary hero src may appear on at most 8 pages in one lane. Town clusters are 8 consecutive areas from data/areas.json. Pools are AOV and vehicle barriers only (v1).",
        "pools": pools,
        "service_pools": {"aov-air-handling": "aov-air-handling"},
        "barrier_keywords": ["car-park-barrier-access", "gate-access-control"],
    }
    with open(os.path.join(ROOT, "data/hero-pools.json"), "w", encoding="utf-8") as fh:
        json.dump(manifest, fh, indent=2)
        fh.write("\n")
    with open(os.path.join(ROOT, "data/hero-image-credits.json"), "w", encoding="utf-8") as fh:
        json.dump(credits, fh, indent=2)
        fh.write("\n")
    print("done", {k: len(v) for k, v in pools.items()})


if __name__ == "__main__":
    main()
