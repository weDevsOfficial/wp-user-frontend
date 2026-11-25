<?php

namespace WeDevs\Wpuf\Admin;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Frontend\Frontend_Form;

/**
 * Resend guest verification e-mails for posts submitted before verification
 * links carried a token
 *
 * Those old links are refused, and the guest has no way to ask for a new one,
 * so an administrator can send fresh links from a notice. Nothing runs on its own.
 *
 * @since WPUF_SINCE
 */
class Guest_Verify_Resend {

    /**
     * Option that hides the notice for good
     *
     * @since WPUF_SINCE
     *
     * @var string
     */
    const DISMISSED = 'wpuf_guest_verify_resend_dismissed';

    /**
     * Class constructor
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'notice' ] );
        add_action( 'admin_post_wpuf_guest_verify_resend', [ $this, 'resend' ] );
        add_action( 'admin_post_wpuf_guest_verify_resend_dismiss', [ $this, 'dismiss' ] );
    }

    /**
     * Guest posts still waiting for e-mail verification that have no token
     *
     * @since WPUF_SINCE
     *
     * @param int $limit how many ids to return
     *
     * @return \WP_Query
     */
    protected function query( $limit ) {
        return new \WP_Query(
            [
                'post_type'              => array_values( get_post_types() ),
                'post_status'            => [ 'draft', 'pending' ],
                'posts_per_page'         => $limit,
                'fields'                 => 'ids',
                'update_post_term_cache' => false,
                'meta_query'             => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    [
                        'key'   => Frontend_Form::$guest_verify_id,
                        'value' => 'yes',
                    ],
                    [
                        'key'     => Frontend_Form::$guest_verify_token_id,
                        'compare' => 'NOT EXISTS',
                    ],
                ],
            ]
        );
    }

    /**
     * Show the notice to administrators when such posts exist
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function notice() {
        if ( ! current_user_can( 'manage_options' ) || get_option( self::DISMISSED ) ) {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['wpuf_guest_resent'] ) ) {
            $sent    = absint( $_GET['wpuf_guest_resent'] );
            $skipped = isset( $_GET['wpuf_guest_skipped'] ) ? absint( $_GET['wpuf_guest_skipped'] ) : 0;
            // phpcs:enable WordPress.Security.NonceVerification.Recommended
            ?>
            <div class="notice notice-success">
                <p>
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: 1: number of e-mails sent, 2: number of posts skipped */
                            __( 'WP User Frontend: %1$d guest verification e-mail(s) sent, %2$d post(s) skipped because the author has no guest e-mail address.', 'wp-user-frontend' ),
                            $sent,
                            $skipped
                        )
                    );
                    ?>
                </p>
            </div>
            <?php
        }

        $count = $this->query( 1 )->found_posts;

        if ( ! $count ) {
            return;
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %d: number of guest posts */
                        _n(
                            'WP User Frontend: %d guest post is still waiting for e-mail verification through a link sent before this update. That link no longer works, so the post will not be published until a new link is sent.',
                            'WP User Frontend: %d guest posts are still waiting for e-mail verification through links sent before this update. Those links no longer work, so the posts will not be published until new links are sent.',
                            $count,
                            'wp-user-frontend'
                        ),
                        $count
                    )
                );
                ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wpuf_guest_verify_resend' ), 'wpuf_guest_verify_resend' ) ); ?>"><?php esc_html_e( 'Resend verification e-mails', 'wp-user-frontend' ); ?></a>
                <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wpuf_guest_verify_resend_dismiss' ), 'wpuf_guest_verify_resend' ) ); ?>"><?php esc_html_e( 'Dismiss', 'wp-user-frontend' ); ?></a>
            </p>
        </div>
        <?php
    }

    /**
     * Send a new verification link to the author of each waiting post
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function resend() {
        check_admin_referer( 'wpuf_guest_verify_resend' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do this.', 'wp-user-frontend' ) );
        }

        if ( 'on' !== wpuf_get_option( 'enable_guest_email_notification', 'wpuf_mails', 'on' ) ) {
            wp_die( esc_html__( 'Guest e-mail notification is turned off in the WP User Frontend e-mail settings, so no e-mail can be sent.', 'wp-user-frontend' ) );
        }

        $default_owner = absint( wpuf_get_option( 'default_post_owner', 'wpuf_frontend_posting', 1 ) );
        $sent          = 0;
        $skipped       = 0;

        // The default mail body echoes the site name (bloginfo); keep it out of the redirect.
        ob_start();

        // shortcut: 100 posts per click so a large backlog can not time out; the notice stays until all are done.
        foreach ( $this->query( 100 )->posts as $post_id ) {
            $author  = absint( get_post_field( 'post_author', $post_id ) );
            $user    = get_userdata( $author );
            $form_id = absint( get_post_meta( $post_id, Frontend_Form::$config_id, true ) );

            // Without guest details the post belongs to the default owner, and no guest address was ever known.
            if ( ! $user || $author === $default_owner || ! is_email( $user->user_email ) || ! $form_id ) {
                ++$skipped;
                continue;
            }

            $charging = ( new Form( $form_id ) )->is_charging_enabled();

            wpuf_send_mail_to_guest(
                wpuf_encryption( $post_id ),
                wpuf_encryption( $form_id ),
                $charging ? 'yes' : 'no',
                $charging ? 1 : 2,
                Frontend_Form::create_guest_verify_token( $post_id ),
                $user->user_email
            );

            ++$sent;
        }

        ob_end_clean();

        wp_safe_redirect(
            add_query_arg(
                [
                    'wpuf_guest_resent'  => $sent,
                    'wpuf_guest_skipped' => $skipped,
                ],
                wp_get_referer() ? wp_get_referer() : admin_url()
            )
        );
        exit;
    }

    /**
     * Hide the notice for good
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function dismiss() {
        check_admin_referer( 'wpuf_guest_verify_resend' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to do this.', 'wp-user-frontend' ) );
        }

        update_option( self::DISMISSED, 1, false );

        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }
}
