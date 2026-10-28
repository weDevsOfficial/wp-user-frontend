<?php
/**
 * AI Form Builder screen
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Admin\BootPayload;

/**
 * "Create Form with AI" (React). Not a menu page: AI_Form_Handler opens it from
 * the `post_form_template` / `wpuf_profile_form_template` admin actions (URL
 * contract kept), fires `wpuf_load_ai_form_builder_page` (Admin enqueues
 * through enqueue()) and prints it with render().
 *
 * The app reads the `wpufAIFormBuilder` global (same keys as the Vue app, plus
 * the stage keys the old template meant to add) through the
 * `wpuf_ai_form_builder_localize_data` filter.
 *
 * @since WPUF_SINCE
 */
class AiFormBuilder extends Screen {

    /**
     * Script and style handle (kept from the Vue app).
     */
    const HANDLE = 'wpuf-ai-form-builder';

    /**
     * Boot payload printer.
     *
     * @var BootPayload|null
     */
    private $boot;

    /**
     * Localized `wpufAIFormBuilder` data by form type (enqueue()).
     *
     * @var array
     */
    private $localized = [];

    /**
     * Load the AI form builder for a form type on the admin app page, as its
     * page does (`wpuf_load_ai_form_builder_page`), and return the route's
     * window globals. The stage keys come from the route query in the app.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type post|profile
     *
     * @return array
     */
    public function load_in_app_for( $form_type ) {
        /** This action is documented in includes/Admin/Forms/AI_Form_Handler.php */
        do_action( 'wpuf_load_ai_form_builder_page', $form_type );

        return [
            'wpufAIFormBuilder' => isset( $this->localized[ $form_type ] ) ? $this->localized[ $form_type ] : $this->localize_data( $form_type ),
        ];
    }

    /**
     * Constructor.
     *
     * @since WPUF_SINCE
     *
     * @param BootPayload|null $boot Boot payload printer.
     */
    public function __construct( $boot = null ) {
        $this->boot = $boot;
    }

    /**
     * Screen id (set_current_screen() in AI_Form_Handler).
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf-ai-form-generation';
    }

    /**
     * The React screens scope their Tailwind utilities to this class.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function body_class() {
        return 'wpuf-admin-screen wpuf-ai-form-builder-page';
    }

    /**
     * Enqueue the app and localize `wpufAIFormBuilder` for a form type.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type post|profile|registration
     *
     * @return void
     */
    public function enqueue( $form_type = 'post' ) {
        wp_enqueue_script( self::HANDLE );
        wp_enqueue_style( self::HANDLE );

        if ( $this->boot ) {
            $this->boot->attach( $this->slug(), self::HANDLE );
        }

        /**
         * Filter the AI Form Builder localization data.
         *
         * Allows external code to modify or enrich the data passed to the frontend,
         * including custom templates, stages, prompts, or form details.
         *
         * @since 4.2.1
         *
         * @param array $localize_data Localization data array to be passed to wp_localize_script.
         */
        $localize_data = apply_filters( 'wpuf_ai_form_builder_localize_data', $this->localize_data( $form_type ) );

        wp_localize_script( self::HANDLE, 'wpufAIFormBuilder', $localize_data );
        $this->localized[ $form_type ] = $localize_data;

        $body_class = $this->body_class();

        add_filter(
            'admin_body_class',
            function ( $classes ) use ( $body_class ) {
                return trim( $classes . ' ' . $body_class );
            }
        );
    }

    /**
     * The `wpufAIFormBuilder` data: the Vue app's keys, then the stage keys
     * (stage, description, prompt, formId, formTitle, confettiUrl) that the
     * old template added after the data was already printed.
     *
     * @since WPUF_SINCE
     *
     * @param string $form_type post|profile|registration
     *
     * @return array
     */
    public function localize_data( $form_type = 'post' ) {
        $ai_status       = wpuf()->platform()->get( Provider_Settings::class )->status();
        $show_api_status = current_user_can( wpuf_admin_role() );
        $ai_manager      = wpuf()->ai_manager;

        $data = [
            'version'              => WPUF_VERSION,
            'assetUrl'             => WPUF_ASSET_URI,
            'siteUrl'              => site_url(),
            'nonce'                => wp_create_nonce( 'wp_rest' ),
            'rest_url'             => esc_url_raw( rest_url() ),
            'endpoints'            => [
                'generate'     => esc_url_raw( rest_url( 'wpuf/v1/ai-form-builder/generate' ) ),
                'createForm'   => esc_url_raw( rest_url( 'wpuf/v1/ai-form-builder/create-form' ) ),
                'integrations' => esc_url_raw( rest_url( 'wpuf/v1/ai-form-builder/integrations' ) ),
            ],
            'formType'             => $form_type,
            'provider'             => '' !== $ai_status['provider'] ? $ai_status['provider'] : 'openai',
            'model'                => '' !== $ai_status['model'] ? $ai_status['model'] : 'gpt-3.5-turbo',
            'hasApiKey'            => $show_api_status ? $ai_status['has_api_key'] : null,
            'isProActive'          => wpuf_is_pro_active(),
            'temperature'          => $ai_status['temperature'],
            'maxTokens'            => $ai_status['max_tokens'],
            'promptTemplates'      => $ai_manager ? $ai_manager->get_all_prompt_templates() : [],
            'promptAIInstructions' => $ai_manager ? $ai_manager->get_all_prompt_ai_instructions() : [],
            'i18n'                 => [
                'errorTitle'         => __( 'Error', 'wp-user-frontend' ),
                'errorMessage'       => __( 'Something went wrong. Please try again.', 'wp-user-frontend' ),
                'invalidRequest'     => __( 'Invalid Request', 'wp-user-frontend' ),
                'nonFormRequest'     => __( 'I can only help with form creation. Try: "Create a contact form"', 'wp-user-frontend' ),
                'proFieldWarning'    => __( 'Pro Feature Required', 'wp-user-frontend' ),
                'proFieldMessage'    => __( 'This field type requires WP User Frontend Pro. You can continue without it or upgrade to Pro for full functionality.', 'wp-user-frontend' ),
                'continueWithoutPro' => __( 'Continue without Pro', 'wp-user-frontend' ),
                'upgradeToPro'       => __( 'Upgrade to Pro', 'wp-user-frontend' ),
                'tryAgain'           => __( 'Try Again', 'wp-user-frontend' ),
                'close'              => __( 'Close', 'wp-user-frontend' ),
            ],
        ];

        return array_merge( $data, $this->stage_data() );
    }

    /**
     * Stage keys from the request, as the old template read them.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function stage_data() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- AI_Form_Handler verified the nonce; display values only.
        $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
        $read   = function ( $key ) {
            return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
        };
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $stages = [
            'wpuf_ai_form_generating' => 'generating',
            'wpuf_ai_form_success'    => 'success',
        ];

        return [
            'stage'       => isset( $stages[ $action ] ) ? $stages[ $action ] : 'input',
            'description' => $read( 'description' ),
            'prompt'      => $read( 'prompt' ),
            'formId'      => $read( 'form_id' ),
            'formTitle'   => $read( 'form_title' ),
            'confettiUrl' => WPUF_ASSET_URI . '/images/confetti_transparent.gif',
        ];
    }

    /**
     * Print the mount point.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function render() {
        include WPUF_ROOT . '/includes/Admin/template-parts/ai-form-builder.php';
    }
}
