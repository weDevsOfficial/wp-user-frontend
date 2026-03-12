# WP User Frontend - CLAUDE.md

## Project Overview

WP User Frontend (WPUF) is a frontend content management plugin for WordPress. It allows users to create, edit, and delete posts, pages, or custom post types from the frontend — without ever accessing the WordPress admin. Version 4.2.10. Requires PHP 5.6+.

Key capabilities: frontend post submission forms, user registration/login forms, frontend user dashboard, subscription/payment plans, guest posting, content restriction, and AI-powered form building.

## Available Skills

The `.claude/skills/` directory contains procedural HOW-TO instructions:

- **`wpuf-backend-dev`** — Backend PHP conventions: namespaces, container, hooks, REST controllers. **Invoke before writing any PHP code or tests.**
- **`wpuf-dev-cycle`** — Testing and linting workflows (PHPCS, Playwright E2E)
- **`wpuf-frontend-dev`** — Frontend conventions: Vue 3, Vite, Tailwind CSS, Pinia stores
- **`wpuf-code-review`** — Code review standards: critical violations to flag, PR checklist verification, severity levels
- **`wpuf-git`** — Git and GitHub operations: branching, PR templates, CI checks

## Build & Development Commands

```bash
# Frontend (Vite + Vue 3)
npm run build                        # Production build (all entry points)
npm run build:forms-list             # Build forms list module
npm run build:subscriptions          # Build admin subscriptions module
npm run build:frontend-subscriptions # Build frontend subscriptions module
npm run build:ai-form-builder        # Build AI form builder module
npm run build:account                # Build account module
npm run build:user-directory         # Build user directory module
npm run build:css                    # Compile Tailwind CSS via Grunt
npm run dev:user-directory           # Dev mode for user directory module

# Frontend (Grunt — legacy)
grunt release                        # Full release build

# PHP
composer phpcs                       # PHP CodeSniffer
composer phpcbf                      # Auto-fix PHP code style
```

## Architecture

### Entry Point
- `wpuf.php` — Main plugin file, defines constants, loads autoloader, bootstraps `WP_User_Frontend` singleton

### Initialization Flow
1. `wpuf.php` loads Composer autoloader (`vendor/autoload.php`)
2. Defines constants (`WPUF_VERSION`, `WPUF_FILE`, `WPUF_ROOT`, etc.)
3. Creates `WP_User_Frontend` singleton via `wpuf()` helper
4. Manually includes `wpuf-functions.php`, `Frontend_Render_Form`, reCaptcha libs, AI manager, and gateway helpers
5. On `plugins_loaded`: loads insights tracker, free/pro loader, upgrades, and instantiates all services into `$this->container[]`
6. Services available via magic getter: `wpuf()->service_name`

### Directory Structure

```
wp-user-frontend/
├── wpuf.php                # Main plugin file & bootstrap class
├── wpuf-functions.php      # Global utility functions (6,782 lines)
├── includes/               # PHP backend (166 files)
│   ├── Admin/              # Admin settings, menus, subscription management
│   ├── Ajax/               # AJAX handlers (forms, uploads, addresses)
│   ├── AI/                 # AI form builder (config, prompts, REST)
│   ├── Api/                # REST API controllers (FormList, Subscription)
│   ├── Fields/             # Form field types (20+ field classes)
│   ├── Free/               # Free version functionality & pro prompts
│   ├── Frontend/           # Frontend forms, dashboard, registration, payments
│   ├── Hooks/              # Form settings cleanup hooks
│   ├── Integrations/       # Third-party integrations (ACF, Dokan, WC Vendors, n8n)
│   ├── Traits/             # Shared traits (FieldableTrait, TaxableTrait)
│   ├── Widgets/            # WordPress widgets
│   ├── Data/               # Data layer classes
│   ├── Log/                # Logging utilities
│   ├── upgrades/           # Version migration scripts
│   └── Abstracts/          # Abstract base classes
├── src/                    # Vue 3 source (17 files)
│   ├── admin/              # Admin panel components
│   ├── components/         # Shared Vue components
│   ├── css/                # Component styles
│   ├── js/                 # JavaScript modules
│   └── router/             # Vue Router config
├── admin/                  # Legacy admin PHP files & form builder
│   ├── form-builder/       # Drag-and-drop form builder (PHP + JS)
│   └── html/               # Admin HTML templates
├── form-builder/           # Form builder assets
├── templates/              # Overridable PHP templates
│   ├── dashboard/          # User dashboard templates
│   ├── subscriptions/      # Subscription plan templates
│   ├── dokan/              # Dokan marketplace integration templates
│   ├── wc-marketplace/     # WC Marketplace integration templates
│   └── *.php               # Login, registration, account, post submission
├── assets/                 # Compiled CSS/JS output
│   ├── css/                # Compiled stylesheets
│   ├── js/                 # Compiled JS bundles
│   ├── images/             # Static images
│   ├── less/               # LESS source files (legacy)
│   └── vendor/             # Third-party vendor assets
├── Lib/                    # Third-party libraries
│   ├── Gateway/            # Payment gateways (PayPal, Bank)
│   ├── Appsero/            # License & analytics
│   └── *.php               # reCaptcha, settings API, insights
├── modules/                # Self-contained feature modules
│   └── user-directory/     # User directory module (own npm project)
├── tests/
│   └── e2e/                # Playwright E2E tests
├── views/                  # Legacy PHP view files
├── class/                  # Legacy class files (subscription, render-form)
├── config/                 # Configuration files
└── languages/              # Translation files
```

