<?php
/**
 * Post forms screen (list and builder)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Screens;

use WeDevs\Wpuf\AI\Services\Provider_Settings;
use WeDevs\Wpuf\Admin\BootPayload;
use WeDevs\Wpuf\Admin\Forms\Admin_Form_Builder;
use WeDevs\Wpuf\Admin\Forms\Template_Picker;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * User Frontend > Post Forms: the React forms list, or the form builder for
 * `action=edit|add-new`.
 *
 * @since WPUF_SINCE
 */
class PostFormsList extends Screen {

    use PrintsNotices;

    /**
     * App load group of the builder routes.
     */
    const BUILDER_GROUP = 'wpuf-post-forms-builder';

    /**
     * App load group of the AI form builder route.
     */
    const AI_GROUP = 'wpuf-post-forms-ai';

    /**
     * Stylesheets the load hook enqueued (load_once()), null before it ran.
     *
     * @var string[]|null
     */
    private $loaded_styles = null;

    /**
     * Menu slug
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function slug() {
        return 'wpuf-post-forms';
    }

    /**
     * The list shows notices; the builder removes them (Admin_Form_Builder).
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function captures_notices() {
        return ! in_array( $this->action(), [ 'edit', 'add-new' ], true );
    }

    /**
     * Load step: the `wpuf_load_post_forms` hook (builder init and list actions listen).
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_post_forms' );
    }

    /**
     * The list's script and styles.
     *
     * @return void
     */
    private function enqueue_list_assets() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-forms-list' );
        wp_enqueue_script( 'wpuf-forms-list-screen' );
    }

    /**
     * The list's window globals: `wpuf_forms_list` and `wpuf_form_templates`
     * (the template picker of "Add New").
     *
     * @return array
     */
    private function list_globals() {
        $ai_configured = wpuf()->platform()->get( Provider_Settings::class )->status()['configured'];
        $in_app        = function_exists( 'wpuf_is_admin_app' ) && wpuf_is_admin_app();

        return [
            'wpuf_forms_list'     => [
                'post_counts'            => wpuf_get_forms_counts_with_status(),
                'rest_nonce'             => wp_create_nonce( 'wp_rest' ),
                'rest_url'               => esc_url_raw( rest_url() ),
                'template_nonce'         => wp_create_nonce( 'wpuf_create_from_template' ),
                'is_plain_permalink'     => empty( Stores::settings()->wp_option( 'permalink_structure' ) ),
                'permalink_settings_url' => admin_url( 'options-permalink.php' ),
                'ai_configured'          => $ai_configured,
                // In the admin app: the settings route (no page load).
                'ai_settings_url'        => $in_app ? wpuf_admin_app_url( '/settings', [ 'hash' => 'wpuf_ai' ] ) : admin_url( 'admin.php?page=wpuf-settings#wpuf_ai' ),
            ],
            'wpuf_form_templates' => Template_Picker::data(
                [
                    'form_type'      => 'post',
                    'registry'       => wpuf_get_post_form_templates(),
                    'pro_templates'  => wpuf_get_pro_form_previews(),
                    'action_name'    => 'post_form_template',
                    // In the admin app: the new form route (no page load).
                    'blank_form_url' => $in_app ? wpuf_admin_app_url( '/post-forms/new' ) : admin_url( 'admin.php?page=wpuf-post-forms&action=add-new' ),
                ]
            ),
        ];
    }

    /**
     * The `action` query value.
     *
     * @return string|null
     */
    private function action() {
        // phpcs:ignore WordPress.Security.NonceVerification
        return ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;
    }

    /**
     * Admin app routes: the post forms list, the builder and the AI form
     * builder (task 5d).
     *
     * @since WPUF_SINCE
     *
     * @return array[]
     */
    public function app_routes() {
        return [
            [
                'id'        => 'post-forms',
                'path'      => '/post-forms',
                'title'     => __( 'Post Forms', 'wp-user-frontend' ),
                'app'       => 'forms-list',
                'boot'      => 'post_forms',
                'in_app'    => true,
                'menuLink'  => true,
                'container' => 'wpuf-post-forms-list-table-view',
                'containerClass' => 'wpuf-h-100vh wpuf-bg-white wpuf-ml-[-20px] wpuf-py-0 wpuf-px-[20px]',
                'notices'   => true,
                'page'      => 'admin.php?page=wpuf-post-forms',
            ],
            [
                'id'          => 'post-form-new',
                'path'        => '/post-forms/new',
                'title'       => __( 'Edit Form', 'wp-user-frontend' ),
                'app'         => 'form-builder',
                'boot'        => 'form_builder',
                'in_app'      => true,
                'group'       => self::BUILDER_GROUP,
                'formType'    => 'wpuf_forms',
                'menuPath'    => '/post-forms',
                'container'   => 'wpuf-form-builder-route',
                'bodyClasses' => [ 'wpuf-builder-screen' ],
                'page'        => 'admin.php?page=wpuf-post-forms&action=add-new',
            ],
            [
                'id'          => 'post-form-edit',
                'path'        => '/post-forms/:id/edit',
                'title'       => __( 'Edit Form', 'wp-user-frontend' ),
                'app'         => 'form-builder',
                'boot'        => 'form_builder',
                'in_app'      => true,
                'group'       => self::BUILDER_GROUP,
                'formType'    => 'wpuf_forms',
                'menuPath'    => '/post-forms',
                'container'   => 'wpuf-form-builder-route',
                'bodyClasses' => [ 'wpuf-builder-screen' ],
                'page'        => 'admin.php?page=wpuf-post-forms&action=edit&id=:id',
            ],
            [
                'id'        => 'post-form-submissions',
                'path'      => '/post-forms/:id/submissions',
                'title'     => __( 'Submissions', 'wp-user-frontend' ),
                'app'       => 'forms-list',
                'boot'      => 'post_forms',
                'in_app'    => true,
                'menuPath'  => '/post-forms',
                'container' => 'wpuf-post-forms-list-table-view',
                'containerClass' => 'wpuf-h-100vh wpuf-bg-white wpuf-ml-[-20px] wpuf-py-0 wpuf-px-[20px]',
                'notices'   => true,
                'page'      => 'admin.php?page=wpuf-post-forms',
            ],
            [
                'id'          => 'post-forms-ai',
                'path'        => '/post-forms/ai',
                'title'       => __( 'AI Form Builder', 'wp-user-frontend' ),
                'app'         => 'ai-form-builder',
                'boot'        => 'wpuf-ai-form-generation',
                'in_app'      => true,
                'group'       => self::AI_GROUP,
                'formType'    => 'post',
                'menuPath'    => '/post-forms',
                'container'   => 'wpuf-ai-form-builder',
                'bodyClasses' => [ 'wpuf-ai-form-builder-page' ],
                'page'        => 'admin.php?action=post_form_template&template=ai_form&_wpnonce=' . wp_create_nonce( 'wpuf_create_from_template' ),
            ],
        ];
    }

    /**
     * App route of a post forms page request: the list (its row and bulk
     * actions ran in the load step and redirected with their notice) or a
     * form's builder.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public function app_route_for_request() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view selection.
        $id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;

        if ( 'edit' === $this->action() ) {
            return $id ? '/post-forms/' . $id . '/edit' : '/post-forms';
        }

        // add-new: the load step created the form and redirected to its builder.
        return 'add-new' === $this->action() ? '' : '/post-forms';
    }

    /**
     * On the app page: the load hook (as on the list page) and the list's assets.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function load_in_app() {
        $this->load_once();
        $this->enqueue_list_assets();
    }

    /**
     * Stylesheets the forms' load hook enqueues for the builder (legacy
     * field previews, jQuery widgets, Pro's unscoped Tailwind 3 base) that
     * the React list never uses. The list route drops them: switched on with
     * the list they restyled the admin menu for a moment on every visit.
     */
    const BUILDER_ONLY_STYLES = [
        'jquery-ui',
        'wpuf-selectize',
        'wpuf-intlTelInput',
        'wpuf-css-stars',
        'wpuf-tax',
        'wpuf-frontend-forms',
        'wpuf-font-awesome',
        'wpuf-tooltip',
        'wpuf-form-builder',
        'wpuf-admin-form-builder',
        'wpuf-admin-form-builder-pro',
    ];

    /**
     * App load groups: the list, and the builder (its own stylesheets; any
     * form's data comes over REST when its builder opens).
     *
     * @since WPUF_SINCE
     *
     * @return callable[]
     */
    public function app_groups() {
        return [
            $this->slug()       => function () {
                $this->load_in_app();
                // Builder sheets belong to the builder group only (the list page
                // printed `wpuf-admin-form-builder` without its file anyway).
                array_map( 'wp_dequeue_style', self::BUILDER_ONLY_STYLES );

                return $this->app_globals();
            },
            self::BUILDER_GROUP => function () {
                $this->load_once();

                return Admin_Form_Builder::enqueue_app_assets();
            },
            self::AI_GROUP      => function () {
                return wpuf()->platform()->get( AiFormBuilder::class )->load_in_app_for( 'post' );
            },
        ];
    }

    /**
     * The load hook once per request; later calls enqueue the stylesheets it
     * enqueued again (each app group starts from the page's style queue).
     *
     * @return void
     */
    private function load_once() {
        if ( null !== $this->loaded_styles ) {
            array_map( 'wp_enqueue_style', $this->loaded_styles );

            return;
        }

        $before = wp_styles()->queue;
        $this->load();
        $this->load_page_extras();
        $this->loaded_styles = array_values( array_diff( wp_styles()->queue, $before ) );
    }

    /**
     * What the post forms page got from Form_Template on its own screen (its
     * screen check does not see the app page): the builder's stylesheet
     * dependencies, and LearnPress's admin sheet taken off (it clashes with
     * the builder).
     *
     * @return void
     */
    private function load_page_extras() {
        array_map( 'wp_enqueue_style', (array) wpuf()->assets->form_builder_css_deps );

        add_action(
            'admin_enqueue_scripts',
            function () {
                wp_deregister_style( 'learn-press-admin' );
            },
            99
        );
    }

    /**
     * Window globals of the list route.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function app_globals() {
        return $this->list_globals();
    }
}
