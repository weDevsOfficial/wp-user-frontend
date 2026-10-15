<?php
/**
 * Modules Preview (free plugin only)
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Free;

/**
 * The Modules page of the free plugin: the Pro modules previewed, the free module switches and their AJAX.
 *
 * @since WPUF_SINCE Moved out of Free_Loader (same Pro_Prompt base), which keeps the hooks and delegates.
 */
class Modules_Preview extends Pro_Prompt {

    /**
     * The loader: callbacks of other groups are registered on it.
     *
     * @var Free_Loader
     */
    protected $loader;

    /**
     * @since WPUF_SINCE
     *
     * @param Free_Loader $loader The loader.
     */
    public function __construct( Free_Loader $loader ) {
        $this->loader = $loader;
    }

    public function module_menu_action() {
        /**
         * Backdoor for calling the menu hook.
         * This hook won't get translated even the site language is changed
         */
        do_action( 'wpuf_load_module_page' );
    }

    /**
     * A preview page to show the Pro Modules of WPUF
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function modules_preview_page() {
        $pro_modules  = $this->pro_modules_info();
        $free_modules = wpuf_free_get_modules();

        $diamond_icon = file_exists( WPUF_ROOT . '/assets/images/diamond.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/diamond.svg' ) : '';
        $check_icon   = file_exists( WPUF_ROOT . '/assets/images/check.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/check.svg' ) : '';
        $crown_icon   = file_exists( WPUF_ROOT . '/assets/images/pro-badge.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/pro-badge.svg' ) : '';
        $close_icon   = file_exists( WPUF_ROOT . '/assets/images/x.svg' ) ? file_get_contents( WPUF_ROOT . '/assets/images/x.svg' ) : '';
        $suffix       = '.min';

        ?>
        <div id="wpuf-upgrade-popup" class="wpuf-popup-window">
            <div class="modal-window">
                <div class="modal-window-inner">
                    <div class="content-area">
                        <div class="popup-close-button">
                            <?php
                            echo wp_kses(
                                $close_icon, array(
									'svg' => [
										'xmlns' => true,
										'width' => true,
										'height' => true,
										'viewBox' => true,
										'fill' => true,
									],
									'path' => [
										'd' => true,
										'fill' => true,
									],
                                )
                            );
							?>
                        </div>
                        <div class="popup-diamond">
                            <?php
                            echo wp_kses(
                                $diamond_icon, array(
									'svg' => [
										'xmlns' => true,
										'width' => true,
										'height' => true,
										'viewBox' => true,
										'fill' => true,
									],
									'path' => [
										'd' => true,
										'fill' => true,
										'stroke' => true,
										'stroke-linecap' => true,
									],
                                )
                            );
							?>
                        </div>
                        <div class="wpuf-popup-header">
                            <h2 class="font-orange header-one">Upgrade to</h2>
                            <h2 class="header-two">WP User Frontend <span class="font-bold">Pro</span></h2>
                            <h2 class="header-three font-gray">to experience even more powerful<span class="line-break"></span>features 🎉</h2>
                        </div>
                        <div class="wpuf-popup-list-area">
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php
                                    echo wp_kses(
                                        $check_icon, array(
											'svg' => [
												'xmlns' => true,
												'width' => true,
												'height' => true,
												'viewBox' => true,
												'fill' => true,
											],
											'path' => [
												'd' => true,
												'fill' => true,
												'fill-rule' => true,
												'clip-rule' => true,
											],
                                        )
                                    );
									?>
                                </div>
                                <div class="check-list">
                                    <p>Get custom <span class="bold font-black">Post Type</span> and <span class="bold font-black">Taxonomy</span> support with
                                        <span class="line-break"></span> subscription-based <span class="bold font-black">restrictions</span> for post <span class="line-break"></span> submission.</p>
                                </div>
                            </div>
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php
                                    echo wp_kses(
                                        $check_icon, array(
											'svg' => [
												'xmlns' => true,
												'width' => true,
												'height' => true,
												'viewBox' => true,
												'fill' => true,
											],
											'path' => [
												'd' => true,
												'fill' => true,
												'fill-rule' => true,
												'clip-rule' => true,
											],
                                        )
                                    );
									?>
                                </div>
                                <div class="check-list">
                                    <p>Enable <span class="bold font-black">conditional logic</span> and <span class="bold font-black">multi-step</span><span class="line-break"></span> functionalities on your forms.</p>
                                </div>
                            </div>
                            <div class="single-checklist">
                                <div class="check-icon">
                                    <?php
                                    echo wp_kses(
                                        $check_icon, array(
											'svg' => [
												'xmlns' => true,
												'width' => true,
												'height' => true,
												'viewBox' => true,
												'fill' => true,
											],
											'path' => [
												'd' => true,
												'fill' => true,
												'fill-rule' => true,
												'clip-rule' => true,
											],
                                        )
                                    );
									?>
                                </div>
                                <div class="check-list">
                                    <p>Show or hide <span class="bold font-black">menus, pages,</span> and <span class="bold font-black">content</span> based on<span class="line-break"></span> user roles or login status of a user.</p>
                                </div>
                            </div>
                            <div class="single-checklist">
                                <div class="check-icon">
                                <?php
                                echo wp_kses(
                                    $check_icon, array(
										'svg' => [
											'xmlns' => true,
											'width' => true,
											'height' => true,
											'viewBox' => true,
											'fill' => true,
										],
										'path' => [
											'd' => true,
											'fill' => true,
											'fill-rule' => true,
											'clip-rule' => true,
										],
                                    )
                                );
								?>
                                </div>
                                <div class="check-list">
                                    <p><span class="bold font-black">20+ Premium Modules</span> (Social Login, User<span class="line-break"></span> Directory, User Activity, Stripe, MailChimp, Private<span class="line-break"></span> Messaging, Zapier, & more)</p>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo esc_url( self::get_upgrade_to_pro_popup_url() ); ?>"
                            target="_blank"
                            class="wpuf-button button-upgrade-to-pro">
                            <?php esc_html_e( 'Upgrade to PRO', 'wp-user-frontend' ); ?>
                            <?php
                            printf(
                                '<span class="pro-icon"> %s</span>', wp_kses(
                                    $crown_icon, array(
										'svg' => [
											'xmlns' => true,
											'width' => true,
											'height' => true,
											'viewBox' => true,
											'fill' => true,
										],
										'path' => [
											'd' => true,
											'fill' => true,
										],
										'circle' => [
											'cx' => true,
											'cy' => true,
											'r' => true,
										],
                                    )
                                )
                            );
							?>
                        </a>
                    </div>
                    <div class="slider-area">
                        <div class="wpuf-slider slider-indicators-outside slider-indicators-round slider-nav-mousedrag slider-nav-autoplay slider-nav-autopause"" id="wpuf-slider">
                        <div class="swiffy-slider">
                            <ul class="slider-container">
                                <li><img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/woocommerce-form-template.png' ); ?>"></li>
                                <li><img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/conditional-form.png' ); ?>"></li>
                                <li><img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/content-restriction.png' ); ?>"></li>
                                <li><img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/modules.png' ); ?>"></li>
                            </ul>

                            <div class="slider-indicators">
                                <button class="active"></button>
                                <button></button>
                                <button></button>
                                <button></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="footer-feature">
                    <p>
                        <?php
                        echo wp_kses(
                            $check_icon, array(
								'svg' => [
									'xmlns' => true,
									'width' => true,
									'height' => true,
									'viewBox' => true,
									'fill' => true,
								],
								'path' => [
									'd' => true,
									'fill' => true,
									'fill-rule' => true,
									'clip-rule' => true,
								],
                            )
                        );
						?>
                        Industry leading 24x7 support
                    </p>
                    <p>
                        <?php
                        echo wp_kses(
                            $check_icon, array(
								'svg' => [
									'xmlns' => true,
									'width' => true,
									'height' => true,
									'viewBox' => true,
									'fill' => true,
								],
								'path' => [
									'd' => true,
									'fill' => true,
									'fill-rule' => true,
									'clip-rule' => true,
								],
                            )
                        );
						?>
                        14 days no questions asked refund policy
                    </p>
                    <p>
                        <?php
                        echo wp_kses(
                            $check_icon, array(
								'svg' => [
									'xmlns' => true,
									'width' => true,
									'height' => true,
									'viewBox' => true,
									'fill' => true,
								],
								'path' => [
									'd' => true,
									'fill' => true,
									'fill-rule' => true,
									'clip-rule' => true,
								],
                            )
                        );
						?>
                        Secured payment
                    </p>

                </div>
            </div>
        </div>
        </div>
        <div class="wrap wpuf-modules">
            <h1><?php esc_attr_e( 'Modules', 'wp-user-frontend' ); ?></h1>

            <?php // Free Modules Section ?>
            <?php if ( $free_modules ) : ?>
            <h2 class="wpuf-modules-section-title">
                <?php esc_html_e( 'Available Modules', 'wp-user-frontend' ); ?>
            </h2>
            <div class="wp-list-table widefat wpuf-modules wpuf-free-modules">
                <?php
                foreach ( $free_modules as $module_id => $module ) {
                    $is_active = wpuf_free_is_module_active( $module_id );
                    ?>
                    <div class="plugin-card">
                        <div class="plugin-card-top">
                            <div class="name column-name">
                                <h3>
                                    <span class="plugin-name"><a href="<?php echo esc_url( $module['plugin_uri'] ); ?>" target="_blank"><?php echo esc_html( $module['name'] ); ?></a></span>
                                    <a href="<?php echo esc_url( $module['plugin_uri'] ); ?>" target="_blank"><img class="plugin-icon" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/modules/' . $module['thumbnail'] ); ?>" alt="" /></a>
                                </h3>
                            </div>

                            <div class="action-links">
                                <ul class="plugin-action-buttons">
                                    <li data-module="<?php echo esc_attr( $module_id ); ?>">
                                        <label class="wpuf-toggle-switch">
                                            <input type="checkbox" name="module_toggle" class="wpuf-toggle-free-module" data-module="<?php echo esc_attr( $module_id ); ?>" <?php checked( $is_active ); ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </li>
                                </ul>
                                <div class="wpuf-doc-link"><a href="<?php echo esc_url( $module['plugin_uri'] ); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'wp-user-frontend' ); ?></a></div>
                            </div>

                            <div class="desc column-description">
                                <p>
                                    <?php echo esc_html( $module['description'] ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php endif; ?>

            <?php // Pro Modules Preview Section ?>
            <h2 class="wpuf-modules-section-title wpuf-pro-modules-title">
                <?php esc_html_e( 'Pro Modules', 'wp-user-frontend' ); ?>
                <span class="wpuf-pro-badge">
                    <?php esc_html_e( '(Upgrade to Pro to unlock)', 'wp-user-frontend' ); ?>
                </span>
            </h2>
            <div class="wp-list-table widefat wpuf-modules wpuf-pro-modules-preview">
                <?php
                if ( $pro_modules ) {
                    foreach ( $pro_modules as $slug => $module ) {
                        ?>
                        <div class="plugin-card">
                            <div class="plugin-card-top">
                                <div class="name column-name">
                                    <h3>
                                        <span class="plugin-name"><a href="<?php echo esc_url( $module['plugin_uri'] ); ?>" target="_blank"><?php echo esc_html( $module['name'] ); ?></a></span>
                                        <a href="<?php echo esc_url( $module['plugin_uri'] ); ?>" target="_blank"><img class="plugin-icon" src="<?php echo esc_url( WPUF_ASSET_URI . '/images/modules/' . $module['thumbnail'] ); ?>" alt="" /></a>
                                    </h3>
                                </div>

                                <div class="action-links">
                                    <ul class="plugin-action-buttons">
                                        <li data-module="<?php echo esc_attr( $slug ); ?>">
                                            <label class="wpuf-toggle-switch">
                                                <input type="checkbox" name="module_toggle" class="wpuf-toggle-module" disabled>
                                                <span class="slider round"></span>
                                            </label>
                                        </li>
                                    </ul>
                                    <div class="wpuf-doc-link" ><a href="<?php echo esc_url( $module['plugin_uri'] ); ?>" target="_blank"><?php esc_html_e( 'Documentation', 'wp-user-frontend' ); ?></a></div>
                                </div>

                                <div class="desc column-description">
                                    <p>
                                        <?php echo esc_html( $module['description'] ); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="form-create-overlay">
                                <a href="https://wedevs.com/wp-user-frontend-pro/pricing/?utm_source=freeplugin&amp;utm_medium=prompt&amp;utm_term=wpuf_free_plugin&amp;utm_content=textlink&amp;utm_campaign=pro_prompt" target="_blank">
                                    <img src="<?php echo esc_url( WPUF_ASSET_URI . '/images/pro-badge.svg' ); ?>" alt="pro icon" class="wpuf-module-pro-badge">
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Load required style and js for Modules page
     *
     * @since 3.6.0
     *
     * @return void
     */
    public function load_modules_scripts() {
        wp_enqueue_style( 'wpuf-admin' );
        wp_enqueue_style( 'wpuf-module' );
        wp_enqueue_style( 'wpuf-swiffy-slider' );
        wp_enqueue_script( 'wpuf-admin' );
        wp_enqueue_script( 'wpuf-subscriptions' );
        wp_enqueue_script( 'wpuf-swiffy-slider' );
        wp_enqueue_script( 'wpuf-swiffy-slider-extensions' );
        wp_enqueue_script( 'wpuf-module' );

        // Localize script for free module toggle
        wp_localize_script(
            'wpuf-module', 'wpuf_free_modules', [
				'rest'    => wpuf_rest_script_data(),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpuf_toggle_free_module' ),
			]
        );
    }

