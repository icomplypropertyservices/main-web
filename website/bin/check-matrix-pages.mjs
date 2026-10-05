import { renderTownPage } from "../../netlify/edge-functions/town-matrix.js";
import { scoreHtml } from "./score-pages.mjs";

const catalogue = {
  keywords: {
    eicr: {
      name: "EICR",
      service: "electrical",
      intro: "An EICR is the inspection and test record for a fixed electrical installation.",
      focus: ["Visual inspection", "Test results", "Observations coded for the client"],
      gas: false,
    },
    boiler: {
      name: "Boiler service",
      service: "gas-systems",
      intro: "A boiler service checks the appliance, the flue and the safety devices.",
      focus: ["Flue and ventilation", "Safety devices", "Service record"],
      gas: true,
    },
    "exterior-painting": {
      name: "Exterior painting",
      service: "painting-decorating",
      intro: "Exterior painting covers preparation and coatings on the outside of the building.",
      focus: ["Preparation", "Coatings", "Access"],
      gas: false,
    },
    "fire-door-survey": {
      name: "Fire door survey",
      service: "fire-doors",
      intro: "A fire door survey looks at the leaf, the frame, the closer and the gaps.",
      focus: ["Leaf and frame", "Closer", "Gaps"],
      gas: false,
    },
  },
  places: {
    stockport: {
      name: "Stockport",
      region: "North West",
      country: "England",
      population: 294000,
      housing: "32% semi-detached houses",
      industry: "18% retail",
      neighbours: ["Manchester", "Cheadle", "Hazel Grove"],
    },
    manchester: {
      name: "Manchester",
      region: "North West",
      country: "England",
      population: 550000,
      housing: "41% flats",
      industry: "22% professional services",
      neighbours: ["Salford", "Stockport", "Trafford"],
    },
    "high-legh": {
      name: "High Legh",
      region: "North West",
      country: "England",
      population: 0,
      housing: "",
      industry: "",
      neighbours: ["Altrincham", "Lymm", "Knutsford"],
    },
  },
  services: {
    labels: {
      electrical: "Electrical",
      "gas-systems": "Gas systems",
      "painting-decorating": "Painting and decorating",
      "windows-doors": "Windows and doors",
      "water-wras": "Water fittings",
      kitchens: "Kitchens",
      "fire-doors": "Fire doors",
      barriers: "Vehicle barriers",
      "ev-chargers": "EV chargers",
    },
    keywords: {
      electrical: ["eicr"],
      "gas-systems": ["boiler"],
      "painting-decorating": ["exterior-painting"],
      "windows-doors": [],
      "water-wras": [],
      "fire-doors": ["fire-door-survey"],
    },
    family: ["fire-doors", "fire-alarms", "barriers", "aov-air-handling"],
  },
  nationwide: {
    aberdeen: {
      name: "Aberdeen",
      region: "Aberdeen City",
      country: "Scotland",
      population: 198590,
      housing: "",
      industry: "published workplace class city",
      neighbours: ["Dundee"],
    },
  },
  manufacturers: {
    came: { name: "CAME", service: "barriers", nationwide: true },
    "rolec-ev": { name: "Rolec", service: "ev-chargers", nationwide: false },
  },
  jobs: {
    eicr: { name: "EICR", service: "electrical", gas: false, nationwide: false },
    "fire-alarms": { name: "Fire alarms", service: "fire-alarms", gas: false, nationwide: true },
    "gas-safety-cp12": { name: "Landlord gas safety certificate", service: "gas-systems", gas: true, nationwide: false },
  },
};

let fail = 0;
function ok(cond, message) {
  if (cond) {
    console.log(`[PASS] ${message}`);
  } else {
    fail += 1;
    console.log(`[FAIL] ${message}`);
  }
}

function shingles(html) {
  const text = html
    .replace(/<script[\s\S]*?<\/script>/gi, " ")
    .replace(/<style[\s\S]*?<\/style>/gi, " ")
    .replace(/<[^>]+>/g, " ")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, " ")
    .trim()
    .split(/\s+/);
  const out = new Set();
  for (let i = 0; i <= text.length - 6; i++) {
    out.add(text.slice(i, i + 6).join(" "));
  }
  return out;
}

function jaccard(a, b) {
  let inter = 0;
  for (const item of a) {
    if (b.has(item)) inter += 1;
  }
  const union = a.size + b.size - inter;
  return union === 0 ? 1 : inter / union;
}

const pages = [
  renderTownPage({ kind: "keyword", keyword: "eicr", town: "stockport", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "eicr", town: "manchester", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "eicr", town: "high-legh", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "boiler", town: "stockport", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "exterior-painting", town: "high-legh", catalogue }),
  renderTownPage({ kind: "service", service: "windows-doors", town: "manchester", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "eicr", town: "ashopton", catalogue }),
  renderTownPage({ kind: "keyword", keyword: "not-a-real-package", town: "slaidburn", catalogue }),
];

ok(pages.every(Boolean), "renderer returns a page for known and unknown towns");
const titles = new Set(pages.map((page) => page.title));
const h1s = new Set(pages.map((page) => page.h1));
ok(titles.size === pages.length, `unique titles ${titles.size}/${pages.length}`);
ok(h1s.size === pages.length, `unique h1s ${h1s.size}/${pages.length}`);

