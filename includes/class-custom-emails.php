<?php

/**
* Custom Emails Class
*
* @since 2.9
*/
class WPUF_Custom_Emails {

    function __construct() {
        add_filter( 'wp_mail_content_type', array( $this, 'mail_content_type' ), 10, 1 );
        add_filter( 'wp_mail_from', array( $this, 'custom_wp_mail_from' ), 10, 1 );
        add_filter( 'wp_mail_from_name', array( $this, 'custom_wp_mail_from_name' ), 10, 1 );
        add_filter( 'retrieve_password_title', array( $this, 'retrieve_password_title' ), 10, 3 );
        add_filter( 'retrieve_password_message', array( $this, 'replace_retrieve_password_message' ), 10, 4 );
    }

    /**
     *Set mail content type
     */
    public function mail_content_type( $content_type ) {
        $type = wpuf_get_option( 'email_type', 'wpuf_mails' );

        if ( $type != 'html' ) {
            return $content_type = 'text/plain';
        }
        $content_type = 'text/html';

        return $content_type;
    }

    /**
     *Set mail content type
     */
    public function custom_wp_mail_from( $email ) {
        $email = wpuf_get_option( 'from_address', 'wpuf_mails' );
        return $email;
    }

    /**
     *Set mail from name
     */
    public function custom_wp_mail_from_name( $name ) {
        $name = wpuf_get_option( 'from_name', 'wpuf_mails' );
        return $name;
    }

    /**
     *Set reset password title
     */
    public function retrieve_password_title( $title, $user_login, $user_data ) {
        $title = wpuf_get_option( 'reset_email_subject', 'wpuf_mails' );
        return $title;
    }

    /**
     * Returns the message body for the password reset mail.
     * Called through the retrieve_password_message filter.
     *
     * @param string  $message    Default mail message.
     * @param string  $key        The activation key.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     *
     * @return string   The mail message to send.
     */
    public function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {

        $subject    = wpuf_get_option( 'reset_email_subject', 'wpuf_mails' );
        $message    = wpuf_get_option( 'reset_email_body', 'wpuf_mails' );
        $blogname   = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $reset_link = site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );

        $field_search = array( '{username}', '{blogname}', '{password_reset_link}' );

        $field_replace = array(
            $user_login,
            $blogname,
            $reset_link
        );

        $message = str_replace( $field_search, $field_replace, $message );
        $message = get_formatted_mail_body( $message, $subject );

        return $message;

    }

}
