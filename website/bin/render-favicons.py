#!/usr/bin/env python3
"""Rasterise the navy circle + orange check mark into the favicon set.

Source of truth: website/assets/images/brand/icomply-mark.svg
(Jack-approved compliance tick — not the old flame mark).
"""
from __future__ import annotations

import math
import struct
import zlib
from pathlib import Path

NAVY = (11, 31, 58, 255)       # #0B1F3A
ORANGE = (255, 107, 0, 255)    # #FF6B00

ROOT = Path(__file__).resolve().parents[1]
IMG = ROOT / "assets" / "images"
BRAND = IMG / "brand"

# Same path as icomply-mark.svg (64-unit viewBox)
CHECK = [(19.2, 33.4), (28.1, 42.6), (45.8, 22.2)]
STROKE = 7.2


def blend(dst: tuple[int, int, int, int], src: tuple[int, int, int, int], a: float) -> tuple[int, int, int, int]:
    a = max(0.0, min(1.0, a))
    if a <= 0:
        return dst
    if a >= 1 and src[3] == 255:
        return src
    ia = 1.0 - a
    return (
        int(src[0] * a + dst[0] * ia),
        int(src[1] * a + dst[1] * ia),
        int(src[2] * a + dst[2] * ia),
        255,
    )


def new_canvas(size: int) -> list[list[tuple[int, int, int, int]]]:
    return [[(0, 0, 0, 0) for _ in range(size)] for _ in range(size)]


def fill_circle(px, size: int, color, cx: float, cy: float, r: float) -> None:
    r2 = r * r
    for y in range(size):
        for x in range(size):
            d2 = (x + 0.5 - cx) ** 2 + (y + 0.5 - cy) ** 2
            if d2 <= (r - 0.55) ** 2:
                px[y][x] = color
            elif d2 <= (r + 0.55) ** 2:
                # Coverage at the rim
                d = math.sqrt(d2)
                a = max(0.0, min(1.0, (r + 0.55 - d)))
                px[y][x] = blend(px[y][x], color, a)


def stamp_disk(px, size: int, x: float, y: float, r: float, color) -> None:
    xmin = max(0, int(x - r - 1))
    xmax = min(size - 1, int(x + r + 1))
    ymin = max(0, int(y - r - 1))
    ymax = min(size - 1, int(y + r + 1))
    r2 = r * r
    for yy in range(ymin, ymax + 1):
        for xx in range(xmin, xmax + 1):
            d2 = (xx + 0.5 - x) ** 2 + (yy + 0.5 - y) ** 2
            if d2 <= (r - 0.45) ** 2:
                px[yy][xx] = color
            elif d2 <= (r + 0.45) ** 2:
                d = math.sqrt(d2)
                a = max(0.0, min(1.0, (r + 0.45 - d)))
                px[yy][xx] = blend(px[yy][xx], color, a)


def draw_check(px, size: int) -> None:
    scale = size / 64.0
    width = STROKE * scale
    radius = width / 2.0
    pts = [(p[0] * scale, p[1] * scale) for p in CHECK]
    samples: list[tuple[float, float]] = []
    for i in range(len(pts) - 1):
        x0, y0 = pts[i]
        x1, y1 = pts[i + 1]
        dist = math.hypot(x1 - x0, y1 - y0)
        steps = max(8, int(dist * 3))
        for s in range(steps + 1):
            t = s / steps
            samples.append((x0 + (x1 - x0) * t, y0 + (y1 - y0) * t))
    for x, y in samples:
        stamp_disk(px, size, x, y, radius, ORANGE)


def render_mark(size: int):
    px = new_canvas(size)
    fill_circle(px, size, NAVY, size / 2, size / 2, size / 2)
    draw_check(px, size)
    return px


def write_png(path: Path, px) -> None:
    h = len(px)
    w = len(px[0])
    raw = b"".join(b"\x00" + b"".join(struct.pack("BBBB", *px[y][x]) for x in range(w)) for y in range(h))

    def chunk(tag: bytes, data: bytes) -> bytes:
        return struct.pack(">I", len(data)) + tag + data + struct.pack(">I", zlib.crc32(tag + data) & 0xFFFFFFFF)

    ihdr = struct.pack(">IIBBBBB", w, h, 8, 6, 0, 0, 0)
    png = b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", ihdr) + chunk(b"IDAT", zlib.compress(raw, 9)) + chunk(b"IEND", b"")
    path.write_bytes(png)


def write_ico(path: Path, pngs: list[bytes]) -> None:
    count = len(pngs)
    header = struct.pack("<HHH", 0, 1, count)
    offset = 6 + 16 * count
    entries = b""
    payload = b""
    for data in pngs:
        # 0x0 means 256 in ICO; we use PNG-in-ICO so size bytes are ignored by modern clients
        entries += struct.pack("<BBBBHHII", 0, 0, 0, 0, 1, 32, len(data), offset)
        payload += data
        offset += len(data)
    path.write_bytes(header + entries + payload)


def main() -> None:
    IMG.mkdir(parents=True, exist_ok=True)
    BRAND.mkdir(parents=True, exist_ok=True)

    sizes = {
        "favicon-16.png": 16,
        "favicon-32.png": 32,
        "apple-touch-icon.png": 180,
        "favicon-192.png": 192,
        "android-chrome-192.png": 192,
        "android-chrome-512.png": 512,
    }
    rendered_png: dict[int, bytes] = {}
    for name, size in sizes.items():
        px = render_mark(size)
        out = IMG / name
        write_png(out, px)
        rendered_png[size] = out.read_bytes()
        print("wrote", out, out.stat().st_size)

    mark512 = IMG / "android-chrome-512.png"
    brand512 = BRAND / "icomply-mark-512.png"
    brand512.write_bytes(mark512.read_bytes())
    print("wrote", brand512)

    ico_pngs = [rendered_png[16], rendered_png[32]]
    write_ico(IMG / "favicon.ico", ico_pngs)
    write_ico(ROOT / "favicon.ico", ico_pngs)
    print("wrote favicon.ico")


if __name__ == "__main__":
    main()
