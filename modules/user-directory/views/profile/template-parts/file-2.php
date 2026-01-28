<?php
$user_id = $user->ID;
$tab_title = ! empty( $tab_title ) ? $tab_title : __( 'Files', 'wp-user-frontend' );

// Get private message attachment IDs to exclude them from public files
$private_message_attachment_ids = [];
global $wpdb;
$message_table = $wpdb->prefix . 'wpuf_message';
$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $message_table ) );
if ( $table_exists ) {
    $messages = $wpdb->get_results( $wpdb->prepare(
        "SELECT message FROM {$message_table} WHERE (`from` = %d OR `to` = %d)",
        $user_id, $user_id
    ) );
    
    foreach ( $messages as $message ) {
        $message_data = maybe_unserialize( $message->message );
        if ( is_array( $message_data ) && ! empty( $message_data['files'] ) && is_array( $message_data['files'] ) ) {
            $private_message_attachment_ids = array_merge( $private_message_attachment_ids, $message_data['files'] );
        }
    }
    $private_message_attachment_ids = array_unique( array_map( 'intval', $private_message_attachment_ids ) );
}

// Get user uploaded files from attachments
$all_files = get_posts([
    'post_type'      => 'attachment',
    'author'         => $user_id,
    'posts_per_page' => -1, // Get all files for grouping
    'post_status'    => 'inherit'
]);

// Filter out private message attachments
$files = [];
foreach ( $all_files as $file ) {
    if ( ! in_array( $file->ID, $private_message_attachment_ids ) ) {
        $files[] = $file;
    }
}

// Get profile size from template data or use default
$gallery_image_size = 150; // Default size
$wp_image_size = 'thumbnail'; // Default WordPress size

// Check for profile_size - Free uses profile_size consistently
$size_value = 'thumbnail'; // Default fallback
if ( ! empty( $template_data['profile_size'] ) ) {
    $size_value = $template_data['profile_size'];
} elseif ( ! empty( $template_data['settings']['profile_size'] ) ) {
    $size_value = $template_data['settings']['profile_size'];
} elseif ( isset( $settings ) && ! empty( $settings['profile_size'] ) ) {
    $size_value = $settings['profile_size'];
}

if ( ! empty( $size_value ) ) {
    // Function to get actual size from WordPress image size name or number
    if ( ! function_exists( 'wpuf_get_image_size_dimensions_file2' ) ) {
        function wpuf_get_image_size_dimensions_file2( $size ) {
            global $_wp_additional_image_sizes;
            
            // If it's already a number, use it directly
            if ( is_numeric( $size ) ) {
                return array( 'size' => intval( $size ), 'wp_size' => 'custom' );
            }
            
            // Default WordPress sizes
            $default_sizes = array(
                'thumbnail' => array( 'width' => get_option( 'thumbnail_size_w', 150 ), 'height' => get_option( 'thumbnail_size_h', 150 ) ),
                'medium' => array( 'width' => get_option( 'medium_size_w', 300 ), 'height' => get_option( 'medium_size_h', 300 ) ),
                'medium_large' => array( 'width' => get_option( 'medium_large_size_w', 768 ), 'height' => get_option( 'medium_large_size_h', 0 ) ),
                'large' => array( 'width' => get_option( 'large_size_w', 1024 ), 'height' => get_option( 'large_size_h', 1024 ) ),
                'full' => array( 'width' => 1536, 'height' => 1536 ) // Fallback for full size
            );
            
            // Check default sizes first
            if ( isset( $default_sizes[ $size ] ) ) {
                $width = $default_sizes[ $size ]['width'];
                return array( 'size' => $width > 0 ? $width : 150, 'wp_size' => $size );
            }
            
            // Check additional custom sizes
            if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
                $width = $_wp_additional_image_sizes[ $size ]['width'];
                return array( 'size' => $width > 0 ? $width : 150, 'wp_size' => $size );
            }
            
            // Fallback to thumbnail if size not found
            return array( 'size' => get_option( 'thumbnail_size_w', 150 ), 'wp_size' => 'thumbnail' );
        }
    }
    
    $size_info = wpuf_get_image_size_dimensions_file2( $size_value );
    $gallery_image_size = $size_info['size'];
    $wp_image_size = $size_info['wp_size'];
}

/**
 * Filters the returned current gallery image width for user profile file tab section
 *
 * @since 3.4.11
 *
 * @param string $gallery_image_size The current image width
 */
$gallery_image_size = apply_filters( 'wpuf_profile_gallery_image_size', $gallery_image_size );

// Group files by type
$grouped_files = [
    'images' => [],
    'documents' => [],
    'videos' => [],
    'audio' => [],
    'archives' => [],
    'others' => []
];

