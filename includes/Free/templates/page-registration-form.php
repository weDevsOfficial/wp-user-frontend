<?php
$crown_icon = WPUF_ROOT . '/assets/images/crown.svg';

$pro_features = [
    [
        'icon' => 'icon-doc.svg',
        'title' => sprintf( esc_html__( 'Registration form %s builder', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-profile.svg',
        'title' => sprintf( esc_html__( 'Profile form %s builder', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-money.svg',
        'title' => sprintf( esc_html__( 'Create & Sell %s Subscription %s Package', 'wp-user-frontend' ), '<span class="line-break"></span>', '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-templates.svg',
        'title' => sprintf( esc_html__( 'Pre-defined %s Templates', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-checked.svg',
        'title' => sprintf( esc_html__( 'Approval System %s after Registration', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-mention.svg',
        'title' => esc_html__( 'Email Notifications', 'wp-user-frontend' ),
    ],
    [
        'icon' => 'icon-settings.svg',
        'title' => esc_html__( 'Custom Field', 'wp-user-frontend' ),
    ],
    [
        'icon' => 'icon-buddypress.svg',
        'title' => sprintf( esc_html__( 'BuddyPress %s Support', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-groups.svg',
        'title' => sprintf( esc_html__( 'Social Login & %s Registration', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
];

$email_integrations = [
    [
        'icon' => 'icon-mailchimp.svg',
        'title' => sprintf( esc_html__( 'Mailchimp %s Support', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-getresponse.svg',
        'title' => sprintf( esc_html__( 'GetResponse %s Support', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-convertkit.svg',
        'title' => sprintf( esc_html__( 'ConvertKit %s Support', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-campaign-monitor.svg',
        'title' => sprintf( esc_html__( 'Campaign Monitor %s Support', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
    [
        'icon' => 'icon-mailpoet.svg',
        'title' => sprintf( esc_html__( 'Mailpoet %s Support', 'wp-user-frontend' ), '<span class="line-break-tablet"></span>' ),
    ],
    [
        'icon' => 'icon-mailpoet3.svg',
        'title' => sprintf( esc_html__( 'Mailpoet 3 Support', 'wp-user-frontend' ), '<span class="line-break"></span>' ),
    ],
];

?>
<div class="wpuf-registration-page-area">
    <header>
        <div class="wpuf-logo-area">
            <img src="<?php echo WPUF_ASSET_URI . '/images/wpuf-pro-2.svg'; ?>" alt="WPUF Pro">
        </div>
        <div class="wpuf-menu-area">
            <ul>
                <li>
                    <a target="_blank" rel="noopener noreferrer" href="https://wedevs.com/docs/wp-user-frontend-pro/">
                        <img src="<?php echo WPUF_ASSET_URI . '/images/doc.svg' ?>" alt="">
                        <?php esc_html_e( 'Docs', 'wp-user-frontend' ); ?>
                    </a>
                </li>
                <li>
                    <a class="button button-primary" target="_blank" rel="noopener noreferrer" href="https://headwayapp.co/wp-user-frontend-changelog">
                        <?php esc_html_e( 'What\'s New?', 'wp-user-frontend' ); ?>
                    </a>
                </li>
            </ul>
        </div>
    </header>
    <div class="wpuf-box flex space-between">
        <div class="wpuf-box-inner position-relative">
            <h3><?php esc_html_e( 'Registration Form', 'wp-user-frontend' ); ?><span class="capsule green text-white"><?php esc_html_e( 'Free', 'wp-user-frontend' ); ?></span></h3>
            <p class="text-gray heading-details">
                <?php printf( esc_html__( 'Use the following shortcode to add a %s simple and default WordPress %s registration form.', 'wp-user-frontend' ), '<span class="line-break"></span>', '<span class="line-break"></span>' ); ?>
            </p>
            <div class="wpuf-shortcode-area">
                <code>[wpuf-registration]</code>
                <button class="button button-dark button-copy"><?php esc_html_e( 'Copy', 'wp-user-frontend' ); ?></button>
            </div>
            <a href="https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/how-to-setup-registrationlogin-page/" target="_blank" rel="noopener noreferrer" class="button-primary position-absolute how-to-setup">
                <?php esc_html_e( 'How to setup →', 'wp-user-frontend' ); ?>
            </a>
        </div>
        <div class="wpuf-box-inner">
            <img src="<?php echo WPUF_ASSET_URI . '/images/form-banner.svg'; ?>" alt="WPUF Registration Form">
        </div>
    </div>
    <div class="wpuf-box">
        <div class="heading">
            <div class="crown-icon pro-icon">
                <?php echo file_get_contents( $crown_icon ); ?>
            </div>
            <div class="titles">
                <h2><?php esc_html_e( 'Unlock PRO Features', 'wp-user-frontend' ); ?></h2>
                <p class="text-gray heading-details"><?php printf( esc_html__( 'Registration form builder is a two way form which can be used both for user registration %s and profile editing.', 'wp-user-frontend' ), '<span class="line-break"></span>' ); ?></p>
            </div>
        </div>
        <div class="grid">
            <?php
            foreach ( $pro_features as $feature ) {
                ?>
                <div class="single-pro-feature">
                    <img src="<?php echo WPUF_ASSET_URI . '/images/' . $feature['icon']; ?>">
                    <p><?php echo $feature['title']; ?></p>
                </div>
                <?php
            }
            ?>
        </div>
        <h3 class="sub-heading"><?php esc_html_e( 'Email Marketing Integrations', 'wp-user-frontend' ); ?></h3>
        <div class="grid">
            <?php
            foreach ( $email_integrations as $integration ) {
                ?>
                <div class="single-pro-feature">
                    <img src="<?php echo WPUF_ASSET_URI . '/images/' . $integration['icon']; ?>">
                    <p><?php echo $integration['title']; ?></p>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="footer-links">
            <ul>
                <li>
                    <a href="https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/" target="_blank" rel="noopener noreferrer" class="button-learn-more"><?php esc_html_e( 'Learn More →', 'wp-user-frontend' ); ?></a>
                </li>
                <li>
                    <a href="<?php echo esc_url( WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url() ); ?>"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="wpuf-button button-upgrade-to-pro">
                        <?php
                        esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' );
                        ?>
                        <span class="pro-icon icon-white"> <?php echo file_get_contents( $crown_icon ); ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
