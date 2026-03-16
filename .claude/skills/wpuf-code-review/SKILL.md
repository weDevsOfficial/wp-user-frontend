---
name: wpuf-code-review
description: Review WPUF code changes and pull requests for coding standards, security, and architectural compliance. Use when reviewing PRs, performing code audits, or checking code quality.
---

# WPUF Code Review

Review code changes against WP User Frontend coding standards and project conventions. Consult the other `wpuf-*` skills for detailed standards.

## Pre-Review Steps

Before reviewing code, perform these steps:

1.  **Determine review scope**: Check if this is a targeted review (specific files) or a diff-based review.
    -   If reviewing recent changes, use `git diff` (or `git diff --cached`, `git diff HEAD~1`) to identify only the changed files and lines. Focus your review on the changed code, not the entire file.
    -   If the user points to specific files, review those files in full.

2.  **Find project configuration**: Use Glob to search for PHPCS config files:
    -   PHPCS: `phpcs.xml.dist` (project root)
    -   If found, read to understand the project's specific ruleset.

3.  **Run automated tools** (when available):
    -   Run `composer phpcs` if PHPCS is installed and configured.
    -   Include tool output in your review but add human-readable context and explanations.

4.  **Diff awareness**: When reviewing a diff, distinguish pre-existing issues from newly introduced ones. Flag pre-existing issues separately (e.g., "Pre-existing pattern propagated to new files").

## Critical Violations to Flag

### Backend PHP

**Architecture & Structure:**

-   **Not extending `WP_REST_Controller`** — REST controllers must extend `WP_REST_Controller`.
-   **Wrong API namespace** — Must use `wpuf/v1` as the REST namespace.
-   **Missing permission callbacks** — Every REST route must have a `permission_callback` (never omit it).
-   **Controller not registered in API.php** — New controllers must be added to `API::__construct()` and stored in the container.
-   **Direct instantiation bypassing container** — Services should be registered in the appropriate bootstrap class (`Admin`, `Frontend`, or `WP_User_Frontend::instantiate()`), not instantiated ad hoc.
-   **Missing Pro/Free guards** — Features exclusive to the free version must check `! class_exists( 'WP_User_Frontend_Pro' )`.

**Naming & Conventions:**

-   **camelCase methods** — Must use `snake_case` for methods, variables, and hooks (WordPress convention).
-   **Wrong namespace** — Must follow `WeDevs\Wpuf\{Domain}\{Class}` pattern with matching file path.
-   **Wrong text domain** — Must use `wp-user-frontend` for free version strings. `wpuf-pro` for Pro strings. Never use `wpuf` alone.
-   **Missing `wpuf_` prefix on hooks** — All `apply_filters()` and `do_action()` hook names must start with `wpuf_` to avoid conflicts.

**Security:**

