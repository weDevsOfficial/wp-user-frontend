<?php

use WeDevs\Wpuf\Platform\Stores\Stores;
/**
 * Posts, attachments, taxonomies and the submitted-post helpers
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

/**
 * Format the post status for user dashboard
 *
 * @param string $status
 *
 * @since version 0.1
 *
 * @author Tareq Hasan
 */
function wpuf_show_post_status( $status ) {
    if ( 'publish' === $status ) {
        $title     = __( 'Live', 'wp-user-frontend' );
        $fontcolor = 'rgb(5, 150, 105)';
    } elseif ( 'draft' === $status ) {
        $title     = __( 'Offline', 'wp-user-frontend' );
        $fontcolor = '#bbbbbb';
    } elseif ( 'pending' === $status ) {
        $title     = __( 'Awaiting Approval', 'wp-user-frontend' );
        $fontcolor = '#C00202';
    } elseif ( 'future' === $status ) {
        $title     = __( 'Scheduled', 'wp-user-frontend' );
        $fontcolor = '#bbbbbb';
    } elseif ( 'private' === $status ) {
        $title     = __( 'Private', 'wp-user-frontend' );
        $fontcolor = '#bbbbbb';
    }

    $show_status = '<span style="color:' . $fontcolor . ';">' . $title . '</span>';
    echo wp_kses_post( apply_filters( 'wpuf_show_post_status', $show_status, $status ) );
}

/**
 * Get the attachments of a post
 *
 * @param int $post_id
 *
 * @return array attachment list
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpfu_get_attachments( $post_id ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $att_list = [];

    $args = [
        'post_type'   => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post_id,
        'order'       => 'ASC',
        'orderby'     => 'menu_order',
    ];

    $attachments = get_posts( $args );

    foreach ( $attachments as $attachment ) {
        $att_list[] = [
            'id'    => $attachment->ID,
            'title' => $attachment->post_title,
            'url'   => wp_get_attachment_url( $attachment->ID ),
            'mime'  => $attachment->post_mime_type,
        ];
    }

    return $att_list;
}

/**
 * Remove the mdedia upload tabs from subscribers
 *
 * @author Tareq Hasan
 */
function wpuf_unset_media_tab( $list ) {
    if ( ! current_user_can( 'edit_posts' ) ) {
        unset( $list['library'] );
        unset( $list['gallery'] );
    }

    return $list;
}

/**
 * Get the registered post types
 *
 * @return array
 */
function wpuf_get_post_types( $args = [] ) {
    $defaults = [];

    $args = wp_parse_args( $args, $defaults );

    $post_types = get_post_types( $args );

    $ignore_post_types = [
        'attachment',
        'revision',
        'nav_menu_item',
    ];

    foreach ( $post_types as $key => $val ) {
        if ( in_array( $val, $ignore_post_types, true ) ) {
            unset( $post_types[ $key ] );
        }
    }

    return apply_filters( 'wpuf_get_post_types', $post_types );
}

/**
 * Edit post link for frontend
 *
 * @since 0.7
 *
 * @param string $url     url of the original post edit link
 * @param int    $post_id
 *
 * @return string url of the current edit post page
 */
function wpuf_override_admin_edit_link( $url, $post_id ) {
    if ( is_admin() ) {
        return $url;
    }

    $override = wpuf_get_option( 'override_editlink', 'wpuf_general', 'no' );

    if ( $override === 'yes' ) {
        $url = '';

        if ( 'yes' === wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) ) {
            $edit_page = (int) wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' );
            $url       = get_permalink( $edit_page );

            $url = wp_nonce_url( $url . '?pid=' . $post_id, 'wpuf_edit' );
        }
    }

    return apply_filters( 'wpuf_front_post_edit_link', $url );
}

/**
 * Displays checklist of a taxonomy
 *
 * @param int $post_id
 * @param array $selected_cats
 *
 * @since 0.8
 */
function wpuf_category_checklist( $post_id = 0, $selected_cats = false, $attr = [], $class = null ) {
    require_once ABSPATH . '/wp-admin/includes/template.php';

    $walker = new WPUF_Walker_Category_Checklist();

    $exclude_type = isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude';
    $exclude      = wpuf_get_field_settings_excludes( $attr, $exclude_type );

    $tax          = $attr['name'];
    $current_user = get_current_user_id();

    $args = [
        'taxonomy' => $tax,
    ];

    if ( $post_id ) {
        $args['selected_cats'] = wp_get_object_terms( $post_id, $tax, [ 'fields' => 'ids' ] );
    } elseif ( $selected_cats ) {
        $args['selected_cats'] = $selected_cats;
    } else {
        $args['selected_cats'] = [];
    }

    $args['show_inline'] = ! empty( $attr['show_inline'] ) ? $attr['show_inline'] : '';
    $args['class']       = $class;
    $args['required']    = ! empty( $attr['required'] ) ? $attr['required'] : 'no';
    $args['label']       = ! empty( $attr['label'] ) ? $attr['label'] : '';

    $tax_args = [
        'taxonomy'    => $tax,
        'hide_empty'  => false,
        $exclude['type'] => ( 'child_of' === $exclude_type ) ? $exclude['childs'] : $attr['exclude'],
        'orderby'     => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
        'order'       => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
    ];
    $tax_args = apply_filters( 'wpuf_taxonomy_checklist_args', $tax_args );

    $categories = (array) get_terms( $tax_args );

    echo wp_kses_post( '<ul class="wpuf-category-checklist">' );
    printf( '<input type="hidden" name="%s" value="0" />', esc_attr( $tax ) );
    echo wp_kses(
        call_user_func_array( [ &$walker, 'walk' ], [ $categories, 0, $args ] ), [
            'li'    => [
                'class'      => [],
                'id'         => [],
                'data-label' => [],
            ],
            'label' => [
                'class' => [],
            ],
            'input' => [
                'class'         => [],
                'type'          => [],
                'value'         => [],
                'name'          => [],
                'id'            => [],
                'checked'       => [],
                'data-required' => [],
                'data-type'     => [],
            ],
            'ul'    => [
                'class' => [],
            ],
        ]
    );
    echo wp_kses_post( '</ul>' );
}

/**
 * Get all the image sizes
 *
 * @return array image sizes
 */
