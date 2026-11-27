---
name: wpuf-code-review
description: Review WPUF code changes and pull requests (free and Pro, v5.0.0 platform) for coding standards, security, and architectural compliance: REST permissions via Caps, rate limits on public routes, store-only data access, hooks that must keep firing, deprecation shims, React admin / frontend conventions, PHPCS, PHPUnit and jest. Use when reviewing PRs, performing code audits, or checking code quality.
---

# WPUF Code Review

Review changes against WP User Frontend's standards as built on `feature/react-admin-revamp` (release 5.0.0). The detailed patterns live in the `wpuf-backend-dev` and `wpuf-frontend-dev` skills; this skill is the checklist and the output format.

## Pre-review steps

1.  **Scope.** Diff-based review: `git diff develop...HEAD --stat`, then `git diff` (or `--cached`, `HEAD~1`) for the changed lines; review the changed code, not whole legacy files. If the user names files, review them in full.
2.  **Know the layer.** Map each changed file to its layer with `docs/architecture.md` ("where is X"): platform (`includes/Platform/**`), screens (`includes/Admin/Screens/**`), builder (`includes/Builder/**`), frontend render (`includes/Frontend/**`), legacy (`includes/Admin/*`, `includes/Fields/*`, `Lib/*`, templates), admin React (`src/admin/**`), frontend React (`src/frontend/**`), classic jQuery (`assets/js/*.js`), Pro (`wpuf-pro/includes/Platform/**`).
3.  **Run the tools** (include their output, with human context):

    ```bash
    vendor/bin/phpcs -q -n $(git diff --name-only --diff-filter=d develop...HEAD -- '*.php')
    WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter <RelatedTest>
    WPUF_TESTS_WITH_PRO=1 WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter <RelatedTest>
    pnpm run test:shared && pnpm run test:stores        # plus test:form-builder / test:ai-form-builder / test:frontend as touched
    WPUF_ENTRY=<entry> pnpm run build:admin             # or build:frontend for frontend entries
    ```

    CI's PHPCS (`.github/workflows/phpcs.yml`) lints **whole changed files**, so legacy debt in a touched file fails the PR: flag it.
4.  **Diff awareness.** Separate newly introduced issues from pre-existing ones and mark the latter `(pre-existing)`.

## Critical on this branch (flag as CRITICAL)

-   **A hook stopped firing.** A WPUF PHP or JS hook that fired on develop no longer fires on the changed path, or fires at a different point, with different args or in a different order. Check `docs/hooks/php-index.md`, `php tests/contracts/static-hooks.php <free> <pro>` and the runtime diff (`php tests/contracts/diff.php <develop-dir> <branch-dir>`, 0 hooks lost).
-   **A hook was retired without a shim** (`apply_filters_deprecated` / `do_action_deprecated` with `WPUF_SINCE` and a replacement, entry in `docs/hooks/migration-vue-to-react.md`).
-   **Public API removed or renamed without a deprecation shim**: a class Pro (current or released) or outside code can name needs a `class_alias` stub; a global `wpuf_*` function or public method needs `@deprecated WPUF_SINCE` + `_deprecated_function( __FUNCTION__ | __METHOD__, 'WPUF_SINCE', $replacement )`. REST routes, AJAX actions, shortcodes and template paths are contracts too.
-   **Stored shapes changed**: an untouched save alters data, `||` coercion of stored values (`''`, `0`, `false` are real), `'1'` / booleans where develop stores `'on'` / `'off'`, `[]` where develop leaves a key absent, new option or meta keys.
-   **REST permission wrong**: a literal capability string or `current_user_can( wpuf_admin_role() )` in a new controller instead of `$this->permission( Caps::MANAGE_* )` / `Caps::can()`; a destructive or site-wide action (payments, import, settings save, Tools danger zone, onboarding) without `Caps::MANAGE_SITE`; `__return_true` or logged-in-only gating on a route that writes or reads non-public data; a client-supplied id used without a post-type check and per-object cap (`current_user_can( 'edit_post', $id )`, see `FormsController::form_permission()`).
-   **Public route without abuse protection**: a visitor-reachable route that sends mail, calls a paid API, creates content or checks credentials without the `Rate_Limiter` contract (`$limiter->allow()` or `account_attempt_allowed()`, 429 on refusal).
-   **Storage bypassing the stores**: new direct `get_post_meta` / `update_option` / `$wpdb` calls in controllers, screens or services for data a `Stores::*()` store owns.
-   **Submission run outside `Submission_Context`**: a REST path calling a classic submit handler directly (third-party submit hooks and field classes expect the admin-ajax context).
-   **Edits inside `wpuf-pro/modules/*` or `modules/user-directory`** to fix a core regression.
-   **A filter Pro listens to that free never applies** (JS or PHP).

