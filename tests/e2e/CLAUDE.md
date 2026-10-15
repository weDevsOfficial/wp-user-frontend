# Playwright E2E (`tests/e2e/`)

Read before adding or modifying end-to-end tests.

## Layout

- `playwright.config.ts` — the **single** config for the whole suite. Phases are
  `projects` selected from the CLI: `--project=setup`, `--project=e2e` (sharded via
  native `--shard=i/n`), `--project=api` (REST layer, no browser).
- `tests/` — test specs
- `pages/` — Page Object Model classes
- `utils/` — helpers (summary generators, auth, etc.)
- `features-map/` — feature-to-test mapping references
- `uploadeditems/` — fixture files for upload-field tests
- `Field_Options_Coverage_Analysis.md` — coverage notes for field option tests

## Running

```bash
cd tests/e2e
pnpm install --frozen-lockfile
npx playwright install chromium

pnpm run test:setup       # run setup suite first (alphaSetupTest)
pnpm run test:parallel    # run the 4 native shards sequentially
pnpm run test:sharded     # setup + shards in sequence
```

CI variants append `:ci` (`test:setup:ci`, `test:parallel:ci`, `test:sharded:ci`) and drop `--headed`.

## One config, three projects

Everything runs from `playwright.config.ts`. The old per-phase configs
(`playwright.setup/parallel/api.config.ts`) and the earlier `parallel-one/two/three`
configs are gone — phases are now **projects** selected with `--project`:

- `--project=setup` → `alphaSetupTest.spec.ts` (run first, once)
- `--project=e2e` → the stateful UI suite, split via native `--shard=i/n`
- `--project=api` → REST layer (`tests/api/`, no browser)
- `--project=parity` → develop vs branch comparison (`parity/`, needs `PARITY_*` in `.env`, see `utils/paritySites.ts`); local gate, not in CI shards

`workers: 1` + `fullyParallel: false`, so no two stateful specs hit the shared site
at once. `pnpm run test:parallel` invokes `--project=e2e` three times
(`--shard=1/3`, `2/3`, `3/3`) **sequentially** against the single wp-env.

- We deliberately do **not** wire `dependencies: ['setup']` — under `--shard` a setup
  dependency reruns the heavy, destructive site reset once per shard. Setup stays a
  separate script step, preserving "reset once, then shard".
- Playwright keeps whole spec files together per shard (never splits a file), so each
  spec's shared-page / ordered / fail-fast pattern stays intact.
- `SHARD_INDEX` (with `--shard`) and `E2E_PHASE` (`setup`|`api`) only pick per-phase
  report/output paths (`parallel-results/shard-<i>-results.json`, `setup/`, `api/`) and
  per-shard `outputDir` so sequential invocations don't clobber each other. They do not
  decide which tests run. `utils/sharded-summary.js` auto-discovers all
  `parallel-results/shard-*-results.json`, so shard count is free to change.
- Real parallel speedup (shards on separate runners) would need a CI matrix with a
  wp-env per job + `playwright merge-reports`; today shards are sequential.

## Conventions

- ES modules (`"type": "module"` in package.json) + TypeScript.
- Use the Page Object Model in `pages/` — don't put selectors directly in specs.
- Fixtures and auth state land in `setup/` (gitignored). Don't commit generated state.
- Screenshots and artifacts go to `test-results/` and `playwright-report/` — both gitignored.

## Session persistence (login reuse)

`BasicLoginPage.basicLogin()` is **session-aware** (`pages/basicLogin.ts` + `utils/authSession.ts`):

- On the first login for a role it does the normal UI login, then caches the
  `storageState` to `.auth/<role-slug>.json` (keyed by username/email).
- On later logins for that role — even in a fresh context in another spec — it
  re-injects the saved cookies instead of retyping credentials, then verifies it
  actually landed logged-in. If the saved session is stale (expired / logged out
  server-side) it self-heals: clears it and falls back to a UI login + re-save.
- No spec changes needed — every existing `basicLogin()` / `basicLoginAndPluginVisit()`
  call benefits automatically. First run reproduces the original behavior exactly.
