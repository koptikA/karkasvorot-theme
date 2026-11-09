"""Base theme: header contacts, skip link, footer, lang, no PHP errors on base templates."""
import re

from stand import fetch

PHP_ERR = re.compile(r"(<b>)?(Warning|Notice|Deprecated|Fatal error|Parse error)(</b>)?:\s.*?\son line", re.I)


def _part(html, tag):
    m = re.search(r"<%s\b.*?</%s>" % (tag, tag), html, re.S)
    return m.group(0) if m else ""


def check(base, s):
    fails = []
    status, html, _ = fetch(base + "/")
    if status != 200:
        return ["главная: статус %s" % status]
    header = _part(html, "header")
    for needle, what in (("tel:+375447726823", "телефон"), ("viber://", "Viber"), ("t.me/", "Telegram"), ("Минск", "«Минск»")):
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
    if "Минская область" not in footer:
        fails.append("в подвале нет зоны работы «Минская область»")
    if not re.search(r'<html[^>]+lang="ru(-RU)?"', html):
        fails.append("<html lang> не ru/ru-RU")
    if not re.search(r"<body[^>]+data-metrika=", html):
        fails.append("<body> без data-metrika")

    pages = {"/": 200, "/?s=%D0%BA%D0%B0%D1%80%D0%BA%D0%B0%D1%81": 200, "/nashi-raboty/": 200, "/net-takoj-stranicy-kv/": 404}
    for path, want in pages.items():
        st, body, _ = fetch(base + path)
        if st != want:
            fails.append("%s: статус %s, ожидался %s" % (path, st, want))
        m = PHP_ERR.search(body)
        if m:
            fails.append("%s: PHP-ошибка в HTML: %s" % (path, m.group(0)[:160]))
        if st == 200 and "<h1" not in body:
            fails.append("%s: нет <h1>" % path)
    return fails
