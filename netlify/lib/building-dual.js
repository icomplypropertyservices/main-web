/**
 * Dual-ring building services P0 town pages.
 * Keyword and job URLs for the 100 P0 intents × the 269-town Manchester/Burnley ring.
 * Other matrix families stay on the Greater Manchester renderer.
 */
import pack from "../../website/data/building-services-dual-p0.json" with { type: "json" };

const SLUGS = new Map((pack.slugs || []).map((row) => [row.slug, row]));
const TOWNS = new Map((pack.towns || []).map((row) => [row.slug, row]));
const TOWN_LIST = pack.towns || [];

export function buildingDualSlug(slug) {
  return SLUGS.get(slug) || null;
}

export function buildingDualTown(slug) {
  return TOWNS.get(slug) || null;
}

export function buildingDualCovers(slug, town) {
  return SLUGS.has(slug) && TOWNS.has(town);
}

export function buildingDualExempt(path) {
  const match = String(path || "").match(/^\/pages\/(keywords|jobs)\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (!match) return false;
  return buildingDualCovers(match[2], match[3]);
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function fitTitle(raw) {
  let title = String(raw).replace(/\s+/g, " ").trim();
  if (!/icomply/i.test(title)) {
    const full = `${title} | iComply Property Services`;
    const short = `${title} | iComply`;
    title = full.length <= 65 ? full : short;
  }
  if (title.length > 65) {
    const cut = title.slice(0, 65).replace(/\s+\S*$/, "");
    title = cut.length >= 30 ? cut : title.slice(0, 65).trim();
  }
  if (title.length < 30) {
    const padded = `${title} | iComply Property Services`;
    title = padded.length <= 65 ? padded : padded.slice(0, 65).trim();
  }
  return title;
}

function fitMeta(raw) {
  let text = String(raw).replace(/\s+/g, " ").trim();
  const pads = [" Request a quote — POA.", " Call 07517806082.", " Stockport SK2.", " Manchester and Burnley."];
  for (const pad of pads) {
    if (text.length >= 140) break;
    text += pad;
  }
  if (text.length > 160) {
    const cut = text.slice(0, 157).replace(/\s+\S*$/, "");
    text = cut.length >= 70 ? cut : text.slice(0, 159).trim();
  }
  return text;
}

function popSentence(town) {
  if (!town.population) {
    return `The dual-ring allowlist does not print a population on row ${town.row}.`;
  }
  return `The allowlist population for row ${town.row} is ${Number(town.population).toLocaleString("en-GB")}.`;
}

function mileSentence(town) {
  const m = town.mi_manchester;
  const b = town.mi_burnley;
  if (m == null || b == null) {
    return `Miles from Manchester and Burnley are not printed on row ${town.row}.`;
  }
  return `Straight-line figures on the allowlist put row ${town.row} about ${m} miles from Manchester and ${b} miles from Burnley.`;
}

function nearby(town, limit = 8) {
  return TOWN_LIST
    .filter((other) => other.slug !== town.slug && other.mi_manchester != null && town.mi_manchester != null)
    .map((other) => ({
      other,
      score: Math.abs(other.mi_manchester - town.mi_manchester) + Math.abs((other.mi_burnley || 0) - (town.mi_burnley || 0)),
    }))
    .sort((a, b) => a.score - b.score || a.other.slug.localeCompare(b.other.slug))
    .slice(0, limit)
    .map((row) => row.other);
}

function paragraphs(meta, town, surface) {
  const cluster = pack.clusters[meta.cluster];
  const facts = cluster.facts;
  const surfaceLabel = surface === "job" ? "job page" : "keyword guide";
  return pack.sections.map((heading, index) => {
    const fact = facts[index % facts.length];
    return `${meta.name} in ${town.name} is the ${surfaceLabel} for “${heading}”. District marker ${town.district}, dual-ring row ${town.row}, ${town.county}. ${popSentence(town)} ${mileSentence(town)} ${meta.angle} ${fact} The quote on this ${surfaceLabel} is price on application. Call ${pack.phone}. Workshop: ${pack.nap}.`;
  });
}

function faqs(meta, town, surface) {
  const label = surface === "job" ? "job" : "guide";
  return [
    [
      `What does ${meta.name} cover in ${town.name}?`,
      `${meta.angle} The ${label} for ${town.name} uses district ${town.district} on row ${town.row}. The quote is price on application.`,
    ],
    [
      `Where is the visit arranged from for ${town.name}?`,
      `Visits for ${meta.name} in ${town.name} are arranged from ${pack.nap}. ${mileSentence(town)} Call ${pack.phone}. ${meta.angle}`,
    ],
    [
      `How is ${meta.name} in ${town.name} priced?`,
      `There is no catalogue fee on the ${town.name} ${label}. ${popSentence(town)} Ask for a quote — price on application. ${meta.angle}`,
    ],
    [
      `Which ring is ${town.name} on?`,
      `${town.name} is on the Manchester and Burnley dual ring, row ${town.row}, district ${town.district}, ${town.county}. ${meta.angle}`,
    ],
  ];
}

function h1For(meta, town, surface) {
  const base = surface === "job" ? `${meta.name} job in ${town.name}` : `${meta.name} in ${town.name}`;
  return base.length >= 12 ? base : `${meta.name} service in ${town.name}`;
}

/**
 * Edge town page. Null when this is not a P0 dual-ring URL.
 * @returns {object|null}
 */
export function renderBuildingDualTown(input) {
  const kind = input.kind === "job" ? "job" : input.kind === "keyword" ? "keyword" : "";
  if (!kind) return null;
  const slug = kind === "job" ? input.job : input.keyword;
  const townSlug = input.town;
  if (!buildingDualCovers(slug, townSlug)) return null;
  const meta = SLUGS.get(slug);
  const town = TOWNS.get(townSlug);
  const surface = kind === "job" ? "job" : "keyword";
  const cluster = pack.clusters[meta.cluster];
  const paras = paragraphs(meta, town, surface);
  const questions = faqs(meta, town, surface);
  const h1 = h1For(meta, town, surface);
  const path = surface === "job"
    ? `/pages/jobs/${slug}/${town.slug}`
    : `/pages/keywords/${slug}/${town.slug}`;
  const otherPath = surface === "job"
    ? `/pages/keywords/${slug}/${town.slug}`
    : `/pages/jobs/${slug}/${town.slug}`;
  const hubPath = surface === "job" ? `/pages/jobs/${slug}` : `/pages/keywords/${slug}`;
  const otherHub = surface === "job" ? `/pages/keywords/${slug}` : `/pages/jobs/${slug}`;
  const canonical = `https://icomplypropertyservices.co.uk${path}`;
  const title = fitTitle(h1);
  const description = fitMeta(
    `${meta.name} in ${town.name} (${town.district}). ${town.county}. Price on application from Stockport.`
  );
  const images = cluster.images;
  const peers = (pack.slugs || []).filter((row) => row.cluster === meta.cluster && row.slug !== slug).slice(0, 8);
  const near = nearby(town, 8);
  const areaLinks = town.gm
    ? `<li><a href="/pages/areas/${escapeHtml(town.slug)}">Property services in ${escapeHtml(town.name)}</a></li>`
    : `<li><a href="/pages/areas/manchester">Manchester area hub</a></li><li><a href="/pages/areas/stockport">Stockport area hub</a></li>`;
  const img = (src, alt, lazy) => (
    `<figure><img src="${src}" alt="${escapeHtml(alt)}" width="1200" height="800"${lazy ? ' loading="lazy"' : ""}></figure>`
  );
  const body = paras.map((paragraph, index) => (
    `<h2>${escapeHtml(pack.sections[index])} in ${escapeHtml(town.name)}</h2><p>${escapeHtml(paragraph)}</p>`
  )).join("");
  const extra = meta.gas
    ? `<p>${escapeHtml(pack.gas_duty)} ${escapeHtml(pack.gas_denial)}</p>`
    : `<p>${escapeHtml(pack.arrange)}</p>`;
  const faqHtml = questions.map(([q, a]) => (
    `<details><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`
  )).join("");
  const peerHtml = peers.map((row) => (
    `<li><a href="/pages/${surface === "job" ? "jobs" : "keywords"}/${escapeHtml(row.slug)}">${escapeHtml(row.name)}</a></li>`
  )).join("");
  const nearHtml = near.map((row) => (
    `<li><a href="${path.split("/").slice(0, -1).join("/")}/${escapeHtml(row.slug)}">${escapeHtml(meta.name)} in ${escapeHtml(row.name)}</a></li>`
  )).join("");
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Service",
        name: h1,
        serviceType: meta.service,
        areaServed: town.name,
        description,
        url: canonical,
        provider: {
          "@type": "Organization",
          name: "iComply Property Services",
          url: "https://icomplypropertyservices.co.uk/",
        },
      },
      {
        "@type": "FAQPage",
        mainEntity: questions.map(([q, a]) => ({
          "@type": "Question",
          name: q,
          acceptedAnswer: { "@type": "Answer", text: a },
        })),
      },
    ],
  };
  const html = `<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${escapeHtml(title)}</title>
<meta name="description" content="${escapeHtml(description)}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="${canonical}">
<meta property="og:type" content="website">
<meta property="og:title" content="${escapeHtml(title)}">
<meta property="og:description" content="${escapeHtml(description)}">
<meta property="og:url" content="${canonical}">
<meta property="og:image" content="https://icomplypropertyservices.co.uk${images[0]}">
<script type="application/ld+json">${JSON.stringify(schema).replace(/</g, "\\u003c")}</script>
</head>
<body data-seo-family="${surface === "job" ? "keyword-area" : "keyword-area"}">
<header>
<p><a href="/">iComply Property Services</a> · <a href="/pages/services">Services</a> · <a href="/pages/jobs">Jobs</a> · <a href="/pages/keywords">Keywords</a> · <a href="/pages/areas">Areas</a> · <a href="/contact">Contact</a></p>
</header>
<main id="main-content">
<nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="${hubPath}">${escapeHtml(meta.name)}</a> / <span>${escapeHtml(h1)}</span></nav>
<h1>${escapeHtml(h1)}</h1>
${img(images[0], `${meta.name} in ${town.name}`, false)}
${img(images[1], `${meta.name} tools for ${town.name}`, true)}
${img(images[2], `${meta.name} finish in ${town.name}`, true)}
<article id="local-copy" data-seo-faq="1">
${body}
${extra}
<p>Parent: <a href="${hubPath}">${escapeHtml(meta.name)} ${surface === "job" ? "job hub" : "guide"}</a> · <a href="${otherHub}">${escapeHtml(meta.name)} ${surface === "job" ? "guide" : "job hub"}</a> · <a href="${otherPath}">${escapeHtml(meta.name)} ${surface === "job" ? "guide" : "job"} in ${escapeHtml(town.name)}</a> · <a href="/pages/services/${escapeHtml(meta.service)}">${escapeHtml(meta.service.replace(/-/g, " "))}</a> · <a href="/pages/keywords/${escapeHtml(slug)}/manchester">${escapeHtml(meta.name)} in Manchester</a> · <a href="/pages/keywords/${escapeHtml(slug)}/burnley">${escapeHtml(meta.name)} in Burnley</a> · <a href="/pages/jobs/${escapeHtml(slug)}/manchester">${escapeHtml(meta.name)} job in Manchester</a> · <a href="/pages/jobs/${escapeHtml(slug)}/burnley">${escapeHtml(meta.name)} job in Burnley</a> · <a href="tel:07517806082">07517806082</a></p>
<h2>Related ${escapeHtml(meta.cluster)} pages</h2><ul>${peerHtml}</ul>
<h2>Nearby dual-ring towns</h2><ul>${nearHtml}</ul>
<h2>Area hubs</h2><ul>${areaLinks}<li><a href="/pages/areas">All published areas</a></li></ul>
<h2>Questions about ${escapeHtml(town.name)}</h2>
${faqHtml}
</article>
</main>
</body>
</html>`;
  const words = html.replace(/<script[\s\S]*?<\/script>/gi, " ").replace(/<[^>]+>/g, " ").trim().split(/\s+/).filter(Boolean).length;
  return {
    html,
    title,
    description,
    h1,
    canonical,
    robots: "index, follow",
    indexable: true,
    path,
    status: 200,
    words,
  };
}
