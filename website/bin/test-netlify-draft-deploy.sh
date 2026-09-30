#!/usr/bin/env bash
# Guards for the draft-preview path. Does not call Netlify.
set -euo pipefail

root="$(cd "$(dirname "$0")/../.." && pwd)"
workflow="$root/.github/workflows/netlify-draft-preview.yml"
script="$root/website/bin/netlify-draft-deploy.sh"

if grep -F -n -- '--prod' "$workflow"; then
  echo "draft workflow contains a production deploy flag" >&2
  exit 1
fi

python3 - "$script" << 'PY'
import pathlib, sys
path = pathlib.Path(sys.argv[1])
for number, line in enumerate(path.read_text(encoding="utf-8").splitlines(), 1):
    code = line.split("#", 1)[0]
    if "--prod" not in code:
        continue
    if "grep" in code or "printf" in code or "refusing draft deploy" in code:
        continue
    raise SystemExit(f"production deploy flag outside the guard: {path}:{number}:{line}")
PY

dist="$(mktemp -d)"
echo "<html></html>" > "$dist/index.html"

set +e
bash "$script" "$dist" 11 --prod >/tmp/draft-prod.out 2>/tmp/draft-prod.err
code=$?
set -e
if [ "$code" -ne 2 ]; then
  echo "expected exit 2 when --prod is passed, got $code" >&2
  cat /tmp/draft-prod.err >&2
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
if ! grep -F -q "Production was not deployed" /tmp/draft-empty.err; then
  echo "missing-token message did not say production was not deployed" >&2
  exit 1
fi

echo "netlify draft deploy guards passed"
