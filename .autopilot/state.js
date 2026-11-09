window.STATE =
{
  "slug": "karkasvorot-site",
  "dir": "2026-11-09-karkasvorot-site--wip",
  "title": "Новый сайт karkasvorot.by",
  "mode": "semi",
  "depth": "normal",
  "polish": null,
  "tier": "T2",
  "briefFile": "2026-11-09-brief.md",
  "memoryFile": "CLAUDE.md",
  "skillDir": "C:/Users/User/.claude/skills/autopilot",
  "startedAt": "2026-11-09T12:40:31+03:00",
  "updatedAt": "2026-11-09T13:11:02+03:00",
  "finishedAt": null,
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
      "status": "active",
      "startedAt": "2026-11-09T12:54:10+03:00"
    },
    {
      "id": "review",
      "status": "active",
      "startedAt": "2026-11-09T13:11:02+03:00"
    },
    {
      "id": "final",
      "status": "pending"
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
      "status": "review",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0,
      "startedAt": "2026-11-09T12:54:19+03:00"
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
      "status": "pending",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0
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
      "status": "pending",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0
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
      "status": "pending",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0
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
      "status": "pending",
      "retries": 0,
      "repairs": 0,
      "handoffs": 0
    }
  ],
  "singlePass": null,
  "tests": null,
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
    "inc/seo.php — kv_seo_plugin_active уже рабочая, таск 04 владеет ей"
  ],
  "reviewers": {
    "manifestSpec": "rev-ms",
    "craft": "rev-craft"
  },
  "blind": null
}
