---
name: wpuf-frontend-dev
description: Add or modify WPUF frontend code (jQuery, vanilla JS, Vue legacy, Tailwind). Use when creating components, modifying build configuration, or working with frontend assets.
---

# WPUF Frontend Development

This skill provides guidance for developing WP User Frontend frontend code.

## Admin platform revamp (branch `feature/react-admin-revamp`)
Applies to the builders, forms lists, subscriptions, settings and onboarding only. Read `docs/architecture.md` first.
- Components: `@wedevs/plugin-ui` through WPUF wrappers in `src/admin/shared/ui/` (target); never import plugin-ui directly in a screen. Look = old develop Vue design (primary `#059669`, hover `#10b981`).
- Buttons: always the shared `Button` (variants primary, secondary, link, destructive, icon; sizes sm, md). No local button classes, no outer margin; groups use the 12px gap.
- Stored values: never coerce with `||` (B20): `''`, `0`, `false` are real values. Write develop's shapes (toggles `'on'/'off'`, absent-when-unchecked where develop does).
- Rich text stays TinyMCE via `wp.editor` (shared `WpEditor`).
- A screen still being migrated renders `<WpufProviders host>` (design D25): plugin-ui CSS then reaches only `[data-slot]` parts and wrapper roots marked `data-wpuf-ui=""`, so legacy markup keeps its look. Every new wrapper whose root is not a plugin-ui component sets `data-wpuf-ui=""`. Keep `host` while the screen renders its own layout markup (headings, tables, grids).
- Page look (design D26, FlyHR): every shared-layer screen = `<PageShell>` with `PageHeader` (white strip; pass `helpUrl`/`helpLabel` for the screen's docs, no "Learn more" footer bands), content in white cards on the gray page, `PageFooter` last. The gray page / no gutter / hidden `#wpfooter` come from `tools/admin-css/src/pui.css`; don't set page backgrounds per screen (only override a PHP root's white class). Lists use the FlyHR list card (Tabs `toolbar`, selection bar, plain table, Pagination `footer`).
- A class that a shared wrapper also uses compiles `!important` in the plugin-ui part: inside islands/portals it beats a screen-only class on the same element (e.g. `m-0` kills `mb-16`); don't mix them.
- Owner UI rules (2026-10-04, openspec design-tokens.md "Owner corrections"): no blue focus anywhere (WordPress `input:focus` / `a:focus` rings; reset in `tools/admin-css/src/pui.css`, green keyboard outline only); radios and checkboxes keep plugin-ui's default checked look (no `data-checked:*` overrides, FlyHR style); builder alerts use `showOops()` / `SwalModal.js` (styled), never a bare `Swal.fire`; canvas markup changes are measured against develop in top-level and column/repeat bars (PAR0019); every task ends with mouse + keyboard screenshots of the touched states.
- Hooks: existing `wpuf.*` JS names are frozen; new names in `shared/filters.ts`; slots in `docs/slots/`. Free must apply every filter pro listens to.
- Build on that branch: pnpm (`pnpm install --frozen-lockfile && pnpm build`); User Directory keeps npm and is not touched.
- Every change: parity project (`tests/e2e --project=parity`) for the touched screen, existing e2e specs, runtime contract diff.

## Framework Policy

**New UI must use vanilla JS or jQuery.** Vue is legacy — only touch existing Vue code for bug fixes. Do not create new Vue components or entry points.

## Tech Stack

| Technology | Usage | Files |
|---|---|---|
| jQuery | Frontend forms, form builder, general interactivity | `assets/js/` |
| Vue 3.4 | **Legacy only**: frontend subscriptions and account (the admin lists, builders, subscriptions, settings and AI form builder are React) | `.vue` files, Vite entry points |
| Tailwind CSS 3.3.5 | Styling with scoped preflight | `tailwind.config.js` |
| Less | Legacy admin/frontend styles | `assets/less/` |
| Vite 5.1 | Bundler for Vue entry points (5 entries) | `vite.config.mjs` |
| Grunt | Legacy tasks (Less, i18n, release) | `Gruntfile.js` |

## Build System

### Vite (remaining Vue apps)

2 entry points defined in `vite.config.mjs` (the admin subscriptions, forms list and AI form builder are React apps now, built with wp-scripts; Pro has no Vite):

| Entry | Source | Output |
|---|---|---|
| `frontend-subscriptions` | `./assets/js/frontend-subscriptions.js` | `assets/js/frontend-subscriptions.min.js` |
| `account` | `./assets/js/account.js` | `assets/js/account.min.js` |

Build commands:

```bash
npm run build                        # Full build (all modules + user-directory + CSS)
npm run build:forms-list             # Single: ENTRY=forms-list vite build
npm run build:subscriptions          # Single: ENTRY=subscriptions vite build
npm run build:frontend-subscriptions # Single: ENTRY=frontend-subscriptions vite build
npm run build:ai-form-builder        # React AI form builder (wp-scripts entry + Tailwind 4 sheet)
npm run build:account                # Single: ENTRY=account vite build
npm run build:user-directory         # Build user directory module (Webpack)
npm run dev:user-directory           # Dev watch mode for user directory module
```

Vite output config:
-   JS: `assets/js/[name].min.js` (IIFE format, global name `WPUF`)
-   CSS: `assets/css/[name].min.css`
-   Source maps enabled

### Grunt (Legacy Tasks)

```bash
grunt less:front      # Compile frontend Less
grunt less:admin      # Compile admin Less
grunt tailwind        # Generate Tailwind CSS
grunt tailwind-minify # Minify Tailwind output
grunt watch           # Watch for file changes
grunt makepot         # Generate .pot translation file
pnpm run release      # install, clean, build once, POT, zip (bin/zip.mjs)
```

### CSS Build

```bash
npm run build:css     # grunt tailwind && grunt tailwind-minify
```

## Vue 3 Components (Legacy)

Vue 3.4 exists for **legacy admin pages only**. Do not create new Vue components.

### Key Libraries

-   `vue-router 4.3` — Client-side routing
-   `@vueform/multiselect 2.6` — Multi-select inputs
-   `@vuepic/vue-datepicker 8.2` — Date pickers
-   `@headlessui/vue 1.7` — Accessible UI primitives
-   `@heroicons/vue 2.1` — Icon set

### Source Structure

```
src/
├── admin/           # Admin page components
├── components/      # Shared components
├── css/             # Source CSS
└── router/          # Vue Router config

assets/js/
├── subscriptions.js          # Vue entry: admin subscriptions
├── frontend-subscriptions.js # Vue entry: frontend packs page
├── forms-list.js             # Vue entry: forms listing
└── account.js                # Vue entry: user account
```

## jQuery (Legacy)

jQuery is still heavily used for:
-   Frontend post forms (`frontend-form.js`)
-   Form builder UI (`wpuf-form-builder.js`)
-   File uploads (`wpuf-upload.js`)
-   Validation (`jquery.validate`)

These are **not** built via Vite/Webpack — they live directly in `assets/js/`.

## Tailwind CSS

### React admin screens (branch `feature/react-admin-revamp`): Tailwind 4, NO prefix

Builders, forms lists, subscriptions and settings (free `src/admin/apps/{form-builder,forms-list,subscriptions,settings}` since 3.5, shared layer `src/admin/shared`; Pro `admin/form-builder/src`, `admin/forms-list/src`) use **plain Tailwind 4 classes** (`flex`, `text-sm`, `shadow-xs`), never `wpuf-` and never `wpuf:`.

-   Built by `tools/admin-css` (own tailwindcss@4): `pnpm run build:admin-css` (free), `npm run build:admin-css` (Pro). One entry: `node tools/admin-css/build.mjs <settings|subscriptions|forms>`.
-   Utilities are scoped `:where(.wpuf-admin-screen) .x`; the body class comes from `Screen::body_class()` / `Assets::forms_body_class()`.
-   Base + components (daisyUI, `wpuf-` component classes) are frozen Tailwind 3 output in `tools/admin-css/src/base-v3/` (regenerate with `gen-v3-base.cjs`, needs the root tailwindcss@3). Keep component class names (`wpuf-btn`, ...) as they are.
-   A class that never had CSS in Tailwind 3 must not start working: block it with `@source not inline(...)` in `src/<screen>.css`.
-   Pro's utilities sheet prints before Free's `forms-screen.css` (Free wins shared names).

Everything below (prefix `wpuf-`, Tailwind 3) is for the frontend, the Vue apps and legacy CSS.

Config: `tailwind.config.js`

### Key Settings

-   **Prefix:** `wpuf-` — All Tailwind classes are prefixed to prevent conflicts
-   **Preflight:** Scoped via `tailwindcss-scoped-preflight` — only applies inside specific containers
-   **Plugins:** `@tailwindcss/forms` (class strategy), `daisyui`
-   **Primary color:** `emerald[600]`

### Scoped Containers

Tailwind preflight styles only apply inside these selectors:

```
.wpuf_packs
#wpuf-subscription-page
#wpuf-form-builder
#wpuf-profile-forms-list-table-view
#wpuf-post-forms-list-table-view
#wpuf-ai-form-builder
.wpuf-ai-form-wrapper
.swal2-container
.wpuf-account-container
.wpuf-form-template-modal
```

### Usage

```html
<!-- All Tailwind classes must use wpuf- prefix -->
<div class="wpuf-bg-primary wpuf-text-white wpuf-p-4">
    <button class="wpuf-btn wpuf-btn-primary">Submit</button>
</div>
```

## Localization / Translation (JavaScript)

### Vue Components

Use `@wordpress/i18n` via import:

```js
import { __, _n, sprintf } from '@wordpress/i18n';

const label = __( 'Save changes', 'wp-user-frontend' );
```

### Localized Data (PHP -> JS)

Server-side data is passed via `wp_localize_script()`:

-   `wpuf_admin_script` — Admin-side data (nonce, URLs, version, Pro status)
-   `wpufAIFormBuilder` — AI form builder config (endpoints, templates, i18n)

### Key Rules

-   **Text domain:** Always use `'wp-user-frontend'` (not `'wpuf'`)
-   **Never concatenate** translated strings — use `sprintf()` with placeholders
-   **Always add translator comments** for strings with placeholders

## Asset Registration

Scripts and styles are registered in `includes/Assets.php`. When adding new assets:

1.  Register the script/style in `Assets.php`
2.  Enqueue in the appropriate admin/frontend hook
3.  Use `wp_set_script_translations()` for translation support

## Key Reference Files

-   `vite.config.mjs`: Vite configuration (3 entry points)
-   `tailwind.config.js` — Tailwind with `wpuf-` prefix and scoped preflight
-   `postcss.config.js` — PostCSS configuration
-   `Gruntfile.js` — Legacy tasks (Less, i18n, release)
-   `package.json` — All build scripts and dependencies
-   `includes/Assets.php` — Script/style registration
-   `includes/Admin.php` — Admin script enqueuing

## Form template picker (forms lists)

-   "Add New" on the post and registration forms lists opens `src/admin/apps/forms-list/components/TemplatePicker.jsx`; its data comes from `WeDevs\Wpuf\Admin\Forms\Template_Picker::data()` (global `wpuf_form_templates`).
-   A template's `image` is a screenshot of the form it creates (`assets/images/templates/*.webp`, 800px wide). New templates need one: create the form, capture the frontend form element, convert to webp.
-   `/assets/*` is gitignored: add new images with `git add -f`.