- `.auth/` is gitignored via the suite-local `tests/e2e/.gitignore` — **never commit it**
  (it holds live auth cookies). Note: the plugin-root `.gitignore`'s bare `.auth/` rule
  does **not** actually match this nested dir, which is why the local `.gitignore` exists.

## Before Adding a New Test

1. Check `features-map/` to see if the feature already has coverage.
2. Reuse existing Page Object methods before adding new ones.
3. If the test needs Pro features, gate it so Lite-only runs don't fail.

## Debugging

- Run a single spec: `npx playwright test tests/<file>.spec.ts --headed`
- Use `--debug` for inspector; `--trace on` for traces.
- Check `test-results/` for failure screenshots and videos.

## Environment-dependent tests (green-run prerequisites)

Two areas need external services configured or they fail regardless of code:

- **Google Maps** — the WPUF Google Map field only renders its "Search address" box after
  the Maps JS API loads *in the browser*. The key must have `http://localhost:8889` (and the
  CI base URL) in its **referer allowlist**, with Maps JS + Places APIs enabled. Where the map
  is optional (post form) the fill is best-effort (`base.ts::fillStringIfAvailable`). Where it
  is **required** (Dokan vendor store), the Register button stays disabled without it, so
  **RF0009 self-skips** (and RF0010/RF0011 with it) when the map can't render.
- **MailPoet + SMTP** — `EM0004` registers on a form with MailPoet **subscription** enabled;
  the subscribe-during-registration call needs a working MailPoet list + SMTP (double-opt-in),
  or the registration AJAX stalls and `wpuf-success` never appears. Base registration itself
  works without it — only the subscription path needs the mail stack.

Both are QA-environment config, not WPUF bugs. Failures here mean "configure the service,"
not "fix the code."

**AI form builder** — no real provider in tests. `wp/wpuf-ai-mock.php` (mu-plugin, mapped by
`.wp-env.json`; on a Herd site copy it into `wp-content/mu-plugins/`) answers the OpenAI /
Anthropic / Google calls ONLY when the stored key is `sk-wpuf-e2e-mock`
(`AiFormBuilderPage.configureMock()` sets it and `restore()` puts the old `wpuf_ai` back).
Prompts pick the answer: "mock-error" (provider 500), "not-a-form" (refusal), "pro-fields"
(phone + date), chat "website" / "date" / "remove the message"; integrations add
" (<id>)" to the title. It also answers Settings "Test Connection" (HTTP 200), the Google
model list (`gemini-mock-flash`, `configureMock(key, 'google')`) and the builder's
"AI Generate Options" AJAX (Mock Red / Green / Blue). Pro is detected by class
(`AiFormBuilderPage.proActive()`), so the specs work on a dev checkout (`wpuf-pro`) and on the
release zip (`wp-user-frontend-pro`).

**Release smoke** — `tests/releaseSmokeTest.spec.ts` (RS0001-RS0006) opens every React admin
screen and fails on a JS error, a 4xx/5xx plugin asset, a Vue runtime or Vue app, or an asset of
the removed Vue admin (`VUE_ASSETS` in the spec). Run it on a site with the release
packages installed (steps in `docs/build-and-test.md`, "Release build check"). Run against a local site with
`QA_BASE_URL=http://site.test WPUF_E2E_WP_PATH=/path/to/site`. AI0015 / AI0016 need
WooCommerce / Dokan active, AI0013 needs Pro off.

## React screens: locator patterns

The builders, forms lists, subscriptions and settings screens are React on
plugin-ui (base-ui). Native inputs became ARIA widgets; locate them this way
(all in `pages/selectors.ts`, never inline in specs):

