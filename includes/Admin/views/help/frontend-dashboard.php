<?php
/**
 * Help topic body: frontend-dashboard
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
<?php
    printf(
        wp_kses(
            // translators: %1$s. Opening paragraph tag. %2$s Opening strong tag for "Frontend Dashboard". %3$s Closing strong tag. %4$s Opening strong tag for "My Account". %5$s Closing strong tag. %6$s Closing paragraph tag.
            __( '%1$sWP User Frontend generates %2$sFrontend Dashboard%3$s and %4$sMy Account%5$s page for all your users. Using these pages, they can get a list of their posts and subscriptions directly at frontend. They can also customize the details of their profile. You don\'t need to give them access to the backend at all!%6$s', 'wp-user-frontend' ),
            array(
                'p'      => array(),
                'strong' => array(),
            )
        ),
        '<p>',
        '<strong>',
        '</strong>',
        '<strong>',
        '</strong>',
        '</p>'
    );
    printf(
        wp_kses(
            // translators: %1$s Opening paragraph tag. %2$s Opening anchor tag for creating a new page. %3$s Closing anchor tag. %4$s Opening code tag for shortcode. %5$s Closing code tag. %6$s Opening anchor tag for "my account" page documentation link. %7$s Closing anchor tag. %8$s Closing paragraph tag.
            __( '%1$sTo create this page, %2$screate a new page%3$s, put a title and simply copy-paste the following shortcode: %4$s[wpuf_dashboard]%5$s. Alternatively, there is an unified %6$smy account page%7$s as well. Finally, hit the publish button and you are done.%8$s', 'wp-user-frontend' ),
            array(
                'p'      => array(),
                'a'      => array(
					'href' => array(),
					'target' => array(),
				),  // Allow links with href and target attributes
                'code'   => array(),
            )
        ),
        '<p>',
        '<a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '" target="_blank">',
        '</a>',
        '<code>',
        '</code>',
        '<a href="https://wedevs.com/docs/wp-user-frontend-pro/frontend/how-to-create-my-account-page/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=unified-my-account-page" target="_blank">',
        '</a>',
        '</p>'
    );
