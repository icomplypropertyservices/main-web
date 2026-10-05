/**
 * Unique town × keyword, town × service, manufacturer × town and job × town HTML.
 * Catalogue JSON is written by website/includes/matrix-catalogue.php.
 * Keyword variants ({service}--{stem}--…) and /matrix-sitemap/{n}.xml are
 * decoded from assets/matrix/variants.json. Greater Manchester towns are
 * index,follow. Other places, including the nationwide gazetteer, stay noindex.
 */
import { renderVariantPage, renderVariantSitemap } from "../lib/variant-matrix.js";
import { breadcrumbHtml, isGmTown, matrixRelatedHtml } from "../lib/link-blocks.js";
import { gmTownBlurb } from "../lib/gm-blurbs.js";

const RESERVED = new Set([
  "keywords", "services", "areas", "manufacturers", "resources", "packages",
  "jobs", "commercial", "aov", "barriers", "aov-air-handling", "products",
  "shop", "emergency-lighting-jobs", "asbestos-jobs",
]);

const FAMILY = new Set([
  "fire-alarms", "fire-risk-assessments", "fire-extinguishers", "fire-doors",
  "fire-stopping", "fire-suppression", "fire-signage", "fire-compartmentation",
  "dry-risers", "emergency-lighting", "barriers", "aov-air-handling",
]);

const IMAGE_SLUG = {};

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

function clampMeta(text) {
  const clean = String(text).replace(/\s+/g, " ").trim();
  if (clean.length <= 158) return clean;
  const cut = clean.slice(0, 158).replace(/\s+\S*$/, "");
  return cut.length >= 40 ? cut : clean.slice(0, 158);
}

function jsonLd(value) {
  return JSON.stringify(value).replace(/</g, "\\u003c");
}

function isFamilyService(slug, catalogue) {
  const family = catalogue.services && catalogue.services.family;
  if (Array.isArray(family) && family.length) return family.includes(slug);
  return FAMILY.has(slug);
}

function resolvePlace(townSlug, catalogue) {
  const places = catalogue.places || {};
  const nationwide = catalogue.nationwide || {};
  if (places[townSlug]) return { place: places[townSlug], tier: "core" };
  if (nationwide[townSlug]) return { place: nationwide[townSlug], tier: "nationwide" };
  return {
    place: {
      name: titleCaseSlug(townSlug),
      region: "North West",
      country: "England",
      population: 0,
      housing: "",
      industry: "",
      neighbours: [],
      synthetic: true,
    },
    tier: "other",
  };
}

