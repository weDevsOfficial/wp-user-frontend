---
name: wpuf-test-automation
description: Author, extend, and run automated tests for WP User Frontend (Lite + Pro, v5.0.0 platform) across all layers: PHPUnit (tests/php), jest (React admin and frontend), Playwright e2e (UI), REST API tests (wpuf/v1), the develop-vs-branch parity suite and contract snapshots. Use when writing new test cases, filling coverage gaps, detecting locators (via the Playwright MCP), stabilizing flaky tests, or wiring tests into the CI/CD release pipeline. Trigger on "write wpuf tests", "add e2e/api test", "test coverage", "automate <feature>", "/wpuf-test-automation".
---

# WPUF Test Automation

Owns the automated test strategy for **WP User Frontend Lite + Pro**. Layers, cheapest first:

| Layer | Where | Run |
|---|---|---|
| PHPUnit 9.6 (WordPress test library) | `tests/php/tests/*Test.php`, bootstrap `tests/php/bootstrap.php`, helpers `tests/php/src/` | `WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter <Test>` (add `WPUF_TESTS_WITH_PRO=1` to load the sibling `wpuf-pro`) |
| jest | `src/admin/shared`, `src/admin/apps/*`, `src/frontend` | `pnpm run test:shared`, `test:stores`, `test:form-builder`, `test:ai-form-builder`, `test:frontend` |
| Playwright e2e (UI) | `tests/e2e/tests/*.spec.ts`, POM `tests/e2e/pages/` | project `e2e` (sharded) after project `setup` |
| REST API (`wpuf/v1`, no browser) | `tests/e2e/tests/api/wpufRestApi.spec.ts` (AP0001-AP0015), client `pages/api/WpufApi.ts` | project `api` |
| Parity (develop site vs branch site) | `tests/e2e/parity/*.spec.ts`, POM `pages/parity.ts`, WP-CLI helpers `parity/wp/*.php`, fixtures `parity/fixtures/` | project `parity` (local gate, not in CI) |
| Contract snapshots | `tests/contracts/` (static hooks, endpoints, runtime recorder, diff), see its README | PHP scripts / mu-plugin |

Consult `wpuf-frontend-dev` / `wpuf-backend-dev` for app internals and `wpuf-code-review`
for the review bar. For releases, tests gate `wpuf-release`. Build and test command reference:
`docs/build-and-test.md`; Playwright patterns: `tests/e2e/CLAUDE.md`.

## Ground rules (read first)

- **Never hardcode data.** URLs, credentials, license, and API keys come from `tests/e2e/.env`
  (template `tests/e2e/.env-example`: `QA_BASE_URL`, `QA_ADMIN_USERNAME/PASSWORD`,
  `WPUF_PRO_LICENSE_KEY`, Stripe / PayPal / AI / SMTP keys, `PARITY_*`). Never commit `.env`,
  `setup/` or `.auth/` (live cookies).
- **Page Object Model is mandatory.** No raw selectors in `*.spec.ts`. Locators live in
  `pages/selectors.ts`; actions + assertions live in `pages/*.ts`; specs orchestrate steps.
- **Every behaviour gets an ID.** Name tests `'<ID> : <behavior>'` and tag `@Test_<ID>` plus
  `@Lite` / `@Pro` (and `@Basic`, `@Subscription`, `@Vendor`, `@FrontendLogin`, `@Negative`,
  `@EmailMarketing`, `@Block` where they fit). Prefixes in use: `LS`, `S` (setup), `PF`, `PFS`,
  `PFD`, `RF`, `RFS`, `FOS`, `SB`, `FL`, `EM`, `AI`, `APP`, `SR`, `ONB`, `WEL`, `MOD`, `QR`,
  `BLK`, `RS`, `AP`; parity `PAR`, `SET`, `SEC`, `HLT`, `CTR`, `ONB`.
  `features-map/features-map.yml` only lists LS, PF, PFS, RF, RFS, FOS, SB, FL, EM, AP, PAR,
  SET, SEC, HLT, CTR so far: add entries for the newer prefixes when you touch those specs.
- **Pro-gate Pro tests** so Lite-only runs don't fail (tag `@Pro`; guard on Pro being active).
- **Layout** (`tests/e2e/`): `pages/` (POM), `tests/` (specs), `tests/api/`, `parity/`,
  `utils/` (auth sessions, testData, fail-fast, wp-env CLI, parity sites), `wp/` (mu-plugins
  such as the AI mock `wpuf-ai-mock.php`), `uploadeditems/` (upload fixtures), `features-map/`,
  living coverage docs `coverage-gap.md`, `Field_Options_Coverage_Analysis.md`,
  `Subscription_Scenarios_Coverage_Analysis.md` (update them).

