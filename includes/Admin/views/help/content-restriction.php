<?php
/**
 * Help topic body: content-restriction
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
        // translators: %1$s and %2$s are HTML tags
        esc_html__(
            'To set content restriction for a certain form, navigate to %1$sPages%2$s',
            'wp-user-frontend'
        ),
        '<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">',
        '</a>'
    );
    ?>
</p>

<ol>
    <li><?php esc_html_e( 'Now, select the page that has the shortcode of the selected form.', 'wp-user-frontend' ); ?></li>
    <li><?php echo wp_kses_post( __( 'Scroll down and you will find the <strong>WPUF Content Restriction</strong> settings.', 'wp-user-frontend' ) ); ?></li>
    <li><?php echo wp_kses_post( __( 'You can set the form visible to three types of people: <strong>Everyone</strong>, <strong>Logged in users only</strong> or <strong>Subscription users only</strong>', 'wp-user-frontend' ) ); ?></li>
    <li><?php echo wp_kses_post( __( 'You can also set <strong>subscription plans</strong> for the form. For this, check the box of relevant subscription pack.', 'wp-user-frontend' ) ); ?></li>
    <li><?php esc_html_e( 'Finally, update the page.', 'wp-user-frontend' ); ?></li>
</ol>