foreach ( $files as $file ) {
    $file_type = get_post_mime_type( $file->ID );
    
    if ( strpos( $file_type, 'image/' ) === 0 ) {
        $grouped_files['images'][] = $file;
    } elseif ( in_array( $file_type, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'text/plain'] ) ) {
        $grouped_files['documents'][] = $file;
    } elseif ( strpos( $file_type, 'video/' ) === 0 ) {
        $grouped_files['videos'][] = $file;
    } elseif ( strpos( $file_type, 'audio/' ) === 0 ) {
        $grouped_files['audio'][] = $file;
    } elseif ( in_array( $file_type, ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar', 'application/gzip'] ) ) {
        $grouped_files['archives'][] = $file;
    } else {
        $grouped_files['others'][] = $file;
    }
}

// Remove empty groups
$grouped_files = array_filter( $grouped_files );

// File type labels
$type_labels = [
    'images' => __( 'Images', 'wp-user-frontend' ),
    'documents' => __( 'Documents', 'wp-user-frontend' ),
    'videos' => __( 'Videos', 'wp-user-frontend' ),
    'audio' => __( 'Audio', 'wp-user-frontend' ),
    'archives' => __( 'Archives', 'wp-user-frontend' ),
    'others' => __( 'Others', 'wp-user-frontend' )
];

// File type icons
$type_icons = [
    'images' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="!wpuf-w-8 !wpuf-h-8" fill="currentColor"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32zm64 80a48 48 0 1 1 0 96 48 48 0 1 1 0-96zM272 224c8.4 0 16.1 4.4 20.5 11.5l88 144c4.5 7.4 4.7 16.7 .5 24.3S368.7 416 360 416L88 416c-8.9 0-17.2-5-21.3-12.9s-3.5-17.5 1.6-24.8l56-80c4.5-6.4 11.8-10.2 19.7-10.2s15.2 3.8 19.7 10.2l26.4 37.8 61.4-100.5c4.4-7.1 12.1-11.5 20.5-11.5z"/></svg>',
    'documents' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="!wpuf-w-8 !wpuf-h-8" fill="currentColor"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-277.5c0-17-6.7-33.3-18.7-45.3L258.7 18.7C246.7 6.7 230.5 0 213.5 0L64 0zM325.5 176L232 176c-13.3 0-24-10.7-24-24L208 58.5 325.5 176z"/></svg>',
    'videos' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="!wpuf-w-8 !wpuf-h-8" fill="currentColor"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M0 256a256 256 0 1 1 512 0 256 256 0 1 1 -512 0zM188.3 147.1c-7.6 4.2-12.3 12.3-12.3 20.9l0 176c0 8.7 4.7 16.7 12.3 20.9s16.8 4.1 24.3-.5l144-88c7.1-4.4 11.5-12.1 11.5-20.5s-4.4-16.1-11.5-20.5l-144-88c-7.4-4.5-16.7-4.7-24.3-.5z"/></svg>',
    'audio' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="!wpuf-w-8 !wpuf-h-8" fill="currentColor"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M533.6 32.5c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C557.5 113.8 592 180.8 592 256s-34.5 142.2-88.7 186.3c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5C598.5 426.7 640 346.2 640 256S598.5 85.2 533.6 32.5zM473.1 107c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C475.3 170.7 496 210.9 496 256s-20.7 85.3-53.2 111.8c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5c43.2-35.2 70.9-88.9 70.9-149s-27.7-113.8-70.9-149zm-60.5 74.5c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C393.1 227.6 400 241 400 256s-6.9 28.4-17.7 37.3c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5C434.1 312.9 448 286.1 448 256s-13.9-56.9-35.4-74.5zM80 352l48 0 134.1 119.2c6.4 5.7 14.6 8.8 23.1 8.8 19.2 0 34.8-15.6 34.8-34.8l0-378.4c0-19.2-15.6-34.8-34.8-34.8-8.5 0-16.7 3.1-23.1 8.8L128 160 80 160c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48z"/></svg>',
    'archives' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="!wpuf-w-8 !wpuf-h-8" fill="currentColor"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M56 225.6L32.4 296.2 32.4 96c0-35.3 28.7-64 64-64l138.7 0c13.8 0 27.3 4.5 38.4 12.8l38.4 28.8c5.5 4.2 12.3 6.4 19.2 6.4l117.3 0c35.3 0 64 28.7 64 64l0 16-365.4 0c-41.3 0-78 26.4-91.1 65.6zM477.8 448L99 448c-32.8 0-55.9-32.1-45.5-63.2l48-144C108 221.2 126.4 208 147 208l378.8 0c32.8 0 55.9 32.1 45.5 63.2l-48 144c-6.5 19.6-24.9 32.8-45.5 32.8z"/></svg>',
    'others' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="!wpuf-w-8 !wpuf-h-8" fill="currentColor"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M56 225.6L32.4 296.2 32.4 96c0-35.3 28.7-64 64-64l138.7 0c13.8 0 27.3 4.5 38.4 12.8l38.4 28.8c5.5 4.2 12.3 6.4 19.2 6.4l117.3 0c35.3 0 64 28.7 64 64l0 16-365.4 0c-41.3 0-78 26.4-91.1 65.6zM477.8 448L99 448c-32.8 0-55.9-32.1-45.5-63.2l48-144C108 221.2 126.4 208 147 208l378.8 0c32.8 0 55.9 32.1 45.5 63.2l-48 144c-6.5 19.6-24.9 32.8-45.5 32.8z"/></svg>'
];
?>

