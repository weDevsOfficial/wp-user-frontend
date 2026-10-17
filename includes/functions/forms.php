<?php

use WeDevs\Wpuf\Platform\Stores\Stores;
/**
 * Forms: fields, settings, templates, builder helpers and form-level checks
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

/**
 * Get exclude settings for a field type
 *
 * @since 3.4.0
 *
 * @param array $field_settings
 * @param string $exclude_type
 *
 * @return array
 */
function wpuf_get_field_settings_excludes( $field_settings, $exclude_type ) {
    $attributes   = $field_settings['exclude'];
    $child_ids    = [];

    if ( ! empty( $attributes ) ) {
        foreach ( $attributes as $attr ) {
            $terms = get_terms(
                [
                    'taxonomy'   => $field_settings['name'],
                    'hide_empty' => false,
                    'parent'     => $attr,
                ]
            );

            foreach ( $terms as $term ) {
                array_push( $child_ids, $term->term_id );
            }
        }
    }

    if ( $exclude_type === 'child_of' ) {
        $exclude_type = 'include';
    }

    $excludes = [
        'type'   => $exclude_type,
        'childs' => $child_ids,
    ];

    return $excludes;
}

/**
 * Get form fields from a form
 *
 * @param int $form_id
 *
 * @return array
 */
function wpuf_get_form_fields( $form_id ) {
    $fields = get_children(
        [
            'post_parent' => $form_id,
            'post_status' => 'publish',
            'post_type'   => 'wpuf_input',
            'numberposts' => '-1',
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
        ]
    );

    $form_fields = [];

    foreach ( $fields as $key => $content ) {
        $field = (array) maybe_unserialize( $content->post_content );

        $field['id'] = $content->ID;
        $field['input_type'] = isset( $field['input_type'] ) ? $field['input_type'] : '';

        // Add inline property for radio and checkbox fields
        $inline_supported_fields = [ 'radio', 'checkbox' ];

        if ( in_array( $field['input_type'], $inline_supported_fields, true ) ) {
            if ( ! isset( $field['inline'] ) ) {
                $field['inline'] = 'no';
            }
        }

        // Add 'selected' property
        $option_based_fields = [ 'select', 'multiselect', 'radio', 'checkbox' ];

        if ( in_array( $field['input_type'], $option_based_fields, true ) ) {
            if ( ! isset( $field['selected'] ) ) {
                if ( 'select' === $field['input_type'] || 'radio' === $field['input_type'] ) {
                    $field['selected'] = '';
                } else {
                    $field['selected'] = [];
                }
            }
        }

        // Add 'multiple' key for input_type:repeat
        if ( 'repeat' === $field['input_type'] ) {
            if ( ! isset( $field['multiple'] ) ) {
                $field['multiple'] = '';
            }

            // Ensure inner_fields is a simple array (not column structure)
            if ( empty( $field['inner_fields'] ) ) {
                $field['inner_fields'] = [];
            } elseif ( isset( $field['inner_fields']['column-1'] ) ) {
                // Convert column structure to simple array
                $field['inner_fields'] = $field['inner_fields']['column-1'];
            }
        }

        if ( 'recaptcha' === $field['input_type'] ) {
            $field['name']              = 'recaptcha';
            $field['enable_no_captcha'] = isset( $field['enable_no_captcha'] ) ? $field['enable_no_captcha'] : '';
        }

        $form_fields[] = apply_filters( 'wpuf-get-form-fields', $field );
    }

    /**
     * Filter form fields data array before returning
     *
     * Allows filtering the complete form fields array. Used to filter out
     * pro-only fields when pro plugin is not active.
     *
     * @since 4.2.9
     *
     * @param array $form_fields The array of form fields data
     * @param int   $form_id     The form ID
     */
    return apply_filters( 'wpuf_form_fields_data', $form_fields, $form_id );
}

function wpuf_form_field_id_generator( $min = 999999, $max = 9999000001 ) {
    return rand( $min, $max );
}

/**
 * Returns form setting value
 *
 * @param int   $form_id
 * @param boolen $status
 *
 * @return array
 */
function wpuf_get_form_settings( $form_id, $status = true ) {
    return get_post_meta( $form_id, 'wpuf_form_settings', $status );
}

/**
 * Get form notifications
 *
 * @since 2.5.2
 *
 * @param int $form_id
 *
 * @return array
 */
function wpuf_get_form_notifications( $form_id ) {
    $notifications = Stores::forms()->read_meta( $form_id, 'notifications' );

    if ( ! $notifications ) {
        return [];
    }

    return $notifications;
}

/**
 * Get form integration settings
 *
 * @since 2.5.4
 *
 * @param int $form_id
 *
 * @return array
 */
function wpuf_get_form_integrations( $form_id ) {
    $integrations = Stores::forms()->read_meta( $form_id, 'integrations' );

    if ( ! $integrations ) {
        return [];
    }

    return $integrations;
}

/**
 * Check if an integration is active
 *
 * @since 2.5.4
 *
 * @param int    $form_id
 * @param string $integration_id
 *
 * @return bool
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_is_integration_active( $form_id, $integration_id ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $integrations = wpuf_get_form_integrations( $form_id );

    if ( ! $integrations ) {
        return false;
    }

    foreach ( $integrations as $id => $integration ) {
        if ( $integration_id === $id && $integration->enabled === true ) {
            return $integration;
        }
    }

    return false;
}

/**
 * Get post form templates
 *
 * @since 2.4
 *
 * @return array
 */
function wpuf_get_post_form_templates() {
    $integrations['post_form_template_post']                 = new WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Template_Post();
    $integrations['post_form_template_video']                = new WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Template_Video();
    $integrations['post_form_template_paid_guest_post']      = new WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Template_Paid_Guest_Post();
    $integrations['post_form_template_guest_post_recurring'] = new WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Template_Guest_Post_Recurring();

    return apply_filters( 'wpuf_get_post_form_templates', $integrations );
}

/**
 * Get the pro form templates list
 *
 * @since 3.6.0
 *
 * @return mixed|null
 */
function wpuf_get_pro_form_previews() {
    $template_names = [];

    /**
     * Filter pro post form templates to preview
     *
     * @since 3.6.0
     *
     * @param array $template_names
     */
    return apply_filters( 'wpuf_get_pro_form_previews', $template_names );
}

/**
 * API to duplicate a form
 *
 * @since 2.5
 *
 * @param int $post_id
 *
 * @return int New duplicated form id
 */
function wpuf_duplicate_form( $post_id ) {
    // Forwards to the form store (task 2.4a); same draft copy, title and meta.
    return \WeDevs\Wpuf\Platform\Stores\Stores::forms()->duplicate( $post_id );
}

/**
 * Save form fields
 *
 * @since 2.5
 *
 * @param int   $form_id
 * @param array $field
 * @param int   $field_id
 * @param int   $order
 *
 * @return int ID of updated or inserted post
 */
function wpuf_insert_form_field( $form_id, $field = [], $field_id = null, $order = 0 ) {
    // Forwards to the field store (task 2.4a); the field is unslashed as before.
    return \WeDevs\Wpuf\Platform\Stores\Stores::fields()->write( $form_id, $field, $field_id ? $field_id : 0, $order );
}

