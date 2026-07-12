#!/usr/bin/env python3
"""Find near-duplicate products and carefully archive lower-quality copies.

Strategy:
  - Deduplicate live product list by id
  - CLEAR duplicates only: same listing re-uploaded (identical/near-identical
    titles after synonym normalization with compatible hard specs, or same
    manufacturer part number). Intentional size/spec variants are report-only.
  - Archive (status=draft) the lower-quality copy; keep the better one
  - Max 40 archives; prefer draft over delete
"""

from __future__ import annotations

import json
import re
import time
from collections import Counter, defaultdict
from difflib import SequenceMatcher
from pathlib import Path

from shopify_client import api, get_token, get_store

ROOT = Path(__file__).resolve().parent
CATALOG = ROOT / "catalog"
LIVE_PATH = CATALOG / "_live_products.json"
REPORT_PATH = CATALOG / "duplicates_report.json"
RESULT_PATH = ROOT / "dedupe_result.json"
MAX_ARCHIVE = 40

# Hard discriminators — products differing on these are NOT duplicates.
# Allow optional hyphen/space between number and unit (1-loop, 2 zone, 1.5mm).
HARD_SPEC_RE = re.compile(
    r"""
    (
        \b\d+(?:\.\d+)?\s*-?\s*(?:loops?|zones?|ways?|ch|channels?|cores?|
            pairs?|doors?|cameras?|btns?|buttons?|amps?|ah)\b
        | \b\d+(?:\.\d+)?\s*-?\s*(?:mm|m2|m²)\b
        | \b\d+\s*-?\s*(?:hr|hrs|hours?|h)\b
        | \b\d+(?:\.\d+)?\s*-?\s*a\b          # 1.5A / 5A PSUs
        | \b\d+(?:\.\d+)?\s*-?\s*v\b          # 12V / 24V
        | \b\d+\s*-?\s*m\b                    # 100m drum
        | \bm\d{2}\b                          # M20 / M25
        | \b\d+c(?:e)?\b                      # 2C / 2CE
        | \bip\d{2}\b
        | \b(?:red|white|black|blue|green|yellow)\b
        | \b(?:maintained|non[- ]?maintained|nonmaintained)\b
        | \bnm\b
        | \b(?:addressable|conventional)\b
        | \b(?:optical|ionisation|ionization|heat|multisensor|
            multi[- ]?sensor|smoke|photoelectric)\b
        | \b(?:recessed|surface|flush|industrial)\b
        | \b(?:rate[- ]?of[- ]?rise|static)\b
        | \b(?:a1r|cs|br|bs)\b
        | \b(?:vad|beacon|strobe)\b
        | \b(?:diode|relay|isolator|deep|deckhead)\b
        | \b(?:open[- ]?area)\b
        | \b(?:2-wire|2wire|4-wire|4wire)\b
    )
    """,
    re.I | re.X,
)

# Alphanumeric model codes that distinguish products (ECO1002 vs ECO1003)
MODEL_CODE_RE = re.compile(
    r"\b("
    r"[A-Z]{1,6}[-]?\d{2,}[A-Z0-9-]*"  # ECO1003, CHQ-DIM, MXPRO5, NP7-12
    r"|[A-Z]{2,}\d{2,}[A-Z0-9-]*"
    r")\b",
    re.I,
)

UNIT_SYNONYMS = [
    (re.compile(r"\b(\d+)\s*[- ]?\s*hours?\b", re.I), r"\1h"),
    (re.compile(r"\b(\d+)\s*[- ]?\s*hrs?\b", re.I), r"\1h"),
    (re.compile(r"\bnon[- ]maintained\b", re.I), "nonmaintained"),
    (re.compile(r"\bmulti[- ]sensor\b", re.I), "multisensor"),
    (re.compile(r"\bcall[- ]point\b", re.I), "callpoint"),
    (re.compile(r"\bcontrol[- ]panel\b", re.I), "panel"),
    (re.compile(r"\s*[—–]\s*"), " "),
    (re.compile(r"[×]"), "x"),
    (re.compile(r"\s+"), " "),
]


