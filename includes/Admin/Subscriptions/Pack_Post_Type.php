<?php
/**
 * Subscription pack post type
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Subscriptions;

use WeDevs\Wpuf\Admin\Subscription;

/**
 * The `wpuf_subscription` post type: registration, scripts, the post types a
 * pack can allow, and the pack chosen on registration (register form field,
 * after-registration redirect). The classic edit screen (title placeholder,
 * metabox save) is gone: packs are edited in the React subscriptions screen.
 *
 * @since WPUF_SINCE Moved out of Admin\Subscription, which keeps the hooks, the
 * static helpers and the data methods, and delegates.
 */
class Pack_Post_Type {

    /**
     * The facade: hook objects, callbacks and the other groups go through it.
     *
     * @var Subscription
     */
    protected $admin;

    /**
     * @since WPUF_SINCE
     *
     * @param Subscription $admin The facade.
     */
    public function __construct( Subscription $admin ) {
        $this->admin = $admin;
    }

    /**
     * Subscription post types
     *
     * @return void
     */
    public function register_post_type() {
        $capability = wpuf_admin_role();

        register_post_type(
            'wpuf_subscription', [
                'label'           => __( 'Subscription', 'wp-user-frontend' ),
                'public'          => true,
                'show_in_menu'    => false,
                'show_in_rest'    => true,
                'query_var'       => false,
                'supports'        => [ 'title' ],
                'capability_type' => 'post',
                'capabilities'    => [
                    'publish_posts'       => $capability,
                    'edit_posts'          => $capability,
                    'edit_others_posts'   => $capability,
                    'delete_posts'        => $capability,
                    'delete_others_posts' => $capability,
                    'read_private_posts'  => $capability,
                    'edit_post'           => $capability,
                    'delete_post'         => $capability,
                    'read_post'           => $capability,
                ],
                'labels' => [
                    'name'               => __( 'Subscription', 'wp-user-frontend' ),
                    'singular_name'      => __( 'Subscription', 'wp-user-frontend' ),
                    'menu_name'          => __( 'Subscription', 'wp-user-frontend' ),
                    'add_new'            => __( 'Add Subscription', 'wp-user-frontend' ),
                    'add_new_item'       => __( 'Add New Subscription', 'wp-user-frontend' ),
                    'edit'               => __( 'Edit', 'wp-user-frontend' ),
                    'edit_item'          => __( 'Edit Subscription', 'wp-user-frontend' ),
                    'new_item'           => __( 'New Subscription', 'wp-user-frontend' ),
                    'view'               => __( 'View Subscription', 'wp-user-frontend' ),
                    'view_item'          => __( 'View Subscription', 'wp-user-frontend' ),
                    'search_items'       => __( 'Search Subscription', 'wp-user-frontend' ),
                    'not_found'          => __( 'No Subscription Found', 'wp-user-frontend' ),
                    'not_found_in_trash' => __( 'No Subscription Found in Trash', 'wp-user-frontend' ),
                    'parent'             => __( 'Parent Subscription', 'wp-user-frontend' ),
                ],
            ]
        );
    }

    /**
     * Enqueue scripts and styles
     *
     * @since 2.2
     */
    public function subscription_script() {
        // wp_enqueue_script( 'wpuf-subscriptions', WPUF_ASSET_URI . '/js/subscriptions.js', [ 'jquery' ], WPUF_VERSION, true );
        wp_localize_script(
            'wpuf-subscriptions', 'wpuf_subs_vars', array(
                'wpuf_subscription_delete_nonce' => wp_create_nonce( 'wpuf-subscription-delete-nonce' ),
            )
        );
    }

    /**
     * Get all post types
     *
     * @since 2.2
     *
     * @return array
     */
    public function get_all_post_type() {
        $post_types = get_post_types();

        unset(
            $post_types['attachment'],
            $post_types['revision'],
            $post_types['nav_menu_item'],
            $post_types['wpuf_forms'],
            $post_types['wpuf_profile'],
            $post_types['wpuf_subscription'],
            $post_types['wpuf_coupon'],
            $post_types['wpuf_input'],
            $post_types['custom_css'],
            $post_types['customize_changeset'],
            $post_types['oembed_cache']
        );

        return apply_filters( 'wpuf_posts_type', $post_types );
    }

