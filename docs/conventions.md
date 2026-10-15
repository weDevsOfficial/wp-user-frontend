DESCRIPTION: WPUF coding standards and key design patterns.
Read before writing or reviewing code.

# WPUF Conventions

## Coding Standards
- **PHP**: WordPress Coding Standards (WPCS) via PHPCS. Run `composer phpcs` before committing.
- **JS**: JSHint (legacy), Prettier for formatting.
- **CSS**: frontend, Vue apps and legacy: Tailwind CSS 3 with DaisyUI (`wpuf-` prefix). React admin screens (builders, forms lists, subscriptions, settings): Tailwind 4, plain classes (no prefix), built by `tools/admin-css` (`pnpm run build:admin-css`).
- **PHP Namespace**: `WeDevs\Wpuf\` for `includes/`, `WeDevs\Wpuf\Lib\` for `Lib/`.

## Key Patterns
- **Singleton pattern** via `WeDevs\WpUtils\SingletonTrait` for main plugin class.
- **Simple container** — array-based `$container[]` with magic `__get()` (not a formal DI container).
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
- Docs (`docs/`, `CLAUDE.md`, skills) for this work stay local and are not committed.

## Scope Discipline
- Do not reformat or restructure code as a side effect of a feature change.
- Do not drop function parameters silently — deprecate instead.
- Build exactly what was asked; note unrelated observations separately.
