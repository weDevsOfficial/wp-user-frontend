<?php
/**
 * Who may post is checked before plan and payment notices: a visitor who has
 * to log in, or a role the form does not allow, gets the login / role message
 * (React schema and the classic [wpuf_form] render used by the block), not a
 * "buy a plan" notice that would not help.
 *
 * @package WP_User_Frontend
 */

class FormGateOrderTest extends WP_UnitTestCase {

    private $form_id;

    public function set_up() {
        parent::set_up();

        $this->form_id = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        self::factory()->post->create(
            [
                'post_type'    => 'wpuf_input',
                'post_parent'  => $this->form_id,
                'post_status'  => 'publish',
                'post_content' => maybe_serialize(
                    [
                        'input_type' => 'text',
                        'template'   => 'post_title',
                        'name'       => 'post_title',
                        'label'      => 'Title',
                        'is_meta'    => 'no',
                        'id'         => 1,
                    ]
                ),
            ]
        );

        update_post_meta(
            $this->form_id,
            'wpuf_form_settings',
            [
                'post_type'        => 'post',
                'post_permission'  => 'role_base',
                'roles'            => [ 'administrator', 'subscriber' ],
                'message_restrict' => 'Members only, please {login}.',
            ]
        );

        // Stand-in for a plan or payment refusal (is_submission_open() saying no).
        add_filter(
            'wpuf_can_post',
            function () {
                return 'no';
            },
            PHP_INT_MAX
        );
        add_filter(
            'wpuf_addpost_notice',
            function () {
                return 'You must purchase a subscription package before posting';
            },
            PHP_INT_MAX
        );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_can_post' );
        remove_all_filters( 'wpuf_addpost_notice' );

        parent::tear_down();
    }

    private function schema_message() {
        $schema = ( new \WeDevs\Wpuf\Frontend\Forms\Form_Schema() )->build( $this->form_id );

        return wp_json_encode( $schema );
    }

    private function classic_html() {
        return \WeDevs\Wpuf\Frontend\Renderer::with_source(
            'block',
            function () {
                return do_shortcode( '[wpuf_form id="' . $this->form_id . '"]' );
            }
        );
    }

    public function test_role_not_allowed_gets_the_role_message_not_the_plan_notice() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );

        $this->assertStringContainsString( 'You do not have sufficient permissions to access this form.', $this->schema_message() );
        $this->assertStringNotContainsString( 'purchase a subscription', $this->schema_message() );

        $html = $this->classic_html();

        $this->assertStringContainsString( 'You do not have sufficient permissions to access this form.', $html );
        $this->assertStringNotContainsString( 'purchase a subscription', $html );
    }

    public function test_visitor_gets_the_login_message_not_the_plan_notice() {
        wp_set_current_user( 0 );

        $this->assertStringContainsString( 'Members only, please', $this->schema_message() );
        $this->assertStringNotContainsString( 'purchase a subscription', $this->schema_message() );

        $html = $this->classic_html();

        $this->assertStringContainsString( 'Members only, please', $html );
        $this->assertStringNotContainsString( 'purchase a subscription', $html );
    }

    public function test_allowed_role_still_gets_the_plan_notice() {
        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );

        $this->assertStringContainsString( 'purchase a subscription', $this->schema_message() );
        $this->assertStringContainsString( 'purchase a subscription', $this->classic_html() );
    }
}
