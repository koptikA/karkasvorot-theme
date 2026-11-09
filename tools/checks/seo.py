"""SEO: every public URL from the sitemap (+ home, works archive) has unique title/description, absolute canonical,
OG copying the page, absolute og:image, sane JSON-LD; robots.txt, sitemap, noindex, redirects, REST users,
and silence when Yoast/Rank Math is active. Missing pages are fine: only what the sitemap lists is checked."""
import json
import re
from html import unescape

from stand import fetch, location, render_head, run_php

BANNED = ("aggregateRating", "review", "FAQPage", "SearchAction", "sameAs")


def _render(base, setup, page):
    """-> (title, {@type: JSON-LD}, html) of page id expression `page` after PHP `setup` (filters live one request)."""
    html = render_head(base, setup, page)
    lds = {}
    for block in _ld(html):
        try:
            data = json.loads(block)
        except ValueError:
            data = {"@type": "UNPARSED"}
        lds[data.get("@type")] = data
    return _first(r"<title>(.*?)</title>", html), lds, html


def _php_str(v):
    return "'" + v.replace("\\", "\\\\").replace("'", "\\'") + "'"


ORG_KEYS = {"telephone": "phone", "email": "email", "openingHours": "hours", "taxID": "unp", "legalName": "org_name"}
ORG_VALUES = {"phone": "+375 29 111-22-33", "email": "a@b.by", "hours": "Mo-Fr 09:00-18:00", "unp": "123456789",
              "org_name": "ИП Тест <b>", "address": "г. Минск, ул. Тестовая, 1"}
SERVICE = "0"  # id of a service page; set once per run in check()
FRONT = "get_option('page_on_front')"


def _mods(values):
    return " ".join("add_filter('theme_mod_kv_%s', function () { return %s; });" % (k, _php_str(v)) for k, v in values.items())


def _schema_data(base):
    fails = []
    _, lds, _ = _render(base, _mods({k: "" for k in ORG_VALUES}), FRONT)
    org = lds.get("HomeAndConstructionBusiness")
    extra = [k for k in list(ORG_KEYS) + ["address"] if org and k in org]
    if not org or extra:
        fails.append("JSON-LD организации при пустых настройках: нет блока или лишние поля %s" % extra)
    _, lds, html = _render(base, _mods(ORG_VALUES), FRONT)
    org = lds.get("HomeAndConstructionBusiness") or {}
    for prop, key in ORG_KEYS.items():
        if org.get(prop) != ORG_VALUES[key]:
            fails.append("JSON-LD организации: %s=%r, ожидалось %r" % (prop, org.get(prop), ORG_VALUES[key]))
    if (org.get("address") or {}).get("streetAddress") != ORG_VALUES["address"]:
        fails.append("JSON-LD организации: address=%r" % org.get("address"))
    if "Тест <b>" in html:
        fails.append("JSON-LD не экранирует < (нужен JSON_HEX_TAG)")
    price = "add_filter('get_post_metadata', function ($v, $id, $key) { return '_kv_price_from' === $key ? array(%s) : $v; }, 10, 3);"
    _, lds, _ = _render(base, price % "''", SERVICE)
    if "Service" in lds:
        fails.append("Service в JSON-LD при пустой цене")
    for raw, want in (("1000 (проём 3×1,5 м)", 1000), ("800–1200", 800)):
        title, lds, _ = _render(base, price % _php_str(raw), SERVICE)
        if "Service" in lds or (title and re.search(r"цена от|BYN", title)):
            fails.append("внутренняя цена попала в SEO/JSON-LD: %r" % title)
    # Title swap without touching the DB: the_title filter + this request's post cache.
    swap = ("$kv_s = (int) (%s); $kv_new = 'Каркасы ворот в Минске & калитки';"
            " add_filter('the_title', function ($t, $id = 0) use ($kv_s, $kv_new) { return (int) $id === $kv_s ? $kv_new : $t; }, 10, 2);"
            " $kv_o = get_post($kv_s)->to_array(); $kv_o['post_title'] = $kv_new; wp_cache_set($kv_s, (object) $kv_o, 'posts');") % SERVICE
    title, lds, _ = _render(base, swap + price % "'500'", SERVICE)
    if not title or title.count("в Минске") != 1:
        fails.append("title удваивает «в Минске» или пуст: %r" % title)
    names = [i.get("name", "") for i in (lds.get("BreadcrumbList") or {}).get("itemListElement", [])]
    names.append((lds.get("Service") or {}).get("name", ""))
    if any(re.search(r"&\w+;|&#\d+;", n) for n in names):
        fails.append("HTML-сущности в именах JSON-LD: %r" % names)
    return fails


