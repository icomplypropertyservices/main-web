/**
 * Security P0 job twins on the Netlify edge (SEO PR145, 2026-10-20).
 * Keyword hubs and keyword x town pages render through the shared town
 * matrix (site shell, link blocks, town facts). The job twins are 301 on live
 * today; they now 301 to the live keyword equivalent instead of /pages/jobs.
 */
import { SECURITY_JOB_TWINS, SECURITY_TOWNS } from "./security-job-twins.js";

export function securityJobRedirect(path) {
  const cleaned = String(path || "").replace(/\/+$/, "") || "/";
  const m = cleaned.match(/^\/pages\/jobs\/([a-z0-9-]+)\/([a-z0-9-]+)$/);
  if (!m || !SECURITY_JOB_TWINS.has(m[1])) return null;
  return SECURITY_TOWNS.has(m[2]) ? `/pages/keywords/${m[1]}/${m[2]}` : `/pages/keywords/${m[1]}`;
}
