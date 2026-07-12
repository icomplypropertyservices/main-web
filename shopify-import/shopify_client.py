#!/usr/bin/env python3
"""Shared Shopify Admin API client for iComply Supplys.

Credentials are loaded from environment variables or a local credentials file
(never hard-coded). Required:

  SHOPIFY_CLIENT_ID
  SHOPIFY_CLIENT_SECRET

Optional:

  SHOPIFY_STORE          (default: icomply-supplys.myshopify.com)
  SHOPIFY_CREDENTIALS    (path to key=value credentials file)
"""

from __future__ import annotations

import json
import os
import time
import urllib.error
import urllib.request
from pathlib import Path

API_VERSION = "2024-10"
_DEFAULT_CRED_FILE = Path(os.environ.get(
    "SHOPIFY_CREDENTIALS",
    str(Path.home() / "shopify-credentials.txt"),
))
CRED_FILE = _DEFAULT_CRED_FILE

_token: str | None = None
_token_ts: float = 0
_creds_cache: dict[str, str] | None = None


def _parse_cred_file(path: Path) -> dict[str, str]:
    out: dict[str, str] = {}
    if not path.is_file():
        return out
    for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, _, val = line.partition("=")
        out[key.strip()] = val.strip()
    return out


def _load_creds() -> dict[str, str]:
    global _creds_cache
    if _creds_cache is not None:
        return _creds_cache
    file_creds = _parse_cred_file(CRED_FILE)
    store = (
        os.environ.get("SHOPIFY_STORE")
        or file_creds.get("Store")
        or "icomply-supplys.myshopify.com"
    )
    client_id = os.environ.get("SHOPIFY_CLIENT_ID") or file_creds.get("ClientId") or ""
    client_secret = (
        os.environ.get("SHOPIFY_CLIENT_SECRET") or file_creds.get("ClientSecret") or ""
    )
    if not client_id or not client_secret:
        raise RuntimeError(
            "Shopify credentials missing. Set SHOPIFY_CLIENT_ID and "
            "SHOPIFY_CLIENT_SECRET, or create a credentials file with "
            f"ClientId=... and ClientSecret=... at {CRED_FILE}"
        )
    _creds_cache = {
        "store": store,
        "client_id": client_id,
        "client_secret": client_secret,
    }
    return _creds_cache


def get_store() -> str:
    return _load_creds()["store"]


def get_token(force: bool = False) -> str:
    global _token, _token_ts
    if not force and _token and (time.time() - _token_ts) < 80000:
        return _token
    creds = _load_creds()
    store = creds["store"]
    client_id = creds["client_id"]
    client_secret = creds["client_secret"]
    data = json.dumps(
        {
            "client_id": client_id,
            "client_secret": client_secret,
            "grant_type": "client_credentials",
        }
    ).encode()
    req = urllib.request.Request(
        f"https://{store}/admin/oauth/access_token",
        data=data,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=60) as resp:
        payload = json.loads(resp.read().decode())
    _token = payload["access_token"]
    _token_ts = time.time()
    try:
        CRED_FILE.write_text(
            f"Store={store}\nClientId={client_id}\nClientSecret={client_secret}\n"
            f"AccessToken={_token}\nScope={payload.get('scope','')}\n"
            f"Updated={time.strftime('%Y-%m-%dT%H:%M:%S')}\n",
            encoding="utf-8",
        )
    except OSError:
        pass
    return _token


def api(method: str, path: str, body: dict | None = None, retries: int = 5) -> dict:
    token = get_token()
    store = get_store()
    url = f"https://{store}/admin/api/{API_VERSION}{path}"
    data = None if body is None else json.dumps(body).encode()
    for attempt in range(retries):
        req = urllib.request.Request(
            url,
            data=data,
            method=method,
            headers={
                "X-Shopify-Access-Token": token,
                "Content-Type": "application/json",
                "Accept": "application/json",
            },
        )
        try:
            with urllib.request.urlopen(req, timeout=120) as resp:
                raw = resp.read().decode()
                time.sleep(0.35)  # rate limit courtesy
                return json.loads(raw) if raw else {}
        except urllib.error.HTTPError as e:
            err = e.read().decode(errors="replace")
            if e.code in (429, 500, 502, 503, 504) and attempt < retries - 1:
                time.sleep(1.5 * (attempt + 1))
                if e.code == 401:
                    token = get_token(force=True)
                continue
            if e.code == 401 and attempt < retries - 1:
                token = get_token(force=True)
                continue
            raise RuntimeError(f"{method} {path} -> {e.code}: {err}") from e
    return {}


def graphql(query: str, variables: dict | None = None) -> dict:
    token = get_token()
    store = get_store()
    payload = {"query": query, "variables": variables or {}}
    req = urllib.request.Request(
        f"https://{store}/admin/api/{API_VERSION}/graphql.json",
        data=json.dumps(payload).encode(),
        method="POST",
        headers={
            "X-Shopify-Access-Token": token,
            "Content-Type": "application/json",
        },
    )
    with urllib.request.urlopen(req, timeout=120) as resp:
        data = json.loads(resp.read().decode())
    time.sleep(0.35)
    if data.get("errors"):
        raise RuntimeError(f"GraphQL errors: {data['errors']}")
    return data