def fetch_all_products(use_cache: bool = True, refresh: bool = False) -> list[dict]:
    if use_cache and not refresh and LIVE_PATH.is_file():
        raw = json.loads(LIVE_PATH.read_text(encoding="utf-8"))
        products = dedupe_by_id(raw)
        print(f"Loaded cache {LIVE_PATH} raw={len(raw)} unique={len(products)}")
        return products

    get_token()
    print("Store:", get_store())
    products: list[dict] = []
    url = (
        "/products.json?limit=250"
        "&fields=id,title,handle,vendor,product_type,status,tags,body_html,"
        "variants,images,options,created_at,updated_at"
    )
    page = 0
    while url:
        data = api("GET", url)
        batch = data.get("products", [])
        if not batch:
            break
        products.extend(batch)
        page += 1
        print(f"Page {page}: +{len(batch)} total={len(products)}")
        if len(batch) < 250:
            break
        last_id = batch[-1]["id"]
        url = (
            f"/products.json?limit=250&since_id={last_id}"
            "&fields=id,title,handle,vendor,product_type,status,tags,body_html,"
            "variants,images,options,created_at,updated_at"
        )
    products = dedupe_by_id(products)
    LIVE_PATH.write_text(json.dumps(products, indent=2), encoding="utf-8")
    print(f"Saved {LIVE_PATH} ({len(products)} unique products)")
    return products


def dedupe_by_id(products: list[dict]) -> list[dict]:
    seen: dict[int, dict] = {}
    for p in products:
        seen[p["id"]] = p
    return list(seen.values())


def fetch_product_collections(product_id: int) -> list[dict]:
    try:
        data = api("GET", f"/collects.json?product_id={product_id}&limit=250")
        collects = data.get("collects", []) or []
        return [{"id": c.get("collection_id")} for c in collects]
    except Exception as e:
        print(f"  warn collections for {product_id}: {e}")
        return []


def normalize_title(title: str) -> str:
    t = (title or "").lower().strip()
    # Drop superscripts / non-ascii so 1.5mm² → 1.5mm
    t = t.replace("²", "2").replace("³", "3")
    t = re.sub(r"[^\w\s\-+./]", " ", t, flags=re.ASCII)
    for pat, repl in UNIT_SYNONYMS:
        t = pat.sub(repl, t)
    return t.strip()


def extract_hard_specs(title: str) -> frozenset[str]:
    t = normalize_title(title)
    specs: set[str] = set()
    for m in HARD_SPEC_RE.finditer(t):
        tok = re.sub(r"[\s-]+", "", m.group(0).lower())
        # Normalize plural units: loops->loop, zones->zone, etc.
        tok = re.sub(
            r"(\d+(?:\.\d+)?)(loops|zones|ways|channels|cores|pairs|doors|"
            r"cameras|buttons|amps|hours|hrs)$",
            lambda m: m.group(1) + m.group(2).rstrip("s").replace("hr", "h"),
            tok,
        )
        tok = tok.replace("hours", "h").replace("hour", "h").replace("hrs", "h")
        specs.add(tok)
    return frozenset(specs)


def extract_model_codes(title: str) -> frozenset[str]:
    codes = set()
    for m in MODEL_CODE_RE.finditer(title or ""):
        codes.add(m.group(0).upper().replace(" ", ""))
    # Also from common patterns like "Series 65", "XP95", "MxPro 5"
    t = title or ""
    for m in re.finditer(r"\b(XP95|Series\s*65|MxPro\s*5|Syncro\s*AS|Sigma\s*CP|"
                         r"Axis\s*EN|Twinflex|Open-Area)\b", t, re.I):
        codes.add(re.sub(r"\s+", "", m.group(0).upper()))
    return frozenset(codes)


def _soft_specs() -> set[str]:
    return {
        "addressable", "conventional", "optical", "smoke", "heat",
        "surface", "flush", "recessed", "industrial", "openarea",
        "multisensor", "rateofrise", "static", "photoelectric",
    }


