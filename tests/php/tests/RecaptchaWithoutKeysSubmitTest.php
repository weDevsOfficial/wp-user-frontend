<?php
/**
 * A post form with a reCaptcha field on a site without reCaptcha keys (the
 * Press Release template, for one) is sent like the login form: the field
 * shows "API key is missing" and guards nothing. With the keys set, leaving
 * the token out is still refused.
 *
 * @package WP_User_Frontend
 */

class RecaptchaWithoutKeysSubmitTest extends WP_UnitTestCase {

    private $form_id;

    public function set_up() {
        parent::set_up();

        global $wp_rest_server;
        $wp_rest_server = new WP_REST_Server();
        do_action( 'rest_api_init' );

        $this->form_id = wpuf_create_sample_form( 'Captcha Form', 'wpuf_forms' );

        // As the template stores it: no recaptcha_type.
        self::factory()->post->create(
            [
                'post_type'    => 'wpuf_input',
                'post_parent'  => $this->form_id,
                'post_status'  => 'publish',
                'menu_order'   => 99,
                'post_content' => maybe_serialize(
                    [
                        'input_type' => 'recaptcha',
                        'template'   => 'recaptcha',
                        'label'      => 'Spam Protection',
                        'name'       => '',
                        'is_meta'    => 'no',
                        'id'         => 99,
                    ]
                ),
            ]
        );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
    }

    public function tear_down() {
        delete_option( 'wpuf_general' );

        parent::tear_down();
    }

    private function submit() {
        $request = new WP_REST_Request( 'POST', '/wpuf/v1/forms/' . $this->form_id . '/submissions' );

        foreach (
            [
                'post_title'   => 'Press release',
                'post_content' => 'Body',
                'page_id'      => 0,
                'wpuf_nonce'   => wp_create_nonce( 'wpuf_form_add' ),
            ] as $key => $value
        ) {
            $request->set_param( $key, $value );
        }

        return rest_do_request( $request );
    }

    public function test_submit_works_when_the_site_has_no_recaptcha_keys() {
        $response = $this->submit();

        $this->assertSame( 200, $response->get_status(), wp_json_encode( $response->get_data() ) );
    }

    public function test_missing_token_is_refused_when_the_keys_are_set() {
        update_option( 'wpuf_general', [ 'recaptcha_public' => 'pub', 'recaptcha_private' => 'priv' ] );
        add_filter(
            'pre_http_request', function () {
                return [ 'body' => wp_json_encode( [ 'success' => false ] ), 'response' => [ 'code' => 200 ] ];
            }
        );

        $response = $this->submit();

        $this->assertSame( 400, $response->get_status() );
    }
}
