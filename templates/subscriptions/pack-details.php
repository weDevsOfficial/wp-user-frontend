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
?>
<div class="wpuf-rounded-xl wpuf-p-6 wpuf-ring-1 wpuf-ring-gray-200 wpuf-bg-white wpuf-shadow-md hover:wpuf-shadow-lg wpuf-transition-all wpuf-duration-300 wpuf-relative wpuf-h-full wpuf-flex wpuf-flex-col wpuf-mt-2">
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
    <div class="wpuf-mt-3 wpuf-text-sm wpuf-leading-5 wpuf-text-gray-600 wpuf-flex-grow">
        <?php echo wp_kses_post( wpautop( $pack->post_content ) ); ?>
    </div>
    <?php endif; ?>
    
    <!-- Price Section -->
    <div class="wpuf-mt-auto wpuf-pt-4">
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
        <a <?php echo ( esc_attr( $current_pack_status ) == 'completed' ) ? 
            'class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 wpuf-cursor-not-allowed wpuf-bg-gray-100 wpuf-leading-6"' : 
            'class="wpuf-block wpuf-w-full wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-center wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-bg-indigo-600 hover:wpuf-bg-indigo-500 wpuf-shadow-sm wpuf-ring-0 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2 focus-visible:wpuf-outline-indigo-600 wpuf-transition-colors wpuf-duration-200 wpuf-leading-6"'; ?> 
           href="<?php echo ( esc_attr( $current_pack_status ) == 'completed' ) ? 'javascript:void(0)' : esc_attr( add_query_arg( $query_args, esc_url( $query_url ) ) ); ?>" 
           onclick="<?php echo esc_attr( $details_meta['onclick'] ); ?>">
            <?php echo esc_html( $button_name ); ?>
        </a>
    </div>
    
    <?php if ( isset( $pack->meta_value['features'] ) && is_array( $pack->meta_value['features'] ) ) { ?>
        <ul role="list" class="wpuf-mt-8 wpuf-space-y-3 wpuf-text-sm wpuf-text-gray-600 xl:wpuf-mt-10 wpuf-leading-6">
            <?php foreach ( $pack->meta_value['features'] as $feature ) { ?>
                <li class="wpuf-flex wpuf-gap-x-3">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="wpuf-h-6 wpuf-w-5 wpuf-flex-none wpuf-text-indigo-600" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    <?php echo esc_html( $feature ); ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
    
    <?php if ( !isset( $pack->meta_value['features'] ) ) { ?>
        <ul role="list" class="wpuf-mt-8 wpuf-space-y-3 wpuf-text-sm wpuf-text-gray-600 xl:wpuf-mt-10 wpuf-leading-6">
            <?php if ( isset( $pack->meta_value['post_count'] ) && $pack->meta_value['post_count'] > 0 ) { ?>
                <li class="wpuf-flex wpuf-gap-x-3">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="wpuf-h-6 wpuf-w-5 wpuf-flex-none wpuf-text-indigo-600" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    <?php printf( esc_html__( '%d posts allowed', 'wp-user-frontend' ), intval( $pack->meta_value['post_count'] ) ); ?>
                </li>
            <?php } ?>
            <?php if ( isset( $pack->meta_value['post_expiration_settings']['enabled'] ) && $pack->meta_value['post_expiration_settings']['enabled'] == 'on' ) { ?>
                <li class="wpuf-flex wpuf-gap-x-3">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="wpuf-h-6 wpuf-w-5 wpuf-flex-none wpuf-text-indigo-600" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    <?php esc_html_e( 'Post expiration enabled', 'wp-user-frontend' ); ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</div>
<?php
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

if ( $action == 'wpuf_pay' || $coupon_status ) {
    return;
}
?>
