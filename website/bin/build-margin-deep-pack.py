#!/usr/bin/env python3
"""Build data/margin-deep/{pack}.json for one margin DEEP pack.

Locks: icomply-ops/seo/*-DEEP-LOCK-2026-10-05.md (index PROPERTY-SEO-LOCK-INDEX-2026-10-05.md).
Copy modules live in bin/margin_deep/{pack}/ and export PACK (see fencing for the shape).

Each P0 slug becomes a keyword hub at /pages/keywords/{slug}. Town pages are hub × dual-ring 269
(manufacturer × job heads: Greater Manchester core 60), rendered by includes/margin-deep.php and
netlify/lib/margin-deep.js. The build is deterministic and refuses £ prices, attendance-time
promises, doubled words, scaffold text and each pack's wrong-service vocabulary.

Usage: python3 bin/build-margin-deep-pack.py --pack=fencing [--seo-dir=/workspace/icomply-ops/seo]
"""
from __future__ import annotations

import difflib
import hashlib
import importlib
import json
import os
import random
import re
import sys
from itertools import combinations

HERE = os.path.dirname(os.path.abspath(__file__))
sys.dont_write_bytecode = True
sys.path.insert(0, HERE)
ROOT = os.path.dirname(HERE)
NAP = "17 Woodlands Park Road, Offerton, Stockport, Cheshire SK2 5DE"

TIMING = re.compile(r"(within \d+ ?(minutes|mins|hours|hrs|days)|\d+[- ]hour (response|call-?out)|same[- ]day|same[- ]week|next[- ]day|24/7|24-hour|round[- ]the[- ]clock|fast response|rapid response|guaranteed (attendance|response|arrival)|we will be there|on site in \d|response time of)", re.I)
PRICE = re.compile(r"£\s*\d|\bfixed[- ]price\b|\bfrom only\b|\bcheapest\b", re.I)
SCAFFOLD = re.compile(r"\{[A-Za-z_]+\}|\bTODO\b|\bTBD\b|\blorem\b|\bslugs?\b|\bplaceholder\b|\bjson\b|\b(P0|P1|DEEP|SEO|WT)\b")
DOUBLE = re.compile(r"\b(\w+)\s+\1\b", re.I)
GAS = re.compile(r"gas safe", re.I)
CERT_CLAIM = re.compile(r"\b(we are|iComply is|iComply are|our company is)\s+(an? )?(accredited|certified|registered|approved)\b", re.I)


def h(*parts: str) -> int:
    return int(hashlib.sha1("|".join(parts).encode()).hexdigest()[:8], 16)


def words(text: str) -> int:
    return len(re.findall(r"[A-Za-z0-9'’-]+", text))


def cap(text: str) -> str:
    text = text.strip()
    if text.startswith("iComply"):
        return text
    return text[:1].upper() + text[1:] if text else text


_COMBOS: dict[tuple[int, int], list[tuple[int, ...]]] = {}