def _first(pattern, html):
    m = re.search(pattern, html, re.S)
    return unescape(m.group(1)).strip() if m else None


def _ld(html):
    return re.findall(r'<script type="application/ld\+json"[^>]*>(.*?)</script>', html, re.S)


def _page(url, base):
    """-> (fails, title, description) for one indexable page."""
    status, html, _ = fetch(url)
    if status != 200:
        return ["%s: статус %s" % (url, status)], None, None
    if re.search(r'<meta name=[\'"]robots[\'"] content=[\'"][^\'"]*noindex', html):
        return ["%s: в sitemap, но noindex" % url], None, None
    fails = []
    title = _first(r"<title>(.*?)</title>", html)
    desc = _first(r'<meta name="description" content="([^"]*)"', html)
    canon = _first(r'<link rel="canonical" href="([^"]*)"', html)
    og = {k: unescape(v) for k, v in re.findall(r'<meta property="(og:[a-z:_]+)" content="([^"]*)"', html)}
    if not title:
        fails.append("%s: нет <title>" % url)
    if not desc:
        fails.append("%s: нет meta description" % url)
    if not canon or not canon.startswith(base + "/") or "?" in canon:
        fails.append("%s: canonical не абсолютный или с query: %r" % (url, canon))
    if len(re.findall(r'<link rel="canonical"', html)) > 1:
        fails.append("%s: canonical выведен дважды" % url)
    for key, want in (("og:title", title), ("og:description", desc), ("og:url", canon)):
        if og.get(key) != want:
            fails.append("%s: %s=%r не совпадает со страницей (%r)" % (url, key, og.get(key), want))
    if not re.match(r"https?://", og.get("og:image", "")):
        fails.append("%s: og:image не абсолютный" % url)
    if og.get("og:locale") != "ru_RU":
        fails.append("%s: og:locale не ru_RU" % url)
    if 'name="twitter:card" content="summary_large_image"' not in html:
        fails.append("%s: нет twitter:card summary_large_image" % url)
    types = []
    for block in _ld(html):
        try:
            data = json.loads(block)
        except ValueError as e:
            fails.append("%s: JSON-LD не парсится: %s" % (url, e))
            continue
        if not {"@context", "@type"} <= set(data):
            fails.append("%s: JSON-LD без @context/@type" % url)
        types.append(data.get("@type"))
        for word in BANNED:
            if '"%s"' % word in block:
                fails.append("%s: в JSON-LD запрещённое %s" % (url, word))
    is_home = url.rstrip("/") == base
    if is_home and "HomeAndConstructionBusiness" not in types:
        fails.append("главная: нет JSON-LD HomeAndConstructionBusiness")
    if not is_home and "BreadcrumbList" not in types:
        fails.append("%s: нет JSON-LD BreadcrumbList" % url)
    return fails, title, desc


def _sitemap_urls(base, fails):
    status, index, _ = fetch(base + "/wp-sitemap.xml")
    if status != 200:
        fails.append("wp-sitemap.xml: статус %s" % status)
        return []
    subs = re.findall(r"<loc>(.*?)</loc>", index)
    bads = ["users", "taxonomies"]
    if run_php(base, "echo (int) wp_count_posts('post')->publish;").strip() == "0":
        bads.append("posts-post-")  # posts appear in the sitemap only once something is published
    for bad in bads:
        if any("wp-sitemap-%s" % bad in u for u in subs):
            fails.append("sitemap содержит %s" % bad)
    urls = []
    for sub in subs:
        urls += re.findall(r"<loc>(.*?)</loc>", fetch(sub)[1])
    return urls


