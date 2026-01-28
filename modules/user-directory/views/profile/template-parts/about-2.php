<?php
/**
 * About Tab Template 2 - Free Version (Pro Only Feature)
 *
 * @since 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'wpuf_user_profile_before_content' );
?>

<div class="wpuf-about-section !wpuf-mb-8">
    <div class="!wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-py-20 !wpuf-bg-gray-50 !wpuf-rounded-xl">
        <div class="!wpuf-mb-4">
            <svg class="!wpuf-w-24 !wpuf-h-24 !wpuf-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <p class="!wpuf-text-xl !wpuf-font-medium !wpuf-text-gray-900 !wpuf-mb-2"><?php esc_html_e( 'About Tab', 'wp-user-frontend' ); ?></p>
        <p class="!wpuf-text-base !wpuf-text-gray-500 !wpuf-text-center !wpuf-max-w-md"><?php esc_html_e( 'Display custom profile fields, social links, and more information about users.', 'wp-user-frontend' ); ?></p>
    </div>
</div>

<?php
do_action( 'wpuf_user_profile_after_content' );
?>
