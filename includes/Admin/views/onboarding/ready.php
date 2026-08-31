<?php
/**
 * Onboarding: ready
 *
 * @since WPUF_SINCE
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$checklist  = $this->get_checklist();

// Set only by the redirect from the previous step's Continue button.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$celebrate  = isset( $_GET['celebrate'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['celebrate'] ) );
$progress   = $this->get_progress();
$revisiting = in_array( 'ready', $progress['completed'], true );
$share      = $revisiting ? wpuf_get_option( 'share_wpuf_essentials', 'wpuf_general', 'off' ) : 'on';

if ( $this->wants( 'post_form' ) ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf-post-forms' );
    $cta_label = __( 'Open my post forms', 'wp-user-frontend' );
} elseif ( $this->wants( 'registration' ) && wpuf_is_pro_active() ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf-profile-forms' );
    $cta_label = __( 'Open my registration forms', 'wp-user-frontend' );
} elseif ( $this->wants( 'registration' ) ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf-settings#wpuf_profile' );
    $cta_label = __( 'Open login & registration settings', 'wp-user-frontend' );
} elseif ( $this->wants( 'user_directory' ) ) {
    $cta_url   = admin_url( 'admin.php?page=wpuf_userlisting' );
    $cta_label = __( 'Open my user directories', 'wp-user-frontend' );
} else {
    $cta_url   = admin_url( 'admin.php?page=wp-user-frontend' );
    $cta_label = __( 'Go to User Frontend', 'wp-user-frontend' );
}
?>
<form method="post">
<h2><?php esc_html_e( 'Your frontend is ready', 'wp-user-frontend' ); ?></h2>
<p class="wpuf-onboarding-subtitle">
    <?php esc_html_e( 'Anything still grey is worth a look. Each row opens the screen that handles it.', 'wp-user-frontend' ); ?>
</p>

<ul class="wpuf-onboarding-checklist">
    <?php foreach ( $checklist as $item ) : ?>
        <li class="<?php echo $item['done'] ? 'is-done' : ''; ?>">
            <span class="wpuf-check">
                <svg width="9" height="7" viewBox="0 0 9 7" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M1 3.4001L3.4 5.8001L7.6 1.6001" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="wpuf-check-label"><?php echo esc_html( $item['label'] ); ?></span>
            <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['link'] ); ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="wpuf-onboarding-note">
    <?php
    printf(
        // translators: %s is a link to the Tools page
        esc_html__( 'Run this setup again any time from %s.', 'wp-user-frontend' ),
        '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf_tools&tab=tools' ) ) . '">' . esc_html__( 'User Frontend › Tools', 'wp-user-frontend' ) . '</a>'
    );
    ?>
</div>

<div class="wpuf-onboarding-field">
    <label class="wpuf-onboarding-toggle is-switch">
        <input type="checkbox" name="share_essentials" value="1" <?php checked( true, wpuf_is_checkbox_or_toggle_on( $share ) ); ?> />
        <span class="wpuf-toggle-text">
            <strong><?php esc_html_e( 'Share diagnostic data', 'wp-user-frontend' ); ?></strong>
            <span><?php esc_html_e( 'Versions, site name and your email — it tells us what to fix first. Never your members\' data.', 'wp-user-frontend' ); ?></span>
        </span>
    </label>
</div>

<?php wp_nonce_field( 'wpuf-onboarding' ); ?>
<input type="hidden" name="redirect_to" id="wpuf-onboarding-redirect" value="<?php echo esc_url( $cta_url ); ?>" />

<div class="wpuf-onboarding-footer">
    <div class="wpuf-onboarding-footer-inner">
        <div class="wpuf-onboarding-footer-left">
            <button type="submit" name="wpuf_onboarding_save" value="1" class="wpuf-onboarding-btn-white" data-redirect="<?php echo esc_url( admin_url( 'admin.php?page=wpuf-settings' ) ); ?>">
                <?php esc_html_e( 'Go to full settings', 'wp-user-frontend' ); ?>
            </button>
        </div>
        <div class="wpuf-onboarding-footer-right">
            <button type="submit" name="wpuf_onboarding_save" value="1" class="wpuf-onboarding-btn-primary" data-redirect="<?php echo esc_url( $cta_url ); ?>">
                <?php echo esc_html( $cta_label ); ?>
            </button>
        </div>
    </div>
</div>
</form>

<?php if ( $celebrate ) : ?>
<canvas id="wpuf-onboarding-confetti" aria-hidden="true"></canvas>

<?php endif; ?>
