<?php

function wpuf_upgrade_2_8_update_new_options() {
    $wpuf_general = get_option( 'wpuf_general' );
    switch ( $wpuf_general['admin_access'] ) {
        case 'manage_options':
            $roles = array( 'administrator' => 'administrator' );
            break;

        case 'edit_others_posts':
            $roles = array( 'administrator' => 'administrator', 'editor' => 'editor' );
            break;

        case 'publish_posts':
            $roles = array( 'administrator' => 'administrator', 'editor' => 'editor', 'author' => 'author' );
            break;

        case 'edit_posts':
            $roles = array( 'administrator' => 'administrator', 'editor' => 'editor', 'author' => 'author', 'contributor' => 'contributor' );
            break;

        case 'read':
            $roles = array( 'administrator' => 'administrator', 'editor' => 'editor', 'author' => 'author', 'contributor' => 'contributor', 'subscriber' => 'subscriber' );
            break;

        default:
            $roles = array( 'administrator' => 'administrator', 'editor' => 'editor', 'author' => 'author', 'contributor' => 'contributor', 'subscriber' => 'subscriber' );
            break;
    }

    $wpuf_general['show_admin_bar'] = $roles;

    update_option( 'wpuf_general', $wpuf_general );
}
wpuf_upgrade_2_8_update_new_options();
