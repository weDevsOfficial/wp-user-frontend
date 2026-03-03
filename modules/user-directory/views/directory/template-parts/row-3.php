<?php
/**
 * Row template for layout-3 (grid card style)
 *
 * Available variables:
 * @var WP_User $user
 * @var array $columns
 * @var int $avatar_size
 *
 * @since 4.2.0
 */

// Get user data
$user_name  = $user->display_name;
$user_login = $user->user_login;
$user_email = $user->user_email;
$user_url   = $user->user_url;
$user_bio   = get_user_meta( $user->ID, 'description', true );

// Get first and last name
$first_name = get_user_meta( $user->ID, 'first_name', true );
$last_name  = get_user_meta( $user->ID, 'last_name', true );

// Get phone number
$phone = get_user_meta( $user->ID, 'wpuf_profile_phone', true );

// Get avatar size for card layout
$card_avatar_size = ! empty( $avatar_size ) ? $avatar_size : 128;

// Use simple rounded-full class - size is applied via inline style in the function
$avatar = wpuf_ud_get_user_avatar_html( $user, $card_avatar_size, '!wpuf-rounded-full' );
?>

<li class="!wpuf-w-full !wpuf-max-w-[383px] !wpuf-min-h-[300px] !wpuf-border !wpuf-border-gray-300 !wpuf-rounded-lg !wpuf-p-6 !wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-between" style="border: 1px solid #d1d5db;">
    <!-- Top Content Container -->
    <div class="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-gap-3 wpuf-flex-grow">
        <!-- Avatar -->
        <div class="wpuf-flex-shrink-0">
            <?php echo $avatar; ?>
        </div>

        <!-- User Information -->
        <div class="wpuf-flex wpuf-flex-col wpuf-items-center wpuf-text-center">
        <!-- Full Name -->
        <h3 class="wpuf-text-[20px] wpuf-leading-[28px] wpuf-font-semibold wpuf-text-gray-900">
            <?php
            $name_parts = [];
            if ( $first_name ) {
                $name_parts[] = esc_html( $first_name );
            }
            if ( $last_name ) {
                $name_parts[] = esc_html( $last_name );
            }
            if ( ! empty( $name_parts ) ) {
                echo implode( ' ', $name_parts );
            } elseif ( $user_name ) {
                echo esc_html( $user_name );
            }
            ?>
        </h3>

        <!-- Email -->
        <?php if ( $user_email ) : ?>
            <p class="wpuf-text-[14px] wpuf-font-normal wpuf-text-gray-600 !wpuf-m-0">
                <a href="mailto:<?php echo esc_url( $user_email ); ?>" target="_blank" rel="noopener" class="wpuf-text-gray-500 hover:wpuf-text-gray-900">
                    <?php echo esc_html( $user_email ); ?>
                </a>
            </p>
        <?php endif; ?>

        <!-- Website -->
        <?php if ( $user_url ) : ?>
            <p class="wpuf-text-[14px] wpuf-font-normal wpuf-text-gray-600 !wpuf-m-0">
                <a href="<?php echo esc_url( $user_url ); ?>" target="_blank" rel="noopener" class="wpuf-text-gray-500 hover:wpuf-text-gray-900">
                    <?php echo esc_html( $user_url ); ?>
                </a>
            </p>
        <?php endif; ?>

        <!-- Phone Number -->
        <?php if ( $phone ) : ?>
            <p class="wpuf-text-[14px] wpuf-font-normal wpuf-text-gray-600 !wpuf-m-0">
                <a href="tel:<?php echo esc_attr( $phone ); ?>" class="wpuf-text-gray-500 hover:wpuf-text-gray-900">
                    <?php echo esc_html( $phone ); ?>
                </a>
            </p>
        <?php endif; ?>

        <!-- Social Icons -->
        <?php
        // Include social icons
        include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/social-icons.php';
        ?>
        </div>
    </div>

    <!-- View Profile Button -->
    <div class="wpuf-flex-shrink-0 wpuf-mt-4">
        <?php
        // Generate profile URL - matching logic from row-1.php
        if ( ! empty( $profile_url_helper ) && is_callable( $profile_url_helper ) ) {
            // Get profile permalink type from all_data
            $profile_permalink = ! empty( $all_data['profile_permalink'] ) ? $all_data['profile_permalink'] : 'username';
            $profile_url = call_user_func( $profile_url_helper, $user, $profile_permalink );
        } else {
            // Prepare data for wpuf_ud_get_profile_url with profile_base key
            $profile_data = ! empty( $all_data ) ? $all_data : [];
            $profile_data['profile_base'] = ! empty( $all_data['profile_permalink'] ) ? $all_data['profile_permalink'] : 'username';
            $profile_url = wpuf_ud_get_profile_url( $user, $profile_data );
        }

        // Add directory state parameters to preserve pagination context
        $dir_params = [];

        // Get current page number - first check all_data (AJAX), then WordPress query vars, then GET
        $current_page = 1;
        if ( ! empty( $all_data['current_page'] ) ) {
            $current_page = absint( $all_data['current_page'] );
        } elseif ( get_query_var( 'paged' ) ) {
            $current_page = get_query_var( 'paged' );
        } elseif ( get_query_var( 'page' ) ) {
            $current_page = get_query_var( 'page' );
        } elseif ( isset( $_GET['page'] ) ) {
            $current_page = intval( $_GET['page'] );
        }

        if ( $current_page > 1 ) {
            $dir_params['dir_page'] = $current_page;
        }

        // Preserve sorting and search parameters - check all_data first (AJAX), then GET
        if ( ! empty( $all_data['orderby'] ) ) {
            $dir_params['orderby'] = sanitize_text_field( $all_data['orderby'] );
        } elseif ( isset( $_GET['orderby'] ) ) {
            $dir_params['orderby'] = sanitize_text_field( $_GET['orderby'] );
        }

        if ( ! empty( $all_data['order'] ) ) {
            $dir_params['order'] = sanitize_text_field( $all_data['order'] );
        } elseif ( isset( $_GET['order'] ) ) {
            $dir_params['order'] = sanitize_text_field( $_GET['order'] );
        }

        if ( ! empty( $all_data['search'] ) ) {
            $dir_params['search'] = sanitize_text_field( $all_data['search'] );
        } elseif ( isset( $_GET['search'] ) ) {
            $dir_params['search'] = sanitize_text_field( $_GET['search'] );
        }

        // Add parameters to profile URL if any exist
        if ( ! empty( $dir_params ) ) {
            $profile_url = add_query_arg( $dir_params, $profile_url );
        }
        ?>
        <a href="<?php echo esc_url( $profile_url ); ?>" class="wpuf-bg-purple-600 wpuf-text-white wpuf-no-underline wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-rounded-md" style="text-decoration: none;">
            <?php esc_html_e( 'View Profile', 'wp-user-frontend' ); ?>
            <span class="wpuf-sr-only">, <?php echo esc_html( $user_name ); ?></span>
        </a>
    </div>
</li>
