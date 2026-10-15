<?php
/**
 * The legacy settings screen, onboarding, the installer and
 * wpuf_update_option() write through the settings store (task 2.4d) with the
 * same stored bytes, and fire `wpuf_settings_saved`.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Onboarding\Wizard;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * Legacy settings path tests.
 */
class SettingsLegacyPathTest extends WP_UnitTestCase {

    /**
     * `wpuf_settings_saved` calls.
     *
     * @var array
     */
    private $fired = [];

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        wpuf_require_once( WPUF_ROOT . '/Lib/WeDevs_Settings_API.php' );
        add_action(
            'wpuf_settings_saved',
            function ( $saved, $incoming, $extra ) {
                $this->fired[] = [ $saved, $incoming, $extra ];
            },
            10,
            3
        );
    }

    public function tear_down() {
        global $pagenow;

        $_POST   = [];
        $pagenow = 'index.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        parent::tear_down();
    }

    /**
     * Posted values for every field of a section, as the legacy form sends them.
     */
    private function posted_section( $fields ) {
        $posted = [];

        foreach ( (array) $fields as $field ) {
            if ( empty( $field['name'] ) ) {
                continue;
            }

            $type = isset( $field['type'] ) ? $field['type'] : 'text';

            if ( in_array( $type, [ 'multicheck', 'multiselect' ], true ) ) {
                $posted[ $field['name'] ] = [ 'a' => 'a', 'b<script>' => 'b<script>' ];
            } elseif ( 'number' === $type ) {
                $posted[ $field['name'] ] = '1e3';
            } else {
                $posted[ $field['name'] ] = "Value <b>x</b><script>y</script> \\ 'q'";
            }
        }

        $posted['unregistered_key'] = '<i>raw</i>';

        return $posted;
    }

    private function legacy_api() {
        $api = new WeDevs_Settings_API();
        $api->set_sections( Stores::settings()->sections() );
        $api->set_fields( Stores::settings()->fields() );

        return $api;
    }

    public function test_legacy_sanitize_matches_the_settings_api_for_every_section() {
        $api    = $this->legacy_api();
        $store  = Stores::settings();
        $checks = 0;

        foreach ( $store->fields() as $section_id => $fields ) {
            $posted = $this->posted_section( $fields );

            $this->assertSame( $api->sanitize_options( $posted ), $store->sanitize_legacy_section( $posted ), $section_id );
            $checks++;
        }

        $this->assertGreaterThan( 5, $checks );
        $this->assertSame( $api->sanitize_options( [] ), $store->sanitize_legacy_section( [] ) );
        $this->assertSame( $api->sanitize_options( '' ), $store->sanitize_legacy_section( '' ) );
    }

    public function test_legacy_screen_filter_is_swapped_and_the_hook_fires_only_for_the_posted_section() {
        global $pagenow;

        $api      = $this->legacy_api();
        $sections = Stores::settings()->sections();
        $api->admin_init();
        Stores::settings()->hook_legacy_screen( $api, $sections );

        $first = $sections[0]['id'];
        $this->assertFalse( has_filter( 'sanitize_option_' . $first, [ $api, 'sanitize_options' ] ), 'settings API sanitizer removed' );
        $this->assertSame( 10, has_filter( 'sanitize_option_' . $first, [ Stores::settings(), 'sanitize_legacy_section' ] ) );

        // A write outside options.php (e.g. a plugin) does not fire the hook.
        $this->fired = [];
        update_option( $first, [ 'probe' => 'one' ] );
        $this->assertSame( [], $this->fired );

        // The options.php save of that section fires it once.
        $pagenow = 'options.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        $_POST   = [ 'option_page' => $first ];
        update_option( $first, [ 'probe' => 'two' ] );
        $this->assertCount( 1, $this->fired );
        $this->assertSame( [ $first => [ 'probe' => 'two' ] ], $this->fired[0][0] );
        $this->assertSame( [], $this->fired[0][2] );

        // options.php posting another section: no hook for this one.
        $this->fired = [];
        $_POST       = [ 'option_page' => 'something_else' ];
        update_option( $first, [ 'probe' => 'three' ] );
        $this->assertSame( [], $this->fired );
    }

    public function test_write_section_and_set_value_store_the_same_bytes_and_fire_once() {
        update_option( 'wpuf_general', [ 'a' => '1' ] );

        $this->fired = [];
        Stores::settings()->write_section( 'wpuf_general', [ 'a' => '1', 'b' => [ 'x' ] ] );
        $this->assertSame( [ 'a' => '1', 'b' => [ 'x' ] ], get_option( 'wpuf_general' ) );
        $this->assertCount( 1, $this->fired );

        // wpuf_update_option(): same result as its old body (get, set key, update).
        update_option( 'wpuf_my_account', 'not an array' );
        wpuf_update_option( 'show_subscriptions', 'wpuf_my_account', 'on' );
        $this->assertSame( [ 'show_subscriptions' => 'on' ], get_option( 'wpuf_my_account' ) );

        delete_option( 'wpuf_payment_invoices' );
        wpuf_update_option( 'enable_invoices', 'wpuf_payment_invoices', 'off' );
        wpuf_update_option( 'show_invoices', 'wpuf_payment_invoices', 'on' );
        $this->assertSame( [ 'enable_invoices' => 'off', 'show_invoices' => 'on' ], get_option( 'wpuf_payment_invoices' ) );
    }

    public function test_onboarding_writes_go_through_the_store() {
        $onboarding = wpuf()->platform()->get( Wizard::class )->handlers();

        update_option( 'wpuf_payment', [ 'currency' => 'USD' ] );
        $this->fired = [];
        $onboarding->toggle_payments( true );

        $this->assertSame( [ 'currency' => 'USD', 'enable_payment' => 'on' ], get_option( 'wpuf_payment' ) );
        $this->assertCount( 1, $this->fired );
        $this->assertSame( [ 'wpuf_payment' ], array_keys( $this->fired[0][0] ) );

        $onboarding->toggle_payments( false );
        $this->assertSame( [ 'currency' => 'USD', 'enable_payment' => 'off' ], get_option( 'wpuf_payment' ) );
    }
}
