<?php
/**
 * REST: the admin notice buttons (dismiss, ACF compatibility)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\REST\Controllers;

use WeDevs\Wpuf\Admin\Promotion;
use WeDevs\Wpuf\Integrations\WPUF_ACF_Compatibility;
use WeDevs\Wpuf\Platform\REST\RestController;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * `wpuf/v1/admin/notices/{notice}/dismiss` and
 * `wpuf/v1/admin/integrations/acf/{compatible|migrate}`: the buttons of the
 * admin notices (review request, promotion, PayPal settings, ACF), which
 * posted `wpuf_dismiss_review_notice`, `wpuf_dismiss_promotional_offer_notice`,
 * `wpuf_dismiss_paypal_notice`, `wpuf_dismiss_notice_acf`,
 * `wpuf_compatibility_acf` and `wpuf_migrate_acf` to admin-ajax before.
 * Same capabilities as those actions; the `wp_rest` nonce replaces theirs.
 *
 * @since WPUF_SINCE
 */
class NoticesController extends RestController {

    /**
     * Resource name for error codes.
     *
     * @var string
     */
    protected $resource = 'notices';

    /**
     * {@inheritDoc}
     */
    public function register_routes() {
        $this->route(
            '/admin/notices/(?P<notice>review|promotion|paypal|acf)/dismiss', WP_REST_Server::CREATABLE, 'dismiss', [ $this, 'can_dismiss' ], [
                'key' => [
                    'type'    => 'string',
                    'default' => '',
                ],
            ]
        );
        $this->route( '/admin/integrations/acf/(?P<task>compatible|migrate)', WP_REST_Server::CREATABLE, 'acf', [ $this, 'can_manage' ] );
    }

    /**
     * The WPUF admin role; the PayPal notice asks for `manage_options` as before.
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return true|WP_Error
     */
    public function can_dismiss( WP_REST_Request $request ) {
        if ( 'paypal' === $request['notice'] && is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
            return $this->error( 'forbidden', __( 'Unauthorized operation', 'wp-user-frontend' ), 403 );
        }

        return $this->can_manage();
    }

    /**
     * The WPUF admin role.
     *
     * @since WPUF_SINCE
     *
     * @return true|WP_Error
     */
    public function can_manage() {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'wpuf_rest_unauthorized', __( 'You must be logged in.', 'wp-user-frontend' ), [ 'status' => 401 ] );
        }

        if ( ! current_user_can( wpuf_admin_role() ) ) {
            return $this->error( 'forbidden', __( 'Unauthorized operation', 'wp-user-frontend' ), 403 );
        }

        return true;
    }

    /**
     * POST /admin/notices/{notice}/dismiss
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response|WP_Error
     */
    public function dismiss( WP_REST_Request $request ) {
        switch ( $request['notice'] ) {
            case 'review':
                Stores::settings()->set_wp_option( 'wpuf_review_notice_dismiss', 'yes' );
                break;

            case 'promotion':
                // Only the option of the promotion on show: the key comes from the client.
                $key = sanitize_text_field( (string) $request['key'] );

                if ( '' === $key || $key !== $this->promotion_key() ) {
                    return $this->error( 'bad_key', __( 'Unknown notice.', 'wp-user-frontend' ), 400 );
                }

                Stores::settings()->set_wp_option( $key, 'hide' );
                break;

            case 'paypal':
                Stores::settings()->set_wp_option( 'wpuf_paypal_settings_notice_dismissed', true );
                break;

            case 'acf':
                $this->acf_integration()->dismiss_prompt();
                break;
        }

        return rest_ensure_response( [ 'dismissed' => true ] );
    }

    /**
     * POST /admin/integrations/acf/{compatible|migrate}
     *
     * @since WPUF_SINCE
     *
     * @param WP_REST_Request $request Request
     *
     * @return \WP_REST_Response
     */
    public function acf( WP_REST_Request $request ) {
        if ( 'migrate' === $request['task'] ) {
            $this->acf_integration()->migrate();
        } else {
            $this->acf_integration()->mark_compatible();
        }

        return rest_ensure_response( [ 'updated' => true ] );
    }

    /**
     * The option key of the promotion the notice shows (from the cached feed).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    private function promotion_key() {
        $promos = json_decode( (string) get_transient( Promotion::PROMO_KEY ), true );

        return is_array( $promos ) && ! empty( $promos['key'] ) ? (string) $promos['key'] : '';
    }

    /**
     * The ACF integration as the plugin built it.
     *
     * @since WPUF_SINCE
     *
     * @return WPUF_ACF_Compatibility
     */
    private function acf_integration() {
        if ( wpuf()->platform()->has( WPUF_ACF_Compatibility::class ) ) {
            return wpuf()->platform()->get( WPUF_ACF_Compatibility::class );
        }

        return new WPUF_ACF_Compatibility();
    }
}
