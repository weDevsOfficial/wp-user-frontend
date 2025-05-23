<?php

namespace WeDevs\Wpuf\Lib\Gateway;
use WeDevs\Wpuf\Frontend\Payment;
use WeDevs\Wpuf\Traits\TaxableTrait;
use WeDevs\Wpuf\Admin\Subscription;

/**
 * WP User Frontend PayPal gateway
 *
 * @since 0.8
 * @updated 2024
 */
class Paypal {
    use TaxableTrait;

    private $gateway_url;
    private $test_mode;
    private $webhook_id;
    private $api_version = '2.0';

    public function __construct() {
        $this->gateway_url = 'https://www.paypal.com/webscr/?';
        $this->test_mode = 'test' === wpuf_get_option('paypal_test_mode', 'wpuf_payment');
        $this->webhook_id = wpuf_get_option('paypal_webhook_id', 'wpuf_payment');

        // Initialize hooks
        add_action('wpuf_gateway_paypal', [$this, 'prepare_to_send']);
        add_filter('wpuf_options_payment', [$this, 'payment_options']);
        add_action('init', [$this, 'check_paypal_return'], 20);
        add_action('wpuf_cancel_payment_paypal', [$this, 'cancel_subscription']);
        add_action('wpuf_cancel_subscription_paypal', [$this, 'cancel_subscription']);
        add_action('init', [$this, 'handle_pending_payment']);
        add_action('wpuf_paypal_webhook', [$this, 'process_webhook']);
        // Add webhook endpoint handler
        add_action('init', [$this, 'register_webhook_endpoint']);
        add_action('template_redirect', [$this, 'handle_webhook_request']);
    }

    /**
     * Main webhook processor
     */
    public function process_webhook($raw_input) {
        try {
            // Set headers for webhook acknowledgment
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json');
            
            // Verify webhook signature
            if (!$this->verify_webhook_signature_from_input($raw_input)) {
                throw new \Exception('Webhook signature verification failed');
            }

            // Decode the webhook data
            $event = json_decode($raw_input, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \Exception('Invalid JSON in webhook data');
            }

            error_log('WPUF PayPal: Processing webhook event: ' . $event['event_type']);

            switch ($event['event_type']) {
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    if (isset($event['resource'])) {
                        $this->handle_subscription_cancelled($event['resource']);
                    }
                    break;

                case 'BILLING.SUBSCRIPTION.CREATED':
                    if (isset($event['resource'])) {
                        $this->handle_subscription_created($event['resource']);
                    }
                    break;
                    
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    if (isset($event['resource'])) {
                        // Handle when a subscription becomes active (after trial)
                        $this->handle_subscription_activated($event['resource']);
                    }
                    break;

                case 'PAYMENT.SALE.COMPLETED':
                    if (isset($event['resource'])) {
                        $payment = $event['resource'];
                        if (isset($payment['billing_agreement_id'])) {
                            $this->process_subscription_payment($payment);
                        }
                    }
                    break;
                    
                case 'PAYMENT.CAPTURE.COMPLETED':
                    if (isset($event['resource'])) {
                        $this->process_payment_capture($event['resource']);
                    }
                    break;
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Webhook processed successfully'
            ]);

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Webhook processing failed: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process payment capture
     */
    private function process_payment_capture($payment) {
        global $wpdb;

        try {
            // Get custom data from custom_id
            if ( ! isset($payment['custom_id'])) {
                throw new \Exception('Missing custom_id in payment');
            }

            $custom_data = json_decode($payment['custom_id'], true);
            if ( ! $custom_data) {
                throw new \Exception('Invalid custom data');
            }

            // Verify payment amount
            $payment_amount = number_format($payment['amount']['value'], 2, '.', '');
            $expected_amount = number_format($custom_data['subtotal'], 2, '.', '');

            if ( $payment_amount !== $expected_amount ) {
                throw new \Exception('Payment amount mismatch');
            }

            // Check if transaction already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wpuf_transaction 
                WHERE transaction_id = %s",
                $payment['id']
            ));

            if ($existing) {
                return; // Exit if transaction already processed
            }

            // Get user
            $user = get_user_by('id', $custom_data['user_id']);
            if (!$user) {
                throw new \Exception('Invalid user');
            }

            // Calculate tax
            $tax_amount = 0;
            if ($this->wpuf_tax_enabled()) {
                $tax_rate = $this->wpuf_current_tax_rate();
                $payment_amount = $payment['amount']['value'];
                // Calculate tax from total amount
                $tax_amount = ($payment_amount * $tax_rate) / (100 + $tax_rate);
                $subtotal = $payment_amount - $tax_amount;
            } else {
                $subtotal = $payment['amount']['value'];
            }

            // Create payment record
            $data = [
                'user_id' => $custom_data['user_id'],
                'status' => 'completed',
                'subtotal' => $subtotal,
                'tax' => $tax_amount,
                'cost' => $payment['amount']['value'],
                'post_id' => ($custom_data['type'] === 'post') ? $custom_data['item_number'] : 0,
                'pack_id' => ($custom_data['type'] === 'pack') ? $custom_data['item_number'] : 0,
                'payer_first_name' => $user->first_name,
                'payer_last_name' => $user->last_name,
                'payer_email' => $user->user_email,
                'payment_type' => 'paypal',
                'transaction_id' => $payment['id'],
                'created' => $this->get_current_time_utc()
            ];

            // Insert payment record
            Payment::insert_payment($data, $payment['id'], false);
            
            // Handle coupon if present
            if (!empty($custom_data['coupon_id'])) {
                $this->update_coupon_usage($custom_data['coupon_id']);
            }

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Payment capture processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get payer information
     */
    private function get_payer_info($payment, $user) {
        $payer_info = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->user_email
        ];

        if (isset($payment['payer'])) {
            if (isset($payment['payer']['name'])) {
                $name_parts = explode(' ', $payment['payer']['name']);
                $payer_info['first_name'] = $name_parts[0];
                $payer_info['last_name'] = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : '';
            }
            if (isset($payment['payer']['email_address'])) {
                $payer_info['email'] = $payment['payer']['email_address'];
            }
        }

        return $payer_info;
    }

    /**
     * Handle additional payment features
     */
    private function handle_payment_features($custom_data, $payment) {
        // Handle coupon
        if (!empty($custom_data['coupon_id'])) {
            $this->update_coupon_usage($custom_data['coupon_id']);
        }

        // Verify payment amount
        if ($payment['amount']['value'] != number_format($custom_data['subtotal'], 2, '.', '')) {
            throw new \Exception('Payment amount mismatch');
        }
    }


    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        // Add rewrite rule for webhook endpoint
        add_rewrite_rule(
            '^webhook_triggered/?$',
            'index.php?action=webhook_triggered=1',
            'top'
        );
        
