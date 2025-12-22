<?php
/**
 * User Profile Layout 2 - Cover Image with Centered Profile
 *
 * @since 4.2.0
 *
 * Available variables:
 * @var array $template_data Complete data including user info and settings
 * @var array $user User data array
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get centralized profile data
$profile_data = wpuf_ud_get_profile_data( $template_data['user'], $template_data, 'layout-2' );
$user = $profile_data['user'];
$config = $profile_data['template_config'];
$user_meta = $profile_data['user_meta'];
$contact_info = $profile_data['contact_info'];
$social_media = $profile_data['social_media'];
$navigation = $profile_data['navigation'];
$tab_config = $profile_data['tab_config'];

// Legacy variables for backward compatibility
$show_avatar = $config['show_avatar'];
$enable_tabs = $config['enable_tabs'];
$default_tabs = $config['default_tabs'];
$default_active_tab = $config['default_active_tab'];
$avatar_size = $config['avatar_size'];
$custom_tab_labels = $config['custom_tab_labels'];

?>

<div class="wpuf-user-profile wpuf-profile-layout-2">
    <!-- Back Button -->
    <div class="!wpuf-max-w-6xl !wpuf-mx-auto !wpuf-px-4 !wpuf-mb-8">
        <button onclick="wpuf_ud_goBack()" class="wpuf-back-button !wpuf-inline-flex !wpuf-items-center !wpuf-px-4 !wpuf-py-2 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-600 !wpuf-bg-white !wpuf-border !wpuf-border-gray-300 !wpuf-rounded-lg hover:!wpuf-bg-gray-50 hover:!wpuf-text-emerald-600 !wpuf-transition-colors !wpuf-shadow-sm">
            <svg class="!wpuf-w-4 !wpuf-h-4 !wpuf-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <?php esc_html_e( 'Back to Directory', 'wp-user-frontend' ); ?>
        </button>
    </div>

    <!-- Cover Photo Section -->
    <div class="!wpuf-max-w-6xl !wpuf-mx-auto !wpuf-px-4 !wpuf-mb-8">
        <div class="!wpuf-w-full !wpuf-h-[200px] !wpuf-rounded-lg !wpuf-overflow-hidden">
            <div class="!wpuf-w-full !wpuf-h-full !wpuf-bg-gray-200 !wpuf-border !wpuf-border-gray-300"></div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="!wpuf-max-w-6xl !wpuf-mx-auto !wpuf-px-4 wpuf-profile-header-overlap">
        <!-- Profile Header Card -->
        <div class="!wpuf-bg-transparent !wpuf-rounded-lg !wpuf-mb-12">
            <!-- Avatar and Basic Info -->
            <div class="!wpuf-flex !wpuf-flex-col !wpuf-items-center !wpuf-text-center">
                <?php if ( $show_avatar ) : ?>
                    <div class="!wpuf-relative !wpuf-mb-5">
                        <div class="!wpuf-w-32 !wpuf-h-32 !wpuf-rounded-full !wpuf-border-4 !wpuf-border-white !wpuf-overflow-hidden !wpuf-bg-white">
                            <?php
                            $size = 128;
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/user-avatar.php';
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

                <h1 class="!wpuf-text-2xl !wpuf-font-bold !wpuf-text-gray-900 !wpuf-mb-4 !wpuf-leading-[27.6px] !wpuf-tracking-[-0.72px]">
                    <?php echo esc_html( $user_meta['display_name'] ); ?>
                </h1>

                <!-- Contact Info -->
                <div class="!wpuf-flex !wpuf-flex-wrap !wpuf-gap-8 !wpuf-justify-center !wpuf-mb-8 !wpuf-py-4 !wpuf-border-t !wpuf-border-b !wpuf-border-gray-200">
                    <?php foreach ( $contact_info as $contact_key => $contact_item ) : ?>
                    <div class="!wpuf-flex !wpuf-items-center !wpuf-gap-3">
                        <div class="!wpuf-relative">
                            <?php echo $contact_item['icon']; ?>
                        </div>
                        <div class="!wpuf-flex !wpuf-flex-col">
                            <span class="!wpuf-text-xs !wpuf-text-gray-500 !wpuf-mb-0.5"><?php echo esc_html( $contact_item['label'] ); ?></span>
                            <?php if ( $contact_key === 'website' ) : ?>
                                <a href="<?php echo esc_url( $contact_item['value'] ); ?>" target="_blank" class="!wpuf-text-sm !wpuf-text-gray-900 !wpuf-font-medium hover:!wpuf-text-emerald-600">
                                    <?php echo esc_html( $contact_item['display_value'] ); ?>
                                </a>
                            <?php elseif ( $contact_key === 'email' ) : ?>
                                <a href="mailto:<?php echo esc_attr( $contact_item['value'] ); ?>" class="!wpuf-text-sm !wpuf-text-gray-900 !wpuf-font-medium hover:!wpuf-text-emerald-600">
                                    <?php echo esc_html( $contact_item['display_value'] ); ?>
                                </a>
                            <?php else : ?>
                                <span class="!wpuf-text-sm !wpuf-text-gray-900 !wpuf-font-medium"><?php echo esc_html( $contact_item['display_value'] ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Message and Edit Profile Buttons -->
                <div class="!wpuf-flex !wpuf-items-center !wpuf-gap-2.5">
                    <?php
                    $current_user_id = get_current_user_id();
                    // Show message button only if Private Message module is active and user is not viewing their own profile
                    if ( defined( 'WPUF_PM_DIR' ) && $current_user_id && $current_user_id !== $user->ID ) :
                        // Get account page link for private messaging
                        $account_page_id = wpuf_get_option( 'account_page', 'wpuf_my_account', false );
                        if ( $account_page_id ) :
                            $account_page_link = get_page_link( $account_page_id );
                            $private_message_link = $account_page_link . '?section=message#/user/' . $user->ID;
                    ?>
                    <a href="<?php echo esc_url( $private_message_link ); ?>" target="_blank" class="!wpuf-h-11 !wpuf-w-11 !wpuf-bg-emerald-600 !wpuf-text-white !wpuf-rounded-lg hover:!wpuf-bg-emerald-700 !wpuf-transition-colors !wpuf-flex !wpuf-items-center !wpuf-justify-center !wpuf-no-underline">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.625 9.75C8.625 9.95711 8.45711 10.125 8.25 10.125C8.04289 10.125 7.875 9.95711 7.875 9.75C7.875 9.54289 8.04289 9.375 8.25 9.375C8.45711 9.375 8.625 9.54289 8.625 9.75ZM8.625 9.75H8.25M12.375 9.75C12.375 9.95711 12.2071 10.125 12 10.125C11.7929 10.125 11.625 9.95711 11.625 9.75C11.625 9.54289 11.7929 9.375 12 9.375C12.2071 9.375 12.375 9.54289 12.375 9.75ZM12.375 9.75H12M16.125 9.75C16.125 9.95711 15.9571 10.125 15.75 10.125C15.5429 10.125 15.375 9.95711 15.375 9.75C15.375 9.54289 15.5429 9.375 15.75 9.375C15.9571 9.375 16.125 9.54289 16.125 9.75ZM16.125 9.75H15.75M2.25 12.7593C2.25 14.3604 3.37341 15.754 4.95746 15.987C6.04357 16.1467 7.14151 16.27 8.25 16.3556V21L12.4335 16.8165C12.6402 16.6098 12.9193 16.4923 13.2116 16.485C15.1872 16.4361 17.1331 16.2678 19.0425 15.9871C20.6266 15.7542 21.75 14.3606 21.75 12.7595V6.74056C21.75 5.13946 20.6266 3.74583 19.0425 3.51293C16.744 3.17501 14.3926 3 12.0003 3C9.60776 3 7.25612 3.17504 4.95747 3.51302C3.37342 3.74593 2.25 5.13956 2.25 6.74064V12.7593Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <?php
                        endif;
                    endif;
                    ?>
                    <?php if ( $current_user_id && $current_user_id === $user->ID ) :
                        // Get account page link for edit profile
                        $account_page_id = wpuf_get_option( 'account_page', 'wpuf_my_account', false );
                        if ( $account_page_id ) :
                            $edit_profile_link = get_page_link( $account_page_id ) . '?section=edit-profile';
                    ?>
                    <a href="<?php echo esc_url( $edit_profile_link ); ?>" class="!wpuf-inline-block !wpuf-h-11 !wpuf-py-2.5 !wpuf-px-[30px] !wpuf-bg-emerald-600 !wpuf-text-white !wpuf-rounded-lg hover:!wpuf-bg-emerald-700 !wpuf-transition-colors !wpuf-font-medium !wpuf-text-sm !wpuf-no-underline !wpuf-leading-[21px]">
                        <?php esc_html_e( 'Edit Profile', 'wp-user-frontend' ); ?>
                    </a>
                    <?php
                        endif;
                    endif;
                    ?>
                </div>
            </div>

            <!-- Bio/Description -->
            <?php
            $user_bio = get_user_meta( $user->ID, 'description', true );
            if ( $user_bio ) :
            ?>
            <div class="!wpuf-mt-8 !wpuf-text-center">
                <?php
                $word_count = str_word_count( $user_bio );
                $needs_toggle = $word_count > 150;
                $bio_id = 'bio-layout2-' . $user->ID . '-' . uniqid();
                ?>

                <?php if ( $needs_toggle ) : ?>
                    <p class="!wpuf-text-gray-400 !wpuf-text-sm !wpuf-leading-relaxed">
                        <span id="<?php echo esc_attr( $bio_id . '-excerpt' ); ?>">
                            <?php echo esc_html( wp_trim_words( $user_bio, 150, '' ) ); ?>...
                        </span>
                        <span id="<?php echo esc_attr( $bio_id . '-full' ); ?>" style="display: none;">
                            <?php echo esc_html( $user_bio ); ?>
                        </span>
                        <button type="button"
                                onclick="wpuf_toggleBio_layout2('<?php echo esc_js( $bio_id ); ?>')"
                                id="<?php echo esc_attr( $bio_id . '-btn' ); ?>"
                                class="!wpuf-text-sm !wpuf-font-medium !wpuf-text-emerald-600 hover:!wpuf-text-emerald-700 !wpuf-bg-transparent !wpuf-border-0 !wpuf-p-0 !wpuf-cursor-pointer !wpuf-ml-1">
                            <?php esc_html_e( 'Show More', 'wp-user-frontend' ); ?>
                        </button>
                        <button type="button"
                                onclick="wpuf_toggleBio_layout2('<?php echo esc_js( $bio_id ); ?>')"
                                id="<?php echo esc_attr( $bio_id . '-btn-less' ); ?>"
                                style="display: none;"
                                class="!wpuf-text-sm !wpuf-font-medium !wpuf-text-emerald-600 hover:!wpuf-text-emerald-700 !wpuf-bg-transparent !wpuf-border-0 !wpuf-p-0 !wpuf-cursor-pointer !wpuf-ml-1">
                            <?php esc_html_e( 'Show Less', 'wp-user-frontend' ); ?>
                        </button>
                    </p>
                <?php else : ?>
                    <p class="!wpuf-text-gray-400 !wpuf-text-sm !wpuf-leading-relaxed">
                        <?php echo esc_html( $user_bio ); ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Tab Navigation -->
        <?php if ( $enable_tabs && ! empty( $default_tabs ) ) : ?>
        <div class="!wpuf-bg-transparent !wpuf-rounded-lg !wpuf-mb-12">
            <div class="!wpuf-border-t !wpuf-border-gray-200"></div>
            <nav class="!wpuf-flex !wpuf-justify-center !wpuf-border-b !wpuf-border-gray-200">
                <?php foreach ( $default_tabs as $tab ) : ?>
                    <?php
                    // Skip activity tab if User Activity module is not active
                    if ( $tab === 'activity' && ! class_exists( 'WPUF_User_Activity' ) ) {
                        continue;
                    }

                    if ( in_array( $tab, [ 'about', 'posts', 'comments', 'file', 'message', 'activity' ] ) ) :
                    ?>
                        <button class="wpuf-tab-button-2 <?php echo $tab === $default_active_tab ? 'active' : ''; ?> !wpuf-px-6 !wpuf-py-4 !wpuf-text-sm !wpuf-font-medium !wpuf-text-gray-500 hover:!wpuf-text-emerald-600 !wpuf-transition-colors !wpuf-relative hover:!wpuf-bg-transparent active:!wpuf-bg-transparent !wpuf-bg-transparent"
                                data-tab="<?php echo esc_attr( $tab ); ?>">
                            <?php
echo esc_html( wpuf_ud_get_tab_label( $tab, $profile_data ) );
                            ?>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="!wpuf-bg-transparent !wpuf-rounded-lg">
            <div class="wpuf-tab-content-2">
                <!-- About Tab -->
                <div class="wpuf-tab-content-about" style="<?php echo $default_active_tab !== 'about' ? 'display: none;' : ''; ?>">
                    <?php
                    if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/about-2.php' ) ) {
                        include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/about-2.php';
                    } else {
                        // Fallback to about-1 if about-2 doesn't exist
                        if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/about-1.php' ) ) {
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/about-1.php';
                        }
                    }
                    ?>
                </div>

                <!-- Posts Tab -->
                <?php if ( in_array( 'posts', $default_tabs ) ) : ?>
                    <div class="wpuf-tab-content-posts" style="<?php echo $default_active_tab !== 'posts' ? 'display: none;' : ''; ?>">
                        <?php
                        if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/posts-2.php' ) ) {
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/posts-2.php';
                        } else {
                            // Fallback to posts-1
                            if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/posts-1.php' ) ) {
                                include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/posts-1.php';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Comments Tab -->
                <?php if ( in_array( 'comments', $default_tabs ) ) : ?>
                    <div class="wpuf-tab-content-comments" style="<?php echo $default_active_tab !== 'comments' ? 'display: none;' : ''; ?>">
                        <?php
                        if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/comments-2.php' ) ) {
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/comments-2.php';
                        } else {
                            // Fallback to comments-1
                            if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/comments-1.php' ) ) {
                                include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/comments-1.php';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- File Tab -->
                <?php if ( in_array( 'file', $default_tabs ) ) : ?>
                    <div class="wpuf-tab-content-file" style="<?php echo $default_active_tab !== 'file' ? 'display: none;' : ''; ?>">
                        <?php
                        $tab_title = __( 'Files', 'wp-user-frontend' );
                        if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/file-2.php' ) ) {
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/file-2.php';
                        } elseif ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/file-1.php' ) ) {
                            // Fallback to file-1
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/file-1.php';
                        } elseif ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/file.php' ) ) {
                            // Fallback to old template
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/file.php';
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Message Tab -->
                <?php if ( in_array( 'message', $default_tabs ) ) : ?>
                    <div class="wpuf-tab-content-message" style="<?php echo $default_active_tab !== 'message' ? 'display: none;' : ''; ?>">
                        <div class="!wpuf-text-center !wpuf-py-8">
                            <p class="!wpuf-text-gray-500"><?php esc_html_e( 'Message functionality coming soon.', 'wp-user-frontend' ); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Activity Tab -->
                <?php if ( in_array( 'activity', $default_tabs ) && class_exists( 'WPUF_User_Activity' ) ) : ?>
                    <div class="wpuf-tab-content-activity" style="<?php echo $default_active_tab !== 'activity' ? 'display: none;' : ''; ?>">
                        <?php
                        if ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/activity-2.php' ) ) {
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/activity-2.php';
                        } elseif ( file_exists( WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/activity.php' ) ) {
                            // Fallback to general activity template
                            include WPUF_UD_FREE_TEMPLATES . '/profile/template-parts/activity.php';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab Switching JavaScript -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Function to get URL parameter
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Function to build clean URL for tab switching
    function buildCleanTabUrl(targetTab) {
        var currentUrl = new URL(window.location.href);

        // Define ALL tab pagination parameters that should be cleaned
        var allTabPaginationParams = ['posts_page', 'comments_page', 'cpage'];

        // Define pagination parameters for each tab
        var tabPaginationParams = {
            'posts': ['posts_page'],
            'comments': ['comments_page'],
            'activity': ['cpage'],
            'about': [],
            'file': [],
            'message': []
        };

        // Get directory-related parameters to preserve (these persist across all tabs)
        var directoryParams = ['dir_page', 'orderby', 'order', 'search'];

        // Start with a clean URL (path only)
        var cleanUrl = new URL(currentUrl.origin + currentUrl.pathname);

        // Always add the target tab
        cleanUrl.searchParams.set('tab', targetTab);

        // Preserve directory-related parameters across all tabs
        directoryParams.forEach(function(param) {
            var value = currentUrl.searchParams.get(param);
            if (value) {
                cleanUrl.searchParams.set(param, value);
            }
        });

        // ONLY preserve pagination params relevant to the target tab
        // This explicitly excludes other tabs' pagination parameters
        if (tabPaginationParams[targetTab] && tabPaginationParams[targetTab].length > 0) {
            tabPaginationParams[targetTab].forEach(function(param) {
                var value = currentUrl.searchParams.get(param);
                if (value) {
                    cleanUrl.searchParams.set(param, value);
                }
            });
        }
        // Note: We don't add any other pagination parameters, so they're automatically excluded

        return cleanUrl.toString();
    }

    // IMPORTANT: Always prioritize tab parameter from URL over any other detection
    var urlTab = getUrlParameter('tab');

    // If no explicit tab parameter, check for pagination parameters that indicate a specific tab
    if (!urlTab) {
        if (getUrlParameter('cpage')) {
            urlTab = 'activity'; // cpage is used by activity pagination
        } else if (getUrlParameter('posts_page')) {
            urlTab = 'posts';
        } else if (getUrlParameter('comments_page')) {
            urlTab = 'comments';
        }
    }

    if (urlTab) {
        // Activate the tab from URL
        var $targetTabButton = $('.wpuf-tab-button-2[data-tab="' + urlTab + '"]');
        if ($targetTabButton.length) {
            // Remove active class from all tabs
            $('.wpuf-tab-button-2').removeClass('active');

            // Add active class to target tab
            $targetTabButton.addClass('active');

            // Hide all tab content
            $('.wpuf-tab-content-2 > div').hide();

            // Show target tab content
            $('.wpuf-tab-content-' + urlTab).show();
        }
    }

    // Tab switching functionality for layout 2
    $('.wpuf-tab-button-2').on('click', function(e) {
        e.preventDefault();

        var targetTab = $(this).data('tab');
        var $tabsContainer = $(this).closest('.wpuf-user-profile');

        // Remove active class from all tab buttons
        $tabsContainer.find('.wpuf-tab-button-2').removeClass('active');

        // Add active class to clicked tab button
        $(this).addClass('active');

        // Hide all tab content
        $tabsContainer.find('.wpuf-tab-content-2 > div').hide();

        // Show target tab content
        $tabsContainer.find('.wpuf-tab-content-' + targetTab).show();

        // Update URL without reloading page
        if (history.pushState) {
            var cleanUrl = buildCleanTabUrl(targetTab);
            history.pushState({tab: targetTab}, '', cleanUrl);
        }
    });
});
</script>

<style>
.wpuf-tab-button-2 {
    position: relative;
}
.wpuf-tab-button-2:focus {
    outline: none;
}
.wpuf-tab-button-2::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background-color: transparent;
    transition: background-color 0.2s;
}
.wpuf-tab-button-2.active {
    color: #059669; /* Emerald-600 */
}
.wpuf-tab-button-2.active::after {
    background-color: #059669; /* Emerald-600 */
}
.wpuf-profile-header-overlap {
    margin-top: -80px !important;
}
</style>

