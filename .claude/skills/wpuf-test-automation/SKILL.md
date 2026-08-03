---
name: wpuf-test-automation
description: Author, extend, and run automated tests for WP User Frontend (Lite + Pro) across all layers — Playwright e2e (UI), REST API tests (wpuf/v1), and coverage of every feature/module. Use when writing new test cases, filling coverage gaps, detecting locators (via the Playwright MCP), stabilizing flaky tests, or wiring tests into the CI/CD release pipeline. Trigger on "write wpuf tests", "add e2e/api test", "test coverage", "automate <feature>", "/wpuf-test-automation".
---

# WPUF Test Automation

Owns the automated test strategy for **WP User Frontend Lite + Pro**. The suite lives in
`tests/e2e/` (Playwright + TypeScript + Page Object Model). This skill covers **writing**
tests for the whole plugin (UI e2e **and** REST API), **detecting locators with the
Playwright MCP**, following **standard test practices**, and running them in **CI/CD** for
production releases.

Consult `wpuf-frontend-dev` / `wpuf-backend-dev` for app internals and `wpuf-code-review`
for the review bar. For releases, tests gate `wpuf-release` / `wpuf-pro-release`.

## Ground rules (read first)

- **Never hardcode data.** URLs, credentials, license, and API keys come from `.env`
  (see `tests/e2e/.env-example`). Copy it to `.env`; never commit `.env` or `setup/` state.
- **Page Object Model is mandatory.** No raw selectors in `*.spec.ts`. Locators live in
  `pages/selectors.ts`; actions + assertions live in `pages/*.ts`; specs orchestrate steps.
- **Every feature gets a feature-map ID.** Add entries to `features-map/features-map.yml`
  (`LS`/`PF`/`RF`/`PFS`/`RFS`/`FOS`/`SB`/… prefixes) and tag tests `@Lite` / `@Pro` /
  `@Subscription` / `@Vendor` / `@Basic` plus `@Test_<ID>` for traceability.
- **Pro-gate Pro tests** so Lite-only runs don't fail (tag `@Pro`; guard on Pro being active).
- **Layout:** `pages/` (POM), `tests/` (specs), `utils/` (helpers, testData, fail-fast),
  `uploadeditems/` (upload fixtures), `features-map/`, `Field_Options_Coverage_Analysis.md`
  + `Subscription_Scenarios_Coverage_Analysis.md` (living coverage docs — update them).

## Test the WHOLE plugin — coverage map

Aim for coverage across **every** feature area, not just the core spine. Current state and
priority gaps (keep this in sync as you add tests):

| Area | Status | Where |
|---|---|---|
| Form builder + all field types | ✅ strong | `postFormTest`, `fieldAdd.ts` |
| Field options (validation, conditional logic, visibility, content restriction) | ✅ strong | `fieldOptionSettingsTest` |
| Post form settings (status, redirects, multi-step, notifications, pay-per-post, expiration) | ✅ strong | `postFormSettingsTest` |
| Registration + settings (roles, approval, redirects, email verification, multi-step) | ✅ strong | `regFormTestPro`, `regFormSettingsTestPro` |
| Vendor registration (Dokan / WC Vendors / WCFM) | ✅ good | `regFormTestPro` |
| Subscriptions (free/paid/recurring, limits, cancel) | 🟡 partial — **bank transfer only** | `subscriptionTest` |
| **Payments: Stripe / PayPal** | 🔴 **gap — build first** | keys already in `.env-example` |
| **Coupons & Tax** | 🔴 gap | Pro `Coupons`, `Tax` |
| **Content / menu / taxonomy restriction** | 🟡 role-based only | Pro restriction modules |
| **Pro modules**: User Directory, Private Message, Social Login, SMS, Email marketing (Mailchimp/MailPoet/GetResponse/ConvertKit/Campaign Monitor), Zapier/N8N, SEO, Reports, User Activity/Analytics, BuddyPress, PMPro, Comments, QR-code field | 🔴 mostly untested | Pro `modules/*` |
| **Integrations**: Elementor, Events Calendar | 🔴 gap | Pro `includes/Integrations` |
| **AI**: form templates, AI Review | 🔴 gap (only "enable keys" steps) | Pro `includes/AI*` |
| **REST API (`wpuf/v1`)** | 🔴 none | add API layer (below) |
| **Negative / security / authorization** | 🔴 near-zero | all areas |

