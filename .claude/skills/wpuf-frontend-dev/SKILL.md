---
name: wpuf-frontend-dev
description: Add or modify WPUF JavaScript and CSS on the v5.0.0 platform: React admin apps (webpack.admin.config.js, @wpuf/components on plugin-ui, @wordpress/data stores, wp.hooks), React frontend apps (webpack.frontend.config.js, @wpuf/frontend-kit, scoped .wpuf-frontend CSS), the classic jQuery frontend, builds with pnpm and jest tests. Use when creating or changing components, screens, stores, JS hooks, styles or build configuration.
---

# WPUF Frontend Development

How to write JS and CSS for WP User Frontend as built on `feature/react-admin-revamp` (release 5.0.0). Every admin screen is React; the site frontend has React apps plus the classic jQuery files they replace where a theme override, the block or Elementor needs them. There is no Vue and no Vite left. Read `docs/architecture.md`, `docs/hooks/js.md` and `docs/slots/README.md` first when you touch a screen you do not know.

## When to use

Invoke before you:

-   add or change a React admin app, a shared admin component, a store or a JS hook / slot
-   add or change a React frontend app or kit component (forms, account, login, plans, checkout)
-   change admin or frontend CSS
-   change `package.json` scripts, `webpack.*.config.js`, `tools/admin-css` or `tools/frontend-css`
-   touch the classic jQuery frontend (`assets/js/frontend-form.js`, `upload.js`, `billing-address.js`, ...)

## Tech stack

| Part | Tech | Source | Build | Output |
|---|---|---|---|---|
| Admin apps | React 18, `@wordpress/element`, `@wordpress/data`, `@wordpress/hooks`, `@wedevs/plugin-ui` | `src/admin/apps/<app>/`, shell `src/admin/app/`, shared `src/admin/shared/` | `webpack.admin.config.js` (wp-scripts) | `assets/js/screens/<name>.js` + `.asset.php` (admin entries import no CSS; screen sheets come from `build:admin-css`) |
| Admin screen CSS | Tailwind 4, plain classes (no prefix), scoped `:where(.wpuf-admin-screen)` | `tools/admin-css/src/*.css` | `node tools/admin-css/build.mjs <entry>` | `assets/css/settings-screen.css`, `assets/css/admin/*-screen.css`, ... |
| Frontend apps | React 18, `@wpuf/frontend-kit` (alias of `src/frontend/kit`), lucide-react | `src/frontend/{forms,account,login,plans,checkout}/`, kit `src/frontend/kit/` | `webpack.frontend.config.js` | `assets/js/frontend/<name>.js`, `assets/css/frontend/<name>.css` |
| Frontend CSS | Plain hand-scoped CSS under `.wpuf-frontend`, autoprefixer only (no Tailwind) | `src/frontend/**/*.css` | part of `build:frontend`, checked by `tools/frontend-css/check.mjs` | `assets/css/frontend/*.css` |
| Classic frontend | jQuery, plain CSS | `assets/js/*.js`, `assets/css/*` (tracked, served as written) | none | same files |
| Classic account sheet | Tailwind 3, `wpuf-` prefix | `src/css/frontend/account.css` (Gruntfile maps it for `templates/account.php`) | `grunt tailwind` (`build:tailwind`) | `assets/css/frontend/account.css` |
| Classic plans sheet | Tailwind 3 | `assets/css/frontend-subscriptions.css` | `grunt tailwind-minify` (`build:tailwind-minify`) | `assets/css/frontend-subscriptions.min.css` |
| Post form block | wp-scripts | `src/js/blocks/` | `webpack.config.js` (`build:blocks`) | `assets/js/blocks/` |
| User Directory | own npm project, not edited by the revamp | `modules/user-directory/` | `build:user-directory` (npm) | module assets |

Pro has the same admin model: `wpuf-pro/webpack.admin.config.js` (`pnpm exec webpack`, per-entry scripts like `build:form-builder-pro`, `build:coupons-screen`) and its own `tools/admin-css`.

## Package manager and builds

pnpm on this branch (`packageManager` pins the version; Node 22). Never `npm install` in the plugin root; `modules/user-directory` keeps npm.

