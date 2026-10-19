<?php
/**
 * `[wpuf_dashboard]` columns lost in 2021 (meta, category, excerpt) and its
 * row hooks; with Pro: the invoice PDF, the account invoice routes (owner
 * only) and one customer email per payment.
 *
 * @package WP_User_Frontend
 */

class DashboardAndInvoicesTest extends WP_UnitTestCase {

    private $user;

    public function set_up() {
        parent::set_up();
        $this->user = self::factory()->user->create( [ 'role' => 'author' ] );
        wp_set_current_user( $this->user );
    }

    public function tear_down() {
        foreach ( [ 'wpuf_dashboard_head_col', 'wpuf_dashboard_row_col', 'wpuf_account_posts_head_col', 'wpuf_account_posts_row_col', 'pre_wp_mail' ] as $hook ) {
            remove_all_filters( $hook );
        }

        parent::tear_down();
    }

    public function test_dashboard_shortcode_shows_meta_category_and_excerpt_columns_and_fires_both_hooks() {
        $form = self::factory()->post->create( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish' ] );

        self::factory()->post->create(
            [
                'post_type'   => 'wpuf_input',
                'post_parent' => $form,
                'post_status' => 'publish',
                'menu_order'  => 1,
                'post_content' => maybe_serialize(
                    [
                        'input_type' => 'text',
                        'template'   => 'text_field',
                        'name'       => 'qa_org',
                        'label'      => 'Organization',
                        'is_meta'    => 'yes',
                        'id'         => 1,
                    ]
                ),
            ]
        );

        $post = self::factory()->post->create( [ 'post_author' => $this->user, 'post_title' => 'Meta post', 'post_excerpt' => 'Short words' ] );

        update_post_meta( $post, '_wpuf_form_id', $form );
        update_post_meta( $post, 'qa_org', 'Blue <b>Ltd</b>' );

        $fired = [];

        foreach ( [ 'wpuf_dashboard_head_col', 'wpuf_dashboard_row_col', 'wpuf_account_posts_head_col', 'wpuf_account_posts_row_col' ] as $hook ) {
            add_action(
                $hook,
                function () use ( $hook, &$fired ) {
                    $fired[ $hook ] = true;
                }
            );
        }

        $html = do_shortcode( '[wpuf_dashboard meta="qa_org" category="on" excerpt="on"]' );

        $this->assertStringContainsString( '<th>Organization</th>', $html );
        $this->assertStringContainsString( 'Blue &lt;b&gt;Ltd&lt;/b&gt;', $html, 'meta values are escaped' );
        $this->assertStringContainsString( '<th>Category</th>', $html );
        $this->assertStringContainsString( '<th>Excerpt</th>', $html );
        $this->assertStringContainsString( 'Short words', $html );
        $this->assertCount( 4, $fired, 'the dashboard hooks and the account hooks fire' );

        $plain = do_shortcode( '[wpuf_dashboard]' );

        $this->assertStringNotContainsString( '<th>Organization</th>', $plain );
        $this->assertStringNotContainsString( '<th>Category</th>', $plain );
    }

    public function test_invoice_pdf_and_owner_only_routes() {
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        $file = wp_tempnam( 'wpuf-invoice-test' ) . '.pdf';
        $pdf  = new \WeDevs\Wpuf\Pro\Invoices\Invoice_Pdf(
            [
                'title'  => 'Invoice',
                'number' => 'T-1',
                'brand'  => [ 'name' => 'QA Site', 'color' => '#4f46e5' ],
                'to'     => [ 'Buyer' ],
                'items'  => [ [ 'name' => 'Pack', 'qty' => 1, 'amount' => '$5.00' ] ],
                'totals' => [ [ 'label' => 'Total', 'value' => '€5,00 د.إ', 'strong' => true ] ],
            ]
        );

        $pdf->save( $file );

        $this->assertSame( '%PDF', substr( (string) file_get_contents( $file ), 0, 4 ) );
        wp_delete_file( $file );

        global $wpdb;

        ( new \WeDevs\Wpuf\Installer() )->create_tables();
        $wpdb->insert(
            $wpdb->prefix . 'wpuf_transaction',
            [
                'user_id'        => $this->user,
                'status'         => 'completed',
                'cost'           => 5,
                'subtotal'       => 5,
                'pack_id'        => 0,
                'post_id'        => 0,
                'payment_type'   => 'Bank',
                'transaction_id' => 'QA-UNIT-1',
                'created'        => current_time( 'mysql' ),
            ]
        );

        $this->assertSame( '', $wpdb->last_error );

        do_action( 'rest_api_init' );

        $list = rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/account/invoices' ) );

        $this->assertSame( 200, $list->get_status() );
        $this->assertSame( 'QA-UNIT-1', $list->get_data()['items'][0]['transaction'] );
        $this->assertSame( '', $list->get_data()['items'][0]['download'], 'no PDF made for it: no link' );

        wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );

        $this->assertSame( 404, rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/account/invoices/QA-UNIT-1/file' ) )->get_status(), 'another user gets nothing' );
        $this->assertSame( [], rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/account/invoices' ) )->get_data()['items'] );

        wp_set_current_user( 0 );
        $this->assertSame( 401, rest_do_request( new WP_REST_Request( 'GET', '/wpuf/v1/account/invoices' ) )->get_status() );
    }

    public function test_free_sends_no_second_invoice_email_when_pro_invoices_are_on() {
        if ( ! class_exists( 'WP_User_Frontend_Pro' ) ) {
            $this->markTestSkipped( 'Pro is not loaded (set WPUF_TESTS_WITH_PRO=1).' );
        }

        $mails = 0;

        add_filter(
            'pre_wp_mail',
            function () use ( &$mails ) {
                ++$mails;

                return true;
            }
        );

        wpuf()->frontend->payment->payment_notify_user(
            [
                'user_id'        => $this->user,
                'transaction_id' => 'QA-MAIL-1',
                'cost'           => 5,
                'pack_id'        => 1,
            ]
        );

        $this->assertSame( 0, $mails, 'Pro\'s invoice is the customer email' );
    }

    public function test_delete_link_is_refused_while_deleting_is_turned_off() {
        $post = self::factory()->post->create( [ 'post_author' => $this->user ] );

        update_option( 'wpuf_dashboard', [ 'enable_post_del' => 'no' ] );

        $GLOBALS['userdata'] = get_userdata( $this->user );
        $_REQUEST['pid']      = (string) $post;
        $_REQUEST['_wpnonce'] = wp_create_nonce( 'wpuf_del' );

        add_filter(
            'wp_redirect',
            function () {
                throw new Exception( 'deleted and redirected' );
            }
        );

        ob_start();
        wpuf()->frontend->frontend_dashboard->delete_post();
        $html = ob_get_clean();

        unset( $_REQUEST['pid'], $_REQUEST['_wpnonce'] );
        remove_all_filters( 'wp_redirect' );
        delete_option( 'wpuf_dashboard' );

        $this->assertStringContainsString( 'Deleting posts is turned off.', $html );
        $this->assertSame( 'publish', get_post_status( $post ), 'the post is not trashed' );
    }
}
