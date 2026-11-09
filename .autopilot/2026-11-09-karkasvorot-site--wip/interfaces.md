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
