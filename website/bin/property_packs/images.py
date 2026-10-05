"""Fetch Wikimedia Commons images for a pack, crop to 1200x630 JPEG and record credits.

Usage (from website/):
  python3 bin/property_packs/images.py search "floor plan drawing"
  python3 bin/property_packs/images.py fetch /assets/images/services/hmo-design.jpg "File:Some image.jpg"
Prints a JSON credit row on fetch. Identical in every pack PR.
"""
import io
import json
import re
import sys
import urllib.parse
import urllib.request

from PIL import Image

API = "https://commons.wikimedia.org/w/api.php"
UA = {"User-Agent": "iComplyPropertyServicesSiteBuild/1.0 (hello@icomplypropertyservices.co.uk)"}


def api(params):
    url = API + "?" + urllib.parse.urlencode(dict(params, format="json"))
    with urllib.request.urlopen(urllib.request.Request(url, headers=UA), timeout=30) as r:
        return json.load(r)


def search(term, limit=12):
    d = api({"action": "query", "generator": "search", "gsrsearch": "filetype:bitmap " + term, "gsrnamespace": 6,
             "gsrlimit": limit, "prop": "imageinfo", "iiprop": "url|size|extmetadata"})
    for p in (d.get("query", {}).get("pages", {}) or {}).values():
        ii = p["imageinfo"][0]
        lic = ii.get("extmetadata", {}).get("LicenseShortName", {}).get("value", "")
        print(f"{ii['width']}x{ii['height']}\t{lic}\t{p['title']}")


def strip_tags(s):
    return re.sub(r"<[^>]+>", "", s or "").strip()


def fetch(target, title, root="."):
    d = api({"action": "query", "titles": title, "prop": "imageinfo", "iiprop": "url|extmetadata", "iiurlwidth": 1600})
    page = next(iter(d["query"]["pages"].values()))
    ii = page["imageinfo"][0]
    meta = ii.get("extmetadata", {})
    src = ii.get("thumburl") or ii["url"]
    with urllib.request.urlopen(urllib.request.Request(src, headers=UA), timeout=60) as r:
        img = Image.open(io.BytesIO(r.read())).convert("RGB")
    w, hgt = img.size
    ratio = 1200 / 630
    if w / hgt > ratio:
        nw = int(hgt * ratio)
        img = img.crop(((w - nw) // 2, 0, (w - nw) // 2 + nw, hgt))
    else:
        nh = int(w / ratio)
        img = img.crop((0, (hgt - nh) // 2, w, (hgt - nh) // 2 + nh))
    img = img.resize((1200, 630), Image.LANCZOS)
    img.save(root + target, "JPEG", quality=82, optimize=True, progressive=True)
    credit = {
        "file": target,
        "source": "https://commons.wikimedia.org/wiki/" + urllib.parse.quote(page["title"].replace(" ", "_")),
        "title": page["title"],
        "license": strip_tags(meta.get("LicenseShortName", {}).get("value", "")),
        "artist": strip_tags(meta.get("Artist", {}).get("value", "")),
    }
    print(json.dumps(credit, ensure_ascii=False))
    return credit


if __name__ == "__main__":
    if sys.argv[1] == "search":
        search(" ".join(sys.argv[2:]))
    elif sys.argv[1] == "fetch":
        fetch(sys.argv[2], sys.argv[3])
