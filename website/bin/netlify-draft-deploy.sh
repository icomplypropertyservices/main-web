#!/usr/bin/env bash
# Upload one pre-rendered dist/ to non-production Netlify draft aliases:
#   https://pr-<N>--<site>.netlify.app
#   https://deploy-preview-<N>--<site>.netlify.app
# There is no production upload in this script.
set -euo pipefail

PUBLIC_SITE_ID="545c5016-825a-498c-923d-f5a177875b31"
# Pinned so CI does not float to a later CLI that changes draft alias behaviour.
NETLIFY_CLI_VERSION="27.10.2"

if printf '%s\n' "$@" | grep -F -q -- '--prod'; then
  echo "refusing draft deploy: production flag is not allowed" >&2
  exit 2
fi

fail() {
  echo "$1" >&2
  echo "Draft upload stopped. The production workflow was not run." >&2
  exit 1
}

resolve_site_id() {
  if [ -n "${NETLIFY_SITE_ID:-}" ]; then
    return 0
  fi
  NETLIFY_SITE_ID="$PUBLIC_SITE_ID"
  export NETLIFY_SITE_ID
}

fetch_site() {
  python3 - "$NETLIFY_SITE_ID" << 'PY'
import json, os, sys, urllib.error, urllib.request

site_id = sys.argv[1]
token = os.environ["NETLIFY_AUTH_TOKEN"]
req = urllib.request.Request(
    f"https://api.netlify.com/api/v1/sites/{site_id}",
    headers={"Authorization": f"Bearer {token}", "User-Agent": "icomply-draft-preview"},
)
try:
    with urllib.request.urlopen(req, timeout=60) as resp:
        site = json.load(resp)
except urllib.error.HTTPError as exc:
    print(f"site lookup HTTP {exc.code}", file=sys.stderr)
    raise SystemExit(1)
name = site.get("name") or ""
if not name:
    print("site name missing", file=sys.stderr)
    raise SystemExit(1)
print(name)
PY
}

scrape_public_site_id() {
  python3 - << 'PY'
import re, sys, urllib.request

req = urllib.request.Request(
    "https://icomply-main-web.netlify.app/",
    headers={"User-Agent": "icomply-draft-preview"},
)
with urllib.request.urlopen(req, timeout=60) as resp:
    html = resp.read().decode("utf-8", "replace")
match = re.search(r'data-netlify-site-id="([0-9a-f-]{36})"', html)
if not match:
    raise SystemExit("public site id not found")
print(match.group(1))
PY
}

check_auth() {
  if [ -z "${NETLIFY_AUTH_TOKEN:-}" ]; then
    fail "NETLIFY_AUTH_TOKEN is not set."
  fi
  resolve_site_id
  if ! site_name="$(fetch_site)"; then
    if [ "$NETLIFY_SITE_ID" = "$PUBLIC_SITE_ID" ]; then
      scraped="$(scrape_public_site_id)" || fail "Netlify rejected the token and the public project id."
      if [ "$scraped" != "$NETLIFY_SITE_ID" ]; then
        NETLIFY_SITE_ID="$scraped"
        export NETLIFY_SITE_ID
        site_name="$(fetch_site)" || fail "Netlify rejected the token."
      else
        fail "Netlify rejected the token."
      fi
    else
      fail "Netlify rejected the token or NETLIFY_SITE_ID."
    fi
  fi
  echo "draft site=${site_name} id_length=${#NETLIFY_SITE_ID}"
}

mark_draft_unindexed() {
  # Draft host only. The production exporter is not modified.
  printf '\n/*\n  X-Robots-Tag: noindex, nofollow\n' >> "$DIST_DIR/_headers"
  printf 'User-agent: *\nDisallow: /\n' > "$DIST_DIR/robots.txt"
}

install_cli() {
  local prefix
  prefix="$(mktemp -d)"
  npm install --prefix "$prefix" --no-save --silent "netlify-cli@${NETLIFY_CLI_VERSION}"
  NETLIFY_BIN="$prefix/node_modules/.bin/netlify"
  if [ ! -x "$NETLIFY_BIN" ]; then
    fail "netlify CLI ${NETLIFY_CLI_VERSION} did not install"
  fi
}