def hard_specs_equal(a: str, b: str) -> bool:
    """Strict: non-soft hard specs must be identical."""
    soft = _soft_specs()
    sa = {x for x in extract_hard_specs(a) if x not in soft}
    sb = {x for x in extract_hard_specs(b) if x not in soft}
    return sa == sb


def specs_compatible(a: str, b: str) -> bool:
    """True only if hard specs/model codes do not conflict."""
    sa, sb = extract_hard_specs(a), extract_hard_specs(b)
    only_a, only_b = sa - sb, sb - sa

    soft = _soft_specs()
    hard_a = {x for x in only_a if x not in soft}
    hard_b = {x for x in only_b if x not in soft}
    if hard_a or hard_b:
        return False

    # Model codes: differing exclusive non-family codes = different product
    ca, cb = extract_model_codes(a), extract_model_codes(b)
    if ca and cb:
        only_ca, only_cb = ca - cb, cb - ca
        family = {
            "XP95", "SERIES65", "MXPRO5", "SYNCROAS", "SIGMACP",
            "AXISEN", "TWINFLEX", "OPEN-AREA", "OPENAREA", "ESP",
        }
        only_ca = {c for c in only_ca if c not in family}
        only_cb = {c for c in only_cb if c not in family}
        if only_ca and only_cb:
            return False
    return True


def similarity(a: str, b: str) -> float:
    return SequenceMatcher(None, normalize_title(a), normalize_title(b)).ratio()


def body_text(p: dict) -> str:
    body = p.get("body_html") or ""
    return re.sub(r"<[^>]+>", "", body).strip()


def all_skus(p: dict) -> list[str]:
    return [v.get("sku") for v in (p.get("variants") or []) if v.get("sku")]


def normalize_mfr_part(sku: str) -> str:
    s = (sku or "").upper().strip()
    s = re.sub(r"[\s_]+", "-", s)
    s = re.sub(
        r"^(APO|HOC|ADV|KEN|KENT|CTE|EAT|SS|VIM|IC|MOR|HAC|GENT|FGD|NIT|HAI|FIK)-",
        "",
        s,
    )
    s = re.sub(
        r"-(ADDRESSA|ADDRESSABLE|POINT|UNIT|BASE|S65|MODULE|SMOKE|HEAT|PANEL|"
        r"A1R|CS|BR|VAD)$",
        "",
        s,
    )
    return s


def quality_score(p: dict, collection_count: int = 0) -> tuple:
    blen = len(body_text(p))
    images = len(p.get("images") or [])
    tags = p.get("tags") or ""
    tag_count = (
        len([t for t in tags.split(",") if t.strip()])
        if isinstance(tags, str)
        else len(tags)
    )
    variants = p.get("variants") or []
    has_sku = any((v.get("sku") or "").strip() for v in variants)
    has_price = any(float(v.get("price") or 0) > 0 for v in variants)
    status_bonus = 1 if p.get("status") == "active" else 0
    handle_len = len(p.get("handle") or "")
    return (
        blen,
        collection_count,
        images,
        tag_count,
        handle_len,
        1 if has_sku else 0,
        1 if has_price else 0,
        status_bonus,
        len(p.get("title") or ""),
        -int(p.get("id") or 0),
    )


def product_summary(p: dict, collections: list | None = None) -> dict:
    bt = body_text(p)
    return {
        "id": p["id"],
        "title": p.get("title"),
        "handle": p.get("handle"),
        "vendor": p.get("vendor"),
        "product_type": p.get("product_type"),
        "status": p.get("status"),
        "tags": p.get("tags"),
        "skus": all_skus(p),
        "prices": [v.get("price") for v in (p.get("variants") or [])],
        "image_count": len(p.get("images") or []),
        "body_len": len(bt),
        "body_preview": bt[:160],
        "collection_count": len(collections or []),
        "collections": collections or [],
        "created_at": p.get("created_at"),
        "updated_at": p.get("updated_at"),
    }


