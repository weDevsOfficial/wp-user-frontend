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

// Pass template data to the enqueued scripts (register filter before action is fired)
add_filter( 'wpuf_ai_form_builder_localize_data', function( $data ) use ( $stage, $description, $prompt, $form_id, $form_title ) {
    return array_merge( $data, [
        'stage'        => $stage,
        'description'  => $description,
        'prompt'       => $prompt,
        'formId'       => $form_id,
        'formTitle'    => $form_title,
        'confettiUrl'  => WPUF_ASSET_URI . '/images/confetti_transparent.gif',
    ]);
});

// Trigger action to allow other scripts to be enqueued and localized
do_action( 'wpuf_load_ai_form_builder_page' );
?>

<!-- Vue.js AI Form Builder Component Mount Point -->
<div id="wpuf-ai-form-builder" class="wpuf-h-100vh wpuf-ml-[-20px] wpuf-py-0 wpuf-px-[20px]" style="background-color: #F5F5F5;">
    <noscript>
        <strong>
            <?php esc_html_e( "We're sorry but this page doesn't work properly without JavaScript. Please enable it to continue.", 'wp-user-frontend' ); ?>
        </strong>
    </noscript>
    <h2><?php esc_html_e( 'Loading', 'wp-user-frontend' ); ?>...</h2>
</div>