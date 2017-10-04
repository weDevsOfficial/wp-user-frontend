<?php
$left_column = array(
    array(
        'heading'   => 'Setting Up the Plugin',
        'questions' => array(
            array(
                'title' => 'Video on initial plugin setup',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/videos/setting-up-the-plugin/'
            ),
            array(
                'title' => 'Required Page Setup',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/wpuf-shortcodes/'
            ),
            array(
                'title' => 'List of Available Shortcodes',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/wpuf-shortcodes/'
            ),
        )
    ),
    array(
        'heading'   => 'Frontend Dashboard, Registration and Profile Editing',
        'questions' => array(
            array(
                'title' => 'Setting Up Frontend Dashboard for Users',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/creating-posting-forms/'
            ),
            array(
                'title' => 'Allowing User Profile Editing From the Frontend',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/'
            ),
            array(
                'title' => 'Creating Registration Forms',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/'
            ),
        )
    ),
);

$right_column = array(
    array(
        'heading'   => 'Frontend Posting',
        'questions' => array(
            array(
                'title' => 'Creating Posting Forms',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/creating-posting-forms/'
            ),
            array(
                'title' => 'How to Take Guest Posting',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/'
            ),
            array(
                'title' => 'Setting Up Content Restriction',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/content-restriction/'
            ),
        )
    ),
    array(
        'heading'   => 'Subscription and Payments',
        'questions' => array(
            array(
                'title' => 'Charging Users for Submitting a Post',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/creating-posting-forms/'
            ),
            array(
                'title' => 'Creating Subscription Plans for Users',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/'
            ),
            array(
                'title' => 'Recurring Payments for Subscriptions',
                'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/'
            ),
        )
    ),
);
?>

<div class="wrap">
    <h1><?php _e( 'General Help Questions', 'wpuf' ); ?> <a href="https://wedevs.com/docs/wp-user-frontend-pro/?utm_source=wpuf-help-page&utm_medium=button-primary&utm_campaign=view-all-docs" target="_blank" class="page-title-action"><span class="dashicons dashicons-external" style="margin-top: 8px;"></span> <?php _e( 'View all Documentations', 'wpuf' ); ?></a></h1>

    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">
            <div class="postbox-container">
                <div class="meta-box-sortables">

                    <?php foreach ($left_column as $postbox) { ?>

                        <div class="postbox">
                            <h2 class="hndle"><?php echo $postbox['heading']; ?></h2>

                            <div class="wpuf-help-questions">
                                <ul>
                                    <?php foreach ($postbox['questions'] as $question) { ?>
                                        <li><span class="dashicons dashicons-media-text"></span> <a href="<?php echo trailingslashit( $question['link'] ); ?>?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=<?php echo sanitize_title( $question['title'] ); ?>" target="_blank"><?php echo $question['title']; ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>

                    <?php } ?>
                </div>
            </div>

            <div class="postbox-container">
                <div class="meta-box-sortables">

                    <?php foreach ($right_column as $postbox) { ?>

                        <div class="postbox">
                            <h2 class="hndle"><?php echo $postbox['heading']; ?></h2>

                            <div class="wpuf-help-questions">
                                <ul>
                                    <?php foreach ($postbox['questions'] as $question) { ?>
                                        <li><span class="dashicons dashicons-media-text"></span> <a href="<?php echo trailingslashit( $question['link'] ); ?>?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=<?php echo sanitize_title( $question['title'] ); ?>" target="_blank"><?php echo $question['title']; ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>

                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="help-blocks">
        <div class="help-block">
            <img src="<?php echo WPUF_ASSET_URI; ?>/images/help/like.svg" alt="<?php esc_attr_e( 'Like The Plugin?', 'wpuf' ); ?>">

            <h3><?php _e( 'Like The Plugin?', 'wpuf' ); ?></h3>

            <p><?php _e( 'Your Review is very important to us as it helps us to grow more.', 'wpuf' ); ?></p>

            <a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/?rate=5#new-post"><?php _e( 'Review Us on WP.org', 'wpuf' ); ?></a>
        </div>

        <div class="help-block">
            <img src="<?php echo WPUF_ASSET_URI; ?>/images/help/bugs.svg" alt="<?php esc_attr_e( 'Found Any Bugs?', 'wpuf' ); ?>">

            <h3><?php _e( 'Found Any Bugs?', 'wpuf' ); ?></h3>

            <p><?php _e( 'Report any Bug that you Discovered, Get Instant Solutions.', 'wpuf' ); ?></p>

            <a target="_blank" class="button button-primary" href="https://github.com/weDevsOfficial/wp-user-frontend"><?php _e( 'Report to GitHub', 'wpuf' ); ?></a>
        </div>

        <div class="help-block">
            <img src="<?php echo WPUF_ASSET_URI; ?>/images/help/support.svg" alt="<?php esc_attr_e( 'Need Any Assistance?', 'wpuf' ); ?>">

            <h3><?php _e( 'Need Any Assistance?', 'wpuf' ); ?></h3>

            <p><?php _e( 'Our EXPERT Support Team is always ready to Help you out.', 'wpuf' ); ?></p>

            <a target="_blank" class="button button-primary" href="https://wedevs.com/account/tickets/?utm_source=wpuf-help-page&utm_medium=help-block&utm_campaign=need-assistance"><?php _e( 'Contact Support', 'wpuf' ); ?></a>
        </div>
    </div>

</div>


<style>
.wpuf-help-questions ul {
    margin: 5px 0;
}

.wpuf-help-questions li {
    padding: 10px 5px;
    margin: 0;
    display: block;
    border-bottom: 1px solid #eee;
}

.wpuf-help-questions li:hover {
    background-color: #F5F5F5;
}

.wpuf-help-questions li:last-child {
    border-bottom: none;
}

.wpuf-help-questions li .dashicons {
    color: #ccc;
    margin-top: -3px;
}

.wpuf-help-questions li .dashicons-media-text {
    padding-left: 8px;
}

.wpuf-help-questions li .dashicons-arrow-right-alt2 {
    float: right;
}

.help-blocks {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 30px;
}

.help-blocks .help-block {
    flex: 1;
    align-self: flex-start;
    min-width: 25%;
    max-width: 30%;
    border: 1px solid #ddd;
    margin-right: 2%;
    margin-bottom: 25px;
    border-radius: 3px;
    padding: 25px 15px;
    text-align: center;
    background: #fff;
}

.help-blocks .help-block img {
    max-height: 70px;
}
</style>