def find_pairs(products: list[dict]) -> list[dict]:
    """Return list of pair dicts with clear flag. No chaining yet."""
    pairs: dict[frozenset, dict] = {}

    def add_pair(a: dict, b: dict, reason: str, score: float, clear: bool):
        if a["id"] == b["id"]:
            return
        key = frozenset((a["id"], b["id"]))
        if key in pairs:
            existing = pairs[key]
            if reason not in existing["reasons"]:
                existing["reasons"].append(reason)
            existing["score"] = max(existing["score"], score)
            existing["clear"] = existing["clear"] or clear
        else:
            pairs[key] = {
                "ids": sorted([a["id"], b["id"]]),
                "a": a,
                "b": b,
                "reasons": [reason],
                "score": score,
                "clear": clear,
            }

    # 1) Exact normalized title
    by_title: dict[str, list[dict]] = defaultdict(list)
    for p in products:
        by_title[normalize_title(p.get("title") or "")].append(p)
    for nt, plist in by_title.items():
        if len(plist) < 2 or not nt:
            continue
        for i in range(len(plist)):
            for j in range(i + 1, len(plist)):
                add_pair(plist[i], plist[j], f"exact_norm_title:{nt[:90]}", 1.0, True)

    # 2) Same manufacturer part number
    by_part: dict[str, list[dict]] = defaultdict(list)
    for p in products:
        for sku in all_skus(p):
            part = normalize_mfr_part(sku)
            if part and (re.search(r"\d{4,}", part) or len(part) >= 6):
                by_part[part].append(p)
    for part, plist in by_part.items():
        uniq = {p["id"]: p for p in plist}
        if len(uniq) < 2:
            continue
        vals = list(uniq.values())
        for i in range(len(vals)):
            for j in range(i + 1, len(vals)):
                va = (vals[i].get("vendor") or "").lower()
                vb = (vals[j].get("vendor") or "").lower()
                if va and vb and va != vb:
                    continue
                ta, tb = vals[i].get("title") or "", vals[j].get("title") or ""
                if not specs_compatible(ta, tb) or not hard_specs_equal(ta, tb):
                    continue
                sim = similarity(ta, tb)
                if sim >= 0.70:
                    add_pair(vals[i], vals[j], f"same_mfr_part:{part}", max(0.95, sim), True)

    # 3) Near-identical titles same vendor
    by_vendor: dict[str, list[dict]] = defaultdict(list)
    for p in products:
        by_vendor[(p.get("vendor") or "").strip().lower() or "_none_"].append(p)

    for _vendor, plist in by_vendor.items():
        n = len(plist)
        for i in range(n):
            for j in range(i + 1, n):
                a, b = plist[i], plist[j]
                ta, tb = a.get("title") or "", b.get("title") or ""
                if normalize_title(ta) == normalize_title(tb):
                    continue
                sim = similarity(ta, tb)
                if sim < 0.90:
                    continue
                compat = specs_compatible(ta, tb)
                equal = hard_specs_equal(ta, tb)
                # title_sim is only CLEAR when hard specs are identical
                # (prevents 1-loop↔2-loop / 4-zone↔8-zone false merges)
                if equal and compat and sim >= 0.93:
                    add_pair(a, b, f"title_sim:{sim:.3f}", sim, True)
                elif equal and compat and sim >= 0.90:
                    add_pair(a, b, f"title_sim_medium:{sim:.3f}", sim, False)
                else:
                    add_pair(a, b, f"near_title_diff_spec:{sim:.3f}", sim, False)

    # 4) Handle near-collisions (foo vs foo-copy) with high title sim
    by_handle: dict[str, list[dict]] = defaultdict(list)
    for p in products:
        h = re.sub(r"-(copy|dup|duplicate)$", "", (p.get("handle") or "").lower())
        by_handle[h].append(p)
    for hbase, plist in by_handle.items():
        uniq = {p["id"]: p for p in plist}
        if len(uniq) < 2:
            continue
        vals = list(uniq.values())
        for i in range(len(vals)):
            for j in range(i + 1, len(vals)):
                ta, tb = vals[i].get("title") or "", vals[j].get("title") or ""
                sim = similarity(ta, tb)
                if sim >= 0.90 and specs_compatible(ta, tb):
                    add_pair(vals[i], vals[j], f"handle_base:{hbase}", max(0.94, sim), True)

    return list(pairs.values())