export function renderTownPage(input) {
  const catalogue = input.catalogue || {};
  const keywords = catalogue.keywords || {};
  const services = catalogue.services || {};
  const labels = services.labels || services;
  const serviceKeywords = services.keywords || {};
  const kind = input.kind === "service" || input.kind === "manufacturer" || input.kind === "job"
    ? input.kind
    : "keyword";

  let keyword = null;
  let serviceSlug = "";
  let serviceName = "";
  let keywordKnown = false;
  let nationwideFlag = false;
  let path = "";
  let subject = "";

  if (kind === "manufacturer") {
    const brand = catalogue.manufacturers && catalogue.manufacturers[input.brand];
    if (!brand || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.brand || "")) return null;
    subject = brand.name || titleCaseSlug(input.brand);
    serviceSlug = brand.service || "building-maintenance";
    serviceName = labels[serviceSlug] || titleCaseSlug(serviceSlug);
    nationwideFlag = Boolean(brand.nationwide) || isFamilyService(serviceSlug, catalogue);
    keywordKnown = true;
    keyword = {
      name: subject,
      service: serviceSlug,
      intro: `${subject} equipment and visits are arranged from Stockport for sites that already use this manufacturer.`,
      focus: [
        `${subject} parts identified before the visit`,
        "Quote is price on application",
        "Paperwork left with the instructing client",
      ],
      gas: serviceSlug === "gas-systems",
    };
    path = `/pages/manufacturers/${input.brand}/${input.town}`;
  } else if (kind === "job") {
    const job = catalogue.jobs && catalogue.jobs[input.job];
    if (!job || !/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.job || "")) return null;
    subject = job.name || titleCaseSlug(input.job);
    serviceSlug = job.service || "building-maintenance";
    serviceName = labels[serviceSlug] || titleCaseSlug(serviceSlug);
    nationwideFlag = Boolean(job.nationwide) || isFamilyService(serviceSlug, catalogue);
    keywordKnown = true;
    keyword = {
      name: subject,
      service: serviceSlug,
      intro: `${subject} is a job type under ${serviceName}, scoped per building rather than sold as a package price.`,
      focus: job.focus && job.focus.length ? job.focus : [
        "Scope agreed before anyone attends",
        "Quote is price on application",
        "Result left with the instructing client",
      ],
      gas: Boolean(job.gas) || serviceSlug === "gas-systems",
    };
    path = `/pages/jobs/${input.job}/${input.town}`;
  } else if (kind === "keyword") {
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(input.keyword || "")) return null;
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
    subject = keyword.name;
    nationwideFlag = isFamilyService(serviceSlug, catalogue);
    path = `/pages/keywords/${input.keyword}/${input.town}`;
  } else {
    serviceSlug = input.service;
    serviceName = labels[serviceSlug];
    if (!serviceName || RESERVED.has(serviceSlug)) return null;
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
    subject = serviceName;
    nationwideFlag = isFamilyService(serviceSlug, catalogue);
    path = `/pages/${serviceSlug}/${input.town}`;
  }

  const townSlug = input.town;
  if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(townSlug || "")) return null;
  const resolved = resolvePlace(townSlug, catalogue);
  const place = resolved.place;
  const tier = resolved.tier;
  const indexable = keywordKnown && !keyword.synthetic && tier === "core";
  const robots = indexable ? "index, follow" : "noindex, follow";
  const seed = hashStr(`${kind}|${subject}|${serviceSlug}|${townSlug}`);
  const audience = pick(AUDIENCES, seed);
  const visit = pick(VISIT_STYLES, seed >>> 3);
  const neighbours = Array.isArray(place.neighbours) ? place.neighbours.filter(Boolean) : [];
  const near = neighbours.length ? neighbours.join(", ") : "Stockport, Manchester and the surrounding North West";
  const pop = place.population
    ? `${place.name} has a published usual-resident population of about ${Number(place.population).toLocaleString("en-GB")}.`
    : `${place.name} is quoted from the address, not from an invented population figure.`;
  const housing = place.housing
    ? `Census housing in ${place.name} includes ${place.housing}.`
    : `Buildings in ${place.name} include rented homes, shops, offices and light-industrial units.`;
  const industry = place.industry
    ? `Local employment notes for ${place.name} include ${place.industry}, so visits are planned around occupied buildings.`
    : `Sites in ${place.name} are a mix of housing and commercial units. The scope follows the building, not a stock paragraph.`;
  const focus = (keyword.focus && keyword.focus.length ? keyword.focus : [`Scope agreed before ${subject} starts`]).slice(0, 4);
  const canonical = `https://icomplypropertyservices.co.uk${path}`;
  const h1 = `${subject} for ${audience} in ${place.name}`;
  const title = `${subject} in ${place.name} | ${serviceName} | iComply`;
  const description = clampMeta(
    `${subject} in ${place.name}, ${place.region}. ${housing} Quote is price on application (POA). Cover also reaches ${near}.`
  );
  const imageSlug = IMAGE_SLUG[serviceSlug] || serviceSlug;
  const imageSrc = `/assets/images/services/${imageSlug}.jpg`;
  const imageAlt = `${subject} in ${place.name} — ${serviceName}`;

  const openings = [
    `${subject} in ${place.name} is booked from the Stockport workshop for ${audience} who want the visit tied to that site, not a generic national script.`,
    `Clients ask for ${subject} in ${place.name} when a landlord, agent or facilities lead needs ${serviceName.toLowerCase()} arranged around the way that town is actually built.`,
    `A ${subject} enquiry in ${place.name} starts with the building, the access and the paperwork the instructing client has to keep.`,
  ];
  const scopes = [
    `The scope for ${subject} is confirmed in writing before anyone attends ${place.name}. ${focus.map((item) => String(item).replace(/\.$/, "")).join(". ")}.`,
    `On ${visit}, the person on site works to the agreed ${subject} scope and leaves the certificate, test sheet or report with the person who instructed iComply.`,
    `We do not invent a day rate on this page. The quote for ${subject} in ${place.name} is price on application (POA) after the scope is clear.`,
  ];
  const processes = [
    [
      `Phone or write with the ${place.name} address, what ${subject} has to cover, and whether the building is occupied.`,
      `iComply confirms the ${serviceName.toLowerCase()} scope in writing. The quote for this ${place.name} visit is POA.`,
      `${visit.charAt(0).toUpperCase()}${visit.slice(1)} is booked around access in ${place.name}.`,
      `The result is handed to the instructing client. ${SUBCONTRACT_SENTENCE}`,
    ],
    [
      `For ${audience} in ${place.name}, send photos of the plant or the board before asking for a date.`,
      `The ${subject} scope names what will be looked at and what is outside the visit.`,
      `Travel from Stockport to ${place.name} is part of the POA quote, not a hidden extra.`,
      `If a specialist ticket is required in ${place.region}, an approved subcontractor attends with the relevant qualification.`,
    ],
    [
      `Start with the postcode in ${place.name} and the reason for the ${subject} visit.`,
      `A written scope comes back before anyone is dispatched. There is no catalogue fee on this page.`,
      `The visit follows ${housing.charAt(0).toLowerCase()}${housing.slice(1)}`,
      `Paperwork names ${place.name} and the agreed ${serviceName.toLowerCase()} work.`,
    ],
  ];
  const process = pick(processes, seed >>> 7);
  const travel = [
    `${place.name} is in ${place.region}, ${place.country}. Travel is planned from Stockport, with nearby cover in ${near}.`,
    pop,
    housing,
    industry,
    `If the job sits just outside ${place.name}, say in ${near}, it is still quoted as one visit rather than split into a different product.`,
  ].filter(Boolean);
  const intro = keyword.intro ? keyword.intro.replace(/\s+/g, " ").trim() : "";
  const gas = keyword.gas ? GAS_SENTENCE : "";
  const packed = (kind === "keyword" || kind === "service")
    ? gmTownBlurb(kind, kind === "keyword" ? input.keyword : serviceSlug, townSlug)
    : null;
  const packedLead = packed && packed.blurb
    ? `${packed.h2 ? `<h2>${escapeHtml(packed.h2)}</h2>` : ""}<p>${escapeHtml(packed.blurb)}</p>${packed.cta ? `<p>${escapeHtml(packed.cta)}</p>` : ""}`
    : "";
  const paragraphs = [
    pick(openings, seed),
    intro,
    pick(scopes, seed >>> 5),
    travel.join(" "),
    SUBCONTRACT_SENTENCE,
    gas,
    `Ask for ${subject} in ${place.name} by phone on 07517806082 or through the contact form. Say which building, whether it is occupied, and whether you need ${visit}. The reply quotes the work as POA and names the ${serviceName.toLowerCase()} scope before a date is fixed.`,
  ].filter(Boolean);

  const hubHref = kind === "keyword"
    ? `/pages/keywords/${input.keyword}`
    : kind === "job"
      ? `/pages/jobs/${input.job}`
      : kind === "manufacturer"
        ? `/pages/manufacturers/${input.brand}`
        : `/pages/services/${serviceSlug}`;
  const relatedHtml = matrixRelatedHtml(catalogue, {
    kind,
    subject,
    serviceSlug,
    serviceName,
    townSlug,
    townName: place.name,
    keyword: input.keyword || "",
    job: input.job || "",
    brand: input.brand || "",
    selfPath: path,
  });
  const crumbItems = [
    { href: "/", label: "Home" },
    { href: hubHref, label: subject },
  ];
  if (isGmTown(townSlug)) {
    crumbItems.push({ href: `/pages/areas/${townSlug}`, label: place.name });
  }
  crumbItems.push({ label: `${subject} in ${place.name}` });
  const crumbs = breadcrumbHtml(crumbItems);

  const faqs = [
    [
      `Do you cover ${place.name} for ${subject}?`,
      `Yes. ${place.name} is covered from Stockport, including nearby ${near}. The quote is POA once the building and the scope are known.`,
    ],
    [
      `Who carries out ${subject} in ${place.name}?`,
      `${SUBCONTRACT_SENTENCE} ${gas}`.trim(),
    ],
    [
      `How is ${subject} priced in ${place.name}?`,
      `The quote is price on application. We do not publish a fixed price for ${subject} in ${place.name} because access, condition and the agreed scope change the visit.`,
    ],
  ];
  const faqHtml = faqs.map(([q, a]) => (
    `<details><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`
  )).join("");
  const focusHtml = focus.map((item) => `<li>${escapeHtml(item)}</li>`).join("");
  const processHtml = process.map((step) => `<li>${escapeHtml(step)}</li>`).join("");
  const bodyHtml = packedLead + paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("");
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "BreadcrumbList",
        itemListElement: [
          { "@type": "ListItem", position: 1, name: "Home", item: "https://icomplypropertyservices.co.uk/" },
          { "@type": "ListItem", position: 2, name: serviceName, item: `https://icomplypropertyservices.co.uk/pages/services/${serviceSlug}` },
          { "@type": "ListItem", position: 3, name: `${subject} in ${place.name}`, item: canonical },
        ],
      },
      {
        "@type": "Service",
        name: `${subject} in ${place.name}`,
        serviceType: serviceName,
        areaServed: place.name,
        description,
        provider: {
          "@type": "Organization",
          name: "iComply Property Services",
          url: "https://icomplypropertyservices.co.uk/",
          areaServed: "Stockport and the UK where the service is published",
        },
      },
      {
        "@type": "FAQPage",
        mainEntity: faqs.map(([q, a]) => ({
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
<meta name="robots" content="${robots}">
<link rel="canonical" href="${canonical}">
<link rel="stylesheet" href="/assets/css/site.css">
<script type="application/ld+json">${jsonLd(schema)}</script>
</head>
<body>
<header>
<p><a href="/">iComply Property Services</a> · <a href="/pages/services">Services</a> · <a href="/pages/jobs">Jobs</a> · <a href="/pages/keywords">Keywords</a> · <a href="/pages/areas">Areas</a> · <a href="/pages/manufacturers">Manufacturers</a> · <a href="/directories">Directories</a> · <a href="/contact">Contact</a></p>
</header>
<main>
${crumbs}
<h1>${escapeHtml(h1)}</h1>
<figure>
<img src="${imageSrc}" alt="${escapeHtml(imageAlt)}" width="1200" height="630">
</figure>
<p>${escapeHtml(serviceName)} arranged from Stockport for ${escapeHtml(place.name)} and ${escapeHtml(place.region)}.</p>
<article id="local-copy">
${bodyHtml}
<h2>How the ${escapeHtml(subject)} visit runs in ${escapeHtml(place.name)}</h2>
<ol>${processHtml}</ol>
<h2>What the ${escapeHtml(subject)} visit covers in ${escapeHtml(place.name)}</h2>
<ul>${focusHtml}</ul>
<h2>Quote</h2>
<p>The quote is price on application (POA). Published list prices on other iComply pages are not a price for this ${escapeHtml(place.name)} visit.</p>
${relatedHtml}
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
let variantPromise = null;

async function loadVariants(origin) {
  if (!variantPromise) {
    variantPromise = fetch(`${origin}/assets/matrix/variants.json`).then((response) => {
      if (!response.ok) throw new Error("variants");
      return response.json();
    }).catch((error) => {
      variantPromise = null;
      throw error;
    });
  }
  return variantPromise;
}

function variantResponse(rendered) {
  return new Response(rendered.html, {
    status: 200,
    headers: {
      "content-type": "text/html; charset=utf-8",
      "cache-control": "public, max-age=3600",
      "x-robots-tag": rendered.robots,
    },
  });
}

async function loadCatalogue(origin) {
  if (!cataloguePromise) {
    cataloguePromise = (async () => {
      const names = ["keywords", "places", "services", "nationwide", "manufacturers", "jobs"];
      const files = await Promise.all(names.map((name) => (
        fetch(`${origin}/assets/matrix/${name}.json`).then((response) => response.json())
      )));
      return {
        keywords: files[0],
        places: files[1],
        services: files[2],
        nationwide: files[3],
        manufacturers: files[4],
        jobs: files[5],
      };
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
  const sitemapMatch = path.match(/^\/matrix-sitemap\/(\d+)(?:\.xml)?$/);
  if (sitemapMatch) {
    try {
      const spec = await loadVariants(url.origin);
      const xml = renderVariantSitemap(spec, Number(sitemapMatch[1]));
      if (!xml) {
        return new Response("Not found", { status: 404, headers: { "content-type": "text/plain; charset=utf-8" } });
      }
      return new Response(xml, {
        status: 200,
        headers: {
          "content-type": "application/xml; charset=utf-8",
          "cache-control": "public, max-age=86400",
        },
      });
    } catch {
      return new Response("Variant sitemap unavailable", {
        status: 503,
        headers: { "content-type": "text/plain; charset=utf-8" },
      });
    }
  }
  const variantTown = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const variantHub = path.match(/^\/pages\/keywords\/([a-z0-9-]+)$/);
  const variantSlug = variantTown?.[1] || variantHub?.[1] || "";
  if (variantSlug.includes("--")) {
    try {
      const spec = await loadVariants(url.origin);
      return variantResponse(renderVariantPage({
        spec,
        keyword: variantSlug,
        town: variantTown ? variantTown[2] : "",
      }));
    } catch {
      return new Response("Variant page unavailable", {
        status: 503,
        headers: { "content-type": "text/plain; charset=utf-8" },
      });
    }
  }
  const manufacturerMatch = path.match(/^\/pages\/manufacturers\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const jobMatch = path.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const keywordMatch = path.match(/^\/pages\/keywords\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  const serviceMatch = path.match(/^\/pages\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (!manufacturerMatch && !jobMatch && !keywordMatch && !serviceMatch) {
    return context.next();
  }
  let catalogue;
  try {
    catalogue = await loadCatalogue(url.origin);
  } catch {
    return context.next();
  }
  let rendered = null;
  if (manufacturerMatch) {
    rendered = renderTownPage({
      kind: "manufacturer",
      brand: manufacturerMatch[1],
      town: manufacturerMatch[2],
      catalogue,
    });
  } else if (jobMatch) {
    rendered = renderTownPage({
      kind: "job",
      job: jobMatch[1],
      town: jobMatch[2],
      catalogue,
    });
  } else if (keywordMatch) {
    rendered = renderTownPage({
      kind: "keyword",
      keyword: keywordMatch[1],
      town: keywordMatch[2],
      catalogue,
    });
  } else {
    rendered = renderTownPage({
      kind: "service",
      service: serviceMatch[1],
      town: serviceMatch[2],
      catalogue,
    });
  }
  if (!rendered) return context.next();
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
    "/pages/keywords/:keyword",
    "/pages/keywords/:keyword/",
    "/pages/keywords/:keyword/:town",
    "/pages/keywords/:keyword/:town/",
    "/pages/manufacturers/:brand/:town",
    "/pages/manufacturers/:brand/:town/",
    "/pages/jobs/:job/:town",
    "/pages/jobs/:job/:town/",
    "/pages/:service/:town",
    "/pages/:service/:town/",
    "/matrix-sitemap/:part",
  ],
};