When asked to "cover a feature," first check the map + `features-map.yml`; extend the
matching spec (or add a new one) and update the coverage `.md` files.

## Detecting locators with the Playwright MCP

Use the **Playwright MCP** to discover resilient locators instead of hand-guessing XPath.

1. Load the tools once: `ToolSearch` → `select:mcp__plugin_playwright_playwright__browser_navigate,mcp__plugin_playwright_playwright__browser_snapshot,mcp__plugin_playwright_playwright__browser_click,mcp__plugin_playwright_playwright__browser_type,mcp__plugin_playwright_playwright__browser_evaluate`.
2. `browser_navigate` to the page under test (admin form builder, front-end form, account page).
3. `browser_snapshot` returns the **accessibility tree with element refs** — read it to pick
   the most stable handle. Prefer, in order: **role + accessible name** (`getByRole`),
   `getByLabel`, `getByPlaceholder`, `getByText`, then a `data-*`/`id` hook. Fall back to
   XPath only when nothing stable exists.
4. Verify the locator resolves to exactly one node (`browser_click` / `browser_evaluate` to
   confirm), then **add it to `pages/selectors.ts`** under the right namespace — never inline.
5. For dynamic/AJAX UI (Vue/React form builder), confirm the element via snapshot **after**
   the action that renders it; add a web-first wait, not a sleep.

> The existing `selectors.ts` uses XPath. New locators should prefer role/label-based
> Playwright locators for resilience; only keep XPath where the DOM offers no better anchor.

## API test cases (REST `wpuf/v1`)

The plugin exposes REST controllers under the **`wpuf/v1`** namespace (controllers extend
`WP_REST_Controller`; every route has a `permission_callback`). Add an API layer so logic is
tested below the slow UI:

- Use Playwright's built-in `request` fixture / `APIRequestContext` (no browser). Put API
  specs in `tests/api/` and a `WpufApi` client helper in `pages/api/`.
- **Auth:** create an Application Password for the admin user and send Basic auth, or reuse
  a logged-in `storageState` + nonce. Keep creds in `.env`.
- **What to assert:** status codes, response schema/shape, `permission_callback` enforcement
  (401/403 for unauthorized), input **sanitization/validation** (bad payloads rejected), and
  data round-trips (create → read → update → delete a form / subscription / entry).
- **Security-focused API cases (high value):** unauthenticated access blocked, capability
  checks per role, nonce/CSRF failures, SQL-injection-ish and XSS payloads sanitized, mass
  quota / pricing tampering rejected server-side.
- Prefer API calls for **setup/teardown** of UI tests (seed a form/subscription via API, then
  assert in UI) — faster and less flaky than clicking through setup every time.

## Standard test practices

- **Independence & isolation.** Design tests to be self-contained: seed their own data (API
  or fixtures) and clean up. The current suite runs **sequential/stateful** with a shared
  page and `configureSpecFailFast()` — when adding tests, minimize cross-test coupling so one
  failure doesn't mask the rest; prefer per-test setup over relying on a prior test's output.
- **No hard sleeps.** Replace `page.waitForTimeout(...)` with web-first assertions
  (`await expect(locator).toBeVisible()`) and `waitForResponse`/`waitForLoadState`. Fixed
  sleeps are the #1 flakiness source here.
- **Assertions are explicit.** Every test must assert observable outcomes (UI state, DB via
  UI/API, emails via SMTP capture). Keep `expect()` in POM methods named `validate*`.
- **Data via faker + `.env`.** Generate unique data with `@faker-js/faker`; pull config from
  `utils/testData.ts` which reads `.env`.
- **Cover the pyramid.** Push logic down: unit/API for pricing, tax, coupon math and
  validation; reserve e2e for true user journeys. Don't e2e what an API test can prove.
- **Negative + boundary cases.** For each feature add: required-field failure, invalid input,
  over-limit/quota, unauthorized access, payment failure/cancel/duplicate, direct-URL access
  to restricted content.
- **Cross-browser/viewport.** Config is Chromium-only. Add a Firefox/WebKit project and a
  mobile viewport for front-end/theme-facing flows before release-critical sign-off.
