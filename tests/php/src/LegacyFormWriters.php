<?php
/**
 * Verbatim copies of the form writers as they were before task 2.4a, kept as
 * the reference the store tests compare stored bytes against.
 *
 * @package WP_User_Frontend
 */

// phpcs:ignoreFile -- reference copies, kept byte for byte.

/**
 * Pre-store Admin_Form_Builder writers.
 */
class WPUF_Legacy_Form_Builder {

    public static function save_form( $data ) {
        $saved_wpuf_inputs = [];
        wp_update_post( [ 'ID' => $data['form_id'], 'post_status' => 'publish', 'post_title' => $data['post_title'] ] );
        $existing_wpuf_input_ids = get_children(
            [
                'post_parent' => $data['form_id'],
                'post_status' => 'publish',
                'post_type'   => 'wpuf_input',
                'numberposts' => '-1',
                'orderby'     => 'menu_order',
                'order'       => 'ASC',
                'fields'      => 'ids',
            ]
        );
        $new_wpuf_input_ids = [];
        if ( ! empty( $data['form_fields'] ) ) {
            foreach ( $data['form_fields'] as $order => $field ) {
                if ( ! empty( $field['is_new'] ) ) {
                    unset( $field['is_new'] );
                    unset( $field['id'] );
                    $field_id = 0;
                } else {
                    $field_id = $field['id'];
                }
                $field_id = wpuf_legacy_insert_form_field( $data['form_id'], $field, $field_id, $order );
                $new_wpuf_input_ids[] = $field_id;
                $field['id'] = $field_id;
                $field['is_new'] = false;  // Mark as saved field
                $saved_wpuf_inputs[] = $field;
            }
        }
        $inputs_to_delete = array_diff( $existing_wpuf_input_ids, $new_wpuf_input_ids );
        // Without Pro the builder never loads custom taxonomy fields, so their
        // absence from the save is not a removal.
        $inputs_to_delete = array_diff( $inputs_to_delete, self::get_hidden_pro_taxonomy_input_ids( $inputs_to_delete ) );
        if ( ! empty( $inputs_to_delete ) ) {
            foreach ( $inputs_to_delete as $delete_id ) {
                wp_delete_post( $delete_id, true );
            }
        }

        // Filter out pro notification settings if pro version is not active
        if ( ! wpuf_is_pro_active() && isset( $data['form_settings']['notification'] ) ) {
            // Remove update post notification settings for free version
            if ( isset( $data['form_settings']['notification']['edit'] ) ) {
                unset( $data['form_settings']['notification']['edit'] );
            }
            if ( isset( $data['form_settings']['notification']['edit_to'] ) ) {
                unset( $data['form_settings']['notification']['edit_to'] );
            }
            if ( isset( $data['form_settings']['notification']['edit_subject'] ) ) {
                unset( $data['form_settings']['notification']['edit_subject'] );
            }
            if ( isset( $data['form_settings']['notification']['edit_body'] ) ) {
                unset( $data['form_settings']['notification']['edit_body'] );
            }
        }

        // Also filter out standalone notification_edit field if it exists
        if ( ! wpuf_is_pro_active() && isset( $data['form_settings']['notification_edit'] ) ) {
            unset( $data['form_settings']['notification_edit'] );
        }

        update_post_meta( $data['form_id'], $data['form_settings_key'], $data['form_settings'] );
        self::update_list_meta( $data['form_id'], 'notifications', $data['notifications'] );

        if ( isset( $data['integrations'] ) ) {
            self::update_list_meta( $data['form_id'], 'integrations', $data['integrations'] );
        }

        return $saved_wpuf_inputs;
    }

    protected static function get_hidden_pro_taxonomy_input_ids( $input_ids ) {
        if ( empty( $input_ids ) || wpuf_is_pro_active() ) {
            return [];
        }

        $free_taxonomies = wpuf_get_free_taxonomies();
        $hidden          = [];

        foreach ( $input_ids as $input_id ) {
            $field = maybe_unserialize( get_post_field( 'post_content', $input_id ) );

            if (
                is_array( $field )
                && isset( $field['input_type'], $field['name'] )
                && 'taxonomy' === $field['input_type']
                && ! in_array( $field['name'], $free_taxonomies, true )
            ) {
                $hidden[] = $input_id;
            }
        }

        return $hidden;
    }

    /**
     * Store a list meta (notifications, integrations) unless an empty list would
     * only replace a stored empty value ('' or no meta) with `[]`.
     *
     * @since WPUF_SINCE
     *
     * @param int    $form_id  Form id.
     * @param string $meta_key Meta key.
     * @param mixed  $value    Value from the builder.
     *
     * @return void
     */
    protected static function update_list_meta( $form_id, $meta_key, $value ) {
        if ( empty( $value ) && empty( get_post_meta( $form_id, $meta_key, true ) ) ) {
            return;
        }

        update_post_meta( $form_id, $meta_key, $value );
    }

}

function wpuf_legacy_duplicate_form( $post_id ) {
    $post = get_post( $post_id );

    if ( ! $post ) {
        return;
    }

    $contents = wpuf_get_form_fields( $post_id );

    $new_form = [
        'post_title'  => $post->post_title,
        'post_type'   => $post->post_type,
        'post_status' => 'draft',
    ];

    $form_id = wp_insert_post( $new_form );

    foreach ( $contents as $content ) {
        wpuf_legacy_insert_form_field( $form_id, $content );
    }

    // update the post title to remove confusion
    wp_update_post(
        [
            'ID'         => $form_id,
            'post_title' => $post->post_title . ' (#' . $form_id . ')',
        ]
    );

    if ( $form_id ) {
        $form_settings = wpuf_get_form_settings( $post_id );
        $notifications = wpuf_get_form_notifications( $post_id );

        update_post_meta( $form_id, 'wpuf_form_settings', $form_settings );
        update_post_meta( $form_id, 'notifications', $notifications );

        // The copy carries the source form's integrations and version too.
        foreach ( [ 'integrations', 'wpuf_form_version' ] as $meta_key ) {
            if ( metadata_exists( 'post', $post_id, $meta_key ) ) {
                update_post_meta( $form_id, $meta_key, get_post_meta( $post_id, $meta_key, true ) );
            }
        }

        return $form_id;
    }

    return 0;
}

