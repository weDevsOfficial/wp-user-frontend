<?php
/**
 * Help topic body: user-registration
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
            // translators: %1$s Opening paragraph tag %2$s Opening anchor tag for Registration Forms link %3$s Closing anchor tag %$4s Closing paragraph tag %5$s Opening paragraph tag for the second sentence %6$s Closing paragraph tag for the second sentence.
            __( '%1$sYou can create as many registration forms as you want and assign them to different user roles. Creating Registration forms are easy. Navigate to %2$sRegistration Forms%3$s.%4$s%5$sYou can create new forms just you would create posts in WordPress.%6$s', 'wp-user-frontend' ),
            array(
                'p'      => array(),
                'a'      => array(
					'href' => array(),
					'target' => array(),
				),  // Allow links with href and target attributes
            )
        ),
        '<p>',
        '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf-profile-forms' ) ) . '" target="_blank">',
        '</a>',
        '</p>',
        '<p>',
        '</p>'
    );
	?>

<ol>
    <li><?php esc_html_e( 'Give your form a name and click on Form Elements on the right sidebar.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'The form elements will appear to the Form Editor tab with some options.', 'wp-user-frontend' ); ?></li>
</ol>

<p><?php esc_html_e( 'From settings you can –', 'wp-user-frontend' ); ?></p>

<ul>
    <li><?php esc_html_e( 'Assign New User Roles', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'Can redirect to any custom page or same page with successful message', 'wp-user-frontend' ); ?></li>
</ul>

<h3><?php esc_html_e( 'Showing Registration Form', 'wp-user-frontend' ); ?></h3>

<ul>
    <li><?php esc_html_e( 'By using short-code you can show your registration form into any page or post.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You will get different short-codes for each registration forms separately.', 'wp-user-frontend' ); ?></li>
</ul>
