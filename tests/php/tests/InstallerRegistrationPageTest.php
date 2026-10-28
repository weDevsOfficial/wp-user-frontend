<?php
/**
 * Registration page install: Pro's page is built once, then reused (QA story 01).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Admin_Installer;

/**
 * Admin_Installer::install_registration_page() tests.
 */
class InstallerRegistrationPageTest extends WP_UnitTestCase {

    /**
     * Times the Pro filter ran.
     *
     * @var int
     */
    private $calls = 0;

    public function set_up() {
        parent::set_up();
        $this->calls = 0;
        remove_all_filters( 'wpuf_pro_page_install' );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_pro_page_install' );
        parent::tear_down();
    }

    /**
     * What Pro's Page_Installer does: a new form id and page on every call.
     */
    private function listen_like_pro() {
        add_filter(
            'wpuf_pro_page_install',
            function ( $profile_options ) {
                $this->calls++;
                $page = self::factory()->post->create(
                    [
                        'post_type'    => 'page',
                        'post_content' => '[wpuf_profile type="registration" id="' . ( 900 + $this->calls ) . '"]',
                    ]
                );
                $profile_options['reg_override_page'] = $page;

                return [
                    'profile_options' => $profile_options,
                    'reg_page'        => $page,
                ];
            }
        );
    }

    public function test_without_a_listener_nothing_changes() {
        $data = ( new Admin_Installer() )->install_registration_page( [ 'login_page' => 5 ] );

        $this->assertSame( [ 'login_page' => 5 ], $data['profile_options'] );
        $this->assertFalse( $data['reg_page'] );
    }

    public function test_builds_once_then_reuses() {
        $this->listen_like_pro();
        $installer = new Admin_Installer();

        $first  = $installer->install_registration_page( [] );
        $second = $installer->install_registration_page( $first['profile_options'] );
        $third  = $installer->install_registration_page( [] );

        $this->assertSame( 1, $this->calls, 'Pro builds the page only once' );
        $this->assertSame( $first['reg_page'], $second['reg_page'] );
        $this->assertSame( $first['reg_page'], $third['reg_page'], 'an unset setting finds the page by its shortcode' );
        $this->assertSame( $first['reg_page'], $third['profile_options']['reg_override_page'] );
    }

    public function test_builds_again_when_the_page_is_gone() {
        $this->listen_like_pro();
        $installer = new Admin_Installer();

        $first = $installer->install_registration_page( [] );
        wp_trash_post( $first['reg_page'] );

        $second = $installer->install_registration_page( $first['profile_options'] );

        $this->assertSame( 2, $this->calls );
        $this->assertNotSame( $first['reg_page'], $second['reg_page'] );
    }
}