function wpuf_get_image_sizes() {
    $image_sizes_orig   = get_intermediate_image_sizes();
    $image_sizes_orig[] = 'full';
    $image_sizes        = [];

    foreach ( $image_sizes_orig as $size ) {
        $image_sizes[ $size ] = $size;
    }

    return $image_sizes;
}

function wpuf_allowed_extensions() {
    $extesions = [
        'images' => [
            'ext' => 'jpg,jpeg,jfif,gif,png,bmp,webp',
            'label' => __( 'Images', 'wp-user-frontend' ),
        ],
        'audio'  => [
            'ext' => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi',
            'label' => __( 'Audio', 'wp-user-frontend' ),
        ],
        'video'  => [
            'ext' => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe',
            'label' => __( 'Videos', 'wp-user-frontend' ),
        ],
        'pdf'    => [
            'ext' => 'pdf',
            'label' => __( 'PDF', 'wp-user-frontend' ),
        ],
        'office' => [
            'ext' => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt',
            'label' => __( 'Office Documents', 'wp-user-frontend' ),
        ],
        'zip'    => [
            'ext' => 'zip,gz,gzip,rar,7z',
            'label' => __( 'Zip Archives', 'wp-user-frontend' ),
        ],
        'csv'    => [
            'ext' => 'csv',
            'label' => __( 'CSV', 'wp-user-frontend' ),
        ],
    ];

    return apply_filters( 'wpuf_allowed_extensions', $extesions );
}

/**
 * Adds notices on add post form if any
 *
 * @param string $text
 *
 * @return string
 */
function wpuf_addpost_notice( $text ) {
    $user = wp_get_current_user();

    if ( is_user_logged_in() ) {
        $lock = ( 'yes' === $user->wpuf_postlock ) ? 'yes' : 'no';

        if ( 'yes' === $lock ) {
            return $user->wpuf_lock_cause;
        }
    }

    return $text;
}

/**
 * Associate attachemnt to a post
 *
 * @since 2.0
 *
 * @param type $attachment_id
 * @param type $post_id
 */
function wpuf_associate_attachment( $attachment_id, $post_id ) {
    $args = [
        'ID'          => $attachment_id,
        'post_parent' => $post_id,
    ];

    wpuf_update_post( $args );
}

/**
 * Update post when hooked to save_post
 *
 * @since 2.5.4
 *
 * @param array args
 */
function wpuf_update_post( $args ) {
    if ( ! wp_is_post_revision( $args['ID'] ) ) {
        // unhook this function so it doesn't loop infinitely
        remove_action( 'save_post', [ WeDevs\Wpuf\Admin\Posting::init(), 'save_meta' ], 1 );

        // update the post, which calls save_post again
        wp_update_post( $args );

        // re-hook this function
        add_action( 'save_post', [ WeDevs\Wpuf\Admin\Posting::init(), 'save_meta' ], 1 );
    }
}

/**
 * Show custom fields in post content area
 *
 * @since 3.3.0 Introducing `render_field_data` to render field value
 *                   Rendering field values should be in field classes to follow
 *                   more OOP style.
 *
 * @todo Move the rendering snippets to respective field classes. The default case
 *       should be placed in the abstract class.
 *
 * @param string $content
 *
 * @return string
 */
