<?php
/**
 * Subscription pack display
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Admin\Subscriptions;

use WeDevs\Wpuf\Admin\Forms\Form;
use WeDevs\Wpuf\Admin\Subscription;
use WeDevs\Wpuf\Platform\Stores\Stores;

/**
 * The frontend side of packs: the packs page, a pack's details, the pack
 * dropdown, the notice and the permission check on forms that need a pack.
 *
 * @since WPUF_SINCE Moved out of Admin\Subscription, which keeps the hooks, the
 * static helpers and the data methods, and delegates.
 */
class Pack_Display {

    /**
     * The facade: hook objects, callbacks and the other groups go through it.
     *
     * @var Subscription
     */
    protected $admin;

    /**
     * @since WPUF_SINCE
     *
     * @param Subscription $admin The facade.
     */
    public function __construct( Subscription $admin ) {
        $this->admin = $admin;
    }

    /**
     * Show the subscription packs that are built
     * from admin Panel
     */
    public function subscription_packs( $atts = null ) {
        $react = $this->react_markup( 'plans', 'wpuf_subscription_packs', 'packs', $atts, 'wpuf-plans-root' );

        if ( null !== $react ) {
            return $react;
        }

        //$cost_per_post = isset( $form_settings['pay_per_post_cost'] ) ? $form_settings['pay_per_post_cost'] : 0;

        // Verify nonce for security
        if ( isset( $_GET['_wpnonce'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'wpuf_subscription_packs' ) ) {
                // If nonce verification fails, still allow the function to work but log the issue
                if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                    error_log( 'WPUF Subscription: Nonce verification failed for subscription_packs function' );
                }
            }
        }

