#!/usr/bin/env python3
"""Hard SEO + AI-slop gate for rendered HTML.

Rubric: docs/SEO-AI-HARD-GUIDELINES.md
A page PASSes only when it has zero fail codes. Noindex pages that are outside
the coverage policy are excluded from duplicate comparisons.

Usage:
  python3 scripts/seo_ai_page_review.py --report docs/SEO-AI-REVIEW-REPORT.md
"""
from __future__ import annotations

import argparse
import json
import re
import subprocess
import sys
from collections import defaultdict
from html.parser import HTMLParser
from pathlib import Path
from urllib.parse import urlparse

ROOT = Path(__file__).resolve().parents[1]
TITLE_MIN, TITLE_MAX = 30, 65
META_MIN, META_MAX = 70, 160
MIN_WORDS = 160
BOILERPLATE_WORDS = 28
MIN_INTERNAL_LINKS = 3
PHONE_DIGITS = "07517806082"
PHONE_OK = {"07517806082", "447517806082"}

FILLER = (
    "in today's world",
    "look no further",
    "nestled",
    "elevate your",
    "cutting-edge",
    "state-of-the-art",
    "when it comes to",
    "whether you're",
    "whether you are",
    "peace of mind",
    "one-stop shop",
    "second to none",
    "tailored to your needs",
    "go above and beyond",
    "it's important to note",
    "delve into",
    "comprehensive solution",
)
HYPE = (
    "expert",
    "professional",
    "specialist",
    "qualified",
    "certified",
    "trusted",
    "leading",
    "premier",
    "dedicated",
    "comprehensive",
    "bespoke",
    "tailored",
    "renowned",
    "seasoned",
)
STOP = {
    "a", "an", "the", "and", "or", "of", "to", "in", "for", "on", "with", "from",
    "by", "at", "our", "we", "you", "your", "is", "are", "this", "that", "it",
    "as", "be", "not", "page", "than", "into", "over", "its", "their", "they",
}
LOCAL_FAMILIES = {"service-area", "keyword-area", "area-hub"}
OUTWARD = re.compile(r"\b([A-Z]{1,2}\d{1,2}[A-Z]?)\b")
DENIAL = re.compile(r"\b(do not|does not|don't|not claim|not print|no niceic)\b", re.I)
SELF = re.compile(r"\b(icomply|our|we|all engineers)\b", re.I)
NICEIC = re.compile(r"\bNICEIC\b", re.I)
GAS = re.compile(r"\bGas Safe registered\b", re.I)
BADGES = (
    re.compile(r"\bBAFE\b", re.I),
    re.compile(r"\bCHAS\b", re.I),
    re.compile(r"\bSafeContractor\b", re.I),
    re.compile(r"\baward-winning\b", re.I),
    re.compile(r"\bISO\s*9001\b", re.I),
)


def words(text: str) -> list[str]:
    return re.findall(r"[A-Za-z0-9']+", text)


def norm_space(text: str) -> str:
    return re.sub(r"\s+", " ", text).strip()


def fold_token(text: str, token: str, replacement: str) -> str:
    if not token:
        return text
    return re.sub(rf"\b{re.escape(token)}\b", replacement, text, flags=re.I)


class PageParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.title_parts: list[str] = []
        self.in_title = False
        self.metas: dict[str, str] = {}
        self.canonical = ""
        self.family = ""
        self.h1: list[str] = []
        self.in_h1 = False
        self.h1_parts: list[str] = []
        self.main_depth = 0
        self.skip_depth = 0
        self.skip_stack: list[bool] = []
        self.tag_stack: list[str] = []
        self.in_script = False
        self.in_style = False
        self.script_parts: list[str] = []
        self.jsonld: list[str] = []
        self.imgs: list[dict] = []
        self.stylesheets: list[str] = []
        self.preconnects: list[str] = []
        self.paragraphs: list[str] = []
        self.prose_parts: list[str] = []
        self.p_parts: list[str] = []
        self.in_p = False
        self.in_prose = False
        self.prose_buf: list[str] = []
        self.links: list[str] = []
        self.tels: list[str] = []
        self.details_depth = 0
        self.in_summary = False
        self.summary_parts: list[str] = []
        self.answer_parts: list[str] = []
        self.faqs: list[tuple[str, str]] = []
        self.visible_parts: list[str] = []
        self.lang = ""

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        ad = {k.lower(): (v or "") for k, v in attrs}
        tag = tag.lower()
        self.tag_stack.append(tag)
        shared = ad.get("data-seo-shared") == "1"
        self.skip_stack.append(shared or (self.skip_stack[-1] if self.skip_stack else False))
        if tag == "html":
            self.lang = ad.get("lang", "")
        if tag == "body" and not self.family:
            self.family = ad.get("data-seo-family", "")
        if tag in {"main"} or ad.get("id") in {"main-content", "main"}:
            self.main_depth += 1
        if tag == "title":
            self.in_title = True
        if tag == "meta":
            name = (ad.get("name") or ad.get("property") or "").lower()
            if name:
                self.metas[name] = ad.get("content", "")
        if tag == "link":
            rel = ad.get("rel", "").lower()
            href = ad.get("href", "")
            if "canonical" in rel.split():
                self.canonical = href
            if "stylesheet" in rel.split() and href:
                self.stylesheets.append(href)
            if "preconnect" in rel.split() and href:
                self.preconnects.append(href)
        if tag == "img":
            self.imgs.append({
                "alt": ad.get("alt"),
                "width": ad.get("width", ""),
                "height": ad.get("height", ""),
                "loading": ad.get("loading", ""),
                "in_main": self.main_depth > 0 and not self.skip_stack[-1],
                "src": ad.get("src", ""),
            })
        if tag == "a":
            href = ad.get("href", "")
            if href.startswith("tel:"):
                self.tels.append(href)
            elif self.main_depth > 0 and not self.skip_stack[-1]:
                self.links.append(href)
        if tag == "script":
            self.in_script = True
            self.script_parts = []
            self._script_ld = ad.get("type", "") == "application/ld+json"
        if tag == "style":
            self.in_style = True
        if tag == "h1":
            self.in_h1 = True
            self.h1_parts = []
        if tag == "details":
            self.details_depth += 1
            self.summary_parts = []
            self.answer_parts = []
        if tag == "summary" and self.details_depth:
            self.in_summary = True
        if tag == "p" and self._capture_prose():
            self.in_p = True
            self.p_parts = []
        if tag in {"li", "h2", "h3"} and self._capture_prose():
            self.in_prose = True
            self.prose_buf = []

    def handle_endtag(self, tag: str) -> None:
        tag = tag.lower()
        if tag == "title":
            self.in_title = False
        if tag == "script" and self.in_script:
            if getattr(self, "_script_ld", False):
                blob = "".join(self.script_parts).strip()
                if blob:
                    self.jsonld.append(blob)
            self.in_script = False
        if tag == "style":
            self.in_style = False
        if tag == "h1" and self.in_h1:
            text = norm_space("".join(self.h1_parts))
            if text:
                self.h1.append(text)
            self.in_h1 = False
        if tag == "summary":
            self.in_summary = False
        if tag == "p" and self.in_p:
            text = norm_space("".join(self.p_parts))
            if text:
                self.paragraphs.append(text)
                self.prose_parts.append(text)
            self.in_p = False
        if tag in {"li", "h2", "h3"} and self.in_prose:
            text = norm_space("".join(self.prose_buf))
            if text:
                self.prose_parts.append(text)
            self.in_prose = False
        if tag == "details" and self.details_depth:
            q = norm_space("".join(self.summary_parts))
            a = norm_space("".join(self.answer_parts))
            if q and a:
                self.faqs.append((q, a))
            self.details_depth -= 1
        if tag in {"main"} or (self.tag_stack and False):
            pass
        if tag in {"main", "div"} and self.main_depth:
            # div#main-content closes; we only decrement main when the matching
            # opener incremented. Track via a simple counter on end of main
            # and of the element that opened main. Handled below by id-less
            # decrement only for <main>. div#main-content is closed by counting
            # start tags stored separately.
            if tag == "main":
                self.main_depth = max(0, self.main_depth - 1)
        if self.tag_stack:
            self.tag_stack.pop()
        if self.skip_stack:
            self.skip_stack.pop()

    def handle_startendtag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        self.handle_starttag(tag, attrs)
        if self.tag_stack:
            self.tag_stack.pop()
        if self.skip_stack:
            self.skip_stack.pop()

    def handle_data(self, data: str) -> None:
        if self.in_title:
            self.title_parts.append(data)
        if self.in_script:
            self.script_parts.append(data)
            return
        if self.in_style:
            return
        if self.in_h1:
            self.h1_parts.append(data)
        if self.in_summary:
            self.summary_parts.append(data)
        if self.details_depth and not self.in_summary and not self.skipping():
            self.answer_parts.append(data)
        if self.in_p:
            self.p_parts.append(data)
        if self.in_prose:
            self.prose_buf.append(data)
        if not self.skipping():
            self.visible_parts.append(data)

    def skipping(self) -> bool:
        return bool(self.skip_stack and self.skip_stack[-1])

    def _capture_prose(self) -> bool:
        return self.main_depth > 0 and not self.skipping() and not self.in_script

    def close_main_divs(self, raw: str) -> None:
        """Count div#main-content / div#main open and close using a second pass marker.

        HTMLParser does not expose the id on end tags. Decrement main_depth when
        we see the closing of the wrapper by scanning nothing here; instead the
        start handler increments and we treat the whole body after the wrapper
        as main until </body> by leaving main_depth raised. The wrapper is closed
        before the footer in this site, so we must decrement. We do that by
        noting start line numbers is hard. Fallback: if id=main-content was seen,
        set a flag and decrement on the first </div> that brings a stored depth
        back. Implemented with an explicit stack of main flags.
        """
        return