function wpuf_show_custom_fields( $content ) {
    global $post;

    if ( ! is_a( $post, '\WP_Post' ) ) {
        return $content;
    }

    $show_custom = wpuf_get_option( 'cf_show_front', 'wpuf_frontend_posting' );

    if ( 'on' !== $show_custom ) {
        return $content;
    }

    $show_caption  = wpuf_get_option( 'image_caption', 'wpuf_frontend_posting' );
    $form_id       = Stores::submissions()->form_id( $post->ID );
    $form_settings = wpuf_get_form_settings( $form_id );

    if ( ! $form_id ) {
        return $content;
    }

    $html = '<ul class="wpuf_customs">';

    $form_vars = wpuf_get_form_fields( $form_id );
    $meta      = [];

    if ( $form_vars ) {
        foreach ( $form_vars as $attr ) {
            // get column field input fields
            if ( 'column_field' === $attr['input_type'] ) {
                $inner_fields = $attr['inner_fields'];
                foreach ( $inner_fields as $column_key => $column_fields ) {
                    if ( ! empty( $column_fields ) ) {
                        // ignore section break and HTML input type
                        foreach ( $column_fields as $column_field_key => $column_field ) {
                            // Skip if input type is not set
                            if ( ! isset( $column_field['input_type'] ) ) {
                                continue;
                            }

                            // Check if it's a map field
                            $is_map_field = in_array( $column_field['input_type'], [ 'map', 'google_map' ], true );

                            // Include field if it's a map or if show_in_post is enabled
                            if ( $is_map_field || ( isset( $column_field['show_in_post'] ) && wpuf_is_checkbox_or_toggle_on( $column_field['show_in_post'] ) ) ) {
                                $meta[] = $column_field;
                            }
                        }
                    }
                }
                continue;
            }

            // Skip if input type is not set
            if ( ! isset( $attr['input_type'] ) ) {
                continue;
            }

            // Check if it's a map field
            $is_map_field = in_array( $attr['input_type'], [ 'map', 'google_map' ], true );

            // Include field if it's a map or if show_in_post is enabled
            if ( $is_map_field || ( isset( $attr['show_in_post'] ) && wpuf_is_checkbox_or_toggle_on( $attr['show_in_post'] ) ) ) {
                $meta[] = $attr;
            }
        }

        if ( ! $meta ) {
            return $content;
        }

        foreach ( $meta as $attr ) {
            $wpuf_field = wpuf()->fields->get_field( $attr['template'] );

            if ( ! isset( $attr['name'] ) ) {
                $attr['name'] = $attr['input_type'];
            }

            $field_value    = get_post_meta( $post->ID, $attr['name'] );
            $hide_label     = isset( $attr['hide_field_label'] ) ? $attr['hide_field_label'] : 'no';

            $return_for_no_cond = 0;

            if ( isset( $attr['wpuf_cond']['condition_status'] ) && 'yes' === $attr['wpuf_cond']['condition_status'] ) {
                foreach ( $attr['wpuf_cond']['cond_field'] as $field_key => $cond_field_name ) {

                    //check if the conditional field is a taxonomy
                    if ( taxonomy_exists( $cond_field_name ) ) {
                        $post_terms       = wp_get_post_terms( $post->ID, $cond_field_name, true );
                        $cond_field_value = [];

                        if ( is_array( $post_terms ) ) {
                            foreach ( $post_terms as $term_key => $term_array ) {
                                $cond_field_value[] = $term_array->term_id;
                            }
                        }
                        $cond_field_value = isset( $post_terms[0] ) ? $post_terms[0]->term_id : '';
                    } else {
                        $cond_field_value = get_post_meta( $post->ID, $cond_field_name, true );
                    }

                    if ( isset( $attr['wpuf_cond']['cond_option'][ $field_key ] ) ) {
                        if ( is_array( $cond_field_value ) ) {
                            continue;
                        } elseif ( (string) $attr['wpuf_cond']['cond_option'][ $field_key ] !== (string) $cond_field_value ) {
                                $return_for_no_cond = 1;
                        } else {
                            $return_for_no_cond = 0;
                            break;
                        }
                    }
                }
            }

            if ( $return_for_no_cond === 1 ) {
                continue;
            }

            if ( ! count( $field_value ) ) {
                continue;
            }

            if ( 'hidden' === $attr['input_type'] ) {
                continue;
            }

            if ( ! empty( $wpuf_field ) && method_exists( $wpuf_field, 'render_field_data' ) ) {
                $html .= $wpuf_field->render_field_data( $field_value, $attr );
                continue;
            }

            switch ( $attr['input_type'] ) {
                case 'image_upload':
                case 'file_upload':
                    $image_html = '<li style="list-style-type:none;">';

                    if ( 'no' === $hide_label ) {
                        $image_html .= '<label>' . $attr['label'] . ':</label> ';
                    }

                    if ( $field_value ) {
                        if ( is_serialized( $field_value[0] ) ) {
                            $field_value = wpuf_safe_unserialize( $field_value[0] );
                        }

                        if ( is_array( $field_value[0] ) ) {
                            $field_value = $field_value[0];
                        }

                        foreach ( $field_value as $attachment_id ) {
                            if ( 'image_upload' === $attr['input_type'] ) {
                                $image_size = wpuf_get_option( 'insert_photo_size', 'wpuf_frontend_posting', 'thumbnail' );
                                $thumb      = wp_get_attachment_image( $attachment_id, $image_size );
                            } else {
                                $thumb = get_post_field( 'post_title', $attachment_id );
                            }

                            $full_size = wp_get_attachment_url( $attachment_id );
                            $path      = parse_url( $full_size, PHP_URL_PATH );
                            $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

                            if ( $thumb ) {
                                $playable                 = isset( $attr['playable_audio_video'] ) ? $attr['playable_audio_video'] : 'no';
                                $wpuf_allowed_extensions  = wpuf_allowed_extensions();
                                $allowed_audio_extensions = array_map(
                                    'strtolower',
                                    array_map( 'trim', explode( ',', $wpuf_allowed_extensions['audio']['ext'] ) )
                                );
                                $allowed_video_extensions = array_map(
                                    'strtolower',
                                    array_map( 'trim', explode( ',', $wpuf_allowed_extensions['video']['ext'] ) )
                                );
                                $allowed_extensions       = array_merge(
                                    $allowed_audio_extensions, $allowed_video_extensions
                                );

                                if ( 'yes' === $playable && in_array( $extension, $allowed_extensions, true ) ) {
                                    $is_video = in_array( $extension, $allowed_video_extensions, true );
                                    $is_audio = in_array( $extension, $allowed_audio_extensions, true );
                                    $preview_width  = isset( $attr['preview_width'] ) ? $attr['preview_width'] : '123';
                                    $preview_height = isset( $attr['preview_height'] ) ? $attr['preview_height'] : '456';

                                    $image_html .= '<div class="wpuf-embed-preview">';

                                    if ( $is_video ) {
                                        $image_html .= '[video src="' . $full_size . '" width="' . $preview_width . '" height="' . $preview_height . '"]';
                                    }

                                    if ( $is_audio ) {
                                        $image_html .= '[audio src="' . $full_size . '" width="' . $preview_width . '" height="' . $preview_height . '"]';
                                    }

                                    $image_html .= '</div>';
                                } else {
                                    $image_html .= sprintf( '<a href="%s">%s</a> ', $full_size, $thumb );
                                }

                                if ( 'on' === $show_caption ) {
                                    $post_detail = get_post( $attachment_id );

                                    if ( ! empty( $post_detail->post_title ) ) {
                                        $image_html .= '<br /><label>' . __( 'Title', 'wp-user-frontend' ) . ':</label> <span class="image_title">' . esc_html( $post_detail->post_title ) . '</span>';
                                    }

                                    if ( ! empty( $post_detail->post_excerpt ) ) {
                                        $image_html .= '<br /><label>' . __( 'Caption', 'wp-user-frontend' ) . ':</label> <span class="image_caption">' . esc_html( $post_detail->post_excerpt ) . '</span>';
                                    }

                                    if ( ! empty( $post_detail->post_content ) ) {
                                        $image_html .= '<br /><label>' . __( 'Description', 'wp-user-frontend' ) . ':</label> <span class="image_description">' . esc_html( $post_detail->post_content ) . '</span>';
                                    }
                                }
                            }
                        }
                    }

                    $html .= $image_html . '</li>';
                    break;

                case 'map':
                    ob_start();
                    wpuf_shortcode_map_post( $attr['name'], $post->ID );

                    wp_enqueue_script( 'wpuf-google-maps' );

                    if ( isset( $attr['directions'] ) && $attr['directions'] ) {
                        $location   = get_post_meta( $post->ID, $attr['name'], true );
                        $def_lat    = isset( $location['lat'] ) ? $location['lat'] : 40.7143528;
                        $def_long   = isset( $location['lng'] ) ? $location['lng'] : -74.0059731; ?>
                        <div>
                            <a class="btn btn-brand btn-sm" href="https://www.google.com/maps/dir/?api=1&amp;destination=<?php echo esc_attr( $def_lat ); ?>,<?php echo esc_attr( $def_long ); ?>" target="_blank" rel="nofollow external"><?php esc_html_e( 'Directions »', 'wp-user-frontend' ); ?></a>
                        </div>
                        <?php
                    }

                    $html .= ob_get_clean();
                    break;

                case 'address':
                    include_once WPUF_ROOT . '/includes/Data/countries.php';

                    $address_html = '';

                    if ( isset( $field_value[0] ) && is_array( $field_value[0] ) ) {
                        $country_state = new WeDevs\Wpuf\Data\Country_State();
                        $country_value = isset( $field_value[0]['country_select'] ) ? $field_value[0]['country_select'] : '';

                        // Get countries array from Country_State class as fallback
                        if ( empty( $countries ) ) {
                            $countries = $country_state->countries();
                        }

                        foreach ( $field_value[0] as $field_key => $value ) {
                            if ( 'country_select' === $field_key ) {
                                if ( isset( $countries[ $value ] ) ) {
                                    $value = $countries[ $value ];
                                }
                            } elseif ( 'state' === $field_key && ! empty( $country_value ) ) {
                                $state_resolved = false;

                                if ( wpuf()->is_pro() && file_exists( WPUF_PRO_INCLUDES . '/states.php' ) ) {
                                    $pro_states = include WPUF_PRO_INCLUDES . '/states.php';
                                    if ( ! empty( $pro_states[ $country_value ] ) && isset( $pro_states[ $country_value ][ $value ] ) ) {
                                        $value = $pro_states[ $country_value ][ $value ];
                                        $state_resolved = true;
                                    }
                                }

                                if ( ! $state_resolved ) {
                                    $state_name = $country_state->getStateName( $value, $country_value );
                                    if ( $state_name ) {
                                        $value = $state_name;
                                    }
                                }
                            }

                            if ( ! empty( $value ) ) {
                                $address_html .= '<li>';

                                if ( 'no' === $hide_label ) {
                                    $address_html .= '<label>' . esc_html( $attr['address'][ $field_key ]['label'] ) . ': </label> ';
                                }

                                $address_html .= ' ' . $value . '</li>';
                            }
                        }
                    }

                    $html .= $address_html;
                    break;

                case 'repeat':
                    $repeat_data = get_post_meta( $post->ID, $attr['name'], true );

                    if ( empty( $repeat_data ) ) {
                        break;
                    }

                    // Unserialize if needed
                    if ( is_serialized( $repeat_data ) ) {
                        $repeat_data = wpuf_safe_unserialize( $repeat_data );
                    }

                    if ( ! is_array( $repeat_data ) ) {
                        break;
                    }

                    $repeat_html = '<li>';

                    if ( 'no' === $hide_label ) {
                        $repeat_html .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                    }

                    $repeat_html .= '<ul class="wpuf-repeat-field-data">';

                    foreach ( $repeat_data as $repeat_entry ) {
                        $repeat_html .= '<li class="wpuf-repeat-entry">';
                        $repeat_html .= '<ul class="wpuf-repeat-entry-fields">';

                        foreach ( $attr['inner_fields'] as $inner_field ) {
                            $inner_field_name = $inner_field['name'];

                            if ( isset( $repeat_entry[ $inner_field_name ] ) ) {
                                $inner_field_value = $repeat_entry[ $inner_field_name ];

                                if ( ! empty( $inner_field_value ) ) {
                                    $repeat_html .= '<li>';

                                    if ( 'no' === $inner_field['hide_field_label'] ) {
                                        $repeat_html .= '<label>' . esc_html( $inner_field['label'] ) . ':</label> ';
                                    }

                                    // Handle different field types
                                    if ( 'checkbox' === $inner_field['input_type'] && is_array( $inner_field_value ) ) {
                                        // For checkbox fields, join multiple values
                                        $repeat_html .= '<span>' . make_clickable( strip_shortcodes( implode( ', ', $inner_field_value ) ) ) . '</span>';
                                    } elseif ( 'multiselect' === $inner_field['input_type'] && is_array( $inner_field_value ) ) {
                                        $repeat_html .= '<span>' . make_clickable( strip_shortcodes( implode( ', ', $inner_field_value ) ) ) . '</span>';
                                    } elseif ( 'radio' === $inner_field['input_type'] || 'select' === $inner_field['input_type'] ) {
                                        // For radio and select fields, display single value
                                        $repeat_html .= '<span>' . make_clickable( strip_shortcodes( $inner_field_value ) ) . '</span>';
                                    } else {
                                        // For text and other fields
                                        $repeat_html .= '<span>' . make_clickable( strip_shortcodes( $inner_field_value ) ) . '</span>';
                                    }

                                    $repeat_html .= '</li>';
                                }
                            }
                        }

                        $repeat_html .= '</ul>';
                        $repeat_html .= '</li>';
                    }

                    $repeat_html .= '</ul>';
                    $repeat_html .= '</li>';

                    $html .= $repeat_html;
                    break;

                case 'url':
                    $value = get_post_meta( $post->ID, $attr['name'], true );

                    if ( empty( $value ) ) {
                        break;
                    }

                    if ( 'embed' === $attr['template'] ) {
                        global $wp_embed;

                        $preview_width  = isset( $attr['preview_width'] ) ? $attr['preview_width'] : '123';
                        $preview_height = isset( $attr['preview_height'] ) ? $attr['preview_height'] : '456';
                        $shortcode      = '[embed width="' . $preview_width . '" height="' . $preview_height . '"]' . esc_url_raw( $value ) . '[/embed]';

                        $preview = '<li>';

                        if ( 'no' === $hide_label ) {
                            $preview .= sprintf( '<label>%s: </label>', esc_html( $attr['label'] ) );
                        }

                        $preview .= "<div class='wpuf-embed-preview'>";
                        $preview .= $wp_embed->run_shortcode( $shortcode );
                        $preview .= '</div>';
                        $preview .= '</li>';

                        $html .= $preview;
                        break;
                    }

                    $open_in = 'same' === $attr['open_window'] ? '' : '_blank';

                    $link = '<li>';

                    if ( 'no' === $hide_label ) {
                        $link .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                    }

                    $link .= sprintf( " <a href='%s' target = '%s'>%s</a></li>", esc_url( $value ), esc_attr( $open_in ), esc_html( $value ) );

                    $html .= $link;
                    break;

                case 'date':
                    $value = get_post_meta( $post->ID, $attr['name'], true );

                    $html .= '<li>';

                    if ( 'no' === $hide_label ) {
                        $html .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                    }

                    $html .= sprintf( ' %s</li>', make_clickable( strip_shortcodes( $value ) ) );
                    break;

                case 'country_list':
                    $value         = get_post_meta( $post->ID, $attr['name'], true );
                    $country_state = new WeDevs\Wpuf\Data\Country_State();
                    $countries     = $country_state->countries();

                    if ( isset( $countries[ $value ] ) ) {
                        $value = $countries[ $value ];
                    }

                    $html .= '<li>';

                    if ( 'no' === $hide_label ) {
                        $html .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                    }

                    $html .= sprintf( ' %s</li>', make_clickable( strip_shortcodes( $value ) ) );
                    break;

                default:
                    $value       = get_post_meta( $post->ID, $attr['name'] );
                    $filter_html = apply_filters( 'wpuf_custom_field_render', '', $value, $attr, $form_settings );
                    $separator   = ' | ';

                    if ( ! empty( $filter_html ) ) {
                        $html .= $filter_html;
                    } elseif ( is_serialized( $value[0] ) ) {
                        $new            = wpuf_safe_unserialize( $value[0] );
                        $modified_value = is_array( $new ) ? implode( $separator, $new ) : '';

                        if ( $modified_value ) {
                            $html .= '<li>';

                            if ( 'no' === $hide_label ) {
                                $html .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                            }

                            $html .= sprintf( ' %s</li>', make_clickable( strip_shortcodes( $modified_value ) ) );
                        }
                    } elseif ( ( 'checkbox' === $attr['input_type'] || 'multiselect' === $attr['input_type'] ) && is_array( $value[0] ) ) {
                        if ( ! empty( $value[0] ) ) {
                            $modified_value = implode( $separator, $value[0] );

                            if ( $modified_value ) {
                                $html .= '<li>';

                                if ( 'no' === $hide_label ) {
                                    $html .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                                }

                                $html .= sprintf( ' %s</li>', make_clickable( strip_shortcodes( $modified_value ) ) );
                            }
                        }
                    } else {
                        $new = implode( ', ', $value );

                        if ( $new ) {
                            $html .= '<li>';

                            if ( 'no' === $hide_label ) {
                                $html .= '<label>' . esc_html( $attr['label'] ) . ':</label>';
                            }

                            $html .= sprintf( ' %s</li>', make_clickable( strip_shortcodes( $new ) ) );
                        }
                    }

                    break;
            }
        }
    }

    $html .= '</ul>';

    return $content . $html;
}

