<?php
/**
 * Which editor a rich field gets in the React form (Form_Schema::editor_engine())
 * and what the page loads for it.
 *
 * @package WP_User_Frontend
 */

/**
 * @covers \WeDevs\Wpuf\Frontend\Forms\Form_Schema
 */
class FrontendEditorEngineTest extends WP_UnitTestCase {

    public function set_up() {
        parent::set_up();
        do_action( 'rest_api_init' );
    }

    public function tear_down() {
        remove_all_filters( 'mce_buttons' );
        remove_all_filters( 'teeny_mce_buttons' );
        remove_all_filters( 'wpuf_frontend_editor_engine' );
        parent::tear_down();
    }

    private function schema( $post_id = 0 ) {
        $form_id = wpuf_create_sample_form( 'Editor Form', 'wpuf_forms' );

        if ( $post_id ) {
            update_post_meta( $post_id, '_wpuf_form_id', $form_id );
        }

        $request = new WP_REST_Request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' );

        if ( $post_id ) {
            $request->set_param( 'post_id', $post_id );
        }

        return rest_do_request( $request )->get_data();
    }

    private function content_field( array $schema ) {
        foreach ( $schema['fields'] as $field ) {
            if ( 'post_content' === $field['template'] ) {
                return $field;
            }
        }

        return [];
    }

    private function own_post( $content ) {
        $user = self::factory()->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $user );

        return self::factory()->post->create( [ 'post_author' => $user, 'post_content' => $content ] );
    }

    public function test_known_toolbar_and_empty_post_get_tiptap_without_tinymce() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $schema = $this->schema();

        $this->assertSame( 'tiptap', $this->content_field( $schema )['editor']['engine'] );
        $this->assertTrue( $schema['needs']['editor_lite'] );
        $this->assertArrayNotHasKey( 'editor', $schema['needs'] );
    }

    public function test_a_plugin_tinymce_button_keeps_tinymce() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        $add = function ( $buttons ) {
            $buttons[] = 'my_plugin_button';

            return $buttons;
        };
        add_filter( 'mce_buttons', $add );
        add_filter( 'teeny_mce_buttons', $add );

        $schema = $this->schema();

        $this->assertSame( 'tinymce', $this->content_field( $schema )['editor']['engine'] );
        $this->assertTrue( $schema['needs']['editor'] );
    }

    public function test_stored_content_loads_tinymce_as_the_fallback() {
        $schema = $this->schema( $this->own_post( '<p>Saved by the classic editor.</p>' ) );

        $this->assertSame( 'tiptap', $this->content_field( $schema )['editor']['engine'] );
        $this->assertTrue( $schema['needs']['editor'], 'TinyMCE is on the page in case the content does not round-trip' );
    }

    public function test_block_editor_markup_opens_in_tinymce() {
        $schema = $this->schema( $this->own_post( "<!-- wp:paragraph -->\n<p>Block</p>\n<!-- /wp:paragraph -->" ) );

        $this->assertSame( 'tinymce', $this->content_field( $schema )['editor']['engine'] );
    }

    public function test_more_and_nextpage_tags_stay_in_tiptap() {
        $schema = $this->schema( $this->own_post( "<p>Teaser</p>\n<!--more-->\n<p>Rest</p>\n<!--nextpage-->\n<p>Two</p>" ) );

        $this->assertSame( 'tiptap', $this->content_field( $schema )['editor']['engine'] );
    }

    public function test_the_engine_filter_wins() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
        add_filter(
            'wpuf_frontend_editor_engine',
            function () {
                return 'tinymce';
            }
        );

        $this->assertSame( 'tinymce', $this->content_field( $this->schema() )['editor']['engine'] );
    }
}