/**
 * Create a sample / base form
 *
 * @since  2.5
 *
 * @param string $post_title (optional)
 * @param string $post_type  (optional)
 * @param bool   $blank      (optional)
 *
 * @return int
 */
function wpuf_create_sample_form( $post_title = 'Sample Form', $post_type = 'wpuf_forms', $blank = false ) {
    $form_fields = [];
    $settings    = [];

    // Post form
    if ( 'wpuf_forms' === $post_type ) {
        $form_fields = [
            [
                'input_type'  => 'text',
                'template'    => 'post_title',
                'required'    => 'yes',
                'label'       => 'Post Title',
                'name'        => 'post_title',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => [],
            ],
            [
                'input_type'   => 'textarea',
                'template'     => 'post_content',
                'required'     => 'yes',
                'label'        => 'Post Content',
                'name'         => 'post_content',
                'is_meta'      => 'no',
                'help'         => '',
                'css'          => '',
                'rows'         => '5',
                'cols'         => '25',
                'placeholder'  => '',
                'default'      => '',
                'rich'         => 'teeny',
                'insert_image' => 'yes',
                'wpuf_cond'    => [],
            ],
        ];

        $settings = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'post_format'         => '0',
            'default_cat'         => '-1',
            'guest_post'          => 'false',
            'guest_details'       => 'true',
            'name_label'          => 'Name',
            'email_label'         => 'Email',
            'message_restrict'    => 'This page is restricted. Please {login} / {register} to view this page.',
            'redirect_to'         => 'post',
            'message'             => 'Post saved',
            'page_id'             => '',
            'url'                 => '',
            'comment_status'      => 'open',
            'submit_text'         => 'Submit',
            'submit_button_cond'  => [
                'condition_status' => 'no',
                'cond_logic'       => 'any',
                'conditions'       => [
                    [
                        'name'             => '',
                        'operator'         => '=',
                        'option'           => '',
                    ],
                ],
            ],
            'draft_post'       => 'false',
            'edit_post_status' => 'publish',
            'edit_redirect_to' => 'same',
            'update_message'   => 'Post updated successfully',
            'edit_page_id'     => '',
            'edit_url'         => '',
            'subscription'     => '- Select -',
            'update_text'      => 'Update',
            'notification'     => [
                'new'          => 'on',
                'new_to'       => get_option( 'admin_email' ),
                'new_subject'  => 'New post created',
                'new_body'     => "Hi Admin, \r\n\r\nA new post has been created in your site {sitename} ({siteurl}). \r\n\r\nHere is the details: \r\nPost Title: {post_title} \r\nContent: {post_content} \r\nAuthor: {author} \r\nPost URL: {permalink} \r\nEdit URL: {editlink}",
                'edit'         => 'off',
                'edit_to'      => get_option( 'admin_email' ),
                'edit_subject' => 'A post has been edited',
                'edit_body'    => "Hi Admin, \r\n\r\nThe post \"{post_title}\" has been updated. \r\n\r\nHere is the details: \r\nPost Title: {post_title} \r\nContent: {post_content} \r\nAuthor: {author} \r\nPost URL: {permalink} \r\nEdit URL: {editlink}",
            ],
        ];
    }

    // Profile form
    if ( 'wpuf_profile' === $post_type ) {
        $form_fields = [
            [
                'input_type'  => 'email',
                'template'    => 'user_email',
                'required'    => 'yes',
                'label'       => 'Email',
                'name'        => 'user_email',
                'is_meta'     => 'no',
                'help'        => '',
                'css'         => '',
                'placeholder' => '',
                'default'     => '',
                'size'        => '40',
                'wpuf_cond'   => null,
            ],
            [
                'input_type'    => 'password',
                'template'      => 'password',
                'required'      => 'yes',
                'label'         => 'Password',
                'name'          => 'password',
                'is_meta'       => 'no',
                'help'          => '',
                'css'           => '',
                'placeholder'   => '',
                'default'       => '',
                'size'          => '40',
                'min_length'    => '5',
                'repeat_pass'   => 'yes',
                're_pass_label' => 'Confirm Password',
                'pass_strength' => 'yes',
                'wpuf_cond'     => null,
            ],
        ];

        $settings = [
            'role'             => 'subscriber',
            'reg_redirect_to'  => 'same',
            'message'          => 'Registration successful',
            'update_message'   => 'Profile updated successfully',
            'reg_page_id'      => '0',
            'registration_url' => '',
            'profile_url'      => '',
            'submit_text'      => 'Register',
            'update_text'      => 'Update Profile',
        ];
    }

    // The form store inserts the post, then the fields, settings (when not
    // empty) and version, as this function did (task 2.4a).
    $form_id = \WeDevs\Wpuf\Platform\Stores\Stores::forms()->create(
        [
            'post_title'     => $post_title,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'post_content'   => '',
            'fields'         => $blank ? [] : $form_fields,
            'settings'       => $settings,
        ]
    );

    return is_wp_error( $form_id ) ? false : $form_id;
}

/**
 * Delete a form with it's field and meta
 *
 * @since 2.5.2
 *
 * @param int  $form_id
 * @param bool $force
 *
 * @return void
 */
function wpuf_delete_form( $form_id, $force = true ) {
    // Forwards to the form store (task 2.4a), legacy semantics kept.
    \WeDevs\Wpuf\Platform\Stores\Stores::forms()->delete( $form_id, $force );
}

/**
 * Check if it's post form builder
 *
 * @since 2.6
 *
 * @return bool
 */
function is_wpuf_post_form_builder() {
    $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

    return 'wpuf-post-forms' === $page ? true : false;
}

/**
 * Check if it's profile form builder
 *
 * @since 2.6
 *
 * @return bool
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function is_wpuf_profile_form_builder() {
    $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

    return 'wpuf-profile-forms' === $page ? true : false;
}

/**
 * Displays Form Schedule Messages
 *
 * @since 2.8.10
 *
 * @param int $form_id
 */
function wpuf_show_form_schedule_message( $form_id ) {
    $form_settings = wpuf_get_form_settings( $form_id );
    $is_scheduled  = isset( $form_settings['schedule_form'] ) && wpuf_is_checkbox_or_toggle_on( $form_settings['schedule_form'] );

    if ( $is_scheduled ) {
        $start_time   = ! empty( $form_settings['schedule_start'] ) ? strtotime( $form_settings['schedule_start'] ) : 0;
        $end_time     = ! empty( $form_settings['schedule_end'] ) ? strtotime( $form_settings['schedule_end'] ) : 0;
        $current_time = current_time( 'timestamp' );

        if ( $current_time >= $start_time && $current_time <= $end_time ) {
            return;
        }

        // too early?
        if ( $current_time < $start_time ) {
            echo wp_kses_post( '<div class="wpuf-message">' . $form_settings['form_pending_message'] . '</div>' );
        } elseif ( $current_time > $end_time ) {
            echo wp_kses_post( '<div class="wpuf-message">' . $form_settings['form_expired_message'] . '</div>' );
        }
        ?>
        <script>
            jQuery( function($) {
                $(".wpuf-submit-button").attr("disabled", "disabled");
            });
        </script>
        <?php
        return;
    }
}

