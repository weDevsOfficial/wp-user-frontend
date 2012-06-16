<?php

/**
 * Get the value of a settings field
 *
 * @param string $option option field name
 * @return mixed
 */
function wpuf_get_option( $option ) {

    $fields = wpuf_settings_fields();
    $prepared_fields = array();

    //prepare the array with the field as key
    //and set the section name on each field
    foreach ($fields as $section => $field) {
        foreach ($field as $fld) {
            $prepared_fields[$fld['name']] = $fld;
            $prepared_fields[$fld['name']]['section'] = $section;
        }
    }

    //get the value of the section where the option exists
    $opt = get_option( $prepared_fields[$option]['section'] );
    $opt = is_array( $opt ) ? $opt : array();

    //return the value if found, otherwise default
    if ( array_key_exists( $option, $opt ) ) {
        return $opt[$option];
    } else {
        $val = isset( $prepared_fields[$option]['default'] ) ? $prepared_fields[$option]['default'] : '';
        return $val;
    }
}

/**
 * Settings Sections
 *
 * @since 1.0
 * @return array
 */
function wpuf_settings_sections() {
    $sections = array(
        array(
            'id' => 'wpuf_labels',
            'title' => __( 'Labels', 'wpuf' )
        ),
        array(
            'id' => 'wpuf_frontend_posting',
            'title' => __( 'Frontend Posting', 'wpuf' )
        ),
        array(
            'id' => 'wpuf_dashboard',
            'title' => __( 'Dashboard', 'wpuf' )
        ),
        array(
            'id' => 'wpuf_others',
            'title' => __( 'Others', 'wpuf' )
        ),
        array(
            'id' => 'wpuf_payment',
            'title' => __( 'Payments', 'wpuf' )
        ),
        array(
            'id' => 'wpuf_support',
            'title' => __( 'Support', 'wpuf' )
        ),
    );

    return apply_filters( 'wpuf_settings_sections', $sections );
}

