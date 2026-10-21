// Visible credits for third-party photos (data/hero-image-credits.json), edge side.
// Mirrors website/includes/photo-credits.php (icomplyPhotoCreditInject).
import creditRows from "../../website/data/hero-image-credits.json" with { type: "json" };

export const VISIBLE_CREDIT = new Set(["/assets/images/services/plastering.jpg", "/assets/images/services/dry-lining.jpg", "/assets/images/services/damp-proofing.jpg", "/assets/images/services/building-maintenance.jpg", "/assets/images/services/cdm-support.jpg", "/assets/images/services/groundworks.jpg", "/assets/images/services/fire-signage.jpg", "/assets/images/services/fire-extinguishers.jpg", "/assets/images/services/smoke-co-alarms.jpg", "/assets/images/services/insulation.jpg", "/assets/images/services/carpentry.jpg", "/assets/images/services/sprinkler-systems.jpg", "/assets/images/services/barriers-photo.jpg", "/assets/images/services/barriers.jpg", "/assets/images/keywords/car-park-barrier-access.jpg"]);
const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
const decode = (t) => t.replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&lt;/g, "<").replace(/&gt;/g, ">");
export const PHOTO_CREDITS = Object.fromEntries(creditRows.filter((r) => /^https?:\/\//.test(r.source || "")).map((r) => [r.file, {
  artist: decode(String(r.artist || "").replace(/<\/li>/g, "; ").replace(/<[^>]+>/g, "")).replace(/\s+/g, " ").replace(/^[\s;]+|[\s;]+$/g, "") || "Unknown author",
  license: r.license || "",
  source: r.source,
}]));
export function photoCreditText(src) {
  const c = PHOTO_CREDITS[src];
  if (!c) return "";
  return `Example photo, not an iComply job. Photo: ${esc(c.artist)}, ${esc(c.license)}, via <a href="${esc(c.source)}" rel="nofollow noopener" target="_blank">Wikimedia Commons</a>. <a href="/pages/photo-credits">All photo credits</a>`;
}
/** <figcaption> for the credited photos among srcs ('' when none). */
export function photoCreditCaption(srcs) {
  const lines = [...new Set(srcs)].filter((s) => VISIBLE_CREDIT.has(s)).map(photoCreditText).filter(Boolean);
  return lines.length ? `<figcaption data-photo-credit="1" style="font-size:.75rem;line-height:1.35;color:#444;margin:4px 2px 0">${lines.join("<br>")}</figcaption>` : "";
}
