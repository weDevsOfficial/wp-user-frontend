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
<p><?php esc_html_e( "You've subscribed to the following package.", 'wp-user-frontend' ); ?></p>
<div class="wpuf_sub_info">
    <h3><?php esc_html_e( 'Subscription Details', 'wp-user-frontend' ); ?></h3>
    <div class="wpuf-text">
        <div><strong><?php esc_html_e( 'Subcription Name: ', 'wp-user-frontend' ); ?></strong><?php echo esc_html( $pack->post_title ); ?></div>
        <div>
            <strong><?php esc_html_e( 'Package & billing details: ', 'wp-user-frontend' ); ?></strong>
            <?php echo esc_html( $billing_amount . ' ' . $recurring_des ); ?>
        </div>
        <?php if ( is_wp_error( $user_sub ) ) { ?>
        <div>
            <strong><?php esc_html_e( 'Subscription Status: ', 'wp-user-frontend' ); ?></strong>
            <?php esc_html_e( 'Subscription Expired!', 'wp-user-frontend' ); ?>
        </div>
			<?php
        } else {

			if ( ! empty( $user_sub['total_feature_item'] ) && -1 != intval( $user_sub['total_feature_item'] ) ) {
				?>
                <div><strong><?php esc_html_e( 'Number of featured item: ', 'wp-user-frontend' ); ?></strong><?php echo esc_html( $user_sub['total_feature_item'] ); ?></div>
            <?php } else if ( ! empty( $user_sub['total_feature_item'] ) && -1 == intval( $user_sub['total_feature_item'] ) ) {
                ?>
                <div><strong><?php esc_html_e( 'Number of featured item: ', 'wp-user-frontend' ); ?></strong><?php esc_html_e( 'Unlimited', 'wp-user-frontend' ); ?></div>
            <?php }
             ?>
            <?php if(!empty($user_sub['posts']['wp_block']) && -1 != intval( $user_sub['posts']['wp_block'] ) ){ ?>
                <div><strong><?php esc_html_e( 'Number of reusable blocks: ', 'wp-user-frontend' ); ?></strong><?php echo esc_html( $user_sub['posts']['wp_block'] ); ?></div>
            <?php } else if ( ! empty( $user_sub['posts']['wp_block'] ) && -1 == intval( $user_sub['posts']['wp_block'] ) ) {
                ?>
                <div><strong><?php esc_html_e( 'Number of reusable blocks: ', 'wp-user-frontend' ); ?></strong><?php esc_html_e( 'Unlimited', 'wp-user-frontend' ); ?></div>
            <?php } ?>

            <div>
                <strong><?php esc_html_e( 'Remaining post: ', 'wp-user-frontend' ); ?></strong>
                <?php
                $i = 0;
                $post_count = 0;
                if ( ! empty( $user_sub['posts'] ) ) {
                    foreach ( $user_sub['posts'] as $key => $value ) {
                        $value = intval( $value );
                        if ( $value === 0 ) {
                            continue;
                        }
                        $post_type_obj = get_post_type_object( $key );
                        if ( ! $post_type_obj ) {
                            continue;
                        }
                        ++$post_count;
                    }
                }
                ?>
                <div id="wpuf-remaining-posts-list">
                <?php
                $i = 0;
                if ( ! empty( $user_sub['posts'] ) ) {
                    foreach ( $user_sub['posts'] as $key => $value ) {
                        $value = intval( $value );
                        if ( $value === 0 ) {
                            continue;
                        }
                        $post_type_obj = get_post_type_object( $key );
                        if ( ! $post_type_obj ) {
                            continue;
                        }
                        $value = ( -1 === intval( $value ) ) ? __( 'Unlimited', 'wp-user-frontend' ) : $value;
                        $hidden_class = ( $i >= 3 ) ? 'wpuf-remaining-post-hidden' : '';
                        ?>
                        <div class="<?php echo esc_attr( $hidden_class ); ?>"><?php echo esc_html( $post_type_obj->labels->name ) . ': ' . esc_html( $value ); ?></div>
                        <?php
                        ++$i;
                    }
                }
                echo $i ? '' : esc_attr( $i );
                ?>
                </div>
                <?php if ( $post_count > 3 ) : ?>
                    <button type="button" id="wpuf-remaining-posts-toggle" class="btn btn-link" style="padding:0;"><?php esc_html_e( 'Show More', 'wp-user-frontend' ); ?></button>
                    <script>
                        (function(){
                            var btn = document.getElementById('wpuf-remaining-posts-toggle');
                            var hidden = document.querySelectorAll('.wpuf-remaining-post-hidden');
                            var expanded = false;
                            btn.addEventListener('click', function() {
                                expanded = !expanded;
                                hidden.forEach(function(el) {
                                    el.style.display = expanded ? 'block' : 'none';
                                });
                                btn.textContent = expanded ? '<?php echo esc_js( __( 'Show Less', 'wp-user-frontend' ) ); ?>' : '<?php echo esc_js( __( 'Show More', 'wp-user-frontend' ) ); ?>';
                            });
                            hidden.forEach(function(el) { el.style.display = 'none'; });
                        })();
                    </script>
                <?php endif; ?>
            </div>
            <?php
            if ( 'yes' !== $user_sub['recurring'] ) {
                if ( ! empty( $user_sub['expire'] ) ) {
                    $expiry_date = ( 'unlimited' === $user_sub['expire'] ) ? __( 'Unlimited', 'wp-user-frontend' ) : wpuf_get_date( wpuf_date2mysql( $user_sub['expire'] ) );
                    ?>
                    <div class="wpuf-expire">
                        <strong><?php echo esc_html__( 'Expire date:', 'wp-user-frontend' ); ?></strong> <?php echo esc_html( $expiry_date ); ?>
                    </div>
                    <?php
                }
            }

            if ( ! empty( $user_sub['recurring'] ) && 'yes' === $user_sub['recurring'] ) {
                $subscription_data = wpuf_dashboard_get_subscription_data( $user_sub );
                if ( ! empty( $subscription_data ) ) {
                    display_subscription_details( $subscription_data );
                }
            }
        }
        ?>
    </div>
</div>