def build_groups(products: list[dict], pairs: list[dict], clear_only: bool) -> list[dict]:
    """Union-find groups. If clear_only, only connect clear pairs."""
    parent: dict[int, int] = {}

    def find(x: int) -> int:
        parent.setdefault(x, x)
        while parent[x] != x:
            parent[x] = parent[parent[x]]
            x = parent[x]
        return x

    def union(a: int, b: int):
        ra, rb = find(a), find(b)
        if ra != rb:
            parent[rb] = ra

    used_pairs = []
    for pair in pairs:
        if clear_only and not pair["clear"]:
            continue
        if not clear_only and pair["clear"]:
            continue  # for report-only groups we use non-clear pairs
        union(pair["ids"][0], pair["ids"][1])
        used_pairs.append(pair)

    if not parent:
        return []

    id_to_product = {p["id"]: p for p in products}
    components: dict[int, list[int]] = defaultdict(list)
    for pid in parent:
        components[find(pid)].append(pid)

    groups = []
    for _root, pids in components.items():
        if len(pids) < 2:
            continue
        members = [id_to_product[pid] for pid in sorted(pids) if pid in id_to_product]
        if len(members) < 2:
            continue

        reasons = []
        scores = []
        for pair in used_pairs:
            if set(pair["ids"]).issubset(set(pids)):
                reasons.extend(pair["reasons"])
                scores.append(pair["score"])

        # Safety: every member pair must be specs-compatible for clear groups
        titles = [m.get("title") or "" for m in members]
        all_compat = all(
            specs_compatible(titles[i], titles[j])
            for i in range(len(titles))
            for j in range(i + 1, len(titles))
        )
        all_high = all(
            similarity(titles[i], titles[j]) >= 0.88
            for i in range(len(titles))
            for j in range(i + 1, len(titles))
        )

        clear_duplicate = bool(clear_only and all_compat and all_high)
        # If a clear group somehow mixed incompatible items, demote
        if clear_only and not (all_compat and all_high):
            # Split is hard here — demote entire group to report-only
            clear_duplicate = False

        max_score = max(scores) if scores else 0
        if clear_duplicate and max_score >= 0.95:
            confidence = "high"
        elif clear_duplicate:
            confidence = "medium"
        else:
            confidence = "low"

        skus_all = []
        for m in members:
            skus_all.extend(all_skus(m))

        groups.append({
            "member_ids": sorted(pids),
            "titles": titles,
            "handles": [m.get("handle") for m in members],
            "vendors": sorted({(m.get("vendor") or "") for m in members}),
            "skus": skus_all,
            "reasons": sorted(set(reasons)),
            "max_score": round(max_score, 4),
            "confidence": confidence,
            "clear_duplicate": clear_duplicate,
            "likely_size_variants": not all_compat,
            "size_markers": sorted(set().union(*[extract_hard_specs(t) for t in titles])),
            "members": members,
        })

    return groups


def find_duplicate_groups(products: list[dict]) -> list[dict]:
    pairs = find_pairs(products)
    clear_groups = build_groups(products, pairs, clear_only=True)
    report_groups = build_groups(products, pairs, clear_only=False)

    # Avoid double-listing members already in a clear group
    clear_ids = set()
    for g in clear_groups:
        clear_ids.update(g["member_ids"])

    filtered_report = []
    for g in report_groups:
        # Drop groups fully covered by clear handling
        remaining = [pid for pid in g["member_ids"] if pid not in clear_ids]
        if len(remaining) < 2 and set(g["member_ids"]).issubset(clear_ids):
            # fully handled as clear dups — skip
            continue
        # keep as report-only near-miss family
        g = dict(g)
        g["clear_duplicate"] = False
        g["confidence"] = "low"
        filtered_report.append(g)

    groups = clear_groups + filtered_report
    groups.sort(
        key=lambda g: (not g["clear_duplicate"], -g["max_score"], g["member_ids"][0])
    )
    return groups


