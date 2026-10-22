/** Twin of the consent-gated gtag loader in website/includes/header.php.
 * One gtag.js loader, gtag('config') for both GA4 properties. Idempotent. */
export const GA_IDS = ["G-RN048B7861", "G-G8NTX4JMC9"];
const SNIPPET = `<script id="icomply-ga">window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});
window.__icomplyAnalytics={ga:${JSON.stringify(GA_IDS[0])},ga2:${JSON.stringify(GA_IDS[1])},aw:"",gtm:"",load:function(){if(window.__icomplyAnalyticsLoaded)return;window.__icomplyAnalyticsLoaded=true;gtag('consent','update',{ad_storage:'granted',ad_user_data:'granted',ad_personalization:'granted',analytics_storage:'granted'});var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(this.ga);document.head.appendChild(s);gtag('js',new Date());gtag('config',this.ga);if(this.ga2&&this.ga2!==this.ga)gtag('config',this.ga2);}};
try{if(localStorage.getItem('icomply_cookie_consent')==='accepted')window.__icomplyAnalytics.load();}catch(e){}</script>`;

export function withAnalytics(html) {
  let s = String(html);
  if (!/<\/head>/i.test(s)) return s;
  if (GA_IDS.every((id) => s.includes(id))) return s;
  if (s.includes("__icomplyAnalytics")) {
    // Page already carries the PHP loader (single-ID era): add the second config to it.
    return s.replace(/(ga:\s*"G-[A-Z0-9]+",)/, `$1 ga2: ${JSON.stringify(GA_IDS[1])},`)
      .replace(/if \(this\.ga\) gtag\('config', this\.ga\);/, `if (this.ga) gtag('config', this.ga); if (this.ga2 && this.ga2 !== this.ga) gtag('config', this.ga2);`);
  }
  return s.replace(/<\/head>/i, SNIPPET + "</head>");
}
