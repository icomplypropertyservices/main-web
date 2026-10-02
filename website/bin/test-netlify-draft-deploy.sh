#!/usr/bin/env bash
# Guards for the draft-preview path. Does not call Netlify.
set -euo pipefail

root="$(cd "$(dirname "$0")/../.." && pwd)"
workflow="$root/.github/workflows/netlify-draft-preview.yml"
action="$root/.github/actions/netlify-draft-preview/action.yml"
script="$root/website/bin/netlify-draft-deploy.sh"
production="$root/.github/workflows/netlify-deploy.yml"

for file in "$workflow" "$action"; do
  if grep -F -n -- '--prod' "$file"; then
    echo "draft workflow contains a production deploy flag: $file" >&2
    exit 1
  fi
  if grep -n -E 'clear_cache|/builds' "$file"; then
    echo "draft workflow queues a site build: $file" >&2
    exit 1
  fi
done

if grep -n -E 'environment:.*Production' "$workflow"; then
  echo "draft workflow selects a Production environment" >&2
  exit 1
fi

if grep -n 'pull_request' "$production"; then
  echo "production workflow must keep its current triggers" >&2
  exit 1
fi

if ! grep -F -q 'args: deploy --dir=dist --prod --message' "$production"; then
  echo "production workflow lost its production deploy step" >&2
  exit 1
fi

python3 - "$script" << 'PY'
import pathlib, sys
path = pathlib.Path(sys.argv[1])
text = path.read_text(encoding="utf-8")
if 'NETLIFY_CLI_VERSION="27.10.2"' not in text or "netlify-cli@${NETLIFY_CLI_VERSION}" not in text:
    raise SystemExit("draft deploy script must pin netlify-cli@27.10.2")
if "545c5016-825a-498c-923d-f5a177875b31" not in text:
    raise SystemExit("draft deploy script must keep the public project id fallback")
for number, line in enumerate(text.splitlines(), 1):
    code = line.split("#", 1)[0]
    if "--prod" not in code:
        continue
    if "grep" in code or "printf" in code or "refusing draft deploy" in code:
        continue
    raise SystemExit(f"production deploy flag outside the guard: {path}:{number}:{line}")
PY

dist="$(mktemp -d)"
echo "<html></html>" > "$dist/index.html"
printf '/*\n  X-Content-Type-Options: nosniff\n' > "$dist/_headers"
printf 'User-agent: *\nAllow: /\n' > "$dist/robots.txt"

set +e
bash "$script" "$dist" 11 --prod >/tmp/draft-prod.out 2>/tmp/draft-prod.err
code=$?
set -e
if [ "$code" -ne 2 ]; then
  echo "expected exit 2 when a production flag is passed, got $code" >&2
  cat /tmp/draft-prod.err >&2
  exit 1
fi
if grep -F -q 'noindex' "$dist/_headers"; then
  echo "production flag path rewrote draft headers" >&2
  exit 1
fi

set +e
NETLIFY_AUTH_TOKEN= NETLIFY_SITE_ID= bash "$script" "$dist" 11 >/tmp/draft-empty.out 2>/tmp/draft-empty.err
code=$?
set -e
if [ "$code" -ne 1 ]; then
  echo "expected exit 1 when the token is missing, got $code" >&2
  cat /tmp/draft-empty.err >&2
  exit 1
fi
if ! grep -F -q "production workflow was not run" /tmp/draft-empty.err; then
  echo "missing-token message did not say the production workflow was not run" >&2
  exit 1
fi
if ! grep -F -q 'Allow: /' "$dist/robots.txt"; then
  echo "missing-token path rewrote robots.txt" >&2
  exit 1
fi

set +e
NETLIFY_AUTH_TOKEN= bash "$script" check-auth >/tmp/draft-auth.out 2>/tmp/draft-auth.err
code=$?
set -e
if [ "$code" -ne 1 ]; then
  echo "expected exit 1 when check-auth has no token, got $code" >&2
  cat /tmp/draft-auth.err >&2
  exit 1
fi

out="$(mktemp)"
NETLIFY_AUTH_TOKEN=test-token NETLIFY_DRAFT_DRY_RUN=1 \
  bash "$script" "$dist" 11 "$out" >/tmp/draft-dry.out
expected="$(printf '%s\n' \
  "https://pr-11--icomply-main-web.netlify.app" \
  "https://deploy-preview-11--icomply-main-web.netlify.app")"
if [ "$(cat "$out")" != "$expected" ]; then
  echo "dry run URLs did not match the draft alias contract" >&2
  cat "$out" >&2
  exit 1
fi
if ! grep -F -q 'X-Robots-Tag: noindex, nofollow' "$dist/_headers"; then
  echo "dry run did not mark the draft upload noindex" >&2
  exit 1
fi
if ! grep -F -q 'Disallow: /' "$dist/robots.txt"; then
  echo "dry run did not replace robots.txt" >&2
  exit 1
fi

echo "netlify draft deploy guards passed"
