DESCRIPTION: WPUF build, lint, and test commands.
Read when setting up the dev loop or before running CI-equivalent checks locally.

# Build & Test

## Frontend (Vite + Vue 3)

```bash
pnpm build                           # Production build (all entry points), ends with bin/verify-build.mjs
pnpm run build:admin                # React admin apps in one wp-scripts build (webpack.admin.config.js)
WPUF_ENTRY=form-builder pnpm run build:admin  # one app (form-builder | forms-list-screen | subscriptions | settings-screen | ai-form-builder | admin-app = the app shell)
pnpm run build:forms-list-screen     # Build forms list module (alias of WPUF_ENTRY=forms-list-screen)
pnpm run build:subscriptions         # Build admin subscriptions module
pnpm run build:frontend-subscriptions # Build frontend subscriptions module
pnpm run build:ai-form-builder       # React AI form builder: wp-scripts entry + Tailwind 4 sheet
pnpm run build:account               # Build account module
pnpm run build:user-directory        # Build user directory module
pnpm run build:css                   # Compile Tailwind CSS via Grunt
pnpm run dev:user-directory          # Dev mode for user directory module
```

## Frontend (Grunt, legacy)

```bash
grunt release                        # Full release build
```

## PHP

```bash
composer phpcs                       # PHP CodeSniffer
composer phpcbf                      # Auto-fix PHP code style
```

## Testing

- **Playwright** for E2E tests in `tests/e2e/`: one `playwright.config.ts` with projects
  `setup`, `e2e` (sharded with `--shard=i/n`), `api` and `parity`. See `tests/e2e/CLAUDE.md`.
- **Parity suite** (`--project=parity`, local gate): compares a develop site with a branch
  site. Needs `PARITY_DEVELOP_URL/_PATH` and `PARITY_BRANCH_URL/_PATH` in `tests/e2e/.env`
  (WP-CLI runs with `--path`). Seeds identical stored forms (`parity/wp/seed-form.php`),
  dumps storage with PHP types (`parity/wp/dump-form.php`), compares.
  ```bash
  cd tests/e2e && npx playwright test --project=parity
  ```
  Specs: `seed` (PAR0001), `noopSave` (PAR0002, untouched save byte-identical), `fieldFill`
  (PAR0004, slow: ~35 min), `builderShapes` (PAR0005-PAR0014), `settings` (SET0001-SET0006),
  `security` (SEC0001-SEC0006), `health` (HLT0001-HLT0005), `templatePicker` (TPL0001-TPL0002:
  the React form template picker, its screenshots and scrolling preview), `contracts` (CTR0001: live develop +
  branch crawl with the recorder mu-plugin, diff, `CONTRACT_ALLOW=<allow.tsv>` for accepted losses). Run one parity command at a time:
  both sites are shared, parallel runs flake. Pro-off checks use
  `wp eval-file ... --skip-plugins=wpuf-pro`; settings helpers need
  `--exec='define("WP_ADMIN",true);'` or pro sections are not registered.
- **Contract snapshots** (`tests/contracts/`, see its README): static hook call sites,
  REST/AJAX/admin pages, per-screen runtime hooks/handles/globals, and a develop vs branch
  diff. A missing JS file 301s to an HTML 404 with status 200, so crawls check the script
  content type, not only 4xx.
- **PHPUnit 9.6** is a dev dependency; the suite (`tests/php`, wp-env) is added with the
  admin platform.

## Package manager

pnpm on branch `feature/react-admin-revamp` (like FlyHR): `pnpm install --frozen-lockfile && pnpm build`
(pnpm 12.8.1 from `packageManager`, Node 22.22.2+). `pnpm build` ends with `bin/verify-build.mjs`
(fails on a missing/empty generated asset, a React `.asset.php` without deps, or an unresolved
import). Tailwind scans sources only, so a full build is deterministic (no CSS drift).
`modules/user-directory` keeps npm (`build:user-directory` runs `npm ci` there).

## Translations (React admin)
- The React admin bundles build to `assets/js/screens/<name>.js` (+ `.asset.php`),
  minified but **not** named `.min.js`: `wp i18n make-pot` and translate.wordpress.org
  skip `*.min.js`, and WordPress loads a script's JSON translations by the md5 of the
  enqueued file's path, so the bundle must be both extracted and enqueued under that name.
- `node bin/make-pot.mjs` (run by `grunt i18n` / `grunt release`, needs WP-CLI) writes
  `languages/wp-user-frontend.pot` from the PHP, `src/admin` and those bundles.
- Every React handle calls `wp_set_script_translations( $handle, 'wp-user-frontend', WPUF_ROOT . '/languages' )`
  so JSON shipped in the plugin's `languages/` loads too, not only `WP_LANG_DIR`.
- Wrap every user-facing string in `__()` (also `title`, `alt`, `aria-label`, `placeholder`).
- Pro: `node tools/make-pot.mjs` links each React string to its bundle (`assets/js/<bundle>.js`;
  core reads `.min.js` as `.js` for translations).

## Release build check (local)

Generated assets are gitignored, so a release only works if CI builds every React part. Check it locally before tagging:

1. Clean clones of free and Pro (siblings), then the free steps of `deploy-org.yml`: `pnpm install --frozen-lockfile`, `pnpm build`, `pnpm exec grunt release`, `composer install --no-dev --optimize-autoloader`, `node bin/verify-build.mjs --release`, then `rsync -a --exclude-from=.distignore ./ <pkg>/wp-user-frontend/` (that folder is the wp.org package).
2. Pro steps of `release.yml`: `composer install --no-dev -o`, the user-directory composer install (with the transient advisory / stability relax), `npm install && npm run build`, the user-directory `npm install && npm run build`, `npx grunt --force`, `npx grunt zip-all --force`, then the workflow's own "Verify built assets" block on `dist/` (on macOS drop `globstar` and use `dist/wp-user-frontend-pro-*`: bash 3.2 has no `**`).
3. Install the package and one plan zip on a fresh site with the AI mock (`tests/e2e/wp/wpuf-ai-mock.php` in mu-plugins) and run, with Pro on and off:
   `QA_BASE_URL=http://site.test WPUF_E2E_WP_PATH=/path/to/site npx playwright test tests/releaseSmokeTest.spec.ts tests/aiFormBuilderTest.spec.ts --project=e2e`
   `releaseSmokeTest` (RS0001-RS0005) fails when a React screen does not mount, logs a JS error or a plugin asset returns 4xx/5xx.

## Before Committing
1. `composer phpcs` on changed PHP files.
2. Relevant `pnpm run build:*` for changed Vue entry point.
3. Verify Playwright tests still pass for affected features.