-   **Missing permission callbacks** — Every REST route must have a `permission_callback`.
-   **Loose comparisons** — Must use strict comparisons (`===`, `!==`). `in_array()` must pass `true` as third argument (enforced as error by PHPCS).
-   **Unsanitized input** — All `$request` params must be sanitized (`sanitize_text_field()`, `absint()`, etc.).
-   **Missing `wp_unslash()` before sanitization** — Superglobals (`$_GET`, `$_POST`, `$_SERVER`, `$_REQUEST`) must be unslashed before sanitizing: `sanitize_text_field( wp_unslash( $_POST['field'] ) )`.
-   **Unescaped output** — All output must be escaped (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`, etc.). Escape late, sanitize early.
-   **Direct SQL without `$wpdb->prepare()`** — All dynamic SQL values must use prepared statements.
-   **Missing nonce verification** — Admin form submissions and AJAX handlers must verify nonces (`wp_verify_nonce()`, `check_ajax_referer()`).
-   **Dangerous functions** — No `eval()`, `extract()`, or direct file includes without proper path validation.

**Performance:**

-   **N+1 queries** — Avoid database queries inside loops. Use batch queries or caching.
-   **Unnecessary database calls** — Check for redundant queries that could be cached or combined.
-   **Missing caching** — Repeated expensive operations should use WordPress object cache or transients.

**Documentation:**

-   **Missing `@since` tag** — Required for new public/protected methods and hooks. Use `WPUF_SINCE` placeholder.
-   **Missing PHPDoc** — Required for all class methods, hooks, and filters.

### Frontend

-   **Using Vue for new UI** — Vue is legacy. New UI must use React, vanilla JS, or jQuery. Only modify existing Vue code when fixing bugs in Vue-based pages.
-   **Missing Tailwind prefix** — All Tailwind classes must use the `wpuf-` prefix (e.g., `wpuf-bg-primary`, not `bg-primary`).
-   **Tailwind outside scoped containers** — Tailwind preflight only works inside designated containers (`.wpuf_packs`, `#wpuf-subscription-page`, etc.). Using Tailwind outside these containers will lack reset styles.
-   **Missing TypeScript types** — New code should include proper typing.
-   **Wrong text domain in JS** — Must use `'wp-user-frontend'`, not `'wpuf'`.
-   **Missing translator comments** — `sprintf()` calls with placeholders need `/* translators: */` comments.
-   **Accessibility** — Check for ARIA attributes, semantic HTML, and keyboard navigation support.

### Testing

-   **Hardcoded test data** — Use environment variables from `.env` for URLs, credentials, and API keys.

## PR Checklist Verification

1.  **Code follows WordPress coding standards** — Run `composer phpcs` on changed files
2.  **PHPCS passes** — No coding standard violations
3.  **Inline documentation present** — PHPDoc for new methods/hooks
4.  **Frontend builds succeed** — `npm run build` completes without errors
5.  **Appropriate labels assigned** — Bug fix, feature, enhancement, etc.
6.  **Changelog entry** — Before/after description included
7.  **Screenshots** — Required for visual changes
8.  **Test instructions** — Steps to reproduce/verify the change

## Review Approach

1.  **Run pre-review steps** — scope the diff, run PHPCS if available
2.  **Scan for critical violations** listed above
3.  **Check the PR checklist** items are satisfied
4.  **Verify REST patterns** — permission checks, sanitization, response format
5.  **Check security** — permissions, sanitization, escaping, SQL safety, nonce verification
6.  **Check performance** — N+1 queries, unnecessary DB calls, missing caching
7.  **Review extensibility** — are appropriate filters/actions in place for Pro extension?
8.  **Verify Tailwind usage** — correct prefix, inside scoped containers

## Output Format

**Every issue MUST include a file reference.** Use the format `file/path.php:LINE_NUMBER` for each issue. If an issue spans multiple files, list each file path explicitly. Never use vague references like "all templates" — always name the files.

For each violation found:

```text
[severity]: [Specific problem]
Location: file/path.php:42
Code: `the problematic code snippet`
Fix: [Brief explanation or correct code example]
```

When reviewing diffs, indicate if an issue is newly introduced or pre-existing:

```text
[severity]: [Specific problem] (pre-existing)
Location: file/path.php:42
Note: This pattern existed before this PR but was propagated to new code.
```

Severity levels:

-   **CRITICAL** — Security vulnerabilities, data loss risk, broken functionality
-   **ERROR** — Standards violation, missing required patterns
-   **WARNING** — Suboptimal approach, performance issues, missing best practice
-   **SUGGESTION** — Minor improvement, style, optimization opportunity

## Reviewer Principles

> **Correct** — Does the change do what it's supposed to? Code 100% fulfilling the requirements?
> **Secure** — Would a nefarious party find some way to exploit this change? Everything sanitized/escaped appropriately?
> **Readable** — Will your future self be able to understand this change months down the road?
> **Elegant** — Does the change fit aesthetically within the overall style and architecture?
