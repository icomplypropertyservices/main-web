/**
 * SEO audit 22 Oct 2026: town pages repeated the exact H1 phrase
 * ("Emergency Lighting Installation in Rochdale") about 15 times in body copy.
 * Keep the first few body mentions and swap the rest for plain references,
 * leaving headings, links, the <head> and scripts untouched.
 */

const SKIP_TAGS = new Set(["h1", "h2", "h3", "h4", "a", "title", "script", "style", "button", "label", "option", "summary"]);

function escapeRe(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

function decode(s) {
  return s.replace(/&amp;/g, "&").replace(/&#0?39;|&apos;/g, "'").replace(/&quot;/g, '"').replace(/\s+/g, " ").trim();
}

export function h1Phrase(html) {
  const m = /<h1\b[^>]*>([\s\S]*?)<\/h1>/i.exec(html);
  if (!m) return "";
  return decode(m[1].replace(/<[^>]+>/g, " "));
}

export function capPhrase(html, phrase, keep = 3) {
  if (!html || !phrase || phrase.split(" ").length < 3) return html;
  const start = html.search(/<main\b/i);
  const end = html.search(/<\/main>/i);
  if (start < 0 || end < start) return html;
  const words = phrase.split(" ").map((w) => escapeRe(w).replace(/&/g, "(?:&|&amp;)"));
  const re = new RegExp(`(\\b(?:an?|the)\\s+)?${words.join("\\s+")}`, "gi");
  let seen = 0;
  const stack = [];
  const body = html.slice(start, end).replace(/(<\/?)([a-zA-Z0-9]+)([^>]*>)|([^<]+)/g, (all, open, tag, rest, text) => {
    if (tag) {
      const t = tag.toLowerCase();
      if (open === "</") {
        const i = stack.lastIndexOf(t);
        if (i >= 0) stack.splice(i, 1);
      } else if (!/\/>$/.test(rest) && SKIP_TAGS.has(t)) {
        stack.push(t);
      }
      return all;
    }
    if (stack.length) return all;
    return text.replace(re, (match, article, offset, whole) => {
      seen += 1;
      if (seen <= keep) return match;
      const before = whole.slice(0, offset);
      const sentenceStart = /(^|[.!?:]\s+)$/.test(before.replace(/\s+$/, " ")) || before.trim() === "";
      const swap = seen % 2 === 0 ? "this work" : "the job";
      return sentenceStart ? swap.charAt(0).toUpperCase() + swap.slice(1) : swap;
    });
  });
  return html.slice(0, start) + body + html.slice(end);
}

export function capH1Phrase(html, keep = 3) {
  return capPhrase(html, h1Phrase(html), keep);
}
