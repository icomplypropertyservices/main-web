/**
 * Shared quality floor for edge HTML: visible breadcrumbs, FAQ schema,
 * a real-photo slot, image credits on content images, and a local essay
 * written from service facts.
 */
import copy from "../../website/data/homeowner-copy.json" with { type: "json" };
import services from "../../website/data/services.json" with { type: "json" };
import creditsDoc from "../../website/data/hero-image-credits.json" with { type: "json" };
import { sanitizeInternalLinks } from "./internal-links.js";

const CREDIT = "iComply Property Services";
const REAL_JOB = new Set([
  "index", "company", "packages", "resources", "manufacturers", "areas",
  "about", "privacy", "terms", "renewals", "contact", "home",
]);
const BARRIER_BRANDS = new Set([
  "apt-controls", "atg-access", "avon-barrier", "dea", "designa", "eagle",
  "frontier-pitts", "heald", "hysecurity", "key-automation", "life", "newgate", "skidata",
]);
const creditBySrc = new Map();
for (const row of Array.isArray(creditsDoc) ? creditsDoc : []) {
  if (row && row.file) creditBySrc.set(String(row.file), row);
}

export function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function pagePath(html) {
  const canon = String(html).match(/<link[^>]+rel="canonical"[^>]+href="([^"]+)"/i)
    || String(html).match(/<link[^>]+href="([^"]+)"[^>]+rel="canonical"/i);
  if (canon) {
    try { return new URL(canon[1], "https://icomplypropertyservices.co.uk").pathname.replace(/\/$/, "") || "/"; }
    catch { return "/"; }
  }
  return "/";
}

function plainH1(html) {
  const m = String(html).match(/<h1\b[^>]*>([\s\S]*?)<\/h1>/i);
  if (!m) return "Property work";
  return m[1].replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim() || "Property work";
}

function titleTown(slug) {
  const small = new Set(["under", "upon", "le", "in", "the", "of", "on", "de", "y"]);
  return String(slug || "stockport").split("-").map((word, index) => {
    if (word === "st") return "St";
    if (index > 0 && small.has(word)) return word;
    return word.charAt(0).toUpperCase() + word.slice(1);
  }).join(" ");
}

export function serviceKeyFromPath(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  if (parts[0] !== "pages") return "default";
  if (parts[1] === "services") return copy.services[parts[2]] ? parts[2] : "default";
  if (parts[1] === "aov") return "aov";
  if (parts[1] === "barriers") return "barriers";
  if (parts[1] === "manufacturers" && BARRIER_BRANDS.has(parts[2] || "")) return "barriers";
  if (copy.services[parts[1]]) return parts[1];
  return "default";
}

function placeFromPath(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  if (parts[0] !== "pages") return "Stockport";
  const head = parts[1] || "";
  if (["keywords", "jobs", "manufacturers"].includes(head) && parts[3]) return titleTown(parts[3]);
  if (head === "areas" && parts[2]) return titleTown(parts[2]);
  if (head === "services") return "Stockport";
  if (parts[2] && !["keywords", "jobs", "manufacturers", "areas", "resources", "packages"].includes(head)) {
    return titleTown(parts[2]);
  }
  return "Stockport";
}

function shortSubject(subject, key) {
  if (key && services[key]) return services[key];
  const cleaned = String(subject || "").replace(/\s+in\s+.+$/i, "").trim();
  return cleaned || "the work";
}

function wordCount(text) {
  return String(text).trim().split(/\s+/).filter(Boolean).length;
}

function localise(text, place, subject) {
  const town = place || "Stockport";
  const job = subject || "the work";
  const filled = String(text).replaceAll("{place}", town).replaceAll("{subject}", job);
  return filled.split(/(?<=[.!?])\s+/).map((sentence) => {
    let line = sentence.trim();
    if (!line) return "";
    if (!line.toLowerCase().includes(town.toLowerCase())) {
      line = `In ${town}, ${line.charAt(0).toLowerCase()}${line.slice(1)}`;
    }
    return line;
  }).filter(Boolean).join(" ");
}