/**
 * Get attachment ID from a URL
 *
 * @since 2.1.8
 * @see http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/ Original Implementation
 *
 * @global type $wpdb
 *
 * @param type $attachment_url
 *
 * @return type
 */
function wpuf_get_attachment_id_from_url( $attachment_url = '' ) {
    global $wpdb;

    $attachment_id = false;

    // If there is no url, return.
    if ( '' === $attachment_url ) {
        return;
    }

    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();

    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

        // If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = %s AND wposts.post_type = 'attachment'", $attachment_url ) );
    }

    return $attachment_id;
}

/**
 * The term names of a taxonomy matching what a visitor types (the tag
 * suggest of the classic text taxonomy input): the last comma-separated part
 * of the input, 2 characters at least.
 *
 * @since WPUF_SINCE
 *
 * @param string $taxonomy Taxonomy
 * @param string $search   What was typed (all of the input)
 * @param int[]  $term_ids Only these terms (the field's "child of" setting), empty for all
 *
 * @return string[]
 */
function wpuf_tag_suggestions( $taxonomy, $search, array $term_ids = [] ) {
    global $wpdb;

    $comma = _x( ',', 'tag delimiter', 'wp-user-frontend' );
    $s     = (string) $search;

    if ( ',' !== $comma ) {
        $s = str_replace( $comma, ',', $s );
    }

    if ( false !== strpos( $s, ',' ) ) {
        $s = explode( ',', $s );
        $s = $s[ count( $s ) - 1 ];
    }

    $s = trim( $s );

    // require 2 chars for matching
    if ( strlen( $s ) < 2 || ! get_taxonomy( $taxonomy ) ) {
        return [];
    }

    $term_id_list = array_values( array_filter( array_map( 'absint', $term_ids ) ) );

    if ( ! empty( $term_id_list ) ) {
        $placeholders = implode( ',', array_fill( 0, count( $term_id_list ), '%d' ) );
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders only
        return (array) $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.term_id IN ($placeholders) AND t.name LIKE (%s)", array_merge( [ $taxonomy ], $term_id_list, [ '%' . $wpdb->esc_like( $s ) . '%' ] ) ) );
    }

    return (array) $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . $wpdb->esc_like( $s ) . '%' ) );
}

