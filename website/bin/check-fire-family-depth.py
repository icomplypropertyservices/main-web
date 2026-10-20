#!/usr/bin/env python3
"""Fire-family service x town pages: every indexable page >= 800 prose words, and the
worst pair in a seeded 20-town sample per family <= 0.70 (difflib on word lists).
Usage: check-fire-family-depth.py dist"""
import difflib, itertools, os, random, re, sys
from html.parser import HTMLParser

FAMILIES = ["fire-alarms", "emergency-lighting", "fire-signage", "fire-extinguishers", "evacuation-alerts",
            "fire-doors", "fire-risk-assessments", "dry-risers", "sprinkler-systems", "fire-stopping",
            "fire-compartmentation"]
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

def words(path):
    html = open(path, errors="ignore").read()
    m = re.search(r'<meta name="robots" content="([^"]*)"', html)
    if m and "noindex" in m.group(1): return None
    p = Prose(); p.feed(html); return " ".join(p.parts).split()

dist = sys.argv[1] if len(sys.argv) > 1 else "dist"
fails, total, low, worst = [], 0, 10**9, (0.0, "", "", "")
for fam in FAMILIES:
    d = os.path.join(dist, "pages", fam)
    if not os.path.isdir(d): continue
    W = {}
    for fn in sorted(os.listdir(d)):
        if not fn.endswith(".php"): continue
        w = words(os.path.join(d, fn))
        if w is None: continue
        W[fn] = w; total += 1; low = min(low, len(w))
        if len(w) < 800: fails.append(f"{fam}/{fn}: {len(w)} words")
    random.seed(fam)
    sample = random.sample(sorted(W), min(20, len(W)))
    for a, b in itertools.combinations(sample, 2):
        r = difflib.SequenceMatcher(None, W[a], W[b], autojunk=False).ratio()
        if r > worst[0]: worst = (r, fam, a, b)
        if r > 0.70: fails.append(f"{fam}: {a} vs {b} similarity {r:.3f}")
for f in fails[:20]: print("FAIL", f)
print(f"check-fire-family-depth: {'FAIL' if fails else 'OK'} pages={total} min_words={low} worst={worst[0]:.3f} {worst[1]} {worst[2]} {worst[3]}")
sys.exit(1 if fails or total == 0 else 0)
