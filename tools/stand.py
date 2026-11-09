"""Helpers for the local Playground stand (stdlib only).

As a module (used by tools/checks/*):
    fetch(url, opener=None, data=None, follow=True, headers=None) -> (status:int, body:str, final_url:str)
    admin_session(base) -> urllib.request.OpenerDirector logged in as admin/password, or None
    location(url) -> (status, Location)   # no redirect following
    render_head(base, setup, page_expr) -> str   # <title> + wp_head() of a page
    run_php(base, code) -> str   # sends X-KV-Token from tools/stand/.token; runs PHP (no "<?php") inside WordPress on the stand
As a CLI:
    python tools/stand.py php 'echo get_option("home");' [--base http://127.0.0.1:9400]
"""
import http.cookiejar
import pathlib
import re
import sys
import urllib.error
import urllib.parse
import urllib.request

BASE = "http://127.0.0.1:9400"
# PHP errors shown by WP_DEBUG_DISPLAY; re.S because a fatal "Uncaught Error ... Stack trace ... on line" spans lines.
PHP_ERR = re.compile(r"(<b>)?(Warning|Notice|Deprecated|Fatal error|Parse error)(</b>)?:\s.{0,3000}?\son line"
                     r"|There has been a critical error|критическая ошибка", re.I | re.S)


class _NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


def fetch(url, opener=None, data=None, follow=True, headers=None):
    if isinstance(data, dict):
        data = urllib.parse.urlencode(data).encode()
    elif isinstance(data, str):
        data = data.encode()
    if opener is None:
        opener = urllib.request.build_opener() if follow else urllib.request.build_opener(_NoRedirect)
    elif not follow:
        opener = urllib.request.build_opener(_NoRedirect, *[h for h in opener.handlers if isinstance(h, urllib.request.HTTPCookieProcessor)])
    req = urllib.request.Request(url, data=data, headers=headers or {})
    try:
        with opener.open(req, timeout=120) as r:
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


def location(url):
    """-> (status, Location header) without following redirects."""
    try:
        with urllib.request.build_opener(_NoRedirect).open(url, timeout=120) as r:
            return r.status, None
    except urllib.error.HTTPError as e:
        return e.code, e.headers.get("Location")


def render_head(base, setup, page):
    """<title> + wp_head() of the page with id expression `page`, as a guest, after PHP `setup` (lives one request)."""
    return run_php(base, (
        "wp_set_current_user(0); %s $kv_id = (int) (%s); $q = $GLOBALS['wp_query']; $q->query(array('page_id' => $kv_id));"
        " $GLOBALS['wp_the_query'] = $q; $GLOBALS['post'] = $q->post;"
        " echo '<title>' . wp_get_document_title() . '</title>'; wp_head();"
    ) % (setup, page))


TOKEN_FILE = pathlib.Path(__file__).resolve().parent / "stand" / ".token"


def run_php(base, code):
    if urllib.parse.urlsplit(base).hostname not in ("127.0.0.1", "localhost", "::1"):
        raise ValueError("run_php is restricted to the local Playground")
    token = TOKEN_FILE.read_text().strip() if TOKEN_FILE.exists() else ""
    status, body, _ = fetch(base + "/kv-run.php", data=code, headers={"X-KV-Token": token})
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
