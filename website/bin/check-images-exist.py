#!/usr/bin/env python3
"""Every <img src> and og:image / twitter:image on every exported page must exist on disk
(dist, falling back to website/ for assets the export copies at deploy).
Usage: check-images-exist.py dist"""
import pathlib, re, sys, urllib.parse
from collections import defaultdict

dist = pathlib.Path(sys.argv[1] if len(sys.argv) > 1 else "dist")
site = pathlib.Path(__file__).resolve().parent.parent
HOST = "icomplypropertyservices.co.uk"
IMG = re.compile(r'<img\b[^>]*?\ssrc="([^"]+)"', re.I)
META = re.compile(r'<meta\b[^>]*(?:property|name)="(?:og:image|twitter:image)"[^>]*content="([^"]+)"|<meta\b[^>]*content="([^"]+)"[^>]*(?:property|name)="(?:og:image|twitter:image)"', re.I)
missing = defaultdict(list)
pages = refs = 0
seen = {}
for f in dist.rglob("*"):
    if f.suffix not in (".php", ".html") or not f.is_file():
        continue
    pages += 1
    html = f.read_text(errors="ignore")
    urls = IMG.findall(html) + [a or b for a, b in META.findall(html)]
    for u in urls:
        u = u.replace("&amp;", "&").strip()
        if not u or u.startswith(("data:", "{", "<?", "$")):
            continue
        p = urllib.parse.urlparse(u)
        if p.scheme and p.hostname and not p.hostname.endswith(HOST):
            continue
        path = urllib.parse.unquote(p.path)
        if not path.startswith("/"):
            path = "/" + str((f.parent.relative_to(dist) / path)).lstrip("./")
        refs += 1
        if path not in seen:
            rel = path.lstrip("/")
            seen[path] = (dist / rel).is_file() or (site / rel).is_file()
        if not seen[path]:
            missing[path].append(str(f.relative_to(dist)))
for path, where in sorted(missing.items(), key=lambda x: -len(x[1]))[:30]:
    print(f"FAIL missing {path} on {len(where)} page(s), e.g. {where[0]}")
print(f"check-images-exist: {'FAIL' if missing else 'OK'} pages={pages} image_refs={refs} unique={len(seen)} missing={len(missing)}")
sys.exit(1 if missing or pages == 0 else 0)
