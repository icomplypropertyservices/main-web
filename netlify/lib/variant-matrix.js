/**
 * Decode the compact keyword-variant catalogue written by
 * website/includes/keyword-variants.php. Stem is the fastest radix so every
 * existing job appears inside the first 10,000 variants of a service.
 */

import { breadcrumbHtml, isLocalTown, matrixRelatedHtml } from "./link-blocks.js";

const GAS_SENTENCE = "Gas work is carried out by Gas Safe registered engineers.";

function hashStr(value) {
  let h = 2166136261;
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h >>> 0;
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

function clampMeta(text) {
  const clean = String(text).replace(/\s+/g, " ").trim();
  if (clean.length <= 158 && clean.length >= 40) return clean;
  if (clean.length < 40) {
    return `${clean} Price on application for Greater Manchester.`.slice(0, 158);
  }
  const cut = clean.slice(0, 158).replace(/\s+\S*$/, "");
  return cut.length >= 40 ? cut : clean.slice(0, 158);
}

function jsonLd(value) {
  return JSON.stringify(value).replace(/</g, "\\u003c");
}

function words(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

export function variantTotalUrls(spec) {
  const services = (spec.services || []).length;
  const towns = (spec.towns || []).length;
  const per = spec.per_service || 0;
  return services * per * (1 + towns);
}

export function decodeVariant(service, spec, n) {
  const stems = service.stems;
  const mods = spec.modifiers;
  const auds = spec.audiences;
  const scopes = spec.scopes;
  const intents = spec.intents;
  const s = stems.length;
  let r = Math.floor(n / s);
  const stem = stems[n % s];
  const modifier = mods[r % mods.length];
  r = Math.floor(r / mods.length);
  const audience = auds[r % auds.length];
  r = Math.floor(r / auds.length);
  const scope = scopes[r % scopes.length];
  r = Math.floor(r / scopes.length);
  const intent = intents[r % intents.length];
  return { stem, modifier, audience, scope, intent, n };
}

export function variantSlug(serviceSlug, decoded) {
  return [
    serviceSlug,
    decoded.stem.slug,
    decoded.audience.slug,
    decoded.modifier.slug,
    decoded.scope.slug,
    decoded.intent.slug,
  ].join("--");
}

export function encodeVariant(service, spec, stemIndex, modIndex, audIndex, scopeIndex, intentIndex) {
  const s = service.stems.length;
  const m = spec.modifiers.length;
  const a = spec.audiences.length;
  const c = spec.scopes.length;
  return stemIndex + s * (modIndex + m * (audIndex + a * (scopeIndex + c * intentIndex)));
}

export function variantPath(spec, globalIndex) {
  const towns = spec.towns || [];
  const per = spec.per_service;
  const stride = 1 + towns.length;
  const perService = per * stride;
  const service = spec.services[Math.floor(globalIndex / perService)];
  const local = globalIndex % perService;
  const n = Math.floor(local / stride);
  const slot = local % stride;
  const decoded = decodeVariant(service, spec, n);
  const slug = variantSlug(service.slug, decoded);
  if (slot === 0) return `/pages/keywords/${slug}`;
  return `/pages/keywords/${slug}/${towns[slot - 1].slug}`;
}

export function parseVariantKeyword(spec, keyword) {
  if (!keyword || !String(keyword).includes("--")) return { ok: false };
  const parts = String(keyword).split("--");
  if (parts.length !== 6) return { ok: false };
  const [serviceSlug, stemSlug, audienceSlug, modifierSlug, scopeSlug, intentSlug] = parts;
  const token = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;
  if (![serviceSlug, stemSlug, audienceSlug, modifierSlug, scopeSlug, intentSlug].every((part) => token.test(part))) {
    return { ok: false };
  }
  const service = (spec.services || []).find((row) => row.slug === serviceSlug);
  if (!service) return { ok: false };
  const stemIndex = service.stems.findIndex((row) => row.slug === stemSlug);
  const audIndex = spec.audiences.findIndex((row) => row.slug === audienceSlug);
  const modIndex = spec.modifiers.findIndex((row) => row.slug === modifierSlug);
  const scopeIndex = spec.scopes.findIndex((row) => row.slug === scopeSlug);
  const intentIndex = spec.intents.findIndex((row) => row.slug === intentSlug);
  if (stemIndex < 0 || audIndex < 0 || modIndex < 0 || scopeIndex < 0 || intentIndex < 0) {
    return { ok: false, service };
  }
  const n = encodeVariant(service, spec, stemIndex, modIndex, audIndex, scopeIndex, intentIndex);
  const decoded = decodeVariant(service, spec, n);
  const round = decoded.stem.slug === stemSlug
    && decoded.audience.slug === audienceSlug
    && decoded.modifier.slug === modifierSlug
    && decoded.scope.slug === scopeSlug
    && decoded.intent.slug === intentSlug;
  return {
    ok: true,
    inSet: n >= 0 && n < spec.per_service && round,
    n,
    service,
    stem: service.stems[stemIndex],
    audience: spec.audiences[audIndex],
    modifier: spec.modifiers[modIndex],
    scope: spec.scopes[scopeIndex],
    intent: spec.intents[intentIndex],
  };
}

function variantMark(stemSlug) {
  return (hashStr(stemSlug) % 46656).toString(36).padStart(3, "0");
}

export function fitTitle({ stemLabel, stemSlug, aud, mod, scope, intent, place }) {
  const tail = ` ${aud} ${mod} ${scope} ${intent} in ${place} | iComply`;
  const full = `${stemLabel}${tail}`.replace(/\s+/g, " ").trim();
  if (full.length >= 15 && full.length <= 90) return full;
  const slugTitle = titleCaseSlug(stemSlug);
  const withSlug = `${slugTitle}${tail}`.replace(/\s+/g, " ").trim();
  if (withSlug.length >= 15 && withSlug.length <= 90) return withSlug;
  const mark = variantMark(stemSlug);
  const room = 90 - tail.length - mark.length - 1;
  const cut = (stemLabel || slugTitle).slice(0, Math.max(8, room)).trim();
  let title = `${cut} ${mark}${tail}`.replace(/\s+/g, " ").trim();
  if (title.length > 90) title = `${mark}${tail}`.replace(/\s+/g, " ").trim().slice(0, 90);
  if (title.length < 15) title = `${stemSlug} in ${place} | iComply`.slice(0, 90);
  return title;
}

function fitMeta(place, stemLabel, stemSlug, aud, mod, scope, intent) {
  const tail = " POA quote for Greater Manchester.";
  const mark = variantMark(stemSlug);
  let meta = `${place}: ${stemLabel} (${mark}) ${aud} ${mod} ${scope} ${intent}.${tail}`;
  if (meta.length > 158) {
    const room = 158 - (meta.length - stemLabel.length);
    const cut = stemLabel.slice(0, Math.max(8, room)).trim();
    meta = `${place}: ${cut} (${mark}) ${aud} ${mod} ${scope} ${intent}.${tail}`;
  }
  return clampMeta(meta);
}

function hubPlace(spec) {
  const names = (spec.towns || []).slice(0, 10).map((town) => town.name);
  return {
    slug: "",
    name: "Greater Manchester",
    districts: names.length ? names.join(", ") : "Greater Manchester",
    stock: "terraces, mills, shops, offices and industrial estates across the boroughs",
    focus: "landlords, agents and commercial sites",
  };
}

function localSentences(place, parsed) {
  const name = place.name;
  const subject = parsed.stem.label;
  const service = parsed.service.label;
  const aud = parsed.audience.label;
  const mod = parsed.modifier.label;
  const scope = parsed.scope.label;
  const intent = parsed.intent.label;
  const districts = place.districts || name;
  const stock = place.stock || name;
  const focus = place.focus || name;
  return [
    `${name} is covered from Stockport for ${subject}. The building stock is ${stock}.`,
    `${name} outward codes on this page are ${districts}. ${subject} for ${aud} uses that place, not a UK-wide script.`,
    `${focus} is the local note for ${name}. ${mod} does not move the address outside Greater Manchester.`,
    `${service} in ${name} follows the ${scope} scope. ${districts} is how this ${name} page is told apart from the next town.`,
    `${intent} is the request type for ${subject} in ${name}. The ${stock} can change access, so the visit is quoted after the scope.`,
    `Paperwork for ${subject} in ${name} names ${districts}, the access and the ${aud} who instructed iComply.`,
    `A ${name} page for ${subject} sits with the other Greater Manchester towns. ${focus} stays specific to ${name}.`,
    `${mod} ${scope} in ${name} is arranged around ${stock}. Greater Manchester is the limit of this matrix.`,
    `Clients in ${name} ask for ${subject} when the building is in ${districts}. Neighbouring towns have their own pages.`,
    `The ${name} ${intent} for ${aud} still needs a written ${scope} scope before a date is fixed.`,
    `${service} visits in ${name} leave the result with the instructing client. ${districts} is written on the note.`,
    `If the building is just outside ${name} but still in Greater Manchester, use the town page that matches the address. This page is ${name}.`,
  ];
}

function pageHtml(options) {
  const {
    spec, parsed, place, path, indexable, keyword,
  } = options;
  const site = spec.site || "https://icomplypropertyservices.co.uk";
  const robots = indexable ? "index, follow" : "noindex, follow";
  const subject = parsed && parsed.ok ? parsed.stem.label : "Property visit";
  const service = parsed && parsed.service ? parsed.service : { slug: "building-maintenance", label: "Property services", image: "building-maintenance", gas: false };
  const audience = parsed && parsed.ok ? parsed.audience : { label: "clients", title: "Local", note: "The quote is price on application." };
  const modifier = parsed && parsed.ok ? parsed.modifier : { label: "Local", title: "Local", note: "Coverage on this matrix is Greater Manchester. The quote is price on application." };
  const scope = parsed && parsed.ok ? parsed.scope : { label: "visit", title: "Visit", note: "The scope is agreed in writing. The quote is price on application." };
  const intent = parsed && parsed.ok ? parsed.intent : { label: "Quote", title: "Quote", note: "The figure is price on application. No fee is published here." };
  const stem = parsed && parsed.ok ? parsed.stem : { slug: "enquiry", label: subject, gas: false };
  const gas = Boolean(service.gas || stem.gas);
  const placeName = place.name;
  const canonical = `${site}${path}`;
  const title = fitTitle({
    stemLabel: subject,
    stemSlug: stem.slug || "enquiry",
    aud: audience.title,
    mod: modifier.title,
    scope: scope.title,
    intent: intent.title,
    place: placeName,
  });
  const description = fitMeta(placeName, subject, stem.slug || "enquiry", audience.title, modifier.title, scope.title, intent.title);
  const h1 = parsed && parsed.ok
    ? `${subject} for ${audience.label} in ${placeName}: ${modifier.label} ${scope.label} (${intent.label})`
    : `Property enquiry in ${placeName}`;
  const image = service.image || service.slug || "building-maintenance";
  const imageSrc = `/assets/images/services/${image}.jpg`;
  const imageAlt = `${subject} in ${placeName}`;
  const sentences = parsed && parsed.ok
    ? localSentences(place, { ...parsed, service })
    : [
      `${placeName} is in Greater Manchester. This address did not match a published variant, so the page is not indexed.`,
      `You can still ask for a visit in ${placeName}. The quote is price on application after the scope is clear.`,
      `Published variant pages use a double hyphen in the keyword slug and a Greater Manchester town.`,
      `iComply arranges the visit from the Stockport office. The figure is agreed after the building is seen.`,
      `Say which building in ${placeName} you mean and what the visit has to cover.`,
      `There is no price list on this page. Cheap, emergency and next day are enquiry types on the published variants, not a published rate.`,
      `${placeName} stays on the Greater Manchester list when the town is one of the published area pages.`,
    ];
  const extras = [
    modifier.note,
    audience.note,
    scope.note,
    intent.note,
    gas ? GAS_SENTENCE : "",
    `Phone 07517806082 or use the contact form. Name the ${placeName} building, whether it is occupied, and the ${scope.label} you want quoted.`,
  ].filter(Boolean);
  const seed = hashStr(`${path}|${placeName}`);
  const rotated = sentences.slice(seed % Math.max(1, sentences.length)).concat(sentences.slice(0, seed % Math.max(1, sentences.length)));
  const body = [...rotated, ...extras];
  const labels = {};
  for (const row of spec.services || []) {
    if (row && row.slug && row.label) labels[row.slug] = row.label;
  }
  const variantSlugValue = parsed && parsed.ok ? variantSlug(service.slug, parsed) : String(keyword || "");
  const variantCatalogue = {
    keywords: {},
    services: { labels, keywords: {}, excluded: ["barriers", "aov-air-handling"] },
    jobs: {},
    manufacturers: {},
  };
  const relatedHtml = matrixRelatedHtml(variantCatalogue, {
    kind: "variant",
    subject,
    serviceSlug: service.slug,
    serviceName: service.label,
    townSlug: place.slug || "",
    townName: placeName,
    keyword: variantSlugValue,
    selfPath: path,
    towns: spec.towns || [],
  });
  const crumbItems = [
    { href: "/", label: "Home" },
    { href: "/pages/keywords", label: "Guides" },
    { href: `/pages/keywords/${variantSlugValue}`, label: subject },
  ];
  if (isLocalTown(place.slug || "")) {
    crumbItems.push({ href: `/pages/areas/${place.slug}`, label: placeName });
  }
  crumbItems.push({ label: h1 });
  const crumbs = breadcrumbHtml(crumbItems);
  const faqs = [
    [`Do you cover ${placeName} for ${subject}?`, `Yes, where ${placeName} is in Greater Manchester. The quote is price on application once the building and the ${scope.label} scope are known.`],
    [`Who carries out ${subject} in ${placeName}?`, gas ? GAS_SENTENCE : `The ${placeName} visit is scoped to the building named on the enquiry. The figure follows that scope.`],
    [`How is ${subject} priced in ${placeName}?`, `The quote is price on application. ${modifier.slug === "cheap" ? "This page does not publish a low rate." : "No fixed price is published for this visit."}`],
  ];
  const schema = {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "BreadcrumbList",
        itemListElement: [
          { "@type": "ListItem", position: 1, name: "Home", item: `${site}/` },
          { "@type": "ListItem", position: 2, name: service.label, item: `${site}/pages/services/${service.slug}` },
          { "@type": "ListItem", position: 3, name: h1, item: canonical },
        ],
      },
      {
        "@type": "Service",
        name: h1,
        serviceType: service.label,
        areaServed: placeName,
        description,
        provider: {
          "@type": "Organization",
          name: "iComply Property Services",
          url: `${site}/`,
        },
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
<meta property="og:type" content="website">
<meta property="og:title" content="${escapeHtml(title)}">
<meta property="og:description" content="${escapeHtml(description)}">
<meta property="og:url" content="${escapeHtml(canonical)}">
<meta property="og:image" content="${escapeHtml(`${String(site).replace(/^http:/, "https:")}${imageSrc}`)}">
<link rel="canonical" href="${escapeHtml(canonical)}">
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
<img src="${imageSrc}" alt="${escapeHtml(imageAlt)}" width="1400" height="900">
</figure>
<p>${escapeHtml(service.label)} in ${escapeHtml(placeName)}, Greater Manchester. Quotes are price on application.</p>
<article id="local-copy">
${body.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("\n")}
<h2>How the ${escapeHtml(subject)} visit runs in ${escapeHtml(placeName)}</h2>
<ol>
<li>Send the ${escapeHtml(placeName)} address and what ${escapeHtml(subject)} has to cover.</li>
<li>iComply confirms the ${escapeHtml(scope.label)} scope in writing. The quote is POA.</li>
<li>${escapeHtml(modifier.note)}</li>
<li>The result is handed to the person who instructed the visit.</li>
</ol>
<h2>Questions about ${escapeHtml(placeName)}</h2>
${faqs.map(([q, a]) => `<details><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`).join("")}
${relatedHtml}
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

export function renderVariantPage({ spec, keyword, town }) {
  const townSlug = String(town || "");
  if (townSlug && !isLocalTown(townSlug)) {
    const keywordSlug = String(keyword || "enquiry");
    const safeSlug = /^[a-z0-9-]+$/.test(keywordSlug) ? keywordSlug : "enquiry";
    const location = `/pages/keywords/${safeSlug}`;
    return {
      html: "",
      title: "",
      description: "",
      h1: "",
      canonical: location,
      robots: "noindex, nofollow",
      indexable: false,
      path: location,
      status: 301,
      location,
      words: 0,
    };
  }
  const parsed = parseVariantKeyword(spec, keyword);
  const knownTown = townSlug
    ? (spec.towns || []).find((row) => row.slug === townSlug) || null
    : null;
  const place = townSlug
    ? (knownTown || {
      slug: /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(townSlug) ? townSlug : "",
      name: titleCaseSlug(townSlug.replace(/[^a-z0-9-]+/g, "")) || "Greater Manchester",
      districts: titleCaseSlug(townSlug.replace(/[^a-z0-9-]+/g, "")) || "Greater Manchester",
      stock: "the building at the address given",
      focus: "the instructing client",
    })
    : hubPlace(spec);
  const slug = parsed.ok ? variantSlug(parsed.service.slug, parsed) : String(keyword || "enquiry");
  const safeSlug = /^[a-z0-9-]+$/.test(slug) ? slug : "enquiry";
  const safeTown = place.slug && /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(place.slug) ? place.slug : "";
  const path = safeTown ? `/pages/keywords/${safeSlug}/${safeTown}` : `/pages/keywords/${safeSlug}`;
  const indexable = Boolean(parsed.ok && parsed.inSet && (townSlug === "" || knownTown));
  return pageHtml({ spec, parsed, place, path, indexable, keyword });
}

export function renderVariantSitemap(spec, partIndex) {
  const total = variantTotalUrls(spec);
  const chunk = spec.chunk || 45000;
  if (!Number.isInteger(partIndex) || partIndex < 0) return null;
  const start = partIndex * chunk;
  if (start >= total) return null;
  const end = Math.min(total, start + chunk);
  const site = spec.site || "https://icomplypropertyservices.co.uk";
  const lines = [
    '<?xml version="1.0" encoding="UTF-8"?>',
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
  ];
  for (let g = start; g < end; g++) {
    const locPath = variantPath(spec, g);
    const parts = locPath.split("/");
    if (parts.length >= 5 && !isLocalTown(parts[4])) continue;
    lines.push(`  <url><loc>${site}${locPath}</loc><priority>0.5</priority></url>`);
  }
  lines.push("</urlset>");
  return lines.join("\n") + "\n";
}