deploy_alias() {
  local alias="$1"
  local required="$2"
  local expected="https://${alias}--${SITE_NAME}.netlify.app"
  local tmp
  tmp="$(mktemp)"
  echo "draft alias ${alias} -> ${expected}"
  set +e
  "$NETLIFY_BIN" deploy \
    --site "$NETLIFY_SITE_ID" \
    --dir "$DIST_DIR" \
    --config "$NETLIFY_CONFIG" \
    --no-build \
    --alias "$alias" \
    --json \
    --message "draft PR #${PR_NUMBER} ${alias}" \
    >"$tmp"
  local code=$?
  set -e
  if [ "$code" -ne 0 ]; then
    echo "netlify deploy alias ${alias} exited ${code}" >&2
    tail -n 40 "$tmp" >&2 || true
    rm -f "$tmp"
    if [ "$required" = "yes" ]; then
      fail "required draft alias ${alias} was not uploaded"
    fi
    echo "optional alias ${alias} was not uploaded; keeping the aliases that succeeded" >&2
    return 0
  fi
  python3 - "$tmp" "$expected" << 'PY'
import json, sys
from urllib.parse import urlparse

raw = open(sys.argv[1], encoding="utf-8").read()
expected = sys.argv[2]
start, end = raw.find("{"), raw.rfind("}")
if start < 0 or end < start:
    raise SystemExit("netlify deploy did not return JSON")
data = json.loads(raw[start:end + 1])
context = str(data.get("context") or "")
if context == "production":
    raise SystemExit("refusing deploy whose context is production")
production_hosts = {
    "icomply-main-web.netlify.app",
    "icomplypropertyservices.co.uk",
    "www.icomplypropertyservices.co.uk",
}
for key in ("url", "deploy_url", "ssl_url"):
    value = str(data.get(key) or "")
    if not value:
        continue
    host = urlparse(value).hostname or ""
    if host in production_hosts:
        raise SystemExit(f"refusing deploy that returned the production URL via {key}")
    print(f"cli_{key}={value}")
print("expected=" + expected)
PY
  echo "${expected}" >> "$OUT_FILE"
  rm -f "$tmp"
}

if [ "${1:-}" = "check-auth" ]; then
  check_auth
  exit 0
fi

DIST_DIR="${1:-}"
PR_NUMBER="${2:-}"
OUT_FILE="${3:-}"

if [ -z "$DIST_DIR" ] || [ -z "$PR_NUMBER" ]; then
  echo "usage: netlify-draft-deploy.sh <dist-dir> <pr-number> [out-file]" >&2
  echo "       netlify-draft-deploy.sh check-auth" >&2
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
  fail "NETLIFY_AUTH_TOKEN is not set."
fi

mark_draft_unindexed

if [ -z "$OUT_FILE" ]; then
  OUT_FILE="$(mktemp)"
fi
: > "$OUT_FILE"

if [ "${NETLIFY_DRAFT_DRY_RUN:-}" = "1" ]; then
  SITE_NAME="${NETLIFY_DRAFT_DRY_RUN_SITE_NAME:-icomply-main-web}"
  echo "https://pr-${PR_NUMBER}--${SITE_NAME}.netlify.app" >> "$OUT_FILE"
  echo "https://deploy-preview-${PR_NUMBER}--${SITE_NAME}.netlify.app" >> "$OUT_FILE"
  cat "$OUT_FILE"
  exit 0
fi

if [ -z "${NETLIFY_CONFIG:-}" ] || [ ! -f "$NETLIFY_CONFIG" ]; then
  parent="$(dirname "$DIST_DIR")"
  if [ -f "$parent/netlify.toml" ]; then
    NETLIFY_CONFIG="$parent/netlify.toml"
  fi
fi
if [ -z "${NETLIFY_CONFIG:-}" ] || [ ! -f "$NETLIFY_CONFIG" ]; then
  fail "netlify.toml is missing, so the draft would not have pretty-URL redirects."
fi

check_auth
SITE_NAME="$(fetch_site)"
export NETLIFY_TELEMETRY_DISABLED=1
export CI=1
install_cli
deploy_alias "pr-${PR_NUMBER}" yes
deploy_alias "deploy-preview-${PR_NUMBER}" no

if ! grep -F -q "https://pr-${PR_NUMBER}--" "$OUT_FILE"; then
  fail "stable pr-${PR_NUMBER} draft URL was not recorded"
fi

echo "draft URLs:"
cat "$OUT_FILE"
