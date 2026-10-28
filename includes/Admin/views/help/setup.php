<?php
/**
 * Help topic body: setup
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
<p><?php esc_html_e( 'Setting up WP User Frontend is very easy. Here are few things that you should consider.', 'wp-user-frontend' ); ?></p>

<ol>
    <li>
        <?php
        printf(
            // translators: %1$s and %2$s are HTML tags
            esc_html__(
                '%1$sInstall WPUF Pages%2$s with a single click. Check your admin dashboard for a message to install WPUF required pages.',
                'wp-user-frontend'
            ),
            '<strong>',
            '</strong>'
        );
        ?>
    </li>
    <li>
        <?php esc_html_e( 'You can create amazing frontend posting forms with more than 20 useful form fields.', 'wp-user-frontend' ); ?>
    </li>
    <li>
        <?php
        printf(
            // translators: %1$s and %2$s are HTML tags
            esc_html__(
                'Posting the forms in the frontend is also very easy. All you have to do is %1$sput the shortcode%2$s of your form to a page.',
                'wp-user-frontend'
            ),
            '<strong>',
            '</strong>'
        );
        ?>
    </li>
    <li>
        <?php
        printf(
            // translators: %1$s and %2$s are HTML tags
            esc_html__(
                'Building registration &amp; profile editing forms has never been easier, thanks to WP User Frontend. %1$sBuild registration &amp; profile forms%2$s on the go with simple steps.',
                'wp-user-frontend'
            ),
            '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf-profile-forms' ) ) . '" target="_blank">',
            '</a>'
        );
        ?>
    </li>
    <li>
        <?php
            printf(
                // translators: %1$s: {login_forms}, %2$s: {subscription_forms}, %3$s: {guest_posting}
                esc_html__(
                    'Add customized %1$slogin forms%2$s using simple shortcodes and override default WordPress login and registration.', 'wp-user-frontend'
                ),
                '<strong>',
                '</strong>'
            );
            ?>
    </li>
    <li>
        <?php
        printf(
        // translators: %1$s: {login_forms}, %2$s: {subscription_forms}, %3$s: {guest_posting}
            esc_html__(
                'Create %1$ssubscription packs%2$s and charge users for posting.',
                'wp-user-frontend'
            ),
            '<strong>',
            '</strong>'
        );
        ?>
    </li>
    <li>
        <?php
        printf(
        // translators: %1$s: {login_forms}, %2$s: {subscription_forms}, %3$s: {guest_posting}
            esc_html__(
                'Enable %1$sguest posting%2$s and earn from each posts without any difficulties.',
                'wp-user-frontend'
            ), '<strong>', '</strong>'
        );
        ?>
    </li>
</ol>
