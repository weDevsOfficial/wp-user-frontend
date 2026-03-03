<?php
$tab_title = ! empty( $tab_title ) ? $tab_title : __( 'Comments', 'wp-user-frontend' );

// Get current page from query string for comments pagination
$comments_page = isset( $_GET['comments_page'] ) ? max( 1, intval( $_GET['comments_page'] ) ) : 1;
$comments_per_page = 10;

// Get total comments count first
$total_comments_args = [
    'user_id' => $user->ID,
    'status'  => 'approve',
    'count'   => true
];
$total_comments_count = get_comments( $total_comments_args );

// Calculate offset
$offset = ( $comments_page - 1 ) * $comments_per_page;

// Get paginated comments
$comments = get_comments([
    'user_id' => $user->ID,
    'status'  => 'approve',
    'orderby' => 'comment_date',
    'order'   => 'DESC',
    'number'  => $comments_per_page,
    'offset'  => $offset
]);

// Prepare pagination data
$pagination = [
    'total_pages'   => ceil( $total_comments_count / $comments_per_page ),
    'current_page'  => $comments_page,
    'per_page'      => $comments_per_page,
    'total_items'   => $total_comments_count
];
?>

<div class="wp-user-frontendfile-section wpuf-comments-section !wpuf-mb-8">
    <h1 class="profile-section-heading !wpuf-text-2xl !wpuf-font-bold !wpuf-text-gray-900 !wpuf-mb-6">
        <?php echo esc_html( $tab_title ); ?>
    </h1>

    <?php if ( ! empty( $comments ) ) : ?>
        <div class="comments-list !wpuf-space-y-4">
            <?php foreach ( $comments as $comment ) : ?>
                <div class="comment-item !wpuf-bg-white !wpuf-p-6 !wpuf-rounded-xl !wpuf-border !wpuf-border-gray-200 hover:!wpuf-border-emerald-200 !wpuf-transition-colors !wpuf-duration-200">
                    <div class="comment-header !wpuf-flex !wpuf-justify-between !wpuf-items-start !wpuf-mb-4">
                        <div class="comment-meta">
                            <h4 class="commented-post !wpuf-font-semibold !wpuf-text-gray-900 !wpuf-text-base !wpuf-mb-1">
                                <?php echo esc_html( get_the_title( $comment->comment_post_ID ) ); ?>
                            </h4>
                            <div class="comment-date !wpuf-text-sm !wpuf-text-gray-500">
                                <?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $comment->comment_date ) ); ?>
                            </div>
                        </div>
                        <a href="<?php echo get_comment_link( $comment ); ?>" 
                           target="_blank"
                           class="comment-link !wpuf-inline-flex !wpuf-items-center !wpuf-gap-1 !wpuf-px-3 !wpuf-py-1.5 !wpuf-text-xs !wpuf-font-medium !wpuf-text-emerald-600 !wpuf-bg-emerald-50 !wpuf-rounded-lg hover:!wpuf-bg-emerald-100 !wpuf-transition-colors !wpuf-no-underline">
                            <?php esc_html_e( 'View', 'wp-user-frontend' ); ?>
                            <svg class="!wpuf-w-3 !wpuf-h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="comment-content !wpuf-text-gray-700 !wpuf-leading-relaxed !wpuf-text-sm">
                        <p><?php echo wp_trim_words( $comment->comment_content, 30, '...' ); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php else : ?>
        <div class="!wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-py-20 !wpuf-bg-gray-50 !wpuf-rounded-xl">
            <div class="!wpuf-mb-4">
                <svg class="!wpuf-w-24 !wpuf-h-24 !wpuf-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <p class="!wpuf-text-xl !wpuf-font-medium !wpuf-text-gray-900 !wpuf-mb-2"><?php esc_html_e( 'No comments found', 'wp-user-frontend' ); ?></p>
            <p class="!wpuf-text-base !wpuf-text-gray-500"><?php esc_html_e( 'This user hasn\'t posted any comments yet.', 'wp-user-frontend' ); ?></p>
        </div>
    <?php endif; ?>

    <?php
    // Add pagination if there are multiple pages
    if ( $pagination['total_pages'] > 1 ) {
        // Build base URL and query args for pagination
        $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $parsed_url = wp_parse_url( $current_url );
        $base_url = $parsed_url['path'] ?? '';

        // Parse current URL to get all parameters
        $all_params = [];
        if ( ! empty( $parsed_url['query'] ) ) {
            parse_str( $parsed_url['query'], $all_params );
        }

        // Create clean query args with only directory-related parameters
        $clean_query_args = [];
        $preserve_params = ['dir_page', 'orderby', 'order', 'search'];
        foreach ( $preserve_params as $param ) {
            if ( isset( $all_params[$param] ) ) {
                $clean_query_args[$param] = $all_params[$param];
            }
        }

        // Helper function to build pagination URLs for comments
        if ( ! function_exists( 'wpuf_ud_build_comments_page_url' ) ) {
            function wpuf_ud_build_comments_page_url( $base_url, $clean_args, $page ) {
                // Set the tab and page for comments
                $final_args = $clean_args;
                $final_args['tab'] = 'comments';
                $final_args['comments_page'] = $page;

                return add_query_arg( $final_args, $base_url );
            }
        }

        $current = $pagination['current_page'];
        $total = $pagination['total_pages'];

        // Calculate visible page range - show max 7 pages
        $max_visible = 7;
        $pages = [];

        if ( $total <= $max_visible ) {
            for ( $i = 1; $i <= $total; $i++ ) {
                $pages[] = $i;
            }
        } else {
            $pages[] = 1;
            $start = max( 2, $current - 2 );
            $end = min( $total - 1, $current + 2 );

            if ( $start > 2 ) {
                $pages[] = '...';
            }

            for ( $i = $start; $i <= $end; $i++ ) {
                $pages[] = $i;
            }

            if ( $end < $total - 1 ) {
                $pages[] = '...';
            }

            $pages[] = $total;
        }
        ?>

        <div class="wpuf-ud-comments-pagination !wpuf-mt-6">
            <nav class="!wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-gap-2" aria-label="<?php esc_attr_e( 'Comments Pagination', 'wp-user-frontend' ); ?>">

                <!-- Previous Button -->
                <?php if ( $current > 1 ) : ?>
                    <a href="<?php echo esc_url( wpuf_ud_build_comments_page_url( $base_url, $clean_query_args, $current - 1 ) ); ?>"
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
                                  class="!wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-emerald-600 !wpuf-border-t-2 !wpuf-border-emerald-600">
                                <?php echo esc_html( $page ); ?>
                            </span>
                        <?php else : ?>
                            <a href="<?php echo esc_url( wpuf_ud_build_comments_page_url( $base_url, $clean_query_args, $page ) ); ?>"
                               class="!wpuf-no-underline wpuf-pagination-link !wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-700 hover:!wpuf-text-emerald-600 hover:!wpuf-border-emerald-600 hover:!wpuf-border-t-2 !wpuf-transition-colors">
                                <?php echo esc_html( $page ); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Next Button -->
                <?php if ( $current < $total ) : ?>
                    <a href="<?php echo esc_url( wpuf_ud_build_comments_page_url( $base_url, $clean_query_args, $current + 1 ) ); ?>"
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
    <?php } ?>
</div>