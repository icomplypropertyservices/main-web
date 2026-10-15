export default async () => {
  return new Response(`# iComply Property Services — robots.txt
# Search engines and AI assistants (ChatGPT, Perplexity, Gemini, Claude, Copilot, Grok, Apple) are welcome.

User-agent: *
Allow: /
Disallow: /admin/
Disallow: /bin/
Disallow: /data/
Disallow: /includes/
Disallow: /templates/
Disallow: /config.php
Disallow: /config.local.php

User-agent: Googlebot
User-agent: Google-Extended
User-agent: Bingbot
User-agent: msnbot
User-agent: GPTBot
User-agent: OAI-SearchBot
User-agent: ChatGPT-User
User-agent: PerplexityBot
User-agent: Perplexity-User
User-agent: ClaudeBot
User-agent: Claude-User
User-agent: Claude-SearchBot
User-agent: anthropic-ai
User-agent: Applebot
User-agent: Applebot-Extended
User-agent: xAI-Bot
User-agent: GrokBot
User-agent: Amazonbot
User-agent: DuckAssistBot
User-agent: CCBot
User-agent: meta-externalagent
User-agent: YandexBot
User-agent: DuckDuckBot
Allow: /
Disallow: /admin/
Disallow: /bin/
Disallow: /data/
Disallow: /config.php
Disallow: /config.local.php

Sitemap: https://icomplypropertyservices.co.uk/sitemap.xml
`, {
    headers: {
      "content-type": "text/plain; charset=utf-8",
      "cache-control": "public, max-age=3600",
    },
  });
};

export const config = { path: "/robots.txt" };