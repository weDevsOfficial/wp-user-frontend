<?php

if ( ! function_exists( 'wpuf_dashboard_get_subscription_data' ) ) {

    /**
     * Get subscription data for dashboard
     *
     * @since 4.1.5
     *
     * @param array $user_sub User subscription data
     *
     * @return array Subscription data
     */
    function wpuf_dashboard_get_subscription_data( $user_sub ) {
        global $wpdb;

		$user_id      = get_current_user_id();
		$pack_id      = $user_sub['pack_id'];
		$subscription = wpuf()->subscription->get_subscription( $pack_id );

        if ( ! $subscription || is_wp_error( $subscription ) || empty( $subscription->meta_value ) ) {
            return [];
        }
		// Get payment gateway
		$payment_gateway = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT payment_type
            FROM {$wpdb->prefix}wpuf_transaction
            WHERE user_id = %d
            AND status = 'completed'
            ORDER BY created DESC",
                $user_id
            )
		);
		$payment_gateway = $payment_gateway ? strtolower( $payment_gateway ) : '';

		// Get last payment date
		$last_payment_date = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT created
            FROM {$wpdb->prefix}wpuf_transaction
            WHERE user_id = %d
            AND status = 'completed'
            ORDER BY created DESC
            LIMIT 1",
                $user_id
            )
		);

		// Get billing cycle details
		$cycle_number = intval( $subscription->meta_value['billing_cycle_number'] );
		$cycle_period = $subscription->meta_value['cycle_period'];

		// Get trial details
		$trial_status        = $subscription->meta_value['_trial_status'];
		$trial_duration      = $subscription->meta_value['_trial_duration'];
		$trial_duration_type = $subscription->meta_value['_trial_duration_type'];


		return [
			'payment_gateway'       => $payment_gateway,
			'last_payment_date'     => $last_payment_date,
			'cycle_number'          => $cycle_number,
			'cycle_period'          => $cycle_period,
			'trial_status'          => $trial_status,
			'trial_duration'        => $trial_duration,
			'trial_duration_type'   => $trial_duration_type,
		];
	}
}

/**
 * Display subscription details
 *
 * @since 4.1.5
 *
 * @param array $subscription_data Subscription data
 */
function display_subscription_details( $subscription_data ) {
	$trial_html = get_trial_expiration_html( $subscription_data );
	$billing_html = get_next_billing_html( $subscription_data );
	?>
        <br>
	<?php if ( ! empty( $subscription_data['trial_status'] ) && wpuf_is_checkbox_or_toggle_on( $subscription_data['trial_status'] ) ) : ?>
            <?php echo wp_kses_post( $trial_html ); ?>
        <?php elseif ( ! empty( $subscription_data['trial_status'] ) && ! wpuf_is_checkbox_or_toggle_on( $subscription_data['trial_status'] ) ) : ?>
            <div class="wpuf-recurring-info">
                <?php echo wp_kses_post( $billing_html ); ?>
            </div>
        <?php endif; ?>

        <p><i><?php esc_html_e( 'To cancel the pack, press the following cancel button.', 'wp-user-frontend' ); ?></i></p>
        <form action="" method="post" style="text-align: center;">
		<?php wp_nonce_field( 'wpuf-sub-cancel' ); ?>
            <input type="hidden" name="gateway" value="<?php echo esc_attr( $subscription_data['payment_gateway'] ); ?>">
            <input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
            <input type="submit" name="wpuf_cancel_subscription" class="btn btn-sm btn-danger" value="<?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?>">
        </form>
        <?php
}

/**
 * Get trial expiration HTML
 *
 * @since 4.1.5
 *
 * @param array $subscription_data Subscription data
 *
 * @return string Trial expiration HTML
 */
function get_trial_expiration_html( $subscription_data ) {
	if ( ! empty( $subscription_data['trial_status'] ) && 'on' !== $subscription_data['trial_status'] ) {
		return '';
	}

	$trial_expiration_date = gmdate(
		'Y-m-d',
		strtotime(
			"+{$subscription_data['trial_duration']} {$subscription_data['trial_duration_type']}",
			strtotime( $subscription_data['last_payment_date'] )
		)
	);

	return sprintf(
		'<div><strong>%s</strong> %s</div>',
		esc_html__( 'Trial expiration date:', 'wp-user-frontend' ),
		esc_html( $trial_expiration_date )
	);
}

/**
 * Get next billing HTML
 *
 * @since 4.1.5
 *
 * @param array $subscription_data Subscription data
 *
 * @return string Next billing HTML
 */
