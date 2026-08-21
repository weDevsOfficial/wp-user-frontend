<?php
/**
 * Onboarding: post form
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$templates    = wpuf_get_post_form_templates();
$existing     = absint( wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting', 0 ) );
$post_edit    = wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' );
$post_del     = wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' );
$existing_url = $existing ? admin_url( 'admin.php?page=wpuf-post-forms&action=edit&id=' . $existing ) : '';
?>
<form method="post">
    <h2><?php esc_html_e( 'Post Form', 'wp-user-frontend' ); ?></h2>
    <p class="wpuf-onboarding-subtitle">
        <?php esc_html_e( 'The form visitors fill in to publish. Start from a template, change any field later.', 'wp-user-frontend' ); ?>
    </p>

    <div class="wpuf-onboarding-field">
        <label for="wpuf-onboarding-template"><?php esc_html_e( 'Start from a template', 'wp-user-frontend' ); ?></label>

        <select name="post_form_template" id="wpuf-onboarding-template">
            <?php foreach ( $templates as $key => $template ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" data-desc="<?php echo esc_attr( $template->get_description() ); ?>" <?php selected( 'post_form_template_post', $key ); ?>>
                    <?php echo esc_html( $template->get_title() ); ?>
                </option>
            <?php endforeach; ?>
            <option value="skip" data-desc="<?php esc_attr_e( 'Keep your current form and build one later.', 'wp-user-frontend' ); ?>">
                <?php esc_html_e( 'Do not create a form', 'wp-user-frontend' ); ?>
            </option>
        </select>

        <p class="wpuf-onboarding-help" id="wpuf-onboarding-template-desc"></p>

        <?php if ( $existing ) : ?>
            <p class="wpuf-onboarding-help">
                <?php
                printf(
                    // translators: %s is the link to the current default form
                    esc_html__( '%s is your default form now. A new one takes its place.', 'wp-user-frontend' ),
                    '<a href="' . esc_url( $existing_url ) . '" target="_blank" rel="noopener">' . esc_html( get_the_title( $existing ) ) . '</a>'
                );
                ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="wpuf-onboarding-field">
        <span class="wpuf-onboarding-label"><?php esc_html_e( 'What authors can do after posting', 'wp-user-frontend' ); ?></span>

        <label class="wpuf-onboarding-toggle is-switch">
            <input type="checkbox" name="enable_post_edit" value="1" <?php checked( 'yes', $post_edit ); ?> />
            <span class="wpuf-toggle-text">
                <strong><?php esc_html_e( 'Let authors edit their posts', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'They can change their own posts from the frontend dashboard.', 'wp-user-frontend' ); ?></span>
            </span>
        </label>

        <label class="wpuf-onboarding-toggle is-switch">
            <input type="checkbox" name="enable_post_del" value="1" <?php checked( 'yes', $post_del ); ?> />
            <span class="wpuf-toggle-text">
                <strong><?php esc_html_e( 'Let authors delete their posts', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'Deleted posts go to trash, so you can get them back.', 'wp-user-frontend' ); ?></span>
            </span>
        </label>
    </div>

    <?php wp_nonce_field( 'wpuf-onboarding' ); ?>
    <?php $this->action_bar(); ?>
</form>

<script>
( function () {
    var select = document.getElementById( 'wpuf-onboarding-template' );
    var desc   = document.getElementById( 'wpuf-onboarding-template-desc' );

    function render() {
        desc.textContent = select.options[ select.selectedIndex ].getAttribute( 'data-desc' ) || '';
    }

    select.addEventListener( 'change', render );
    render();
} )();
</script>
