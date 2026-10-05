/**
 * Edge renderer gate for security P0 × dual-269 town pages.
 */
import { renderSecurityDualRing, securityDualRingHandles } from "../../netlify/lib/security-dual-ring.js";

const fail = [];
const bad = (message) => fail.push(message);

function words(html) {
  const match = html.match(/<article id="local-copy"[\s\S]*?<\/article>/);
  const text = (match ? match[0] : "").replace(/<[^>]+>/g, " ");
  return (text.match(/[A-Za-z0-9']+/g) || []).length;
}

if (!securityDualRingHandles("/pages/keywords/cctv-installation/burnley")) {
  bad("burnley keyword not handled");
}
if (!securityDualRingHandles("/pages/jobs/cctv-repair/york")) {
  bad("york job not handled");
}
if (securityDualRingHandles("/pages/jobs/emergency-cctv-repair/stockport")) {
  bad("keyword-only job handled");
}
if (securityDualRingHandles("/pages/keywords/eicr/york")) {
  bad("non-security handled");
}
if (securityDualRingHandles("/pages/keywords/cctv-installation")) {
  bad("hub should stay on static PHP");
}

const html = renderSecurityDualRing("/pages/keywords/cctv-monitoring/burnley");
if (!html) bad("no html");
else {
  if (words(html) < 800) bad(`words ${words(html)}`);
  if ((html.match(/<img /g) || []).length < 3) bad("images");
  if (!html.includes('data-seo-faq="1"')) bad("faq");
  if (!html.includes('rel="canonical" href="https://icomplypropertyservices.co.uk/pages/keywords/cctv-monitoring/burnley"')) bad("canonical");
  if (!html.includes('property="og:image" content="https://icomplypropertyservices.co.uk/')) bad("og image");
  if (!html.includes("does not claim police response") && !html.includes("does not operate an alarm receiving centre")) bad("monitoring honesty");
  const desc = html.match(/name="description" content="([^"]*)"/);
  if (!desc || desc[1].length < 140 || desc[1].length > 160) bad(`meta ${desc ? desc[1].length : 0}`);
  if (/£|approved subcontractors?|\bb\d{4,}\b/i.test(html)) bad("banned wording");
}

if (fail.length) {
  console.error(fail.join("\n"));
  process.exit(1);
}
console.log("OK security edge sample");
