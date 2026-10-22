/**
 * Semrush/Ahrefs 21 Oct, template level (twin: website/includes/seo-basics.php).
 * Runs last on every rendered page, after money-ctr. Pages with a money-ctr /
 * GSC brief keep their authored title and meta.
 *  - title over 60: drop the brand tail, then trailing " | " segments, then words
 *  - meta outside 140-155 (or missing): trim at a word and end with the call line,
 *    or pad short ones; og/twitter descriptions follow the meta
 *  - FAQPage JSON-LD: questions with no name or answer text are dropped (and the
 *    block removed if none are left); Service JSON-LD gets a name and provider
 * Never touches robots, canonical, H1 or the hero/CTA area.
 */
const TAIL = ". Free quote, call 07517 806082";
const PAD = " across Greater Manchester and the North West";
const PROVIDER = { "@type": "LocalBusiness", name: "iComply Property Services", telephone: "07517 806082", email: "icomplypropertyservices@gmail.com" };
const dec = (s) => String(s).replace(/&amp;/g, "&").replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&lt;/g, "<").replace(/&gt;/g, ">");
const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");

const STOP = /\s+(in|for|and|from|of|the|&|with|to|at|on|by|a|an|or)$/i;
function trimStop(s) {
  let prev;
  do { prev = s; s = s.replace(STOP, "").replace(/[\s,:;|–&-]+$/, ""); } while (s !== prev);
  return s;
}

export function fitTitle(t) {
  let s = dec(t).replace(/\s+/g, " ").trim();
  if (s.length <= 60) return s;
  s = s.replace(/\s*\|\s*iComply Property Services$/i, " | iComply");
  while (s.length > 60 && s.includes(" | ")) s = s.slice(0, s.lastIndexOf(" | "));
  while (s.length > 60) s = s.slice(0, s.lastIndexOf(" ")).replace(/[\s,:;|–-]+$/, "");
  return trimStop(s);
}

export function fitMeta(m, title) {
  let s = dec(m || "").replace(/\s+/g, " ").trim();
  if (s.length >= 140 && s.length <= 155) return s;
  if (!s) s = fitTitle(title || "iComply Property Services").replace(/\s*\|.*$/, "");
  s = s.replace(/\s*(Free quote, )?call 07517 806082\.?$/i, "").replace(/[\s.,;:–-]+$/, "");
  const room = 155 - TAIL.length;
  if (s.length > room) {
    s = s.slice(0, room + 1);
    s = trimStop(s.slice(0, s.lastIndexOf(" ")).replace(/[\s.,;:–&-]+$/, ""));
  }
  if ((s + TAIL).length < 140 && !/North West/i.test(s) && (s + PAD + TAIL).length <= 155) s += PAD;
  const pads = [" with a written scope and Same/Next-Day visits", " and Same/Next-Day visits", " with a written scope", ". Local team from Stockport", ", tidy work"];
  if ((s + TAIL).length < 140) {
    const hit = pads.find((p) => (s + p + TAIL).length >= 140 && (s + p + TAIL).length <= 155);
    if (hit) s += hit;
    else {
      const first = pads.find((p) => (s + p + TAIL).length <= 155);
      if (first) s += first;
      for (const p of [". Local team from Stockport", ", tidy work", " today"]) {
        if ((s + TAIL).length >= 140) break;
        if ((s + p + TAIL).length <= 155) s += p;
      }
    }
  }
  return s + TAIL;
}

function setMeta(html, attr, key, value) {
  const re = new RegExp(`(<meta\\s+${attr}="${key}"\\s+content=")[^"]*(")`, "i");
  return re.test(html) ? html.replace(re, `$1${esc(value)}$2`) : html;
}

function fixLd(o) {
  if (Array.isArray(o)) return o.map(fixLd).filter((x) => x !== null);
  if (!o || typeof o !== "object") return o;
  if (Array.isArray(o["@graph"])) o["@graph"] = fixLd(o["@graph"]);
  const types = [].concat(o["@type"] || []);
  if (types.includes("FAQPage")) {
    const me = (Array.isArray(o.mainEntity) ? o.mainEntity : []).filter((q) => q && String(q.name || "").trim() && q.acceptedAnswer && String(q.acceptedAnswer.text || "").trim());
    if (!me.length) return null;
    o.mainEntity = me;
  }
  if (types.includes("Service")) {
    if (!String(o.name || o.serviceType || "").trim()) o.name = "Property services";
    if (!o.provider) o.provider = PROVIDER;
  }
  return o;
}

export function applySeoBasics(html, { authored = false } = {}) {
  if (typeof html !== "string" || !/<html/i.test(html.slice(0, 2000))) return html;
  let out = html.replace(/<script([^>]+application\/ld\+json[^>]*)>([\s\S]*?)<\/script>/gi, (all, attrs, body) => {
    if (!/FAQPage|"Service"/.test(body)) return all;
    try {
      const fixed = fixLd(JSON.parse(body));
      if (fixed === null || (Array.isArray(fixed) && !fixed.length)) return "";
      return `<script${attrs}>${JSON.stringify(fixed).replace(/</g, "\\u003c")}</script>`;
    } catch {
      return all;
    }
  });
  if (authored) return out;
  const tm = out.match(/<title>([\s\S]*?)<\/title>/i);
  const title = tm ? fitTitle(tm[1]) : "";
  if (tm && dec(tm[1]).trim() !== title) {
    out = out.replace(/<title>[\s\S]*?<\/title>/i, `<title>${esc(title)}</title>`);
    out = setMeta(out, "property", "og:title", title);
    out = setMeta(out, "name", "twitter:title", title);
  }
  const mm = out.match(/<meta\s+name="description"\s+content="([^"]*)"/i);
  const meta = fitMeta(mm ? mm[1] : "", title);
  if (!mm) {
    out = out.replace(/<\/title>/i, `</title>\n<meta name="description" content="${esc(meta)}">`);
  } else if (dec(mm[1]) !== meta) {
    out = setMeta(out, "name", "description", meta);
    out = setMeta(out, "property", "og:description", meta);
    out = setMeta(out, "name", "twitter:description", meta);
  }
  return out;
}
