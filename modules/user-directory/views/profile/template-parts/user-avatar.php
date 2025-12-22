<?php
/**
 * User Avatar Template Part for Shortcode
 * Displays user avatar with upload functionality for own profile
 *
 * @since 4.2.0
 *
 * @var WP_User $user User object
 * @var int $size Avatar size in pixels (default: 128)
 * @var string $wrapper_class Additional wrapper classes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! $user ) {
    return;
}

// Get avatar size from parameter or use default
$size = isset( $size ) ? intval( $size ) : 128;
$wrapper_class = isset( $wrapper_class ) ? $wrapper_class : '';

// Use the SAME function as directory listing for consistency
// This checks wpuf_profile_photo first, then Gravatar
$avatar_url = wpuf_ud_get_avatar_url( $user, $size );
$has_avatar = ! empty( $avatar_url );

// Get user initials for fallback (same logic as directory)
$first_name = get_user_meta( $user->ID, 'first_name', true );
$last_name = get_user_meta( $user->ID, 'last_name', true );

if ( $first_name && $last_name ) {
    $initials = strtoupper( substr( $first_name, 0, 1 ) . substr( $last_name, 0, 1 ) );
} else {
    $name = $user->display_name ?: $user->user_login;
    $name_parts = explode( ' ', $name );
    if ( count( $name_parts ) >= 2 ) {
        $initials = strtoupper( substr( $name_parts[0], 0, 1 ) . substr( $name_parts[1], 0, 1 ) );
    } else {
        $initials = strtoupper( substr( $name, 0, 2 ) );
    }
}

// Calculate font size for initials (same as directory)
$font_size = max( $size / 2.5, 16 );

// Check if current user can edit this profile (Free version - only own profile)
$can_edit = is_user_logged_in() && get_current_user_id() === $user->ID;

?>
<div class="wpuf-avatar-wrapper !wpuf-relative <?php echo esc_attr( $wrapper_class ); ?>" data-user-id="<?php echo esc_attr( $user->ID ); ?>" style="width: <?php echo esc_attr( $size ); ?>px; height: <?php echo esc_attr( $size ); ?>px;">
    <?php if ( $has_avatar ) : ?>
        <!-- Show ONLY avatar image (custom photo or Gravatar) -->
        <img src="<?php echo esc_url( $avatar_url ); ?>"
             alt="<?php echo esc_attr( $user->display_name ); ?>"
             class="!wpuf-rounded-full !wpuf-object-cover"
             width="<?php echo esc_attr( $size ); ?>"
             height="<?php echo esc_attr( $size ); ?>"
             style="width: <?php echo esc_attr( $size ); ?>px; height: <?php echo esc_attr( $size ); ?>px; object-fit: cover;" />
    <?php else : ?>
        <!-- Show ONLY initials (no image available) -->
        <div class="!wpuf-rounded-full !wpuf-bg-gray-400 !wpuf-text-white !wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-font-semibold"
             style="width: <?php echo esc_attr( $size ); ?>px; height: <?php echo esc_attr( $size ); ?>px; font-size: <?php echo esc_attr( $font_size ); ?>px !important;">
            <?php echo esc_html( $initials ); ?>
        </div>
    <?php endif; ?>
</div>