/**
 * Unique town × keyword and town × service HTML.
 * Catalogue JSON is written by website/includes/matrix-catalogue.php.
 * A known keyword or service with any well-formed town slug returns HTTP 200.
 * Towns in the published catalogue are index,follow. Other towns are noindex
 * so the sitemap stays an exact list of indexable URLs.
 */

const RESERVED = new Set([
  "keywords", "services", "areas", "manufacturers", "resources", "packages",
  "jobs", "commercial", "aov", "barriers", "aov-air-handling", "products",
  "shop", "emergency-lighting-jobs", "asbestos-jobs", "water-wras",
]);

const AUDIENCES = [
  "landlords",
  "managing agents",
  "facilities managers",
  "commercial tenants",
  "HMO operators",
  "care home managers",
  "school and college sites",
  "warehouse and industrial units",
  "retail units",
  "housing associations",
];

const VISIT_STYLES = [
  "a planned daytime visit",
  "an out-of-hours attendance where the site cannot close",
  "a landlord turnaround between tenancies",
  "a facilities visit booked around occupied floors",
  "a survey followed by a return visit for the agreed scope",
];

const GAS_SENTENCE = "Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers. iComply is not Gas Safe registered.";
const SUBCONTRACT_SENTENCE = "The work is carried out by relevant qualified people. Where a visit needs a specialist ticket, iComply uses approved subcontractors.";

function hashStr(value) {
  let h = 2166136261;
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h >>> 0;
}