/**
 * The URL the classic tag suggest (jQuery `suggest()`) reads: the REST route
 * `wpuf/v1/terms/suggest`, which answers plain text, one name per line.
 *
 * @since WPUF_SINCE
 *
 * @param string $taxonomy Taxonomy
 * @param int[]  $term_ids Only these terms, empty for all
 *
 * @return string
 */
function wpuf_tag_suggest_url( $taxonomy, array $term_ids = [] ) {
    $args = [ 'tax' => $taxonomy ];

    if ( $term_ids ) {
        $args['term_ids'] = implode( ',', array_map( 'absint', $term_ids ) );
    }

    return esc_url_raw( add_query_arg( $args, rest_url( 'wpuf/v1/terms/suggest' ) ) );
}

/**
 * Non logged in users tag autocomplete
 *
 * @since 2.1.9
 * @deprecated WPUF_SINCE The suggest reads the REST route `wpuf/v1/terms/suggest` (wpuf_tag_suggestions()).
 *
 * @global object $wpdb
 */
function wpuf_ajax_tag_search() {
    if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ), 'wpuf_ajax_tag_search' ) ) {
        wp_send_json_error( __( 'Permission denied', 'wp-user-frontend' ) );
    }

    $taxonomy = isset( $_GET['tax'] ) ? sanitize_text_field( wp_unslash( $_GET['tax'] ) ) : '';
    $term_ids = ! empty( $_GET['term_ids'] ) ? sanitize_text_field( wp_unslash( $_GET['term_ids'] ) ) : ''; // a comma list of ids, cast below

    if ( ! get_taxonomy( $taxonomy ) ) {
        wp_die( 0 );
    }

    $s = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

    echo esc_html( join( "\n", wpuf_tag_suggestions( $taxonomy, $s, explode( ',', (string) $term_ids ) ) ) );
    wp_die();
}