function packFor(key) {
  const resolved = copy.services[key] ? key : "default";
  return {
    key: resolved,
    paragraphs: copy.services[resolved] || [],
    notes: (copy.notes && copy.notes[resolved]) || [],
    facts: (copy.facts && copy.facts[resolved]) || (copy.facts && copy.facts.default) || [],
  };
}

export function essayHtml(path, subject, place) {
  const key = serviceKeyFromPath(path);
  const pack = packFor(key);
  const job = shortSubject(subject, pack.key);
  const town = place || "Stockport";
  const blocks = [];
  const pushAll = (list) => {
    for (const item of list || []) blocks.push(localise(item, town, job));
  };
  pushAll(pack.paragraphs);
  pushAll(pack.notes);
  pushAll(pack.facts);
  return blocks.map((text) => `<p>${escapeHtml(text)}</p>`).join("");
}

function slotSrc(path) {
  const parts = String(path || "").split("/").filter(Boolean);
  const head = parts[0] === "pages" ? (parts[1] || "") : (parts[0] || "");
  const leaf = (parts[parts.length - 1] || "home").toLowerCase();
  const known = {
    aov: "aov-air-handling",
    barriers: "barriers",
    fencing: "fencing",
    drainage: "drainage",
    driveways: "driveways",
    demolition: "demolition",
  };
  if (head === "services" && parts[2] && services[parts[2]]) return `/assets/images/services/${parts[2]}.jpg`;
  if (known[head]) return `/assets/images/services/${known[head]}.jpg`;
  if (parts[0] === "pages" && services[head] && !["keywords", "jobs", "areas", "manufacturers"].includes(head)) {
    return `/assets/images/services/${head}.jpg`;
  }
  if (BARRIER_BRANDS.has(leaf)) return "/assets/images/services/barriers.jpg";
  if (head === "manufacturers" || leaf === "manufacturers") return "/assets/images/real-job/manufacturers.jpg";
  if (leaf === "jobs" || head === "jobs") return "/assets/images/services/building-maintenance.jpg";
  if (leaf === "become-a-subcontractor" || path.includes("become-a-subcontractor")) {
    return "/assets/images/services/building-maintenance.jpg";
  }
  if (leaf === "products" || head === "products" || leaf === "directories" || head === "directories") {
    return leaf === "directories" || head === "directories"
      ? "/assets/images/real-job/resources.jpg"
      : "/assets/images/services/building-maintenance.jpg";
  }
  if (REAL_JOB.has(leaf)) return `/assets/images/real-job/${leaf}.jpg`;
  if (REAL_JOB.has(head)) return `/assets/images/real-job/${head === "index" ? "home" : head}.jpg`;
  if (path === "/" || path === "") return "/assets/images/real-job/home.jpg";
  return "/assets/images/services/building-maintenance.jpg";
}

