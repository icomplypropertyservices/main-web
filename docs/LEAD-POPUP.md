# Lead popup + email delivery (Netlify Forms)

The 10-second quote popup posts to a **Netlify Form** named `lead-popup`.
That is a real submission pipeline (not a fake toast). Netlify stores the
row and can email `info@icomplypropertyservices.co.uk`.

## What is in the HTML

- Hidden registration form + visible dialog form, both `name="lead-popup"`
  and `data-netlify="true"` (see `website/includes/lead-popup.php`).
- JS (`website/assets/js/lead-popup.js`) waits **10 seconds**, shows once
  per browser **session** (`sessionStorage`), and skips if already sent
  (`localStorage`). Escape / overlay / close button dismiss. Focus is trapped.
- Submit: `POST /` as `application/x-www-form-urlencoded` with `form-name=lead-popup`.
  On success the visitor is sent to `/thank-you?ref=lead-popup`.
- Quick CTAs: Call `07517806082`, WhatsApp, `/contact`.

Netlify only detects forms that exist in the **published HTML**. The hidden
form is in the shared footer (and compact matrix pages) so every deploy
registers it.

## Wire the inbox (Jack, once per site)

1. Open [Netlify](https://app.netlify.com) → site **icomply-main-web** → **Forms**.
2. After the first deploy that includes `lead-popup`, the form appears.
3. **Form notifications** → add **Email notification**:
   - Event: new submission
   - Address: `info@icomplypropertyservices.co.uk`
   - Confirm the inbox (Netlify sends a confirmation mail).

Until that notification is saved, submissions still land in the Netlify
Forms inbox even if email is not yet confirmed.

PHP `mail()` on `/contact` does **not** run on Netlify (static HTML).
Contact POSTs need a server; the popup uses Netlify Forms instead.

## Draft vs production

Draft deploys collect form submissions on the same site. Do not `--prod`
until Ellie says. Confirm the notification on the **icomply-main-web** site
so draft and later production share the same inbox.
