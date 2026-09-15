#!/usr/bin/env python3
"""Rasterise the navy + orange checkmark mark into the favicon set."""
from __future__ import annotations

import math
import struct
import zlib
from pathlib import Path

NAVY = (11, 31, 58, 255)
ORANGE = (255, 107, 0, 255)

ROOT = Path(__file__).resolve().parents[1]
IMG = ROOT / "assets" / "images"


def new_canvas(size: int, radius: bool = True) -> list[list[tuple[int, int, int, int]]]:
    px = [[(0, 0, 0, 0) for _ in range(size)] for _ in range(size)]
    cx = cy = (size - 1) / 2
    r = size / 2
    for y in range(size):
        for x in range(size):
            if (x - cx) ** 2 + (y - cy) ** 2 <= r * r:
                px[y][x] = NAVY
    return px


def set_px(px, x: float, y: float, color, w: float) -> None:
    size = len(px)
    r = max(1.0, w / 2)
    xmin = max(0, int(x - r - 1))
    xmax = min(size - 1, int(x + r + 1))
    ymin = max(0, int(y - r - 1))
    ymax = min(size - 1, int(y + r + 1))
    for yy in range(ymin, ymax + 1):
        for xx in range(xmin, xmax + 1):
            d = math.hypot(xx - x, yy - y)
            if d <= r:
                px[yy][xx] = color


def draw_check(px, size: int) -> None:
    # Check path in 64-unit viewBox: (18,33.2) -> (27.2,42.4) -> (46.5,22)
    pts = [(18, 33.2), (27.2, 42.4), (46.5, 22)]
    scale = size / 64
    width = max(2.0, 6.2 * scale)
    segs = []
    for i in range(len(pts) - 1):
        x0, y0 = pts[i][0] * scale, pts[i][1] * scale
        x1, y1 = pts[i + 1][0] * scale, pts[i + 1][1] * scale
        steps = max(size * 3, int(math.hypot(x1 - x0, y1 - y0) * 4))
        for s in range(steps + 1):
            t = s / steps
            segs.append((x0 + (x1 - x0) * t, y0 + (y1 - y0) * t))
    for x, y in segs:
        set_px(px, x, y, ORANGE, width)


def write_png(path: Path, px) -> None:
    h = len(px)
    w = len(px[0])
    raw = b"".join(b"\x00" + b"".join(struct.pack("BBBB", *px[y][x]) for x in range(w)) for y in range(h))

    def chunk(tag: bytes, data: bytes) -> bytes:
        return struct.pack(">I", len(data)) + tag + data + struct.pack(">I", zlib.crc32(tag + data) & 0xFFFFFFFF)

    ihdr = struct.pack(">IIBBBBB", w, h, 8, 6, 0, 0, 0)
    png = b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", ihdr) + chunk(b"IDAT", zlib.compress(raw, 9)) + chunk(b"IEND", b"")
    path.write_bytes(png)


def write_ico(path: Path, png_bytes: bytes) -> None:
    # PNG-in-ICO (modern browsers)
    header = struct.pack("<HHH", 0, 1, 1)
    entry = struct.pack("<BBBBHHII", 0, 0, 0, 0, 1, 32, len(png_bytes), 22)
    path.write_bytes(header + entry + png_bytes)


def main() -> None:
    sizes = {
        "favicon-16.png": 16,
        "favicon-32.png": 32,
        "apple-touch-icon.png": 180,
        "favicon-192.png": 192,
        "android-chrome-192.png": 192,
        "android-chrome-512.png": 512,
    }
    IMG.mkdir(parents=True, exist_ok=True)
    rendered: dict[int, Path] = {}
    for name, size in sizes.items():
        px = new_canvas(size)
        draw_check(px, size)
        out = IMG / name
        write_png(out, px)
        rendered[size] = out
        print("wrote", out, out.stat().st_size)
    ico32 = (IMG / "favicon-32.png").read_bytes()
    write_ico(IMG / "favicon.ico", ico32)
    write_ico(ROOT / "favicon.ico", ico32)
    print("wrote favicon.ico")


if __name__ == "__main__":
    main()