class TrackingParser(HTMLParser):
    """Parser that tracks #main-content / <main> with an explicit stack."""

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.title = ""
        self._title = False
        self._title_buf: list[str] = []
        self.metas: dict[str, str] = {}
        self.canonical = ""
        self.family = ""
        self.lang = ""
        self.h1: list[str] = []
        self._h1 = False
        self._h1_buf: list[str] = []
        self.stack: list[dict] = []
        self.main_depth = 0
        self.shared_depth = 0
        self.faq_depth = 0
        self.in_script = False
        self.in_style = False
        self._ld = False
        self._script: list[str] = []
        self.jsonld: list[str] = []
        self.imgs: list[dict] = []
        self.stylesheets: list[str] = []
        self.preconnects: list[str] = []
        self.paragraphs: list[str] = []
        self.prose: list[str] = []
        self._p = False
        self._p_buf: list[str] = []
        self._prose = False
        self._prose_buf: list[str] = []
        self.links: list[str] = []
        self.tels: list[str] = []
        self.faqs: list[tuple[str, str]] = []
        self._details = 0
        self._summary = False
        self._sum: list[str] = []
        self._ans: list[str] = []
        self.visible: list[str] = []

    def _in_main(self) -> bool:
        return self.main_depth > 0 and self.shared_depth == 0

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        ad = {k.lower(): (v or "") for k, v in attrs}
        tag = tag.lower()
        opens_main = tag == "main" or ad.get("id") in {"main-content", "main"}
        opens_shared = ad.get("data-seo-shared") == "1"
        opens_faq = ad.get("data-seo-faq") == "1"
        void = tag in {
            "area", "base", "br", "col", "embed", "hr", "img", "input", "link",
            "meta", "param", "source", "track", "wbr",
        }
        if not void:
            self.stack.append({"tag": tag, "main": opens_main, "shared": opens_shared, "faq": opens_faq})
        if opens_main:
            self.main_depth += 1
        if opens_shared:
            self.shared_depth += 1
        if opens_faq:
            self.faq_depth += 1
        if tag == "html":
            self.lang = ad.get("lang", "")
        if tag == "body":
            self.family = ad.get("data-seo-family", "") or self.family
        if tag == "title":
            self._title = True
            self._title_buf = []
        if tag == "meta":
            name = (ad.get("name") or ad.get("property") or "").lower()
            if name:
                self.metas[name] = ad.get("content", "")
        if tag == "link":
            rel = set(ad.get("rel", "").lower().split())
            href = ad.get("href", "")
            if "canonical" in rel:
                self.canonical = href
            if "stylesheet" in rel and href:
                self.stylesheets.append(href)
            if "preconnect" in rel and href:
                self.preconnects.append(href)
        if tag == "img":
            self.imgs.append({
                "alt": ad.get("alt"),
                "width": ad.get("width", ""),
                "height": ad.get("height", ""),
                "loading": ad.get("loading", "").lower(),
                "in_main": self._in_main(),
            })
        if tag == "a":
            href = ad.get("href", "")
            if href.lower().startswith("tel:"):
                self.tels.append(href)
            elif self._in_main():
                self.links.append(href)
        if tag == "script":
            self.in_script = True
            self._ld = ad.get("type", "") == "application/ld+json"
            self._script = []
        if tag == "style":
            self.in_style = True
        if tag == "h1":
            self._h1 = True
            self._h1_buf = []
        if tag == "details":
            self._details += 1
            self._sum = []
            self._ans = []
        if tag == "summary" and self._details:
            self._summary = True
        if tag == "p" and self._in_main():
            self._p = True
            self._p_buf = []
        if tag in {"li", "h2", "h3"} and self._in_main():
            self._prose = True
            self._prose_buf = []

    def handle_endtag(self, tag: str) -> None:
        tag = tag.lower()
        if tag == "title" and self._title:
            self.title = norm_space("".join(self._title_buf))
            self._title = False
        if tag == "script" and self.in_script:
            if self._ld:
                blob = "".join(self._script).strip()
                if blob:
                    self.jsonld.append(blob)
            self.in_script = False
        if tag == "style":
            self.in_style = False
        if tag == "h1" and self._h1:
            text = norm_space("".join(self._h1_buf))
            if text:
                self.h1.append(text)
            self._h1 = False
        if tag == "summary":
            self._summary = False
        if tag == "p" and self._p:
            text = norm_space("".join(self._p_buf))
            if text:
                self.paragraphs.append(text)
                self.prose.append(text)
            self._p = False
        if tag in {"li", "h2", "h3"} and self._prose:
            text = norm_space("".join(self._prose_buf))
            if text:
                self.prose.append(text)
            self._prose = False
        if tag == "details" and self._details:
            q = norm_space("".join(self._sum))
            a = norm_space("".join(self._ans))
            if q and a and self.shared_depth == 0 and self.faq_depth > 0:
                self.faqs.append((q, a))
            self._details -= 1
        if self.stack:
            frame = self.stack.pop()
            if frame["main"]:
                self.main_depth = max(0, self.main_depth - 1)
            if frame["shared"]:
                self.shared_depth = max(0, self.shared_depth - 1)
            if frame.get("faq"):
                self.faq_depth = max(0, self.faq_depth - 1)

    def handle_startendtag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        self.handle_starttag(tag, attrs)
        void = tag.lower() in {
            "area", "base", "br", "col", "embed", "hr", "img", "input", "link",
            "meta", "param", "source", "track", "wbr",
        }
        if void and (self.main_depth or self.shared_depth):
            # starttag incremented main/shared for a void element that will not end
            ad = {k.lower(): (v or "") for k, v in attrs}
            if tag.lower() == "main" or ad.get("id") in {"main-content", "main"}:
                self.main_depth = max(0, self.main_depth - 1)
            if ad.get("data-seo-shared") == "1":
                self.shared_depth = max(0, self.shared_depth - 1)

    def handle_data(self, data: str) -> None:
        if self._title:
            self._title_buf.append(data)
        if self.in_script:
            self._script.append(data)
            return
        if self.in_style:
            return
        if self._h1:
            self._h1_buf.append(data)
        if self._summary:
            self._sum.append(data)
        elif self._details and self.shared_depth == 0:
            self._ans.append(data)
        if self._p:
            self._p_buf.append(data)
        if self._prose:
            self._prose_buf.append(data)
        if self.shared_depth == 0:
            self.visible.append(data)


