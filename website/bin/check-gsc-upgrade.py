#!/usr/bin/env python3
"""GSC 21 Oct gate on the pages rendered by render-gsc-pages.mjs: every page 200,
index,follow, >= 800 prose words, worst-pair similarity <= 0.70 within each family
(difflib on word lists, autojunk off), and contact details only 07517 806082 /
icomplypropertyservices@gmail.com. Usage: check-gsc-upgrade.py /tmp/gsc-render"""
import collections, difflib, itertools, os, re, sys
from html.parser import HTMLParser
PROSE = {"p", "h1", "h2", "h3", "h4", "summary", "dt", "dd"}
SKIP = {"header", "nav", "footer", "script", "style"}
class Prose(HTMLParser):
    def __init__(self):
        super().__init__(); self.skip = 0; self.stack = []; self.parts = []; self.buf = []
    def handle_starttag(self, tag, attrs):
        if tag in SKIP: self.skip += 1
        if tag in PROSE and not self.skip: self.stack.append(tag); self.buf = []
    def handle_endtag(self, tag):
        if tag in SKIP and self.skip: self.skip -= 1
        if tag in PROSE and self.stack and self.stack[-1] == tag:
            self.stack.pop(); t = " ".join("".join(self.buf).split())
            if t: self.parts.append(t)
    def handle_data(self, data):
        if self.stack and not self.skip: self.buf.append(data)
d = sys.argv[1] if len(sys.argv) > 1 else "/tmp/gsc-render"
fails, fam = [], collections.defaultdict(dict)
rows = [l.rstrip("\n").split("\t") for l in open(os.path.join(d, "index.tsv")) if l.strip()]
low = 10**9
for p, family, status, f in rows:
    if status != "200": fails.append(f"{p}: status {status}"); continue
    html = open(os.path.join(d, f), errors="ignore").read()
    rb = re.search(r'<meta name="robots" content="([^"]*)"', html)
    if not rb or "noindex" in rb.group(1): fails.append(f"{p}: robots {rb.group(1) if rb else 'missing'}")
    for ph in set(re.findall(r"0\d{3}\s?\d{3}\s?\d{4}|07\d{3}\s?\d{6}", re.sub(r"<script[\s\S]*?</script>", "", html))):
        if re.sub(r"\s", "", ph) not in ("07517806082", "0800111999"): fails.append(f"{p}: phone {ph}")
    for em in set(re.findall(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[a-z]{2,}", html)):
        if em.lower() != "icomplypropertyservices@gmail.com" and not em.endswith((".png", ".jpg", ".webp")): fails.append(f"{p}: email {em}")
    pr = Prose(); pr.feed(html); w = " ".join(pr.parts).split()
    low = min(low, len(w))
    if len(w) < 800: fails.append(f"{p}: {len(w)} words")
    fam[family][p] = w
worst_all = (0, "", "")
for name, pages in sorted(fam.items()):
    worst = (0.0, "", "")
    for a, b in itertools.combinations(sorted(pages), 2):
        sm = difflib.SequenceMatcher(None, pages[a], pages[b], autojunk=False)
        if sm.real_quick_ratio() <= worst[0] or sm.quick_ratio() <= worst[0]: continue
        r = sm.ratio()
        if r > worst[0]: worst = (r, a, b)
    if worst[0] > 0.70: fails.append(f"{name}: {worst[1]} vs {worst[2]} similarity {worst[0]:.3f}")
    if worst[0] > worst_all[0]: worst_all = (worst[0], worst[1], worst[2])
    print(f"{name}: pages={len(pages)} worst={worst[0]:.3f}")
for f in fails[:60]: print("FAIL", f)
print(f"check-gsc-upgrade: {'FAIL' if fails else 'OK'} pages={len(rows)} min_words={low} worst_pair={worst_all[0]:.3f} ({worst_all[1]} vs {worst_all[2]}) fails={len(fails)}")
sys.exit(1 if fails else 0)
