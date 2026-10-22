/**
 * Landlord lines, one from each bank, hashed by path. Banks are wide enough
 * that a single sentence stays well under half of pages after the town is removed.
 * The Useful photographs sentence follows the page's own trade. Town and landlord
 * pages keep a generic line.
 */
import lines from "../../website/data/landlord-lines.json" with { type: "json" };
import services from "../../website/data/services.json" with { type: "json" };
import keywordServices from "../../website/data/keyword-services.json" with { type: "json" };
import keywordFallback from "../../website/data/keyword-service-fallback.json" with { type: "json" };
import photoAsk from "../../website/data/photo-ask.json" with { type: "json" };

const BARRIER_BRANDS = new Set([
  "apt-controls", "atg-access", "avon-barrier", "dea", "designa", "eagle",
  "frontier-pitts", "heald", "hysecurity", "key-automation", "life", "newgate", "skidata",
]);

function keywordFamily(slug) {
  const service = keywordServices[slug] || keywordFallback[slug] || "";
  return service === "aov" ? "aov-air-handling" : service;
}

function noteFamily(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  const leaf = (parts[parts.length - 1] || "").toLowerCase();
  if (!path || path === "/") return "hub:home";
  if (head === "services" && services[parts[2]]) return parts[2];
  if (head === "aov") return "aov-air-handling";
  if (head === "barriers" || (head === "manufacturers" && BARRIER_BRANDS.has(parts[2] || ""))) return "barriers";
  if (head === "keywords" && keywordFamily(parts[2])) return keywordFamily(parts[2]);
  if (head === "jobs" && parts[2] && (services[parts[2]] || keywordFamily(parts[2]))) {
    return services[parts[2]] ? parts[2] : keywordFamily(parts[2]);
  }
  if (head === "landlords" || /landlord/.test(leaf)) return "landlord-compliance";
  if (head === "areas" || leaf === "areas") return "hub:areas";
  if (services[head]) return head;
  if (head === "packages" || leaf === "packages") return "hub:packages";
  return head ? `hub:${head}` : "directory:unknown";
}

function photoTemplate(path) {
  const generic = String(photoAsk.generic || "Useful photographs before {subject} in {place} show the part that has failed and the access.");
  const family = noteFamily(path);
  const parts = String(path || "").split("/").filter(Boolean);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  if (family.startsWith("hub:") || family.startsWith("directory:") || head === "areas" || head === "landlords" || String(path).includes("/landlords")) {
    return generic;
  }
  const specific = photoAsk.families && photoAsk.families[family];
  if (specific) return String(specific);
  const label = services[family] || (family ? family.replace(/-/g, " ") : "");
  const fallback = String(photoAsk.fallback || "");
  if (!label || !fallback) return generic;
  return fallback.replaceAll("{trade}", label);
}

const BANKS = Array.isArray(lines.banks) ? lines.banks : [];

function mix(path, salt) {
  let h = (0x811c9dc5 ^ Math.imul(salt + 1, 0x9e3779b9)) >>> 0;
  const value = String(path || "/");
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i) + salt;
    h = Math.imul(h, 0x01000193);
  }
  h = Math.imul(h ^ (h >>> 16), 0x85ebca6b) >>> 0;
  h = Math.imul(h ^ (h >>> 13), 0xc2b2ae35) >>> 0;
  return (h ^ (h >>> 16)) >>> 0;
}

export function landlordLines(path, subject, place) {
  const job = subject || "the work";
  const town = place || "Stockport";
  const parts = [];
  BANKS.forEach((bank, index) => {
    if (!Array.isArray(bank) || bank.length === 0) return;
    let line = String(bank[mix(path, index + 1) % bank.length]);
    if (line.startsWith("Useful photographs")) line = photoTemplate(path);
    parts.push(line.replaceAll("{subject}", job).replaceAll("{place}", town));
  });
  return parts;
}

export function landlordNote(path, subject, place) {
  return landlordLines(path, subject, place).join(" ");
}
