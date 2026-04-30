<?php

namespace WeDevs\Wpuf\TwoFactor;

/**
 * Admin reset on Users → Edit User
 *
 * Only `manage_options` (administrator) can see and trigger the reset.
 * Reset loops over every method the user is currently enrolled in,
 * calls `Method_Interface::reset()` on each, writes one audit entry per
 * method, and fires `wpuf_2fa_method_disabled` once per method with
 * `reason = 'admin_reset'`. Lower-cap roles get nothing — no UI, no
 * working POST.
 *
 * @since WPUF_SINCE
 */
class Admin_User_Profile {

    public const ACTION = 'wpuf_2fa_admin_reset';

    /** @var Method_Registry */
    private $registry;

    /** @var User_Storage */
    private $storage;

    public function __construct( Method_Registry $registry, User_Storage $storage ) {
        $this->registry = $registry;
        $this->storage  = $storage;

        add_action( 'show_user_profile', [ $this, 'render_section' ], 35 );
        add_action( 'edit_user_profile', [ $this, 'render_section' ], 35 );
        add_action( 'admin_post_' . self::ACTION, [ $this, 'handle_reset' ] );
    }

    public function render_section( $user ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $enrolled  = $this->registry->enrolled_for( $user->ID );
        $audit_log = $this->storage->get_audit_log( $user->ID );
        ?>
        <h2><?php esc_html_e( 'Two-Factor Authentication', 'wp-user-frontend' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Status', 'wp-user-frontend' ); ?></th>
                <td>
                    <?php if ( ! empty( $enrolled ) ) : ?>
                        <ul style="margin:0 0 1em 0;">
                            <?php foreach ( $enrolled as $method ) : ?>
                                <?php
                                $enrolled_at = $this->storage->get_method_enrolled_at( $user->ID, $method->get_id() );
                                ?>
                                <li>
                                    <?php
                                    if ( $enrolled_at ) {
                                        printf(
                                            /* translators: 1: method label, 2: human-readable enrollment date */
                                            esc_html__( '%1$s — active since %2$s.', 'wp-user-frontend' ),
                                            esc_html( $method->get_label() ),
                                            esc_html( wp_date( get_option( 'date_format' ), $enrolled_at ) )
                                        );
                                    } else {
                                        echo esc_html( $method->get_label() );
                                    }
                                    ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
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
                        $reset_confirm = esc_attr(
                            count( $enrolled ) > 1
                                ? __( 'This will disable all enrolled 2FA methods for this user. They will need to re-enroll.', 'wp-user-frontend' )
                                : __( 'This will allow this user to log in without 2FA. They will need to re-enroll if they want to use 2FA again.', 'wp-user-frontend' )
                        );
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
                                    $actor      = isset( $entry['actor_id'] ) ? get_userdata( (int) $entry['actor_id'] ) : null;
                                    $method_id  = isset( $entry['method_id'] ) ? (string) $entry['method_id'] : '';
                                    $method_obj = $method_id ? $this->registry->get( $method_id ) : null;
                                    $method_lbl = $method_obj ? $method_obj->get_label() : $method_id;
                                    $action     = isset( $entry['action'] ) ? (string) $entry['action'] : '';
                                    $timestamp  = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
                                    printf(
                                        /* translators: 1: method label, 2: action label, 3: actor login, 4: timestamp */
                                        esc_html__( '%1$s — %2$s by %3$s on %4$s', 'wp-user-frontend' ),
                                        esc_html( $method_lbl ),
                                        esc_html( $this->action_label( $action ) ),
                                        esc_html( $actor ? $actor->user_login : __( 'unknown', 'wp-user-frontend' ) ),
                                        esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) )
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

        $actor_id = get_current_user_id();
        $enrolled = $this->registry->enrolled_for( $target_user_id );

        foreach ( $enrolled as $method ) {
            $method->reset( $target_user_id );

            $this->storage->append_audit(
                $target_user_id,
                $method->get_id(),
                'admin_reset',
                $actor_id
            );

            do_action( 'wpuf_2fa_method_disabled', $target_user_id, $method->get_id(), $actor_id, 'admin_reset' );
        }

        wp_safe_redirect( add_query_arg( 'wpuf_2fa_reset', '1', get_edit_user_link( $target_user_id ) ) );
        exit;
    }

    private function action_label( $action ): string {
        switch ( $action ) {
            case 'enrolled':
                return __( 'Enrolled', 'wp-user-frontend' );
            case 'self_disable':
                return __( 'Self-disabled', 'wp-user-frontend' );
            case 'admin_reset':
                return __( 'Admin reset', 'wp-user-frontend' );
            case 'challenge_issued':
                return __( 'Challenge issued', 'wp-user-frontend' );
            case 'challenge_issue_failed':
                return __( 'Challenge issue failed', 'wp-user-frontend' );
            default:
                return $action;
        }
    }
}