<div class="wpuf-profile-section wpuf-files-section-2">
    <h1 class="profile-section-heading !wpuf-text-2xl !wpuf-font-bold !wpuf-text-gray-800 !wpuf-mb-6">
        <?php echo esc_html( $tab_title ); ?>
    </h1>
    
    <?php if ( ! empty( $grouped_files ) ) : ?>
        
        <!-- File Type Tabs -->
        <div class="wpuf-file-tabs !wpuf-mb-6">
            <div class="wpuf-tab-nav !wpuf-flex !wpuf-flex-wrap !wpuf-gap-3 !wpuf-border-b !wpuf-border-gray-200">
                <?php 
                $first_tab = true;
                foreach ( $grouped_files as $type => $type_files ) : 
                    $count = count( $type_files );
                ?>
                    <button class="wpuf-file-tab-btn-2 !wpuf-flex !wpuf-items-center !wpuf-gap-2 !wpuf-px-3 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-border-b-2 !wpuf-transition-all !wpuf-bg-transparent !wpuf-border-0 !wpuf-border-b-2 !wpuf-outline-none !wpuf-cursor-pointer <?php echo $first_tab ? '!wpuf-border-b-green-500 !wpuf-text-green-600' : '!wpuf-border-b-transparent !wpuf-text-green-500 hover:!wpuf-text-green-700 hover:!wpuf-border-b-green-300'; ?>" 
                            data-tab="<?php echo esc_attr( $type ); ?>"
                            <?php echo $first_tab ? 'data-active="true"' : ''; ?>
                            style="background: transparent !important; border-top: none !important; border-left: none !important; border-right: none !important;">
                        <span class="!wpuf-flex-shrink-0"><?php echo str_replace('!wpuf-w-8 !wpuf-h-8', '!wpuf-w-4 !wpuf-h-4', $type_icons[$type]); ?></span>
                        <span class="!wpuf-flex-shrink-0"><?php echo esc_html( $type_labels[$type] ); ?></span>
                        <span class="!wpuf-px-1.5 !wpuf-py-0.5 !wpuf-bg-green-100 !wpuf-rounded-full !wpuf-text-xs !wpuf-leading-none">
                            <?php echo esc_html( $count ); ?>
                        </span>
                    </button>
                <?php 
                    $first_tab = false;
                endforeach; 
                ?>
            </div>
        </div>
        
        <!-- File Groups -->
        <?php 
        $first_group = true;
        foreach ( $grouped_files as $type => $type_files ) : 
        ?>
            <div class="wpuf-file-group-2" data-type="<?php echo esc_attr( $type ); ?>" 
                 style="<?php echo ! $first_group ? 'display: none;' : ''; ?>">
                
                <div class="files-grid !wpuf-grid !wpuf-grid-cols-2 sm:!wpuf-grid-cols-3 md:!wpuf-grid-cols-4 lg:!wpuf-grid-cols-5 !wpuf-gap-6">
                    <?php foreach ( $type_files as $file ) : ?>
                        <?php
                        $file_url = wp_get_attachment_url( $file->ID );
                        $file_type = get_post_mime_type( $file->ID );
                        $file_extension = strtoupper( pathinfo( $file_url, PATHINFO_EXTENSION ) );
                        $is_image = strpos( $file_type, 'image/' ) === 0;
                        $is_pdf = $file_type === 'application/pdf';
                        
                        // Get appropriate icon/label for the file type
                        $display_icon = $type_icons[$type];
                        $display_label = $file_extension ?: strtoupper( $type );
                        
                        if ( $is_image ) {
                            $display_label = 'IMG';
                        } elseif ( $is_pdf ) {
                            $display_label = 'PDF';
                        }
                        ?>
                        
                        <div class="file-item !wpuf-relative !wpuf-group">
                            <a href="<?php echo esc_url( $file_url ); ?>" target="_blank" class="!wpuf-block">
                                <?php if ( $is_image ) : ?>
                                    <?php
                                    // For images, use the calculated WordPress image size
                                    $image_thumb = wp_get_attachment_image_src( $file->ID, $wp_image_size );
                                    ?>
                                    <div class="file-preview !wpuf-w-full !wpuf-rounded-xl !wpuf-overflow-hidden !wpuf-transition-all !wpuf-duration-300 !wpuf-shadow-md hover:!wpuf-shadow-lg group-hover:!wpuf-scale-105">
                                        <?php if ( $image_thumb ) : ?>
                                            <img src="<?php echo esc_url( $image_thumb[0] ); ?>" alt="<?php echo esc_attr( $file->post_title ); ?>" class="!wpuf-w-full !wpuf-h-auto !wpuf-object-cover">
                                        <?php else : ?>
                                            <!-- Fallback to icon if image thumb fails -->
                                            <div class="!wpuf-w-full !wpuf-h-28 !wpuf-bg-green-700 !wpuf-rounded-lg !wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-text-white !wpuf-font-bold !wpuf-text-sm hover:!wpuf-bg-green-500">
                                                <div class="!wpuf-text-2xl !wpuf-mb-1"><?php echo $display_icon; ?></div>
                                                <span><?php echo esc_html( $display_label ); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else : ?>
                                    <!-- For non-images, show icon as before -->
                                    <div class="file-preview !wpuf-w-full !wpuf-h-28 !wpuf-bg-green-700 !wpuf-rounded-lg !wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-text-white !wpuf-font-bold !wpuf-text-sm hover:!wpuf-bg-green-500 !wpuf-transition-all !wpuf-duration-200 group-hover:!wpuf-scale-105">
                                        <div class="!wpuf-text-2xl !wpuf-mb-1"><?php echo $type_icons[$type]; ?></div>
                                        <span><?php echo esc_html( $display_label ); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="file-info !wpuf-mt-3">
                                    <div class="file-name !wpuf-text-sm !wpuf-text-gray-700 !wpuf-text-center !wpuf-truncate !wpuf-font-medium">
                                        <?php echo esc_html( $file->post_title ?: pathinfo( $file_url, PATHINFO_FILENAME ) ); ?>
                                    </div>
                                    <div class="file-size !wpuf-text-xs !wpuf-text-gray-400 !wpuf-text-center !wpuf-mt-1">
                                        <?php
                                        $file_path = get_attached_file( $file->ID );
                                        if ( $file_path && file_exists( $file_path ) ) {
                                            $file_size = size_format( filesize( $file_path ) );
                                            echo esc_html( $file_size );
                                        }
                                        ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php 
            $first_group = false;
        endforeach; 
        ?>
        
    <?php else : ?>
        <div class="!wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-justify-center !wpuf-py-20 !wpuf-bg-gray-50 !wpuf-rounded-xl">
            <div class="!wpuf-mb-4">
                <svg class="!wpuf-w-24 !wpuf-h-24 !wpuf-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <p class="!wpuf-text-xl !wpuf-font-medium !wpuf-text-gray-900 !wpuf-mb-2"><?php esc_html_e( 'No files uploaded yet', 'wp-user-frontend' ); ?></p>
            <p class="!wpuf-text-base !wpuf-text-gray-500"><?php esc_html_e( 'Files will appear here once uploaded.', 'wp-user-frontend' ); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php if ( ! empty( $grouped_files ) ) : ?>
<script type="text/javascript">
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.wpuf-file-tab-btn-2');
        const fileGroups = document.querySelectorAll('.wpuf-file-group-2');
        
        tabButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetType = this.getAttribute('data-tab');
                
                // Update button states
                tabButtons.forEach(function(btn) {
                    btn.classList.remove('!wpuf-border-b-green-500', '!wpuf-text-green-600');
                    btn.classList.add('!wpuf-border-b-transparent', '!wpuf-text-green-500');
                    btn.removeAttribute('data-active');
                });
                
                this.classList.remove('!wpuf-border-b-transparent', '!wpuf-text-green-500');
                this.classList.add('!wpuf-border-b-green-500', '!wpuf-text-green-600');
                this.setAttribute('data-active', 'true');
                
                // Show/hide file groups
                fileGroups.forEach(function(group) {
                    if (group.getAttribute('data-type') === targetType) {
                        group.style.display = 'block';
                    } else {
                        group.style.display = 'none';
                    }
                });
            });
        });
    });
})();
</script>
<?php endif; ?>