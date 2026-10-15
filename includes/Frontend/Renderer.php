<?php
/**
 * Mounts the React frontend apps for the WPUF shortcodes
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend;

use WeDevs\Wpuf\Platform\Contracts\App_Renderer;

/**
 * The frontend shortcodes (post form, edit form, registration / profile,
 * login screens, account) render their React app (`frontend-react-architecture.md`
 * 4.1): this class prints the mount with its boot data and enqueues the app.
 * The classic PHP render stays only where it must:
 *
 * - a theme overrides one of the part's templates (`wpuf/<template>` in the
 *   child or parent theme), so a site's own markup keeps working;
 * - the block render callback and the Elementor form widget, which render
 *   their own way (with_source()); the Elementor account widget renders the
 *   React app (REACT_SOURCES);
 * - the admin, and an app whose bundle is not built.
 *
 * @since WPUF_SINCE
 */
class Renderer implements App_Renderer {

    /**
     * App per part: part => [ app script name, templates a theme can override ].
     *
     * @since WPUF_SINCE
     */
    const PARTS = [
        'post_form'      => [ 'forms', [] ],
        'edit_form'      => [ 'forms', [] ],
        'registration'   => [ 'forms', [] ],
        'profile'        => [ 'forms', [] ],
        'login'          => [ 'login', [ 'login-form.php' ] ],
        'lost_password'  => [ 'login', [ 'lost-pass-form.php' ] ],
        'reset_password' => [ 'login', [ 'reset-pass-form.php' ] ],
        'plans'          => [ 'plans', [ 'subscriptions/listing.php', 'subscriptions/pack-details.php' ] ],
        'plan_info'      => [ 'plans', [ 'dashboard/subscription.php' ] ],
        'account'        => [ 'account', [ 'account.php', 'unauthorized.php', 'submit-post.php', 'dashboard/dashboard.php', 'dashboard/posts.php', 'dashboard/edit-profile.php', 'dashboard/change-password.php', 'dashboard/billing-address.php', 'dashboard/subscription.php' ] ],
    ];

