<?php

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
