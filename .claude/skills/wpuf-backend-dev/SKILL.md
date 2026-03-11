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
| `API` | REST API bootstrap (2 controllers) | `wpuf()->api` |

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
-   **Current controllers:** `Api\Subscription`, `Api\FormList`

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

## Key Reference Files

-   `wpuf.php` — Main plugin file, singleton bootstrap
-   `wpuf-functions.php` — Global utility functions
-   `includes/Admin.php` — Admin subsystem (16+ services)
-   `includes/Frontend.php` — Frontend subsystem (7+ services)
-   `includes/API.php` — REST API bootstrap
-   `includes/Api/Subscription.php` — Subscription REST controller
-   `includes/Api/FormList.php` — Form list REST controller
-   `includes/Assets.php` — Script/style registration
-   `includes/Free/Free_Loader.php` — Free-only features (conditional on Pro absence)
-   `includes/Integrations.php` — Third-party integrations (Dokan, WC Vendors, ACF, n8n)
-   `includes/AI_Manager.php` — AI form builder features
