<?php
/**
 * EDD Download forms carry the EDD template key, so the template's
 * after-submit step turns the uploads into EDD download files; forms saved
 * with the old wrong key (Events Calendar) are repaired once. Runs with
 * WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Admin\Forms\PostTemplates\Post_Form_Template_EDD;
use WeDevs\Wpuf\Pro\Upgrade\Upgrades;

class ProEddFormTemplateTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( Post_Form_Template_EDD::class ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }
    }

    public function tear_down() {
        delete_option( 'wpuf_pro_edd_form_template_repaired' );

        parent::tear_down();
    }

    public function test_template_saves_its_own_key() {
        $settings = ( new Post_Form_Template_EDD() )->get_form_settings();

        $this->assertSame( 'download', $settings['post_type'] );
        $this->assertSame( 'post_form_template_edd', $settings['form_template'] );
    }

    public function test_uploads_become_edd_download_files() {
        $download = self::factory()->post->create( [ 'post_type' => 'download' ] );
        $file     = self::factory()->attachment->create_object( 'guide.zip', $download, [ 'post_mime_type' => 'application/zip' ] );
        $cover    = self::factory()->attachment->create_object( 'cover.png', $download, [ 'post_mime_type' => 'image/png' ] );

        set_post_thumbnail( $download, $cover );

        ( new Post_Form_Template_EDD() )->update_downloadable_files( $download );

        $files = get_post_meta( $download, 'edd_download_files', true );

        $this->assertCount( 1, $files, 'the Download Image is the cover, not a file to sell' );
        $this->assertSame( wp_get_attachment_url( $file ), $files[0]['file'] );
        $this->assertSame( 'guide.zip', $files[0]['name'] );
    }

    public function test_repair_fixes_only_download_forms_with_the_wrong_key_once() {
        $edd    = self::factory()->post->create( [ 'post_type' => 'wpuf_forms' ] );
        $events = self::factory()->post->create( [ 'post_type' => 'wpuf_forms' ] );

        update_post_meta( $edd, 'wpuf_form_settings', [ 'post_type' => 'download', 'form_template' => 'post_form_template_events_calendar' ] );
        update_post_meta( $events, 'wpuf_form_settings', [ 'post_type' => 'tribe_events', 'form_template' => 'post_form_template_events_calendar' ] );

        $upgrades = new Upgrades();
        $upgrades->repair_edd_form_template();

        $this->assertSame( 'post_form_template_edd', get_post_meta( $edd, 'wpuf_form_settings', true )['form_template'] );
        $this->assertSame( 'post_form_template_events_calendar', get_post_meta( $events, 'wpuf_form_settings', true )['form_template'], 'real event forms keep their key' );
        $this->assertSame( '1', (string) get_option( 'wpuf_pro_edd_form_template_repaired' ) );

        // Runs once: a later wrong key is left alone.
        update_post_meta( $edd, 'wpuf_form_settings', [ 'post_type' => 'download', 'form_template' => 'post_form_template_events_calendar' ] );
        $upgrades->repair_edd_form_template();

        $this->assertSame( 'post_form_template_events_calendar', get_post_meta( $edd, 'wpuf_form_settings', true )['form_template'] );
    }
}