```bash
pnpm install --frozen-lockfile

# Admin: one app (form-builder | forms-list-screen | subscriptions | settings-screen |
# ai-form-builder | admin-app | registration-promo | onboarding | welcome | help | tools |
# transactions | coupons-promo | modules-preview | admin-runtime | admin-ui)
WPUF_ENTRY=tools pnpm run build:admin
WPUF_ENTRY=form-builder pnpm run dev:admin          # watch
pnpm run build:form-builder                          # aliases exist for some apps
node tools/admin-css/build.mjs forms                 # one admin sheet: settings | subscriptions | forms | forms-list | onboarding | pages | ai-form-builder

# Frontend: one app (runtime | forms | account-page | login | plans | checkout)
WPUF_ENTRY=forms pnpm run build:frontend
WPUF_ENTRY=account-page pnpm run dev:frontend

pnpm build                                           # everything + sanitize + bin/verify-build.mjs (CI / release)
```

**Build only your entry while working.** A full `pnpm build` regenerates every shared output (Tailwind sheets, admin CSS for every screen, the shared `admin-runtime` / `admin-ui` and frontend `runtime` bundles, blocks, User Directory), which hides what your change did and touches screens you did not test. Run the full build once before handoff to prove the tree builds and `bin/verify-build.mjs` passes. Generated assets under `assets/` are gitignored (CI builds them); only tracked legacy files and new images are committed (`git add -f` for new files under `/assets/*`).

`WPUF_ENTRY` with an unknown name throws, listing the valid names (both webpack configs).

## Admin React apps

### Shared layer and externals

`src/admin/shared/` is published at runtime on `window.wpuf` by two shared bundles and consumed through import names mapped in `webpack.wpuf-externals.js`:

| Import | Global | Handle |
|---|---|---|
| `@wpuf/components` (`shared/ui/*`, `WpufProviders`) | `wpuf.components` | `wpuf-admin-ui` |
| `@wedevs/plugin-ui` | `wpuf.ui` | `wpuf-admin-ui` |
| `@wpuf/api` (`shared/api`) | `wpuf.api` | `wpuf-admin-runtime` |
| `@wpuf/filters`, `@wpuf/hooks`, `@wpuf/utilities` | `wpuf.filters`, `.hooks`, `.utilities` | `wpuf-admin-runtime` |

Screens import these names, never relative paths into `shared/`, and **never import `@wedevs/plugin-ui` directly in a screen**: use the WPUF wrapper in `shared/ui/` (add one if missing, sized and styled to the live component). The dependency extraction plugin adds the handles to each `.asset.php`.

### Registering a screen

A screen bundle registers with the single admin app (`src/admin/app/client.js`) and renders inside `WpufProviders` (`src/admin/apps/tools/index.jsx`):

```jsx
import { createRoot } from '@wordpress/element';
import { WpufProviders } from '@wpuf/components';

import { registerScreen } from '../../app/client';
import ToolsPage from './ToolsPage';

registerScreen( 'tools', [ 'wpuf-tools-root' ], ( element, context ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <ToolsPage context={ context } />
        </WpufProviders>
    );

    return () => root.unmount();
} );
```

The PHP side is the screen class (`includes/Admin/Screens/<Screen>.php`: route, container id, boot globals) and the handle in `Admin\Bundles`; see the `wpuf-backend-dev` skill. Route helpers: `getRouteQuery()`, `setRouteQuery()`, `addRouteGuard()`, `openRoute()` from `app/client.js`.

### REST calls

Use `request()` / `restPath()` from `@wpuf/api` (`src/admin/shared/api/request.js`): 30 s timeout, GET retried on 5xx, body parsed by `parseJsonBody()` so stray PHP output before the JSON does not break it, every failure normalized to `{ code, message, status, data }`. Keep the calls of an app in its `api.js` (`src/admin/apps/tools/api.js`):

```js
import { request, restPath } from '@wpuf/api';

const path = ( route, query ) => restPath( 'wpuf/v1', 'admin/tools/' + route, query );

export const getForms = ( type ) => request( path( 'forms', { type } ) );
export const runTool = ( route, data ) => request( path( route ), { method: 'POST', data: data || {} } );
```

Do not add a global `apiFetch.use()` middleware (it would change core and other plugins on the page).

### State: `@wordpress/data` stores

