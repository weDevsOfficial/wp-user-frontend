<?php
/**
 * Hooks of the classic screens that other plugins may use keep firing on the
 * React screens: the subscription pack form hooks (through the shared
 * Platform\Classic\Hook_Fields bridge, values back in $_POST on save), the
 * setup wizard styles action, and with Pro the modules page data filter and
 * the coupon pack filters.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Classic\Hook_Fields;
use WeDevs\Wpuf\Platform\Stores\Stores;

class ClassicHooksKeptTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
    }

    public function tear_down() {
        foreach ( [ 'wpuf_admin_subscription_detail', 'wpuf_admin_subscription_post_restriction', 'wpuf_admin_subs_nav_tab', 'wpuf_admin_subs_nav_content', 'wpuf_update_subscription_pack', 'save_post', 'wpuf_setup_wizard_styles', 'wpuf_module_localize_param', 'wpuf_get_pack', 'wpuf_get_packs' ] as $hook ) {
            remove_all_actions( $hook );
        }

        unset( $_GET['wpuf_route'] );
        parent::tear_down();
    }

    public function test_hook_fields_render_sanitizes_and_restores_the_post() {
        global $post;

        $before = $post;
        $pack   = get_post( self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] ) );
        $seen   = null;
        $html   = wpuf()->platform()->get( Hook_Fields::class )->render(
            $pack,
            function () use ( &$seen ) {
                global $post;

                $seen = $post->ID;
                echo '<tr><td><input name="qa_field" value="1" onclick="alert(1)"></td></tr><script>alert(1)</script>';
            }
        );

        $this->assertSame( $pack->ID, $seen, 'global $post is the pack while the hooks run' );
        $this->assertSame( $before, $post, 'global $post is restored' );
        $this->assertStringContainsString( 'name="qa_field"', $html );
        $this->assertStringNotContainsString( 'onclick', $html );
        $this->assertStringNotContainsString( '<script', $html );
    }

    public function test_subscription_form_hooks_fire_and_their_values_reach_the_save() {
        $args = [];

        add_action(
            'wpuf_admin_subscription_detail',
            function ( $meta, $recurring, $trial ) use ( &$args ) {
                $args[] = [ 'detail', is_array( $meta ), $recurring, $trial ];
                echo '<tr><th>QA</th><td><input name="qa_extra" value=""></td></tr>';
            },
            10,
            3
        );
        add_action(
            'wpuf_admin_subscription_post_restriction',
            function ( $meta, $post ) use ( &$args ) {
                $args[] = [ 'restriction', $post instanceof WP_Post ];
            },
            10,
            2
        );
        add_action(
            'wpuf_admin_subs_nav_tab',
            function () {
                echo '<li><a href="#qa-pane">QA tab</a></li>';
            }
        );
        add_action(
            'wpuf_admin_subs_nav_content',
            function () {
                echo '<section id="qa-pane"><input name="qa_pane" value="x"></section>';
            }
        );

        $fields = Stores::subscriptions()->legacy_fields( 0 );

        $this->assertSame( [ [ 'detail', true, 'none', 'none' ], [ 'restriction', true ] ], $args );
        $this->assertStringContainsString( 'name="qa_extra"', $fields['rows'] );
        $this->assertStringContainsString( 'QA tab', $fields['html'] );
        $this->assertStringContainsString( 'name="qa_pane"', $fields['html'] );

        $posted = [];
        $packed = [];

        add_action(
            'save_post',
            function ( $id, $post ) use ( &$posted ) {
                if ( 'wpuf_subscription' === $post->post_type ) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing
                    $posted = isset( $_POST['qa_extra'] ) ? wp_unslash( $_POST['qa_extra'] ) : null;
                }
            },
            10,
            2
        );
        add_action(
            'wpuf_update_subscription_pack',
            function ( $id, $data ) use ( &$packed ) {
                $packed = $data;
            },
            10,
            2
        );

        do_action( 'rest_api_init' );
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/wpuf_subscription' );
        $request->set_param(
            'subscription',
            [
                'post_title'  => 'QA classic hooks',
                'post_status' => 'publish',
                'meta_value'  => [],
            ]
        );
        $request->set_param( 'extra', [ 'qa_extra' => 'kept <b>bold</b><script>x</script>' ] );

        $response = rest_do_request( $request );

        $this->assertTrue( $response->get_data()['success'] );
        $this->assertSame( 'kept <b>bold</b>x', $posted, 'save_post listeners read the field from $_POST' );
        $this->assertSame( 'kept <b>bold</b>x', $packed['qa_extra'], 'wpuf_update_subscription_pack gets the posted fields' );
        $this->assertArrayNotHasKey( 'qa_extra', $_POST, '$_POST is restored' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }

    public function test_fields_route_is_admin_only() {
        do_action( 'rest_api_init' );
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );

        $this->assertSame( 403, rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/wpuf_subscription/fields' ) )->get_status() );
    }

    public function test_setup_wizard_styles_fire_on_the_onboarding_route_only() {
        $fired = 0;

        add_action(
            'wpuf_setup_wizard_styles',
            function () use ( &$fired ) {
                ++$fired;
            }
        );

        $screen = new \WeDevs\Wpuf\Admin\Screens\Onboarding();

        $screen->load_in_app();
        do_action( 'admin_head' );
        $this->assertSame( 0, $fired, 'other routes' );

        $_GET['wpuf_route'] = '/onboarding';
        $screen->load_in_app();
        do_action( 'admin_head' );
        $this->assertSame( 1, $fired, 'the onboarding route' );
    }

    public function test_pro_modules_data_filter_and_pack_filters() {
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        add_filter(
            'wpuf_module_localize_param',
            function ( $params ) {
                $params['qa'] = 'yes';

                return $params;
            }
        );

        $globals = ( new \WeDevs\Wpuf\Pro\Platform\Screens\Modules() )->app_globals();

        $this->assertSame( 'yes', $globals['wpuf_module']['qa'] );
        $this->assertArrayHasKey( 'nonce', $globals['wpuf_module'] );

        $pack = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish' ] );

        add_filter(
            'wpuf_get_pack',
            function ( $found, $id ) {
                return [ 'filtered', $id ];
            },
            10,
            2
        );

        $coupon = new \WeDevs\Wpuf\Pro\Admin\Coupon();

        $this->assertSame( [ 'filtered', $pack ], $coupon->get_pack( $pack ) );
        $this->assertContains( $pack, wp_list_pluck( $coupon->get_packs(), 'ID' ) );
    }
}
