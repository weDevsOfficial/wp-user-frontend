<?php
/**
 * Help topic body: subscription-payment
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
<p><?php esc_html_e( 'WP User Frontend allows you to create as many subscription packs you want. Simply, navigate to - WP-Admin → User Frontend → Subscription → Add Subscription', 'wp-user-frontend' ); ?></p>

<ol>
    <li><?php esc_html_e( 'Enter your subscription name and pack description.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'Include the billing amount and the validity of the pack. You can choose day, week, month or year in case of expiry.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You can enable post expiration if you want to expire post after a certain amount of time. To do so check the Enable Post Expiration box.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'This will enable some new settings. You have to specify post expiration time and the post status after the post expires.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You can also notify users when a post expires. To do so, check the Send Mail option.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'Now, enter the message you want to send the user in the Post Expiration Message field.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You can specify the number of posts you are giving away with this subscription pack. If you want to provide unlimited posts, enter -1 in the number of posts field.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You can also set the number of pages and custom CSS. For unlimited value, enter −1', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'WPUF offers you recurring payment while creating a Subscription pack. Enable this option if you want to set recurring payment for this pack. It will provide you some new options for the recurring payment.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'Now, select the billing cycle.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You can also stop the billing cycle if you want. If you don\'t want to stop the cycle select Never.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'To enable trial period, check the Trial box. You can set the trial amount to be paid by the user for trial period.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'Now, specify the trial period. Enter number of days, week, month or year.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'You can also enable post number rollback. If enabled, number of posts will be restored if the post is deleted.', 'wp-user-frontend' ); ?></li>
    <li><?php esc_html_e( 'Finally, click on the publish button to create the subscription pack.', 'wp-user-frontend' ); ?></li>
</ol>

<h2><?php esc_html_e( 'Subscription Packs on Frontend', 'wp-user-frontend' ); ?></h2>
<p><?php esc_html_e( 'To view the created subscription packs on frontend, visit the Subscription page.', 'wp-user-frontend' ); ?></p>

<p>
    <?php
    printf(
        // translators: %1$s and %2$s are HTML tags
        esc_html__(
            'Short-code for creating the Subscription page – %1$s[wpuf_sub_pack]%2$s.',
            'wp-user-frontend'
        ),
        '<code>',
        '</code>'
    );
    ?>
</p>
<h2><?php esc_html_e( 'Payment &amp; Gateway Settings', 'wp-user-frontend' ); ?></h2>
<p><?php esc_html_e( 'Post subscription and payment system is a module where you can add paid posting system with WP User Frontend. You can introduce two types of payment system. Pay per post and subscription pack based.', 'wp-user-frontend' ); ?></p>

<div class="wpuf-info-card pay-per-post-card">
    <h3 style="margin-top: 0;">
        <?php esc_html_e( 'Pay Per Post', 'wp-user-frontend' ); ?>
    </h3>
    <p><?php esc_html_e( 'Pay Per Post lets you charge users per post. ', 'wp-user-frontend' ); ?></p>
    <ol style="margin-left: 1.5em;">
        <li><?php esc_html_e( 'Go to Post Form Settings → Payment Settings.', 'wp-user-frontend' ); ?></li>
        <li><?php esc_html_e( 'Toggle on "Enable Payment".', 'wp-user-frontend' ); ?></li>
        <li><?php esc_html_e( 'Choose "Pay Per Post" from the dropdown.', 'wp-user-frontend' ); ?></li>
        <li><?php esc_html_e( 'Set a price for each post.', 'wp-user-frontend' ); ?></li>
        <li><?php esc_html_e( 'Select a Payment Success redirection Page.', 'wp-user-frontend' ); ?></li>
        <li><?php esc_html_e( 'Save your settings.', 'wp-user-frontend' ); ?></li>
    </ol>
    <ul style="margin-left: 1.5em; list-style: disc;">
        <li><?php esc_html_e( 'Users will see a payment notice before the form.', 'wp-user-frontend' ); ?></li>
        <li><?php esc_html_e( 'After submitting, the post stays pending and redirects to the payment page.', 'wp-user-frontend' ); ?></li>
        <li>
            <strong style="color: #d63638;">
                <?php esc_html_e( 'Currently, only PayPal is supported.', 'wp-user-frontend' ); ?>
            </strong>
        </li>
        <li><?php esc_html_e( 'After successful payment, users return to the success page and the post gets published.', 'wp-user-frontend' ); ?></li>
    </ul>
</div>

<h2><?php esc_html_e( 'Subscription Pack', 'wp-user-frontend' ); ?></h2>

<p><?php esc_html_e( 'There is an another option for charged posting. With this feature, you can create unlimited subscription pack. In each pack, you can configure the number of posts, validity date and the cost.', 'wp-user-frontend' ); ?></p>
<p><?php esc_html_e( 'When a user buys a subscription package, he gets to create some posts (e.g. 10) in X days (e.g: 30 days). If he crosses the number of posts or the validity date, he can\'t post again. You can force the user to buy a pack before posting "Settings → Payments → Force pack purchase".', 'wp-user-frontend' ); ?></p>
<p></p>
<p>
    <?php
    printf(
        // translators: %1$s and %2$s are HTML tags, %3$s and %4$s are HTML tags
        esc_html__(
            'To show the subscription packs in a page, you can use the shortcode: %1$s[wpuf_sub_pack]%2$s. To show the user subscription info: %3$s[wpuf_sub_info]%4$s. The info will show the user about his pack\'s remaining post count and expiration date of his pack.',
            'wp-user-frontend'
        ),
        '<code>',
        '</code>',
        '<code>',
        '</code>'
    );
    ?>
</p>

<h2><?php esc_html_e( 'Payment Gateway', 'wp-user-frontend' ); ?></h2>

<p><?php esc_html_e( 'Currently only PayPal basic gateway is supported. The plugin is extension aware, that means other gateways can be integrated.', 'wp-user-frontend' ); ?></p>