| Control | Locator |
|---|---|
| Select (plugin-ui) | `//*[@role="combobox"][@id="X"]` or `label/following::*[@role="combobox"][1]`; pick with `base.ts::selectOptionWithValue/Label` (combobox-aware: opens it, clicks `[@role="option"][@data-value="v"]`) |
| Switch / toggle | `//label[normalize-space()="Label"]/following::*[@role="switch"][1]` (ids are generated, `base-ui-:r12:`), or `input#id/preceding-sibling::*[@role="switch"][1]` where a hidden input keeps the id |
| Radio | `//*[@role="radio"][following-sibling::input[1][@value="v"]]` or `role=radio[name="Label"s]` (exact) |
| Checkbox | `[@role="checkbox"]`; `.check()` works on it |
| Multi-select | `[@role="combobox"]`, options `[@role="option"][normalize-space()="Name"]` (no data-value) |
| Menus (card "⋯", Save/Publish) | trigger `button[@aria-label="Actions"]` or the button; items `[@role="menuitem"]`. Menus open on **click** (develop opened some on hover) |
| Tabs with counts | `role=button[name="All Subscriptions 1"s]` (count is its own element; no count when 0) |
| Confirm dialogs | scope to `//*[@role="alertdialog" or @role="dialog"]//button[...]` (the "Trash" tab is also a button) |
| Builder canvas labels | `//label[@for="x" or @for="wpuf-x"]` |
| Forms list rows | `//tr[.//td//a[normalize-space()="Name"]]` |

Traps:
- **Union XPath picks the first match in document order**, not the first branch:
  a fallback like `(//label[text()="Country List"]/following::input)[2]` can hit
  the canvas preview before the options panel. Anchor to the panel container
  (`panel-field-opt-*`) or drop the fallback.
- **Strict mode**: text such as "Enable time input" exists on a wrapper div,
  the label and a span. Target the `label` only.
- **React unmounts switched-off sections** (develop only hid them), so
  index-based locators like `(//span[@data-clipboard-text="x"])[2]` break.
  Anchor to the section heading instead.
- Single-use palette fields (Username, First Name...) answer a second add with
  an "Oops... already have this field" alert; close it (`alreadyAddedOk`).
- Save toast text is "Saved form data" (as develop).

## The admin app (one page, hash routes)

The React screens run as one admin app on `admin.php?page=wp-user-frontend`
(`#/post-forms`, `#/post-forms/:id/edit`, `#/post-forms/new`, `#/post-forms/ai`,
`#/registration-forms…` with Pro, `#/subscriptions`, `#/settings?tab=&sub=`).
Old URLs still work: the old page's load step runs (row actions, template
actions, add-new), then it redirects to the route. There is no switch back to
per-page screens (they were removed). App tests: `adminAppTest.spec.ts`
(APP0001-APP0014); release smoke RS0007 (no reload across menu rows) and RS0008
(reload on each route).

- **Builder URLs have two shapes**: the old builder page (`action=edit&id=N`,
  redirects) and the route (`#/post-forms/N/edit`). Wait with `BUILDER_URL` and read the id
  with `builderFormId()` (`utils/builderUrl.ts`); `Base::getFormId()` reads both.
- **Menu rows are route changes**, not page loads: after a click, wait for the
  screen, not for a navigation. Reloading right after a menu click can reload the
  previous route (the shell restores the old URL while a screen asks about unsaved
  changes).
- **A hash route's stylesheet loads before the screen renders**, a moment after
  the page `load` event: use `Base::isVisibleSoon()` (or an expect with a
  timeout), never an instant `isVisible()`, for list checks after a load.
- **Third-party code that checks the page slug** (`$_GET['page'] === 'wpuf-post-forms'`)
  sees `wp-user-frontend` on the app page; `wpuf_is_admin_app()` /
  `wpuf_is_admin_screen( $slug )` answer for the app (the parity fixture
  `parity/wp/third-party-retired-hooks.php` shows the change).
- **CTR0001** follows the app's redirect chain: the recorder (`tests/contracts/runtime-recorder.php`)
  merges an old URL's load step and the builder's REST boot into the screen the app
  page shows. Run with `CONTRACT_ALLOW=<allow file> CONTRACT_BASELINE=278`.

## Reruns and local runs: what breaks and why

