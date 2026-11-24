<?php

/**
 * Filters the heading shown above the account page Submit Post form. Return an empty string to hide it.
 *
 * @since WPUF_SINCE
 *
 * @param string $heading The "Submission Menu Label" setting, "Submit Post" by default.
 */
$submit_post_heading = apply_filters(
    'wpuf_account_submit_post_heading',
    wpuf_get_option( 'post_submission_label', 'wpuf_my_account', __( 'Submit Post', 'wp-user-frontend' ) )
);

if ( '' !== (string) $submit_post_heading ) {
    ?>
    <div class="wpuf-bg-transparent wpuf-rounded-t-lg wpuf-mb-3">
        <h2 class="wpuf-text-gray-700 wpuf-font-bold wpuf-text-[32px] wpuf-leading-[48px] wpuf-tracking-[0.13px] wpuf-m-0">
            <?php echo esc_html( $submit_post_heading ); ?>
        </h2>
    </div>
    <?php
}

$no_form_notice = __( 'No post form assigned yet by the administrator.', 'wp-user-frontend' );
$selected_form  = absint( wpuf_get_option( 'post_submission_form', 'wpuf_my_account' ) );
$selected_post  = $selected_form ? get_post( $selected_form ) : null;

// Use the My Account "Submission Form"; fall back to the Default Post Form when it is unset or not a published post form.
if ( ! $selected_post || 'wpuf_forms' !== $selected_post->post_type || 'publish' !== $selected_post->post_status ) {
    $selected_form = absint( wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting' ) );
}

if ( empty( $selected_form ) ) {
    echo wp_kses_post( '<div class="wpuf-info">' . $no_form_notice . '</div>' );

    return;
}

echo do_shortcode( '[wpuf_form id="' . $selected_form . '"]' );
