/** Shared under-H1 call + WhatsApp row. Twin of website/includes/hero-cta.php. */
export function heroCta(label) {
  const clean = String(label || "Property services").replace(/\s+/g, " ").trim().slice(0, 80) || "Property services";
  const text = encodeURIComponent(clean);
  return `<div class="hero-cta" data-cta="hero"><a class="call" href="tel:07517806082">Call 07517 806082</a><a class="wa" href="https://wa.me/447517806082?text=${text}%20quote" target="_blank" rel="noopener">WhatsApp</a></div>`;
}
