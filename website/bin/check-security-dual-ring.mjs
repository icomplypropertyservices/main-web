/**
 * Edge gate for security P0 job twins (SEO PR145, 2026-10-20).
 * Job x town twins 301 to the live keyword x town page; keyword x town pages
 * are left to the shared town matrix renderer (site shell, >= 80 links).
 */
import { securityJobRedirect } from "../../netlify/lib/security-dual-ring.js";
import { nonGmMatrixRedirect } from "../../netlify/edge-functions/town-matrix.js";

const fail = [];
const expect = (got, want, label) => { if (got !== want) fail.push(`${label}: got ${got} want ${want}`); };

expect(securityJobRedirect("/pages/jobs/cctv-installation/burnley"), "/pages/keywords/cctv-installation/burnley", "job x town twin");
expect(securityJobRedirect("/pages/jobs/anpr-installation/york"), "/pages/keywords/anpr-installation/york", "created job twin");
expect(securityJobRedirect("/pages/jobs/cctv-repair/aberdeen"), "/pages/keywords/cctv-repair", "outside 269 -> keyword hub");
expect(securityJobRedirect("/pages/jobs/maglock-installation/stockport"), null, "maglock keeps live job x town");
expect(securityJobRedirect("/pages/jobs/eicr/stockport"), null, "non-security job untouched");
expect(securityJobRedirect("/pages/keywords/cctv-installation/burnley"), null, "keyword x town untouched");
expect(securityJobRedirect("/pages/jobs/emergency-cctv-repair/stockport"), null, "keyword-only intent has no job twin");
for (const p of ["/pages/keywords/cctv-installation/burnley", "/pages/keywords/emergency-cctv-repair/preston", "/pages/keywords/anpr-near-me/blackpool"]) {
  expect(nonGmMatrixRedirect(p), null, `local keyword x town stays (${p})`);
}

if (fail.length) {
  console.error(fail.join("\n"));
  process.exit(1);
}
console.log("OK security job twins (edge 301 to keyword equivalents)");
