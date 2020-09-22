<?php
$changelog = [
    [
        'version'  => 'Version 3.5.2',
        'released' => '2020-09-22',
        'changes'  => [
            [
                'title' => __( 'Add character restriction feature', 'wp-user-frontend' ),
                'type'  => 'New',
            ],
            [
                'title' => __( 'Make sure post author edit link works only in frontend', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Inconsistency in lost password reset email message', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Saving custom taxonomy terms when input type is text', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Taxonomy field JS error in builder', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Showing WPUF edit link for WP default roles', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Upload button unresponsive issue in iOS', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
        ],
    ],    
    [
        'version'  => 'Version 3.4.0',
        'released' => '2020-08-24',
        'changes'  => [
            [
                'title' => __( 'Add post edit link for post authors in single or archive pages', 'wp-user-frontend' ),
                'type' => 'New',
            ],
            [
                'title' => __( 'Enhance post delete message', 'wp-user-frontend' ),
                'type' => 'Enhancement',
            ],
            [
                'title' => __( 'Refactor control buttons visibility in form builder', 'wp-user-frontend' ),
                'type' => 'Tweak',
            ],
            [
                'title' => __( 'Add missing colons after field label', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Post edit map capability condition', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Role based permission for accessing a post form', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Section-break field alignment', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Pay per post doesn\'t work if subscription pack is activated', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Mime type for uploading JSON files', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'File upload with same file name', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Post preview missing fields', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Illigal variable declartion', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Featured image updating issue', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Conflict with Phlox theme', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Textarea custom field data sanitization', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'exclude_type warning in wpuf_category_checklist', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Category field not showing all child categories for selection type child of', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Conflict between image and file upload custom fields', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
            [
                'title' => __( 'Login url when login page is not set', 'wp-user-frontend' ),
                'type' => 'Fix',
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.1',
        'released' => '2020-06-16',
        'changes'  => [
            [
                'title' => __( 'Use common names for Ivory Coast, North Korea and Sourth Korea instead of their official names', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Fix condition to use default avatar', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Make Email and URL fields clickable', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Fix redirect after user login', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Sanitize textarea field data', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Fix missing colon to email, URL, text and textarea labels when renders their data', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Prevent showing empty labels for fields that have render_field_data method', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
        ],
    ],
    [
        'version'  => 'Version 3.3.0',
        'released' => '2020-06-11',
        'changes'  => [
            [
                'title' => __( 'Add Namibian Dollar in currency list', 'wp-user-frontend' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Add sync values option for option data fields', 'wp-user-frontend' ),
                'type'  => 'Enhancement',
            ],
            [
                'title' => __( 'Allow uploading image that having filesize meets php ini settings', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Limit the selection of one image at a time', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Use file name and size to generate hash to prevent duplicant image upload', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Sanitize text and textarea field data', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Show label instead of values for radio, checkbox, dropdown and multiselect data', 'wp-user-frontend' ),
                'type'  => 'Tweak',
            ],
            [
                'title' => __( 'Saving custom taxonomies for type text input', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Admin settings link for recaptcha helper text', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Undefined name property for Custom HTML fields', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Delete attachment process', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Missing billing address in invoice PDF', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Showing country field value in frontend post content', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Avatar size display not complying with admin settings size', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Display default avatars on admin settings discussion page', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Redirect to subscription page at registration', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Error notice regarding registration page redirect', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Escaping html in registration errors', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Default login redirect link', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Implementing default WP login page override option', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Transparent background of autosuggestion dropdown', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
        ],
    ],
    [
        'version'  => 'Version 3.2.0',
        'released' => '2020-04-14',
        'changes'  => [
            [
                'title' => __( 'Import forms system', 'wp-user-frontend' ),
                'type'  => 'Improvement',
            ],
            [
                'title' => __( 'Password reset system', 'wp-user-frontend' ),
                'type'  => 'Improvement',
            ],
            [
                'title' => __( 'Updated url validation regex to support modern tlds', 'wp-user-frontend' ),
                'type'  => 'Improvement',
            ],
            [
                'title' => __( 'Export WPUF forms individually from admin tools page', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Subscription cycle label translation issue', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'ACF integration for checkbox fields', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Illegal string offset warning while updating settings', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Conditional logic for Section Break field', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Subscriptions cannot be deleted from backend', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'A regression regarding saving checkbox data', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
            [
                'title' => __( 'Default value of multi-select fields is not showing', 'wp-user-frontend' ),
                'type'  => 'Fix',
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.18',
        'released' => '2020-03-13',
        'changes'  => [
            [
                'title'       => __( 'Hide post edit option when subscription is expired', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Hide post edit option from users whose subscription pack is expired.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Check files to prevent duplicity in media upload', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'A simple measure has been taken to prevent maliciously flooding the site by uploading same file multiple times. Though this won\'t work with already uploaded medias.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Refactor address fields in Account section', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Address edit section from Account section has been rewritten to improve UX.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Update Paypal payment gateway', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Paypal payment gateway has seen some improvements.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Default Category selection improvements', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'An intuitive way of selecting default category of a selected post type has been introduced.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Compatibility issue with ACF date time field', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'A Compatibility issue with ACF date time field has been addressed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Media title, caption & description not saving', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Media title, caption & description were not saving from frontend. They will now.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'The Events Calendar venue and organizer fields issue in WPUF Custom Fields metabox', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'A workaround has been introduced to save The Events Calendar Venue and Organizer fields properly from WPUF Custom Fields metabox.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Checkbox data not saving from WPUF Custom Fields metabox', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Checkboxe data from WPUF Custom Fields metabox were not saving. It has been fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Multi-column Repeater field data saving issue', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Multi-column Repeater field data from a form was not saving. It has been fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Multistep form conflict with Elementor', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Multistep form had a conflict with Elementor. It has been fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Multiple images showing issue in frontend', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Multiple images in a post were not showing in frontend. Now they will.', 'wp-user-frontend' ),
            ]
        ],
    ],
    [
        'version'  => 'Version 3.1.12',
        'released' => '2019-10-17',
        'changes'  => [
            [
                'title'       => __( 'Nonce not verify on login', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Return of function wp_verify_nonce() was ignored.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.11',
        'released' => '2019-10-02',
        'changes'  => [
            [
                'title'       => __( 'Option to set which tab shows as active on the account page', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Option to set which tab shows as active on the account page. To configure this setting navigate to wp-admin->User Frontend->Settings->My Account->Active Tab ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Unlock option was unavailable after the post being locked', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Unlock option was unavailable after the post being locked.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( "Gutenberg block of WPUF didn't work on bedrock installation", 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( "Gutenberg block of WPUF didn't work on bedrock installation.", 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Sending admin payment received email twice', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'After processing payment admin & user was receiving payment received email twice.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Add shortcode support to display post information in the Post Expiration Message', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Add shortcode support to display post information in the Post Expiration Message. You can use: <strong>{post_author} {post_url} {blogname} {post_title} {post_status}</strong>', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Add optin on the setup wizard', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Added optin on the setup wizard, admin can choose whether he/she wants to share server environment details (php, mysql, server, WordPress versions), Number of users, Site language, Number of active and inactive plugins, Site name and url, admin name and email address. No sensitive data is tracked', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.10',
        'released' => '2019-09-06',
        'changes'  => [
            [
                'title'       => __( 'Post Owner problem', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Posts were not assigned to the selected default post owner, this issue has been fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Google reCaptcha was not working', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Google reCaptcha was not working, users could submit the form without reCaptcha validation.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.2',
        'released' => '2019-04-01',
        'changes'  => [
            [
                'title'       => __( 'Added column field', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => 'Now, creating multi-column in a single row is super easy with WPUF Column field. Just drag the column field in the builder area, configure columns number, column space and any fields you want inside that Column field.' . '<img src="' . WPUF_ASSET_URI . '/images/whats-new/column-field.png" alt="Multi-select Category">',
            ],
            [
                'title'       => __( 'Unable to render the events on the front-end dashboard', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'On the frontend dashboard, the submitted events were not showing, you will get it fixed in this version.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Page order getting 0(zero) after editing from the frontend', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Page order was not saving while editing a post using WPUF form, it has been fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Text input field for taxonomies not working', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'When taxonomy field type is set to `Text Input` then a fatal error was showing on the frontend, no error with taxonomy field in the latest version.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'In radio and checkbox field use conditional logic that value does not save in database', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'The selected value of radio and checkbox field were not showing while editing posts from the backend or frontend, you can see the selected value in this version.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'The args param not working with get_avatar filter', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'The args parameter did not exist with get_avatar filter, which now exists.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'The item in ajax taxonomy field was not selected', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'When the taxonomy field type is set to Ajax, the submitted terms were not showing in the backend and frontend which have been fixed.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 3.1.0',
        'released' => '2019-01-31',
        'changes'  => [
            [
                'title'       => __( 'Unable to send new user registration email', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'WP User Frontend default registration form `[wpuf-registration]` was unable to send the new user registration email.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'WPUF forms block compatibility issue with the latest WP version', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'With the latest version of WordPress the gutenberg block of WP User Frontend were not working. In this version, you will get it fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Page not update where `[wpuf_dashboard]` shortcode exist', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'While using Gutenberg, the page were not being updated with WPUF shortcode [wpuf dashboard]', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Retain default when determining whether to display the admin bar', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( "From the User Frontend Settings, set that Administrator, Editor, Vendor can see the admin bar. Now, the super admin want, one specific user ( who has the user role from the above ) can't see the admin bar and disabled it from the Toolbar form that specific user profile. And this configuration ( Toolbar ) from the specific user profile were unable to impact on the frontend.", 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Fatal error when use PHP lower version (5.4 or lower)', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( "It was unable to install WP User Frontend with PHP 5.4 or lower version. Here is the error details: <br><br><strong>Fatal error: Can't use method return value in write context in /wp-user-frontend/class/frontend-form-post.php on line 194</strong>", 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Product form was unable to show the single gallery image', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'When user upload single image for product gallery using WPUF WooCommerce product form, that image were not showing on the frontend.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.4',
        'released' => '2018-11-20',
        'changes'  => [
            [
                'title'       => __( 'WooCommerce gallery images not getting saved', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'After releasing version 2.9.3, WooCommerce gallery image field stopped working. You will get it fixed in this version.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.9.0',
        'released' => '2018-08-14',
        'changes'  => [
            [
                'title'       => __( 'The Events Calendar Integration Form', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now admin can allow users to create event from the frontend. Currently WPUF has a one click pre-build event form that has been integrated with The Events Calendar plugin', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Post Submission Facility From Account Page', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'On the frontend account page, added a new menu item named <b>Submit Post</b>. Now admin can allow users to submit post from their default account page. As an admin you can disable or enable this option from <b>User Frontend -> Settings -> My Account -> Post Submission</b>, Also, you can assign any post form that will use to submit posts.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Login/Lost Password Link Under Registration Form', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Added Login/Lost Password link under registration form', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.10',
        'released' => '2018-07-17',
        'changes'  => [
            [
                'title'       => __( 'Added drag and drop image ordering on image upload', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now frontend users can drag & drop the images/files to change the order while uploading.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added reCAPTCHA field in login form', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Admin has the option to show reCAPTCHA field in login form. Check the related settings from <strong>User Frontend > Settings > Login/Registration</strong>', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added preview option in forms', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'You can see a nice <strong>Preview</strong> button with <strong>Save Form</strong> button, admin can take a quick look of the form without using shortcode', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Fixed hiding “Select Image” button while uploading multiple images.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'The upload button will not be hidden until the user selects max number of files ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added form limit notice before form submission', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Limit notice message was showing after submission, now it is showing when rendering the form', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Fixed: default post category not saving', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'From the form <strong>Settings > Post Settings</strong>, default post category options were not saving. Now, it\'s fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'WPUF dashboard shortcode with form_id attribute was not showing posts properly', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Now you can list posts on the frontend by using <strong>form_id<strong/> attribute with <strong>[wpuf_dashboard]</strong> shortcode', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.9',
        'released' => '2018-06-06',
        'changes'  => [
            [
                'title'       => __( 'Changed text domain to `wp-user-frontend` from `wpuf`  ', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'If you are using other language than English. Please <b>rename</b> your <i>.po and .mo </i> files to `wp-user-frontend_` from `wpuf_` <br> This change was made to support translations from translate.wordpress.org', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added WP User Frontend Data export and erase functionality.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Added functionality to export WP User Frontend Data to comply with GDPR.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added billing address customizer.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Added customizer options for billing address in payment page.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Make the payment page responsive.', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Some css adjustments are made in payment page to make it responsive.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Fixed image upload issue in Safari.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Images were not showing after upload in safari, it is fixed now.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Post update issue after updating or removing post images.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Posts cannot be updated after updating or removing post images, it is fixed now.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.8',
        'released' => '2018-05-16',
        'changes'  => [
            [
                'title'       => __( 'Allow overriding form input styles using theme styling.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Overriding form input styles using theme style is now possible.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Fixed Auto Login after registration.', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Auto Login after registration was not working is fixed now.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Fixed fallback cost calculation', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fallback cost calculation was inaccurate for some cases, it is fixed now.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Removal of subscription from User Profile gets reverted if updated', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'User subscription deletion gets reverted if updated is fixed.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Show Free pack users in subscribers list.', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Free pack users were not showing in subscribers list, now they will.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.7',
        'released' => '2018-04-09',
        'changes'  => [
            [
                'title'       => __( 'WP User Frontend Guten Block is added', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'WPUF Form Block is now available to be used within gutenberg editor with preview of the form.  ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Advanced Custom Fields plugin compatibility', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now all your ACF fields can be used within WPUF Post forms. ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Taxonomy Terms not showing for custom post types', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fixed an issue with taxonomy terms not appearing for Custom Post types within Form Settings and Dashboard Post Listing', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Various other code optimizations', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Code structure organization and optimization for better performance', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.6',
        'released' => '2018-03-22',
        'changes'  => [
            [
                'title'       => __( 'WoooCommerce billing address Sync', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'If an existing customer has previously set his billing address, that will be imported into WPUF Billing address ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Trial subscription message not showing properly', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Subscriptions with Trial now shows trial notices', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Reset email Key not working', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Reset Email key was not working in some cases', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Post count not showing on the frontend dashboard', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Dashboard with multiple post type was not showing post counts properly, is now fixed and shows count for each post type', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Login Redirect showing blank page is fixed', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'If "Previous Page" was set for redirection, login redirect was redirecting to blank page for users who hit login page directly', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.5',
        'released' => '2018-03-12',
        'changes'  => [
            [
                'title'       => __( 'Enhanced Login Redirect to redirect users to previous page', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'You can choose Previous Page as Login Redirect page settings now to redirect users to the page from which they went for Login. ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Email HTML links not Rendreing properly issue is fixed', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'For some clients emails were not rendering the HTML links properly, this is now fixed', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Form Builder : Form Field\'s Help text styles not showing properly', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Help texts styling is now fixed and much easier to read and understand', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Various other code improvements', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Code structure organization and optimization for better performance', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.4',
        'released' => '2018-03-04',
        'changes'  => [
            [
                'title'       => __( 'Dashboard Post Listing now supports multiple post types', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now you can show multiple post type in user dashboard using shortcode like this : <br><b>[wpuf_dashboard post_type="post,page,custom_type"]</b> ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added Login Redirect Settings', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'You can now set a page from <i>WPUF Settings > Login/Registration > Redirect after Login</i>. When login redirection is active the user will be redirected to this page after login.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Image Upload field button text can be changed', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'The upload button text can now be changed for image upload fields which defaults to "Select Image" if not set. ', 'wp-user-frontend' ) . '<img src="' . WPUF_ASSET_URI . '/images/whats-new/image_upload_label.png" alt="Multi-select Category">',
            ],
            [
                'title'       => __( 'Multi Step Form styles made compatible with more themes', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Multi Step form can now be styled more easily with other themes ', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Required field condition for google map not working is fixed', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'If Google Map field was set as required users were able to submit form without changing the default value.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.3',
        'released' => '2018-02-15',
        'changes'  => [
            [
                'title'       => __( 'Admin form builder is now fully responsive.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Now you can edit forms from your mobile devices directly. Our improved responsive layouts of form builder makes it easy for you to build forms on the go.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Added color schemes for creating attractive form layouts.', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'We have added 3 new color schemes for the form layouts which you can choose from each form\'s new display settings.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Restrict Free subscription pack to be enabled multiple times ', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Free subscription packs now can only be purchased once and the limit applies properly', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Various other bug fixes and improvements were made ', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Please see the change log to see full details.', 'wp-user-frontend' ),
            ],
        ],
    ],
    [
        'version'  => 'Version 2.8.2',
        'released' => '2018-01-23',
        'changes'  => [
            [
                'title'       => __( 'Added upgrade function for default category', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Upgrader added to upgrade previously set default post category.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Subscription pack cannot be canceled', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fixed recurring subscription pack cannot be canceled from my account page in subscription details section.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'page installer admin notice logic issue', 'wp-user-frontend' ),
                'type'        => 'Fix',
                'description' => __( 'Fixed page installer admin notice logic problem due to new payment settings default value not set.', 'wp-user-frontend' ),
            ],
        ],
    ],

    [
        'version'  => 'Version 2.8.1',
        'released' => '2018-01-14',
        'changes'  => [
            [
                'title'       => __( 'Setup Wizard', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Setup Wizard added to turn off payment options and install pages.', 'wp-user-frontend' ) .
                '<img src="' . WPUF_ASSET_URI . '/images/whats-new/wizard.gif" alt="Setup Wizard">',
            ],
            [
                'title'       => __( 'Multi-select Category', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Add multi-select to default category in post form settings.', 'wp-user-frontend' ) .
                '<img src="' . WPUF_ASSET_URI . '/images/whats-new/category.png" alt="Multi-select Category">',
            ],
            [
                'title'       => __( 'Select Text option for Taxonomy', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Add Select Text option for taxonomy fields. Now you can add default text with empty value as first option for Taxonomy dropdown.', 'wp-user-frontend' ),
            ],
            [
                'title'       => __( 'Taxonomy Checkbox Inline', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Added checkbox inline option to taxonomy checkbox. You can now display Taxonomy checkbox fields inline.', 'wp-user-frontend' ),
            ],
        ],
    ],

    [
        'version'  => 'Version 2.8',
        'released' => '2018-01-06',
        'changes'  => [
            [
                'title'       => __( 'Manage schedule for form submission', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Do not accept form submission if the current date is not between the date range of the schedule.', 'wp-user-frontend' ) .
                '<img src="' . WPUF_ASSET_URI . '/images/whats-new/schedule.png" alt="Manage schedule for form submission">',
            ],
            [
                'title'       => __( 'Restrict form submission based on the user roles', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Restrict form submission based on the user roles. Now you can manage user role base permission on form submission.', 'wp-user-frontend' ) .
                '<img src="' . WPUF_ASSET_URI . '/images/whats-new/role-base.png" alt="Restrict form submission based on the users role">',
            ],
            [
                'title'       => __( 'Limit how many entries a form will accept', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Limit how many entries a form will accept and display a custom message when that limit is reached.', 'wp-user-frontend' ) .
                '<img src="' . WPUF_ASSET_URI . '/images/whats-new/limit.png" alt="Limit how many entries a form will accept">',
            ],
            [
                'title'       => __( 'Show/hide Admin Bar', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Control the admin bar visibility based on user roles.', 'wp-user-frontend' ) .
                '<img src="' . WPUF_ASSET_URI . '/images/whats-new/admin-bar.png" alt="Show/hide Admin Bar">',
            ],
            [
                'title'       => __( 'Ajax Login widget', 'wp-user-frontend' ),
                'type'        => 'New',
                'description' => __( 'Login user is more simple now with Ajax Login Widget. The simple ajax login form do not required page loading for login.', 'wp-user-frontend' ) .
                '<br><br><iframe width="100%" height="372" src="https://www.youtube.com/embed/eZYSuXsCw8E" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>',
            ],
            [
                'title'       => __( 'Form submission with Captcha field', 'wp-user-frontend' ),
                'type'        => 'Improvement',
                'description' => __( 'Form field validation process updated if form submits with captcha field.', 'wp-user-frontend' ),
            ],
        ],
    ],
];

function _wpuf_changelog_content( $content ) {
    $content = wpautop( $content, true );

    return $content;
}
?>

<div class="wrap wpuf-whats-new">
    <h1><?php esc_html_e( 'What\'s New in WPUF?', 'wp-user-frontend' ); ?></h1>

    <div class="wedevs-changelog-wrapper">

        <?php foreach ( $changelog as $release ) { ?>
            <div class="wedevs-changelog">
                <div class="wedevs-changelog-version">
                    <h3><?php echo esc_html( $release['version'] ); ?></h3>
                    <p class="released">
                        (<?php echo esc_html( human_time_diff( time(), strtotime( $release['released'] ) ) ); ?> ago)
                    </p>
                </div>
                <div class="wedevs-changelog-history">
                    <ul>
                        <?php foreach ( $release['changes'] as $change ) { ?>
                            <li>
                                <h4>
                                    <span class="title"><?php echo esc_html( $change['title'] ); ?></span>
                                    <span class="label <?php echo esc_html( strtolower( $change['type'] ) ); ?>"><?php echo esc_html( $change['type'] ); ?></span>
                                </h4>

                                <?php if ( ! empty( $change['description'] ) ): ?>
                                    <div class="description">
                                        <?php echo wp_kses_post( _wpuf_changelog_content( $change['description'] ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>

</div>