def origin(url: str) -> str:
    parsed = urlparse(url)
    if not parsed.scheme or not parsed.netloc:
        return ""
    return f"{parsed.scheme}://{parsed.netloc}"


def phone_digits(href: str) -> str:
    raw = re.sub(r"\D", "", href)
    if raw.startswith("44"):
        raw = "0" + raw[2:]
    return raw


def walk_schema(node, problems: list[str]) -> None:
    if isinstance(node, list):
        for item in node:
            walk_schema(item, problems)
        return
    if not isinstance(node, dict):
        return
    typ = node.get("@type")
    types = typ if isinstance(typ, list) else ([typ] if typ else [])
    if "Offer" in types:
        availability = str(node.get("availability", ""))
        has_price = any(k in node and str(node.get(k)).strip() not in {"", "0"} for k in ("price", "lowPrice", "highPrice"))
        if availability.endswith("InStock") and not has_price:
            problems.append("schema_offer_price")
    if "FAQPage" in types or any(t == "FAQPage" for t in types):
        entities = node.get("mainEntity") or []
        if isinstance(entities, dict):
            entities = [entities]
        if not entities:
            problems.append("schema_faq_incomplete")
        for q in entities:
            if not isinstance(q, dict):
                problems.append("schema_faq_incomplete")
                continue
            ans = q.get("acceptedAnswer") or {}
            text = ans.get("text") if isinstance(ans, dict) else ""
            if not q.get("name") or not text:
                problems.append("schema_faq_incomplete")
    for key, value in node.items():
        if key.startswith("@"):
            continue
        if isinstance(value, (dict, list)):
            walk_schema(value, problems)


def schema_problems(blobs: list[str]) -> list[str]:
    problems: list[str] = []
    if not blobs:
        return ["schema_missing"]
    for blob in blobs:
        try:
            data = json.loads(blob)
        except json.JSONDecodeError:
            problems.append("schema_invalid_json")
            continue
        nodes = data if isinstance(data, list) else [data]
        for node in nodes:
            if isinstance(node, dict):
                if "https://schema.org" not in str(node.get("@context", "")):
                    problems.append("schema_missing_context")
                if "@type" not in node and "@graph" not in node:
                    problems.append("schema_missing_context")
            walk_schema(node, problems)
    # de-dupe preserving order
    seen = set()
    out = []
    for p in problems:
        if p not in seen:
            seen.add(p)
            out.append(p)
    return out


