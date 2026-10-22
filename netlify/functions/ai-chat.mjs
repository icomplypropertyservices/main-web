// iComply AI assistant backend — POST /api/chat
// Env: DEEPSEEK_API_KEY (preferred AI provider), DEEPSEEK_MODEL (default deepseek-chat),
//      XAI_API_KEY (fallback provider), XAI_MODEL (default grok-4.3),
//      XAI_REASONING_EFFORT (default "none"), TELEGRAM_BOT_TOKEN + TELEGRAM_LEAD_CHAT_ID (optional lead alerts).
// No database: nothing is stored. A lead is forwarded once (Netlify Forms from the browser + optional Telegram).
import fs from "node:fs";
import path from "node:path";

const PHONE = "07517 806082";
const FALLBACK = `Sorry, the assistant is not available right now. Please call ${PHONE} or WhatsApp us on ${PHONE} — we usually reply within 2 hours on business days.`;
const MAX_MSGS = 24;
const MAX_CHARS = 600;
const RATE = { windowMs: 10 * 60 * 1000, max: 30 };
const hits = new Map(); // best-effort per-instance rate limit (IP -> timestamps)

let siteFacts = "";
try {
  siteFacts = fs.readFileSync(path.join(process.cwd(), "website", "llms-full.txt"), "utf8").slice(0, 12000);
} catch { siteFacts = ""; }

const SYSTEM = `You are the AI assistant on the iComply Property Services website. Always be clear you are an AI assistant if asked; a real person (Jack Scott) follows up.
GOAL: turn visitors into calls and leads. Be brief (2-4 short sentences), friendly, UK English. Answer the question, then ask for what's missing to arrange a quote: name, phone number, postcode, and the job. Ask for one or two things at a time.
FACTS (never contradict):
- Business: iComply Property Services, 17 Woodlands Park Road, Offerton, Stockport SK2 5DE. Phone/WhatsApp ${PHONE}. Email icomplypropertyservices@gmail.com. Mon–Fri 08:00–18:00; same/next-day slots are often available.
- Areas: Greater Manchester and the North West (Stockport, Manchester, Salford, Trafford, Tameside, Oldham, Rochdale, Bolton, Bury, Wigan, Cheshire, Lancashire, Merseyside, Cumbria). AOV/smoke control, vehicle barriers and fire alarms also UK mainland.
- PRICES: the ONLY fixed price you may state is EICR from £160 for a typical rented home. For everything else say "Jack will confirm a free written quote" — never invent or estimate any other price.
- GAS: say gas safety checks / CP12s / boiler work are "carried out by Gas Safe registered engineers". NEVER say iComply is Gas Safe registered.
- Never claim accreditations, scheme memberships, reviews or guarantees not listed here. If unsure, say Jack will confirm.
- Don't give safety-critical DIY instructions; for gas smell tell them to call the National Gas Emergency number 0800 111 999; for immediate danger call 999.
LEAD CAPTURE: once you have at least a phone number AND the job (name and postcode if given), end your reply with exactly one line:
<lead>{"name":"...","phone":"...","postcode":"...","job":"...","email":"..."}</lead>
Only output the <lead> line once per conversation and only with details the visitor actually gave. After that, tell them Jack will call them back soon, or they can call ${PHONE} now.
SITE INFO:
${siteFacts}`;

function json(body, status = 200) {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "content-type": "application/json; charset=utf-8", "cache-control": "no-store" },
  });
}

function limited(ip) {
  const now = Date.now();
  const arr = (hits.get(ip) || []).filter((t) => now - t < RATE.windowMs);
  arr.push(now);
  hits.set(ip, arr);
  if (hits.size > 5000) hits.clear();
  return arr.length > RATE.max;
}

function clean(messages) {
  if (!Array.isArray(messages)) return [];
  return messages
    .filter((m) => m && (m.role === "user" || m.role === "assistant") && typeof m.content === "string")
    .slice(-MAX_MSGS)
    .map((m) => ({ role: m.role, content: m.content.slice(0, MAX_CHARS) }));
}

const PHONE_RE = /(?:\+?44\s?7\d{3}|\(?07\d{3}\)?|\+?44\s?\d{2,4}|0\d{2,4})[\s-]?\d{3}[\s-]?\d{3,4}/;
const POSTCODE_RE = /\b[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}\b/i;

