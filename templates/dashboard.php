<div class="wpuf-dashboard-container">

    <h2 class="page-head">
        <span class="colour"><?php printf( __( "%s's Dashboard", 'wp-user-frontend' ), $userdata->display_name ); ?></span>
    </h2>

    <?php if ( wpuf_get_option( 'show_post_count', 'wpuf_dashboard', 'on' ) == 'on' ) { ?>
        <?php if ( !empty( $post_type_obj )): ?>
            <div class="post_count">
                <?php
                $labels = array();
                foreach ($post_type_obj as $key => $post_type_name) {
                    if ( isset( $post_type_name->label ) ) {
                        $labels[] = $post_type_name->label;
                    }
                }
                printf( __( 'You have created <span>%d</span> (%s)', 'wp-user-frontend' ), $dashboard_query->found_posts, implode( ', ', $labels ) );
                ?>
            </div>
        <?php endif ?>
    <?php } ?>

    <?php if ( !empty( $post_type_obj ) ) do_action( 'wpuf_dashboard_top', $userdata->ID, $post_type_obj ) ?>
    <?php

    $meta_label = array();
    $meta_name  = array();
    $meta_id    = array();
    $meta_key   = array();
    if ( !empty( $meta ) ) {
        $arr =  explode(',', $meta);
        foreach ($arr as $mkey) {
            $meta_key[] = trim($mkey);
        }
    }
    ?>
    <?php if ( $dashboard_query->have_posts() ) {

        $args = array(
            'post_status' => 'publish',
            'post_type'   => 'wpuf_forms'
        );

        $query = new WP_Query( $args );

        foreach ( $query->posts as $post ) {
            $postdata = get_object_vars( $post );
            unset( $postdata['ID'] );

            $data = array(
                'meta_data' => array(
                    'fields'    => wpuf_get_form_fields( $post->ID )
                )
            );

            foreach ($data['meta_data']['fields'] as $fields) {
                foreach ($fields as $key => $field_value) {
                    if ( $key == 'is_meta' && $field_value == 'yes' ) {
                        $meta_label[]= $fields['label'];
                        $meta_name[] = $fields['name'];
                        $meta_id[]   = $fields['id'];
                    }
                };
            };
        }

        wp_reset_postdata();

        $len       = count( $meta_key );
        $len_label = count( $meta_label );
        $len_id    = count( $meta_id );
        $featured_img       = wpuf_get_option( 'show_ft_image', 'wpuf_dashboard' );
        $featured_img_size  = wpuf_get_option( 'ft_img_size', 'wpuf_dashboard' );
        $enable_payment     = wpuf_get_option( 'enable_payment', 'wpuf_payment' );
        $current_user       = wpuf_get_user();
        ?>

        <div class="items-table-container">
            <table class="items-table <?php echo implode( ' ', $post_type ); ?>" cellpadding="0" cellspacing="0">
                <thead>
                    <tr class="items-list-header">
                        <?php
                        if ((( 'on' == $featured_img || 'on' == $featured_image ) || ( 'off' == $featured_img && 'on' == $featured_image ) || ( 'on' == $featured_img && 'default' == $featured_image )) && !( 'on' == $featured_img && 'off' == $featured_image )) {
                            echo '<th>' . __( 'Featured Image', 'wp-user-frontend' ) . '</th>';
                        }
                        ?>
                        <th><?php _e( 'Title', 'wp-user-frontend' ); ?></th>

                        <?php
                        if ( 'on' == $category ) {
                            echo '<th>' . __( 'Category', 'wp-user-frontend' ) . '</th>';
                        }
                        ?>

                        <?php
                    // populate meta column headers

                        if ( $meta != 'off' ) {
                            for ( $i = 0; $i < $len_label; $i++ ) {
                                for ( $j = 0; $j < $len; $j++ ) {
                                    if ( $meta_key[$j] == $meta_name[$i] ) {
                                        echo '<th>';
                                        echo __( $meta_label[$i], 'wp-user-frontend' );
                                        echo '</th>';
                                    }
                                }
                            }
                        }
                        ?>

                        <?php
                        if ( 'on' == $excerpt ) {
                            echo '<th>' . __( 'Excerpt', 'wp-user-frontend' ) . '</th>';
                        }
                        ?>

                        <th><?php _e( 'Status', 'wp-user-frontend' ); ?></th>

                        <?php do_action( 'wpuf_dashboard_head_col', $args ) ?>

                        <?php if( 'on' == $enable_payment && 'off' != $payment_column ): ?>
                            <th><?php _e( 'Payment', 'wp-user-frontend' ); ?></th>
                        <?php endif; ?>

                        <th><?php _e( 'Options', 'wp-user-frontend' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $post;

                    while ( $dashboard_query->have_posts() ) {
                        $dashboard_query->the_post();
                        $show_link        = !in_array( $post->post_status, array('draft', 'future', 'pending') );
                        $payment_status   = get_post_meta( $post->ID, '_wpuf_payment_status', true );
                        ?>
                        <tr>
                            <?php if ((( 'on' == $featured_img || 'on' == $featured_image ) || ( 'off' == $featured_img && 'on' == $featured_image ) || ( 'on' == $featured_img && 'default' == $featured_image )) && !( 'on' == $featured_img && 'off' == $featured_image )) { ?>
                                <td>
                                    <?php
                                    echo $show_link ? '<a href="' . get_permalink( $post->ID ) . '">' : '';

                                    if ( has_post_thumbnail() ) {
                                        the_post_thumbnail( $featured_img_size );
                                    } else {
                                        printf( '<img src="%1$s" class="attachment-thumbnail wp-post-image" alt="%2$s" title="%2$s" />', apply_filters( 'wpuf_no_image', plugins_url( '/assets/images/no-image.png', dirname( __FILE__ ) ) ), __( 'No Image', 'wp-user-frontend' ) );
                                    }

                                    echo $show_link ? '</a>' : '';
                                    ?>
                                </td>
                            <?php } ?>
                            <td>
                                <?php if ( !$show_link ) { ?>

                                    <?php the_title(); ?>

                                <?php } else { ?>

                                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'wp-user-frontend' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>

                                <?php } ?>
                            </td>

                            <?php if ( 'on' == $category ) { ?>
                                <td>
                                    <?php

                                    $taxonomies = get_object_taxonomies( get_post_type(), 'objects' );
                                    $post_tax_terms = array();

                                    foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
                                        if ( $taxonomy->hierarchical == 1 ) {
                                            $terms = get_the_terms( $post->ID, $taxonomy_slug );

                                            if ( ! empty( $terms ) ) {
                                                foreach ( $terms as $term ) {
                                                    $post_tax_terms[] = sprintf( '<a href="%1$s">%2$s</a>',
                                                        esc_url( get_term_link( $term->slug, $taxonomy_slug ) ),
                                                        esc_html( $term->name )
                                                    );
                                                }
                                            }
                                        }
                                    }
                                    echo apply_filters( 'wpuf_dashboard_post_taxonomy', implode( ',', $post_tax_terms ) );

                                    ?>
                                </td>
                            <?php }

                        //populate meta column fields
                            ?>
                            <?php if ( $meta != 'off' ) {
                                for ( $i = 0; $i < $len_label; $i++ ) {
                                    for ( $j = 0; $j < $len; $j++ ) {
                                        if ( $meta_key[$j] == $meta_name[$i] ) {
                                            echo '<td>';
                                            $m_val = get_post_meta( $post->ID, $meta_name[$i], true );
                                            echo $m_val;
                                            echo '</td>';
                                        }
                                    }
                                }
                            } ?>

                            <?php if ( 'on' == $excerpt ) { ?>
                                <td>
                                    <?php the_excerpt(); ?>
                                </td>
                            <?php } ?>
                            <td>
                                <?php wpuf_show_post_status( $post->post_status ) ?>
                            </td>

                            <?php do_action( 'wpuf_dashboard_row_col', $args, $post ) ?>

                            <?php if( 'on' == $enable_payment && 'off' != $payment_column ): ?>
                                <td>
                                    <?php if( empty( $payment_status ) ) : ?>
                                        <?php _e( 'Not Applicable', 'wp-user-frontend' ); ?>
                                        <?php elseif( $payment_status != 'completed' ) : ?>
                                            <a href="<?php echo trailingslashit( get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) ) ); ?>?action=wpuf_pay&type=post&post_id=<?php echo $post->ID; ?>"><?php _e( 'Pay Now', 'wp-user-frontend' ); ?></a>
                                            <?php elseif( $payment_status == 'completed' ) : ?>
                                                <?php _e( 'Completed', 'wp-user-frontend' ); ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>

                                    <td>
                                        <?php
                                        if ( wpuf_get_option( 'enable_post_edit', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                            $disable_pending_edit   = wpuf_get_option( 'disable_pending_edit', 'wpuf_dashboard', 'on' );
                                            $edit_page              = (int) wpuf_get_option( 'edit_page_id', 'wpuf_frontend_posting' );
                                            $post_id                = $post->ID;
                                            $url                    = add_query_arg( array('pid' => $post->ID), get_permalink( $edit_page ) );

                                            $edit_page_url = apply_filters( 'wpuf_edit_post_link', $url );


                                            $show_edit = true;

                                            if ( $post->post_status == 'pending' && $disable_pending_edit == 'on' ) {
                                                $show_edit  = false;
                                            }

                                            if ( ($post->post_status =='draft' || $post->post_status =='pending') && ( !empty( $payment_status ) && $payment_status != 'completed' ) ) {
                                                $show_edit  = false;
                                            }

                                            if ( $show_edit ) {
                                                ?>
                                                <a href="<?php echo wp_nonce_url( $edit_page_url, 'wpuf_edit' ); ?>"><?php _e( 'Edit', 'wp-user-frontend' ); ?></a>
                                                <?php
                                            }
                                        }
                                        ?>

                                        <?php
                                        if ( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ) == 'yes' ) {
                                            $del_url = add_query_arg( array('action' => 'del', 'pid' => $post->ID) );
                                            $message = __( 'Are you sure to delete?', 'wp-user-frontend' );
                                            ?>
                                            <a href="<?php echo wp_nonce_url( $del_url, 'wpuf_del' ) ?>" onclick="return confirm('<?php echo $message ?>');"><span style="color: red;"><?php _e( 'Delete', 'wp-user-frontend' ); ?></span></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            }

                            wp_reset_postdata();
                            ?>

                        </tbody>
                    </table>
                </div>

                <div class="wpuf-pagination">
                    <?php
                    $pagination = paginate_links( array(
                        'base'      => add_query_arg( 'pagenum', '%#%' ),
                        'format'    => '',
                        'prev_text' => __( '&laquo;', 'wp-user-frontend' ),
                        'next_text' => __( '&raquo;', 'wp-user-frontend' ),
                        'total'     => $dashboard_query->max_num_pages,
                        'current'   => $pagenum,
                        'add_args'  => false
                    ) );

                    if ( $pagination ) {
                        echo $pagination;
                    }
                    ?>
                </div>
                <?php
            } else {
                if ( !empty( $post_type_obj ) && !empty( $labels ) ) {
                    printf( '<div class="wpuf-message">' . __( 'No %s found', 'wp-user-frontend' ) . '</div>', implode( ', ', $labels ) );
                    do_action( 'wpuf_dashboard_nopost', $userdata->ID, $post_type_obj );
                }
            }
            if ( !empty( $post_type_obj ) ) do_action( 'wpuf_dashboard_bottom', $userdata->ID, $post_type_obj ); ?>

        </div>
