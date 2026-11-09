"""One-time: download public media of the live site into tools/fixtures/ (read-only on the live site).

python tools/fetch_fixtures.py   -> tools/fixtures/uploads/<yyyy>/<mm>/<file>, tools/fixtures/media.json
"""
import html
import json
import pathlib
import urllib.request

LIVE = "https://karkasvorot.by"
OUT = pathlib.Path(__file__).resolve().parent / "fixtures"


def get(url):
    req = urllib.request.Request(url, headers={"User-Agent": "karkasvorot-fixtures"})
    with urllib.request.urlopen(req, timeout=60) as r:
        return r.read()


def main():
    items = json.loads(get(LIVE + "/index.php?rest_route=/wp/v2/media&per_page=100"))
    media = []
    for m in items:
        path = m["source_url"].split("/wp-content/uploads/", 1)[1]
        dest = OUT / "uploads" / path
        if not dest.exists():
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_bytes(get(m["source_url"]))
        media.append({
            "path": path,
            "alt": html.unescape(m["alt_text"]),
            "title": html.unescape(m["title"]["rendered"]),
            "mime": m["mime_type"],
        })
    media.sort(key=lambda x: x["path"])
    (OUT / "media.json").write_text(json.dumps(media, ensure_ascii=False, indent=1), encoding="utf-8")
    print(len(media), "files")


if __name__ == "__main__":
    main()
