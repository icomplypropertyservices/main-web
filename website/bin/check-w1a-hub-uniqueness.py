#!/usr/bin/env python3
"""W1a nationwide P0 job hubs gate (SEO PR144 scorecard, 2026-10-20).

Run after static-export. For every rendered /pages/jobs/{slug} hub listed in
website/data/nationwide-p0-jobs.json: >= 800 prose words, self-canonical,
no noindex, no doubled word, no builder wording, and worst-pair difflib
similarity <= 0.70 across all hubs (prose = p/h1-h4/summary/dt/dd outside
header/nav/footer, the SEO audit method).
usage: python3 website/bin/check-w1a-hub-uniqueness.py [dist]
"""
import difflib, itertools, json, os, re, sys
from html.parser import HTMLParser

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
MIN_WORDS, MAX_SIM = 800, 0.70
CHROME = {"header", "nav", "footer", "noscript", "svg", "iframe"}
PROSE = {"p", "h1", "h2", "h3", "h4", "summary", "dt", "dd"}
DOUBLED = re.compile(r"\b((?:[A-Za-z][\w'’-]*\s+){0,3}[A-Za-z][\w'’-]*)\s+\1\b", re.I)
BANNED = ["on this page", "do not claim", "do not invent", "intent of this page", "this index",
          "dual ring", "job×town", "this page", "catalogue fee", "removed url", "this town page",
          "info@", "not gas safe", "keyword guide"]


class Prose(HTMLParser):
    def __init__(self):
        super().__init__()
        self.parts, self.buf, self.tag, self.chrome, self.skip, self.body = [], [], None, 0, 0, False

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


def main(dist):
    jobs = json.load(open(os.path.join(ROOT, "data", "nationwide-p0-jobs.json")))
    docs, fail = {}, 0
    for slug in jobs:
        path = f"/pages/jobs/{slug}"
        f = os.path.join(dist, path.strip("/") + ".php")
        if not os.path.isfile(f):
            f = os.path.join(dist, path.strip("/"), "index.html")
        html = open(f, encoding="utf-8").read()
        p = Prose()
        p.feed(html)
        joined = " ".join(p.parts)
        words = joined.split()
        docs[slug] = words
        issues = []
        can = re.search(r'rel="canonical" href="([^"]+)"', html)
        if not can or not can.group(1).endswith(path):
            issues.append(f"canonical {can.group(1) if can else None}")
        if re.search(r'<meta name="robots" content="[^"]*noindex', html, re.I):
            issues.append("noindex")
        if len(words) < MIN_WORDS:
            issues.append(f"{len(words)} words")
        if DOUBLED.search(joined):
            issues.append(f"doubled {DOUBLED.search(joined).group(0)!r}")
        low = joined.lower()
        issues += [f"builder wording {b!r}" for b in BANNED if b in low]
        if issues:
            fail += 1
            print(f"FAIL {path}: {'; '.join(issues)}")
    worst, pair, over = 0.0, None, 0
    for a, b in itertools.combinations(docs, 2):
        q = difflib.SequenceMatcher(None, docs[a], docs[b], autojunk=False).ratio()
        over += q > MAX_SIM
        if q > worst:
            worst, pair = q, (a, b)
    if worst > MAX_SIM:
        fail += 1
        print(f"FAIL worst-pair {worst:.2f} {pair}; {over} pairs > {MAX_SIM}")
    print(f"check-w1a-hub-uniqueness: {'%d failure(s)' % fail if fail else 'OK'} ({len(docs)} hubs, min {min(map(len, docs.values()))} words, worst-pair {worst:.2f} {pair})")
    return 1 if fail else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(ROOT), "dist")))
