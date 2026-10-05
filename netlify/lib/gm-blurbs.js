/**
 * Greater Manchester blurbs from the SEO copy pack.
 * Keys are "{slug}/{town}". Authored service×town rows win over composed remainders.
 */
import serviceTown from "../../website/data/gm-blurbs/gm-service-town-blurbs.json" with { type: "json" };
import serviceRest from "../../website/data/gm-blurbs/gm-service-town-blurbs-remaining.json" with { type: "json" };
import keywordTown from "../../website/data/gm-blurbs/keyword-gm-blurbs.json" with { type: "json" };

const serviceMap = { ...serviceRest, ...serviceTown };

export function gmTownBlurb(kind, slug, town) {
  if (!slug || !town) return null;
  const key = `${slug}/${town}`;
  const row = kind === "keyword" ? keywordTown[key] : serviceMap[key];
  if (!row || !row.blurb) return null;
  return row;
}