## Test the WHOLE plugin: coverage map

Current state and priority gaps (keep in sync with `coverage-gap.md` as you add tests):

| Area | Status | Where |
|---|---|---|
| Setup, login, pages, settings | ✅ | `alphaSetupTest` (project `setup`) |
| Post form builder + all field types | ✅ strong | `postFormTest`, `fieldAdd.ts`; parity `everyField`, `fieldFill`, `builderShapes`, `innerFields` |
| Field options (validation, conditional logic, visibility, content restriction) | ✅ strong | `fieldOptionSettingsTest`; parity `fieldConditions` |
| Post form settings + new-form defaults | ✅ strong | `postFormSettingsTest`, `postFormDefaultsTest`; parity `formSettings` |
| Registration + settings (roles, approval, redirects, email verification, multi-step) | ✅ strong | `regFormTestPro`, `regFormSettingsTestPro`; parity `registrationSettings` |
| Vendor registration (Dokan / WC Vendors / WCFM) | ✅ good | `regFormTestPro` |
| React admin app (lists, Submissions page, builder, module screens, welcome) | ✅ | `adminAppTest`, `moduleScreensTest`, `welcomeTest` |
| Settings screen | ✅ | `settingsScreenTest`; parity `settings`, `settingsSections`, `settingsConditions`, `moduleSettings` |
| Onboarding wizard | ✅ | `onboardingTest`; parity `onboarding` |
| AI form builder (with the AI mock) | ✅ | `aiFormBuilderTest`; parity `aiFormBuilder` |
| Post form block, frontend login, QR field (Pro), MailPoet on registration | ✅ / 🟡 | `blockFormTest`, `frontendLoginTest`, `qrCodeFieldTestPro`, `mailpoetRegistrationTestPro` |
| Release package smoke (screens mount, no JS errors, no asset 4xx/5xx) | ✅ | `releaseSmokeTest` (RS0001-RS0005) |
| REST API (`wpuf/v1`) | 🟡 AP0001-AP0015 | `tests/api/wpufRestApi.spec.ts`; per-route permissions in PHPUnit (`RestRoutesPermissionTest`, `PlatformRestTest`) |
| Hooks keep firing / stored data unchanged | ✅ | parity `hookBridge`, `noopSave`, `contracts`, `security`, `health`; PHPUnit `ClassicHooksKeptTest`, store tests |
| Subscriptions (free/paid/recurring, limits, cancel) | 🟡 bank transfer only | `subscriptionTest` |
| **Payments: Stripe / PayPal** | 🔴 gap, build first | keys already in `.env-example` |
| **Coupons & Tax** | 🔴 gap | Pro `Coupons`, `Tax` |
| **Content / menu / taxonomy restriction** | 🟡 field visibility / role-based only | Pro restriction modules |
| **Pro modules**: User Directory, Private Message, Social Login, SMS, other email marketing (Mailchimp / GetResponse / ConvertKit / Campaign Monitor), Zapier/N8N, SEO, Reports, User Activity/Analytics, BuddyPress, PMPro, Comments | 🔴 mostly untested (module screens only open) | Pro `modules/*` |
| **Integrations**: Elementor, Events Calendar | 🔴 gap | `includes/Integrations` (free + Pro) |
| **Negative / security / authorization in the UI** | 🟡 `@Negative` handful; parity `security` | all areas |

When asked to "cover a feature," check this map, `coverage-gap.md` and `features-map.yml`
first; push logic into PHPUnit / jest / API where it can be proven there, extend the matching
spec (or add one) for real user journeys, and update the coverage docs.

## Detecting locators with the Playwright MCP

Use the **Playwright MCP** (prefix `mcp__playwright__*`) to discover resilient locators instead of hand-guessing XPath.

1. Load the tools once: `ToolSearch` → `select:mcp__playwright__browser_navigate,mcp__playwright__browser_snapshot,mcp__playwright__browser_click,mcp__playwright__browser_type,mcp__playwright__browser_evaluate`.
2. `browser_navigate` to the page under test (React admin route, front-end form, account page). The MCP browser starts logged out every session (`--isolated`).
3. `browser_snapshot` returns the **accessibility tree with element refs**; read it to pick
   the most stable handle. Prefer, in order: **role + accessible name** (`getByRole`),
   `getByLabel`, `getByPlaceholder`, `getByText`, then a `data-*`/`id` hook. Fall back to
   XPath only when nothing stable exists.
4. Verify the locator resolves to exactly one node (`browser_click` / `browser_evaluate` to
   confirm), then **add it to `pages/selectors.ts`** under the right namespace, never inline.
5. For React UI (admin screens, frontend apps), confirm the element via snapshot **after**
   the action that renders it; add a web-first wait, not a sleep.
