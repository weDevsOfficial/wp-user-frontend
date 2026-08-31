<?php
/**
 * Onboarding: companion plugins
 *
 * Only plugins that are not running yet appear here. Once they are all
 * active the step drops out of the wizard, and it comes back if one is
 * later deactivated or deleted.
 *
 * @since WPUF_SINCE
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$pending_plugins = $this->get_pending_plugins();
$can_install     = current_user_can( 'install_plugins' );
$install_errors  = get_option( \WeDevs\Wpuf\Admin\Onboarding::PLUGIN_ERRORS_OPTION, [] );
$install_errors  = is_array( $install_errors ) ? $install_errors : [];
?>
<form method="post">
    <h2><?php esc_html_e( 'Plugins that work well alongside', 'wp-user-frontend' ); ?></h2>
    <p class="wpuf-onboarding-subtitle">
        <?php esc_html_e( 'Free weDevs plugins for the members and content you collect. Untick any you do not want.', 'wp-user-frontend' ); ?>
    </p>

    <?php if ( $install_errors ) : ?>
        <div class="wpuf-onboarding-note">
            <?php esc_html_e( 'These did not install last time:', 'wp-user-frontend' ); ?>
            <?php foreach ( $install_errors as $name => $message ) : ?>
                <br /><strong><?php echo esc_html( $name ); ?>:</strong> <?php echo esc_html( $message ); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="wpuf-onboarding-field">
        <div class="wpuf-onboarding-grid">
            <?php foreach ( $pending_plugins as $slug => $pending_plugin ) : ?>
                <?php if ( $can_install ) : ?>
                    <label class="wpuf-onboarding-card is-selected">
                        <input type="checkbox" name="plugins[]" value="<?php echo esc_attr( $slug ); ?>" checked="checked" />
                        <?php if ( ! empty( $pending_plugin['logo'] ) ) : ?>
                            <img class="wpuf-onboarding-logo" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/' . $pending_plugin['logo'] ); ?>" alt="" />
                        <?php endif; ?>
                        <strong><?php echo esc_html( $pending_plugin['name'] ); ?></strong>
                        <p><?php echo esc_html( $pending_plugin['desc'] ); ?></p>
                        <?php if ( ! empty( $pending_plugin['installed'] ) ) : ?>
                            <span class="wpuf-onboarding-badge"><?php esc_html_e( 'Will be activated', 'wp-user-frontend' ); ?></span>
                        <?php endif; ?>
                    </label>
                <?php else : ?>
                    <div class="wpuf-onboarding-card is-installed">
                        <?php if ( ! empty( $pending_plugin['logo'] ) ) : ?>
                            <img class="wpuf-onboarding-logo" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/' . $pending_plugin['logo'] ); ?>" alt="" />
                        <?php endif; ?>
                        <strong><?php echo esc_html( $pending_plugin['name'] ); ?></strong>
                        <p><?php echo esc_html( $pending_plugin['desc'] ); ?></p>
                        <span class="wpuf-onboarding-badge is-error"><?php esc_html_e( 'No permission to install', 'wp-user-frontend' ); ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php wp_nonce_field( 'wpuf-onboarding' ); ?>
    <?php
    $this->action_bar(
        [
            'next_label' => __( 'Install & Continue', 'wp-user-frontend' ),
            'show_skip'  => true,
        ]
    );
    ?>
</form>

