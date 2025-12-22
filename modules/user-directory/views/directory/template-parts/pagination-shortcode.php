<?php
/**
 * Pagination template specifically for shortcode rendering
 *
 * This template provides pagination for shortcode-based user directory listings
 * with a simple, clean design
 *
 * Expects:
 * @param array $pagination [ 'total_pages', 'current_page', 'per_page', 'total_items' ]
 * @param string $base_url (optional) Base URL for pagination links
 * @param array $query_args (optional) Query args to preserve (except 'page')
 * @param string $context (optional) Context identifier ('shortcode' or 'block')
 * @param string $layout (optional) Layout identifier for color scheme
 *
 * @since 4.2.0
 */

// Exit if no pagination needed
if ( empty( $pagination ) || ! isset( $pagination['total_pages'] ) || (int) $pagination['total_pages'] <= 1 ) {
    return;
}

// Also exit if there are no items (users)
if ( isset( $pagination['total_items'] ) && (int) $pagination['total_items'] === 0 ) {
    return;
}

// Get layout-specific colors
$layout = isset( $layout ) ? $layout : 'layout-1';

// Check if this is for profile pagination (determined by context or explicit flag)
$is_profile = isset( $is_profile ) ? $is_profile : false;

// Get layout colors - use profile colors if in profile context
if ( $is_profile ) {
    $colors = wpuf_ud_get_profile_layout_colors( $layout );
} else {
    $colors = wpuf_ud_get_layout_colors( $layout );
}

$current = (int) $pagination['current_page'];
$total   = (int) $pagination['total_pages'];

// Determine base_url and query_args if not provided
if ( ! isset( $base_url ) || ! isset( $query_args ) ) {
    $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $parsed_url = wp_parse_url( $current_url );
    $base_url = $parsed_url['path'] ?? '';

    $query_args = [];
    if ( ! empty( $parsed_url['query'] ) ) {
        parse_str( $parsed_url['query'], $query_args );
    }
    unset( $query_args['page'] ); // We'll set this per link
}


// Calculate visible page range - show max 7 pages
$max_visible = 7;
$pages = [];

if ( $total <= $max_visible ) {
    // Show all pages
    for ( $i = 1; $i <= $total; $i++ ) {
        $pages[] = $i;
    }
} else {
    // Always show first page
    $pages[] = 1;

    // Calculate range around current page
    $start = max( 2, $current - 2 );
    $end = min( $total - 1, $current + 2 );

    // Add ellipsis if needed before range
    if ( $start > 2 ) {
        $pages[] = '...';
    }

    // Add the range of pages
    for ( $i = $start; $i <= $end; $i++ ) {
        $pages[] = $i;
    }

    // Add ellipsis if needed after range
    if ( $end < $total - 1 ) {
        $pages[] = '...';
    }

    // Always show last page
    $pages[] = $total;
}
?>

<div class="wpuf-ud-pagination-shortcode !wpuf-mt-10">
    <nav class="!wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-gap-2" aria-label="<?php esc_attr_e( 'Pagination', 'wp-user-frontend' ); ?>">

        <!-- Previous Button -->
        <?php if ( $current > 1 ) : ?>
            <a href="<?php echo wpuf_ud_build_page_url( $base_url, $query_args, $current - 1 ); ?>"
               class="wpuf-pagination-link !wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-700 hover:!wpuf-text-gray-900">
                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <?php esc_html_e( 'Previous', 'wp-user-frontend' ); ?>
            </a>
        <?php else : ?>
            <span class="!wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-300 !wpuf-cursor-not-allowed">
                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <?php esc_html_e( 'Previous', 'wp-user-frontend' ); ?>
            </span>
        <?php endif; ?>

        <!-- Page Numbers -->
        <div class="!wpuf-flex !wpuf-items-center !wpuf-gap-1">
            <?php foreach ( $pages as $page ) : ?>
                <?php if ( $page === '...' ) : ?>
                    <span class="!wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-500">
                        &hellip;
                    </span>
                <?php elseif ( $page == $current ) : ?>
                    <span aria-current="page"
                          class="!wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium <?php echo esc_attr( $colors['text_primary_600'] ); ?> !wpuf-border-t-2 <?php echo esc_attr( $colors['border_primary_600'] ); ?>">
                        <?php echo esc_html( $page ); ?>
                    </span>
                <?php else : ?>
                    <a href="<?php echo wpuf_ud_build_page_url( $base_url, $query_args, $page ); ?>"
                       class="!wpuf-no-underline wpuf-pagination-link !wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-700 <?php echo esc_attr( $colors['hover_primary_600'] ); ?> <?php echo esc_attr( $colors['hover_border_primary_600'] ); ?> hover:!wpuf-border-t-2 !wpuf-transition-colors">
                        <?php echo esc_html( $page ); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Next Button -->
        <?php if ( $current < $total ) : ?>
            <a href="<?php echo wpuf_ud_build_page_url( $base_url, $query_args, $current + 1 ); ?>"
               class="wpuf-pagination-link !wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-700 hover:!wpuf-text-gray-900">
                <?php esc_html_e( 'Next', 'wp-user-frontend' ); ?>
                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        <?php else : ?>
            <span class="!wpuf-inline-flex !wpuf-items-center !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-text-gray-300 !wpuf-cursor-not-allowed">
                <?php esc_html_e( 'Next', 'wp-user-frontend' ); ?>
                <svg class="!wpuf-w-5 !wpuf-h-5 !wpuf-ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        <?php endif; ?>

    </nav>
</div>
