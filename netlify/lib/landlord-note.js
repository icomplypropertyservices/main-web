/**
 * One landlord note per page. The eight banks are combined from a hash of
 * the path, so the paragraph is not one sentence with the town swapped.
 */
import lines from "../../website/data/landlord-lines.json" with { type: "json" };

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

export function landlordNote(path, subject, place) {
  const job = subject || "the work";
  const town = place || "Stockport";
  const parts = [];
  BANKS.forEach((bank, index) => {
    if (!Array.isArray(bank) || bank.length === 0) return;
    const line = bank[mix(path, index + 1) % bank.length];
    parts.push(String(line).replaceAll("{subject}", job).replaceAll("{place}", town));
  });
  return parts.join(" ");
}
