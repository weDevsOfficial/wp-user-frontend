<?php
/**
 * Builder boot for the React admin app
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Builder;

use WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder;
use WP_Error;

/**
 * Builds the data a form builder reads (`wpuf_form_builder`,
 * `wpuf_single_objects`, `wpuf_mixins`) for one form, outside the builder
 * screen. The React admin app opens builders without a page load, so it asks
 * for this over REST.
 *
 * The data is built the way the builder screen builds it: the request is made
 * to look like `admin.php?page=wpuf-post-forms&action=edit&id=N` (or the
 * registration builder), the screen's load hook fires so the same listeners in
 * free, Pro and add-ons register their filters, then Admin_Form_Builder
 * localizes. While it runs, the current screen is the builder screen, so
 * `is_admin()` and `get_current_screen()` answer as they do on that page.
 *
 * @since WPUF_SINCE
 */
class BuilderBoot {

    /**
     * Builder screens by form post type: page slug, load hook and the view
     * hook the screen printed the builder on.
     */
    const SCREENS = [
        'wpuf_forms'   => [
            'page' => 'wpuf-post-forms',
            'hook' => 'wpuf_load_post_forms',
            'view' => 'wpuf_admin_form_builder_view',
        ],
        'wpuf_profile' => [
            'page' => 'wpuf-profile-forms',
            'hook' => 'wpuf_load_profile_forms',
            'view' => 'wpuf_admin_form_builder',
        ],
    ];

    /**
     * Whether a boot is running (for code that must answer as on the builder screen).
     *
     * @var bool
     */
    private static $running = false;

    /**
     * Whether a builder boot is running.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public static function is_running() {
        return self::$running;
    }

    /**
     * Build the builder data for a form.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form ID
     *
     * @return array|WP_Error { wpuf_form_builder, wpuf_single_objects, wpuf_mixins, builder_form }
     */
    public function boot( $form_id ) {
        $form = get_post( absint( $form_id ) );

        if ( ! $form || ! isset( self::SCREENS[ $form->post_type ] ) ) {
            return new WP_Error( 'wpuf_invalid_form', __( 'Form not found.', 'wp-user-frontend' ), [ 'status' => 404 ] );
        }

        $this->ensure_admin_layer();

        $screen   = self::SCREENS[ $form->post_type ];
        $previous = $this->set_request( $screen['page'], $form->ID );

        self::$running          = true;
        Admin_Form_Builder::$current = null;

        try {
            ob_start();
            /** This action is documented in includes/Admin/Screens/PostFormsList.php */
            do_action( $screen['hook'] );

            $builder = Admin_Form_Builder::$current;

            if ( ! $builder ) {
                ob_end_clean();

                return new WP_Error( 'wpuf_builder_unavailable', __( 'This form builder is not available.', 'wp-user-frontend' ), [ 'status' => 404 ] );
            }

            // The builder enqueue hooks fire as on the builder screen: listeners may
            // register builder filters there. Enqueues do nothing in this request.
            $builder->enqueue_builder_assets();
            $data = $builder->localize_data();

            // The builder screen's view hook (the screen printed the builder form on
            // it); the app renders that form itself, so the output is discarded.
            do_action( $screen['view'] );
            // The builder screen printed these as hidden form inputs.
            $data['builder_form'] = $builder->form_attributes();
            ob_end_clean();

            return array_map( [ $this, 'as_localized' ], $data );
        } finally {
            self::$running = false;
            $this->restore_request( $previous );
        }
    }

    /**
     * The shape wp_localize_script() prints: top-level scalars become strings
     * (true -> "1", false -> ""), so the app reads the same values as the
     * builder screen did.
     *
     * @param mixed $value Localized object
     *
     * @return mixed
     */
    private function as_localized( $value ) {
        if ( ! is_array( $value ) || wp_is_numeric_array( $value ) ) {
            return $value;
        }

        foreach ( $value as $key => $item ) {
            if ( is_scalar( $item ) ) {
                $value[ $key ] = html_entity_decode( (string) $item, ENT_QUOTES, 'UTF-8' );
            }
        }

        return $value;
    }