def _plugin_silence(base):
    """Render the front page head inside WP with and without a fake WPSEO_VERSION."""
    own, quiet = render_head(base, "", FRONT), render_head(base, "define('WPSEO_VERSION', 'kv-test');", FRONT)
    fails = []
    for needle in ('name="description"', 'property="og:title"', "application/ld+json", "Минск и вся Беларусь | KARKASVOROT.BY</title>"):
        if needle not in own:
            fails.append("главная без плагина: в <head> нет %s" % needle)
        if needle in quiet:
            fails.append("при Yoast тема всё равно выводит %s" % needle)
    return fails


def check(base, s):
    fails = []
    urls = _sitemap_urls(base, fails)
    if base + "/" not in urls:
        urls.append(base + "/")
    st, _, _ = fetch(base + "/nashi-raboty/")
    if st == 200:
        urls.append(base + "/nashi-raboty/")
    titles, descs = {}, {}
    for url in urls:
        f, title, desc = _page(url, base)
        fails += f
        if title:
            titles.setdefault(title, []).append(url)
        if desc:
            descs.setdefault(desc, []).append(url)
    for label, seen in (("title", titles), ("description", descs)):
        fails += ["одинаковый %s на %s" % (label, ", ".join(u)) for u in seen.values() if len(u) > 1]

    st, og, _ = fetch(base + "/wp-content/themes/karkasvorot/assets/og.jpg")
    if st != 200:
        fails.append("assets/og.jpg: статус %s" % st)
    st, robots, _ = fetch(base + "/robots.txt")
    for line in ("Disallow: /wp-admin/", "Allow: /wp-admin/admin-ajax.php", "Sitemap: %s/wp-sitemap.xml" % base):
        if line not in robots:
            fails.append("robots.txt без строки %r" % line)
    st, loc = location(base + "/?author=1")
    if st != 301 or loc != base + "/":
        fails.append("?author=1: %s → %s, ожидался 301 на %s/" % (st, loc, base))
    st, html, _ = fetch(base + "/?s=x")
    if not re.search(r"<meta name=[\'\"]robots[\'\"] content=[\'\"][^\'\"]*noindex", html):
        fails.append("поиск ?s=x без noindex")
    st, html, _ = fetch(base + "/net-takoj-stranicy-kv/")
    if "noindex" not in html:
        fails.append("404 без noindex")
    for path in ("/wp-json/wp/v2/users", "/wp-json/wp/v2/Users", "/?rest_route=/wp/v2/USERS"):
        st, body, _ = fetch(base + path)
        if st != 401 or '"slug"' in body:
            fails.append("%s гостю: статус %s (ожидался 401 без списка)" % (path, st))
    att = run_php(base, (
        "$a = get_posts(array('post_type' => 'attachment', 'numberposts' => 1)); $id = $a ? $a[0]->ID : 0;"
        " if (!$id) { $u = wp_upload_bits('kv-seo-test.jpg', null, file_get_contents(get_template_directory() . '/assets/og.jpg'));"
        " $id = wp_insert_attachment(array('post_mime_type' => 'image/jpeg', 'post_title' => 'kv seo test', 'post_status' => 'inherit'), $u['file']); }"
        " echo get_permalink($id) . ' ' . wp_get_attachment_url($id);")).split()
    st, loc = location(att[0]) if len(att) == 2 else (None, None)
    if len(att) != 2 or st != 301 or loc != att[1]:
        fails.append("вложение %s: %s → %s, ожидался 301 на файл" % (" ".join(att), st, loc))
    fails += _plugin_silence(base)
    global SERVICE
    made = run_php(base, "echo ($s = kv_service_pages()) ? 0 : wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish',"
                         " 'post_title' => 'kv seo test service', 'page_template' => 'page-service.php'));").strip()
    SERVICE = made if made != "0" else "kv_service_pages()[0]->ID"
    try:
        fails += _schema_data(base)
    finally:
        if made != "0":
            run_php(base, "wp_delete_post(%d, true);" % int(made))
    return fails
