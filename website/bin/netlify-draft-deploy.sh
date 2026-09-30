#!/usr/bin/env bash
# Publish one pre-rendered dist/ to two non-production Netlify aliases:
#   pr-<N>--<site>.netlify.app
#   deploy-preview-<N>--<site>.netlify.app
# This script has no production deploy path.
set -euo pipefail

if printf '%s\n' "$@" | grep -F -q -- '--prod'; then
  echo "refusing draft deploy: --prod is not allowed" >&2
  exit 2
fi

DIST_DIR="${1:-}"
PR_NUMBER="${2:-}"

if [ -z "$DIST_DIR" ] || [ -z "$PR_NUMBER" ]; then
  echo "usage: netlify-draft-deploy.sh <dist-dir> <pr-number>" >&2
  exit 1
fi

if ! [[ "$PR_NUMBER" =~ ^[0-9]+$ ]]; then
  echo "PR number must be digits, got: $PR_NUMBER" >&2
  exit 1
fi

if [ ! -d "$DIST_DIR" ] || [ ! -f "$DIST_DIR/index.html" ]; then
  echo "dist dir is missing index.html: $DIST_DIR" >&2
  exit 1
fi

if [ -z "${NETLIFY_AUTH_TOKEN:-}" ]; then
  echo "NETLIFY_AUTH_TOKEN is not set. Draft deploy skipped. Production was not deployed." >&2
  exit 1
fi

if [ -z "${NETLIFY_SITE_ID:-}" ]; then
  echo "NETLIFY_SITE_ID is not set; reading the public site id from the live Netlify host." >&2
  html="$(curl -fsS "https://icomply-main-web.netlify.app/")"
  NETLIFY_SITE_ID="$(printf '%s' "$html" | python3 -c '
import re, sys
html = sys.stdin.read()
match = re.search(r"data-netlify-site-id=\"([0-9a-f-]{36})\"", html)
if not match:
    raise SystemExit("public site id not found")
print(match.group(1))
')"
  export NETLIFY_SITE_ID
fi

export NETLIFY_TELEMETRY_DISABLED=1
export CI=1

site_json="$(curl -fsS \
  -H "Authorization: Bearer ${NETLIFY_AUTH_TOKEN}" \
  "https://api.netlify.com/api/v1/sites/${NETLIFY_SITE_ID}")"

SITE_NAME="$(printf '%s' "$site_json" | python3 -c '
import json, sys
site = json.load(sys.stdin)
name = site.get("name") or ""
if not name:
    raise SystemExit("site name missing")
settings = site.get("build_settings") or {}
skip = settings.get("skip_prs") if isinstance(settings, dict) else None
print(name)
print("skip_prs=" + ("unset" if skip is None else str(bool(skip)).lower()), file=sys.stderr)
print("repo_branch=" + str(settings.get("repo_branch") or ""), file=sys.stderr)
')"

OUT_FILE="${3:-}"
if [ -z "$OUT_FILE" ]; then
  OUT_FILE="$(mktemp)"
fi
: > "$OUT_FILE"

deploy_alias() {
  local alias="$1"
  local expected="https://${alias}--${SITE_NAME}.netlify.app"
  local tmp
  tmp="$(mktemp)"
  echo "draft alias ${alias} -> ${expected}"
  # Draft upload of an already rendered dist/. No build, no production flag.
  npx --yes netlify-cli deploy \
    --site "$NETLIFY_SITE_ID" \
    --dir "$DIST_DIR" \
    --no-build \
    --alias "$alias" \
    --json \
    --message "draft PR #${PR_NUMBER} ${alias}" \
    > "$tmp"
  python3 - "$tmp" "$expected" "$alias" << 'PY'
import json, sys
raw = open(sys.argv[1], encoding="utf-8").read()
start, end = raw.find("{"), raw.rfind("}")
if start < 0 or end < start:
    raise SystemExit("netlify deploy did not return JSON")
data = json.loads(raw[start:end + 1])
context = str(data.get("context") or "")
if context == "production":
    raise SystemExit("refusing deploy whose context is production")
url = str(data.get("url") or data.get("deploy_url") or "")
prod_hosts = {
    "https://icomply-main-web.netlify.app",
    "https://icomplypropertyservices.co.uk",
    "http://icomply-main-web.netlify.app",
    "http://icomplypropertyservices.co.uk",
}
if url.rstrip("/") in prod_hosts:
    raise SystemExit("refusing deploy that returned the production URL")
print("cli_url=" + url)
print("expected=" + sys.argv[2])
PY
  echo "${expected}" >> "$OUT_FILE"
  rm -f "$tmp"
}

deploy_alias "pr-${PR_NUMBER}"
deploy_alias "deploy-preview-${PR_NUMBER}"

echo "draft URLs:"
cat "$OUT_FILE"
