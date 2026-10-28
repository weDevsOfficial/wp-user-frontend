<?php
/**
 * Server-side checks of a form submission: required fields, e-mail, URL,
 * number range; conditional and passive fields are not checked.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Forms\Submission_Validator;

class SubmissionValidatorTest extends WP_UnitTestCase {

    private function field( $name, $template, array $extra = [] ) {
        return array_merge(
            [
                'name'       => $name,
                'template'   => $template,
                'input_type' => 'text',
                'label'      => ucfirst( str_replace( '_', ' ', $name ) ),
                'required'   => 'yes',
                'wpuf_cond'  => [ 'condition_status' => 'no' ],
            ],
            $extra
        );
    }

    public function test_required_text_rejects_missing_blank_and_spaces_but_accepts_zero() {
        $validator = new Submission_Validator();
        $fields    = [ $this->field( 'post_title', 'post_title' ) ];

        $this->assertSame( 'Post title is required.', $validator->validate( $fields, [] ) );
        $this->assertSame( 'Post title is required.', $validator->validate( $fields, [ 'post_title' => '   ' ] ) );
        $this->assertSame( '', $validator->validate( $fields, [ 'post_title' => '0' ] ) );
        $this->assertSame( '', $validator->validate( $fields, [ 'post_title' => 'Hello' ] ) );
    }

    public function test_select_placeholder_and_empty_arrays_are_empty() {
        $validator = new Submission_Validator();

        $this->assertSame( 'Category is required.', $validator->validate( [ $this->field( 'category', 'taxonomy', [ 'input_type' => 'taxonomy' ] ) ], [ 'category' => '-1' ] ) );
        $this->assertSame( 'Colors is required.', $validator->validate( [ $this->field( 'colors', 'checkbox_field', [ 'input_type' => 'checkbox' ] ) ], [ 'colors' => [ '', '' ] ] ) );
        $this->assertSame( '', $validator->validate( [ $this->field( 'colors', 'checkbox_field', [ 'input_type' => 'checkbox' ] ) ], [ 'colors' => [ 'red' ] ] ) );
    }

    public function test_optional_fields_pass_empty_but_formats_are_checked_when_filled() {
        $validator = new Submission_Validator();
        $email     = $this->field( 'contact', 'email_address', [ 'input_type' => 'email', 'required' => 'no' ] );
        $url       = $this->field( 'site', 'website_url', [ 'input_type' => 'url', 'required' => 'no' ] );

        $this->assertSame( '', $validator->validate( [ $email, $url ], [] ) );
        $this->assertSame( 'Contact: please enter a valid e-mail address.', $validator->validate( [ $email ], [ 'contact' => 'not-an-email' ] ) );
        $this->assertSame( 'Site: please enter a valid URL.', $validator->validate( [ $url ], [ 'site' => 'not a url' ] ) );
        $this->assertSame( '', $validator->validate( [ $email, $url ], [ 'contact' => 'a@example.com', 'site' => 'https://example.com' ] ) );
    }

    /**
     * Register a field type the way Pro does (Pro is not loaded in these tests).
     */
    private function with_field_type( $type, callable $run ) {
        $manager  = wpuf()->fields;
        $manager->get_fields();
        $property = new ReflectionProperty( $manager, 'fields' );
        $property->setAccessible( true );
        $saved = $property->getValue( $manager );

        $property->setValue( $manager, array_merge( $saved, [ $type => new stdClass() ] ) );

        try {
            $run();
        } finally {
            $property->setValue( $manager, $saved );
        }
    }

    public function test_unregistered_field_types_are_not_required() {
        $validator = new Submission_Validator();
        $logo      = $this->field( 'logo', 'file_upload', [ 'input_type' => 'file_upload', 'required' => 'yes' ] );

        $this->assertSame( '', $validator->validate( [ $logo ], [] ), 'a Pro field while Pro is off is not on the form, so it can not be required' );

        $this->with_field_type(
            'file_upload',
            function () use ( $validator, $logo ) {
                $this->assertNotSame( '', $validator->validate( [ $logo ], [] ), 'registered (Pro on): required again' );
            }
        );
    }

    public function test_number_range() {
        $this->with_field_type( 'numeric_text_field', [ $this, 'number_range_checks' ] );
    }

    public function number_range_checks() {
        $validator = new Submission_Validator();
        $number    = $this->field( 'age', 'numeric_text_field', [ 'input_type' => 'numeric_text', 'min_value_field' => '18', 'max_value_field' => '0' ] );

        $this->assertSame( 'Age: please enter a number.', $validator->validate( [ $number ], [ 'age' => 'abc' ] ) );
        $this->assertSame( 'Age: the value must be at least 18.', $validator->validate( [ $number ], [ 'age' => '12' ] ) );
        $this->assertSame( '', $validator->validate( [ $number ], [ 'age' => '99' ] ), 'max 0 means no maximum' );
    }

    public function test_uploads_conditional_passive_and_column_children() {
        $validator = new Submission_Validator();
        $image     = $this->field( 'photo', 'image_upload', [ 'input_type' => 'image_upload' ] );

        $this->assertSame( 'Photo is required.', $validator->validate( [ $image ], [ 'wpuf_files' => [ 'photo' => [] ] ] ) );
        $this->assertSame( '', $validator->validate( [ $image ], [ 'wpuf_files' => [ 'photo' => [ '12' ] ] ] ) );

        $conditional = $this->field( 'reason', 'text_field', [ 'wpuf_cond' => [ 'condition_status' => 'yes' ] ] );
        $this->assertSame( '', $validator->validate( [ $conditional, $this->field( 'note', 'custom_html' ) ], [] ) );

        $column = [
            'name'         => 'col',
            'template'     => 'column_field',
            'inner_fields' => [ 'column-1' => [ $this->field( 'inner_text', 'text_field' ) ], 'column-2' => [] ],
        ];
        $this->assertSame( 'Inner text is required.', $validator->validate( [ $column ], [] ) );
    }

    public function test_fields_inside_a_conditional_column_are_not_required() {
        $validator = new Submission_Validator();
        $column    = [
            'name'         => 'video_col',
            'template'     => 'column_field',
            'wpuf_cond'    => [ 'condition_status' => 'yes' ],
            'inner_fields' => [ 'column-1' => [ $this->field( 'video_url', 'text_field' ) ], 'column-2' => [] ],
        ];

        $this->assertSame( '', $validator->validate( [ $column ], [] ), 'the column was hidden by its own condition' );
    }

    public function test_required_content_with_only_media_is_filled() {
        $validator = new Submission_Validator();
        $content   = [ $this->field( 'post_content', 'post_content', [ 'input_type' => 'textarea' ] ) ];

        $this->assertSame( '', $validator->validate( $content, [ 'post_content' => '<p><img src="https://example.com/a.jpg" alt="" /></p>' ] ) );
        $this->assertSame( '', $validator->validate( $content, [ 'post_content' => '<figure><iframe src="https://example.com/v"></iframe></figure>' ] ) );
        $this->assertSame( '', $validator->validate( $content, [ 'post_content' => '[gallery ids="1,2"]' ] ) );
        $this->assertSame( 'Post content is required.', $validator->validate( $content, [ 'post_content' => '<p> </p>' ] ) );
    }

    public function test_fields_hidden_by_visibility_are_not_required() {
        $validator = new Submission_Validator();
        $members   = $this->field( 'members_note', 'text_field', [ 'css' => '', 'wpuf_visibility' => [ 'selected' => 'logged_in', 'choices' => [] ] ] );

        wp_set_current_user( 0 );
        $this->assertSame( '', $validator->validate( [ $members ], [] ), 'a visitor never saw the logged-in-only field' );

        wp_set_current_user( self::factory()->user->create() );
        $this->assertSame( 'Members note is required.', $validator->validate( [ $members ], [] ) );
    }

    public function test_render_fields_filter_shapes_the_checked_list() {
        $validator = new Submission_Validator();
        $drop      = function ( $fields ) {
            return array_values( array_filter( $fields, function ( $field ) { return 'extra' !== $field['name']; } ) );
        };

        add_filter( 'wpuf_render_fields', $drop );
        $error = $validator->validate( [ $this->field( 'extra', 'text_field' ) ], [], 123 );
        remove_filter( 'wpuf_render_fields', $drop );

        $this->assertSame( '', $error, 'a field the filter removed from the form is not required' );
    }
}
