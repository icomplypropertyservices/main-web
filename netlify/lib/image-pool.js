/**
 * Per-service photograph pools shared with the PHP floor.
 * company.jpg and home.jpg are not pool members.
 * AOV pages draw a smoke-vent file and a separate AOV file.
 */
import pools from "../../website/data/service-image-pools.json" with { type: "json" };
import acCluster from "../../website/data/ac-photo-cluster.json" with { type: "json" };
import imageOwners from "../../website/data/image-owners.json" with { type: "json" };

const AC_CLUSTER = new Set(Array.isArray(acCluster.paths) ? acCluster.paths : []);

export function aliasFamily(family) {
  return family === "aov" ? "aov-air-handling" : String(family || "");
}

function imageHash(path, slot) {
  let h = (0x811c9dc5 ^ Math.imul(slot + 1, 0x9e3779b9)) >>> 0;
  const value = String(path || "/");
  for (let i = 0; i < value.length; i++) {
    h ^= value.charCodeAt(i);
    h = Math.imul(h, 0x01000193);
  }
  h = Math.imul(h ^ (h >>> 16), 0x85ebca6b) >>> 0;
  return (h ^ (h >>> 16)) >>> 0;
}

function hashed(list, path, slot) {
  if (!list.length) return "/assets/images/real-job/directory-hub.jpg";
  return list[imageHash(path, slot) % list.length];
}

export function pickImages(path, family, count = 3) {
  const want = Math.max(1, count);
  const key = aliasFamily(family);
  const entry = pools[key] || {};
  const smoke = Array.isArray(entry.smoke) ? entry.smoke : [];
  const aov = Array.isArray(entry.aov) ? entry.aov : [];
  let own = Array.isArray(entry.own) ? entry.own.slice() : [];
  if (key === "aov-air-handling" && smoke.length && aov.length) {
    const first = hashed(smoke, path, 0);
    const second = hashed(aov, path, 1);
    const rest = smoke.concat(aov).filter((src) => src !== first && src !== second);
    const third = hashed(rest.length ? rest : aov, path, 2);
    return [first, second, third].slice(0, want);
  }
  if (!own.length) own = [`/assets/images/services/${key}.jpg`];
  const picked = [];
  const used = new Set();
  for (let slot = 0; slot < want; slot++) {
    let choice = hashed(own, path, slot);
    if (used.has(choice) && used.size < own.length) {
      for (let offset = 0; offset < own.length; offset++) {
        const alt = own[(offset + slot) % own.length];
        if (!used.has(alt)) {
          choice = alt;
          break;
        }
      }
    }
    used.add(choice);
    picked.push(choice);
  }
  return picked;
}

export function isCollapsedAov(src) {
  const path = String(src || "").split("?")[0].replace(/^https?:\/\/[^/]+/i, "");
  if (/\/opt\/a055da46974c-/i.test(path)) return true;
  if (/\/assets\/images\/services\/aov-air-handling(?:-(?:photo|context))?\.jpe?g$/i.test(path)) return true;
  return AC_CLUSTER.has(path);
}

export function imageOwner(src) {
  const path = String(src || "").split("?")[0].replace(/^https?:\/\/[^/]+/i, "");
  return String(imageOwners[path] || "");
}
