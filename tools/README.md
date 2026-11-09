# Локальный стенд и проверки

Нужны только Node (npx) и Python 3 (stdlib). PHP ставить не нужно: WordPress крутится в WordPress Playground CLI (PHP 7.4 WASM + SQLite).

## Поднять стенд

Из корня проекта, в Git Bash:

```
bash tools/serve.sh
```

Ждать строку `Ready! WordPress is running on http://127.0.0.1:9400`. Первый запуск качает WordPress, до пары минут.
Строка `Error downloading translations for theme` нормальна: у темы нет переводов на wordpress.org.

Что делает стенд (`tools/stand/blueprint.json`):

- свежий WordPress (latest), PHP 7.4, язык ru_RU, `WP_DEBUG` + `WP_DEBUG_DISPLAY` (ошибки PHP видны в HTML);
- тема `theme/karkasvorot/` примонтирована вживую: правки в файлах видны сразу, без перезапуска;
- постоянные ссылки `/%postname%/`;
- `tools/stand/seed.php` кладёт в медиатеку все файлы из `tools/fixtures/` с alt и заголовками как на боевом, добавляет записи-«слайды» (две без заголовка, «Слайд2», «Слайд4») и страницу «Пример страницы» (`sample-page`);
- затем активирует тему, так что `after_switch_theme` (установщик) срабатывает уже после импорта медиа.

Вход в админку: http://127.0.0.1:9400/wp-admin/, `admin` / `password` (браузер логинится сам, флаг `--login`).

Остановить: Ctrl+C в окне сервера (или завершить процесс, слушающий порт 9400).

## Пересобрать с нуля

Стенд не хранит состояние: каждый запуск `bash tools/serve.sh` — новый сайт, заново импорт фикстур и активация темы. Остановить и запустить снова.

Фикстуры (`tools/fixtures/uploads/`, `tools/fixtures/media.json`) скачаны один раз с боевого сайта и лежат в git. Обновить их (только чтение боевого): `python tools/fetch_fixtures.py`.

## Проверки

```
python tools/check_site.py http://127.0.0.1:9400
```

Раннер грузит каждый `tools/checks/*.py` (кроме `_*.py`) и вызывает `check(base, s) -> list[str]` — список провалов, пусто = зелёно. `s` — `urllib.request.OpenerDirector` с cookie админа (`admin`/`password`) или `None`, если вход не удался. Код выхода 1 при любом провале. Модуль, упавший с исключением, — тоже провал.

Помощники для модулей — `tools/stand.py` (импорт `from stand import fetch, admin_session, run_php`):

- `fetch(url, opener=None, data=None, follow=True) -> (status, body, final_url)` — GET, или POST при `data` (dict/str/bytes); 4xx/5xx не бросают исключение; `follow=False` — не следовать редиректам (для проверки 301);
- `admin_session(base) -> OpenerDirector | None`;
- `run_php(base, code) -> str` — выполнить PHP внутри загруженного WordPress на стенде (код без `<?php`), вернуть вывод.

## Выполнить PHP/WP-код на стенде

```
python tools/stand.py php 'echo get_option("home");'
python tools/stand.py php 'wp_set_current_user(1); print_r(kv_install());'
```

Работает через `/kv-run.php` — файл копируется только в стенд (`tools/stand/run.php`), принимает POST только с 127.0.0.1, в тему и в zip не попадает. Пользователь по умолчанию — гость; нужен админ — `wp_set_current_user(1);`.
