<?php

$no_form_notice = __( 'No post form assigned yet by the administrator.', 'wp-user-frontend' );
// My Account > "Submission Form"; Frontend Posting > "Default Post Form" when unset.
$selected_form  = absint( wpuf_get_option( 'post_submission_form', 'wpuf_my_account', 0 ) );
$selected_form  = $selected_form ? $selected_form : absint( wpuf_get_option( 'default_post_form', 'wpuf_frontend_posting', 0 ) );

if ( empty( $selected_form ) ) {
    echo wp_kses_post( '<div class="wpuf-info">' . $no_form_notice . '</div>' );

    return;
}

echo do_shortcode( '[wpuf_form id="' . absint( $selected_form ) . '"]' );
