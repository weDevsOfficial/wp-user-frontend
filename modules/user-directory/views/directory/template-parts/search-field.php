<?php
/**
 * Search Field Template Part
 * Free version - simplified search options
 *
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get placeholder text
$placeholder = ! empty( $all_data['search_placeholder'] ) ? $all_data['search_placeholder'] : __( 'Search users...', 'wp-user-frontend' );

// Get current search_by parameter from URL
$current_search_by = ! empty( $_GET['search_by'] ) ? sanitize_text_field( $_GET['search_by'] ) : '';

// Get layout-specific colors
$layout = isset( $layout ) ? $layout : 'layout-1';
$colors = wpuf_ud_get_layout_colors( $layout );
?>

<!-- Search Container - Responsive with Tailwind -->
<div class="wpuf-search-container !wpuf-flex !wpuf-flex-col md:!wpuf-flex-row !wpuf-items-stretch md:!wpuf-items-center !wpuf-gap-2 !wpuf-w-full md:!wpuf-w-auto">

    <!-- Search Input with Icon -->
    <div class="!wpuf-flex !wpuf-items-center !wpuf-justify-between !wpuf-bg-white !wpuf-rounded-md !wpuf-border !wpuf-border-gray-300 !wpuf-w-full md:!wpuf-w-[280px] !wpuf-h-[42px] !wpuf-py-[9px] !wpuf-pr-[15px] !wpuf-pl-[17px] wpuf-ud-search-wrapper"
         data-block-id="<?php echo esc_attr( $block_id ); ?>"
         data-page-id="<?php echo esc_attr( $page_id ); ?>">
        <input
            type="text"
            class="wpuf-ud-search-input !wpuf-bg-transparent !wpuf-border-0 !wpuf-w-full !wpuf-text-gray-900 !wpuf-placeholder-gray-400 focus:!wpuf-outline-none focus:!wpuf-ring-0"
            placeholder="<?php echo esc_attr( $placeholder ); ?>"
            autocomplete="off"
            aria-label="<?php esc_attr_e( 'Search users', 'wp-user-frontend' ); ?>"
        />
        <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-text-gray-400 !wpuf-flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
        </svg>
    </div>

    <!-- Search By Dropdown -->
    <div class="wpuf-search-by-control !wpuf-w-full md:!wpuf-w-auto">
        <select class="wpuf-ud-search-by !wpuf-w-full md:!wpuf-w-[140px] !wpuf-h-[42px] !wpuf-pr-10 !wpuf-pl-4 !wpuf-py-2 !wpuf-bg-white !wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md !wpuf-text-sm !wpuf-appearance-none !wpuf-bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2714%27%20height%3D%278%27%20viewBox%3D%270%200%2014%208%27%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%3E%3Cpath%20d%3D%27M1%201l6%206%206-6%27%20stroke%3D%27%239CA3AF%27%20stroke-width%3D%272%27%20fill%3D%27none%27%20fill-rule%3D%27evenodd%27/%3E%3C/svg%3E')] !wpuf-bg-[position:right_0.75rem_center] !wpuf-bg-[size:14px] !wpuf-bg-no-repeat focus:!wpuf-outline-none focus:!wpuf-ring-2 <?php echo esc_attr( $colors['focus_ring_primary_500'] ); ?>"
                data-block-id="<?php echo esc_attr( $block_id ); ?>"
                data-page-id="<?php echo esc_attr( $page_id ); ?>">
            <option value=""><?php esc_html_e( 'Search By', 'wp-user-frontend' ); ?></option>
            <option value="display_name" <?php selected( $current_search_by, 'display_name' ); ?>><?php esc_html_e( 'Name', 'wp-user-frontend' ); ?></option>
            <option value="user_email" <?php selected( $current_search_by, 'user_email' ); ?>><?php esc_html_e( 'Email', 'wp-user-frontend' ); ?></option>
            <option value="user_login" <?php selected( $current_search_by, 'user_login' ); ?>><?php esc_html_e( 'Username', 'wp-user-frontend' ); ?></option>
        </select>
    </div>

</div>