Server data and cross-component state live in a store per app (`createReduxStore` + `register`, name `wpuf/<app>`), e.g. `src/admin/apps/tools/store/index.js`, `forms-list/store`, `transactions/store`, `form-builder/store`, `settings/stores/settings`, `subscriptions/stores/*`:

```js
import { createReduxStore, register } from '@wordpress/data';

export const STORE_NAME = 'wpuf/tools';

export const actions = {
    loadForms( formType ) {
        return async ( { dispatch } ) => {
            try {
                dispatch( { type: 'RECEIVE_FORMS', formType, items: await getForms( formType ) } );
                return null;
            } catch ( error ) {
                dispatch( { type: 'RECEIVE_FORMS', formType, items: [], error } );
                return error;
            }
        };
    },
};
```

Store modules (`actions.js` / `selectors.js`) are consumed whole: a name anyone calls through `dispatch( STORE )` / `select( STORE )` stays exported (`shared/store-contract.test.js`). Form fields that only one panel edits may stay local state.

### Stored values

-   Never coerce stored values with `||` (B20): `''`, `0` and `false` are real values. Use `??` or explicit checks.
-   Write develop's shapes: toggles `'on'` / `'off'`, keys absent when unchecked where develop left them absent, same arrays and nesting. An untouched save must send what was loaded.

### Look and components

-   Every button is the shared `Button` (`shared/ui/Button.jsx`): variants `primary`, `secondary`, `danger-outline`, `link`, `destructive`, `icon`; sizes `xs`, `sm`, `md`, `lg`; `href` for link buttons. No local button classes, no outer margin; groups use the container gap.
-   Page layout (FlyHR look): `PageShell` with `PageHeader` (pass `helpUrl` / `helpLabel`), content in white cards, `PageFooter` last. The gray page and hidden `#wpfooter` come from `tools/admin-css/src/pui.css`; do not set page backgrounds per screen. Lists use `DataTable`, `Tabs`, `Pagination`, `SearchInput`, `SortHeader`, `EmptyState`, `Skeleton`.
-   Dialogs and toasts: `ConfirmDialog` / `useConfirm`, `Modal`, `dialogs`, `notify` from `@wpuf/components` (plugin-ui design). No SweetAlert2 in the admin.
-   Rich text stays TinyMCE through `WpEditor` (`wp.editor`); `RichEditor` for the lazy editor chunk.
-   Classic PHP hook output (fields other plugins print on coupon / pack screens) renders with `ClassicFields` / `classicFieldValues` (server side `Platform\Classic\Hook_Fields`).
-   Primary `#059669`, hover `#10b981`. No blue focus rings (WordPress `input:focus` / `a:focus` are reset in `pui.css`); radios and checkboxes keep plugin-ui's default checked look.

### Admin CSS scoping

-   Utilities are plain Tailwind 4 classes (`flex`, `text-sm`, `shadow-xs`), never `wpuf-` or `wpuf:`; each sheet scopes them to the screen body class with zero specificity (`:where(.wpuf-admin-screen) .flex`). The body class comes from `Screen::body_class()` / `Bundles::forms_body_class()`.
-   **plugin-ui island (`host` mode, design D25)**: a screen that still renders its own layout markup uses `<WpufProviders host>`; plugin-ui CSS then reaches only `[data-slot]` parts and roots marked `data-wpuf-ui=""`. Every new wrapper whose root is not a plugin-ui component sets `data-wpuf-ui=""`. Keep `host` while the screen renders its own headings, tables or grids.
-   A class a shared wrapper also uses compiles with `!important` in the plugin-ui part and beats a screen-only class on the same element inside islands and portals (e.g. `m-0` kills `mb-16`): do not mix them.
-   A class that never had CSS in Tailwind 3 must not start working: block it with `@source not inline(...)` in the screen's `src/<screen>.css`. Base and component rules are frozen Tailwind 3 output in `tools/admin-css/src/base-v3/` (regenerate with `gen-v3-base.cjs`).
-   Pro's utilities sheet prints before free's `forms-screen.css` (free wins shared names).

### Form template picker (forms lists)

