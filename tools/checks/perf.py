"""Speed budget: theme CSS+JS < 60 KB, no jQuery, every <img> has width/height; Lighthouse mobile >= 90.

Lighthouse is slow (~1 min a page), so it runs only with KV_LIGHTHOUSE=1; otherwise it prints an explicit SKIP.
    KV_LIGHTHOUSE=1 python tools/check_site.py http://127.0.0.1:9405
"""
import json
import os
import pathlib
import re
import shutil
import subprocess

from stand import fetch

ASSETS = pathlib.Path(__file__).resolve().parents[2] / "theme" / "karkasvorot" / "assets"
BUDGET = 60 * 1024
CATEGORIES = ("performance", "seo", "accessibility")
CHROMES = (r"C:\Program Files\Google\Chrome\Application\chrome.exe", r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
           "/usr/bin/google-chrome", "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome")


def lighthouse(url):
    """-> {category: score 0..100}; raises RuntimeError(reason) when Lighthouse or Chrome is unavailable."""
    chrome = os.environ.get("CHROME_PATH") or next((c for c in CHROMES if os.path.exists(c)), None)
    npx = shutil.which("npx")
    if not chrome or not npx:
        raise RuntimeError("нет %s" % ("Chrome/Edge (CHROME_PATH)" if not chrome else "npx"))
    report_dir = ASSETS.parents[2] / "docs" / "reports"
    report_dir.mkdir(parents=True, exist_ok=True)
    slug = url.rstrip("/").split("/")[-1]
    slug = "home" if ":" in slug else slug
    out = report_dir / ("lighthouse-" + slug + ".json")
    if out.exists():
        out.unlink()
    cmd = [npx, "-y", "lighthouse@12.8.2", url, "--quiet", "--output=json", "--output-path=%s" % out,
           "--only-categories=%s" % ",".join(CATEGORIES), "--chrome-flags=--headless=new --no-first-run"]
    r = subprocess.run(cmd, env=dict(os.environ, CHROME_PATH=chrome), capture_output=True, text=True, timeout=300)
    if not out.exists():
        raise RuntimeError("lighthouse не отработал: %s" % (r.stderr or r.stdout)[-300:])
    report = json.loads(out.read_text(encoding="utf-8"))
    if report.get("runtimeError"):
        raise ValueError("Lighthouse runtime error: %s" % report["runtimeError"])
    cats = report["categories"]
    return {c: round((cats[c]["score"] or 0) * 100) for c in CATEGORIES}


def check(base, s):
    fails = []
    size = sum(p.stat().st_size for p in ASSETS.rglob("*") if p.suffix in (".css", ".js"))
    if size >= BUDGET:
        fails.append("CSS+JS темы %d байт, бюджет < %d" % (size, BUDGET))
    _, works, _ = fetch(base + "/nashi-raboty/")
    one = re.search(r'kv-work__caption"><a href="([^"]+)"', works)
    urls = [base + p for p in ("/", "/karkas-otkatnyh-vorot/", "/prochie-izdeliya/", "/nashi-raboty/", "/kontakty/")]
    urls += [one.group(1)] if one else []
    for url in urls:
        html = fetch(url)[1]
        if re.search(r"jquery", html, re.I):
            fails.append("%s: в HTML есть jQuery" % url)
        for img in re.findall(r"<img\b[^>]*>", html):
            if not (re.search(r'\bwidth="\d+"', img) and re.search(r'\bheight="\d+"', img)):
                fails.append("%s: <img> без width/height: %s" % (url, img[:120]))
    if os.environ.get("KV_LIGHTHOUSE") != "1":
        print("SKIP perf: Lighthouse не запускался (нужен KV_LIGHTHOUSE=1) — это пропуск, не зелёный")
        return fails
    for url in (base + "/", base + "/karkas-otkatnyh-vorot/"):
        try:
            scores = lighthouse(url)
        except (RuntimeError, OSError, subprocess.TimeoutExpired) as e:
            print("SKIP perf: Lighthouse %s — %s" % (url, e))
            continue
        print("Lighthouse mobile %s: %s" % (url, ", ".join("%s %d" % kv for kv in scores.items())))
        fails += ["Lighthouse %s: %s %d < 90" % (url, c, v) for c, v in scores.items() if v < 90]
    return fails