function get_next_billing_html( $subscription_data ) {
	if ( ! $subscription_data['last_payment_date'] ||
		! $subscription_data['cycle_period'] ||
		empty( $subscription_data['cycle_number'] ) ||
		-1 === intval( $subscription_data['cycle_number'] ) ) {
		return sprintf(
			'<div><strong>%s</strong> %s</div>',
			esc_html__( 'Next billing date:', 'wp-user-frontend' ),
			esc_html__( 'N/A', 'wp-user-frontend' )
		);
	}

	$next_billing_date = gmdate(
		'Y-m-d',
		strtotime(
			"+{$subscription_data['cycle_number']} {$subscription_data['cycle_period']}",
			strtotime( $subscription_data['last_payment_date'] )
		)
	);
	return sprintf(
		'<div><strong>%s</strong> %s</div>',
		esc_html__( 'Next billing date:', 'wp-user-frontend' ),
		esc_html( $next_billing_date )
	);
}
?>
<!-- Subscription Page Header -->
<div class="wpuf-bg-transparent wpuf-rounded-t-lg wpuf-mb-3">
    <h2 class="wpuf-text-gray-700 wpuf-font-bold wpuf-text-[32px] wpuf-leading-[48px] wpuf-tracking-[0.13px] wpuf-m-0">
        <?php esc_html_e( 'Subscription', 'wp-user-frontend' ); ?>
    </h2>
</div>

<div class="wpuf-bg-transparent wpuf-mb-[48px]">
    <p class="wpuf-text-gray-400 wpuf-font-normal wpuf-text-[18px] wpuf-leading-[24px] wpuf-tracking-[0.13px] wpuf-m-0">
        <?php esc_html_e( 'View your current subscription status, billing details, and renewal information.', 'wp-user-frontend' ); ?>
    </p>
</div>