-   "Add New" on the post and registration forms lists opens `src/admin/apps/forms-list/components/TemplatePicker.jsx`; its data comes from `WeDevs\Wpuf\Admin\Forms\Template_Picker::data()` (global `wpuf_form_templates`).
-   A template's `image` is a screenshot of the form it creates (`assets/images/templates/*.webp`, 800px wide). New templates need one: create the form, capture the frontend form element, convert to webp, `git add -f` it (`/assets/*` is gitignored).

## JS hooks and slots (admin)

-   All hooks use the global `wp.hooks` (`import { applyFilters, doAction } from '@wordpress/hooks'`); no private `createHooks()`.
-   Existing names are frozen: `wpuf.formBuilder.*`, `wpuf.formsList.*`, `wpuf.subscription.*`, `wpuf.userPosts.*`, `wpuf.settings.*`. New names `wpuf.<area>.<thing>` in camelCase.
-   Every name is a constant in `src/admin/shared/filters.js` (`HOOKS`, published as `window.wpuf.filters`); `shared/filters.test.js` fails when code uses a name that is not listed. Add the constant first.
-   Free must apply every filter Pro listens to (B21). Pro registers listeners at module scope in a script that depends on free's handle, not in `domReady`; free reads filters at render time.
-   Builder slots: `wp.plugins.registerPlugin( 'my-plugin', { scope: 'wpuf-form-builder', render } )` with `<Fill name="...">`; names in `docs/hooks/js.md` and `window.wpuf.builderSlots`. Other screens: `docs/slots/README.md`.
-   Retired Vue globals keep working with a one-time `@wordpress/deprecated` warning (`window.wpuf_mixins`, `wpuf_form_builder.event_hub` forwarding to `wpuf.formBuilder.event.<name>`).

## Frontend React apps

### Mounting

The server prints `<div class="wpuf-frontend" data-wpuf-mount="<app>">` with a `script.wpuf-boot` JSON child (`Frontend\Renderer::markup()`). The app mounts every root with `mountAll()` from the kit (`src/frontend/forms/index.jsx`):

```jsx
import { createRoot } from '@wordpress/element';
import { mountAll } from '@wpuf/frontend-kit';
import PostForm from './PostForm';
import './forms.css';

mountAll( 'forms', ( root, schema ) => {
    createRoot( root ).render( <PostForm schema={ schema } root={ root } /> );
} );
```

`mountAll()` handles several roots per page and roots added later (`wpuf:frontend:html` event from page builders and the account shell).

### The kit (`@wpuf/frontend-kit`)

`src/frontend/kit/` is built once as the `runtime` entry and published on `window.wpuf.frontend` (handle `wpuf-frontend-runtime`); apps import it by name and it is externalized (`FRONTEND_MAP` in `webpack.wpuf-externals.js`). It holds:

-   `api.js`: REST client for `/wpuf/v1` (apiFetch for JSON, XHR for upload progress); errors carry `code`, `status`, `data`
-   `components/`: `Button`, `Notice`, `Pill`, `Skeleton*`, `Spinner`, `Field`, `Select`, `ServerHtml`, layout, plans
-   `fields/`: field components by template with a registry (`registerField( template, Component )`, `fieldComponent()`), uploads, rich text (TipTap editor chunk), taxonomy, captcha
-   `form/`: `FormRenderer`, `useForm`, `store.js` (`@wordpress/data` form store), `validation.js`, `conditions.js`, `payload.js`

Add shared frontend pieces to the kit, not to one app. App state that spans sections uses a store (`src/frontend/account/store.js`, `wpuf/account`).

### Frontend JS hooks

Fired with `wp.hooks`: `wpuf.frontend.form.schema`, `.fields`, `.validateField`, `.beforeSubmit`, `.payload`, actions `.mounted`, `.submitted`, `.draftSaved`; `wpuf.frontend.account.boot`, `.sections`, `.section.component`, action `wpuf.frontend.account.section`. These are public contracts: never rename or change their arguments.

### Frontend CSS rules (`tools/frontend-css/check.mjs` fails the build otherwise)

-   Every rule's first compound selector is `.wpuf-frontend` (or `:where(.wpuf-frontend)`).
-   No `:root`, `html`, `body` or `*` rules, no `@font-face`; tokens live on `.wpuf-frontend` (`--wpuf-fe-*` in `kit/kit.css`), the theme's font is inherited, native controls, no preflight.
-   No Tailwind in frontend sheets (`postcss.config.frontend.js` runs autoprefixer only).
-   Classes from the classic markup stay on the React output where theme CSS targets them (`Renderer::markup()` `$classes`).

