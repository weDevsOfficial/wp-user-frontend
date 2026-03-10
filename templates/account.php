<div class="wpuf-account-container">
    <!-- Left Sidebar -->
    <aside class="wpuf-account-sidebar">
        <?php
        global $current_user;
        ?>

        <!-- Profile Section -->
        <div class="wpuf-profile-section">
            <?php
            $avatar_size = 96;
            $avatar_data = wpuf_get_user_avatar_data( $current_user, $avatar_size );
            ?>
            <div class="wpuf-profile-avatar">
                <div class="wpuf-avatar-wrapper" style="width: <?php echo esc_attr( $avatar_size ); ?>px; height: <?php echo esc_attr( $avatar_size ); ?>px;">
                    <?php if ( $avatar_data['url'] ) : ?>
                        <!-- Show avatar image (custom photo or Gravatar) -->
                        <img src="<?php echo esc_url( $avatar_data['url'] ); ?>"
                             alt="<?php echo esc_attr( $current_user->display_name ); ?>"
                             class="wpuf-rounded-full wpuf-object-cover"
                             width="<?php echo esc_attr( $avatar_size ); ?>"
                             height="<?php echo esc_attr( $avatar_size ); ?>"
                             style="width: <?php echo esc_attr( $avatar_size ); ?>px; height: <?php echo esc_attr( $avatar_size ); ?>px; object-fit: cover;" />
                    <?php else : ?>
                        <!-- Show initials (no image available) -->
                        <div class="wpuf-avatar-initials wpuf-rounded-full wpuf-bg-gray-400 wpuf-text-white wpuf-flex wpuf-items-center wpuf-justify-center wpuf-font-semibold"
                             style="width: <?php echo esc_attr( $avatar_size ); ?>px; height: <?php echo esc_attr( $avatar_size ); ?>px; font-size: <?php echo esc_attr( $avatar_data['font_size'] ); ?>px;">
                            <?php echo esc_html( $avatar_data['initials'] ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <h3 class="wpuf-profile-name"><?php echo esc_html( $current_user->display_name ); ?></h3>
            <p class="wpuf-profile-role">
                <?php
                $user_roles = $current_user->roles;
                echo ! empty( $user_roles ) ? esc_html( ucfirst( $user_roles[0] ) ) : '';
                ?>
            </p>
            <a href="<?php echo esc_url( add_query_arg( [ 'section' => 'edit-profile' ], get_permalink() ) ); ?>" class="wpuf-edit-profile-btn">
                <?php esc_html_e( 'Edit Profile', 'wp-user-frontend' ); ?>
                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.75 2.75H2.75C1.64543 2.75 0.75 3.64543 0.75 4.75V15.75C0.75 16.8546 1.64543 17.75 2.75 17.75H13.75C14.8546 17.75 15.75 16.8546 15.75 15.75V10.75M14.3358 1.33579C15.1168 0.554738 16.3832 0.554738 17.1642 1.33579C17.9453 2.11683 17.9453 3.38316 17.1642 4.16421L8.57842 12.75H5.75L5.75 9.92157L14.3358 1.33579Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="wpuf-account-nav">
            <ul class="wpuf-space-y-1">
                <?php
                    if ( is_user_logged_in() ) {
                        $section_param = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : null;

                        foreach ( $sections as $section => $label ) {
                            // backward compatibility
                            if ( is_array( $label ) ) {
                                $section = $label['slug'];
                                $label   = $label['label'];
                            }

                            if ( 'subscription' == $section ) {
                                if ( 'off' == wpuf_get_option( 'show_subscriptions', 'wpuf_my_account', 'on' ) || 'on' != wpuf_get_option( 'enable_payment',
                                                                                                                                           'wpuf_payment', 'on' ) ) {
                                    continue;
                                }
                            }

                            if ( 'billing-address' == $section ) {
                                if ( 'off' == wpuf_get_option( 'show_billing_address', 'wpuf_my_account', 'on' ) || 'on' != wpuf_get_option( 'enable_payment',
                                                                                                                                             'wpuf_payment', 'on' ) ) {
                                    continue;
                                }
                            }

                            $default_active_tab = wpuf_get_option( 'account_page_active_tab', 'wpuf_my_account', 'dashboard' );
                            $active_tab         = false;

                            if ( ( null !== $section_param && $section_param === $section ) || ( null === $section_param && $default_active_tab === $section ) ) {
                                $active_tab = true;
                            }

                            $active_class = $active_tab ? 'wpuf-account-nav-item active' : 'wpuf-account-nav-item';

                            // Get icon for each section
                            $icon = '';
                            switch ( $section ) {
                                case 'dashboard':
                                   $icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 10L3 8M3 8L10 1L17 8M3 8V18C3 18.5523 3.44772 19 4 19H7M17 8L19 10M17 8V18C17 18.5523 16.5523 19 16 19H13M7 19C7.55228 19 8 18.5523 8 18V14C8 13.4477 8.44772 13 9 13H11C11.5523 13 12 13.4477 12 14V18C12 18.5523 12.4477 19 13 19M7 19H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            ';
                                    break;
                                case 'subscription':
                                    $icon ='<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12.0322 7.49153C12.3036 7.80439 12.7773 7.83794 13.0902 7.56648C13.403 7.29501 13.4366 6.82132 13.1651 6.50847L12.0322 7.49153ZM7.96784 12.5085C7.69638 12.1956 7.22269 12.1621 6.90983 12.4335C6.59698 12.705 6.56342 13.1787 6.83489 13.4915L7.96784 12.5085ZM10.75 5C10.75 4.58579 10.4142 4.25 10 4.25C9.58579 4.25 9.25 4.58579 9.25 5H10.75ZM9.25 15C9.24999 15.4142 9.58577 15.75 9.99998 15.75C10.4142 15.75 10.75 15.4142 10.75 15L9.25 15ZM19 10H18.25C18.25 14.5563 14.5563 18.25 10 18.25V19V19.75C15.3848 19.75 19.75 15.3848 19.75 10H19ZM10 19V18.25C5.44365 18.25 1.75 14.5563 1.75 10H1H0.25C0.25 15.3848 4.61522 19.75 10 19.75V19ZM1 10H1.75C1.75 5.44365 5.44365 1.75 10 1.75V1V0.25C4.61522 0.25 0.25 4.61522 0.25 10H1ZM10 1V1.75C14.5563 1.75 18.25 5.44365 18.25 10H19H19.75C19.75 4.61522 15.3848 0.25 10 0.25V1ZM10 10V9.25C9.29899 9.25 8.69827 9.05922 8.2947 8.79018C7.8859 8.51764 7.75 8.2235 7.75 8H7H6.25C6.25 8.88107 6.78567 9.58693 7.46265 10.0383C8.14488 10.4931 9.04416 10.75 10 10.75V10ZM7 8H7.75C7.75 7.7765 7.8859 7.48236 8.2947 7.20982C8.69827 6.94078 9.29899 6.75 10 6.75V6V5.25C9.04416 5.25 8.14488 5.50693 7.46265 5.96175C6.78567 6.41307 6.25 7.11893 6.25 8H7ZM10 6V6.75C10.9554 6.75 11.6923 7.09978 12.0322 7.49153L12.5987 7L13.1651 6.50847C12.4676 5.70461 11.2654 5.25 10 5.25V6ZM10 10V10.75C10.701 10.75 11.3017 10.9408 11.7053 11.2098C12.1141 11.4824 12.25 11.7765 12.25 12H13H13.75C13.75 11.1189 13.2143 10.4131 12.5374 9.96175C11.8551 9.50693 10.9558 9.25 10 9.25V10ZM10 5H9.25V6H10H10.75V5H10ZM10 14L9.25002 14L9.25 15L10 15L10.75 15L10.75 14L10 14ZM10 14L10 13.25C9.04459 13.25 8.30776 12.9002 7.96784 12.5085L7.40137 13L6.83489 13.4915C7.53239 14.2954 8.73463 14.75 10 14.75L10 14ZM13 12H12.25C12.25 12.2235 12.1141 12.5176 11.7053 12.7902C11.3018 13.0592 10.701 13.25 10 13.25V14V14.75C10.9559 14.75 11.8551 14.4931 12.5374 14.0383C13.2143 13.5869 13.75 12.8811 13.75 12H13ZM10 6L9.25 6L9.25002 14L10 14L10.75 14L10.75 6L10 6Z" fill="currentColor"/>
                                            </svg>
                                            ';
                                    break;
                                case 'edit-profile':
                                    $icon = '<svg class="wpuf-w-5 wpuf-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>';
                                    break;
                                case 'billing-address':
                                    $icon = '<svg class="wpuf-w-5 wpuf-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>';
                                    break;
                                default:
                                    $icon = '<svg class="wpuf-w-5 wpuf-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                            }

                            echo sprintf(
                                '<li><a href="%s" class="%s">%s<span>%s</span></a></li>',
                                esc_url( add_query_arg( [ 'section' => $section ], get_permalink() ) ),
                                esc_attr( $active_class ),
                                $icon,
                                esc_html( $label )
                             );
                        }
                    }
                ?>
            </ul>
        </nav>

        <!-- Logout Link -->
        <div class="wpuf-logout-section">
            <a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>" class="wpuf-logout-link">
                <svg class="wpuf-w-5 wpuf-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span><?php esc_html_e( 'Log out', 'wp-user-frontend' ); ?></span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="wpuf-account-content">
        <?php
            if ( !empty( $current_section ) && is_user_logged_in() ) {
                do_action( "wpuf_account_content_{$current_section}", $sections, $current_section );
            }
        ?>
    </main>
</div>
