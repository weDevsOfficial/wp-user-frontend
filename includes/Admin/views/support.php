<?php
$current_user = wp_get_current_user();

$articles = [
    'setup' => [
        [
            'title' => __( 'How to Install', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/',
        ],
        [
            'title' => __( 'License Activation', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/troubleshoot/license-activation/',
        ],
        [
            'title' => __( 'Shortcodes', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/wpuf-shortcodes/',
        ],
        [
            'title' =>  __( 'User Dashboard', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/getting-started/user-dashboard/',
        ],
    ],
    'posting' => [
        [
            'title' => __( 'Creating Posting Forms', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/creating-posting-forms/',
        ],
        [
            'title' => __( 'Available Form Elements', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/form-elements/',
        ],
        [
            'title' => __( 'Creating Forms Using The Form Templates', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/form-templates/',
        ],
        [
            'title' => __( 'How to Allow Guest Posting', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/guest-posting/',
        ],
        [
            'title' => __( 'Setup Automatic Post Expiration', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/using-post-expiration-wp-user-frontend/',
        ],
        [
            'title' => __( 'How to create Multistep forms', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/how-to-add-multi-step-form/',
        ],
    ],
    'dashboard' => [
        [
            'title' => __( 'Setting up Frontend Dashboard for Users', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/configuring-dashboard-settings/',
        ],
        [
            'title' => __( 'Unified My Account Page', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/how-to-create-my-account-page/',
        ],
        [
            'title' => __( 'Showing meta fields in frontend', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/frontend/showing-meta-fields-in-frontend/',
        ],
    ],
    'settings' => [
        [
            'title' => __( 'General Options', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/configuring-general-options/',
        ],
        [
            'title' => __( 'Dashboard Settings', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/configuring-dashboard-settings/',
        ],
        [
            'title' => __( 'Login Registration Settings', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/login-registration-settings/',
        ],
        [
            'title' => __( 'Payment Settings', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/settings/configuring-payment-settings/',
        ],
    ],
    'registration' => [
        [
            'title' => __( 'Creating Registration Form', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-forms/',
        ],
        [
            'title' => __( 'Creating a Multistep Registration Form', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/creating-a-multistep-registration-form/',
        ],
        [
            'title' => __( 'Setting Up Confirmation Message', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/setup-confirmation-message/',
        ],
        [
            'title' => __( 'Paid Membership Registration', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/paid-membership-registration/',
        ],
        [
            'title' => __( 'Setting Up Email Verification for New Users', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/setting-up-email-verification-for-new-users/',
        ],
    ],
    'profile' => [
        [
            'title' => __( 'Creating a Profile Editing Form', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/wordpress-edit-user-profile-from-front-end/',
        ],
    ],
    'subscription' => [
        [
            'title' => __( 'Creating Subscription Packs', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/creating-subscription-packs/',
        ],
        [
            'title' => __( 'Payment & Gateway Settings', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/configuring-payment-settings/',
        ],
        [
            'title' => __( 'Setting Up Recurring Payment', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/setting-up-recurring-payment/',
        ],
        [
            'title' => __( 'Forcing Subscription Pack For Post Submission', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/forcing-subscription-pack-for-post-submission/',
        ],
        [
            'title' => __( 'How to Charge for Each Post Submission?', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/how-to-charge-for-each-post-submission/',
        ],
        [
            'title' => __( 'Creating Coupons', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/coupons/',
        ],
    ],

    'developer' => [
        [
            'title' => __( 'Action Hook Field', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/action-hook-field/',
        ],
        [
            'title' => __( 'Add a New Tab on My Account Page', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/add-a-new-tab-on-my-account-page/',
        ],
        [
            'title' => __( 'Insert/update checkbox or radio field data as serialize', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/insertupdate-checkbox-or-radio-field-data-as-serialize/',
        ],
        [
            'title' => __( 'Filters', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/filters/',
        ],
        [
            'title' => __( 'Actions', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/developer-docs/actions/',
        ],
        [
            'title' => __( 'Changelog', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/changelog/',
        ],
    ],
    'restriction' => [
        [
            'title' => __( 'Content Restriction for Logged in Users', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/content-restriction/',
        ],
        [
            'title' => __( 'Restricting Content by User Roles', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/restricting-content-by-user-roles/',
        ],
        [
            'title' => __( 'Restricting Contents for Different Subscription Packs', 'wp-user-frontend' ),
            'link'  => 'https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/restricting-contents-for-different-subscription-packs/',
        ],
    ],
];

/**
 * Print related articles
 *
 * @param array $articles
 *
 * @return void
 */
function wpuf_help_related_articles( $articles ) {
    ?>
    <h2><?php esc_html_e( 'Related Articles:', 'wp-user-frontend' ); ?></h2>

    <ul class="related-articles">
    <?php
        foreach ( $articles as $article ) {
            ?>
            <li>
                <span class="dashicons dashicons-media-text"></span>
                <a href="<?php echo  esc_attr( trailingslashit( $article['link'] ) ); ?>?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=<?php echo esc_attr( $article['title'] ); ?>" target="_blank"><?php echo esc_attr( $article['title'] ); ?></a>
            </li>
            <?php
        } ?>
    </ul>
    <?php
}
?>

<div class="wrap wpuf-help-page">
    <h1><?php esc_html_e( 'General Help Questions', 'wp-user-frontend' ); ?> <a href="https://wedevs.com/docs/wp-user-frontend-pro/?utm_source=wpuf-help-page&utm_medium=button-primary&utm_campaign=view-all-docs" target="_blank" class="page-title-action"><span class="dashicons dashicons-external" style="margin-top: 8px;"></span> <?php esc_html_e( 'View all Documentations', 'wp-user-frontend' ); ?></a></h1>

    <div class="wpuf-subscribe-box">
        <div class="wpuf-text-wrap">
            <h3><?php esc_html_e( 'Subscribe to Our Newsletter', 'wp-user-frontend' ); ?></h3>
            <p>
                <?php echo wp_kses_post(
                    __(
                        'Subscribe to our newsletter for regular <strong>tips</strong>, <strong>offers</strong> and <strong>news updates</strong>.',
                        'wp-user-frontend'
                    )
                ); ?>
            </p>
        </div>
        <div class="wpuf-form-wrap">
            <form id="wemail-embedded-subscriber-form" method="post"
                  action="https://api.getwemail.io/v1/embed/subscribe/8da67b42-c367-4ad3-ae70-5cf63635a832">
                <div class="form-group">
                    <label for="wemail-first-name">First Name <span
                            class="required-indicator">*</span></label>
                    <div>
                        <input
                            type="text"
                            name="first_name"
                            id="wemail-first-name"
                            required="required"
                            placeholder="<?php echo esc_attr( "Enter first name" ); ?>"
                            value="<?php echo esc_attr( $current_user->first_name ); ?>"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="wemail-email">Email <span
                            class="required-indicator">*</span></label>
                    <div>
                        <input
                            type="email"
                            name="email"
                           required="required"
                            id="wemail-email"
                            placeholder="<?php echo esc_attr( "Enter email" ); ?>"
                            value="<?php echo esc_attr( $current_user->user_email ); ?>"
                           class="form-control">
                    </div>
                </div>
                <input type="hidden" name="tag" value="698f5d31-4ef9-430a-a6f3-7f4bb24cdaf9">
                <div>
                    <button class="button button-primary"><?php esc_html_e( 'Subscribe', 'wp-user-frontend' ); ?></button>
                </div>

            </form>
        </div>
    </div>

    <div class="wpuf-help-tabbed">
        <nav>
            <ul>
                <li class="tab-current">
                    <a href="#setup">
                        <span class="dashicons dashicons-admin-home"></span>
                        <label><?php esc_html_e( 'Plugin Setup', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#frontend-posting">
                        <span class="dashicons dashicons-media-text"></span>
                        <label><?php esc_html_e( 'Frontend Posting', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#frontend-dashboard">
                        <span class="dashicons dashicons-dashboard"></span>
                        <label><?php esc_html_e( 'Frontend Dashboard', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#user-registration">
                        <span class="dashicons dashicons-admin-users"></span>
                        <label><?php esc_html_e( 'User Registration', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#login-page">
                        <span class="dashicons dashicons-lock"></span>
                        <label><?php esc_html_e( 'User Login', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#profile-editing">
                        <span class="dashicons dashicons-edit"></span>
                        <label><?php esc_html_e( 'Profile Editing', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#subscription-payment">
                        <span class="dashicons dashicons-cart"></span>
                        <label><?php esc_html_e( 'Subscription &amp; Payment', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
                <li>
                    <a href="#content-restriction">
                        <span class="dashicons dashicons-unlock"></span>
                        <label><?php esc_html_e( 'Content Restriction', 'wp-user-frontend' ); ?></label>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="nav-content">
            <section id="setup" class="content-current">
                <h2><?php esc_html_e( 'Plugin Setup Guide', 'wp-user-frontend' ); ?></h2>

                <p><?php esc_html_e( 'Setting up WP User Frontend is very easy. Here are few things that you should consider.', 'wp-user-frontend' ); ?></p>

                <ol>
                    <li>
                        <?php
                        printf(
                            esc_html__(
                                // translators: %1$s and %2$s are HTML tags
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
                            esc_html__(
                            // translators: %1$s and %2$s are HTML tags
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
                            esc_html__(
                            // translators: %1$s and %2$s are HTML tags
                                'Building registration &amp; profile editing forms has never been easier, thanks to WP User Frontend. %1$sBuild registration &amp; profile forms%2$s on the go with simple steps.',
                                'wp-user-frontend'
                            ),
                            '<a href="'. esc_url( admin_url( 'admin.php?page=wpuf-profile-forms' ) ) . '" target="_blank">',
                            '</a>'
                        );
                        ?>
                    </li>
                    <li>
                        <?php
                            printf(
                                // translators: %1$s: {login_forms}, %2$s: {subscription_forms}, %3$s: {guest_posting}
                                esc_html__( 'Add customized %1$slogin forms%2$s using simple shortcodes and override default WordPress login and registration.', 'wp-user-frontend' ),
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

                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/getting-started/how-to-install/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=how-to-install" target="_blank"><?php esc_html_e( 'Learn More About Installation', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['setup'] ); ?>
            </section>
            <section id="frontend-posting">
                <h2><?php esc_html_e( 'Frontend Posting', 'wp-user-frontend' ); ?></h2>

                <?php
                printf(
                    __( '%1$sPosting Forms are used to %2$screate new%3$s blog posts, WooCommerce Products, Directory Listing Entries etc. You can create any custom post type from the front using this feature. You just need to create a form with necessary fields and embed the form in a page and your users will be able to create posts from frontend in no time.%4$s
                %5$sTo create a posting form, go to %6$sPost Forms%7$s → Add Form and start building your ultimate frontend posting forms.%8$s
                %9$sAfter building your forms, %10$suse the shortcodes%11$s on any new page or post and publish them before sharing.%12$s', 'wp-user-frontend' ),
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

                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=frontend-posting" target="_blank"><?php esc_html_e( 'Learn More About Frontend Posting', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['posting'] ); ?>
            </section>
            <section id="frontend-dashboard">
                <h2><?php esc_html_e( 'Frontend Dashboard', 'wp-user-frontend' ); ?></h2>

                <?php
                    printf(
                        __( '%1$sWP User Frontend generates %2$sFrontend Dashboard%3$s and %4$sMy Account%5$s page for all your users. Using these pages, they can get a list of their posts and subscriptions directly at frontend. They can also customize the details of their profile. You don’t need to give them access to the backend at all!%6$s', 'wp-user-frontend' ),
                        '<p>',
                        '<strong>',
                        '</strong>',
                        '<strong>',
                        '</strong>',
                        '</p>'
                    );
                    printf(
                        __( '%1$sTo create this page, %2$screate a new page%3$s, put a title and simply copy-paste the following shortcode: %4$s[wpuf_dashboard]%5$s. Alternatively, there is an unified %6$smy account page%7$s as well. Finally, hit the publish button and you are done.%8$s', 'wp-user-frontend' ),
                        '<p>',
                        '<a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '" target="_blank">',
                        '</a>',
                        '<code>',
                        '</code>',
                        '<a href="https://wedevs.com/docs/wp-user-frontend-pro/frontend/how-to-create-my-account-page/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=unified-my-account-page" target="_blank">',
                        '</a>',
                        '</p>'
                    );
                ?>

                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/frontend/configuring-dashboard-settings/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=frontend-dashboard" target="_blank"><?php esc_html_e( 'Learn More About Frontend Dashboard', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['dashboard'] ); ?>
            </section>
            <section id="user-registration">
                <h2><?php esc_html_e( 'User Registration', 'wp-user-frontend' ); ?></h2>

                <?php
                    printf(
                        __( '%1$sYou can create as many registration forms as you want and assign them to different user roles. Creating Registration forms are easy. Navigate to %2$sRegistration Forms%3$s.%4$s%5$sYou can create new forms just you would create posts in WordPress.%6$s', 'wp-user-frontend' ),
                        '<p>',
                        '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf-profile-forms' ) ) . '" target="_blank">',
                        '</a>',
                        '</p>',
                        '<p>',
                        '</p>'
                    );
                ?>

                <ol>
                    <li><?php esc_html_e('Give your form a name and click on Form Elements on the right sidebar.', 'wp-user-frontend'); ?></li>
                    <li><?php esc_html_e('The form elements will appear to the Form Editor tab with some options.', 'wp-user-frontend'); ?></li>
                </ol>

                <p><?php esc_html_e('From settings you can –', 'wp-user-frontend'); ?></p>

                <ul>
                    <li><?php esc_html_e('Assign New User Roles', 'wp-user-frontend'); ?></li>
                    <li><?php esc_html_e('Can redirect to any custom page or same page with successful message', 'wp-user-frontend'); ?></li>
                </ul>

                <h3><?php esc_html_e('Showing Registration Form', 'wp-user-frontend'); ?></h3>

                <ul>
                    <li><?php esc_html_e('By using short-code you can show your registration form into any page or post.', 'wp-user-frontend'); ?></li>
                    <li><?php esc_html_e('You will get different short-codes for each registration forms separately.', 'wp-user-frontend'); ?></li>
                </ul>


                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=registration-profile-forms" target="_blank"><?php esc_html_e( 'Learn More About Registration', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['registration'] ); ?>
            </section>
            <section id="login-page">
                <h2><?php esc_html_e( 'Login Page', 'wp-user-frontend' ); ?></h2>

                <p><?php esc_html_e('WP User Frontend Automatically creates important pages when you install it for the first time. You can also create login forms manually.', 'wp-user-frontend'); ?></p>

                <?php
                    printf(
                    /* translators: 1: URL to the settings page, 2: opening <a> tag, 3: closing </a> tag, 4: opening <strong> tag, 5: closing </strong> tag, 6: opening <strong> tag, 7: closing </strong> tag, 8: shortcode [wpuf-login] */
                        esc_html__(
                            'Navigate to %2$sSettings%3$s → %4$sLogin/Registration%5$s tab. In this page, you will find several useful settings related to WPUF login. You can override default registration and login forms with WPUF login & registration feature if you want. To do this, check the %6$sLogin/Registration override option%7$s. You can also specify the login page. WPUF automatically adds the default login page that it has created. If you manually create one, use the following shortcode – %8$s. Simply, create a new page and put the above shortcode. Finally, publish the page and add it to the Login Page option in the settings.',
                            'wp-user-frontend'
                        ),
                        esc_url( admin_url( 'admin.php?page=wpuf-settings' ) ),
                        '<a href="' . esc_url( admin_url( 'admin.php?page=wpuf-settings' ) ) . '" target="_blank">',
                        '</a>',
                        '<strong>',
                        '</strong>',
                        '<strong>',
                        '</strong>',
                        '<code>[wpuf-login]</code>'
                    )
                ?>

                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/user-login/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=learn-more-login" target="_blank"><?php esc_html_e( 'Learn More About Login', 'wp-user-frontend' ); ?></a>
            </section>
            <section id="profile-editing">
                <h2><?php esc_html_e( 'Creating a Profile Editing Form', 'wp-user-frontend' ); ?></h2>

                <?php
                printf(
                /* translators: 1: shortcode for registration form, 2: shortcode for profile edit, 3: opening <strong> tag, 4: closing </strong> tag, 5: opening <strong> tag, 6: closing </strong> tag */
                    esc_html__(
                        'When you are making a registration form, you get two shortcodes: For embedding the registration form: this is something like %1$s. For profile edit page: this is something like %2$s. You already know how to make a registration form in WP User Frontend Pro and embed that into a page. The very same process is for creating the profile edit page. How to get the shortcode: We assume that you already have created a registration form. If not, you can use the default registration form, that was created automatically while installing the plugin. So to get the shortcode, navigate to %3$sUser Frontend%4$s → %5$sRegistration Forms%6$s and you will be able to see the shortcodes on the right side of your screen.',
                        'wp-user-frontend'
                    ),
                    '<code>[wpuf_profile type="registration" id="3573"]</code>',
                    '<code>[wpuf_profile type="profile" id="3573"]</code>',
                    '<strong>',
                    '</strong>',
                    '<strong>',
                    '</strong>'
                );
                ?>

                <p>
                    <?php
                    printf(
                    // translators: %1$s and %2$s are HTML tags
                        esc_html__(
                        'When you are making a registration form, you get two shortcodes: For embedding the registration form: this is something like %1$s[wpuf_profile type="registration" id="3573"]%2$s',
                            'wp-user-frontend'
                        ),
                        '<code>',
                        '</code>'
                    );
                    ?>
                </p>

                <p>
                    <?php
                    printf(
                        // translators: %1$s and %2$s are HTML tags
                        esc_html__(
                            'For profile edit page: this is something like %1$s[wpuf_profile type="profile" id="3573"]%2$s',
                            'wp-user-frontend'
                        ),
                        '<code>',
                        '</code>'
                    );
                    ?>
                </p>

                <p>
                    <?php
                     esc_html_e(
                        'You already know that how to make a registration form in WP User Frontend Pro and embed that into a page. The very same process is for creating the profile edit page.',
                        'wp-user-frontend');
                    ?>
                </p>

                <h2><?php esc_html_e( 'How to get the shortcode', 'wp-user-frontend' ); ?></h2>

                <p>
                    <?php
                    printf(
                        // translators: %1$s and %2$s are HTML tags
                        esc_html__(
                            'We assume that you already have created a registration form. If not you can use the default registration form, that was created automatically while installing the plugin.
                So to get the shortcode, navigate to %1$sUser Frontend%2$s → %3$sRegistration Forms%4$s and you will be able to see the shortcodes on the right side of your screen.',
                            'wp-user-frontend'
                        ),
                        '<strong>',
                        '</strong>',
                        '<strong>',
                        '</strong>'
                    );
                    ?>
                </p>

                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=registration-profile-forms" target="_blank"><?php esc_html_e( 'Learn More About Profile Editing', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['profile'] ); ?>
            </section>
            <section id="subscription-payment">
                <h2><?php esc_html_e( 'Subscription Payment', 'wp-user-frontend' ); ?></h2>

                <p><?php esc_html_e( 'WP User Frontend allows you to create as many subscription packs you want. Simply, navigate to - WP-Admin → User Frontend → Subscription → Add Subscription', 'wp-user-frontend' ); ?></p>

                <ol>
                    <li><?php esc_html_e( 'Enter your subscription name and pack description.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'Include the billing amount and the validity of the pack. You can choose day, week, month or year in case of expiry.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can enable post expiration if you want to expire post after a certain amount of time. To do so check the Enable Post Expiration box.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'This will enable some new settings. You have to specify post expiration time and the post status after the post expires.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can also notify users when a post expires. To do so, check the Send Mail option.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'Now, enter the message you want to send the user in the Post Expiration Message field.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can specify the number of posts you are giving away with this subscription pack. If you want to provide unlimited posts, enter ‘-1’ in the number of posts field.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can also set the number of pages and custom CSS. For unlimited value, enter ‘-1’.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'WPUF offers you recurring payment while creating a Subscription pack. Enable this option if you want to set recurring payment for this pack. It will provide you some new options for the recurring payment.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'Now, select the billing cycle.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can also stop the billing cycle if you want. If you don’t want to stop the cycle select Never.', 'wp-user-frontend' ); ?></li>
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
                <h2><?php esc_html_e('Payment &amp; Gateway Settings', 'wp-user-frontend'); ?></h2>
                <p><?php esc_html_e('Post subscription and payment system is a module where you can add paid posting system with WP User Frontend. You can introduce two types of payment system. Pay per post and subscription pack based.', 'wp-user-frontend'); ?></p>

                <h2><?php esc_html_e('Pay Per Post', 'wp-user-frontend'); ?></h2>

                <p>
                    <?php esc_html_e('With this you can introduce pay per post feature where users pay to publish their posts each post. When pay per post is enabled from “Settings → Payments → Charge for posting“, users see a notice right before the post creation form in frontend about payment. When the submits a post, the post status gets pending and he is redirected to the payment page (to setup the payment page, create a Page Payment and select the page at “Settings → Payments → Payment Page“. No shortcode is needed). Currently by default PayPal is only supported gateway. Upon selecting PayPal, he is redirected to PayPal for payment. After successful payment he is redirected back to the site and the post gets published.', 'wp-user-frontend'); ?>
                </p>

                <h2><?php esc_html_e('Subscription Pack', 'wp-user-frontend'); ?></h2>

                <p><?php esc_html_e('There is an another option for charged posting. With this feature, you can create unlimited subscription pack. In each pack, you can configure the number of posts, validity date and the cost.', 'wp-user-frontend'); ?></p>
                <p><?php esc_html_e('When a user buys a subscription package, he gets to create some posts (e.g. 10) in X days (e.g: 30 days). If he crosses the number of posts or the validity date, he can’t post again. You can force the user to buy a pack before posting “Settings → Payments → Force pack purchase“.', 'wp-user-frontend'); ?></p>
                <p></p>
                <p>
                    <?php
                    printf(
                        esc_html__(
                            // translators: %1$s and %2$s are HTML tags, %3$s and %4$s are HTML tags
                            'To show the subscription packs in a page, you can use the shortcode: %1$s[wpuf_sub_pack]%2$s. To show the user subscription info: %3$s[wpuf_sub_info]%4$s. The info will show the user about his pack’s remaining post count and expiration date of his pack.',
                            'wp-user-frontend'
                        ),
                        '<code>',
                        '</code>',
                        '<code>',
                        '</code>'
                    );
                    ?>
                </p>

                <h2><?php esc_html_e('Payment Gateway', 'wp-user-frontend'); ?></h2>

                <p><?php esc_html_e('Currently only PayPal basic gateway is supported. The plugin is extension aware, that means other gateways can be integrated.', 'wp-user-frontend'); ?></p>


                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=subscription-payment" target="_blank"><?php esc_html_e( 'Learn More About Payments', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['subscription'] ); ?>
            </section>
            <section id="content-restriction">
                <h2><?php esc_html_e('Content Restriction', 'wp-user-frontend'); ?></h2>
                <p>
                    <?php
                    printf(
                        esc_html__(
                            // translators: %1$s and %2$s are HTML tags
                            'To set content restriction for a certain form, navigate to %1$sPages%2$s',
                            'wp-user-frontend'
                        ),
                        '<a href="'. esc_url( admin_url( 'edit.php?post_type=page' ) ) . '" target="_blank">',
                        '</a>'
                    );
                    ?>
                </p>

                <ol>
                    <li><?php esc_html_e( 'Now, select the page that has the shortcode of the selected form.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'Scroll down and you will find the <strong>WPUF Content Restriction</strong> settings.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can set the form visible to three types of people: <strong>Everyone</strong>, <strong>Logged in users only</strong> or <strong>Subscription users only</strong>', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'You can also set <strong>subscription plans</strong> for the form. For this, check the box of relevant subscription pack.', 'wp-user-frontend' ); ?></li>
                    <li><?php esc_html_e( 'Finally, update the page.', 'wp-user-frontend' ); ?></li>
                </ol>

                <a class="button button-primary button-large" href="https://wedevs.com/docs/wp-user-frontend-pro/content-restriction/?utm_source=wpuf-help-page&utm_medium=help-links&utm_campaign=wpuf-help&utm_term=content-restriction" target="_blank"><?php esc_html_e( 'Learn More About Content Restriction', 'wp-user-frontend' ); ?></a>

                <?php wpuf_help_related_articles( $articles['restriction'] ); ?>
            </section>
        </div>
    </div>

    <div class="help-blocks">
        <div class="help-block">
            <img src="<?php echo esc_url( WPUF_ASSET_URI ); ?>/images/help/like.svg" alt="<?php esc_attr_e( 'Like The Plugin?', 'wp-user-frontend' ); ?>">

            <h3><?php esc_html_e( 'Like The Plugin?', 'wp-user-frontend' ); ?></h3>

            <p><?php esc_html_e( 'Your Review is very important to us as it helps us to grow more.', 'wp-user-frontend' ); ?></p>

            <a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/?rate=5#new-post"><?php esc_html_e( 'Review Us on WP.org', 'wp-user-frontend' ); ?></a>
        </div>

        <div class="help-block">
            <img src="<?php echo esc_url( WPUF_ASSET_URI ); ?>/images/help/bugs.svg" alt="<?php esc_attr_e( 'Found Any Bugs?', 'wp-user-frontend' ); ?>">

            <h3><?php esc_html_e( 'Found Any Bugs?', 'wp-user-frontend' ); ?></h3>

            <p><?php esc_html_e( 'Report any Bug that you Discovered, Get Instant Solutions.', 'wp-user-frontend' ); ?></p>

            <a target="_blank" class="button button-primary" href="https://github.com/weDevsOfficial/wp-user-frontend/?utm_source=wpuf-help-page&utm_medium=help-block&utm_campaign=found-bugs"><?php esc_html_e( 'Report to GitHub', 'wp-user-frontend' ); ?></a>
        </div>

        <div class="help-block">
            <img src="<?php echo esc_url( WPUF_ASSET_URI ); ?>/images/help/support.svg" alt="<?php esc_attr_e( 'Need Any Assistance?', 'wp-user-frontend' ); ?>">

            <h3><?php esc_html_e( 'Need Any Assistance?', 'wp-user-frontend' ); ?></h3>

            <p><?php esc_html_e( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-user-frontend' ); ?></p>

            <a target="_blank" class="button button-primary" href="https://wedevs.com/account/tickets/?utm_source=wpuf-help-page&utm_medium=help-block&utm_campaign=need-assistance"><?php esc_html_e( 'Contact Support', 'wp-user-frontend' ); ?></a>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(function($) {
        var tabs = $('.wpuf-help-tabbed > nav > ul > li' ),
            items = $('.wpuf-help-tabbed .nav-content > section');

        tabs.first().addClass('tab-current');
        items.first().addClass('content-current');

        tabs.on('click', 'a', function(event) {
            event.preventDefault();

            var self = $(this);

            tabs.removeClass('tab-current');
            self.parent('li').addClass('tab-current');

            $.each(items, function(index, val) {
                var element = $(val);

                if ( '#' + element.attr( 'id' ) === self.attr('href') ) {
                    element.addClass('content-current');
                } else {
                    element.removeClass('content-current');
                }
            });
        });

        const wemailForm = document.getElementById('wemail-embedded-subscriber-form');

        if (wemailForm) {
            wemailForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(wemailForm);
                const email = formData.get('email');

                if (!isValidEmail( email )) {
                    alert( 'Please enter a valid email address' );

                    return;
                }

                wemailForm.submit();
            });
        }

        function isValidEmail(email) {
            // Regular expression for validating an Email
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            return regex.test(email);
        }
    });
</script>
