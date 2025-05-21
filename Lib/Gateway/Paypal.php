<?php

namespace WeDevs\Wpuf\Lib\Gateway;
use WeDevs\Wpuf\Frontend\Payment;

/**
 * WP User Frontend PayPal gateway
 *
 * @since 0.8
 * @updated 2024
 */
class Paypal {
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
            if ( ! $this->verify_webhook_signature_from_input($raw_input)) {
                throw new \Exception('Webhook signature verification failed');
            }

            // Decode the webhook data
            $event = json_decode($raw_input, true);
            if ( JSON_ERROR_NONE !== json_last_error() ) {
                throw new \Exception('Invalid JSON in webhook data');
            }

            // Process PAYMENT.CAPTURE.COMPLETED event
            if ( 'PAYMENT.CAPTURE.COMPLETED' === $event['event_type'] && isset($event['resource'])) {
                $payment = $event['resource'];
                $this->process_payment_capture($payment);
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

            // Create payment record
            $data = [
                'user_id' => $custom_data['user_id'],
                'status' => 'completed',
                'subtotal' => $payment['amount']['value'],
                'tax' => isset($custom_data['tax']) ? $custom_data['tax'] : 0,
                'cost' => $custom_data['subtotal'],
                'post_id' => ($custom_data['type'] === 'post') ? $custom_data['item_number'] : 0,
                'pack_id' => ($custom_data['type'] === 'pack') ? $custom_data['item_number'] : 0,
                'payer_first_name' => $user->first_name,
                'payer_last_name' => $user->last_name,
                'payer_email' => $user->user_email,
                'payment_type' => 'PayPal',
                'transaction_id' => $payment['id'],
                'created' => current_time('mysql')
            ];

            // Insert payment record
            Payment::insert_payment($data, $payment['id'], false);
            

            // Handle subscription if needed
            if ($custom_data['type'] === 'pack') {
                $this->handle_subscription_purchase($custom_data['user_id'], $custom_data['item_number'], $payment['id']);
            }

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

        // Handle subscription
        if ($custom_data['type'] === 'pack') {
            $this->handle_subscription_purchase($custom_data['user_id'], $custom_data['item_number'], $payment['id']);
        }

        // Verify payment amount
        if ($payment['amount']['value'] != number_format($custom_data['subtotal'], 2, '.', '')) {
            throw new \Exception('Payment amount mismatch');
        }
    }

