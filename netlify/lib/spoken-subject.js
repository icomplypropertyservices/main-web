/**
 * Spoken labels for catalogue slugs. Kept out of trades-lock.js so
 * link-blocks.js can use it without a circular import.
 */
import copyDoc from "../../website/data/seo-lock/family-copy.json" with { type: "json" };

const TOPICS = copyDoc.topics || {};
const COPY = copyDoc.families || {};

const ACRONYM = {
  aov: "AOV", grp: "GRP", epdm: "EPDM", eicr: "EICR", ev: "EV", pir: "PIR",
  upvc: "uPVC", lvt: "LVT", cctv: "CCTV", cp12: "CP12", pat: "PAT", hmo: "HMO",
  cdm: "CDM", co: "CO", tv: "TV", ip: "IP", led: "LED", rcd: "RCD", mcb: "MCB",
};

const VERB = {
  fit: "fitting", fitting: "fitting", swap: "replacement", replace: "replacement",
  replacement: "replacement", install: "installation", installation: "installation",
  repair: "repair", repairs: "repairs", service: "servicing", servicing: "servicing",
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
  const last = parts[parts.length - 1].toLowerCase();
  if (VERB[last]) parts[parts.length - 1] = VERB[last];
  const out = [];
  for (let i = 0; i < parts.length; i++) {
    if (parts[i].toLowerCase() === "d" && String(parts[i + 1] || "").toLowerCase() === "h") {
      out.push("D+H");
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
      if (part === "aov") return "AOV";
      if (part === "grp") return "GRP";
      if (part === "epdm") return "EPDM";
      if (part === "eicr") return "EICR";
      if (part === "ev") return "EV";
      if (part === "pir") return "PIR";
      if (part === "upvc") return "uPVC";
      if (part === "lvt") return "LVT";
      if (part === "d" ) return "D";
      return part.charAt(0).toUpperCase() + part.slice(1);
    })
    .join(" ")
    .replace(/\bD H\b/g, "D+H");
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
  const topic = TOPICS[slug] || topicFor(slug, "");
  if (topic) {
    const lead = String(topic).split(/\s+is\s+/i)[0].replace(/^(?:an|a)\s+/i, "").trim();
    if (lead && lead.length >= 3 && lead.length <= 48 && !/[.?!,:]/.test(lead)) {
      return lead.charAt(0).toUpperCase() + lead.slice(1);
    }
  }
  return naturalizeSlug(slug) || given;
}
