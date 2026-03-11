---
name: wpuf-git
description: Guidelines for git and GitHub operations in the WPUF repository. Use when creating branches, commits, or pull requests.
---

# WPUF Git Guidelines

## Branch Strategy

-   **Main development branch:** `develop`
-   **WordPress.org sync branch:** `trunk` (auto-deploys to wordpress.org)
-   **Feature branches:** branch off `develop`

## Pre-commit Checks

Before committing, run:

```bash
# PHP changes
composer phpcs

# JS/TS changes
npm run lint:js

# CSS changes
npm run lint:css

# Verify frontend builds
npm run build
```

## CI Checks

PRs trigger:

1.  **PHPCS** — Runs on changed files only (not entire codebase)
2.  **Playwright E2E** — Full E2E test suite (on PRs to `develop`)

Pushes to `trunk` trigger WordPress.org asset sync.
