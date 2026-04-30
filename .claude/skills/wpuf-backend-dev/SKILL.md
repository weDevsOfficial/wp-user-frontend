---
name: wpuf-backend-dev
description: Add or modify WPUF backend PHP code following project conventions. Use when creating new classes, methods, hooks, REST controllers, or modifying existing backend code.
---

# WPUF Backend Development

This skill provides guidance for developing WP User Frontend backend PHP code according to project standards.

## When to Use This Skill

**Invoke this skill before:**

-   Writing new PHP classes or services
-   Modifying existing backend PHP code
-   Adding hooks, filters, or REST endpoints
-   Working with the container or subsystem bootstrap

## Namespace & File Structure

-   **Root namespace:** `WeDevs\Wpuf\`
-   **PSR-4 autoloading:** `WeDevs\Wpuf\` maps to `includes/`
-   **File path follows namespace:** `WeDevs\Wpuf\Admin\Menu` -> `includes/Admin/Menu.php`
-   **Third-party libs:** `Lib/` directory (not namespaced via PSR-4)
-   **Utility traits:** `WeDevs\WpUtils\ContainerTrait`, `WeDevs\WpUtils\SingletonTrait` from `wedevs/wp-utils`

## Bootstrap & Initialization Flow

1.  `wpuf.php` loads Composer autoloader (`vendor/autoload.php`)
2.  Defines constants: `WPUF_VERSION`, `WPUF_FILE`, `WPUF_ROOT`, `WPUF_INCLUDES`, `WPUF_ASSET_URI`
3.  `WP_User_Frontend` singleton via `SingletonTrait` — accessed globally via `wpuf()`
4.  Constructor calls `includes()` (loads critical files) and `init_hooks()`
5.  On `plugins_loaded`: Appsero init, Free_Loader, Pro version check, upgrades, class instantiation
6.  On `init`: Textdomain loading, gateway manager, AJAX initialization

## Container Pattern

WPUF uses a **simple array-based container** (not a full DI container like League):

```php
// Registration (in bootstrap classes)
$this->container['service_name'] = new ServiceClass();

// Access via magic __get()
wpuf()->service_name->method();
```

Classes that use the container import `WeDevs\WpUtils\ContainerTrait`.

### Key Container Classes

| Class | Role | Access |
|---|---|---|
| `WP_User_Frontend` | Main singleton, top-level container | `wpuf()` |
| `Admin` | Admin subsystem bootstrap (16+ services) | `wpuf()->admin` |
| `Frontend` | Frontend subsystem bootstrap (7+ services) | `wpuf()->frontend` |
| `API` | REST API bootstrap (2 controllers under `Api/`) | `wpuf()->api` |

### Adding a New Service

Register in the appropriate bootstrap class constructor:

```php
// In Admin.php for admin services:
$this->container['my_feature'] = new Admin\MyFeature();

// In Frontend.php for frontend services:
$this->container['my_feature'] = new Frontend\MyFeature();

// In WP_User_Frontend::instantiate() for global services:
$this->container['my_feature'] = new MyFeature();
```

## Class Conventions

### Method & Property Naming

-   Methods: `snake_case` (WordPress convention) — e.g., `register_routes()`, `get_items()`
-   Properties: standard PHP typed where possible
-   Constants: `UPPER_SNAKE_CASE`

### Subsystem Bootstrap Pattern

Admin and Frontend classes instantiate their child services in the constructor:

```php
namespace WeDevs\Wpuf;

use WeDevs\WpUtils\ContainerTrait;

class Admin {
    use ContainerTrait;

    public function __construct() {
        $this->container['menu']     = new Admin\Menu();
        $this->container['settings'] = new Admin\Admin_Settings();
        // ... more services

        add_action( 'admin_init', [ $this, 'some_hook' ] );
    }
}
```

### Pro/Free Conditional Loading

Free-only features load via `Free_Loader` when Pro is not active:

```php
if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
    $this->container['free_feature'] = new Free\MyFeature();
}
```

## REST API Controllers

### Controller Pattern

Controllers extend `WP_REST_Controller` directly (no custom base class):

```php
namespace WeDevs\Wpuf\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class MyController extends WP_REST_Controller {

