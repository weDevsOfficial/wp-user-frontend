<?php
/**
 * Sort and Filter Fields Template Part
 * Free version - simplified sorting options
 *
 * @since 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get context and data
$block_id = isset( $block_id ) ? $block_id : 'wpuf-directory-' . uniqid();
$page_id = isset( $page_id ) ? $page_id : get_the_ID();

// Get sorting options from data
$orderby = ! empty( $all_data['orderby'] ) ? $all_data['orderby'] : 'id';
$order = ! empty( $all_data['order'] ) ? $all_data['order'] : 'desc';

// Get layout-specific colors
$layout = isset( $layout ) ? $layout : 'layout-1';
$colors = wpuf_ud_get_layout_colors( $layout );
?>

<!-- Filter Controls -->
<div class="wpuf-filter-controls !wpuf-flex !wpuf-flex-col md:!wpuf-flex-row !wpuf-items-stretch md:!wpuf-items-center !wpuf-gap-2 md:!wpuf-gap-3 !wpuf-w-full md:!wpuf-w-auto">

    <!-- Sort By Dropdown -->
    <div class="wpuf-sort-by-control !wpuf-w-full md:!wpuf-w-auto">
        <select class="wpuf-ud-sort-by !wpuf-w-full md:!wpuf-w-[140px] !wpuf-h-[42px] !wpuf-pr-10 !wpuf-pl-4 !wpuf-py-2 !wpuf-bg-white !wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md !wpuf-text-sm !wpuf-appearance-none !wpuf-bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2714%27%20height%3D%278%27%20viewBox%3D%270%200%2014%208%27%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%3E%3Cpath%20d%3D%27M1%201l6%206%206-6%27%20stroke%3D%27%239CA3AF%27%20stroke-width%3D%272%27%20fill%3D%27none%27%20fill-rule%3D%27evenodd%27/%3E%3C/svg%3E')] !wpuf-bg-[position:right_0.75rem_center] !wpuf-bg-[size:14px] !wpuf-bg-no-repeat focus:!wpuf-outline-none focus:!wpuf-ring-2 <?php echo esc_attr( $colors['focus_ring_primary_500'] ); ?>"
                data-block-id="<?php echo esc_attr( $block_id ); ?>"
                data-page-id="<?php echo esc_attr( $page_id ); ?>"
                data-default-value="<?php echo esc_attr( $orderby ); ?>">
            <option value="id" <?php selected( $orderby, 'id' ); ?>><?php esc_html_e( 'User ID', 'wp-user-frontend' ); ?></option>
        </select>
    </div>

    <!-- Sort Order (ASC/DESC) -->
    <div class="wpuf-sort-order-control !wpuf-w-full md:!wpuf-w-auto">
        <select class="wpuf-ud-sort-order !wpuf-w-full md:!wpuf-w-[100px] !wpuf-h-[42px] !wpuf-pr-10 !wpuf-pl-4 !wpuf-py-2 !wpuf-bg-white !wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md !wpuf-text-sm !wpuf-appearance-none !wpuf-bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2714%27%20height%3D%278%27%20viewBox%3D%270%200%2014%208%27%20xmlns%3D%27http%3A//www.w3.org/2000/svg%27%3E%3Cpath%20d%3D%27M1%201l6%206%206-6%27%20stroke%3D%27%239CA3AF%27%20stroke-width%3D%272%27%20fill%3D%27none%27%20fill-rule%3D%27evenodd%27/%3E%3C/svg%3E')] !wpuf-bg-[position:right_0.75rem_center] !wpuf-bg-[size:14px] !wpuf-bg-no-repeat focus:!wpuf-outline-none focus:!wpuf-ring-2 <?php echo esc_attr( $colors['focus_ring_primary_500'] ); ?>"
                data-block-id="<?php echo esc_attr( $block_id ); ?>"
                data-page-id="<?php echo esc_attr( $page_id ); ?>"
                data-default-value="<?php echo esc_attr( $order ); ?>">
            <option value="asc" <?php selected( $order, 'asc' ); ?>><?php esc_html_e( 'ASC', 'wp-user-frontend' ); ?></option>
            <option value="desc" <?php selected( $order, 'desc' ); ?>><?php esc_html_e( 'DESC', 'wp-user-frontend' ); ?></option>
        </select>
    </div>

    <!-- Reset Button -->
    <button type="button" class="wpuf-ud-reset-filters !wpuf-w-full md:!wpuf-w-auto !wpuf-h-[42px] !wpuf-px-6 !wpuf-py-2 !<?php echo esc_attr( $colors['primary_600'] ); ?> !wpuf-text-white !wpuf-rounded-md !wpuf-text-sm !wpuf-font-medium <?php echo esc_attr( $colors['hover_primary_700'] ); ?> focus:!wpuf-outline-none focus:!wpuf-ring-2 <?php echo esc_attr( $colors['focus_ring_primary_500'] ); ?> focus:!wpuf-ring-offset-2"
            data-block-id="<?php echo esc_attr( $block_id ); ?>"
            data-page-id="<?php echo esc_attr( $page_id ); ?>">
        <?php esc_html_e( 'Reset', 'wp-user-frontend' ); ?>
    </button>

</div>
