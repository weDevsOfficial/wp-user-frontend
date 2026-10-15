<?php
/**
 * The handlers' old "Ajax" class names (their requests are REST routes now)
 * still resolve: class aliases and container keys give the new classes and
 * the same shared objects.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Billing\Billing_Address;
use WeDevs\Wpuf\Frontend\Forms\Post_Form_Handler;
use WeDevs\Wpuf\Frontend\Forms\Upload_Handler;

class LegacyClassAliasTest extends WP_UnitTestCase {

    public function aliases() {
        return [
            'submit handler' => [ 'WeDevs\\Wpuf\\Ajax\\Frontend_Form_Ajax', Post_Form_Handler::class ],
            'upload handler' => [ 'WeDevs\\Wpuf\\Ajax\\Upload_Ajax', Upload_Handler::class ],
            'billing'        => [ 'WeDevs\\Wpuf\\Ajax\\Address_Form_Ajax', Billing_Address::class ],
        ];
    }

    /**
     * @dataProvider aliases
     */
    public function test_old_name_is_the_new_class_and_the_same_service( $old, $new ) {
        $this->assertTrue( class_exists( $old ), $old );
        $this->assertSame( $new, ( new ReflectionClass( $old ) )->getName(), 'the old name is an alias of the new class' );
        $this->assertSame( wpuf()->platform()->get( $new ), wpuf()->platform()->get( $old ) );
        $this->assertInstanceOf( $old, wpuf()->platform()->get( $new ) );
    }

    public function test_old_static_entry_points_still_work() {
        $this->assertTrue( method_exists( 'WeDevs\\Wpuf\\Ajax\\Upload_Ajax', 'attach_html' ) );
        $this->assertTrue( method_exists( 'WeDevs\\Wpuf\\Ajax\\Address_Form_Ajax', 'wpuf_ajax_address_form' ) );
    }
}