function crumbBlock(path, subject) {
  const parts = String(path).split("/").filter(Boolean);
  const items = [{ href: "/", label: "Home" }];
  const head = parts[0] === "pages" ? (parts[1] || "") : "";
  const pushHub = (href, label) => items.push({ href, label });
  if (head === "services") {
    pushHub("/pages/services", "Services");
  } else if (head === "keywords") {
    pushHub("/pages/keywords", "Guides");
    if (parts[2] && parts[3]) pushHub(`/pages/keywords/${parts[2]}`, parts[2].replace(/-/g, " "));
  } else if (head === "jobs") {
    pushHub("/pages/jobs", "Jobs");
    if (parts[2] && parts[3]) pushHub(`/pages/jobs/${parts[2]}`, parts[2].replace(/-/g, " "));
  } else if (head === "areas") {
    pushHub("/pages/areas", "Areas");
  } else if (head === "manufacturers") {
    pushHub("/pages/manufacturers", "Manufacturers");
    if (parts[2] && parts[3]) pushHub(`/pages/manufacturers/${parts[2]}`, parts[2].replace(/-/g, " "));
  } else if (head === "aov" || head === "barriers") {
    pushHub(`/pages/${head}`, head === "aov" ? "AOV" : "Barriers");
  } else if (services[head]) {
    pushHub(`/pages/services/${head}`, services[head]);
  } else if (head) {
    pushHub(`/pages/${head}`, head.replace(/-/g, " "));
  }
  items.push({ label: subject || "This page" });
  const lis = items.map((item, index) => {
    const name = escapeHtml(item.label);
    const href = item.href ? escapeHtml(item.href) : "";
    const body = href && index !== items.length - 1
      ? `<a itemprop="item" href="${href}"><span itemprop="name">${name}</span></a>`
      : `<span itemprop="name">${name}</span>`;
    return `<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">${body}<meta itemprop="position" content="${index + 1}"></li>`;
  }).join("");
  const origin = "https://icomplypropertyservices.co.uk";
  const ld = {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: items.map((item, index) => ({
      "@type": "ListItem",
      position: index + 1,
      name: item.label,
      item: origin + (item.href || path || "/"),
    })),
  };
  return `<nav class="breadcrumb" aria-label="Breadcrumb"><ol itemscope itemtype="https://schema.org/BreadcrumbList">${lis}</ol></nav>`
    + `<script type="application/ld+json">${JSON.stringify(ld)}</script>`;
}

function faqBlock(subject, place) {
  const q1 = `How is ${subject} booked?`;
  const a1 = `Call 07517 806082 or WhatsApp the same number. The office is 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. You get a written quote before work is agreed.`;
  const q2 = `Who attends in ${place}?`;
  const a2 = `iComply arranges the visit from Stockport. Gas work is carried out by Gas Safe registered engineers.`;
  const ld = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: [
      { "@type": "Question", name: q1, acceptedAnswer: { "@type": "Answer", text: a1 } },
      { "@type": "Question", name: q2, acceptedAnswer: { "@type": "Answer", text: a2 } },
    ],
  };
  return `<section class="faq" id="quality-floor-faq"><h2>Questions</h2>`
    + `<details><summary>${escapeHtml(q1)}</summary><p>${escapeHtml(a1)}</p></details>`
    + `<details><summary>${escapeHtml(q2)}</summary><p>${escapeHtml(a2)}</p></details>`
    + `</section><script type="application/ld+json">${JSON.stringify(ld)}</script>`;
}

