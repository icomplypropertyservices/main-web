/**
 * Build the public-page performance assets (non-prod source change).
 *
 * 1. Purge Tailwind v2 down to classes that actually appear in website PHP/JS/HTML.
 * 2. Resize the 93 unique service/keyword/hero JPEGs to 640w and 1280w WebP.
 *
 * Requires sharp + purgecss resolvable from PERF_MODULES (default /tmp/icomply-perf/package.json)
 * and a Tailwind v2.2.19 stylesheet at PERF_TAILWIND (default /tmp/icomply-perf/tailwind.min.css).
 *
 *   npm install --prefix /tmp/icomply-perf sharp purgecss
 *   curl -fsSL -o /tmp/icomply-perf/tailwind.min.css \
 *     https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css
 *   node website/bin/build-perf-assets.mjs
 */
import { createRequire } from 'node:module';
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import zlib from 'node:zlib';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const websiteRoot = path.resolve(__dirname, '..');
const repoRoot = path.resolve(websiteRoot, '..');
const require = createRequire(process.env.PERF_MODULES || '/tmp/icomply-perf/package.json');
const sharp = require('sharp');
const { PurgeCSS } = require('purgecss');

const tailwindPath = process.env.PERF_TAILWIND || '/tmp/icomply-perf/tailwind.min.css';
const outCss = path.join(websiteRoot, 'assets/css/utilities.css');
const optDir = path.join(websiteRoot, 'assets/images/opt');
const manifestPath = path.join(websiteRoot, 'data/image-manifest.json');
const reportPath = path.join(websiteRoot, 'data/perf-report.json');

const CONTENT_GLOBS = [
  path.join(websiteRoot, '**/*.php'),
  path.join(websiteRoot, '**/*.html'),
  path.join(websiteRoot, '**/*.js'),
];

