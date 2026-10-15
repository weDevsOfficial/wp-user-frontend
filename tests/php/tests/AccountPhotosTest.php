<?php
/**
 * Account header photos: `wpuf/v1/account/avatar|cover` are a Pro feature
 * (403 without Pro), remove clears the user meta the profile / cover photo
 * fields write and deletes only the user's own attachment.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Frontend\Account\Account_Photos;

class AccountPhotosTest extends WP_UnitTestCase {

    /**
     * @var int
     */
    private $user;

    public function set_up() {
        parent::set_up();

        $this->user = self::factory()->user->create( [ 'role' => 'subscriber' ] );
        wp_set_current_user( $this->user );
        do_action( 'rest_api_init' );
    }

    public function tear_down() {
        remove_all_filters( 'wpuf_account_photos_editable' );
        parent::tear_down();
    }

    private function request( $method, $route ) {
        return rest_get_server()->dispatch( new WP_REST_Request( $method, '/wpuf/v1' . $route ) );
    }

    public function test_service_comes_from_the_container() {
        $this->assertInstanceOf( Account_Photos::class, wpuf()->platform()->get( Account_Photos::class ) );
    }

    public function test_routes_need_pro() {
        add_filter( 'wpuf_account_photos_editable', '__return_false' );

        foreach ( [ '/account/avatar', '/account/cover' ] as $route ) {
            $this->assertSame( 403, $this->request( 'DELETE', $route )->get_status(), $route );
            $this->assertSame( 403, $this->request( 'POST', $route )->get_status(), $route );
        }

        $profile = wpuf()->platform()->get( \WeDevs\Wpuf\Frontend\Account\Account_Service::class )->profile();
        $this->assertFalse( $profile['photos_editable'] );
    }

    public function test_remove_clears_the_meta_and_deletes_only_own_attachments() {
        add_filter( 'wpuf_account_photos_editable', '__return_true' );

        $own   = self::factory()->attachment->create( [ 'post_author' => $this->user ] );
        $other = self::factory()->attachment->create( [ 'post_author' => self::factory()->user->create() ] );

        update_user_meta( $this->user, 'wpuf_profile_photo', $own );
        update_user_meta( $this->user, 'wpuf_cover_photo', $other );

        $profile = $this->request( 'DELETE', '/account/avatar' )->get_data()['profile'];
        $this->assertSame( '', get_user_meta( $this->user, 'wpuf_profile_photo', true ) );
        $this->assertNull( get_post( $own ), 'own photo deleted' );
        $this->assertFalse( $profile['avatar']['custom'] );
        $this->assertTrue( $profile['photos_editable'] );

        $this->assertSame( 200, $this->request( 'DELETE', '/account/cover' )->get_status() );
        $this->assertSame( '', get_user_meta( $this->user, 'wpuf_cover_photo', true ) );
        $this->assertNotNull( get_post( $other ), 'someone else\'s attachment is kept' );
    }

    public function test_upload_without_a_file_is_refused() {
        add_filter( 'wpuf_account_photos_editable', '__return_true' );

        $response = $this->request( 'POST', '/account/cover' );

        $this->assertSame( 400, $response->get_status() );
        $this->assertSame( 'wpuf_account_no_file', $response->get_data()['code'] );
    }
}
