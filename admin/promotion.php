<?php

/**
 * Promotional offer class
 */
class WPUF_Admin_Promotion {

    public function __construct() {
        add_action( 'admin_notices', [ $this, 'promotional_offer' ] );
        add_action( 'admin_notices', [ $this, 'wpuf_review_notice_message' ] );
        add_action( 'wp_ajax_wpuf-dismiss-promotional-offer-notice', [ $this, 'dismiss_promotional_offer' ] );
        add_action( 'wp_ajax_wpuf-dismiss-review-notice', [ $this, 'dismiss_review_notice' ] );
    }

    /**
     * Promotional offer notice
     *
     * @since 1.1.15
     *
     * @return void
     */
    public function promotional_offer() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
            return;
        }

        // check if it has already been dismissed
        $offer_key        = 'wpuf_promotional_offer_notice';
        $offer_start_date = strtotime( '2019-11-20 00:00:01' );
        $offer_end_date   = strtotime( '2019-12-04 23:59:00' );
        $hide_notice      = get_option( $offer_key, 'show' );

        if ( 'hide' == $hide_notice ) {
            return;
        }

        if ( $offer_start_date < current_time( 'timestamp' ) && current_time( 'timestamp' ) < $offer_end_date ) {
            ?>
            <div class="notice notice-success is-dismissible" id="wpuf-bfcm-notice">
                <div class="logo">
                    <img src="<?php echo esc_url( WPUF_ASSET_URI ) . '/images/promo-logo.png'; ?>" alt="WPUF">
                </div>
                <div class="content">
                    <p>Biggest Sale of the year on this</p>

                    <h3><span class="highlight-green">Black Friday &amp; </span>Cyber Monday</h3>
                    <p><span class="highlight-lightgreen">Claim your discount on </span>WP User Frontend <span class="highlight-lightgreen">till 4th December</span></p>
                </div>
                <div class="call-to-action">
                    <a target="_blank" href="https://wedevs.com/wp-user-frontend-pro/pricing?utm_campaign=black_friday_&_cyber_monday&utm_medium=banner&utm_source=plugin_dashboard">
                        <img src="<?php echo esc_url( WPUF_ASSET_URI ) . '/images/promo-btn.png'; ?>" alt="Btn">
                    </a>
                    <p>
                        <span class="highlight-green2">Coupon: </span>
                        <span class="coupon-code">BFCM2019</span>
                    </p>
                </div>
            </div>

            <style>
                #wpuf-bfcm-notice {
                    font-size: 14px;
                    border-left: none;
                    background: #468E4B;
                    color: #fff;
                    display: flex
                }

                #wpuf-bfcm-notice .notice-dismiss:before {
                    color: #76E5FF;
                }

                #wpuf-bfcm-notice .notice-dismiss:hover:before {
                    color: #b71c1c;
                }

                #wpuf-bfcm-notice .logo {
                    text-align: center;
                    text-align: center;
                    margin: auto 50px;
                }

                #wpuf-bfcm-notice .logo img {
                    width: 80%;
                }

                #wpuf-bfcm-notice .highlight-green {
                    color: #4FFF67;
                }
                #wpuf-bfcm-notice .highlight-green2 {
                    color: #5AB035;
                }

                #wpuf-bfcm-notice .highlight-lightgreen {
                    color: #E0EFE7;
                }

                #wpuf-bfcm-notice .content {
                    margin-top: 5px;
                }

                #wpuf-bfcm-notice .content h3 {
                    color: #FFF;
                    margin: 12px 0 5px;
                    font-weight: normal;
                    font-size: 30px;
                }

                #wpuf-bfcm-notice .content p {
                    margin-top: 12px;
                    padding: 0;
                    letter-spacing: .4px;
                    color: #ffffff;
                    font-size: 15px;
                }

                #wpuf-bfcm-notice .call-to-action {
                    margin-left: 10%;
                    margin-top: 20px;
                }

                #wpuf-bfcm-notice .call-to-action a:focus {
                    box-shadow: none;
                }

                #wpuf-bfcm-notice .call-to-action p {
                    font-size: 16px;
                    color: #fff;
                    margin-top: 1px;
                    text-align: center;
                }

                #wpuf-bfcm-notice .coupon-code {
                    -moz-user-select: all;
                    -webkit-user-select: all;
                    user-select: all;
                }
            </style>
            </style>

            <script type='text/javascript'>
                jQuery('body').on('click', '#wpuf-bfcm-notice .notice-dismiss', function (e) {
                    e.preventDefault();

                    wp.ajax.post('wpuf-dismiss-promotional-offer-notice', {
                        dismissed: true,
                        _wpnonce: '<?php echo esc_attr ( wp_create_nonce( 'wpuf_nonce' ) ); ?>'
                    });
                });
            </script>
            <?php
        }
    }

    /**
     * @since 3.1.0
     *
     * @return void
     **/
    public function wpuf_review_notice_message() {
        // Show only to Admins
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }

        $dismiss_notice  = get_option( 'wpuf_review_notice_dismiss', 'no' );
        $activation_time = get_option( 'wpuf_installed' );

        // check if it has already been dismissed
        // and don't show notice in 15 days of installation, 1296000 = 15 Days in seconds
        if ( 'yes' == $dismiss_notice ) {
            return;
        }

        if ( time() - $activation_time < 1296000 ) {
            return;
        } ?>
            <div id="wpuf-review-notice" class="wpuf-review-notice">
                <div class="wpuf-review-thumbnail">
                    <img src="<?php echo esc_url( WPUF_ASSET_URI ) . '/images/icon-128x128.png'; ?>" alt="">
                </div>
                <div class="wpuf-review-text">
                        <h3><?php echo wp_kses_post( 'Enjoying WP User Frontend?', 'wp-user-frontend' ); ?></h3>
                        <p><?php echo wp_kses_post( 'Hope that you had a neat and snappy experience with the tool. Would you please show us a little love by rating us in the <a href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/#new-post" target="_blank"><strong>WordPress.org</strong></a>?', 'wp-user-frontend' ); ?></p>

                    <ul class="wpuf-review-ul">
                        <li><a href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/#new-post" target="_blank"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Sure! I\'d love to!', 'wp-user-frontend' ); ?></a></li>
                        <li><a href="#" class="notice-dismiss"><span class="dashicons dashicons-smiley"></span><?php esc_html_e( 'I\'ve already left a review', 'wp-user-frontend' ); ?></a></li>
                        <li><a href="#" class="notice-dismiss"><span class="dashicons dashicons-dismiss"></span><?php esc_html_e( 'Never show again', 'wp-user-frontend' ); ?></a></li>
                     </ul>
                </div>
            </div>
            <style type="text/css">
                #wpuf-review-notice .notice-dismiss{
                    padding: 0 0 0 26px;
                }

                #wpuf-review-notice .notice-dismiss:before{
                    display: none;
                }

                #wpuf-review-notice.wpuf-review-notice {
                    padding: 15px 15px 15px 0;
                    background-color: #fff;
                    border-radius: 3px;
                    margin: 20px 20px 0 0;
                    border-left: 4px solid transparent;
                }

                #wpuf-review-notice .wpuf-review-thumbnail {
                    width: 114px;
                    float: left;
                    line-height: 80px;
                    text-align: center;
                    border-right: 4px solid transparent;
                }

                #wpuf-review-notice .wpuf-review-thumbnail img {
                    width: 60px;
                    vertical-align: middle;
                }

                #wpuf-review-notice .wpuf-review-text {
                    overflow: hidden;
                }

                #wpuf-review-notice .wpuf-review-text h3 {
                    font-size: 24px;
                    margin: 0 0 5px;
                    font-weight: 400;
                    line-height: 1.3;
                }

                #wpuf-review-notice .wpuf-review-text p {
                    font-size: 13px;
                    margin: 0 0 5px;
                }

                #wpuf-review-notice .wpuf-review-ul {
                    margin: 0;
                    padding: 0;
                }

                #wpuf-review-notice .wpuf-review-ul li {
                    display: inline-block;
                    margin-right: 15px;
                }

                #wpuf-review-notice .wpuf-review-ul li a {
                    display: inline-block;
                    color: #82C776;
                    text-decoration: none;
                    padding-left: 26px;
                    position: relative;
                }

                #wpuf-review-notice .wpuf-review-ul li a span {
                    position: absolute;
                    left: 0;
                    top: -2px;
                }
            </style>
            <script type='text/javascript'>
                jQuery('body').on('click', '#wpuf-review-notice .notice-dismiss', function(e) {
                    e.preventDefault();
                    jQuery("#wpuf-review-notice").hide();

                    wp.ajax.post('wpuf-dismiss-review-notice', {
                        dismissed: true,
                        _wpnonce: '<?php echo esc_attr ( wp_create_nonce( 'wpuf_nonce' ) ); ?>'
                    });
                });
            </script>
        <?php
    }

    /**
     * Dismiss promotion notice
     *
     * @since  2.5
     *
     * @return void
     */
    public function dismiss_promotional_offer() {
        if( empty( $_POST['_wpnonce'] ) ) {
             wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'wpuf_nonce' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( !empty( $_POST['dismissed'] ) ) {
            $offer_key = 'wpuf_promotional_offer_notice';
            update_option( $offer_key, 'hide' );
        }
    }

    /**
     * Dismiss review notice
     *
     * @since  3.1.0
     *
     * @return void
     **/
    public function dismiss_review_notice() {
        if( empty( $_POST['_wpnonce'] ) ) {
             wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'wpuf_nonce' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( !empty( $_POST['dismissed'] ) ) {
            update_option( 'wpuf_review_notice_dismiss', 'yes' );
        }
    }
}
