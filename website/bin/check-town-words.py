#!/usr/bin/env python3
"""CLOSE-Q1 gate (2026-10-19): x town pages carry >= 800 prose words and stay
unique per town (worst-pair difflib <= 0.70 within each keyword/job/service).

Prose = text of p, h1-h4, summary, dt, dd outside header/nav/footer/script/
style (the SEO audit method). Similarity = difflib.SequenceMatcher ratio on
the prose word sequence. Also fails any page that renders noindex (#135).
usage: python3 website/bin/check-town-words.py /tmp/town-sample
"""
import collections, difflib, itertools, json, os, re, sys
from html.parser import HTMLParser

MIN_WORDS = 800
MAX_SIM = 0.70
CHROME = {"header", "nav", "footer", "noscript", "svg", "iframe"}
PROSE = {"p", "h1", "h2", "h3", "h4", "summary", "dt", "dd"}


class Prose(HTMLParser):
    def __init__(self):
        super().__init__()
        self.parts, self.buf, self.tag = [], [], None
        self.chrome = self.skip = 0
        self.body = False

    def handle_starttag(self, tag, attrs):
        if tag == "body":
            self.body = True
        if tag in ("script", "style"):
            self.skip += 1
            return
        if self.skip:
            return
        if tag in CHROME:
            self.chrome += 1
        if self.body and not self.chrome and tag in PROSE:
            self.tag, self.buf = tag, []

    def handle_endtag(self, tag):
        if tag in ("script", "style"):
            self.skip = max(0, self.skip - 1)
            return
        if self.skip:
            return
        if tag in CHROME and self.chrome:
            self.chrome -= 1
        if tag == self.tag:
            text = " ".join("".join(self.buf).split())
            if text:
                self.parts.append(text)
            self.tag = None

    def handle_data(self, data):
        if self.tag is not None and not self.skip:
            self.buf.append(data)


def main(out):
    rows = json.load(open(os.path.join(out, "index.json")))
    groups = collections.defaultdict(list)
    fail = 0
    for r in rows:
        html = open(os.path.join(out, r["file"]), encoding="utf-8").read()
        if "noindex" in r.get("robots", "") or re.search(r'<meta name="robots" content="[^"]*noindex', html, re.I):
            print(f"FAIL noindex {r['file']}")
            fail += 1
        p = Prose()
        p.feed(html)
        words = " ".join(p.parts).split()
        if len(words) < MIN_WORDS:
            print(f"FAIL {len(words)} words < {MIN_WORDS} {r['file']}")
            fail += 1
        groups[(r["kind"], r["slug"])].append((r["town"], words))
    for (kind, slug), items in groups.items():
        worst, pair = 0.0, None
        for (ta, a), (tb, b) in itertools.combinations(items, 2):
            q = difflib.SequenceMatcher(None, a, b, autojunk=False).ratio()
            if q > worst:
                worst, pair = q, (ta, tb)
        wmin = min(len(w) for _, w in items)
        status = "FAIL" if worst > MAX_SIM else "ok"
        if worst > MAX_SIM:
            fail += 1
        print(f"{status} {kind} {slug}: {len(items)} towns, min {wmin} words, worst-pair {worst:.2f} {pair}")
    print(f"check-town-words: {fail} failure(s)" if fail else f"check-town-words: OK ({len(rows)} pages; >= {MIN_WORDS} words, worst-pair <= {MAX_SIM})")
    return 1 if fail else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1] if len(sys.argv) > 1 else "/tmp/town-sample"))
