<?php
/**
 * Admin screens: page states, notices, Pro state and previews
 *
 * Split out of wpuf-functions.php, which still loads every file here; every
 * function keeps its name.
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

use WeDevs\Wpuf\Free\Pro_Prompt;

/**
 * Show helper texts to understand the type of page in admin page listing
 *
 * @since 2.6.0
 *
 * @param array   $state
 * @param WP_Post $post
 *
 * @return array
 */
function wpuf_admin_page_states( $state, $post ) {
    if ( 'page' !== $post->post_type ) {
        return $state;
    }

    $pattern = '/\[(wpuf[\w\-\_]+).+\]/';

    preg_match_all( $pattern, $post->post_content, $matches );
    $matches = array_unique( $matches[0] );

    if ( ! empty( $matches ) ) {
        $page      = '';
        $shortcode = $matches[0];

        if ( '[wpuf_account]' === $shortcode ) {
            $page = 'WPUF Account Page';
        } elseif ( '[wpuf_edit]' === $shortcode ) {
            $page = 'WPUF Post Edit Page';
        } elseif ( '[wpuf-login]' === $shortcode ) {
            $page = 'WPUF Login Page';
        } elseif ( '[wpuf_sub_pack]' === $shortcode ) {
            $page = 'WPUF Subscription Page';
        } elseif ( '[wpuf_editprofile]' === $shortcode ) {
            $page = 'WPUF Profile Edit Page';
        } elseif ( stristr( $shortcode, '[wpuf_dashboard' ) ) {
            $page = 'WPUF Dashboard Page';
        } elseif ( stristr( $shortcode, '[wpuf_profile type="registration"' ) ) {
            $page = 'WPUF Registration Page';
        } elseif ( stristr( $shortcode, '[wpuf_profile type="profile"' ) ) {
            $page = 'WPUF Profile Edit Page';
        } elseif ( stristr( $shortcode, '[wpuf_form' ) ) {
            $page = 'WPUF Form Page';
        }

        if ( ! empty( $page ) ) {
            $state['wpuf'] = $page;
        }
    }

    return $state;
}

/**
 * The HTML preview part when hovering over a pro settings field
 *
 * @since 3.6.0
 *
 * @return string
 */
function wpuf_get_pro_preview_html() {
    return sprintf(
        '<div class="pro-field-overlay">
                        <a href="%1$s" target="%2$s" class="%3$s">Upgrade to PRO</a>
                    </div>', esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ), '_blank', 'wpuf-button button-upgrade-to-pro'
    );
}

/**
 * The HTML tooltip when hovering over a pro settings field
 *
 * @since 3.6.0
 *
 * @return string
 */
function wpuf_get_pro_preview_tooltip() {
    $check_icon = WPUF_ROOT . '/assets/images/check.svg';
    $features = [
        '24/7 Priority Support',
        '20+ Premium Modules',
        'User Activity and Reports',
        'Private Messaging Option',
        'License for 20 websites',
    ];
    $html = '<div class="wpuf-pro-field-tooltip">';
    $html .= '<h3 class="tooltip-header">Available in Pro. Also enjoy:</h3>';
    $html .= '<ul>';

    foreach ( $features as $feature ) {
        $html .= sprintf(
            '<li><span class="tooltip-check">%1$s</span> %2$s</li>',
            file_get_contents( $check_icon ),
            esc_html( $feature )
        );
    }

    $html .= '</ul>';
    $html .= sprintf(
        '<div class="pro-link"><a href="%1$s" target="%2$s" class="%3$s">Upgrade to PRO</a></div>',
        esc_url( Pro_Prompt::get_upgrade_to_pro_popup_url() ), '_blank', 'wpuf-button button-upgrade-to-pro'
    );

    $html .= '<i></i>';
    $html .= '</div>';

    return $html;
}

/**
 * Remove all kinds of admin notices from admin dashboard
 *
 * Since we don't have much space left on top of the page,
 * we have to remove all kinds of admin notices
 *
 * @since 2.5
 *
 * @since 4.0.11 function moved to wpuf-functions.php
 *
 * @return void
 */
function wpuf_remove_admin_notices() {
    remove_all_actions( 'network_admin_notices' );
    remove_all_actions( 'user_admin_notices' );
    remove_all_actions( 'admin_notices' );
    remove_all_actions( 'all_admin_notices' );
}

/**
 * Load the Headway badge
 *
 * @since 4.0.11
 *
 * @return void
 */
function wpuf_load_headway_badge( $selector = '#wpuf-headway-icon' ) {
    wp_enqueue_script( 'wpuf-headway-script' );
    ?>
    <script>
        const selector = '<?php echo $selector; ?>';
        const badgeCount = selector + ' ul li.headway-icon span#HW_badge_cont.HW_visible';

        const HW_config = {
            selector: selector,
            account: 'JPqPQy',
            callbacks: {
                // The badge element exists only while there are unseen posts.
                onWidgetReady: function ( widget ) {
                    const badge = document.querySelector(badgeCount);

                    if ( badge && widget.getUnseenCount() === 0 ) {
                        badge.style = 'opacity: 0';
                    }
                },
                onHideWidget: function(){
                    const badge = document.querySelector(badgeCount);

                    if ( badge ) {
                        badge.style = 'opacity: 0';
                    }
                }
            }
        };

    </script>

    <?php
}

/**
 * Check if the pro version is active
 *
 * @since 4.1.0
 *
 * @return bool
 */
function wpuf_is_pro_active() {
    // An older Pro the version guard did not start counts as not active.
    return class_exists( 'WP_User_Frontend_Pro' ) && ! \WeDevs\Wpuf\Platform\VersionGuard::pro_needs_update();
}