## Backend PHP checks

**Architecture**

-   Services built only in providers (`share_tagged()` / `bind_tagged()` in `includes/Platform/Providers/*`), dependencies injected in constructors; no ad hoc `new` of a shared service where `wpuf()->platform()->get()` or injection applies; no new keys in the legacy `wpuf()->container` array.
-   Hooks added in `register_hooks()` of a `Hookable`, never in a platform constructor.
-   REST controllers extend `Platform\REST\RestController`, live in `includes/Platform/REST/Controllers/` (Pro: `wpuf-pro/includes/Platform/<Feature>/`), are shared in a provider (no `API.php` edits, no `includes/Api/`), use namespace `wpuf/v1`, admin routes under `admin/*`, typed `args` with `sanitize_callback`, `collection_args()` for lists, `paginate()` for list responses, `error()` (`wpuf_{resource}_{reason}`) with the right status. Frozen routes (`wpuf_form`, `wpuf_subscription*`, `settings`) keep paths, args and response shapes.
-   Pro resolves free services by contract (`Rate_Limiter`, `App_Renderer`, `Form_Schema_Provider`), reads free data through `Stores::*()`, registers on `wpuf_platform_loaded`, and respects `Platform_Guard`; free never assumes Pro (`wpuf_is_pro_active()` / `class_exists( 'WP_User_Frontend_Pro' )`).
-   Admin screens: a `Screen` subclass registered in `Screens\Registry`, its load step fires the hooks the old page fired, old URLs redirect to the app route.
-   Frontend: React mounts only through `Frontend\Renderer` (classic render kept for theme overrides, block, Elementor, admin, unbuilt bundles); boot JSON printed with the `JSON_HEX_*` flags; captured hook output keeps classic order and args (`Form_Schema`); new client-side validation has its server rule in `Submission_Validator`.

**Naming and conventions**

-   snake_case methods, variables and hooks; `WeDevs\Wpuf\{Domain}\{Class}` with a matching path.
-   Text domain `wp-user-frontend` (free) / `wpuf-pro` (Pro), never `wpuf`.
-   Hook names start with `wpuf_` (`wpuf_{context}_{action}`; new REST / store hooks `wpuf_rest_prepare_{resource}`, `wpuf_before|after_{store}_save`), docblock with `@since WPUF_SINCE` and every `@param`; the hook index regenerated (`php tests/contracts/static-hooks.php --md . > docs/hooks/php-index.md`).
-   `@since WPUF_SINCE` (free) / `WPUF_PRO_SINCE` (Pro) on every new public / protected method, class and hook; never a hardcoded version.

**Security** (also self-check against the WordPress plugin secure-coding rules)

