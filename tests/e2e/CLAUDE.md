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
npm i
npx playwright install chromium

npm run test:setup       # run setup suite first (alphaSetupTest)
npm run test:parallel    # run the 3 native shards sequentially
npm run test:sharded     # setup + shards in sequence
```

CI variants append `:ci` (`test:setup:ci`, `test:parallel:ci`, `test:sharded:ci`) and drop `--headed`.

## One config, three projects

Everything runs from `playwright.config.ts`. The old per-phase configs
(`playwright.setup/parallel/api.config.ts`) and the earlier `parallel-one/two/three`
configs are gone — phases are now **projects** selected with `--project`:

- `--project=setup` → `alphaSetupTest.spec.ts` (run first, once)
- `--project=e2e` → the stateful UI suite, split via native `--shard=i/n`
- `--project=api` → REST layer (`tests/api/`, no browser)

`workers: 1` + `fullyParallel: false`, so no two stateful specs hit the shared site
at once. `npm run test:parallel` invokes `--project=e2e` three times
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
