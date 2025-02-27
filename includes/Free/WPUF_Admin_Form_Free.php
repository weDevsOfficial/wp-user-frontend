<?php

namespace WeDevs\Wpuf\Free;

use WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder_Free;

/**
 * Free features for wpuf_forms builder
 *
 * @since 2.5
 */
class WPUF_Admin_Form_Free {

    /**
     * Class constructor
     *
     * @since 2.5
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wpuf_form_builder_init_type_wpuf_forms', [ $this, 'init_free' ] );
    }

    /**
     * Initialize the framework
     *
     * @since 2.5
     *
     * @return void
     */
    public function init_free() {
        // require_once WPUF_ROOT . '/includes/Free/admin/form-builder/class-wpuf-form-builder-Free.php';

        new Admin_Form_Builder_Free();
    }
}