-   Strict comparisons (`===`, `!==`); `in_array()` / `array_search()` with `true`.
-   Superglobals: `wp_unslash()` before sanitizing; REST params validated by route `args` or sanitized in the callback.
-   Output escaped late with the context-correct function (`esc_html`, `esc_attr`, `esc_url`, `esc_js`, `wp_kses_post` or a narrow allowlist such as `HookBridge::allowed_html()`).
-   SQL through `$wpdb->prepare()`, `ORDER BY` / `LIMIT` from allowlists, inside a store.
-   AJAX and classic form posts: nonce (`check_ajax_referer()` / `wp_verify_nonce()`) **and** capability.
-   No `eval()`, `extract()`, `unserialize()` / `maybe_unserialize()` of attacker-influenced data, unvalidated file paths, or non-public data (drafts, `user_login`, secrets, other users' data) returned to a lower privilege. Settings secrets stay masked (`SettingsStore::mask_secrets()`).

**Performance**

-   No queries in loops (N+1); batch through store `query()` / `count()`.
-   No unbounded queries (`per_page` capped at 100 by `collection_args()`).
-   Assets enqueued only on their screen or when a part mounts.

## Frontend checks

**Admin React (`src/admin/**`)**

-   Screens import `@wpuf/components`, `@wpuf/api`, `@wpuf/filters`; `@wedevs/plugin-ui` imported directly in a screen is an ERROR (wrap it in `shared/ui/`).
-   Every button is the shared `Button` (variants `primary`, `secondary`, `danger-outline`, `link`, `destructive`, `icon`); local button classes or margins are an ERROR.
-   REST through `request()` / `restPath()`; no global `apiFetch.use()`.
-   Server data in a `wpuf/<app>` `@wordpress/data` store; a store action or selector name removed while something dispatches it breaks `shared/store-contract.test.js`.
-   New hook or slot names added to `src/admin/shared/filters.js` (guarded by `filters.test.js`); existing `wpuf.formBuilder.*`, `wpuf.formsList.*`, `wpuf.subscription.*`, `wpuf.settings.*` names and args unchanged; listeners registered at module scope.
-   CSS: plain Tailwind 4 classes (no `wpuf-` / `wpuf:` prefix) scoped by `tools/admin-css`; island rules (`<WpufProviders host>`, `data-wpuf-ui=""` on wrapper roots); no blue focus rings; no screen-only class fighting a wrapper's `!important` class.
-   Dialogs through `ConfirmDialog` / `Modal` / `dialogs`; no SweetAlert2 in the admin.

**Frontend React (`src/frontend/**`)**

-   Mounts through `mountAll()`; shared parts added to `@wpuf/frontend-kit`, not copied per app.
-   CSS scoped under `.wpuf-frontend`, no `:root` / `html` / `body` / `*` / `@font-face`, no Tailwind (`tools/frontend-css/check.mjs` must pass).
-   `wpuf.frontend.form.*` / `wpuf.frontend.account.*` hook names and args unchanged.
-   `ServerHtml` / `dangerouslySetInnerHTML` only for markup from our own REST routes or boot data; never raw user, remote or AI HTML.

**Classic jQuery (`assets/js/*.js`)**

-   Served without a build: no syntax the file did not already use, existing globals and events kept.

**All JS**

-   `@wordpress/i18n` with `'wp-user-frontend'` (Pro `'wpuf-pro'`), translator comments for `sprintf()`, every user-facing string translated (also `aria-label`, `title`, `alt`, `placeholder`).
-   Accessibility: labels tied to inputs, keyboard reachable controls, ARIA live regions for errors.

**Build**

-   pnpm only (no `package-lock.json`, no `npm install` in the root; `modules/user-directory` keeps npm); `pnpm-lock.yaml` updated with any dependency change.
-   Generated assets not committed (`assets/` is gitignored; new tracked images added with `git add -f` on purpose).
-   New webpack entries keep the `.js` (not `.min.js`) naming so translations are extracted.

## Evidence to ask for

-   PHPCS clean (`vendor/bin/phpcs -q -n`) on every touched PHP file.
-   Related PHPUnit tests green, free alone and with `WPUF_TESTS_WITH_PRO=1` when Pro is involved; a new REST route has a test that visitors and subscribers are refused (`PlatformRestTest` style).
-   Related jest suites green; the touched entry builds; full `pnpm build` passes (`bin/verify-build.mjs`).
-   Hook contract: static hook diff and runtime diff with 0 hooks lost for touched screens.
-   Parity project for touched save paths (`cd tests/e2e && npx playwright test --project=parity`, one run at a time) and existing e2e specs for the feature.
-   Free-only check (Pro deactivated) for anything Pro touches.
-   Screenshots (before / after, mouse and keyboard states) for visual changes; changelog entry; test steps.

## Output format

**Every issue MUST include a file reference** (`file/path.php:LINE`). Name every file; never "all templates".

```text
[SEVERITY]: [Specific problem]
Location: includes/Platform/REST/Controllers/FooController.php:42
Code: `the problematic code`
Fix: [Brief explanation or corrected code]
```

Pre-existing issues:

```text
[SEVERITY]: [Specific problem] (pre-existing)
Location: includes/Admin/Forms/Admin_Form.php:158
Note: Existed before this PR; CI lints the whole file, so it still blocks the PHPCS check.
```

Severity levels:

-   **CRITICAL**: security vulnerability, data loss or stored-shape change, a hook that stopped firing, public API removed without a shim, broken functionality
-   **ERROR**: standards violation or a missing required pattern (Caps, stores, providers, shared `Button`, translations, docblocks)
-   **WARNING**: suboptimal approach, performance issue, missing best practice or test
-   **SUGGESTION**: minor improvement, style, optimization

End with a verdict (approve / request changes / block) and the must-fix list in priority order.

## Reviewer principles

> **Correct**: does the change do what it is supposed to, completely?
> **Secure**: could a malicious party exploit it? Everything permission-checked, sanitized and escaped?
> **Compatible**: do develop's hooks, routes, stored data, public API and URLs still work for Pro and third parties?
> **Readable**: will someone understand it months from now?
> **Elegant**: does it fit the platform's layers (providers, stores, Caps, shared components) instead of working around them?