    /**
     * Parts that render the React app when called from a source other than
     * the shortcode itself: source => parts.
     *
     * @since WPUF_SINCE
     */
    const REACT_SOURCES = [
        'elementor' => [ 'account' ],
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
     * The bundle name of an app (the account bundle is `account-page`, so its
     * sheet does not collide with the classic account.css).
     *
     * @since WPUF_SINCE
     *
     * @param string $app App name
     *
     * @return string
     */
    public static function bundle( $app ) {
        return 'account' === $app ? 'account-page' : $app;
    }

    /**
     * Run a shortcode call under a source other than the shortcode itself
     * (the block render callback, an Elementor widget), which keeps their own
     * classic render.
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
     * Whether this call renders the React app of a part (else the classic template).
     *
     * @since WPUF_SINCE
     *
     * @param string $part Part (a PARTS key)
     *
     * @return bool
     */
    public function mounts( $part ) {
        $source = self::$source;
        $react  = 'shortcode' === $source
            || ( isset( self::REACT_SOURCES[ $source ] ) && in_array( $part, self::REACT_SOURCES[ $source ], true ) );
        // Elementor renders widgets for its editor on the editor page and over
        // admin-ajax; the preview frame mounts them (Elementor::enqueue_react_apps()).
        $front  = ! is_admin() || 'elementor' === $source;

        return isset( self::PARTS[ $part ] )
            && $react
            && $front
            && $this->app_built( self::PARTS[ $part ][0] )
            && ! $this->has_template_override( $part );
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
            '<div id="%1$s" class="wpuf-frontend %2$s" data-wpuf-mount="%3$s"><script type="application/json" class="wpuf-boot">%4$s</script>%5$s</div>',
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
     * A form that cannot be filled in yet (no fields): a centred card with an
     * icon, a title, the message and, for those who may edit forms, a button
     * to the builder. Same markup as the kit's StateCard.
     *
     * @since WPUF_SINCE
     *
     * @param array $state Schema state: title, message, action (label, url)
     *
     * @return string
     */
    private function state_card( array $state ) {
        $action = '';

        if ( ! empty( $state['action']['url'] ) ) {
            $action = sprintf( '<a class="wpuf-btn wpuf-btn--primary" href="%1$s">%2$s</a>', esc_url( $state['action']['url'] ), esc_html( isset( $state['action']['label'] ) ? $state['action']['label'] : '' ) );
        }

        return sprintf(
            '<div class="wpuf-frontend wpuf-form-gate"><div class="wpuf-state-card wpuf-state-card--empty" role="status"><span class="wpuf-state-card__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg></span><h3 class="wpuf-state-card__title">%1$s</h3><p class="wpuf-state-card__text">%2$s</p>%3$s</div></div>',
            esc_html( isset( $state['title'] ) ? $state['title'] : '' ),
            esc_html( isset( $state['message'] ) ? wp_strip_all_tags( $state['message'] ) : '' ),
            $action // Escaped above.
        );
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
            // The classic wrappers: a closed post form (pack, payment, limit) and the edit gates
            // (locks, pending, disabled) are `wpuf-info` boxes; login / role / profile gates `wpuf-message`.
            $reason = isset( $schema['state']['reason'] ) ? $schema['state']['reason'] : '';
            $post   = 'post' === ( isset( $schema['form_type'] ) ? $schema['form_type'] : 'post' );
            $class  = $post && ! in_array( $reason, [ 'login', 'role', '' ], true ) ? 'wpuf-info' : 'wpuf-message';

            wp_enqueue_style( 'wpuf-frontend-runtime-style' );

            if ( 'empty' === $reason ) {
                return $this->state_card( $schema['state'] );
            }

            // The classic box (its class is what themes and scripts look for) in the kit's notice look.
            return '<div class="wpuf-frontend wpuf-form-gate"><div class="' . esc_attr( $class ) . '">' . wp_kses_post( isset( $schema['state']['message'] ) ? $schema['state']['message'] : '' ) . '</div></div>';
        }

        wp_enqueue_style( 'wpuf-font-awesome' );

        if ( ! empty( $schema['needs']['editor'] ) ) {
            wp_enqueue_editor();
            wp_enqueue_media();
        } elseif ( ! empty( $schema['needs']['editor_lite'] ) ) {
            // TipTap fields only: wp.editor.autop() without TinyMCE.
            wp_enqueue_script( 'editor' );
        }

        $layout   = isset( $schema['layout']['layout'] ) ? $schema['layout']['layout'] : 'layout1';
        $position = isset( $schema['layout']['label_position'] ) ? $schema['layout']['label_position'] : 'left';
        $rows     = '';

        // The classic sheets the PHP form loads (Frontend::enqueue_form_assets,
        // Frontend_Render_Form): the React form keeps the classic markup, so
        // the same layout CSS styles it. Profile form templates are Pro's
        // (Profile_Form::mount_profile_form).
        wp_enqueue_style( 'wpuf-layout1' );
        wp_enqueue_style( 'wpuf-frontend-forms' );
        wp_enqueue_style( 'wpuf-dialog' );
        wp_enqueue_script( 'wpuf-dialog' );

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

        return $this->markup( $part, $schema, $skeleton, 'wpuf-form-root' );
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
        wp_enqueue_style( 'wpuf-frontend-runtime-style' );
        wp_enqueue_style( 'wpuf-frontend-' . $app . '-style' );
        wp_enqueue_script( 'wpuf-frontend-' . $app );

        /**
         * Fires when a frontend React app is enqueued: add-ons enqueue the
         * bundles that register their field components with the kit.
         *
         * @since WPUF_SINCE
         *
         * @param string $app 'forms' | 'account'
         */
        do_action( 'wpuf_frontend_fields', $app );
    }
}
