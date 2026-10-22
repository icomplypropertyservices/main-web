/**
 * Spoken labels for catalogue slugs. Kept out of trades-lock.js so
 * link-blocks.js can use it without a circular import.
 */
import copyDoc from "../../website/data/seo-lock/family-copy.json" with { type: "json" };

const TOPICS = copyDoc.topics || {};
const COPY = copyDoc.families || {};

const ACRONYM = {
  aov: "AOV", ahu: "AHU", grp: "GRP", epdm: "EPDM", eicr: "EICR", ev: "EV", pir: "PIR",
  upvc: "uPVC", lvt: "LVT", cctv: "CCTV", cp12: "CP12", cp44: "CP44", pat: "PAT", hmo: "HMO",
  cdm: "CDM", co: "CO", tv: "TV", ip: "IP", led: "LED", rcd: "RCD", mcb: "MCB",
  hd: "HD", bs: "BS", htm: "HTM", lpg: "LPG", ptz: "PTZ", fra: "FRA", epc: "EPC",
  fd30: "FD30", fd60: "FD60", anpr: "ANPR", nvr: "NVR", dvr: "DVR", ppm: "PPM", gsm: "GSM",
};


function sentenceToken(part, index) {
  const lower = String(part).toLowerCase();
  if (ACRONYM[lower]) return ACRONYM[lower];
  if (index === 0) return lower.charAt(0).toUpperCase() + lower.slice(1);
  return lower;
}

export function naturalizeSlug(slug) {
  const parts = String(slug).split("-").filter(Boolean);
  if (!parts.length) return "";
  // Only verbs with no sibling slug are rewritten ("consumer-unit-swap" -> replacement,
  // "retaining-wall-build" -> construction). install/installation stay as written so the
  // two keyword pages keep different titles.
  const VERB = { swap: "replacement", build: "construction" };
  const last = parts[parts.length - 1].toLowerCase();
  if (parts.length > 1 && VERB[last]) parts[parts.length - 1] = VERB[last];
  const out = [];
  for (let i = 0; i < parts.length; i++) {
    if (parts[i].toLowerCase() === "d" && String(parts[i + 1] || "").toLowerCase() === "h") {
      out.push("D+H");
      i += 1;
      continue;
    }
    if (parts[i].toLowerCase() === "k" && String(parts[i + 1] || "").toLowerCase() === "rend") {
      out.push("K Rend");
      i += 1;
      continue;
    }
    out.push(sentenceToken(parts[i], out.length));
  }
  return out.join(" ");
}

export function titleCaseSlug(slug) {
  return String(slug)
    .split("-")
    .filter(Boolean)
    .map((part) => {
      const lower = part.toLowerCase();
      if (ACRONYM[lower]) return ACRONYM[lower];
      if (lower === "d") return "D";
      return part.charAt(0).toUpperCase() + part.slice(1);
    })
    .join(" ")
    .replace(/\bD H\b/g, "D+H");
}

/** Town labels: a lowercase slug becomes a spoken place. Proper names stay. */
export function spokenPlace(town) {
  const raw = String(town || "").trim();
  if (!raw || /[A-Z]/.test(raw)) return raw;
  return raw.split(/(\s+|-)/).map((part) => {
    if (!part || part === "-" || /^\s+$/.test(part)) return part;
    const lower = part.toLowerCase();
    if (ACRONYM[lower]) return ACRONYM[lower];
    return lower.charAt(0).toUpperCase() + lower.slice(1);
  }).join("");
}

export function topicFor(slug, family) {
  const stem = String(slug)
    .replace(/^emergency-/, "")
    .replace(/-(near-me|cost|prices|quote|cheap|affordable)$/g, "");
  if (TOPICS[stem]) return TOPICS[stem];
  const keys = Object.keys(TOPICS).sort((a, b) => b.length - a.length);
  for (const key of keys) {
    if (stem === key || stem.startsWith(`${key}-`) || stem.endsWith(`-${key}`) || stem.includes(key)) {
      return TOPICS[key];
    }
  }
  const fam = COPY[family] || COPY.roofing;
  return fam.fallback;
}

export function spokenSubject(slug, name) {
  const titled = titleCaseSlug(slug);
  const given = String(name || "").trim();
  if (!given || given.toLowerCase() !== titled.toLowerCase()) return given || titled;
  // Only an exact topic may replace the slug; a family-level topic would give
  // "coving-cost" and "coving-near-me" the same name, title and H1.
  const topic = TOPICS[slug];
  if (topic) {
    // Use the topic only when it opens "X is ...": otherwise the lead is a sentence fragment.
    const opener = String(topic).match(/^(.*?)\s+is\s+/i);
    const lead = opener ? opener[1].replace(/^(?:an|a)\s+/i, "").trim() : "";
    if (lead && lead.length >= 3 && lead.length <= 48 && lead.split(/\s+/).length <= 6 && !/[.?!,:]/.test(lead)) {
      return lead.charAt(0).toUpperCase() + lead.slice(1);
    }
  }
  return naturalizeSlug(slug) || given;
}
