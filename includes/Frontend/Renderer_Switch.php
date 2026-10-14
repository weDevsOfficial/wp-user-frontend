<?php
/**
 * Decides whether a frontend shortcode renders the classic PHP markup or mounts the React app
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend;

/**
 * One decision for the post form, the edit form and the account shortcode
 * (`frontend-react-architecture.md` 4.1). React only when the setting is on,
 * the app is built, the call comes from the shortcode on a page (not the block
 * render callback, not an Elementor widget), no theme overrides the templates
 * that part uses, and the `wpuf_frontend_react_render` filter agrees. Every
 * other case keeps the classic render exactly as before.
 *
 * @since WPUF_SINCE
 */
class Renderer_Switch {

    /**
     * Setting key under `wpuf_general`.
     *
     * @since WPUF_SINCE
     */
    const OPTION = 'frontend_react';

    /**
     * App per part: part => [ app script name, templates a theme can override ].
     *
     * @since WPUF_SINCE
     */
    const PARTS = [
        'post_form'    => [ 'forms', [] ],
        'edit_form'    => [ 'forms', [] ],
        'registration' => [ 'forms', [] ],
        'profile'      => [ 'forms', [] ],
        'account'   => [ 'account', [ 'account.php', 'unauthorized.php', 'submit-post.php', 'dashboard/dashboard.php', 'dashboard/posts.php', 'dashboard/edit-profile.php', 'dashboard/change-password.php', 'dashboard/billing-address.php', 'dashboard/subscription.php' ] ],
    ];

    /**
     * Where the current shortcode call comes from ('shortcode', 'block', 'elementor').
     *
     * @var string
     */
    private static $source = 'shortcode';

    /**
     * Parts rendered as React during this request.
     *
     * @var array
     */
    private $rendered = [];

    /**
     * The part whose React markup is being built right now ('' when none).
     * While set, the classic asset listeners (`wpuf_before_form_render`) stay quiet.
     *
     * @var string
     */
    private $building = '';

    /**
     * Whether the owner switched the React frontend on.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function enabled() {
        $on = wpuf_is_checkbox_or_toggle_on( wpuf_get_option( self::OPTION, 'wpuf_general', 'off' ) );

        return (bool) apply_filters( 'wpuf_frontend_react_enabled', $on );
    }

    /**
     * Whether an app bundle exists on disk (the PHP part ships before the apps).
     *
     * @since WPUF_SINCE
     *
     * @param string $app App name ('forms', 'account')
     *
     * @return bool
     */
    public function app_built( $app ) {
        return file_exists( WPUF_ROOT . '/assets/js/frontend/' . self::bundle( $app ) . '.js' )
            && file_exists( WPUF_ROOT . '/assets/js/frontend/runtime.js' );
    }

    /**
     * The bundle name of an app (the account bundle is `account-react`, so its
     * sheet does not collide with the classic account.css).
     *
     * @since WPUF_SINCE
     *
     * @param string $app App name
     *
     * @return string
     */
    public static function bundle( $app ) {
        return 'account' === $app ? 'account-react' : $app;
    }

    /**
     * Run a shortcode call under a source other than the shortcode itself
     * (the block render callback, an Elementor widget), which keeps it classic.
     *
     * @since WPUF_SINCE
     *
     * @param string   $source  'block' or 'elementor'
     * @param callable $render  Produces the markup
     *
     * @return mixed What $render returned
     */
    public static function with_source( $source, callable $render ) {
        $previous     = self::$source;
        self::$source = (string) $source;

        try {
            return $render();
        } finally {
            self::$source = $previous;
        }
    }

    /**
     * The source of the current shortcode call.
     *
     * @since WPUF_SINCE
     *
     * @return string
     */
    public static function source() {
        return self::$source;
    }

