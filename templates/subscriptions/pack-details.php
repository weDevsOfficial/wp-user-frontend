<?php
/**
 * Subscription single pack details template
 *
 * @version 2.8.8
 *
 * @var WP_Post
 * @var $billing_amount
 * @var $details_meta
 * @var $recurring_des
 * @var $trial_des
 * @var $coupon_status
 * @var $current_pack_id
 * @var $button_name
 */

// Gate before any output
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
if ( 'wpuf_pay' === $action || ! empty( $coupon_status ) ) {
    return;
}

// Get the button color from settings and validate it
$button_color = wpuf_get_option( 'button_color', 'wpuf_subscription_settings', '' );

// Check if custom color is set, otherwise use Tailwind primary class
$use_custom_color = false;
if ( is_string( $button_color ) && ! empty( $button_color ) ) {
    $sanitized_color = sanitize_hex_color( $button_color );
    // If sanitization succeeded, use the custom color
    if ( ! empty( $sanitized_color ) ) {
        $button_color = $sanitized_color;
        $use_custom_color = true;
    }
}
?>
<style>
/* Critical inline styles to prevent FOUC */
.wpuf-pack-<?php echo esc_attr( $pack->ID ); ?> svg {
    width: 1.25rem !important;
    height: 1.5rem !important;
    flex-shrink: 0 !important;
}
.wpuf-pack-<?php echo esc_attr( $pack->ID ); ?> .wpuf-hidden {
    display: none !important;
}
</style>
<div class="wpuf-rounded-xl wpuf-p-6 wpuf-ring-1 wpuf-ring-gray-200 wpuf-bg-white wpuf-shadow-md hover:wpuf-shadow-lg wpuf-transition-all wpuf-duration-300 wpuf-relative wpuf-mt-2">
    <!-- Header Section -->
    <div class="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-gap-x-4">
        <h3 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-900 wpuf-leading-8">
            <?php echo wp_kses_post( $pack->post_title ); ?>
        </h3>
        <?php if ( isset( $pack->featured ) && $pack->featured ) { ?>
            <p class="wpuf-rounded-full wpuf-bg-indigo-600/10 wpuf-px-2.5 wpuf-py-1 wpuf-text-xs wpuf-font-semibold wpuf-text-indigo-600 wpuf-leading-5">
                <?php esc_html_e( 'Most popular', 'wp-user-frontend' ); ?>
            </p>
        <?php } ?>
    </div>
    
    <?php if ( ! empty( $pack->post_content ) ) : ?>
    <div class="wpuf-mt-3 wpuf-text-sm wpuf-leading-5 wpuf-text-gray-600">
        <?php echo wp_kses_post( wpautop( $pack->post_content ) ); ?>
    </div>
    <?php endif; ?>
    
    <!-- Price Section -->
    <div class="wpuf-mt-4">
    <div class="wpuf-flex wpuf-items-baseline wpuf-gap-x-1">
        <?php if ( $billing_amount != '0.00' ) { ?>
            <span class="wpuf-text-3xl wpuf-font-bold wpuf-tracking-tight wpuf-text-gray-900">
                <?php echo esc_html( wpuf_format_price( $billing_amount ) ); ?>
            </span>
            <span class="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-600 wpuf-leading-6">
                <?php echo wp_kses_post( $recurring_des ); ?>
            </span>
        <?php } else { ?>
            <span class="wpuf-text-3xl wpuf-font-bold wpuf-tracking-tight wpuf-text-gray-900">
                <?php esc_html_e( 'Free', 'wp-user-frontend' ); ?>
            </span>
        <?php } ?>
    </div>

    <?php if ( wpuf_is_checkbox_or_toggle_on( $pack->meta_value['recurring_pay'] ) && !empty( $trial_des ) ) { ?>
        <div class="wpuf-mt-3">
            <div class="wpuf-text-sm wpuf-text-gray-600">
                <?php echo esc_html( $trial_des ); ?>
            </div>
        </div>
    <?php } ?>
    </div>
    
    <!-- Button Section -->
    <div class="wpuf-mt-6">
        <?php if ( 'completed' === $current_pack_status ) : ?>
            <a class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 wpuf-cursor-not-allowed wpuf-bg-gray-100 wpuf-leading-6"
               href="javascript:void(0)">
                <?php echo esc_html( is_string( $button_name ) ? $button_name : strval( $button_name ) ); ?>
            </a>
        <?php else : ?>
            <style>
                .wpuf-pack-<?php echo esc_attr( $pack->ID ); ?> .wpuf-subscription-buy-btn:hover {
                    filter: brightness(0.9);
                }
            </style>
            <a class="wpuf-subscription-buy-btn wpuf-block wpuf-w-full wpuf-rounded-md <?php echo $use_custom_color ? '' : 'wpuf-bg-primary hover:wpuf-bg-primaryHover'; ?> wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm wpuf-ring-0 wpuf-transition-all wpuf-duration-200 wpuf-leading-6"
               <?php if ( $use_custom_color ) : ?>
               style="background-color: <?php echo esc_attr( $button_color ); ?>;"
               onmouseover="this.style.filter='brightness(0.9)'"
               onmouseout="this.style.filter='brightness(1)'"
               <?php endif; ?>
               href="<?php echo esc_attr( add_query_arg( $query_args, esc_url( $query_url ) ) ); ?>"
               onclick="<?php echo esc_attr( $details_meta['onclick'] ); ?>">
                <?php echo esc_html( $button_name ); ?>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Features List -->
    <?php 
    // Start collecting all features to be shown
    $features_list = [];
    
    // Check if custom features are set, if so show them
    if ( isset( $pack->meta_value['features'] ) && is_array( $pack->meta_value['features'] ) && !empty( $pack->meta_value['features'] ) ) {
        foreach ( $pack->meta_value['features'] as $feature ) {
            $features_list[] = esc_html( $feature );
        }
    } else {
        // Show default features from advanced configuration
        $post_type_limits = isset( $pack->meta_value['_post_type_name'] ) ? maybe_unserialize( $pack->meta_value['_post_type_name'] ) : [];
        $additional_cpt = isset( $pack->meta_value['additional_cpt_options'] ) ? maybe_unserialize( $pack->meta_value['additional_cpt_options'] ) : [];
        $all_limits = array_merge( (array) $post_type_limits, (array) $additional_cpt );
        
        // Collect all features into array
        // Show Posts limit
        if ( isset( $all_limits['post'] ) && '0' !== $all_limits['post'] ) {
            if ( '-1' === $all_limits['post'] ) {
                $features_list[] = __( 'Unlimited posts', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d posts allowed', 'wp-user-frontend' ), intval( $all_limits['post'] ) );
            }
        }
        
        // Show Pages limit
        if ( isset( $all_limits['page'] ) && '0' !== $all_limits['page'] ) {
            if ( '-1' === $all_limits['page'] ) {
                $features_list[] = __( 'Unlimited pages', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d pages allowed', 'wp-user-frontend' ), intval( $all_limits['page'] ) );
            }
        }
        
        // Show User Requests limit
        if ( isset( $all_limits['user_request'] ) && '0' !== $all_limits['user_request'] ) {
            if ( '-1' === $all_limits['user_request'] ) {
                $features_list[] = __( 'Unlimited user requests', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d user requests allowed', 'wp-user-frontend' ), intval( $all_limits['user_request'] ) );
            }
        }
        
        // Show WooCommerce Products limit if exists
        if ( isset( $all_limits['product'] ) && '0' !== $all_limits['product'] ) {
            if ( '-1' === $all_limits['product'] ) {
                $features_list[] = __( 'Unlimited products', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d products allowed', 'wp-user-frontend' ), intval( $all_limits['product'] ) );
            }
        }
        
        // Show Reusable Blocks limit (Design Elements)
        if ( isset( $all_limits['wp_block'] ) && '0' !== $all_limits['wp_block'] ) {
            if ( '-1' === $all_limits['wp_block'] ) {
                $features_list[] = __( 'Unlimited reusable blocks', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d reusable blocks allowed', 'wp-user-frontend' ), intval( $all_limits['wp_block'] ) );
            }
        }
        
        // Show Templates limit (Design Elements)
        if ( isset( $all_limits['wp_template'] ) && '0' !== $all_limits['wp_template'] ) {
            if ( '-1' === $all_limits['wp_template'] ) {
                $features_list[] = __( 'Unlimited templates', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d templates allowed', 'wp-user-frontend' ), intval( $all_limits['wp_template'] ) );
            }
        }
        
        // Show Template Parts limit (Design Elements)
        if ( isset( $all_limits['wp_template_part'] ) && '0' !== $all_limits['wp_template_part'] ) {
            if ( '-1' === $all_limits['wp_template_part'] ) {
                $features_list[] = __( 'Unlimited template parts', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d template parts allowed', 'wp-user-frontend' ), intval( $all_limits['wp_template_part'] ) );
            }
        }
        
        // Show Navigation Menus limit (Design Elements)
        if ( isset( $all_limits['wp_navigation'] ) && '0' !== $all_limits['wp_navigation'] ) {
            if ( '-1' === $all_limits['wp_navigation'] ) {
                $features_list[] = __( 'Unlimited navigation menus', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d navigation menus allowed', 'wp-user-frontend' ), intval( $all_limits['wp_navigation'] ) );
            }
        }
        
        // Show Global Styles limit (Additional Options)
        if ( isset( $all_limits['wp_global_styles'] ) && '0' !== $all_limits['wp_global_styles'] ) {
            if ( '-1' === $all_limits['wp_global_styles'] ) {
                $features_list[] = __( 'Unlimited global styles', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d global styles allowed', 'wp-user-frontend' ), intval( $all_limits['wp_global_styles'] ) );
            }
        }
        
        // Show Font Families limit (Additional Options)
        if ( isset( $all_limits['wp_font_family'] ) && '0' !== $all_limits['wp_font_family'] ) {
            if ( '-1' === $all_limits['wp_font_family'] ) {
                $features_list[] = __( 'Unlimited font families', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d font families allowed', 'wp-user-frontend' ), intval( $all_limits['wp_font_family'] ) );
            }
        }
        
        // Show Font Faces limit (Additional Options)
        if ( isset( $all_limits['wp_font_face'] ) && '0' !== $all_limits['wp_font_face'] ) {
            if ( '-1' === $all_limits['wp_font_face'] ) {
                $features_list[] = __( 'Unlimited font faces', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d font faces allowed', 'wp-user-frontend' ), intval( $all_limits['wp_font_face'] ) );
            }
        }
        
        // Show Product Variations limit (WooCommerce)
        if ( isset( $all_limits['product_variation'] ) && '0' !== $all_limits['product_variation'] ) {
            if ( '-1' === $all_limits['product_variation'] ) {
                $features_list[] = __( 'Unlimited product variations', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d product variations allowed', 'wp-user-frontend' ), intval( $all_limits['product_variation'] ) );
            }
        }
        
        // Show Shop Orders limit (WooCommerce)
        if ( isset( $all_limits['shop_order'] ) && '0' !== $all_limits['shop_order'] ) {
            if ( '-1' === $all_limits['shop_order'] ) {
                $features_list[] = __( 'Unlimited shop orders', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d shop orders allowed', 'wp-user-frontend' ), intval( $all_limits['shop_order'] ) );
            }
        }
        
        // Show Shop Refunds limit (WooCommerce)
        if ( isset( $all_limits['shop_order_refund'] ) && '0' !== $all_limits['shop_order_refund'] ) {
            if ( '-1' === $all_limits['shop_order_refund'] ) {
                $features_list[] = __( 'Unlimited shop refunds', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d shop refunds allowed', 'wp-user-frontend' ), intval( $all_limits['shop_order_refund'] ) );
            }
        }
        
        // Show Shop Coupons limit (WooCommerce)
        if ( isset( $all_limits['shop_coupon'] ) && '0' !== $all_limits['shop_coupon'] ) {
            if ( '-1' === $all_limits['shop_coupon'] ) {
                $features_list[] = __( 'Unlimited shop coupons', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d shop coupons allowed', 'wp-user-frontend' ), intval( $all_limits['shop_coupon'] ) );
            }
        }
        
        // Show Featured items limit
        if ( isset( $pack->meta_value['_total_feature_item'] ) && '0' !== $pack->meta_value['_total_feature_item'] ) {
            if ( '-1' === $pack->meta_value['_total_feature_item'] ) {
                $features_list[] = __( 'Unlimited featured items', 'wp-user-frontend' );
            } else {
                $features_list[] = sprintf( __( '%d featured items', 'wp-user-frontend' ), intval( $pack->meta_value['_total_feature_item'] ) );
            }
        }
        
        // Show Post expiration
        if ( isset( $pack->meta_value['_enable_post_expiration'] ) && 'yes' === $pack->meta_value['_enable_post_expiration'] ) {
            $expiry_period = isset( $pack->meta_value['_post_expiration_number'] ) ? intval( $pack->meta_value['_post_expiration_number'] ) : 0;
            $expiry_type = isset( $pack->meta_value['_post_expiration_period'] ) ? $pack->meta_value['_post_expiration_period'] : 'day';
            
            if ( $expiry_period > 0 ) {
                $features_list[] = sprintf( __( 'Posts expire after %d %s', 'wp-user-frontend' ), $expiry_period, $expiry_type . ($expiry_period > 1 ? 's' : '') );
            } else {
                $features_list[] = __( 'Post expiration enabled', 'wp-user-frontend' );
            }
        }
        
        // Show Recurring payment feature
        if ( isset( $pack->meta_value['_recurring_pay'] ) && 'yes' === $pack->meta_value['_recurring_pay'] ) {
            $features_list[] = __( 'Recurring subscription', 'wp-user-frontend' );
        }
        
        // Show Trial period if available
        if ( isset( $pack->meta_value['_trial_status'] ) && 'yes' === $pack->meta_value['_trial_status'] ) {
            $trial_duration = isset( $pack->meta_value['_trial_duration'] ) ? intval( $pack->meta_value['_trial_duration'] ) : 0;
            $trial_type = isset( $pack->meta_value['_trial_duration_type'] ) ? $pack->meta_value['_trial_duration_type'] : 'day';
            
            if ( $trial_duration > 0 ) {
                $features_list[] = sprintf( __( '%d %s free trial', 'wp-user-frontend' ), $trial_duration, $trial_type . ($trial_duration > 1 ? 's' : '') );
            } else {
                $features_list[] = __( 'Free trial available', 'wp-user-frontend' );
            }
        }
        
        // Show Mail notification on expiry
        if ( isset( $pack->meta_value['_enable_mail_after_expired'] ) && 'yes' === $pack->meta_value['_enable_mail_after_expired'] ) {
            $features_list[] = __( 'Email notifications on post expiry', 'wp-user-frontend' );
        }
        
        // If no features found, show a basic feature
        if ( empty( $features_list ) ) {
            $features_list[] = __( 'Full website access', 'wp-user-frontend' );
        }
    }
    
    // Show the features list with expandable functionality
    if ( ! empty( $features_list ) ) :
        $features_count = count( $features_list );
        $initial_display_count = 5; // Show first 5 features initially
        ?>
        <div class="wpuf-mt-6">
            <ul class="wpuf-text-sm wpuf-leading-6 wpuf-text-gray-600 wpuf-space-y-3" id="wpuf-features-list-<?php echo esc_attr( $pack->ID ); ?>">
                <?php foreach ( $features_list as $index => $feature ) : ?>
                    <li class="wpuf-flex wpuf-gap-x-3 <?php echo $index >= $initial_display_count ? 'wpuf-hidden wpuf-expandable-feature' : ''; ?>">
                        <svg viewBox="0 0 20 20" fill="<?php echo esc_attr( $button_color ); ?>" class="wpuf-h-6 wpuf-w-5 wpuf-flex-none" style="width: 1.25rem; height: 1.5rem; flex-shrink: 0;" width="20" height="24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                        <?php echo esc_html( $feature ); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if ( $features_count > $initial_display_count ) : ?>
                <div class="wpuf-mt-3">
                    <button 
                        type="button"
                        class="<?php echo $use_custom_color ? '' : 'wpuf-text-primary hover:wpuf-text-primaryHover'; ?> wpuf-text-sm wpuf-font-medium wpuf-transition-colors wpuf-duration-200"
                        <?php if ( $use_custom_color ) : ?>
                        style="color: <?php echo esc_attr( $button_color ); ?>;"
                        onmouseover="this.style.opacity='0.8'"
                        onmouseout="this.style.opacity='1'"
                        <?php endif; ?>
                        data-wpuf-toggle-features="true"
                        data-wpuf-pack-id="<?php echo esc_attr( $pack->ID ); ?>"
                        data-expanded="false"
                        data-hidden-count="<?php echo esc_attr( $features_count - $initial_display_count ); ?>"
                        id="wpuf-see-more-btn-<?php echo esc_attr( $pack->ID ); ?>"
                    >
                        <?php printf( esc_html__( 'See %d more features', 'wp-user-frontend' ), $features_count - $initial_display_count ); ?>
                    </button>
                    <button 
                        type="button"
                        class="<?php echo $use_custom_color ? '' : 'wpuf-text-primary hover:wpuf-text-primaryHover'; ?> wpuf-text-sm wpuf-font-medium wpuf-transition-colors wpuf-duration-200 wpuf-hidden"
                        <?php if ( $use_custom_color ) : ?>
                        style="color: <?php echo esc_attr( $button_color ); ?>;"
                        onmouseover="this.style.opacity='0.8'"
                        onmouseout="this.style.opacity='1'"
                        <?php endif; ?>
                        data-wpuf-toggle-features="true"
                        data-wpuf-pack-id="<?php echo esc_attr( $pack->ID ); ?>"
                        data-expanded="true"
                        data-hidden-count="<?php echo esc_attr( $features_count - $initial_display_count ); ?>"
                        id="wpuf-see-less-btn-<?php echo esc_attr( $pack->ID ); ?>"
                    >
                        <?php esc_html_e( 'See less', 'wp-user-frontend' ); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
</div>

<?php
?>
