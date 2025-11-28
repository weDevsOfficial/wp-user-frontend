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
        <p class="!wpuf-text-base !wpuf-text-gray-500 !wpuf-mb-4 !wpuf-text-center !wpuf-max-w-md"><?php esc_html_e( 'Display custom profile fields, social links, and more information about users.', 'wp-user-frontend' ); ?></p>
        <span class="!wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-1 !wpuf-rounded-full !wpuf-text-sm !wpuf-font-medium !wpuf-bg-emerald-600 !wpuf-text-white">
            <svg class="!wpuf-w-4 !wpuf-h-4 !wpuf-mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
            </svg>
            <?php esc_html_e( 'Pro Only', 'wp-user-frontend' ); ?>
        </span>
        <a href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=freeplugin&utm_medium=userdirectory&utm_campaign=about_tab" target="_blank" class="!wpuf-mt-4 !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-white !wpuf-bg-emerald-600 !wpuf-rounded-md hover:!wpuf-bg-emerald-700 !wpuf-no-underline !wpuf-transition-colors">
            <?php esc_html_e( 'Upgrade to Pro', 'wp-user-frontend' ); ?>
            <svg class="!wpuf-w-4 !wpuf-h-4 !wpuf-ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
    </div>
</div>

<?php
do_action( 'wpuf_user_profile_after_content' );
?>