<!-- Single Subscription Card Container -->
<div class="wpuf-subscription-cards wpuf-single-card">
    <?php if ( is_wp_error( $user_sub ) ) { ?>
        <!-- Expired Subscription Card -->
        <div class="wpuf-subscription-card wpuf-subscription-expired">
            <div class="wpuf-subscription-card-header">
                <h3 class="wpuf-subscription-name"><?php esc_html_e( 'Subscription:', 'wp-user-frontend' ); ?> <?php echo esc_html( $pack->post_title ); ?></h3>
                <span class="wpuf-subscription-status wpuf-status-expired"><?php esc_html_e( 'Expired', 'wp-user-frontend' ); ?></span>
            </div>
            <div class="wpuf-subscription-card-body">
                <div class="wpuf-subscription-price">
                    <span class="wpuf-price"><?php echo esc_html( $billing_amount ); ?></span>
                    <?php if ( ! empty( $recurring_des ) ) : ?>
                        <span class="wpuf-price-period"><?php echo esc_html( $recurring_des ); ?></span>
                    <?php endif; ?>
                </div>
                <p class="wpuf-subscription-expired-message"><?php esc_html_e( 'Subscription Expired!', 'wp-user-frontend' ); ?></p>
            </div>
        </div>
    <?php } else { ?>
        <!-- Active Subscription Card -->
        <div class="wpuf-subscription-card">
            <div class="wpuf-subscription-card-header">
                <div class="wpuf-subscription-header-content">
                    <h3 class="wpuf-subscription-name"><?php esc_html_e( 'Subscription:', 'wp-user-frontend' ); ?> <?php echo esc_html( $pack->post_title ); ?></h3>
                    <?php
                    // Get expiry or next payment info
                    $expire_info = '';
                    $next_payment = '';
                    if ( 'yes' !== $user_sub['recurring'] ) {
                        if ( ! empty( $user_sub['expire'] ) ) {
                            $expiry_date = ( 'unlimited' === $user_sub['expire'] ) ? __( 'Unlimited', 'wp-user-frontend' ) : wpuf_get_date( wpuf_date2mysql( $user_sub['expire'] ) );
                            $expire_info = sprintf( __( 'Expire: %s', 'wp-user-frontend' ), $expiry_date );
                        }
                    } else {
                        $subscription_data = wpuf_dashboard_get_subscription_data( $user_sub );
                        if ( ! empty( $subscription_data ) ) {
                            if ( ! empty( $subscription_data['trial_status'] ) && wpuf_is_checkbox_or_toggle_on( $subscription_data['trial_status'] ) ) {
                                $trial_expiration_date = gmdate(
                                    'M d, Y',
                                    strtotime(
                                        "+{$subscription_data['trial_duration']} {$subscription_data['trial_duration_type']}",
                                        strtotime( $subscription_data['last_payment_date'] )
                                    )
                                );
                                $next_payment = sprintf( __( 'Next Payment: %s', 'wp-user-frontend' ), $trial_expiration_date );
                            } elseif ( $subscription_data['last_payment_date'] && $subscription_data['cycle_period'] && ! empty( $subscription_data['cycle_number'] ) && -1 !== intval( $subscription_data['cycle_number'] ) ) {
                                $next_billing_date = gmdate(
                                    'M d, Y',
                                    strtotime(
                                        "+{$subscription_data['cycle_number']} {$subscription_data['cycle_period']}",
                                        strtotime( $subscription_data['last_payment_date'] )
                                    )
                                );
                                $next_payment = sprintf( __( 'Next Payment: %s', 'wp-user-frontend' ), $next_billing_date );
                            }
                        }
                    }
                    ?>
                    <?php if ( $expire_info ) : ?>
                        <p class="wpuf-subscription-expire-date"><?php echo esc_html( $expire_info ); ?></p>
                    <?php endif; ?>
                    <?php if ( $next_payment ) : ?>
                        <p class="wpuf-subscription-next-payment"><?php echo esc_html( $next_payment ); ?></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="wpuf-show-details-btn" data-target="subscription-details-1"><?php esc_html_e( 'Show Details', 'wp-user-frontend' ); ?></button>
            </div>

            <div class="wpuf-subscription-card-body">
                <div class="wpuf-subscription-price">
                    <span class="wpuf-price"><?php echo esc_html( $billing_amount ); ?></span>
                    <?php if ( ! empty( $recurring_des ) ) : ?>
                        <span class="wpuf-price-period"><?php echo esc_html( $recurring_des ); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Compact Features List (Initial View - shows first row of 4) -->
                <ul class="wpuf-subscription-features wpuf-features-compact">
                    <?php
                    $feature_count = 0;
                    $max_initial_features = 4; // Show 4 features (1 row)
                    $compact_displayed_features = []; // Track features shown in compact view

                    // Remaining posts
                    if ( ! empty( $user_sub['posts'] ) ) {
                        foreach ( $user_sub['posts'] as $key => $value ) {
                            if ( $feature_count >= $max_initial_features ) {
                                break;
                            }
                            $value = intval( $value );
                            if ( 0 === $value ) {
                                continue;
                            }
                            $post_type_obj = get_post_type_object( $key );
                            if ( ! $post_type_obj ) {
                                continue;
                            }
                            $value_display = ( -1 === intval( $value ) ) ? __( 'Unlimited', 'wp-user-frontend' ) : $value;
                            // Track this feature as displayed in compact view
                            $compact_displayed_features[] = strtolower( trim( $post_type_obj->labels->name ) );
                            ?>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.75 10.5L9 12.75L12.75 7.5M18.75 9.75C18.75 14.7206 14.7206 18.75 9.75 18.75C4.77944 18.75 0.75 14.7206 0.75 9.75C0.75 4.77944 4.77944 0.75 9.75 0.75C14.7206 0.75 18.75 4.77944 18.75 9.75Z" stroke="#99A7B2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><?php echo esc_html( $post_type_obj->labels->name ); ?>: <strong><?php echo esc_html( $value_display ); ?></strong></span>
                            </li>
                            <?php
                            $feature_count++;
                        }
                    }

                    // Compute display value for user requests
                    $user_requests_display = ( isset( $user_sub['total_feature_item'] ) && -1 === intval( $user_sub['total_feature_item'] ) )
                        ? __( 'Unlimited', 'wp-user-frontend' )
                        : ( isset( $user_sub['total_feature_item'] ) ? intval( $user_sub['total_feature_item'] ) : __( 'Unlimited', 'wp-user-frontend' ) );

                    // Add other features to reach 4
                    $basic_features = [
                        [ 'label' => __( 'Template Parts', 'wp-user-frontend' ), 'value' => __( 'Unlimited', 'wp-user-frontend' ) ],
                        [ 'label' => __( 'User Requests', 'wp-user-frontend' ), 'value' => $user_requests_display ],
                        [ 'label' => __( 'Global Styles', 'wp-user-frontend' ), 'value' => __( 'Unlimited', 'wp-user-frontend' ) ],
                        [ 'label' => __( 'Pages', 'wp-user-frontend' ), 'value' => __( 'Unlimited', 'wp-user-frontend' ) ],
                    ];

                    for ( $i = $feature_count; $i < $max_initial_features && $i < count( $basic_features ); $i++ ) {
                        // Track this feature as displayed in compact view
                        $compact_displayed_features[] = strtolower( trim( $basic_features[ $i ]['label'] ) );
                        ?>
                        <li>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.75 10.5L9 12.75L12.75 7.5M18.75 9.75C18.75 14.7206 14.7206 18.75 9.75 18.75C4.77944 18.75 0.75 14.7206 0.75 9.75C0.75 4.77944 4.77944 0.75 9.75 0.75C14.7206 0.75 18.75 4.77944 18.75 9.75Z" stroke="#99A7B2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span><?php echo esc_html( $basic_features[ $i ]['label'] ); ?>: <strong><?php echo esc_html( $basic_features[ $i ]['value'] ); ?></strong></span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>

                <!-- Expandable Detailed Features (Hidden Initially) -->
                <div class="wpuf-subscription-details" id="subscription-details-1" style="display: none;">
                    <ul class="wpuf-subscription-features-detailed">
                        <?php
                        // Start with features already displayed in compact view
                        $displayed_features = $compact_displayed_features;

                        // All remaining posts (skip those already shown in compact view)
                        if ( ! empty( $user_sub['posts'] ) ) {
                            foreach ( $user_sub['posts'] as $key => $value ) {
                                $value = intval( $value );
                                if ( 0 === $value ) {
                                    continue;
                                }
                                $post_type_obj = get_post_type_object( $key );
                                if ( ! $post_type_obj ) {
                                    continue;
                                }

                                // Check if already displayed in compact view
                                $normalized_label = strtolower( trim( $post_type_obj->labels->name ) );
                                if ( in_array( $normalized_label, $displayed_features, true ) ) {
                                    continue; // Skip this one, already shown
                                }

                                $value_display = ( -1 === intval( $value ) ) ? __( 'Unlimited', 'wp-user-frontend' ) : $value;
                                // Store normalized label for matching
                                $displayed_features[] = $normalized_label;
                                ?>
                                <li>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.75 10.5L9 12.75L12.75 7.5M18.75 9.75C18.75 14.7206 14.7206 18.75 9.75 18.75C4.77944 18.75 0.75 14.7206 0.75 9.75C0.75 4.77944 4.77944 0.75 9.75 0.75C14.7206 0.75 18.75 4.77944 18.75 9.75Z" stroke="#99A7B2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span><?php echo esc_html( $post_type_obj->labels->name ); ?>: <strong><?php echo esc_html( $value_display ); ?></strong></span>
                                </li>
                                <?php
                            }
                        }

                        // Featured items (only if not already shown as posts)
                        $user_requests_normalized = strtolower( __( 'User Requests', 'wp-user-frontend' ) );
                        if ( ! empty( $user_sub['total_feature_item'] ) && ! in_array( $user_requests_normalized, $displayed_features, true ) ) {
                            $feature_value = ( -1 === intval( $user_sub['total_feature_item'] ) ) ? __( 'Unlimited', 'wp-user-frontend' ) : $user_sub['total_feature_item'];
                            $displayed_features[] = $user_requests_normalized;
                            ?>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.75 10.5L9 12.75L12.75 7.5M18.75 9.75C18.75 14.7206 14.7206 18.75 9.75 18.75C4.77944 18.75 0.75 14.7206 0.75 9.75C0.75 4.77944 4.77944 0.75 9.75 0.75C14.7206 0.75 18.75 4.77944 18.75 9.75Z" stroke="#99A7B2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><?php esc_html_e( 'User Requests', 'wp-user-frontend' ); ?>: <strong><?php echo esc_html( $feature_value ); ?></strong></span>
                            </li>
                            <?php
                        }

                        // Additional features (only if not already displayed)
                        $all_features = [
                            'Template Parts' => __( 'Template Parts', 'wp-user-frontend' ),
                            'Global Styles' => __( 'Global Styles', 'wp-user-frontend' ),
                            'Templates' => __( 'Templates', 'wp-user-frontend' ),
                            'Navigation Menus' => __( 'Navigation Menus', 'wp-user-frontend' ),
                            'Pages' => __( 'Pages', 'wp-user-frontend' ),
                            'Patterns' => __( 'Patterns', 'wp-user-frontend' ),
                            'Font Families' => __( 'Font Families', 'wp-user-frontend' ),
                            'Font Faces' => __( 'Font Faces', 'wp-user-frontend' ),
                            'Products' => __( 'Products', 'wp-user-frontend' ),
                            'Variations' => __( 'Variations', 'wp-user-frontend' ),
                            'Orders' => __( 'Orders', 'wp-user-frontend' ),
                            'Refunds' => __( 'Refunds', 'wp-user-frontend' ),
                            'Coupons' => __( 'Coupons', 'wp-user-frontend' ),
                        ];

                        foreach ( $all_features as $feature_key => $feature_label ) :
                            // Normalize and check if already displayed
                            $normalized_label = strtolower( trim( $feature_label ) );

                            if ( in_array( $normalized_label, $displayed_features, true ) ) {
                                continue;
                            }

                            // Add to displayed features to avoid future duplicates
                            $displayed_features[] = $normalized_label;

                            // Get the value - always "Unlimited" for these additional features
                            $feature_value = __( 'Unlimited', 'wp-user-frontend' );
                            ?>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.75 10.5L9 12.75L12.75 7.5M18.75 9.75C18.75 14.7206 14.7206 18.75 9.75 18.75C4.77944 18.75 0.75 14.7206 0.75 9.75C0.75 4.77944 4.77944 0.75 9.75 0.75C14.7206 0.75 18.75 4.77944 18.75 9.75Z" stroke="#99A7B2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><?php echo esc_html( $feature_label ); ?>: <strong><?php echo esc_html( $feature_value ); ?></strong></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Show All Button -->
                <button type="button" class="wpuf-show-all-btn" id="wpuf-toggle-details"><?php esc_html_e( 'Show All', 'wp-user-frontend' ); ?></button>
            </div>
        </div>

        <script>
        (function() {
            var toggleBtn = document.getElementById('wpuf-toggle-details');
            var showDetailsBtn = document.querySelector('.wpuf-show-details-btn');
            var detailsSection = document.getElementById('subscription-details-1');
            var compactFeatures = document.querySelector('.wpuf-features-compact');
            var isExpanded = false;

            // Show Details button handler
            if (showDetailsBtn) {
                showDetailsBtn.addEventListener('click', function() {
                    isExpanded = !isExpanded;
                    if (isExpanded) {
                        detailsSection.style.display = 'block';
                        compactFeatures.style.display = 'none';
                        showDetailsBtn.textContent = '<?php echo esc_js( __( 'Hide Details', 'wp-user-frontend' ) ); ?>';
                        toggleBtn.style.display = 'none';
                    } else {
                        detailsSection.style.display = 'none';
                        compactFeatures.style.display = 'block';
                        showDetailsBtn.textContent = '<?php echo esc_js( __( 'Show Details', 'wp-user-frontend' ) ); ?>';
                        toggleBtn.style.display = 'inline-flex';
                    }
                });
            }

            // Show All button handler
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    detailsSection.style.display = 'block';
                    compactFeatures.style.display = 'none';
                    toggleBtn.style.display = 'none';
                    if (showDetailsBtn) {
                        showDetailsBtn.textContent = '<?php echo esc_js( __( 'Hide Details', 'wp-user-frontend' ) ); ?>';
                        isExpanded = true;
                    }
                });
            }
        })();
        </script>
    <?php } ?>
</div>

<!-- Cancel Subscription Section (if recurring) -->
<?php if ( ! is_wp_error( $user_sub ) && ! empty( $user_sub['recurring'] ) && 'yes' === $user_sub['recurring'] ) : ?>
    <?php
    $subscription_data = wpuf_dashboard_get_subscription_data( $user_sub );
    if ( ! empty( $subscription_data ) ) :
        ?>
        <div class="wpuf-cancel-subscription-section">
            <p class="wpuf-cancel-text"><?php esc_html_e( 'To cancel the pack, press the following cancel button.', 'wp-user-frontend' ); ?></p>
            <form action="" method="post" class="wpuf-cancel-form">
                <?php wp_nonce_field( 'wpuf-sub-cancel' ); ?>
                <input type="hidden" name="gateway" value="<?php echo esc_attr( $subscription_data['payment_gateway'] ); ?>">
                <input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
                <button type="submit" name="wpuf_cancel_subscription" class="wpuf-cancel-btn">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?php esc_html_e( 'Cancel This Subscription', 'wp-user-frontend' ); ?>
                </button>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>
