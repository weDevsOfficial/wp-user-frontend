<?php

/**
 * Manage Import Export
 *
 * @since 2.2
 *
 * @package WP User Frontend
 */
class WPUF_Admin_Tools {

    /**
     * List of All the post forms
     *
     * @return void
     */
    function list_forms() {

        if ( isset( $_POST['export'] ) ) {
            $this->export_data( $_POST['export_content'], $_POST['formlist'] );
        }

        $args = array(
            'post_type'      => 'wpuf_forms',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        );
        $forms = get_posts( $args );

        if ( $forms ) {
            ?>
            <div class="postbox" style="margin-top: 15px;">
                <h3 style="padding:10px 15px"><?php _e( 'Form Export', 'wp-user-frontend' ); ?></h3>
                <div class="inside">
                    <div class="main">
                        <form action="" method="post" style="margin-top: 20px;">
                            <p>
                                <input class="export_type" type="radio" name="export_content" value="all" id="wpuf-all_export" checked>
                                <label for="wpuf-all_export"><?php _e( 'All', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <input class="export_type" type="radio" name="export_content" value="selected" id="wpuf-selected_export">
                                <label for="wpuf-selected_export"><?php _e( 'Select individual', 'wp-user-frontend' ); ?></label></p>
                            <p>
                                <select class="formlist" name="formlist[]" multiple="multiple">
                                    <?php foreach ( $forms as $form ) { ?>
                                        <option value="<?php echo esc_attr( $form->ID ) ?>"><?php echo esc_attr( $form->post_title ); ?></option>
                                    <?php } ?>
                                </select>
                            </p>

                            <?php wp_nonce_field( 'wpuf-export-form' ); ?>
                            <input type="submit" class="button button-primary" name="export" value="<?php _e( 'Export', 'wp-user-frontend' ) ?>">
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
    function list_regis_forms() {

        if ( isset( $_POST['export_regis_form'] ) ) {
            $this->export_regis_data( $_POST['export_regis_content'], $_POST['formlist'] );
        }

        $args = array(
            'post_type'      => 'wpuf_profile',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        );

        $forms = get_posts( $args );
        if ( $forms ) {
            ?>
            <div class="postbox">
                <h3 style="padding:10px 15px"><?php _e( 'Registration Form Export', 'wp-user-frontend' ); ?></h3>
                <div class="inside">
                    <div class="main">

                        <form action="" method="post" style="margin-top: 20px;">

                            <p>
                                <input class="export_type" type="radio" name="export_regis_content" value="all" id="wpuf-all_regis_export" checked>
                                <label for="wpuf-all_regis_export"><?php _e( 'All', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <input class="export_type" type="radio" name="export_regis_content" value="selected" id="wpuf-selected_regis_export">
                                <label for="wpuf-selected_regis_export"><?php _e( 'Select individual', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <select class="formlist" name="formlist[]" multiple="multiple">
                                    <?php foreach ( $forms as $form ) { ?>
                                        <option value="<?php echo esc_attr( $form->ID ); ?>"><?php echo esc_attr( $form->post_title ); ?></option>";
                                    <?php } ?>
                                </select>
                            </p>

                            <?php wp_nonce_field( 'wpuf-export-regs-form' ); ?>

                            <input type="submit" class="button button-primary" name="export_regis_form" value="<?php _e( 'Export', 'wp-user-frontend' ) ?>">
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
    function import_data() {

        if ( isset( $_FILES['import'] ) && check_admin_referer( 'wpuf-import' ) ) {

            if ( $_FILES['import']['error'] > 0 ) {

                printf( '<div class="error"><p>%s</p></div>', __( 'Somthing went wrong. Please choose a file again', 'wp-user-frontend' ) );
            } else {

                $file_name = $_FILES['import']['name'];
                $file_ext  = pathinfo( $file_name, PATHINFO_EXTENSION );
                $file_size = $_FILES['import']['size'];

                if ( ($file_ext == "json") && ($file_size < 500000) ) {

                    $data = static::import_json_file( $_FILES['import']['tmp_name'] );

                    if ( $data ) {
                        printf( '<div class="updated"><p>%s</p></div>', __( 'Import successful. Have fun!', 'wp-user-frontend' ) );
                    }
                } else {
                    printf( '<div class="error"><p>%s</p></div>', __( 'Invalid file or file size too big.', 'wp-user-frontend' ) );
                }
            }
        }
        ?>

        <h3><?php _e( 'Import forms', 'wp-user-frontend' ); ?></h3>

        <p><?php _e( 'Click Browse button and choose a json file that you backup before.', 'wp-user-frontend' ); ?></p>
        <p><?php _e( 'Press <strong>Import</strong> button, we will do the rest for you.', 'wp-user-frontend' ); ?></p>

        <form action="" method="post" enctype='multipart/form-data' style="margin-top: 20px;">
            <?php wp_nonce_field( 'wpuf-import' ); ?>
            <input type='file' name='import' />
            <input type="submit" class="button button-primary" name="import_data" value="<?php _e( 'Import', 'wp-user-frontend' ); ?>">
        </form>
        <?php
    }

    /**
     * Import json file into database
     * @param  array $file
     * @return boolean
     */
    public static function import_json_file( $file ) {

        $encode_data = file_get_contents( $file );
        $options     = json_decode( $encode_data, true );

        foreach ( $options as $key => $value ) {

            $generate_post = array(
                'post_title'     => $value['post_data']['post_title'],
                'post_status'    => $value['post_data']['post_status'],
                'post_type'      => $value['post_data']['post_type'],
                'ping_status'    => $value['post_data']['ping_status'],
                'comment_status' => $value['post_data']['comment_status']
            );

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
     * @param  string $export_type
     * @param  integer $post_ids
     */
    function export_regis_data( $export_type, $post_ids ) {

        if ( $export_type == 'all' && check_admin_referer( 'wpuf-export-regs-form' ) ) {

            static::export_to_json( 'wpuf_profile' );

        } elseif ( $export_type == 'selected' && check_admin_referer( 'wpuf-export-regs-form' ) ) {

            if ( $_POST['formlist'] == NULL ) {
                printf( '<div class="error"><p>%s</p></div>', __( 'Please select some form for exporting', 'wp-user-frontend' ) );
            } else {
                static::export_to_json( 'wpuf_profile', $post_ids );
            }
        }
    }

    /**
     * Export normal form data
     * @param  string $export_type
     * @param  integer $post_ids
     */
    function export_data( $export_type, $post_ids ) {
        if ( $export_type == 'all' && check_admin_referer( 'wpuf-export-form' ) ) {

            static::export_to_json( 'wpuf_forms' );

        } elseif ( $export_type == 'selected' && check_admin_referer( 'wpuf-export-form' ) ) {

            if ( $_POST['formlist'] == NULL ) {
                printf( '<div class="error"><p>%s</p></div>', __( 'Please select some form for exporting', 'wp-user-frontend' ) );
            } else {
                static::export_to_json( 'wpuf_forms', $post_ids );
            }
        }
    }

    /**
     * Export into json file
     *
     * @param  string $post_type
     * @param  array  $post_ids
     */
    public static function export_to_json( $post_type, $post_ids = array( ) ) {

        $formatted_data = array();
        $ids            = array();
        $blogname       = strtolower( str_replace( " ", "-", get_option( 'blogname' ) ) );
        $date           = date( "Y-m-d" );
        $json_name      = $blogname . "-wpuf-" . $post_type . '-' . $date; // Namming the filename will be generated.

        if ( ! empty( $post_ids ) ) {
            foreach ( $post_ids as $key => $value ) {
                array_push( $ids, $value );
            }
        }

        $args = array(
            'post_status' => 'publish',
            'post_type'   => $post_type,
            'post__in'    => (!empty( $ids ) ) ? $ids : ''
        );

        $query = new WP_Query( $args );

        foreach ( $query->posts as $post ) {
            $postdata = get_object_vars( $post );
            unset( $postdata['ID'] );

            $data = array(
                'post_data' => $postdata,
                'meta_data' => array(
                    'fields'        => wpuf_get_form_fields( $post->ID ),
                    'settings'      => wpuf_get_form_settings( $post->ID ),
                    'notifications' => wpuf_get_form_notifications( $post->ID )
                )
            );

            array_push( $formatted_data, $data );
        }

        $json_file = json_encode( $formatted_data ); // Encode data into json data

        ob_clean();

        echo $json_file;

        header( "Content-Type: text/json; charset=" . get_option( 'blog_charset' ) );
        header( "Content-Disposition: attachment; filename=$json_name.json" );

        exit();
    }

    /**
     * Formetted meta key value
     *
     * @param  array $array
     * @return array
     */
    function formetted_meta_key_value( $array ) {
        $result = array( );

        foreach ( $array as $key => $val ) {
            $result[$key] = $val[0];
        }

        return $result;
    }

    function tool_page() {
        $msg = isset( $_GET['msg'] ) ? $_GET['msg'] : '';
        $text = '';
        $confirmation_message  = __( 'Are you Sure?', 'wp-user-frontend' );
        switch ($msg) {
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
                    <?php echo $text; ?>
                </p>
            </div>

        <?php } ?>


        <div class="metabox-holder">
            <div class="postbox">
                <h3><?php _e( 'Page Installation', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <p><?php _e( 'Clicking this button will create required pages for the plugin. Note: It\'ll not delete/replace existing pages.', 'wp-user-frontend' ); ?></p>
                    <a class="button button-primary" href="<?php echo add_query_arg( array( 'install_wpuf_pages' => true ) ); ?>"><?php _e( 'Install WPUF Pages', 'wp-user-frontend' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php _e( 'Reset Settings', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <p><?php _e( '<strong>Caution:</strong> This tool will delete all the plugin settings of WP User Frontend Pro', 'wp-user-frontend' ); ?></p>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'wpuf_action' => 'clear_settings' ), 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ); ?>" onclick="return confirm('Are you sure?');"><?php _e( 'Reset Settings', 'wp-user-frontend' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php _e( 'Delete Forms', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <p><?php _e( '<strong>Caution:</strong> This tool will delete all the post and registration/profile forms.', 'wp-user-frontend' ); ?></p>

                    <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'wpuf_action' => 'del_post_forms' ), 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ); ?>" onclick="return confirm('<?php echo $confirmation_message ?>');"><?php _e( 'Delete Post Forms', 'wp-user-frontend' ); ?></a>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'wpuf_action' => 'del_pro_forms' ), 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ); ?>" onclick="return confirm('<?php echo $confirmation_message ?>');"><?php _e( 'Delete Registration Forms', 'wp-user-frontend' ); ?></a>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'wpuf_action' => 'del_subs' ), 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ); ?>" onclick="return confirm('<?php echo $confirmation_message ?>');"><?php _e( 'Delete Subscriptions', 'wp-user-frontend' ); ?></a>
                    <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'wpuf_action' => 'del_coupon' ), 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ); ?>" onclick="return confirm('<?php echo $confirmation_message ?>');"><?php _e( 'Delete Coupons', 'wp-user-frontend' ); ?></a>
                </div>
            </div>

            <div class="postbox">
                <h3><?php _e( 'Transactions', 'wp-user-frontend' ); ?></h3>

                <div class="inside">
                    <p><?php _e( 'This tool will delete all the transactions from the transaction table.', 'wp-user-frontend' ); ?></p>

                    <a class="button button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'wpuf_action' => 'clear_transaction' ), 'admin.php?page=wpuf_tools&action=tools' ), 'wpuf-tools-action' ); ?>" onclick="return confirm('<?php echo $confirmation_message ?>');"><?php _e( 'Delete Transactions', 'wp-user-frontend' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }

}
