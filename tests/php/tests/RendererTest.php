<?php
/**
 * The React frontend renderer: React by default, classic for theme
 * overrides, the block and Elementor, the admin and unbuilt apps.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Renderer;

class RendererTest extends WP_UnitTestCase {

    private $built = [];

    public function set_up() {
        parent::set_up();
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );

        // Each test starts with empty asset queues (they persist across tests otherwise).
        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles']  = new WP_Styles();
        wpuf()->assets->register_all_scripts();
    }

    public function tear_down() {
        foreach ( $this->built as $file ) {
            if ( file_exists( $file ) ) {
                unlink( $file );
            }
        }

        $this->built = [];
        delete_option( 'wpuf_general' );

        parent::tear_down();
    }

    private function renderer() {
        return wpuf()->platform()->get( Renderer::class );
    }

    /** Pretend the bundles are built (the PHP part ships before the apps). */
    private function build( ...$apps ) {
        $dir = WPUF_ROOT . '/assets/js/frontend';

        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        foreach ( array_merge( [ 'runtime' ], $apps ) as $app ) {
            $file = $dir . '/' . Renderer::bundle( $app ) . '.js';

            if ( ! file_exists( $file ) ) {
                file_put_contents( $file, '/* test stub */' );
                $this->built[] = $file;
            }
        }
    }

    public function test_by_default_with_the_apps_built() {
        $this->build( 'forms', 'account' );

        $this->assertTrue( $this->renderer()->mounts( 'account' ), 'no setting: React is the default' );
        $this->assertTrue( $this->renderer()->mounts( 'post_form' ) );
        $this->assertTrue( $this->renderer()->mounts( 'login' ) );
    }

    public function test_an_app_not_built_stays_classic() {
        $this->assertFalse( $this->renderer()->app_built( 'nope' ) );
        $this->assertSame( 'account-page', Renderer::bundle( 'account' ) );
        $this->assertSame( 'forms', Renderer::bundle( 'forms' ) );
        $this->assertFalse( $this->renderer()->mounts( 'nope' ) );
    }

    public function test_when_built_except_from_block_elementor_forms_or_override() {
        $this->build( 'forms', 'account' );
        $renderer = $this->renderer();

        $this->assertTrue( $renderer->mounts( 'account' ) );
        $this->assertTrue( $renderer->mounts( 'post_form' ) );
        $this->assertTrue( $renderer->mounts( 'edit_form' ) );
        $this->assertFalse( $renderer->mounts( 'unknown_part' ) );

        // Block: the page renders the React form, the editor preview the classic one.
        $this->assertTrue(
            Renderer::with_source(
                'block',
                function () use ( $renderer ) {
                    return $renderer->mounts( 'post_form' );
                }
            )
        );
        $this->assertFalse(
            Renderer::with_source(
                'block-preview',
                function () use ( $renderer ) {
                    return $renderer->mounts( 'post_form' );
                }
            )
        );
        // Elementor: the account widget renders the React app, the form widget the classic form.
        $this->assertTrue(
            Renderer::with_source(
                'elementor',
                function () use ( $renderer ) {
                    return $renderer->mounts( 'account' );
                }
            )
        );
        $this->assertFalse(
            Renderer::with_source(
                'elementor',
                function () use ( $renderer ) {
                    return $renderer->mounts( 'post_form' );
                }
            )
        );
        $this->assertSame( 'shortcode', Renderer::source(), 'the source is restored' );

        $override = get_stylesheet_directory() . '/wpuf/account.php';

        if ( ! is_dir( dirname( $override ) ) ) {
            mkdir( dirname( $override ), 0777, true );
        }

        file_put_contents( $override, '<?php // theme override' );
        $this->built[] = $override;

        $this->assertTrue( $renderer->has_template_override( 'account' ) );
        $this->assertFalse( $renderer->mounts( 'account' ), 'a theme override keeps the classic render' );
        $this->assertTrue( $renderer->mounts( 'post_form' ), 'forms have no overridable templates' );
    }

    public function test_account_shortcode_mounts_the_app_and_editprofile_stays_classic() {
        $this->build( 'forms', 'account' );

        $account = do_shortcode( '[wpuf_account]' );

        $this->assertStringContainsString( 'data-wpuf-mount="account"', $account );
        $this->assertStringContainsString( 'class="wpuf-boot"', $account );
        $this->assertStringContainsString( 'wpuf-account-container wpuf-account-boot', $account, 'the classic container class stays for CSS written against it' );
        $this->assertStringContainsString( '"slug":"dashboard"', $account );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-account', 'enqueued' ) );
        $this->assertTrue( wp_style_is( 'wpuf-frontend-runtime-style', 'enqueued' ) );
        $this->assertTrue( wp_style_is( 'wpuf-frontend-account-style', 'enqueued' ) );
        $this->assertTrue( $this->renderer()->rendered( 'account' ) );

        $classic = do_shortcode( '[wpuf_editprofile]' );

        $this->assertStringNotContainsString( 'data-wpuf-mount', $classic );
        $this->assertStringContainsString( 'wpuf-account-sidebar', $classic );
    }

    public function test_post_form_shortcode_mounts_the_app_with_the_schema() {
        $this->build( 'forms', 'account' );
        $form_id = wpuf_create_sample_form( 'Switch Form', 'wpuf_forms' );

        $html = do_shortcode( '[wpuf_form id="' . $form_id . '"]' );

        $this->assertStringContainsString( 'data-wpuf-mount="forms"', $html );
        $this->assertStringContainsString( 'wpuf-form-add wpuf-form-layout1 wpuf-form-boot', $html );
        $this->assertStringContainsString( '"name":"post_title"', $html );
        $this->assertStringContainsString( '"password_warning_weak":', $html, 'free texts the field components show (no window.wpuf_frontend)' );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-forms', 'enqueued' ) );
        $this->assertFalse( wp_script_is( 'wpuf-frontend-form', 'enqueued' ), 'the classic form bundle stays off' );

        $classic = Renderer::with_source(
            'block-preview',
            function () use ( $form_id ) {
                return do_shortcode( '[wpuf_form id="' . $form_id . '"]' );
            }
        );

        $this->assertStringNotContainsString( 'data-wpuf-mount', $classic );
        $this->assertStringContainsString( 'name="form_id" value="' . $form_id . '"', $classic );
        $this->assertTrue( wp_script_is( 'wpuf-frontend-form', 'enqueued' ), 'a form that stays classic gets the classic bundle' );
    }

    public function test_block_renders_the_react_form_and_the_preview_the_classic_one() {
        $this->build( 'forms', 'account' );
        $form_id = wpuf_create_sample_form( 'Block Form', 'wpuf_forms' );
        $render  = function () use ( $form_id ) {
            return do_shortcode( '[wpuf_form id="' . $form_id . '"]' );
        };

        $this->assertStringContainsString( 'data-wpuf-mount="forms"', Renderer::with_source( 'block', $render ) );

        $preview = Renderer::with_source( 'block-preview', $render );

        $this->assertStringNotContainsString( 'data-wpuf-mount', $preview );
        $this->assertStringContainsString( 'name="form_id" value="' . $form_id . '"', $preview );
    }
}
