<?php

if ( !class_exists( 'WeDevs\Wpuf\Free\Pro_Prompt' ) ) {
    function wpuf_get_upgrade_to_pro_popup_url() {
        return '#';
    }
} else {
    function wpuf_get_upgrade_to_pro_popup_url() {
        return \WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url();
    }
}

// Check AI provider configuration
if ( ! function_exists( 'wpuf_check_ai_configuration' ) ) {
function wpuf_check_ai_configuration() {
    // Get AI settings from WPUF options
    $ai_settings = get_option( 'wpuf_ai', [] );

    $ai_provider = isset( $ai_settings['ai_provider'] ) ? $ai_settings['ai_provider'] : '';
    $ai_model    = isset( $ai_settings['ai_model'] ) ? $ai_settings['ai_model'] : '';

    // Check for provider-specific API key
    $provider_key_field = $ai_provider . '_api_key';
    $ai_api_key = isset( $ai_settings[$provider_key_field] ) ? $ai_settings[$provider_key_field] : '';

    // Check that provider, model and provider-specific API key are present
    return !empty( $ai_provider ) && !empty( $ai_api_key ) && !empty( $ai_model );
}
}

$form_type = ! empty( $form_type ) ?  $form_type : 'Post Form';

// Check if this is a post form or registration/profile form
$is_post_form = strpos( strtolower( $form_type ), 'registration' ) === false && strpos( strtolower( $form_type ), 'profile' ) === false;

// Define categories based on form type
$categories = [];
if ( strpos( strtolower( $form_type ), 'registration' ) !== false || strpos( strtolower( $form_type ), 'profile' ) !== false ) {
    // Profile/Registration form categories
    $categories = [
        'ecommerce'    => [
            'label'    => __( 'E-commerce', 'wp-user-frontend' ),
            'keywords' => [ 'vendor', 'marketplace', 'product' ],
        ],
        'membership'   => [
            'label'    => __( 'Membership', 'wp-user-frontend' ),
            'keywords' => [ 'membership' ],
        ],
        // 'community'    => [
        //     'label'    => __( 'Community', 'wp-user-frontend' ),
        //     'keywords' => [],
        // ],
        // 'associations' => [
        //     'label'    => __( 'Associations', 'wp-user-frontend' ),
        //     'keywords' => [],
        // ],
    ];
} else {
    // Post form categories
    $categories = [
        'ecommerce'    => [
            'label'    => __( 'E-commerce', 'wp-user-frontend' ),
            'keywords' => [ 'vendor', 'marketplace', 'product', 'WooCommerce','edd' ],
        ],
        'post'   => [
            'label'    => __( 'Post Form', 'wp-user-frontend' ),
            'keywords' => [ 'post', 'article', 'blog' ],
        ],
    ];
}

// Helper function to determine a template's category based on its title
if ( ! function_exists( 'wpuf_get_template_category' ) ) {
    function wpuf_get_template_category( $template_title, $categories, $form_type = 'Post Form' ) {
        $template_title_lower = strtolower( $template_title );

        foreach ( $categories as $slug => $category ) {
            if ( ! empty( $category['keywords'] ) ) {
                foreach ( $category['keywords'] as $keyword ) {
                    if ( strpos( $template_title_lower, $keyword ) !== false ) {
                        return $slug;
                    }
                }
            }
        }

        // Default category based on form type
        if ( strpos( strtolower( $form_type ), 'registration' ) !== false || strpos( strtolower( $form_type ), 'profile' ) !== false ) {
            return 'registration';
        } else {
            return 'post';
        }
    }
}

$category_counts = array_fill_keys( array_keys( $categories ), 0 );

