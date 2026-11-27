<?php
/**
 * Help topic body: frontend-posting
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
        // translators:  %1$s. Opening paragraph tag. %2$s. Opening strong tag for emphasis on "create new". %3$s. Closing strong tag. %4$s. Closing paragraph tag. %5$s. Opening paragraph tag. %6$s. Opening anchor tag for "Post Forms" menu link. %7$s. Closing anchor tag. %8$s. Closing paragraph tag. %9$s. Opening paragraph tag. %10$s. Opening strong tag for emphasis on "use the shortcodes". %11$s. Closing strong tag. %12$s. Closing paragraph tag.
        __( '%1$sPosting Forms are used to %2$screate new%3$s blog posts, WooCommerce Products, Directory Listing Entries etc. You can create any custom post type from the front using this feature. You just need to create a form with necessary fields and embed the form in a page and your users will be able to create posts from frontend in no time.%4$s
        %5$sTo create a posting form, go to %6$sPost Forms%7$s → Add Form and start building your ultimate frontend posting forms.%8$s
        %9$sAfter building your forms, %10$suse the shortcodes%11$s on any new page or post and publish them before sharing.%12$s', 'wp-user-frontend' ),
        array(
            'p'      => array(), 
            'strong' => array(), 
            'a'      => array( 'href' => array(), 'target' => array() ), 
            'code'   => array()
        )
    ),
    '<p>',
    '<strong>',
    '</strong>',
    '</p>',
    '<p>',
    '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf-post-forms' ) ) . '" target="_blank">',
    '</a>',
    '</p>',
    '<p>',
    '<strong>',
    '</strong>',
    '</p>'
);
?>
