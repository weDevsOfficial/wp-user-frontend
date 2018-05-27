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
            'exporter_friendly_name' => __('WPUF User Data'),
            'callback'               => array( 'WPUF_Privacy', 'export_user_data'),
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
            'eraser_friendly_name' => __( 'WPUF User Data' ),
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
            'group_label' => __( 'WPUF User Data' ),
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


}