if ( ! empty( $registry ) ) {
    foreach ( $registry as $template ) {
        $category = wpuf_get_template_category( $template->get_title(), $categories, $form_type );

        if ( isset( $category_counts[ $category ] ) ) {
            $category_counts[ $category ]++;
        }
    }
}
?>
<div class="wpuf-form-template-modal wpuf-fixed wpuf-top-0 wpuf-left-0 wpuf-w-screen wpuf-h-screen wpuf-bg-gray-100 wpuf-hidden wpuf-z-[999999]" role="dialog" aria-modal="true" aria-labelledby="template-modal-title" aria-describedby="template-modal-description" style="background-color: #F8FAFC;">
    <button
        class="wpuf-absolute wpuf-right-8 wpuf-top-4 wpuf-text-gray-400 hover:wpuf-text-gray-600 focus:wpuf-outline-none wpuf-close-btn wpuf-border wpuf-border-gray-200 wpuf-rounded-full wpuf-p-2 hover:wpuf-border-gray-300 wpuf-bg-white wpuf-z-[1000000]">
        <svg xmlns="http://www.w3.org/2000/svg" class="wpuf-h-6 wpuf-w-6" fill="none" viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    
    <div class="wpuf-relative wpuf-mx-auto wpuf-p-8 wpuf-h-full wpuf-overflow-y-auto wpuf-max-w-[1400px]">
        <div class="wpuf-max-w-full wpuf-mx-auto wpuf-relative wpuf-z-[999998]">
            <!-- Header -->
            <div class="wpuf-mb-14 wpuf-mt-10 wpuf-ml-10">
                <h1 class="wpuf-text-3xl wpuf-text-gray-900 wpuf-m-0 wpuf-p-0" id="template-modal-title">
                    <?php
                        // translators: %s is the form type (e.g., 'Post', 'Registration')
                        echo esc_html( sprintf( __( 'Select a %s Template', 'wp-user-frontend' ), $form_type ) );
                    ?>
                </h1>
                <p class="wpuf-text-base wpuf-text-gray-500 wpuf-mt-3 wpuf-p-0" id="template-modal-description">
                    <?php esc_html_e( 'Select from a pre-defined template to get started quickly, or start from a blank form to build your own from scratch', 'wp-user-frontend' ); ?>
                </p>
            </div>

            <div class="wpuf-flex wpuf-gap-12">
                <!-- Left Sidebar -->
                <div class="wpuf-w-80 wpuf-flex-shrink-0">
                    <!-- Search Box -->
                    <div class="wpuf-mb-8 wpuf-mx-10">
                        <div class="wpuf-relative wpuf-group">
                            <input
                                type="text"
                                id="template-search"
                                placeholder="<?php esc_attr_e( 'Search Templates', 'wp-user-frontend' ); ?>"
                                class="wpuf-w-full !wpuf-py-[4px] !wpuf-px-[14px] wpuf-border !wpuf-border-gray-300 wpuf-rounded-lg wpuf-text-base wpuf-bg-white wpuf-transition-all wpuf-duration-200 focus:wpuf-outline-none focus:!wpuf-border-[#10b981] focus:wpuf-ring-1  placeholder:wpuf-text-gray-400 wpuf-shadow-primary"
                            />
                            <div class="wpuf-absolute wpuf-right-4 wpuf-top-1/2 wpuf--translate-y-1/2 wpuf-pointer-events-none">
                                <svg class="wpuf-h-5 wpuf-w-5 wpuf-text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="wpuf-mx-4">
                        <ul class="wpuf-space-y-2">
                            <li>
                                <button class="wpuf-template-category wpuf-w-64 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-text-left wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-transition-all wpuf-duration-200 wpuf-bg-gray-100 wpuf-text-primary wpuf-rounded-md !wpuf-ml-6 !wpuf-mr-10 wpuf-pl-4 wpuf-pr-4" data-category="all">
                                    <span><?php esc_html_e( 'All Templates', 'wp-user-frontend' ); ?></span>
                                    <span class="wpuf-border wpuf-border-primary wpuf-text-primary wpuf-text-sm wpuf-font-semibold wpuf-px-2.5 wpuf-py-0.5 wpuf-rounded-full wpuf-ml-6">
                                        <?php
                                        // Base count: registry templates + blank form + AI form
                                        $total_count = count($registry) + 2; // +1 for blank form, +1 for AI form

                                        if (!empty($pro_templates)) {
                                            $total_count += count($pro_templates);
                                        }
                                        echo esc_html( $total_count );
                                        ?>
                                    </span>
                                </button>
                            </li>
                            <?php foreach ( $categories as $slug => $category ) : ?>
                                <li>

                                    <button class="wpuf-template-category wpuf-w-64 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-text-left wpuf-py-2 wpuf-text-sm wpuf-transition-all wpuf-duration-200 wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100 wpuf-rounded-md !wpuf-ml-6 !wpuf-mr-10 wpuf-pl-4 wpuf-pr-4" data-category="<?php echo esc_attr( $slug ); ?>">
                                        <span><?php echo esc_html( $category['label'] ); ?></span>
                                        <span class="wpuf-text-gray-500 wpuf-px-2 wpuf-py-0.5 wpuf-text-sm wpuf-ml-6">
                                            <?php echo esc_html( $category_counts[ $slug ] ); ?>
                                        </span>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Content Area -->
                <div class="wpuf-flex-1">
                    <!-- Templates Grid -->
                    <div class="wpuf-flex wpuf-flex-wrap wpuf-gap-4" id="templates-grid">
                        <!-- Blank Form -->
                        <?php 
                        $blank_form_category = strpos( strtolower( $form_type ), 'registration' ) !== false || strpos( strtolower( $form_type ), 'profile' ) !== false ? 'registration' : 'post';
                        ?>
                        <div class="template-box wpuf-template-item" data-category="<?php echo esc_attr($blank_form_category); ?>" data-title="blank form" style="width: calc(25% - 12px);">
                            <div class="wpuf-relative wpuf-group wpuf-shadow-base">
                                <img src="<?php echo esc_attr( WPUF_ASSET_URI . '/images/templates/blank.svg' ); ?>" alt="Blank Form">
                                <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md"></div>
                                <a href="<?php echo esc_url( $blank_form_url ); ?>" class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none" title="<?php echo esc_attr( 'Blank Form' ); ?>">
                                    <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                </a>
                            </div>
                            <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( 'Blank Form' ); ?></p>
                        </div>

                        <!-- AI Forms Template - Show for both Post Forms and Registration/Profile Forms -->
                        <?php
                        // Determine the appropriate category for AI Forms based on form type
                        $ai_form_category = $is_post_form ? 'post' : 'registration';
                        $ai_configured = wpuf_check_ai_configuration();
                        $ai_form_url = $ai_configured ? add_query_arg( [
                            'action'   => $action_name,
                            'template' => 'ai_form',
                            '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ),
                        ], admin_url( 'admin.php' ) ) : '#';
                        ?>
                        <div class="template-box wpuf-template-item wpuf-ai-forms-template" data-category="<?php echo esc_attr($ai_form_category); ?>" data-title="ai forms" data-ai-configured="<?php echo $ai_configured ? 'true' : 'false'; ?>" style="width: calc(25% - 12px);">
                            <div class="wpuf-relative wpuf-group wpuf-shadow-base">
                                <div class="wpuf-bg-white wpuf-rounded-lg wpuf-flex wpuf-items-center wpuf-justify-center">
                                    <svg width="246" height="249" viewBox="0 0 246 249" fill="none" xmlns="http://www.w3.org/2000/svg" class="wpuf-w-full wpuf-h-auto">
                                        <g filter="url(#filter0_dd_ai_forms)">
                                            <path d="M3 10C3 5.58172 6.58172 2 11 2H235C239.418 2 243 5.58172 243 10V237C243 241.418 239.418 245 235 245H11C6.58173 245 3 241.418 3 237V10Z" fill="white"/>
                                            <path d="M11 2.5H235C239.142 2.5 242.5 5.85786 242.5 10V237C242.5 241.142 239.142 244.5 235 244.5H11C6.85787 244.5 3.5 241.142 3.5 237V10C3.5 5.85786 6.85786 2.5 11 2.5Z" stroke="#E2E8F0"/>
                                        </g>
                                        <path d="M118.397 118.109L116.5 124.75L114.603 118.109C113.606 114.621 110.879 111.894 107.391 110.897L100.75 109L107.391 107.103C110.879 106.106 113.606 103.379 114.603 99.8911L116.5 93.25L118.397 99.8911C119.394 103.379 122.121 106.106 125.609 107.103L132.25 109L125.609 110.897C122.121 111.894 119.394 114.621 118.397 118.109Z" stroke="url(#paint0_linear_ai_forms)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M138.104 101.334L137.5 103.75L136.896 101.334C136.191 98.5124 133.988 96.3094 131.166 95.604L128.75 95L131.166 94.396C133.988 93.6906 136.191 91.4876 136.896 88.6661L137.5 86.25L138.104 88.6661C138.809 91.4876 141.012 93.6906 143.834 94.396L146.25 95L143.834 95.604C141.012 96.3094 138.809 98.5124 138.104 101.334Z" stroke="url(#paint1_linear_ai_forms)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M134.92 128.99L134 131.75L133.08 128.99C132.558 127.423 131.327 126.192 129.76 125.67L127 124.75L129.76 123.83C131.327 123.308 132.558 122.077 133.08 120.51L134 117.75L134.92 120.51C135.442 122.077 136.673 123.308 138.24 123.83L141 124.75L138.24 125.67C136.673 126.192 135.442 127.423 134.92 128.99Z" stroke="url(#paint2_linear_ai_forms)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M96.173 163H94.5423L98.2064 152.818H99.9813L103.645 163H102.015L99.1361 154.668H99.0566L96.173 163ZM96.4465 159.013H101.736V160.305H96.4465V159.013ZM106.652 152.818V163H105.116V152.818H106.652ZM112.594 163V152.818H118.908V154.141H114.13V157.243H118.456V158.56H114.13V163H112.594ZM123.334 163.154C122.618 163.154 121.993 162.99 121.46 162.662C120.926 162.334 120.512 161.875 120.217 161.285C119.922 160.695 119.774 160.005 119.774 159.217C119.774 158.424 119.922 157.732 120.217 157.138C120.512 156.545 120.926 156.085 121.46 155.756C121.993 155.428 122.618 155.264 123.334 155.264C124.05 155.264 124.675 155.428 125.208 155.756C125.742 156.085 126.156 156.545 126.451 157.138C126.746 157.732 126.894 158.424 126.894 159.217C126.894 160.005 126.746 160.695 126.451 161.285C126.156 161.875 125.742 162.334 125.208 162.662C124.675 162.99 124.05 163.154 123.334 163.154ZM123.339 161.906C123.803 161.906 124.188 161.784 124.492 161.538C124.797 161.293 125.023 160.967 125.169 160.559C125.318 160.151 125.392 159.702 125.392 159.212C125.392 158.724 125.318 158.277 125.169 157.869C125.023 157.458 124.797 157.129 124.492 156.88C124.188 156.631 123.803 156.507 123.339 156.507C122.872 156.507 122.484 156.631 122.176 156.88C121.871 157.129 121.644 157.458 121.495 157.869C121.349 158.277 121.276 158.724 121.276 159.212C121.276 159.702 121.349 160.151 121.495 160.559C121.644 160.967 121.871 161.293 122.176 161.538C122.484 161.784 122.872 161.906 123.339 161.906ZM128.553 163V155.364H129.99V156.577H130.069C130.209 156.166 130.454 155.843 130.805 155.607C131.16 155.369 131.561 155.249 132.008 155.249C132.101 155.249 132.21 155.253 132.336 155.259C132.466 155.266 132.567 155.274 132.64 155.284V156.706C132.58 156.689 132.474 156.671 132.321 156.651C132.169 156.628 132.017 156.616 131.864 156.616C131.513 156.616 131.2 156.691 130.924 156.84C130.653 156.986 130.437 157.19 130.278 157.452C130.119 157.71 130.04 158.005 130.04 158.337V163H128.553ZM133.926 163V155.364H135.353V156.607H135.447C135.606 156.186 135.867 155.857 136.228 155.622C136.589 155.384 137.022 155.264 137.525 155.264C138.036 155.264 138.463 155.384 138.808 155.622C139.156 155.861 139.413 156.189 139.579 156.607H139.658C139.841 156.199 140.131 155.874 140.528 155.632C140.926 155.387 141.4 155.264 141.95 155.264C142.643 155.264 143.208 155.481 143.646 155.915C144.086 156.35 144.307 157.004 144.307 157.879V163H142.82V158.018C142.82 157.501 142.679 157.127 142.398 156.895C142.116 156.663 141.78 156.547 141.388 156.547C140.905 156.547 140.528 156.696 140.26 156.994C139.991 157.289 139.857 157.669 139.857 158.133V163H138.376V157.924C138.376 157.51 138.246 157.177 137.988 156.925C137.729 156.673 137.393 156.547 136.979 156.547C136.697 156.547 136.437 156.621 136.198 156.771C135.963 156.916 135.772 157.12 135.626 157.382C135.484 157.644 135.413 157.947 135.413 158.292V163H133.926ZM152.021 157.228L150.674 157.467C150.618 157.294 150.528 157.13 150.406 156.974C150.286 156.819 150.124 156.691 149.918 156.592C149.713 156.492 149.456 156.442 149.148 156.442C148.727 156.442 148.376 156.537 148.094 156.726C147.812 156.911 147.671 157.152 147.671 157.447C147.671 157.702 147.766 157.907 147.955 158.063C148.144 158.219 148.448 158.347 148.869 158.446L150.082 158.724C150.785 158.887 151.309 159.137 151.653 159.475C151.998 159.813 152.171 160.252 152.171 160.793C152.171 161.25 152.038 161.658 151.773 162.016C151.511 162.37 151.145 162.649 150.674 162.851C150.207 163.053 149.665 163.154 149.048 163.154C148.193 163.154 147.496 162.972 146.955 162.607C146.415 162.239 146.084 161.717 145.961 161.041L147.398 160.822C147.487 161.197 147.671 161.48 147.95 161.673C148.228 161.862 148.591 161.956 149.038 161.956C149.526 161.956 149.915 161.855 150.207 161.653C150.498 161.447 150.644 161.197 150.644 160.902C150.644 160.663 150.555 160.463 150.376 160.3C150.2 160.138 149.93 160.015 149.565 159.933L148.273 159.649C147.56 159.487 147.033 159.228 146.692 158.874C146.354 158.519 146.185 158.07 146.185 157.526C146.185 157.076 146.311 156.681 146.563 156.343C146.814 156.005 147.162 155.741 147.607 155.553C148.051 155.36 148.56 155.264 149.133 155.264C149.958 155.264 150.608 155.443 151.082 155.801C151.556 156.156 151.869 156.631 152.021 157.228Z" fill="#4B5563"/>
                                        <defs>
                                            <filter id="filter0_dd_ai_forms" x="0" y="0" width="246" height="249" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                                <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                                <feOffset dy="1"/>
                                                <feGaussianBlur stdDeviation="1"/>
                                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.06 0"/>
                                                <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_ai_forms"/>
                                                <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
                                                <feOffset dy="1"/>
                                                <feGaussianBlur stdDeviation="1.5"/>
                                                <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0"/>
                                                <feBlend mode="normal" in2="effect1_dropShadow_ai_forms" result="effect2_dropShadow_ai_forms"/>
                                                <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow_ai_forms" result="shape"/>
                                            </filter>
                                            <linearGradient id="paint0_linear_ai_forms" x1="138" y1="86" x2="123.5" y2="134.5" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#FFEE00"/>
                                                <stop offset="0.278846" stop-color="#D500FF"/>
                                                <stop offset="1" stop-color="#0082FF"/>
                                            </linearGradient>
                                            <linearGradient id="paint1_linear_ai_forms" x1="138" y1="86" x2="123.5" y2="134.5" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#FFEE00"/>
                                                <stop offset="0.278846" stop-color="#D500FF"/>
                                                <stop offset="1" stop-color="#0082FF"/>
                                            </linearGradient>
                                            <linearGradient id="paint2_linear_ai_forms" x1="138" y1="86" x2="123.5" y2="134.5" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#FFEE00"/>
                                                <stop offset="0.278846" stop-color="#D500FF"/>
                                                <stop offset="1" stop-color="#0082FF"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-10 wpuf-rounded-md"></div>
                                <a href="<?php echo esc_url( $ai_form_url ); ?>" class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none" title="<?php echo esc_attr( 'AI Forms' ); ?>">
                                    <?php esc_html_e( 'Create with AI', 'wp-user-frontend' ); ?>
                                </a>
                            </div>
                            <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( 'AI Forms' ); ?></p>
                        </div>

                        <?php
                            $crown_icon = WPUF_ROOT . '/assets/images/pro-badge.svg';
                            $pro_badge = WPUF_ASSET_URI . '/images/pro-badge.svg';

                            foreach ( $registry as $key => $template ) {
                                $template_title = $template->get_title();
                                $category = wpuf_get_template_category( $template_title, $categories, $form_type );
                                ?>
                                <div class="template-box wpuf-template-item" data-category="<?php echo esc_attr($category); ?>" data-title="<?php echo esc_attr(strtolower($template_title)); ?>" style="width: calc(25% - 12px);">
                                    <div class="wpuf-relative wpuf-group">
                                    <?php
                                        $class     = 'template-active';
                                        $title     = $template->title;
                                        $image     = $template->image ? $template->image : '';
                                        $disabled  = '';
                                        $description = ! empty( $template->description ) ? $template->description : '';
                                        $btn_class = 'wpuf-btn-primary';

                                        $url   = esc_url( add_query_arg( [
                                            'action'   => $action_name,
                                            'template' => $key,
                                            '_wpnonce' => wp_create_nonce( 'wpuf_create_from_template' ),
                                        ], admin_url( 'admin.php' ) ) );

                                        if ( ! $template->is_enabled() ) {
                                            $url      = '#';
                                            $class    = 'template-inactive';
                                            $disabled = 'disabled';
                                            $btn_class = 'wpuf-btn wpuf-btn-disabled';
                                        }

                                        if ( $image ) {
                                            printf( '<img src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                        } else {
                                            echo '<div class="wpuf-aspect-square wpuf-flex wpuf-items-center wpuf-justify-center wpuf-bg-gray-50 wpuf-rounded-lg" role="img" aria-label="' . esc_attr( $title ) . '">';
                                            printf( '<h2 class="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-800 wpuf-text-center wpuf-px-2">%s</h2>', esc_html( $title ) );
                                            echo '</div>';
                                        }
                                    ?>
                                        <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-5 wpuf-rounded-md"></div>
                                        <?php
                                        if ( ! $template->is_enabled() ) {
                                        ?>
                                            <div class="wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-w-full wpuf-h-full wpuf-p-3 wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center">
                                                <h1 class="wpuf-text-sm wpuf-text-white wpuf-mb-1 wpuf-text-center"><?php esc_html_e( 'This integration is not installed.', 'wp-user-frontend' ) ?></h1>
                                                <p class="wpuf-text-white wpuf-text-xs wpuf-text-center"><?php echo esc_html( $description ); ?></p>
                                            </div>
                                        <?php
                                        } else {
                                        ?>
                                        <a
                                            href="<?php echo esc_url( $url ); ?>"
                                            class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none wpuf-transition-all"
                                            title="<?php echo esc_attr( $template->get_title() ); ?>" <?php echo esc_attr($disabled ); ?>
                                        >
                                            <?php esc_html_e( 'Create Form', 'wp-user-frontend' ); ?>
                                        </a>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( $title ); ?></p>
                                </div>
                            <?php
                            }

                            // Pro Templates
                            if (!empty($pro_templates)) {
                                foreach ( $pro_templates as $template ) {
                                    $class = 'template-inactive is-pro-template';
                                    $image = $template->get_image();
                                    $title = $template->get_title();
                                    $pro_template_category = wpuf_get_template_category( $title, $categories, $form_type );
                                    ?>
                                    <div class="template-box wpuf-template-item" data-category="<?php echo esc_attr($pro_template_category); ?>" data-title="<?php echo esc_attr(strtolower($title)); ?>" style="width: calc(25% - 12px);">
                                        <div class="wpuf-relative wpuf-group">
                                        <?php
                                            if ( $image ) {
                                                printf( '<img class="wpuf-opacity-50" src="%s" alt="%s">', esc_attr( $image ), esc_attr( $title ) );
                                            } else {
                                                echo '<div class="wpuf-aspect-square wpuf-flex wpuf-items-center wpuf-justify-center wpuf-bg-gray-50 wpuf-rounded-lg wpuf-opacity-50">';
                                                printf( '<h2 class="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-800 wpuf-text-center wpuf-px-2">%s</h2>', esc_html( $title ) );
                                                echo '</div>';
                                            }
                                        ?>
                                            <img class="wpuf-absolute wpuf-top-3 wpuf-right-3 wpuf-w-6 wpuf-h-6" src="<?php echo esc_attr( $pro_badge ); ?>" alt="Pro">
                                            <div class="wpuf-absolute wpuf-opacity-0 group-hover:wpuf-opacity-70 wpuf-transition-all wpuf-z-10 wpuf-text-center wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-bg-emerald-900 wpuf-h-full wpuf-w-full wpuf-top-0 wpuf-left-0 wpuf-text-white wpuf-p-5 wpuf-rounded-md"></div>
                                            <a
                                                href="<?php echo esc_url( class_exists( 'WeDevs\Wpuf\Free\Pro_Prompt' ) ? \WeDevs\Wpuf\Free\Pro_Prompt::get_upgrade_to_pro_popup_url() : '#' ); ?>"
                                                target="_blank"
                                                class="wpuf-btn-secondary wpuf-w-max wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-20 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-border-transparent focus:wpuf-shadow-none wpuf-transition-all"
                                                title="<?php echo esc_attr( $template->get_title() ); ?>" >
                                                <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                                            </a>
                                        </div>
                                        <p class="wpuf-text-sm wpuf-text-gray-700 wpuf-text-center wpuf-font-medium"><?php echo esc_html( $title ); ?></p>
                                    </div>
                                    <?php
                                }
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Provider Configuration Modal -->
<div class="wpuf-ai-config-modal wpuf-fixed wpuf-top-0 wpuf-left-0 wpuf-w-screen wpuf-h-screen wpuf-bg-black wpuf-bg-opacity-50 wpuf-hidden wpuf-z-[1000000] wpuf-flex wpuf-items-center wpuf-justify-center" id="ai-config-modal">
    <div class="wpuf-bg-white wpuf-rounded-md wpuf-p-8 wpuf-max-w-xl wpuf-w-full wpuf-mx-5 wpuf-relative">
        <!-- Key Icon -->
        <div class="wpuf-flex wpuf-justify-center wpuf-mb-8">
            <svg width="110" height="110" viewBox="0 0 110 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="110" height="110" rx="55" fill="#D1FAE5"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M60 41C55.0294 41 51 45.0294 51 50C51 50.525 51.0451 51.0402 51.1317 51.5419C51.2213 52.0604 51.089 52.4967 50.8369 52.7489L42.1716 61.4142C41.4214 62.1644 41 63.1818 41 64.2426V68C41 68.5523 41.4477 69 42 69H47C47.5523 69 48 68.5523 48 68V66H50C50.5523 66 51 65.5523 51 65V63H53C53.2652 63 53.5196 62.8946 53.7071 62.7071L57.2511 59.1631C57.5033 58.911 57.9396 58.7787 58.4581 58.8683C58.9598 58.9549 59.475 59 60 59C64.9706 59 69 54.9706 69 50C69 45.0294 64.9706 41 60 41ZM60 45C59.4477 45 59 45.4477 59 46C59 46.5523 59.4477 47 60 47C61.6569 47 63 48.3431 63 50C63 50.5523 63.4477 51 64 51C64.5523 51 65 50.5523 65 50C65 47.2386 62.7614 45 60 45Z" fill="#065F46"/>
            </svg>
        </div>
        
        <!-- Title -->
        <h2 class="wpuf-text-2xl wpuf-font-medium wpuf-text-center wpuf-text-gray-900 wpuf-mb-4">
            <?php esc_html_e( 'AI Provider Not Configured', 'wp-user-frontend' ); ?>
        </h2>
        
        <!-- Description -->
        <p class="wpuf-text-lg wpuf-text-center wpuf-text-gray-400 wpuf-mb-16">
            <?php esc_html_e( 'To use AI Form Generation, please connect an AI provider by adding your API key in the settings', 'wp-user-frontend' ); ?>
        </p>
        
        <!-- Buttons -->
        <div class="wpuf-flex wpuf-justify-center wpuf-gap-3">
            <button class="wpuf-px-6 wpuf-py-3 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-text-lg wpuf-transition-colors wpuf-min-w-[101px]" id="ai-config-cancel">
                <?php esc_html_e( 'Cancel', 'wp-user-frontend' ); ?>
            </button>
            <button class="wpuf-px-6 wpuf-py-3 wpuf-bg-emerald-700 hover:wpuf-bg-emerald-800 wpuf-text-white wpuf-rounded-md wpuf-text-lg wpuf-transition-colors wpuf-min-w-[158px]" id="ai-config-settings">
                <?php esc_html_e( 'Go to Settings', 'wp-user-frontend' ); ?>
            </button>
        </div>
    </div>
</div>

<style>
    /* AI Configuration Modal Styles */
    .wpuf-ai-config-modal {
        transition: opacity 0.3s ease-in-out;
    }
    .wpuf-ai-config-modal.wpuf-hidden {
        display: none !important;
    }
    .wpuf-ai-config-modal .wpuf-opacity-100 {
        opacity: 1;
    }
    /* Ensure the modal backdrop covers everything */
    #ai-config-modal {
        backdrop-filter: blur(4px);
    }
    /* Button hover effects */
    #ai-config-cancel:hover {
        background-color: rgba(249, 250, 251, 1);
    }
    #ai-config-settings:hover {
        background-color: rgba(5, 150, 105, 1);
    }
