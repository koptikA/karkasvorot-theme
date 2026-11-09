# Интерфейсы

## Правила проекта

- Корень: `C:\Users\User\Desktop\мои проекты\karkasvorot`. Тема: `theme/karkasvorot/`. Проверки: `tools/`. Документы: `docs/`. Сборка: `dist/`.
- Классическая PHP-тема WordPress, **без плагинов и без Composer/npm-зависимостей в теме**. Совместимость **PHP 7.4** (без `match`, enum, readonly, union types, `str_contains` — нет в 7.4, nullsafe `?->`) и WP 6.4+. Префикс всех функций, хуков, опций, мета-ключей: `kv_` / `_kv_`. Текстовый домен `karkasvorot`. Весь пользовательский текст по-русски.
- Экранирование на выводе (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`), санитизация на входе. Никаких секретов в репозитории.
- Без jQuery, без внешних CDN; шрифты локально в `assets/fonts/`. CSS+JS темы суммарно < 60 КБ (без шрифтов).
- Все цвета, шрифты, отступы, радиусы — CSS-переменные в `:root` в начале `assets/site.css` (под дизайн Astra6). Классы — БЭМ (`.kv-header__nav`), стабильные.
- Локальный WordPress: WordPress Playground CLI через `npx @wp-playground/cli@latest` (node 24 есть; PHP на машине **нет**). Команды запуска и проверки — в `tools/README.md` (пишет таск 01). Сервер на `http://127.0.0.1:9400`.
- Проверки: `python tools/check_site.py http://127.0.0.1:9400` — грузит все модули `tools/checks/*.py`; каждый модуль выставляет `def check(base: str, s: requests.Session|None) -> list[str]` (список провалов; пусто = зелёно). Только stdlib Python (urllib), без pip.
- Не хватает зависимости или инструмент не работает → вернуть `BLOCKED` с точной ошибкой, ничего не устанавливать глобально.
- Не трогать: `.autopilot/` (кроме того, что разрешено оркестратором), боевой сайт karkasvorot.by (только чтение: можно скачать публичные фото для фикстур).

## Границы, решённые в спецификации

| Модуль | Файл(ы) | Владеет | Выставляет | Прячет |
|---|---|---|---|---|
| `settings` | `inc/settings.php` | бизнес-данные в Customizer (телефон, viber, telegram, email заявок, наименование, УНП, адрес, регистрация, часы, metrika_id, yandex_verification, google_verification) | `kv_opt(string $key, string $default = ''): string`; `kv_phone_href(): string` (`tel:+375…`); `kv_is_placeholder(string $key): bool` | Customizer-секции, санитизацию, значения по умолчанию (контакты со старого сайта) |
| `content-model` | `inc/content-model.php` | типы `work`, `lead`; мета услуги (`_kv_price_from`, `_kv_includes` — строки через \n, `_kv_illustration` — ID вложения, `_kv_term`, `_kv_warranty`, `_kv_faq` — JSON [{q,a}], для «Прочих» — `_kv_prices` JSON {mebel,obuvnicy,zabory,navesy}); мета работы (`_kv_service` — ID страницы, `_kv_section` — якорь раздела «Прочих» или '', `_kv_city`); шаблон страницы «Услуга» (`page-service.php`, Template Name: Услуга) | `kv_service_pages(): array` (WP_Post[], страницы с шаблоном «Услуга», по menu_order); `kv_service_price(int $page_id): array` (`['from'=>string,'includes'=>string[]]`); `kv_works(int $service_id = 0, int $limit = 12, string $section = ''): array` | регистрацию, метабоксы, сохранение мета |
| `installer` | `inc/installer.php` | первичный контент, меню, импорт работ, черновики старого | `kv_install(): array` (`['created'=>[], 'skipped'=>[], 'drafted'=>[]]`); вызов на `after_switch_theme`; опция `kv_install_version` | тексты, таблицу «файл → услуга», slug-и страниц |
| `leads` | `inc/leads.php`, `template-parts/order-form.php` | форму и приём заявок | `kv_order_form(array $args = ['product' => '']): void`; action `admin_post(_nopriv)_kv_order`; редирект с `?zayavka=ok#zayavka` или `?zayavka=err&…#zayavka` | валидацию, ловушку `kv_hp`, время `kv_ts` (≥3 с), письмо, PRG |
| `seo` | `inc/seo.php` | `<head>`-мета, JSON-LD, robots, sitemap, noindex, редиректы | хуки WP; `kv_breadcrumbs(): array` (`[['name'=>…, 'url'=>…], …]`); `kv_seo_plugin_active(): bool` | сборку JSON-LD |
| `templates`+`assets` | `*.php` в корне темы, `template-parts/`, `assets/` | разметку, вид, `assets/site.js` (меню, cookie-баннер, цели Метрики) | — | — |

**Slug-и страниц (фиксированы):** `/` (главная, статическая страница `glavnaya`), `/karkas-otkatnyh-vorot/`, `/karkas-raspashnyh-vorot/`, `/kalitki/`, `/prochie-izdeliya/`, `/nashi-raboty/` (архив `work`), `/kontakty/`, `/politika-konfidencialnosti/`. Работа: `/nashi-raboty/<slug>/`.

**Цели Метрики:** `form_submit`, `phone_click`, `viber_click`, `telegram_click`. Ссылки связи несут `data-goal="…"`; JS вызывает `ym(ID,'reachGoal',goal)` только если Метрика загружена (после согласия).

**Cookie-согласие:** `localStorage['kv_consent']` = `'yes'|'no'`; Метрика вставляется JS-ом только при `'yes'`. ID берётся из `<body data-metrika="…">`.

**Шов тестов один:** HTTP-ответы Playground-сайта, проверяемые `tools/check_site.py`.

## Из таска 01 — каркас темы и стенд

- **Стенд:** `bash tools/serve.sh` (Git Bash, из корня проекта): PHP 7.4, WP latest, ru_RU, WP_DEBUG виден в HTML, тема примонтирована вживую, каждый запуск собирает сайт с нуля, готов после «Ready!» в логе; админ `admin`/`password`. Запускать в фоне, по окончании останавливать.
- **PHP на стенде:** `python tools/stand.py php '<код без <?php>'`. Из Python: `from stand import fetch, admin_session, run_php`; `fetch(url, opener=None, data=None, follow=True) -> (status, body, final_url)`, `admin_session(base) -> OpenerDirector|None`, `run_php(base, code) -> str` (стендовый `/kv-run.php`, только POST с 127.0.0.1).
- **Проверки:** `python tools/check_site.py http://127.0.0.1:9400`; модуль `tools/checks/<имя>.py` выставляет `check(base, s) -> list[str]`, где `s` — urllib `OpenerDirector` с cookie админа или `None` (**не** requests). Сейчас модули: base, theme_model, fixtures.
- **settings:** `kv_opt($key, $default='')` — ключи `phone, viber, telegram, email, hours, org_name, unp, address, registration, metrika_id, yandex_verification, google_verification` (theme_mod `kv_<key>`); `email` — и адрес заявок, и публичный в подвале. `kv_phone_href()`, `kv_telegram_href()`, `kv_is_placeholder($key)`, `kv_settings_fields()`. Секции Customizer: `kv_contacts`, `kv_requisites`, `kv_analytics`.
- **content-model:** `kv_service_pages()`, `kv_service_price($id)`, `kv_works($service_id=0, $limit=12, $section='')`, `kv_other_sections()` → якоря `mebel, obuvnicy, zabory, navesy`; `kv_faq_from_text()`/`kv_faq_to_text()`, `kv_set_meta()`. Метабокс услуги: nonce `kv_service_nonce`/`kv_service_meta`, поля `kv_price_from, kv_includes, kv_illustration, kv_term, kv_warranty, kv_faq, kv_prices[anchor]`. Метабокс работы: `kv_work_nonce`/`kv_work_meta`, поля `kv_service, kv_section, kv_city`. Метабокс услуги виден только после сохранения страницы с шаблоном «Услуга».
- **Шаблоны:** `kv_contact_link($type, $class, $label='')` (`phone|viber|telegram`, ставит `data-goal`), `kv_footer_field($key, $label)`, `kv_menu_fallback()`, `template-parts/card.php`; размеры `kv-card` 640×480, `kv-wide` 1280×720; меню `primary`, `footer`; `<main id="content">`. Мобильная панель «Заявка» → `#zayavka` (якорь даёт форма из таска 03 — форма должна быть на каждой странице).
- **Ассеты:** `assets/site.css`, `assets/site.js`, шрифт Manrope вариативный (`manrope-cyrillic.woff2`, `manrope-latin.woff2`); CSS+JS сейчас 9.7 КБ.

## Из таска 04 — SEO

- `kv_breadcrumbs(): array` — `[['name'=>…,'url'=>…],…]`, `[]` на главной и вне запроса.
- `kv_seo_plugin_active(): bool` — `WPSEO_VERSION`, `RANK_MATH_VERSION` или класс `RankMath`; при true тема не выводит свои title/description/canonical/OG/JSON-LD (остаётся canonical ядра).
- `kv_seo_title(): string`, `kv_seo_description(): string`, `kv_seo_canonical(): string`, `kv_seo_image(): array{url,w,h}`, `kv_seo_schema(): array`, `kv_seo_name(int $post_id): string`; константа `KV_BRAND`.
- Мета `_kv_seo_title`, `_kv_seo_description`; метабокс «SEO» на `page` и `work` (nonce `kv_seo_nonce`/`kv_seo_meta`). Дублирующиеся подписи работ в title получают « — фото N».
- Canonical — свой на всех индексируемых страницах (rel_canonical ядра снят). robots.txt — штатный ядра (Disallow /wp-admin/, Allow admin-ajax, Sitemap).
- `assets/og.jpg` 1200×630. Проверка: `tools/checks/seo.py` (все URL из sitemap + главная + /nashi-raboty/).
- Стенд на своём порту: `npx -y @wp-playground/cli@latest server --port <P> …` с теми же аргументами, что в `tools/serve.sh` (serve.sh пока не принимает `--port`).

## Из таска 03 — заявки и согласие

- `kv_order_form(array $args = ['product'=>'']): void` — идемпотентная (второй вызов на странице ничего не выводит), дополнительно вызывается на хуке `get_footer` → форма `#zayavka` есть на каждой странице. Пустой `product` → `kv_lead_product_for_page()` по slug (откатные / распашные / калитки).
- `kv_lead_products(): array`; `kv_normalize_phone(string): string` (`+375XXXXXXXXX` или `''`); `kv_lead_handle()` на `admin_post(_nopriv)_kv_order`, PRG-редирект 303.
- Мета заявки: `_kv_name, _kv_phone, _kv_product, _kv_comment, _kv_page, _kv_time, _kv_req`; transient `kv_lead_<req>` на 10 мин. E-mail заявок — `kv_opt('email')`, при невалидном — `admin_email`.
- Классы: `.kv-order`, `.kv-form__*`; баннер `.kv-cookie[hidden]` с кнопками `[data-consent=yes|no]`; сообщение успеха `[data-goal-auto="form_submit"]`. Стили — `assets/form.css`. CSS+JS темы 14.6 КБ.

## Из таска 02 — страницы и контент

- `kv_install(): array` (`created`/`skipped`/`drafted`; отчёт — опция `kv_install_report`, версия — `kv_install_version`); `kv_install_pages(): array`; `kv_install_works(): array` (файл → [slug услуги, раздел]); `kv_attachment_by_file(string $file): int`; `kv_blocks(string $html): string`; `kv_policy_html(): string`; шорткод `[kv_operator]`.
- Мета работы `_kv_source` = ID вложения (отсекает дубли при повторной установке).
- Части: `get_template_part('template-parts/<price-card|price-note|how-to-order|works|work-card|faq|breadcrumbs>', null, $args)`.
- Фильтр работ: `/nashi-raboty/?usluga=<slug>`; пагинации нет (все работы на одной странице).
- `kv_order_form()` в шаблонах вызывается без `product` — изделие подбирает leads по slug.
- `/sample-page/` → 404 (проверяет `content.py`; строка 200 убрана из `base.py`). Проверка `theme_model` на `kv_breadcrumbs() == []` устарела после таска 04.
- (04, ремонт) `kv_seo_image()` возвращает `['url'=>…,'w'=>…,'h'=>…]`; `kv_seo_price(int $id): string` — первое число поля «Цена от»; `kv_seo_plain_title(int $id): string`; крошки — простой текст без HTML-сущностей. Редиректы автора и вложений висят на приоритете 1 (раньше `redirect_canonical`).