def spread_combos(n: int, k: int) -> list[tuple[int, ...]]:
    key = (n, k)
    if key in _COMBOS:
        return _COMBOS[key]
    pool = list(combinations(range(n), k))
    if len(pool) > 400:
        pool = pool[:: max(1, len(pool) // 400)]
    out = [pool.pop(0)]
    while pool:
        best = min(pool, key=lambda c: (max(len(set(c) & set(o)) for o in out[-8:]), pool.index(c)))
        pool.remove(best)
        out.append(best)
    _COMBOS[key] = out
    return out


USERS: dict[str, list[str]] = {}


def user_index(module: str, slug: str) -> int:
    users = USERS.setdefault(module, [])
    if slug not in users:
        users.append(slug)
    return users.index(slug)


def take(paras: list[str], module: str, slug: str, target: int, k: int = 3) -> list[str]:
    """k paragraphs chosen so pages sharing a module get maximally different subsets, topped up to target words."""
    if not paras:
        return []
    k = min(k, len(paras))
    combos = spread_combos(len(paras), k)
    combo = combos[user_index(module, slug) % len(combos)]
    out = [paras[i] for i in combo]
    for i in random.Random(h(slug, module)).sample(range(len(paras)), len(paras)):
        if sum(words(x) for x in out) >= target:
            break
        if paras[i] not in out:
            out.append(paras[i])
    rnd = random.Random(h(slug, module, "order"))
    rnd.shuffle(out)
    return out


def fill(text: str, ctx: dict) -> str:
    for key, val in ctx.items():
        text = text.replace("{" + key + "}", val)
    return cap(text)


def fit_meta(text: str, pads: list[str]) -> str:
    text = re.sub(r"\s+", " ", text).strip()
    if len(text.encode()) > 160:
        ws = text.split(" ")
        while len(ws) > 1 and len(" ".join(ws).encode()) > 152:
            ws.pop()
        text = re.sub(r"[ .,;:—-]+$", "", " ".join(ws)) + ". POA."
    for pad in pads + [" Quote POA.", " Survey first.", " Written scope.", " Stockport base.", " POA."]:
        if len(text.encode()) >= 140:
            break
        if len((text + pad).encode()) <= 160 and pad.strip().lower() not in text.lower():
            text += pad
    return text


def title_for(name: str) -> str:
    full = f"{name} | iComply Property Services"
    if len(full) <= 60:
        return full
    return f"{name} | iComply" if len(f"{name} | iComply") <= 70 else name


def word_seq(text: str) -> list[str]:
    return re.findall(r"[a-z0-9'’-]+", text.lower())


def main() -> None:
    pack_id = ""
    seo_dir = "/workspace/icomply-ops/seo"
    for arg in sys.argv[1:]:
        if arg.startswith("--pack="):
            pack_id = arg.split("=", 1)[1]
        elif arg.startswith("--seo-dir="):
            seo_dir = arg.split("=", 1)[1]
    if not pack_id:
        raise SystemExit("--pack is required")
    P = importlib.import_module(f"margin_deep.{pack_id.replace('-', '_')}").PACK
    p0_path = os.path.join(seo_dir, P["p0_file"])
    p0 = [l.strip() for l in open(p0_path, encoding="utf-8") if l.strip()]
    if len(p0) != P["p0_count"] or len(set(p0)) != P["p0_count"]:
        raise SystemExit(f"P0 must be {P['p0_count']} unique slugs, got {len(p0)}")
    banned = re.compile(P["banned"], re.I) if P.get("banned") else None

    infos = {s: P["classify"](s) for s in p0}
    by_core: dict[str, list[str]] = {}
    for s, i in infos.items():
        by_core.setdefault(i["core"], []).append(s)

    keywords: dict[str, dict] = {}
    hubs: list[dict] = []
    for slug in p0:
        info = infos[slug]
        core = P["cores"][info["core"]]
        intent_id = info["intent"]
        intent = P["intents"][intent_id]
        ctx_id = info.get("context") or core.get("context") or P["default_context"]
        context = P["contexts"][ctx_id]
        name = info.get("name") or P["names"].get(slug) or P["display_name"](slug)
        thing = info.get("thing") or core["thing"]
        slots = {"thing": thing, "Thing": cap(thing), "name": name}
        sibs = [x for x in by_core[info["core"]] if x != slug] or [x for x in p0 if x != slug]
        related = info.get("related") or sibs[h(slug, "rel") % len(sibs)]

        body: list[str] = []
        lens = P["lenses"].get(slug)
        if not lens:
            raise SystemExit(f"missing lens for {slug}")
        body.append(fill(lens, slots))
        body += [fill(p, slots) for p in take(core["paras"], "core:" + info["core"], slug, P.get("core_words", 260), 3)]
        body += [fill(p, slots) for p in take(intent["paras"], "intent:" + intent_id, slug, P.get("intent_words", 210), 3)]
        angles = [a for a in P["angles"] if a != intent_id and a in P["intents"]]
        angle = angles[h(slug, "angle") % len(angles)]
        body += [fill(p, slots) for p in take(P["intents"][angle]["paras"], "angle:" + angle, slug, 120, 2)]
        for other in random.Random(h(slug, "cross")).sample(core.get("cross", []), min(2, len(core.get("cross", [])))):
            paras = P["cores"][other]["paras"]
            body.append(fill(paras[h(slug, "cross", other) % len(paras)], {**slots, "thing": P["cores"][other]["thing"], "Thing": cap(P["cores"][other]["thing"])}))
        body += [fill(p, slots) for p in take(context["paras"], "context:" + ctx_id, slug, P.get("context_words", 170), 2)]
        body += [fill(p, slots) for p in take(P["shared"], "shared", slug, P.get("shared_words", 150), 2)]
        seen: set[str] = set()
        body = [p for p in body if not (p in seen or seen.add(p))]

        faqs = [(fill(q, slots), fill(a, slots)) for q, a in core["faqs"]]
        faqs += [(fill(q, slots), fill(a, slots)) for q, a in intent.get("faqs", [])]
        faqs += [(fill(q, slots), fill(a, slots)) for q, a in context.get("faqs", [])]
        k = h(slug, "faq") % len(faqs)
        faqs = (faqs[k:] + faqs[:k])[:5]
        spoken = P["spoken"](name) if P.get("spoken") else name
        faqs.append((f"How do I ask for a quote for {spoken}?",
                     f"Phone or WhatsApp 07517 806082, or email info@icomplypropertyservices.co.uk, with the site address, photos and what you need. "
                     f"The reply is price on application once the scope is written. Workshop: {NAP}."))

        hook = core["hook"]
        aud = context["audience"]
        lead = [
            f"{spoken} from iComply Property Services covers {hook}. This guide is written for {aud} and starts from a site survey and a written scope, not a catalogue price.",
            f"{spoken}: {hook}. iComply works for {aud} from its Stockport workshop, measures before it specifies, and quotes every job price on application.",
            f"This guide to {spoken} explains {hook}, what decides the right approach for {aud}, and how iComply scopes the work before anything is ordered.",
        ][h(slug, "intro") % 3]
        intro = f"{lead} {fill(intent['tail'], slots)}"
        meta = ""
        for cand in (f"{spoken} for {aud}: {hook}. Quote POA.",
                     f"{spoken}: {hook}. Survey and written scope from Stockport. Quote POA.",
                     f"{spoken}: {hook}. Written scope, quote POA.",
                     f"{spoken}: {hook}."):
            if len(cand.encode()) <= 160:
                m = fit_meta(cand, P.get("meta_pads", []))
                if 140 <= len(m.encode()) <= 160:
                    meta = m
                    break
        if not meta:
            meta = fit_meta(f"{spoken}: {hook}. Request a quote — POA.", P.get("meta_pads", []))
        page = {
            "name": name,
            "h1": name,
            "service": P["service"],
            "related": related,
            "seo_title": title_for(name),
            "meta_desc": meta,
            "intro": intro,
            "body": "\n\n".join(body),
            "focus_points": list(core["focus"]),
            "faq": [[q, a] for q, a in faqs],
            "seo_keywords": ", ".join(dict.fromkeys([name.lower(), thing.lower(), core["label"].lower(), "price on application"])),
        }
        blob = " ".join([intro, page["body"], meta, " ".join(page["focus_points"]), " ".join(q + " " + a for q, a in faqs)])
        for rx, label in ((TIMING, "timing promise"), (PRICE, "price"), (SCAFFOLD, "scaffold text"), (DOUBLE, "doubled word"), (CERT_CLAIM, "registration claim")):
            m = rx.search(blob)
            if m:
                raise SystemExit(f"{label} in {slug}: {m.group(0)!r}")
        if banned and banned.search(blob):
            raise SystemExit(f"wrong-service wording in {slug}: {banned.search(blob).group(0)!r}")
        g = GAS.search(blob)
        if g and "carried out by Gas Safe registered engineers" not in blob:
            raise SystemExit(f"gas wording in {slug}")
        if words(page["body"]) < 800:
            raise SystemExit(f"{slug} body only {words(page['body'])} words")
        keywords[slug] = page
        images = P["images_for"](slug, info)
        for img in images:
            if not os.path.isfile(ROOT + img):
                raise SystemExit(f"missing image {img}")
        if len(images) != 3 or len(set(images)) != 3:
            raise SystemExit(f"{slug} needs 3 distinct images")
        hubs.append({"slug": slug, "name": name, "core": info["core"], "images": images, "geo": info.get("geo", "dual269")})

    doc = {
        "pack": P["pack"],
        "label": P["label"],
        "lock": P["lock"],
        "p0_source": P["p0_file"],
        "p0_count": P["p0_count"],
        "geo": "dual-ring-269 (manufacturer × job heads: Greater Manchester core 60)",
        "service": P["service"],
        "trade_label": P["trade_label"],
        "parents": P["parents"],
        "town_who": P["town_who"],
        "town_angles": P["town_angles"],
        "redirects": P.get("redirects", {}),
        "banned": P.get("banned", ""),
        "hubs": hubs,
        "keywords": keywords,
    }
    out_dir = os.path.join(ROOT, "data", "margin-deep")
    os.makedirs(out_dir, exist_ok=True)
    out = os.path.join(out_dir, f"{P['pack']}.json")
    with open(out, "w", encoding="utf-8") as fh:
        json.dump(doc, fh, ensure_ascii=False, indent=1)
        fh.write("\n")
    wc = [words(k["body"]) for k in keywords.values()]
    print(f"wrote {os.path.relpath(out, ROOT)}: hubs={len(hubs)} body words min={min(wc)} max={max(wc)} avg={sum(wc)//len(wc)}")
    if "--sim" in sys.argv:
        seqs = {s: word_seq(k["intro"] + " " + k["body"] + " " + " ".join(q + " " + a for q, a in k["faq"])) for s, k in keywords.items()}
        worst = (0.0, "")
        for a, b in combinations(p0, 2):
            sm = difflib.SequenceMatcher(None, seqs[a], seqs[b])
            if sm.real_quick_ratio() <= worst[0] or sm.quick_ratio() <= worst[0]:
                continue
            r = sm.ratio()
            if r > worst[0]:
                worst = (r, f"{a} ~ {b}")
        print(f"worst-pair difflib similarity {worst[0]:.3f} ({worst[1]})")


if __name__ == "__main__":
    main()
