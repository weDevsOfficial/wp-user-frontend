# WP User Frontend — CLAUDE.md

## What This Is

WP User Frontend (WPUF) is a WordPress plugin that enables frontend content management — post submission, user registration/login, dashboards, subscriptions, guest posting, content restriction, and AI form building. Requires PHP 5.6+.

## Skill Routing (invoke these, don't re-explain them)

The `.claude/skills/` directory has procedural HOW-TOs. **Invoke the matching skill before starting work.**

| When you're about to… | Invoke |
|---|---|
| Write or modify PHP | [`wpuf-backend-dev`](.claude/skills/wpuf-backend-dev/SKILL.md) |
| Write or modify React / JS / CSS | [`wpuf-frontend-dev`](.claude/skills/wpuf-frontend-dev/SKILL.md) |
| Review a PR or code change | [`wpuf-code-review`](.claude/skills/wpuf-code-review/SKILL.md) |

## Deeper Docs (read on demand)

| Topic | File | When to read |
|---|---|---|
| Architecture (directory layout, init flow, services, REST, payments, fields) | [`docs/architecture.md`](docs/architecture.md) | New to the codebase, or touching a subsystem you don't know |
| Coding standards & patterns | [`docs/conventions.md`](docs/conventions.md) | Before writing non-trivial code |
| Build & test commands | [`docs/build-and-test.md`](docs/build-and-test.md) | Before running lint/build/tests |
| Hooks (PHP rules, generated index, JS hooks, Vue -> React migration) | [`docs/hooks/`](docs/hooks/php.md) | Before adding, moving or retiring a hook |
| Slots on admin React screens | [`docs/slots/README.md`](docs/slots/README.md) | Before injecting UI into an admin React screen |
| Settings React | [`docs/settings-screen.md`](docs/settings-screen.md) | Before adding or changing a setting |

Per-directory `CLAUDE.md` files auto-load when Claude works in that path:
- [`includes/Fields/CLAUDE.md`](includes/Fields/CLAUDE.md) — form field contract & conventions
- [`tests/e2e/CLAUDE.md`](tests/e2e/CLAUDE.md) — Playwright patterns

## Non-Negotiables

- **Preserve existing behavior.** Hooks, filters, public methods, REST routes, template paths, and shortcodes are contracts — don't change signatures or drop parameters silently.
- **Legacy code stays.** The site frontend, field types, payments, LESS and Grunt coexist with the React admin platform intentionally. Don't rewrite legacy code without explicit permission; renamed classes keep a `class_alias` stub at the old name.
- **Free/Pro split matters.** Never assume Pro features exist. Detect with `class_exists('WP_User_Frontend_Pro')`.
- **Scope discipline.** No drive-by refactors. Note unrelated issues separately instead of fixing them inline.

## Admin platform revamp (branch `feature/react-admin-revamp`)

Every admin screen runs on a FlyHR-style platform (`includes/Platform/`, `includes/Admin/Screens/`, `src/admin/`, `@wedevs/plugin-ui` with the old Vue look, Dokan-style hooks) as one React app on `admin.php?page=wp-user-frontend`; old admin URLs redirect to their route. Read [`docs/architecture.md`](docs/architecture.md) first (boot flow and the "where is X" map). Rules: stored data frozen (untouched save changes nothing), WPUF keeps firing every hook it owns, Pro modules and User Directory are not edited, every in-scope button is the shared `Button` wrapper, pnpm on that branch, tracked docs are committed with the work. Plan and evidence: `openspec/changes/revamp-admin-react-flyhr/` in the dev site.

## Quick Reference

### Entry Point
- `wpuf.php` — Main plugin file, defines constants, loads autoloader, bootstraps `WP_User_Frontend` singleton

### Initialization Flow
1. `wpuf.php` loads Composer autoloader (`vendor/autoload.php`)
2. Defines constants (`WPUF_VERSION`, `WPUF_FILE`, `WPUF_ROOT`, etc.)
3. Creates `WP_User_Frontend` singleton via `wpuf()` helper
4. Manually includes `wpuf-functions.php`, `Frontend_Render_Form`, reCaptcha libs, AI manager, and gateway helpers
5. On `plugins_loaded`: loads insights tracker, free/pro loader, upgrades, then `instantiate()` builds every core service from the platform container (`Platform\Providers\PluginServiceProvider`, eager, in the old order) into `$this->container[]` and boots the platform (`wpuf_platform_loaded`)
6. Services available via magic getter: `wpuf()->service_name`

### Directory Structure