    /**
     * Handle subscription purchase
     */
    private function handle_subscription_purchase($user_id, $pack_id, $transaction_id) {
        global $wpdb;

        // Check for existing subscription
        $existing_sub = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}wpuf_subscribers 
            WHERE transaction_id = %s",
            $transaction_id
        ));

        if ($existing_sub) {
            error_log('WPUF PayPal: Subscription already exists for transaction: ' . $transaction_id);
            return;
        }

        $user = get_userdata($user_id);
        $pack = get_post($pack_id);
        $pack_meta = $pack ? get_post_meta($pack->ID, '_wpuf_subscription_pack', true) : [];
        
        $is_recurring = isset($pack_meta['recurring_pay']) && $pack_meta['recurring_pay'] === 'yes';
        $expire_date = $is_recurring ? 'recurring' : date('Y-m-d H:i:s', strtotime('+1 year'));

        // Insert subscriber data
        $subscriber_data = [
            'user_id' => $user_id,
            'name' => $user->display_name,
            'subscribtion_id' => (string)$pack_id,
            'subscribtion_status' => 'active',
            'gateway' => 'PayPal',
            'transaction_id' => $transaction_id,
            'starts_from' => $this->get_current_time_utc(),
            'expire' => $expire_date
        ];

        $result = $wpdb->insert(
            $wpdb->prefix . 'wpuf_subscribers',
            $subscriber_data,
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result) {
            $this->update_user_subscription($user_id, $pack_id);
        } else {
            error_log('WPUF PayPal: Failed to insert subscriber data: ' . $wpdb->last_error);
        }
    }

    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        // Add rewrite rule for webhook endpoint
        add_rewrite_rule(
            '^payment_capture_completed/?$',
            'index.php?action=payment_capture_completed=1',
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
                home_url('/?action=payment_capture_completed'));
        }
    }

    /**
     * Handle webhook request
     */
    public function handle_webhook_request() {
        if ( 'payment_capture_completed' === get_query_var('action') && 
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
                if ( ! isset($webhook_data['event_type']) || 'PAYMENT.CAPTURE.COMPLETED' !== $webhook_data['event_type'] ) {
                    throw new \Exception('Invalid event type');
                }

                // Save to DB (process payment)
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
        
        return $date->format('Y-m-d H:i:s');
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
            $custom_data = $subscription['custom'];
            if (!$custom_data) {
                throw new \Exception('Invalid custom data in subscription');
            }

            // Store subscription details
            update_user_meta($custom_data['user_id'], '_wpuf_subscription_pack', [
                'profile_id' => $subscription['id'],
                'status' => 'active',
                'created' => current_time('mysql')
            ]);

        } catch (\Exception $e) {
            \WP_User_Frontend::log('paypal-webhook', 'Subscription creation handling failed: ' . $e->getMessage());
            error_log('[WPUF PayPal Webhook] Subscription creation handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle subscription cancellation
     */
    public function cancel_subscription($data) {
        try {
            // Log the incoming data
            error_log('WPUF PayPal: ===== Subscription Cancellation Debug Start =====');
            error_log('WPUF PayPal: Incoming cancellation data: ' . print_r($data, true));

            // Extract user_id from the input
            $user_id = is_array($data) ? (isset($data['user_id']) ? $data['user_id'] : null) : $data;
            
            if (!$user_id || !is_numeric($user_id)) {
                error_log('WPUF PayPal: Invalid user ID provided for cancellation: ' . print_r($data, true));
                throw new \Exception('Invalid user ID provided for cancellation');
            }

            error_log('WPUF PayPal: Processing cancellation for user ID: ' . $user_id);

            // Get all user meta for debugging
            $all_user_meta = get_user_meta($user_id);
            error_log('WPUF PayPal: All user meta: ' . print_r($all_user_meta, true));

            // Get user's subscription data
            $subscription = get_user_meta($user_id, '_wpuf_subscription_pack', true);
            error_log('WPUF PayPal: Raw subscription data: ' . print_r($subscription, true));
            
            if (!$subscription) {
                // Check if subscription exists in subscribers table
                global $wpdb;
                $subscriber_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}wpuf_subscribers 
                    WHERE user_id = %d AND gateway = 'PayPal' 
                    ORDER BY id DESC LIMIT 1",
                    $user_id
                ));

                if ($subscriber_data) {
                    error_log('WPUF PayPal: Found subscription in subscribers table: ' . print_r($subscriber_data, true));
                    // Update the subscription status in subscribers table
                    $wpdb->update(
                        $wpdb->prefix . 'wpuf_subscribers',
                        [
                            'subscribtion_status' => 'cancelled',
                            'expire' => current_time('mysql')
                        ],
                        ['id' => $subscriber_data->id],
                        ['%s', '%s'],
                        ['%d']
                    );
                    
                    // Update user meta
                    update_user_meta($user_id, '_wpuf_subscription_pack', [
                        'status' => 'cancelled',
                        'updated' => current_time('mysql')
                    ]);

                    error_log('WPUF PayPal: Subscription cancelled in database only');
                    return true;
                }

                error_log('WPUF PayPal: No subscription found for user: ' . $user_id);
                throw new \Exception('No subscription found for this user');
            }

            // If we have subscription data but no profile_id, it might be a one-time payment
            if (!isset($subscription['profile_id'])) {
                error_log('WPUF PayPal: No profile_id found, checking for one-time payment');
                
                // Update subscription status in database
                global $wpdb;
                $wpdb->update(
                    $wpdb->prefix . 'wpuf_subscribers',
                    [
                        'subscribtion_status' => 'cancelled',
                        'expire' => current_time('mysql')
                    ],
                    [
                        'user_id' => $user_id,
                        'gateway' => 'PayPal'
                    ],
                    ['%s', '%s'],
                    ['%d', '%s']
                );

                // Update user meta
                update_user_meta($user_id, '_wpuf_subscription_pack', [
                    'status' => 'cancelled',
                    'updated' => current_time('mysql')
                ]);

                error_log('WPUF PayPal: One-time payment subscription cancelled in database');
                return true;
            }

            $profile_id = $subscription['profile_id'];
            error_log('WPUF PayPal: Found profile_id: ' . $profile_id);

            // Get access token
            $access_token = $this->get_access_token();

            // Cancel the subscription in PayPal
            $cancel_url = ($this->test_mode ? 
                'https://api-m.sandbox.paypal.com' : 
                'https://api-m.paypal.com') . '/v1/billing/subscriptions/' . $profile_id . '/cancel';

            $response = wp_remote_post($cancel_url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'reason' => 'Customer requested cancellation'
                ])
            ]);

            if (is_wp_error($response)) {
                error_log('WPUF PayPal: Failed to cancel subscription: ' . $response->get_error_message());
                throw new \Exception('Failed to cancel subscription: ' . $response->get_error_message());
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);
            
            if (isset($body['error'])) {
                error_log('WPUF PayPal: PayPal API error: ' . print_r($body['error'], true));
                throw new \Exception('PayPal API error: ' . $body['error']['message']);
            }

            // Update local subscription status
            $updated_subscription = [
                'profile_id' => $profile_id,
                'status' => 'cancelled',
                'updated' => current_time('mysql')
            ];
            
            update_user_meta($user_id, '_wpuf_subscription_pack', $updated_subscription);
            error_log('WPUF PayPal: Updated subscription meta: ' . print_r($updated_subscription, true));

            // Update subscriber table
            $update_result = $wpdb->update(
                $wpdb->prefix . 'wpuf_subscribers',
                [
                    'subscribtion_status' => 'cancelled',
                    'expire' => current_time('mysql')
                ],
                [
                    'user_id' => $user_id,
                    'gateway' => 'PayPal'
                ],
                ['%s', '%s'],
                ['%d', '%s']
            );

            if ($update_result === false) {
                error_log('WPUF PayPal: Database error updating subscribers table: ' . $wpdb->last_error);
            } else {
                error_log('WPUF PayPal: Updated subscribers table. Rows affected: ' . $update_result);
            }

            error_log('WPUF PayPal: Subscription cancelled successfully for user: ' . $user_id);

            // Trigger action for other plugins
            do_action('wpuf_paypal_subscription_cancelled', $user_id, $profile_id);

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
    private function handle_subscription_payment($payment) {
        try {
            $subscription_id = $payment['billing_agreement_id'];
            $user_id = $this->get_user_id_by_subscription($subscription_id);
            
            if (!$user_id) {
                throw new \Exception('User not found for subscription: ' . $subscription_id);
            }

            $data = [
                'user_id' => $user_id,
                'status' => 'completed',
                'subtotal' => $payment['amount']['total'],
                'currency' => $payment['amount']['currency'],
                'payment_type' => 'Paypal',
                'transaction_id' => $payment['id'],
                'created' => current_time('mysql')
            ];

            // Insert payment record
            \WeDevs\Wpuf\Frontend\Payment::insert_payment($data, $payment['id'], true);

        } catch (\Exception $e) {
            \WP_User_Frontend::log('paypal-webhook', 'Subscription payment handling failed: ' . $e->getMessage());
            error_log('[WPUF PayPal Webhook] Subscription payment handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user ID by subscription ID
     */
    private function get_user_id_by_subscription($subscription_id) {
        global $wpdb;
        
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
            WHERE meta_key = '_wpuf_subscription_pack' 
            AND meta_value LIKE %s",
            '%' . $wpdb->esc_like($subscription_id) . '%'
        ));

        return $user_id;
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

    public function subscription_cancel( $user_id ) {
        $sub_meta = 'cancel';
        wpuf_get_user( $user_id )->subscription()->update_meta( $sub_meta );
    }

    /**
     * Handle subscription suspension
     */
    private function handle_subscription_suspended($subscription) {
        try {
            $custom_data = $subscription['custom'];
            if (!$custom_data) {
                throw new \Exception('Invalid custom data in subscription');
            }

            update_user_meta($custom_data['user_id'], '_wpuf_subscription_pack', [
                'profile_id' => $subscription['id'],
                'status' => 'suspended',
                'updated' => current_time('mysql')
            ]);

        } catch (\Exception $e) {
            \WP_User_Frontend::log('paypal-webhook', 'Subscription suspension handling failed: ' . $e->getMessage());
            error_log('[WPUF PayPal Webhook] Subscription suspension handling failed: ' . $e->getMessage());
            throw $e;
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
     * Update user subscription
     *
     * @param int $user_id
     * @param int $pack_id
     */
    private function update_user_subscription($user_id, $pack_id) {
        if (empty($user_id) || empty($pack_id)) {
            return;
        }

        wpuf_get_user($user_id)->subscription()->add_pack($pack_id, null, false, 'PayPal');
        delete_user_meta($user_id, '_wpuf_user_active');
        delete_user_meta($user_id, '_wpuf_activation_key');
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

            // Handle coupon if present
            if ( isset($_POST['coupon_id']) && ! empty($_POST['coupon_id']) ) {
                $billing_amount = wpuf_pro()->coupon->discount($billing_amount, $_POST['coupon_id'], $data['item_number']);
                $coupon_id = $_POST['coupon_id'];
            } else {
                $coupon_id = '';
            }

            $data['subtotal'] = $billing_amount;
            $billing_amount = apply_filters('wpuf_payment_amount', $data['subtotal']);
            $data['tax'] = $billing_amount - $data['subtotal'];

            // Handle free payments
            if ( 0 == $billing_amount ) {
                wpuf_get_user($user_id)->subscription()->add_pack($data['item_number'], null, false, 'Free');
                wp_redirect($return_url);
                exit();
            }

            // Get access token
            $access_token = $this->get_access_token();

            if ( 'pack' === $data['type'] && wpuf_is_checkbox_or_toggle_on($data['custom']['recurring_pay']) ) {
                // Handle recurring payment setup
                error_log('WPUF PayPal: Setting up recurring payment');
                // Add recurring payment logic here
            } else {
                // Prepare payment data
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
            }

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

        } catch (\Exception $e) {
            error_log('WPUF PayPal: Payment preparation failed: ' . $e->getMessage());
            wp_die($e->getMessage());
        }
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
}

// Register webhook endpoint
add_action('init', function() {
    add_rewrite_rule(
        '^payment_capture_completed/?$',
        'index.php?action=payment_capture_completed=1',
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
    if ( 'payment_capture_completed' === get_query_var('action') ) {
        $paypal = new \WeDevs\Wpuf\Lib\Gateway\Paypal();
        $raw_input = file_get_contents('php://input');
        $paypal->process_webhook($raw_input);
        exit;
    }
});