<?php
/**
 * The setup wizard on the platform: the container service, step handlers
 * called with the step's values, the user directory offer, and installing one
 * companion plugin over REST (permissions, unknown plugin, failure recorded).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Onboarding\Step_Input;
use WeDevs\Wpuf\Platform\Onboarding\Wizard;

class OnboardingWizardTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        do_action( 'rest_api_init' );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_onboarding_steps' );
        remove_all_filters( 'wpuf_onboarding_recommended_plugins' );
        remove_all_filters( 'plugins_api' );
        delete_option( 'wpuf_onboarding_plugin_errors' );
        parent::tear_down();
    }

    private function wizard() {
        return wpuf()->platform()->get( Wizard::class );
    }

    private function install( $slug ) {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/onboarding/plugins/install' );
        $request->set_param( 'slug', $slug );

        return rest_get_server()->dispatch( $request );
    }

    public function test_one_shared_wizard() {
        $this->assertSame( $this->wizard(), wpuf()->platform()->get( Wizard::class ) );
        $this->assertSame( $this->wizard()->handlers(), $this->wizard()->handlers() );
    }

    public function test_a_filtered_step_handler_gets_the_step_values() {
        $seen = null;

        add_filter(
            'wpuf_onboarding_steps',
            function ( $steps ) use ( &$seen ) {
                $steps['extra'] = [
                    'label'   => 'Extra',
                    'handler' => function ( Step_Input $input ) use ( &$seen ) {
                        $seen = [ $input->text( 'name' ), $input->flag( 'on' ), $input->keys( 'list' ) ];
                    },
                ];

                return $steps;
            }
        );

        $result = $this->wizard()->run_step( 'extra', [ 'name' => '<b>Ann</b>', 'on' => '1', 'list' => [ 'A b', 'c' ] ] );

        $this->assertSame( [ 'Ann', true, [ 'ab', 'c' ] ], $seen );
        $this->assertSame( '', $result['next'] );
        $this->assertContains( 'extra', $this->wizard()->get_progress()['completed'] );
    }

    public function test_unknown_step_is_a_404() {
        $result = $this->wizard()->run_step( 'nope', [] );

        $this->assertWPError( $result );
        $this->assertSame( 404, $result->get_error_data()['status'] );
    }

    public function test_the_plugins_step_saves_after_its_own_installs_finished_the_list() {
        add_filter( 'wpuf_onboarding_recommended_plugins', '__return_empty_array' );

        $this->assertArrayNotHasKey( 'plugins', $this->wizard()->get_steps(), 'nothing pending: no plugins step on the rail' );

        $result = $this->wizard()->run_step( 'plugins', [ 'plugins' => [] ] );

        $this->assertNotWPError( $result );
        $this->assertSame( 'ready', $result['next'] );
    }

    public function test_free_always_offers_the_directory() {
        if ( wpuf_is_pro_active() ) {
            $this->markTestSkipped( 'Pro: the plan decides (pack.json).' );
        }

        $this->assertTrue( $this->wizard()->directory_available() );
        $this->assertArrayHasKey( 'user_directory', $this->wizard()->get_feature_definitions() );
    }

    public function test_install_needs_install_rights() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

        $this->assertSame( 403, $this->install( 'wemail' )->get_status() );
    }

    public function test_unknown_plugin_is_refused() {
        $this->assertSame( 404, $this->install( 'not-recommended' )->get_status() );
    }

    public function test_a_failed_install_is_reported_and_remembered() {
        add_filter(
            'wpuf_onboarding_recommended_plugins',
            function () {
                return [
                    'qa-missing-plugin' => [
                        'name' => 'QA Missing',
                        'file' => 'qa-missing-plugin/qa-missing-plugin.php',
                        'desc' => '',
                    ],
                ];
            }
        );
        add_filter(
            'plugins_api',
            function () {
                return new WP_Error( 'plugins_api_failed', 'No network in tests' );
            }
        );

        $response = $this->install( 'qa-missing-plugin' );

        $this->assertSame( 500, $response->get_status() );
        $this->assertArrayHasKey( 'QA Missing', $this->wizard()->state()->plugin_errors() );
        $this->assertSame( 'QA Missing', $this->wizard()->data()->plugins()['errors'][0]['name'] );
    }
}
