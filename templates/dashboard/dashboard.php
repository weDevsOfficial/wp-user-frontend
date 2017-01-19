<p><?php
    global $current_user;

    printf(
        __( 'Hello %1$s (not %1$s? <a href="%2$s">Sign out</a>)', 'wpuf' ),
        '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
        esc_url( wp_logout_url( get_permalink() ) )
    );
?></p>

<?php
    echo get_avatar( $current_user->user_email, $size = '64', $default = '<path_to_url>' ) . '<br />';
?>

<ul class="wpuf-form">
    <li>
        <div class="wpuf-label">
            <label for="name"><strong><?php _e( 'Name: ', 'wpuf' ); ?></strong></label>
        </div>
        <div class="wpuf-fields">
            <?php echo $current_user->display_name; ?>
        </div>
    </li>
    <li>
        <div class="wpuf-label">
            <label for="nickname"><strong><?php _e( 'Nickname: ', 'wpuf' ); ?></strong></label>
        </div>
        <div class="wpuf-fields">
            <?php echo $current_user->nickname; ?>
        </div>
    </li>
    <li>
        <div class="wpuf-label">
            <label for="email"><strong><?php _e( 'Email: ', 'wpuf' ); ?></strong></label>
        </div>
        <div class="wpuf-fields">
            <?php echo $current_user->user_email; ?>
        </div>
    </li>
    <li>
        <div class="wpuf-label">
            <label for="website"><strong><?php _e( 'Website: ', 'wpuf' ); ?></strong></label>
        </div>
        <div class="wpuf-fields">
            <?php echo $current_user->user_url; ?>
        </div>
    </li>
</ul>