function creditName(src) {
  const path = String(src || "").split("?")[0];
  const row = creditBySrc.get(path);
  if (!row) return CREDIT;
  const source = String(row.source || "");
  if (!/^https?:/i.test(source)) return CREDIT;
  const artist = String(row.artist || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
  const license = String(row.license || "").replace(/<[^>]+>/g, " ").trim();
  const name = (artist || "Wikimedia Commons").slice(0, 120);
  return license ? `${name} (${license})` : name;
}

function skipImg(attrs) {
  const src = (String(attrs).match(/\bsrc\s*=\s*"([^"]*)"/i) || [])[1] || "";
  const alt = (String(attrs).match(/\balt\s*=\s*"([^"]*)"/i) || [])[1] || "";
  const cls = (String(attrs).match(/\bclass\s*=\s*"([^"]*)"/i) || [])[1] || "";
  const width = Number((String(attrs).match(/\bwidth\s*=\s*"(\d+)"/i) || [])[1] || 0);
  if (/\.svg(?:$|\?)/i.test(src) || /favicon|\/logo/i.test(src)) return true;
  if (/\blogo\b|\bicon\b|\bbrand\b|mega-logo/i.test(cls)) return true;
  if (/\blogo\b/i.test(alt)) return true;
  if (width > 0 && width <= 64) return true;
  return false;
}

function stampCredits(html) {
  const held = [];
  const masked = String(html).replace(/<(header|nav|footer)\b[^>]*>[\s\S]*?<\/\1>/gi, (block) => {
    const cleaned = block
      .replace(/\sdata-credit="[^"]*"/gi, "")
      .replace(/\sdata-credit-stamped="[^"]*"/gi, "")
      .replace(/<span class="image-credit">[\s\S]*?<\/span>/gi, "");
    held.push(cleaned);
    return `\u0000HOLD${held.length - 1}\u0000`;
  });
  const stamped = masked.replace(/<img\b([^>]*?)>/gi, (tag, attrs) => {
    let next = String(attrs).replace(/\s*\/\s*$/, "");
    if (skipImg(next) || /\bdata-credit-stamped=/.test(next)) return `<img${next}>`;
    const src = (next.match(/\bsrc\s*=\s*"([^"]*)"/i) || [])[1] || "";
    const name = creditName(src);
    if (/\bdata-credit=/.test(next)) {
      next = next.replace(/\bdata-credit="[^"]*"/i, `data-credit="${escapeHtml(name)}"`);
    } else {
      next += ` data-credit="${escapeHtml(name)}"`;
    }
    next += ` data-credit-stamped="1"`;
    return `<img${next}><span class="image-credit">Image credit: ${escapeHtml(name)}</span>`;
  });
  return stamped.replace(/\u0000HOLD(\d+)\u0000/g, (_, index) => held[Number(index)]);
}

function photoHtml(path, subject) {
  const src = slotSrc(path);
  const name = creditName(src);
  return `<figure class="real-job-photo" data-pe-slot="real-photo" data-real-job-photo="owner"><img src="${escapeHtml(src)}" alt="${escapeHtml(`Completed ${subject} by ${name}`)}" data-pe-slot="real-photo" data-credit="${escapeHtml(name)}" data-credit-stamped="1" width="1200" height="800"><figcaption>Image credit: ${escapeHtml(name)}</figcaption></figure>`;
}

function scrubFixedPricing(html, path) {
  if (!/\/(?:fencing|drainage)(?:\/|$)/.test(path)) return html;
  return String(html).replace(/fixed pricing/gi, "a written quote");
}

export function applyQualityFloor(html, ctx = {}) {
  let out = String(html || "");
  if (!/<html[\s>]/i.test(out) && !/<body[\s>]/i.test(out)) return out;
  const path = ctx.path || pagePath(out);
  const subject = ctx.subject || plainH1(out);
  const place = ctx.place || placeFromPath(path);
  if (!out.includes('data-quality-floor="1"')) {
    const srcBlock = photoHtml(path, subject);
    const needsCrumbs = !/itemtype="https:\/\/schema\.org\/BreadcrumbList"/i.test(out);
    const needsFaq = !/"@type"\s*:\s*"FAQPage"/i.test(out) && !/itemtype="https:\/\/schema\.org\/FAQPage"/i.test(out);
    const block = `<section data-quality-floor="1" id="local-essay">`
      + (needsCrumbs ? crumbBlock(path, subject) : "")
      + srcBlock
      + essayHtml(path, subject, place)
      + (needsFaq ? faqBlock(subject, place) : "")
      + `</section>`;
    const article = out.toLowerCase().lastIndexOf("</article>");
    if (article !== -1) out = out.slice(0, article) + block + out.slice(article);
    else {
      const main = out.toLowerCase().lastIndexOf("</main>");
      if (main !== -1) out = out.slice(0, main) + block + out.slice(main);
      else {
        const body = out.toLowerCase().lastIndexOf("</body>");
        if (body !== -1) out = out.slice(0, body) + block + out.slice(body);
        else out += block;
      }
    }
  } else if (!out.includes('data-pe-slot="real-photo"')) {
    const figure = photoHtml(path, subject);
    const body = out.toLowerCase().lastIndexOf("</body>");
    out = body === -1 ? out + figure : out.slice(0, body) + figure + out.slice(body);
  }
  out = sanitizeInternalLinks(out);
  out = stampCredits(out);
  out = scrubFixedPricing(out, path);
  return out;
}
