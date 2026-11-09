> Обновление 09.11.2026: цены публично не показываем; новые иллюстрации и описательные имена фотографий — см. [katalog.md](katalog.md). Это решение владельца заменяет прежние указания о ценах.

# Бриф для Astra6 — дизайн KARKASVOROT.BY

> Оформление выполнено по команде владельца. Актуальная реализация и результаты — [oformlenie.md](oformlenie.md); этот бриф сохраняется как исходные ограничения. Текущие токены смотрите непосредственно в assets/site.css.

## Цель и контекст

Адресат: Astra6, Codex-агент по дизайну. Цель №1, подтверждённая владельцем: находить покупателей через поиск по изделиям и получать заказы. Приоритет — понятный выбор изделия, реальные цены и фото, доверие и обращение с телефона. Внешний вид должен помогать этому пути. Не обещать посетителю неподтверждённые сроки и выгоды и владельцу — гарантированные первые позиции.

Мастерская: изготовление и монтаж каркасов ворот и других металлоконструкций. Монтаж в Минске и Минской области, доставка каркасов по Беларуси. Это классическая WordPress-тема без сборки и плагинов. Цены и работы владелец меняет в админке. Текущий вид — функциональная основа; скриншоты показывают её, а не утверждённый дизайн.

## Страницы и обязательные блоки

На всех страницах: шапка с навигацией и контактами, ссылка пропуска к содержимому, форма заявки, подвал, мобильная панель «Позвонить / Viber / Telegram / Заявка». Баннер согласия появляется только при заданной Метрике и отсутствии выбора.

| URL / шаблон | Контент и порядок смысловых блоков |
|---|---|
| `/`, `front-page.php` | H1 «Изготовление и монтаж каркасов ворот и металлоконструкций в Минске и области», вводный текст, заявка, карточки цен, оговорка к цене, работы, как заказать, форма |
| `/karkas-otkatnyh-vorot/` | Крошки, H1, описание, чертёж, от 1000 BYN для проёма 3×1,5 м; труба 60×40×2 мм, грунтовка; работы, этапы заказа, FAQ, форма |
| `/karkas-raspashnyh-vorot/` | Та же структура: от 500 BYN, труба 60×40×2 мм, грунтовка, реальные работы услуги |
| `/kalitki/` | Та же структура: от 270 BYN, труба 60×40×2 мм, грунтовка |
| `/prochie-izdeliya/` | Общее введение; `#mebel` — каркасы мебели от 300 BYN, 20×20×2 мм; `#obuvnicy` — обувницы от 300 BYN, 40×40×2 мм; `#zabory`, `#navesy` — цена по запросу; фото каждого раздела, этапы, FAQ, форма |
| `/nashi-raboty/`, `archive-work.php` | Крошки, H1, ссылки-фильтры услуг, все работы с фото и подписями, форма; пустое состояние |
| `/nashi-raboty/<slug>/`, `single-work.php` | Название, крупное фото, известный город и описание, ссылка услуги и общего списка, форма |
| `/kontakty/` | Крошки, H1, зона работы, телефон и мессенджеры, заполненные реквизиты и часы, форма |
| `/politika-konfidencialnosti/` | Читаемый длинный текст политики, сведения оператора; форма остаётся |
| Поиск, обычная запись, 404 | Общая визуальная система; поиск и пустая выдача, читабельная запись, понятная 404 с переходами |

Полные фактические тексты находятся в `inc/installer.php`, содержимое полей — в WordPress. Цены предварительные: сохраните оговорку о согласовании размеров и возможном изменении цены. Телефон +375 44 772-68-23, Telegram Maksimus05061981. Реквизиты, e-mail, часы, сроки и гарантия пока не подтверждены; не заполняйте их выдуманными значениями. Не заменяйте реальные изделия изображениями, выдаваемыми за выполненные работы.

## Скриншоты

`screens/` содержит полноразмерные снимки основных страниц, отдельной работы, поиска и 404 при ширине 390 и 1440 CSS px. Имена: `home-390.png`, `kalitki-1440.png` и т. п. Точный URL и ширина каждого снимка — `screens/index.json`. Снимки делаются гостем, без панели WordPress. Проверяйте также промежуточную ширину 896 px, где переключается мобильная панель.

## Что можно менять

`assets/site.css`, `assets/form.css`, локальные шрифты в `assets/fonts/`, декоративные изображения. Основной интерфейс уже имеет стабильные БЭМ-классы. Менять визуальную композицию через CSS; если нужен иной HTML, сдайте отдельный макет с пояснениями для переноса, а не меняйте рабочую PHP-логику самостоятельно.

## Что сохранить

- PHP-логику, установщик, поля WordPress и существующие URL.
- `<head>`, title/description/canonical, JSON-LD, robots, sitemap, проверки SEO.
- Форму: имена полей, action, скрытые значения, согласие, антиспам, сообщения ошибок, `aria-describedby`, `#zayavka`.
- Один H1, уровни H2/H3, alt, ссылки и подписи работ, реальный текст и оговорки цен.
- `data-goal`, `data-goal-auto`, `data-consent`, `data-consent-open`, `data-metrika`, классы и ID для скриптов. Логику `assets/site.js` не переписывать ради оформления.
- Мобильную панель связи, видимый фокус, skip-link и управление меню с клавиатуры. Cookie-баннер не перекрывает панель.

## Ограничения и проверка

