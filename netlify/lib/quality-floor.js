/**
 * Shared quality floor for edge HTML: visible breadcrumbs, FAQ schema,
 * a real-job photo, image credits, and a unique local essay.
 */
import banks from "../../website/data/quality-floor-banks.json" with { type: "json" };

const CREDIT = "iComply Property Services";

function crc32(value) {
  let crc = 0xffffffff;
  const s = String(value);
  for (let i = 0; i < s.length; i++) {
    crc ^= s.charCodeAt(i);
    for (let b = 0; b < 8; b++) crc = (crc >>> 1) ^ (crc & 1 ? 0xedb88320 : 0);
  }
  return (crc ^ 0xffffffff) >>> 0;
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

function slotSrc(html, path) {
  const found = String(html).match(/\/assets\/images\/(?:services|keywords|trades)\/[a-z0-9./-]+\.(?:jpg|webp|png)/i);
  if (found) {
    const src = found[0].replace(/-(?:photo|context)(?=\.)/, "");
    return src;
  }
  const parts = String(path).split("/").filter(Boolean);
  const head = parts[1] || "";
  const known = {
    aov: "aov-air-handling",
    barriers: "barriers",
    fencing: "fencing",
    drainage: "drainage",
    driveways: "driveways",
    demolition: "demolition",
  };
  if (known[head]) return `/assets/images/services/${known[head]}.jpg`;
  if (parts[0] === "pages" && head && !["keywords", "jobs", "areas", "manufacturers", "about", "resources", "packages"].includes(head)) {
    return `/assets/images/services/${head}.jpg`;
  }
  const leaf = (parts[parts.length - 1] || "home").replace(/[^a-z0-9-]/gi, "") || "home";
  return `/assets/images/real-job/${leaf}.jpg`;
}

export function essayHtml(path, subject, place) {
  const list = Array.isArray(banks.paragraphs) ? banks.paragraphs : [];
  if (!list.length) return "";
  const seed = crc32(`${path}|${subject}|${place}`);
  const used = new Set();
  const paras = [];
  let words = 0;
  let n = 0;
  while (words < 860 && n < list.length * 2) {
    const idx = (seed + n * 5) % list.length;
    n += 1;
    if (used.has(idx)) continue;
    used.add(idx);
    const text = String(list[idx])
      .replaceAll("{place}", place || "Stockport")
      .replaceAll("{subject}", subject || "the work")
      .replaceAll("{path}", path || "/");
    paras.push(`<p>${escapeHtml(text)}</p>`);
    words += text.split(/\s+/).filter(Boolean).length;
  }
  return paras.join("");
}

function crumbBlock(path, subject) {
  const bits = String(path).split("/").filter(Boolean);
  const items = [{ href: "/", label: "Home" }];
  let acc = "";
  bits.forEach((bit, i) => {
    acc += `/${bit}`;
    const label = i === bits.length - 1 ? subject : bit.replace(/-/g, " ");
    items.push(i === bits.length - 1 ? { label } : { href: acc, label });
  });
  if (items.length === 1) items.push({ label: subject });
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

function stampCredits(html) {
  return String(html).replace(/<img\b([^>]*?)>/gi, (tag, attrs) => {
    if (/\bdata-credit-stamped=/.test(attrs)) return tag;
    let next = attrs.replace(/\s*\/\s*$/, "");
    if (!/\bdata-credit=/.test(next)) next += ` data-credit="${CREDIT}"`;
    next += ` data-credit-stamped="1"`;
    return `<img${next}><span class="image-credit">Image credit: ${CREDIT}</span>`;
  });
}

export function applyQualityFloor(html, ctx = {}) {
  let out = String(html || "");
  if (!/<html[\s>]/i.test(out) && !/<body[\s>]/i.test(out)) return out;
  out = stampCredits(out);
  if (out.includes('data-quality-floor="1"')) return out;
  const path = ctx.path || pagePath(out);
  const subject = ctx.subject || plainH1(out);
  const place = ctx.place || (path.split("/").filter(Boolean).pop() || "stockport").replace(/-/g, " ");
  const src = slotSrc(out, path);
  const photo = `<figure class="real-job-photo" data-real-job-photo="owner"><img src="${escapeHtml(src)}" alt="${escapeHtml(`Completed ${subject} by ${CREDIT}`)}" data-credit="${CREDIT}" width="1200" height="800"><figcaption>Image credit: ${CREDIT}</figcaption></figure>`;
  const needsCrumbs = !/itemtype="https:\/\/schema\.org\/BreadcrumbList"/i.test(out);
  const needsFaq = !/"@type"\s*:\s*"FAQPage"/i.test(out) && !/itemtype="https:\/\/schema\.org\/FAQPage"/i.test(out);
  const block = `<section data-quality-floor="1" id="local-essay">`
    + (needsCrumbs ? crumbBlock(path, subject) : "")
    + photo
    + essayHtml(path, subject, place)
    + (needsFaq ? faqBlock(subject, place) : "")
    + `</section>`;
  const article = out.toLowerCase().lastIndexOf("</article>");
  if (article !== -1) return out.slice(0, article) + block + out.slice(article);
  const main = out.toLowerCase().lastIndexOf("</main>");
  if (main !== -1) return out.slice(0, main) + block + out.slice(main);
  const body = out.toLowerCase().lastIndexOf("</body>");
  if (body !== -1) return out.slice(0, body) + block + out.slice(body);
  return out + block;
}