        $action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        $pack_msg = isset( $_GET['pack_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['pack_msg'] ) ) : '';
        $ppp_msg  = isset( $_GET['ppp_msg'] ) ? sanitize_text_field( wp_unslash( $_GET['ppp_msg'] ) ) : '';

        $defaults = [
            'include' => '',
            'exclude' => '',
            'order'   => '',
            'orderby' => '',
        ];

        $arranged = [];
        $args     = wp_parse_args( $atts, $defaults );

        if ( $args['include'] !== '' ) {
            $pack_order = explode( ',', $args['include'] );
        }

        // Prepare arguments for get_subscriptions, only include ordering if explicitly set
        $subscription_args = [];

        // Only pass order/orderby if they were explicitly set in shortcode attributes
        if ( ! empty( $atts['order'] ) ) {
            $subscription_args['order'] = $args['order'];
        }
        if ( ! empty( $atts['orderby'] ) ) {
            $subscription_args['orderby'] = $args['orderby'];
        }
        if ( ! empty( $args['include'] ) ) {
            $subscription_args['include'] = $args['include'];
        }
        if ( ! empty( $args['exclude'] ) ) {
            $subscription_args['exclude'] = $args['exclude'];
        }

        $packs = $this->admin->get_subscriptions( $subscription_args );

        $details_meta = $this->get_details_meta_value();

        ob_start();

        if ( $action === 'wpuf_paypal_success' ) {
            printf( '<h1>%1$s</h1><p>%2$s</p>', esc_html( __( 'Payment is complete', 'wp-user-frontend' ) ), esc_html( __( 'Congratulations, your payment has been completed!', 'wp-user-frontend' ) ) );
        }

        if ( $pack_msg === 'buy_pack' ) {
            esc_html_e( 'Please buy a subscription pack to post', 'wp-user-frontend' );
        }

        if ( $ppp_msg === 'pay_per_post' ) {
            esc_html_e( 'Please buy a subscription pack to post', 'wp-user-frontend' );
        }

        $current_pack = Subscription::get_user_pack( get_current_user_id() );

        if (
            isset( $current_pack['pack_id'] ) &&
            ! empty( $current_pack['pack_id'] ) &&
            isset( $current_pack['status'] ) &&
            $current_pack['status'] === 'completed'
        ) {
            $payment_gateway = Stores::transactions()->last_completed_gateway( get_current_user_id() );

            $payment_gateway = $payment_gateway ? strtolower( $payment_gateway ) : '';
            ?>

            <p><i><?php esc_html_e( 'You have a subscription pack activated.', 'wp-user-frontend' ); ?></i></p>
            <p><i>
            <?php
                // translators: %s: pack title
                printf( esc_html__( 'Pack name: %s', 'wp-user-frontend' ), esc_html( get_the_title( $current_pack['pack_id'] ) ) );
            ?>
            </i></p>

            <?php echo '<p><i>' . esc_html__( 'To cancel the pack, press the following cancel button', 'wp-user-frontend' ) . '</i></p>'; ?>

            <form action="" id="wpuf_cancel_subscription" method="post">
                <?php wp_nonce_field( 'wpuf-sub-cancel-' . get_current_user_id() ); ?>
                <input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>">
                <input type="hidden" name="gateway" value="<?php echo esc_attr( $payment_gateway ); ?>">
                <input type="hidden" name="wpuf_cancel_subscription" value="Cancel">
                <input type="submit" name="wpuf_user_subscription_cancel" class="btn btn-sm btn-danger" value="<?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?>">
            </form>
            <?php
        }

        wpuf_load_template(
            'subscriptions/listing.php', apply_filters(
                'wpuf_subscription_listing_args', [
                    'subscription' => $this->admin,
                    'args'         => $args,
                    'packs'        => $packs,
                    'pack_order'   => isset( $pack_order ) ? $pack_order : '',
                    'details_meta' => $details_meta,
                    'current_pack' => $current_pack,
                ]
            )
        );

        $contents = ob_get_clean();

        return apply_filters( 'wpuf_subscription_packs', $contents, $packs );
    }

    /**
     * The React plans app for a subscription shortcode, or null for the
     * classic render: a theme override of its templates, the block / Elementor
     * / admin contexts, an unbuilt bundle, or a callback on the classic
     * markup filter (it rewrites finished HTML, which React output is not).
     *
     * @since WPUF_SINCE
     *
     * @param string $part        Renderer part ('plans', 'plan_info')
     * @param string $html_filter The classic markup filter
     * @param string $schema      Subscription_Schema method ('packs', 'info')
     * @param mixed  $argument    Its argument
     * @param string $classes     Wrapper classes
     *
     * @return string|null
     */
    private function react_markup( $part, $html_filter, $schema, $argument, $classes ) {
        $platform = function_exists( 'wpuf' ) && method_exists( wpuf(), 'platform' ) ? wpuf()->platform() : null;
        $renderer = $platform && $platform->has( \WeDevs\Wpuf\Frontend\Renderer::class ) ? $platform->get( \WeDevs\Wpuf\Frontend\Renderer::class ) : null;

        if ( ! $renderer || ! $renderer->mounts( $part ) || has_filter( $html_filter ) ) {
            return null;
        }

        $schemas = $platform->get( \WeDevs\Wpuf\Frontend\Subscriptions\Subscription_Schema::class );
        $data    = $renderer->build(
            $part, function () use ( $schemas, $schema, $argument ) {
				return $schemas->{$schema}( $argument );
			}
        );

        return $renderer->markup( $part, $data, '', $classes );
    }

    /**
     * Render Subscription Pack details
     *
     * @param $pack
     * @param $details_meta
     * @param string $current_pack_id
     * @param bool   $coupon_status
     */
    public function pack_details( $pack, $details_meta, $current_pack_id = '', $coupon_status = false ) {
        wpuf_load_template( 'subscriptions/pack-details.php', $this->pack_view_data( $pack, $details_meta, $current_pack_id, $coupon_status ) );
    }

    /**
     * What a pack card shows (price, cycle, trial, button label and link),
     * after `wpuf_subscription_pack_details_args`: the classic template and
     * the React plans app render from the same values.
     *
     * @since WPUF_SINCE
     *
     * @param object $pack            Pack
     * @param array  $details_meta    get_details_meta_value()
     * @param string $current_pack_id The user's pack id
     * @param bool   $coupon_status   Coupon applied
     *
     * @return array
     */
    public function pack_view_data( $pack, $details_meta, $current_pack_id = '', $coupon_status = false ) {
        // $price_with_tax = $this->wpuf_prices_include_tax();

        $user_id = get_current_user_id();

        if ( $user_id !== 0 ) {
            $pack_details = Stores::user_packs()->read( $user_id );

            if ( ! empty( $pack_details ) ) {
                $current_pack_status = isset( $pack_details['status'] ) ? $pack_details['status'] : '';
            }
        }

        $billing_amount = ( $pack->meta_value['billing_amount'] >= 0 && ! empty( $pack->meta_value['billing_amount'] ) ) ? $pack->meta_value['billing_amount'] : '0.00';
        $trial_des      = '';
        $recurring_des  = '<div class="wpuf-pack-cycle wpuf-nullamount-hide">' . __( 'One time payment', 'wp-user-frontend' ) . '</div>';

        $billing_amount = apply_filters( 'wpuf_payment_amount', $billing_amount );

        if ( $billing_amount && wpuf_is_checkbox_or_toggle_on( $pack->meta_value['recurring_pay'] ) ) {
            $cycle_number = ! empty( $pack->meta_value['billing_cycle_number'] ) && '1' !== $pack->meta_value['billing_cycle_number'] ? $pack->meta_value['billing_cycle_number'] : '';

            $recurring_des = sprintf( __( 'Every', 'wp-user-frontend' ) . ' %s %s', $cycle_number, Subscription::get_cycle_label( $pack->meta_value['cycle_period'], $pack->meta_value['billing_cycle_number'] ), $pack->meta_value['_trial_duration_type'] );

            if ( wpuf_is_checkbox_or_toggle_on( $pack->meta_value['enable_billing_limit'] ) && ! empty( $pack->meta_value['billing_limit'] ) && '-1' !== $pack->meta_value['billing_limit'] ) {
                $recurring_des .= ! empty( $pack->meta_value['billing_limit'] ) ? sprintf( ', ' . __( 'for', 'wp-user-frontend' ) . ' %s ' . __( 'installments', 'wp-user-frontend' ), $pack->meta_value['billing_limit'] ) : '';
            }

            $recurring_des = '<div class="wpuf-pack-cycle wpuf-nullamount-hide">' . $recurring_des . '</div>';
        }

        if ( $billing_amount && wpuf_is_checkbox_or_toggle_on( $pack->meta_value['recurring_pay'] ) && wpuf_is_checkbox_or_toggle_on( $pack->meta_value['_trial_status'] ) ) {
            //phpcs:ignore
            $duration = _n( $pack->meta_value['_trial_duration_type'], $pack->meta_value['_trial_duration_type'] . 's', $pack->meta_value['_trial_duration'], 'wp-user-frontend' );
            /* translators: %s: trial days */
            $trial_des = sprintf( __( 'Trial available for first %1$s %2$s', 'wp-user-frontend' ), $pack->meta_value['_trial_duration'], $duration );
        }

        $label       = wpuf_get_option( 'logged_in_label', 'wpuf_subscription_settings', false );
        $button_name = $label ? $label : __( 'Buy Now', 'wp-user-frontend' );

        if ( ! is_user_logged_in() ) {
            $label       = wpuf_get_option( 'logged_out_label', 'wpuf_subscription_settings', false );
            $button_name = $label ? $label : __( 'Sign Up', 'wp-user-frontend' );
        } elseif ( 0.0 === (float) $billing_amount ) {
            // wpuf_payment_amount (tax, Pro pricing fields) can turn '0.00' into 0.0.
            $label       = wpuf_get_option( 'free_label', 'wpuf_subscription_settings', false );
            $button_name = $label ? $label : __( 'Free', 'wp-user-frontend' );
        }

        $query_args = [
            'action'  => 'register',
            'type'    => 'wpuf_sub',
            'pack_id' => $pack->ID,
        ];
        $query_url = wp_registration_url();

        if ( $coupon_status === false && is_user_logged_in() ) {
            $query_args = [
                'action'  => 'wpuf_pay',
                'type'    => 'pack',
                'pack_id' => $pack->ID,
            ];
            $query_url = $details_meta['payment_page'];
        }

        return apply_filters(
            'wpuf_subscription_pack_details_args', [
                'pack'                  => $pack,
                'billing_amount'        => $billing_amount,
                'details_meta'          => $details_meta,
                'recurring_des'         => $recurring_des,
                'trial_des'             => $trial_des,
                'coupon_status'         => $coupon_status,
                'current_pack_id'       => $current_pack_id,
                'current_pack_status'   => isset( $current_pack_status ) ? $current_pack_status : '',
                'button_name'           => $button_name,
                'query_args'            => $query_args,
                'query_url'             => $query_url,
            ]
        );
    }

    public function packdropdown( $packs, $selected = '' ) {
        $packs = isset( $packs ) ? $packs : [];

        foreach ( $packs as $key => $pack ) {
            ?>
            <option value="<?php echo esc_attr( $pack->ID ); ?>" <?php selected( $selected, $pack->ID ); ?>><?php echo esc_attr( $pack->post_title ); ?></option>
            <?php
        }
    }

    /**
     * Show a info message when posting if payment is enabled
     */
    public function add_post_info( $form_id, $form_settings ) {
        $form              = new Form( $form_id );
        $pay_per_post      = $form->is_enabled_pay_per_post();
        $pay_per_post_cost = (float) $form->get_pay_per_post_cost();
        $force_pack        = $form->is_enabled_force_pack();
        $current_user      = wpuf_get_user();
        $current_pack      = $current_user->subscription()->current_pack();
        $payment_enabled   = $form->is_charging_enabled();

        // $price_with_tax = $this->wpuf_prices_include_tax();

        // A charge of 0 is no charge: no "There is a $0.00 charge" notice.
        if (
            0 < (float) apply_filters( 'wpuf_payment_amount', $pay_per_post_cost )
            && (
                ( Subscription::has_user_error( $form_settings ) && ! ( $force_pack && $form->is_enabled_fallback_cost() ) )
                || ( $payment_enabled && $pay_per_post && ! $force_pack )
            )
        ) {
            ?>
            <div class="wpuf-info">
                <?php
                $form              = new Form( $form_id );
                $pay_per_post_cost = (float) $form->get_pay_per_post_cost();

                $pay_per_post_cost = apply_filters( 'wpuf_payment_amount', $pay_per_post_cost );

                /* translators: %s: amount */
                $text = sprintf( __( 'There is a <strong>%s</strong> charge to add a new post.', 'wp-user-frontend' ), wpuf_format_price( $pay_per_post_cost ) );

                echo wp_kses_post( apply_filters( 'wpuf_ppp_notice', $text, $form_id, $form_settings ) );
                ?>
            </div>
            <?php
        } elseif ( $payment_enabled && $force_pack && $form->is_enabled_fallback_cost() && 0 < (float) apply_filters( 'wpuf_payment_amount', (float) $form->get_subs_fallback_cost() ) && ! is_wp_error( $current_pack ) && ! $current_user->subscription()->has_post_count( $form_settings['post_type'] ) ) {
            ?>
            <div class="wpuf-info">
                <?php
                $fallback_cost = (float) $form->get_subs_fallback_cost();

                $fallback_cost = apply_filters( 'wpuf_payment_amount', $fallback_cost );

                /* translators: %s: amount */
                $text = sprintf( __( 'Your Subscription pack is exhausted. There is a <strong>%s</strong> charge to add a new post.', 'wp-user-frontend' ), wpuf_format_price( $fallback_cost ) );

                echo wp_kses_post( apply_filters( 'wpuf_ppp_notice', wp_kses_post( $text ), esc_html( $form_id ), $form_settings ) );
                ?>
            </div>
            <?php
        } elseif ( $payment_enabled && $force_pack && ! is_wp_error( $current_pack ) && ! $current_user->subscription()->has_post_count( $form_settings['post_type'] ) ) {
            // The pack is used up and there is no charge to show (0): say so without a
            // "$0.00 charge" sentence (develop printed one; owner: no $0.00 charge notice).
            ?>
            <div class="wpuf-info">
                <?php
                $text = __( 'Your Subscription pack is exhausted.', 'wp-user-frontend' );

                echo wp_kses_post( apply_filters( 'wpuf_ppp_notice', $text, $form_id, $form_settings ) );
                ?>
            </div>
            <?php
        }
    }

    /**
     * Generate users subscription info with a shortcode
     *
     * @global type $userdata
     */
    public function subscription_info() {
        $react = $this->react_markup( 'plan_info', 'wpuf_sub_info', 'info', true, 'wpuf-plan-info-root' );

        if ( null !== $react ) {
            return $react;
        }

        // _deprecated_function( __FUNCTION__, '2.6.0', 'wpuf_get_user()->subscription()->pack_info( $form_id );' );
        // wpuf_get_user()->subscription()->pack_info( $form_id );
        ob_start();
        $sections = wpuf_get_account_sections();
        do_action( 'wpuf_account_content_subscription', $sections, 'subscription' );

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function get_details_meta_value() {
        $meta['payment_page'] = get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
        $meta['onclick']      = '';
        $meta['symbol']       = wpuf_get_currency( 'symbol' );

        return $meta;
    }

    public function force_pack_notice( $text, $id, $form_settings ) {
        // Admin users don't need subscription notices
        if ( current_user_can( wpuf_admin_role() ) ) {
            return $text;
        }

        $form = new Form( $id );

        $force_pack       = $form->is_enabled_force_pack();
        $fallback_enabled = $form->is_enabled_fallback_cost();

        // When fallback pay-per-post is enabled, don't show "purchase a pack" notice
        if ( $force_pack && $fallback_enabled ) {
            return $text;
        }

        if ( $force_pack && Subscription::has_user_error( $form_settings ) ) {
            $pack_page = get_permalink( wpuf_get_option( 'subscription_page', 'wpuf_payment' ) );
            /* translators: %s: subscription link */
            $text = sprintf( __( 'You must <a href="%s">purchase a pack</a> before posting', 'wp-user-frontend' ), $pack_page );
        }

        return apply_filters( 'wpuf_pack_notice', $text, $id, $form_settings );
    }

    public function force_pack_permission( $perm, $id, $form_settings ) {
        $form              = new Form( $id );
        $force_pack        = $form->is_enabled_force_pack();
        $pay_per_post      = $form->is_enabled_pay_per_post();
        $fallback_enabled  = $form->is_enabled_fallback_cost();
        $fallback_cost     = $form->get_subs_fallback_cost();

        $current_user   = wpuf_get_user();
        $current_pack   = $current_user->subscription()->current_pack();
        $has_post_count = isset( $form_settings['post_type'] ) ? $current_user->subscription()->has_post_count( $form_settings['post_type'] ) : false;

        if ( current_user_can( wpuf_admin_role() ) ) {
            return 'yes';
        }

        // When force_pack + fallback pay-per-post is enabled, skip this early return
        // so the downstream fallback logic can allow the user to post with per-post payment.
        $skip_limit_block = $force_pack && $fallback_enabled;

        if ( $current_user->subscription()->current_pack_id() && ! $has_post_count && ! $skip_limit_block ) {
            return 'no';
        }

        if ( is_user_logged_in() ) {
            if ( wpuf_get_user()->post_locked() ) {
                return 'no';
            } elseif ( ! wpuf_get_user()->post_locked() ) {
                // if post locking not enabled
                if ( ! $form->is_charging_enabled() ) {
                    return 'yes';
                } else {
                    //if charging is enabled
                    if ( $force_pack ) {
                        if ( ! is_wp_error( $current_pack ) ) {
                            // current pack has no error
                            if ( ! $fallback_enabled ) {
                                // fallback cost disabled
                                if ( ! $current_user->subscription()->current_pack_id() ) {
                                    return 'no';
                                } elseif ( $current_user->subscription()->has_post_count( $form_settings['post_type'] ) ) {
                                    return 'yes';
                                }
                            } elseif ( $fallback_enabled ) {
                                // fallback cost enabled
                                if ( ! $current_user->subscription()->current_pack_id() ) {
                                    return 'no';
                                } elseif ( $has_post_count ) {
                                    return 'yes';
                                } elseif ( $current_user->subscription()->current_pack_id() && ! $has_post_count ) {
                                    return 'yes';
                                }
                            }
                        } else {
                            return 'no';
                        }
                    }

                    if ( ! $force_pack && $pay_per_post ) {
                        return 'yes';
                    }
                }
            }
        }

        if ( ! is_user_logged_in() && isset( $form_settings['post_permission'] ) && 'guest_post' === $form_settings['post_permission'] ) {
            if ( $form->is_charging_enabled() ) {
                if ( $force_pack ) {
                    return 'no';
                }

                if ( ! $force_pack && $pay_per_post ) {
                    return 'yes';
                } elseif ( ! $force_pack && ! $pay_per_post ) {
                    return 'no';
                }
            } else {
                return 'yes';
            }
        }

        return $perm;
    }
}
