---
name: wpuf-backend-dev
description: Add or modify WPUF backend PHP code on the v5.0.0 platform (container + service providers, Stores, REST controllers on Platform\REST\RestController with Caps, admin Screens, frontend Renderer / Form_Schema, hook bridges, deprecation shims) following project conventions. Use when creating or changing classes, services, REST routes, stores, hooks, screens or any backend PHP.
---

# WPUF Backend Development

How to write PHP for WP User Frontend as built on `feature/react-admin-revamp` (release 5.0.0). Read `docs/architecture.md` (boot flow, "where is X" map) before touching a subsystem you do not know, and `docs/hooks/php.md` before adding, moving or retiring a hook.

## When to use

Invoke before you:

-   add or change a PHP class, service, store, REST route, AJAX handler or admin screen
-   add, move or retire a `do_action` / `apply_filters` call
-   touch the frontend render path (shortcodes, form schema, submissions)
-   add Pro code that talks to free (see "Pro" below)

## Non-negotiables (owner rules)

1.  **Stored data is frozen.** Same CPTs, meta keys, option keys and value shapes as develop; an untouched save changes nothing. Toggles stay `'on'` / `'off'`, absent keys stay absent.
2.  **WPUF fires every hook it owns.** A React screen or REST route still calls every PHP hook the old screen called, at the equivalent point, same arguments, same order.
3.  **Retired hooks and removed public API get deprecation shims**, never a silent delete (see "Deprecations").
4.  **Pro modules and `modules/user-directory` are not edited** to fix a core regression; fix core.
5.  **Old admin URLs keep working**: they redirect to their app route after their load step ran.
6.  **Frontend output stays identical** to develop for the classic render paths.
7.  **Scope discipline**: no drive-by refactors; note unrelated issues separately.

## Layers at a glance

| Layer | Where | Notes |
|---|---|---|
| Platform container, providers, boot | `includes/Platform/{Container,ServiceProvider,Bootstrap}.php`, `includes/Platform/Providers/*` | Seven free providers: Plugin, Core, Admin, Frontend, Store, Rest, Ai |
| Contracts | `includes/Platform/Contracts/` | `Hookable`, `RestRoute`, `DataStore`, `App_Renderer`, `Form_Schema_Provider`, `Rate_Limiter` |
| Capabilities | `includes/Platform/Caps.php` | `MANAGE_FORMS`, `MANAGE_SUBSCRIPTIONS`, `MANAGE_SETTINGS` (map to `wpuf_admin_role()`), `MANAGE_SITE` (`manage_options`); filter `wpuf_capability` |
| Stores (all storage access) | `includes/Platform/Stores/*`, facade `Stores::forms()`, `::fields()`, `::subscriptions()`, `::settings()`, `::transactions()`, `::subscribers()`, `::user_packs()`, `::submissions()`, `::users()` | Models in `Platform/Models` |
| REST | `includes/Platform/REST/{Manager,RestController,Rate_Limit}.php`, `Controllers/*Controller.php` | Namespace `wpuf/v1`; AI keeps `includes/AI/RestController.php` |
| Domain services | `Platform/Tools/ToolsService`, `Platform/Transactions/TransactionService`, `Platform/Onboarding/*` | Built in providers, injected into controllers |
| Admin app + screens | `includes/Admin/App/AppPage.php`, `includes/Admin/Screens/*` (+ `Registry`), `includes/Admin/Bundles.php` | One page `admin.php?page=wp-user-frontend`, hash routes |
| Builder | `includes/Builder/{BuilderBoot,FormSave,HookBridge,HookDeprecations}.php` | Save path shared by AJAX and REST |
| Frontend React | `includes/Frontend/Renderer.php`, `Frontend/Forms/{Form_Schema,Submission_Service,Submission_Validator,Upload_Service}.php`, `Frontend/Login/Login_Schema.php`, `Frontend/Account/Account_Service.php`, `Frontend/Bundles.php` | Classic templates stay where required |
| Classic hook bridge | `includes/Platform/Classic/Hook_Fields.php` | Fields other plugins printed through classic form hooks |
| Version guards | `includes/Platform/VersionGuard.php` (free), `wpuf-pro/includes/Platform_Guard.php` (Pro) | `WPUF_PLATFORM_VERSION` / `WPUF_PRO_PLATFORM_VERSION` |
| Global functions | `includes/functions/<domain>.php`, loaded by `wpuf-functions.php` | Public API: never drop or rename |
| Legacy layer | `includes/Admin/*`, `includes/Frontend/*`, `includes/Fields/*`, `Lib/Gateway/*`, `templates/` | Built from the container, still adds its hooks in constructors |

