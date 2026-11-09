"""Base theme: header contacts, skip link, footer, lang, no PHP errors on base templates."""
import re

from stand import PHP_ERR, fetch

SAMPLE_FATAL = ("<br />\n<b>Fatal error</b>:  Uncaught Error: Call to undefined function kv_x() in /wordpress/a.php:3\n"
                "Stack trace:\n#0 {main}\n  thrown in <b>/wordpress/a.php</b> on line <b>3</b><br />")


def _part(html, tag):
    m = re.search(r"<%s\b.*?</%s>" % (tag, tag), html, re.S)
    return m.group(0) if m else ""


def check(base, s):
    fails = []
    status, html, _ = fetch(base + "/")
    if status != 200:
        return ["главная: статус %s" % status]
    header = _part(html, "header")
    for needle, what in (("tel:+375447726823", "телефон"), ("viber://", "Viber"), ("t.me/", "Telegram")):
        if needle not in header:
            fails.append("в шапке нет: %s (%s)" % (what, needle))
    for goal in ("phone_click", "viber_click", "telegram_click"):
        if 'data-goal="%s"' % goal not in html:
            fails.append("нет ссылки с data-goal=%s" % goal)
    skip = re.search(r'<a[^>]+href="#([\w-]+)"[^>]*>\s*Перейти к содержимому', html)
    if not skip:
        fails.append("нет ссылки «Перейти к содержимому»")
    elif 'id="%s"' % skip.group(1) not in html:
        fails.append("цель ссылки «к содержимому» #%s не найдена" % skip.group(1))
    footer = _part(html, "footer")
    if not footer:
        fails.append("нет <footer>")
    if re.search(r"источник", footer, re.I):
        fails.append("в подвале шаблонное «источник: URL»")
    # Requisites are known (owner, 2026-11-09): seller name and УНП must be shown to visitors.
    if "191469362" not in footer or "Краснолобов" not in footer:
        fails.append("в подвале нет реквизитов продавца (ИП Краснолобов, УНП 191469362)")
    # Region moved out of the compact header by owner-approved design.
    if "Минск" not in _part(html, "main"):
        fails.append("главная: нет региона работы")
    if '<details class="kv-products__dropdown">' not in header:
        fails.append("шапка: нет группы продукции")
    for part, label in ((header, "шапка"), (footer, "подвал")):
        for messenger in ("Viber", "Telegram"):
            if 'aria-label="Написать в %s"' % messenger not in part:
                fails.append(label + ": нет доступной иконки " + messenger)
    if "Беларус" not in footer:
        fails.append("в подвале нет зоны работы «по всей Беларуси»")
    if not re.search(r'<html[^>]+lang="ru(-RU)?"', html):
        fails.append("<html lang> не ru/ru-RU")
    if not re.search(r"<body[^>]+data-metrika=", html):
        fails.append("<body> без data-metrika")

    for hdr in ({}, {"X-KV-Token": "wrong"}):  # a cross-site form cannot send the stand token
        st, _, _ = fetch(base + "/kv-run.php", data="echo 'KV_RUN_OPEN';", headers=hdr)
        if st != 403:
            fails.append("/kv-run.php без верного X-KV-Token: статус %s, ожидался 403" % st)

    if not PHP_ERR.search(SAMPLE_FATAL):
        fails.append("PHP_ERR не ловит многострочный fatal «Uncaught Error … on line»")
    _, works, _ = fetch(base + "/nashi-raboty/")
    one = re.search(r'kv-work__caption"><a href="%s(/nashi-raboty/[^"]+)"' % re.escape(base), works)
    search = "/?s=%D0%BA%D0%B0%D1%80%D0%BA%D0%B0%D1%81"
    pages = {"/": 200, search: 200, "/nashi-raboty/": 200, "/kalitki/": 200, "/net-takoj-stranicy-kv/": 404,
             "/nashi-raboty/page/2/": 404}
    if one:
        pages[one.group(1)] = 200
    else:
        fails.append("в «Наших работах» нет ссылки на работу")
    if 'class="kv-work' not in works:
        fails.append("«Наши работы»: нет карточек работ")
    for path, want in pages.items():
        st, body, _ = fetch(base + path)
        if st != want:
            fails.append("%s: статус %s, ожидался %s" % (path, st, want))
        m = PHP_ERR.search(body)
        if m:
            fails.append("%s: PHP-ошибка в HTML: %s" % (path, m.group(0)[:160]))
        if st == 200 and "<h1" not in body:
            fails.append("%s: нет <h1>" % path)
        if path == search and "kv-card" not in body:
            fails.append("поиск «каркас»: нет карточек kv-card")
    return fails
