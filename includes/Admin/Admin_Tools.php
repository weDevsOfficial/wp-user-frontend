<?php

namespace WeDevs\Wpuf\Admin;

use WP_Error;
use WP_Http;
use WP_Query;

/**
 * Manage Import Export
 *
 * @since 2.2
 */
class Admin_Tools {

    public function __construct() {
        add_action( 'wpuf_load_tools', [ $this, 'handle_tools_action' ] );
        add_action( 'wpuf_load_tools', [ $this, 'add_logout_to_menu' ] );
        add_filter( 'upload_mimes', [ $this, 'add_json_mime_type' ] );
        add_filter( 'wp_handle_upload_prefilter', [ $this, 'enable_json_upload' ] );
    }

    /**
     * List of All the post forms
     *
     * @return void
     */
    public function list_forms() {
        $post_data = wp_unslash( $_POST );

        if ( isset( $post_data['export'] ) ) {
            check_admin_referer( 'wpuf-export-form' );

            $export_type = isset( $post_data['export_type'] ) ? sanitize_text_field( $post_data['export_type'] ) : 'all';
            $form_ids    = isset( $post_data['form_ids'] ) ? array_map( 'absint', $post_data['form_ids'] ) : array();

            $this->export_forms( 'wpuf_forms', $export_type, $form_ids );
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
                                <input class="export_type" type="radio" name="export_type" value="all" id="wpuf-all_export" checked>
                                <label for="wpuf-all_export"><?php esc_html_e( 'All', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <input class="export_type" type="radio" name="export_type" value="selected" id="wpuf-selected_export">
                                <label for="wpuf-selected_export"><?php esc_html_e( 'Select individual', 'wp-user-frontend' ); ?></label></p>
                            <p>
                                <select class="formlist" name="form_ids[]" multiple="multiple">
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
            printf( '<p>%s</p>', esc_html( __( 'Sorry you have no post form to export', 'wp-user-frontend' ) ) );
        }
    }

    /**
     * List of All Registration forms
     *
     * @return void
     */
    public function list_regis_forms() {
        $post_data = wp_unslash( $_POST );

        if ( isset( $post_data['export_regis_form'] ) ) {
            check_admin_referer( 'wpuf-export-regs-form' );

            $export_type = isset( $post_data['export_type'] ) ? sanitize_text_field( $post_data['export_type'] ) : 'all';
            $form_ids    = isset( $post_data['form_ids'] ) ? array_map( 'absint', $post_data['form_ids'] ) : array();

            $this->export_forms( 'wpuf_profile', $export_type, $form_ids );
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
                                <input class="export_type" type="radio" name="export_type" value="all" id="wpuf-all_regis_export" checked>
                                <label for="wpuf-all_regis_export"><?php esc_html_e( 'All', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <input class="export_type" type="radio" name="export_type" value="selected" id="wpuf-selected_regis_export">
                                <label for="wpuf-selected_regis_export"><?php esc_html_e( 'Select individual', 'wp-user-frontend' ); ?></label>
                            </p>

                            <p>
                                <select class="formlist" name="form_ids[]" multiple="multiple">
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
            printf( '<p>%s</p>', esc_html( __( 'Sorry you have no registration form to export', 'wp-user-frontend' ) ) );
        }
    }

    /**
     * Import functionality
     */
    public function import_data() {
        ?>
        <h3><?php esc_html_e( 'Import forms', 'wp-user-frontend' ); ?></h3>

        <p>
            <?php esc_html_e( 'Upload your JSON file and start imporing WPUF forms here', 'wp-user-frontend' ); ?>
        </p>

        <div id="wpuf-import-form">
            <wpuf-form-uploader />
        </div>

        <script type="text/x-template" id="wpuf-import-form-template">
            <button v-if="! isBusy" type="button" class="button button-primary" @click="openImageManager()">
                <?php esc_html_e( 'Upload JSON File', 'wp-user-frontend' ); ?>
            </button>
            <button v-else type="button" class="button button-primary" disabled>
                <?php esc_html_e( 'Importing JSON File', 'wp-user-frontend' ); ?>...
            </button>
        </script>
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

        $errors = new WP_Error();

        foreach ( $options as $key => $value ) {
            $generate_post = [
                'post_title'     => $value['post_data']['post_title'],
                'post_status'    => $value['post_data']['post_status'],
                'post_type'      => $value['post_data']['post_type'],
                'ping_status'    => $value['post_data']['ping_status'],
                'comment_status' => $value['post_data']['comment_status'],
            ];

            $post_id = wp_insert_post( $generate_post, true );

            if ( is_wp_error( $post_id ) ) {
                $errors->add( $post_id->get_error_code(), $post_id->get_error_message() );
            } else {
                foreach ( $value['meta_data']['fields'] as $order => $field ) {
                    wpuf_insert_form_field( $post_id, $field, false, $order );
                }

                update_post_meta( $post_id, 'wpuf_form_settings', $value['meta_data']['settings'] );
                update_post_meta( $post_id, 'notifications', $value['meta_data']['notifications'] );
            }
        }

        if ( $errors->has_errors() ) {
            return $errors;
        }

        return true;
    }

    /**
     * Export normal form data
     *
     * @param string $export_type
     * @param array    $form_ids
     */
    public function export_forms( $form_type, $export_type, $form_ids ) {
        if ( $export_type === 'all' ) {
            static::export_to_json( $form_type );
        } else if ( 'selected' === $export_type ) {
            if ( empty( $form_ids ) ) {
                printf(
                    '<div class="error"><p>%s</p></div>',
                    esc_html__( 'Please select some form for exporting', 'wp-user-frontend' )
                );
            } else {
                static::export_to_json( $form_type, $form_ids );
            }
        }
    }

    /**
     * Export into json file
     *
     * @param string $post_type
     * @param array  $post_ids
     */
    public static function export_to_json( $post_type, $post_ids = [] ) {
        $formatted_data = [];
        $ids            = [];
        $blogname       = strtolower( str_replace( ' ', '-', get_option( 'blogname' ) ) );
        $date           = date( 'Y-m-d' ); // phpcs:ignore
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

        $json_file = wp_json_encode( $formatted_data ); // Encode data into json data

        ob_clean();

        echo $json_file; // phpcs:ignore

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
        $error_text            = '';
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

            case 'logout_menu_added':
                $text = __( 'Logout link has been added to the menu successfully!', 'wp-user-frontend' );
                break;

            case 'logout_menu_error':
                $error_text = __( 'Failed to add logout link to the menu.', 'wp-user-frontend' );
                break;

            case 'no_menu_selected':
                $error_text = __( 'Please select a menu to add the logout link.', 'wp-user-frontend' );
                break;
        }

        if ( $text ) {
            ?>
            <div class="updated">
                <p>
                    <?php echo esc_html( $text ); ?>
                </p>
            </div>

        <?php }

        if ( $error_text ) {
            ?>
            <div class="error">
                <p>
                    <?php echo esc_html( $error_text ); ?>
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

            <?php $this->logout_menu_tool(); ?>
        </div>
        <?php
    }

    /**
     * Render the logout menu tool UI
     *
     * @since 4.2.10
     *
     * @return void
     */
    public function logout_menu_tool() {
        $menus         = $this->get_nav_menus();
        $is_block_theme = $this->is_block_theme();
        $logout_url    = wpuf_get_logout_url();
        ?>
        <div class="postbox">
            <h3><?php esc_html_e( 'Add Logout to Menu', 'wp-user-frontend' ); ?></h3>

            <div class="inside">
                <p><?php esc_html_e( 'Add a logout link to your navigation menu so users can easily log out from the frontend.', 'wp-user-frontend' ); ?></p>

                <?php if ( $is_block_theme ) : ?>
                    <div class="wpuf-fse-notice" style="background: #fff8e5; border-left: 4px solid #ffb900; padding: 12px; margin-bottom: 15px;">
                        <strong><?php esc_html_e( 'Block Theme Detected (FSE)', 'wp-user-frontend' ); ?></strong>
                        <p style="margin: 8px 0 0;">
                            <?php esc_html_e( 'Your theme uses the Full Site Editor. To add a logout link to your navigation:', 'wp-user-frontend' ); ?>
                        </p>
                        <ol style="margin: 10px 0 10px 20px;">
                            <li><?php esc_html_e( 'Go to Appearance > Editor > Navigation', 'wp-user-frontend' ); ?></li>
                            <li><?php esc_html_e( 'Click the + button to add a new item', 'wp-user-frontend' ); ?></li>
                            <li><?php esc_html_e( 'Select "Custom Link"', 'wp-user-frontend' ); ?></li>
                            <li><?php esc_html_e( 'Use the URL and label below', 'wp-user-frontend' ); ?></li>
                        </ol>
                    </div>
                <?php endif; ?>

                <div class="wpuf-logout-url-info" style="background: #f6f7f7; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <p style="margin: 0 0 10px;"><strong><?php esc_html_e( 'Logout URL (copy this):', 'wp-user-frontend' ); ?></strong></p>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="wpuf-logout-url" value="<?php echo esc_url( $logout_url ); ?>" readonly style="flex: 1; padding: 8px;" onclick="this.select();" />
                        <button type="button" class="button" onclick="navigator.clipboard.writeText(document.getElementById('wpuf-logout-url').value); this.textContent='<?php echo esc_js( __( 'Copied!', 'wp-user-frontend' ) ); ?>'; setTimeout(() => this.textContent='<?php echo esc_js( __( 'Copy', 'wp-user-frontend' ) ); ?>', 2000);">
                            <?php esc_html_e( 'Copy', 'wp-user-frontend' ); ?>
                        </button>
                    </div>
                    <p style="margin: 10px 0 0; color: #666; font-size: 12px;">
                        <?php esc_html_e( 'Note: The logout URL contains a security nonce that may expire. For dynamic logout URLs, consider using a shortcode or widget.', 'wp-user-frontend' ); ?>
                    </p>
                </div>

                <?php if ( ! empty( $menus ) && ! $is_block_theme ) : ?>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'wpuf-add-logout-to-menu', 'wpuf_add_logout_nonce' ); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="wpuf_menu_id"><?php esc_html_e( 'Select Menu', 'wp-user-frontend' ); ?></label>
                                </th>
                                <td>
                                    <select name="wpuf_menu_id" id="wpuf_menu_id" style="min-width: 200px;">
                                        <option value=""><?php esc_html_e( '— Select a menu —', 'wp-user-frontend' ); ?></option>
                                        <?php foreach ( $menus as $menu ) : ?>
                                            <option value="<?php echo esc_attr( $menu->term_id ); ?>">
                                                <?php echo esc_html( $menu->name ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="wpuf_logout_label"><?php esc_html_e( 'Menu Label', 'wp-user-frontend' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wpuf_logout_label" id="wpuf_logout_label" value="<?php esc_attr_e( 'Logout', 'wp-user-frontend' ); ?>" class="regular-text" />
                                </td>
                            </tr>
                        </table>

                        <p>
                            <input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Logout to Menu', 'wp-user-frontend' ); ?>" />
                        </p>
                    </form>
                <?php elseif ( empty( $menus ) && ! $is_block_theme ) : ?>
                    <p style="color: #666;">
                        <?php
                        printf(
                            /* translators: %s: URL to create menu */
                            wp_kses_post( __( 'No menus found. <a href="%s">Create a menu</a> first, then come back to add the logout link.', 'wp-user-frontend' ) ),
                            esc_url( admin_url( 'nav-menus.php' ) )
                        );
                        ?>
                    </p>
                <?php endif; ?>

                <p style="margin-top: 15px;">
                    <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button">
                        <?php esc_html_e( 'Go to Menus', 'wp-user-frontend' ); ?>
                    </a>
                    <?php if ( $is_block_theme ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'site-editor.php?path=%2Fnavigation' ) ); ?>" class="button">
                            <?php esc_html_e( 'Go to Site Editor Navigation', 'wp-user-frontend' ); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Display shortcodes page with all available shortcodes
     *
     * @since 4.2.9
     *
     * @return void
     */
    public function shortcodes_page() {
        $shortcodes    = $this->get_all_shortcodes();
        $is_pro_active = class_exists( 'WP_User_Frontend_Pro' );
        ?>
        <div class="wpuf-shortcodes-wrapper">
            <p class="description" style="margin-bottom: 15px;">
                <?php esc_html_e( 'Copy and paste these shortcodes into your pages or posts. Click the copy icon to copy a shortcode to your clipboard.', 'wp-user-frontend' ); ?>
            </p>

            <?php if ( ! $is_pro_active ) : ?>
                <div class="notice notice-info inline" style="margin: 0 0 20px 0; padding: 10px 15px;">
                    <p>
                        <?php
                        printf(
                            /* translators: %s: Pro badge */
                            esc_html__( 'Shortcodes marked with %s require WP User Frontend Pro.', 'wp-user-frontend' ),
                            '<span style="background: #ff6b35; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; text-transform: uppercase;">Pro</span>'
                        );
                        ?>
                        <a href="https://wedevs.com/wp-user-frontend-pro/" target="_blank" style="margin-left: 5px;"><?php esc_html_e( 'Upgrade to Pro', 'wp-user-frontend' ); ?></a>
                    </p>
                </div>
            <?php endif; ?>

            <?php foreach ( $shortcodes as $category ) : ?>
                <div class="postbox wpuf-shortcode-category" style="margin-bottom: 20px;">
                    <h3 style="padding: 10px 15px; margin: 0; border-bottom: 1px solid #ccd0d4;">
                        <?php echo esc_html( $category['title'] ); ?>
                    </h3>
                    <div class="inside" style="padding: 15px;">
                        <?php if ( ! empty( $category['description'] ) ) : ?>
                            <p class="description" style="margin-bottom: 15px;">
                                <?php echo esc_html( $category['description'] ); ?>
                            </p>
                        <?php endif; ?>

                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th style="width: 25%;"><?php esc_html_e( 'Shortcode', 'wp-user-frontend' ); ?></th>
                                    <th style="width: 45%;"><?php esc_html_e( 'Description', 'wp-user-frontend' ); ?></th>
                                    <th style="width: 30%;"><?php esc_html_e( 'Example', 'wp-user-frontend' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $category['shortcodes'] as $shortcode ) : ?>
                                    <?php $is_pro_shortcode = ! empty( $shortcode['pro'] ); ?>
                                    <tr<?php echo $is_pro_shortcode ? ' class="wpuf-pro-shortcode"' : ''; ?>>
                                        <td>
                                            <div class="wpuf-shortcode-copy-wrapper" style="display: flex; align-items: center; gap: 8px;">
                                                <code class="wpuf-shortcode-code" style="background: #f0f0f1; padding: 4px 8px; border-radius: 3px;">
                                                    <?php echo esc_html( $shortcode['code'] ); ?>
                                                </code>
                                                <?php if ( $is_pro_shortcode ) : ?>
                                                    <span class="wpuf-pro-badge" style="background: #ff6b35; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; text-transform: uppercase;"><?php esc_html_e( 'Pro', 'wp-user-frontend' ); ?></span>
                                                <?php endif; ?>
                                                <button type="button" class="wpuf-copy-shortcode button button-small" data-shortcode="<?php echo esc_attr( $shortcode['code'] ); ?>" title="<?php esc_attr_e( 'Copy to clipboard', 'wp-user-frontend' ); ?>">
                                                    <span class="dashicons dashicons-clipboard" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo esc_html( $shortcode['description'] ); ?>
                                        </td>
                                        <td>
                                            <?php if ( ! empty( $shortcode['example'] ) ) : ?>
                                                <div class="wpuf-shortcode-copy-wrapper" style="display: flex; align-items: center; gap: 8px;">
                                                    <code style="background: #e7f3ff; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                                        <?php echo esc_html( $shortcode['example'] ); ?>
                                                    </code>
                                                    <button type="button" class="wpuf-copy-shortcode button button-small" data-shortcode="<?php echo esc_attr( $shortcode['example'] ); ?>" title="<?php esc_attr_e( 'Copy to clipboard', 'wp-user-frontend' ); ?>">
                                                        <span class="dashicons dashicons-clipboard" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
                                                    </button>
                                                </div>
                                            <?php else : ?>
                                                <span class="description"><?php esc_html_e( 'Same as shortcode', 'wp-user-frontend' ); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <style>
            .wpuf-shortcodes-wrapper .wpuf-copy-shortcode {
                padding: 2px 6px;
                min-height: 28px;
            }
            .wpuf-shortcodes-wrapper .wpuf-copy-shortcode:hover {
                background: #2271b1;
                color: #fff;
                border-color: #2271b1;
            }
            .wpuf-shortcodes-wrapper .wpuf-copy-shortcode:hover .dashicons {
                color: #fff;
            }
            .wpuf-shortcodes-wrapper .wpuf-copy-shortcode.copied {
                background: #00a32a;
                border-color: #00a32a;
                color: #fff;
            }
            .wpuf-shortcodes-wrapper .wpuf-copy-shortcode.copied .dashicons {
                color: #fff;
            }
            .wpuf-shortcodes-wrapper .wpuf-copy-shortcode.copied .dashicons::before {
                content: "\f147";
            }
            .wpuf-shortcode-category table td,
            .wpuf-shortcode-category table th {
                vertical-align: middle;
            }
        </style>

        <script>
            (function($) {
                'use strict';

                $(document).on('click', '.wpuf-copy-shortcode', function(e) {
                    e.preventDefault();

                    var $button = $(this);
                    var shortcode = $button.data('shortcode');

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(shortcode).then(function() {
                            $button.addClass('copied');
                            setTimeout(function() {
                                $button.removeClass('copied');
                            }, 1500);
                        });
                    } else {
                        var $temp = $('<textarea>');
                        $('body').append($temp);
                        $temp.val(shortcode).select();
                        document.execCommand('copy');
                        $temp.remove();

                        $button.addClass('copied');
                        setTimeout(function() {
                            $button.removeClass('copied');
                        }, 1500);
                    }
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Get all available shortcodes organized by category
     *
     * @since 4.2.9
     *
     * @return array
     */
    public function get_all_shortcodes() {
        $shortcodes = [
            'account' => [
                'title'       => __( 'Account & Profile', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for user account management, login, and registration.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_account]',
                        'description' => __( 'Displays the user account page with profile, posts, and subscription info.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                    [
                        'code'        => '[wpuf_dashboard]',
                        'description' => __( 'Shows the frontend dashboard where users can view and manage their posts.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_dashboard post_type="post"]',
                    ],
                    [
                        'code'        => '[wpuf_editprofile]',
                        'description' => __( 'Displays the profile edit form for logged-in users.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                    [
                        'code'        => '[wpuf-login]',
                        'description' => __( 'Displays a login form for users.', 'wp-user-frontend' ),
                        'example'     => '[wpuf-login redirect="https://example.com"]',
                    ],
                    [
                        'code'        => '[wpuf-registration]',
                        'description' => __( 'Displays a registration form. Use id attribute to specify a form.', 'wp-user-frontend' ),
                        'example'     => '[wpuf-registration id="123"]',
                    ],
                    [
                        'code'        => '[wpuf_profile]',
                        'description' => __( 'Displays a profile form for users to update their profile. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_profile id="123"]',
                        'pro'         => true,
                    ],
                ],
            ],
            'forms' => [
                'title'       => __( 'Post Forms', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for displaying and editing post submission forms.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_form]',
                        'description' => __( 'Displays a post submission form. Use id attribute to specify which form.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_form id="123"]',
                    ],
                    [
                        'code'        => '[wpuf_edit]',
                        'description' => __( 'Allows users to edit their submitted posts.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                ],
            ],
            'subscription' => [
                'title'       => __( 'Subscription & Payments', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for subscription packs and user subscription information.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_sub_pack]',
                        'description' => __( 'Displays all available subscription packs/pricing plans.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_sub_pack]',
                    ],
                    [
                        'code'        => '[wpuf_sub_info]',
                        'description' => __( 'Shows the current user\'s subscription information and status.', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                ],
            ],
            'user_directory' => [
                'title'       => __( 'User Directory', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for displaying and managing user directories.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf-edit-users]',
                        'description' => __( 'Displays a list of users that can be edited (admin only).', 'wp-user-frontend' ),
                        'example'     => '',
                    ],
                    [
                        'code'        => '[wpuf_user_listing]',
                        'description' => __( 'Displays a user directory listing with search and filters. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_user_listing id="123"]',
                        'pro'         => true,
                    ],
                    [
                        'code'        => '[wpuf_user_listing_id]',
                        'description' => __( 'Displays a single user profile from the directory. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_user_listing_id id="123"]',
                        'pro'         => true,
                    ],
                ],
            ],
            'content_restriction' => [
                'title'       => __( 'Content Restriction (Pro)', 'wp-user-frontend' ),
                'description' => __( 'Shortcodes for restricting content based on subscriptions or user roles.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf_restrict]',
                        'description' => __( 'Restricts content inside the shortcode to specific subscription packs.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_restrict pack="1,2"]Content here[/wpuf_restrict]',
                        'pro'         => true,
                    ],
                    [
                        'code'        => '[wpuf_partial_restriction]',
                        'description' => __( 'Shows partial content with a message to subscribe for full access.', 'wp-user-frontend' ),
                        'example'     => '[wpuf_partial_restriction]',
                        'pro'         => true,
                    ],
                ],
            ],
            'utility' => [
                'title'       => __( 'Utility Shortcodes', 'wp-user-frontend' ),
                'description' => __( 'Helper shortcodes for displaying meta fields and other utilities.', 'wp-user-frontend' ),
                'shortcodes'  => [
                    [
                        'code'        => '[wpuf-meta]',
                        'description' => __( 'Displays a custom field/meta value. Use name attribute to specify the meta key.', 'wp-user-frontend' ),
                        'example'     => '[wpuf-meta name="my_custom_field"]',
                    ],
                    [
                        'code'        => '[wpuf_qr]',
                        'description' => __( 'Displays a QR code for a field value. (Pro)', 'wp-user-frontend' ),
                        'example'     => '[wpuf_qr]',
                        'pro'         => true,
                    ],
                ],
            ],
        ];

        /**
         * Filter the shortcodes list for the tools page
         *
         * @since 4.2.9
         *
         * @param array $shortcodes The shortcodes array organized by category
         */
        return apply_filters( 'wpuf_tools_shortcodes_list', $shortcodes );
    }

    /**
     * Handle tools page action
     *
     * @return void
     */
    public function handle_tools_action() {
        if ( ! isset( $_GET['wpuf_action'] ) ) {
            return;
        }
        check_admin_referer( 'wpuf-tools-action' );
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        global $wpdb;
        $action  = isset( $_GET['wpuf_action'] ) ? sanitize_text_field( wp_unslash( $_GET['wpuf_action'] ) ) : '';
        $message = 'del_forms';
        switch ( $action ) {
            case 'clear_settings':
                delete_option( 'wpuf_general' );
                delete_option( 'wpuf_dashboard' );
                delete_option( 'wpuf_profile' );
                delete_option( 'wpuf_payment' );
                delete_option( '_wpuf_page_created' );
                $message = 'settings_cleared';
                break;
            case 'del_post_forms':
                $this->delete_post_type( 'wpuf_forms' );
                break;
            case 'del_pro_forms':
                $this->delete_post_type( 'wpuf_profile' );
                break;
            case 'del_subs':
                $this->delete_post_type( 'wpuf_subscription' );
                break;
            case 'del_coupon':
                $this->delete_post_type( 'wpuf_coupon' );
                break;
            case 'clear_transaction':
                $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wpuf_transaction" );
                $message = 'del_trans';
                break;
            default:
                // code...
                break;
        }
        wp_safe_redirect( add_query_arg( [ 'msg' => $message ], admin_url( 'admin.php?page=wpuf_tools&action=tools' ) ) );
        exit;
    }

    /**
     * Enable json file upload via ajax in tools page
     *
     * @since 3.2.0
     *
     * @param array $file
     *
     * @return array
     */
    public function enable_json_upload( $file ) {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && 'upload-attachment' === $_POST['action'] && isset( $_POST['type'] ) && 'wpuf-form-uploader' === $_POST['type'] ) {
            // @see wp_ajax_upload_attachment
            check_ajax_referer( 'media-form' );

            // Security: Only allow admins to upload JSON files
            if ( ! current_user_can( 'manage_options' ) ) {
                $file['error'] = __( 'You do not have permission to upload files here.', 'wp-user-frontend' );
                return $file;
            }

            add_filter( 'wp_check_filetype_and_ext', [ $this, 'check_filetype_and_ext' ], 10, 5 );
        }

        return $file;
    }

    /**
     * Ajax handler to import WPUF form
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function import_forms() {
        check_ajax_referer( 'wpuf_admin_tools' );

        // Security: Check user has proper admin capabilities
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_send_json_error(
                new WP_Error(
                    'wpuf_ajax_import_forms_error',
                    __( 'Unauthorized operation', 'wp-user-frontend' )
                ),
                WP_Http::FORBIDDEN
            );
        }

        if ( ! isset( $_POST['file_id'] ) ) {
            wp_send_json_error(
                new WP_Error(
                    'wpuf_ajax_import_forms_error',
                    __( 'Missing file_id param', 'wp-user-frontend' )
                ),
                WP_Http::BAD_REQUEST
            );
        }
        $file_id = absint( wp_unslash( $_POST['file_id'] ) );
        $file    = get_attached_file( $file_id );
        if ( empty( $file ) ) {
            wp_send_json_error(
                new WP_Error(
                    'wpuf_ajax_import_forms_error',
                    __( 'JSON file not found', 'wp-user-frontend' )
                ),
                WP_Http::NOT_FOUND
            );
        }
        $filetype = wp_check_filetype( $file, [ 'json' => 'application/json' ] );
        if ( ! isset( $filetype['type'] ) || 'application/json' !== $filetype['type'] ) {
            wp_send_json_error(
                new WP_Error(
                    'wpuf_ajax_import_forms_error',
                    __( 'Provided file is not a JSON file.', 'wp-user-frontend' )
                ),
                WP_Http::UNSUPPORTED_MEDIA_TYPE
            );
        }

        $imported = self::import_json_file( $file );
        if ( is_wp_error( $imported ) ) {
            wp_send_json_error( $imported, WP_Http::UNPROCESSABLE_ENTITY );
        }
        wp_send_json_success(
            [
                'message' => __( 'Forms imported successfully.', 'wp-user-frontend' ),
            ]
        );
    }

    /**
     * Add json file mime type to upload in WP Media
     *
     * @since 3.2.0
     *
     * @param array $mime_types
     *
     * @return array
     */
    public function add_json_mime_type( $mime_types ) {
        $mime_types['json'] = 'application/json';

        return $mime_types;
    }

    /**
     * Allow json file to upload with async uploader
     *
     * @since 3.2.0
     * @since 4.2.9 Added security validation to prevent arbitrary file uploads
     *
     * @param array  $info            File data array with 'ext', 'type', and 'proper_filename' keys
     * @param string $file            Full path to the file
     * @param string $filename        The name of the file (may differ from $file due to $file being in a tmp directory)
     * @param array  $mimes           Array of mime types keyed by their file extension regex
     * @param string $real_mime       The actual mime type or false if the type cannot be determined
     *
     * @return array
     */
    public function check_filetype_and_ext( $info, $file, $filename, $mimes, $real_mime ) {
        // Security: Validate this is actually a JSON file

        // 1. Check the file extension is .json
        $filetype = wp_check_filetype( $filename, [ 'json' => 'application/json' ] );

        if ( 'json' !== $filetype['ext'] ) {
            // Not a .json file - reject it
            return $info;
        }

        // 2. Verify the file exists and is readable
        if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
            return $info;
        }

        // 3. Check for dangerous file extensions that might be disguised
        $dangerous_extensions = [ 'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd' ];
        $file_parts = pathinfo( $filename );

        // Check for double extensions (e.g., shell.php.json)
        $filename_lower = strtolower( $filename );
        foreach ( $dangerous_extensions as $ext ) {
            if ( strpos( $filename_lower, '.' . $ext ) !== false ) {
                // Dangerous extension found - reject
                return $info;
            }
        }

        // 4. Validate the file content is actually valid JSON
        $file_content = file_get_contents( $file );

        if ( false === $file_content ) {
            return $info;
        }

        // Try to decode the JSON
        json_decode( $file_content );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // Not valid JSON - reject it
            return $info;
        }

        // 5. Additional security: Check file doesn't contain PHP tags
        if ( preg_match( '/<\?php|<\?=|<script[^>]*>.*?<\/script>/i', $file_content ) ) {
            // Contains PHP or script tags - reject it
            return $info;
        }

        // All validations passed - it's a legitimate JSON file
        $info['ext']  = 'json';
        $info['type'] = 'application/json';

        return $info;
    }

    /**
     * Delete all posts by a post type
     *
     * @param string $post_type
     *
     * @return void
     */
    public function delete_post_type( $post_type ) {
        $query = new WP_Query(
            [
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => [ 'publish', 'draft', 'pending', 'trash' ],
            ]
        );
        $posts = $query->get_posts();
        if ( $posts ) {
            foreach ( $posts as $item ) {
                wp_delete_post( $item->ID, true );
            }
        }
        wp_reset_postdata();
    }

    /**
     * Get all navigation menus
     *
     * @since 4.2.10
     *
     * @return array
     */
    public function get_nav_menus() {
        $menus = wp_get_nav_menus();

        return $menus;
    }

    /**
     * Add logout link to navigation menu
     *
     * @since 4.2.10
     *
     * @return void
     */
    public function add_logout_to_menu() {
        if ( ! isset( $_POST['wpuf_add_logout_nonce'] ) ) {
            return;
        }

        check_admin_referer( 'wpuf-add-logout-to-menu', 'wpuf_add_logout_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $menu_id    = isset( $_POST['wpuf_menu_id'] ) ? intval( $_POST['wpuf_menu_id'] ) : 0;
        $menu_label = isset( $_POST['wpuf_logout_label'] ) ? sanitize_text_field( wp_unslash( $_POST['wpuf_logout_label'] ) ) : __( 'Logout', 'wp-user-frontend' );

        if ( ! $menu_id ) {
            wp_safe_redirect( add_query_arg( [ 'msg' => 'no_menu_selected' ], admin_url( 'admin.php?page=wpuf_tools&tab=tools' ) ) );
            exit;
        }

        $result = wpuf_add_logout_to_menu( $menu_id, $menu_label );

        if ( is_wp_error( $result ) ) {
            wp_safe_redirect( add_query_arg( [ 'msg' => 'logout_menu_error' ], admin_url( 'admin.php?page=wpuf_tools&tab=tools' ) ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( [ 'msg' => 'logout_menu_added' ], admin_url( 'admin.php?page=wpuf_tools&tab=tools' ) ) );
        exit;
    }

    /**
     * Get logout URL info for display
     *
     * @since 4.2.10
     *
     * @return array
     */
    public function get_logout_url_info() {
        return [
            'url'   => wpuf_get_logout_url(),
            'label' => __( 'Logout', 'wp-user-frontend' ),
        ];
    }

    /**
     * Check if current theme is a block theme (FSE)
     *
     * @since 4.2.10
     *
     * @return bool
     */
    public function is_block_theme() {
        return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
    }
}
