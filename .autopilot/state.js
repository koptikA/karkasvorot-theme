window.STATE =
{
  "slug": "karkasvorot-site",
  "dir": "2026-11-09-karkasvorot-site",
  "title": "Новый сайт karkasvorot.by",
  "mode": "semi",
  "depth": "normal",
  "polish": null,
  "tier": "T2",
  "briefFile": "2026-11-09-brief.md",
  "memoryFile": "CLAUDE.md",
  "skillDir": "C:/Users/User/.claude/skills/autopilot",
  "startedAt": "2026-11-09T12:40:31+03:00",
  "updatedAt": "2026-11-09T20:36:37+03:00",
  "finishedAt": "2026-11-09T20:36:37+03:00",
  "stages": [
    {
      "id": "preflight",
      "status": "done",
      "startedAt": "2026-11-09T12:40:31+03:00",
      "finishedAt": "2026-11-09T12:48:28+03:00"
    },
    {
      "id": "manifest",
      "status": "done",
      "startedAt": "2026-11-09T12:40:31+03:00",
      "finishedAt": "2026-11-09T12:48:28+03:00"
    },
    {
      "id": "briefing",
      "status": "done",
      "startedAt": "2026-11-09T12:40:31+03:00",
      "finishedAt": "2026-11-09T12:48:28+03:00"
    },
    {
      "id": "spec",
      "status": "done",
      "startedAt": "2026-11-09T12:48:28+03:00",
      "finishedAt": "2026-11-09T12:54:10+03:00"
    },
    {
      "id": "plan",
      "status": "done",
      "startedAt": "2026-11-09T12:54:10+03:00",
      "finishedAt": "2026-11-09T12:54:10+03:00",
      "note": "5 тасков, ярус T2, 3 волны"
    },
    {
      "id": "build",
      "status": "done",
      "startedAt": "2026-11-09T12:54:10+03:00",
      "note": "5 из 5 тасков готовы (05 — Codex)",
      "finishedAt": "2026-11-09T20:28:45+03:00"
    },
    {
      "id": "review",
      "status": "done",
      "startedAt": "2026-11-09T13:11:02+03:00",
      "note": "5 из 5 тасков готовы (05 — Codex)",
      "finishedAt": "2026-11-09T20:28:45+03:00"
    },
    {
      "id": "final",
      "status": "done",
      "startedAt": "2026-11-09T20:28:45+03:00",
      "finishedAt": "2026-11-09T20:36:37+03:00"
    }
  ],
  "requirements": {
    "total": 23,
    "done": 5,
    "inTicket": 18,
    "inSpec": 0,
    "placeholder": 0,
    "deferred": 0,
    "dropped": 0
  },
  "tickets": [
    {
      "id": "01",
      "title": "Каркас темы, настройки, стенд",
      "requirements": [
        "R04",
        "R07",
        "R09i",
        "R14i",
        "R16i"
      ],
      "blockedBy": [],
      "wave": 1,
      "zone": [
        "theme/karkasvorot/",
        "tools/"
      ],
      "status": "done",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0,
      "startedAt": "2026-11-09T12:54:19+03:00",
      "finishedAt": "2026-11-09T13:13:50+03:00",
      "commit": "bd9e3d9",
      "tests": {
        "passed": 3,
        "failed": 0
      }
    },
    {
      "id": "02",
      "title": "Страницы, тексты, работы",
      "requirements": [
        "R08",
        "R10i",
        "R11i",
        "R15i",
        "R19i"
      ],
      "blockedBy": [
        "01"
      ],
      "wave": 2,
      "zone": [
        "inc/installer.php",
        "templates",
        "assets/site.css"
      ],
      "status": "done",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0,
      "startedAt": "2026-11-09T13:13:50+03:00",
      "finishedAt": "2026-11-09T13:39:29+03:00",
      "commit": "01c197e",
      "tests": {
        "passed": 6,
        "failed": 0
      }
    },
    {
      "id": "03",
      "title": "Заявки и cookie-согласие",
      "requirements": [
        "R12i",
        "R15i",
        "R17i"
      ],
      "blockedBy": [
        "01"
      ],
      "wave": 2,
      "zone": [
        "inc/leads.php",
        "assets/site.js",
        "assets/form.css"
      ],
      "status": "done",
      "retries": 0,
      "repairs": 1,
      "handoffs": 0,
      "startedAt": "2026-11-09T13:13:50+03:00",
      "repairFindings": [
        "kv_req рендерится сервером — при кэше страниц заявки разных посетителей склеиваются (R12i)",
        "transient ошибки читается по ссылке; проверка Метрики не может покраснеть"
      ],
      "finishedAt": "2026-11-09T13:45:31+03:00",
      "commit": "260d925",
      "tests": {
        "passed": 6,
        "failed": 0
      }
    },
    {
      "id": "04",
      "title": "SEO",
      "requirements": [
        "R05",
        "R18i",
        "R17i"
      ],
      "blockedBy": [
        "01"
      ],
      "wave": 2,
      "zone": [
        "inc/seo.php"
      ],
      "status": "done",
      "retries": 1,
      "repairs": 1,
      "handoffs": 0,
      "startedAt": "2026-11-09T13:13:50+03:00",
      "repairFindings": [
        "seo.py: author/attachment 301 без проверки Location",
        "seo.py: JSON-LD «только реальные данные» не проверено",
        "REST users обходится регистром",
        "minPrice склеивает цифры поля"
      ],
      "finishedAt": "2026-11-09T13:42:26+03:00",
      "commit": "e6c3cf6",
      "tests": {
        "passed": 6,
        "failed": 0
      }
    },
    {
      "id": "05",
      "title": "Запуск, бриф Astra6, проверка скорости",
      "requirements": [
        "R05",
        "R06",
        "R19i",
        "R20i",
        "G01"
      ],
      "blockedBy": [
        "02",
        "03",
        "04"
      ],
      "wave": 3,
      "zone": [
        "docs/",
        "dist/"
      ],
      "status": "done",
      "retries": 0,
      "repairs": 0,
      "handoffs": 1,
      "startedAt": "2026-11-09T13:45:31+03:00",
      "finishedAt": "2026-11-09T20:28:45+03:00",
      "commit": "5193e2c (+1d55c23, 28aab3a — оформление и каталог)",
      "tests": {
        "passed": 8,
        "failed": 0
      }
    }
  ],
  "singlePass": null,
  "tests": {
    "passed": 3,
    "failed": 0
  },
  "debt": {
    "placeholders": [
      "R14i — реквизиты (наименование, УНП, адрес, регистрация)",
      "e-mail заявок",
      "часы работы",
      "цены на заборы, навесы, беседки",
      "ID Метрики и коды вебмастеров",
      "сроки и гарантия"
    ],
    "assumptions": [],
    "emptyEnv": []
  },
  "additions": [],
  "coverage": {
    "found": 7,
    "fixed": 7,
    "deferred": 0
  },
  "concerns": [
    "footer.php:33-43 — заголовок «Реквизиты» выводится посетителю при пустом списке (R14i) → таск 05",
    "Manrope вариативный (кир.+лат., 38 КБ) вместо 2 начертаний — отклонение принято",
    "inc/seo.php — kv_seo_plugin_active уже рабочая, таск 04 владеет ей",
    "tools/checks/base.py:6 — регулярка не ловит fatal «Uncaught Error» (многострочный) → таск 05",
    "tools/stand/run.php — /kv-run.php без секрета, CSRF с локальной страницы → таск 05",
    "base.py — обход не рендерит single/page-service/card → таск 05",
    "theme_model.py — метабоксы не проверены с неверным nonce → таск 05",
    "fixtures.py — не проверяет, что файл фикстуры отдаётся 200 → таск 05",
    "index/archive/search.php — дублированный блок цикла → таск 05",
    "single.php:12, card.php:10 — esc_html(get_the_title()) → таск 05",
    "tools/serve.sh — порт зашит, второй --port ломает CLI; обход npx напрямую → таск 05",
    "seo: openingHours свободным текстом — подсказать формат в docs/zapusk.md → таск 05",
    "installer: работы slider1/2/4 получают подпись из имени файла — владельцу переименовать (отчёт)",
    "Метрика/цели проверены только чтением кода, нет headless-теста → таск 05 (Lighthouse/браузер)",
    "form.css:31 — брейкпоинт баннера 48rem против 56rem мобильной панели → таск 05",
    "cookie-баннер показывается без ID Метрики → таск 05",
    "?zayavka=ok при обновлении повторно шлёт form_submit → таск 05",
    "нет ссылки для отзыва cookie-согласия (99-З) → таск 05",
    "e-mail заявок = публичный e-mail (один ключ) — сказать пользователю",
    "seo: physical robots.txt на хостинге перекроет виртуальный → docs/zapusk.md (таск 05)",
    "seo: legalName в разметке сверх списка — данные реальные, оставлено",
    "seo: openingHours выводить только в валидном формате → таск 05",
    "seo: редирект вложения с внешним URL уводит на /wp-admin/ → таск 05",
    "seo.py:78 запрет posts-post- безусловный → таск 05",
    "leads: wp_slash, kv_back в подкаталоге, open-redirect-сценарий → таск 05 (если не сделано в ремонте)",
    "theme_model.py — проверка kv_breadcrumbs()==[] устарела → таск 05",
    "на боевом есть опубликованные записи помимо слайдов (id 92, 18 …) — проверить после активации, docs/zapusk.md → таск 05",
    "архив работ без пагинации (ponytail) — хватит до ~50 работ",
    "installer.php:158-195 — существующая страница со slug (черновик) засчитывается установленной; главная может стать черновиком; ID 0 при сбое insert → таск 05",
    "content.py — цены/контакты ищутся во всём HTML, выдумки проверяются только на услугах, нет сценария «чужая страница с тем же slug» → таск 05",
    "два механизма уникальности подписей работ ((N) и — фото N) → таск 05",
    "single.php — мёртвые ветки work; alt-фолбэк повторён 3 раза → таск 05",
    "политика ПД: «хранятся до достижения целей», а заявки лежат бессрочно — сказать владельцу удалять старые / указать срок",
    "тексты: «сварные/сваривается», «профильная труба» — додумки, подтвердить у владельца",
    "чертёж obuvnicza.png не используется (одна иллюстрация у «Прочих»)",
    "/nashi-raboty/page/2/ отдаёт 200 с тем же содержимым → таск 05",
    "docs/zapusk.md: проверить существующие slug-и до активации → таск 05",
    "seo.php:281-285 редирект вложений дублирует ядро — пометить комментарием → таск 05",
    "seo.php minPrice: «300 2 шт» → 3002 (край)",
    "seo.py: тестовые страницы услуг не удаляются; подмена заголовка через shutdown; дубли _location/_render с stand.py → таск 05",
    "leads: тот же человек, тот же телефон, тот же kv_req (без JS / «Назад») — вторая заявка отбрасывается; первая сохранена",
    "leads.php:36-43 — ответ ?zayavka=err без nocache → таск 05",
    "leads.py:143 — восстановление metrika_id искажает значение → таск 05",
    "таск 05 и оформление выполнены Codex вне ревью автопилота (ревьюеры их не видели)"
  ],
  "reviewers": {
    "manifestSpec": "rev-ms",
    "craft": "rev-craft"
  },
  "blind": {
    "verdict": "проходит, блокирующих нет",
    "drift": [
      "услуги без JSON-LD Service (выводится только при цене, а цены скрыты)",
      "подписи работ с SEO-хвостами из старых alt",
      "на странице откатных ворот нет фото работ (нет таких фото в медиатеке)",
      "письмо о заявке не проверено — на стенде нет почты"
    ]
  }
}
