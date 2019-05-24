<?php

    $no_form_notice = __('No post form assigned yet by the administrator.', 'wp-user-frontend' );
    $selected_form = wpuf_get_option( 'post_submission_form', 'wpuf_my_account' );

    if ( empty( $selected_form ) ) {
        echo '<div class="wpuf-info">' . $no_form_notice . '</div>';
        return;
    }

    echo do_shortcode('[wpuf_form id="'.$selected_form.'"]');

?>