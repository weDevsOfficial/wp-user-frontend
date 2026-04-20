# Playwright E2E (`tests/e2e/`)

Read before adding or modifying end-to-end tests.

## Layout

- `playwright.setup.config.ts` — shared setup run before parallel suites
- `playwright.parallel-one.config.ts` / `playwright.parallel-two.config.ts` — sharded parallel suites
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

npm run test:setup       # run setup suite first
npm run test:parallel    # run both parallel shards
npm run test:sharded     # setup + parallel in sequence
```

CI variants append `:ci` (`test:setup:ci`, `test:parallel:ci`, `test:sharded:ci`) and drop `--headed`.

## Conventions

- ES modules (`"type": "module"` in package.json) + TypeScript.
- Use the Page Object Model in `pages/` — don't put selectors directly in specs.
- Fixtures and auth state land in `setup/` (gitignored). Don't commit generated state.
- Screenshots and artifacts go to `test-results/` and `playwright-report/` — both gitignored.

## Before Adding a New Test

1. Check `features-map/` to see if the feature already has coverage.
2. Reuse existing Page Object methods before adding new ones.
3. If the test needs Pro features, gate it so Lite-only runs don't fail.

## Debugging

- Run a single spec: `npx playwright test tests/<file>.spec.ts --headed`
- Use `--debug` for inspector; `--trace on` for traces.
- Check `test-results/` for failure screenshots and videos.
