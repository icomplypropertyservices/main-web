// QM #158: PAT / asbestos / legionella keyword town URLs are twins of /pages/{slug}/{town}; give them their own intent.
// Mirror of icomplyKeywordTwinIntent() in website/includes/ctr-meta.php.
const LABELS = { "pat-testing": "Costs & Certificates", "asbestos-survey": "Survey Types & Reports", "legionella-risk-assessment": "Costs & Written Reports",
  "first-fix-electrical": "Costs & What Is Included", "bathroom-fitting": "Costs & Design Options", "kitchen-fitting": "Costs & Design Options",
  "property-renovation": "Costs & Project Stages", "damp-proofing": "Costs & Treatment Options", "bs-5306-extinguisher-service-cost": "Prices & What Affects Them" };
export function keywordTwinIntent(slug, name, town) {
  const l = LABELS[slug];
  if (!l) return null;
  let title = `${name} ${town}: ${l} | Same/Next-Day Engineers`;
  if (title.length > 70) title = `${name} ${town}: ${l} | Same/Next-Day`;
  let desc = `${name} in ${town}: ${l.toLowerCase()} explained, and how to book. Same/next-day slots. Call or WhatsApp 07517 806082.`;
  if (desc.length > 160) desc = `${desc.slice(0, 157).replace(/\s+\S*$/, "")}…`;
  return { label: l, title, desc, h1Tail: `: ${l.toLowerCase()}` };
}
