<?php

/**
 * Page installer
 *
 * @since 2.3
 */
class WPUF_Admin_Installer {

    function __construct() {
        add_action( 'admin_notices', array($this, 'admin_notice') );
        add_action( 'admin_init', array($this, 'handle_request') );
    }

    /**
     * Print admin notices
     *
     * @return void
     */
    function admin_notice() {
        $page_created = get_option( '_wpuf_page_created' );

        if ( $page_created != '1' ) {
            ?>
            <div class="updated error">
                <p>
                    <?php _e( 'If you have not created <strong>WP User Frontend Pro</strong> pages yet, you can do this by one click.', 'wpuf' ); ?>
                </p>
                <p class="submit">
                    <a class="button button-primary" href="<?php echo add_query_arg( array( 'install_wpuf_pages' => true ), admin_url( 'admin.php?page=wpuf-settings' ) ); ?>"><?php _e( 'Install WPUF Pages', 'wpuf' ); ?></a>
                    or
                    <a class="button" href="<?php echo add_query_arg( array( 'wpuf_hide_page_nag' => true ) ); ?>"><?php _e( 'Skip Setup', 'wpuf' ); ?></a>
                </p>
            </div>
            <?php
        }

        if ( isset( $_GET['wpuf_page_installed'] ) && $_GET['wpuf_page_installed'] == '1' ) {
            ?>
            <div class="updated">
                <p>
                    <strong><?php _e( 'Congratulations!', 'wpuf' ); ?></strong> <?php _e( 'Pages for <strong>WP User Frontend Pro</strong> has been successfully installed and saved!', 'wpuf' ); ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Handle the page creation button requests
     *
     * @return void
     */
    function handle_request() {
        if ( isset( $_GET['install_wpuf_pages'] ) && $_GET['install_wpuf_pages'] == '1' ) {
            $this->init_pages();
        }

        if ( isset( $_GET['wpuf_hide_page_nag'] ) && $_GET['wpuf_hide_page_nag'] == '1' ) {
            update_option( '_wpuf_page_created', '1' );
        }
    }

    /**
     * Initialize the plugin with some default page/settings
     *
     * @since 2.2
     * @return void
     */
    function init_pages() {

        // create a dashboard page
        $dashboard_page = $this->create_page( __( 'Dashboard', 'wpuf' ), '[wpuf_dashboard]' );
        $edit_page      = $this->create_page( __( 'Edit', 'wpuf' ), '[wpuf_edit]' );

        // login page
        $login_page     = $this->create_page( __( 'Login', 'wpuf' ), '[wpuf-login]' );

        $post_form      = $this->create_form();

        // payment page
        $subscr_page    = $this->create_page( __( 'Subscription', 'wpuf' ), __( '[wpuf_sub_pack]') );
        $payment_page   = $this->create_page( __( 'Payment', 'wpuf' ), __( 'Please select a gateway for payment') );
        $thank_page     = $this->create_page( __( 'Thank You', 'wpuf' ), __( '<h1>Payment is complete</h1><p>Congratulations, your payment has been completed!</p>') );
        $bank_page      = $this->create_page( __( 'Order Received', 'wpuf' ), __( 'Hi, we have received your order. We will validate the order and will take necessary steps to move forward.') );

        // save the settings
        if ( $edit_page ) {
            update_option( 'wpuf_general', array(
                'edit_page_id'      => $edit_page,
                'default_post_form' => $post_form
            ) );
        }

        // profile pages
        $profile_options = array();
        $reg_page = false;

        if ( $login_page ) {
            $profile_options['login_page'] = $login_page;
        }

        $data = apply_filters( 'wpuf_pro_page_install', $profile_options );

        if ( is_array( $data ) ) {

            if ( isset ( $data['profile_options'] ) ) {
                $profile_options = $data['profile_options'];
            }
            if ( isset ( $data['reg_page'] ) ) {
                $reg_page = $data['reg_page'];
            }
        }

        if ( $login_page && $reg_page ) {
            $profile_options['register_link_override'] = 'on';
        }

        update_option( 'wpuf_profile', $profile_options );

        // payment pages
        update_option( 'wpuf_payment', array(
            'subscription_page' => $subscr_page,
            'payment_page'      => $payment_page,
            'payment_success'   => $thank_page,
            'bank_success'      => $bank_page
        ) );

        update_option( '_wpuf_page_created', '1' );

        wp_redirect( admin_url( 'admin.php?page=wpuf-settings&wpuf_page_installed=1' ) );
        exit;
    }

    /**
     * Create a page with title and content
     *
     * @param  string $page_title
     * @param  string $post_content
     * @return false|int
     */
    function create_page( $page_title, $post_content = '', $post_type = 'page' ) {
        $page_id = wp_insert_post( array(
            'post_title'     => $page_title,
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'post_content'   => $post_content
        ) );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            return $page_id;
        }

        return false;
    }

    /**
     * Create a basic registration form by default
     *
     * @return int|boolean
     */
    function create_reg_form() {
        $form_id = $this->create_page( __( 'Registration', 'wpuf' ), '', 'wpuf_profile' );

        if ( $form_id ) {
            $form_fields = array(
                array(
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
                    'wpuf_cond'   => NULL,
                ),
                array(
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
                    'wpuf_cond'     => NULL
                )
            );

            foreach ($form_fields as $order => $field) {
                WPUF_Admin_Form::insert_form_field( $form_id, $field, false, $order );
            }

            update_post_meta( $form_id, 'wpuf_form_settings', array(
                'role'           => 'subscriber',
                'redirect_to'    => 'same',
                'message'        => 'Registration successful',
                'update_message' => 'Profile updated successfully',
                'page_id'        => '0',
                'url'            => '',
                'submit_text'    => 'Register',
                'update_text'    => 'Update Profile'
            ) );

            return $form_id;
        }

        return false;
    }

    /**
     * Create a post form
     *
     * @return void
     */
    function create_form() {
        $form_id = $this->create_page( __( 'Sample Form', 'wpuf' ), '', 'wpuf_forms' );

        if ( $form_id ) {
            $form_fields = array(
                array(
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
                    'wpuf_cond'   => array( )
                ),
                array(
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
                    'wpuf_cond'    => array( )
                )
            );

            foreach ($form_fields as $order => $field) {
                WPUF_Admin_Form::insert_form_field( $form_id, $field, false, $order );
            }

            $settings = array(
                'post_type'        => 'post',
                'post_status'      => 'publish',
                'post_format'      => '0',
                'default_cat'      => '-1',
                'guest_post'       => 'false',
                'guest_details'    => 'true',
                'name_label'       => 'Name',
                'email_label'      => 'Email',
                'message_restrict' => 'This page is restricted. Please Log in / Register to view this page.',
                'redirect_to'      => 'post',
                'message'          => 'Post saved',
                'page_id'          => '',
                'url'              => '',
                'comment_status'   => 'open',
                'submit_text'      => 'Submit',
                'draft_post'       => 'false',
                'edit_post_status' => 'publish',
                'edit_redirect_to' => 'same',
                'update_message'   => 'Post updated successfully',
                'edit_page_id'     => '',
                'edit_url'         => '',
                'subscription'     => '- Select -',
                'update_text'      => 'Update',
                'notification'     => array(
                    'new'          => 'on',
                    'new_to'       => get_option( 'admin_email' ),
                    'new_subject'  => 'New post created',
                    'new_body'     => "Hi Admin, \r\n\r\nA new post has been created in your site %sitename% (%siteurl%). \r\n\r\nHere is the details: \r\nPost Title: %post_title% \r\nContent: %post_content% \r\nAuthor: %author% \r\nPost URL: %permalink% \r\nEdit URL: %editlink%",
                    'edit'         => 'off',
                    'edit_to'      => get_option( 'admin_email' ),
                    'edit_subject' => 'A post has been edited',
                    'edit_body'    => "Hi Admin, \r\n\r\nThe post \"%post_title%\" has been updated. \r\n\r\nHere is the details: \r\nPost Title: %post_title% \r\nContent: %post_content% \r\nAuthor: %author% \r\nPost URL: %permalink% \r\nEdit URL: %editlink%",
                ),
            );

            update_post_meta( $form_id, 'wpuf_form_settings', $settings );
        }
    }

}