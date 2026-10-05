/**
 * Manchester electrical P0 hubs and GM-core town pages.
 * Spec: /assets/matrix/manchester-electrical-p0.json (also read from disk in tests).
 * Body is unique per intent and town. Quotes stay price on application.
 */

const SITE = "https://icomplypropertyservices.co.uk";

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

function fill(template, vars) {
  return String(template).replace(/\{(\w+)\}/g, (_, key) => (vars[key] == null ? "" : String(vars[key])));
}

function wordCount(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

function metaDescription(name, where) {
  const bits = [
    " Landlords, agents and commercial sites.",
    " Qualified electricians to BS 7671.",
    " Written scope from Stockport.",
    " Request a quote — POA.",
    " Greater Manchester cover.",
  ];
  const fillers = [" POA.", " SK2 5DE.", " Offerton.", " Quote POA.", " GM electricians."];
  let text = `${name} in ${where}.`;
  if (text.length > 160) text = `${name}. ${where}.`;
  for (const bit of bits.concat(fillers)) {
    if (text.length >= 140 && text.length <= 160) break;
    if (text.length + bit.length <= 160) text += bit;
  }
  return text;
}

function pageTitle(name, where, isHub) {
  const brand = " | iComply Property Services";
  const short = " — iComply";
  const base = isHub ? name : `${name} in ${where}`;
  if ((base + brand).length <= 70) return base + brand;
  return base + short;
}

function intentBySlug(spec, slug) {
  return (spec.intents || []).find((item) => item.slug === slug) || null;
}

function townBySlug(spec, slug) {
  return (spec.towns || []).find((item) => item.slug === slug) || null;
}

/**
 * @returns {string|null}
 */
export function renderManchesterElectricalFromSpec(spec, input) {
  const surface = input.surface === "job" ? "job" : "keyword";
  const intent = intentBySlug(spec, input.slug || "");
  if (!intent) return null;
  if (surface === "job" && intent.kind === "keyword") return null;
  if (surface === "keyword" && intent.kind === "job") return null;
  const isHub = !input.town;
  const town = isHub ? null : townBySlug(spec, input.town);
  if (!isHub && !town) return null;

  const where = isHub ? "Greater Manchester" : town.name;
  const fact = isHub
    ? "Manchester is the primary hub city, and the team is based in Offerton, Stockport"
    : town.fact;
  const related = intentBySlug(spec, intent.related);
  const relatedName = related ? related.name : "EICR";
  const relatedSlug = related ? related.slug : "eicr";
  const vars = {
    intent: intent.name,
    town: where,
    local: fact,
    nap: spec.nap,
    phone: spec.phone,
    related: relatedName,
  };
  const paragraphs = [
    fill(spec.intro, vars),
    intent.angle,
    `${where} is on the Greater Manchester electrical matrix. ${fact}. Travel for ${intent.name} is planned from ${spec.nap}. Landlords, agents and commercial occupiers can combine certificates, fault finding, board changes and rewires on one enquiry, with each item scoped in writing.`,
  ];
  const blocks = spec.blocks || [];
  const seed = hashStr(`${surface}|${intent.slug}|${isHub ? "hub" : town.slug}`);
  const order = blocks.map((_, index) => index);
  for (let i = order.length - 1; i > 0; i--) {
    const j = (seed + i * 17) % (i + 1);
    const swap = order[i];
    order[i] = order[j];
    order[j] = swap;
  }
  for (const index of order) paragraphs.push(fill(blocks[index], vars));
  paragraphs.push(fill(spec.closer, vars));

  const path = isHub
    ? `/pages/${surface === "job" ? "jobs" : "keywords"}/${intent.slug}`
    : `/pages/${surface === "job" ? "jobs" : "keywords"}/${intent.slug}/${town.slug}`;
  const canonical = SITE + path;
  const title = pageTitle(intent.name, where, isHub);
  const description = metaDescription(intent.name, where);
  const h1 = isHub ? `${intent.name} across Greater Manchester` : `${intent.name} in ${where}`;
  const images = spec.images || [];
  const picked = [];
  for (let step = 0; step < images.length && picked.length < 3; step++) {
    picked.push(images[(seed + step) % images.length]);
  }
  while (picked.length < 3) picked.push(images[0] || "/assets/images/services/electrical.jpg");
  const ogImage = SITE + picked[0];
  const areaSlug = isHub ? "manchester" : town.slug;
  const siblingSurface = surface === "job" ? "keywords" : "jobs";
  const sibling = intent.kind === "both" ? `/pages/${siblingSurface}/${intent.slug}${isHub ? "" : `/${town.slug}`}` : "";

  const faqs = (spec.faqs || []).map((faq) => [fill(faq.q, vars), fill(faq.a, vars)]);
  if (intent.slug === "nic-electrician") {
    faqs.push([
      "Are you NICEIC registered?",
      "No. iComply does not claim NICEIC, NAPIT or Elecsa membership. Ask what the attending electrician can show, and we will say if a named scheme contractor is required.",
    ]);
  }
  if (intent.slug === "eicr") {
    faqs.push([
      "Is there a published EICR price?",
      "The published EICR list price is £249 for a typical North West 6-bed HMO. Other domestic sizes and commercial EICRs stay price on application.",
    ]);
  }

  const bodyText = paragraphs.join(" ");
  const faqHtml = faqs.map(([q, a]) => `<details><summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p></details>`).join("");
  const imageHtml = picked.map((src, index) => (
    `<figure><img src="${escapeHtml(src)}" alt="${escapeHtml(`${intent.name} in ${where} — photograph ${index + 1}`)}" width="1200" height="800"><figcaption>${escapeHtml(`${intent.name} · ${where}`)}</figcaption></figure>`
  )).join("");
  const paraHtml = paragraphs.map((paragraph) => `<p>${escapeHtml(paragraph)}</p>`).join("");
  const faqSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: faqs.map(([q, a]) => ({
      "@type": "Question",
      name: q,
      acceptedAnswer: { "@type": "Answer", text: a },
    })),
  };
  const links = [
    `<a href="${escapeHtml(spec.service_path || "/pages/services/electrical")}">Electrical services</a>`,
    `<a href="/pages/keywords/${escapeHtml(relatedSlug)}">${escapeHtml(relatedName)}</a>`,
    `<a href="/pages/areas/${escapeHtml(areaSlug)}">${escapeHtml(where === "Greater Manchester" ? "Manchester" : where)} area</a>`,
    `<a href="/pages/keywords/${escapeHtml(intent.slug)}/manchester">${escapeHtml(intent.name)} in Manchester</a>`,
  ];
  if (sibling) links.push(`<a href="${escapeHtml(sibling)}">${escapeHtml(surface === "job" ? "Keyword guide" : "Job page")}</a>`);

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
<meta property="og:locale" content="en_GB">
<meta property="og:site_name" content="iComply Property Services">
<meta property="og:title" content="${escapeHtml(title)}">
<meta property="og:description" content="${escapeHtml(description)}">
<meta property="og:url" content="${canonical}">
<meta property="og:image" content="${escapeHtml(ogImage)}">
<meta property="og:image:alt" content="${escapeHtml(`${intent.name} in ${where}`)}">
</head>
<body>
<header>
<p><a href="/">iComply Property Services</a> · <a href="/pages/services/electrical">Electrical</a> · <a href="/pages/keywords">Keywords</a> · <a href="/pages/jobs">Jobs</a> · <a href="/pages/areas">Areas</a> · <a href="/contact">Contact</a></p>
<p>${escapeHtml(spec.nap)} · ${escapeHtml(spec.phone)}</p>
</header>
<main>
<p><a href="/">Home</a> / <a href="${surface === "job" ? "/pages/jobs" : "/pages/keywords"}">${surface === "job" ? "Jobs" : "Keywords"}</a> / ${escapeHtml(intent.name)}${isHub ? "" : ` / ${escapeHtml(where)}`}</p>
<h1>${escapeHtml(h1)}</h1>
<article id="guide">
${paraHtml}
<h2>Photographs</h2>
${imageHtml}
<h2>${escapeHtml(intent.name)} FAQ</h2>
${faqHtml}
</article>
<p>${links.join(" · ")}</p>
</main>
<footer>
<p>iComply Property Services, ${escapeHtml(spec.nap)}. Phone ${escapeHtml(spec.phone)}. Electrical quotes are price on application.</p>
</footer>
<script type="application/ld+json">${JSON.stringify(faqSchema).replace(/</g, "\\u003c")}</script>
</body>
</html>`;

  return {
    html,
    path,
    canonical,
    title,
    description,
    words: wordCount(bodyText),
    images: picked.length,
  };
}

let specPromise = null;

export async function renderManchesterElectricalPage({ origin, surface, slug, town }) {
  if (!specPromise) {
    specPromise = fetch(`${origin}/assets/matrix/manchester-electrical-p0.json`).then((response) => {
      if (!response.ok) throw new Error("p0 spec missing");
      return response.json();
    });
  }
  const spec = await specPromise;
  const rendered = renderManchesterElectricalFromSpec(spec, { surface, slug, town });
  return rendered ? rendered.html : null;
}
