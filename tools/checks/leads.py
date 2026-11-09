"""Leads: order form on every page, validation, PRG, dedup, honeypot, privacy of leads, cookie banner."""
import http.cookiejar
import json
import re
import time
import urllib.error
import urllib.request
import uuid

from stand import fetch, run_php

COUNT = 'echo count(get_posts(array("post_type" => "lead", "post_status" => "any", "numberposts" => -1, "fields" => "ids")));'


def _form_fails(where, html):
    fails = []
    forms = re.findall(r'<form\b[^>]*\bid="zayavka"', html)
    if len(forms) != 1:
        return ["%s: форм #zayavka %d, нужна одна" % (where, len(forms))]
    form = re.search(r'<form\b[^>]*\bid="zayavka".*?</form>', html, re.S).group(0)
    if "admin-post.php" not in form or not re.search(r'name="action"\s+value="kv_order"', form):
        fails.append("%s: форма не ведёт на admin-post.php с action=kv_order" % where)
    for name in ("kv_hp", "kv_ts", "kv_back", "kv_req", "kv_name", "kv_product", "kv_comment", "kv_consent"):
        if 'name="%s"' % name not in form:
            fails.append("%s: в форме нет поля %s" % (where, name))
    if not re.search(r'<input[^>]+type="tel"[^>]+name="kv_phone"[^>]+autocomplete="tel"[^>]*required', form):
        fails.append("%s: нет обязательного телефона type=tel autocomplete=tel" % where)
    if "/politika-konfidencialnosti/" not in form:
        fails.append("%s: у согласия нет ссылки на политику" % where)
    for fid in re.findall(r'<(?:input|select|textarea)[^>]+id="([\w-]+)"', form):
        if 'for="%s"' % fid not in form:
            fails.append("%s: у поля #%s нет <label>" % (where, fid))
    for opt in ("Каркас откатных ворот", "Каркас распашных ворот", "Калитка", "Каркас мебели", "Обувница", "Забор", "Навес или беседка", "Другое"):
        if ">%s</option>" % opt not in form:
            fails.append("%s: в списке изделий нет «%s»" % (where, opt))
    return fails


def _post(base, **over):
    data = {"action": "kv_order", "kv_name": "Иван Проверка", "kv_phone": "8 (029) 123-45-67", "kv_product": "Калитка",
            "kv_comment": "Проём 1 м", "kv_consent": "1", "kv_hp": "", "kv_ts": str(int(time.time()) - 60),
            "kv_back": base + "/", "kv_req": "chk-" + uuid.uuid4().hex}
    data.update(over)
    data = {k: v for k, v in data.items() if v is not None}
    browser = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    return data["kv_req"], fetch(base + "/wp-admin/admin-post.php", browser, data=data)


def _count(base):
    return int(run_php(base, COUNT).strip())