def internal_link(href: str) -> bool:
    if not href or href.startswith(("#", "mailto:", "tel:", "javascript:")):
        return False
    if href.startswith(("http://", "https://")):
        host = urlparse(href).netloc.lower()
        return "icomplypropertyservices.co.uk" in host or host.startswith("localhost")
    return href.startswith("/") or href.startswith("../") or not href.startswith("//")


def banned_codes(text: str) -> list[str]:
    codes = []
    sentences = re.split(r"(?<=[.!?])\s+", text)
    niceic = gas = badge = False
    for sentence in sentences:
        if DENIAL.search(sentence):
            continue
        if NICEIC.search(sentence):
            niceic = True
        if GAS.search(sentence) and SELF.search(sentence):
            gas = True
        if any(p.search(sentence) for p in BADGES):
            badge = True
    if niceic:
        codes.append("banned_claim_niceic")
    if gas:
        codes.append("banned_claim_gas_safe")
    if badge:
        codes.append("banned_claim_badge")
    return codes


def filler_and_hype(prose: str) -> list[str]:
    codes = []
    low = prose.lower()
    hits = [p for p in FILLER if p in low]
    if len(hits) >= 2:
        codes.append("ai_filler")
    tokens = set(re.findall(r"[a-z']+", low))
    hype_hits = [w for w in HYPE if w in tokens]
    if len(hype_hits) >= 5:
        codes.append("ai_synonym_churn")
    return codes


def stuffing(prose: str, ignore: set[str] | None = None) -> bool:
    """Repeated tokens that are not the page topic.

    The H1, town, service name and keyword name are the topic. Repeating a
    different word 14+ times and above 3.5% of prose is stuffing.
    """
    skip = set(STOP)
    if ignore:
        skip |= ignore
    toks = [t.lower() for t in words(prose)]
    if len(toks) < 40:
        return False
    counts: dict[str, int] = defaultdict(int)
    for t in toks:
        if len(t) >= 5 and t not in skip:
            counts[t] += 1
    for token, count in counts.items():
        if count >= 14 and count / len(toks) > 0.035:
            return True
    return False


def meta_keyword_list(meta: str) -> bool:
    return meta.count(",") >= 5


