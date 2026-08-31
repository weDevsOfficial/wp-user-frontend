<?php
/**
 * Onboarding: login and registration
 *
 * @since WPUF_SINCE
 *
 * @var \WeDevs\Wpuf\Admin\Onboarding $this
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'wpuf_onboarding_required_mark' ) ) {
    /**
     * Print the required marker beside a label
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    function wpuf_onboarding_required_mark() {
        \WeDevs\Wpuf\Admin\Onboarding::required_mark();
    }
}

$login_page  = absint( wpuf_get_option( 'login_page', 'wpuf_profile', 0 ) );
$reg_page    = absint( wpuf_get_option( 'reg_override_page', 'wpuf_profile', 0 ) );
$override    = wpuf_get_option( 'register_link_override', 'wpuf_profile', 'off' );
$autologin   = wpuf_get_option( 'autologin_after_registration', 'wpuf_profile', 'off' );

$is_pro      = wpuf_is_pro_active();
$layouts     = wpuf_get_login_layout_options();
$layouts     = ! empty( $layouts ) && is_array( $layouts ) ? $layouts : [];
$layout      = wpuf_get_option( 'wpuf_login_form_layout', 'wpuf_profile', 'layout1' );

// The stored value is not guaranteed to name a layout that exists: the settings
// API writes back whatever was posted, and a site that has since lost Pro can be
// left pointing at a layout this build does not ship. Indexing straight into the
// list would then warn on PHP 8 and hand esc_url() a null.
$first_layout   = key( $layouts );
$default_layout = array_key_exists( 'layout1', $layouts ) ? 'layout1' : ( null !== $first_layout ? $first_layout : '' );

if ( ! $is_pro || ! array_key_exists( $layout, $layouts ) ) {
    // Layouts are a Pro feature, so free always shows the basic one rather than a
    // locked preview of whatever was chosen while Pro was active.
    $layout = $default_layout;
}

$current_layout = array_key_exists( $layout, $layouts ) ? $layouts[ $layout ] : [
    'image' => '',
    'label' => '',
];
$login_pages = $this->get_pages_for_shortcode( 'wpuf-login', __( 'UF Login Page', 'wp-user-frontend' ) );
// Free and Pro register different registration shortcodes, so the page list has
// to follow the build: [wpuf-registration] in free, [wpuf_profile] in Pro.
$reg_shortcode_tag = $is_pro ? 'wpuf_profile' : 'wpuf-registration';
$reg_pages   = $this->get_pages_for_shortcode( $reg_shortcode_tag, __( 'UF Registration Page', 'wp-user-frontend' ) );
$account_pages = $this->get_pages_for_shortcode( 'wpuf_account', __( 'UF Account Page', 'wp-user-frontend' ) );
$account_page  = absint( wpuf_get_option( 'account_page', 'wpuf_my_account', 0 ) );

// The setting is often empty even though the page exists, so fall back to it
// rather than offering to create a second one.
if ( ! $account_page ) {
    $account_page = $this->find_page_with_shortcode( 'wpuf_account' );
}
?>
<form method="post">
    <h2><?php esc_html_e( 'Login &amp; Registration', 'wp-user-frontend' ); ?></h2>
    <p class="wpuf-onboarding-subtitle">
        <?php esc_html_e( 'Visitors sign up and log in on your own site, in your own theme.', 'wp-user-frontend' ); ?>
    </p>

    <div class="wpuf-onboarding-field">
        <label for="wpuf-onboarding-login-page">
            <?php esc_html_e( 'Login page', 'wp-user-frontend' ); ?>
            <?php wpuf_onboarding_required_mark(); ?>
        </label>

        <select name="login_page" id="wpuf-onboarding-login-page" required aria-required="true">
            <option value="create"><?php esc_html_e( '— Create a new Login page —', 'wp-user-frontend' ); ?></option>
            <?php foreach ( $login_pages as $page_id => $page_title ) : ?>
                <option value="<?php echo esc_attr( $page_id ); ?>" <?php selected( $login_page, $page_id ); ?>><?php echo esc_html( $page_title ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="wpuf-onboarding-help"><?php esc_html_e( 'Pick a page and we add the login form to it, or we make a new page.', 'wp-user-frontend' ); ?></p>
    </div>

    <?php if ( $is_pro ) : ?>
        <div class="wpuf-onboarding-field" id="wpuf-onboarding-reg-page-field">
            <label for="wpuf-onboarding-reg-page">
                <?php esc_html_e( 'Registration page', 'wp-user-frontend' ); ?>
                <?php wpuf_onboarding_required_mark(); ?>
            </label>

            <select name="reg_page" id="wpuf-onboarding-reg-page" required aria-required="true">
                <option value="create"><?php esc_html_e( '— Create a new Registration page and form —', 'wp-user-frontend' ); ?></option>
                <?php foreach ( $reg_pages as $page_id => $page_title ) : ?>
                    <option value="<?php echo esc_attr( $page_id ); ?>" <?php selected( $reg_page, $page_id ); ?>><?php echo esc_html( $page_title ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="wpuf-onboarding-help"><?php esc_html_e( 'Same for sign ups. You get a form you can add your own fields to.', 'wp-user-frontend' ); ?></p>
        </div>
    <?php else : ?>
        <div class="wpuf-onboarding-field" id="wpuf-onboarding-reg-page-field">
            <label for="wpuf-onboarding-reg-page">
                <?php esc_html_e( 'Registration page', 'wp-user-frontend' ); ?>
                <?php wpuf_onboarding_required_mark(); ?>
            </label>

            <select name="reg_page" id="wpuf-onboarding-reg-page" required aria-required="true">
                <option value="create"><?php esc_html_e( '— Create a new Registration page —', 'wp-user-frontend' ); ?></option>
                <?php foreach ( $reg_pages as $page_id => $page_title ) : ?>
                    <option value="<?php echo esc_attr( $page_id ); ?>" <?php selected( $reg_page, $page_id ); ?>><?php echo esc_html( $page_title ); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="wpuf-onboarding-help"><?php esc_html_e( 'Sign ups run on the built-in registration form, on a page in your own theme.', 'wp-user-frontend' ); ?></p>

            <div class="wpuf-onboarding-toggle">
                <span class="wpuf-toggle-text">
                    <strong>
                        <?php esc_html_e( 'Custom registration forms', 'wp-user-frontend' ); ?>
                        <img class="wpuf-onboarding-pro-badge" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="<?php esc_attr_e( 'PRO', 'wp-user-frontend' ); ?>" />
                    </strong>
                    <span><?php esc_html_e( 'The page above works now. Building your own fields, roles and paid sign ups on top of it comes with Pro.', 'wp-user-frontend' ); ?></span>
                </span>
            </div>
        </div>
    <?php endif; ?>

    <div class="wpuf-onboarding-field">
        <label for="wpuf-onboarding-account-page">
            <?php esc_html_e( 'Account page', 'wp-user-frontend' ); ?>
            <?php wpuf_onboarding_required_mark(); ?>
        </label>

        <select name="account_page" id="wpuf-onboarding-account-page" required aria-required="true">
            <option value="create"><?php esc_html_e( '— Create a new Account page —', 'wp-user-frontend' ); ?></option>
            <?php foreach ( $account_pages as $page_id => $page_title ) : ?>
                <option value="<?php echo esc_attr( $page_id ); ?>" <?php selected( $account_page, $page_id ); ?>><?php echo esc_html( $page_title ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="wpuf-onboarding-help"><?php esc_html_e( 'Where members manage their posts, profile and subscription.', 'wp-user-frontend' ); ?></p>
    </div>

    <div class="wpuf-onboarding-field" id="wpuf-onboarding-autologin-field">
        <span class="wpuf-onboarding-label"><?php esc_html_e( 'After they sign up', 'wp-user-frontend' ); ?></span>

        <label class="wpuf-onboarding-toggle is-switch">
            <input type="checkbox" name="autologin_after_registration" value="1" <?php checked( true, wpuf_is_checkbox_or_toggle_on( $autologin ) ); ?> />
            <span class="wpuf-toggle-text">
                <strong><?php esc_html_e( 'Log them in straight away', 'wp-user-frontend' ); ?></strong>
                <span><?php esc_html_e( 'They are in as soon as they submit. Leave off if you approve members yourself.', 'wp-user-frontend' ); ?></span>
            </span>
        </label>
    </div>

    <div class="wpuf-onboarding-field">
        <details class="wpuf-onboarding-disclosure">
            <summary>
                <img id="wpuf-onboarding-layout-preview" src="<?php echo esc_url( $current_layout['image'] ); ?>" alt="" />
                <span class="wpuf-disclosure-text">
                    <strong>
                        <?php esc_html_e( 'Login form layout', 'wp-user-frontend' ); ?>
                        <?php if ( ! $is_pro ) : ?>
                            <img class="wpuf-onboarding-pro-badge" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="<?php esc_attr_e( 'PRO', 'wp-user-frontend' ); ?>" />
                        <?php endif; ?>
                    </strong>
                    <span id="wpuf-onboarding-layout-name"><?php echo esc_html( $current_layout['label'] ); ?></span>
                </span>
                <span class="wpuf-disclosure-action"><?php esc_html_e( 'Change', 'wp-user-frontend' ); ?></span>
            </summary>

            <div class="wpuf-onboarding-grid is-layouts">
                <?php foreach ( $layouts as $key => $option ) : ?>
                    <label class="wpuf-onboarding-layout <?php echo $layout === $key ? 'is-selected' : ''; ?>">
                        <input type="radio" name="wpuf_login_form_layout" value="<?php echo esc_attr( $key ); ?>" data-image="<?php echo esc_url( $option['image'] ); ?>" data-label="<?php echo esc_attr( $option['label'] ); ?>" <?php checked( $layout, $key ); ?> <?php disabled( true, ! $is_pro ); ?> />
                        <img src="<?php echo esc_url( $option['image'] ); ?>" alt="<?php echo esc_attr( $option['label'] ); ?>" />
                    </label>
                <?php endforeach; ?>
            </div>

            <?php if ( ! $is_pro ) : ?>
                <p class="wpuf-onboarding-help">
                    <?php
                    printf(
                        // translators: %s is the upgrade link
                        esc_html__( 'Login form layouts come with %s.', 'wp-user-frontend' ),
                        '<a href="' . esc_url( \WeDevs\Wpuf\Free\Pro_Prompt::get_pro_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Pro', 'wp-user-frontend' ) . '</a>'
                    );
                    ?>
                </p>
            <?php endif; ?>
        </details>
    </div>

    <?php wp_nonce_field( 'wpuf-onboarding' ); ?>
    <?php $this->action_bar(); ?>
</form>

