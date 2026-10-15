<?php
/**
 * Promo Pages (free plugin only)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Free;

use WeDevs\Wpuf\Admin\Forms\Post\Templates\Post_Form_Template_WooCommerce;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_Artwork;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_EDD;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_Portfolio;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_Press_Release;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_Professional_Video;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_Testimonial;
use WeDevs\Wpuf\Admin\Forms\Post\Templates\Pro_Form_Preview_Volunteer;

/**
 * The free plugin's promo pages and menus: Registration Forms and Coupons promos, the Pro banner metabox, the Pro form templates and previews.
 *
 * @since WPUF_SINCE Moved out of Free_Loader (same Pro_Prompt base), which keeps the hooks and delegates.
 */
class Promo_Pages extends Pro_Prompt {

    /**
     * The loader: callbacks of other groups are registered on it.
     *
     * @var Free_Loader
     */
    protected $loader;

    /**
     * @since WPUF_SINCE
     *
     * @param Free_Loader $loader The loader.
     */
    public function __construct( Free_Loader $loader ) {
        $this->loader = $loader;
    }

    public function admin_menu_top() {
        // Registration Forms and Modules without Pro: routes of the React admin app.
        if ( wpuf()->platform()->has( \WeDevs\Wpuf\Admin\Screens\Registry::class ) ) {
            $registry = wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Screens\Registry::class );

            $registry->add( new \WeDevs\Wpuf\Admin\Screens\RegistrationPromo() );
            $registry->add( new \WeDevs\Wpuf\Admin\Screens\ModulesPreview() );
        }

        $capability     = wpuf_admin_role();
        $reg_forms_hook = add_submenu_page(
            wpuf()->admin->menu->parent_slug,
            __( 'Registration Forms', 'wp-user-frontend' ),
            __( 'Registration Forms', 'wp-user-frontend' ),
            $capability,
            'wpuf-profile-forms',
            [ $this, 'admin_reg_forms_page' ]
        );
        $modules        = add_submenu_page( wpuf()->admin->menu->parent_slug, __( 'Modules', 'wp-user-frontend' ), __( 'Modules', 'wp-user-frontend' ), $capability, 'wpuf-modules', [ $this->loader, 'modules_preview_page' ] );

        // add this menu to all menu hooks
        wpuf()->admin->menu->add_submenu_hooks( 'registration_forms', $reg_forms_hook );
        wpuf()->admin->menu->add_submenu_hooks( 'modules', $modules );

        add_action( "load-$reg_forms_hook", [ $this, 'reg_form_menu_action' ] );
        add_action( "load-$modules", [ $this->loader, 'module_menu_action' ] );

    }

    /**
     * The action to run just after the menu is created
     *
     * @since 4.0.0
     *
     * @return void
     */
    public function reg_form_menu_action() {
        // Admin app on: the registry runs the load step and opens the route.
        if ( wpuf()->platform()->has( \WeDevs\Wpuf\Admin\Screens\Registry::class ) ) {
            wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Screens\Registry::class )->load( 'wpuf-profile-forms' );
        }

        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_registration_forms' );
    }

    public function admin_menu() {
        if ( 'on' === wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
            $capability  = wpuf_admin_role();
            $coupon_hook = add_submenu_page(
                wpuf()->admin->menu->parent_slug,
                __( 'Coupons', 'wp-user-frontend' ),
                __( 'Coupons', 'wp-user-frontend' ),
                $capability,
                'wpuf_coupon',
                [ $this, 'admin_coupon_page' ],
                4
            );

            // Coupons without Pro: a route of the React admin app.
            if ( wpuf()->platform()->has( \WeDevs\Wpuf\Admin\Screens\Registry::class ) ) {
                $registry = wpuf()->platform()->get( \WeDevs\Wpuf\Admin\Screens\Registry::class );

                $registry->add( new \WeDevs\Wpuf\Admin\Screens\CouponsPromo() );
                add_action(
                    "load-$coupon_hook",
                    function () use ( $registry ) {
                        $registry->load( 'wpuf_coupon' );
                    }
                );
            }
        }
    }

    /**
     * Menu callback of Registration Forms without Pro. Prints nothing: the
     * page's load step (reg_form_menu_action()) redirects to the React
     * route (Screens\RegistrationPromo); the classic PHP promo page is gone.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    public function admin_reg_forms_page() {}

    public function admin_coupon_page() {
        ?>
        <h2><?php esc_html_e( 'Coupons', 'wp-user-frontend' ); ?></h2>

        <div class="wpuf-notice" style="padding: 20px; background: #fff; border: 1px solid #ddd;">
            <p>
                <?php esc_html_e( 'Use Coupon codes for subscription for discounts.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <?php esc_html_e( 'This feature is only available in the Pro Version.', 'wp-user-frontend' ); ?>
            </p>

            <p>
                <a href="<?php echo esc_url( Pro_Prompt::get_pro_url() ); ?>" target="_blank" class="button-primary"><?php esc_html_e( 'Upgrade to Pro Version', 'wp-user-frontend' ); ?></a>
                <a href="https://wedevs.com/docs/wp-user-frontend-pro/subscription-payment/coupons/" target="_blank" class="button"><?php esc_html_e( 'Learn more about Coupons', 'wp-user-frontend' ); ?></a>
            </p>
        </div>

        <?php
    }

    /**
     * Add meta boxes to post form builder
     *
     * @return void
     */
    public function add_meta_box_post() {
        add_meta_box( 'wpuf-metabox-fields-banner', __( 'Upgrade to Pro', 'wp-user-frontend' ), [ $this, 'show_banner_metabox' ], 'wpuf_forms', 'side', 'core' );
    }

    public function show_banner_metabox() {
        printf( 'Upgrade to in <a href="%s" target="_blank">Pro Version</a> to get more fields and features.', esc_url( self::get_pro_url() ) );
    }

    //subscription
    /**
     * Post form templates
     *
     * @since 2.4
     *
     * @param array $integrations
     *
     * @return array
     */
    public function post_form_templates( $integrations ) {
        $integrations['post_form_template_woocommerce'] = new Post_Form_Template_WooCommerce();

        // Enable Events Calendar template with new integration
        if ( class_exists( 'Tribe__Events__Main' ) ) {
            $integrations['post_form_template_events_calendar'] = new \WeDevs\Wpuf\Integrations\Events_Calendar\Templates\Event_Form_Template();
        }

        return $integrations;
    }

    /**
     * Pro form templates for previewing
     *
     * @since 3.6.0
     *
     * @param array $integrations
     *
     * @return array
     */
    public function pro_form_previews( $integrations ) {
        $integrations['WPUF_Pro_Form_Preview_EDD']                = new Pro_Form_Preview_EDD();
        $integrations['WPUF_Pro_Form_Preview_Press_Release']      = new Pro_Form_Preview_Press_Release();
        $integrations['WPUF_Pro_Form_Preview_Professional_Video'] = new Pro_Form_Preview_Professional_Video();
        $integrations['WPUF_Pro_Form_Preview_Artwork']            = new Pro_Form_Preview_Artwork();
        $integrations['WPUF_Pro_Form_Preview_Testimonial']        = new Pro_Form_Preview_Testimonial();
        $integrations['WPUF_Pro_Form_Preview_Portfolio']          = new Pro_Form_Preview_Portfolio();
        $integrations['WPUF_Pro_Form_Preview_Volunteer']          = new Pro_Form_Preview_Volunteer();

        return $integrations;
    }
}
