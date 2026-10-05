/**
 * Greater Manchester blurbs from the SEO copy pack.
 * Keys are "{slug}/{town}". Authored service×town rows win over composed remainders.
 */
import serviceTown from "../../website/data/gm-blurbs/gm-service-town-blurbs.json" with { type: "json" };
import serviceRest from "../../website/data/gm-blurbs/gm-service-town-blurbs-remaining.json" with { type: "json" };
import keywordTown from "../../website/data/gm-blurbs/keyword-gm-blurbs.json" with { type: "json" };
import { isGmTown } from "./link-blocks.js";

const serviceMap = { ...serviceRest, ...serviceTown };
const KEYWORD_TOWNS = new Set(["bolton", "manchester", "stockport"]);
const FORMULAIC = /\bGM\d{2,}[a-z0-9]/i;

export function gmTownBlurb(kind, slug, town) {
  if (!slug || !town) return null;
  if (kind === "keyword" && !KEYWORD_TOWNS.has(town)) return null;
  if (kind !== "keyword" && !isGmTown(town)) return null;
  const key = `${slug}/${town}`;
  const row = kind === "keyword" ? keywordTown[key] : serviceMap[key];
  if (!row || !row.blurb) return null;
  if (FORMULAIC.test(row.blurb) || /\b(SK2Base|CityWards|BoltonCentre|WiganPier|SalfordCrescent)\b/.test(row.blurb)) {
    return null;
  }
  if (/\bsub-?contract/i.test(row.blurb)) return null;
  const blurb = String(row.blurb).replace(/\bb\d{4,6}\b/g, "").replace(/\s{2,}/g, " ").replace(/\s+([,.;:])/g, "$1").trim();
  if (!blurb) return null;
  return { ...row, blurb };
}
