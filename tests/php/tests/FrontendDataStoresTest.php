<?php
/**
 * The store reads the frontend services use (Form_Schema, Account_Service):
 * each fixture is written with the plain WordPress / plugin functions, so
 * the tests prove the stores read the same keys and give the same values.
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Stores\Stores;
use WeDevs\Wpuf\Platform\Stores\UserStore;

class FrontendDataStoresTest extends WP_UnitTestCase {

    public function test_user_store_comes_from_the_container() {
        $this->assertInstanceOf( UserStore::class, Stores::users() );
        $this->assertSame( Stores::users(), wpuf()->platform()->get( UserStore::class ) );
    }

    public function test_settings_get_matches_wpuf_get_option() {
        update_option( 'wpuf_dashboard', [ 'per_page' => '7', 'enable_post_del' => 'no' ] );

        $this->assertSame( '7', Stores::settings()->get( 'wpuf_dashboard', 'per_page', 5 ) );
        $this->assertSame( wpuf_get_option( 'enable_post_del', 'wpuf_dashboard', 'yes' ), Stores::settings()->get( 'wpuf_dashboard', 'enable_post_del', 'yes' ) );
        $this->assertSame( 'thumbnail', Stores::settings()->get( 'wpuf_dashboard', 'ft_img_size', 'thumbnail' ) );
        $this->assertSame( '', Stores::settings()->get( 'wpuf_missing_section', 'anything' ) );
    }

    public function test_wp_option_reads_core_options() {
        $post_id = self::factory()->post->create();

        stick_post( $post_id );
        update_option( 'users_can_register', 1 );

        $this->assertContains( $post_id, array_map( 'intval', (array) Stores::settings()->wp_option( 'sticky_posts' ) ) );
        $this->assertSame( 1, (int) Stores::settings()->wp_option( 'users_can_register' ) );
        $this->assertSame( 20, (int) Stores::settings()->wp_option( 'wpuf_never_stored_option', 20 ) );
    }

    public function test_status_counts_per_user_and_type() {
        $user  = self::factory()->user->create();
        $other = self::factory()->user->create();

        self::factory()->post->create_many( 2, [ 'post_author' => $user, 'post_status' => 'publish' ] );
        self::factory()->post->create( [ 'post_author' => $user, 'post_status' => 'draft' ] );
        self::factory()->post->create( [ 'post_author' => $user, 'post_status' => 'trash' ] );
        self::factory()->post->create( [ 'post_author' => $user, 'post_status' => 'publish', 'post_type' => 'page' ] );
        self::factory()->post->create( [ 'post_author' => $other, 'post_status' => 'publish' ] );

        $this->assertSame(
            [ 'publish' => 2, 'pending' => 0, 'draft' => 1, 'future' => 0, 'private' => 0 ],
            Stores::submissions()->status_counts( $user, 'post' )
        );
        $this->assertSame( 1, Stores::submissions()->status_counts( $user, 'page' )['publish'] );
    }

    public function test_user_meta_and_photo_ids() {
        $user = self::factory()->user->create();

        update_user_meta( $user, 'company', 'weDevs' );
        update_user_meta( $user, 'wpuf_profile_photo', '41' );
        update_user_meta( $user, 'my_avatar', '42' );
        update_user_meta( $user, 'wpuf_cover_photo', '43' );

        $this->assertSame( 'weDevs', Stores::users()->meta( $user, 'company' ) );
        $this->assertSame( '', Stores::users()->meta( $user, 'never_saved' ) );
        $this->assertSame( 41, Stores::users()->photo_id( $user, 'profile_photo', 'whatever' ) );
        $this->assertSame( 42, Stores::users()->photo_id( $user, 'avatar', 'my_avatar' ) );
        $this->assertSame( 43, Stores::users()->cover_id( $user ) );
        $this->assertSame( 0, Stores::users()->cover_id( self::factory()->user->create() ) );
    }

    public function test_submission_form_id_reads_the_classic_meta() {
        $post_id = self::factory()->post->create();

        update_post_meta( $post_id, '_wpuf_form_id', '77' );

        $this->assertSame( 77, Stores::submissions()->form_id( $post_id ) );
    }
}
