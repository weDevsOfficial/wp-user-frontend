<?php
/**
 * Help topic body: login-page
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
<p><?php esc_html_e( 'WP User Frontend Automatically creates important pages when you install it for the first time. You can also create login forms manually.', 'wp-user-frontend' ); ?></p>

<?php
    printf(
    /* translators: 1: URL to the settings page, 2: opening <a> tag, 3: closing </a> tag, 4: opening <strong> tag, 5: closing </strong> tag, 6: opening <strong> tag, 7: closing </strong> tag, 8: shortcode [wpuf-login] */
        esc_html__(
            'Navigate to %2$sSettings%3$s → %4$sLogin/Registration%5$s tab. In this page, you will find several useful settings related to WPUF login. You can override default registration and login forms with WPUF login & registration feature if you want. To do this, check the %6$sLogin/Registration override option%7$s. You can also specify the login page. WPUF automatically adds the default login page that it has created. If you manually create one, use the following shortcode – %8$s. Simply, create a new page and put the above shortcode. Finally, publish the page and add it to the Login Page option in the settings.',
            'wp-user-frontend'
        ),
        esc_url( admin_url( 'admin.php?page=wpuf-settings#wpuf_profile' ) ),
        '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf-settings#wpuf_profile' ) ) . '" target="_blank">',
        '</a>',
        '<strong>',
        '</strong>',
        '<strong>',
        '</strong>',
        '<code>[wpuf-login]</code>'
    )
	?>