def review_pages(rows: list[dict]) -> list[dict]:
    parsed = []
    for row in rows:
        html = Path(row["file_path"]).read_text(encoding="utf-8", errors="replace")
        parser = TrackingParser()
        parser.feed(html)
        family = row.get("family") or parser.family or infer_family(row.get("path", ""))
        item = {
            **row,
            "family": family,
            "title": parser.title,
            "meta": norm_space(parser.metas.get("description", "")),
            "robots": parser.metas.get("robots", ""),
            "canonical": parser.canonical,
            "h1": parser.h1,
            "paragraphs": parser.paragraphs,
            "prose": " ".join(parser.prose),
            "faqs": parser.faqs,
            "imgs": parser.imgs,
            "stylesheets": parser.stylesheets,
            "preconnects": parser.preconnects,
            "links": parser.links,
            "tels": parser.tels,
            "jsonld": parser.jsonld,
            "visible": norm_space(" ".join(parser.visible)),
            "lang": parser.lang,
            "keywords_meta": parser.metas.get("keywords", ""),
            "viewport": "viewport" in parser.metas,
            "render_error": "RENDER_ERROR" in html or "Fatal error" in html,
        }
        parsed.append(item)

    indexable = [p for p in parsed if p.get("expect_index") and "noindex" not in p["robots"].lower()]
    # duplicate indexes
    def bucket(pages, keyfn):
        groups = defaultdict(list)
        for p in pages:
            key = keyfn(p)
            if key:
                groups[key].append(p)
        return {k: v for k, v in groups.items() if len(v) > 1}

    dup_titles = bucket(indexable, lambda p: p["title"].lower())
    dup_meta = bucket(indexable, lambda p: p["meta"].lower())
    dup_h1 = bucket(indexable, lambda p: " | ".join(p["h1"]).lower())
    dup_canon = bucket(parsed, lambda p: p["canonical"])

    def swap_meta(p):
        return fold_token(p["meta"].lower(), p.get("area") or "", "{area}")

    dup_swap = bucket(
        [p for p in indexable if p["family"] in LOCAL_FAMILIES and p.get("area")],
        lambda p: (p["family"], p.get("service") or "", p.get("keyword") or "", swap_meta(p)),
    )

    def para_key(p, paragraph):
        text = paragraph.lower()
        text = fold_token(text, p.get("area") or "", "{area}")
        text = fold_token(text, p.get("service_name") or "", "{service}")
        text = fold_token(text, p.get("keyword_name") or "", "{keyword}")
        return norm_space(text)

    para_groups = defaultdict(list)
    for p in indexable:
        for para in p["paragraphs"]:
            if len(words(para)) < BOILERPLATE_WORDS:
                continue
            para_groups[(p["family"], para_key(p, para))].append(p["file"])

    def faq_key(p, q, a):
        text = f"{q} || {a}".lower()
        text = fold_token(text, p.get("area") or "", "{area}")
        return norm_space(text)

    faq_groups = defaultdict(list)
    for p in indexable:
        if p["family"] not in {"service-area", "keyword-area"} or not p.get("area"):
            continue
        group = (p["family"], p.get("service") or "", p.get("keyword") or "")
        for q, a in p["faqs"]:
            faq_groups[(group, faq_key(p, q, a))].append((p["file"], p.get("area")))

    results = []
    for p in parsed:
        codes: list[str] = []
        expect = bool(p.get("expect_index"))
        noindex = "noindex" in p["robots"].lower()
        indexed = expect and not noindex
        if p["render_error"]:
            codes.append("render_error")
        if expect and noindex:
            codes.append("unexpected_noindex")
        if p["family"] in {"service-area", "keyword-area"} and not expect and not noindex:
            codes.append("coverage_out_of_policy")

        title = p["title"]
        if not title:
            codes.append("title_missing")
        elif title.lower() in {"icomply", "icomply property services"}:
            codes.append("title_brand_only")
        elif not (TITLE_MIN <= len(title) <= TITLE_MAX):
            codes.append("title_length")
        if indexed and any(p["file"] in {x["file"] for x in grp} and len(grp) > 1 for grp in dup_titles.values() if p["title"].lower() in dup_titles):
            pass
        if indexed and p["title"].lower() in dup_titles:
            codes.append("title_duplicate")

        meta = p["meta"]
        if not meta:
            codes.append("meta_missing")
        elif not (META_MIN <= len(meta) <= META_MAX):
            codes.append("meta_length")
        if indexed and meta.lower() in dup_meta:
            codes.append("meta_duplicate")
        if indexed and (p["family"], p.get("service") or "", p.get("keyword") or "", swap_meta(p)) in dup_swap:
            codes.append("meta_town_swap")
        if meta and meta_keyword_list(meta):
            codes.append("meta_keyword_list")
        kw_meta = p["keywords_meta"]
        if kw_meta:
            items = [x.strip() for x in kw_meta.split(",") if x.strip()]
            if len(items) > 6:
                codes.append("meta_keywords_stuffing")

        canon = p["canonical"]
        if not canon:
            codes.append("canonical_missing")
        elif not canon.startswith(("http://", "https://")):
            codes.append("canonical_not_absolute")
        elif p["canonical"] in dup_canon:
            codes.append("canonical_duplicate")
        path = p.get("path") or ""
        if canon and path and path not in {"/"} and path.rstrip("/") not in canon:
            codes.append("canonical_mismatch")
        if path == "/" and canon and urlparse(canon).path not in {"", "/"}:
            codes.append("canonical_mismatch")

        h1 = p["h1"]
        if len(h1) == 0:
            codes.append("h1_missing")
        elif len(h1) > 1:
            codes.append("h1_multiple")
        elif len(h1[0]) < 12:
            codes.append("h1_short")
        if indexed and h1 and " | ".join(h1).lower() in dup_h1:
            codes.append("h1_duplicate")
        if indexed and p["family"] in LOCAL_FAMILIES and p.get("area"):
            blob = " ".join(h1)
            if p["area"].lower() not in blob.lower():
                codes.append("h1_missing_place")

        if indexed:
            wc = len(words(p["prose"]))
            if wc < MIN_WORDS:
                codes.append("thin_content")
            for para in p["paragraphs"]:
                if len(words(para)) < BOILERPLATE_WORDS:
                    continue
                key = (p["family"], para_key(p, para))
                files = set(para_groups.get(key, []))
                if len(files) > 1:
                    codes.append("boilerplate_paragraph")
                    break
            topic = set()
            for field in (p.get("keyword_name"), p.get("service_name"), p.get("area"), " ".join(p["h1"])):
                for token in words(field or ""):
                    if len(token) >= 5:
                        topic.add(token.lower())
            if stuffing(p["prose"], topic):
                codes.append("keyword_stuffing")
            if p["family"] in LOCAL_FAMILIES:
                codes_found = {c for c in OUTWARD.findall(p["prose"]) if c.upper() != "SK2"}
                if not codes_found:
                    codes.append("local_specific_missing")
            if indexed and p["family"] in {"service-area", "keyword-area"} and not p["faqs"]:
                codes.append("faq_block_missing")
            if p["family"] in {"service-area", "keyword-area"} and p.get("area"):
                group = (p["family"], p.get("service") or "", p.get("keyword") or "")
                for q, a in p["faqs"]:
                    members = faq_groups.get((group, faq_key(p, q, a)), [])
                    areas = {area for _file, area in members}
                    if len(areas) > 1:
                        codes.append("identical_faq_across_towns")
                        break
            internals = [href for href in p["links"] if internal_link(href)]
            if len(internals) < MIN_INTERNAL_LINKS:
                codes.append("internal_links_thin")
            codes.extend(filler_and_hype(p["prose"]))

        if indexed or p["jsonld"]:
            for code in schema_problems(p["jsonld"]):
                if code == "schema_missing" and not indexed:
                    continue
                codes.append(code)

        if not p["viewport"]:
            codes.append("cwv_viewport")
        if not p["lang"].lower().startswith("en"):
            codes.append("cwv_lang")
        main_imgs = [img for img in p["imgs"] if img["in_main"]]
        for img in main_imgs:
            alt = img["alt"]
            if alt is None or not str(alt).strip():
                codes.append("cwv_img_alt")
                break
        for img in main_imgs:
            if not img["width"] or not img["height"]:
                codes.append("cwv_img_dimensions")
                break
        if main_imgs and main_imgs[0]["loading"] == "lazy":
            codes.append("cwv_lcp_lazy")
        pre = {origin(href) for href in p["preconnects"]}
        page_origin = origin(p["canonical"]) if p["canonical"] else ""
        for href in p["stylesheets"]:
            sheet_origin = origin(href)
            if sheet_origin and sheet_origin != page_origin and sheet_origin not in pre:
                codes.append("cwv_preconnect")
                break

        codes.extend(banned_codes(p["visible"] + " " + p["title"] + " " + p["meta"]))
        if p["tels"]:
            if any(phone_digits(tel) not in PHONE_OK and phone_digits(tel) != PHONE_DIGITS for tel in p["tels"]):
                codes.append("phone_mismatch")
        elif indexed and p["family"] != "shop":
            codes.append("phone_missing")

        # stable unique order
        seen = set()
        ordered = []
        for code in codes:
            if code not in seen:
                seen.add(code)
                ordered.append(code)
        results.append({**p, "fails": ordered, "pass": not ordered})
    return results