- **Partial runs (`--grep`) must include the tests that create state**: a spec's
  first test logs in and creates the form (FOS0001, PFS0001, PF0001, RFS0001).
  Some tests continue on the page the previous one left (FOS0084 needs FOS0083
  open; FOS0083 needs FOS0082's second form; PFS0069+ needs PFS0063-66 that set
  the notification subject). A "not found" right at the start of a test is
  usually this, not a selector problem.
  `postFormSettingsTest`: PFS0001 creates "PF Settings" and makes it the account
  page's default form by name; with older "PF Settings" copies on the site the
  account page can keep an old one. Delete old copies before rerunning.
  PFS0094 expects the admin to still get the form with role-based "Subscriber":
  Administrator is an always-selected role on the React builder (develop dropped
  it and locked admins out, a develop bug fixed on this branch).
  `regFormTestPro`: RF0004 logs the admin out before RF0005 registers a visitor
  (else "You are already logged in!"), and RF0002 re-adds non-single fields on
  every rerun; rerun from RF0004 without RF0002.
  `regFormSettingsTestPro` chains settings through the whole spec (approval from
  RFS0011, user notification from RFS0036, welcome mail from RFS0043): run it in full.
- **The site keeps data between runs.** Reruns create duplicates (two products
  with one title → strict-mode error; extra subscription packs → wrong counts;
  fields already on the "Registration" form → Oops alert). Clean before rerunning:
  `npx @wordpress/env run cli wp post delete $(npx @wordpress/env run cli wp post list --post_type=<type> --post_status=any --format=ids) --force`
  (`cli` = 8888, `tests-cli` = 8889).
- **Specs change global settings for later specs**: the settings spec can switch
  off `wpuf_profile[autologin_after_registration]` (RFS0002 then lands on the
  login screen); the onboarding "what you need" step can switch off Pro modules
  (email-templates → SR0029). In a full sharded run the order is alphabetical;
  after ad-hoc runs restore the option / module first.
- A test failing in a few **milliseconds** with "Target page, context or browser
  has been closed" means the browser window was closed; just rerun. Same for a
  one-off `page.goto: net::ERR_ABORTED` (navigation interrupted, seen on PFS0040).
- **Builder save goes over REST on the branch** (`POST wpuf/v1/admin/forms/{id}`), AJAX on develop:
  wait for either (`ParityPage.doSaveBuilder()`), never only `admin-ajax.php`.
- **Parity specs that change site options must restore them** (SEC0001 restores
  `wpuf_general`): a leftover key changed CTR0001's count from 65 to 78.
- **CTR0001 with an odd count**: list `wp-content/uploads/wpuf-contracts/ctr-*` with
  their times first; a crawl that recorded nothing now fails instead of passing with 0.
- **Fresh local site (`wp-env clean all`)**: skip RS0001 (`--grep-invert RS0001`; it resets an
  existing site through the WP Reset plugin, which a clean site does not have) and seed the
  Pro license first (`wp option update wpuf_license "$(cat <license json>)" --format=json` on
  `cli` and `tests-cli`), else LS0005 waits on the license screen and setup stops there.
- **Never run two Playwright processes on the same output dir** (they delete each
  other's artifacts: `ENOENT ... .playwright-artifacts`). Give parallel runs their
  own `TEST_PARALLEL_INDEX` + `SHARD_INDEX` (and base URL); run parity alone.
- With the `list` reporter the **error text prints at the end of the run**; for a
  failure mid-run read `test-results/**/error-context.md` (page snapshot),
  `test-failed-1.png`, or the `trace.zip` (actions + errors).
- A test that "hangs" for its full timeout is usually a locator that never
  resolves (`page.textContent()` and `locator.waitFor()` wait without limit);
  check the last action in the trace.
- Builder / settings / subscriptions dialogs are the shared React ConfirmDialog
  (`[data-wpuf-dialog]`, buttons `[data-slot="alert-dialog-action"]` /
  `[data-slot="alert-dialog-cancel"]`), no SweetAlert. They open one render after
  the click: wait for them (`dismissPromptIfShown()` in `pages/fieldAdd.ts`), never
  a one-shot `isVisible()`. The custom field tooltip's "Don't show again" is the
  cancel slot; "Okay" alone lets it reopen for the next custom field.
- Leftover subscription packs restrict post content ("Access Restricted"): run the
  post form specs before, or on a different site than, the subscription spec.