    /**
     * Handle AJAX request to toggle a free module on/off
     *
     * @since 4.3.0
     *
     * @return void
     */
    public function toggle_free_module() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wpuf_toggle_free_module' ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed', 'wp-user-frontend' ) ] );
        }

        // Check permission
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied', 'wp-user-frontend' ) ] );
        }

        $module_id = isset( $_POST['module'] ) ? sanitize_text_field( wp_unslash( $_POST['module'] ) ) : '';
        $status    = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

        if ( empty( $module_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid module', 'wp-user-frontend' ) ] );
        }

        // Check if module exists
        $modules = wpuf_free_get_modules();
        if ( ! isset( $modules[ $module_id ] ) ) {
            wp_send_json_error( [ 'message' => __( 'Module not found', 'wp-user-frontend' ) ] );
        }

        if ( 'active' === $status ) {
            $result = wpuf_free_activate_module( $module_id );
        } else {
            $result = wpuf_free_deactivate_module( $module_id );
        }

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        wp_send_json_success(
            [
				'message' => 'active' === $status
					? __( 'Module activated successfully', 'wp-user-frontend' )
					: __( 'Module deactivated successfully', 'wp-user-frontend' ),
			]
        );
    }

    /**
     * Get the info of the pro modules as an array
     *
     * @since 3.6.0
     *
     * @return string[][]
     */
    public function pro_modules_info() {
        return [
            'campaign-monitor/campaign-monitor.php' => [
                'name'        => 'Campaign Monitor',
                'description' => 'Subscribe a contact to Campaign Monitor when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/campaign-monitor/',
                'thumbnail'   => 'campaign_monitor.svg',
            ],
            'social-login/wpuf-social-login.php' => [
                'name'        => 'Social Login & Registration',
                'description' => 'Add Social Login and registration feature in WP User Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/social-login-registration/',
                'thumbnail'   => 'Social-Media-Login.svg',
            ],
            'bp-profile/wpuf-bp.php' => [
                'name'        => 'BuddyPress Profile',
                'description' => 'Register and upgrade user profiles and sync data with BuddyPress',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/buddypress-profile-integration/',
                'thumbnail'   => 'wpuf-buddypress.svg',
            ],
            'comments/comments.php' => [
                'name'        => 'Comments Manager',
                'description' => 'Handle comments in frontend',
                'plugin_uri'  => 'https://wedevs.com/wp-user-frontend-pro/modules/comments-manager/',
                'thumbnail'   => 'wpuf-comment.svg',
            ],
            'mailpoet/wpuf-mailpoet.php' => [
                'name'        => 'Mailpoet',
                'description' => 'Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/mailpoet/',
                'thumbnail'   => 'wpuf-mailpoet.svg',
            ],
            'pmpro/wpuf-pmpro.php' => [
                'name'        => 'Paid Membership Pro Integration',
                'description' => 'Membership Integration of WP User Frontend PRO with Paid Membership Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/install-and-configure-pmpro-add-on-for-wpuf/',
                'thumbnail'   => 'wpuf-pmpro.svg',
            ],
            'sms-notification/wpuf-sms.php' => [
                'name'        => 'SMS Notification',
                'description' => 'SMS notification for post',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/sms-notification/',
                'thumbnail'   => 'wpuf-sms.svg',
            ],
            'email-templates/email-templates.php' => [
                'name'        => 'HTML Email Templates',
                'description' => 'Send Email Notifications with HTML Template',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/html-email-templates/',
                'thumbnail'   => 'email-templates.svg',
            ],
            'getresponse/getresponse.php' => [
                'name'        => 'GetResponse',
                'description' => 'Subscribe a contact to GetResponse when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/get-response/',
                'thumbnail'   => 'getresponse.svg',
            ],
            'zapier/zapier.php' => [
                'name'        => 'Zapier',
                'description' => 'Subscribe a contact to Zapier when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/zapier/',
                'thumbnail'   => 'zapier.svg',
            ],
            'convertkit/convertkit.php' => [
                'name'        => 'ConvertKit',
                'description' => 'Subscribe a contact to ConvertKit when a form is submited',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/convertkit/',
                'thumbnail'   => 'convertkit.svg',
            ],
            'private-message/private-message.php' => [
                'name'        => 'Private Message',
                'description' => 'User to user message from Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/private-messaging/',
                'thumbnail'   => 'message.svg',
            ],
            'user-analytics/wpuf-user-analytics.php' => [
                'name'        => 'User Analytics',
                'description' => 'Show user tracking info during post and registration from Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-analytics/',
                'thumbnail'   => 'wpuf-ua.svg',
            ],
            'mailchimp/wpuf-mailchimp.php' => [
                'name'        => 'Mailchimp',
                'description' => 'Add subscribers to Mailchimp mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/add-users-to-mailchimp-subscribers-list-upon-registration-from-frontend/',
                'thumbnail'   => 'wpuf-mailchimp.svg',
            ],
            'user-activity/user_activity.php' => [
                'name'        => 'User Activity',
                'description' => 'Handle user activity in frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/user-activity/',
                'thumbnail'   => 'wpuf-activity.svg',
            ],
            'report/wpuf-report.php' => [
                'name'        => 'Reports',
                'description' => 'Show various reports in WP User Frontend menu',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/reports/',
                'thumbnail'   => 'reports.svg',
            ],
            'qr-code-field/wpuf-qr-code.php' => [
                'name'        => 'QR Code',
                'description' => 'Post Qr code generator plugin',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/qr-code/',
                'thumbnail'   => 'wpuf-qr.svg',
            ],
            'mailpoet3/wpuf-mailpoet-3.php' => [
                'name'        => 'Mailpoet 3',
                'description' => 'Add subscribers to mailpoet mailing list when they registers via WP User Frontend Pro',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/mailpoet3/',
                'thumbnail'   => 'mailpoet3.svg',
            ],
            'stripe/wpuf-stripe.php' => [
                'name'        => 'Stripe Payment',
                'description' => 'Stripe payment gateway for WP User Frontend',
                'plugin_uri'  => 'https://wedevs.com/docs/wp-user-frontend-pro/modules/stripe/',
                'thumbnail'   => 'wpuf-stripe.svg',
            ],
            'seo/wpuf-seo.php' => [
                'name'        => 'SEO Settings',
                'description' => 'SEO settings for user directory and profiles',
                'plugin_uri'  => 'https://wedevs.com/products/plugins/wp-user-frontend-pro/seo-settings/',
                'thumbnail'   => 'wpuf-seo.svg',
            ],
        ];
    }

    /**
     * The content of the module page
     *
     * @since 3.6.0
     *
     * @param array $modules
     *
     * @return void
     */
    public function modules_page_contents() {
    }

}