function wpuf_legacy_insert_form_field( $form_id, $field = [], $field_id = null, $order = 0 ) {
    $args = [
        'post_type'    => 'wpuf_input',
        'post_parent'  => $form_id,
        'post_status'  => 'publish',
        'post_content' => maybe_serialize( wp_unslash( $field ) ),
        'menu_order'   => $order,
    ];

    if ( $field_id ) {
        $args['ID'] = $field_id;
    }

    if ( $field_id ) {
        return wp_update_post( $args );
    } else {
        return wp_insert_post( $args );
    }
}

function wpuf_legacy_create_sample_form( $post_title = 'Sample Form', $post_type = 'wpuf_forms', $blank = false ) {
    $form_id = wp_insert_post(
        [
            'post_title'     => $post_title,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'post_content'   => '',
        ]
    );

    if ( ! $form_id ) {
        return false;
    }

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

    if ( ! empty( $form_fields ) && ! $blank ) {
        foreach ( $form_fields as $order => $field ) {
            wpuf_legacy_insert_form_field( $form_id, $field, false, $order );
        }
    }

    if ( ! empty( $settings ) ) {
        update_post_meta( $form_id, 'wpuf_form_settings', $settings );
    }

    //set form Version
    update_post_meta( $form_id, 'wpuf_form_version', WPUF_VERSION );

    return $form_id;
}

function wpuf_legacy_delete_form( $form_id, $force = true ) {
    global $wpdb;

    wp_delete_post( $form_id, $force );

    // delete form inputs as WP doesn't know the relationship
    $wpdb->delete(
        $wpdb->posts,
        [
            'post_parent' => $form_id,
            'post_type'   => 'wpuf_input',
        ]
    );
}

/**
 * The form insert Form_Template::create_post_form_from_template() and
 * Onboarding::create_form_from_template() ran (same code in both).
 */
function wpuf_legacy_create_form_from_template( $template_object ) {
    $form_id = wp_insert_post(
        [
            'post_title'  => $template_object->get_title(),
            'post_type'   => 'wpuf_forms',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]
    );

    if ( is_wp_error( $form_id ) ) {
        return false;
    }

    update_post_meta( $form_id, 'wpuf_form_settings', $template_object->get_form_settings() );
    update_post_meta( $form_id, 'wpuf_form_version', WPUF_VERSION );

    $form_fields = $template_object->get_form_fields();

    if ( $form_fields ) {
        foreach ( $form_fields as $menu_order => $field ) {
            wp_insert_post(
                [
                    'post_type'    => 'wpuf_input',
                    'post_status'  => 'publish',
                    'post_content' => maybe_serialize( $field ),
                    'post_parent'  => $form_id,
                    'menu_order'   => $menu_order,
                ]
            );
        }
    }

    return $form_id;
}

/**
 * Form_Manager::create() before task 2.4a.
 */
function wpuf_legacy_form_manager_create( $form_name, $fields = [] ) {
    $form_id = wp_insert_post( [
        'post_title'  => $form_name,
        'post_type'   => 'wpuf_forms',
        'post_status' => 'publish',
    ] );
    if ( is_wp_error( $form_id ) ) {
        return $form_id;
    }
    if ( $fields ) {
        foreach ( $fields as $order => $field ) {
            $args = [
                'post_type'    => 'wpuf_input',
                'post_parent'  => $form_id,
                'post_status'  => 'publish',
                'post_content' => maybe_serialize( wp_unslash( $field ) ),
                'menu_order'   => $order,
            ];
            wp_insert_post( $args );
        }
    }

    return $form_id;
}

/**
 * Forms list trash / restore (Admin_Form_Handler) before task 2.4a.
 */
function wpuf_legacy_list_trash( $id ) {
    delete_post_meta( $id, '_wp_trash_meta_status' );
    delete_post_meta( $id, '_wp_trash_meta_time' );
    delete_post_meta( $id, '_wp_desired_post_slug' );

    wp_trash_post( $id );
}

function wpuf_legacy_list_restore( $id ) {
    $trash_meta_status = get_post_meta( $id, '_wp_trash_meta_status', true );

    $args = [
        'ID'            => $id,
        'post_status'   => $trash_meta_status,
    ];

    wp_update_post( $args );
}

/**
 * Form_Settings_Cleanup::remove_pro_notification_settings() before task 2.4a.
 */
function wpuf_legacy_remove_pro_notification_settings( $form_settings ) {
    if ( ! is_array( $form_settings ) ) {
        return $form_settings;
    }

    $pro_notification_keys = [
        'notification_edit',
        'notification_edit_to',
        'notification_edit_subject',
        'notification_edit_body'
    ];

    foreach ( $pro_notification_keys as $key ) {
        if ( isset( $form_settings[ $key ] ) ) {
            unset( $form_settings[ $key ] );
        }
    }

    if ( isset( $form_settings['notification'] ) && is_array( $form_settings['notification'] ) ) {
        $notification_pro_keys = [ 'edit', 'edit_to', 'edit_subject', 'edit_body' ];

        foreach ( $notification_pro_keys as $key ) {
            if ( isset( $form_settings['notification'][ $key ] ) ) {
                unset( $form_settings['notification'][ $key ] );
            }
        }
    }

    return $form_settings;
}
