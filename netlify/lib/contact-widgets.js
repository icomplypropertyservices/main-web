// Sitewide WhatsApp bubble + AI chat launcher for edge-rendered pages.
// Source of truth: website/includes/contact-widgets.html (JSON copy refreshed by static-export).
import widgets from "../../website/data/contact-widgets.json" with { type: "json" };

export function withContactWidgets(html) {
  const s = String(html);
  if (s.includes("<!--icomply-widgets-->")) return s;
  let w = widgets.html || "";
  if (s.includes('class="wa-float"')) w = w.replace(/<a class="icw-wa"[\s\S]*?<\/a>\n/, "");
  const i = s.toLowerCase().lastIndexOf("</body>");
  return i === -1 ? s + w : s.slice(0, i) + w + s.slice(i);
}
