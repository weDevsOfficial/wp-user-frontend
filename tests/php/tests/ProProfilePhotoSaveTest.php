<?php
/**
 * The Profile Photo of a Pro registration / profile form is kept: the plain
 * custom-field loop no longer overwrites it with an empty value, a removed
 * photo is still cleared. Runs with WPUF_TESTS_WITH_PRO=1.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Pro\Frontend\Profile_Form;

class ProProfilePhotoSaveTest extends WP_UnitTestCase {

    private $meta_vars = [
        [
            'input_type' => 'profile_photo',
            'template'   => 'profile_photo',
            'name'       => 'wpuf_profile_photo',
            'is_meta'    => 'yes',
            'max_size'   => 1024,
        ],
    ];

    public function set_up() {
        parent::set_up();

        if ( ! class_exists( Profile_Form::class ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }
    }

    public function tear_down() {
        $_POST = [];

        parent::tear_down();
    }

    public function test_uploaded_photo_is_kept_and_a_removed_one_cleared() {
        $user  = self::factory()->user->create();
        $photo = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/33772.jpg' );

        $_POST = [ 'wpuf_files' => [ 'wpuf_profile_photo' => [ (string) $photo ] ] ];
        Profile_Form::update_user_meta( $this->meta_vars, $user );

        $this->assertSame( (string) $photo, (string) get_user_meta( $user, 'wpuf_profile_photo', true ) );

        // Saved again without a file: the member removed it.
        $_POST = [];
        Profile_Form::update_user_meta( $this->meta_vars, $user );

        $this->assertSame( '', (string) get_user_meta( $user, 'wpuf_profile_photo', true ) );
    }
}
