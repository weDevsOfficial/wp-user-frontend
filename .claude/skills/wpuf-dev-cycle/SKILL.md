---
name: wpuf-dev-cycle
description: Run tests, linting, and quality checks for WPUF development. Use when running tests, fixing code style, building assets, or following the development workflow.
---

# WPUF Development Cycle

This skill provides guidance for the WP User Frontend development workflow including building, testing, linting, and CI.

## Build Commands

```bash
# Full build (all Vite entries + CSS)
npm run build

# Individual Vite builds
npm run build:forms-list
npm run build:subscriptions
npm run build:frontend-subscriptions
npm run build:ai-form-builder
npm run build:account

# React (Webpack)
npm run build:subscriptions-react    # Production build
npm run start:subscriptions-react    # Dev watch mode

# CSS
npm run build:css                    # Tailwind compile + minify

# Legacy (Grunt)
npm run release                      # Full release build (grunt release)
```

## PHP Linting (PHPCS)

```bash
composer phpcs             # Check coding standards
composer phpcbf            # Auto-fix coding standard violations
```

PHPCS config is in `phpcs.xml.dist`. It scans all PHP files excluding `assets/`, `src/`, `vendor/`, `node_modules/`, `tests/`, `dist/`, `build/`.

**CI behavior:** GitHub Actions runs PHPCS only on changed files in PRs (not the entire codebase).

### PHPCS Rules Summary

-   **Standards:** `WordPress-Core` + `WordPress` + `PHPCompatibilityWP`
-   **PHP compatibility:** 5.6+
-   **Text domains:** `wp-user-frontend`, `wpuf-pro`
-   **Strict `in_array()`:** enforced as error
-   **Yoda conditions:** not enforced
-   **File naming:** not enforced
-   **Direct DB queries:** not enforced (severity 0)
-   **Output escaping:** not enforced (severity 0)

## JavaScript/CSS Linting

```bash
npm run lint:js            # ESLint (via @wordpress/scripts)
npm run lint:css           # Stylelint
```

## Playwright E2E Tests

### Location & Config

Tests live in `tests/e2e/` with their own `package.json`.

```bash
# From tests/e2e/ directory
npm ci                                    # Install test dependencies
npx wp-env start                          # Start WordPress environment
npx playwright test                       # Run all tests
npm run test:sharded:ci                   # Sharded parallel execution (CI)
```

### Config Files

-   `tests/e2e/playwright.parallel-one.config.ts` — First parallel shard
-   `tests/e2e/playwright.parallel-two.config.ts` — Second parallel shard

### Environment Variables

Tests use `.env` (see `.env-example`):

| Variable | Purpose |
|---|---|
| `BASE_URL` | WordPress site URL |
| `ADMIN_USERNAME` | Admin login |
| `ADMIN_PASSWORD` | Admin password |
| `WPUF_PRO_LICENSE_KEY` | Pro license for testing |
| `GOOGLE_MAP_API_KEY` | Google Maps integration tests |
| `RECAPTCHA_SITE_KEY` | reCAPTCHA tests |

### Test Structure

Tests are TypeScript spec files in `tests/e2e/`. The CI runs tests with sharding across two parallel configs for faster execution.

## PHPUnit

PHPUnit 7.5.9 is listed as a dev dependency but **no active test suite exists** in the `tests/` directory. The infrastructure is in place for future use.

## Development Workflow

1.  Make code changes
2.  Run `composer phpcs` for PHP changes
3.  Run `npm run lint:js` for JS/TS changes
4.  Run `npm run build` to verify frontend builds
5.  Fix any issues (`composer phpcbf`, manual fixes)
6.  Test manually or run E2E tests
7.  Commit only after all checks pass

## CI Pipeline (GitHub Actions)

### 1. `phpcs.yml` — Code Quality Inspection

-   **Trigger:** Pull requests
-   **PHP version:** 7.4
-   **Steps:**
    1.  Checkout code
    2.  Setup PHP + Composer + cs2pr tool
    3.  Install Composer dependencies
    4.  Detect changed files from PR
    5.  Run PHPCS on changed files only
    6.  Convert output to GitHub Check format (cs2pr)

### 2. `e2e-wpuf.yml` — Playwright E2E Tests

-   **Trigger:** Push to `develop`, PRs to `develop`, weekly schedule (Sunday 1 AM BDT), manual dispatch
-   **Steps:**
    1.  Setup PHP 7.4 + Node 24
    2.  Clone and build wpuf-pro
    3.  Build wpuf-lite (`composer install`, `npm run build`, `grunt release`)
    4.  Install test dependencies and additional plugins (WooCommerce, Dokan, WC Vendors, etc.)
    5.  Start wp-env
    6.  Run Playwright tests (sharded)
    7.  Upload HTML report as artifact
    8.  Send email report (pass/fail)

### 3. `main.yml` — WordPress.org Sync

-   **Trigger:** Push to `trunk`
-   **Action:** Sync plugin assets and readme to wordpress.org via `10up/action-wordpress-plugin-asset-update`
