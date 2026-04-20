DESCRIPTION: WPUF build, lint, and test commands.
Read when setting up the dev loop or before running CI-equivalent checks locally.

# Build & Test

## Frontend (Vite + Vue 3)

```bash
npm run build                        # Production build (all entry points)
npm run build:forms-list             # Build forms list module
npm run build:subscriptions          # Build admin subscriptions module
npm run build:frontend-subscriptions # Build frontend subscriptions module
npm run build:ai-form-builder        # Build AI form builder module
npm run build:account                # Build account module
npm run build:user-directory         # Build user directory module
npm run build:css                    # Compile Tailwind CSS via Grunt
npm run dev:user-directory           # Dev mode for user directory module
```

## Frontend (Grunt — legacy)

```bash
grunt release                        # Full release build
```

## PHP

```bash
composer phpcs                       # PHP CodeSniffer
composer phpcbf                      # Auto-fix PHP code style
```

## Testing

- **Playwright** for E2E tests in `tests/e2e/`.
  - Parallel configs: `playwright.parallel-one.config.ts`, `playwright.parallel-two.config.ts`.
  - Setup: `playwright.setup.config.ts`.
- **PHPUnit 7.5.9** listed as dev dependency (test infrastructure in development — no suite yet).

## Before Committing
1. `composer phpcs` on changed PHP files.
2. Relevant `npm run build:*` for changed Vue entry point.
3. Verify Playwright tests still pass for affected features.
