<?php

namespace Wp\User\Frontend;

class Admin {
    function __construct() {
        wpuf()->add_to_container( 'menu', new Admin\Menu() );

        wpuf()->add_to_container( 'form_template', new Admin\PostFormTemplates\WPUF_Admin_Form_Template() );
        wpuf()->add_to_container( 'admin_form', new Admin\WPUF_Admin_Form() );

        // bind the tasks that needs to be done after menu is created. for hook sequence purpose
        // add_action( 'admin_init', [ $this, 'admin_init' ] );
    }

    public function admin_init() {

    }
}
