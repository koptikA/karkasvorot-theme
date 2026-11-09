"""Helpers for the local Playground stand (stdlib only).

As a module (used by tools/checks/*):
    fetch(url, opener=None, data=None, follow=True) -> (status:int, body:str, final_url:str)
    admin_session(base) -> urllib.request.OpenerDirector logged in as admin/password, or None
    run_php(base, code) -> str   # runs PHP (no "<?php") inside WordPress on the stand
As a CLI:
    python tools/stand.py php 'echo get_option("home");' [--base http://127.0.0.1:9400]
"""
import http.cookiejar
import sys
import urllib.error
import urllib.parse
import urllib.request

BASE = "http://127.0.0.1:9400"


class _NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


def fetch(url, opener=None, data=None, follow=True):
    if isinstance(data, dict):
        data = urllib.parse.urlencode(data).encode()
    elif isinstance(data, str):
        data = data.encode()
    if opener is None:
        opener = urllib.request.build_opener() if follow else urllib.request.build_opener(_NoRedirect)
    elif not follow:
        opener = urllib.request.build_opener(_NoRedirect, *[h for h in opener.handlers if isinstance(h, urllib.request.HTTPCookieProcessor)])
    try:
        with opener.open(url, data=data, timeout=120) as r:
            return r.status, r.read().decode("utf-8", "replace"), r.geturl()
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace"), e.geturl() or url


def admin_session(base=BASE):
    jar = http.cookiejar.CookieJar()
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    try:
        fetch(base + "/wp-login.php", op)
        fetch(base + "/wp-login.php", op, {"log": "admin", "pwd": "password", "testcookie": "1", "redirect_to": base + "/wp-admin/"})
        status, body, url = fetch(base + "/wp-admin/", op)
    except OSError:
        return None
    return op if status == 200 and "wp-login.php" not in url else None


def run_php(base, code):
    status, body, _ = fetch(base + "/kv-run.php", data=code)
    if status != 200:
        raise RuntimeError("run_php HTTP %s: %s" % (status, body[:500]))
    return body


if __name__ == "__main__":
    args = sys.argv[1:]
    base = BASE
    if "--base" in args:
        i = args.index("--base")
        base = args[i + 1].rstrip("/")
        del args[i:i + 2]
    if len(args) != 2 or args[0] != "php":
        sys.exit(__doc__)
    sys.stdout.reconfigure(encoding="utf-8")
    sys.stdout.write(run_php(base, args[1]))
