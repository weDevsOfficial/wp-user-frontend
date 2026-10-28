DESCRIPTION: WPUF architecture as built on `feature/react-admin-revamp`: the admin platform (container, providers, REST, stores, screens, React apps), the legacy layer that stays, how Pro plugs in, and a "where is X" map.
Read when new to the codebase, before touching the admin (lists, builders, subscriptions, settings, tools, transactions, onboarding), or before adding a hook, a route or a screen.

# Architecture

WPUF has two layers that run together:

- **Admin platform** (`includes/Platform/`, `includes/Admin/Screens/`, `includes/Admin/App/`, `src/admin/`): the FlyHR-style backend and the single React admin app. Every admin screen of the plugin runs here.
- **Legacy layer** (`wpuf.php` array container, `includes/Admin/*`, `includes/Frontend/*`, `includes/Fields/*`, `wpuf-functions.php`, templates): the site frontend, the form field types, payments, the classic Settings mode and the services the platform still calls. It is not a second admin: the classic per-page admin screens were removed (phase B of the clean-up), only their load hooks and old URLs remain, redirecting to the app.

## Boot flow

1. `wpuf.php` loads Composer, defines the constants and creates the `WP_User_Frontend` singleton (`wpuf()`).
2. `plugins_loaded` → `instantiate()`: `Bootstrap::register()` registers the seven providers in `Platform\Container` (`PluginServiceProvider`, `CoreServiceProvider`, `AdminServiceProvider`, `FrontendServiceProvider`, `StoreServiceProvider`, `RestServiceProvider`, `AiServiceProvider`; no hooks yet), then the legacy services are built **from the container** in the order and under the conditions they always were (`WP_User_Frontend::legacy( $key )`: `wpuf()->assets`, `->subscription`, `->fields`, `->customize`, `->bank`, `->paypal`, `->api`, `->integrations`, `->ai_manager`, `->post_form_block`, in wp-admin `->admin`, `->setup_wizard`, `->pro_upgrades`, `->privacy`, else `->frontend`; later `->gateway_manager` and `->ajax` on `init`, `->widgets` on `widgets_init`, `->tracker`, `->free_loader`, `->upgrades` in their own `plugins_loaded` steps). One instance serves `wpuf()->key` and `$container->get( Class::class )`; their constructors add their hooks, as always.
3. Then `wpuf()->platform_bootstrap()->boot()`: every service tagged `Hookable` gets `register_hooks()` once, then `do_action( 'wpuf_platform_loaded', $container, $bootstrap )`.
4. Pro boots after free (`wpuf_platform_loaded`, script dependencies) and adds its own providers to the same container (`wpuf-pro/includes/Platform/ProPlatformProvider.php`). Version guards both ways: Pro declares `WPUF_PRO_PLATFORM_VERSION` when its file loads; free's `Platform\VersionGuard` does not start a Pro without it (before 5.0.0: free runs alone, `wpuf_is_pro_active()` false, one notice), and Pro's `Platform_Guard` keeps Pro off with a notice when free has no `WPUF_PLATFORM_VERSION` (before 5.0.0).
5. `rest_api_init` → `Platform\REST\Manager` registers every controller once: the three frozen ones `wpuf()->api` built, every service tagged `RestRoute` (free and Pro), plus the `wpuf_rest_controllers` filter.
6. `admin_menu` → `Admin\Menu` registers the top-level page (the app) and the old submenu slugs; each old page's `load-` hook runs `Screens\Registry::load()`, which fires the screen's load step and redirects to the app route.

Rules of the container: services are built only in providers (`share_tagged`), dependencies are passed in constructors, hooks are added in `register_hooks()` (never in a platform constructor), capabilities come from `Platform\Caps` (never literal strings).

## Where is X