### What stays classic

The classic PHP + jQuery render stays for theme template overrides, the post form block, Elementor widgets, wp-admin and an unbuilt bundle (`Renderer::PARTS`, `Renderer::with_source()`). Do not port those paths to React, and keep `assets/js/frontend-form.js` and friends working: they are served as written (no build step, no transpiling), so edit them in the file's existing style (jQuery inside an IIFE, mostly `var`) and keep browser-safe syntax.

## Translations (JS)

-   `import { __, _n, sprintf } from '@wordpress/i18n';`, text domain `'wp-user-frontend'` (Pro `'wpuf-pro'`), never `'wpuf'`.
-   Wrap every user-facing string, also `title`, `alt`, `aria-label`, `placeholder`.
-   `/* translators: */` comment before every `sprintf()` with placeholders; never concatenate translated fragments.
-   Bundles are minified but not named `.min.js` so `wp i18n make-pot` (`pnpm run i18n` → `bin/make-pot.mjs`) extracts them and JSON translations match the enqueued path. Keep that naming for new entries.

## Tests

```bash
pnpm run test:shared          # src/admin/shared (filters contract, store contract, api, ui helpers)
pnpm run test:stores          # admin app stores (src/admin/apps/stores.jest.config.js)
pnpm run test:form-builder    # form builder
pnpm run test:ai-form-builder # AI form builder
pnpm run test:frontend        # src/frontend (kit, form store, payload, account store)
```

Add a jest test next to a new store, reducer, payload builder or helper. Browser checks: Playwright in `tests/e2e/` (see `tests/e2e/CLAUDE.md`); `releaseSmokeTest` fails when a React screen does not mount, logs a JS error or an asset 404s.

## Self-check before handoff

1. [ ] Screen imports `@wpuf/components` / `@wpuf/api`, not `@wedevs/plugin-ui` or relative paths into `shared/`
2. [ ] Every button is the shared `Button`; no local button styles or margins
3. [ ] Server data in a `wpuf/<app>` store; exported store names unchanged
4. [ ] No `||` coercion of stored values; saves send develop's shapes; untouched save sends what was loaded
5. [ ] New hook / slot names added to `shared/filters.js` first; existing names and args unchanged; free applies every filter Pro listens to
6. [ ] Admin CSS: plain Tailwind 4 classes, island rules respected (`data-wpuf-ui`), no blue focus
7. [ ] Frontend CSS scoped under `.wpuf-frontend`, no global rules, no Tailwind; `check.mjs` passes
8. [ ] No raw remote / AI / user HTML into `dangerouslySetInnerHTML` or `innerHTML`; the kit's `ServerHtml` (which also runs inline scripts) is only for markup from our own REST routes or boot data
9. [ ] All strings translated with the right text domain and translator comments
10. [ ] Built only the touched entries while working; related jest suites green; full `pnpm build` (with `verify-build`) passes once before handoff
11. [ ] Touched states checked in a real browser with mouse and keyboard

## Key reference files

-   `package.json` (scripts), `webpack.admin.config.js`, `webpack.frontend.config.js`, `webpack.wpuf-externals.js`, `webpack.config.js` (blocks)
-   `tools/admin-css/build.mjs`, `tools/admin-css/src/pui.css`, `tools/frontend-css/check.mjs`, `bin/verify-build.mjs`
-   `src/admin/app/client.js`, `src/admin/shared/ui/index.js`, `src/admin/shared/api/request.js`, `src/admin/shared/filters.js`, `src/admin/shared/providers/WpufProviders.jsx`
-   `src/frontend/kit/index.js`, `src/frontend/kit/runtime.js`, `src/frontend/kit/kit.css`, `src/frontend/kit/form/*`
-   `includes/Admin/Bundles.php`, `includes/Frontend/Bundles.php`, `includes/Frontend/Renderer.php`
-   `docs/hooks/js.md`, `docs/slots/README.md`, `docs/settings-screen.md`, `docs/build-and-test.md`
