// QM #158: PAT / asbestos / legionella keyword town URLs are twins of /pages/{slug}/{town}; give them their own intent.
// Mirror of icomplyKeywordTwinIntent() in website/includes/ctr-meta.php.
const LABELS = { "pat-testing": "Costs & Certificates", "asbestos-survey": "Survey Types & Reports", "legionella-risk-assessment": "Costs & Written Reports" };
export function keywordTwinIntent(slug, name, town) {
  const l = LABELS[slug];
  if (!l) return null;
  let title = `${name} ${town}: ${l} | Same/Next-Day Engineers`;
  if (title.length > 70) title = `${name} ${town}: ${l} | Same/Next-Day`;
  let desc = `${name} in ${town}: what it costs, what the ${l.toLowerCase()} cover and how to book. Same/next-day slots often available. Call or WhatsApp 07517 806082.`;
  if (desc.length > 160) desc = `${desc.slice(0, 157).replace(/\s+\S*$/, "")}…`;
  return { label: l, title, desc, h1Tail: `: ${l.toLowerCase()}` };
}