| I want to find... | Look in |
|---|---|
| The admin page and its hash routes | `Admin\App\AppPage` (one page `admin.php?page=wp-user-frontend`, routes from every screen's `app_routes()`, `wpuf_admin_app_routes` filter); helpers in `includes/functions/admin-app.php` |
| A screen (menu slug, load hook, route, boot data) | `includes/Admin/Screens/<Screen>.php`, registered in `Screens\Registry`; Pro screens in `wpuf-pro/includes/Platform/Screens/` |
| A REST route under `wpuf/v1` | `includes/Platform/REST/Controllers/*Controller.php` (admin routes `admin/*`, frozen routes `wpuf_form`, `wpuf_subscription*`, `settings`); the AI form builder's routes in `includes/AI/RestController.php` (feature-local); Pro routes in `wpuf-pro/includes/Platform/<Feature>/*Controller.php`. All go through `Platform\REST\Manager`; base class `Platform\REST\RestController` |
| Is AI configured, which key | `AI\Services\Provider_Settings::status()` and `::api_key_for()` (the settings screen stores `{provider}_api_key`; the older generic `ai_api_key` is read as a fallback) |
| Who may do what | `Platform\Caps` (`MANAGE_FORMS`, `MANAGE_SUBSCRIPTIONS`, `MANAGE_SETTINGS` map to `wpuf_admin_role()`; `MANAGE_SITE` to `manage_options`; `wpuf_capability` filter) |
| Reading or writing forms, fields, packs, settings, subscribers, a user's pack, a submission's form / payment / lock meta | `Platform\Stores\*Store` (through `Stores::forms()`, `::fields()`, `::subscriptions()`, `::settings()`, `::transactions()`, `::subscribers()`, `::user_packs()`, `::submissions()`); models in `Platform\Models` |
| The builder save | AJAX `wpuf_form_builder_save_form` and REST `admin/forms/{id}` both call `Builder\FormSave` → `Admin\Forms\Admin_Form_Builder::save_form()` → the stores. Builder boot data: `Builder\BuilderBoot` |
| Tools, Transactions | `Platform\Tools\ToolsService` (reset through `SettingsStore::delete_section()`, delete through `FormStore::delete_all_of_type()` / `SubscriptionStore::delete_all()`, `TransactionStore::truncate()`, listing / export / import through `FormStore` `query` + `read` + `create`; `Admin_Tools::import_json_file()` forwards to `ToolsService::import_forms()`), `Platform\Transactions\TransactionService` over `Platform\Stores\TransactionStore` (every SQL of the screen); container services; old page URLs redirect to the app routes (develop's action links are not replayed; `Admin_Tools` keeps only `import_json_file()`, `get_all_shortcodes()`, `delete_post_type()` and the JSON mime filter) |
| A pack's subscribers | REST `admin/subscriptions/{id}/subscribers` (`Platform\REST\Controllers\SubscribersController` over `Stores::subscribers()`, gateway marks from `TransactionService::gateway_logo()`), the `subscribers` view of the subscriptions app (`?action=subscribers&id=N`); the old `page=wpuf_subscribers&post_ID=N` URL redirects there (`Screens\Subscribers`) |
| Onboarding | `Admin\Onboarding` (the wizard: hooks, steps, state, step savers) delegating to `Platform\Onboarding\Pages` and `Platform\Onboarding\Plugin_Installer`; REST `OnboardingController`; screen `Screens\Onboarding` |
| Hooks of the old Vue builder | `Builder\HookBridge` (fires the kept PHP hooks into React slots), `Builder\HookDeprecations` (retired hooks fire as deprecated), guide `docs/hooks/migration-vue-to-react.md` |
| React admin assets | `Admin\Bundles` (bundles under `assets/js/screens/`, their `*.asset.php`, screen stylesheets); the legacy `WeDevs\Wpuf\Assets` keeps the full handle list and merges these in |
| A React app | `src/admin/apps/<app>/` (forms-list, form-builder, subscriptions, settings, onboarding, tools, transactions, help, welcome, ai-form-builder, the Pro promos); shared parts in `src/admin/shared/` (`ui/` is `@wpuf/components`, `api/` is `@wpuf/api`); the runtime `window.wpuf` in `src/admin/app/`. Store modules (`actions.js` / `selectors.js`) are consumed whole, so a name called through `dispatch( STORE )` stays exported (`shared/store-contract.test.js`) |
| The React frontend | `Frontend\Renderer` mounts the app for each shortcode call (no setting, no filter). The classic PHP render stays only when a theme overrides the part's templates, the call comes from the post form block or an Elementor widget (`Renderer::with_source()`), in the admin, or when the bundle is not built. Login, lost and reset password: `Frontend\Login\Login_Schema` (boot data) for the `login` app. Form data from `Frontend\Forms\Form_Schema`; REST `FormsPublicController` (schema, submissions, drafts, terms, embed, states), `AccountController` + `Account_Service` (account sections, profile, password), `UploadsController`; Pro adds `Platform\Frontend\RegistrationController` + `Registration_Service`. Source `src/frontend/` (kit, forms, account, login), built by `webpack.frontend.config.js`; submissions run the classic handlers and hooks (`wpuf()->boot_submission_hooks()`, action `wpuf_frontend_submission_hooks`) |
| Settings values and schema | `Platform\Stores\SettingsStore`, `includes/functions/settings-options.php` (the schema), `includes/functions/settings-screen.php`; REST `wpuf/v1/settings` is the only save path (the classic screen is gone; `Lib/WeDevs_Settings_API.php` stays for storage helpers and older Pro) |
| A global `wpuf_*` function | `includes/functions/<domain>.php` by what it touches (posts, users, forms, shortcodes, payments, settings, admin, helpers); all loaded by `wpuf-functions.php` |
| Pro previews on a free site | `includes/Free/*` through `Free_Loader` (Pro off only) |
| The AI form builder | `includes/AI/RestController.php` on `Platform\REST\RestController` (routes, argument schemas, permissions `can_use_ai()` / `Caps::MANAGE_SITE`, request reading, the builder's `wpuf_ai_generate_field_options` AJAX action) over `includes/AI/Services/` (`Generation`, `Provider_Settings`, `Field_Options`, `Form_Writer`: plain arguments, arrays or `WP_Error` back). Persistence: `Form_Writer` through `FormStore` (`create` with `meta`, `update`, `read_settings` / `write_settings`, `read_meta` / `write_meta`) and `FieldStore::replace()`; the `wpuf_ai` section through `SettingsStore`. `AI\FormGenerator` and `AI\Config` (the provider client and model lists) stay |
| Stray output in front of a JSON answer | `Platform\Http\JsonOutputGuard` (buffers `wpuf/v1` REST and `wpuf_*` AJAX requests from `plugins_loaded`, drops what other code printed before the JSON, logs it under WP_DEBUG); client side `src/admin/shared/api/parse.js` (`parseJsonBody`, published as `@wpuf/api`) |

## Folder map (PHP)

```
includes/Platform/            the platform: Container, ServiceProvider, Bootstrap, Caps, VersionGuard, Http/JsonOutputGuard,
                              Providers/ (Plugin, Core, Admin, Frontend, Store, Rest, Ai)
  Contracts/                  Hookable, RestRoute, DataStore, App_Renderer, Form_Schema_Provider, Rate_Limiter
                              (the last three are what Pro / add-ons resolve from the container)
  Providers/                  Plugin, Core, Admin, Frontend, Store, Rest, Ai service providers (Admin shares the admin layer's 16 services, Frontend the frontend layer's 8 and the 6 AJAX handler classes: `Ajax::service()` and `Frontend` take them from the container)
  REST/                       Manager, RestController (base), Controllers/*
  Stores/                     FormStore, FieldStore, SubscriptionStore, SettingsStore, TransactionStore, SubscriberStore,
                              UserPackStore, SubmissionStore, Normalizers, Stores
  Models/                     Form, SubscriptionPack
  Onboarding/                 Pages, Plugin_Installer, State (the wizard's own options and the activation redirect)
  Tools/, Transactions/       ToolsService, TransactionService
includes/Admin/Screens/       one class per admin screen + Registry, Screen (base), PrintsNotices
includes/Admin/App/           AppPage (the one admin page)
includes/Builder/             BuilderBoot, FormSave, HookBridge, HookDeprecations
includes/Admin/               legacy admin services still in use, all built by the platform container: Menu (the WP
                              admin_menu adapter, every callback renders through Screens\Registry / AppPage), Admin_Tools
                              (handlers through ToolsService), Onboarding facade, Posting (metaboxes through
                              SubmissionStore), Customizer_Options (Customizer bridge), Upgrades, Help_Content,
                              views/ for Premium / shortcode builder / help content
includes/Admin/Subscriptions/ the subscription packs admin behind two facades (hooks + delegators, statics stay):
                              Admin_Subscription -> Pack_Fields (pack editor schema), Pack_Screen (React screen boot,
                              classic CPT screens redirected to it, sort order migration through SubscriptionStore), User_Profile
                              (a user's pack on the profile screen); Admin\Subscription -> Pack_Post_Type (the CPT,
                              registration-form pack flow), Payment_Flow (pending posts, payment received,
                              new subscription, cancel, trial reset; through the Submission / UserPack / Subscriber /
                              Transaction stores), Pack_Display (packs page, pack details, dropdowns, force-pack notices).
                              User_Subscription (includes/) reads and writes a user's pack through the same stores.
includes/AI/                  AI form builder (Config, FormGenerator, prompts, its RestController, Services/)
includes/Frontend/, Fields/, Integrations/   the site frontend, field types, third-party integrations
includes/Free/                free plugin only: Free_Loader (hooks, delegating facade) + Settings_Preview, Modules_Preview,
                              Form_Settings_Preview, Subscription_Preview, Promo_Pages, Pro_Prompt, Edit_Profile, Simple_Login
wpuf-functions.php            loader of the global functions (public API): includes/functions/{helpers,settings,posts,users,
                              forms,shortcodes,payments,admin}.php, plus modules.php and admin-app.php; the file-scope hooks
                              and the two Walker classes stay in the loader
```

Pro mirrors this under `wpuf-pro/includes/Platform/<Feature>/` (Coupons, License, Modules, Settings, Subscriptions, Builder, Screens), each with its provider; `Platform/ProServiceProvider` shares every Pro service the bootstraps used to build with `new` (reached through `Platform_Guard::make()`: the container's shared instance, else `new`); Pro reads and writes free data through free's `Stores::*()` and resolves free services by contract (`App_Renderer`, `Form_Schema_Provider`, `Rate_Limiter`); `Platform/Content/RestrictionStore` holds the content restriction meta.

## Non-negotiables

1. **Stored data is frozen.** Same CPTs, meta keys, option keys and value shapes as develop; an untouched save changes nothing.
2. **WPUF fires its own hooks.** A React screen still calls every PHP hook the old screen called, at the equivalent point, with the same arguments.
3. **Modules are not edited.** Pro modules and User Directory keep their code.
4. **Retired hooks are deprecated, never deleted silently.**
5. **Old URLs keep working**: every old admin URL redirects to its app route after its load step ran.
6. **Old names stay only while something can use them**: a renamed class or an unused function that Pro (current or released) or outside code can name keeps a deprecated stub (`class_alias`, `_deprecated_function()`); one nothing can reach is deleted outright (owner rule, 2026-10-09: the `includes/Api/` stubs, `Admin\Assets`, the `Post\Templates\Form_Template` handler stub and five admin-only helpers went that way).
7. **Frontend output is identical** to develop.

## Verification

- PHPUnit (`tests/php/`): platform container and REST, every `wpuf/v1` route refuses visitors and subscribers, stores, onboarding, tools, transactions, builder boot.
- Playwright (`tests/e2e/`): the app, every screen, the frontend; parity suite against a develop site (`tests/e2e/parity/`).
- Contract dump (QA script `contract-dump.php`): REST routes, AJAX actions and shortcodes compared before and after a refactor.
