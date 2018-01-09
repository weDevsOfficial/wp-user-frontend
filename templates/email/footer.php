<?php
/**
 * Email Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        </table>

                        <!-- Footer -->
                        <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
                            <tr>
                                <td valign="top">
                                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                        <tr>
                                            <td colspan="2" valign="middle" id="credit">
                                                <?php
                                                $settings = get_option( 'wpuf_mails' );
                                                if ( is_array( $settings ) && ( isset( $settings['footer_text'] ) && !empty( $settings['footer_text'] ) ) ) {
                                                    $footer_text = $settings['footer_text'];
                                                }

                                                $footer_text = empty( $footer_text ) ? sprintf( '&copy; %s. Powered by <a href="%s">WP User Frontend</a>.', get_bloginfo( 'name', 'display' ), 'https://wordpress.org/plugins/wp-user-frontend/' ) : $footer_text;
                                                $footer_text = wpautop( wp_kses_post( wptexturize( apply_filters( 'wpuf_email_footer_text', $footer_text ) ) ) );

                                                echo $footer_text;

                                                do_action( 'wpuf_email_after_footer' );
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <!-- End Footer -->
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