def _post_fails(base):
    fails = []
    _, (st, html, url) = _post(base, kv_phone="")
    if "zayavka=err" not in url:
        fails.append("POST без телефона: нет zayavka=err (%s)" % url)
    if "Укажите телефон" not in html or 'value="Иван Проверка"' not in html or "Проём 1 м" not in html:
        fails.append("POST без телефона: нет ошибки у поля или введённое не сохранилось")
    if "Проверка" in url or "%D0%9F%D1%80%D0%BE" in url:
        fails.append("введённые значения попали в URL")
    if not re.search(r'id="kv-f-phone"[^>]+aria-describedby="kv-f-phone-err"', html) or 'role="alert"' not in html:
        fails.append("ошибка телефона не связана aria-describedby / нет role=alert")
    try:
        cache = urllib.request.urlopen(url, timeout=120).headers.get("Cache-Control", "")
    except urllib.error.HTTPError as e:
        cache = e.headers.get("Cache-Control", "")
    if "no-cache" not in cache and "no-store" not in cache:
        fails.append("?zayavka=err отдаётся без запрета кэша (Cache-Control: %r)" % cache)
    st, html, _ = fetch(url)  # same ?zayavka=err link, another browser (no sender cookie)
    if "Иван Проверка" in html or "Проём 1 м" in html:
        fails.append("ссылка ?zayavka=err без cookie отправителя показывает введённые данные")
    _, (st, html, url) = _post(base, kv_phone="12345")
    if "в формате +375 XX XXX-XX-XX" not in html:
        fails.append("кривой телефон: нет «Укажите телефон в формате +375 XX XXX-XX-XX»")
    _, (st, html, url) = _post(base, kv_consent=None)
    if "zayavka=err" not in url or "согласие" not in html:
        fails.append("POST без согласия: нет ошибки")

    before = _count(base)
    req, (st, html, url) = _post(base)
    if "zayavka=ok" not in url or "Спасибо! Мы перезвоним в ближайшее время." not in html:
        fails.append("валидный POST: нет zayavka=ok и «Спасибо» (%s)" % url)
    if _count(base) != before + 1:
        fails.append("валидный POST: заявка не сохранилась (wp_mail на стенде возвращает false)")
    else:
        got = run_php(base, 'echo implode("|", array_map(function ($id) { return get_post_meta($id, "_kv_phone", true) . "/" . get_post_meta($id, "_kv_product", true); }, get_posts(array("post_type" => "lead", "meta_key" => "_kv_req", "meta_value" => "%s", "fields" => "ids"))));' % req)
        if got.strip() != "+375291234567/Калитка":
            fails.append("заявка: телефон/изделие %r, ожидалось +375291234567/Калитка" % got.strip())
    _post(base, kv_req=req)
    if _count(base) != before + 1:
        fails.append("повтор того же kv_req создал вторую заявку")
    # Cached page: two visitors share one kv_req → still two leads; same kv_req + same phone → one.
    _post(base, kv_req=req, kv_phone="+375 33 765-43-21")
    if _count(base) != before + 2:
        fails.append("тот же kv_req с другим телефоном (кэш страницы) не создал вторую заявку")
    _post(base, kv_req=req, kv_phone="+375 33 765-43-21")
    if _count(base) != before + 2:
        fails.append("тот же kv_req с тем же телефоном создал лишнюю заявку")
    before += 1
    _, (st, html, url) = _post(base, kv_hp="http://spam.example")
    _post(base, kv_ts=str(int(time.time())))
    if "zayavka=ok" not in url or _count(base) != before + 1:
        fails.append("ловушка/быстрая отправка: заявка сохранилась или нет тихого «успеха»")
    for phone in ("+375 (44) 772-68-23", "375447726823", "80447726823"):
        if run_php(base, 'echo kv_normalize_phone("%s");' % phone).strip() != "+375447726823":
            fails.append("телефон %s не нормализован к +375447726823" % phone)

    st, html, _ = fetch(base + "/?post_type=lead")
    if "Заявка: +375" in html or "Иван Проверка" in html:
        fails.append("/?post_type=lead показывает заявки гостю")
    st, html, _ = fetch(base + "/wp-json/wp/v2/lead")
    if st == 200:
        fails.append("/wp-json/wp/v2/lead гостю отвечает 200")
    return fails


def check(base, s):
    fails = []
    status, home, _ = fetch(base + "/")
    if status != 200:
        return ["главная: статус %s" % status]
    fails += _form_fails("главная", home)
    for path in ("/nashi-raboty/", "/kontakty/"):
        st, html, _ = fetch(base + path)
        if st == 200:
            fails += _form_fails(path, html)
    services = run_php(base, 'foreach (kv_service_pages() as $p) echo get_permalink($p), "\\n";').split()
    for url in services:
        st, html, _ = fetch(url)
        fails += _form_fails(url, html)
        if "kalitki" in url and not re.search(r'<option value="Калитка"\s+selected', html):
            fails.append("%s: изделие «Калитка» не выбрано заранее" % url)

    old = json.loads(run_php(base, '$m = get_theme_mods(); echo wp_json_encode(array("exists" => is_array($m) && array_key_exists("kv_metrika_id", $m), "value" => get_theme_mod("kv_metrika_id", "")));'))
    run_php(base, 'remove_theme_mod("kv_metrika_id");')
    if 'class="kv-cookie' in fetch(base + "/")[1]:
        fails.append("cookie-баннер выводится при пустом ID Метрики")
    run_php(base, 'set_theme_mod("kv_metrika_id", "12345678");')
    try:
        st, html, _ = fetch(base + "/")
        banner = re.search(r'<div\b[^>]*class="kv-cookie\b.*?</div>\s*</div>', html, re.S)
        if not banner:
            fails.append("нет cookie-баннера .kv-cookie при заданном ID Метрики")
        else:
            for needle in ("Принять", "Отклонить", "/politika-konfidencialnosti/"):
                if needle not in banner.group(0):
                    fails.append("в cookie-баннере нет «%s»" % needle)
        footer = re.search(r"<footer\b.*?</footer>", html, re.S)
        if not footer or not re.search(r"<button[^>]+data-consent-open[^>]*>\s*Настройки cookie", footer.group(0)):
            fails.append("в подвале нет кнопки «Настройки cookie» (data-consent-open)")
        if 'data-metrika="12345678"' not in html:
            fails.append("ID Метрики из настроек не попал в <body data-metrika>")
        if "mc.yandex.ru" in html:
            fails.append("в HTML есть mc.yandex.ru — Метрика должна грузиться только JS после согласия")
    finally:
        run_php(base, ('set_theme_mod("kv_metrika_id", %s);' % ("'" + old["value"].replace("\\", "\\\\").replace("'", "\\'") + "'")) if old['exists'] else 'remove_theme_mod("kv_metrika_id");')
    fails += _post_fails(base)
    return fails