function pick(list, seed) {
  return list[seed % list.length];
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function titleCaseSlug(slug) {
  return String(slug)
    .split("-")
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");
}

function words(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

export function renderTownPage(input) {
  const kind = input.kind === "service" ? "service" : "keyword";
  const catalogue = input.catalogue || {};
  const keywords = catalogue.keywords || {};
  const places = catalogue.places || {};
  const services = catalogue.services || {};
  const labels = services.labels || services;
  const serviceKeywords = services.keywords || {};

  let keyword = null;
  let serviceSlug = "";
  let serviceName = "";
  let keywordKnown = false;
  if (kind === "keyword") {
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.keyword || "")) {
      return null;
    }
    keyword = keywords[input.keyword];
    keywordKnown = Boolean(keyword);
    if (!keyword) {
      const blob = input.keyword.replace(/-/g, " ");
      const gas = /\b(cp12|cp44|gas safety|boiler|gas certificate|landlord gas)\b/.test(blob)
        && !/\b(gas suppression|inert gas|clean agent)\b/.test(blob);
      keyword = {
        name: titleCaseSlug(input.keyword),
        service: gas ? "gas-systems" : "building-maintenance",
        intro: "",
        focus: [
          "Scope agreed before the visit",
          "Quote is price on application",
          "Paperwork left with the instructing client",
        ],
        gas,
        synthetic: true,
      };
    }
    serviceSlug = keyword.service || "electrical";
    serviceName = labels[serviceSlug] || titleCaseSlug(serviceSlug);
  } else {
    serviceSlug = input.service;
    serviceName = labels[serviceSlug];
    if (!serviceName || RESERVED.has(serviceSlug)) {
      return null;
    }
    keywordKnown = true;
    const related = (serviceKeywords[serviceSlug] || [])[0];
    const relatedKw = related ? keywords[related] : null;
    keyword = {
      name: serviceName,
      service: serviceSlug,
      intro: relatedKw && relatedKw.intro
        ? relatedKw.intro
        : `${serviceName} for landlords, managing agents and commercial sites, arranged from Stockport.`,
      focus: relatedKw && relatedKw.focus && relatedKw.focus.length
        ? relatedKw.focus
        : [
          `Survey and scope for ${serviceName}`,
          "Quote is price on application",
          "Paperwork handed to the instructing client",
        ],
      gas: serviceSlug === "gas-systems" || Boolean(relatedKw && relatedKw.gas),
    };
  }

  const townSlug = input.town;
  const known = Boolean(places[townSlug]);
  const place = known
    ? places[townSlug]
    : {
      name: titleCaseSlug(townSlug),
      region: "North West",
      country: "England",
      population: 0,
      housing: "",
      industry: "",
      neighbours: [],
      synthetic: true,
    };
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(townSlug)) {
    return null;
  }

  const seed = hashStr(`${kind}|${keyword.name}|${serviceSlug}|${townSlug}`);
  const audience = pick(AUDIENCES, seed);
  const visit = pick(VISIT_STYLES, seed >>> 3);
  const neighbours = Array.isArray(place.neighbours) ? place.neighbours.filter(Boolean) : [];
  const near = neighbours.length ? neighbours.join(", ") : "Stockport, Manchester and the surrounding North West";
  const pop = place.population
    ? `${place.name} has a built-up population of about ${Number(place.population).toLocaleString("en-GB")}.`
    : "";
  const housing = place.housing
    ? `Census housing in ${place.name} includes ${place.housing}.`
    : `Property in ${place.name} runs from older terraces and conversions to commercial units.`;
  const industry = place.industry
    ? `Local employment includes ${place.industry}, so we plan visits around occupied buildings rather than empty showrooms.`
    : `Sites in ${place.name} include rented homes, shops, offices and light-industrial units.`;
  const focus = (keyword.focus && keyword.focus.length ? keyword.focus : [`Scope agreed before ${keyword.name} starts`]).slice(0, 4);
  const path = kind === "keyword"
    ? `/pages/keywords/${input.keyword}/${townSlug}`
    : `/pages/${serviceSlug}/${townSlug}`;
  const canonical = `https://icomplypropertyservices.co.uk${path}`;
  const indexable = known && keywordKnown && !keyword.synthetic;
  const robots = indexable ? "index, follow" : "noindex, follow";
  const h1 = `${keyword.name} for ${audience} in ${place.name}`;
  const title = `${keyword.name} in ${place.name} | ${serviceName} | iComply`;
  const fact = place.housing || place.region;
  const description = `${keyword.name} in ${place.name}, ${place.region}. ${housing} Quote is price on application (POA). Cover also reaches ${near}.`
    .replace(/\s+/g, " ")
    .trim();

  const openings = [
    `${keyword.name} in ${place.name} is booked from the Stockport workshop for ${audience} who want the visit tied to that site, not a generic national script.`,
    `Clients ask for ${keyword.name} in ${place.name} when a landlord, agent or facilities lead needs ${serviceName.toLowerCase()} arranged around the way that town is actually built.`,
    `A ${keyword.name} enquiry in ${place.name} starts with the building, the access and the paperwork the instructing client has to keep.`,
  ];
  const scopes = [
    `The scope for ${keyword.name} is confirmed in writing before anyone attends ${place.name}. ${focus.map((item) => item.replace(/\.$/, "")).join(". ")}.`,
    `On a ${visit}, the engineer works to the agreed ${keyword.name} scope and leaves the certificate, test sheet or report with the person who instructed iComply.`,
    `We do not invent a day rate on this page. The quote for ${keyword.name} in ${place.name} is price on application (POA) after the scope is clear.`,
  ];
  const travel = [
    `${place.name} is in ${place.region}, ${place.country}. Travel is planned from Stockport, with nearby cover in ${near}.`,
    pop,
    housing,
    industry,
    `If the job sits just outside ${place.name}, say in ${near}, it is still quoted as one visit rather than split into a different product.`,
  ].filter(Boolean);
  const intro = keyword.intro
    ? `${keyword.intro.replace(/\s+/g, " ").trim()}`
    : "";
  const gas = keyword.gas ? GAS_SENTENCE : "";
  const paragraphs = [
    pick(openings, seed),
    intro,
    pick(scopes, seed >>> 5),
    travel.join(" "),
    SUBCONTRACT_SENTENCE,
    gas,
    `Ask for ${keyword.name} in ${place.name} by phone on 07517806082 or through the contact form. Say which building, whether it is occupied, and whether you need ${visit}. The reply quotes the work as POA and names the ${serviceName.toLowerCase()} scope before a date is fixed.`,
  ].filter(Boolean);

  const relatedKeywords = (serviceKeywords[serviceSlug] || []).slice(0, 3);
  const keywordLinks = relatedKeywords
    .filter((slug) => keywords[slug])
    .map((slug) => `<li><a href="/pages/keywords/${slug}/${townSlug}">${escapeHtml(keywords[slug].name)} in ${escapeHtml(place.name)}</a></li>`)
    .join("");
  const neighbourLinks = neighbours.slice(0, 3).map((name) => {
    const slug = name.toLowerCase().replace(/&/g, "and").replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
    if (!slug || slug === townSlug) {
      return "";
    }
    const href = kind === "keyword"
      ? `/pages/keywords/${input.keyword}/${slug}`
      : `/pages/${serviceSlug}/${slug}`;
    return `<li><a href="${href}">${escapeHtml(keyword.name)} in ${escapeHtml(name)}</a></li>`;
  }).join("");

  const faqs = [
    [
      `Do you cover ${place.name} for ${keyword.name}?`,
      `Yes. ${place.name} is covered from Stockport, including nearby ${near}. The quote is POA once the building and the scope are known.`,
    ],
    [
      `Who carries out ${keyword.name} in ${place.name}?`,
      `${SUBCONTRACT_SENTENCE} ${gas}`.trim(),
    ],
    [
      `How is ${keyword.name} priced in ${place.name}?`,
      `The quote is price on application. We do not publish a fixed price for ${keyword.name} in ${place.name} because access, condition and the agreed scope change the visit.`,
    ],
  ];
  const faqHtml = faqs.map(([q, a]) => (
    `<details><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`
  )).join("");
  const focusHtml = focus.map((item) => `<li>${escapeHtml(item)}</li>`).join("");
  const bodyHtml = paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("");

  const html = `<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${escapeHtml(title)}</title>
<meta name="description" content="${escapeHtml(description)}">
<meta name="robots" content="${robots}">
<link rel="canonical" href="${canonical}">
<link rel="stylesheet" href="/assets/css/site.css">
</head>
<body>
<header>
<p><a href="/">iComply Property Services</a> · <a href="/pages/services">Services</a> · <a href="/pages/keywords">Keywords</a> · <a href="/contact">Contact</a></p>
</header>
<main>
<h1>${escapeHtml(h1)}</h1>
<p>${escapeHtml(serviceName)} arranged from Stockport for ${escapeHtml(place.name)} and ${escapeHtml(place.region)}.</p>
<article id="local-copy">
${bodyHtml}
<h2>What the ${escapeHtml(keyword.name)} visit covers in ${escapeHtml(place.name)}</h2>
<ul>${focusHtml}</ul>
<h2>Quote</h2>
<p>The quote is price on application (POA). Published list prices on other iComply pages are not a price for this ${escapeHtml(place.name)} visit.</p>
<h2>Nearby ${escapeHtml(keyword.name)}</h2>
<ul>
<li><a href="${kind === "keyword" ? `/pages/keywords/${input.keyword}` : `/pages/services/${serviceSlug}`}">${escapeHtml(keyword.name)} hub</a></li>
<li><a href="/pages/services/${serviceSlug}">${escapeHtml(serviceName)}</a></li>
${keywordLinks}
${neighbourLinks}
</ul>
<h2>Questions about ${escapeHtml(place.name)}</h2>
${faqHtml}
</article>
</main>
</body>
</html>`;

  return {
    html,
    title,
    description,
    h1,
    canonical,
    robots,
    indexable,
    path,
    status: 200,
    words: words(html.replace(/<[^>]+>/g, " ")),
  };
}