WCAG AA: контраст текста минимум 4.5:1 (крупного 3:1), различимые состояния и фокус; текст ошибок не только цветом. Элементы управления удобны пальцем (ориентир 44 px). Нет горизонтального скролла на 390 px; интерфейс остаётся пригодным при увеличении текста.

Суммарный исходный CSS+JS темы **строго меньше 60×1024 байт**, без учёта шрифтов. Без внешних шрифтов, CDN, jQuery, тяжёлых библиотек и каруселей. Локальные шрифты с лицензией; `font-display: swap`. У изображений сохраняются width/height и адаптивная загрузка. Lighthouse mobile: Performance, Accessibility, SEO ≥90 на главной и услуге; скорость локального стенда не является гарантией скорости хостинга.

## Токены

Единый источник — `assets/site.css`, `:root`. Используйте существующие токены, новые значения вводите здесь, а не рассыпайте по селекторам. Текущая палитра белая/графит с акцентом #42a9ed, её можно развить при сохранении контраста.

```css
:root {
	/* Colors */
	--kv-bg: #ffffff;
	--kv-surface: #f4f6f8;
	--kv-text: #1f2328;
	--kv-muted: #4f5964;
	--kv-border: #d9dee3;
	--kv-accent: #42a9ed;
	--kv-accent-text: #0b6aa8;
	--kv-on-accent: #10202c;
	--kv-dark: #1f2328;
	--kv-on-dark: #f4f6f8;
	--kv-focus: #0b6aa8;
	/* Type */
	--kv-font: "Manrope", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
	--kv-fs-base: 1rem;
	--kv-fs-small: 0.875rem;
	--kv-fs-h1: clamp(1.75rem, 1.2rem + 2.4vw, 2.75rem);
	--kv-fs-h2: clamp(1.375rem, 1.1rem + 1.2vw, 1.875rem);
	--kv-fs-h3: 1.25rem;
	--kv-lh: 1.6;
	--kv-lh-tight: 1.2;
	/* Space */
	--kv-space-1: 0.25rem;
	--kv-space-2: 0.5rem;
	--kv-space-3: 1rem;
	--kv-space-4: 1.5rem;
	--kv-space-5: 2.5rem;
	--kv-space-6: 4rem;
	--kv-container: 72rem;
	--kv-gutter: 1rem;
	/* Shape */
	--kv-radius: 0.5rem;
	--kv-radius-lg: 1rem;
	--kv-header-h: 4rem;
	--kv-mobilebar-h: 3.5rem;
}
```

## Стабильные классы

Основные группы: `.kv-header`, `.kv-menu`, `.kv-hero`, `.kv-prices`, `.kv-price`, `.kv-service`, `.kv-other`, `.kv-works`, `.kv-work`, `.kv-single-work`, `.kv-contacts`, `.kv-order`, `.kv-form`, `.kv-cookie`, `.kv-footer`, `.kv-mobilebar`. Полный список текущих CSS-селекторов:

```text
kv-button
kv-card
kv-card__img
kv-card__media
kv-card__text
kv-card__title
kv-contacts
kv-contacts__grid
kv-contacts__list
kv-container
kv-content
kv-cookie
kv-cookie__actions
kv-cookie__btn
kv-cookie__btn--decline
kv-cookie__inner
kv-cookie__text
kv-crumbs__item
kv-crumbs__list
kv-facts
kv-faq
kv-faq__a
kv-faq__item
kv-faq__q
kv-filter
kv-filter__link
kv-footer
kv-footer__bottom
kv-footer__cookie
kv-footer__inner
kv-footer__item
kv-footer__item--empty
kv-footer__label
kv-footer__list
kv-footer__logo
kv-footer__menu
kv-footer__title
kv-form
kv-form__check
kv-form__consent
kv-form__error
kv-form__field
kv-form__field--wide
kv-form__grid
kv-form__hp
kv-form__input
kv-form__label
kv-form__lead
kv-form__req
kv-form__submit
kv-form__success
kv-form__summary
kv-form__title
kv-grid
kv-header
kv-header__burger
kv-header__burger-icon
kv-header__contacts
kv-header__inner
kv-header__logo
kv-header__msg
kv-header__nav
kv-header__phone
kv-header__region
kv-hero
kv-hero__lead
kv-lead
kv-main
kv-menu
kv-menu-open
kv-meta
kv-mobilebar
kv-mobilebar__link
kv-mobilebar__link--order
kv-order
kv-other
kv-other__price
kv-pagination
kv-placeholder
kv-price
kv-price-note
kv-price__img
kv-price__label
kv-price__list
kv-price__media
kv-price__more
kv-price__title
kv-price__value
kv-prices
kv-prices__grid
kv-section
kv-service__price
kv-service__top
kv-single-work__photo
kv-skip
kv-steps
kv-steps__item
kv-steps__list
kv-title
kv-visually-hidden
kv-work
kv-work__caption
kv-work__img
kv-work__media
kv-works
kv-works__grid
```

## Сдача

Передайте изменённый `assets/` с перечнем файлов и снимками 390/1440, либо отдельный HTML-макет и таблицу соответствия блокам. Укажите причины визуальных решений с точки зрения выбора изделия и отправки заявки. Запуск стенда, скриншотов и проверок описан в `../tools/README.md`. После переноса дизайна необходим новый полный `check_site.py`, браузерная проверка формы/согласия и Lighthouse. Боевой сайт не открывать, не менять и не публиковать без отдельной команды владельца.