        // Add query var filter
        add_filter('query_vars', function($vars) {
            $vars[] = 'action';
            return $vars;
        });
        
        // Flush rewrite rules only once
        if (!get_option('wpuf_paypal_webhook_flushed')) {
            flush_rewrite_rules();
            update_option('wpuf_paypal_webhook_flushed', true);
            error_log('WPUF PayPal: Webhook endpoint registered and rewrite rules flushed');
        }
        
        // Verify webhook configuration
        if (empty($this->webhook_id)) {
            error_log('WPUF PayPal: Warning - Webhook ID is not configured');
        } else {
            error_log('WPUF PayPal: Webhook endpoint registered at: ' . 
                home_url('/?action=webhook_triggered'));
        }
    }

    /**
     * Handle webhook request
     */
    public function handle_webhook_request() {
        if ( 'webhook_triggered' === get_query_var('action') && 
            'POST' === $_SERVER['REQUEST_METHOD'] && 
            isset($_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'])) {

            $raw_input = file_get_contents('php://input');
            $acknowledged = false;

            try {
                // Log and basic checks
                error_log('WPUF PayPal: Webhook received');
                if ( empty($raw_input) ) {
                    throw new \Exception('Empty webhook payload');
                }

                // Verify signature
                if ( ! $this->verify_webhook_signature_from_input($raw_input)) {
                    throw new \Exception('Invalid webhook signature');
                }

                // Decode and validate
                $webhook_data = json_decode($raw_input, true);
                if ( JSON_ERROR_NONE !== json_last_error() ) {
                    throw new \Exception('Invalid JSON');
                }
                if ( ! isset($webhook_data['event_type']) ) {
                    throw new \Exception('Missing event type');
                }

                // Log the event type
                error_log('WPUF PayPal: Received webhook event: ' . $webhook_data['event_type']);

                // Process the webhook
                $this->process_webhook($raw_input);

                $acknowledged = true;
            } catch (\Exception $e) {
                error_log('WPUF PayPal: Webhook error: ' . $e->getMessage());
                // Optionally, save the failed payload for manual review
            }

            // Always acknowledge to PayPal
            http_response_code(200);
            echo json_encode(['status' => $acknowledged ? 'ok' : 'error']);
            exit;
        }
    }

    /**
     * Get current time in UTC
     */
    private function get_current_time_utc() {
        // Get WordPress timezone setting
        $timezone = wp_timezone();
        
        // Create DateTime object in WordPress timezone
        $date = new \DateTime('now', $timezone);
        
        // Convert to UTC
        $date->setTimezone(new \DateTimeZone('UTC'));
        
        return $date->format('d-m-Y');
    }

    /**
     * Verify webhook signature from raw input
     */
    private function verify_webhook_signature_from_input($raw_input) {
        try {
            // Get all headers, lowercased
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    
            // Required PayPal webhook headers
            $required_headers = [
                'paypal-transmission-id',
                'paypal-transmission-time',
                'paypal-transmission-sig',
                'paypal-cert-url',
                'paypal-auth-algo'
            ];
    
            foreach ($required_headers as $header) {
                if ( empty($headers[$header]) ) {
                    error_log('WPUF PayPal: Missing required header: ' . $header);
                    return false;
                }
            }
    
            $access_token = $this->get_access_token();
            if ( ! $access_token) {
                error_log('WPUF PayPal: Failed to get access token');
                return false;
            }
    
            $verification_url = $this->test_mode ?
                'https://api.sandbox.paypal.com/v1/notifications/verify-webhook-signature' :
                'https://api.paypal.com/v1/notifications/verify-webhook-signature';
    
            $verification_data = [
                'transmission_id'    => $headers['paypal-transmission-id'],
                'transmission_time'  => $headers['paypal-transmission-time'],
                'cert_url'           => $headers['paypal-cert-url'],
                'webhook_id'         => $this->webhook_id,
                'webhook_event'      => json_decode($raw_input, false),
                'transmission_sig'   => $headers['paypal-transmission-sig'],
                'auth_algo'          => $headers['paypal-auth-algo'],
            ];
    
            error_log('WPUF PayPal: Verification data: ' . print_r($verification_data, true));
    
            $response = wp_remote_post($verification_url, [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ],
                'body'    => json_encode($verification_data),
                'timeout' => 30
            ]);
    
            if (is_wp_error($response)) {
                error_log('WPUF PayPal: Verification request failed: ' . $response->get_error_message());
                return false;
            }
    
            $body = json_decode(wp_remote_retrieve_body($response), true);
            error_log('WPUF PayPal: Verification response: ' . print_r($body, true));
    
            $is_verified = isset($body['verification_status']) && $body['verification_status'] === 'SUCCESS';
    
            error_log('WPUF PayPal: Webhook verification ' . ($is_verified ? 'successful' : 'failed'));
            error_log('WPUF PayPal: ===== Webhook Verification End =====');
    
            return $is_verified;
    
        } catch (\Exception $e) {
            error_log('WPUF PayPal: Webhook verification error: ' . $e->getMessage());
            error_log('WPUF PayPal: Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Get PayPal access token
     */
    private function get_access_token() {
        $client_id = wpuf_get_option('paypal_client_id', 'wpuf_payment');
        $client_secret = wpuf_get_option('paypal_client_secret', 'wpuf_payment');

        $token_url = $this->test_mode ? 
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' :
            'https://api-m.paypal.com/v1/oauth2/token';

        $response = wp_remote_post($token_url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => 'grant_type=client_credentials'
        ]);

        if (is_wp_error($response)) {
            throw new \Exception('Failed to get access token: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $body['access_token'];
    }

    /**
     * Handle subscription creation
     */
    private function handle_subscription_created($subscription) {
        try {
            error_log('WPUF PayPal: Processing subscription creation: ' . print_r($subscription, true));

            // Extract custom data
            $custom_data = [];
            if (isset($subscription['custom_id'])) {
                $custom_data = json_decode($subscription['custom_id'], true);
            }

            if (!$custom_data || !isset($custom_data['user_id'])) {
                throw new \Exception('Invalid custom data in subscription');
            }

            $user_id = $custom_data['user_id'];
            $subscription_id = $subscription['id']; // This is the PayPal subscription ID
            $trial_period_days = isset($custom_data['trial_period_days']) ? $custom_data['trial_period_days'] : 0;
            $is_trial = $trial_period_days > 0;
            
            // Check if we're in a trial period
            $is_in_trial = false;
            if (isset($subscription['billing_info']['cycle_executions'])) {
                foreach ($subscription['billing_info']['cycle_executions'] as $cycle) {
                    if ($cycle['tenure_type'] === 'TRIAL' && $cycle['cycles_completed'] < $cycle['cycles_remaining']) {
                        $is_in_trial = true;
                        break;
                    }
                }
            }

            // Get the pack details
            $pack = get_post($custom_data['item_number']);
            $pack_meta = array_map(function($value) {
                return maybe_unserialize($value[0]);
            }, get_post_meta($pack->ID));

            // Get subscription period and interval from pack meta
            $period = isset($pack_meta['_cycle_period']) ? $pack_meta['_cycle_period'] : 'month';
            $interval = isset($pack_meta['_billing_cycle_number']) ? intval($pack_meta['_billing_cycle_number']) : 1;

            // Get tax rate if enabled
            $tax_rate = 0;
            if ($this->wpuf_tax_enabled()) {
                $tax_rate = $this->wpuf_current_tax_rate();
            }

            // Create subscription data structure with all necessary meta
            $subscription_data = [
                'pack_id' => $custom_data['item_number'],
                'posts' => isset($pack_meta['_post_types']) ? $pack_meta['_post_types'] : [],
                'total_feature_item' => isset($pack_meta['_total_feature_item']) ? $pack_meta['_total_feature_item'] : '-1',
                'remove_feature_item' => isset($pack_meta['_remove_feature_item']) ? $pack_meta['_remove_feature_item'] : '-1',
                'status' => 'completed',
                'expire' => '',  // Will be calculated based on expiration settings
                'profile_id' => $subscription_id,
                'recurring' => 'yes',
                'cycle_period' => $period,
                'cycle_number' => $interval,
                'postnum_rollback_on_delete' => isset($pack_meta['_postnum_rollback_on_delete']) ? $pack_meta['_postnum_rollback_on_delete'] : '',
                '_enable_post_expiration' => isset($pack_meta['_enable_post_expiration']) ? $pack_meta['_enable_post_expiration'] : 'no',
                '_post_expiration_time' => isset($pack_meta['_post_expiration_time']) ? $pack_meta['_post_expiration_time'] : '',
                '_expired_post_status' => isset($pack_meta['_expired_post_status']) ? $pack_meta['_expired_post_status'] : 'publish',
                '_enable_mail_after_expired' => isset($pack_meta['_enable_mail_after_expired']) ? $pack_meta['_enable_mail_after_expired'] : 'no',
                '_post_expiration_message' => isset($pack_meta['_post_expiration_message']) ? $pack_meta['_post_expiration_message'] : '',
                'subscription_id' => $subscription_id,
                'trial' => $is_trial ? 'yes' : 'no',
                'created' => $this->get_current_time_utc()
            ];

            // If posts meta is empty, set default values
            if (empty($subscription_data['posts'])) {
                $subscription_data['posts'] = [
                    'post' => '-1',
                    'page' => '-1',
                    'user_request' => '-1',
                    'wp_block' => '-1',
                    'wp_template' => '-1',
                    'wp_template_part' => '-1',
                    'wp_global_styles' => '-1',
                    'wp_navigation' => '-1',
                    'wp_font_family' => '-1',
                    'wp_font_face' => '-1'
                ];
            }

            error_log('WPUF PayPal: Current pack: ' . print_r($subscription_data, true));

            // Update user meta with complete subscription data
            update_user_meta($user_id, '_wpuf_subscription_pack', $subscription_data);

            // Create a trial payment record if this is a trial
            if ($is_in_trial) {
                $this->create_trial_payment_record($user_id, $custom_data['item_number'], $subscription_id);
            }
          
        } catch (\Exception $e) {
            error_log('WPUF PayPal: Subscription creation handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a trial payment record with zero cost
     */
    private function create_trial_payment_record($user_id, $pack_id, $subscription_id) {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            error_log('WPUF PayPal: Invalid user ID for trial payment: ' . $user_id);
            return;
        }
        
        // Create payment data with zero cost for trial
        $payment_data = [
            'user_id' => $user_id,
            'status' => 'completed',
            'subtotal' => 0,
            'tax' => 0,
            'cost' => 0,
            'post_id' => 0,
            'pack_id' => $pack_id,
            'payer_first_name' => $user->first_name,
            'payer_last_name' => $user->last_name,
            'payer_email' => $user->user_email,
            'payment_type' => 'PayPal',
            'transaction_id' => $subscription_id . '_trial',
            'created' => gmdate('Y-m-d H:i:s'),
        ];
        
        \WeDevs\Wpuf\Frontend\Payment::insert_payment($payment_data, $subscription_id . '_trial', true);
        error_log('WPUF PayPal: Trial payment record created for user: ' . $user_id);
    }

    /**
     * Handle subscription cancellation
     */
    public function cancel_subscription($data) {
        error_log('WPUF PayPal: ===== Subscription Cancellation Debug Start =====');
        error_log('WPUF PayPal: Raw input data: ' . print_r($data, true));

        try {
            global $wpdb;
            // Extract user_id from the input
            $user_id = null;
            
            // Get user ID from form data or current user
            if (isset($data['user_id'])) {
                $user_id = $data['user_id'];
            } else {
                $user_id = get_current_user_id();
            }

            if (!$user_id || !is_numeric($user_id)) {
                error_log('WPUF PayPal: Invalid user ID provided for cancellation: ' . print_r($data, true));
                throw new \Exception('Invalid user ID provided for cancellation');
            }

            error_log('WPUF PayPal: Processing cancellation for user ID: ' . $user_id);

            // Get subscription data
            $subscription = get_user_meta($user_id, '_wpuf_subscription_pack', true);
            error_log('WPUF PayPal: Raw subscription data: ' . print_r($subscription, true));

            // Get subscription ID from PayPal
            $profile_id = '';
            
            // First try to get from user meta
            if (isset($subscription['subscription_id']) && !empty($subscription['subscription_id'])) {
                $profile_id = $subscription['subscription_id'];
            }
            
            // If not in user meta, try to get from subscribers table
            if (empty($profile_id)) {
                $subscriber_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT transaction_id FROM {$wpdb->prefix}wpuf_subscribers 
                    WHERE user_id = %d AND gateway = 'paypal' AND subscribtion_status = 'completed'
                    ORDER BY id DESC LIMIT 1",
                    $user_id
                ));
                
                if ($subscriber_data && !empty($subscriber_data->transaction_id)) {
                    $profile_id = $subscriber_data->transaction_id;
                }
            }

            error_log('WPUF PayPal: Found profile_id: ' . $profile_id);

            // If we have a profile ID and subscription is recurring
            if (!empty($profile_id) && $profile_id !== 'Free' && $subscription['recurring'] === 'yes') {
                try {
                    // Get access token
                    $access_token = $this->get_access_token();

                    // Cancel the subscription in PayPal
                    $cancel_url = ($this->test_mode ? 
                        'https://api-m.sandbox.paypal.com' : 
                        'https://api-m.paypal.com') . '/v1/billing/subscriptions/' . $profile_id . '/cancel';

                    error_log('WPUF PayPal: Attempting to cancel subscription in PayPal with URL: ' . $cancel_url);

                    $response = wp_remote_post($cancel_url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json',
                            'Prefer' => 'return=representation'
                        ],
                        'body' => json_encode([
                            'reason' => 'Customer requested cancellation'
                        ])
                    ]);

                    if (is_wp_error($response)) {
                        error_log('WPUF PayPal: API Error: ' . $response->get_error_message());
                        throw new \Exception('Failed to cancel subscription in PayPal: ' . $response->get_error_message());
                    }

                    $response_code = wp_remote_retrieve_response_code($response);
                    $response_body = wp_remote_retrieve_body($response);
                    error_log('WPUF PayPal: API Response Code: ' . $response_code);
                    error_log('WPUF PayPal: API Response Body: ' . $response_body);

                    if ($response_code !== 204) {
                        throw new \Exception('Unexpected response from PayPal: ' . $response_body);
                    }

                } catch (\Exception $e) {
                    error_log('WPUF PayPal: PayPal API error: ' . $e->getMessage());
                    // Continue with local cancellation even if PayPal fails
                }
            }

            // Update local subscription status regardless of PayPal API result
            $updated_subscription = [
                'profile_id' => $profile_id,
                'status' => 'cancel',
                'updated' => $this->get_current_time_utc()
            ];
            
            update_user_meta($user_id, '_wpuf_subscription_pack', $updated_subscription);
            update_user_meta($user_id,'_wpuf_paypal_subscription_status', 'cancel');
            error_log('WPUF PayPal: Updated subscription meta: ' . print_r($updated_subscription, true));

            // Update subscriber table
            $update_result = $wpdb->update(
                $wpdb->prefix . 'wpuf_subscribers',
                [
                    'subscribtion_status' => 'cancel',
                    'expire' => $this->get_current_time_utc()
                ],
                [
                    'user_id' => $user_id,
                    'gateway' => 'paypal'
                ],
                ['%s', '%s'],
                ['%d', '%s']
            );

            if ($update_result === false) {
                error_log('WPUF PayPal: Database error updating subscribers table: ' . $wpdb->last_error);
            } else {
                error_log('WPUF PayPal: Updated subscribers table. Rows affected: ' . $update_result);
            }

            error_log('WPUF PayPal: Subscription cancel successfully for user: ' . $user_id);
            error_log('WPUF PayPal: ===== Subscription Cancellation Debug End =====');
            return true;

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Subscription cancellation failed: ' . $e->getMessage());
            error_log('WPUF PayPal: Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Handle subscription payment
     */
    private function process_subscription_payment($payment) {
        global $wpdb;

        try {
            // Get transaction ID - could be in different locations based on event type
            $transaction_id = isset($payment['id']) ? $payment['id'] : '';
            
            // Get subscription ID
            $subscription_id = isset($payment['billing_agreement_id']) ? $payment['billing_agreement_id'] : '';
            
            // If no subscription ID or transaction ID, exit
            if (empty($subscription_id) || empty($transaction_id)) {
                error_log('WPUF PayPal: Missing subscription ID or transaction ID in payment data');
                return;
            }
            
            // Check if transaction already exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wpuf_transaction 
                WHERE transaction_id = %s",
                $transaction_id
            ));

            if ($existing) {
                error_log('WPUF PayPal: Transaction already processed: ' . $transaction_id);
                // Even if transaction exists, clean up any transients
                $this->clean_up_transients($subscription_id);
                return; // Exit if transaction already processed
            }
            
            // Get payment amount
            $amount = 0;
            if (isset($payment['amount']['total'])) {
                $amount = $payment['amount']['total'];
            } elseif (isset($payment['amount']['value'])) {
                $amount = $payment['amount']['value'];
            }
            
            // Get currency
            $currency = '';
            if (isset($payment['amount']['currency'])) {
                $currency = $payment['amount']['currency'];
            } elseif (isset($payment['amount']['currency_code'])) {
                $currency = $payment['amount']['currency_code'];
            }
            
            // Initialize custom data
            $custom_data = [];
            
            // Get custom data
            if (isset($payment['custom'])) {
                $custom_data = json_decode($payment['custom'], true);
            } elseif (isset($payment['custom_id'])) {
                $custom_data = json_decode($payment['custom_id'], true);
            }
            
            // If JSON decode failed, initialize as empty array
            if (!is_array($custom_data)) {
                $custom_data = [];
            }
            
            // Check for pending subscription in transient
            $pending_subscription = get_transient('wpuf_paypal_pending_' . $subscription_id);
            $user_id = null;
            $pack_id = 0;
            
            // If we have pending subscription data, use it
            if ($pending_subscription && isset($pending_subscription['user_id'])) {
                $user_id = $pending_subscription['user_id'];
                
                if (isset($pending_subscription['pack_id'])) {
                    $pack_id = $pending_subscription['pack_id'];
                }
            } else {
                // Get user ID from subscription if not found in transient
                $user_id = $this->get_user_id_by_subscription($subscription_id);
            }
            
            // Try to get user ID from custom data if still not found
            if (!$user_id && isset($custom_data['user_id'])) {
                $user_id = $custom_data['user_id'];
            }
            
            if (!$user_id) {
                error_log('WPUF PayPal: User not found for subscription: ' . $subscription_id);
                // Clean up any transients even if user not found
                $this->clean_up_transients($subscription_id);
                return;
            }
            
            // Get user data
            $user = get_user_by('id', $user_id);
            if (!$user) {
                error_log('WPUF PayPal: Invalid user ID: ' . $user_id);
                // Clean up any transients even if user invalid
                $this->clean_up_transients($subscription_id);
                return;
            }
            
            // If pack ID not found in transient, try to get it from custom data or meta
            if ($pack_id == 0) {
                if (isset($custom_data['item_number']) && isset($custom_data['type']) && $custom_data['type'] === 'pack') {
                    $pack_id = $custom_data['item_number'];
                } else {
                    // Try to get pack ID from user meta
                    $pack_id = $this->get_pack_id_by_subscription($user_id, $subscription_id);
                }
            }
            
            // Check if a subscriber record already exists for this subscription
            $existing_subscriber = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}wpuf_subscribers 
                WHERE user_id = %d AND transaction_id = %s",
                $user_id, $subscription_id
            ));
            
            // If no subscriber record exists yet and we have all the data, create one
            if (!$existing_subscriber && $pack_id > 0) {
                // Update user subscription status in WordPress - this should be the only record
                wpuf_get_user($user_id)->subscription()->add_pack($pack_id, $subscription_id, true, 'recurring');
                update_user_meta($user_id,'_wpuf_paypal_subscription_status', 'completed');
                // Log subscription creation
                error_log('WPUF PayPal: User subscription pack added for user: ' . $user_id . ', pack: ' . $pack_id);
            }
            
            // Prepare payment data
            $data = [
                'user_id'           => $user_id,
                'status'            => 'completed',
                'subtotal'          => $amount,
                'tax'               => 0,
                'cost'              => $amount,
                'post_id'           => 0,
                'pack_id'           => $pack_id,
                'payer_first_name'  => $user->first_name,
                'payer_last_name'   => $user->last_name,
                'payer_email'       => $user->user_email,
                'payment_type'      => 'paypal',
                'transaction_id'    => $transaction_id,
                'created'           => gmdate('Y-m-d H:i:s')
            ];
            
            // Add custom meta information if available
            if (isset($payment['time'])) {
                $data['created'] = date('Y-m-d H:i:s', strtotime($payment['time']));
            }

            // Insert payment record
            \WeDevs\Wpuf\Frontend\Payment::insert_payment($data, $transaction_id, true);
            
            // Final cleanup of any transients after successful processing
            $this->clean_up_transients($subscription_id);
            
        } catch (\Exception $e) {
            error_log('WPUF PayPal: Subscription payment processing failed: ' . $e->getMessage());
            
            // Even in case of error, try to clean up transients
            if (isset($subscription_id)) {
                $this->clean_up_transients($subscription_id);
            }
        }
    }
    
    /**
     * Clean up all transients related to a subscription
     */
    private function clean_up_transients($subscription_id) {
        // Delete the specific transient
        delete_transient('wpuf_paypal_pending_' . $subscription_id);
        
        // Log cleanup
        error_log('WPUF PayPal: Cleaned up transients for subscription: ' . $subscription_id);
    }

    /**
     * Get pack ID by subscription ID
     */
    private function get_pack_id_by_subscription($user_id, $subscription_id) {
        global $wpdb;
        
        // First try to get from subscribers table
        $pack_id = $wpdb->get_var($wpdb->prepare(
            "SELECT subscribtion_id FROM {$wpdb->prefix}wpuf_subscribers 
            WHERE user_id = %d AND transaction_id = %s",
            $user_id, $subscription_id
        ));
        
        if ($pack_id) {
            return $pack_id;
        }
        
        // Then try to get from user meta
        $subscription = get_user_meta($user_id, '_wpuf_subscription_pack', true);
        if ($subscription && isset($subscription['pack_id'])) {
            return $subscription['pack_id'];
        }
        
        return 0;
    }

    /**
     * Update payment options
     */
    public function payment_options($options) {
        // Existing options
        $options[] = [
            'name'  => 'paypal_email',
            'label' => __( 'PayPal Email', 'wp-user-frontend' ),
        ];

        $options[] = [
            'name'    => 'gate_instruct_paypal',
            'label'   => __( 'PayPal Instruction', 'wp-user-frontend' ),
            'type'    => 'wysiwyg',
            'default' => "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account",
        ];

        // New REST API options
        $options[] = [
            'name' => 'paypal_client_id',
            'label' => __('PayPal Client ID', 'wp-user-frontend'),
        ];

        $options[] = [
            'name' => 'paypal_client_secret',
            'label' => __('PayPal Client Secret', 'wp-user-frontend'),
        ];

        $options[] = [
            'name' => 'paypal_webhook_id',
            'label' => __('PayPal Webhook ID', 'wp-user-frontend'),
        ];

        // Legacy API options (keep for backward compatibility)
        $options[] = [
            'name'  => 'paypal_api_username',
            'label' => __( 'PayPal API username', 'wp-user-frontend' ),
        ];
        $options[] = [
            'name'  => 'paypal_api_password',
            'label' => __( 'PayPal API password', 'wp-user-frontend' ),
        ];
        $options[] = [
            'name'  => 'paypal_api_signature',
            'label' => __( 'PayPal API signature', 'wp-user-frontend' ),
        ];

        // Replace sandbox mode checkbox with test mode radio button
        $options[] = [
            'name' => 'paypal_test_mode',
            'label' => __('PayPal Mode', 'wp-user-frontend'),
            'type' => 'radio',
            'default' => 'live',
            'options' => [
                'live' => __('Live Mode', 'wp-user-frontend'),
                'test' => __('Test Mode (Sandbox)', 'wp-user-frontend')
            ],
            'desc' => __('Choose whether to process real payments or test payments. Test mode uses PayPal Sandbox environment.', 'wp-user-frontend')
        ];

        return $options;
    }

    public function subscription_cancel($user_id) {
        try {
            $this->cancel_subscription(['user_id' => $user_id]);
        } catch (\Exception $e) {
            error_log('WPUF PayPal: Failed to cancel subscription for user ' . $user_id . ': ' . $e->getMessage());
            // Update local meta even if PayPal API call fails
            $sub_meta = 'cancel';
            wpuf_get_user($user_id)->subscription()->update_meta($sub_meta);
        }
    }

    /**
     * Update coupon usage count
     *
     * @param int $coupon_id
     */
    private function update_coupon_usage($coupon_id) {
        if (empty($coupon_id)) {
            return;
        }

        $pre_usage = get_post_meta($coupon_id, '_coupon_used', true);
        $pre_usage = empty($pre_usage) ? 0 : $pre_usage;
        $new_use = $pre_usage + 1;

        update_post_meta($coupon_id, '_coupon_used', $new_use);
    }


    /**
     * Prepare and send payment to PayPal
     *
     * @param array $data Payment data
     */
    public function prepare_to_send($data) {
        try {
            $user_id = $data['user_info']['id'];
            $return_url = add_query_arg([
                'action' => 'wpuf_paypal_success',
                'type' => $data['type'],
                'item_number' => $data['item_number'],
                'wpuf_payment_method' => 'paypal'
            ], wpuf_payment_success_page([
                'type' => isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'pack',
                'item_number' => isset($_GET['item_number']) ? sanitize_text_field($_GET['item_number']) : '',
                'wpuf_payment_method' => 'paypal'
            ]));
            
            $cancel_url = $return_url;

            $billing_amount = empty($data['price']) ? 0 : $data['price'];
            $tax_amount = 0;

            // Handle tax if enabled
            if ($this->wpuf_tax_enabled()) {
                $tax_rate = $this->wpuf_current_tax_rate();
                $tax_amount = $billing_amount * ($tax_rate / 100);
                $billing_amount = $billing_amount + $tax_amount;
            }

            // Handle coupon if present
            if (isset($_POST['coupon_id']) && !empty($_POST['coupon_id'])) {
                $billing_amount = wpuf_pro()->coupon->discount($billing_amount, $_POST['coupon_id'], $data['item_number']);
                $coupon_id = $_POST['coupon_id'];
            } else {
                $coupon_id = '';
            }

            $data['subtotal'] = $billing_amount - $tax_amount;
            $data['tax'] = $tax_amount;
            $billing_amount = apply_filters('wpuf_payment_amount', $billing_amount);

            // Handle free payments
            if ($billing_amount == 0) {
                wpuf_get_user($user_id)->subscription()->add_pack($data['item_number'], null, false, 'Free');
                wp_redirect($return_url);
                exit();
            }

            // Get access token
            $access_token = $this->get_access_token();

            // Check if this is a recurring payment
            if ($data['type'] === 'pack' && isset($data['custom']['recurring_pay']) && wpuf_is_checkbox_or_toggle_on($data['custom']['recurring_pay'])) {
                error_log('WPUF PayPal: Setting up recurring payment subscription');
                
                // Get subscription details from pack
                $pack = get_post($data['item_number']); 
                
                $flattened_subscription_meta = array_map(function($value) {
                    return maybe_unserialize( $value[0] );
                }, get_post_meta( $pack->ID ));
                
                if (empty($flattened_subscription_meta)) {
                    throw new \Exception('Invalid subscription pack');
                }

                // Get subscription period and interval
                $period = isset($flattened_subscription_meta['_cycle_period']) ? $flattened_subscription_meta['_cycle_period'] : 'month';
                $interval = isset($flattened_subscription_meta['_billing_cycle_number']) ? intval($flattened_subscription_meta['_billing_cycle_number']) : 1;
                
                // Handle trial period
                $trial_period_days = 0;
                
                if (isset($data['custom']['trial_status']) && wpuf_is_checkbox_or_toggle_on($data['custom']['trial_status'])) {
                    $trial_duration_type = $data['custom']['trial_duration_type'];
                    $trial_duration = absint($data['custom']['trial_duration']);

                    switch ($trial_duration_type) {
                        case 'week':
                            $trial_period_days = $trial_duration * 7;
                            break;
                        case 'month':
                            $trial_period_days = $trial_duration * 30;
                            break;
                        case 'year':
                            $trial_period_days = $trial_duration * 365;
                            break;
                        case 'day':
                        default:
                            $trial_period_days = $trial_duration;
                            break;
                    }
                    
                    // Set trial meta for once per user
                    if (!get_user_meta($user_id, '_wpuf_used_trial', true)) {
                        update_user_meta($user_id, '_wpuf_used_trial', 'yes');
                    }
                }
                
                // Create a plan if not exists
                $plan_id = $this->get_or_create_plan($pack, $billing_amount, $period, $interval, $trial_period_days);
                
                if (!$plan_id) {
                    throw new \Exception('Failed to create or get subscription plan');
                }

                // Get tax rate if enabled
                $tax_rate = 0;
                if ($this->wpuf_tax_enabled()) {
                    $tax_rate = $this->wpuf_current_tax_rate();
                }

                // Prepare subscription data
                $subscription_data = [
                    'plan_id' => $plan_id,
                    'application_context' => [
                        'brand_name' => get_bloginfo('name'),
                        'locale' => 'en-US',
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'SUBSCRIBE_NOW',
                        'return_url' => $return_url,
                        'cancel_url' => $cancel_url
                    ],
                    'custom_id' => wp_json_encode([
                        'type' => $data['type'],
                        'user_id' => $user_id,
                        'item_number' => $data['item_number'],
                        'subtotal' => $data['subtotal'],
                        'tax_rate' => $tax_rate,
                        'tax' => $data['tax'],
                        'coupon_id' => $coupon_id,
                        'trial_period_days' => $trial_period_days
                    ])
                ];

                error_log('WPUF PayPal: Subscription data: ' . print_r($subscription_data, true));

                // Create subscription
                $response = wp_remote_post(
                    ($this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com') . '/v1/billing/subscriptions',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json',
                            'Prefer' => 'return=representation'
                        ],
                        'body' => wp_json_encode($subscription_data)
                    ]
                );

                if (is_wp_error($response)) {
                    throw new \Exception('Failed to create PayPal subscription: ' . $response->get_error_message());
                }

                $body = json_decode(wp_remote_retrieve_body($response), true);
                error_log('WPUF PayPal: Subscription response: ' . print_r($body, true));

                if (!isset($body['id'])) {
                    throw new \Exception('Invalid response from PayPal - no subscription ID');
                }

                // Store subscription details in transient instead of creating initial record
                set_transient(
                    'wpuf_paypal_pending_' . $body['id'], 
                    [
                        'user_id' => $user_id,
                        'pack_id' => $data['item_number'],
                        'subscription_id' => $body['id'],
                        'status' => 'pending',
                        'created' => gmdate('Y-m-d H:i:s'),
                        'trial_period_days' => $trial_period_days
                    ],
                    HOUR_IN_SECONDS * 24 // Expire after 24 hours
                );

                // Find approval URL
                $approval_url = '';
                foreach ($body['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approval_url = $link['href'];
                        break;
                    }
                }

                if (empty($approval_url)) {
                    throw new \Exception('Approval URL not found in PayPal response');
                }

                // Redirect to PayPal
                wp_redirect($approval_url);
                exit();

            } else {
                $payment_data = [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'amount' => [
                            'currency_code' => $data['currency'],
                            'value' => number_format($billing_amount, 2, '.', '')
                        ],
                        'description' => isset($data['custom']['post_title']) ? $data['custom']['post_title'] : $data['item_name'],
                        'custom_id' => wp_json_encode([
                            'type' => $data['type'],
                            'user_id' => $user_id,
                            'coupon_id' => $coupon_id,
                            'subtotal' => $data['subtotal'],
                            'tax' => $data['tax'],
                            'item_number' => $data['item_number'],
                            'first_name' => $data['user_info']['first_name'],
                            'last_name' => $data['user_info']['last_name'],
                            'email' => $data['user_info']['email']
                        ])
                    ]],
                    'application_context' => [
                        'return_url' => $return_url,
                        'cancel_url' => $cancel_url,
                        'brand_name' => get_bloginfo('name'),
                        'landing_page' => 'LOGIN',
                        'user_action' => 'PAY_NOW',
                        'shipping_preference' => 'NO_SHIPPING'
                    ]
                ];

                // Add debug logging
                error_log('WPUF PayPal: Return URL: ' . $return_url);
                error_log('WPUF PayPal: Payment Data: ' . print_r($payment_data, true));

                // Create order
                $response = wp_remote_post(
                    $this->test_mode ? 'https://api-m.sandbox.paypal.com/v2/checkout/orders' : 'https://api-m.paypal.com/v2/checkout/orders',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json'
                        ],
                        'body' => wp_json_encode($payment_data)
                    ]
                );

                if (is_wp_error($response)) {
                    throw new \Exception('Failed to create PayPal order: ' . $response->get_error_message());
                }

                $body = json_decode(wp_remote_retrieve_body($response), true);
                error_log('WPUF PayPal: PayPal response: ' . print_r($body, true));

                if (!isset($body['id'])) {
                    throw new \Exception('Invalid response from PayPal - no order ID');
                }

                // Find approval URL
                $approval_url = '';
                foreach ($body['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approval_url = $link['href'];
                        break;
                    }
                }

                if (empty($approval_url)) {
                    throw new \Exception('Approval URL not found in PayPal response');
                }

                // Redirect to PayPal
                wp_redirect($approval_url);
                exit();
            }

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Payment preparation failed: ' . $e->getMessage());
            wp_die($e->getMessage());
        }
    }

    /**
     * Get or create a PayPal subscription plan
     */
    private function get_or_create_plan($pack, $amount, $period, $interval, $trial_period_days = 0) {
        try {
            $access_token = $this->get_access_token();
            $plan_name = 'WPUF-' . $pack->post_title . '-' . uniqid();
            $plan_id = get_post_meta($pack->ID, '_paypal_plan_id', true);

            // If plan exists and is active, return it
            if ($plan_id) {
                $plan_url = ($this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com') . 
                           '/v1/billing/plans/' . $plan_id;
                
                $response = wp_remote_get($plan_url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type' => 'application/json'
                    ]
                ]);

                if (!is_wp_error($response)) {
                    $body = json_decode(wp_remote_retrieve_body($response), true);
                    if (isset($body['status']) && $body['status'] === 'ACTIVE') {
                        return $plan_id;
                    }
                }
            }

            // Create new plan
            $plan_data = [
                'product_id' => $this->get_or_create_product($pack),
                'name' => $plan_name,
                'description' => $pack->post_title,
                'status' => 'ACTIVE',
                'billing_cycles' => [[
                    'frequency' => [
                        'interval_unit' => strtoupper($period),
                        'interval_count' => $interval
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0, // Unlimited
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format($amount, 2, '.', ''),
                            'currency_code' => wpuf_get_option('currency', 'wpuf_payment', 'USD')
                        ]
                    ]
                ]],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee' => [
                        'value' => '0',
                        'currency_code' => wpuf_get_option('currency', 'wpuf_payment', 'USD')
                    ],
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ]
            ];

            // Add trial period if specified
            if ($trial_period_days > 0) {
                $plan_data['payment_preferences']['setup_fee_failure_action'] = 'CONTINUE';
                
                // Add trial period as a billing cycle before the regular one
                array_unshift($plan_data['billing_cycles'], [
                    'frequency' => [
                        'interval_unit' => 'DAY',
                        'interval_count' => $trial_period_days
                    ],
                    'tenure_type' => 'TRIAL',
                    'sequence' => 1,
                    'total_cycles' => 1,
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => '0',
                            'currency_code' => wpuf_get_option('currency', 'wpuf_payment', 'USD')
                        ]
                    ]
                ]);
                
                // Update the regular billing cycle sequence
                $plan_data['billing_cycles'][1]['sequence'] = 2;
            }

            $response = wp_remote_post(
                ($this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com') . '/v1/billing/plans',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type' => 'application/json',
                        'Prefer' => 'return=representation'
                    ],
                    'body' => wp_json_encode($plan_data)
                ]
            );

            if (is_wp_error($response)) {
                throw new \Exception('Failed to create PayPal plan: ' . $response->get_error_message());
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!isset($body['id'])) {
                throw new \Exception('Invalid response from PayPal - no plan ID');
            }

            // Store plan ID
            update_post_meta($pack->ID, '_paypal_plan_id', $body['id']);
            
            return $body['id'];

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Plan creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get or create a PayPal product
     */
    private function get_or_create_product($pack) {
        try {
            $access_token = $this->get_access_token();
            $product_id = get_post_meta($pack->ID, '_paypal_product_id', true);

            // If product exists, return it
            if ($product_id) {
                return $product_id;
            }

            // Create new product
            $product_data = [
                'name' => $pack->post_title,
                'description' => $pack->post_excerpt ?: $pack->post_title,
                'type' => 'SERVICE',
                'category' => 'SERVICES'
            ];

            $response = wp_remote_post(
                ($this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com') . '/v1/catalogs/products',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => wp_json_encode($product_data)
                ]
            );

            if (is_wp_error($response)) {
                throw new \Exception('Failed to create PayPal product: ' . $response->get_error_message());
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!isset($body['id'])) {
                throw new \Exception('Invalid response from PayPal - no product ID');
            }

            // Store product ID
            update_post_meta($pack->ID, '_paypal_product_id', $body['id']);
            
            return $body['id'];

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Product creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user ID by subscription ID
     */
    private function get_user_id_by_subscription($subscription_id) {
        global $wpdb;
        
        // First try from subscribers table
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}wpuf_subscribers 
            WHERE transaction_id = %s",
            $subscription_id
        ));
        
        if ($user_id) {
            return $user_id;
        }
        
        // Then try from usermeta table
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = '_wpuf_subscription_pack' 
            AND meta_value LIKE %s",
            '%' . $wpdb->esc_like($subscription_id) . '%'
        ));

        return $user_id;
    }

    /**
     * Handle PayPal return
     */
    public function handle_paypal_return() {
        $token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
        $payer_id = isset($_GET['PayerID']) ? sanitize_text_field($_GET['PayerID']) : '';
        
        if ( empty($token) || empty($payer_id) ) {
            if ( isset($_GET['payment_status']) ) {
                return; // Just show success page
            }
            // Redirect to subscription page with error message
            $error_url = add_query_arg([
                'action' => 'wpuf_paypal_success',
                'type' => isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'pack',
                'item_number' => isset($_GET['item_number']) ? sanitize_text_field($_GET['item_number']) : '',
                'wpuf_payment_method' => 'paypal',
                'payment_status' => 'failed',
                'error' => urlencode('Invalid payment session. Please try again.')
            ], wpuf_payment_success_page([
                'type' => isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'pack',
                'item_number' => isset($_GET['item_number']) ? sanitize_text_field($_GET['item_number']) : '',
                'wpuf_payment_method' => 'paypal'
            ]));
            
            wp_redirect($error_url);
            exit;
        }

        try {
            // Get access token and capture payment
            $access_token = $this->get_access_token();
            $capture_url = ($this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com') . 
                          '/v2/checkout/orders/' . $token . '/capture';

            $response = wp_remote_post($capture_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            if (is_wp_error($response)) {
                throw new \Exception('Failed to capture PayPal payment');
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!isset($body['status']) || $body['status'] !== 'COMPLETED') {
                throw new \Exception('Payment not completed');
            }

            // Redirect to success page
            $success_url = add_query_arg([
                'action' => 'wpuf_paypal_success',
                'type' => isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'pack',
                'item_number' => isset($_GET['item_number']) ? sanitize_text_field($_GET['item_number']) : '',
                'wpuf_payment_method' => 'paypal',
                'payment_status' => 'completed'
            ], wpuf_payment_success_page([
                'type' => isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'pack',
                'item_number' => isset($_GET['item_number']) ? sanitize_text_field($_GET['item_number']) : '',
                'wpuf_payment_method' => 'paypal'
            ]));

            wp_redirect($success_url);
            exit;

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Payment capture failed: ' . $e->getMessage());
            wp_redirect(home_url('/payment-error/?error=' . urlencode($e->getMessage())));
            exit;
        }
    }

    /**
     * Check PayPal return
     */
    public function check_paypal_return() {
        if ( ! isset($_GET['action']) || 'wpuf_paypal_success' !== $_GET['action'] ) {
            return;
        }

        if ( isset($_GET['payment_completed']) ) {
            return;
        }

        $this->handle_paypal_return();
    }

    /**
     * Add pending payment page handler
     */
    public function handle_pending_payment() {
        if ( ! isset($_GET['action']) || 'wpuf_paypal_pending' !== $_GET['action'] ) {
            return;
        }

        $capture_id = isset($_GET['capture_id']) ? sanitize_text_field($_GET['capture_id']) : '';
        if ( empty($capture_id) ) {
            wp_redirect(home_url('/payment-error/?error=' . urlencode('Invalid capture ID')));
            exit;
        }

        // Show pending payment page
        include WPUF_ROOT . '/templates/payment-pending.php';
        exit;
    }

    /**
     * Handle subscription cancellation from webhook
     */
    private function handle_subscription_cancelled($subscription) {
        try {
            error_log('WPUF PayPal: Processing subscription cancellation webhook: ' . print_r($subscription, true));

            // Extract custom data
            $custom_data = [];
            if (isset($subscription['custom_id'])) {
                $custom_data = json_decode($subscription['custom_id'], true);
            }

            if (!$custom_data || !isset($custom_data['user_id'])) {
                throw new \Exception('Invalid custom data in subscription');
            }

            $user_id = $custom_data['user_id'];
            $subscription_id = $subscription['id'];

            // Update subscription status in user meta
            $subscription_data = [
                'profile_id' => $subscription_id,
                'status' => 'cancel',
                'updated' => $this->get_current_time_utc()
            ];
            
            update_user_meta($user_id, '_wpuf_subscription_pack', $subscription_data);

            // Update subscriber table
            global $wpdb;
            $wpdb->update(
                $wpdb->prefix . 'wpuf_subscribers',
                [
                    'subscribtion_status' => 'cancel',
                    'expire' => $this->get_current_time_utc()
                ],
                [
                    'user_id' => $user_id,
                    'transaction_id' => $subscription_id,
                    'gateway' => 'PayPal'
                ],
                ['%s', '%s'],
                ['%d', '%s', '%s']
            );

            error_log('WPUF PayPal: Subscription cancel via webhook for user: ' . $user_id);

            // Trigger action for other plugins
            do_action('wpuf_paypal_subscription_cancelled', $user_id, $subscription_id);

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Webhook subscription cancellation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle subscription activation (after trial period)
     */
    private function handle_subscription_activated($subscription) {
        try {
            error_log('WPUF PayPal: Processing subscription activation: ' . print_r($subscription, true));
            
            // Extract custom data
            $custom_data = [];
            if (isset($subscription['custom_id'])) {
                $custom_data = json_decode($subscription['custom_id'], true);
            }
            
            if (!$custom_data || !isset($custom_data['user_id'])) {
                // Try to find the user based on subscription ID
                $subscription_id = $subscription['id'];
                $user_id = $this->get_user_id_by_subscription($subscription_id);
                
                if (!$user_id) {
                    throw new \Exception('Could not find user for subscription: ' . $subscription_id);
                }
            } else {
                $user_id = $custom_data['user_id'];
            }
            
            // Get the subscription pack
            $subscription_id = $subscription['id'];
            $user_pack = get_user_meta($user_id, '_wpuf_subscription_pack', true);
            
            if (!$user_pack || !isset($user_pack['pack_id'])) {
                throw new \Exception('No subscription pack found for user: ' . $user_id);
            }
            
            $pack_id = $user_pack['pack_id'];
            
            // Update subscription status if needed
            if (isset($user_pack['status']) && 'completed' !== $user_pack['status']) {
                $user_pack['status'] = 'completed';
                update_user_meta($user_id, '_wpuf_subscription_pack', $user_pack);
                update_user_meta($user_id,'_wpuf_paypal_subscription_id', $subscription_id);
            }
            
            // If this is the first payment after a trial, create a payment record
            if (isset($user_pack['trial']) && 'yes' === $user_pack['trial']) {
                // Get subscription details from PayPal
                $access_token = $this->get_access_token();
                $subscription_url = ($this->test_mode ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com') . 
                                    '/v1/billing/subscriptions/' . $subscription_id;
                
                $response = wp_remote_get($subscription_url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type' => 'application/json'
                    ]
                ]);
                
                if (is_wp_error($response)) {
                    throw new \Exception('Failed to fetch subscription details: ' . $response->get_error_message());
                }
                
                $subscription_details = json_decode(wp_remote_retrieve_body($response), true);
                
                // Create payment record for the first real payment
                if (isset($subscription_details['billing_info']['last_payment'])) {
                    $payment = $subscription_details['billing_info']['last_payment'];
                    
                    $payment_data = [
                        'user_id' => $user_id,
                        'status' => 'completed',
                        'subtotal' => $payment['amount']['value'],
                        'tax' => 0, // You may need to calculate tax
                        'cost' => $payment['amount']['value'],
                        'post_id' => 0,
                        'pack_id' => $pack_id,
                        'payer_first_name' => get_user_meta($user_id, 'first_name', true),
                        'payer_last_name' => get_user_meta($user_id, 'last_name', true),
                        'payer_email' => get_user_by('id', $user_id)->user_email,
                        'payment_type' => 'PayPal',
                        'transaction_id' => $payment['id'],
                        'created' => $this->get_current_time_utc(),
                    ];
                    
                    \WeDevs\Wpuf\Frontend\Payment::insert_payment($payment_data, $payment['id'], true);
                    error_log('WPUF PayPal: First payment after trial created for user: ' . $user_id);
                }
            }
            
            error_log('WPUF PayPal: Subscription activated successfully for user: ' . $user_id);
            
        } catch (\Exception $e) {
            error_log('WPUF PayPal: Subscription activation handling failed: ' . $e->getMessage());
        }
    }

}

// Register webhook endpoint
add_action('init', function() {
    add_rewrite_rule(
        '^webhook_triggered/?$',
        'index.php?action=webhook_triggered=1',
        'top'
    );
});

// Add query var
add_filter('query_vars', function($vars) {
    $vars[] = 'action';
    return $vars;
});

// Handle webhook request
add_action('template_redirect', function() {
    if ( 'webhook_triggered' === get_query_var('action') ) {
        $paypal = new \WeDevs\Wpuf\Lib\Gateway\Paypal();
        $raw_input = file_get_contents('php://input');
        $paypal->process_webhook($raw_input);
        exit;
    }
});