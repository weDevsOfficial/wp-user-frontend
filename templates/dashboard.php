<div class="wpuf-dashboard-container">

    <h2 class="page-head">
        <span class="colour"><?php printf( esc_attr( __( "%s's Dashboard", 'wp-user-frontend' ) ), esc_html( $userdata->display_name )); ?></span>
    </h2>

    <?php if ( wpuf_get_option( 'show_post_count', 'wpuf_dashboard', 'on' ) == 'on' ) { ?>
        <?php if ( !empty( $post_type_obj ) ) { ?>
            <div class="post_count">
                <?php
                $labels = [];

                foreach ( $post_type_obj as $key => $post_type_name ) {
                    if ( isset( $post_type_name->label ) ) {
                        $labels[] = $post_type_name->label;
                    }
                }

                printf(
                    wp_kses_post( __( 'You have created <span>%d</span> (%s)', 'wp-user-frontend' ) ),
                    wp_kses_post( $dashboard_query->found_posts ),
                    wp_kses_post( implode( ', ', $labels ) )
                );
                ?>
            </div>
        <?php } ?>
    <?php } ?>

    <?php
    if ( !empty( $post_type_obj ) ) {
        do_action( 'wpuf_dashboard_top', $userdata->ID, $post_type_obj );
    }

    $meta_label = [];
    $meta_name  = [];
    $meta_id    = [];
    $meta_key   = [];

    if ( !empty( $meta ) ) {
        $arr =  explode( ',', $meta );

        foreach ( $arr as $mkey ) {
            $meta_key[] = trim( $mkey );
        }
    }
    ?>
    <?php if ( $dashboard_query->have_posts() ) {
        $args = [
            'post_status' => 'publish',
            'post_type'   => [ 'wpuf_forms' ],
        ];

        $query = new WP_Query( $args );

        foreach ( $query->posts as $post ) {
            $postdata = get_object_vars( $post );
            unset( $postdata['ID'] );

            $data = [
                'meta_data' => [
                    'fields'    => wpuf_get_form_fields( $post->ID ),
                ],
            ];

            foreach ( $data['meta_data']['fields'] as $fields ) {
                foreach ( $fields as $key => $field_value ) {
                    if ( $key == 'is_meta' && $field_value == 'yes' ) {
                        $meta_label[]= $fields['label'];
                        $meta_name[] = $fields['name'];
                        $meta_id[]   = $fields['id'];
                    }
                }
            }
        }

        wp_reset_postdata();

        $len               = count( $meta_key );
        $len_label         = count( $meta_label );
        $len_id            = count( $meta_id );
        $featured_img      = wpuf_get_option( 'show_ft_image', 'wpuf_dashboard' );
        $featured_img_size = wpuf_get_option( 'ft_img_size', 'wpuf_dashboard' );
        $enable_payment    = wpuf_get_option( 'enable_payment', 'wpuf_payment' );
        $current_user      = wpuf_get_user();
        $user_subscription = new WPUF_User_Subscription( $current_user );
        $user_sub          = $user_subscription->current_pack();
        $sub_id            = $current_user->subscription()->current_pack_id();

        if ( $sub_id ) {
            $subs_expired = $user_subscription->expired();
        } else {
            $subs_expired = false;
        }
        ?>

        <div class="items-table-container">
            <table class="items-table <?php echo wp_kses_post( implode( ' ', $post_type ) ); ?>" cellpadding="0" cellspacing="0">
                <thead>
                    <tr class="items-list-header">
                        <?php
                        if ( ( ( 'on' == $featured_img || 'on' == $featured_image ) || ( 'off' == $featured_img && 'on' == $featured_image ) || ( 'on' == $featured_img && 'default' == $featured_image ) ) && !( 'on' == $featured_img && 'off' == $featured_image ) ) {
                            echo wp_kses_post( '<th>' . __( 'Featured Image', 'wp-user-frontend' ) . '</th>' );
                        } ?>
                        <th><?php esc_html_e( 'Title', 'wp-user-frontend' ); ?></th>

                        <?php
                        if ( 'on' == $category ) {
                            echo wp_kses_post( '<th>' . __( 'Category', 'wp-user-frontend' ) . '</th>' );
                        } ?>

                        <?php
                    // populate meta column headers

                        if ( $meta != 'off' ) {
                            for ( $i = 0; $i < $len_label; $i++ ) {
                                for ( $j = 0; $j < $len; $j++ ) {
                                    if ( $meta_key[$j] == $meta_name[$i] ) {
                                        echo wp_kses_post( '<th>' );
                                        echo wp_kses_post( __( $meta_label[$i], 'wp-user-frontend' ) );
                                        echo wp_kses_post( '</th>' );
                                    }
                                }
                            }
                        } ?>

                        <?php
                        if ( 'on' == $excerpt ) {
                            echo wp_kses_post( '<th>' . __( 'Excerpt', 'wp-user-frontend' ) . '</th>' );
                        } ?>

                        <th><?php esc_html_e( 'Status', 'wp-user-frontend' ); ?></th>

                        <?php do_action( 'wpuf_dashboard_head_col', $args ); ?>

                        <?php if ( 'on' == $enable_payment && 'off' != $payment_column ) { ?>
                            <th><?php esc_html_e( 'Payment', 'wp-user-frontend' ); ?></th>
                        <?php } ?>

                        <th><?php esc_html_e( 'Options', 'wp-user-frontend' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $post;

                    while ( $dashboard_query->have_posts() ) {
                        $dashboard_query->the_post();
                        $show_link        = !in_array( $post->post_status, ['draft', 'future', 'pending'] );
                        $payment_status   = get_post_meta( $post->ID, '_wpuf_payment_status', true ); ?>
                        <tr>
                            <?php if ( ( ( 'on' == $featured_img || 'on' == $featured_image ) || ( 'off' == $featured_img && 'on' == $featured_image ) || ( 'on' == $featured_img && 'default' == $featured_image ) ) && !( 'on' == $featured_img && 'off' == $featured_image ) ) { ?>
                                <td data-label="<?php esc_attr_e( 'Featured Image: ', 'wp-user-frontend' ); ?>">
                                    <?php
                                    echo $show_link ? wp_kses_post( '<a href="' . get_permalink( $post->ID ) . '">' ) : '';

                                    if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( $featured_img_size );
                                    } else {
                                        printf( '<img src="%1$s" class="attachment-thumbnail wp-post-image" alt="%2$s" title="%2$s" />', esc_attr( apply_filters( 'wpuf_no_image', plugins_url( '/assets/images/no-image.png', __DIR__ ) ) ), esc_html( __( 'No Image', 'wp-user-frontend' ) ) );
                                    }

                                    echo $show_link ? '</a>' : '';
                                    ?>
                                    <span class="post-edit-icon">
                                    &#x25BE;
                                </span>
                                </td>
                            <?php } ?>
                            <td data-label="<?php esc_attr_e( 'Title: ', 'wp-user-frontend' ); ?>" class="<?php echo 'on' === $featured_img ? 'data-column' : '' ; ?>">
                                <?php if ( !$show_link ) { ?>

                                    <?php the_title(); ?>

                                <?php } else { ?>

                                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wp-user-frontend' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

                                <?php } ?>
                                <?php if ( 'on' !== $featured_img ){?>
                                    <span class="post-edit-icon">
                                    &#x25BE;
                                </span>
                                <?php }?>
                            </td>

                            <?php if ( 'on' == $category ) { ?>
                                <td data-label="<?php esc_attr_e( 'Category: ', 'wp-user-frontend' ); ?>">
                                    <?php

                                    $taxonomies     = get_object_taxonomies( get_post_type(), 'objects' );
                                    $post_tax_terms = [];

                                    foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
                                        if ( $taxonomy->hierarchical == 1 ) {
                                            $terms = get_the_terms( $post->ID, $taxonomy_slug );

                                            if ( !empty( $terms ) ) {
                                                foreach ( $terms as $term ) {
                                                    $post_tax_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
                                                        esc_url_raw( get_term_link( $term->slug, $taxonomy_slug ) ),
                                                        esc_html( $term->name )
                                                     );
                                                }
                                            }
                                        }
                                    }
                                    echo wp_kses_post( apply_filters( 'wpuf_dashboard_post_taxonomy', implode( ',', $post_tax_terms ) ) );

                                    ?>
                                </td>
                            <?php }

                            // populate meta column fields ?>
                            <?php if ( $meta != 'off' ) {
                                for ( $i = 0; $i < $len_label; $i++ ) {
                                    for ( $j = 0; $j < $len; $j++ ) {
                                        if ( $meta_key[$j] == $meta_name[$i] ) {
                                            echo wp_kses_post( '<td>' );
                                            $m_val = get_post_meta( $post->ID, $meta_name[$i], true );
                                            echo esc_html( $m_val );
                                            echo wp_kses_post( '</td>' );
                                        }
                                    }
                                }
                            } ?>

                            <?php if ( 'on' == $excerpt ) { ?>
                                <td data-label="<?php esc_attr_e( 'Excerpt: ', 'wp-user-frontend' ); ?>">
                                    <?php the_excerpt(); ?>
                                </td>
                            <?php } ?>
                            <td data-label="<?php esc_attr_e( 'Status: ', 'wp-user-frontend' ); ?>" class="data-column">
                                <?php wpuf_show_post_status( $post->post_status ); ?>
                            </td>

                            <?php do_action( 'wpuf_dashboard_row_col', $args, $post ); ?>

                            <?php if ( 'on' == $enable_payment && 'off' != $payment_column ) { ?>
                                <td data-label="<?php esc_attr_e( 'Payment: ', 'wp-user-frontend' ); ?>" class="data-column">
                                    <?php if ( empty( $payment_status ) ) { ?>
                                        <?php esc_html_e( 'Not Applicable', 'wp-user-frontend' ); ?>
                                        <?php } elseif ( $payment_status != 'completed' ) { ?>
                                            <a href="<?php echo esc_url( trailingslashit( get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) ) ) ); ?>?action=wpuf_pay&type=post&post_id=<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Pay Now', 'wp-user-frontend' ); ?></a>
                                            <?php } elseif ( $payment_status == 'completed' ) { ?>
                                                <?php esc_html_e( 'Completed', 'wp-user-frontend' ); ?>
                                            <?php } ?>
                                        </td>
                                    <?php } ?>

                            <td data-label="<?php esc_attr_e( 'Options: ', 'wp-user-frontend' ); ?>" class="data-column">
                                <?php
                                if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                    $disable_pending_edit = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );
                                    $edit_page            = (int) wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' );
                                    $url                  = add_query_arg( ['pid' => $post->ID], get_permalink( $edit_page ) );

                                    $show_edit = true;

                                    if ( $post->post_status == 'pending' && $disable_pending_edit == 'on' ) {
                                        $show_edit  = false;
                                    }

                                    if ( ( $post->post_status == 'draft' || $post->post_status == 'pending' ) && ( !empty( $payment_status ) && $payment_status != 'completed' ) ) {
                                        $show_edit  = false;
                                    }

                                    if ( $subs_expired ) {
                                        $show_edit  = false;
                                    }

                                    if ( $show_edit ) {
                                        ?>
                                        <a class="wpuf-posts-options wpuf-posts-edit" href="<?php echo esc_url( wp_nonce_url( $url, 'wpuf_edit' ) ); ?>"><svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12.2175 0.232507L14.0736 2.08857C14.3836 2.39858 14.3836 2.90335 14.0736 3.21336L12.6189 4.66802L9.63808 1.68716L11.0927 0.232507C11.4027 -0.0775022 11.9075 -0.0775022 12.2175 0.232507ZM0 14.3061V11.3253L8.7955 2.52974L11.7764 5.5106L2.98086 14.3061H0Z" fill="#B7C4E7"/></svg></a>
                                        <?php
                                    }
                                } ?>

                                <?php
                                if ( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                    $del_url = add_query_arg( ['action' => 'del', 'pid' => $post->ID] );
                                    $message = __( 'Are you sure to delete?', 'wp-user-frontend' ); ?>
                                    <a class="wpuf-posts-options wpuf-posts-delete" style="color: red;" href="<?php echo esc_url_raw( wp_nonce_url( $del_url, 'wpuf_del' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $message ); ?>');"><svg width="15" height="15" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.8082 1.9102H7.98776C7.73445 1.9102 7.49152 1.80958 7.3124 1.63046C7.13328 1.45134 7.03266 1.20841 7.03266 0.955102C7.03266 0.701793 7.13328 0.458859 7.3124 0.279743C7.49152 0.100626 7.73445 0 7.98776 0H11.8082C12.0615 0 12.3044 0.100626 12.4835 0.279743C12.6626 0.458859 12.7633 0.701793 12.7633 0.955102C12.7633 1.20841 12.6626 1.45134 12.4835 1.63046C12.3044 1.80958 12.0615 1.9102 11.8082 1.9102ZM1.30203 2.86529H18.4939C18.7472 2.86529 18.9901 2.96591 19.1692 3.14503C19.3483 3.32415 19.449 3.56708 19.449 3.82039C19.449 4.0737 19.3483 4.31663 19.1692 4.49575C18.9901 4.67486 18.7472 4.77549 18.4939 4.77549H16.5837V16.2367C16.5835 16.9966 16.2815 17.7253 15.7442 18.2626C15.2069 18.7999 14.4782 19.1018 13.7184 19.102H6.07754C5.31768 19.1018 4.58901 18.7998 4.05171 18.2625C3.51441 17.7252 3.21246 16.9966 3.21223 16.2367V4.77549H1.30203C1.04872 4.77549 0.805783 4.67486 0.626667 4.49575C0.44755 4.31663 0.346924 4.0737 0.346924 3.82039C0.346924 3.56708 0.44755 3.32415 0.626667 3.14503C0.805783 2.96591 1.04872 2.86529 1.30203 2.86529ZM8.6631 14.0468C8.84222 13.8677 8.94284 13.6247 8.94284 13.3714V8.5959C8.94284 8.34259 8.84222 8.09966 8.6631 7.92054C8.48398 7.74142 8.24105 7.6408 7.98774 7.6408C7.73443 7.6408 7.4915 7.74142 7.31238 7.92054C7.13327 8.09966 7.03264 8.34259 7.03264 8.5959V13.3714C7.03264 13.6247 7.13327 13.8677 7.31238 14.0468C7.4915 14.2259 7.73443 14.3265 7.98774 14.3265C8.24105 14.3265 8.48398 14.2259 8.6631 14.0468ZM12.4835 14.0468C12.6626 13.8677 12.7633 13.6247 12.7633 13.3714V8.5959C12.7633 8.34259 12.6626 8.09966 12.4835 7.92054C12.3044 7.74142 12.0615 7.6408 11.8081 7.6408C11.5548 7.6408 11.3119 7.74142 11.1328 7.92054C10.9537 8.09966 10.853 8.34259 10.853 8.5959V13.3714C10.853 13.6247 10.9537 13.8677 11.1328 14.0468C11.3119 14.2259 11.5548 14.3265 11.8081 14.3265C12.0615 14.3265 12.3044 14.2259 12.4835 14.0468Z" fill="#B7C4E7"/></svg></a>
                                    <?php
                                } ?>
                            </td>
                                </tr>
                                <?php
                            }

                            wp_reset_postdata(); ?>

                        </tbody>
                    </table>
                </div>

                <div class="wpuf-pagination">
                    <?php
                    $pagination = paginate_links( [
                        'base'      => add_query_arg( 'pagenum', '%#%' ),
                        'format'    => '',
                        'prev_text' => __( '&laquo;', 'wp-user-frontend' ),
                        'next_text' => __( '&raquo;', 'wp-user-frontend' ),
                        'total'     => $dashboard_query->max_num_pages,
                        'current'   => $pagenum,
                        'add_args'  => false,
                    ] );

                    if ( $pagination ) {
                        echo wp_kses( $pagination, [
                            'span' => [
                                'aria-current' => [],
                                'class' => [],
                            ],
                            'a' => [
                                'href' => [],
                                'class' => [],
                            ]
                        ] );
                    } ?>
                </div>
                <?php
            } else {
                if ( !empty( $post_type_obj ) && !empty( $labels ) ) {
                    printf( '<div class="wpuf-message">' . wp_kses_post( __( 'No %s found', 'wp-user-frontend' ) ) . '</div>', esc_html( implode( ', ', $labels ) ) );
                    do_action( 'wpuf_dashboard_nopost', $userdata->ID, $post_type_obj );
                }
            }

            if ( !empty( $post_type_obj ) ) {
                do_action( 'wpuf_dashboard_bottom', $userdata->ID, $post_type_obj );
            } ?>

        </div>