6. Subagents in one session share one MCP browser: never plan parallel locator hunts.

> `selectors.ts` is mostly XPath. New locators should prefer role/label-based Playwright
> locators; keep XPath only where the DOM offers no better anchor.

## API test cases (REST `wpuf/v1`)

Controllers live in `includes/Platform/REST/Controllers/` on the `Platform\REST\RestController`
base; `Platform\REST\Manager` registers them, permissions come from `Platform\Caps`, public
routes are rate limited (`Platform\REST\Rate_Limit`). Pro adds its own controllers.

- Use Playwright's `request` fixture / `APIRequestContext` (no browser). Specs in `tests/api/`,
  client helpers in `pages/api/WpufApi.ts`.
- **Auth:** Application Password + Basic auth, or a logged-in `storageState` + REST nonce.
  Keep creds in `.env`.
- **What to assert:** status codes, response shape, permission enforcement (401/403 for
  visitors and subscribers), input validation (bad payloads rejected), round-trips
  (create → read → update → delete a form / subscription / entry).
- **Security cases (high value):** unauthenticated access blocked, capability per role,
  nonce failures, injection / XSS payloads sanitized, quota / pricing tampering rejected.
- Every new route also gets a PHPUnit test that visitors and subscribers are refused
  (`PlatformRestTest` / `RestRoutesPermissionTest` style); that is faster and runs free-only
  and with Pro.
- Prefer API calls for **setup/teardown** of UI tests (seed via API, assert in UI).

## Standard test practices

- **Independence & isolation.** Seed your own data and clean up. The suite is still
  **sequential/stateful** (one shared site, cached sessions in `.auth/`,
  `configureSpecFailFast()`); minimize cross-test coupling so one failure doesn't mask the rest.
- **No hard sleeps.** Replace `page.waitForTimeout(...)` with web-first assertions
  (`await expect(locator).toBeVisible()`) and `waitForResponse` / `waitForLoadState`. Fixed
  sleeps (about 170 left in specs and POMs) are the #1 flakiness source.
- **Assertions are explicit.** Every test asserts observable outcomes (UI state, DB via
  UI/API/WP-CLI, emails via SMTP capture). Prefer `validate*` POM methods for reusable checks.
- **Data via faker + `.env`.** Unique data with `@faker-js/faker`; config from
  `utils/testData.ts` (reads `.env`, default `http://localhost:8889`).
- **Cover the pyramid.** Pricing, tax, coupon math, stores, normalizers, payload builders and
  validation belong in PHPUnit / jest; reserve e2e for true user journeys.
- **Negative + boundary cases.** Required-field failure, invalid input, over-limit / quota,
  unauthorized access, payment failure / cancel / duplicate, direct-URL access to restricted content.
- **Cross-browser / viewport.** Config is Chromium only. Add Firefox / WebKit and a mobile
  viewport for front-end flows deliberately (CI time is bounded).
- **Traceability.** One ID per behaviour, tagged `@Test_<ID>`; keep the coverage docs current.

## How to add a test (workflow)

1. **Scope & check**: find the feature area; check this map, `coverage-gap.md`, `features-map.yml`; reserve new IDs.
2. **Pick the layer**: PHPUnit / jest if it can be proven there; API for REST behaviour; e2e for the journey; parity when the claim is "same as develop".
3. **Locators**: detect via Playwright MCP snapshot; add to `pages/selectors.ts`.
4. **POM**: reuse or add `do*` (actions) and `validate*` (assertions) methods in the right `pages/*.ts`.
5. **Spec**: `test('<ID> : <behavior>', { tag: ['@Pro'|'@Lite', '@Test_<ID>'] }, ...)` in the matching `tests/*.spec.ts`. A new spec file is picked up by project `e2e` automatically; also add it to a group in `.github/workflows/e2e-wpuf.yml` (CI runs explicit file lists).
6. **Data**: faker + `.env` via `utils/testData.ts`; upload fixtures in `uploadeditems/`.
7. **Run locally** (below); iterate to green.
8. **Update docs**: feature-map entry + coverage `.md`; note Lite/Pro gating.

## Running