def infer_family(path: str) -> str:
    if path in {"", "/"}:
        return "home"
    if path.startswith("/shop"):
        return "shop"
    if path.startswith("/pages/services/"):
        return "service-hub"
    if path.startswith("/pages/keywords/") and path.count("/") >= 4:
        return "keyword-area"
    if path.startswith("/pages/keywords/"):
        return "keyword-hub"
    if path.startswith("/pages/areas/"):
        return "area-hub"
    if path.startswith("/pages/manufacturers/"):
        return "manufacturer"
    if path.startswith("/pages/resources/"):
        return "resource"
    parts = [p for p in path.split("/") if p]
    if len(parts) == 3 and parts[0] == "pages":
        return "service-area"
    return "static"


def shop_rows(shop_dir: Path) -> list[dict]:
    rows = []
    for path in sorted(shop_dir.rglob("*.html")):
        rel = "/" + str(path.relative_to(shop_dir.parent)).replace("\\", "/")
        # website/shop/index.html -> /shop/ or /shop/fire/
        posix = path.relative_to(ROOT / "website").as_posix()
        url_path = "/" + posix
        if url_path.endswith("/index.html"):
            url_path = url_path[: -len("index.html")]
        elif url_path.endswith(".html"):
            url_path = url_path[: -len(".html")]
        rows.append({
            "file": path.name,
            "file_path": str(path),
            "family": "shop",
            "path": url_path if url_path.endswith("/") else url_path + ("" if url_path.endswith("/") else ""),
            "service": "",
            "service_name": "",
            "area": "",
            "keyword": "",
            "keyword_name": "",
            "expect_index": True,
        })
    # normalise directory indexes to their canonical-style path
    for row in rows:
        if row["path"].endswith("/index"):
            row["path"] = row["path"][: -len("index")]
    return rows


def render_sample(out_dir: Path) -> None:
    out_dir.mkdir(parents=True, exist_ok=True)
    cmd = ["php", str(ROOT / "website" / "bin" / "render-seo-sample.php"), str(out_dir)]
    env = dict(**{k: v for k, v in __import__("os").environ.items()})
    env["ICOMPLY_STATIC_EXPORT"] = "1"
    subprocess.run(cmd, check=False, cwd=str(ROOT), env=env)


def load_manifest(out_dir: Path) -> list[dict]:
    manifest = json.loads((out_dir / "manifest.json").read_text())
    rows = []
    for row in manifest:
        row = dict(row)
        row["file_path"] = str(out_dir / row["file"])
        rows.append(row)
    return rows