/**
 * Returns child category dropdown on ajax request
 */
function wpuf_get_child_cats() {
    $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['nonce'] ) ) : '';

    $parent_cat  = isset( $_POST['catID'] ) ? sanitize_text_field( wp_unslash( $_POST['catID'] ) ) : '';
    $field_attr = isset( $_POST['field_attr'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_attr'] ) ) : [];

    if ( ! wp_verify_nonce( $nonce, 'wpuf_nonce' ) ) {
        wp_send_json_error( __( 'Permission denied', 'wp-user-frontend' ) );
    }

    $allowed_tags = wp_kses_allowed_html( 'post' );

    $taxonomy = $field_attr['name'];

    $terms  = null;
    $result = '';

    if ( $parent_cat < 1 ) {
        die( wp_kses( $result, $allowed_tags ) );
    }

    $terms = get_categories( 'taxonomy=' . $taxonomy . '&child_of=' . $parent_cat . '&hide_empty=0' );

    if ( $terms ) {
        $field_attr['parent_cat'] = $parent_cat;

        if ( is_array( $terms ) ) {
            foreach ( $terms as $key => $term ) {
                $terms[ $key ] = (array) $term;
            }
        }

        $field_attr['form_id'] = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

        $result .= taxnomy_select( '', $field_attr );
    } else {
        die( '' );
    }
    die( wp_kses( $result, $allowed_tags ) );
}

function taxnomy_select( $terms, $attr ) {
    $selected           = $terms ? $terms : '';
    $taxonomy           = $attr['name'];
    $class              = ' wpuf_' . $attr['name'] . '_' . $attr['form_id'];
    $exclude_type       = isset( $attr['exclude_type'] ) ? $attr['exclude_type'] : 'exclude';
    $exclude            = isset( $attr['exclude'] ) ? $attr['exclude'] : '';

    $dataset = sprintf(
        'data-required="%s" data-type="select" data-form-id="%d"',
        $attr['required'],
        $attr['form_id']
    );

    if ( 'child_of' === $exclude_type && ! empty( $exclude ) ) {
        $exclude = $exclude[0];
    }

    $tax_args = [
        'show_option_none' => __( '&mdash; Select &mdash;', 'wp-user-frontend' ),
        'hierarchical'     => 1,
        'hide_empty'       => 0,
        'orderby'          => isset( $attr['orderby'] ) ? $attr['orderby'] : 'name',
        'order'            => isset( $attr['order'] ) ? $attr['order'] : 'ASC',
        'name'             => $taxonomy . '[]',
        'taxonomy'         => $taxonomy,
        'echo'             => 0,
        'title_li'         => '',
        'class'            => 'cat-ajax ' . $taxonomy . $class,
        $exclude_type      => $exclude,
        'selected'         => $selected,
        'depth'            => 1,
        'child_of'         => isset( $attr['parent_cat'] ) ? $attr['parent_cat'] : '',
    ];

    $tax_args = apply_filters( 'wpuf_taxonomy_checklist_args', $tax_args );

    $select = wp_dropdown_categories( $tax_args );

    echo str_replace( '<select', '<select ' . $dataset, $select ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
    $attr = [
        'required'     => $attr['required'],
        'name'         => $attr['name'],
        'exclude_type' => $attr['exclude_type'],
        'exclude'      => isset( $attr['exclude'] ) ? $attr['exclude'] : '',
        'orderby'      => $attr['orderby'],
        'order'        => $attr['order'],
        //'last_term_id' => isset( $attr['parent_cat'] ) ? $attr['parent_cat'] : '',
        //'term_id'      => $selected
    ];
    $attr = apply_filters( 'wpuf_taxonomy_checklist_args', $attr );
    ?>
    <span data-taxonomy=<?php echo wp_json_encode( $attr ); ?>></span>
    <?php
}

/**
 * Check save draft post status based on subscription
 *
 * @since 2.5.2
 *
 * @param array $form_settings
 *
 * @return string $post_status
 */
function wpuf_get_draft_post_status( $form_settings ) {

    $post_status                 = 'draft';
    $current_user                = wpuf_get_user();
    $charging_enabled            = $current_user->subscription()->current_pack_id();
    $user_wpuf_subscription_pack = Stores::user_packs()->read( get_current_user_id() );

    if ( $charging_enabled && ! isset( $_POST['post_id'] ) ) {
        if ( ! empty( $user_wpuf_subscription_pack ) ) {
            if ( $current_user->subscription()->expired() ) {
                $post_status = 'pending';
            }
        }
    }

    return $post_status;
}

/**
 * Add all terms as allowed terms
 *
 * @since 2.7.0
 *
 * @return void
 */
function wpuf_set_all_terms_as_allowed() {
    if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
        $subscriptions  = wpuf()->subscription->get_subscriptions();
        $allowed_term   = [];

        foreach ( $subscriptions as $pack ) {
            if ( ! metadata_exists( 'post', $pack->ID, '_sub_allowed_term_ids' ) ) {
                $cts = get_taxonomies( [ '_builtin' => true ], 'objects' );
                ?>
                <?php
                foreach ( $cts as $ct ) {
                    if ( is_taxonomy_hierarchical( $ct->name ) ) {
                        $tax_terms = get_terms(
                            [
                                'taxonomy'   => $ct->name,
                                'hide_empty' => false,
                            ]
                        );

                        foreach ( $tax_terms as $tax_term ) {
                            $allowed_term[] = $tax_term->term_id;
                        }
                    }
                }

                $cts = get_taxonomies( [ '_builtin' => false ], 'objects' );
                ?>
                <?php
                foreach ( $cts as $ct ) {
                    if ( is_taxonomy_hierarchical( $ct->name ) ) {
                        $tax_terms = get_terms(
                            [
                                'taxonomy'   => $ct->name,
                                'hide_empty' => false,
                            ]
                        );

                        foreach ( $tax_terms as $tax_term ) {
                            $allowed_term[] = $tax_term->term_id;
                        }
                    }
                }

                update_post_meta( $pack->ID, '_sub_allowed_term_ids', $allowed_term );
            }
        }
    }
}

/**
 * Post submitted by form
 *
 * @since 2.8
 *
 * @param int $form_id
 *
 * @return int[]|WP_Post[]
 */
function wpuf_posts_submitted_by( $form_id ) {
    $settings = wpuf_get_form_settings( $form_id );

    $args = [
        'meta_key'         => '_wpuf_form_id',
        'meta_value'       => $form_id,
        'post_type'        => $settings['post_type'],
        'post_status'      => 'publish',
    ];

    return get_posts( $args );
}

/**
 * Count post submitted by form
 *
 * @since 2.8
 *
 * @param int $form_id
 *
 * @return int
 */
function wpuf_form_posts_count( $form_id ) {
    return count( wpuf_posts_submitted_by( $form_id ) );
}

/**
 * Get terms of related taxonomy
 *
 * @since  2.8.5
 *
 * @param string $taxonomy
 *
 * @return array
 */
function wpuf_get_terms( $taxonomy = 'category' ) {
    $items = [];

    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ]
    );

    foreach ( $terms as $key => $term ) {
        $items[ $term->term_id ] = $term->name;
    }

    return $items;
}

