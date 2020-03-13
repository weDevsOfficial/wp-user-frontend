<?php

/**
 * Manage Import Export
 *
 * @since 2.2
 */
class WPUF_Admin_Tools {
    /**
     * List of All the post forms
     *
     * @return void
     */
    public function list_forms() {
        if ( isset( $_POST['export'] ) ) {

            $export_content = isset( $_POST['export_content'] ) ? sanitize_text_field( wp_unslash( $_POST['export_content'] ) ) : '';
            $formlist       = isset( $_POST['formlist'] ) ? sanitize_text_field( wp_unslash( $_POST['formlist'] ) ) : '';
            $this->export_data( $export_content, $formlist );

            $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

            if ( isset( $nonce ) && ! wp_verify_nonce( $nonce, 'wpuf-export-form' ) ) {
                return ;
            }
        }

        $args = [
            'post_type'      => 'wpuf_forms',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];
        $forms = get_posts( $args );

        if ( $forms ) {
            ?>
            <div class="postbox" style="margin-top: 15px;">
                <h3 style="padding:10px 15px"><?php esc_html_e( 'Form Export', 'wp-user-frontend' ); ?></h3>
                <div class="inside">
                    <div class="main">
                        <form action="" method="post" style="margin-top: 20px;">
                            <p>
                                <input class="export_type" type="radio" name="export_content" value="all" id="wpuf-all_export" checked>
                                <label for="wpuf-all_export"><?php esc_html_e( 'All', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <input class="export_type" type="radio" name="export_content" value="selected" id="wpuf-selected_export">
                                <label for="wpuf-selected_export"><?php esc_html_e( 'Select individual', 'wp-user-frontend' ); ?></label></p>
                            <p>
                                <select class="formlist" name="formlist[]" multiple="multiple">
                                    <?php foreach ( $forms as $form ) { ?>
                                        <option value="<?php echo esc_attr( $form->ID ); ?>"><?php echo esc_attr( $form->post_title ); ?></option>
                                    <?php } ?>
                                </select>
                            </p>

                            <?php wp_nonce_field( 'wpuf-export-form' ); ?>
                            <input type="submit" class="button button-primary" name="export" value="<?php esc_html_e( 'Export', 'wp-user-frontend' ); ?>">
                        </form>
                    </div>
                </div>
            </div>

            <?php
        } else {
            sprintf( '<p>%s</p>', __( 'Sorry you have no form to export', 'wp-user-frontend' ) );
        }
    }

    /**
     * List of All Registration forms
     *
     * @return void
     */
    public function list_regis_forms() {
        if ( isset( $_POST['export_regis_form'] ) ) {

            $nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ): '';

            $export_regis_content = isset( $_POST['export_regis_content'] ) ? sanitize_text_field( wp_unslash( $_POST['export_regis_content'] ) ) : '';
            $formlist = isset( $_POST['formlist'] ) ? sanitize_text_field( wp_unslash( $_POST['formlist'] ) ) : '';

            $this->export_regis_data( $export_regis_content, $formlist );


            if ( isset( $nonce) && ! wp_verify_nonce( $nonce, 'wpuf-export-regs-form' ) ) {
                return ;
            }
        }

        $args = [
            'post_type'      => 'wpuf_profile',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $forms = get_posts( $args );

        if ( $forms ) {
            ?>
            <div class="postbox">
                <h3 style="padding:10px 15px"><?php esc_html_e( 'Registration Form Export', 'wp-user-frontend' ); ?></h3>
                <div class="inside">
                    <div class="main">

                        <form action="" method="post" style="margin-top: 20px;">

                            <p>
                                <input class="export_type" type="radio" name="export_regis_content" value="all" id="wpuf-all_regis_export" checked>
                                <label for="wpuf-all_regis_export"><?php esc_html_e( 'All', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <input class="export_type" type="radio" name="export_regis_content" value="selected" id="wpuf-selected_regis_export">
                                <label for="wpuf-selected_regis_export"><?php esc_html_e( 'Select individual', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <select class="formlist" name="formlist[]" multiple="multiple">
                                    <?php foreach ( $forms as $form ) { ?>
                                        <option value="<?php echo esc_attr( $form->ID ); ?>"><?php echo esc_attr( $form->post_title ); ?></option>";
                                    <?php } ?>
                                </select>
                            </p>

                            <?php wp_nonce_field( 'wpuf-export-regs-form' ); ?>

                            <input type="submit" class="button button-primary" name="export_regis_form" value="<?php esc_html_e( 'Export', 'wp-user-frontend' ); ?>">
                        </form>
                    </div>
                </div>
            </div>
            <?php
        } else {
            sprintf( '<p>%s</p>', __( 'Sorry you have no form to export', 'wp-user-frontend' ) );
        }
    }

    /**
     * Import functionality
     */
    public function import_data() {
        if ( isset( $_FILES['import'] ) && check_admin_referer( 'wpuf-import' ) ) {
            $import_files = array_map( 'sanitize_text_field', wp_unslash( $_FILES['import'] ) );

            if ( $import_files['error'] > 0 ) {
                printf( '<div class="error"><p>%s</p></div>', esc_html( __( 'Somthing went wrong. Please choose a file again', 'wp-user-frontend' ) ) );
            } else {
                $file_name = $import_files['name'];
                $file_ext  = pathinfo( $file_name, PATHINFO_EXTENSION );
                $file_size = $import_files['size'];

                if ( ( $file_ext == 'json' ) && ( $file_size < 500000 ) ) {
                    $data = static::import_json_file( $import_files['tmp_name'] );

                    if ( $data ) {
                        printf( '<div class="updated"><p>%s</p></div>', esc_html( __( 'Import successful. Have fun!', 'wp-user-frontend' ) ) );
                    }
                } else {
                    printf( '<div class="error"><p>%s</p></div>',esc_html( __( 'Invalid file or file size too big.', 'wp-user-frontend' ) ) );
                }
            }
        } ?>

        <h3><?php esc_html_e( 'Import forms', 'wp-user-frontend' ); ?></h3>

        <p><?php esc_html_e( 'Click Browse button and choose a json file that you backup before.', 'wp-user-frontend' ); ?></p>
        <p><?php echo wp_kses( __( 'Press <strong>Import</strong> button, we will do the rest for you.', 'wp-user-frontend' ), array(
            'strong' => array()
            ) ); ?></p>

        <form action="" method="post" enctype='multipart/form-data' style="margin-top: 20px;">
            <?php wp_nonce_field( 'wpuf-import' ); ?>
            <input type='file' name='import' />
            <input type="submit" class="button button-primary" name="import_data" value="<?php esc_html_e( 'Import', 'wp-user-frontend' ); ?>">
        </form>
        <?php
    }

    /**
     * Import json file into database
     *
     * @param array $file
     *
     * @return bool
     */
    public static function import_json_file( $file ) {
        $encode_data = file_get_contents( $file );
        $options     = json_decode( $encode_data, true );

        foreach ( $options as $key => $value ) {
            $generate_post = [
                'post_title'     => $value['post_data']['post_title'],
                'post_status'    => $value['post_data']['post_status'],
                'post_type'      => $value['post_data']['post_type'],
                'ping_status'    => $value['post_data']['ping_status'],
                'comment_status' => $value['post_data']['comment_status'],
            ];

            $post_id = wp_insert_post( $generate_post, true );

            if ( $post_id && !is_wp_error( $post_id ) ) {
                foreach ( $value['meta_data']['fields'] as $order => $field ) {
                    wpuf_insert_form_field( $post_id, $field, false, $order );
                }

                update_post_meta( $post_id, 'wpuf_form_settings', $value['meta_data']['settings'] );
                update_post_meta( $post_id, 'notifications', $value['meta_data']['notifications'] );
            }
        }

        return true;
    }

    /**
     * Export Registration form
     *
     * @param string $export_type
     * @param int    $post_ids
     */
    public function export_regis_data( $export_type, $post_ids ) {
        if ( $export_type == 'all' && check_admin_referer( 'wpuf-export-regs-form' ) ) {
            static::export_to_json( 'wpuf_profile' );
        } elseif ( $export_type == 'selected' && check_admin_referer( 'wpuf-export-regs-form' ) ) {
            $formlist = isset( $_POST['formlist'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['formlist'] ) ) : [];

            if ( $formlist == null ) {
                printf( '<div class="error"><p>%s</p></div>', esc_html( __( 'Please select some form for exporting', 'wp-user-frontend' ) ) );
            } else {
                static::export_to_json( 'wpuf_profile', $post_ids );
            }
        }
    }

    /**
     * Export normal form data
     *
     * @param string $export_type
     * @param int    $post_ids
     */
    public function export_data( $export_type, $post_ids ) {
        $formlist = isset( $_POST['formlist'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['formlist'] ) ) : [];

        if ( $export_type == 'all' && check_admin_referer( 'wpuf-export-form' ) ) {
            static::export_to_json( 'wpuf_forms' );
        } elseif ( $export_type == 'selected' && check_admin_referer( 'wpuf-export-form' ) ) {
            if ( $formlist == null ) {
                printf( '<div class="error"><p>%s</p></div>',esc_html( __( 'Please select some form for exporting', 'wp-user-frontend' ) ) );
            } else {
                static::export_to_json( 'wpuf_forms', $post_ids );
            }
        }
    }

    /**
     * Export into json file
     *
     * @param string $post_type
     * @param array  $post_ids
     */
    public static function export_to_json( $post_type, $post_ids = [ ] ) {
        $formatted_data = [];
        $ids            = [];
        $blogname       = strtolower( str_replace( ' ', '-', get_option( 'blogname' ) ) );
        $date           = date( 'Y-m-d' );
        $json_name      = $blogname . '-wpuf-' . $post_type . '-' . $date; // Namming the filename will be generated.

        if ( !empty( $post_ids ) ) {
            foreach ( $post_ids as $key => $value ) {
                array_push( $ids, $value );
            }
        }

        $args = [
            'post_status' => 'publish',
            'post_type'   => $post_type,
            'post__in'    => ( !empty( $ids ) ) ? $ids : '',
        ];

        $query = new WP_Query( $args );

        foreach ( $query->posts as $post ) {
            $postdata = get_object_vars( $post );
            unset( $postdata['ID'] );

            $data = [
                'post_data' => $postdata,
                'meta_data' => [
                    'fields'        => wpuf_get_form_fields( $post->ID ),
                    'settings'      => wpuf_get_form_settings( $post->ID ),
                    'notifications' => wpuf_get_form_notifications( $post->ID ),
                ],
            ];

            array_push( $formatted_data, $data );
        }

        $json_file = json_encode( $formatted_data ); // Encode data into json data

        ob_clean();

        echo $json_file; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

        header( 'Content-Type: text/json; charset=' . get_option( 'blog_charset' ) );
        header( "Content-Disposition: attachment; filename=$json_name.json" );

        exit();
    }

    /**
     * Formetted meta key value
     *
     * @param array $array
     *
     * @return array
     */
    public function formetted_meta_key_value( $array ) {
        $result = [ ];

        foreach ( $array as $key => $val ) {
            $result[$key] = $val[0];
        }

        return $result;
    }

    public function tool_page() {
        $msg                   = isset( $_GET['msg'] ) ? sanitize_text_field( wp_unslash( $_GET['msg'] ) ) : '';
        $text                  = '';
        $confirmation_message  = __( 'Are you Sure?', 'wp-user-frontend' );
        switch ( $msg ) {
            case 'del_forms':
                $text = __( 'All forms has been deleted', 'wp-user-frontend' );
                break;

            case 'settings_cleared':
                $text = __( 'Settings has been cleared!', 'wp-user-frontend' );
                break;

            case 'del_trans':
                $text = __( 'All transactions has been deleted!', 'wp-user-frontend' );
                break;
        }

        if ( $text ) {
            ?>
            <div class="updated">
                <p>
                    <?php echo esc_html( $text ); ?>
                </p>
            </div>

        <?php } ?>

        <div class="metabox-holder">
            <div class="postbox">
                <h3><?php esc_html_e( 'Page Installation', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <p><?php esc_html_e( 'Clicking this button will create required pages for the plugin. Note: It\'ll not delete/replace existing pages.', 'wp-user-frontend' ); ?></p>
                    <a class="button button-primary" href="<?php echo esc_attr( add_query_arg( [ 'install_wpuf_pages' => true ] ) ); ?>"><?php esc_html_e( 'Install WPUF Pages', 'wp-user-frontend' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php esc_html_e( 'Reset Settings', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <strong><p><?php esc_html_e( 'Caution: This tool will delete all the plugin settings of WP User Frontend Pro', 'wp-user-frontend' ); ?></p></strong>
                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_action' => 'clear_settings' ], 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ) ); ?>" onclick="return confirm('Are you sure?');"><?php esc_html_e( 'Reset Settings', 'wp-user-frontend' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php esc_html_e( 'Delete Forms', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <strong><p><?php esc_html_e( 'Caution: This tool will delete all the post and registration/profile forms.', 'wp-user-frontend' ); ?></p></strong>

                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_action' => 'del_post_forms' ], 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>');"><?php esc_html_e( 'Delete Post Forms', 'wp-user-frontend' ); ?></a>
                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_action' => 'del_pro_forms' ], 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>');"><?php esc_html_e( 'Delete Registration Forms', 'wp-user-frontend' ); ?></a>
                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_action' => 'del_subs' ], 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>');"><?php esc_html_e( 'Delete Subscriptions', 'wp-user-frontend' ); ?></a>
                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_action' => 'del_coupon' ], 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>');"><?php esc_html_e( 'Delete Coupons', 'wp-user-frontend' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php esc_html_e( 'Transactions', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <p><?php esc_html_e( 'This tool will delete all the transactions from the transaction table.', 'wp-user-frontend' ); ?></p>

                    <a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'wpuf_action' => 'clear_transaction' ], 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ) ); ?>" onclick="return confirm('<?php echo esc_attr( $confirmation_message ); ?>');"><?php esc_html_e( 'Delete Transactions', 'wp-user-frontend' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
}
