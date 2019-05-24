<?php

/**
 * Promotional offer class
 */
class WPUF_Admin_Promotion {

    public function __construct() {
        add_action( 'admin_notices', array( $this, 'promotional_offer' ) );
        add_action( 'admin_notices' , array( $this, 'wpuf_review_notice_message' ) );
        add_action( 'wp_ajax_wpuf-dismiss-promotional-offer-notice', array( $this, 'dismiss_promotional_offer' ) );
        add_action( 'wp_ajax_wpuf-dismiss-review-notice', array( $this, 'dismiss_review_notice' ) );
    }

    /**
     * Promotional offer notice
     *
     * @since 1.1.15
     *
     * @return void
     */
    public function promotional_offer() {
        // Show only to Admins
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // 2018-03-26 23:59:00
        if ( time() > 1543276740 ) {
            return;
        }

        // check if it has already been dismissed
        $hide_notice = get_option( 'wpuf_promotional_offer_notice', 'no' );

        if ( 'hide' == $hide_notice ) {
            return;
        }

        // $product_text = (  wpuf()->is_pro() ) ? __( 'Pro upgrade and all extensions, ', 'wpuf' ) : __( 'all extensions, ', 'wpuf' );

        // $offer_msg  = __( '<h2><span class="dashicons dashicons-awards"></span> weDevs 5th Birthday Offer</h2>', 'wpuf' );
        $offer_msg = __( '<p>
                                        <strong class="highlight-text" style="font-size: 18px">33&#37; flat discount on all our products</strong><br>
                                        Save money this holiday season while supercharging your WordPress site with plugins that were made to empower you.
                                        <br>
                                        Offer ending soon!
                                    </p>', 'wp-user-frontend' );

        ?>
            <div class="notice is-dismissible" id="wpuf-promotional-offer-notice">
                <table>
                    <tbody>
                        <tr>
                            <td class="image-container">
                                <img src="https://ps.w.org/wp-user-frontend/assets/icon-256x256.png" alt="">
                            </td>
                            <td class="message-container">
                                <?php echo $offer_msg; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <span class="dashicons dashicons-megaphone"></span>
                <a href="https://wedevs.com/coupons/?utm_campaign=black_friday_cyber_monday&utm_medium=banner&utm_source=inside_plugin" class="button button-primary promo-btn" target="_blank"><?php _e( 'Get the Offer', 'wp-user-frontend' ); ?></a>
            </div><!-- #wpuf-promotional-offer-notice -->

            <style>
                #wpuf-promotional-offer-notice {
                    background-image: url("<?php echo WPUF_ASSET_URI . '/images/promotional-offer/bg.png' ?>");
                    background-size: cover;
                    border: 0px;
                    padding: 0;
                    opacity: 0;
                }

                .wrap > #wpuf-promotional-offer-notice {
                    opacity: 1;
                }

                #wpuf-promotional-offer-notice table {
                    border-collapse: collapse;
                    width: 100%;
                }

                #wpuf-promotional-offer-notice table td {
                    padding: 0;
                }

                #wpuf-promotional-offer-notice table td.image-container {
                    background-color: #fff;
                    vertical-align: middle;
                    width: 95px;
                }


                #wpuf-promotional-offer-notice img {
                    max-width: 100%;
                    max-height: 100px;
                    vertical-align: middle;
                }

                #wpuf-promotional-offer-notice table td.message-container {
                    padding: 0 10px;
                }

                #wpuf-promotional-offer-notice h2{
                    color: rgba(250, 250, 250, 0.77);
                    margin-bottom: 10px;
                    font-weight: normal;
                    margin: 16px 0 14px;
                    -webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                }


                #wpuf-promotional-offer-notice h2 span {
                    position: relative;
                    top: 0;
                }

                #wpuf-promotional-offer-notice p{
                    color: rgba(250, 250, 250, 0.77);
                    font-size: 14px;
                    margin-bottom: 10px;
                    -webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    -o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                    text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
                }

                #wpuf-promotional-offer-notice p strong.highlight-text{
                    color: #fff;
                }

                #wpuf-promotional-offer-notice p a {
                    color: #fafafa;
                }

                #wpuf-promotional-offer-notice .notice-dismiss:before {
                    color: #fff;
                }

                #wpuf-promotional-offer-notice span.dashicons-megaphone {
                    position: absolute;
                    bottom: 46px;
                    right: 248px;
                    color: rgba(253, 253, 253, 0.29);
                    font-size: 96px;
                    transform: rotate(-21deg);
                }

                #wpuf-promotional-offer-notice a.promo-btn{
                    background: #fff;
                    border-color: #fafafa #fafafa #fafafa;
                    box-shadow: 0 1px 0 #fafafa;
                    color: #4caf4f;
                    text-decoration: none;
                    text-shadow: none;
                    position: absolute;
                    top: 30px;
                    right: 26px;
                    height: 40px;
                    line-height: 40px;
                    width: 130px;
                    text-align: center;
                    font-weight: 600;
                }

            </style>

            <script type='text/javascript'>
                jQuery('body').on('click', '#wpuf-promotional-offer-notice .notice-dismiss', function(e) {
                    e.preventDefault();

                    wp.ajax.post('wpuf-dismiss-promotional-offer-notice', {
                        dismissed: true
                    });
                });
            </script>
        <?php
    }

    /**
     *
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
        if ( 'yes' == $dismiss_notice ) {
            return;
        }

        if ( time() - $activation_time < 1296000 ) {
            return;
        }

        ?>
            <div id="wpuf-review-notice" class="wpuf-review-notice">
                <div class="wpuf-review-thumbnail">
                    <img src="<?php echo WPUF_ASSET_URI . '/images/icon-128x128.png' ?>" alt="">
                </div>
                <div class="wpuf-review-text">
                        <h3><?php _e( 'Enjoying <strong>WP User Frontend</strong>?', 'wp-user-frontend' ) ?></h3>
                        <p><?php _e( 'Hope that you had a neat and snappy experience with the tool. Would you please show us a little love by rating us in the <a href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/#new-post" target="_blank"><strong>WordPress.org</strong></a>?', 'wp-user-frontend' ) ?></p>

                    <ul class="wpuf-review-ul">
                        <li><a href="https://wordpress.org/support/plugin/wp-user-frontend/reviews/#new-post" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure! I\'d love to!', 'wp-user-frontend' ) ?></a></li>
                        <li><a href="#" class="notice-dismiss"><span class="dashicons dashicons-smiley"></span><?php _e( 'I\'ve already left a review', 'wp-user-frontend' ) ?></a></li>
                        <li><a href="#" class="notice-dismiss"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Never show again', 'wp-user-frontend' ) ?></a></li>
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
                        dismissed: true
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
        if ( ! empty( $_POST['dismissed'] ) ) {
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
        if ( ! empty( $_POST['dismissed'] ) ) {
            update_option( 'wpuf_review_notice_dismiss', 'yes' );
        }
    }
}
