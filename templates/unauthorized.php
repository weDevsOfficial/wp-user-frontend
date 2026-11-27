<?php
/**
 * The account page for a visitor who is not logged in: a card with the
 * restricted message, a Log in button and, when sign-up is open, Register.
 *
 * Overridable from the theme (wp-user-frontend/unauthorized.php).
 *
 * @var string $message The "Unauthorized message" setting ('' for the default text)
 */

wp_enqueue_style( 'wpuf-frontend-runtime-style' );

$wpuf_login_url    = wp_login_url( get_permalink() );
$wpuf_register_url = get_option( 'users_can_register' ) ? wp_registration_url() : '';
$wpuf_lock_icon    = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';

if ( empty( $message ) ) {
    $msg = sprintf(
        '<div class="wpuf-frontend wpuf-account-guest"><div class="wpuf-state-card wpuf-state-card--login" role="status"><span class="wpuf-state-card__icon">%1$s</span><h3 class="wpuf-state-card__title">%2$s</h3><p class="wpuf-state-card__text">%3$s</p><div class="wpuf-state-card__actions"><a class="wpuf-btn wpuf-btn--primary" href="%4$s">%5$s</a>%6$s</div></div></div>',
        $wpuf_lock_icon,
        esc_html__( 'Log in to see your account', 'wp-user-frontend' ),
        esc_html__( 'This page is restricted. Please log in to view this page.', 'wp-user-frontend' ),
        esc_url( $wpuf_login_url ),
        esc_html__( 'Log in', 'wp-user-frontend' ),
        $wpuf_register_url ? sprintf( '<a class="wpuf-btn wpuf-btn--secondary" href="%1$s">%2$s</a>', esc_url( $wpuf_register_url ), esc_html__( 'Register', 'wp-user-frontend' ) ) : ''
    );

    $wpuf_allowed         = wp_kses_allowed_html( 'post' );
    $wpuf_allowed['svg']  = [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true ];
    $wpuf_allowed['rect'] = [ 'width' => true, 'height' => true, 'x' => true, 'y' => true, 'rx' => true, 'ry' => true ];
    $wpuf_allowed['path'] = [ 'd' => true ];

    echo wp_kses( apply_filters( 'wpuf_account_unauthorized', $msg ), $wpuf_allowed );
} else {
    printf(
        '<div class="wpuf-frontend wpuf-account-guest"><div class="wpuf-state-card wpuf-state-card--login" role="status"><span class="wpuf-state-card__icon">%1$s</span><p class="wpuf-state-card__text">%2$s</p><div class="wpuf-state-card__actions"><a class="wpuf-btn wpuf-btn--primary" href="%3$s">%4$s</a></div></div></div>',
        wp_kses( $wpuf_lock_icon, [ 'svg' => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true ], 'rect' => [ 'width' => true, 'height' => true, 'x' => true, 'y' => true, 'rx' => true, 'ry' => true ], 'path' => [ 'd' => true ] ] ),
        esc_html( $message ),
        esc_url( $wpuf_login_url ),
        esc_html__( 'Log in', 'wp-user-frontend' )
    );
}
