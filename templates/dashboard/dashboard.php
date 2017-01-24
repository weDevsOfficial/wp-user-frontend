<p><?php
    global $current_user;

    printf(
        __( 'Hello %1$s, (not %1$s? <a href="%2$s">Sign out</a>)', 'wpuf' ),
        '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
        esc_url( wp_logout_url( get_permalink() ) )
    );
?></p>

<p><?php
    printf(
        __( 'From your account dashboard you can view your dashboard, manage your <a href="%1$s">posts</a>, <a href="%2$s">subscription</a> and <a href="%3$s">edit your password and profile</a>.', 'wpuf' ),
        esc_url( add_query_arg( array( 'section' => 'posts' ), get_permalink() ) ),
        esc_url( add_query_arg( array( 'section' => 'subscription' ), get_permalink() ) ),
        esc_url( add_query_arg( array( 'section' => 'edit-profile' ), get_permalink() ) )
    );
?></p>