def decide_archive(
    members: list[dict], coll_map: dict[int, list]
) -> tuple[dict, list[dict], list[str]]:
    scored = []
    for m in members:
        cols = coll_map.get(m["id"], [])
        scored.append((quality_score(m, len(cols)), m, cols))
    scored.sort(key=lambda x: x[0], reverse=True)
    keep = scored[0][1]
    archive = [s[1] for s in scored[1:]]
    notes = [
        f"keep id={keep['id']} body_len={scored[0][0][0]} "
        f"collections={len(scored[0][2])} images={scored[0][0][2]} "
        f"status={keep.get('status')} title={keep.get('title')!r}"
    ]
    for sc, m, cols in scored[1:]:
        notes.append(
            f"archive id={m['id']} body_len={sc[0]} collections={len(cols)} "
            f"images={sc[2]} status={m.get('status')} title={m.get('title')!r}"
        )
    return keep, archive, notes


def set_status_draft(product_id: int) -> dict:
    return api(
        "PUT",
        f"/products/{product_id}.json",
        {"product": {"id": product_id, "status": "draft"}},
    )


def main():
    CATALOG.mkdir(parents=True, exist_ok=True)
    get_token()
    print("Store:", get_store())

    products = fetch_all_products(use_cache=True, refresh=False)
    print("Status:", dict(Counter(p.get("status") for p in products)))

    groups = find_duplicate_groups(products)
    clear = [g for g in groups if g["clear_duplicate"]]
    print(f"Found {len(groups)} groups | clear_duplicate={len(clear)}")

    print("\n=== PREVIEW CLEAR DUPS ===")
    for g in clear:
        print(f"\n[{g['confidence']}] score={g['max_score']}")
        for r in g["reasons"][:5]:
            print(f"  reason: {r}")
        for m in g["members"]:
            print(
                f"  id={m['id']} body={len(body_text(m))} "
                f"imgs={len(m.get('images') or [])} sku={all_skus(m)} | {m.get('title')}"
            )

    need_ids: set[int] = set()
    for g in clear:
        need_ids.update(g["member_ids"])

    print(f"\nFetching collections for {len(need_ids)} clear-dup products...")
    coll_map: dict[int, list] = {}
    for i, pid in enumerate(sorted(need_ids)):
        coll_map[pid] = fetch_product_collections(pid)
        if (i + 1) % 15 == 0 or (i + 1) == len(need_ids):
            print(f"  collections {i + 1}/{len(need_ids)}")

    report_groups = []
    archive_actions = []
    archived_count = 0
    skipped = []
    errors = []

    for g in groups:
        members = g["members"]
        member_summaries = [
            product_summary(m, coll_map.get(m["id"], [])) for m in members
        ]
        entry = {
            "member_ids": g["member_ids"],
            "titles": g["titles"],
            "handles": g["handles"],
            "vendors": g["vendors"],
            "skus": g["skus"],
            "reasons": g["reasons"],
            "max_score": g["max_score"],
            "confidence": g["confidence"],
            "clear_duplicate": g["clear_duplicate"],
            "likely_size_variants": g["likely_size_variants"],
            "size_markers": sorted(g["size_markers"]),
            "members": member_summaries,
            "action": "report_only",
            "keep_id": None,
            "archive_ids": [],
            "notes": [],
        }

        if g["clear_duplicate"]:
            keep, to_archive, notes = decide_archive(members, coll_map)
            entry["notes"] = notes
            entry["keep_id"] = keep["id"]

            for m in to_archive:
                if m.get("status") == "archived":
                    notes.append(f"skip already archived id={m['id']}")
                    continue
                if m.get("status") == "draft":
                    notes.append(f"already draft id={m['id']}")
                    entry["archive_ids"].append(m["id"])
                    if entry["action"] == "report_only":
                        entry["action"] = "already_draft"
                    continue
                if archived_count >= MAX_ARCHIVE:
                    skipped.append({
                        "id": m["id"],
                        "title": m.get("title"),
                        "reason": "max_archive_cap",
                    })
                    notes.append(f"SKIP cap reached id={m['id']}")
                    continue
                try:
                    print(f"ARCHIVE->draft id={m['id']} title={m.get('title')!r}")
                    set_status_draft(m["id"])
                    archived_count += 1
                    entry["archive_ids"].append(m["id"])
                    entry["action"] = "archived_lower_quality"
                    archive_actions.append({
                        "id": m["id"],
                        "title": m.get("title"),
                        "handle": m.get("handle"),
                        "vendor": m.get("vendor"),
                        "skus": all_skus(m),
                        "previous_status": m.get("status"),
                        "new_status": "draft",
                        "kept_id": keep["id"],
                        "kept_title": keep.get("title"),
                        "kept_handle": keep.get("handle"),
                        "reasons": g["reasons"],
                        "quality_note": (
                            f"archived body_len={len(body_text(m))} "
                            f"cols={len(coll_map.get(m['id'], []))} vs "
                            f"kept body_len={len(body_text(keep))} "
                            f"cols={len(coll_map.get(keep['id'], []))}"
                        ),
                    })
                except Exception as e:
                    err = {"id": m["id"], "error": str(e)}
                    errors.append(err)
                    notes.append(f"ERROR id={m['id']}: {e}")
                    print("  ERROR", err)
        else:
            entry["action"] = "report_only_not_clear"
            entry["notes"] = [
                "Not auto-archived: "
                + (
                    "likely intentional size/spec variants"
                    if g["likely_size_variants"]
                    else f"confidence={g['confidence']}"
                )
            ]

        report_groups.append(entry)

    report = {
        "generated_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "store": get_store(),
        "total_products_scanned": len(products),
        "duplicate_groups_found": len(report_groups),
        "clear_duplicate_groups": sum(1 for g in report_groups if g["clear_duplicate"]),
        "archived_count": archived_count,
        "max_archive": MAX_ARCHIVE,
        "policy": {
            "prefer": "draft_over_delete",
            "archive_criteria": (
                "clear duplicates only: exact/near-identical titles after "
                "synonym normalization with compatible hard specs, or same "
                "manufacturer part number; keep higher quality (longer "
                "description, more collections/images). Size/loop/zone/colour "
                "variants are listed but not archived."
            ),
            "max_archive": MAX_ARCHIVE,
        },
        "groups": report_groups,
    }
    REPORT_PATH.write_text(json.dumps(report, indent=2), encoding="utf-8")
    print(f"\nWrote {REPORT_PATH}")

    result = {
        "generated_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "store": get_store(),
        "total_products_scanned": len(products),
        "duplicate_groups_found": len(report_groups),
        "clear_duplicate_groups": sum(1 for g in report_groups if g["clear_duplicate"]),
        "archived_count": archived_count,
        "max_archive": MAX_ARCHIVE,
        "archived": archive_actions,
        "skipped": skipped,
        "errors": errors,
        "report_path": str(REPORT_PATH),
        "summary": {
            "high_confidence_groups": sum(
                1 for g in report_groups if g["confidence"] == "high"
            ),
            "medium_confidence_groups": sum(
                1 for g in report_groups if g["confidence"] == "medium"
            ),
            "low_confidence_groups": sum(
                1 for g in report_groups if g["confidence"] == "low"
            ),
            "actions": dict(Counter(g["action"] for g in report_groups)),
        },
    }
    RESULT_PATH.write_text(json.dumps(result, indent=2), encoding="utf-8")
    print(f"Wrote {RESULT_PATH}")
    print(f"DONE archived={archived_count} errors={len(errors)} skipped={len(skipped)}")

    print("\n=== CLEAR DUPLICATE GROUPS (RESULT) ===")
    for g in report_groups:
        if not g["clear_duplicate"]:
            continue
        print(f"\n[{g['confidence']}] action={g['action']} score={g['max_score']}")
        print(f"  keep={g['keep_id']} archive={g['archive_ids']}")
        for t in g["titles"]:
            print(f"  - {t}")


if __name__ == "__main__":
    main()