- **Traceability.** One feature-map ID per behavior; tag with `@Test_<ID>`; keep the coverage
  `.md` files current so the gap picture stays honest.

## How to add a test (workflow)

1. **Scope & check** — find the feature area; check `features-map.yml` + coverage `.md` to
   avoid duplication. Reserve new IDs.
2. **Locators** — detect via Playwright MCP snapshot; add to `pages/selectors.ts`.
3. **POM** — reuse or add `do*` (actions) and `validate*` (assertions) methods in the right
   `pages/*.ts`. No selectors/asserts in the spec.
4. **Spec** — add `test('<ID> : <behavior>', { tag: ['@Pro'|'@Lite', '@Test_<ID>'] }, ...)`
   in the matching `tests/*.spec.ts` (or new file wired into a parallel config).
5. **Data** — faker + `.env` via `utils/testData.ts`; upload fixtures in `uploadeditems/`.
6. **Run locally** — `npm run test` (headed) or a single spec (below); iterate to green.
7. **Update docs** — feature-map entry + coverage `.md`; note Lite/Pro gating.

## Running

```bash
cd tests/e2e
npm ci                                # install
npx playwright install chromium       # browsers
cp .env-example .env                  # then fill in real values (never commit)

# Local (headed)
npm run test                          # full run, playwright.config.ts
npx playwright test tests/postFormTest.spec.ts --headed    # single spec
npx playwright test --grep @Subscription                   # by tag
npx playwright test --debug           # Playwright Inspector
npx playwright show-report            # last HTML report

# Sharded (mirrors CI): setup suite first, then 3 parallel shards
npm run test:setup && npm run test:parallel
npm run test:sharded                  # setup + parallel in sequence
npm run sharded-summary               # merge shard summaries
```

CI variants append `:ci` (`test:setup:ci`, `test:parallel:ci`, `test:sharded:ci`) and drop
`--headed`. Config: `fullyParallel:false`, `workers:1`, `retries:0` — when you make tests
independent, revisit these to enable real parallelism + retries.

## CI/CD for production releases

Tests run via **`.github/workflows/e2e-wpuf.yml`** (Ubuntu 22.04, PHP 7.4, Node 24):
checkout Lite → clone + build **wpuf-pro** (needs `ACCESS_TOKEN` secret) → build Lite
(`composer`, `npm run build`, `grunt release`) → install plugins → `npm ci` in `tests/e2e`
→ Playwright. Triggers: push/PR to `develop`, weekly `schedule` (Sun 19:00 UTC), and
`workflow_dispatch`.

Release gating (standard practice — enforce before shipping):
- **Block releases on red e2e.** `wpuf-release` / `wpuf-pro-release` must only tag/deploy
  after the e2e workflow is green on `develop`.
- Store all secrets in GitHub Actions secrets (`ACCESS_TOKEN`, `WPUF_PRO_LICENSE_KEY`,
  Stripe/PayPal/AI/SMTP keys) — mirror `.env-example`. Never echo secrets in logs.
- Publish the **HTML report + traces** as workflow artifacts; on failure, attach
  screenshots/videos from `test-results/` for triage.
- Run the **full sharded suite** on the release branch; a fast smoke subset (tag a
  `@Smoke` set) on every PR for quick feedback.
- Keep the CI Node/PHP versions aligned with the plugin's build matrix so test env == ship env.

## Gotchas

- Suite is **stateful/sequential** with a shared browser + fail-fast — an early failure hides
  downstream coverage. Read the report from the **first** failure, not the count.
- The `.spec.ts` files hold **0** `expect()` — assertions are inside POM `validate*` methods
  (~135 total). When judging coverage, count assertions/feature-map IDs, not `test()` blocks
  (many `test()` are workflow *steps*, e.g. "Admin is setting X").
- Pro tests need **wpuf-pro built + a license** (`WPUF_PRO_LICENSE_KEY`); gate them `@Pro`.
- Fixed `waitForTimeout` (e.g. 15s) exists in older specs — don't copy the pattern; use
  web-first waits.
- Only Chromium is configured today; add browsers/viewports deliberately, not by default,
  to keep CI time bounded (workflow timeout is 240 min).
