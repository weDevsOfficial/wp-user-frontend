<?php
/**
 * AI Form Builder Template - Single mount point for all stages
 * 
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Determine which stage based on the action
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$stage = 'input'; // default

// Map actions to stages
if ( $action === 'wpuf_ai_form_generating' ) {
    $stage = 'generating';
} elseif ( $action === 'wpuf_ai_form_success' ) {
    $stage = 'success';
} else {
    // For post_form_template or default
    $stage = 'input';
}

// Get variables from URL
$description = isset( $_GET['description'] ) && ! empty( $_GET['description'] ) ? sanitize_text_field( wp_unslash( $_GET['description'] ) ) : '';
$prompt = isset( $_GET['prompt'] ) && ! empty( $_GET['prompt'] ) ? sanitize_text_field( wp_unslash( $_GET['prompt'] ) ) : '';
$form_id = isset( $_GET['form_id'] ) && ! empty( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : '';
$form_title = isset( $_GET['form_title'] ) && ! empty( $_GET['form_title'] ) ? sanitize_text_field( wp_unslash( $_GET['form_title'] ) ) : '';

// Note: Scripts are already enqueued in the handler, so we don't need to enqueue them here

// Localize script data for Vue component
wp_localize_script(
    'wpuf-ai-form-builder', 'wpufAIFormBuilder',
    [
        'version'      => WPUF_VERSION,
        'assetUrl'     => WPUF_ASSET_URI,
        'siteUrl'      => site_url(),
        'nonce'        => wp_create_nonce( 'wp_rest' ),
        'rest_url'     => esc_url_raw( rest_url() ),
        'stage'        => $stage,
        'description'  => $description,
        'prompt'       => $prompt,
        'formId'       => $form_id,
        'formTitle'    => $form_title,
        'confettiUrl'  => WPUF_ASSET_URI . '/images/confetti_transparent.gif',
    ]
);

// Trigger action to allow other scripts to be enqueued
do_action( 'wpuf_load_ai_form_builder_page' );
?>

<!-- Vue.js AI Form Builder Component Mount Point -->
<div id="wpuf-ai-form-builder" class="wpuf-h-100vh wpuf-bg-white wpuf-ml-[-20px] wpuf-py-0 wpuf-px-[20px]">
    <noscript>
        <strong>
            <?php esc_html_e( "We're sorry but this page doesn't work properly without JavaScript. Please enable it to continue.", 'wp-user-frontend' ); ?>
        </strong>
    </noscript>
    <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
</div>