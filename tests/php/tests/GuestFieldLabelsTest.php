<?php
/**
 * Guest Name / Email labels when the form has none saved (the Post Form
 * template stores none): the classic renderer and the React schema both
 * fall back to "Name" / "Email".
 *
 * @package WP_User_Frontend
 */

class GuestFieldLabelsTest extends WP_UnitTestCase {

    private function guest_facts( array $settings ) {
        $method = new ReflectionMethod( \WeDevs\Wpuf\Frontend\Forms\Form_Schema::class, 'guest_facts' );
        $method->setAccessible( true );

        $schema = ( new ReflectionClass( \WeDevs\Wpuf\Frontend\Forms\Form_Schema::class ) )->newInstanceWithoutConstructor();

        return $method->invoke( $schema, $settings );
    }

    public function test_classic_guest_fields_fall_back_to_default_labels() {
        $render = new \WeDevs\Wpuf\Frontend_Render_Form();

        ob_start();
        $render->guest_fields( [ 'email_label' => '' ] );
        $html = ob_get_clean();

        $this->assertStringContainsString( 'Name <span class="required">', $html );
        $this->assertStringContainsString( 'Email <span class="required">', $html );

        ob_start();
        $render->guest_fields( [ 'name_label' => 'Your name', 'email_label' => 'E-Mail' ] );
        $html = ob_get_clean();

        $this->assertStringContainsString( 'Your name <span class="required">', $html );
        $this->assertStringContainsString( 'E-Mail <span class="required">', $html );
    }

    public function test_schema_guest_labels_fall_back_for_missing_and_empty() {
        $facts = $this->guest_facts( [ 'post_permission' => 'guest_post', 'guest_details' => 'on', 'name_label' => '' ] );

        $this->assertSame( 'Name', $facts['name_label'] );
        $this->assertSame( 'Email', $facts['email_label'] );
    }
}
