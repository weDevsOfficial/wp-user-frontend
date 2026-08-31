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
// The selector context is the one that carries the logo and the pro flag, which is
// what the settings screen renders from too, so both places stay in step.
$gateway_cards = wpuf_get_gateways( 'gateway_selector' );
$gateway_cards = is_array( $gateway_cards ) ? $gateway_cards : [];
$active_ways   = wpuf_get_option( 'active_gateways', 'wpuf_payment', [] );
$active_ways   = is_array( $active_ways ) ? $active_ways : [];

if ( ! function_exists( 'wpuf_onboarding_allowed_svg' ) ) {
    /**
     * The SVG tags allowed through wp_kses for the fallback gateway icons
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    function wpuf_onboarding_allowed_svg() {
        $attrs = [
            'xmlns' => [], 'viewbox' => [], 'width' => [], 'height' => [], 'fill' => [],
            'stroke' => [], 'stroke-width' => [], 'stroke-linecap' => [], 'stroke-linejoin' => [],
            'd' => [], 'x' => [], 'y' => [], 'x1' => [], 'y1' => [], 'x2' => [], 'y2' => [],
            'rx' => [], 'ry' => [], 'cx' => [], 'cy' => [], 'r' => [], 'class' => [],
        ];

        return [
            'svg'  => $attrs,
            'path' => $attrs,
            'rect' => $attrs,
            'line' => $attrs,
            'circle' => $attrs,
        ];
    }
}

if ( ! function_exists( 'wpuf_onboarding_gateway_fallback_icon' ) ) {
    /**
     * Icon for a gateway that ships no logo of its own
     *
     * Mirrors the fallbacks the settings screen draws, so a gateway without an image
     * looks the same in both places rather than showing an empty box here.
     *
     * @since WPUF_SINCE
     *
     * @param string $gateway_id
     *
     * @return string
     */
    function wpuf_onboarding_gateway_fallback_icon( $gateway_id ) {
        if ( 'bank' === $gateway_id ) {
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#787c82" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M3 10h18"/><path d="M12 3l9 7H3l9-7z"/><path d="M5 10v8"/><path d="M9 10v8"/><path d="M15 10v8"/><path d="M19 10v8"/></svg>';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#787c82" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>';
    }
}

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
                <strong><?php esc_html_e( 'Install the remaining UF pages', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'Adds whichever of the Dashboard, Subscription, Payment, Thank You and Order Received pages your site is still missing. Pages you already have, including the ones picked in the earlier steps, are reused rather than duplicated.', 'wp-user-frontend' ); ?></span>
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
                <?php
                foreach ( $gateway_cards as $gateway_id => $gateway ) :
                    $gateway_label  = ! empty( $gateway['admin_label'] ) ? $gateway['admin_label'] : $gateway_id;
                    $gateway_icon   = ! empty( $gateway['icon'] ) ? $gateway['icon'] : '';
                    // A gateway is only ever flagged as a preview while Pro is off, so
                    // this is what decides the badge. Never a hardcoded gateway name.
                    $gateway_is_pro = ! empty( $gateway['is_pro_preview'] );
                    // Bank needs no credentials, so it is the safe default to land on.
                    $gateway_on     = 'bank' === $gateway_id
                        ? ( ! $revisiting || in_array( $gateway_id, $active_ways, true ) )
                        : in_array( $gateway_id, $active_ways, true );
                    ?>
                    <?php if ( $gateway_is_pro ) : ?>
                        <a class="wpuf-onboarding-card is-pro" href="<?php echo esc_url( \WeDevs\Wpuf\Free\Pro_Prompt::get_pro_url() ); ?>" target="_blank" rel="noopener">
                            <span class="wpuf-onboarding-card-icon">
                                <?php if ( $gateway_icon ) : ?>
                                    <img src="<?php echo esc_url( $gateway_icon ); ?>" alt="<?php echo esc_attr( $gateway_label ); ?>" />
                                <?php else : ?>
                                    <?php echo wp_kses( wpuf_onboarding_gateway_fallback_icon( $gateway_id ), wpuf_onboarding_allowed_svg() ); ?>
                                <?php endif; ?>
                            </span>
                            <strong>
                                <?php echo esc_html( $gateway_label ); ?>
                                <img class="wpuf-onboarding-pro-badge" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="<?php esc_attr_e( 'PRO', 'wp-user-frontend' ); ?>" />
                            </strong>
                        </a>
                    <?php else : ?>
                        <label class="wpuf-onboarding-card<?php echo $gateway_on ? ' is-selected' : ''; ?>">
                            <input type="checkbox" name="active_gateways[]" value="<?php echo esc_attr( $gateway_id ); ?>" <?php checked( true, $gateway_on ); ?> />
                            <span class="wpuf-onboarding-card-icon">
                                <?php if ( $gateway_icon ) : ?>
                                    <img src="<?php echo esc_url( $gateway_icon ); ?>" alt="<?php echo esc_attr( $gateway_label ); ?>" />
                                <?php else : ?>
                                    <?php echo wp_kses( wpuf_onboarding_gateway_fallback_icon( $gateway_id ), wpuf_onboarding_allowed_svg() ); ?>
                                <?php endif; ?>
                            </span>
                            <strong><?php echo esc_html( $gateway_label ); ?></strong>
                        </label>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

    <?php endif; ?>

    <?php wp_nonce_field( 'wpuf-onboarding' ); ?>
    <?php $this->action_bar(); ?>
</form>