    /**
     * Build free's admin layer when this request has none. It is built on
     * admin requests only (is_admin()), and its classes own the builder
     * listeners (Admin_Form, Posting). Pro builds its admin
     * layer for WPUF REST routes the same way (boot_admin_for_wpuf_rest).
     *
     * @return void
     */
    private function ensure_admin_layer() {
        $wpuf = wpuf();

        if ( empty( $wpuf->container['admin'] ) ) {
            $wpuf->container['admin'] = $wpuf->platform()->get( \WeDevs\Wpuf\Admin::class );
        }

        // The Pro preview fields (palette tiles, pricing section) come from
        // Pro_Upgrades, also built on admin requests only; it does nothing
        // when Pro is active.
        if ( empty( $wpuf->container['pro_upgrades'] ) ) {
            $wpuf->container['pro_upgrades'] = $wpuf->platform()->get( \WeDevs\Wpuf\Pro_Upgrades::class );
        }
    }

    /**
     * Make the request look like the builder screen's.
     *
     * @param string $page    Builder page slug
     * @param int    $form_id Form ID
     *
     * @return array Previous values
     */
    private function set_request( $page, $form_id ) {
        global $plugin_page, $pagenow, $post;

        $values   = [
            'page'   => $page,
            'action' => 'edit',
            'id'     => (string) $form_id,
        ];
        $previous = [
            'get'          => [],
            'request'      => [],
            'plugin_page'  => $plugin_page,
            'pagenow'      => $pagenow,
            'post'         => $post,
            'screen'       => isset( $GLOBALS['current_screen'] ) ? $GLOBALS['current_screen'] : null,
            'has_screen'   => array_key_exists( 'current_screen', $GLOBALS ),
        ];

        // phpcs:disable WordPress.Security.NonceVerification -- the REST permission check ran; this only reproduces the builder screen's request shape.
        foreach ( array_merge( array_keys( $values ), [ '_wpnonce', 'action2' ] ) as $key ) {
            $previous['get'][ $key ]     = array_key_exists( $key, $_GET ) ? $_GET[ $key ] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            $previous['request'][ $key ] = array_key_exists( $key, $_REQUEST ) ? $_REQUEST[ $key ] : null; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            unset( $_GET[ $key ], $_REQUEST[ $key ] );
        }

        foreach ( $values as $key => $value ) {
            $_GET[ $key ]     = $value;
            $_REQUEST[ $key ] = $value;
        }
        // phpcs:enable WordPress.Security.NonceVerification

        $plugin_page = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $pagenow     = 'admin.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $post        = get_post( $form_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        if ( ! class_exists( 'WP_Screen' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
            require_once ABSPATH . 'wp-admin/includes/screen.php';
        }

        $GLOBALS['current_screen'] = \WP_Screen::get( self::hook_suffix( $page ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        return $previous;
    }

    /**
     * Put the request back as it was before set_request().
     *
     * @param array $previous Previous values
     *
     * @return void
     */
    private function restore_request( array $previous ) {
        global $plugin_page, $pagenow, $post;

        foreach ( [ 'get' => '_GET', 'request' => '_REQUEST' ] as $key => $global ) {
            foreach ( $previous[ $key ] as $name => $value ) {
                if ( null === $value ) {
                    unset( $GLOBALS[ $global ][ $name ] );
                } else {
                    $GLOBALS[ $global ][ $name ] = $value;
                }
            }
        }

        $plugin_page = $previous['plugin_page']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $pagenow     = $previous['pagenow']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $post        = $previous['post']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

        if ( $previous['has_screen'] ) {
            $GLOBALS['current_screen'] = $previous['screen']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        } else {
            unset( $GLOBALS['current_screen'] );
        }
    }

    /**
     * The builder page's hook suffix, as WordPress names it under the WPUF menu.
     *
     * @since WPUF_SINCE
     *
     * @param string $page Page slug
     *
     * @return string
     */
    public static function hook_suffix( $page ) {
        return sanitize_title( __( 'User Frontend', 'wp-user-frontend' ) ) . '_page_' . $page;
    }
}
