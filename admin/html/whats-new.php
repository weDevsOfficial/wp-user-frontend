<?php
$changelog = array(
    array(
        'version'  => 'Version 3.1.0',
        'released' => '2019-01-31',
        'changes' => array(
            array(
                'title'       => __( 'Unable to send new user registration email', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'WP User Frontend default registration form `[wpuf-registration]` was unable to send the new user registration email.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'WPUF forms block compatibility issue with the latest WP version', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'With the latest version of WordPress the gutenberg block of WP User Frontend were not working. In this version, you will get it fixed.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Page not update where `[wpuf_dashboard]` shortcode exist', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'While using Gutenberg, the page were not being updated with WPUF shortcode [wpuf dashboard]', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Retain default when determining whether to display the admin bar', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( "From the User Frontend Settings, set that Administrator, Editor, Vendor can see the admin bar. Now, the super admin want, one specific user ( who has the user role from the above ) can't see the admin bar and disabled it from the Toolbar form that specific user profile. And this configuration ( Toolbar ) from the specific user profile were unable to impact on the frontend.", 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Fatal error when use PHP lower version (5.4 or lower)', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( "It was unable to install WP User Frontend with PHP 5.4 or lower version. Here is the error details: <br><br><strong>Fatal error: Can't use method return value in write context in /wp-user-frontend/class/frontend-form-post.php on line 194</strong>", 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Product form was unable to show the single gallery image', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( "When user upload single image for product gallery using WPUF WooCommerce product form, that image were not showing on the frontend.", 'wp-user-frontend' )
            ),
        )
    ),
    array(
        'version'  => 'Version 2.9.4',
        'released' => '2018-11-20',
        'changes' => array(
            array(
                'title'       => __( 'WooCommerce gallery images not getting saved', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'After releasing version 2.9.3, WooCommerce gallery image field stopped working. You will get it fixed in this version.', 'wp-user-frontend' )
            ),
        )
    ),
    array(
        'version'  => 'Version 2.9.0',
        'released' => '2018-08-14',
        'changes' => array(
            array(
                'title'       => __( 'The Events Calendar Integration Form', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now admin can allow users to create event from the frontend. Currently WPUF has a one click pre-build event form that has been integrated with The Events Calendar plugin', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Post Submission Facility From Account Page', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'On the frontend account page, added a new menu item named <b>Submit Post</b>. Now admin can allow users to submit post from their default account page. As an admin you can disable or enable this option from <b>User Frontend -> Settings -> My Account -> Post Submission</b>, Also, you can assign any post form that will use to submit posts.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Login/Lost Password Link Under Registration Form', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Added Login/Lost Password link under registration form', 'wp-user-frontend' )
            ),
        )
    ),
    array(
        'version'  => 'Version 2.8.10',
        'released' => '2018-07-17',
        'changes' => array(
            array(
                'title'       => __( 'Added drag and drop image ordering on image upload', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now frontend users can drag & drop the images/files to change the order while uploading.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added reCAPTCHA field in login form', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Admin has the option to show reCAPTCHA field in login form. Check the related settings from <strong>User Frontend > Settings > Login/Registration</strong>', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added preview option in forms', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'You can see a nice <strong>Preview</strong> button with <strong>Save Form</strong> button, admin can take a quick look of the form without using shortcode', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Fixed hiding “Select Image” button while uploading multiple images.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'The upload button will not be hidden until the user selects max number of files ', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added form limit notice before form submission', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Limit notice message was showing after submission, now it is showing when rendering the form', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Fixed: default post category not saving', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'From the form <strong>Settings > Post Settings</strong>, default post category options were not saving. Now, it\'s fixed.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'WPUF dashboard shortcode with form_id attribute was not showing posts properly', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Now you can list posts on the frontend by using <strong>form_id<strong/> attribute with <strong>[wpuf_dashboard]</strong> shortcode', 'wp-user-frontend' )
            ),
        )
    ),
    array(
        'version'  => 'Version 2.8.9',
        'released' => '2018-06-06',
        'changes' => array(
            array(
                'title'       => __( 'Changed text domain to `wp-user-frontend` from `wpuf`  ', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'If you are using other language than English. Please <b>rename</b> your <i>.po and .mo </i> files to `wp-user-frontend_` from `wpuf_` <br> This change was made to support translations from translate.wordpress.org', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added WP User Frontend Data export and erase functionality.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Added functionality to export WP User Frontend Data to comply with GDPR.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added billing address customizer.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Added customizer options for billing address in payment page.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Make the payment page responsive.', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Some css adjustments are made in payment page to make it responsive.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Fixed image upload issue in Safari.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Images were not showing after upload in safari, it is fixed now.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Post update issue after updating or removing post images.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Posts cannot be updated after updating or removing post images, it is fixed now.', 'wp-user-frontend' ),
            ),
        )
    ),
    array(
        'version'  => 'Version 2.8.8',
        'released' => '2018-05-16',
        'changes' => array(
            array(
                'title'       => __( 'Allow overriding form input styles using theme styling.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Overriding form input styles using theme style is now possible.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Fixed Auto Login after registration.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Auto Login after registration was not working is fixed now.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Fixed fallback cost calculation', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fallback cost calculation was inaccurate for some cases, it is fixed now.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Removal of subscription from User Profile gets reverted if updated', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'User subscription deletion gets reverted if updated is fixed.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Show Free pack users in subscribers list.', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Free pack users were not showing in subscribers list, now they will.', 'wp-user-frontend' ),
            )
        )
    ),
    array(
        'version'  => 'Version 2.8.7',
        'released' => '2018-04-09',
        'changes' => array(
            array(
                'title'       => __( 'WP User Frontend Guten Block is added', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'WPUF Form Block is now available to be used within gutenberg editor with preview of the form.  ', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Advanced Custom Fields plugin compatibility', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now all your ACF fields can be used within WPUF Post forms. ', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Taxonomy Terms not showing for custom post types', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fixed an issue with taxonomy terms not appearing for Custom Post types within Form Settings and Dashboard Post Listing', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Various other code optimizations', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Code structure organization and optimization for better performance', 'wp-user-frontend' ),
            )
        )
    ),
    array(
        'version'  => 'Version 2.8.6',
        'released' => '2018-03-22',
        'changes' => array(
            array(
                'title'       => __( 'WoooCommerce billing address Sync', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'If an existing customer has previously set his billing address, that will be imported into WPUF Billing address ', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Trial subscription message not showing properly', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Subscriptions with Trial now shows trial notices', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Reset email Key not working', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Reset Email key was not working in some cases', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Post count not showing on the frontend dashboard', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Dashboard with multiple post type was not showing post counts properly, is now fixed and shows count for each post type', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Login Redirect showing blank page is fixed', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'If "Previous Page" was set for redirection, login redirect was redirecting to blank page for users who hit login page directly', 'wp-user-frontend' ),
            ),
        )
    ),
    array(
        'version'  => 'Version 2.8.5',
        'released' => '2018-03-12',
        'changes' => array(
            array(
                'title'       => __( 'Enhanced Login Redirect to redirect users to previous page', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'You can choose Previous Page as Login Redirect page settings now to redirect users to the page from which they went for Login. ', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Email HTML links not Rendreing properly issue is fixed', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'For some clients emails were not rendering the HTML links properly, this is now fixed', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Form Builder : Form Field\'s Help text styles not showing properly', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Help texts styling is now fixed and much easier to read and understand', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Various other code improvements', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Code structure organization and optimization for better performance', 'wp-user-frontend' ),
            )
        )
    ),
    array(
        'version'  => 'Version 2.8.4',
        'released' => '2018-03-04',
        'changes' => array(
            array(
                'title'       => __( 'Dashboard Post Listing now supports multiple post types', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now you can show multiple post type in user dashboard using shortcode like this : <br><b>[wpuf_dashboard post_type="post,page,custom_type"]</b> ', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added Login Redirect Settings', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'You can now set a page from <i>WPUF Settings > Login/Registration > Redirect after Login</i>. When login redirection is active the user will be redirected to this page after login.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Image Upload field button text can be changed', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'The upload button text can now be changed for image upload fields which defaults to "Select Image" if not set. ', 'wp-user-frontend' ).'<img src="'. WPUF_ASSET_URI .'/images/whats-new/image_upload_label.png" alt="Multi-select Category">',
            ),
            array(
                'title'       => __( 'Multi Step Form styles made compatible with more themes', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Multi Step form can now be styled more easily with other themes ', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Required field condition for google map not working is fixed', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'If Google Map field was set as required users were able to submit form without changing the default value.', 'wp-user-frontend' ),
            )
        )
    ),
    array(
        'version'  => 'Version 2.8.3',
        'released' => '2018-02-15',
        'changes' => array(
            array(
                'title'       => __( 'Admin form builder is now fully responsive.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now you can edit forms from your mobile devices directly. Our improved responsive layouts of form builder makes it easy for you to build forms on the go.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Added color schemes for creating attractive form layouts.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'We have added 3 new color schemes for the form layouts which you can choose from each form\'s new display settings.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Restrict Free subscription pack to be enabled multiple times ', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Free subscription packs now can only be purchased once and the limit applies properly', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'Various other bug fixes and improvements were made ', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Please see the change log to see full details.', 'wp-user-frontend' ),
            ),
        )
    ),
    array(
        'version'  => 'Version 2.8.2',
        'released' => '2018-01-23',
        'changes' => array(
            array(
                'title'       => __( 'Added upgrade function for default category', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Upgrader added to upgrade previously set default post category.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Subscription pack cannot be canceled', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fixed recurring subscription pack cannot be canceled from my account page in subscription details section.', 'wp-user-frontend' ),
            ),
            array(
                'title'       => __( 'page installer admin notice logic issue', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fixed page installer admin notice logic problem due to new payment settings default value not set.', 'wp-user-frontend' ),
            ),
        )
    ),

    array(
        'version'  => 'Version 2.8.1',
        'released' => '2018-01-14',
        'changes' => array(
            array(
                'title'       => __( 'Setup Wizard', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Setup Wizard added to turn off payment options and install pages.', 'wp-user-frontend' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/wizard.gif" alt="Setup Wizard">'
            ),
            array(
                'title'       => __( 'Multi-select Category', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Add multi-select to default category in post form settings.', 'wp-user-frontend' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/category.png" alt="Multi-select Category">'
            ),
            array(
                'title'       => __( 'Select Text option for Taxonomy', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Add Select Text option for taxonomy fields. Now you can add default text with empty value as first option for Taxonomy dropdown.', 'wp-user-frontend' )
            ),
            array(
                'title'       => __( 'Taxonomy Checkbox Inline', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Added checkbox inline option to taxonomy checkbox. You can now display Taxonomy checkbox fields inline.', 'wp-user-frontend' )
            ),
        )
    ),

    array(
        'version'  => 'Version 2.8',
        'released' => '2018-01-06',
        'changes' => array(
            array(
                'title'       => __( 'Manage schedule for form submission', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Do not accept form submission if the current date is not between the date range of the schedule.', 'wp-user-frontend' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/schedule.png" alt="Manage schedule for form submission">'
            ),
            array(
                'title'       => __( 'Restrict form submission based on the user roles', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Restrict form submission based on the user roles. Now you can manage user role base permission on form submission.', 'wp-user-frontend' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/role-base.png" alt="Restrict form submission based on the users role">'
            ),
            array(
                'title'       => __( 'Limit how many entries a form will accept', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Limit how many entries a form will accept and display a custom message when that limit is reached.', 'wp-user-frontend' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/limit.png" alt="Limit how many entries a form will accept">'
            ),
            array(
                'title'       => __( 'Show/hide Admin Bar', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Control the admin bar visibility based on user roles.', 'wp-user-frontend' ) .
                '<img src="'. WPUF_ASSET_URI .'/images/whats-new/admin-bar.png" alt="Show/hide Admin Bar">'
            ),
            array(
                'title'       => __( 'Ajax Login widget', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Login user is more simple now with Ajax Login Widget. The simple ajax login form do not required page loading for login.', 'wp-user-frontend' ) .
                '<br><br><iframe width="100%" height="372" src="https://www.youtube.com/embed/eZYSuXsCw8E" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>'
            ),
            array(
                'title'       => __( 'Form submission with Captcha field', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Form field validation process updated if form submits with captcha field.', 'wp-user-frontend' )
            ),
        )
    )
);

function _wpuf_changelog_content( $content ) {
    $content = wpautop( $content, true );

    return $content;
}
?>

<div class="wrap wpuf-whats-new">
    <h1><?php _e( 'What\'s New in WPUF?', 'wp-user-frontend' ); ?></h1>

    <div class="wedevs-changelog-wrapper">

        <?php foreach ( $changelog as $release ) { ?>
            <div class="wedevs-changelog">
                <div class="wedevs-changelog-version">
                    <h3><?php echo esc_html( $release['version'] ); ?></h3>
                    <p class="released">
                        (<?php echo human_time_diff( time(), strtotime( $release['released'] ) ); ?> ago)
                    </p>
                </div>
                <div class="wedevs-changelog-history">
                    <ul>
                        <?php foreach ( $release['changes'] as $change ) { ?>
                            <li>
                                <h4>
                                    <span class="title"><?php echo esc_html( $change['title'] ); ?></span>
                                    <span class="label <?php echo strtolower( $change['type'] ); ?>"><?php echo esc_html( $change['type'] ); ?></span>
                                </h4>

                                <div class="description">
                                    <?php echo _wpuf_changelog_content( $change['description'] ); ?>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

</div>
