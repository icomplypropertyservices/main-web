// Shared branded chrome for edge-rendered matrix pages (keeps them styled).
export const MATRIX_CSS = `<link rel="stylesheet" href="/assets/css/site.css">
<link rel="stylesheet" href="/assets/css/matrix.css">`;
export const MATRIX_HEADER = `<header class="mx-top mega-header" data-mega-nav="1"><div class="mx-top-in">
<a class="mx-logo" href="/">i<span>Comply</span> Property Services</a>
<nav class="mx-nav" aria-label="Main">
<a href="/pages/services">Services</a>
<details class="mx-drop"><summary>Building</summary><div class="mx-drop-panel">
<a href="/pages/services/plastering">Plastering</a>
<a href="/pages/services/rendering">Rendering</a>
<a href="/pages/services/brickwork">Brickwork</a>
<a href="/pages/services/roofing">Roofing</a>
<a href="/pages/services/extensions">Extensions</a>
<a href="/pages/services/loft-conversions">Loft conversions</a>
<a href="/pages/services/kitchens">Kitchens</a>
<a href="/pages/services/bathrooms">Bathrooms</a>
<a href="/pages/services/joinery">Joinery</a>
<a href="/pages/services/flooring">Flooring</a>
<a href="/pages/services/tiling">Tiling</a>
<a href="/pages/services/painting-decorating">Decorating</a>
<a href="/pages/services/groundworks">Groundworks</a>
<a href="/pages/services/drainage">Drainage</a>
<a href="/pages/services/driveways">Driveways</a>
<a href="/pages/services/fencing">Fencing</a>
<a href="/pages/services/damp-proofing">Damp</a>
<a href="/pages/services/insulation">Insulation</a>
<a href="/pages/services/windows-doors">Windows and doors</a>
<a href="/pages/services/demolition">Demolition</a>
<a href="/pages/services/building-maintenance">Building maintenance</a>
</div></details>
<details class="mx-drop"><summary>Electrical</summary><div class="mx-drop-panel">
<a href="/pages/services/electrical">Electrical</a>
<a href="/pages/keywords/eicr">EICR</a>
<a href="/pages/services/ev-chargers">EV chargers</a>
</div></details>
<details class="mx-drop"><summary>Fire and AOV</summary><div class="mx-drop-panel">
<a href="/pages/services/fire-alarms">Fire alarms</a>
<a href="/pages/services/aov-air-handling">AOV and smoke control</a>
<a href="/pages/services/fire-doors">Fire doors</a>
<a href="/pages/services/emergency-lighting">Emergency lighting</a>
<a href="/shop/fire/">Fire supplies</a>
</div></details>
<details class="mx-drop"><summary>Security</summary><div class="mx-drop-panel">
<a href="/pages/services/cctv">CCTV</a>
<a href="/pages/services/access-control">Access control</a>
<a href="/pages/services/intruder-alarm">Intruder alarms</a>
<a href="/pages/services/barriers">Barriers</a>
</div></details>
<a href="/pages/jobs">Jobs</a>
<a href="/pages/keywords">Guides</a>
<a href="/pages/areas">Areas</a>
<a href="/pages/manufacturers">Manufacturers</a>
<a href="/contact">Contact</a>
</nav>
<a class="mx-call" href="tel:07517806082">Call 07517 806082</a>
</div></header>`;
export const MATRIX_FOOTER = `<section class="mx-cta"><h2>Get a quote</h2><p>Send the address and what you need. We reply the same day.</p>
<a href="tel:07517806082">Call 07517 806082</a><a class="wa" href="https://wa.me/447517806082?text=Hi%20iComply%2C%20I%20need%20a%20quote" target="_blank" rel="noopener">WhatsApp</a><a href="/contact">Request a quote</a></section>`;
export const MATRIX_SITE_FOOTER = `<footer class="mx-foot"><p>iComply Property Services · 17 Woodlands Park Road, Offerton, Stockport SK2 5DE · <a href="tel:07517806082">07517 806082</a> · <a href="mailto:icomplypropertyservices@gmail.com">icomplypropertyservices@gmail.com</a></p><p class="mx-foot-hubs"><a href="/pages/services/plastering">Plastering</a> · <a href="/pages/services/roofing">Roofing</a> · <a href="/pages/services/electrical">Electrical</a> · <a href="/pages/services/aov-air-handling">AOV</a> · <a href="/pages/services/fire-alarms">Fire alarms</a> · <a href="/pages/services/cctv">Security</a> · <a href="/pages/services/kitchens">Kitchens</a> · <a href="/pages/services/bathrooms">Bathrooms</a> · <a href="/pages/services/drainage">Drainage</a> · <a href="/pages/areas">Areas</a></p><p><a href="/privacy">Privacy</a> · <a href="/terms">Terms</a> · <a href="/sitemap.xml">Sitemap</a></p></footer>`;
export const BRAND_CSS = `<link rel="stylesheet" href="/assets/css/site.css">
<style id="brand-matrix">/* Styled shell for edge-rendered keyword/job/manufacturer/service x town pages. */ :root{--navy:#0B1F3A;--ink:#0B1F3A;--orange:#FF6B00;--muted:#555555;--line:#eeeeee} *{box-sizing:border-box} body.mx{margin:0;font:16px/1.65 ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:#222222;background:#f7f7f7} .mx a{color:var(--navy)} .mx-top{background:var(--ink);color:#fff;position:sticky;top:0;z-index:50} .mx-top-in{max-width:1120px;margin:0 auto;padding:.6rem 1rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap} .mx-logo{color:#fff!important;font-weight:800;text-decoration:none;font-size:1.1rem} .mx-logo span{color:var(--orange)} .mx-nav{display:flex;gap:.9rem;flex-wrap:wrap;font-size:.92rem} .mx-nav a{color:#dddddd!important;text-decoration:none} .mx-nav a:hover{color:var(--orange)!important} .mx-call{margin-left:auto;background:var(--orange);color:#fff!important;padding:.5rem .9rem;border-radius:.5rem;font-weight:700;text-decoration:none;white-space:nowrap} @media(max-width:767px){.mx-nav{order:3;width:100%;flex-wrap:wrap;gap:.35rem .9rem;font-size:.85rem;padding-bottom:.2rem}} .mx main{max-width:960px;margin:0 auto;padding:1.25rem 1rem 3rem;background:#fff} .mx nav[aria-label=Breadcrumb]{font-size:.85rem;color:var(--muted);margin-bottom:.75rem} .mx h1{font-size:clamp(1.6rem,4vw,2.4rem);line-height:1.2;color:var(--ink);margin:.3rem 0 1rem} .mx h2{font-size:1.35rem;color:var(--ink);margin:2rem 0 .6rem;padding-top:.4rem;border-top:3px solid var(--orange);display:inline-block} .mx h3{font-size:1.05rem;margin:1.2rem 0 .4rem;color:var(--navy)} .mx figure{margin:0 0 1.25rem;display:grid;gap:.5rem;grid-template-columns:2fr 1fr} .mx figure img{width:100%;height:auto;border-radius:.6rem;object-fit:cover;aspect-ratio:16/9} .mx figure img:first-child{grid-row:span 2;height:100%} .mx figure img:only-child{grid-column:1/-1} @media(max-width:600px){.mx figure{grid-template-columns:1fr}.mx figure img:not(:first-child){display:none}} .mx .related-links ul{list-style:none;padding:0;display:flex;flex-wrap:wrap;gap:.4rem} .mx .related-links li a{display:inline-block;padding:.3rem .7rem;border:1px solid var(--line);border-radius:999px;font-size:.88rem;text-decoration:none;background:#f9f9f9} .mx .related-links li a:hover{border-color:var(--orange);color:var(--orange)} .mx .faq-item{border:1px solid var(--line);border-radius:.5rem;padding:.7rem 1rem;margin:.5rem 0;background:#fafafa} .mx .faq-item summary{font-weight:600;cursor:pointer} .mx-cta{margin:2rem 0 0;padding:1.25rem;border-radius:.75rem;background:var(--navy);color:#fff} .mx-cta h2{color:#fff;border:0;margin:0 0 .4rem} .mx-cta a{display:inline-block;margin:.4rem .5rem 0 0;padding:.6rem 1rem;border-radius:.5rem;font-weight:700;text-decoration:none;color:#fff!important;background:var(--orange)} .mx-cta a.wa{background:#0B1F3A} .mx-foot{background:var(--ink);color:#cccccc;font-size:.88rem;padding:1.5rem 1rem 6rem;text-align:center} .mx-foot a{color:#fff} @media(max-width:480px){.mx-logo{font-size:1rem}.mx-call{padding:.4rem .65rem;font-size:.85rem}.mx-top-in{flex-wrap:wrap;gap:.5rem}}</style>`;