/**
 * Save frontend post revision
 *
 * @param int   $post_id
 * @param array $form_settings
 *
 * @return void
 */
function wpuf_frontend_post_revision( $post_id, $form_settings ) {
    $post      = get_post( $post_id );
    $post_type = ! empty( $form_settings['post_type'] ) ? $form_settings['post_type'] : 'post';

    if ( post_type_supports( $post_type, 'revisions' ) ) {
        $revisions = wp_get_post_revisions(
            $post_id, [
                'order' => 'ASC',
                'posts_per_page' => 1,
            ]
        );
        $revision  = current( $revisions );

        _wp_upgrade_revisions_of_post( $post, wp_get_post_revisions( $post_id ) );
    }
}

/**
 * Check if current post is editable
 *
 * This is a backward-compatible wrapper around wpuf_user_can_edit_post()
 * for use in templates. Returns boolean instead of WP_Error.
 *
 * @param WP_Post|int $post Post object or post ID
 *
 * @since 3.5.27
 * @since 4.2.9 Refactored to use wpuf_user_can_edit_post(). Now accepts post ID or post object.
 *
 * @return bool True if editable, false otherwise
 */
function wpuf_is_post_editable( $post ) {
    // Handle WordPress post object
    if ( ! $post instanceof WP_Post ) {
        return false;
    }

    if ( is_numeric( $post ) ) {
        $post_id = absint( $post );

        if ( ! $post_id ) {
            return false;
        }

        $can_edit = wpuf_user_can_edit_post( $post_id );

        return ! is_wp_error( $can_edit );
    }

    $can_edit = wpuf_user_can_edit_post( $post->ID );

    // Return true if not an error, false otherwise
    return ! is_wp_error( $can_edit );
}

/**
 * Get an array of available image sizes with height and weight
 *
 * @since 3.5.27
 *
 * @param $size     string      size of the image. thumbnail, medium, large etc.
 *
 * @return array                single image size returned if parameter size is passed
 *                              full array of all the sizes will return otherwise
 *
 * @deprecated WPUF_SINCE Not used by WP User Frontend any more; kept as public API.
 */
function wpuf_get_image_sizes_array( $size = '' ) {
    _deprecated_function( __FUNCTION__, 'WPUF_SINCE' );

    $additional_image_sizes   = wp_get_additional_image_sizes();
    $intermediate_image_sizes = get_intermediate_image_sizes();
    $sizes = [];

    // Create the full array with sizes and crop info
    foreach ( $intermediate_image_sizes as $_size ) {
        if ( in_array( $_size, [ 'thumbnail', 'medium', 'large', 'medium_large' ], true ) ) {
            $sizes[ $_size ]['width']  = get_option( $_size . '_size_w' );
            $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
            $sizes[ $_size ]['crop']   = (bool) get_option( $_size . '_crop' );
        } elseif ( isset( $additional_image_sizes[ $_size ] ) ) {
            $sizes[ $_size ] = [
                'width'  => $additional_image_sizes[ $_size ]['width'],
                'height' => $additional_image_sizes[ $_size ]['height'],
                'crop'   => $additional_image_sizes[ $_size ]['crop'],
            ];
        }
    }

    // Get only 1 size if found
    if ( $size ) {
        if ( isset( $sizes[ $size ] ) ) {
            return $sizes[ $size ];
        } else {
            return false;
        }
    }
    return $sizes;
}

/**
 * Get taxonomy object types (post types the taxonomy is associated with)
 *
 * This works for all taxonomies - built-in or custom.
 *
 * @since 4.2.6
 *
 * @param string $taxonomy_name The taxonomy name to check
 * @return array Array of post type names associated with the taxonomy
 */
if ( ! function_exists( 'wpuf_get_taxonomy_post_types' ) ) {
    function wpuf_get_taxonomy_post_types( $taxonomy_name ) {
        // If taxonomy doesn't exist, return empty array
        if ( ! taxonomy_exists( $taxonomy_name ) ) {
            return [];
        }

        // Get the taxonomy object
        $taxonomy = get_taxonomy( $taxonomy_name );

        if ( ! $taxonomy ) {
            return [];
        }

        // WordPress stores associated post types in object_type property
        if ( isset( $taxonomy->object_type ) && is_array( $taxonomy->object_type ) ) {
            return $taxonomy->object_type;
        }

        return [];
    }
}

/**
 * Get list of taxonomies that should be available in free version
 *
 * This includes built-in taxonomies and custom taxonomies associated with 'post' or 'page' post types.
 *
 * @since 4.2.6
 *
 * @return array Array of taxonomy names that are available in free version
 */
