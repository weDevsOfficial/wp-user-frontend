<?php

/**
 * Promotional offer class
 */
class WPUF_Admin_Promotion {
    const PROMO_KEY = 'wpuf_promo_notices';

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
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        // Showing only for Uf menu
        if ( 'wp-user-frontend' !== get_admin_page_parent() ) {
            return;
        }

        $promos = get_transient( self::PROMO_KEY );

        if ( false === $promos ) {
            $promo_notice_url = 'https://raw.githubusercontent.com/weDevsOfficial/wpuf-util/master/promotion.json';
            $response         = wp_remote_get( $promo_notice_url, array( 'timeout' => 15 ) );
            $promos           = wp_remote_retrieve_body( $response );

            if ( is_wp_error( $response ) || $response['response']['code'] !== 200 ) {
                $promos = '[]';
            }

            set_transient( self::PROMO_KEY, $promos, DAY_IN_SECONDS );
        }

        $promos = json_decode( $promos, true );

        if ( empty( $promos ) || ! is_array( $promos ) ) {
            return;
        }

        $option_name = $promos['key'];
        $start_date  = $promos['start_date'];
        $end_date    = $promos['end_date'];
        $content     = $promos['content'] . '.' . $promos['title'];
        $action_url  = $promos['action_url'];
        $action_title = $promos['action_title'];

        $current_time = $this->convert_utc_to_est();

        if (
            strtotime( $start_date ) < strtotime( $current_time )
            && strtotime( $current_time ) < strtotime( $end_date )
            ) {
            $this->generate_notice( $promos );
        }
    }

    /**
     * Convert EST Time zone to UTC timezone
     *
     * @param string $date_time
     * @return string
     */
    public function convert_utc_to_est() {
        $dt = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
        $dt->setTimezone( new DateTimeZone( 'EST' ) );

        return $dt->format( 'Y-m-d H:i:s T' );
    }

    /**
     * @since 3.1.0
     *
     * @return void
     **/
    public function wpuf_review_notice_message() {
        // Show only to Admins
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $dismiss_notice  = get_option( 'wpuf_review_notice_dismiss', 'no' );
        $activation_time = get_option( 'wpuf_installed' );

        // check if it has already been dismissed
        // and don't show notice in 15 days of installation, 1296000 = 15 Days in seconds
        if ( 'yes' === $dismiss_notice ) {
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
                        _wpnonce: '<?php echo esc_attr( wp_create_nonce( 'wpuf_nonce' ) ); ?>'
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
        if ( empty( $_POST['_wpnonce'] ) ) {
             wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'wpuf_nonce' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! empty( $_POST['dismissed'] ) ) {
            $offer_key = ! empty( $_POST['option_name'] ) ? sanitize_text_field( wp_unslash( $_POST['option_name'] ) ) : '';
            update_option( $offer_key, 'hide' );
        }
    }

    /**
     * Show admin notice
     *
     * @since WPUF @param $message and @param $option_name replaced with $args
     *
     * @return void
     */
    public function generate_notice( $args ) {
        $option_name  = ! empty( $args['key'] ) ? $args['key'] : '';
        $content      = ! empty( $args['content'] ) ? $args['content'] : '';
        $title        = ! empty( $args['title'] ) ? $args['title'] : '';
        $action_url   = ! empty( $args['action_url'] ) ? $args['action_url'] : '';
        $action_title = ! empty( $args['action_title'] ) ? $args['action_title'] : '';
        $hide_notice  = get_option( $option_name, 'no' );

        if ( 'hide' === $hide_notice ) {
            return;
        }
        ?>
        <div class="notice notice-success wpuf-notice" id="wpuf-bfcm-notice">
            <div class="wpuf-logo-wrapper">
                <img src="<?php echo WPUF_ASSET_URI . '/images/icon-128x128.png'; ?>" alt="WPUF Icon">
            </div>
            <div class="wpuf-notice-content-wrapper">
                <h3><?php echo esc_html( $title ); ?></h3>
                <p><b><?php echo esc_html( $content ); ?></b></p>
                <a href="<?php echo esc_url_raw( $action_url ); ?>" class="button button-primary"><?php echo esc_html( $action_title ); ?></a>
            </div>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_attr_e( 'Dismiss this notice.', 'wp-user-frontend' ); ?></span></button>
        </div>
        <style>
            .wpuf-notice {
                position: relative;
                display: flex;
                padding: 0;
            }
            .wpuf-logo-wrapper {
                display: flex;
                margin-right: 20px;
            }
            .wpuf-notice-content-wrapper h3 {
                margin-bottom: 0.5em;
            }
        </style>

        <script type='text/javascript'>
            jQuery('body').on('click', '#wpuf-bfcm-notice .notice-dismiss', function (e) {
                e.preventDefault();
                jQuery('#wpuf-bfcm-notice').remove();
                wp.ajax.post('wpuf-dismiss-promotional-offer-notice', {
                    dismissed: true,
                    option_name: '<?php echo esc_html( $option_name ); ?>',
                    _wpnonce: '<?php echo esc_attr( wp_create_nonce( 'wpuf_nonce' ) ); ?>'
                });
            });
        </script>
        <?php
    }

    /**
     * Dismiss review notice
     *
     * @since  3.1.0
     *
     * @return void
     **/
    public function dismiss_review_notice() {
        if ( empty( $_POST['_wpnonce'] ) ) {
             wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( isset( $_POST['_wpnonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'wpuf_nonce' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'wp-user-frontend' ) );
        }

        if ( ! empty( $_POST['dismissed'] ) ) {
            update_option( 'wpuf_review_notice_dismiss', 'yes' );
        }
    }
}