    protected $namespace = 'wpuf/v1';
    protected $base = 'my-resource';

    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );
    }

    public function permission_check() {
        return current_user_can( wpuf_admin_role() );
    }
}
```

### API Namespace

-   **Base namespace:** `wpuf/v1`
-   **Current controllers:** `Api\Subscription`, `Api\FormList`, `AI\RestController` (registered via `AI_Manager`)

### Registering a Controller

Add to `API::__construct()`:

```php
public function __construct() {
    $this->subscription = new Subscription();
    $this->form_list    = new FormList();
    $this->my_resource  = new MyResource();  // Add new controller

    add_action( 'rest_api_init', [ $this, 'init_api' ] );
}
```

The `init_api()` method iterates the container and calls `register_routes()` on each.

### Response Pattern

WPUF controllers return `WP_REST_Response` with a `success` flag:

```php
// Success
return new WP_REST_Response( [
    'success' => true,
    'data'    => $items,
] );

// Error
return new WP_REST_Response( [
    'success' => false,
    'message' => __( 'Something went wrong', 'wp-user-frontend' ),
] );

// Or use WP_Error for HTTP status codes
return new \WP_Error(
    'wpuf_invalid_input',
    __( 'Invalid input.', 'wp-user-frontend' ),
    [ 'status' => 400 ]
);
```

### Permission Callbacks

Standard permission check uses `wpuf_admin_role()`:

```php
public function permission_check() {
    return current_user_can( wpuf_admin_role() );
}
```

## Extensibility Patterns

### Filters

```php
$value = apply_filters( 'wpuf_subscription_data', $data, $request );
$params = apply_filters( 'wpuf_ai_form_builder_localize_data', $localize_data );
```

### Actions

```php
do_action( 'wpuf_before_update_subscription_pack', $id, $request, $post_arr );
do_action( 'wpuf_after_update_subscription_pack_meta', $id, $request );
do_action( 'wpuf_loaded' );
```

### Plugin Options

```php
$value = wpuf_get_option( 'key', 'wpuf_option_group', 'default' );
```

## Localization / Translation (PHP)

**Text domain:** `wp-user-frontend` — used for ALL translatable strings in free version. `wpuf-pro` for Pro strings.

### Translation Functions

| Function | Usage |
|---|---|
| `__( 'Text', 'wp-user-frontend' )` | Return translated string |
| `_e( 'Text', 'wp-user-frontend' )` | Echo translated string |
| `esc_html__( 'Text', 'wp-user-frontend' )` | Return translated + HTML-escaped |
| `esc_html_e( 'Text', 'wp-user-frontend' )` | Echo translated + HTML-escaped |
| `esc_attr__( 'Text', 'wp-user-frontend' )` | Return translated + attribute-escaped |
| `_n( 'single', 'plural', $count, 'wp-user-frontend' )` | Pluralization |
| `_x( 'Text', 'context', 'wp-user-frontend' )` | Context-aware translation |

### Translator Comments

Always add `/* translators: */` comments before `sprintf()` with placeholders:

```php
/* translators: %1$s: opening tag, %2$s: shortcode, %3$s: closing tag */
__( '%1$sThis post contains %2$s shortcode%3$s', 'wp-user-frontend' )
```

### String Formatting

Use `sprintf()` for dynamic content — never concatenate translated strings:

```php
// CORRECT
sprintf( __( 'Welcome, %s!', 'wp-user-frontend' ), $name )

// WRONG
__( 'Welcome, ', 'wp-user-frontend' ) . $name
```

## Coding Standards

-   **PHPCS ruleset:** `WordPress-Core` + `WordPress` (via `phpcs.xml.dist`)
-   **PHP compatibility:** 5.6+ (configured in PHPCS), plugin header says 5.6
-   **`in_array()` strict mode:** required (enforced as error)
-   **Text domains:** `wp-user-frontend`, `wpuf-pro`
-   **Yoda conditions:** not enforced (disabled in PHPCS)
-   **File naming:** not enforced (disabled in PHPCS)
-   **Direct DB queries:** allowed (severity 0)

## Code Review Prevention Checklist

**MANDATORY: Apply every rule below before submitting code. These are the top causes of review rejections.**

### 1. Strict Comparisons (CRITICAL — #1 review issue)

**NEVER use `==` or `!=`.** Always use `===` and `!==`.

```php
// ❌ WRONG — will be rejected
if ( $value == 'yes' ) {}
if ( $status != false ) {}
if ( 0 == $count ) {}

