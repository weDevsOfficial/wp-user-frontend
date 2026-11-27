DESCRIPTION: WPUF coding standards and key design patterns.
Read before writing or reviewing code.

# WPUF Conventions

## Coding Standards
- **PHP**: WordPress Coding Standards (WPCS) via PHPCS. Run `composer phpcs` before committing.
- **JS**: JSHint (legacy), Prettier for formatting.
- **CSS**: React admin screens (builders, forms lists, subscriptions, settings, onboarding, pages, AI form builder): Tailwind 4, plain classes (no prefix), scoped to the screen body class, built by `tools/admin-css` (`pnpm run build:admin-css`). React frontend apps: plain hand-scoped CSS under `.wpuf-frontend` (autoprefixer only, no Tailwind, checked by `tools/frontend-css/check.mjs`). Classic sheets: Tailwind CSS 3 with DaisyUI (`wpuf-` prefix) only for `assets/css/frontend/account.css` (Grunt `tailwind`) and `assets/css/frontend-subscriptions.min.css` (Grunt `tailwind-minify`); the other classic jQuery CSS is plain and served as written. No Vue is left.
- **PHP Namespace**: `WeDevs\Wpuf\` for `includes/`, `WeDevs\Wpuf\Lib\` for `Lib/`.

## Key Patterns
- **Singleton pattern** via `WeDevs\WpUtils\SingletonTrait` for main plugin class.
- **Platform container**: services are built by `WeDevs\Wpuf\Platform\Container` (`wpuf()->platform()->get( Class::class )`), registered by service providers in `includes/Platform/Providers/` (Pro adds its providers on `wpuf_platform_loaded`). The legacy keys (`wpuf()->assets`, `wpuf()->subscription`, ...) are kept in the `$container[]` array with magic `__get()` and point at the same instances (`Platform\Providers\PluginServiceProvider::SERVICES`). Data is read and written through the stores (`Platform\Stores\Stores::forms()`, `::settings()`, `::subscriptions()`, ...), not with direct option, meta or SQL calls in screens or controllers.
- **WordPress hooks** extensively used for extensibility (actions & filters). Existing hooks are a public contract — do not change signatures.
- **Overridable templates** — themes can override templates from `templates/` directory. Do not break override paths.
- **Free/Pro split** — `includes/Free/Free_Loader.php` loads free-only features; Pro version detected via `WP_User_Frontend_Pro` class.
- **Form field contract** — all form fields implement `Field_Contract` for consistent rendering.
- **Legacy + modern coexistence** — the classic jQuery frontend (plain JS and CSS) alongside the React admin and frontend. Do not rewrite legacy code without explicit permission.

## Admin platform rules (branch `feature/react-admin-revamp`, see `docs/architecture.md`)
- Stored data shapes are frozen; an untouched save changes nothing.
- WPUF keeps firing every hook it owns on React screens (`docs/hooks/php.md`); retire a hook only through deprecation shims.
- Pro modules and User Directory are not edited; core provides what they use.
- Admin UI uses `@wedevs/plugin-ui` through WPUF wrappers with the old Vue look; every button is the shared `Button` wrapper (one variant/size table, no local button styles).
- Tracked docs (`docs/`, `CLAUDE.md`, `.claude/skills/`) are committed with the work and kept as built; the openspec plan files stay out of git (`openspec/` is gitignored).

## Scope Discipline
- Do not reformat or restructure code as a side effect of a feature change.
- Do not drop function parameters silently — deprecate instead.
- Build exactly what was asked; note unrelated observations separately.