if ( ! function_exists( 'wpuf_get_free_taxonomies' ) ) {
    function wpuf_get_free_taxonomies() {
        // Built-in taxonomies that are always available
        $free_taxonomies = [ 'category', 'post_tag' ];

        // Allow filtering to add more free taxonomies
        //$free_taxonomies = apply_filters( 'wpuf_free_taxonomies', $free_taxonomies );

        // Get all registered taxonomies (built-in and custom)
        $all_taxonomies = get_taxonomies( [], 'names' );

        foreach ( $all_taxonomies as $taxonomy_name ) {
            // Skip if already in free list
            if ( in_array( $taxonomy_name, $free_taxonomies, true ) ) {
                continue;
            }

            // Get the post types this taxonomy is associated with
            $post_types = wpuf_get_taxonomy_post_types( $taxonomy_name );

            // Only allow taxonomies that are associated with 'post' or 'page' in free version
            if ( ! empty( $post_types ) ) {
                $allowed_post_types = [ 'post', 'page' ];
                $has_allowed_type = false;

                foreach ( $post_types as $post_type ) {
                    if ( in_array( $post_type, $allowed_post_types, true ) ) {
                        $has_allowed_type = true;
                        break;
                    }
                }

                // If this taxonomy is for post or page, add it to free list
                if ( $has_allowed_type ) {
                    $free_taxonomies[] = $taxonomy_name;
                }
            }
        }

        return $free_taxonomies;
    }
}

/**
 * Check if current user can edit a specific post
 *
 * Validates user authorization to edit a post by checking:
 * - User is logged in
 * - WPUF global and user-specific edit settings
 * - Post-specific lock settings
 * - User is post author with edit_post capability
 * - User has edit_others_posts capability for posts they don't own
 *
 * @since SINCE_WPUF
 *
 * @param int  $post_id         Post ID to check
 * @param bool $check_settings  Whether to check WPUF settings (default true). Set false for AJAX/admin operations
 *
 * @return true|WP_Error True if user can edit, WP_Error on failure
 */
function wpuf_user_can_edit_post( $post_id, $check_settings = true ) {

    $post_id = absint( $post_id );

    if ( ! $post_id ) {
        return new WP_Error(
            'wpuf_invalid_post',
            __( 'Invalid post ID.', 'wp-user-frontend' )
        );
    }

    // Get the post
    $post = get_post( $post_id );

    if ( ! $post || is_wp_error( $post ) ) {
        return new WP_Error(
            'wpuf_post_not_found',
            __( 'Post not found.', 'wp-user-frontend' )
        );
    }

    // Get current user and post author
    $current_user_id = get_current_user_id();
    $post_author_id  = (int) $post->post_author;

    // Early return: user must be logged in
    if ( $current_user_id <= 0 ) {
        return new WP_Error(
            'wpuf_user_not_logged_in',
            __( 'You must be logged in to edit posts.', 'wp-user-frontend' )
        );
    }

    if ( $current_user_id !== $post_author_id && ! current_user_can( 'edit_post', $post_id ) ) {
        return new WP_Error(
            'wpuf_unauthorized_edit',
            __( 'You are not authorized to edit this post.', 'wp-user-frontend' )
        );
    }

    // Check WPUF-specific settings (only for non-admin users)
    if ( $check_settings && ! current_user_can( 'edit_others_posts' ) ) {

        // Check if post editing is globally enabled
        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) !== 'yes' ) {
            return new WP_Error(
                'wpuf_post_edit_disabled',
                __( 'Post editing is disabled.', 'wp-user-frontend' )
            );
        }

        // Check user-level post lock
        if ( wpuf_get_user()->edit_post_locked() ) {
            $reason = wpuf_get_user()->edit_post_lock_reason();

            return new WP_Error(
                'wpuf_user_edit_locked',
                $reason ?: __( 'Your post edit access has been locked by an administrator.', 'wp-user-frontend' )
            );
        }

        // Check post-specific lock (admin can lock individual posts)
        $post_lock = Stores::submissions()->lock( $post_id );

        if ( 'yes' === $post_lock ) {
            return new WP_Error(
                'wpuf_post_locked',
                apply_filters(
                    'wpuf_edit_post_lock_user_notice',
                    __( 'Your edit access for this post has been locked by an administrator.', 'wp-user-frontend' )
                )
            );
        }

        // Check time-based lock
        $lock_time = Stores::submissions()->lock_time( $post_id );

        if ( ! empty( $lock_time ) && $lock_time < time() ) {
            return new WP_Error(
                'wpuf_post_lock_expired',
                apply_filters(
                    'wpuf_edit_post_lock_expire_notice',
                    __( 'Your allocated time for editing this post has expired.', 'wp-user-frontend' )
                )
            );
        }

        // Check post status restrictions
        $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );
        $disable_publish_edit = wpuf_get_option( 'disable_publish_edit', 'wpuf_dashboard', 'off' );

        if ( 'pending' === $post->post_status && 'on' === $disable_pending_edit ) {
            return new WP_Error(
                'wpuf_pending_edit_disabled',
                __( 'You can\'t edit a post while in pending mode.', 'wp-user-frontend' )
            );
        }

        if ( 'publish' === $post->post_status && 'off' !== $disable_publish_edit ) {
            return new WP_Error(
                'wpuf_publish_edit_disabled',
                __( 'You\'re not allowed to edit this post.', 'wp-user-frontend' )
            );
        }

        // Check subscription expiration
        $current_user      = wpuf_get_user();
        $user_subscription = new \WeDevs\Wpuf\User_Subscription( $current_user );
        $sub_id            = $current_user->subscription()->current_pack_id();

        if ( $sub_id ) {
            $subs_expired = $user_subscription->expired();

            if ( $subs_expired ) {
                return new WP_Error(
                    'wpuf_subscription_expired',
                    __( 'Your subscription has expired. Please renew to edit posts.', 'wp-user-frontend' )
                );
            }
        }

        // Check payment status for draft/pending posts
        $payment_status = Stores::submissions()->payment_status( $post_id );

        if ( ( 'draft' === $post->post_status || 'pending' === $post->post_status ) && ! empty( $payment_status ) && 'completed' !== $payment_status ) {
            return new WP_Error(
                'wpuf_payment_incomplete',
                __( 'You cannot edit this post until payment is completed.', 'wp-user-frontend' )
            );
        }
    }

    return true;
}
