<div class="wpuf-user-listing" data-block-id="<?php echo esc_attr( $block_id ); ?>" data-page-id="<?php echo esc_attr( $page_id ); ?>" data-layout="<?php echo esc_attr( ! empty( $directory_layout ) ? $directory_layout : 'layout-3' ); ?>" <?php if ( ! empty( $all_data['id'] ) ) : ?>data-directory-id="<?php echo esc_attr( $all_data['id'] ); ?>"<?php endif; ?> <?php if ( ! empty( $avatar_size ) ) : ?>data-avatar-size="<?php echo esc_attr( $avatar_size ); ?>"<?php endif; ?> <?php if ( ! empty( $all_data['max_item'] ) ) : ?>data-max-item="<?php echo esc_attr( $all_data['max_item'] ); ?>"<?php endif; ?>>
    <!-- Search and Filter Controls -->
    <?php 
    // Check if either search or sorting is enabled
    $show_header = ! empty( $enable_search ) || ! empty( $enable_frontend_sorting );
    
    if ( $show_header ) : ?>
        <div class="wpuf-user-directory-header !wpuf-flex !wpuf-justify-between !wpuf-items-center !wpuf-my-8 !wpuf-gap-4">

            <!-- Search Field -->
            <?php
                if ( ! empty( $enable_search ) ) {
                    include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/search-field.php';
                }
            ?>
            
            <!-- Sort and Filter Fields -->
            <?php
                // Include the sorting and filter controls if frontend sorting is enabled
                if ( ! empty( $enable_frontend_sorting ) ) {
                    $layout = ! empty( $directory_layout ) ? $directory_layout : 'layout-3';
                    include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/sort-field.php';
                }
            ?>
            
        </div>
    <?php endif; ?>
    <div class="wpuf-ud-list wpuf-ud-list-layout-3 wpuf-flow-root">
        <?php if ( ! empty( $users ) ) { ?>
            <div>
                <ul role="list" class="!wpuf-mx-auto !wpuf-grid !wpuf-max-w-2xl !wpuf-grid-cols-1 !wpuf-gap-x-6 !wpuf-gap-y-6 sm:!wpuf-grid-cols-2 lg:!wpuf-mx-0 lg:!wpuf-max-w-none lg:!wpuf-grid-cols-3">
                    <?php
                        $row = WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/row-3.php';

                        if ( file_exists( $row ) ) {
                            foreach ( $users as $user ) {
                                include $row;
                            }
                        }
                    ?>
                </ul>
            </div>
        <?php } else { ?>
            <div class="wpuf-no-users-container !wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-min-h-[400px]">
                <div class="wpuf-no-users-found !wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-text-center">
                    <div class="!wpuf-bg-gray-100 !wpuf-rounded-full !wpuf-p-4 !wpuf-mb-4">
                        <svg class="!wpuf-w-12 !wpuf-h-12 !wpuf-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="!wpuf-text-base !wpuf-font-semibold !wpuf-text-gray-900 !wpuf-mb-2">
                        <?php esc_html_e('No users found matching your search criteria.', 'wp-user-frontend'); ?>
                    </h3>
                    <p class="!wpuf-text-sm !wpuf-text-gray-500">
                        <?php esc_html_e('Try adjusting your search or filter to find what you\'re looking for.', 'wp-user-frontend'); ?>
                    </p>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="wpuf-ud-pagination">
        <?php
            // Use different pagination template based on context
            // Check if this is a shortcode context (not a block context)
            $is_shortcode = empty( $block_id ) || strpos( $block_id, 'shortcode_' ) === 0;
            
            if ( $is_shortcode ) {
                // Use shortcode-specific pagination template
                $layout = ! empty( $directory_layout ) ? $directory_layout : 'layout-3';
                
                // Prepare query args for pagination links
                $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $parsed_url = wp_parse_url( $current_url );
                $base_url = $parsed_url['path'] ?? '';
                
                $query_args = [];
                if ( ! empty( $parsed_url['query'] ) ) {
                    parse_str( $parsed_url['query'], $query_args );
                }
                
                // Ensure directory_layout is included in pagination links
                $query_args['directory_layout'] = $layout;
                unset( $query_args['page'] ); // Will be set per link
                
                include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/pagination-shortcode.php';
            } else {
                // Use default block pagination template
                $layout = ! empty( $directory_layout ) ? $directory_layout : 'layout-3';
                include WPUF_UD_FREE_TEMPLATES . '/directory/template-parts/pagination-shortcode.php';
            }
        ?>
    </div>
</div>
