#!/usr/bin/env python3
"""Distinct WebP diagrams for P0 trade stems, plus missing service jpgs.

Each stem gets its own file so a coving page does not reuse a plastering photo.
These are original diagrams, not stock photos. The owner-job.svg slot is where
a real site photograph is swapped in later.
"""
from __future__ import annotations

import hashlib
import json
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parents[2]
INDEX = ROOT / "website/data/seo-lock/p0-index.json"
FAMILIES = ROOT / "website/data/service-image-families.json"
TRADES = ROOT / "website/assets/images/trades"
SERVICES = ROOT / "website/assets/images/services"
PLACEHOLDERS = ROOT / "website/assets/images/placeholders"
FONT = "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"
FONT_SM = "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"

NEW_SERVICES = ["drainage", "driveways", "fencing", "demolition"]


def hue(key: str) -> tuple[int, int, int]:
    digest = hashlib.sha256(key.encode()).digest()
    return (40 + digest[0] % 180, 50 + digest[1] % 140, 70 + digest[2] % 120)


def draw_card(key: str, title: str, subtitle: str, size=(960, 540)) -> Image.Image:
    bg = hue(key)
    img = Image.new("RGB", size, bg)
    draw = ImageDraw.Draw(img)
    accent = tuple(min(255, c + 70) for c in bg)
    dark = tuple(max(0, c // 3) for c in bg)
    w, h = size
    draw.rectangle((0, 0, w, 18), fill=(255, 107, 0))
    draw.rectangle((36, 70, w - 36, h - 36), outline=(255, 255, 255), width=4)
    # A simple trade mark that changes with the key: roof, wall, or vent.
    kind = int(hashlib.sha256(key.encode()).hexdigest(), 16) % 4
    if kind == 0:
        draw.polygon([(120, 300), (300, 150), (480, 300)], fill=accent)
        draw.rectangle((170, 300, 430, 430), fill=dark)
    elif kind == 1:
        draw.rectangle((140, 180, 420, 430), fill=dark)
        for i in range(4):
            draw.rectangle((160 + i * 60, 210, 200 + i * 60, 270), fill=accent)
    elif kind == 2:
        draw.ellipse((160, 160, 420, 420), outline=accent, width=18)
        draw.rectangle((270, 120, 310, 200), fill=(255, 107, 0))
    else:
        draw.polygon([(140, 400), (300, 160), (460, 400)], fill=accent)
        draw.rectangle((250, 250, 350, 400), fill=dark)
    font = ImageFont.truetype(FONT, 36)
    small = ImageFont.truetype(FONT_SM, 22)
    draw.text((48, 40), title[:42], fill=(255, 255, 255), font=font)
    draw.text((48, h - 78), subtitle[:70], fill=(255, 255, 255), font=small)
    return img


def stem_of(slug: str) -> str:
    stem = slug
    if stem.startswith("emergency-"):
        stem = stem[len("emergency-"):]
    for suffix in ("-near-me", "-cost", "-prices", "-quote"):
        if stem.endswith(suffix):
            stem = stem[: -len(suffix)]
    return stem


def ensure_context(slug: str) -> None:
    dest = SERVICES / f"{slug}-context.jpg"
    if dest.exists():
        return
    base = SERVICES / f"{slug}.jpg"
    if base.exists():
        img = Image.open(base).convert("RGB").transpose(Image.FLIP_LEFT_RIGHT)
    else:
        img = draw_card(slug + "-context", slug.replace("-", " "), "Context for this service")
    draw = ImageDraw.Draw(img)
    draw.rectangle((0, 0, img.width, 28), fill=(11, 31, 58))
    font = ImageFont.truetype(FONT_SM, 16)
    draw.text((12, 6), f"{slug} context", fill=(255, 255, 255), font=font)
    img.save(dest, quality=82)


def ensure_photo(slug: str) -> None:
    base = SERVICES / f"{slug}.jpg"
    photo = SERVICES / f"{slug}-photo.jpg"
    if not base.exists():
        draw_card(slug, slug.replace("-", " "), "iComply service diagram").save(base, quality=82)
    if not photo.exists():
        img = Image.open(base).convert("RGB")
        draw = ImageDraw.Draw(img)
        draw.rectangle((0, img.height - 36, img.width, img.height), fill=(255, 107, 0))
        font = ImageFont.truetype(FONT_SM, 18)
        draw.text((12, img.height - 30), f"{slug} detail", fill=(255, 255, 255), font=font)
        img.save(photo, quality=82)


def split_building_families() -> None:
    doc = json.loads(FAMILIES.read_text())
    families = doc["families"]
    building = list(families.get("building") or [])
    for slug in NEW_SERVICES:
        if slug not in building:
            building.append(slug)
    families["building"] = ["building-maintenance", "building-maintenance-photo", "building-maintenance-context"]
    for slug in building:
        if slug == "building-maintenance":
            continue
        families[slug] = [slug, f"{slug}-photo", f"{slug}-context"]
        ensure_photo(slug)
        ensure_context(slug)
    ensure_photo("building-maintenance")
    ensure_context("building-maintenance")
    for slug in NEW_SERVICES:
        families[slug] = [slug, f"{slug}-photo", f"{slug}-context"]
        ensure_photo(slug)
        ensure_context(slug)
    for name, members in list(families.items()):
        if len(members) == 2 and members[1] == f"{members[0]}-photo":
            families[name] = [members[0], members[1], f"{members[0]}-context"]
            ensure_context(members[0])
    FAMILIES.write_text(json.dumps(doc, indent=1) + "\n")


def main() -> None:
    TRADES.mkdir(parents=True, exist_ok=True)
    PLACEHOLDERS.mkdir(parents=True, exist_ok=True)
    rows = json.loads(INDEX.read_text())["rows"]
    keys = set()
    families = set()
    for slug, meta in rows.items():
        family = meta.get("family") or "general-building"
        families.add(family)
        keys.add(f"{family}--{stem_of(slug)}")
    made = 0
    for key in sorted(keys):
        dest = TRADES / f"{key}.webp"
        if dest.exists():
            continue
        title = key.split("--", 1)[-1].replace("-", " ")
        draw_card(key, title, "Diagram for this exact job").save(dest, "WEBP", quality=68)
        made += 1
    for family in sorted(families):
        dest = TRADES / f"{family}--detail.webp"
        if dest.exists():
            continue
        draw_card(family + "--detail", family.replace("-", " "), "Supporting detail for this trade").save(dest, "WEBP", quality=68)
        made += 1
    svg = PLACEHOLDERS / "owner-job.svg"
    if not svg.exists():
        svg.write_text(
            """<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675">
<rect width="1200" height="675" fill="#0B1F3A"/>
<rect x="48" y="48" width="1104" height="579" fill="none" stroke="#FF6B00" stroke-width="6" stroke-dasharray="16 10"/>
<text x="600" y="310" text-anchor="middle" fill="#ffffff" font-family="Arial,sans-serif" font-size="42">iComply job photo</text>
<text x="600" y="370" text-anchor="middle" fill="#dbe3ee" font-family="Arial,sans-serif" font-size="24">Swap this frame for a real site photograph</text>
</svg>
"""
        )
    split_building_families()
    print(f"trade diagrams written now={made} unique stems={len(keys)} families={len(families)}")


if __name__ == "__main__":
    main()
