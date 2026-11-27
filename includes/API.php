<?php

namespace WeDevs\Wpuf;

use WeDevs\Wpuf\Platform\REST\Controllers\FormListController;
use WeDevs\Wpuf\Platform\REST\Controllers\SettingsController;
use WeDevs\Wpuf\Platform\REST\Controllers\SubscriptionController;
use WeDevs\Wpuf\Platform\REST\Manager;
use WeDevs\WpUtils\ContainerTrait;

/**
 * API class.
 *
 * Handle API.
 */
class API {
    use ContainerTrait;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->subscription = new SubscriptionController();
        $this->form_list    = new FormListController();
        $this->settings     = new SettingsController();

        // Routes are registered by the platform REST manager
        // (Platform\REST\Manager) on rest_api_init, once per controller.
    }

    /**
     * The core REST controllers (wpuf()->api->subscription, ->form_list, ->settings).
     *
     * @since WPUF_SINCE
     *
     * @return object[]
     */
    public function controllers() {
        return array_values( $this->container );
    }

    /**
     * API initialization
     *
     * Kept for callers of the old method: forwards to the platform REST manager,
     * which registers each controller once (this loop used to build every
     * controller a second time).
     *
     * @since 4.0.11
     *
     * @return void
     */
    public function init_api() {
        wpuf()->platform()->get( Manager::class )->register_routes();
    }
}