let cataloguePromise = null;

async function loadCatalogue(origin) {
  if (!cataloguePromise) {
    cataloguePromise = (async () => {
      const [keywords, places, services] = await Promise.all([
        fetch(`${origin}/assets/matrix/keywords.json`).then((response) => response.json()),
        fetch(`${origin}/assets/matrix/places.json`).then((response) => response.json()),
        fetch(`${origin}/assets/matrix/services.json`).then((response) => response.json()),
      ]);
      return { keywords, places, services };
    })().catch((error) => {
      cataloguePromise = null;
      throw error;
    });
  }
  return cataloguePromise;
}

export default async (request, context) => {
  const url = new URL(request.url);
  const path = url.pathname.replace(/\/+$/, "") || "/";
  const keywordMatch = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const serviceMatch = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (!keywordMatch && !serviceMatch) {
    return context.next();
  }
  let catalogue;
  try {
    catalogue = await loadCatalogue(url.origin);
  } catch {
    return context.next();
  }
  const rendered = keywordMatch
    ? renderTownPage({
      kind: "keyword",
      keyword: keywordMatch[1],
      town: keywordMatch[2],
      catalogue,
    })
    : renderTownPage({
      kind: "service",
      service: serviceMatch[1],
      town: serviceMatch[2],
      catalogue,
    });
  if (!rendered) {
    return context.next();
  }
  return new Response(rendered.html, {
    status: 200,
    headers: {
      "content-type": "text/html; charset=utf-8",
      "cache-control": "public, max-age=3600",
      "x-robots-tag": rendered.robots,
    },
  });
};

export const config = {
  path: [
    "/pages/keywords/:keyword/:town",
    "/pages/keywords/:keyword/:town/",
    "/pages/:service/:town",
    "/pages/:service/:town/",
  ],
};