function wpuf_settings_fields() {
    $settings_fields = array(
        'wpuf_labels' => apply_filters( 'wpuf_options_label', array(
            array(
                'name' => 'title_label',
                'label' => __( 'Post title label', 'wpuf' ),
                'default' => 'Title'
            ),
            array(
                'name' => 'title_help',
                'label' => __( 'Post title help text', 'wpuf' )
            ),
            array(
                'name' => 'cat_label',
                'label' => __( 'Post category label', 'wpuf' ),
                'default' => 'Category'
            ),
            array(
                'name' => 'cat_help',
                'label' => __( 'Post category help text', 'wpuf' ),
            ),
            array(
                'name' => 'desc_label',
                'label' => __( 'Post description label', 'wpuf' ),
                'default' => 'Description'
            ),
            array(
                'name' => 'desc_help',
                'label' => __( 'Post description help text', 'wpuf' ),
            ),
            array(
                'name' => 'tag_label',
                'label' => __( 'Post tag label', 'wpuf' ),
                'default' => 'Tags'
            ),
            array(
                'name' => 'tag_help',
                'label' => __( 'Post tag help text', 'wpuf' ),
            ),
            array(
                'name' => 'submit_label',
                'label' => __( 'Post submit button label', 'wpuf' ),
                'default' => 'Submit Post!'
            ),
            array(
                'name' => 'update_label',
                'label' => __( 'Post update button label', 'wpuf' ),
                'default' => 'Update Post!'
            ),
            array(
                'name' => 'updating_label',
                'label' => __( 'Post updating button label', 'wpuf' ),
                'desc' => __( 'the text will be used when the submit button is pressed', 'wpuf' ),
                'default' => 'Please wait...'
            ),
            array(
                'name' => 'ft_image_label',
                'label' => __( 'Featured image label', 'wpuf' ),
                'default' => 'Featured Image'
            ),
            array(
                'name' => 'ft_image_btn_label',
                'label' => __( 'Featured Button image label', 'wpuf' ),
                'default' => 'Upload Image'
            ),
            array(
                'name' => 'attachment_label',
                'label' => __( 'Attachment Label', 'wpuf' ),
                'default' => 'Attachments'
            ),
            array(
                'name' => 'attachment_btn_label',
                'label' => __( 'Attachment upload button', 'wpuf' ),
                'default' => 'Add another'
            ),
        ) ),
        'wpuf_frontend_posting' => apply_filters( 'wpuf_options_frontend', array(
            array(
                'name' => 'post_status',
                'label' => __( 'Post Status', 'wpuf' ),
                'desc' => __( 'Default post status after user submits a post', 'wpuf' ),
                'type' => 'select',
                'default' => 'publish',
                'options' => array(
                    'publish' => 'Publish',
                    'draft' => 'Draft',
                    'pending' => 'Pending'
                )
            ),
            array(
                'name' => 'post_author',
                'label' => __( 'Post Author', 'wpuf' ),
                'desc' => __( 'Set the new post\'s post author by default', 'wpuf' ),
                'type' => 'select',
                'default' => 'original',
                'options' => array(
                    'original' => __( 'Original Author', 'wpuf' ),
                    'to_other' => __( 'Map to other user', 'wpuf' )
                )
            ),
            array(
                'name' => 'map_author',
                'label' => __( 'Map posts to poster', 'wpuf' ),
                'desc' => __( 'If <b>Map to other user</b> selected, new post\'s post author will be this user by default', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_list_users()
            ),
            array(
                'name' => 'allow_cats',
                'label' => __( 'Allow to choose category?', 'wpuf' ),
                'desc' => __( 'Allow users to choose category while posting?', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'exclude_cats',
                'label' => __( 'Exclude category ID\'s', 'wpuf' ),
                'desc' => __( 'Exclude categories fro the dropdown', 'wpuf' ),
                'type' => 'text'
            ),
            array(
                'name' => 'default_cat',
                'label' => __( 'Default post category', 'wpuf' ),
                'desc' => __( 'If users are not allowed to choose any category, this category will be used instead', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_get_cats()
            ),
            array(
                'name' => 'cat_type',
                'label' => __( 'Category Selection type', 'wpuf' ),
                'type' => 'radio',
                'default' => 'normal',
                'options' => array(
                    'normal' => __( 'Normal', 'wpuf' ),
                    'ajax' => __( 'Ajaxified', 'wpuf' ),
                    'checkbox' => __( 'Checkbox', 'wpuf' )
                )
            ),
            array(
                'name' => 'enable_featured_image',
                'label' => __( 'Featured Image upload', 'wpuf' ),
                'desc' => __( 'Gives ability to upload an image as featured image', 'wpuf' ),
                'type' => 'radio',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Enable', 'wpuf' ),
                    'no' => __( 'Disable', 'wpuf' )
                )
            ),
            array(
                'name' => 'allow_attachment',
                'label' => __( 'Allow attachments', 'wpuf' ),
                'desc' => __( 'Will the users be able to add attachments on posts?', 'wpuf' ),
                'type' => 'radio',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Enable', 'wpuf' ),
                    'no' => __( 'Disable', 'wpuf' )
                )
            ),
            array(
                'name' => 'attachment_num',
                'label' => __( 'Number of attachments', 'wpuf' ),
                'desc' => __( 'How many attachments can be attached on a post. Put <b>0</b> for unlimited attachment', 'wpuf' ),
                'type' => 'text',
                'default' => '0'
            ),
            array(
                'name' => 'attachment_max_size',
                'label' => __( 'Attachment max size', 'wpuf' ),
                'desc' => __( 'Enter the maximum file size in <b>KILOBYTE</b> that is allowed to attach', 'wpuf' ),
                'type' => 'text',
                'default' => '2048'
            ),
            array(
                'name' => 'editor_type',
                'label' => __( 'Content editor type', 'wpuf' ),
                'type' => 'select',
                'default' => 'plain',
                'options' => array(
                    'rich' => __( 'Rich Text (tiny)', 'wpuf' ),
                    'full' => __( 'Rich Text (full)', 'wpuf' ),
                    'plain' => __( 'Plain Text', 'wpuf' )
                )
            ),
            array(
                'name' => 'allow_tags',
                'label' => __( 'Allow post tags', 'wpuf' ),
                'desc' => __( 'Users will be able to add post tags', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'enable_custom_field',
                'label' => __( 'Enable custom fields', 'wpuf' ),
                'desc' => __( 'You can use additional fields on your post submission form. Add new fields by going <b>Custom Fields</b> option page.', 'wpuf' ),
                'type' => 'checkbox'
            ),
            array(
                'name' => 'enable_post_date',
                'label' => __( 'Enable post date input', 'wpuf' ),
                'desc' => __( 'This will enable users to input the post published date', 'wpuf' ),
                'type' => 'checkbox'
            ),
            array(
                'name' => 'enable_post_expiry',
                'label' => __( 'Enable Post expiration', 'wpuf' ),
                'desc' => __( 'Users will be able to select a duration, after that time from post publish date, post will be set to draft. This feature depends on <strong>Post Expirator</strong> plugin. ', 'wpuf' ),
                'type' => 'checkbox'
            ),
        ) ),
        'wpuf_dashboard' => apply_filters( 'wpuf_options_dashboard', array(
            array(
                'name' => 'post_type',
                'label' => __( 'Show post type', 'wpuf' ),
                'desc' => __( 'Select the post type that the user will see', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_get_post_types()
            ),
            array(
                'name' => 'per_page',
                'label' => __( 'Posts per page', 'wpuf' ),
                'desc' => __( 'How many posts will be listed in a page', 'wpuf' ),
                'type' => 'text',
                'default' => '10'
            ),
            array(
                'name' => 'show_user_bio',
                'label' => __( 'Show user bio', 'wpuf' ),
                'desc' => __( 'Users biographical info will be shown', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'show_post_count',
                'label' => __( 'Show post count', 'wpuf' ),
                'desc' => __( 'Show how many posts are created by the user', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'show_ft_image',
                'label' => __( 'Show Featured Image', 'wpuf' ),
                'desc' => __( 'Show featured image of the post', 'wpuf' ),
                'type' => 'checkbox'
            ),
            array(
                'name' => 'ft_img_size',
                'label' => __( 'Featured Image size', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_get_image_sizes()
            ),
        ) ),
        'wpuf_others' => apply_filters( 'wpuf_options_others', array(
            array(
                'name' => 'post_notification',
                'label' => __( 'New post notification', 'wpuf' ),
                'desc' => __( 'A mail will be sent to admin when a new post is created', 'wpuf' ),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no' => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name' => 'enable_post_edit',
                'label' => __( 'Users can edit post?', 'wpuf' ),
                'desc' => __( 'Users will be able to edit their own posts', 'wpuf' ),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no' => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name' => 'enable_post_del',
                'label' => __( 'User can delete post?', 'wpuf' ),
                'desc' => __( 'Users will be able to delete their own posts', 'wpuf' ),
                'type' => 'select',
                'default' => 'yes',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no' => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name' => 'edit_page_id',
                'label' => __( 'Edit Page', 'wpuf' ),
                'desc' => __( 'Select the page where [wpuf_editpost] is located', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_get_pages()
            ),
            array(
                'name' => 'admin_access',
                'label' => __( 'Admin area access', 'wpuf' ),
                'desc' => __( 'Allow you to block specific user role to WordPress admin area.', 'wpuf' ),
                'type' => 'select',
                'default' => 'read',
                'options' => array(
                    'install_themes' => __( 'Admin Only', 'wpuf' ),
                    'edit_others_posts' => __( 'Admins, Editors', 'wpuf' ),
                    'publish_posts' => __( 'Admins, Editors, Authors', 'wpuf' ),
                    'edit_posts' => __( 'Admins, Editors, Authors, Contributors', 'wpuf' ),
                    'read' => __( 'Default', 'wpuf' )
                )
            ),
            array(
                'name' => 'cf_show_front',
                'label' => __( 'Show custom fields in the post', 'wpuf' ),
                'desc' => __( 'If you want to show the custom field data in the post added by the plugin.', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'att_show_front',
                'label' => __( 'Show attachments in the post', 'wpuf' ),
                'desc' => __( 'If you want to show the uploaded attachments in the post', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'override_editlink',
                'label' => __( 'Override the post edit link', 'wpuf' ),
                'desc' => __( 'Users see the edit link in post if s/he is capable to edit the post/page. Selecting <strong>Yes</strong> will override the default WordPress link', 'wpuf' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no' => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name' => 'custom_css',
                'label' => __( 'Custom CSS codes', 'wpuf' ),
                'desc' => __( 'If you want to add your custom CSS code, it will be added on page header wrapped with style tag', 'wpuf' ),
                'type' => 'textarea'
            ),
        ) ),
        'wpuf_payment' => apply_filters( 'wpuf_options_payment', array(
            array(
                'name' => 'charge_posting',
                'label' => __( 'Charge for posting', 'wpuf' ),
                'desc' => __( 'Charge user for submitting a post', 'wpuf' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'yes' => __( 'Yes', 'wpuf' ),
                    'no' => __( 'No', 'wpuf' )
                )
            ),
            array(
                'name' => 'force_pack',
                'label' => __( 'Force pack purchase', 'wpuf' ),
                'desc' => __( 'When active, users must have to buy a pack for posting', 'wpuf' ),
                'type' => 'select',
                'default' => 'no',
                'options' => array(
                    'no' => __( 'Disable', 'wpuf' ),
                    'yes' => __( 'Enable', 'wpuf' )
                )
            ),
            array(
                'name' => 'currency',
                'label' => __( 'Currency', 'wpuf' ),
                'type' => 'select',
                'default' => 'USD',
                'options' => array(
                    'AUD' => 'Australian Dollar',
                    'CAD' => 'Canadian Dollar',
                    'EUR' => 'Euro',
                    'GBP' => 'British Pound',
                    'JPY' => 'Japanese Yen',
                    'USD' => 'U.S. Dollar',
                    'NZD' => 'New Zealand Dollar',
                    'CHF' => 'Swiss Franc',
                    'HKD' => 'Hong Kong Dollar',
                    'SGD' => 'Singapore Dollar',
                    'SEK' => 'Swedish Krona',
                    'DKK' => 'Danish Krone',
                    'PLN' => 'Polish Zloty',
                    'NOK' => 'Norwegian Krone',
                    'HUF' => 'Hungarian Forint',
                    'CZK' => 'Czech Koruna',
                    'ILS' => 'Israeli New Shekel',
                    'MXN' => 'Mexican Peso',
                    'BRL' => 'Brazilian Real',
                    'MYR' => 'Malaysian Ringgit',
                    'PHP' => 'Philippine Peso',
                    'TWD' => 'New Taiwan Dollar',
                    'THB' => 'Thai Baht',
                    'TRY' => 'Turkish Lira'
                )
            ),
            array(
                'name' => 'currency_symbol',
                'label' => __( 'Currency Symbol', 'wpuf' ),
                'type' => 'text',
                'default' => '$'
            ),
            array(
                'name' => 'cost_per_post',
                'label' => __( 'Cost', 'wpuf' ),
                'desc' => __( 'Cost per post', 'wpuf' ),
                'type' => 'text',
                'default' => '2'
            ),
            array(
                'name' => 'sandbox_mode',
                'label' => __( 'Enable demo/sandbox mode', 'wpuf' ),
                'desc' => __( 'When sandbox mode is active, all payment gateway will be used in demo mode', 'wpuf' ),
                'type' => 'checkbox',
                'default' => 'on'
            ),
            array(
                'name' => 'payment_page',
                'label' => __( 'Payment Page', 'wpuf' ),
                'desc' => __( 'This page will be used to process payment options', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_get_pages()
            ),
            array(
                'name' => 'payment_success',
                'label' => __( 'Payment Success Page', 'wpuf' ),
                'desc' => __( 'After payment users will be redirected here', 'wpuf' ),
                'type' => 'select',
                'options' => wpuf_get_pages()
            ),
            array(
                'name' => 'active_gateways',
                'label' => __( 'Payment Gateways', 'wpuf' ),
                'desc' => __( 'Active payment gateways', 'wpuf' ),
                'type' => 'multicheck',
                'options' => wpuf_get_gateways()
            ),
        ) ),
        'wpuf_support' => apply_filters( 'wpuf_options_support', array(
            array(
                'name' => 'support',
                'label' => __( 'Need Help?', 'wpuf' ),
                'type' => 'html',
                'desc' => '
                        <ol>
                            <li>
                                <strong>Check the FAQ and the documentation</strong>
                                <p>First of all, check the <strong><a href="http://wordpress.org/extend/plugins/wp-user-frontend/faq/">FAQ</a></strong> before contacting! Most of the questions you might need answers to have already been asked and the answers are in the FAQ. Checking the FAQ is the easiest and quickest way to solve your problem.</p>
                            </li>
                            <li>
                                <strong>Use the Support Forum</strong>
                                <p>If you were unable to find the answer to your question on the FAQ page, you should check the <strong><a href="http://wordpress.org/tags/wp-user-frontend?forum_id=10">support forum on WordPress.org</a></strong>. If you can’t locate any topics that pertain to your particular issue, post a new topic for it.</p>
                                <p>But, remember that this is a free support forum and no one is obligated to help you. Every person who offers information to help you is a volunteer, so be polite. And, I would suggest that you read the <a href="http://wordpress.org/support/topic/68664">“Forum Rules”</a> before posting anything on this page.</p>
                            </li>
                            <li>
                                <strong>Got an idea?</strong>
                                <p>I would love to hear about your ideas and suggestions about the plugin. Please post them on the <strong><a href="http://wordpress.org/tags/wp-user-frontend?forum_id=10">support forum on WordPress.org</a></strong> and I will look into it</p>
                            </li>
                            <li>
                                <strong>Gettings no response?</strong>
                                <p>I try to answer all the question in the forum. I created the plugin without any charge and I am usually very busy with my other works. As this is a free plugin, I am not bound answer all of your questions.</p>
                            </li>
                            <li>
                                I spent countless hours to build this plugin, <strong><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=tareq%40wedevs%2ecom&lc=US&item_name=WP%20User%20Frontend&item_number=Tareq%27s%20Planet&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted">support</a></strong> me if you like this plugin and <a href="http://wordpress.org/extend/plugins/wp-user-frontend/">rate</a> the plugin.
                            </li>
                        </ol>'
            )
        ) ),
    );

    return apply_filters( 'wpuf_settings_fields', $settings_fields );
}