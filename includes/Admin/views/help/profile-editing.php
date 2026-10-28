<?php
/**
 * Help topic body: profile-editing
 *
 * Shared by the classic Help page (views/support.php) and the admin app's
 * Help route (Admin\Help_Content::body()).
 *
 * @package WP_User_Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<p>
    <?php
    printf(
        // translators: %1$s = registration shortcode, %2$s = profile edit shortcode
        esc_html__(
            'When you create a registration form, you get two shortcodes: one for embedding the registration form: %1$s, and one for the profile edit page: %2$s.',
            'wp-user-frontend'
        ),
        '<code>[wpuf_profile type="registration" id="3573"]</code>',
        '<code>[wpuf_profile type="profile" id="3573"]</code>'
    );
    ?>
</p>

<p>
    <?php
    esc_html_e(
        'You already know how to create a registration form in WP User Frontend Pro and embed it into a page. The same process applies when creating the profile edit page.',
        'wp-user-frontend'
    );
    ?>
</p>

<h2><?php esc_html_e( 'How to Get the Shortcode', 'wp-user-frontend' ); ?></h2>

<p>
    <?php
    printf(
        // translators: %1$s and %2$s = <strong> tags
        esc_html__(
            'We assume you\'ve already created a registration form. If not, you can use the default form that was automatically created during plugin installation. To get the shortcode, go to %1$sUser Frontend%2$s → %3$sRegistration Forms%4$s in your dashboard. You\'ll find the shortcodes listed on the right side of the screen.',
            'wp-user-frontend'
        ),
        '<strong>',
        '</strong>',
        '<strong>',
        '</strong>'
    );
    ?>
</p>