```
wp-user-frontend/
├── wpuf.php                # Main plugin file & bootstrap class
├── wpuf-functions.php      # Loads the includes/functions/<domain>.php helpers
├── includes/               # PHP backend (264 files)
│   ├── Admin/              # Admin settings, menus, subscription management
│   ├── Ajax/               # AJAX handlers (forms, uploads, addresses)
│   ├── AI/                 # AI form builder (config, prompts, REST)
│   ├── Builder/            # Builder boot data, save, hook bridge, retired-hook deprecations
│   ├── Fields/             # Form field types (20+ field classes)
│   ├── Free/               # Free version functionality & pro prompts
│   ├── Frontend/           # Frontend forms, dashboard, registration, payments
│   ├── Hooks/              # Form settings cleanup hooks
│   ├── Integrations/       # Third-party integrations (ACF, Dokan, WC Vendors, n8n)
│   ├── Traits/             # Shared traits (FieldableTrait, TaxableTrait)
│   ├── Widgets/            # WordPress widgets
│   ├── Data/               # Data layer classes
│   ├── Log/                # Logging utilities
│   ├── Platform/           # Container, providers, REST (Manager + Controllers), stores, Caps, Onboarding services
│   ├── upgrades/           # Version migration scripts
│   └── Abstracts/          # Abstract base classes
├── src/                    # React source (no Vue left)
│   ├── admin/              # React admin: app/ (runtime), apps/<screen>/, shared/
│   ├── frontend/           # React frontend: kit, forms, account, login (Renderer)
│   ├── css/                # Admin + frontend source styles
│   └── js/                 # Blocks, user directory
├── admin/form-builder/views # Legacy builder PHP views still included
├── templates/              # Overridable PHP templates
│   ├── dashboard/          # User dashboard templates
│   ├── subscriptions/      # Subscription plan templates
│   ├── dokan/              # Dokan marketplace integration templates
│   ├── wc-marketplace/     # WC Marketplace integration templates
│   └── *.php               # Login, registration, account, post submission
├── assets/                 # Compiled CSS/JS output
│   ├── css/                # Compiled stylesheets
│   ├── js/                 # Compiled JS bundles
│   ├── images/             # Static images
│   ├── less/               # LESS source files (legacy)
│   └── vendor/             # Third-party vendor assets
├── Lib/                    # Third-party libraries
│   ├── Gateway/            # Payment gateways (PayPal, Bank)
│   ├── Appsero/            # License & analytics
│   └── *.php               # reCaptcha, settings API, insights
├── modules/                # Self-contained feature modules
│   └── user-directory/     # User directory module (own npm project)
├── tests/
│   └── e2e/                # Playwright E2E tests
├── views/                  # Legacy PHP view files
├── config/                 # Configuration files
└── languages/              # Translation files
```

### Key PHP Files
- `wpuf-functions.php` — loads the global helpers split by domain under `includes/functions/`
- `includes/Assets.php` — Script/style registration and enqueuing
- `includes/Frontend_Render_Form.php` / `includes/Render_Form.php` — Core form rendering engine
- `includes/User_Subscription.php` — Subscription plan management
- `includes/Installer.php` — Plugin activation, DB table creation
- `includes/API.php` — builds the three frozen REST controllers (`wpuf()->api`); routes are registered by `Platform\REST\Manager`
- `includes/AI_Manager.php` — AI form builder orchestration

### Service Container
Simple array-based container with magic `__get()`. Services accessed via `wpuf()->service_name`. Major services:
`assets`, `subscription`, `fields`, `customize`, `bank`, `paypal`, `gateway_manager`, `api`, `integrations`, `ai_manager`, `admin`, `frontend`, `ajax`, `setup_wizard`, `pro_upgrades`, `privacy`, `widgets`, `tracker`, `free_loader`, `upgrades`

### Frontend Architecture
- **React** for every admin screen (`webpack.admin.config.js`, wp-scripts) and the optional React frontend (`webpack.frontend.config.js`); no Vue is left
- **jQuery** (legacy) for the classic frontend forms and older frontend interactions
- **Vite** builds two plain entries (`assets/js/frontend-subscriptions.js`, `assets/js/account.js`), no Vue
- **Grunt** for legacy build tasks (LESS compilation, Tailwind CSS, release packaging)
- **Tailwind CSS 3** with DaisyUI component library and scoped preflight
- **LESS** (legacy) for older stylesheets

### REST API
Namespace `wpuf/v1`. Controllers live in `includes/Platform/REST/Controllers/` on the `Platform\REST\RestController` base: the admin routes (`FormsController`, `ToolsController`, `TransactionsController`, `SubscribersController`, `OnboardingController`), the frontend routes (`FormsPublicController`, `AccountController`, `UploadsController`) and the frozen routes (`FormListController`, `SubscriptionController`, `SettingsController`). The AI form builder keeps its controller with its feature (`includes/AI/RestController.php`); Pro adds its own under `wpuf-pro/includes/Platform/<Feature>/`. `Platform\REST\Manager` registers all of them once on `rest_api_init`; permissions come from `Platform\Caps`.

