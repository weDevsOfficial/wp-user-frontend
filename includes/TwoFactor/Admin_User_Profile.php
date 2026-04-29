<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Admin reset on Users → Edit User
 *
 * Only `manage_options` (administrator) can see and trigger the reset.
 * Lower-cap roles get nothing — no UI, no working POST. Audit trail
 * records the actor.
 *
 * @since WPUF_SINCE
 */
class Admin_User_Profile {

    public const ACTION = 'wpuf_2fa_admin_reset';

    /** @var TOTP_Method */
    private $totp;

    /** @var User_Storage */
    private $storage;

    public function __construct( TOTP_Method $totp, User_Storage $storage ) {
        $this->totp    = $totp;
        $this->storage = $storage;

        add_action( 'show_user_profile', [ $this, 'render_section' ], 35 );
        add_action( 'edit_user_profile', [ $this, 'render_section' ], 35 );
        add_action( 'admin_post_' . self::ACTION, [ $this, 'handle_reset' ] );
    }

    public function render_section( $user ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $is_enrolled = $this->totp->is_enrolled( $user->ID );
        $enrolled_at = $is_enrolled ? $this->storage->get_totp_enrolled_at( $user->ID ) : null;
        $audit_log   = $this->storage->get_audit_log( $user->ID );
        ?>
        <h2><?php esc_html_e( 'Two-Factor Authentication', 'wp-user-frontend' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Status', 'wp-user-frontend' ); ?></th>
                <td>
                    <?php if ( $is_enrolled ) : ?>
                        <p>
                            <?php
                            printf(
                                /* translators: %s: human-readable enrollment date */
                                esc_html__( 'Authenticator app active since %s.', 'wp-user-frontend' ),
                                esc_html( wp_date( get_option( 'date_format' ), $enrolled_at ) )
                            );
                            ?>
                        </p>
                        <?php
                        // The Users → Edit User screen wraps the entire body
                        // in <form id="your-profile">. HTML doesn't allow
                        // nested forms — a literal <form> here gets flattened
                        // by the browser, so the click ends up POSTing the
                        // outer profile form to user-edit.php (which then
                        // shows "The link you followed has expired").
                        // Instead: render a plain button and have JS build
                        // and submit a real top-level form on click.
                        $reset_action  = esc_attr( self::ACTION );
                        $reset_url     = esc_url( admin_url( 'admin-post.php' ) );
                        $reset_nonce   = esc_attr( wp_create_nonce( self::ACTION . '_' . $user->ID ) );
                        $reset_user_id = (int) $user->ID;
                        $reset_confirm = esc_attr( __( 'This will allow this user to log in without 2FA. They will need to re-enroll if they want to use 2FA again.', 'wp-user-frontend' ) );
                        ?>
                        <button type="button"
                                class="button button-link-delete wpuf-2fa-admin-reset"
                                data-action-url="<?php echo $reset_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>"
                                data-action-name="<?php echo $reset_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>"
                                data-user-id="<?php echo $reset_user_id; ?>"
                                data-nonce="<?php echo $reset_nonce; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>"
                                data-confirm="<?php echo $reset_confirm; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above ?>">
                            <?php esc_html_e( 'Reset 2FA', 'wp-user-frontend' ); ?>
                        </button>
                        <script>
                        (function () {
                            document.addEventListener( 'click', function ( e ) {
                                var btn = e.target.closest( '.wpuf-2fa-admin-reset' );
                                if ( ! btn ) {
                                    return;
                                }
                                e.preventDefault();
                                if ( ! window.confirm( btn.dataset.confirm ) ) {
                                    return;
                                }
                                var f = document.createElement( 'form' );
                                f.method = 'post';
                                f.action = btn.dataset.actionUrl;
                                // Append to <body> so it's outside the
                                // surrounding profile form.
                                var add = function ( name, value ) {
                                    var i = document.createElement( 'input' );
                                    i.type = 'hidden';
                                    i.name = name;
                                    i.value = value;
                                    f.appendChild( i );
                                };
                                add( 'action', btn.dataset.actionName );
                                add( 'user_id', btn.dataset.userId );
                                add( '_wpnonce', btn.dataset.nonce );
                                document.body.appendChild( f );
                                f.submit();
                            } );
                        })();
                        </script>
                    <?php else : ?>
                        <p><?php esc_html_e( 'This user has not enrolled in two-factor authentication.', 'wp-user-frontend' ); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ( ! empty( $audit_log ) ) : ?>
                <tr>
                    <th><?php esc_html_e( 'Recent activity', 'wp-user-frontend' ); ?></th>
                    <td>
                        <ul style="margin:0;">
                            <?php foreach ( array_reverse( $audit_log ) as $entry ) : ?>
                                <li>
                                    <?php
                                    $actor = get_userdata( $entry['actor_id'] );
                                    printf(
                                        /* translators: 1: action label, 2: actor login, 3: timestamp */
                                        esc_html__( '%1$s by %2$s on %3$s', 'wp-user-frontend' ),
                                        esc_html( $this->action_label( $entry['action'] ) ),
                                        esc_html( $actor ? $actor->user_login : __( 'unknown', 'wp-user-frontend' ) ),
                                        esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry['timestamp'] ) )
                                    );
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
        <?php
    }

    public function handle_reset() {
        $target_user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to reset 2FA for this user.', 'wp-user-frontend' ), 403 );
        }

        $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, self::ACTION . '_' . $target_user_id ) ) {
            wp_die( esc_html__( 'Security check failed.', 'wp-user-frontend' ), 403 );
        }

        if ( ! $target_user_id || ! get_userdata( $target_user_id ) ) {
            wp_die( esc_html__( 'User not found.', 'wp-user-frontend' ), 404 );
        }

        $this->totp->reset( $target_user_id );
        $this->storage->append_audit( $target_user_id, 'admin_reset', get_current_user_id() );

        do_action( 'wpuf_2fa_totp_reset', $target_user_id, get_current_user_id() );

        wp_safe_redirect( add_query_arg( 'wpuf_2fa_reset', '1', get_edit_user_link( $target_user_id ) ) );
        exit;
    }

    private function action_label( $action ) {
        switch ( $action ) {
            case 'enrolled':
                return __( 'Enrolled', 'wp-user-frontend' );
            case 'self_disable':
                return __( 'Self-disabled', 'wp-user-frontend' );
            case 'admin_reset':
                return __( 'Admin reset', 'wp-user-frontend' );
            default:
                return $action;
        }
    }
}
