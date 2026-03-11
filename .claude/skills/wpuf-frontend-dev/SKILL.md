---
name: wpuf-frontend-dev
description: Add or modify WPUF frontend code (React, jQuery, vanilla JS, Tailwind). Use when creating components, modifying build configuration, or working with frontend assets.
---

# WPUF Frontend Development

This skill provides guidance for developing WP User Frontend frontend code.

## Framework Policy

**New UI must use React, vanilla JS, or jQuery.** Vue is legacy — only touch existing Vue code for bug fixes. Do not create new Vue components or entry points.

## Tech Stack

| Technology | Usage | Files |
|---|---|---|
| React 18.2 | New UI development (preferred for new features) | `.jsx`/`.tsx` files, Webpack entry |
| jQuery | Frontend forms, form builder, general interactivity | `assets/js/` |
| Vue 3.4 | **Legacy only** — existing admin pages (subscriptions, forms list, AI builder, account) | `.vue` files, Vite entry points |
| Tailwind CSS 3.3.5 | Styling with scoped preflight | `tailwind.config.js` |
| Less | Legacy admin/frontend styles | `assets/src/less/` |
| Vite 5.1 | Bundler for legacy Vue entry points (5 entries) | `vite.config.mjs` |
| Webpack 5 | React builds | `webpack.config.js` |
| Grunt | Legacy tasks (Less, i18n, release) | `Gruntfile.js` |

## Build System

### Vite (Primary — Vue Components)

5 entry points defined in `vite.config.mjs`:

| Entry | Source | Output |
|---|---|---|
| `subscriptions` | `./assets/js/subscriptions.js` | `assets/js/subscriptions.min.js` |
| `frontend-subscriptions` | `./assets/js/frontend-subscriptions.js` | `assets/js/frontend-subscriptions.min.js` |
| `forms-list` | `./assets/js/forms-list.js` | `assets/js/forms-list.min.js` |
| `account` | `./assets/js/account.js` | `assets/js/account.min.js` |
| `ai-form-builder` | `./assets/js/ai-form-builder.js` | `assets/js/ai-form-builder.min.js` |

Build commands:

```bash
npm run build                        # Full build (all modules)
npm run build:forms-list             # Single: ENTRY=forms-list vite build
npm run build:subscriptions          # Single: ENTRY=subscriptions vite build
npm run build:frontend-subscriptions # Single: ENTRY=frontend-subscriptions vite build
npm run build:ai-form-builder        # Single: ENTRY=ai-form-builder vite build
npm run build:account                # Single: ENTRY=account vite build
```

Vite output config:
-   JS: `assets/js/[name].min.js` (IIFE format, global name `WPUF`)
-   CSS: `assets/css/[name].min.css`
-   Source maps enabled

### Webpack (React Subscription)

Single entry point extending `@wordpress/scripts` config:

```bash
npm run build:subscriptions-react    # wp-scripts build
npm run start:subscriptions-react    # wp-scripts start (dev watch)
```

Output: `assets/react-build/js/subscriptions-react.min.js`

### Grunt (Legacy Tasks)

```bash
grunt less:front      # Compile frontend Less
grunt less:admin      # Compile admin Less
grunt tailwind        # Generate Tailwind CSS
grunt tailwind-minify # Minify Tailwind output
grunt watch           # Watch for file changes
grunt makepot         # Generate .pot translation file
grunt release         # Full release build
npm run release       # Alias for grunt release
```

### CSS Build

```bash
npm run build:css     # grunt tailwind && grunt tailwind-minify
```

## Vue 3 Components (Legacy)

Vue 3.4 exists for **legacy admin pages only**. Do not create new Vue components — use React instead.

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
├── account.js                # Vue entry: user account
└── ai-form-builder.js        # Vue entry: AI form builder
```

## React Components

React 18.2 is used for the newer subscription management UI.

### Key Libraries

-   `@wordpress/element` — React wrapper for WordPress
-   `@wordpress/data` — State management
-   `@wordpress/api-fetch` — REST API client
-   `react-select 5.8` — Select inputs
-   `react-datepicker 6.0` — Date pickers
-   `@headlessui/react 1.7` — Accessible UI primitives
-   `@heroicons/react 2.1` — Icon set

### Source

```
src/js/
└── subscriptions-react.jsx   # React entry point
```

## jQuery (Legacy)

jQuery is still heavily used for:
-   Frontend post forms (`frontend-form.js`)
-   Form builder UI (`wpuf-form-builder.js`)
-   File uploads (`wpuf-upload.js`)
-   Validation (`jquery.validate`)

These are **not** built via Vite/Webpack — they live directly in `assets/js/`.

## Tailwind CSS

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

-   `vite.config.mjs` — Vite configuration (5 entry points)
-   `webpack.config.js` — Webpack config (React subscriptions)
-   `tailwind.config.js` — Tailwind with `wpuf-` prefix and scoped preflight
-   `postcss.config.js` — PostCSS configuration
-   `Gruntfile.js` — Legacy tasks (Less, i18n, release)
-   `package.json` — All build scripts and dependencies
-   `includes/Assets.php` — Script/style registration
-   `includes/Admin.php` — Admin script enqueuing
