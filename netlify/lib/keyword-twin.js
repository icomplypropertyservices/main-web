// QM #158: PAT / asbestos / legionella keyword town URLs are twins of /pages/{slug}/{town}; give them their own intent.
// Mirror of icomplyKeywordTwinIntent() in website/includes/ctr-meta.php. Titles keep "Same/Next-Day", <= 60 chars.
const LABELS = { "pat-testing": ["Costs & Certificates", "Costs"], "asbestos-survey": ["Survey Types & Reports", "Survey Types"], "legionella-risk-assessment": ["Costs & Written Reports", "Costs"] };
export function keywordTwinIntent(slug, name, town) {
  const l = LABELS[slug];
  if (!l) return null;
  const [long, short] = l;
  let title = [`${name} ${town}: ${long} | Same/Next-Day`, `${name} ${town}: ${short} | Same/Next-Day`, `${name} ${town} ${short} | Same/Next-Day`].find((t) => t.length <= 60);
  if (!title) title = `${`${name} ${town}`.slice(0, 44).replace(/\s+\S*$/, "")} | Same/Next-Day`;
  let desc = `${name} in ${town}: typical costs, what the visit and report include, and how to book. Same/next-day slots often available. Call or WhatsApp 07517 806082.`;
  if (desc.length > 160) desc = `${desc.slice(0, 157).replace(/\s+\S*$/, "")}…`;
  return { label: long, title, desc, h1Tail: `: ${long.toLowerCase()}` };
}