function heuristicLead(msgs) {
  const userText = msgs.filter((m) => m.role === "user").map((m) => m.content).join(" \n ");
  const phone = (userText.match(PHONE_RE) || [])[0];
  if (!phone) return null;
  const postcode = (userText.match(POSTCODE_RE) || [])[0] || "";
  return { name: "", phone: phone.trim(), postcode, job: userText.slice(0, 400), email: "" };
}

function extractLead(text) {
  const m = String(text).match(/<lead>([\s\S]*?)<\/lead>/i);
  let lead = null;
  if (m) {
    try {
      const o = JSON.parse(m[1]);
      lead = {};
      for (const k of ["name", "phone", "postcode", "job", "email"]) lead[k] = String(o[k] || "").slice(0, 200);
      if (!lead.phone) lead = null;
    } catch { lead = null; }
  }
  const reply = String(text).replace(/<lead>[\s\S]*?<\/lead>/gi, "").trim();
  return { reply, lead };
}

async function telegram(lead, page) {
  const token = process.env.TELEGRAM_BOT_TOKEN;
  const chat = process.env.TELEGRAM_LEAD_CHAT_ID;
  if (!token || !chat || !lead) return;
  const text = `New website AI-chat lead\nName: ${lead.name || "-"}\nPhone: ${lead.phone}\nPostcode: ${lead.postcode || "-"}\nJob: ${lead.job || "-"}\nEmail: ${lead.email || "-"}\nPage: ${page || "-"}`;
  try {
    await fetch(`https://api.telegram.org/bot${token}/sendMessage`, {
      method: "POST",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({ chat_id: chat, text }),
    });
  } catch { /* ignore */ }
}

export default async (req, context) => {
  if (req.method !== "POST") return json({ error: "POST only" }, 405);
  const ip = (context && context.ip) || req.headers.get("x-nf-client-connection-ip") || "anon";
  if (limited(ip)) return json({ reply: `You've sent a lot of messages — please call or WhatsApp ${PHONE} and we'll help straight away.` }, 429);

  let body = {};
  try { body = await req.json(); } catch { return json({ reply: FALLBACK }, 400); }
  const msgs = clean(body.messages);
  const page = typeof body.page === "string" ? body.page.slice(0, 200) : "";
  const leadAlreadySent = Boolean(body.leadSent);
  if (!msgs.length) return json({ reply: FALLBACK }, 400);

  const providers = [];
  if (process.env.DEEPSEEK_API_KEY) providers.push({
    url: "https://api.deepseek.com/chat/completions",
    key: process.env.DEEPSEEK_API_KEY,
    extra: { model: process.env.DEEPSEEK_MODEL || "deepseek-chat" },
  });
  if (process.env.XAI_API_KEY) providers.push({
    url: "https://api.x.ai/v1/chat/completions",
    key: process.env.XAI_API_KEY,
    extra: { model: process.env.XAI_MODEL || "grok-4.3", reasoning_effort: process.env.XAI_REASONING_EFFORT || "none" },
  });
  if (!providers.length) {
    // No AI yet: still capture a lead if the visitor typed a phone number.
    const lead = leadAlreadySent ? null : heuristicLead(msgs);
    if (lead) {
      await telegram(lead, page);
      return json({ reply: `Thanks — I've passed your details to Jack, who will call you back on ${lead.phone}. For anything urgent call ${PHONE}.`, lead });
    }
    return json({ reply: `Thanks for your message. Our AI assistant is being set up — please call ${PHONE} or WhatsApp us, or leave your phone number and postcode here and Jack will call you back.` });
  }

  for (const p of providers) {
    try {
      const ctrl = new AbortController();
      const timer = setTimeout(() => ctrl.abort(), 20000);
      const r = await fetch(p.url, {
        method: "POST",
        signal: ctrl.signal,
        headers: { "content-type": "application/json", authorization: `Bearer ${p.key}` },
        body: JSON.stringify({
          ...p.extra,
          max_tokens: 350,
          temperature: 0.3,
          messages: [{ role: "system", content: SYSTEM }, ...msgs],
        }),
      });
      clearTimeout(timer);
      if (!r.ok) continue;
      const d = await r.json();
      const raw = d?.choices?.[0]?.message?.content || "";
      if (!raw) continue;
      let { reply, lead } = extractLead(raw);
      if (leadAlreadySent) lead = null;
      if (lead) await telegram(lead, page);
      return json({ reply: reply || FALLBACK, lead });
    } catch { /* try next provider */ }
  }
  return json({ reply: FALLBACK });
};

export const config = { path: "/api/chat" };
