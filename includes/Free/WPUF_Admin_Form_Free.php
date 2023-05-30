<?php

namespace Wp\User\Frontend\Free;

use Wp\User\Frontend\Admin\WPUF_Admin_Form_Builder_Free;

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
        add_action( 'wpuf-form-builder-init-type-wpuf_forms', [ $this, 'init_free' ] );
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

        new WPUF_Admin_Form_Builder_Free();
    }
}