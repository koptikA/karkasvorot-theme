"""Build dist/karkasvorot.zip (folder karkasvorot/ inside); optionally install it on a clean stand by upload.

    python tools/build_zip.py                                    # build only
    python tools/build_zip.py --install http://127.0.0.1:9406    # + upload via «Темы → Добавить → Загрузить», activate, check
Clean stand without the theme:
    MSYS_NO_PATHCONV=1 npx -y @wp-playground/cli@latest server --port 9406 --php 7.4 --blueprint tools/stand/blueprint-clean.json
"""
import pathlib
import re
import sys
from urllib.parse import urlsplit
import urllib.request
import uuid
import zipfile

ROOT = pathlib.Path(__file__).resolve().parent.parent
THEME = ROOT / "theme" / "karkasvorot"
ZIP = ROOT / "dist" / "karkasvorot.zip"
sys.path.insert(0, str(ROOT / "tools"))


def build():
    ZIP.parent.mkdir(exist_ok=True)
    with zipfile.ZipFile(ZIP, "w", zipfile.ZIP_DEFLATED) as z:
        for p in sorted(THEME.rglob("*")):
            if p.is_file() and "__pycache__" not in p.parts and not p.name.startswith("."):
                z.write(p, "karkasvorot/" + p.relative_to(THEME).as_posix())
    return ZIP


def install(base):
    """Upload the zip like the owner does, activate it, open pages; -> list of problems ([] = ok)."""
    from stand import PHP_ERR, admin_session, fetch
    if urlsplit(base).hostname not in ("127.0.0.1", "localhost", "::1"):
        raise ValueError("Use a local Playground URL only")
    s = admin_session(base)
    if not s:
        return ["нет входа admin/password на %s" % base]
    _, form, _ = fetch(base + "/wp-admin/theme-install.php?upload", s)
    nonce = re.search(r'name="_wpnonce" value="([^"]+)"', form)
    if not nonce:
        return ["нет формы загрузки темы"]
    b = uuid.uuid4().hex
    body = b"".join([
        ('--%s\r\nContent-Disposition: form-data; name="_wpnonce"\r\n\r\n%s\r\n' % (b, nonce.group(1))).encode(),
        ('--%s\r\nContent-Disposition: form-data; name="themezip"; filename="karkasvorot.zip"\r\n'
         'Content-Type: application/zip\r\n\r\n' % b).encode(),
        ZIP.read_bytes(),
        ("\r\n--%s--\r\n" % b).encode()])
    req = urllib.request.Request(base + "/wp-admin/update.php?action=upload-theme", data=body,
                                 headers={"Content-Type": "multipart/form-data; boundary=" + b})
    with s.open(req, timeout=300) as r:
        res = r.read().decode("utf-8", "replace")
    act = re.search(r'href="([^"]*themes\.php\?action=activate[^"]*stylesheet=karkasvorot[^"]*)"', res)
    if not act:
        return ["загрузка zip не дала ссылку «Активировать»: %s" % re.sub(r"\s+", " ", re.sub(r"<[^>]+>", " ", res))[-400:]]
    fails = []
    st, html, _ = fetch(act.group(1).replace("&#038;", "&").replace("&amp;", "&"), s)
    err = PHP_ERR.search(html)
    if st != 200 or err:
        fails.append("активация: статус %s %s" % (st, err.group(0)[:200] if err else ""))
    for path in ("/", "/karkas-otkatnyh-vorot/", "/kontakty/", "/nashi-raboty/"):
        st, html, _ = fetch(base + path)
        err = PHP_ERR.search(html)
        if st != 200 or err or "kv-header" not in html:
            fails.append("%s после активации: статус %s %s" % (path, st, err.group(0)[:200] if err else "(или не наша тема)"))
    return fails


if __name__ == "__main__":
    sys.stdout.reconfigure(encoding="utf-8")
    print("built %s, %d bytes" % (build(), ZIP.stat().st_size))
    if "--install" in sys.argv:
        problems = install(sys.argv[sys.argv.index("--install") + 1].rstrip("/"))
        print("\n".join(problems) or "zip installed and activated without errors")
        sys.exit(1 if problems else 0)