```bash
# PHPUnit and jest (plugin root)
WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter FormsRestTest
WPUF_TESTS_WITH_PRO=1 WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter PlatformRestTest
pnpm run test:shared && pnpm run test:stores   # + test:form-builder / test:ai-form-builder / test:frontend

# Playwright (one config: tests/e2e/playwright.config.ts, projects setup / e2e / api / parity)
cd tests/e2e
pnpm install --frozen-lockfile
npx playwright install chromium
cp .env-example .env                  # then fill in real values (never commit)
pnpm run env:setup                    # npx wp-env start (local wp-env site)

pnpm run test                         # setup + e2e, headed
pnpm run test:setup                   # project setup (site reset + config), run first, once
pnpm run test:parallel                # project e2e, --shard=1/4 .. 4/4 in sequence
pnpm run test:sharded                 # setup + the 4 shards
pnpm run test:api                     # project api (no browser)
pnpm run test:all                     # setup + shards + api
pnpm run test:parity                  # project parity (needs PARITY_* in .env); PAR0004 runs last with PARITY_FILL_WORKERS (default 4)
npx playwright test --config=playwright.config.ts --project=e2e tests/postFormTest.spec.ts --headed   # one spec
npx playwright test --config=playwright.config.ts --project=e2e --grep @Subscription                 # by tag
npx playwright show-report            # last HTML report
pnpm run sharded-summary              # merge shard summaries
```

CI variants append `:ci` (`test:setup:ci`, `test:parallel:ci`, `test:sharded:ci`, `test:api:ci`,
`test:all:ci`) and drop `--headed`. Config: `fullyParallel: false`, `workers: 1` (must stay 1:
specs share one site and cached sessions), `retries: 0`, no `dependencies: ['setup']` (setup
would rerun per shard). `SHARD_INDEX` / `E2E_PHASE` only pick report paths.
Release package check: `QA_BASE_URL=http://site.test WPUF_E2E_WP_PATH=/path/to/site npx playwright test tests/releaseSmokeTest.spec.ts tests/aiFormBuilderTest.spec.ts --project=e2e`
on a fresh site with the built zip(s) and `wp/wpuf-ai-mock.php` in mu-plugins, Pro on and off.

## CI/CD for production releases

- **`.github/workflows/e2e-wpuf.yml`** (Ubuntu 22.04, PHP 7.4, Node from `.nvmrc` (22), pnpm):
  checkout free → clone + build **wpuf-pro** (`ACCESS_TOKEN` secret, inputs `pro_repo` /
  `pro_ref`, default `develop`; Pro builds with lite as its sibling) → pin the AppSumo Appsero
  hash so the license activates → build free (`composer i --no-dev -o`, `pnpm build`) →
  download test plugins (WP Mail Log, WooCommerce, EDD, WC Vendors, WCFM membership, Dokan,
  MailPoet) → `wp-env start` → per matrix group: `test:setup:ci`, then that group's spec files
  with `--project=e2e`; the `post` group also runs `test:api:ci`. Groups: `post`,
  `registration`, `fields-subscription`, `admin-app`, `onboarding`, `blocks` (120 min each).
  A `merge-reports` job merges blob reports into one HTML report, writes the run summary and
  fails the run when any group failed. Triggers: push/PR to `develop`, weekly schedule
  (Sun 19:00 UTC), `workflow_dispatch`. **Both jobs skip on `weDevsOfficial/wp-user-frontend`
  and `weDevsOfficial/wpuf-pro`** (`if: github.repository != ...`), so e2e CI runs on forks.
- PHPCS (`phpcs.yml`, PRs) lints whole changed PHP files. PHPUnit, jest and parity are not
  in CI: run them locally before handoff.

Release gating:
- **Block releases on red tests**: related PHPUnit + jest green, the e2e workflow green on a
  fork run of the release head, `releaseSmokeTest` green on the built package (Pro on and off).
- Store secrets in GitHub Actions secrets (`ACCESS_TOKEN`, `WPUF_PRO_LICENSE_KEY`,
  `QA_*`, reCAPTCHA / Turnstile / Maps keys, SMTP); never echo them.
- The merged HTML report is uploaded as `playwright-report`; traces and screenshots are kept
  on failure (`trace: retain-on-failure`, `screenshot: only-on-failure`).
- Keep CI Node/PHP aligned with the build (`.nvmrc`, PHP 7.4) so test env == ship env.

## Gotchas

- Suite is **stateful/sequential** with cached sessions + fail-fast; an early failure hides
  downstream coverage. Read the report from the **first** failure, not the count.
- Many `test()` blocks are workflow *steps* ("Admin is setting X"); judge coverage by IDs and
  assertions (`expect()` now appears in specs and in POM `validate*` methods), not by `test()` count.
- Pro tests need **wpuf-pro built + a license** (`WPUF_PRO_LICENSE_KEY`); gate them `@Pro`.
- Parity needs both sites (`PARITY_DEVELOP_*`, `PARITY_BRANCH_*`) and WP-CLI on each; run one
  parity command at a time (shared sites); Pro-off checks use `--skip-plugins=wpuf-pro`.
- A missing JS file 301s to WordPress's HTML 404 with status 200: crawls check the script
  content type, not only 4xx.
- `tests/e2e/CLAUDE.md` still says the parallel run uses three shards; `package.json` uses four.