// ✅ CORRECT
if ( $value === 'yes' ) {}
if ( $status !== false ) {}
if ( 0 === $count ) {}
```

### 2. in_array() Must Use Strict Mode (CRITICAL — #2 review issue)

**ALWAYS pass `true` as the third argument.** This is enforced as an error by PHPCS.

```php
// ❌ WRONG — PHPCS error, review rejection
if ( in_array( $value, $allowed ) ) {}
in_array( $type, $list )

// ✅ CORRECT
if ( in_array( $value, $allowed, true ) ) {}
in_array( $type, $list, true )
```

Also applies to: `array_search()` — always pass strict `true`.

### 3. Superglobal Sanitization (CRITICAL — security rejection)

**Every `$_POST`, `$_GET`, `$_REQUEST`, `$_SERVER` access MUST be sanitized with `wp_unslash()` first.**

```php
// ❌ WRONG — missing wp_unslash
$title = sanitize_text_field( $_POST['title'] );
$id = intval( $_GET['id'] );

// ✅ CORRECT
$title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
$id    = intval( wp_unslash( $_GET['id'] ?? 0 ) );
$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
$url   = esc_url_raw( wp_unslash( $_POST['redirect'] ?? '' ) );
```

**Type-specific sanitization:**

| Data type | Function |
|-----------|----------|
| Text | `sanitize_text_field( wp_unslash( ... ) )` |
| Email | `sanitize_email( wp_unslash( ... ) )` |
| URL | `esc_url_raw( wp_unslash( ... ) )` |
| Integer | `intval( wp_unslash( ... ) )` or `absint()` |
| HTML content | `wp_kses_post( wp_unslash( ... ) )` |
| Textarea | `sanitize_textarea_field( wp_unslash( ... ) )` |
| Key/slug | `sanitize_key( wp_unslash( ... ) )` |

### 4. Output Escaping (CRITICAL — security rejection)

**ALL output MUST be escaped. No exceptions.**

```php
// ❌ WRONG — raw output
echo $title;
echo $url;
<input value="<?php echo $value; ?>">

// ✅ CORRECT
echo esc_html( $title );
echo esc_url( $url );
<input value="<?php echo esc_attr( $value ); ?>">
```

**Escaping function reference:**

| Context | Function |
|---------|----------|
| HTML text | `esc_html()` |
| HTML attribute | `esc_attr()` |
| URL (href, src) | `esc_url()` |
| JavaScript string | `esc_js()` |
| Rich HTML | `wp_kses_post()` |
| Translated text output | `esc_html__()`, `esc_html_e()`, `esc_attr__()` |

### 5. SQL Queries Must Use prepare() (CRITICAL — security rejection)

**NEVER concatenate variables into SQL. Always use `$wpdb->prepare()`.**

```php
// ❌ WRONG — SQL injection risk
$wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = $user_id" );
$wpdb->get_results( "SELECT * FROM $table ORDER BY $orderby $order LIMIT $offset, $limit" );

// ✅ CORRECT
$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wpuf_transaction WHERE user_id = %d",
        $user_id
    )
);
```

**Even for ORDER BY / LIMIT — use prepare or allowlist:**

```php
$allowed_orderby = [ 'created_at', 'amount', 'status' ];
$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
$order   = 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC';

$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wpuf_transaction ORDER BY {$orderby} {$order} LIMIT %d, %d",
        $args['offset'],
        $args['number']
    )
);
```

### 6. Nonce Verification (CRITICAL — security rejection)

**Every form submission and AJAX handler MUST verify a nonce.**

```php
// Form submissions
if (
    ! isset( $_POST['wpuf_nonce'] )
    || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wpuf_nonce'] ) ), 'wpuf_action' )
) {
    wp_die( esc_html__( 'Security check failed', 'wp-user-frontend' ) );
}

// AJAX handlers
check_ajax_referer( 'wpuf_nonce', 'nonce' );
```

### 7. Permission Checks (CRITICAL — security rejection)

**Every sensitive operation MUST verify user capabilities.**

```php
// Admin operations
if ( ! current_user_can( wpuf_admin_role() ) ) {
    wp_die( esc_html__( 'Permission denied', 'wp-user-frontend' ) );
}

// REST endpoints — NEVER use __return_true for sensitive data
'permission_callback' => function() {
    return current_user_can( wpuf_admin_role() );
}
```

### 8. Method Naming — snake_case Only (ERROR — standards violation)

**NEVER use camelCase for PHP methods or functions.**

```php
// ❌ WRONG — review rejection
public function getStates() {}
public function verifyResponse() {}
public function catbuildTree() {}

