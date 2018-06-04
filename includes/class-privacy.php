<?php
/**
 * Class WPUF_Privacy
 *
 * Add Exporters and Erasers to WP data exporter
 *
 * @since 2.8.9
 */
Class WPUF_Privacy {

    private $name = "WP User Frontend";

    public function __construct(){
        add_action( 'admin_init', array( $this, 'add_privacy_message' ) );
        add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 10 );
//        add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ), 10 );

        add_filter( 'wpuf_privacy_user_data', array( $this, 'export_billing_address' ), 5, 3 );
    }

    function add_privacy_message(){
        if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
            $content = $this->get_privacy_message();
            wp_add_privacy_policy_content( $this->name, $content );
        }
    }

    /**
     * Add privacy policy content for the privacy policy page.
     */
    function get_privacy_message() {
        $content = '
			<div contenteditable="false">' .
            '<p class="wp-policy-help">' .
            __( 'This sample language includes the basics around what personal data your site may be collecting, storing and sharing, as well as who may have access to that data. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your site will vary. We recommend consulting with a lawyer when deciding what information to disclose on your privacy policy.', 'wpuf' ) .
            '</p>' .
            '</div>' .
            '<p>' . __( 'We collect information about you during the checkout process on our site.', 'wpuf' ) . '</p>' .
            '<h2>' . __( 'What we collect and store', 'wpuf' ) . '</h2>' .
            '<p>' . __( 'While you visit our , we’ll track:', 'wpuf' ) . '</p>' .
            '<ul>' .
            '<li>' . __( 'Products you’ve viewed:  we’ll use this to, for example, show you products you’ve recently viewed', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Location, IP address and browser type: we’ll use this for purposes like estimating taxes and shipping', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Shipping address: we’ll ask you to enter this so we can, for instance, estimate shipping before you place an order, and send you the order!', 'wpuf' ) . '</li>' .
            '</ul>' .
            '<p>' . __( 'We’ll also use cookies to keep track of cart contents while you’re browsing our .', 'wpuf' ) . '</p>' .
            '<div contenteditable="false">' .
            '<p class="wp-policy-help">' . __( 'Note: you may want to further detail your cookie policy, and link to that section from here.', 'wpuf' ) . '</p>' .
            '</div>' .
            '<p>' . __( 'When you purchase from us, we’ll ask you to provide information including your name, billing address, shipping address, email address, phone number, credit card/payment details and optional account information like username and password. We’ll use this information for purposes, such as, to:', 'wpuf' ) . '</p>' .
            '<ul>' .
            '<li>' . __( 'Send you information about your account and order', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Respond to your requests, including refunds and complaints', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Process payments and prevent fraud', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Set up your account for our site', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Comply with any legal obligations we have, such as calculating taxes', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Improve our site offerings', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Send you marketing messages, if you choose to receive them', 'wpuf' ) . '</li>' .
            '</ul>' .
            '<p>' . __( 'If you create an account, we will store your name, address, email and phone number, which will be used to populate the checkout for future orders.', 'wpuf' ) . '</p>' .
            '<p>' . __( 'We generally store information about you for as long as we need the information for the purposes for which we collect and use it, and we are not legally required to continue to keep it. For example, we will store order information for XXX years for tax and accounting purposes. This includes your name, email address and billing and shipping addresses.', 'wpuf' ) . '</p>' .
            '<p>' . __( 'We will also store comments or reviews, if you choose to leave them.', 'wpuf' ) . '</p>' .
            '<h2>' . __( 'Who on our team has access', 'wpuf' ) . '</h2>' .
            '<p>' . __( 'Members of our team have access to the information you provide us. For example, both Administrators and Shop Managers can access:', 'wpuf' ) . '</p>' .
            '<ul>' .
            '<li>' . __( 'Order information like what was purchased, when it was purchased and where it should be sent, and', 'wpuf' ) . '</li>' .
            '<li>' . __( 'Customer information like your name, email address, and billing and shipping information.', 'wpuf' ) . '</li>' .
            '</ul>' .
            '<p>' . __( 'Our team members have access to this information to help fulfill orders, process refunds and support you.', 'wpuf' ) . '</p>' .
            '<h2>' . __( 'What we share with others', 'wpuf' ) . '</h2>' .
            '<div contenteditable="false">' .
            '<p class="wp-policy-help">' . __( 'In this section you should list who you’re sharing data with, and for what purpose. This could include, but may not be limited to, analytics, marketing, payment gateways, shipping providers, and third party embeds.', 'wpuf' ) . '</p>' .
            '</div>' .
            '<p>' . __( 'We share information with third parties who help us provide our orders and store services to you; for example --', 'wpuf' ) . '</p>' .
            '<h3>' . __( 'Payments', 'wpuf' ) . '</h3>' .
            '<div contenteditable="false">' .
            '<p class="wp-policy-help">' . __( 'In this subsection you should list which third party payment processors you’re using to take payments on your site since these may handle customer data. We’ve included PayPal as an example, but you should remove this if you’re not using PayPal.', 'wpuf' ) . '</p>' .
            '</div>' .
            '<p>' . __( 'We accept payments through PayPal. When processing payments, some of your data will be passed to PayPal, including information required to process or support the payment, such as the purchase total and billing information.', 'wpuf' ) . '</p>' .
            '<p>' . __( 'Please see the <a href="https://www.paypal.com/us/webapps/mpp/ua/privacy-full">PayPal Privacy Policy</a> for more details.', 'wpuf' ) . '</p>';

        return apply_filters( 'wpuf_privacy_policy_content', $content );
    }

    /**
     * Register WPUF Exporter to export data
     *
     * @param $exporters
     *
     * @return array
     */
    function register_exporters( $exporters ) {
        $exporters['wpuf-personal-data-export'] = array(
            'exporter_friendly_name' => __( 'WPUF User Data', 'wpuf' ),
            'callback'               => array( 'WPUF_Privacy', 'export_user_data'),
        );

        $exporters['wpuf-subscription-data-export'] = array(
            'exporter_friendly_name' => __( 'WPUF Subscription Data', 'wpuf' ),
            'callback'               => array( 'WPUF_Privacy', 'export_subscription_data'),
        );

        $exporters['wpuf-transaction-data-export'] = array(
            'exporter_friendly_name' => __( 'WPUF Transaction Data', 'wpuf' ),
            'callback'               => array( 'WPUF_Privacy', 'export_transaction_data'),
        );

        $exporters['wpuf-post-data-export'] = array(
            'exporter_friendly_name' => __( 'WPUF Post Data', 'wpuf' ),
            'callback'               => array( 'WPUF_Privacy', 'export_post_data'),
        );

        return apply_filters( 'wpuf_privacy_register_exporters', $exporters );
    }

    /**
     * Register WPUF Eraser to delete data
     *
     * @param $erasers
     *
     * @return array
     */
    function register_erasers( $erasers ) {
        $erasers['wpuf-personal-data-erase'] = array(
            'eraser_friendly_name' => __( 'WPUF User Data', 'wpuf' ),
            'callback'             => array( 'WPUF_Privacy', 'erase_user_data'),
        );

        return apply_filters( 'wpuf_privacy_register_erasers', $erasers );
    }

    /**
     * Get WP_User for given $email address
     *
     * @param string $email
     *
     * @return WP_User | String
     */
    public static function get_user( $email ) {
        $user = get_user_by( 'email', $email );

        if ( $user ) {
            $wpuf_user = new WPUF_User( $user );
            return $wpuf_user;
        }

        return $email;
    }
    /**
     * Finds and exports customer data by email address.
     *
     * @param string $email_address The user email address.
     * @param int    $page  Page.
     *
     * @return array An array of data in name value pairs
     */
    public static function export_user_data( $email_address, $page ){

        $data_to_export = array();
        $wpuf_user = self::get_user( $email_address );

        $data_to_export[] = array(
            'group_id'    => 'wpuf-user-data',
            'group_label' => __( 'WPUF User Data', 'wpuf' ),
            'item_id'     => "wpuf-user",
            'data'        => apply_filters( 'wpuf_privacy_user_data', array(), $wpuf_user, $page ),
        );

        /**
         * Filters the export data array
         *
         * @param array
         */
        $data_to_export = apply_filters( 'wpuf_privacy_export_data', $data_to_export, $wpuf_user, $page );

        return array(
            'data' => $data_to_export,
            'done' => true
        );
    }

    /**
     * Erases personal data associated with an email address from the WPUF user data
     *
     * @param  string $email_address
     *
     * @param  int $page
     *
     * @return array
     */
    public static function erase_user_data( $email_address, $page = 1 ){

        if ( empty( $email_address ) ) {
            return array(
                'items_removed'  => false,
                'items_retained' => false,
                'messages'       => array(),
                'done'           => true,
            );
        }


        $erased = apply_filters( 'wpuf_erase_user_data', array(
            'items_removed'  => false,
            'items_retained' => false,
            'messages'       => array(),
            'done'           => true,
            ), $email_address, $page
        );

        return $erased;

    }

    /**
     * Add Billing address data to export
     *
     * @param $data
     *
     * @param $wpuf_user
     *
     * @param $page
     *
     * @return array
     */
    public function export_billing_address( $data, $wpuf_user, $page ) {

        if ( ! ( $wpuf_user instanceof WPUF_User ) ) {
            return $data;
        }

        $address = $wpuf_user->get_billing_address( true );

        /**
         * @var array $countries
         */
        include_once WPUF_ROOT . '/includes/countries.php';

        if ( !empty( $address ) ) {
            $address_data = array(
                array(
                    'name'  => __( 'Billing Address 1' ),
                    'value' => $address['add_line_1']
                ),
                array(
                    'name'  => __( 'Billing Address 2' ),
                    'value' => $address['add_line_2']
                ),
                array(
                    'name'  => __( 'City' ),
                    'value' => $address['city']
                ),
                array(
                    'name'  => __( 'State' ),
                    'value' => $address['state']
                ),
                array(
                    'name'  => __( 'Zip' ),
                    'value' => $address['zip_code']
                ),
                array(
                    'name'  => __( 'Country' ),
                    'value' => $countries[$address['country']]
                ),
            );

            return array_merge( $data, $address_data );
        }

        return $data;

    }

    /**
     * Export Subscription Data for User
     *
     * @param $email_address
     *
     * @param $page
     *
     * @return array
     */
    public static function export_subscription_data( $email_address, $page ) {

        $data_to_export[] = array(
            'group_id'    => 'wpuf-subscription-data',
            'group_label' => __( 'WPUF Subscription Data', 'wpuf' ),
            'item_id'     => "wpuf-subscription",
            'data'        => self::get_subscription_data( $email_address, $page )
        );

        $response = array(
            'data' => $data_to_export,
            'done' => true
        );

        return $response;
    }

    /**
     * Export transaction Data for User
     *
     * @param $email_address
     *
     * @param $page
     *
     * @return array
     */
    public static function export_transaction_data( $email_address, $page ) {

        $transaction_data = self::get_transaction_data( $email_address, $page );
        foreach ( $transaction_data as $txn_data ) {

            $data_to_export[] = array(
                'group_id' => 'wpuf-transaction-data',
                'group_label' => __('WPUF Transaction Data', 'wpuf'),
                'item_id' => "wpuf-transaction" . $txn_data['transaction_id'],
                'data' => array(
                    array(
                        'name' => __('Transaction ID', 'wpuf'),
                        'value' => $txn_data['transaction_id']
                    ),
                    array(
                        'name' => __('Payment Status', 'wpuf'),
                        'value' => $txn_data['status']
                    ),
                    array(
                        'name' => __('Subtotal', 'wpuf'),
                        'value' => $txn_data['subtotal']
                    ),
                    array(
                        'name' => __('Tax', 'wpuf'),
                        'value' => $txn_data['tax']
                    ),
                    array(
                        'name' => __('Total', 'wpuf'),
                        'value' => $txn_data['cost']
                    ),
                    array(
                        'name' => __('Post ID', 'wpuf'),
                        'value' => $txn_data['post_id']
                    ),
                    array(
                        'name' => __('Pack ID', 'wpuf'),
                        'value' => $txn_data['post_id']
                    ),
                    array(
                        'name' => __('First Name', 'wpuf'),
                        'value' => $txn_data['payer_first_name']
                    ),
                    array(
                        'name' => __('Last Name', 'wpuf'),
                        'value' => $txn_data['payer_last_name']
                    ),
                    array(
                        'name' => __('Email', 'wpuf'),
                        'value' => $txn_data['payer_email']
                    ),
                    array(
                        'name' => __('Payment Type', 'wpuf'),
                        'value' => $txn_data['payment_type']
                    ),
                    array(
                        'name' => __('payer_address', 'wpuf'),
                        'value' => implode(', ', array_map(
                            function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
                            maybe_unserialize( $txn_data['payer_address'] ),
                            array_keys( maybe_unserialize( $txn_data['payer_address'] ) )
                        ) ),
                    ),
                    array(
                        'name' => __('Transaction Date', 'wpuf'),
                        'value' => $txn_data['created']
                    ),
                ),
            );
        }
        $response = array(
            'data' => $data_to_export,
            'done' => true
        );

        return $response;
    }

    /**
     * Export Post Data for User
     *
     * @param $email_address
     *
     * @param $page
     *
     * @return array
     */
    public static function export_post_data( $email_address, $page ) {

        $post_data = self::get_post_data( $email_address, $page );
        $data_to_export = array();
        foreach ( $post_data as $data ) {
            $data_to_export[] = array(
                'group_id'    => 'wpuf-post-data',
                'group_label' => __( 'WPUF Post Data', 'wpuf' ),
                'item_id'     => "wpuf-posts-" . $data['id'],
                'data'        => array(
                    array(
                        'name'  => __('Post ID', 'wpuf'),
                        'value' => $data['id']
                    ),
                    array(
                        'name'  => __('Post Title', 'wpuf'),
                        'value' => $data['title']
                    ),
                    array(
                        'name'  => __('Post URL', 'wpuf'),
                        'value' => $data['url']
                    ),
                    array(
                        'name'  => __('Post Date', 'wpuf'),
                        'value' => $data['date']
                    ),
                ),
            );
        }

        $response = array(
            'data' => $data_to_export,
            'done' => true
        );

        return $response;
    }

    /**
     * Generate Subscription data to export
     *
     * @param $email_address
     *
     * @param $page
     *
     * @return array
     */
    public static function get_subscription_data( $email_address, $page ) {

        $wpuf_user = self::get_user( $email_address );

        if ( ! ( $wpuf_user instanceof WPUF_User ) ) {
            return array();
        }

        $sub_id = $wpuf_user->subscription()->current_pack_id();

        if ( !$sub_id ) {
            return array();
        }

        $pack = $wpuf_user->subscription()->current_pack();

        $subscription_data = array(
            array(
                'name'  => __( 'Pack ID' ),
                'value' => $pack['pack_id']
            ),
            array(
                'name'  => __( 'Pack Title' ),
                'value' => get_the_title( $pack['pack_id'] ),
            ),
            array(
                'name'  => __( 'Expiry' ),
                'value' => $pack['expire']
            ),
            array(
                'name'  => __( 'Recurring' ),
                'value' => $pack['recurring']
            ),
        );

        return apply_filters( 'wpuf_privacy_subscription_export_data', $subscription_data, $email_address, $page );
    }

    /**
     * Generate Transaction data to export
     *
     * @param $wpuf_user
     *
     * @param $page
     *
     * @return array
     */
    public static function get_transaction_data( $email_address, $page ) {

        $wpuf_user = self::get_user( $email_address );

        if ( ! ( $wpuf_user instanceof WPUF_User ) ) {
            return array();
        }

        $txn_data = $wpuf_user->get_transaction_data( true );

        if ( !empty( $txn_data ) ) {

            return $txn_data;
        }

    }

    /**
     * Generate Post data to export
     *
     * @param $email_address
     *
     * @param $page
     *
     * @return array
     */
    public static function get_post_data( $email_address, $page ) {

        $wpuf_user = self::get_user( $email_address );

        if ( ! ( $wpuf_user instanceof WPUF_User ) ) {
            return array();
        }

        $post_data = array();
        $allowed_posts = wpuf_get_option( 'export_post_types', 'wpuf_privacy', 'post' );

        if ( !empty( $allowed_posts ) ) {
                $posts = get_posts( apply_filters( 'wpuf_privacy_post_export_query_args',  array(
                    'author'      => $wpuf_user->id,
                    'post_type'   => $allowed_posts,
                    'numberposts' => '-1',
                    'order'       => 'ASC',
                ), $email_address, $page ) );

                foreach ( $posts as $post ) {
                    $data = array();
                    $data['id']    = $post->ID;
                    $data['title'] = $post->post_title;
                    $data['url']   = $post->guid;
                    $data['date']  = $post->post_date;

                    $post_data[] = $data;
                }
        }

        return apply_filters( 'wpuf_privacy_export_post_data', $post_data, $email_address, $page );

    }

}
