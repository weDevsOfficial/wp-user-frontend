<?php
/**
 * Onboarding: the settings that have to be right first
 *
 * @since WPUF_SINCE
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$admin_bar     = wpuf_get_option( 'show_admin_bar', 'wpuf_general', [ 'administrator', 'editor', 'author', 'contributor' ] );
$admin_bar     = is_array( $admin_bar ) ? $admin_bar : [ $admin_bar ];
$admins_only   = [ 'administrator' ] === array_values( $admin_bar );

// On a first run these default on; a repeat run shows what was saved.
$gateways      = wpuf_get_gateways();
$active_ways   = wpuf_get_option( 'active_gateways', 'wpuf_payment', [] );
$active_ways   = is_array( $active_ways ) ? $active_ways : [];
$stripe_ready  = array_key_exists( 'stripe', $gateways );

$progress      = $this->get_progress();
$revisiting    = in_array( 'common', $progress['completed'], true );


?>
<form method="post">
    <h2><?php esc_html_e( 'A few basics', 'wp-user-frontend' ); ?></h2>
    <p class="wpuf-onboarding-subtitle">
        <?php esc_html_e( 'The bits that make a frontend site feel finished. All of them live in Settings afterwards.', 'wp-user-frontend' ); ?>
    </p>

    <div class="wpuf-onboarding-field">
        <label class="wpuf-onboarding-toggle is-switch">
            <input type="checkbox" name="install_wpuf_pages" value="1" checked="checked" />
            <span class="wpuf-toggle-text">
                <strong><?php esc_html_e( 'Install the UF pages', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'Adds the UF pages your site needs: Dashboard, Account and Edit.', 'wp-user-frontend' ); ?></span>
            </span>
        </label>

        <label class="wpuf-onboarding-toggle is-switch">
            <input type="checkbox" name="hide_admin_bar" value="1" <?php checked( true, $revisiting ? $admins_only : true ); ?> />
            <span class="wpuf-toggle-text">
                <strong><?php esc_html_e( 'Hide the admin bar from members', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'Members see your site, not the WordPress toolbar.', 'wp-user-frontend' ); ?></span>
            </span>
        </label>

        <?php if ( $this->wants( 'registration' ) ) : ?>
        <label class="wpuf-onboarding-toggle is-switch">
            <input type="checkbox" name="add_logout_menu" value="1" checked="checked" />
            <span class="wpuf-toggle-text">
                <strong><?php esc_html_e( 'Add a logout link to the main menu', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'So members can sign out without hunting for it.', 'wp-user-frontend' ); ?></span>
            </span>
        </label>
        <?php endif; ?>

    </div>

    <?php if ( $this->wants( 'payments' ) ) : ?>
        <div class="wpuf-onboarding-field">
            <label class="wpuf-onboarding-toggle is-switch">
                <input type="checkbox" name="enable_payment" id="wpuf-onboarding-enable-payment" value="1" checked="checked" />
                <span class="wpuf-toggle-text">
                    <strong><?php esc_html_e( 'Enable payments', 'wp-user-frontend' ); ?></strong>
                    <span><?php esc_html_e( 'Subscription packs, coupons and transactions. Bank transfer works right away; add PayPal or Stripe in Settings.', 'wp-user-frontend' ); ?></span>
                </span>
            </label>
        </div>

        <div class="wpuf-onboarding-field" id="wpuf-onboarding-gateway-field">
            <span class="wpuf-onboarding-label"><?php esc_html_e( 'How people pay', 'wp-user-frontend' ); ?></span>

            <div class="wpuf-onboarding-grid is-thirds">
                <label class="wpuf-onboarding-card is-selected">
                    <input type="checkbox" name="active_gateways[]" value="bank" checked="checked" />
                    <strong><?php esc_html_e( 'Bank transfer', 'wp-user-frontend' ); ?></strong>
                </label>

                <label class="wpuf-onboarding-card">
                    <input type="checkbox" name="active_gateways[]" value="paypal" <?php checked( true, in_array( 'paypal', $active_ways, true ) ); ?> />
                    <strong><?php esc_html_e( 'PayPal', 'wp-user-frontend' ); ?></strong>
                </label>

                <?php if ( $stripe_ready ) : ?>
                    <label class="wpuf-onboarding-card">
                        <input type="checkbox" name="active_gateways[]" value="stripe" <?php checked( true, in_array( 'stripe', $active_ways, true ) ); ?> />
                        <strong><?php esc_html_e( 'Stripe', 'wp-user-frontend' ); ?></strong>
                    </label>
                <?php else : ?>
                    <a class="wpuf-onboarding-card is-pro" href="<?php echo esc_url( \WeDevs\Wpuf\Free\Pro_Prompt::get_pro_url() ); ?>" target="_blank" rel="noopener">
                        <strong>
                            <?php esc_html_e( 'Stripe', 'wp-user-frontend' ); ?>
                            <img class="wpuf-onboarding-pro-badge" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="<?php esc_attr_e( 'PRO', 'wp-user-frontend' ); ?>" />
                        </strong>
                    </a>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>

    <?php wp_nonce_field( 'wpuf-onboarding' ); ?>
    <?php $this->action_bar(); ?>
</form>

<script>
( function () {
    var enable  = document.getElementById( 'wpuf-onboarding-enable-payment' );
    var gateway = document.getElementById( 'wpuf-onboarding-gateway-field' );

    if ( ! enable || ! gateway ) {
        return;
    }

    function render() {
        gateway.style.display = enable.checked ? '' : 'none';
    }

    enable.addEventListener( 'change', render );
    render();

    document.querySelectorAll( '#wpuf-onboarding-gateway-field input[type="checkbox"]' ).forEach( function ( input ) {
        input.addEventListener( 'change', function () {
            input.closest( '.wpuf-onboarding-card' ).classList.toggle( 'is-selected', input.checked );
        } );
    } );
} )();
</script>
