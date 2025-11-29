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

// Get avatar data with custom profile photo priority
$avatar_data = wpuf_ud_get_block_avatar_data( $user, $size, 'initials' );
$avatar_url = $avatar_data['url'];
$avatar_type = $avatar_data['type'];
$avatar_alt = $avatar_data['alt'];

// Get user initials for fallback
$initials = wpuf_ud_get_user_initials( $user );

// Check if current user can edit this profile (Free version - only own profile)
$can_edit = is_user_logged_in() && get_current_user_id() === $user->ID;

?>
<div class="wpuf-avatar-wrapper !wpuf-relative !wpuf-w-full !wpuf-h-full <?php echo esc_attr( $wrapper_class ); ?>" data-user-id="<?php echo esc_attr( $user->ID ); ?>">
    <?php if ( $avatar_type === 'custom' || $avatar_type === 'gravatar' ) : ?>
        <!-- Show avatar image with fallback -->
        <img src="<?php echo esc_url( $avatar_url ); ?>"
             alt="<?php echo esc_attr( $avatar_alt ); ?>"
             class="!wpuf-w-full !wpuf-h-full !wpuf-object-cover !wpuf-rounded-full wpuf-avatar-image"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
        <!-- Fallback initials (hidden by default) -->
        <div class="wpuf-avatar-initials !wpuf-w-full !wpuf-h-full !wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-bg-gradient-to-br !wpuf-from-blue-500 !wpuf-to-purple-600 !wpuf-text-white !wpuf-text-2xl !wpuf-font-bold !wpuf-rounded-full" style="display: none;">
            <span><?php echo esc_html( $initials ); ?></span>
        </div>
    <?php else : ?>
        <!-- Show initials (no image available) -->
        <div class="wpuf-avatar-initials !wpuf-w-full !wpuf-h-full !wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-bg-gradient-to-br !wpuf-from-blue-500 !wpuf-to-purple-600 !wpuf-text-white !wpuf-text-2xl !wpuf-font-bold !wpuf-rounded-full">
            <span><?php echo esc_html( $initials ); ?></span>
        </div>
    <?php endif; ?>
</div>