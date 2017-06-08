<?php
class WPUF_Admin_Form_Handler {

    public function __construct() {
        // post forms list table
        add_action( "load-user-frontend_page_wpuf-post-forms", array( $this, 'post_forms_actions' ) );
        add_action( "load-user-frontend_page_wpuf-profile-forms", array( $this, 'profile_forms_actions' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'removable_query_args', array( $this, 'removable_query_args' ) );
    }

    /**
     * Check current page actions
     *
     * @since 2.5
     *
     * @param  integer $page_id
     * @param  integer $bulk_action
     *
     * @return boolean
     */
    public function verify_current_page_screen( $page_id, $bulk_action ) {

        if ( ! isset( $_GET['_wpnonce'] ) || ! isset( $_GET['page'] ) ) {
            return false;
        }

        if ( $_GET['page'] != $page_id ) {
            return false;
        }

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], $bulk_action ) ) {
            return false;
        }

        return true;
    }

    /**
     * Handle Post Forms list table action
     *
     * @since 2.5
     *
     * @return void
     */
    public function post_forms_actions() {
        // Nonce validation
        if ( ! $this->verify_current_page_screen( 'wpuf-post-forms', 'bulk-post-forms' ) ) {
            return;
        }

        // Check permission if not wpuf admin then go out from here
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'wpuf' ) );
        }


        $post_forms = new WPUF_Admin_Post_Forms_List_Table();
        $action = $post_forms->current_action();

        if ( $action ) {

            $remove_query_args = array(
                '_wp_http_referer', '_wpnonce', 'action', 'id', 'post', 'action2',
            );

            $add_query_args = array();

            switch ( $action ) {
                case 'post_form_search':
                    $redirect = remove_query_arg( array( 'post_form_search' ), $redirect );

                    break;

                case 'trash' :

                    if ( ! empty( $_GET['id'] ) ) {
                        delete_post_meta( $_GET['id'], '_wp_trash_meta_status' );
                        delete_post_meta( $_GET['id'], '_wp_trash_meta_time' );
                        delete_post_meta( $_GET['id'], '_wp_desired_post_slug' );

                        wp_trash_post( $_GET['id']  );

                        $add_query_args['trashed'] = 1;

                    } else if ( ! empty( $_GET['post'] ) ) {
                        foreach ( $_GET['post'] as $post_id ) {
                            delete_post_meta( $post_id, '_wp_trash_meta_status' );
                            delete_post_meta( $post_id, '_wp_trash_meta_time' );
                            delete_post_meta( $post_id, '_wp_desired_post_slug' );

                            wp_trash_post( $post_id  );
                        }

                        $add_query_args['trashed'] = count( $_GET['post'] );
                    }

                    break;

                case 'restore' :
                    if ( !empty( $_GET['id'] ) ) {
                        $trash_meta_status = get_post_meta( $_GET['id'], '_wp_trash_meta_status', true );

                        $args = array(
                            'ID'            => $_GET['id'],
                            'post_status'   => $trash_meta_status,
                        );

                        wp_update_post( $args );

                        $add_query_args['untrashed'] = 1;

                    } else if ( ! empty( $_GET['post'] ) ) {
                        foreach ( $_GET['post'] as $post_id ) {
                            $trash_meta_status = get_post_meta( $post_id, '_wp_trash_meta_status', true );

                            $args = array(
                                'ID'            => $post_id,
                                'post_status'   => $trash_meta_status,
                            );

                            wp_update_post( $args  );

                            $add_query_args['untrashed'] = count( $_GET['post'] );
                        }
                    }

                    break;

                case 'delete' :

                    if ( ! empty( $_GET['id'] ) ) {
                        wp_delete_post( $_GET['id']  );

                        $add_query_args['deleted'] = 1;

                    } else if ( ! empty( $_GET['post'] ) ) {
                        foreach ( $_GET['post'] as $post_id ) {
                            wp_delete_post( $post_id  );
                        }

                        $add_query_args['deleted'] = count( $_GET['post'] );
                    }

                    $add_query_args['post_status'] = 'trash';

                    break;

                case 'duplicate':
                    if ( ! empty( $_GET['id'] ) ) {
                        $add_query_args['duplicated'] = wpuf_duplicate_form( $_GET['id'] );
                    }

                    break;
            }

            $redirect = remove_query_arg( $remove_query_args, wp_unslash( $_SERVER['REQUEST_URI'] ) );

            $redirect = add_query_arg( $add_query_args, $redirect );

            wp_redirect( $redirect );
            exit();
        }
    }

    /**
     * Handle Profile Forms list table action
     *
     * @since 2.5
     *
     * @return void
     */
    public function profile_forms_actions() {
        // Nonce validation
        if ( ! $this->verify_current_page_screen( 'wpuf-profile-forms', 'bulk-profile-forms' ) ) {
            return;
        }

        // Check permission if not wpuf admin then go out from here
        if ( ! current_user_can( wpuf_admin_role() ) ) {
            wp_die( __( 'You do not have sufficient permissions to do this action', 'wpuf' ) );
        }


        $profile_forms = new WPUF_Admin_Profile_Forms_List_Table();
        $action = $profile_forms->current_action();

        if ( $action ) {

            $remove_query_args = array(
                '_wp_http_referer', '_wpnonce', 'action', 'id', 'post', 'action2',
            );

            $add_query_args = array();

            switch ( $action ) {
                case 'profile_form_search':
                    $redirect = remove_query_arg( array( 'profile_form_search' ), $redirect );

                    break;

                case 'trash' :

                    if ( ! empty( $_GET['id'] ) ) {
                        delete_post_meta( $_GET['id'], '_wp_trash_meta_status' );
                        delete_post_meta( $_GET['id'], '_wp_trash_meta_time' );
                        delete_post_meta( $_GET['id'], '_wp_desired_post_slug' );

                        wp_trash_post( $_GET['id']  );

                        $add_query_args['trashed'] = 1;

                    } else if ( ! empty( $_GET['post'] ) ) {
                        foreach ( $_GET['post'] as $post_id ) {
                            delete_post_meta( $post_id, '_wp_trash_meta_status' );
                            delete_post_meta( $post_id, '_wp_trash_meta_time' );
                            delete_post_meta( $post_id, '_wp_desired_post_slug' );

                            wp_trash_post( $post_id  );
                        }

                        $add_query_args['trashed'] = count( $_GET['post'] );
                    }

                    break;

                case 'restore' :
                    if ( !empty( $_GET['id'] ) ) {
                        $trash_meta_status = get_post_meta( $_GET['id'], '_wp_trash_meta_status', true );

                        $args = array(
                            'ID'            => $_GET['id'],
                            'post_status'   => $trash_meta_status,
                        );

                        wp_update_post( $args );

                        $add_query_args['untrashed'] = 1;

                    } else if ( ! empty( $_GET['post'] ) ) {
                        foreach ( $_GET['post'] as $post_id ) {
                            $trash_meta_status = get_post_meta( $post_id, '_wp_trash_meta_status', true );

                            $args = array(
                                'ID'            => $post_id,
                                'post_status'   => $trash_meta_status,
                            );

                            wp_update_post( $args  );

                            $add_query_args['untrashed'] = count( $_GET['post'] );
                        }
                    }

                    break;

                case 'delete' :

                    if ( ! empty( $_GET['id'] ) ) {
                        wp_delete_post( $_GET['id']  );

                        $add_query_args['deleted'] = 1;

                    } else if ( ! empty( $_GET['post'] ) ) {
                        foreach ( $_GET['post'] as $post_id ) {
                            wp_delete_post( $post_id  );
                        }

                        $add_query_args['deleted'] = count( $_GET['post'] );
                    }

                    $add_query_args['post_status'] = 'trash';

                    break;

                case 'duplicate':
                    if ( ! empty( $_GET['id'] ) ) {
                        $add_query_args['duplicated'] = wpuf_duplicate_form( $_GET['id'] );
                    }

                    break;
            }

            $redirect = remove_query_arg( $remove_query_args, wp_unslash( $_SERVER['REQUEST_URI'] ) );

            $redirect = add_query_arg( $add_query_args, $redirect );

            wp_redirect( $redirect );
            exit();
        }
    }

    /**
     * Print notices for WordPress
     *
     * @since 2.5
     *
     * @param  string  $text
     * @param  string  $type
     *
     * @return void
     */
    public function display_notice( $text, $type = 'updated' ) {
        printf( '<div class="%s"><p>%s</p></div>', esc_attr( $type ), $text );
    }

    /**
     * Admin notices
     *
     * @since 2.5
     *
     * @return void
     */
    public function admin_notices() {
        if ( !empty( $_GET['page'] ) && 'wpuf-post-forms' === $_GET['page'] ) {
            if ( !empty( $_GET['trashed'] ) ) {
                $notice = sprintf( _n( '%d form moved to the trash.', '%d forms moved to the trash.', $_GET['trashed'], 'wpuf' ), $_GET['trashed'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['untrashed'] ) ) {
                $notice = sprintf( _n( '%d form restored from the trash.', '%d forms restored from the trash.', $_GET['untrashed'], 'wpuf' ), $_GET['untrashed'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['deleted'] ) ) {
                $notice = sprintf( _n( '%d form permanently deleted.', '%d forms permanently deleted.', $_GET['deleted'], 'wpuf' ), $_GET['deleted'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['duplicated'] ) ) {
                $form_url = admin_url( 'admin.php?page=wpuf-post-forms&action=edit&id=' . $_GET['duplicated'] );
                $notice = sprintf( __( 'Form duplicated successfully. <a href="%s">View form.</a>', 'wpuf' ), $form_url );
                $this->display_notice( $notice );
            }
        }

        if ( !empty( $_GET['page'] ) && 'wpuf-profile-forms' === $_GET['page'] ) {
            if ( !empty( $_GET['trashed'] ) ) {
                $notice = sprintf( _n( '%d form moved to the trash.', '%d forms moved to the trash.', $_GET['trashed'], 'wpuf' ), $_GET['trashed'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['untrashed'] ) ) {
                $notice = sprintf( _n( '%d form restored from the trash.', '%d forms restored from the trash.', $_GET['untrashed'], 'wpuf' ), $_GET['untrashed'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['deleted'] ) ) {
                $notice = sprintf( _n( '%d form permanently deleted.', '%d forms permanently deleted.', $_GET['deleted'], 'wpuf' ), $_GET['deleted'] );
                $this->display_notice( $notice );

            } else if ( !empty( $_GET['duplicated'] ) ) {
                $form_url = admin_url( 'admin.php?page=wpuf-profile-forms&action=edit&id=' . $_GET['duplicated'] );
                $notice = sprintf( __( 'Form duplicated successfully. <a href="%s">View form.</a>', 'wpuf' ), $form_url );
                $this->display_notice( $notice );
            }
        }
    }

    /**
     * Add custom query args to the wp removable query args
     *
     * @since 2.5
     *
     * @param array $args
     *
     * @return array
     */
    public function removable_query_args() {
        $args[] = 'duplicated';

        return $args;
    }
}