/**
 * Displays Form Limit Messages
 *
 * @since 2.8.10
 *
 * @param int $form_id
 */
function wpuf_show_form_limit_message( $form_id ) {
    $form_settings  = wpuf_get_form_settings( $form_id );
    $has_limit      = isset( $form_settings['limit_entries'] ) && wpuf_is_checkbox_or_toggle_on( $form_settings['limit_entries'] );
    $post_to_check  = get_post( get_the_ID() );
    $is_edit_page   = false;

    if ( $post_to_check && stripos( $post_to_check->post_content, '[wpuf_edit' ) !== false ) {
        $is_edit_page = true;
    }

    if ( $has_limit && ! $is_edit_page ) {
        $limit        = (int) ! empty( $form_settings['limit_number'] ) ? $form_settings['limit_number'] : 0;
        $form_entries = wpuf_form_posts_count( $form_id );

        if ( $limit && $limit <= $form_entries ) {
            $info = $form_settings['limit_message'];
            echo wp_kses_post( '<div class="wpuf-info">' . $info . '</div>' );
            ?>
            <script>
                jQuery( function($) {
                    $(".wpuf-submit-button").attr("disabled", "disabled");
                });
            </script>
            <?php
        }

        return;
    }
}

/**
 * Create a private page for form preview
 *
 * @return bool|false|string|WP_Error
 */
function get_wpuf_preview_page() {
    $page_url        = '';
    $post_status     = '';
    $preview_page_id = get_option( 'wpuf_preview_page', false );

    if ( $preview_page_id ) {
        $page_url = get_permalink( $preview_page_id );
    }

    if ( $page_url ) {
        $post_status = get_post_status( $preview_page_id );
    }

    if ( $page_url && $post_status === 'private' ) {
        return $page_url;
    }

    if ( $post_status && $post_status !== 'private' ) {
        wp_update_post(
            [
                'ID' => $preview_page_id,
                'post_status' => 'private',
            ]
        );
        $page_url = get_permalink( $preview_page_id );
    }

    if ( $page_url ) {
        return $page_url;
    }

    $post_id = wp_insert_post(
        [
            'post_title'  => 'wpuf-preview',
            'post_type'   => 'page',
            'post_status' => 'private',
        ]
    );
    update_option( 'wpuf_preview_page', $post_id );

    return get_permalink( get_option( 'wpuf_preview_page' ) );
}

function wpuf_get_editor_buttons( $type = 'rich' ) {
    $common = [
        'bold'        => 'bold',
        'italic'      => 'italic',
        'bullist'     => 'bullist',
        'numlist'     => 'numlist',
        'blockquote'  => 'blockquote',
        'alignleft'   => 'alignleft',
        'aligncenter' => 'aligncenter',
        'alignright'  => 'alignright',
        'link'        => 'link',
    ];

    $rich = [
        'formatselect' => 'formatselect',
        'wp_more'      => 'wp_more',
        'spellchecker' => 'spellchecker',
    ];

    $teeny = [
        'underline'     => 'underline',
        'strikethrough' => 'strikethrough',
        'undo'          => 'undo',
        'redo'          => 'redo',
        'fullscreen'    => 'fullscreen',
    ];

    return 'rich' === $type || 'yes' === $type ? array_merge( $rich, $common ) : array_merge( $common, $teeny );
}

/**
 * A form style thumbnail for the React forms (modern design): the `modern/`
 * copy of the picture when one sits next to the classic one, the classic
 * picture otherwise. Pro
 * passes its own registration template pictures through it too.
 *
 * @since WPUF_SINCE
 *
 * @param string $url  Classic picture URL
 * @param string $path Classic picture path on disk
 *
 * @return string
 */
function wpuf_form_layout_image( $url, $path ) {
    $modern = dirname( $path ) . '/modern/' . basename( $path );

    if ( ! file_exists( $modern ) ) {
        return $url;
    }

    return dirname( $url ) . '/modern/' . basename( $url );
}

/**
 * Filter editor buttons
 *
 * @param $field_settings
 *
 * @return array
 */
