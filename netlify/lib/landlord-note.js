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

function varyPhotoAsk(path, line) {
  const prefix = "Useful photographs before {subject} in {place} ";
  const text = String(line || "");
  if (!text.startsWith(prefix)) return text;
  const rest = text.slice(prefix.length);
  const openings = [
    "Before {subject} in {place}, pictures that help ",
    "For {subject} in {place}, please send photographs that ",
    "When booking {subject} in {place}, the useful frames ",
    "A quote for {subject} in {place} needs photos that ",
    "If you are arranging {subject} in {place}, pictures should ",
    "The {subject} visit in {place} is clearer when photos ",
    "Send, for {subject} in {place}, images that ",
    "What prepares {subject} in {place} is photographs that ",
  ];
  let h = 0x811c9dc5;
  const value = `${path || "/"}|photo-line`;
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 0x01000193);
  }
  h >>>= 0;
  return openings[h % openings.length] + rest;
}

function photoTemplate(path) {
  const generic = String(photoAsk.generic || "Useful photographs before {subject} in {place} show the part that has failed and the access.");
  const family = noteFamily(path);
  const parts = String(path || "").split("/").filter(Boolean);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  let line = generic;
  if (!(family.startsWith("hub:") || family.startsWith("directory:") || head === "areas" || head === "landlords" || String(path).includes("/landlords"))) {
    const specific = photoAsk.families && photoAsk.families[family];
    if (specific) line = String(specific);
    else {
      const label = services[family] || (family ? family.replace(/-/g, " ") : "");
      const fallback = String(photoAsk.fallback || "");
      if (label && fallback) line = fallback.replaceAll("{trade}", label);
    }
  }
  return varyPhotoAsk(path, line);
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
  // SEO audit 22 Oct: only the trade-specific photo request is kept. The other 13
  // banks were generic access/key/void scenarios rotated per URL to avoid duplicate
  // detection; they read as filler on every page and are left out.
  BANKS.forEach((bank, index) => {
    if (!Array.isArray(bank) || bank.length === 0) return;
    let line = String(bank[mix(path, index + 1) % bank.length]);
    if (!line.startsWith("Useful photographs")) return;
    line = photoTemplate(path);
    parts.push(line.replaceAll("{subject}", job).replaceAll("{place}", town));
  });
  return parts;
}

export function landlordNote(path, subject, place) {
  return landlordLines(path, subject, place).join(" ");
}
