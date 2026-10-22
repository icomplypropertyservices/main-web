// Strips leftover builder/meta sentences ("This page does not publish a fee.")
// from rendered HTML so pages read as plain customer copy.
const META = String.raw`\b(?:this|the) (?:page|readout|pack|guide)\b[^.!?"<>]*?\b(?:does not|doesn't|exists|is generated|was built)\b|on this page does not|\bdoes not invent\b|fee language|not a single template|generic town paragraph|invented fee|no invented|answered honestly|badge this page does not print`;
const PHRASES = [
  [/ for a page that does not invent a day rate/gi, ""],
  [/,? and this page does not\./gi, "."],
  [/; this page does not [^.!?"<>]*\./gi, "."],
  [/ — Icomply does not invent pound prices/g, ""],
  [/\bThis page does not (claim|name|show|state|say|print|publish a registration)/g, "We do not $1"],
  [/ and this page does not claim/g, " and we do not claim"],
  [/What this page does not decide/g, "What we do not decide"],
  [/POA quote — no invented fee list/g, "Price on application"],
];
const SENTENCE = new RegExp(String.raw`(^|[.!?]\s+|>\s*)([^.!?"<>]*?(?:${META})[^.!?"<>]*[.!?])`, "gi");
function capAt(text, max) {
  const clean = String(text).replace(/\s+/g, " ").trim();
  if (clean.length <= max) return clean;
  const cut = clean.slice(0, max);
  const sp = cut.lastIndexOf(" ");
  const out = sp > max * 0.55 ? cut.slice(0, sp) : cut;
  return out.replace(/[\s.,;:\-]+$/g, "");
}

export function scrubMeta(html) {
  let out = String(html);
  for (const [re, rep] of PHRASES) out = out.replace(re, rep);
  let prev;
  do { prev = out; out = out.replace(SENTENCE, (m, lead) => (lead.trim().startsWith(">") ? lead.replace(/\s+$/, "") : lead.replace(/\s+$/, " "))); } while (out !== prev);
  out = out.replace(/<p>\s*<\/p>/g, "").replace(/ +<\/(p|li|summary)>/g, "</$1>");
  out = out.replace(/<title>([^<]*)<\/title>/i, (_, text) => `<title>${capAt(text, 60)}</title>`);
  out = out.replace(/(<meta\s+name="description"\s+content=")([^"]*)(")/i, (_, open, text, close) => open + capAt(text, 155) + close);
  out = out.replace(/(<meta\s+property="og:title"\s+content=")([^"]*)(")/i, (_, open, text, close) => open + capAt(text, 60) + close);
  out = out.replace(/(<meta\s+property="og:description"\s+content=")([^"]*)(")/i, (_, open, text, close) => open + capAt(text, 155) + close);
  return out;
}