    /**
     * Whether a theme overrides any template of a part (`wpuf_load_template` order).
     *
     * @since WPUF_SINCE
     *
     * @param string $part Part
     *
     * @return bool
     */
    public function has_template_override( $part ) {
        if ( empty( self::PARTS[ $part ][1] ) ) {
            return false;
        }

        $dirs = array_unique( [ get_stylesheet_directory() . '/wpuf/', get_template_directory() . '/wpuf/' ] );

        foreach ( self::PARTS[ $part ][1] as $file ) {
            foreach ( $dirs as $dir ) {
                if ( file_exists( $dir . $file ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * React or classic for this call.
     *
     * @since WPUF_SINCE
     *
     * @param string $part    'post_form', 'edit_form' or 'account'
     * @param int    $form_id Form id (0 for the account)
     * @param array  $context Extra context for the filter
     *
     * @return bool
     */
    public function is_react( $part, $form_id = 0, array $context = [] ) {
        $context = array_merge( [ 'source' => self::$source ], $context );
        $react   = isset( self::PARTS[ $part ] )
            && ! is_admin()
            && 'shortcode' === $context['source']
            && $this->enabled()
            && $this->app_built( self::PARTS[ $part ][0] )
            && ! $this->has_template_override( $part );

        return (bool) apply_filters( 'wpuf_frontend_react_render', $react, $part, (int) $form_id, $context );
    }

    /**
     * The wrapper a React app mounts into, with its boot data and a skeleton,
     * and the app's scripts enqueued. The wrapper keeps the classic wrapper
     * classes so CSS written for them still applies.
     *
     * @since WPUF_SINCE
     *
     * @param string $part     Part
     * @param array  $data     Boot data (JSON for the app)
     * @param string $skeleton Skeleton markup (already escaped)
     * @param string $classes  Wrapper classes
     *
     * @return string
     */
    public function markup( $part, array $data, $skeleton = '', $classes = '' ) {
        $app = isset( self::PARTS[ $part ] ) ? self::PARTS[ $part ][0] : 'forms';

        $this->rendered[ $part ] = true;
        $this->enqueue( $app );

        $id = 'wpuf-' . str_replace( '_', '-', $part ) . '-' . ( isset( $data['id'] ) ? (int) $data['id'] : 0 ) . '-' . wp_unique_id();

        return sprintf(
            '<div id="%1$s" class="wpuf-frontend %2$s" data-wpuf-react="%3$s"><script type="application/json" class="wpuf-boot">%4$s</script>%5$s</div>',
            esc_attr( $id ),
            esc_attr( trim( $classes ) ),
            esc_attr( $app ),
            wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ),
            $skeleton
        );
    }

    /**
     * Build a part's React markup: the classic asset listeners on the hooks
     * the schema captures must not enqueue the classic bundle meanwhile.
     *
     * @since WPUF_SINCE
     *
     * @param string   $part  Part
     * @param callable $build Produces the markup
     *
     * @return mixed What $build returned
     */
    public function build( $part, callable $build ) {
        $previous       = $this->building;
        $this->building = (string) $part;

        try {
            return $build();
        } finally {
            $this->building = $previous;
        }
    }

    /**
     * Whether a React part is being built right now.
     *
     * @since WPUF_SINCE
     *
     * @return bool
     */
    public function building() {
        return '' !== $this->building;
    }

    /**
     * The markup a React form mounts into (post form, edit form, registration,
     * profile): the classic wrapper classes (so CSS written for them still
     * applies), the schema as boot data and a skeleton of the first rows. A
     * closed gate prints the classic notice instead of the app.
     *
     * @since WPUF_SINCE
     *
     * @param array  $schema The form schema (Form_Schema::build())
     * @param string $part   Part
     *
     * @return string
     */
    public function form_markup( array $schema, $part ) {
        if ( empty( $schema['state']['open'] ) ) {
            return '<div class="wpuf-message">' . wp_kses_post( isset( $schema['state']['message'] ) ? $schema['state']['message'] : '' ) . '</div>';
        }

        wp_enqueue_style( 'wpuf-font-awesome' );

        if ( ! empty( $schema['needs']['editor'] ) ) {
            wp_enqueue_editor();
            wp_enqueue_media();
        }

        $layout   = isset( $schema['layout']['layout'] ) ? $schema['layout']['layout'] : 'layout1';
        $position = isset( $schema['layout']['label_position'] ) ? $schema['layout']['label_position'] : 'left';
        $rows     = '';

        // The classic sheets the PHP form loads (Frontend::enqueue_form_assets,
        // Frontend_Render_Form): the React form keeps the classic markup, so
        // the same layout CSS styles it. Profile form templates are Pro's
        // (Profile_Form::react_profile_form).
        wp_enqueue_style( 'wpuf-layout1' );
        wp_enqueue_style( 'wpuf-frontend-forms' );
        wp_enqueue_style( 'wpuf-sweetalert2' );
        wp_enqueue_script( 'wpuf-sweetalert2' );

        if ( 'post' === ( isset( $schema['form_type'] ) ? $schema['form_type'] : 'post' ) && empty( $schema['layout']['use_theme_css'] ) && preg_match( '/^layout[1-5]$/', $layout ) ) {
            wp_enqueue_style( 'wpuf-' . $layout );
        }

        foreach ( array_slice( isset( $schema['fields'] ) ? $schema['fields'] : [], 0, 4 ) as $field ) {
            $rows .= '<li class="wpuf-el wpuf-skeleton-row"><div class="wpuf-label"><span class="wpuf-skeleton wpuf-skeleton-label"></span></div><div class="wpuf-fields"><span class="wpuf-skeleton wpuf-skeleton-input"></span></div></li>';
        }

        $skeleton = sprintf(
            '<form class="wpuf-form-add wpuf-form-%1$s wpuf-form-boot" aria-busy="true"><ul class="wpuf-form form-label-%2$s">%3$s<li class="wpuf-submit"><span class="wpuf-skeleton wpuf-skeleton-button"></span></li></ul></form>',
            esc_attr( $layout ),
            esc_attr( $position ),
            $rows
        );

        return $this->markup( $part, $schema, $skeleton, 'wpuf-form-react' );
    }

    /**
     * Whether a part was rendered as React in this request (the classic
     * assets stay off the page then).
     *
     * @since WPUF_SINCE
     *
     * @param string $part Part, or '' for any
     *
     * @return bool
     */
    public function rendered( $part = '' ) {
        return '' === $part ? ! empty( $this->rendered ) : ! empty( $this->rendered[ $part ] );
    }

    /**
     * Enqueue the runtime and one app (section 10 loading rule: only from here).
     *
     * @since WPUF_SINCE
     *
     * @param string $app App name
     *
     * @return void
     */
    public function enqueue( $app ) {
        wp_enqueue_style( 'wpuf-frontend-react-runtime' );
        wp_enqueue_style( 'wpuf-frontend-react-' . $app );
        wp_enqueue_script( 'wpuf-frontend-' . $app );
        wp_set_script_translations( 'wpuf-frontend-' . $app, 'wp-user-frontend', WPUF_ROOT . '/languages' );

        /**
         * Fires when a frontend React app is enqueued: add-ons enqueue the
         * bundles that register their field components with the kit.
         *
         * @since WPUF_SINCE
         *
         * @param string $app 'forms' | 'account'
         */
        do_action( 'wpuf_frontend_react_fields', $app );
    }
}