<script type="text/javascript">
// Back button functionality
function wpuf_ud_goBack() {
    // Try to use browser history first to preserve pagination and other parameters
    // But skip referrer method if we have dir_page parameter (ensures clean URL reconstruction)
    var currentUrl = new URL(window.location.href);
    var hasDirPage = currentUrl.searchParams.has('dir_page');

    if (!hasDirPage && document.referrer && document.referrer.includes(window.location.hostname)) {
        var referrerUrl = new URL(document.referrer);

        // Check if referrer is the directory page
        var referrerPath = referrerUrl.pathname.replace(/\/+$/, '');
        var currentPath = currentUrl.pathname.replace(/\/[^\/]+\/?$/, '').replace(/\/+$/, '');

        // If coming from directory page, use referrer but remove tab and cpage parameters
        if (referrerPath === currentPath ||
            referrerUrl.searchParams.has('page') ||
            referrerUrl.searchParams.has('orderby') ||
            referrerUrl.searchParams.has('order') ||
            referrerUrl.searchParams.has('search')) {

            // Remove tab and activity-related parameters from referrer URL
            referrerUrl.searchParams.delete('tab');
            referrerUrl.searchParams.delete('cpage');
            window.location.href = referrerUrl.toString();
            return;
        }
    }

    // Fallback: construct directory URL preserving directory parameters
    var currentUrl = window.location.href;
    var url = new URL(currentUrl);

    // Check for dir_page parameter to restore directory pagination
    var dirPage = url.searchParams.get('dir_page');

    // Save directory-related parameters
    var preserveParams = {};
    ['orderby', 'order', 'search', 'filter'].forEach(function(param) {
        if (url.searchParams.has(param)) {
            preserveParams[param] = url.searchParams.get(param);
        }
    });

    // Remove ALL query parameters first
    url.search = '';

    // Get the base directory path by removing user identifier
    // For URLs like /new-user-directory/admin/, we want /new-user-directory/
    var pathParts = url.pathname.split('/').filter(part => part !== '');

    // Check if last part looks like a username or user ID (not 'page')
    if (pathParts.length > 0) {
        var lastPart = pathParts[pathParts.length - 1];
        // If the last part is not 'page' and not a number following 'page', it's likely a user identifier
        if (lastPart !== 'page' && !(pathParts.length > 1 && pathParts[pathParts.length - 2] === 'page')) {
            pathParts.pop();
        }
    }

    // Rebuild the base directory path
    url.pathname = '/' + pathParts.join('/') + '/';

    // If we have a dir_page parameter, add it to the path for clean URL pagination
    if (dirPage && dirPage > 1) {
        // Ensure we don't double-add /page/ if it's already there
        if (!url.pathname.includes('/page/')) {
            url.pathname = url.pathname.replace(/\/$/, '') + '/page/' + dirPage + '/';
        }
    }

    // Re-add preserved directory parameters
    Object.keys(preserveParams).forEach(function(param) {
        url.searchParams.set(param, preserveParams[param]);
    });

    // Navigate to directory listing with preserved parameters
    window.location.href = url.toString();
}

// Bio toggle for layout-2
function wpuf_toggleBio_layout2(bioId) {
    var excerpt = document.getElementById(bioId + '-excerpt');
    var full = document.getElementById(bioId + '-full');
    var showBtn = document.getElementById(bioId + '-btn');
    var hideBtn = document.getElementById(bioId + '-btn-less');

    if (full.style.display === 'none') {
        excerpt.style.display = 'none';
        full.style.display = 'inline';
        showBtn.style.display = 'none';
        hideBtn.style.display = 'inline';
    } else {
        excerpt.style.display = 'inline';
        full.style.display = 'none';
        showBtn.style.display = 'inline';
        hideBtn.style.display = 'none';
    }
}
</script>