## Boot flow (what runs when)

1.  `wpuf.php` loads Composer, defines constants (incl. `WPUF_PLATFORM_VERSION`) and creates the `WP_User_Frontend` singleton (`wpuf()`).
2.  `plugins_loaded` → `instantiate()`: `Bootstrap::register()` registers the providers (no hooks yet), then each legacy service is built **from the container** under its old key (`wpuf()->assets`, `->subscription`, `->admin`, `->frontend`, ...). One instance serves both `wpuf()->key` and `wpuf()->platform()->get( Class::class )`.
3.  `wpuf()->platform_bootstrap()->boot()`: `register_hooks()` once on every service tagged `Hookable`, then `do_action( 'wpuf_platform_loaded', $container, $bootstrap )`.
4.  Pro attaches its providers on `wpuf_platform_loaded` (`ProPlatformProvider::attach()`), then fires `wpuf_pro_platform_loaded`.
5.  `rest_api_init` → `Platform\REST\Manager` registers every controller once: the frozen ones from `wpuf()->api`, every service tagged `RestRoute`, plus the `wpuf_rest_controllers` filter.

## Container and service providers

Rules (from `docs/architecture.md`): services are built only in providers, dependencies go in constructors, hooks are added in `register_hooks()` (never in a platform constructor), capabilities come from `Caps`.

Register a service in the provider that owns its area (`includes/Platform/Providers/RestServiceProvider.php`):

```php
// wpuf/v1/admin/transactions (User Frontend > Transactions).
$this->share_tagged(
    TransactionsController::class,
    function ( $container ) {
        return new TransactionsController( $container->get( TransactionService::class ) );
    }
);
```

-   `share_tagged()` shares one instance and tags it with every interface it implements, so a `Hookable` gets `register_hooks()` and a `RestRoute` gets registered by the REST manager automatically. `bind_tagged()` builds a new instance each `get()`.
-   Resolve a service: `wpuf()->platform()->get( ToolsService::class )` (see `includes/Admin/Screens/Tools.php::app_globals()`).
-   Pro and add-ons resolve free services **by contract**, not by class: `wpuf()->platform()->get( Rate_Limiter::class )`, `App_Renderer::class`, `Form_Schema_Provider::class` (aliases set in `FrontendServiceProvider::register_frontend()`).
-   `wpuf()->service_name` still works for the legacy keys (`PluginServiceProvider::SERVICES`), but new code does not add keys to the array container.

A hookable service (`includes/Platform/VersionGuard.php`):

```php
class VersionGuard implements Hookable {

    public function register_hooks() {
        add_action( 'admin_enqueue_scripts', [ $this, 'skip_legacy_pro_builder' ], 999 );
        add_action( 'admin_notices', [ $this, 'pro_update_notice' ] );
    }
}
```

## Stores: the only storage access

