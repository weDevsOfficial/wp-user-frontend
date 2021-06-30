<p><?php
    global $current_user;

    printf(
        wp_kses_post( __( 'Hello %1$s, (not %1$s? <a href="%2$s">Sign out</a>)', 'wp-user-frontend' ) ),
        '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
        esc_url( wp_logout_url( get_permalink() ) )
     );
?></p>

<p><?php

    $tabs = apply_filters( 'wpuf_my_account_tab_links', [
            'posts'  => [
                'label' => __( 'Posts', 'wp-user-frontend' ),
                'url'   => esc_url( add_query_arg( [ 'section' => 'posts' ], get_permalink() ) ),
            ],
            'subscription'  => [
                'label' => __( 'Subscription', 'wp-user-frontend' ),
                'url'   => esc_url( add_query_arg( [ 'section' => 'subscription' ], get_permalink() ) ),
            ],
            'edit-profile'  => [
                'label' => __( 'edit your password and profile', 'wp-user-frontend' ),
                'url'   => esc_url( add_query_arg( [ 'section' => 'edit-profile' ], get_permalink() ) ),
            ],
        ]
     );

    if ( 'off' == wpuf_get_option( 'show_subscriptions', 'wpuf_my_account', 'on' ) ) {
        unset( $tabs['subscription'] );
    }

    $links      = '';
    $count      = 1;
    $total_tabs = count( $tabs );

    foreach ( $tabs as $key => $tab ) {
        if ( $total_tabs == $count ) {
            $links .= ' <a href="' . $tab['url'] . '">' . $tab['label'] . '</a>';
            continue;
        }

        $links .= '<a href="' . $tab['url'] . '">' . $tab['label'] . '</a>, ';
        $count++;
    }

    printf(
        wp_kses_post( __( 'From your account dashboard you can view your dashboard, manage your %s', 'wp-user-frontend' ) ),
        wp_kses( $links, [ 'a' => [ 'href' => [] ] ] )
     );
?></p>
