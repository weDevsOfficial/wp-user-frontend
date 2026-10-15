<?php
/**
 * Core platform services
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Platform\Onboarding\State;

use WeDevs\Wpuf\Admin\Admin_Installer;

use WeDevs\Wpuf\Admin\Bundles;
use WeDevs\Wpuf\Admin\App\AppPage;
use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Admin\Screens\Registry;
use WeDevs\Wpuf\Builder\HookBridge;
use WeDevs\Wpuf\Builder\HookDeprecations;
use WeDevs\Wpuf\Platform\Http\JsonOutputGuard;
use WeDevs\Wpuf\Platform\ServiceProvider;
use WeDevs\Wpuf\Platform\VersionGuard;

/**
 * Registers the free plugin's core platform services.
 *
 * @since WPUF_SINCE
 */
class CoreServiceProvider extends ServiceProvider {

    /**
     * Register services
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        // Free-only hooks (Free_Loader built them with `new`): the post-lock user profile
        // fields and the Pro-settings cleanup on form saves, one instance each.
        $this->share_tagged(
            \WeDevs\Wpuf\Free\Edit_Profile::class,
            function () {
                return new \WeDevs\Wpuf\Free\Edit_Profile();
            }
        );
        $this->share_tagged(
            \WeDevs\Wpuf\Hooks\Form_Settings_Cleanup::class,
            function () {
                return new \WeDevs\Wpuf\Hooks\Form_Settings_Cleanup();
            }
        );

        // The onboarding wizard's own state (options + the activation redirect).
        $this->share_tagged(
            State::class,
            function () {
                return new State();
            }
        );

        // The page installer (admin notice + request handler): one instance for Admin, Tools and Onboarding.
        $this->share_tagged(
            Admin_Installer::class,
            function () {
                return new Admin_Installer();
            }
        );

        // Older Pro without the React admin: its Vue builder scripts are skipped, one notice.
        $this->share_tagged(
            VersionGuard::class,
            function () {
                return new VersionGuard();
            }
        );

        // Keeps wpuf/v1 REST and wpuf_* AJAX answers valid JSON when other code prints first.
        $this->share_tagged(
            JsonOutputGuard::class,
            function () {
                return new JsonOutputGuard();
            }
        );

        // Admin screens on their menu slugs, notice capture for React screens.
        $this->share_tagged(
            Registry::class,
            function () {
                return new Registry();
            }
        );

        // React admin bundles (handles, deps from *.asset.php), stylesheet switch, body classes.
        $this->share_tagged(
            Bundles::class,
            function () {
                return new Bundles();
            }
        );

        // window.wpufAdmin for the React admin screens.
        $this->share_tagged(
            BootPayload::class,
            function () {
                return new BootPayload();
            }
        );

        // The single React admin app page (one page, hash routes; task 5d).
        $this->share_tagged(
            AppPage::class,
            function ( $container ) {
                return new AppPage( $container->get( Registry::class ), $container->get( BootPayload::class ) );
            }
        );

        // Retired Vue builder hooks: deprecation shims + admin notice (4.4g).
        $this->share_tagged(
            HookDeprecations::class,
            function () {
                return new HookDeprecations();
            }
        );

        // One bridge per builder page: get( HookBridge::class, $form_type, $form_settings ).
        $this->bind_tagged(
            HookBridge::class,
            function ( $container, $form_type = 'post', $form_settings = [] ) {
                return new HookBridge( $form_type, $form_settings );
            }
        );
    }
}