REST controllers, screens and services read and write through the stores. Do not add new `get_post_meta` / `update_option` / raw `$wpdb` calls for data a store already owns; add a store method instead (keeping develop's stored shape).

```php
use WeDevs\Wpuf\Platform\Stores\Stores;

$settings = Stores::forms()->read_settings( $form_id );    // FormsController.php
Stores::forms()->write_settings( $form_id, $settings );

$form_id  = Stores::submissions()->form_id( $post_id );   // wpuf-pro Pricing_Field_Payment.php
$value    = Stores::settings()->get( 'wpuf_general', 'key', '' );
```

-   `DataStore` (read side: `exists()`, `read()`, `find()`, `query()`, `count()`) is implemented by the entity stores; writes stay store specific (`FormStore::save()`, `create()`, `duplicate()`, `trash()`, `restore()`, `delete_permanently()`, `write_meta()`; `SettingsStore::save()`, `save_section()`, `write_section()`, `set_value()`).
-   Store hooks follow the Dokan pattern: existing `wpuf_before_form_store_save` / `wpuf_after_form_store_save` (`FormStore::save()`); new REST / store hooks are named `wpuf_rest_prepare_{resource}`, `wpuf_rest_{resource}_query`, `wpuf_rest_insert_{resource}`, `wpuf_before|after_{store}_save` (`docs/hooks/php.md`).
-   Settings: REST `wpuf/v1/settings` (`SettingsController` → `SettingsStore`) is the only save path; schema in `includes/functions/settings-options.php`. `wpuf_get_option()` still reads options and stays public API.
-   SQL lives in stores (`TransactionStore`, `SubscriberStore`, ...) and always uses `$wpdb->prepare()` plus allowlisted `ORDER BY` columns.

## REST controllers

Controllers live in `includes/Platform/REST/Controllers/` (Pro: `wpuf-pro/includes/Platform/<Feature>/*Controller.php`), extend `WeDevs\Wpuf\Platform\REST\RestController` (which extends `WP_REST_Controller` and implements `RestRoute`) and are registered by sharing them in a provider. There is no `API::__construct()` step and no `includes/Api/` folder any more; `includes/API.php` only builds the three frozen controllers (`FormListController`, `SubscriptionController`, `SettingsController`) for `wpuf()->api`.

Route placement: new admin routes under `wpuf/v1/admin/*`; frontend routes are `wpuf/v1/forms/{id}/*`, `account*`, `login*`, `auth/*`, `uploads*`. Frozen routes (`wpuf_form`, `wpuf_subscription*`, `settings`) keep their paths, args, permissions and responses.

What the base gives you (`includes/Platform/REST/RestController.php`):

| Member | Use |
|---|---|
| `$namespace = 'wpuf/v1'`, `$resource` | `$resource` builds error codes `wpuf_{resource}_{reason}` |
| `permission( Caps::X )` | Permission callback: logged out 401 `wpuf_rest_unauthorized`, missing cap 403 `wpuf_rest_forbidden` |
| `route( $path, $methods, $callback, $permission, $args )` | One-line `register_rest_route()` |
| `collection_args( $orderby, $default, $options )` | Standard list args: `page`, `per_page` (1-100), `orderby` enum, `order`, `search` |
| `paginate( $items, $total, $per_page )` | Response with `X-WP-Total` / `X-WP-TotalPages` |
| `error( $reason, $message, $status )` | `WP_Error` with `[ 'status' => N ]` |
| `cast_int()`, `cast_bool()`, `cast_string()`, `cast_array()` | Typed casts of request values |
| `account_attempt_allowed( $limiter, $key, $account, $limit )` | Login / password rate limit per account and per address |

Admin route (`TransactionsController.php`):

```php
class TransactionsController extends RestController {

    protected $rest_base = 'admin/transactions';
    protected $resource  = 'transactions';

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => $this->permission( Caps::MANAGE_SUBSCRIPTIONS ),
                    'args'                => [
                        'gateway' => [
                            'type'              => 'string',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ] + $this->collection_args( TransactionService::ORDERBY, 'created', [ 'per_page' => null ] ),
                ],
            ]
        );
    }
}
```

Per-object checks: a client-supplied id is checked for type and object capability, not only the screen cap (`FormsController::form_permission()`):

```php
$allowed = call_user_func( $this->permission( Caps::MANAGE_FORMS ) );

if ( true !== $allowed ) {
    return $allowed;
}

if ( ! $this->forms->is_form( $form_id ) ) {
    return $this->error( 'not_found', __( 'Invalid form id', 'wp-user-frontend' ), 404 );
}

if ( post_type_exists( get_post_type( $form_id ) ) && ! current_user_can( $meta_cap, $form_id ) ) {
    return $this->error( 'forbidden', __( 'Sorry, you are not allowed to edit this form.', 'wp-user-frontend' ), 403 );
}
```

Permission rules:

-   Admin routes: `$this->permission( Caps::MANAGE_* )`. Destructive or site-wide actions (payment accept / reject / delete, import, settings save, Tools danger zone, onboarding) need `Caps::MANAGE_SITE`.
-   Never a literal capability string in new code, never `__return_true` on anything that reads non-public data or writes.
-   Public-by-design routes (login, lost password, guest form schema, guest submissions) stay public but validate input, check the form allows guests (`FormsPublicController::can_read()` checks `post_permission === 'guest_post'`) and are rate limited where abuse is cheap.
-   Responses: `rest_ensure_response( $data )` on success, `$this->error()` / `WP_Error` with a status on failure. Frozen controllers keep their old `{ success, data }` shapes.

### Rate limits on public routes

`Platform\Contracts\Rate_Limiter` (implementation `Platform\REST\Rate_Limit`, transients keyed by user id or IP, filters `wpuf_rate_limit`, `wpuf_rate_limit_window`, `wpuf_rate_limit_client_address`). Inject it through the provider and return 429:

```php
// LoginController::sign_in()
if ( ! $this->account_attempt_allowed( $this->limiter, 'login_form', $request->get_param( 'log' ), 10 ) ) {
    return $this->too_many();
}

// wpuf-pro CouponsPublicController::apply()
if ( ! $this->limiter->allow( 'coupon_apply', 20 ) ) {
    return $this->error( 'too_many', __( 'Too many attempts. Please wait a minute and try again.', 'wpuf-pro' ), 429 );
}
```

Applied today on login / lost / reset password, `auth/*`, Pro registration username check and coupon apply. `FormsPublicController` and `UploadsController` receive the limiter but do not apply it yet (parity with the classic path); a new public route that sends mail, calls a paid API or creates content must apply one.

## Admin screens

Each admin screen is a class in `includes/Admin/Screens/` extending `Screen`, added in `Screens\Registry::__construct()` (Pro / add-ons call `Registry::add()` on `wpuf_platform_loaded`). It declares its app route(s), the load step that still fires the old `load-` hooks, and the boot globals (`includes/Admin/Screens/Tools.php`):

```php
class Tools extends Screen {

    public function slug() {
        return 'wpuf_tools';
    }

    public function app_routes() {
        return [
            [
                'id'        => 'tools',
                'path'      => '/tools',
                'title'     => __( 'Tools', 'wp-user-frontend' ),
                'app'       => 'tools',
                'in_app'    => true,
                'container' => 'wpuf-tools-root',
            ],
        ];
    }

    public function load_in_app() {
        do_action( 'wpuf_load_tools' );   // the hook the old page fired

        wp_enqueue_script( 'wpuf-tools' );
    }

    public function app_globals() {
        return [
            'wpufTools' => [
                'canManageSite' => Caps::can( Caps::MANAGE_SITE ),
            ],
        ];
    }
}
```

The bundle handle is registered in `Admin\Bundles::scripts()` from the built `assets/js/screens/<name>.js` + `.asset.php`; every React handle gets `wp_set_script_translations( $handle, 'wp-user-frontend', WPUF_ROOT . '/languages' )`.

## Frontend render path

-   `Frontend\Renderer` (implements `App_Renderer`) decides per shortcode call whether a part (`post_form`, `edit_form`, `registration`, `profile`, `login`, `lost_password`, `reset_password`, `plans`, `checkout`, `plan_info`, `account`) mounts React. The classic PHP render stays when a theme overrides one of the part's templates (`Renderer::PARTS`), the call comes from the post form block or Elementor (`Renderer::with_source()`), in wp-admin, or when the bundle is not built.
-   Mount markup is `<div class="wpuf-frontend" data-wpuf-mount="<app>"><script type="application/json" class="wpuf-boot">...</script></div>` (`Renderer::markup()`), JSON encoded with the `JSON_HEX_*` flags. Never print boot data any other way.
-   `Frontend\Forms\Form_Schema` (implements `Form_Schema_Provider`) turns what `Frontend_Render_Form::render_form()` decided at render time into data, including the markup printing hooks produce (`slots`), captured in classic order with classic args. Wrap hook-firing builds in `Renderer::build( $part, $callback )`.
-   Submissions over REST (`FormsPublicController` → `Submission_Service` → `Post_Form_Handler`, also the account profile save and Pro registration) go through `Platform\Http\Submission_Context::run()`: it rebuilds the admin-ajax request the classic handlers, field classes and third-party submit hooks (`wpuf_add_post_after_insert`, `wpuf_edit_post_after_update`, `wpuf_after_register`, Dokan, WooCommerce) expect, turns their JSON exits into values (`Ajax_Abort`) and restores state afterwards. It calls `wpuf()->boot_submission_hooks()` (fires `wpuf_frontend_submission_hooks` once) so admin-layer listeners exist outside wp-admin. Never call a classic submit handler from REST without it.
-   `Submission_Validator::validate( $fields, $posted, $form_id )` repeats the browser checks (required, e-mail, URL, number) server-side for fields the visitor certainly saw. New field types with client validation need a server rule there.

## Hooks and bridges

-   **Adding a hook**: `wpuf_` prefix, snake_case, `wpuf_{context}_{action}`; docblock with `@since WPUF_SINCE` and every `@param`; pass only data the current user may see; regenerate the index: `php tests/contracts/static-hooks.php --md . > docs/hooks/php-index.md`.
-   **Changing a hook**: never rename, reorder, retype or move it to a point with different state; add arguments only at the end.
-   **Builder hooks** the Vue builder printed: `Builder\HookBridge` fires `wpuf_before|after_{post,registration}_form_settings_field[_{key}]` and the tab hooks with develop's args, captures third-party output (sanitized with `HookBridge::allowed_html()`) per slot, and saves `wpuf_settings[...]` inputs via `merge_posted_settings()`. `Builder\HookDeprecations` fires the retired Vue-only hooks as deprecated.
-   **Classic edit screen hooks** (coupon, subscription pack): `Platform\Classic\Hook_Fields::render( $post, $fire )` fires them with the global `$post` set and returns the sanitized markup; `with_post_data( $values, $save )` puts the posted inputs back in `$_POST` while the store saves, so `save_post` listeners see them. The React side renders it with `ClassicFields` from `@wpuf/components`.

## Deprecations

Old names stay only while something can use them (owner rule): a renamed class or an unused function Pro (current or released) or outside code can name keeps a stub; one nothing can reach is deleted.

```php
// Method or function (includes/User_Subscription.php)
/**
 * @deprecated WPUF_SINCE Use \WeDevs\Wpuf\Admin\Subscriptions\User_Profile::remove_user_pack().
 */
public function delete_pack() {
    _deprecated_function( __METHOD__, 'WPUF_SINCE', '\\WeDevs\\Wpuf\\Admin\\Subscriptions\\User_Profile::remove_user_pack()' );

    \WeDevs\Wpuf\Admin\Subscriptions\User_Profile::remove_user_pack( $this->user->id );
}

// Renamed class (includes/Ajax/Upload_Ajax.php)
/**
 * @deprecated WPUF_SINCE Use \WeDevs\Wpuf\Frontend\Forms\Upload_Handler.
 */
class_alias( \WeDevs\Wpuf\Frontend\Forms\Upload_Handler::class, __NAMESPACE__ . '\\Upload_Ajax' );
```

Hooks: keep firing through `apply_filters_deprecated( $name, $args, WPUF_SINCE, $replacement )` / `do_action_deprecated()`, show a one-time admin notice when a non-WPUF callback listens, document it in `docs/hooks/migration-vue-to-react.md` and the changelog, keep the shim at least one major release.

## Pro

-   Pro registers providers on `wpuf_platform_loaded` (`wpuf-pro/includes/Platform/ProPlatformProvider.php::attach()`); never depend on plugin folder order.
-   Pro reads and writes free data through free's `Stores::*()` and resolves free services by contract.
-   `Platform_Guard::make( Class::class )` returns the container's shared instance, else `new`; `Platform_Guard::free_is_ready()` keeps Pro off when free has no `WPUF_PLATFORM_VERSION` (free before 5.0.0). Free's `VersionGuard` does the reverse for a Pro without `WPUF_PRO_PLATFORM_VERSION`.
-   Free never assumes Pro: `wpuf_is_pro_active()` / `class_exists( 'WP_User_Frontend_Pro' )`; free-only previews live in `includes/Free/*` through `Free_Loader`.
-   Placeholders: `@since WPUF_SINCE` / `@deprecated WPUF_SINCE` in free, `@since WPUF_PRO_SINCE` in Pro; text domain `wpuf-pro` in Pro.

## Commands

```bash
vendor/bin/phpcs -q -n path/to/changed.php                    # PHPCS, errors only; CI lints whole changed files
vendor/bin/phpcbf path/to/changed.php                         # auto-fix
WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter FormsRestTest
WPUF_TESTS_WITH_PRO=1 WP_TESTS_DIR=$HOME/Sites/parity-db/wp-tests-lib vendor/bin/phpunit --filter PlatformRestTest
php tests/contracts/static-hooks.php <free-dir> <pro-dir>     # hook call sites + arg counts vs develop
php tests/contracts/diff.php <develop-dir> <branch-dir>       # runtime hooks lost (must be 0)
```

PHPUnit suites live in `tests/php/tests/*Test.php` (bootstrap `tests/php/bootstrap.php`; `composer test:install` creates a test library when you have none). Run the tests related to your change, not the whole suite, while iterating.

## Code Review Prevention Checklist

**MANDATORY: apply every rule below before submitting code. These are the top causes of review rejections.**

### 1. Strict comparisons

Never `==` / `!=`; always `===` / `!==`.

```php
// WRONG
if ( $value == 'yes' ) {}
if ( 0 == $count ) {}

// CORRECT
if ( 'yes' === $value ) {}
if ( 0 === $count ) {}
```

### 2. `in_array()` / `array_search()` strict

Always pass `true` as the third argument (PHPCS error otherwise).

```php
in_array( $value, $allowed, true );
array_search( $key, $list, true );
```

### 3. Superglobals: `wp_unslash()` then sanitize

```php
$title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
$id    = absint( wp_unslash( $_GET['id'] ?? 0 ) );
$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
$url   = esc_url_raw( wp_unslash( $_POST['redirect'] ?? '' ) );
$tab   = sanitize_key( wp_unslash( $_GET['tab'] ?? '' ) );
```

| Data type | Function |
|---|---|
| Text | `sanitize_text_field( wp_unslash( ... ) )` |
| Textarea | `sanitize_textarea_field( wp_unslash( ... ) )` |
| Email | `sanitize_email( wp_unslash( ... ) )` |
| URL | `esc_url_raw( wp_unslash( ... ) )` |
| Integer | `absint()` / `intval( wp_unslash( ... ) )` |
| HTML | `wp_kses_post( wp_unslash( ... ) )` |
| Key / slug | `sanitize_key( wp_unslash( ... ) )` |

REST: declare `type`, `enum`, `pattern`, `minimum` and `sanitize_callback` in the route `args` so WordPress validates before your callback runs.

### 4. Escape all output

| Context | Function |
|---|---|
| HTML text | `esc_html()`, `esc_html__()`, `esc_html_e()` |
| Attribute | `esc_attr()`, `esc_attr__()` |
| URL | `esc_url()` |
| JS string | `esc_js()` |
| Rich HTML | `wp_kses_post()` (or a narrow allowlist like `HookBridge::allowed_html()`) |
| JSON into a script tag | `wp_json_encode( $data, JSON_HEX_TAG \| JSON_HEX_AMP \| JSON_HEX_APOS \| JSON_HEX_QUOT )` |

### 5. SQL via `$wpdb->prepare()`

```php
$allowed = [ 'created', 'cost', 'status' ];
$orderby = in_array( $args['orderby'], $allowed, true ) ? $args['orderby'] : 'created';
$order   = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

$wpdb->get_results(
    $wpdb->prepare(
        "SELECT id, status FROM {$wpdb->prefix}wpuf_transaction ORDER BY {$orderby} {$order} LIMIT %d, %d",
        $offset,
        $limit
    )
);
```

### 6. Nonce and capability

-   REST: the `permission_callback` (via `Caps`) plus WordPress's REST nonce; form-level nonces are still checked where the classic flow checked them (the submission service checks the form nonce).
-   AJAX and classic form posts: `check_ajax_referer()` / `wp_verify_nonce( sanitize_key( wp_unslash( ... ) ), ... )` **and** a capability check (`Caps::can( Caps::MANAGE_FORMS )` in new code, `current_user_can( wpuf_admin_role() )` in legacy code).
-   Client-supplied ids: check the post type and the per-object cap (`current_user_can( 'edit_post', $id )`).

### 7. snake_case methods

`get_states()`, never `getStates()`.

### 8. DocBlocks with `@since WPUF_SINCE`

Every new public / protected method, hook and class gets a docblock with `@since WPUF_SINCE` (Pro: `WPUF_PRO_SINCE`), `@param` and `@return`. Never hardcode a version.

### 9. Translations

-   Text domain `'wp-user-frontend'` in free, `'wpuf-pro'` in Pro; never `'wpuf'`.
-   `/* translators: */` before every `sprintf()` / `printf()` with placeholders; never concatenate translated fragments.

```php
/* translators: %1$s: user name, %2$d: post count */
sprintf( __( 'Hello %1$s, you have %2$d posts', 'wp-user-frontend' ), $name, $count );
```

### 10. Hook prefix

Every `do_action()` / `apply_filters()` name starts with `wpuf_` (Pro: `wpuf_pro_` or `wpuf_`).

### 11. WordPress spacing

`if ( $x ) {`, `if ( ! $x )`, `function ( $a, $b )`, `in_array( $val, $list, true )`. Spaces, never tabs, 4 per level. Short array syntax `[]`.

### Quick self-review

1. [ ] No `==` / `!=`
2. [ ] `in_array()` / `array_search()` strict
3. [ ] Superglobals `wp_unslash()` + sanitized; REST args typed and sanitized
4. [ ] All output escaped (boot JSON with the `JSON_HEX_*` flags)
5. [ ] All SQL prepared, `ORDER BY` allowlisted, and inside a store
6. [ ] REST permission through `Caps` (no literal caps, no `__return_true` on sensitive routes); AJAX has nonce + capability; ids checked per object
7. [ ] Public route that sends mail, calls a paid API or creates content is rate limited
8. [ ] Data read and written through `Stores::*()`; stored shapes unchanged
9. [ ] Every hook the old path fired still fires (same point, args, order); new hooks documented and the index regenerated
10. [ ] Removed or renamed public API has a deprecation shim
11. [ ] Services built in providers, hooks only in `register_hooks()`
12. [ ] snake_case methods, `@since WPUF_SINCE` / `WPUF_PRO_SINCE` docblocks
13. [ ] `/* translators: */` comments, correct text domain, `wpuf_` hook prefix, WP spacing
14. [ ] `vendor/bin/phpcs -q -n` clean on every touched file; related PHPUnit tests green (free, and with `WPUF_TESTS_WITH_PRO=1` when Pro is involved)

## Key reference files

-   `wpuf.php`: constants, `wpuf()`, `platform()`, `platform_bootstrap()`, `legacy()`, `boot_submission_hooks()`
-   `includes/Platform/Bootstrap.php`, `Container.php`, `ServiceProvider.php`, `Providers/*`
-   `includes/Platform/Caps.php`, `VersionGuard.php`, `Http/JsonOutputGuard.php`
-   `includes/Platform/REST/RestController.php`, `Manager.php`, `Rate_Limit.php`, `Controllers/*`
-   `includes/Platform/Stores/Stores.php`, `FormStore.php`, `SettingsStore.php`
-   `includes/Admin/Screens/Screen.php`, `Registry.php`, `includes/Admin/App/AppPage.php`, `includes/Admin/Bundles.php`
-   `includes/Builder/HookBridge.php`, `HookDeprecations.php`, `FormSave.php`
-   `includes/Platform/Classic/Hook_Fields.php`
-   `includes/Frontend/Renderer.php`, `Frontend/Forms/Form_Schema.php`, `Submission_Validator.php`, `Frontend/Bundles.php`
-   `includes/AI/RestController.php` + `includes/AI/Services/*`
-   `docs/architecture.md`, `docs/hooks/php.md`, `docs/hooks/php-index.md`, `docs/settings-screen.md`