### Key PHP Files
- `wpuf-functions.php` — Global utility functions (6,782 lines, the largest file)
- `includes/Assets.php` — Script/style registration and enqueuing
- `includes/Frontend_Render_Form.php` / `includes/Render_Form.php` — Core form rendering engine
- `includes/User_Subscription.php` — Subscription plan management
- `includes/Installer.php` — Plugin activation, DB table creation
- `includes/API.php` — REST API registration
- `includes/AI_Manager.php` — AI form builder orchestration

### Service Container
Simple array-based container with magic `__get()`. Services accessed via `wpuf()->service_name`. Major services:
`assets`, `subscription`, `fields`, `customize`, `bank`, `paypal`, `gateway_manager`, `api`, `integrations`, `ai_manager`, `admin`, `frontend`, `ajax`, `setup_wizard`, `pro_upgrades`, `privacy`, `widgets`, `tracker`, `free_loader`, `upgrades`

### Frontend Architecture
- **Vue 3** (primary) for modern admin and frontend components, with Pinia state management and Vue Router
- **jQuery** (legacy) for form builder and older frontend interactions
- **Vite** as build tool with 5 entry points (forms-list, subscriptions, frontend-subscriptions, ai-form-builder, account)
- **Grunt** for legacy build tasks (LESS compilation, Tailwind CSS, release packaging)
- **Tailwind CSS 3** with DaisyUI component library and scoped preflight
- **LESS** (legacy) for older stylesheets

### REST API
2 controllers under `includes/Api/` — `FormList` and `Subscription`. AI features add an additional REST controller via `includes/AI/RestController.php`.

### Payment System
Payment gateways managed through `Lib/Gateway/`:
- `Gateway_Manager.php` — Gateway registration and orchestration
- `Paypal_Gateway.php` / `Paypal.php` — PayPal integration
- `Bank_Gateway.php` / `Bank.php` — Bank transfer support
- Frontend payment handling via `includes/Frontend/Payment.php`

### Form Fields System
20+ form field types under `includes/Fields/`, each extending `Field_Contract.php`:
Text, Textarea, Email, URL, Dropdown, MultiDropdown, Checkbox, Radio, Image, Featured Image, Post Title, Post Content, Post Excerpt, Post Tags, Post Taxonomy, Hidden, HTML, Section Break, Column, reCaptcha, Cloudflare Turnstile

## Coding Standards

- **PHP**: WordPress Coding Standards (WPCS) via PHPCS
- **JS**: JSHint (legacy), Prettier for formatting
- **CSS**: Tailwind CSS 3 with DaisyUI
- **PHP Namespace**: `WeDevs\Wpuf\` for `includes/`, `WeDevs\Wpuf\Lib\` for `Lib/`

## Key Patterns

- **Singleton pattern** via `WeDevs\WpUtils\SingletonTrait` for main plugin class
- **Simple container** — array-based `$container[]` with magic `__get()` (not a formal DI container)
- **WordPress hooks** extensively used for extensibility (actions & filters)
- **Overridable templates** — themes can override templates from `templates/` directory
- **Free/Pro split** — `includes/Free/Free_Loader.php` loads free-only features; Pro version detected via `WP_User_Frontend_Pro` class
- **Form field contract** — all form fields implement `Field_Contract` for consistent rendering
- **Legacy + modern coexistence** — jQuery form builder alongside Vue 3 components, LESS alongside Tailwind, Grunt alongside Vite
- **Third-party integrations** — Dokan, WC Vendors, WC Marketplace, ACF, n8n, Events Calendar

## Testing

- **Playwright** for E2E tests in `tests/e2e/`
  - Multiple config files for parallel execution (`playwright.parallel-one.config.ts`, `playwright.parallel-two.config.ts`)
  - Setup config: `playwright.setup.config.ts`
- **PHPUnit 7.5.9** listed as dev dependency (test infrastructure in development)

## Integrations

WPUF integrates with several marketplace and third-party plugins:
- **Dokan** — Vendor registration forms and dashboard integration
- **WC Vendors** — Vendor frontend forms
- **WC Marketplace** — Marketplace compatibility
- **ACF** — Advanced Custom Fields compatibility
- **n8n** — Workflow automation integration
- **Events Calendar** — Event post type support
