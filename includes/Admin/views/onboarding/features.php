<?php
/**
 * Onboarding: what the admin wants to use
 *
 * @since WPUF_SINCE
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$definitions = $this->get_feature_definitions();
$picked      = $this->get_features();
?>
<form method="post">
    <h2><?php esc_html_e( 'What does your site need?', 'wp-user-frontend' ); ?></h2>
    <p class="wpuf-onboarding-subtitle">
        <?php esc_html_e( 'Tick what you need and we will set it up with you. We will not ask about the rest.', 'wp-user-frontend' ); ?>
    </p>

    <div class="wpuf-onboarding-field">
        <div class="wpuf-onboarding-grid">
            <?php
            foreach ( $definitions as $key => $feature ) :
                $checked = in_array( $key, $picked, true );
                ?>
                <label class="wpuf-onboarding-card <?php echo $checked ? 'is-selected' : ''; ?>">
                    <input type="checkbox" name="features[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( true, $checked ); ?> />
                    <strong><?php echo esc_html( wp_specialchars_decode( $feature['name'] ) ); ?></strong>
                    <p><?php echo esc_html( $feature['desc'] ); ?></p>
                </label>
            <?php endforeach; ?>
        </div>
        <p class="wpuf-onboarding-help is-spaced"><?php esc_html_e( 'You can turn any of these on later in Settings.', 'wp-user-frontend' ); ?></p>
    </div>

    <?php wp_nonce_field( 'wpuf-onboarding' ); ?>
    <?php $this->action_bar( [ 'next_label' => __( 'Continue', 'wp-user-frontend' ), 'show_skip' => false ] ); ?>
</form>

<script>
( function () {
    document.querySelectorAll( '#wpuf-onboarding-features .wpuf-onboarding-card, .wpuf-onboarding-card input[name="features[]"]' ).forEach( function ( input ) {
        if ( 'INPUT' !== input.tagName ) {
            return;
        }

        input.addEventListener( 'change', function () {
            input.closest( '.wpuf-onboarding-card' ).classList.toggle( 'is-selected', input.checked );
        } );
    } );
} )();
</script>