// ✅ CORRECT
public function get_states() {}
public function verify_response() {}
public function build_category_tree() {}
```

### 9. DocBlocks & @since Tags (ERROR — documentation violation)

**Every public/protected method MUST have a PHPDoc with `@since`.**

```php
// ❌ WRONG — missing docblock
public function process_form( $form_id ) {}

// ✅ CORRECT
/**
 * Process form submission.
 *
 * @since WPUF_SINCE
 *
 * @param int $form_id Form ID.
 *
 * @return bool
 */
public function process_form( $form_id ) {}
```

**Use `WPUF_SINCE` placeholder** — never hardcode a version number.

### 10. Translation & Translator Comments (WARNING)

**All user-facing strings MUST use translation functions. All `sprintf()` with placeholders MUST have translator comments.**

```php
// ❌ WRONG — missing translator comment
sprintf( __( 'Hello %s, you have %d posts', 'wp-user-frontend' ), $name, $count );

// ✅ CORRECT
/* translators: %1$s: user name, %2$d: post count */
sprintf( __( 'Hello %1$s, you have %2$d posts', 'wp-user-frontend' ), $name, $count );
```

**Text domain rules:**
-   Free version: `'wp-user-frontend'` — NEVER use `'wpuf'`
-   Pro version: `'wpuf-pro'`
-   Never concatenate translated strings — use `sprintf()` with a single `__()` call

### 11. Spacing & Formatting (WARNING — style violation)

```php
// ❌ WRONG — missing spaces
if($condition){
if (!empty($items))
in_array($val,$list,true)

// ✅ CORRECT — spaces inside parentheses, after !
if ( $condition ) {
if ( ! empty( $items ) )
in_array( $val, $list, true )
```

### 12. Hook Naming (ERROR — naming violation)

**All hooks MUST be prefixed with `wpuf_` and use snake_case.**

```php
// ❌ WRONG
do_action( 'form_before_render', $form_id );
apply_filters( 'formFields', $fields );

// ✅ CORRECT
do_action( 'wpuf_form_before_render', $form_id );
apply_filters( 'wpuf_form_fields', $fields, $form_id );
```

### Quick Self-Review Before Submitting

Run this mental checklist on every line you write:

1. ☐ Every `==` replaced with `===`? Every `!=` with `!==`?
2. ☐ Every `in_array()` and `array_search()` has `true` as third arg?
3. ☐ Every `$_POST`/`$_GET`/`$_REQUEST`/`$_SERVER` wrapped in `wp_unslash()` + sanitize?
4. ☐ Every `echo`/output uses `esc_html()`, `esc_attr()`, `esc_url()`, or `wp_kses_post()`?
5. ☐ Every SQL query uses `$wpdb->prepare()` for dynamic values?
6. ☐ Every form/AJAX handler verifies a nonce?
7. ☐ Every sensitive operation checks `current_user_can()`?
8. ☐ All methods are `snake_case`? No camelCase?
9. ☐ All new methods have `@since WPUF_SINCE` docblock?
10. ☐ All `sprintf()` with `__()` have `/* translators: */` comment?
11. ☐ Text domain is `'wp-user-frontend'` (not `'wpuf'`)?
12. ☐ All hooks prefixed with `wpuf_`?
13. ☐ Spaces inside parentheses: `( $var )` not `($var)`?
14. ☐ Space after negation: `! $var` not `!$var`?

## Key Reference Files

-   `wpuf.php` — Main plugin file, singleton bootstrap
-   `wpuf-functions.php` — Global utility functions
-   `includes/Admin.php` — Admin subsystem (16+ services)
-   `includes/Frontend.php` — Frontend subsystem (7+ services)
-   `includes/API.php` — REST API bootstrap
-   `includes/Api/Subscription.php` — Subscription REST controller
-   `includes/Api/FormList.php` — Form list REST controller
-   `includes/AI/RestController.php` — AI form builder REST controller (registered via `AI_Manager`)
-   `includes/Assets.php` — Script/style registration
-   `includes/Free/Free_Loader.php` — Free-only features (conditional on Pro absence)
-   `includes/Integrations.php` — Third-party integrations (Dokan, WC Vendors, ACF, n8n)
-   `includes/AI_Manager.php` — AI form builder features
