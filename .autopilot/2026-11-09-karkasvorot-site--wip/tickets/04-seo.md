# 04 — SEO: мета, разметка, robots, sitemap, дубли

**Требования:** R05, R06, R18i, R18i.1–R18i.4, R17i.1, R13i (areaServed)
**Blocked by:** 01
**Зона:** `inc/seo.php`, `assets/og.jpg`, `tools/checks/seo.py`
**Волна:** 2
**Status:** ready

## Что должно заработать

Каждая публичная страница отдаёт уникальные title и description, абсолютный canonical, Open Graph и twitter-карточку, JSON-LD без выдумок. Sitemap и robots чистые, авторы/вложения/поиск/404/заявки не индексируются, со старых `?p=` идёт 301 на ЧПУ. Если стоит Yoast или Rank Math — тема молчит.

## Из брифа, дословно

> «нужно оценить и продвинуть сайт https://karkasvorot.by/»
> «что нужно изменить добавить или еще что-то чтобы сайт продвигался»
> «по всем правилам»

## Разделы спецификации

Истории 19, 24–29; Решения: SEO своё; interfaces.md: `seo`, slug-и. Справочник: `C:\Users\User\.claude\skills\site-seo\SKILL.md` и `references/seo-aeo-baseline.md`, скрипт-образец `scripts/check_seo.py`.

## Состав

- Title: главная — «Каркасы ворот в Минске — изготовление и монтаж | KARKASVOROT.BY»; услуги — мета `_kv_seo_title`, иначе «<Заголовок> в Минске — цена от <price_from> BYN | KARKASVOROT.BY» (без цены — без хвоста цены); работа — «<подпись> — наши работы | KARKASVOROT.BY»; архив работ, контакты, политика — свои.
- Description: мета `_kv_seo_description` → excerpt → первые ~155 символов текста. Метабокс «SEO» (title, description) на страницах и работах — в этом файле.
- Canonical: core для singular; для главной и архива работ — свой абсолютный, без query. OG: `og:type`, `og:site_name`, `og:title`/`og:description`/`og:url` = title/description/canonical, `og:image` (изображение записи → иллюстрация услуги → `assets/og.jpg` 1200×630: «KARKASVOROT.BY» на фоне токена темы, PIL), `og:image:width/height`, `og:locale=ru_RU`, `twitter:card=summary_large_image`.
- JSON-LD: главная — `HomeAndConstructionBusiness`, `@id` `<home>/#org`, name, url, telephone, `areaServed` (Минск; Минская область), address/openingHours/email/taxID — только если заполнены; услуги — `Service` (`provider` → `#org`, `areaServed`, `offers`: `Offer` + `priceSpecification.minPrice`, `priceCurrency: BYN`) — только при заполненной цене; `BreadcrumbList` везде, кроме главной. Без рейтингов, отзывов, FAQPage, SearchAction, sameAs.
- `kv_breadcrumbs()` — Главная → (Наши работы) → текущая.
- Verification-мета Яндекса и Google из настроек, если заполнены.
- `wp_robots`: `noindex, follow` — поиск, 404, архивы дат/тегов/категорий/авторов; остальным `max-image-preview:large`. `robots_txt`: `Disallow: /wp-admin/`, `Allow: /wp-admin/admin-ajax.php`, `Sitemap: <home>/wp-sitemap.xml`.
- Sitemap: убрать провайдер `users` и таксономии; тип `post` убрать, пока нет опубликованных записей; оставить страницы и `work`.
- Редиректы: `?author=N` и архив автора → 301 `/`; вложения → 301 на файл. REST `wp/v2/users` гостям → 401.
- `kv_seo_plugin_active()`: `defined('WPSEO_VERSION') || class_exists('RankMath')` → тема не выводит свои title-фильтры, description, OG, JSON-LD, canonical.
- `tools/checks/seo.py`: все публичные URL из sitemap — title и description есть и уникальны, canonical абсолютный, `og:title/description/url` совпадают со страницей, `og:image` абсолютный, JSON-LD парсится и содержит `@context`/`@type`, нет `aggregateRating`/`review`; robots.txt содержит Sitemap; sitemap 200 без `users`; `?author=1` → 301; `?s=x` → noindex; `/wp-json/wp/v2/users` гостю без списка.

## Критерии приёмки

- [ ] `seo.py` зелёный на всех существующих страницах (страницы от таска 02 могут ещё не быть — проверка не падает на их отсутствии)
- [ ] JSON-LD содержит только поля с реальными данными
- [ ] При активном Yoast/Rank Math (проверить заглушкой константы) тема не выводит свои мета
- [ ] PHP 7.4-совместимо
