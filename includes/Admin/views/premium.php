<?php
/**
 * WPUF Premium (Upgrade to Pro) page.
 *
 * Static marketing view mirroring the "WPUF Upgrade to Pro" Figma redesign.
 *
 * @package WP_User_Frontend
 * @since   WPUF_SINCE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wpuf_pricing_url = 'https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=wpuf-dashboard&utm_medium=why-upgrade&utm_campaign=free-to-pro';
$wpuf_modules_url = 'https://wedevs.com/wp-user-frontend-pro/modules/?utm_source=wpuf-dashboard&utm_medium=why-upgrade&utm_campaign=free-to-pro';
$wpuf_asset       = esc_url( WPUF_ASSET_URI . '/images/premium' );

/*
 * Inline icon SVGs are written as literal markup at each use site (not echoed
 * through wp_kses) because wp_kses lowercases the case-sensitive `viewBox`
 * attribute, which breaks SVG scaling. The markup is static and trusted.
 */

// "What Changes After You Upgrade to Pro?" cards. `icon` is a PNG filename
// under assets/images/premium/icons/ exported from Figma.
$wpuf_changes = [
    [
        'icon'  => 'feat-1.png',
        'title' => __( 'Frontend Posting & Content Management', 'wp-user-frontend' ),
        'desc'  => __( 'Let users submit, edit, and manage posts directly from the frontend without ever accessing wp-admin.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-2.png',
        'title' => __( 'Advanced Forms & AI Builders', 'wp-user-frontend' ),
        'desc'  => __( 'Create powerful forms tailored to your needs using advanced fields, conditional logic, and AI assistance.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-3.png',
        'title' => __( 'User Registration, Profiles & Access', 'wp-user-frontend' ),
        'desc'  => __( 'Design flexible registration flows and rich user profiles that improve onboarding and engagement.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-4.png',
        'title' => __( 'Frontend Dashboard', 'wp-user-frontend' ),
        'desc'  => __( 'Give your users a clean, centralized dashboard where they can manage posts, profiles, and activity.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-5.png',
        'title' => __( 'Memberships, Subscriptions & Monetization', 'wp-user-frontend' ),
        'desc'  => __( 'Monetize your site with flexible pricing, recurring subscriptions, and content restrictions.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-6.png',
        'title' => __( 'User Directory & Listings', 'wp-user-frontend' ),
        'desc'  => __( 'Display users in a structured, searchable directory with modern and customizable layouts.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-7.png',
        'title' => __( 'Access Control & Permissions', 'wp-user-frontend' ),
        'desc'  => __( 'Control who can access, submit, and manage content across your site based on user roles, plans, or custom conditions.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-8.png',
        'title' => __( 'Automation, Messaging & Integrations', 'wp-user-frontend' ),
        'desc'  => __( 'Automate repetitive tasks, connect your tools, and improve communication with built-in messaging and n8n workflow.', 'wp-user-frontend' ),
    ],
    [
        'icon'  => 'feat-9.png',
        'title' => __( 'Reports, Analytics & Insights', 'wp-user-frontend' ),
        'desc'  => __( 'Track performance, understand user behavior, and make smarter decisions with built-in reports and analytics.', 'wp-user-frontend' ),
    ],
];

// Feature comparison rows. Lite: 'yes' | 'no' | 'limited'. Pro is always 'yes'.
$wpuf_compare = [
    [
		'label' => __( 'Frontend posting', 'wp-user-frontend' ),
		'lite' => 'yes',
	],
    [
		'label' => __( 'Registration forms', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Advanced form fields', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Conditional logic', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Membership tools', 'wp-user-frontend' ),
		'lite' => 'limited',
	],
    [
		'label' => __( 'Content restriction', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'User directory', 'wp-user-frontend' ),
		'lite' => 'limited',
	],
    [
		'label' => __( 'n8n automation', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Social logins', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Stripe, Razorpay, and more', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Coupon management', 'wp-user-frontend' ),
		'lite' => 'no',
	],
    [
		'label' => __( 'Premium modules', 'wp-user-frontend' ),
		'lite' => 'no',
	],
];

// Modules & integrations cards.
$wpuf_module_cards = [
    [
		'img' => 'mod-zapier.png',
		'label' => __( 'Zapier Integration', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-mailchimp.png',
		'label' => __( 'Mailchimp Integration', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-pmpro.png',
		'label' => __( 'Paid Memberships Pro Integration', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-seo.png',
		'label' => __( 'SEO Module for User Directory', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-html-email.png',
		'label' => __( 'HTML Email Templates', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-sms.png',
		'label' => __( 'SMS Notification', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-buddypress.png',
		'label' => __( 'BuddyPress Integration', 'wp-user-frontend' ),
	],
    [
		'img' => 'mod-qr.png',
		'label' => __( 'QR Code Generator', 'wp-user-frontend' ),
	],
];

// Pricing plans.
$wpuf_plans = [
    [
        'name'           => __( 'Personal', 'wp-user-frontend' ),
        'desc'           => __( 'All-in-One Frontend Solution', 'wp-user-frontend' ),
        'price_annual'   => '$49',
        'price_lifetime' => '$196',
        'orig_lifetime'  => '$245',
        'off_lifetime'   => __( '20% OFF', 'wp-user-frontend' ),
        'featured'       => false,
        'inherits'       => __( 'Everything in Free', 'wp-user-frontend' ),
        'features'       => [
            __( 'License for 1 Site', 'wp-user-frontend' ),
            __( '2 Premium Modules', 'wp-user-frontend' ),
            __( 'Ticket Based Support', 'wp-user-frontend' ),
        ],
    ],
    [
        'name'           => __( 'Professional', 'wp-user-frontend' ),
        'desc'           => __( 'Achieve More with WP User Frontend Pro', 'wp-user-frontend' ),
        'price_annual'   => '$89',
        'price_lifetime' => '$334',
        'orig_lifetime'  => '$445',
        'off_lifetime'   => __( '25% OFF', 'wp-user-frontend' ),
        'featured'       => true,
        'inherits'       => __( 'Everything in Personal', 'wp-user-frontend' ),
        'features'       => [
            __( 'License for 5 Sites', 'wp-user-frontend' ),
            __( '13 Premium Modules', 'wp-user-frontend' ),
            __( 'Ticket-Based Support', 'wp-user-frontend' ),
        ],
    ],
    [
        'name'           => __( 'Business', 'wp-user-frontend' ),
        'desc'           => __( 'Ultimate frontend & form solution', 'wp-user-frontend' ),
        'price_annual'   => '$159',
        'price_lifetime' => '$557',
        'orig_lifetime'  => '$795',
        'off_lifetime'   => __( '30% OFF', 'wp-user-frontend' ),
        'featured'       => false,
        'inherits'       => __( 'Everything in Professional', 'wp-user-frontend' ),
        'features'       => [
            __( 'License for 15 Sites', 'wp-user-frontend' ),
            __( '18 Premium Modules', 'wp-user-frontend' ),
            __( 'Priority Support', 'wp-user-frontend' ),
        ],
    ],
];

?>
<div class="wrap wpuf-premium-page">

    <?php // 0. Top bar band (full-width white; matches the post-forms admin header. Pro is always off here). ?>
    <div class="wpuf-pp-topbar">
        <div class="wpuf-premium-page__inner wpuf-pp-topbar__inner">
            <div class="wpuf-pp-topbar__left">
                <img class="wpuf-pp-topbar__logo" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/wpuf-icon-circle.svg' ); ?>" alt="<?php esc_attr_e( 'WP User Frontend', 'wp-user-frontend' ); ?>">
                <h2 class="wpuf-pp-topbar__title"><?php esc_html_e( 'WP User Frontend', 'wp-user-frontend' ); ?></h2>
                <span class="wpuf-pp-topbar__version">v<?php echo esc_html( WPUF_VERSION ); ?></span>
                <a class="wpuf-pp-topbar__upgrade" href="<?php echo esc_url( $wpuf_pricing_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?></a>
            </div>
            <div class="wpuf-pp-topbar__right">
                <a class="wpuf-pp-topbar__ideas" href="https://wpuf.canny.io/ideas" target="_blank" rel="noopener">&#128161; <?php esc_html_e( 'Submit Ideas', 'wp-user-frontend' ); ?></a>
                <a class="wpuf-pp-topbar__support" href="<?php echo esc_url( 'https://wedevs.com/contact/?utm_source=wpuf-dashboard&utm_medium=why-upgrade&utm_campaign=free-to-pro' ); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e( 'Support', 'wp-user-frontend' ); ?>
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z"/></svg>
                </a>
            </div>
        </div>
    </div>

    <div class="wpuf-premium-page__inner">

        <?php // 1. Hero. ?>
        <section class="wpuf-pp-hero">
            <span class="wpuf-pp-hero__glow"></span>
            <div class="wpuf-pp-hero__content">
                <h1><?php esc_html_e( "What You're Missing in the Free Version", 'wp-user-frontend' ); ?></h1>
                <p><?php esc_html_e( 'Let users submit posts, register, and manage their profiles from the frontend. Create forms, directories, and membership systems without touching wp-admin. Control who can access what, enable social logins, and run everything with less effort.', 'wp-user-frontend' ); ?></p>
                <a class="wpuf-pp-btn wpuf-pp-btn--primary" href="<?php echo esc_url( $wpuf_pricing_url ); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e( 'Upgrade to Pro', 'wp-user-frontend' ); ?>
                    <svg class="wpuf-pp-ico" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>
                </a>
            </div>
            <div class="wpuf-pp-hero__crown">
                <span class="wpuf-pp-hero__crown-img">
                    <img src="<?php echo esc_url( $wpuf_asset . '/hero-crown.png' ); ?>" alt="<?php esc_attr_e( 'WP User Frontend Pro', 'wp-user-frontend' ); ?>">
                </span>
                <span class="wpuf-pp-hero__badge"><?php esc_html_e( 'Up to 30% Off', 'wp-user-frontend' ); ?></span>
            </div>
        </section>

        <?php // 2. Feature comparison. ?>
        <section class="wpuf-pp-compare">
            <div class="wpuf-pp-compare__intro">
                <h2><?php esc_html_e( 'Powerful Features Available Only in WP User Frontend Pro', 'wp-user-frontend' ); ?></h2>
                <p><?php esc_html_e( 'You’ve started with Lite. Now unlock AI-powered form building, automated n8n workflows, advanced registration, searchable user directories, and full membership control.', 'wp-user-frontend' ); ?></p>
            </div>
            <div class="wpuf-pp-table">
                <div class="wpuf-pp-table__head">
                    <span class="wpuf-pp-table__head-label"><?php esc_html_e( 'Feature', 'wp-user-frontend' ); ?></span>
                    <span class="wpuf-pp-table__cols">
                        <span class="wpuf-pp-table__col"><?php esc_html_e( 'Lite', 'wp-user-frontend' ); ?></span>
                        <span class="wpuf-pp-table__col wpuf-pp-table__col--pro">
                            <?php esc_html_e( 'Pro', 'wp-user-frontend' ); ?>
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M16.9098 7.55819C16.9121 7.59959 16.9091 7.64172 16.8987 7.68357L15.9735 12.5211C15.9268 12.7081 15.7597 12.8396 15.5676 12.8406L10.0162 12.8688H10.0141H4.46262C4.2695 12.8688 4.10121 12.7368 4.05456 12.5488L3.12933 7.69724C3.11867 7.65426 3.11559 7.61095 3.11828 7.56848C2.76036 7.45536 2.5 7.11938 2.5 6.72344C2.5 6.23491 2.89617 5.8375 3.38318 5.8375C3.87019 5.8375 4.26636 6.23491 4.26636 6.72344C4.26636 6.99856 4.14069 7.24471 3.94393 7.40733L5.10232 8.57811C5.39508 8.87404 5.80135 9.04369 6.21697 9.04369C6.70841 9.04369 7.17697 8.80958 7.47204 8.41735L9.37578 5.887C9.21585 5.72669 9.11682 5.50517 9.11682 5.26094C9.11682 4.77241 9.51299 4.375 10 4.375C10.487 4.375 10.8832 4.77241 10.8832 5.26094C10.8832 5.49786 10.7894 5.71291 10.6378 5.87204L10.6395 5.87401L12.5294 8.41133C12.8244 8.80733 13.2946 9.04375 13.7875 9.04375C14.2069 9.04375 14.6013 8.87989 14.8979 8.58233L16.0636 7.41301C15.8627 7.25044 15.7336 7.00193 15.7336 6.72344C15.7336 6.23491 16.1298 5.8375 16.6168 5.8375C17.1038 5.8375 17.5 6.23491 17.5 6.72344C17.5 7.10881 17.253 7.43657 16.9098 7.55819ZM15.8832 14.1906C15.8832 13.9576 15.6949 13.7688 15.4626 13.7688H4.58413C4.35187 13.7688 4.16357 13.9576 4.16357 14.1906V15.2031C4.16357 15.4361 4.35187 15.625 4.58413 15.625H15.4626C15.6949 15.625 15.8832 15.4361 15.8832 15.2031V14.1906Z"/></svg>
                        </span>
                    </span>
                </div>
                <div class="wpuf-pp-table__body">
                    <?php foreach ( $wpuf_compare as $index => $row ) : ?>
                        <?php if ( $index > 0 ) : ?>
                            <div class="wpuf-pp-divider"></div>
                        <?php endif; ?>
                        <div class="wpuf-pp-row">
                            <span class="wpuf-pp-row__label"><?php echo esc_html( $row['label'] ); ?></span>
                            <span class="wpuf-pp-row__marks">
                                <?php if ( 'yes' === $row['lite'] ) : ?>
                                    <span class="wpuf-pp-mark wpuf-pp-mark--yes"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></span>
                                <?php elseif ( 'limited' === $row['lite'] ) : ?>
                                    <span class="wpuf-pp-mark wpuf-pp-mark--limited"><?php esc_html_e( 'Limited', 'wp-user-frontend' ); ?></span>
                                <?php else : ?>
                                    <span class="wpuf-pp-mark wpuf-pp-mark--no"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></span>
                                <?php endif; ?>
                                <span class="wpuf-pp-mark wpuf-pp-mark--yes"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg></span>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

    <?php // 3. What Changes (full-bleed beige band). ?>
    <section class="wpuf-pp-section wpuf-pp-section--beige">
        <div class="wpuf-premium-page__inner">
            <div class="wpuf-pp-section__head">
                <h2><?php esc_html_e( 'What Changes After You Upgrade to Pro?', 'wp-user-frontend' ); ?></h2>
                <p><?php esc_html_e( 'Everything you need to build, manage, and grow your WordPress site from the frontend.', 'wp-user-frontend' ); ?></p>
            </div>
            <div class="wpuf-pp-grid">
                <?php foreach ( $wpuf_changes as $card ) : ?>
                    <div class="wpuf-pp-grid__card">
                        <span class="wpuf-pp-grid__icon">
                            <img src="<?php echo esc_url( $wpuf_asset . '/icons/' . $card['icon'] ); ?>" alt="">
                        </span>
                        <div>
                            <h3><?php echo esc_html( $card['title'] ); ?></h3>
                            <p><?php echo esc_html( $card['desc'] ); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php // 4. Modules & integrations. ?>
    <div class="wpuf-premium-page__inner">
        <section class="wpuf-pp-modules">
            <div class="wpuf-pp-modules__head">
                <h2><?php esc_html_e( 'Modules and Integrations You Can Unlock with Pro', 'wp-user-frontend' ); ?></h2>
                <p><?php esc_html_e( 'Extend your site with powerful modules designed for specific use cases. Turn features on when you need them and build exactly what your platform requires.', 'wp-user-frontend' ); ?></p>
                <a class="wpuf-pp-btn wpuf-pp-btn--primary" href="<?php echo esc_url( $wpuf_modules_url ); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e( 'Explore All Premium Modules', 'wp-user-frontend' ); ?>
                    <svg class="wpuf-pp-ico" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>
                </a>
            </div>
            <div class="wpuf-pp-modules__grid">
                <?php foreach ( $wpuf_module_cards as $module ) : ?>
                    <div class="wpuf-pp-module">
                        <img src="<?php echo esc_url( $wpuf_asset . '/' . $module['img'] ); ?>" alt="<?php echo esc_attr( $module['label'] ); ?>">
                        <span><?php echo esc_html( $module['label'] ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <?php // 5. Pricing + refund (full-bleed gray band). ?>
    <section class="wpuf-pp-section wpuf-pp-section--gray">
        <div class="wpuf-premium-page__inner">
            <div class="wpuf-pp-pricing__head">
                <h2><?php esc_html_e( 'One Tool for All Your Frontend Needs', 'wp-user-frontend' ); ?></h2>
                <p><?php esc_html_e( 'Create AI-powered forms, user profiles, directories and membership', 'wp-user-frontend' ); ?></p>
                <div class="wpuf-pp-pricing__toggle-wrap">
                    <div class="wpuf-pp-toggle">
                        <button type="button" class="is-active" data-period="annual" data-suffix="<?php echo esc_attr__( '/y', 'wp-user-frontend' ); ?>"><?php esc_html_e( 'Annual', 'wp-user-frontend' ); ?></button>
                        <button type="button" data-period="lifetime" data-suffix=""><?php esc_html_e( 'Lifetime', 'wp-user-frontend' ); ?></button>
                    </div>
                    <span class="wpuf-pp-toggle-note">
                        <svg class="wpuf-pp-toggle-note__arrow" viewBox="0 0 54 39" fill="#00a81c" aria-hidden="true"><path d="M26.4666 22.6055C18.879 23.1442 11.374 24.261 4.02379 27.2122C4.67537 27.3062 5.11439 27.3704 5.56507 27.446C5.80541 27.4848 6.05716 27.5119 6.27968 27.6062C6.72918 27.781 7.18393 28.0111 7.05462 28.6054C6.91194 29.2414 6.40936 29.0649 5.97729 29.003C4.66024 28.8104 3.35211 28.59 2.0331 28.4275C1.43014 28.349 0.809891 28.3722 -0.000120122 28.3423C0.654556 27.4951 1.12158 26.7793 1.70497 26.1545C3.04028 24.7136 4.4292 23.3207 5.80173 21.9071C5.95216 21.7485 6.11347 21.5322 6.30218 21.4931C6.55982 21.4302 6.91273 21.4285 7.11272 21.5692C7.25216 21.6599 7.31692 22.079 7.22676 22.2645C7.05563 22.631 6.7967 22.9845 6.50474 23.2739C5.32772 24.4361 4.1182 25.5802 3.15976 26.4908C5.18494 25.6695 7.44927 24.5803 9.81872 23.8081C15.0401 22.0969 20.4554 21.4054 25.9155 21.004C26.4802 20.9629 26.712 20.8611 26.8547 20.2251C29.169 9.56919 36.2996 2.33457 47.2874 0.174615C49.0136 -0.16803 50.738 -0.0516141 52.2602 0.93445C52.7227 1.23597 53.0197 1.79081 53.3595 2.25982C53.4392 2.36963 53.3815 2.57329 53.3907 2.97455C51.749 0.808799 49.6882 0.737742 47.5977 1.28523C40.4657 3.14975 34.6767 6.86971 31.1828 13.6084C29.9978 15.8941 29.1115 18.2755 28.6683 20.8757C30.2263 20.9617 31.738 21.0252 33.2502 21.1348C36.9786 21.4036 40.6263 22.0679 44.1181 23.4328C46.9474 24.547 49.541 26.0375 51.3462 28.5774C52.2277 29.8176 52.9282 31.1683 52.7733 32.7502C52.5313 35.2004 50.9279 36.5936 48.8368 37.5009C46.3257 38.5953 43.6561 38.9418 40.9595 38.5366C35.7997 37.7627 31.6722 35.2747 28.8579 30.8179C27.4413 28.5712 26.7368 26.0625 26.582 23.4161C26.568 23.1972 26.5631 22.9735 26.5491 22.7545C26.5558 22.7336 26.5233 22.7156 26.4714 22.6147L26.4666 22.6055ZM28.5055 22.5776C28.4646 24.8548 28.9863 26.9065 29.9334 28.8495C32.959 35.0363 40.7012 38.3388 47.3254 36.2425C48.2884 35.9386 49.2447 35.4409 50.0451 34.832C51.3898 33.8156 51.6555 32.4377 51.0149 30.8764C50.3146 29.1428 49.0363 27.8979 47.5322 26.8794C45.0076 25.1735 42.1755 24.2116 39.2134 23.5834C35.7125 22.8437 32.1663 22.5796 28.4985 22.5754L28.5055 22.5776Z"/></svg>
                        <span class="wpuf-pp-toggle-note__text"><?php esc_html_e( 'Up to 30% Off', 'wp-user-frontend' ); ?></span>
                    </span>
                </div>
            </div>

            <div class="wpuf-pp-plans">
                <?php foreach ( $wpuf_plans as $plan ) : ?>
                    <div class="wpuf-pp-plan<?php echo esc_attr( $plan['featured'] ? ' wpuf-pp-plan--featured' : '' ); ?>">
                        <div class="wpuf-pp-plan__top">
                            <div>
                                <p class="wpuf-pp-plan__name"><?php echo esc_html( $plan['name'] ); ?></p>
                                <p class="wpuf-pp-plan__desc"><?php echo esc_html( $plan['desc'] ); ?></p>
                            </div>
                            <div class="wpuf-pp-plan__discount" style="display:none;">
                                <span class="wpuf-pp-plan__orig" data-lifetime="<?php echo esc_attr( $plan['orig_lifetime'] ); ?>"></span>
                                <span class="wpuf-pp-plan__chip" data-lifetime="<?php echo esc_attr( $plan['off_lifetime'] ); ?>"></span>
                            </div>
                            <div class="wpuf-pp-plan__price">
                                <strong class="wpuf-pp-plan__amount" data-annual="<?php echo esc_attr( $plan['price_annual'] ); ?>" data-lifetime="<?php echo esc_attr( $plan['price_lifetime'] ); ?>"><?php echo esc_html( $plan['price_annual'] ); ?></strong>
                                <span class="wpuf-pp-plan__period"><?php esc_html_e( '/y', 'wp-user-frontend' ); ?></span>
                            </div>
                        </div>
                        <a class="wpuf-pp-btn <?php echo esc_attr( $plan['featured'] ? 'wpuf-pp-btn--primary' : 'wpuf-pp-btn--outline' ); ?>" href="<?php echo esc_url( $wpuf_pricing_url ); ?>" target="_blank" rel="noopener" style="<?php echo esc_attr( $plan['featured'] ? 'justify-content:space-between;width:100%;' : '' ); ?>">
                            <?php esc_html_e( 'Buy Now', 'wp-user-frontend' ); ?>
                            <svg class="wpuf-pp-ico" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>
                        </a>
                        <p class="wpuf-pp-plan__everything"><?php echo esc_html( $plan['inherits'] ); ?></p>
                        <ul class="wpuf-pp-plan__features">
                            <?php foreach ( $plan['features'] as $feature ) : ?>
                                <li><svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg><?php echo esc_html( $feature ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="wpuf-pp-refund">
                <div class="wpuf-pp-refund__text">
                    <h3><?php esc_html_e( 'Our Fair Refund Policy', 'wp-user-frontend' ); ?></h3>
                    <p><?php esc_html_e( 'We guarantee 100% satisfaction with our help & support service. However, if our plugin still doesn’t meet your needs, we\'ll happily provide full refund within 14 days of your purchase.', 'wp-user-frontend' ); ?></p>
                    <div class="wpuf-pp-refund__pay">
                        <span><?php esc_html_e( 'Payment Options:', 'wp-user-frontend' ); ?></span>
                        <img src="<?php echo esc_url( $wpuf_asset . '/payment-logos.png' ); ?>" alt="<?php esc_attr_e( 'Accepted payment methods', 'wp-user-frontend' ); ?>">
                    </div>
                </div>
                <div class="wpuf-pp-refund__badge">
                    <img src="<?php echo esc_url( $wpuf_asset . '/refund-badge.png' ); ?>" alt="<?php esc_attr_e( '14 days money back guarantee', 'wp-user-frontend' ); ?>">
                </div>
            </div>
        </div>
    </section>

    <?php // 6. CTA banner. ?>
    <section class="wpuf-pp-section wpuf-pp-section--gray" style="padding-top:0;">
        <div class="wpuf-premium-page__inner">
            <div class="wpuf-pp-cta">
                <img class="wpuf-pp-cta__bg" src="<?php echo esc_url( $wpuf_asset . '/hero-crown.png' ); ?>" alt="">
                <div class="wpuf-pp-cta__text">
                    <h2><?php esc_html_e( 'Replace Multiple Plugins with One Powerful Frontend Solution', 'wp-user-frontend' ); ?></h2>
                    <p><?php esc_html_e( 'Build forms, manage users, create directories, run subscriptions, and monetize your site. Everything you need, all in one place.', 'wp-user-frontend' ); ?></p>
                </div>
                <a class="wpuf-pp-btn wpuf-pp-btn--primary" href="<?php echo esc_url( $wpuf_pricing_url ); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e( 'Upgrade to Pro', 'wp-user-frontend' ); ?>
                    <svg class="wpuf-pp-ico" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>
    </section>

</div>
