<?php
/**
 * A post form whose settings were stored empty (a:0:{}) or with empty button
 * texts still renders a labelled submit button, with no PHP warning, on the
 * React form and on the classic renderer (block, Elementor).
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Forms\Form_Schema;

class FormButtonLabelsTest extends WP_UnitTestCase {

    private function form( $settings ) {
        $form_id = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );
        update_post_meta( $form_id, 'wpuf_form_settings', $settings );

        return $form_id;
    }

    public function test_react_schema_falls_back_to_default_labels() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        foreach ( [ [], [ 'submit_text' => '', 'update_text' => '' ] ] as $settings ) {
            $schema = wpuf()->platform()->get( Form_Schema::class )->build( $this->form( $settings ) );

            $this->assertSame( 'Submit', $schema['layout']['submit_text'] );
            $this->assertSame( 'Update', $schema['layout']['update_text'] );
        }

        $schema = wpuf()->platform()->get( Form_Schema::class )->build( $this->form( [ 'submit_text' => 'Create Post' ] ) );
        $this->assertSame( 'Create Post', $schema['layout']['submit_text'] );
    }

    public function test_classic_submit_button_has_a_label_and_no_notice() {
        $render = new \WeDevs\Wpuf\Frontend_Render_Form();

        foreach ( [ [], [ 'submit_text' => '' ] ] as $settings ) {
            ob_start();
            $render->submit_button( $this->form( $settings ), $settings );
            $html = ob_get_clean();

            $this->assertStringContainsString( 'value="Submit"', $html );
        }

        $post_id = self::factory()->post->create();

        ob_start();
        $render->submit_button( $this->form( [] ), [], $post_id );
        $this->assertStringContainsString( 'value="Update"', ob_get_clean() );
    }
}