    /**
     * Insert hidden field on the register form based on selected package
     *
     * @since 2.2
     *
     * @return void
     */
    public function register_form() {
        // Check if the nonce is valid
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'wpuf_register_form' ) ) {
            return;
        }

        $type    = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
        $pack_id = isset( $_GET['pack_id'] ) ? intval( wp_unslash( $_GET['pack_id'] ) ) : 0;

        if ( $type !== 'wpuf_sub' ) {
            return;
        }

        if ( empty( $pack_id ) ) {
            return;
        }
        ?>
        <input type="hidden" name="wpuf_sub" value="yes" />
        <input type="hidden" name="pack_id"  value="<?php echo esc_attr( $pack_id ); ?>" />

        <?php
    }

    /**
     * Redirect to payment page or add Free subscription after user registration
     *
     * @since 2.2
     *
     * @param int $user_id
     *
     * @return void
     */
    public function after_registration( $user_id ) {
        if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( '_wpnonce' ) ) {
            return;
        }

        $wpuf_sub = isset( $_POST['wpuf_sub'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_sub'] ) ) : '';
        $pack_id  = isset( $_POST['pack_id'] ) ? intval( wp_unslash( $_POST['pack_id'] ) ) : 0;

        if ( $wpuf_sub !== 'yes' ) {
            return $user_id;
        }

        if ( empty( $pack_id ) ) {
            return $user_id;
        }

        $pack           = $this->admin->get_subscription( $pack_id );
        $billing_amount = ( $pack->meta_value['billing_amount'] >= 0 && ! empty( $pack->meta_value['billing_amount'] ) ) ? $pack->meta_value['billing_amount'] : false;

        if ( $billing_amount === false ) {
            wpuf_get_user( $user_id )->subscription()->add_pack( $pack_id, null, false, 'Free' );
            wpuf_get_user( $user_id )->subscription()->add_free_pack( $user_id, $pack_id );
        } else {
            $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
            $redirect = add_query_arg(
                [
                    'action'  => 'wpuf_pay',
                    'type'    => 'pack',
                    'pack_id' => (int) $pack_id,
                ], get_permalink( $pay_page )
            );
        }
    }

    /**
     * Redirect a user to subscription page after signup
     *
     * @since 2.2
     *
     * @param array $response
     * @param int   $user_id
     * @param array $userdata
     * @param int   $form_id
     * @param array $form_settings
     *
     * @return array
     */
    public function subs_redirect_pram( $response, $user_id ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['action'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'wpuf_form_add' ) ) {
            return;
        }

        $wpuf_sub = isset( $_POST['wpuf_sub'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_sub'] ) ) : '';
        $pack_id = isset( $_POST['pack_id'] ) ? sanitize_text_field( wp_unslash( $_POST['pack_id'] ) ) : '';

        if ( wpuf_is_checkbox_or_toggle_on( $wpuf_sub ) ) {
            return $response;
        }

        if ( empty( $pack_id ) ) {
            return $response;
        }

        $pack           = $this->admin->get_subscription( $pack_id );
        $billing_amount = ( $pack->meta_value['billing_amount'] >= 0 && ! empty( $pack->meta_value['billing_amount'] ) ) ? $pack->meta_value['billing_amount'] : false;

        if ( $billing_amount !== false ) {
            $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
            $redirect = add_query_arg(
                [
                    'action'  => 'wpuf_pay',
                    'user_id' => $user_id,
                    'type'    => 'pack',
                    'pack_id' => (int) $pack_id,
                ], get_permalink( $pay_page )
            );

            $response['redirect_to']  = $redirect;
            $response['show_message'] = false;
        }

        return $response;
    }
}