def write_report(results: list[dict], dest: Path, sample_note: str) -> None:
    by_family = defaultdict(lambda: {"pages": 0, "pass": 0, "fail": 0, "reasons": defaultdict(int)})
    by_reason = defaultdict(int)
    for row in results:
        fam = by_family[row["family"]]
        fam["pages"] += 1
        if row["pass"]:
            fam["pass"] += 1
        else:
            fam["fail"] += 1
            for code in row["fails"]:
                fam["reasons"][code] += 1
                by_reason[code] += 1
    passed = sum(1 for r in results if r["pass"])
    failed = len(results) - passed
    lines = [
        "# SEO + AI review report",
        "",
        "Hard gate from `docs/SEO-AI-HARD-GUIDELINES.md`. A page passes only with zero fail codes.",
        "Noindex pages outside the coverage policy are not compared for duplicate titles, metas, FAQs or body paragraphs.",
        "",
        sample_note,
        "",
        "## Totals",
        "",
        f"- Pages scanned: {len(results)}",
        f"- Pass: {passed}",
        f"- Fail: {failed}",
        "",
        "## Fail counts by template family",
        "",
        "| Family | Pages | Pass | Fail | Top fail codes |",
        "| --- | ---: | ---: | ---: | --- |",
    ]
    for family in sorted(by_family):
        fam = by_family[family]
        top = ", ".join(f"{code} ({n})" for code, n in sorted(fam["reasons"].items(), key=lambda kv: -kv[1])[:6]) or "—"
        lines.append(f"| {family} | {fam['pages']} | {fam['pass']} | {fam['fail']} | {top} |")
    lines += [
        "",
        "## Fail counts by reason",
        "",
        "| Code | Pages |",
        "| --- | ---: |",
    ]
    if not by_reason:
        lines.append("| — | 0 |")
    else:
        for code, n in sorted(by_reason.items(), key=lambda kv: (-kv[1], kv[0])):
            lines.append(f"| {code} | {n} |")
    lines += ["", "## Failing pages", ""]
    failures = [r for r in results if not r["pass"]]
    if not failures:
        lines.append("None in this sample.")
    else:
        for row in failures:
            lines.append(f"- `{row['family']}` `{row.get('path') or row['file']}`: {', '.join(row['fails'])}")
    lines += [
        "",
        "## Coverage rule used by the renderer",
        "",
        "- Nationwide (indexable in every town in `website/data/areas.json`): fire-safety category, nurse call, access control (barriers).",
        "- Other services: indexable for Greater Manchester towns and Burnley. Other towns render `noindex, follow`.",
        "- Phone checked against 07517806082.",
        "- NICEIC / Gas Safe self-claims fail. Sentences that only state the legal duty, or that explicitly deny a badge, do not fail.",
        "",
    ]
    dest.write_text("\n".join(lines) + "\n", encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser(description="Hard SEO + AI page review")
    parser.add_argument("--out", default="/tmp/seo-ai-sample", help="Rendered HTML directory")
    parser.add_argument("--report", default=str(ROOT / "docs" / "SEO-AI-REVIEW-REPORT.md"))
    parser.add_argument("--skip-render", action="store_true")
    parser.add_argument("--shop", default=str(ROOT / "website" / "shop"))
    args = parser.parse_args()
    out_dir = Path(args.out)
    if not args.skip_render:
        render_sample(out_dir)
    if not (out_dir / "manifest.json").is_file():
        print("manifest missing", file=sys.stderr)
        return 1
    rows = load_manifest(out_dir)
    rows.extend(shop_rows(Path(args.shop)))
    results = review_pages(rows)
    note = (
        "Sample: every service hub, a stratified set of service×town and keyword×town pages "
        "(fire, AOV, nurse call and barriers across Manchester, Stockport, Burnley, Liverpool, Carlisle; "
        "other services in Manchester, Stockport and Burnley; two out-of-policy noindex checks), "
        "four area hubs, six keyword hubs, two manufacturer pages, home, about, contact, one resource, "
        "and the static shop HTML already in the tree. The full keyword×town matrix is not pre-rendered."
    )
    write_report(results, Path(args.report), note)
    failed = [r for r in results if not r["pass"]]
    print(f"pages={len(results)} pass={len(results) - len(failed)} fail={len(failed)}")
    by_family = defaultdict(lambda: [0, 0])
    for r in results:
        by_family[r["family"]][0] += 1
        by_family[r["family"]][1] += 0 if r["pass"] else 1
    for family, (n, f) in sorted(by_family.items()):
        print(f"  {family}: {n} pages, {f} fail")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