### Payment System
Payment gateways managed through `Lib/Gateway/`:
- `Gateway_Manager.php` — Gateway registration and orchestration
- `Paypal_Gateway.php` / `Paypal.php` — PayPal integration
- `Bank_Gateway.php` / `Bank.php` — Bank transfer support
- Frontend payment handling via `includes/Frontend/Payment.php`

### Form Fields System
20+ form field types under `includes/Fields/`, each extending `Field_Contract.php`:
Text, Textarea, Email, URL, Dropdown, MultiDropdown, Checkbox, Radio, Image, Featured Image, Post Title, Post Content, Post Excerpt, Post Tags, Post Taxonomy, Hidden, HTML, Section Break, Column, reCaptcha, Cloudflare Turnstile

## Coding Standards

- **PHP**: WordPress Coding Standards (WPCS) via PHPCS
- **JS**: JSHint (legacy), Prettier for formatting
- **CSS**: Tailwind CSS 3 with DaisyUI
- **PHP Namespace**: `WeDevs\Wpuf\` for `includes/`, `WeDevs\Wpuf\Lib\` for `Lib/`

## Key Patterns

- **Singleton pattern** via `WeDevs\WpUtils\SingletonTrait` for main plugin class
- **Simple container** — array-based `$container[]` with magic `__get()` (not a formal DI container)
- **WordPress hooks** extensively used for extensibility (actions & filters)
- **Overridable templates** — themes can override templates from `templates/` directory
- **Free/Pro split** — `includes/Free/Free_Loader.php` loads free-only features; Pro version detected via `WP_User_Frontend_Pro` class
- **Form field contract** — all form fields implement `Field_Contract` for consistent rendering
- **Legacy + modern coexistence** — classic jQuery frontend alongside the React frontend, LESS alongside Tailwind, Grunt alongside Vite and wp-scripts
- **Third-party integrations** — Dokan, WC Vendors, WC Marketplace, ACF, n8n, Events Calendar

## Testing

- **Playwright** for E2E tests in `tests/e2e/`
  - A single `playwright.config.ts` with `setup` / `e2e` / `api` projects (select via `--project`)
  - The `e2e` project is sharded via Playwright's native `--shard=i/n`
- **PHPUnit 7.5.9** listed as dev dependency (test infrastructure in development)

## Integrations

WPUF integrates with several marketplace and third-party plugins:
- **Dokan** — Vendor registration forms and dashboard integration
- **WC Vendors** — Vendor frontend forms
- **WC Marketplace** — Marketplace compatibility
- **ACF** — Advanced Custom Fields compatibility
- **n8n** — Workflow automation integration
- **Events Calendar** — Event post type support

## Code Review Prevention (MANDATORY)

**Apply these rules to ALL new/modified code. Top causes of review rejection.**

### Zero-Tolerance Rules

1. **Strict comparisons only** — `===`/`!==` always, never `==`/`!=`
2. **`in_array()`/`array_search()` strict** — always pass `true` as 3rd arg
3. **Superglobals** — `wp_unslash()` + sanitize every `$_POST/$_GET/$_REQUEST/$_SERVER` access. Use `sanitize_text_field(wp_unslash(...))`, `absint()`, `sanitize_email(wp_unslash(...))`, `esc_url_raw(wp_unslash(...))`, `sanitize_key(wp_unslash(...))`
4. **Escape all output** — `esc_html()` for text, `esc_attr()` for attributes, `esc_url()` for URLs, `wp_kses_post()` for HTML
5. **SQL safety** — `$wpdb->prepare()` for ALL dynamic values. Allowlist column names for ORDER BY
6. **Nonce + permission** — every form/AJAX: `check_ajax_referer()` + `current_user_can(wpuf_admin_role())`

### Required Standards

7. **snake_case methods** — never camelCase in PHP
8. **DocBlocks** — `@since WPUF_SINCE` on every new public/protected method
9. **Translator comments** — `/* translators: */` before every `sprintf()` with `__()`
10. **Text domain** — `'wp-user-frontend'` (free), `'wpuf-pro'` (pro), never `'wpuf'`
11. **Hook prefix** — all `do_action()`/`apply_filters()` names start with `wpuf_`
12. **WP spacing** — `if ( $x )` not `if($x)`, `! $x` not `!$x`

### Pre-PR Checklist

Run `composer phpcs` — zero violations before submitting. See `wpuf-backend-dev` skill for full examples.