function wpuf_filter_editor_toolbar( $field_settings ) {
    $tinymce_settings = [];

    if ( ! empty( $field_settings['text_editor_control'] ) ) {
        $exclude_button = $field_settings['text_editor_control'];

        $tinymce_settings['toolbar1'] = implode(
            ',',
            array_filter(
                wpuf_get_editor_buttons( $field_settings['rich'] ),
                function ( $key ) use ( $exclude_button ) {
                    return ! in_array( $key, $exclude_button, true );
                },
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    return ! empty( $tinymce_settings['toolbar1'] ) ? $tinymce_settings : [];
}

/**
 * Remove conditional from form builder for selected fields
 *
 * @param $settings
 *
 * @return array
 */
function wpuf_unset_conditional( $settings ) {
    $remove_cond_field = [ 'action_hook', 'step_start' ];

    $field_settings = array_map(
        function ( $field ) use ( $remove_cond_field ) {
            if ( in_array( $field['template'], $remove_cond_field, true ) ) {
                $index = array_filter(
                    $field['settings'],
                    function ( $settings ) {
                        return $settings['name'] === 'wpuf_cond';
                    }
                );

                if ( ! empty( $index ) ) {
                    unset( $field['settings'][ array_keys( $index )[0] ] );
                }
            }

            return $field;
        }, $settings['field_settings']
    );

    $settings['field_settings'] = $field_settings;

    return $settings;
}

/**
 * Get post forms created by WPUF
 *
 * @since 2.9.0
 * @since 4.0.0 moved to wpuf-functions.php from WPUF_Frontend_Account.php
 *
 * @return array $forms
 */
function wpuf_get_post_forms() {
    $args = [
        'post_type'   => 'wpuf_forms',
        'post_status' => 'any',
        'orderby'     => 'DESC',
        'order'       => 'ID',
        'numberposts' => - 1,
    ];
    $posts = get_posts( $args );
    $forms = [];
    if ( ! empty( $posts ) ) {
        foreach ( $posts as $post ) {
            $forms[ $post->ID ] = $post->post_title;
        }
    }

    return $forms;
}

/**
 * Clear Schedule lock
 *
 * @since 3.0.2
 */
function wpuf_clear_schedule_lock() {
    check_ajax_referer( 'wpuf_nonce', 'nonce' );

    // Clearing an edit-lock (potentially set by an admin) requires the ability
    // to edit others' posts. This prevents any logged-in subscriber from wiping
    // lock meta on arbitrary posts.
    if ( ! current_user_can( 'edit_others_posts' ) ) {
        wp_send_json_error( esc_html__( 'You are not allowed to clear this lock.', 'wp-user-frontend' ), 403 );
    }

    $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;

    if ( ! empty( $post_id ) ) {
        Stores::submissions()->set_lock_time( $post_id, '' );
        Stores::submissions()->set_lock( $post_id, 'no' );
    }
    exit;
}

/**
 * Hide the Google map button
 *
 * @since 4.0.0 function moved from Posting class
 *
 * @return void
 */
function wpuf_hide_google_map_button() {
    echo wp_kses(
        "<style>
                button.button[data-name='custom_map'] {
                    display: none;
                }
              </style>",
        [
            'style'  => [],
            'button' => [],
        ]
    );
}

/**
 * Get the post form builder setting menu titles. The titles will show on Post forms > Settings > left side menu
 *
 * @since 4.1.0
 *
 * @return mixed|null
 */
function wpuf_get_post_form_builder_setting_menu_titles() {
    $post_settings_fields = apply_filters(
        'wpuf_post_form_builder_setting_menu_titles',
        [
            'general'               => [
                'label' => __( 'General', 'wp-user-frontend' ),
                'icon'  => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
                                <path d="M8.75 5L16.875 5M8.75 5C8.75 5.69036 8.19036 6.25 7.5 6.25C6.80964 6.25 6.25 5.69036 6.25 5M8.75 5C8.75 4.30964 8.19036 3.75 7.5 3.75C6.80964 3.75 6.25 4.30964 6.25 5M3.125 5H6.25M8.75 15H16.875M8.75 15C8.75 15.6904 8.19036 16.25 7.5 16.25C6.80964 16.25 6.25 15.6904 6.25 15M8.75 15C8.75 14.3096 8.19036 13.75 7.5 13.75C6.80964 13.75 6.25 14.3096 6.25 15M3.125 15L6.25 15M13.75 10L16.875 10M13.75 10C13.75 10.6904 13.1904 11.25 12.5 11.25C11.8096 11.25 11.25 10.6904 11.25 10M13.75 10C13.75 9.30964 13.1904 8.75 12.5 8.75C11.8096 8.75 11.25 9.30964 11.25 10M3.125 10H11.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>',
            ],
            'payment_settings'      => [
                'label' => __( 'Payment Settings', 'wp-user-frontend' ),
                'icon'  => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
                                <path d="M3.125 5H16.875C17.5654 5 18.125 5.55964 18.125 6.25V13.75C18.125 14.4404 17.5654 15 16.875 15H3.125C2.43464 15 1.875 14.4404 1.875 13.75V6.25C1.875 5.55964 2.43464 5 3.125 5Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M5 15V10H15V15" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 10V5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>',
            ],
            'notification_settings' => [
                'label' => __( 'Notification Settings', 'wp-user-frontend' ),
                'icon'  => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
                    <path d="M14.8559 17.0817C16.7504 16.857 18.5773 16.4116 20.3102 15.7719C18.8734 14.177 17.9988 12.0656 17.9988 9.75V9.04919C17.999 9.03281 17.999 9.01641 17.999 9C17.999 5.68629 15.3127 3 11.999 3C8.68531 3 5.99902 5.68629 5.99902 9L5.99883 9.75C5.99883 12.0656 5.1243 14.177 3.6875 15.7719C5.42043 16.4116 7.24746 16.857 9.14216 17.0818M14.8559 17.0817C13.919 17.1928 12.9656 17.25 11.9988 17.25C11.0322 17.25 10.0789 17.1929 9.14216 17.0818M14.8559 17.0817C14.9488 17.3711 14.999 17.6797 14.999 18C14.999 19.6569 13.6559 21 11.999 21C10.3422 21 8.99902 19.6569 8.99902 18C8.99902 17.6797 9.04921 17.3712 9.14216 17.0818" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>',
            ],
            'display_settings'      => [
                'label' => __( 'Display Settings', 'wp-user-frontend' ),
                'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
                            <path d="M9 17.25V18.2574C9 19.053 8.68393 19.8161 8.12132 20.3787L7.5 21H16.5L15.8787 20.3787C15.3161 19.8161 15 19.053 15 18.2574V17.25M21 5.25V15C21 16.2426 19.9926 17.25 18.75 17.25H5.25C4.00736 17.25 3 16.2426 3 15V5.25M21 5.25C21 4.00736 19.9926 3 18.75 3H5.25C4.00736 3 3 4.00736 3 5.25M21 5.25V12C21 13.2426 19.9926 14.25 18.75 14.25H5.25C4.00736 14.25 3 13.2426 3 12V5.25" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>',
            ],
            'advanced'              => [
                'label' => __( 'Advanced', 'wp-user-frontend' ),
                'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
                                <path d="M4.5 11.9993C4.5 16.1414 7.85786 19.4993 12 19.4993C16.1421 19.4993 19.5 16.1414 19.5 11.9993M4.5 11.9993C4.5 7.85712 7.85786 4.49925 12 4.49925C16.1421 4.49926 19.5 7.85712 19.5 11.9993M4.5 11.9993L3 11.9993M19.5 11.9993L21 11.9993M19.5 11.9993L12 11.9993M3.54256 15.0774L4.9521 14.5644M19.0475 9.43411L20.457 8.92108M5.10547 17.785L6.25454 16.8208M17.7452 7.17897L18.8943 6.21479M7.4999 19.7943L8.2499 18.4952M15.7499 5.50484L16.4999 4.2058M10.4371 20.8633L10.6975 19.386M13.3023 4.61393L13.5627 3.13672M13.5627 20.8633L13.3023 19.3861M10.6976 4.61397L10.4371 3.13676M16.4999 19.7941L15.7499 18.4951M7.49995 4.20565L12 11.9993M18.8944 17.7843L17.7454 16.8202M6.25469 7.17835L5.10562 6.21417M20.4573 15.0776L19.0477 14.5646M4.95235 9.43426L3.54281 8.92123M12 11.9993L8.25 18.4944"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>',
            ],
            'post_expiration'       => [
                'label' => __( 'Post Expiration', 'wp-user-frontend' ),
                'icon'  => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="custom-stroke">
<path d="M6.75 3V5.25M17.25 3V5.25M3 18.75V7.5C3 6.25736 4.00736 5.25 5.25 5.25H18.75C19.9926 5.25 21 6.25736 21 7.5V18.75M3 18.75C3 19.9926 4.00736 21 5.25 21H18.75C19.9926 21 21 19.9926 21 18.75M3 18.75V11.25C3 10.0074 4.00736 9 5.25 9H18.75C19.9926 9 21 10.0074 21 11.25V18.75" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',
            ],
        ]
    );

    return apply_filters(
        'wpuf_form_builder_post_settings_menu_items',
        [
            'post_settings' => [
                'label'     => __( 'Post Settings', 'wp-user-frontend' ),
                'icon'      => '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.34332 1.94005C8.43373 1.39759 8.90307 1 9.45302 1H10.547C11.0969 1 11.5663 1.39759 11.6567 1.94005L11.8056 2.83386C11.8764 3.25813 12.1893 3.59838 12.5865 3.76332C12.9839 3.92832 13.4403 3.90629 13.7904 3.65617L14.528 3.12933C14.9755 2.80969 15.5885 2.86042 15.9774 3.24929L16.751 4.02284C17.1398 4.41171 17.1906 5.02472 16.8709 5.47223L16.3439 6.21007C16.0938 6.56012 16.0718 7.01633 16.2367 7.41363C16.4016 7.81078 16.7418 8.12363 17.166 8.19433L18.0599 8.34332C18.6024 8.43373 19 8.90307 19 9.45302V10.547C19 11.0969 18.6024 11.5663 18.0599 11.6567L17.1661 11.8056C16.7419 11.8764 16.4016 12.1893 16.2367 12.5865C16.0717 12.9839 16.0937 13.4403 16.3438 13.7904L16.8706 14.5278C17.1902 14.9753 17.1395 15.5884 16.7506 15.9772L15.9771 16.7508C15.5882 17.1396 14.9752 17.1904 14.5277 16.8707L13.7901 16.3439C13.44 16.0938 12.9837 16.0718 12.5864 16.2367C12.1892 16.4016 11.8764 16.7418 11.8057 17.166L11.6567 18.0599C11.5663 18.6024 11.0969 19 10.547 19H9.45302C8.90307 19 8.43373 18.6024 8.34332 18.0599L8.19435 17.1661C8.12364 16.7419 7.81072 16.4016 7.41349 16.2367C7.01608 16.0717 6.55975 16.0937 6.2096 16.3438L5.47198 16.8707C5.02447 17.1904 4.41146 17.1396 4.02259 16.7507L3.24904 15.9772C2.86017 15.5883 2.80944 14.9753 3.12909 14.5278L3.65612 13.79C3.90616 13.4399 3.92821 12.9837 3.76326 12.5864C3.59837 12.1892 3.25819 11.8764 2.83402 11.8057L1.94005 11.6567C1.39759 11.5663 1 11.0969 1 10.547V9.45302C1 8.90307 1.39759 8.43373 1.94005 8.34332L2.83386 8.19436C3.25813 8.12364 3.59838 7.81071 3.76332 7.41347C3.92833 7.01605 3.90629 6.5597 3.65618 6.20954L3.12948 5.47216C2.80983 5.02465 2.86057 4.41164 3.24943 4.02277L4.02298 3.24922C4.41185 2.86036 5.02486 2.80962 5.47237 3.12927L6.20997 3.65613C6.56004 3.90618 7.01628 3.92822 7.4136 3.76326C7.81077 3.59837 8.12364 3.25819 8.19433 2.834L8.34332 1.94005Z" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.0007 10C13.0007 11.6569 11.6576 13 10.0007 13C8.34384 13 7.0007 11.6569 7.0007 10C7.0007 8.34317 8.34384 7.00002 10.0007 7.00002C11.6576 7.00002 13.0007 8.34317 13.0007 10Z" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>',
                'sub_items' => $post_settings_fields,
            ],
        ]
    );
}

/**
 * Get the post form builder setting menu contents
 *
 * @since 4.1.0
 *
 * @return array
 */
function wpuf_get_post_form_builder_setting_menu_contents() {
    $post_types = get_post_types();
    $pages      = wpuf_get_pages();

    unset( $post_types['attachment'] );
    unset( $post_types['revision'] );
    unset( $post_types['nav_menu_item'] );
    unset( $post_types['wpuf_forms'] );
    unset( $post_types['wpuf_profile'] );
    unset( $post_types['wpuf_input'] );
    unset( $post_types['wpuf_subscription'] );
    unset( $post_types['custom_css'] );
    unset( $post_types['customize_changeset'] );
    unset( $post_types['wpuf_coupon'] );
    unset( $post_types['oembed_cache'] );

    $template_options = [
        ''                                        => __( '-- Select Template --', 'wp-user-frontend' ),
        'post_form_template_post'                 => __( 'Post Form', 'wp-user-frontend' ),
        'post_form_template_woocommerce'          => __( 'WooCommerce Product Form', 'wp-user-frontend' ),
        'post_form_template_edd'                  => __( 'EDD Download Form', 'wp-user-frontend' ),
        'post_form_template_events_calendar'      => __( 'Event Form', 'wp-user-frontend' ),
        'post_form_template_video'                => __( 'Video Form', 'wp-user-frontend' ),
        'post_form_template_paid_guest_post'      => __( 'Paid Guest Post', 'wp-user-frontend' ),
        'post_form_template_guest_post_recurring' => __( 'Guest Post (Recurring Subscription)', 'wp-user-frontend' ),
        'post_form_template_professional_video'   => __( 'Professional Video Form', 'wp-user-frontend' ),
        'post_form_template_artwork'              => __( 'Artwork Form', 'wp-user-frontend' ),
        'post_form_template_press_release'        => __( 'Press Release Form', 'wp-user-frontend' ),
        'post_form_template_portfolio'            => __( 'Portfolio Form', 'wp-user-frontend' ),
        'post_form_template_volunteer'            => __( 'Volunteer Opportunity Form', 'wp-user-frontend' ),
    ];

    $registry = wpuf_get_post_form_templates();

    foreach ( $registry as $key => $template ) {
        if ( ! $template->is_enabled() ) {
            // remove the template if it's not enabled from $template_options
            unset( $template_options[ $key ] );
        }
    }

    $general = apply_filters(
        'wpuf_form_builder_settings_general',
        [
            'section' => [
                'before_post_settings' => [
                    'label'  => __( 'Before Post Settings', 'wp-user-frontend' ),
                    'desc'   => __(
                        'Configure how the form behaves before submission, including content type, default category, post status, draft-saving options, and submit button customization',
                        'wp-user-frontend'
                    ),
                    'fields' => [
                        'show_form_title' => [
                            'label'     => __( 'Show Form Title', 'wp-user-frontend' ),
                            'type'      => 'toggle',
                            'help_text' => __( 'Toggle whether the form title should be displayed on the frontend', 'wp-user-frontend' ),
                        ],
                        'form_description' => [
                            'label'     => __( 'Form Description', 'wp-user-frontend' ),
                            'type'      => 'textarea',
                            'help_text' => __( 'Add a brief message or instruction that will be displayed before the form — helpful for guiding users before they start filling it out', 'wp-user-frontend' ),
                        ],
                        'post_type'        => [
                            'label'     => __( 'Post Type', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'options'   => $post_types,
                            'help_text' => __(
                                'Select the content type this form will create, like a post, product, or custom type',
                                'wp-user-frontend'
                            ),
                            'link'      => esc_url_raw(
                                'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/different-custom-post-type-submission-2/'
                            ),
                        ],
                        'default_category' => [
                            'label'     => __( 'Default Category', 'wp-user-frontend' ),
                            'type'      => 'multi-select',
                            'help_text' => __(
                                'Select the default category for posts submitted through this form', 'wp-user-frontend'
                            ),
                            'options'   => wpuf_get_terms(),
                        ],
                        'redirect_to'      => [
                            'label'     => __( 'Successful Redirection', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'help_text' => __(
                                'Select where users will be redirected after successfully submitting the form',
                                'wp-user-frontend'
                            ),
                            'options'   => [
                                'post' => __( 'Newly created post', 'wp-user-frontend' ),
                                'same' => __( 'Same page', 'wp-user-frontend' ),
                                'page' => __( 'To a page', 'wp-user-frontend' ),
                                'url'  => __( 'To a custom URL', 'wp-user-frontend' ),
                            ],
                        ],
                        'message'          => [
                            'label'   => __( 'Message to show', 'wp-user-frontend' ),
                            'type'    => 'textarea',
                            'default' => __( 'Post saved', 'wp-user-frontend' ),
                        ],
                        'page_id'          => [
                            'label'     => __( 'Page', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'options'   => $pages,
                            'help_text' => __( 'The page users are sent to after submitting the form', 'wp-user-frontend' ),
                        ],
                        'url'              => [
                            'label' => __( 'Custom URL', 'wp-user-frontend' ),
                            'type'  => 'text',
                        ],
                        'post_status'      => [
                            'label'     => __( 'Post Submission Status', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'options'   => [
                                'draft'          => __( 'Draft', 'wp-user-frontend' ),
                                'pending'        => __( 'Pending Review', 'wp-user-frontend' ),
                                'private'        => __( 'Private', 'wp-user-frontend' ),
                                'publish'        => __( 'Published', 'wp-user-frontend' ),
                            ],
                            'help_text' => __( 'Select the status of the post after submission', 'wp-user-frontend' ),
                        ],
                        'draft_post'       => [
                            'label'     => __( 'Enable saving as draft', 'wp-user-frontend' ),
                            'type'      => 'toggle',
                            'help_text' => __(
                                'Allow users to save posts before final submission', 'wp-user-frontend'
                            ),
                        ],
                        'submit_text'      => [
                            'label'     => __( 'Submit Post Button Text', 'wp-user-frontend' ),
                            'type'      => 'text',
                            'help_text' => __(
                                'Customize the text on the \'Submit\' button for this form', 'wp-user-frontend'
                            ),
                        ],
                        'form_template'    => [
                            'label'     => __( 'Choose Form Template', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'help_text' => __(
                                'If selected a form template, it will try to execute that integration options when new post created and updated.',
                                'wp-user-frontend'
                            ),
                            'options'   => $template_options,
                        ],
                    ],
                ],
                'after_post_settings'  => [
                    'label'  => __( 'After Post Settings', 'wp-user-frontend' ),
                    'desc'   => __(
                        'Manage what happens after submission, such as setting post update status, displaying success messages, redirecting users, limiting edit time, and customizing the update button',
                        'wp-user-frontend'
                    ),
                    'fields' => [
                        'edit_post_status' => [
                            'label'     => __( 'Post Update Status', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'options'   => [
                                'draft'     => __( 'Draft', 'wp-user-frontend' ),
                                'pending'   => __( 'Pending Review', 'wp-user-frontend' ),
                                'private'   => __( 'Private', 'wp-user-frontend' ),
                                'publish'   => __( 'Published', 'wp-user-frontend' ),
                                '_nochange' => __( 'No Change', 'wp-user-frontend' ),
                            ],
                            'help_text' => __(
                                'Select the status the post will have after being updated', 'wp-user-frontend'
                            ),
                        ],
                        'edit_redirect_to' => [
                            'label'     => __( 'Successful Redirection', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'help_text' => __(
                                'After successfully submit, where the page will redirect to', 'wp-user-frontend'
                            ),
                            'options'   => [
                                'post' => __( 'Updated post', 'wp-user-frontend' ),
                                'same' => __( 'Same page', 'wp-user-frontend' ),
                                'page' => __( 'To a page', 'wp-user-frontend' ),
                                'url'  => __( 'To a custom URL', 'wp-user-frontend' ),
                            ],
                        ],
                        'update_message'   => [
                            'label'     => __( 'Post Update Message', 'wp-user-frontend' ),
                            'type'      => 'textarea',
                            'default'   => sprintf(
                                /* translators: %s: link to the updated post */
                                __(
                                    'Post has been updated successfully. <a target="_blank" href="%s">View post</a>',
                                    'wp-user-frontend'
                                ), '{link}'
                            ),
                            'help_text' => __(
                                'Customize the message displayed after a post is successfully updated',
                                'wp-user-frontend'
                            ),
                        ],
                        'edit_page_id'     => [
                            'label'     => __( 'Page', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'options'   => $pages,
                            'help_text' => __( 'The page users are sent to after updating a post', 'wp-user-frontend' ),
                        ],
                        'edit_url'         => [
                            'label' => __( 'Custom URL', 'wp-user-frontend' ),
                            'type'  => 'text',
                        ],
                        'lock_edit_post'   => [
                            'label'         => __( 'Lock User Editing After', 'wp-user-frontend' ),
                            'type'          => 'trailing-text',
                            'help_text'     => __(
                                'Set the number of hours after which users can no longer edit their submitted post',
                                'wp-user-frontend'
                            ),
                            'trailing_type' => 'number',
                            'trailing_text' => __( 'Hours', 'wp-user-frontend' ),
                        ],
                        'update_text'      => [
                            'label'     => __( 'Update Post Button Text', 'wp-user-frontend' ),
                            'type'      => 'text',
                            'help_text' => __(
                                'Customize the text on the \'Update\' button for this form', 'wp-user-frontend'
                            ),
                        ],
                    ],
                ],
                'posting_control'      => [
                    'label'  => __( 'Posting Control', 'wp-user-frontend' ),
                    'desc'   => __(
                        'Define who can submit posts using this form. Choose whether to allow guest submissions or restrict access based on user roles',
                        'wp-user-frontend'
                    ),
                    'fields' => [
                        'post_permission'    => [
                            'label'     => __( 'Post Permission', 'wp-user-frontend' ),
                            'type'      => 'select',
                            'options'   => [
                                'everyone'   => __( '- Select Post Permission -', 'wp-user-frontend' ),
                                'guest_post' => __( 'Guest Post', 'wp-user-frontend' ),
                                'role_base'  => __( 'Role Based Post', 'wp-user-frontend' ),
                            ],
                            'help_text' => __(
                                'Select who can submit posts using this form, either guests or specific user roles',
                                'wp-user-frontend'
                            ),
                        ],
                        'guest_details'      => [
                            'label'     => __( 'Require Name and Email address', 'wp-user-frontend' ),
                            'type'      => 'checkbox',
                            'sub_label' => __(
                                'If enabled, guest users will be automatically registered using their name and email',
                                'wp-user-frontend'
                            ),
                        ],
                        'inline_fields'      => [
                            'fields' => [
                                'name_label'  => [
                                    'label'     => __( 'Name Label', 'wp-user-frontend' ),
                                    'type'      => 'text',
                                    'help_text' => __(
                                        'Customize the label for the name field in guest submissions',
                                        'wp-user-frontend'
                                    ),
                                ],
                                'email_label' => [
                                    'label'     => __( 'E-Mail Label', 'wp-user-frontend' ),
                                    'type'      => 'text',
                                    'help_text' => __(
                                        'Customize the label for the email field in guest submissions',
                                        'wp-user-frontend'
                                    ),
                                ],
                            ],
                        ],
                        'guest_email_verify' => [
                            'label'     => __( 'Require email verification', 'wp-user-frontend' ),
                            'type'      => 'checkbox',
                            'sub_label' => __(
                                'If enabled, users must verify their email before submitting a post', 'wp-user-frontend'
                            ),
                        ],
                        'roles'              => [
                            'label'           => __( 'Choose who can submit post', 'wp-user-frontend' ),
                            'type'            => 'multi-select',
                            'help_text'       => __( 'Select the user roles who can submit posts', 'wp-user-frontend' ),
                            'options'         => wpuf_get_user_roles(),
                            'always_selected' => [ 'administrator' ],
                        ],
                        'message_restrict'   => [
                            'label'     => __( 'Unauthorized Message', 'wp-user-frontend' ),
                            'type'      => 'textarea',
                            'help_text' => __(
                                'Display this message to non-logged-in users. Use {login} and {register} placeholders to add login and registration links',
                                'wp-user-frontend'
                            ),
                        ],
                    ],
                ],
            ],
        ]
    );

    $payment = apply_filters(
        'wpuf_form_builder_settings_payment',
        [
            'payment_options'          => [
                'label'     => __( 'Enable Payments', 'wp-user-frontend' ),
                'type'      => 'toggle',
                'help_text' => __(
                    'Enable payments for this form to charge users for submissions', 'wp-user-frontend'
                ),
            ],
            'choose_payment_option'    => [
                'label'   => __( 'Choose Payment Option', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => [
                    'force_pack_purchase' => __( 'Mandatory Subscription', 'wp-user-frontend' ),
                    'enable_pay_per_post' => __( 'Pay as you post', 'wp-user-frontend' ),
                ],
                'help_text' => __(
                    'Select how users will pay for submitting posts', 'wp-user-frontend'
                ),
            ],
            'fallback_ppp_enable'      => [
                'label'     => __( 'Pay-per-post billing when limit exceeds', 'wp-user-frontend' ),
                'type'      => 'checkbox',
                'sub_label' => __( 'Switch to pay-per-post billing if pack limit is exceeded', 'wp-user-frontend' ),
            ],
            'fallback_ppp_cost'        => [
                'label' => __( 'Cost for each additional post after pack limit is reached', 'wp-user-frontend' ),
                'type'  => 'number',
                'help_text' => __( 'This field is required when Pay-per-post billing when limit exceeds is enabled.', 'wp-user-frontend' ),
            ],
            'pay_per_post_cost'        => [
                'label'     => __( 'Charge for each post', 'wp-user-frontend' ),
                'type'      => 'number',
                'help_text' => __(
                    'Set a fee for each post submission. This field is required when Pay as you post is selected.', 'wp-user-frontend'
                ),
            ],
            'ppp_payment_success_page' => [
                'label'   => __( 'Payment Success Page', 'wp-user-frontend' ),
                'type'    => 'select',
                'options' => $pages,
                'help_text'    => __( 'Select the page to redirect after successful payment.', 'wp-user-frontend' ),
            ],
        ]
    );

    $notification = apply_filters(
        'wpuf_form_builder_settings_notification',
        [
            'section' => [
                'new_post' => [
                    'label'  => __( 'New Post Notification', 'wp-user-frontend' ),
                    'desc'   => __(
                        'Enable email alerts for new post submissions via the frontend form. This feature keeps you updated on user activity, allowing for timely review and approval of content',
                        'wp-user-frontend'
                    ),
                    'fields' => [
                        'new'         => [
                            'label' => __( 'New Post Notification', 'wp-user-frontend' ),
                            'type'  => 'toggle',
                            'help_text'  => __( 'Enable email alerts for new submissions through this form', 'wp-user-frontend' ),
                            'name'  => 'wpuf_settings[notification][new]',
                        ],
                        'new_to'      => [
                            'label' => __( 'To', 'wp-user-frontend' ),
                            'type'  => 'text',
                            'value' => get_option( 'admin_email' ),
                            'name'  => 'wpuf_settings[notification][new_to]',
                        ],
                        'new_subject' => [
                            'label' => __( 'Subject', 'wp-user-frontend' ),
                            'type'  => 'text',
                            'value' => __( 'New post created', 'wp-user-frontend' ),
                            'name'  => 'wpuf_settings[notification][new_subject]',
                        ],
                        'new_body'    => [
                            'label'     => __( 'Email Body', 'wp-user-frontend' ),
                            'type'      => 'textarea',
                            'name'      => 'wpuf_settings[notification][new_body]',
                            'value'     => "Hi Admin, \r\n\r\nA new post has been created in your site {sitename} ({siteurl}). \r\n\r\nHere is the details: \r\nPost Title: {post_title} \r\nContent: {post_content} \r\nAuthor: {author} \r\nPost URL: {permalink} \r\nEdit URL: {editlink}",
                            'long_help' => '<h4 class="wpuf-mt-[24px] wpuf-mb-0">You may use in to, subject & message:</h4>
                                         <p class="wpuf-leading-8 !wpuf-ml-0">
                                         <span data-clipboard-text="{post_title}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{post_title}</span>
                                         <span data-clipboard-text="{post_content}" class="wpuf-post-content wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{post_content}</span>
                                         <span data-clipboard-text="{post_excerpt}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{post_excerpt}</span>
                                         <span data-clipboard-text="{tags}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{tags}</span>
                                         <span data-clipboard-text="{category}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{category}</span>
                                         <span data-clipboard-text="{author}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{author}</span>
                                         <span data-clipboard-text="{author_email}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{author_email}</span>
                                         <span data-clipboard-text="{author_bio}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{author_bio}</span>
                                         <span data-clipboard-text="{sitename}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{sitename}</span>
                                         <span data-clipboard-text="{siteurl}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{siteurl}</span>
                                         <span data-clipboard-text="{permalink}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{permalink}</span>
                                         <span data-clipboard-text="{editlink}" class="wpuf-pill-green hover:wpuf-cursor-pointer wpuf-template-text">{editlink}</span>
                                         <span class="wpuf-pill-green">{custom_{NAME_OF_CUSTOM_FIELD}}</span>
                                         e.g: <span class="wpuf-pill-green">{custom_website_url}</span> for <i>website_url</i> meta field</p>',
                        ],
                    ],
                ],
                'update_post' => [
                    'label'  => __( 'Update Post Notification', 'wp-user-frontend' ),
                    'desc'   => __(
                        'Enable this feature to receive email alerts whenever an existing post is updated through the frontend form. This ensures you\'re promptly informed about any changes made by users, allowing you to review and manage content updates',
                        'wp-user-frontend'
                    ),
                ],
            ],
        ]
    );

    $display = apply_filters(
        'wpuf_form_builder_settings_display',
        [
            'section' => [
                'custom_form_style' => [
                    'label'  => __( 'Choose Form Style', 'wp-user-frontend' ),
                    'desc'   => __(
                        'Customize the appearance and layout of your form. Select a form template that best suits your website\'s design for a cohesive look and feel',
                        'wp-user-frontend'
                    ),
                    'fields' => [
                        'form_layout'    => [
                            'label'     => __( 'Choose Form Style', 'wp-user-frontend' ),
                            'type'      => 'pic-radio',
                            'help_text' => __( 'Pick a form template to control the overall layout and visual style of your form.', 'wp-user-frontend' ),
                            'options'   => [
                                'layout1' => [
                                    'label' => __( 'Template 1', 'wp-user-frontend' ),
                                    'image' => wpuf_form_layout_image( WPUF_ASSET_URI . '/images/forms/layout1.png', WPUF_ROOT . '/assets/images/forms/layout1.png' ),
                                ],
                                'layout2' => [
                                    'label' => __( 'Template 2', 'wp-user-frontend' ),
                                    'image' => wpuf_form_layout_image( WPUF_ASSET_URI . '/images/forms/layout2.png', WPUF_ROOT . '/assets/images/forms/layout2.png' ),
                                ],
                                'layout3' => [
                                    'label' => __( 'Template 3', 'wp-user-frontend' ),
                                    'image' => wpuf_form_layout_image( WPUF_ASSET_URI . '/images/forms/layout3.png', WPUF_ROOT . '/assets/images/forms/layout3.png' ),
                                ],
                                'layout4' => [
                                    'label' => __( 'Template 4', 'wp-user-frontend' ),
                                    'image' => wpuf_form_layout_image( WPUF_ASSET_URI . '/images/forms/layout4.png', WPUF_ROOT . '/assets/images/forms/layout4.png' ),
                                ],
                                'layout5' => [
                                    'label' => __( 'Template 5', 'wp-user-frontend' ),
                                    'image' => wpuf_form_layout_image( WPUF_ASSET_URI . '/images/forms/layout5.png', WPUF_ROOT . '/assets/images/forms/layout5.png' ),
                                ],
                            ],
                        ],
                        'use_theme_css'  => [
                            'label'     => __( 'Use Theme CSS', 'wp-user-frontend' ),
                            'type'      => 'toggle',
                            'help_text' => __(
                                'Apply your site\'s theme CSS for consistent styling and appearance', 'wp-user-frontend'
                            ),
                        ],
                        'label_position' => [
                            'label'   => __( 'Label Position', 'wp-user-frontend' ),
                            'type'    => 'select',
                            'options' => [
                                'above'   => __( 'Above Element', 'wp-user-frontend' ),
                                'left'    => __( 'Left of Element', 'wp-user-frontend' ),
                                'right'   => __( 'Right of Element', 'wp-user-frontend' ),
                                'hidden'  => __( 'Hidden', 'wp-user-frontend' ),
                            ],
                            'help'    => __( 'Customize the position of form labels for improved user navigation and clarity', 'wp-user-frontend' ),
                        ],
                    ],
                ],
            ],
        ]
    );

    $advanced = apply_filters(
        'wpuf_form_builder_settings_advanced',
        [
            'comment_status'       => [
                'label' => __( 'Enable User Comment', 'wp-user-frontend' ),
                'type'  => 'select',
                'help'    => __( 'Allow users to comment on posts submitted via this form', 'wp-user-frontend' ),
                'options' => [
                    'open'   => __( 'Open', 'wp-user-frontend' ),
                    'closed'   => __( 'Closed', 'wp-user-frontend' ),
                ],
            ],
            'schedule_form'        => [
                'label'     => __( 'Enable Form Scheduling', 'wp-user-frontend' ),
                'type'      => 'toggle',
                'help_text' => __(
                    'Set specific dates and times for when the form will be accessible to users', 'wp-user-frontend'
                ),
            ],
            'inline_fields'        => [
                'fields' => [
                    'schedule_start' => [
                        'label' => __( 'From', 'wp-user-frontend' ),
                        'type'  => 'date',
                    ],
                    'schedule_end'   => [
                        'label' => __( 'To', 'wp-user-frontend' ),
                        'type'  => 'date',
                    ],
                ],
            ],
            'form_pending_message' => [
                'label' => __( 'Form Pending Message', 'wp-user-frontend' ),
                'type'  => 'textarea',
            ],
            'form_expired_message' => [
                'label' => __( 'Form Expired Message', 'wp-user-frontend' ),
                'type'  => 'textarea',
            ],
            'limit_entries'        => [
                'label'     => __( 'Limit Form Entries', 'wp-user-frontend' ),
                'type'      => 'toggle',
                'help_text' => __( 'Limit the number of submissions allowed for the form', 'wp-user-frontend' ),
            ],
            'limit_number'         => [
                'label'   => __( 'Number of Entries', 'wp-user-frontend' ),
                'type'    => 'number',
                'default' => 100,
            ],
            'limit_message'        => [
                'label' => __( 'Limit Reached Message', 'wp-user-frontend' ),
                'type'  => 'textarea',
            ],
        ]
    );

    $post_expiration = apply_filters(
        'wpuf_form_builder_settings_post_expiration', []
    );

    $post_settings['post_settings'] = apply_filters(
        'wpuf_form_builder_post_settings',
        [
            'general'               => $general,
            'payment_settings'      => $payment,
            'notification_settings' => $notification,
            'display_settings'      => $display,
            'advanced'              => $advanced,
            'post_expiration'       => $post_expiration,
        ]
    );

    return apply_filters(
        'wpuf_post_form_builder_setting_menu_contents',
        $post_settings
    );
}

/**
 * Get the post forms count
 *
 * @since 4.1.4
 *
 * @param string $post_type Post type to count
 * @return array Array of counts with labels
 */
function wpuf_get_forms_counts_with_status( $post_type = 'wpuf_forms' ) {
    $post_counts = (array) wp_count_posts( $post_type );

    // REST requests (the admin app list) run before the form post types are
    // registered, and wp_count_posts() answers nothing for an unknown type.
    if ( ! post_type_exists( $post_type ) ) {
        global $wpdb;

        $rows        = $wpdb->get_results( $wpdb->prepare( "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s GROUP BY post_status", $post_type ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $post_counts = [];

        foreach ( (array) $rows as $row ) {
            $post_counts[ $row->post_status ] = (int) $row->num_posts;
        }
    }

    $post_statuses = apply_filters(
        'wpuf_post_forms_list_table_post_statuses',
        [
            'all'     => __( 'All', 'wp-user-frontend' ),
            'publish' => __( 'Saved', 'wp-user-frontend' ),
            'draft'   => __( 'Drafts', 'wp-user-frontend' ),
            'trash'   => __( 'Trash', 'wp-user-frontend' ),
        ]
    );

    $status_count = [];
    $total_count = 0;

    // Calculate total count (excluding trash)
    foreach ( $post_counts as $status => $count ) {
        if ( 'trash' !== $status ) {
            $total_count += (int) $count;
        }
    }

    // Set up the counts array
    foreach ( $post_statuses as $key => $label ) {
        if ( 'all' === $key ) {
            $status_count[ $key ] = [
                'label' => $label,
                'count' => $total_count,
            ];
        } else {
            $count = isset( $post_counts[ $key ] ) ? (int) $post_counts[ $key ] : 0;
            $status_count[ $key ] = [
                'label' => $label,
                'count' => $count,
            ];
        }
    }

    return $status_count;
}
