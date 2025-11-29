<?php
$user_id = $user->ID;

// Get current page from query string for posts pagination
$posts_page     = isset( $_GET['posts_page'] ) ? max( 1, intval( $_GET['posts_page'] ) ) : 1;
$posts_per_page = 10;

// Get user posts using WP_Query
$post_args = [
    'author'         => $user_id,
    'post_status'    => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged'          => $posts_page,
];

$user_posts = new WP_Query( $post_args );

// Prepare pagination data
$pagination = [
    'total_pages'   => $user_posts->max_num_pages,
    'current_page'  => $posts_page,
    'per_page'      => $posts_per_page,
    'total_items'   => $user_posts->found_posts,
];
?>

<div class="wp-user-frontendfile-section wpuf-posts-section !wpuf-mb-8">
    <h1 class="profile-section-heading !wpuf-text-2xl !wpuf-font-bold !wpuf-text-gray-900 !wpuf-mb-6">
        <?php esc_html_e( 'Posts', 'wp-user-frontend' ); ?>
    </h1>
    
    <?php if ( $user_posts->have_posts() ) : ?>
        <div class="posts-table !wpuf-bg-gray-50 !wpuf-rounded-xl !wpuf-border !wpuf-border-gray-200 !wpuf-overflow-hidden">
            <!-- Table Header -->
            <div class="table-header !wpuf-bg-white !wpuf-border-b !wpuf-border-gray-200 !wpuf-h-[50px] !wpuf-flex !wpuf-items-center">
                <div class="!wpuf-grid !wpuf-grid-cols-12 !wpuf-gap-4 !wpuf-px-6 !wpuf-items-center !wpuf-w-full">
                    <div class="!wpuf-col-span-6 !wpuf-text-base !wpuf-font-normal !wpuf-text-emerald-600 !wpuf-leading-none">
                        <?php esc_html_e( 'Post Title', 'wp-user-frontend' ); ?>
                    </div>
                    <div class="!wpuf-col-span-3 !wpuf-text-base !wpuf-font-normal !wpuf-text-emerald-600 !wpuf-leading-none">
                        <?php esc_html_e( 'Publish Date', 'wp-user-frontend' ); ?>
                    </div>
                    <div class="!wpuf-col-span-3 !wpuf-text-base !wpuf-font-normal !wpuf-text-emerald-600 !wpuf-text-right !wpuf-leading-none">
                        <?php esc_html_e( 'Action', 'wp-user-frontend' ); ?>
                    </div>
                </div>
            </div>
            
            <!-- Table Body -->
            <div class="table-body !wpuf-bg-white">
                <?php while ( $user_posts->have_posts() ) : $user_posts->the_post(); ?>
                <div class="table-row !wpuf-border-b !wpuf-border-gray-100 last:!wpuf-border-b-0 hover:!wpuf-bg-emerald-50 !wpuf-transition-colors !wpuf-duration-150 !wpuf-h-[58px] !wpuf-flex !wpuf-items-center">
                    <div class="!wpuf-grid !wpuf-grid-cols-12 !wpuf-gap-4 !wpuf-px-6 !wpuf-items-center !wpuf-w-full">
                        <div class="!wpuf-col-span-6">
                            <div class="!wpuf-text-base !wpuf-font-normal !wpuf-text-gray-900 !wpuf-leading-none">
                                <?php echo wp_trim_words( get_the_title(), 10 ); ?>
                            </div>
                        </div>
                        <div class="!wpuf-col-span-3">
                            <div class="!wpuf-text-base !wpuf-font-normal !wpuf-text-gray-600 !wpuf-leading-none">
                                <?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
                            </div>
                        </div>
                        <div class="!wpuf-col-span-3 !wpuf-text-right">
                            <a href="<?php echo get_permalink(); ?>" 
                               target="_blank"
                               class="!wpuf-inline-flex !wpuf-items-center !wpuf-gap-1 !wpuf-text-base !wpuf-font-normal !wpuf-text-gray-700 hover:!wpuf-text-emerald-600 !wpuf-transition-colors !wpuf-no-underline">
                                <?php esc_html_e( 'Post Link', 'wp-user-frontend' ); ?>
                                <svg class="!wpuf-w-4 !wpuf-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    <?php else : ?>
        <div class="!wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-py-20 !wpuf-bg-gray-50 !wpuf-rounded-xl">
            <div class="!wpuf-mb-4">
                <svg class="!wpuf-w-24 !wpuf-h-24 !wpuf-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
            </div>
            <p class="!wpuf-text-xl !wpuf-font-medium !wpuf-text-gray-900 !wpuf-mb-2"><?php esc_html_e( 'No posts found', 'wp-user-frontend' ); ?></p>
            <p class="!wpuf-text-base !wpuf-text-gray-500"><?php esc_html_e( 'This user hasn\'t published any posts yet.', 'wp-user-frontend' ); ?></p>
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

        // Helper function to build pagination URLs for posts
        if ( ! function_exists( 'wpuf_ud_build_posts_page_url' ) ) {
            function wpuf_ud_build_posts_page_url( $base_url, $clean_args, $page ) {
                // Set the tab and page for posts
                $final_args = $clean_args;
                $final_args['tab'] = 'posts';
                $final_args['posts_page'] = $page;

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

        <div class="wpuf-ud-posts-pagination !wpuf-mt-6">
            <nav class="!wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-gap-2" aria-label="<?php esc_attr_e( 'Posts Pagination', 'wp-user-frontend' ); ?>">

                <!-- Previous Button -->
                <?php if ( $current > 1 ) : ?>
                    <a href="<?php echo esc_url( wpuf_ud_build_posts_page_url( $base_url, $clean_query_args, $current - 1 ) ); ?>"
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
                            <a href="<?php echo esc_url( wpuf_ud_build_posts_page_url( $base_url, $clean_query_args, $page ) ); ?>"
                               class="!wpuf-no-underline wpuf-pagination-link !wpuf-relative !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-700 hover:!wpuf-text-emerald-600 hover:!wpuf-border-emerald-600 hover:!wpuf-border-t-2 !wpuf-transition-colors">
                                <?php echo esc_html( $page ); ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Next Button -->
                <?php if ( $current < $total ) : ?>
                    <a href="<?php echo esc_url( wpuf_ud_build_posts_page_url( $base_url, $clean_query_args, $current + 1 ) ); ?>"
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

<?php
wp_reset_postdata();
?>