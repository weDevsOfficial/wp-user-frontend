<?php
/**
 * DataStore contract and models (task 5b.5)
 *
 * @package WP_User_Frontend
 */

use WeDevs\Wpuf\Platform\Contracts\DataStore;
use WeDevs\Wpuf\Platform\Models\Form;
use WeDevs\Wpuf\Platform\Models\SubscriptionPack;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * @covers \WeDevs\Wpuf\Platform\Stores\FormStore
 * @covers \WeDevs\Wpuf\Platform\Stores\SubscriptionStore
 * @covers \WeDevs\Wpuf\Platform\Stores\QueriesPosts
 * @covers \WeDevs\Wpuf\Platform\Models\Model
 * @covers \WeDevs\Wpuf\Platform\Models\Form
 * @covers \WeDevs\Wpuf\Platform\Models\SubscriptionPack
 */
class DataStoreTest extends WP_UnitTestCase {

    public function test_entity_stores_implement_the_contract() {
        $this->assertInstanceOf( DataStore::class, Stores::forms() );
        $this->assertInstanceOf( DataStore::class, Stores::subscriptions() );
    }

    public function test_form_store_find_query_count() {
        $post_form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => 'Alpha post form' ] );
        $draft     = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'draft', 'post_title' => 'Beta draft form' ] );
        $reg_form  = self::factory()->post->create( [ 'post_type' => 'wpuf_profile', 'post_status' => 'publish', 'post_title' => 'Alpha registration' ] );
        $page      = self::factory()->post->create( [ 'post_type' => 'page' ] );
        update_post_meta( $post_form, 'wpuf_form_settings', [ 'submit_text' => 'Go' ] );

        $store = Stores::forms();

        $this->assertTrue( $store->exists( $post_form ) );
        $this->assertFalse( $store->exists( $page ) );
        $this->assertNull( $store->find( $page ) );

        $form = $store->find( $post_form );
        $this->assertInstanceOf( Form::class, $form );
        $this->assertSame( $post_form, $form->get_id() );
        $this->assertSame( 'wpuf_forms', $form->get_type() );
        $this->assertSame( 'Alpha post form', $form->get_title() );
        $this->assertSame( [ 'submit_text' => 'Go' ], $form->get_settings() );
        $this->assertSame( [], $store->find( $draft )->get_settings() );

        $this->assertSame( 3, $store->count() );
        $this->assertSame( 2, $store->count( [ 'post_type' => 'wpuf_forms' ] ) );
        $this->assertSame( 1, $store->count( [ 'post_type' => 'wpuf_forms', 'status' => 'publish' ] ) );
        $this->assertSame( 2, $store->count( [ 'search' => 'Alpha' ] ) );
        // A type outside the store falls back to the store's types.
        $this->assertSame( 3, $store->count( [ 'post_type' => 'page' ] ) );

        $page_one = $store->query( [ 'per_page' => 2, 'page' => 1, 'orderby' => 'title', 'order' => 'ASC' ] );
        $page_two = $store->query( [ 'per_page' => 2, 'page' => 2, 'orderby' => 'title', 'order' => 'ASC' ] );
        $this->assertSame( [ 'Alpha post form', 'Alpha registration' ], array_map( function ( $f ) { return $f->get_title(); }, $page_one ) );
        $this->assertSame( [ $draft ], array_map( function ( $f ) { return $f->get_id(); }, $page_two ) );
        $this->assertContains( $reg_form, array_map( function ( $f ) { return $f->get_id(); }, $store->query( [ 'post_type' => 'wpuf_profile' ] ) ) );
    }

    public function test_subscription_store_reads_without_writing() {
        $pack = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'publish', 'post_title' => 'Gold' ] );
        update_post_meta( $pack, '_billing_amount', '19.5' );
        $other = self::factory()->post->create( [ 'post_type' => 'wpuf_subscription', 'post_status' => 'draft', 'post_title' => 'Silver' ] );

        $store = Stores::subscriptions();

        $this->assertNull( $store->read( self::factory()->post->create() ) );

        $model = $store->find( $pack );
        $this->assertInstanceOf( SubscriptionPack::class, $model );
        $this->assertSame( 'Gold', $model->get_title() );
        $this->assertSame( 19.5, $model->get_price() );
        $this->assertSame( '19.5', $model->get_meta( 'billing_amount' ) );

        $this->assertSame( 2, $store->count() );
        $this->assertSame( 1, $store->count( [ 'status' => 'draft' ] ) );
        $this->assertSame( [ $other ], array_map( function ( $p ) { return $p->get_id(); }, $store->query( [ 'status' => 'draft' ] ) ) );

        // Admin\Subscription::get_subscriptions() writes a default _sort_order; the store must not.
        $this->assertSame( '', get_post_meta( $pack, '_sort_order', true ) );
        $this->assertSame( '', get_post_meta( $other, '_sort_order', true ) );
    }

    public function test_model_accessors() {
        $form = new Form( 7, [ 'post_title' => 'T' ] );
        $form->set( 'post_status', 'draft' );

        $this->assertSame( 'draft', $form->get_status() );
        $this->assertSame( 'fallback', $form->get( 'missing', 'fallback' ) );
        $this->assertSame( [ 'id' => 7, 'post_title' => 'T', 'post_status' => 'draft' ], $form->to_array() );
    }
}
