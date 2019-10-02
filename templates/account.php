<div class="wpuf-dashboard-container">
    <nav class="wpuf-dashboard-navigation">
        <ul>
            <?php
                if ( is_user_logged_in() ) {
                    foreach ( $sections as $section ) {
                        if ( 'subscription' == $section['slug']) {
                            if ( 'off' == wpuf_get_option( 'show_subscriptions', 'wpuf_my_account', 'on' ) || 'on' != wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
                                continue;
                            }
                        }
                        if ( 'billing-address' == $section['slug']) {
                            if ( 'off' == wpuf_get_option( 'show_billing_address', 'wpuf_my_account', 'on' ) || 'on' != wpuf_get_option( 'enable_payment', 'wpuf_payment', 'on' ) ) {
                                continue;
                            }
                        }

                        $default_active_tab = wpuf_get_option( 'account_page_active_tab', 'wpuf_my_account', 'dashboard' );
                        $active_tab         = false;

                        if ( ( isset( $_GET['section'] ) && $_GET['section'] == $section['slug'] ) || ( !isset( $_GET['section'] ) && $default_active_tab ==  $section['slug'] ) ) {
                            $active_tab = true;
                        }

                        echo sprintf(
                            '<li class="wpuf-menu-item %s"><a href="%s">%s</a></li>',
                            $active_tab ? $section['slug'] . ' active' : $section['slug'],
                            add_query_arg( array( 'section' => $section['slug'] ), get_permalink() ),
                            $section['label']
                        );
                    }
                }
            ?>
        </ul>
    </nav>

    <div class="wpuf-dashboard-content <?php echo ( ! empty( $current_section ) ) ? $current_section['slug'] : ''; ?>">
        <?php
            if ( ! empty( $current_section ) && is_user_logged_in() ) {
                do_action( "wpuf_account_content_{$current_section['slug']}", $sections, $current_section );
            }
        ?>
    </div>
</div>
