<?php

/*
 * Ajax Address Form Class
 *
 */

class WPUF_Ajax_Address_Form {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ) );
        add_action( 'wp_ajax_wpuf_address_ajax_action', array( $this, 'ajax_form_action' ), 10, 1 );
    }

    /**
     * Enqueue scripts
     */
    public function register_plugin_scripts() {
        wp_enqueue_script( 'wpuf-ajax-script', plugins_url( 'assets/js/billing-address.js', dirname( __FILE__ ) ), array('jquery'), false );
        wp_localize_script( 'wpuf-ajax-script', 'ajax_object', array(  'ajaxurl' => admin_url( 'admin-ajax.php' ) )) ;
    }


    /**
     * Address Form
     */
    public static function wpuf_ajax_address_form() {
        $address_fields = wpuf_get_user_address();
        ?>

        <form class="wpuf-form form-label-above" id="wpuf-ajax-address-form" action="" method="post">
            <table id="wpuf-address-country-state" class="wp-list-table widefat">
                <tr>
                    <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;">
                        <label>Country<span class="required">*</span></label>
                        <br>
                        <?php
                        if ( function_exists( 'wpuf_get_tax_rates' ) ) {
                            $rates = wpuf_get_tax_rates();
                        }
                        $cs = new CountryState();
                        $states = array(); $selected = array();
                        $base_addr = get_option( 'wpuf_base_country_state', false );

                        $selected['country'] = !( empty( $address_fields['country'] ) ) ? $address_fields['country'] : $base_addr['country'];

                        echo wpuf_select( array(
                            'options'          => $cs->countries(),
                            'name'             => 'wpuf_biiling_country',
                            'selected'         => $selected['country'],
                            'show_option_all'  => false,
                            'show_option_none' => false,
                            'id'               => 'wpuf_biiling_country',
                            'class'            => 'wpuf_biiling_country',
                            'chosen'           => false,
                            'placeholder'      => __( 'Choose a country', 'wpuf' )
                        ) );
                        ?>
                    </td>
                    <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;">
                        <label>State/Province/Region<span class="required">*</span></label>
                        <br>
                        <?php
                        $states = $cs->getStates( $selected['country'] );
                        $selected['state'] = ! ( empty( $address_fields['state'] ) ) ? $address_fields['state'] : $base_addr['state'];
                        echo wpuf_select( array(
                            'options'          => $states,
                            'name'             => 'wpuf_biiling_state',
                            'selected'         => $selected['state'],
                            'show_option_all'  => false,
                            'show_option_none' => false,
                            'id'               => 'wpuf_biiling_state',
                            'class'            => 'wpuf_biiling_state',
                            'chosen'           => false,
                            'placeholder'      => __( 'Choose a state', 'wpuf' )
                        ) );
                        ?>
                    </td>
                    <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;">
                        <div class="wpuf-label"><?php _e( 'Address Line 1 ', 'wpuf' ); ?></div>
                        <div class="wpuf-fields">
                            <input type="text" class="input" name="wpuf_biiling_add_line_1" id="wpuf_biiling_add_line_1" value="<?php echo $address_fields['add_line_1']; ?>" />
                        </div>
                    </td>
                    <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;">
                        <div class="wpuf-label"><?php _e( 'Address Line 2 ', 'wpuf' ); ?></div>
                        <div class="wpuf-fields">
                            <input  type="text" class="input" name="wpuf_biiling_add_line_2" id="wpuf_biiling_add_line_2" value="<?php echo $address_fields['add_line_2']; ?>" />
                        </div>
                    </td>
                    <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;">
                        <div class="wpuf-label"><?php _e( 'City', 'wpuf' ); ?></div>
                        <div class="wpuf-fields">
                            <input  type="text" class="input" name="wpuf_biiling_city" id="wpuf_biiling_city" value="<?php echo $address_fields['city']; ?>" />
                        </div>
                    </td>
                    <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;">
                        <div class="wpuf-label"><?php _e( 'Postal Code/ZIP', 'wpuf' ); ?></div>
                        <div class="wpuf-fields">
                            <input  type="text" class="input" name="wpuf_biiling_zip_code" id="wpuf_biiling_zip_code" value="<?php echo $address_fields['zip_code']; ?>" />
                        </div>
                    </td>
                    <td class="wpuf-submit" style="display:none;">
                        <input type="submit" class="wpuf-btn" name="submit" id="wpuf-account-update-billing_address" value="<?php _e( 'Update Billing Address', 'wpuf' ); ?>" />
                    </td>
                </tr>

            </table>
            <div class="clear"></div>
        </form>

    <?php }


    /**
     * Ajax Form action
     */
    public function ajax_form_action() {
        if (isset($_POST)) {
            parse_str($_POST["data"], $_POST);

            $user_id = get_current_user_id();

            $address_fields     = array();

            if ( isset( $_POST['wpuf_biiling_add_line_1'] )
                && isset( $_POST['wpuf_biiling_city'] )
                && isset( $_POST['wpuf_biiling_state'] )
                && isset( $_POST['wpuf_biiling_zip_code'] )
                && isset( $_POST['wpuf_biiling_country'] ) ) {
                $address_fields = array(
                    'add_line_1'    => $_POST['wpuf_biiling_add_line_1'],
                    'add_line_2'    => $_POST['wpuf_biiling_add_line_2'],
                    'city'          => $_POST['wpuf_biiling_city'],
                    'state'         => $_POST['wpuf_biiling_state'],
                    'zip_code'      => $_POST['wpuf_biiling_zip_code'],
                    'country'       => $_POST['wpuf_biiling_country']
                );
                update_user_meta( $user_id, 'wpuf_address_fields', $address_fields );
                $msg = '<div class="wpuf-success">' . __( 'Billing address is updated.', 'wpuf' ) . '</div>';

                echo $msg;
                exit();
            }
        }
    }

}
