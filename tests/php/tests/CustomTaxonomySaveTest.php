<?php
/**
 * FieldableTrait::set_custom_taxonomy(): each taxonomy field saves its own
 * input. A taxonomy that posts nothing used to reuse the previous field's
 * input (a variable kept between loop runs), so a WooCommerce attribute
 * nobody filled was still written to `_product_attributes`.
 *
 * @package WP_User_Frontend
 */
class CustomTaxonomySaveTest extends WP_UnitTestCase {

    public function tear_down() {
        $_POST    = [];
        $_REQUEST = [];
        parent::tear_down();
    }

    private function saver() {
        return new class() {
            use \WeDevs\Wpuf\Traits\FieldableTrait;

            public $form_settings = [ 'post_type' => 'post' ];
        };
    }

    public function test_a_taxonomy_that_posts_nothing_keeps_its_own_terms() {
        register_taxonomy( 'wpuf_genre', 'post', [ 'hierarchical' => true ] );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

        $post_id = self::factory()->post->create();
        $cat     = self::factory()->category->create( [ 'name' => 'News' ] );

        $_POST['category']    = (string) $cat;
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'wpuf_form_add' );

        $saved = $this->saver()->set_custom_taxonomy(
            $post_id,
            [
                [ 'name' => 'category', 'type' => 'select' ],
                [ 'name' => 'wpuf_genre', 'type' => 'checkbox', 'woo_attr' => 'yes', 'woo_attr_vis' => 'yes' ],
            ]
        );

        $this->assertArrayNotHasKey( 'wpuf_genre', $saved, 'an attribute that posted nothing is not added' );
        $this->assertSame( '', get_post_meta( $post_id, '_product_attributes', true ) );

        $this->assertSame( [ $cat ], wp_get_object_terms( $post_id, 'category', [ 'fields' => 'ids' ] ) );
        $this->assertSame( [], wp_get_object_terms( $post_id, 'wpuf_genre', [ 'fields' => 'names' ] ), 'no term made of the category id' );
        $this->assertFalse( (bool) term_exists( (string) $cat, 'wpuf_genre' ) );
    }
}
