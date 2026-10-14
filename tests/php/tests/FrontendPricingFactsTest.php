<?php
/**
 * Pro's pricing fields and math captcha in the React form schema
 * (Field_Facts): drawn by React, defaults on a new entry, stored values
 * mapped back to option keys on edit.
 *
 * @package WP_User_Frontend
 */

class FrontendPricingFactsTest extends WP_UnitTestCase {

    private $options = [
        'first_item'  => 'First Item',
        'second_item' => 'Second Item',
        'third_item'  => 'Third Item',
    ];

    private $prices = [
        'first_item'  => '10',
        'second_item' => '25',
        'third_item'  => '50',
    ];

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        do_action( 'rest_api_init' );
    }

    private function form() {
        $form_id = wpuf_create_sample_form( 'Pricing Form', 'wpuf_forms' );
        $common  = [ 'options' => $this->options, 'prices' => $this->prices, 'is_meta' => 'yes', 'required' => 'no', 'enable_quantity' => 'yes' ];

        wpuf_legacy_insert_form_field( $form_id, array_merge( $common, [ 'template' => 'pricing_checkbox', 'input_type' => 'pricing_checkbox', 'name' => 'pc', 'label' => 'Checkbox', 'selected' => [ 'second_item' ] ] ), null, 10 );
        wpuf_legacy_insert_form_field( $form_id, array_merge( $common, [ 'template' => 'pricing_radio', 'input_type' => 'pricing_radio', 'name' => 'pr', 'label' => 'Radio', 'selected' => '' ] ), null, 11 );
        wpuf_legacy_insert_form_field( $form_id, array_merge( $common, [ 'template' => 'pricing_dropdown', 'input_type' => 'pricing_dropdown', 'name' => 'pd', 'label' => 'Dropdown', 'first' => '' ] ), null, 12 );
        wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'cart_total', 'input_type' => 'cart_total', 'name' => 'total', 'label' => 'Total', 'is_meta' => 'yes', 'show_summary' => 'yes' ], null, 13 );
        wpuf_legacy_insert_form_field( $form_id, [ 'template' => 'math_captcha', 'input_type' => 'math_captcha', 'name' => 'math_captcha', 'label' => 'Captcha', 'required' => 'yes', 'is_meta' => 'no' ], null, 14 );

        return $form_id;
    }

    private function fields( $form_id, $post_id = 0 ) {
        $request = new WP_REST_Request( 'GET', '/wpuf/v1/forms/' . $form_id . '/schema' );

        if ( $post_id ) {
            $request->set_param( 'post_id', $post_id );
        }

        $out = [];

        foreach ( rest_do_request( $request )->get_data()['fields'] as $field ) {
            $out[ $field['template'] ] = $field;
        }

        return $out;
    }

    public function test_pricing_and_math_captcha_are_drawn_by_react_with_their_defaults() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
        $fields = $this->fields( $this->form() );

        // The math captcha is plan-locked without a license (Feature_Lock); checked when present.
        foreach ( [ 'pricing_checkbox', 'pricing_radio', 'pricing_dropdown', 'cart_total', 'math_captcha' ] as $template ) {
            if ( 'math_captcha' === $template && ! isset( $fields[ $template ] ) ) {
                continue;
            }

            $this->assertArrayNotHasKey( 'server_rendered', $fields[ $template ], $template );
            $this->assertArrayNotHasKey( 'html', $fields[ $template ], $template );
        }

        $this->assertSame( [ 'second_item' ], $fields['pricing_checkbox']['value'] );
        $this->assertSame( '', $fields['pricing_radio']['value'] );
        $this->assertSame( 'first_item', $fields['pricing_dropdown']['value'], 'no first option: the select starts on its first option' );
        $this->assertFalse( wp_script_is( 'pricing-fields', 'enqueued' ), 'the jQuery cart script is not loaded for React' );
    }

    public function test_stored_entries_map_back_to_keys_and_quantities() {
        $user = self::factory()->user->create( [ 'role' => 'administrator' ] );
        wp_set_current_user( $user );
        $form_id = $this->form();
        $post_id = self::factory()->post->create( [ 'post_author' => $user ] );

        update_post_meta( $post_id, '_wpuf_form_id', $form_id );
        update_post_meta( $post_id, 'pc', 'first_item | third_item' );
        update_post_meta( $post_id, 'pr', 'Second Item (50)' );
        update_post_meta( $post_id, 'pd', 'third_item' );

        $fields = $this->fields( $form_id, $post_id );

        $this->assertSame( [ 'first_item', 'third_item' ], $fields['pricing_checkbox']['value'] );
        $this->assertSame( 'second_item', $fields['pricing_radio']['value'] );
        $this->assertSame( '2', $fields['pricing_radio']['qty_values'], 'total 50 / price 25' );
        $this->assertSame( 'third_item', $fields['pricing_dropdown']['value'] );
    }
}