</style>

<script type="text/javascript">
    ( function ( $ ) {
        var popup = {
            init: function () {
                $( 'a.new-wpuf-form' ).on( 'click', this.openModal );
                $( '.wpuf-form-template-modal .wpuf-close-btn' ).on( 'click', $.proxy( this.closeModal, this ) );
                $( 'body' ).on( 'keydown', $.proxy( this.onEscapeKey, this ) );

                $( '#template-search' ).on( 'input', this.searchTemplates );
                $( '.wpuf-template-category' ).on( 'click', this.filterByCategory );
                $( document ).on( 'keydown', $.proxy( this.handleKeyboardShortcuts, this ) );
                
                // AI Forms handling - only attach if AI is not configured
                var $aiTemplate = $( '.wpuf-ai-forms-template' );
                var aiConfigured = $aiTemplate.data( 'ai-configured' );
              
                // Check for both boolean true and string 'true'
                var isConfigured = aiConfigured === true || aiConfigured === 'true';
                
                if ( $aiTemplate.length && !isConfigured ) {
                    $( '.wpuf-ai-forms-template a' ).on( 'click', $.proxy( this.handleAIFormsClick, this ) );
                    $( '#ai-config-cancel' ).on( 'click', $.proxy( this.closeAIConfigModal, this ) );
                    $( '#ai-config-settings' ).on( 'click', $.proxy( this.goToSettings, this ) );
                } else {
                    console.log('NOT attaching AI click handler - AI is configured or template not found');
                }
            },

            handleKeyboardShortcuts: function( e ) {
                if ( ( e.ctrlKey || e.metaKey ) && e.keyCode === 75 ) {
                    e.preventDefault();
                    $( '#template-search' ).focus();
                }

                if ( e.keyCode === 27 && $( '#template-search' ).is( ':focus' ) ) {
                    e.preventDefault();
                    $( '#template-search' ).val( '' ).trigger( 'input' );
                    this.searchTemplates({ target: document.getElementById('template-search') });
                }
            },

            openModal: function ( e ) {
                e.preventDefault();
                var $modal = $( '.wpuf-form-template-modal' );
                $modal.show().removeClass( 'wpuf-hidden' );
                
                $modal[0].offsetHeight;
                
                setTimeout( function() {
                    $modal.addClass( 'wpuf-modal-show' );
                }, 10 );
                
                $( 'body' ).addClass( 'wpuf-modal-open' );
                $( 'body' ).css( 'overflow', 'hidden' );
                $( '#wpbody-content .wrap' ).hide();
            },

            onEscapeKey: function ( e ) {
                if (27 === e.keyCode) {
                    this.closeModal( e );
                }
            },

            closeModal: function ( e ) {
                if (typeof e !== 'undefined') {
                    e.preventDefault();
                }
                
                var $modal = $( '.wpuf-form-template-modal' );
                $modal.removeClass( 'wpuf-modal-show' );
                
                setTimeout( function() {
                    $modal.hide().addClass( 'wpuf-hidden' );
                }, 300 ); // Match the CSS transition duration
                
                $( 'body' ).removeClass( 'wpuf-modal-open' );
                $( 'body' ).css( 'overflow', '' );
                $( '#wpbody-content .wrap' ).show();
            },

            searchTemplates: function ( e ) {
                var searchTerm = $( e.target ).val().toLowerCase();
                var $templates = $( '.wpuf-template-item' );

                if (searchTerm.length > 0) {
                    var $allButtons = $('.wpuf-template-category');
                    $allButtons.removeClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' ).addClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' );
                    $allButtons.find('span:last-child').attr('class', 'wpuf-text-gray-500 wpuf-px-2 wpuf-py-0.5 wpuf-text-sm wpuf-ml-6');

                    var $allCategoryButton = $allButtons.filter('[data-category="all"]');
                    $allCategoryButton.removeClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' ).addClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' );
                    $allCategoryButton.find('span:last-child').attr('class', 'wpuf-border wpuf-border-primary wpuf-text-primary wpuf-text-sm wpuf-font-semibold wpuf-px-2.5 wpuf-py-0.5 wpuf-rounded-full wpuf-ml-6');
                }

                $templates.each( function() {
                    var $template = $( this );
                    var title = $template.data( 'title' ) || '';

                    if ( title.indexOf( searchTerm ) !== -1 ) {
                        $template.show();
                    } else {
                        $template.hide();
                    }
                });
            },

            filterByCategory: function ( e ) {
                e.preventDefault();
                var $button = $( this );
                var category = $button.data( 'category' );

                // Update active state
                var $allButtons = $('.wpuf-template-category');
                
                // Reset all buttons to inactive state
                $allButtons.removeClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' ).addClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' );
                $allButtons.find('span:last-child').attr('class', 'wpuf-text-gray-500 wpuf-px-2 wpuf-py-0.5 wpuf-text-sm wpuf-ml-6');
                
                // Set active state for the clicked button
                $button.removeClass( 'wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-100' ).addClass( 'wpuf-bg-gray-100 wpuf-text-primary wpuf-font-medium' );
                $button.find('span:last-child').attr('class', 'wpuf-border wpuf-border-primary wpuf-text-primary wpuf-text-sm wpuf-font-semibold wpuf-px-2.5 wpuf-py-0.5 wpuf-rounded-full wpuf-ml-6');

                // Filter templates
                var $templates = $( '.wpuf-template-item' );

                if ( category === 'all' ) {
                    $templates.show();
                } else {
                    $templates.hide();
                    $templates.filter( '[data-category="' + category + '"]' ).show();
                }

                // Clear search when filtering by category
                $( '#template-search' ).val( '' );
            },
            
            handleAIFormsClick: function( e ) {
                var $template = $( e.target ).closest( '.wpuf-ai-forms-template' );
                var isConfigured = $template.data( 'ai-configured' ) === 'true';
                
                if ( !isConfigured ) {
                    e.preventDefault();
                    this.openAIConfigModal();
                } else {
                    // AI is configured, let the link proceed normally
                    return true;
                }
            },
            
            openAIConfigModal: function() {
                var $modal = $( '#ai-config-modal' );
                $modal.removeClass( 'wpuf-hidden' );
                $modal.css( 'display', 'flex' );
                
                // Add fade-in effect
                setTimeout( function() {
                    $modal.addClass( 'wpuf-opacity-100' );
                }, 10 );
            },
            
            closeAIConfigModal: function( e ) {
                e.preventDefault();
                var $modal = $( '#ai-config-modal' );
                $modal.removeClass( 'wpuf-opacity-100' );
                
                setTimeout( function() {
                    $modal.addClass( 'wpuf-hidden' );
                    $modal.css( 'display', 'none' );
                }, 300 );
            },
            
            goToSettings: function( e ) {
                e.preventDefault();
                // Redirect to WPUF AI Settings page
                window.location.href = '<?php echo admin_url( 'admin.php?page=wpuf-settings#wpuf_ai' ); ?>';
            },
        };

        $( document ).ready( function () {
            popup.init();
        } );

    } )( jQuery );
</script>