function extractTokens(content) {
  return content.match(/[A-Za-z0-9_:[\]%#./-]+/g) || [];
}

function selectorFor(cls) {
  const escaped = cls.replace(/[:/[\]%#.]/g, (ch) => '\\' + ch);
  return '.' + escaped;
}

function cssHasClass(css, cls) {
  const sel = selectorFor(cls);
  const end = new Set(['{', ':', ',', ' ', '.', ')', '>', '+', '~', ';']);
  let from = 0;
  while (from < css.length) {
    const i = css.indexOf(sel, from);
    if (i < 0) return false;
    const next = css[i + sel.length] ?? '';
    const prev = i === 0 ? '' : css[i - 1];
    const prevOk = prev === '' || prev === '}' || prev === '{' || prev === ',' || prev === ' ' || prev === '>' || prev === '+' || prev === '~';
    if (prevOk && (next === '' || end.has(next))) return true;
    from = i + sel.length;
  }
  return false;
}

const ALWAYS = new Set([
  'flex', 'grid', 'block', 'inline', 'inline-block', 'inline-flex', 'hidden', 'table',
  'contents', 'fixed', 'absolute', 'relative', 'sticky', 'static', 'border', 'group',
  'container', 'italic', 'underline', 'truncate', 'rounded', 'shadow', 'outline',
  'sr-only', 'not-sr-only',
]);

function isCandidateClass(token) {
  if (!token || token.length > 80 || token.includes('://')) return false;
  if (ALWAYS.has(token)) return true;
  if (!/^[a-z][A-Za-z0-9-]*$/.test(token.split(':').pop().replace(/\[[^\]]*\]/g, 'x'))) return false;
  return token.includes('-') || token.includes(':');
}

async function buildCss() {
  const full = fs.readFileSync(tailwindPath);
  const fullCss = full.toString('utf8');
  let purged = await new PurgeCSS().purge({
    content: CONTENT_GLOBS,
    css: [tailwindPath],
    defaultExtractor: extractTokens,
    safelist: { standard: ['group'] },
  });
  let css = purged[0].css;

  const files = [];
  function walk(dir) {
    for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
      if (ent.name === 'opt' || ent.name === 'node_modules' || ent.name === 'vendor') continue;
      const p = path.join(dir, ent.name);
      if (ent.isDirectory()) walk(p);
      else if (/\.(php|html|js)$/i.test(ent.name)) files.push(p);
    }
  }
  walk(websiteRoot);

  const tokens = new Set();
  for (const file of files) {
    for (const token of extractTokens(fs.readFileSync(file, 'utf8'))) {
      if (isCandidateClass(token)) tokens.add(token);
    }
  }

  const missing = [];
  for (const token of tokens) {
    if (!cssHasClass(fullCss, token)) continue;
    if (!cssHasClass(css, token)) missing.push(token);
  }
  missing.sort();
  if (missing.length) {
    purged = await new PurgeCSS().purge({
      content: CONTENT_GLOBS,
      css: [tailwindPath],
      defaultExtractor: extractTokens,
      safelist: { standard: ['group', ...missing] },
    });
    css = purged[0].css;
  }

  const stillMissing = missing.filter((token) => !cssHasClass(css, token));
  fs.mkdirSync(path.dirname(outCss), { recursive: true });
  fs.writeFileSync(outCss, css);
  return {
    tailwindBytes: full.length,
    tailwindGzipBytes: zlib.gzipSync(full).length,
    utilitiesBytes: Buffer.byteLength(css),
    utilitiesGzipBytes: zlib.gzipSync(Buffer.from(css)).length,
    safelisted: missing.length,
    stillMissing,
  };
}

function listSources() {
  const roots = ['services', 'keywords', 'heroes', 'manufacturers'].map((d) => path.join(websiteRoot, 'assets/images', d));
  const files = [];
  for (const dir of roots) {
    if (!fs.existsSync(dir)) continue;
    for (const name of fs.readdirSync(dir)) {
      if (!/\.(jpe?g|png)$/i.test(name)) continue;
      const abs = path.join(dir, name);
      if (fs.statSync(abs).isFile()) files.push(abs);
    }
  }
  const og = path.join(websiteRoot, 'assets/images/og-image.jpg');
  if (fs.existsSync(og)) files.push(og);
  return files;
}

async function encodeVariant(input, width) {
  const pipeline = sharp(input, { failOn: 'none' }).rotate().resize({
    width,
    withoutEnlargement: true,
  });
  const webpBuf = await pipeline.clone().webp({ quality: 68, effort: 4 }).toBuffer();
  const meta = await sharp(webpBuf).metadata();
  return { webpBuf, width: meta.width || width, height: meta.height || 0 };
}

async function buildImages() {
  fs.rmSync(optDir, { recursive: true, force: true });
  fs.mkdirSync(optDir, { recursive: true });
  const sources = listSources();
  const byHash = new Map();
  const manifest = {};
  let origBytes = 0;

  for (const abs of sources) {
    const buf = fs.readFileSync(abs);
    origBytes += buf.length;
    const hash = crypto.createHash('sha1').update(buf).digest('hex').slice(0, 12);
    const rel = '/' + path.relative(websiteRoot, abs).split(path.sep).join('/');
    if (!byHash.has(hash)) byHash.set(hash, { abs, buf, rels: [] });
    byHash.get(hash).rels.push(rel);
  }

  let optBytes = 0;
  const written = new Set();
  for (const [hash, group] of byHash) {
    const meta = await sharp(group.buf, { failOn: 'none' }).metadata();
    const ow = meta.width || 0;
    const oh = meta.height || 0;
    // 480 covers 4-column retina cards, 800 covers a phone-width retina card,
    // 1280 covers a half-width hero. Never upscale.
    const widths = [480, 800, 1280].filter((w) => ow >= w);
    if (ow > 0 && (widths.length === 0 || (widths[widths.length - 1] !== ow && ow < 1280))) {
      widths.push(ow);
    }
    if (!widths.length) widths.push(Math.max(1, ow || 480));

    const variants = [];
    for (const width of widths) {
      const variant = await encodeVariant(group.abs, width);
      const name = `${hash}-${variant.width}.webp`;
      const rel = `/assets/images/opt/${name}`;
      if (!written.has(name)) {
        fs.writeFileSync(path.join(optDir, name), variant.webpBuf);
        written.add(name);
        optBytes += variant.webpBuf.length;
      }
      variants.push({
        w: variant.width,
        h: variant.height,
        webp: rel,
        bytes: variant.webpBuf.length,
      });
    }
    variants.sort((a, b) => a.w - b.w);
    const entry = {
      width: ow,
      height: oh,
      origBytes: group.buf.length,
      variants,
    };
    for (const rel of group.rels) manifest[rel] = entry;
  }

  fs.writeFileSync(manifestPath, JSON.stringify(manifest));
  return {
    files: sources.length,
    unique: byHash.size,
    origBytes,
    optBytes,
    optFiles: written.size,
  };
}

const cssReport = await buildCss();
const imageReport = await buildImages();
const report = {
  generatedAt: new Date().toISOString(),
  note: 'Non-prod performance pass. Numbers are transfer sizes before HTTP compression except where gzip is named.',
  css: {
    before: {
      stylesheet: 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
      bytes: cssReport.tailwindBytes,
      gzipBytes: cssReport.tailwindGzipBytes,
      siteCssBytes: fs.statSync(path.join(websiteRoot, 'assets/css/site.css')).size,
    },
    after: {
      stylesheet: '/assets/css/utilities.css',
      bytes: cssReport.utilitiesBytes,
      gzipBytes: cssReport.utilitiesGzipBytes,
      siteCssBytes: fs.statSync(path.join(websiteRoot, 'assets/css/site.css')).size,
      safelistedClasses: cssReport.safelisted,
      stillMissing: cssReport.stillMissing,
    },
  },
  images: imageReport,
};
fs.writeFileSync(reportPath, JSON.stringify(report, null, 2) + '\n');
console.log(JSON.stringify(report, null, 2));
if (cssReport.stillMissing.length) {
  console.error('Classes present in Tailwind but dropped:', cssReport.stillMissing.slice(0, 40).join(', '));
  process.exit(1);
}
if (cssReport.utilitiesGzipBytes > 40000) {
  console.error('Purged CSS gzip is over 40KB');
  process.exit(1);
}
