#!/usr/bin/env python3
"""Fire-family service x town pages: every indexable page >= 800 prose words, and
EVERY pair of towns within each service <= 0.70 similarity (difflib on word lists,
autojunk off). Pairs whose quick_ratio upper bound is <= 0.70 are skipped exactly.
Usage: check-fire-family-depth.py dist [--families a,b]"""
import difflib, itertools, os, re, sys
from multiprocessing import Pool
from html.parser import HTMLParser

FAMILIES = ["fire-alarms", "aov-air-handling", "emergency-lighting", "fire-risk-assessments", "fire-extinguishers",
            "fire-doors", "fire-stopping", "fire-suppression", "sprinkler-systems", "dry-risers", "fire-signage",
            "evacuation-alerts", "kitchen-fire-suppression", "fire-compartmentation", "smoke-co-alarms"]
PROSE = {"p", "h1", "h2", "h3", "h4", "summary", "dt", "dd"}
SKIP = {"header", "nav", "footer", "script", "style"}
LIMIT = 0.70

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

W = {}
def work(chunk):
    worst, over = (0.0, "", ""), []
    for a, b in chunk:
        sm = difflib.SequenceMatcher(None, W[a], W[b], autojunk=False)
        if sm.real_quick_ratio() <= LIMIT or sm.quick_ratio() <= LIMIT: continue
        r = sm.ratio()
        if r > worst[0]: worst = (r, a, b)
        if r > LIMIT: over.append((r, a, b))
    return worst, over

def main():
    dist = sys.argv[1] if len(sys.argv) > 1 else "dist"
    fams = FAMILIES
    if "--families" in sys.argv: fams = sys.argv[sys.argv.index("--families") + 1].split(",")
    fails, total, low = [], 0, 10**9
    for fam in fams:
        d = os.path.join(dist, "pages", fam)
        if not os.path.isdir(d): continue
        W.clear()
        for fn in sorted(os.listdir(d)):
            if not fn.endswith(".php"): continue
            w = words(os.path.join(d, fn))
            if w is None: continue
            W[fn] = w; total += 1; low = min(low, len(w))
            if len(w) < 800: fails.append(f"{fam}/{fn}: {len(w)} words")
        pairs = list(itertools.combinations(sorted(W), 2))
        chunks = [pairs[i::32] for i in range(32)]
        with Pool(os.cpu_count() or 4) as pool:
            res = pool.map(work, chunks)
        worst = max((r[0] for r in res), default=(0.0, "", ""))
        over = sorted((x for r in res for x in r[1]), reverse=True)
        fails += [f"{fam}: {a} vs {b} similarity {r:.3f}" for r, a, b in over[:5]]
        mn = min((len(v) for v in W.values()), default=0)
        print(f"{fam}: pages={len(W)} pairs={len(pairs)} min_words={mn} worst={worst[0]:.3f} {worst[1]} {worst[2]} over={len(over)}", flush=True)
    for f in fails[:40]: print("FAIL", f)
    print(f"check-fire-family-depth: {'FAIL' if fails else 'OK'} pages={total} min_words={low} fails={len(fails)}")
    sys.exit(1 if fails or total == 0 else 0)

if __name__ == "__main__":
    main()
