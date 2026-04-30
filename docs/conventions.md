DESCRIPTION: WPUF coding standards and key design patterns.
Read before writing or reviewing code.

# WPUF Conventions

## Coding Standards
- **PHP**: WordPress Coding Standards (WPCS) via PHPCS. Run `composer phpcs` before committing.
- **JS**: JSHint (legacy), Prettier for formatting.
- **CSS**: Tailwind CSS 3 with DaisyUI.
- **PHP Namespace**: `WeDevs\Wpuf\` for `includes/`, `WeDevs\Wpuf\Lib\` for `Lib/`.

## Key Patterns
- **Singleton pattern** via `WeDevs\WpUtils\SingletonTrait` for main plugin class.
- **Simple container** — array-based `$container[]` with magic `__get()` (not a formal DI container).
- **WordPress hooks** extensively used for extensibility (actions & filters). Existing hooks are a public contract — do not change signatures.
- **Overridable templates** — themes can override templates from `templates/` directory. Do not break override paths.
- **Free/Pro split** — `includes/Free/Free_Loader.php` loads free-only features; Pro version detected via `WP_User_Frontend_Pro` class.
- **Form field contract** — all form fields implement `Field_Contract` for consistent rendering.
- **Legacy + modern coexistence** — jQuery form builder alongside Vue 3 components, LESS alongside Tailwind, Grunt alongside Vite. Do not rewrite legacy code without explicit permission.

## Scope Discipline
- Do not reformat or restructure code as a side effect of a feature change.
- Do not drop function parameters silently — deprecate instead.
- Build exactly what was asked; note unrelated observations separately.
