<?php
/**
 * Admin_Subscription split: facade hooks, groups, store reads
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Admin\Admin_Subscription;
use WeDevs\Wpuf\Admin\Subscriptions\Pack_Fields;
use WeDevs\Wpuf\Admin\Subscriptions\Pack_Screen;
use WeDevs\Wpuf\Admin\Subscriptions\User_Profile;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * The facade keeps every hook on itself; the classic post type screens redirect to the React screen; the sort order migration writes through the subscription store.
 */
class AdminSubscriptionSplitTest extends WP_UnitTestCase {

    /**
     * Hooks the constructor adds (hook, method).
     *
     * @var array
     */
    const HOOKS = [
        [ 'wpuf_subscription_additional_fields', 'third_party_cpt_options' ],
        [ 'show_user_profile', 'profile_subscription_details' ],
        [ 'edit_user_profile', 'profile_subscription_details' ],
        [ 'personal_options_update', 'profile_subscription_update' ],
        [ 'edit_user_profile_update', 'profile_subscription_update' ],
        [ 'load-edit.php', 'redirect_classic_screen' ],
        [ 'load-post.php', 'redirect_classic_screen' ],
        [ 'load-post-new.php', 'redirect_classic_screen' ],
        [ 'wpuf_load_subscription_page', 'remove_notices' ],
        [ 'wpuf_load_subscription_page', 'enqueue_admin_scripts' ],
        [ 'admin_init', 'set_default_sort_order_for_existing_subscriptions' ],
    ];

    /**
     * Hooks of the removed classic pack editor, no longer added.
     *
     * @var array
     */
    const REMOVED_HOOKS = [
        [ 'manage_wpuf_subscription_posts_columns', 'subscription_columns_head' ],
        [ 'post_updated_messages', 'form_updated_message' ],
        [ 'add_meta_boxes', 'add_meta_boxes' ],
        [ 'admin_notices', 'add_help_link' ],
        [ 'wpuf_load_subscription_page', 'modify_admin_footer_text' ],
    ];

    /**
     * Every hook stays on the facade with a facade callable, and every public method is delegated to a group.
     */
    public function test_facade_keeps_the_hooks_and_every_public_method() {
        $admin = new Admin_Subscription();

        foreach ( self::HOOKS as $hook ) {
            $this->assertNotFalse( has_action( $hook[0], [ $admin, $hook[1] ] ), $hook[0] . ' -> ' . $hook[1] );
            remove_action( $hook[0], [ $admin, $hook[1] ] );
        }

        foreach ( self::REMOVED_HOOKS as $hook ) {
            $this->assertFalse( method_exists( $admin, $hook[1] ), $hook[1] . ' is gone with the classic editor' );
        }

        $groups = [];

        foreach ( [ Pack_Fields::class, Pack_Screen::class, User_Profile::class ] as $group ) {
            foreach ( ( new ReflectionClass( $group ) )->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {
                if ( '__construct' !== $method->getName() ) {
                    $groups[ $method->getName() ] = $group;
                }
            }
        }

        foreach ( ( new ReflectionClass( Admin_Subscription::class ) )->getMethods( ReflectionMethod::IS_PUBLIC ) as $method ) {
            if ( '__construct' !== $method->getName() ) {
                $this->assertArrayHasKey( $method->getName(), $groups, $method->getName() . ' has a group' );
            }
        }

        // 25 of the old class, less the 10 classic editor methods, plus the classic screen redirect and its route,
        // plus remove_user_pack() (the REST route admin/users/{id}/pack).
        $this->assertCount( 18, $groups );
    }

    /**
     * The classic post type screens map to the React screen's routes; other requests and post.php actions stay.
     */
    public function test_classic_screens_map_to_the_routes() {
        $screen = new Pack_Screen( ( new ReflectionClass( Admin_Subscription::class ) )->newInstanceWithoutConstructor() );
        $pack   = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] );
        $page   = self::factory()->post->create( [ 'post_type' => 'page' ] );
        $route  = function ( $file, $get ) use ( $screen ) {
            $_GET = $get;

            return $screen->classic_screen_route( $file );
        };

        $this->assertSame( '/subscriptions', $route( 'edit.php', [ 'post_type' => 'wpuf_subscription' ] ) );
        $this->assertSame( '/subscriptions?post_status=trash', $route( 'edit.php', [ 'post_type' => 'wpuf_subscription', 'post_status' => 'trash' ] ) );
        $this->assertSame( '/subscriptions', $route( 'edit.php', [ 'post_type' => 'wpuf_subscription', 'post_status' => 'bogus' ] ) );
        $this->assertSame( '/subscriptions?action=new', $route( 'post-new.php', [ 'post_type' => 'wpuf_subscription' ] ) );
        $this->assertSame( '/subscriptions?action=edit&id=' . $pack, $route( 'post.php', [ 'post' => (string) $pack, 'action' => 'edit' ] ) );

        $this->assertNull( $route( 'edit.php', [ 'post_type' => 'wpuf_subscription', 'page' => 'wpuf_subscribers' ] ), 'the Subscribers page has its own redirect' );
        $this->assertNull( $route( 'edit.php', [ 'post_type' => 'post' ] ) );
        $this->assertNull( $route( 'edit.php', [] ) );
        $this->assertNull( $route( 'post.php', [ 'post' => (string) $pack, 'action' => 'trash' ] ), 'post.php actions other than edit run' );
        $this->assertNull( $route( 'post.php', [ 'post' => (string) $page, 'action' => 'edit' ] ) );
        $this->assertNull( $route( 'upload.php', [ 'post_type' => 'wpuf_subscription' ] ) );

        $_GET = [];
    }

    /**
     * The sort order migration sets 1 on packs without an order, through the store, once (transient).
     */
    public function test_sort_order_migration_runs_through_the_store_once() {
        delete_transient( 'wpuf_sort_order_migration_done' );
        $admin   = ( new ReflectionClass( Admin_Subscription::class ) )->newInstanceWithoutConstructor();
        $missing = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] );
        $zero    = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] );
        $kept    = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription' ] );
        update_post_meta( $zero, '_sort_order', 0 );
        update_post_meta( $kept, '_sort_order', 7 );

        $admin->set_default_sort_order_for_existing_subscriptions();

        $this->assertSame( '1', get_post_meta( $missing, '_sort_order', true ) );
        $this->assertSame( '1', get_post_meta( $zero, '_sort_order', true ) );
        $this->assertSame( '7', get_post_meta( $kept, '_sort_order', true ) );
        $this->assertTrue( (bool) get_transient( 'wpuf_sort_order_migration_done' ) );
        $this->assertSame( 0, Stores::subscriptions()->set_default_sort_order(), 'nothing left to migrate' );
        delete_transient( 'wpuf_sort_order_migration_done' );
    }
}
