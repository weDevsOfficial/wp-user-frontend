<div class="wpuf-space-y-6">
    <!-- Welcome Section -->
    <div class="wpuf-bg-gray-50 wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-4">
        <p class="wpuf-text-gray-800 wpuf-mb-0">
            <?php
            global $current_user;

            printf(
                wp_kses_post(
                    // translators: %1$s is displayname and %2$s is permalink
                    __( 'Hello %1$s, (not %1$s? <a href="%2$s" class="wpuf-text-gray-900 wpuf-underline hover:wpuf-font-bold">Sign out</a>)', 'wp-user-frontend' ) ),
                '<strong>' . esc_html( $current_user->display_name ) . '</strong>',
                esc_url( wp_logout_url( get_permalink() ) )
             );
            ?>
        </p>
    </div>

    <!-- Quick Links -->
    <div class="wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-p-6">
        <h3 class="wpuf-text-lg wpuf-font-semibold wpuf-text-gray-900 wpuf-mb-4 wpuf-mt-0">
            <?php esc_html_e( 'Quick Links', 'wp-user-frontend' ); ?>
        </h3>
        <p class="wpuf-text-gray-600 wpuf-leading-relaxed wpuf-mb-0">
            <?php
            $tabs = apply_filters( 'wpuf_my_account_tab_links', wpuf_get_account_sections() );

            if ( 'off' == wpuf_get_option( 'show_subscriptions', 'wpuf_my_account', 'on' ) ) {
                unset( $tabs['subscription'] );
            }

            $links      = '';
            $count      = 1;
            $total_tabs = count( $tabs );

            foreach ( $tabs as $section => $label ) {
                // backward compatibility
                if ( is_array( $label ) ) {
                    $section = $label['slug'];
                    $label   = $label['label'];
                }

                if ( $total_tabs == $count ) {
                    $links .= ' <a href="' . esc_url( add_query_arg( [ 'section' => $section ], get_permalink() ) ) . '" class="wpuf-text-gray-900 wpuf-underline hover:wpuf-font-bold">' . esc_html( $label ) . '</a>';
                    continue;
                }

                $links .= '<a href="' . esc_url( add_query_arg( [ 'section' => $section ], get_permalink() ) ) . '" class="wpuf-text-gray-900 wpuf-underline hover:wpuf-font-bold">' . esc_html( $label ) . '</a>, ';
                $count++;
            }

            printf(
                wp_kses_post(
                    // translators: %s is link
                    __( 'From your account dashboard you can view your dashboard, manage your %s', 'wp-user-frontend' ) ),
                wp_kses( $links, [ 'a' => [ 'href' => [], 'class' => [] ] ] )
             );
            ?>
        </p>
    </div>
</div>
