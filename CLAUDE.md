# WP User Frontend — CLAUDE.md

## What This Is

WP User Frontend (WPUF) is a WordPress plugin that enables frontend content management — post submission, user registration/login, dashboards, subscriptions, guest posting, content restriction, and AI form building. Requires PHP 5.6+.

## Skill Routing (invoke these, don't re-explain them)

The `.claude/skills/` directory has procedural HOW-TOs. **Invoke the matching skill before starting work.**

| When you're about to… | Invoke |
|---|---|
| Write or modify PHP | [`wpuf-backend-dev`](.claude/skills/wpuf-backend-dev/SKILL.md) |
| Write or modify Vue / JS / CSS | [`wpuf-frontend-dev`](.claude/skills/wpuf-frontend-dev/SKILL.md) |
| Review a PR or code change | [`wpuf-code-review`](.claude/skills/wpuf-code-review/SKILL.md) |

## Deeper Docs (read on demand)

| Topic | File | When to read |
|---|---|---|
| Architecture (directory layout, init flow, services, REST, payments, fields) | [`docs/architecture.md`](docs/architecture.md) | New to the codebase, or touching a subsystem you don't know |
| Coding standards & patterns | [`docs/conventions.md`](docs/conventions.md) | Before writing non-trivial code |
| Build & test commands | [`docs/build-and-test.md`](docs/build-and-test.md) | Before running lint/build/tests |

Per-directory `CLAUDE.md` files auto-load when Claude works in that path:
- [`includes/Fields/CLAUDE.md`](includes/Fields/CLAUDE.md) — form field contract & conventions
- [`tests/e2e/CLAUDE.md`](tests/e2e/CLAUDE.md) — Playwright patterns

## Non-Negotiables

- **Preserve existing behavior.** Hooks, filters, public methods, REST routes, template paths, and shortcodes are contracts — don't change signatures or drop parameters silently.
- **Legacy code stays.** jQuery form builder, LESS, Grunt, and the `class/` directory coexist with modern Vue/Tailwind/Vite intentionally. Don't rewrite legacy code without explicit permission.
- **Free/Pro split matters.** Never assume Pro features exist. Detect with `class_exists('WP_User_Frontend_Pro')`.
- **Scope discipline.** No drive-by refactors. Note unrelated issues separately instead of fixing them inline.

## Quick Reference

- Entry point: `wpuf.php` → `wpuf()` singleton
- Service access: `wpuf()->service_name` (e.g., `wpuf()->subscription`)
- Namespace: `WeDevs\Wpuf\` (includes/) and `WeDevs\Wpuf\Lib\` (Lib/)
- Main lint: `composer phpcs`
- Main build: `npm run build`
