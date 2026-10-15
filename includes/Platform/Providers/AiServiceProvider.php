<?php
/**
 * AI service provider
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Providers;

use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Admin\Forms\AI_Form_Handler;
use WeDevs\Wpuf\Admin\Screens\AiFormBuilder;
use WeDevs\Wpuf\AI\FormGenerator;
use WeDevs\Wpuf\AI\RestController;
use WeDevs\Wpuf\AI\Services\Field_Options;
use WeDevs\Wpuf\AI\Services\Form_Writer;
use WeDevs\Wpuf\AI\Services\Generation;
use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Platform\Stores\FieldStore;
use WeDevs\Wpuf\Platform\Stores\FormStore;
use WeDevs\Wpuf\Platform\Stores\SettingsStore;
use WeDevs\Wpuf\Platform\ServiceProvider;

/**
 * AI form builder services: the form generator, the frozen
 * `wpuf/v1/ai-form-builder/*` controller (registered once, by REST\Manager),
 * the page handler (Pro's registration list opens the builder through it)
 * and the React screen.
 *
 * The generator and controller are the objects `wpuf()->ai_manager` built
 * (its getters keep returning them); the controller hooks its AJAX action in
 * its constructor, so it must exist once.
 *
 * @since WPUF_SINCE
 */
class AiServiceProvider extends ServiceProvider {

    /**
     * Register the services.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function register() {
        $this->share_tagged(
            FormGenerator::class,
            function () {
                $manager = wpuf()->ai_manager;

                return is_object( $manager ) ? $manager->get_form_generator() : new FormGenerator();
            }
        );

        // The AI services over the provider client and the stores.
        $this->share_tagged(
            Generation::class,
            function ( $container ) {
                return new Generation( $container->get( FormGenerator::class ), $container->get( SettingsStore::class ) );
            }
        );

        $this->share_tagged(
            Provider_Settings::class,
            function ( $container ) {
                return new Provider_Settings( $container->get( FormGenerator::class ), $container->get( SettingsStore::class ) );
            }
        );

        $this->share_tagged(
            Field_Options::class,
            function ( $container ) {
                return new Field_Options( $container->get( FormGenerator::class ) );
            }
        );

        $this->share_tagged(
            Form_Writer::class,
            function ( $container ) {
                return new Form_Writer( $container->get( FormGenerator::class ), $container->get( FormStore::class ), $container->get( FieldStore::class ) );
            }
        );

        // Frozen routes: same paths, arguments, permissions and responses. On the
        // platform REST base (a RestRoute, registered once by REST\Manager).
        $this->share_tagged(
            RestController::class,
            function ( $container ) {
                return new RestController(
                    $container->get( Generation::class ),
                    $container->get( Provider_Settings::class ),
                    $container->get( Field_Options::class ),
                    $container->get( Form_Writer::class )
                );
            }
        );

        // The admin_action handler; Admin pulls it from here (wpuf()->admin->ai_form_handler).
        $this->share_tagged(
            AI_Form_Handler::class,
            function () {
                return new AI_Form_Handler();
            }
        );

        $this->share_tagged(
            AiFormBuilder::class,
            function ( $container ) {
                return new AiFormBuilder( $container->get( BootPayload::class ) );
            }
        );
    }
}
