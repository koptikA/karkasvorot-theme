# Handoff for Codex: karkasvorot.by WordPress theme

Written 2026-11-09 by Claude Code, mid-build. Read this file first, then `CLAUDE.md`.

## What this project is

A new classic WordPress theme `karkasvorot` for karkasvorot.by: a metal-works shop (frames for sliding and swing gates, wickets, other items) in Minsk and the Minsk region. It replaces a one-page landing. Goals: search promotion (Yandex and Google), incoming leads, and Belarus legal compliance. The site owner edits works and prices in wp-admin. Hosting stays at hoster.by (Belarus).

## Where things are

- `theme/karkasvorot/`: the theme (PHP 7.4 compatible, no plugins, no build step).
- `tools/`: local WordPress Playground stand, site checks, fixtures copied from the live media library.
- `.autopilot/2026-11-09-karkasvorot-site--wip/`: run records.
  - `spec.md`: the specification.
  - `interfaces.md`: the module contract plus what tickets 01–04 built. Read it before touching code.
  - `manifest.md`: requirements, each with the client's own words.
  - `tickets/`: one file per ticket.
- `.autopilot/state.js`: run state for the dashboard.

## Status

| Ticket | What | State |
|---|---|---|
| 01 | Theme skeleton, Customizer settings, content model, Playground stand, check runner | done, commit bd9e3d9 |
| 02 | Pages, texts, works import, installer | done, 01c197e |
| 03 | Order form, lead storage, cookie consent, Metrika goals | done, 260d925 |
| 04 | SEO: meta, JSON-LD, robots, sitemap, redirects | done, e6c3cf6 |
| 05 | Launch guide, design brief for Astra6, zip, perf/a11y, ~25 deferred review fixes | **done — see docs/proverka-05.md** |

When this file was written, `python tools/check_site.py http://127.0.0.1:9400` was green on all 6 modules.

## Your job: finish ticket 05

1. Run `git status` first. Ticket 05 was being worked on when the session ended, so there may be uncommitted changes. Review them and keep the ones that are sound. Do not blindly reset.
2. Read `.autopilot/2026-11-09-karkasvorot-site--wip/tickets/05-zapusk-dizain-brif-proverka.md` end to end, including the long «Дополнительно — отложенные замечания ревью» list. All of it is in scope.
3. Start with `tools/serve.sh`: make the port configurable. Right now it hard-codes `--port 9400`, and passing a second `--port` breaks the CLI. Until that is fixed, start the stand directly:
   `export MSYS_NO_PATHCONV=1 MSYS2_ARG_CONV_EXCL="*"; ROOT="$(pwd -W)"; npx -y @wp-playground/cli@latest server --port 9405 --php 7.4 --login --mount-dir "$ROOT/theme/karkasvorot" /wordpress/wp-content/themes/karkasvorot --mount-dir "$ROOT/tools/fixtures" /kv-fixtures --mount-dir "$ROOT/tools/stand" /kv-stand --blueprint "$ROOT/tools/stand/blueprint.json"`
   Wait for the «Ready!» line. Every start rebuilds the site from scratch.
4. Fix each deferred finding. Where possible, write the check in `tools/checks/*.py` first and see it fail.
5. Write the deliverables:
   - `docs/zapusk.md`: for the shop owner, in Russian, no jargon, step by step. Include a separate list of what to fill in before launch.
   - `docs/dizain-brif.md`: for Astra6 (a Codex design agent). Precise: pages and blocks, `:root` tokens, BEM classes, what may and may not be changed, size budget.
   - Screenshots in `docs/screens/` at 390 px and 1440 px width.
   - `tools/build_zip.py` producing `dist/karkasvorot.zip`.
   - `tools/checks/perf.py` plus Lighthouse mobile at ≥ 90. If no headless browser is available, record it as skipped, not as passed.
6. Run the full suite: `python tools/check_site.py http://127.0.0.1:<port>`. It must be green.
7. Commit: `feat(05): …`.

## Rules that must not be broken

- Never invent business facts: prices, deadlines, warranties, years in business, reviews, addresses, requisites. Unknown values stay as visible placeholders. Requisites (name, УНП, address), lead e-mail, opening hours, and prices for fences and canopies are still unknown.
- Never write the WP admin password into any file. Do not log into or change the live site karkasvorot.by: launch happens only when the owner explicitly says so.
- PHP 7.4 syntax only: no `match`, no `?->`, no `str_contains`, no union types.
- Escape on output, sanitize on input, use the `kv_` prefix. Checks use the Python stdlib only.
- Keep line endings as committed (`git config core.autocrlf false` is set).

## After ticket 05

Final acceptance: compare the finished site against `.autopilot/2026-11-09-karkasvorot-site--wip/2026-11-09-brief.md`. Then report to the owner in Russian, short and plain:
- what was built;
- what is still a placeholder;
- what goes to Astra6 for design;
- launch steps.

Design will be done later by Astra6. Its result then has to be ported into `assets/` with all checks still green.

## Completed 2026-11-09 by Codex

Ticket 05 is complete. Full local suite including Lighthouse: GREEN. Deliverables: `docs/zapusk.md`, `docs/dizain-brif.md`, `docs/screens/`, `docs/proverka-05.md`, `dist/karkasvorot.zip`. Zip uploaded and activated on a clean local Playground. Production was not accessed. Next step: Astra6 design, then rerun checks and rebuild the zip; owner fills missing business data and approves launch. Priority confirmed by owner: organic-search enquiries and orders, not appearance alone.

## Visual design completed 2026-11-09

Owner requested implementation. CSS design and explicit priority hero image applied; PHP/SEO/form logic unchanged. Updated screenshots and zip, full suite including Lighthouse GREEN. See `docs/oformlenie.md`. Local preview on port 9425 remains available for owner review. Production untouched.