for (const page of pages) {
  const h1Count = (page.html.match(/<h1>/g) || []).length;
  ok(h1Count === 1, `one h1 for ${page.path}`);
  ok(page.html.includes(`rel="canonical" href="${page.canonical}"`), `canonical ${page.canonical}`);
  ok(!page.html.includes("£"), `no price on ${page.path}`);
  ok(!/NICEIC/i.test(page.html), `no NICEIC on ${page.path}`);
  ok(!/iComply does not carry out gas/i.test(page.html), `no gas denial on ${page.path}`);
  ok(!/iComply does not issue CP12/i.test(page.html), `no CP12 denial on ${page.path}`);
  ok(page.words >= 250, `body words ${page.words} on ${page.path}`);
  ok(/price on application|\bPOA\b/.test(page.html), `POA on ${page.path}`);
  ok(!page.html.includes("approved subcontractors"), `no approved subcontractors on ${page.path}`);
}

const eicrTowns = pages.slice(0, 3);
ok(eicrTowns.every((page) => page.robots === "index, follow"), "catalogue towns are index,follow");
ok(pages[6].robots === "noindex, follow" && pages[6].status === 200, "unknown town is 200 noindex");
ok(pages[7].robots === "noindex, follow" && pages[7].status === 200, "unknown keyword is 200 noindex");

const boiler = pages[3];
ok(boiler.html.includes("Landlord gas safety certificates (CP12) are carried out by Gas Safe registered engineers."), "gas engineers sentence");
ok(boiler.html.includes("iComply is not Gas Safe registered."), "company is not Gas Safe registered");
ok(!/iComply is Gas Safe registered\./.test(boiler.html.replace("iComply is not Gas Safe registered.", "")), "no positive Gas Safe claim");

const sets = eicrTowns.map((page) => shingles(page.html));
ok(jaccard(sets[0], sets[1]) < 0.55, `stockport/manchester overlap ${jaccard(sets[0], sets[1]).toFixed(3)}`);
ok(jaccard(sets[0], sets[2]) < 0.55, `stockport/high-legh overlap ${jaccard(sets[0], sets[2]).toFixed(3)}`);
ok(pages[4].h1.includes("High Legh") && pages[4].title.includes("Exterior painting"), "High Legh exterior painting title");
ok(pages[5].canonical === "https://icomplypropertyservices.co.uk/pages/windows-doors/manchester", "service canonical");

const scored = pages.map((page) => scoreHtml(page.html, { local: ["stockport", "manchester", "high legh", "ashopton", "ellesmere", "north west"] }));
ok(scored.every((row) => row.score === 100), `sample scores ${scored.map((row) => row.score).join(",")}`);

const water = renderTownPage({ kind: "service", service: "water-wras", town: "stockport", catalogue });
ok(water && water.status === 200 && water.robots === "index, follow", "water-wras town page is indexable");
ok(water && water.html.includes("/assets/images/services/water-wras.jpg"), "water-wras uses its own service image");
ok(scoreHtml(water.html, { local: ["stockport"] }).score === 100, "water-wras score 100");

const aberdeenFire = renderTownPage({ kind: "keyword", keyword: "fire-door-survey", town: "aberdeen", catalogue });
const aberdeenEicr = renderTownPage({ kind: "keyword", keyword: "eicr", town: "aberdeen", catalogue });
ok(aberdeenFire && aberdeenFire.robots === "noindex, follow", "fire keyword outside the catalogue stays noindex");
ok(aberdeenEicr && aberdeenEicr.robots === "noindex, follow", "electrical keyword in Aberdeen stays noindex");

const came = renderTownPage({ kind: "manufacturer", brand: "came", town: "aberdeen", catalogue });
const rolec = renderTownPage({ kind: "manufacturer", brand: "rolec-ev", town: "aberdeen", catalogue });
const rolecLocal = renderTownPage({ kind: "manufacturer", brand: "rolec-ev", town: "stockport", catalogue });
ok(came && came.robots === "noindex, follow" && came.path === "/pages/manufacturers/came/aberdeen", "manufacturer outside the catalogue stays noindex");
ok(rolec && rolec.robots === "noindex, follow", "local manufacturer stays noindex outside the core list");
ok(rolecLocal && rolecLocal.robots === "index, follow", "local manufacturer town in the core list");
ok(renderTownPage({ kind: "manufacturer", brand: "tunstall", town: "stockport", catalogue }) === null, "tunstall is not published");

const jobLocal = renderTownPage({ kind: "job", job: "eicr", town: "stockport", catalogue });
const jobFar = renderTownPage({ kind: "job", job: "eicr", town: "aberdeen", catalogue });
const jobFire = renderTownPage({ kind: "job", job: "fire-alarms", town: "aberdeen", catalogue });
const jobGas = renderTownPage({ kind: "job", job: "gas-safety-cp12", town: "stockport", catalogue });
ok(jobLocal && jobLocal.robots === "index, follow", "EICR job town page");
ok(jobFar && jobFar.robots === "noindex, follow", "EICR job is not nationwide");
ok(jobFire && jobFire.robots === "noindex, follow", "fire alarm job outside the catalogue stays noindex");
ok(jobGas && jobGas.html.includes("iComply is not Gas Safe registered."), "gas job keeps the legal sentence");
ok([came, rolecLocal, jobLocal, jobFire, aberdeenFire].every((page) => scoreHtml(page.html, { local: ["aberdeen", "stockport"] }).score === 100), "new page types score 100");

const kitchen = renderTownPage({ kind: "service", service: "kitchens", town: "stockport", catalogue });
ok(kitchen && kitchen.status === 200 && kitchen.robots === "index, follow", "kitchen town page in Stockport is indexable");
ok(kitchen && kitchen.html.toLowerCase().includes("price on application"), "kitchen town page is POA");
ok(kitchen && !kitchen.html.includes("£"), "kitchen town page has no price");
ok(kitchen && scoreHtml(kitchen.html, { local: ["stockport"] }).score === 100, "kitchen town score 100");

console.log(fail === 0 ? "PASS" : `FAIL ${fail}`);
process.exit(fail === 0 ? 0 : 1);
