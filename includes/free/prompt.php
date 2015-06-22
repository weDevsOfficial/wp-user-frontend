<?php

/**
 * Free to Pro prompter class
 *
 * @package WPUF
 */
class WPUF_Pro_Prompt {

    public static function get_pro_prompt() {
        echo '<h3 class="wpuf-pro-text-alert">' . self::get_pro_prompt_text() . '</h3>';
    }

    public static function get_pro_url() {
        return 'https://wedevs.com/products/plugins/wp-user-frontend-pro/?utm_source=freeplugin&utm_medium=prompt&utm_term=wpuf_free_plugin&utm_content=textlink&utm_campaign=pro_prompt';
    }

    public static function get_pro_prompt_text() {
        return sprintf( 'Available in <a href="%s" target="_blank">Pro Version</a>', self::get_pro_url() );
    }
}