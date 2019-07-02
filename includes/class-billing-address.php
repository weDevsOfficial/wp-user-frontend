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
        global $post;
        $pay_page = intval( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );

        if ( wpuf_get_option( 'load_script', 'wpuf_general', 'on' ) == 'on' ) {
            $this->plugin_scripts();
        } elseif ( isset( $post->ID  ) && ( $pay_page == $post->ID ) ) {
            $this->plugin_scripts();
        }
    }

    /**
     * Load billing scripts
     */
    public function plugin_scripts() {
        wp_enqueue_script( 'wpuf-ajax-script', plugins_url( 'assets/js/billing-address.js', dirname( __FILE__ ) ), array('jquery'), false );
        wp_localize_script( 'wpuf-ajax-script', 'ajax_object', array(  'ajaxurl' => admin_url( 'admin-ajax.php' ), 'fill_notice' => __( 'Some Required Fields are not filled!', 'wp-user-frontend' ) )  ) ;
    }

    /**
     * Address Form
     */
    public static function wpuf_ajax_address_form() {
        $address_fields = wpuf_get_user_address();
        $show_address   = wpuf_get_option( 'show_address', 'wpuf_address_options', false );
        $show_country   = wpuf_get_option( 'country', 'wpuf_address_options', false );
        $show_state     = wpuf_get_option( 'state', 'wpuf_address_options', false );
        $show_add1      = wpuf_get_option( 'address_1', 'wpuf_address_options', false );
        $show_add2      = wpuf_get_option( 'address_2', 'wpuf_address_options', false );
        $show_city      = wpuf_get_option( 'city', 'wpuf_address_options', false );
        $show_zip       = wpuf_get_option( 'zip', 'wpuf_address_options', false );

        $required_class = 'bill_required';
        $req_div        = '<span class="required">*</span>';

        $country_req = ''; $country_hide = ''; $state_req = ''; $state_hide = ''; $add1_req = ''; $add1_hide = '';
        $add2_req = ''; $add2_hide = ''; $city_req = ''; $city_hide = ''; $zip_req = ''; $zip_hide = ''; $required = '';

        if ( $show_country == 'hidden' ) {
            $show_state = 'hidden';
        }

        switch ( $show_country ) {
            case 'required':
                $country_required  = true;
                break;
            case 'hidden':
                $country_hide = 'display: none;';
            default:
                break;
        }
        switch ( $show_state ) {
            case 'required':
                $state_required  = true;
                break;
            case 'hidden':
                $state_hide   = 'display: none;';
            default:
                break;
        }
        switch ( $show_add1 ) {
            case 'required':
                $address1_required  = true;
                break;
            case 'hidden':
                $add1_hide    = 'display: none;';
            default:
                break;
        }
        switch ( $show_add2 ) {
            case 'required':
                $address2_required     = true;
                break;
            case 'hidden':
                $add2_hide    = 'display: none;';
            default:
                break;
        }
        switch ( $show_city ) {
            case 'required':
                $city_required = true;
                break;
            case 'hidden':
                $city_hide    = 'display: none;';
            default:
                break;
        }
        switch ( $show_zip ) {
            case 'required':
                $zip_required = true;
                break;
            case 'hidden':
                $zip_hide = 'display: none;';
            default:
                break;
        }

        if ( $show_address ) {
            ?>

            <form class="wpuf-form form-label-above" id="wpuf-ajax-address-form" action="" method="post">
                <table id="wpuf-address-country-state" class="wp-list-table widefat">
                    <tr>
                        <td class="<?php echo isset( $country_required ) ? $required_class : null; ?>" style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo $country_hide; ?>">
                            <label><?php _e( "Country", "wp-user-frontend" ); ?><?php echo isset( $country_required ) ? $req_div : null; ?></label>
                            <br>
                            <?php
                            if (function_exists('wpuf_get_tax_rates')) {
                                $rates = wpuf_get_tax_rates();
                            }
                            $cs = new CountryState();
                            $states = array();
                            $selected = array();
                            $base_addr = get_option('wpuf_base_country_state', false);

                            $selected['country'] = !(empty($address_fields['country'])) ? $address_fields['country'] : $base_addr['country'];

                            echo wpuf_select(array(
                                'options' => $cs->countries(),
                                'name' => 'wpuf_biiling_country',
                                'selected' => $selected['country'],
                                'show_option_all' => false,
                                'show_option_none' => false,
                                'id' => 'wpuf_biiling_country',
                                'class' => 'wpuf_biiling_country',
                                'chosen' => false,
                                'placeholder' => __('Choose a country', 'wp-user-frontend')
                            ));
                            ?>
                        </td>
                        <td class="<?php echo isset( $state_required ) ? $required_class : null; ?>" style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo $state_hide;?>">
                            <label><?php _e( "State/Province/Region", "wp-user-frontend" ); ?><?php echo isset( $state_required ) ? $req_div : null; ?></label>
                            <br>
                            <?php
                            $states = $cs->getStates($selected['country']);
                            $selected['state'] = !(empty($address_fields['state'])) ? $address_fields['state'] : $base_addr['state'];
                            echo wpuf_select(array(
                                'options' => $states,
                                'name' => 'wpuf_biiling_state',
                                'selected' => $selected['state'],
                                'show_option_all' => false,
                                'show_option_none' => false,
                                'id' => 'wpuf_biiling_state',
                                'class' => 'wpuf_biiling_state',
                                'chosen' => false,
                                'placeholder' => __('Choose a state', 'wp-user-frontend')
                            ));
                            ?>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo $add1_hide;?>">
                            <div class="wpuf-label"><?php _e('Address Line 1 ', 'wp-user-frontend'); ?><?php echo isset( $address1_required ) ? $req_div : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text" class="input <?php echo isset( $address1_required ) ? $required_class : null; ?>" name="wpuf_biiling_add_line_1"
                                       id="wpuf_biiling_add_line_1"
                                       value="<?php echo $address_fields['add_line_1']; ?>"/>
                            </div>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo $add2_hide;?>">
                            <div class="wpuf-label"><?php _e('Address Line 2 ', 'wp-user-frontend'); ?><?php echo isset( $address2_required ) ? $req_div : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text" class="input <?php echo isset( $address2_required ) ? $required_class : null; ?>" name="wpuf_biiling_add_line_2"
                                       id="wpuf_biiling_add_line_2"
                                       value="<?php echo $address_fields['add_line_2']; ?>"/>
                            </div>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo $city_hide; ?>">
                            <div class="wpuf-label"><?php _e('City', 'wp-user-frontend'); ?><?php echo isset( $city_required ) ? $req_div : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text" class="input <?php echo isset( $city_required ) ? $required_class : null; ?>" name="wpuf_biiling_city" id="wpuf_biiling_city"
                                       value="<?php echo $address_fields['city']; ?>"/>
                            </div>
                        </td>
                        <td style="display:inline-block;float:left;width:100%;margin:0px;padding:5px;<?php echo $zip_hide; ?>">
                            <div class="wpuf-label"><?php _e('Postal Code/ZIP', 'wp-user-frontend'); ?><?php echo isset( $zip_required ) ? $req_div : null; ?></div>
                            <div class="wpuf-fields">
                                <input type="text" class="input <?php echo isset( $zip_required ) ? $required_class : null; ?>" name="wpuf_biiling_zip_code" id="wpuf_biiling_zip_code"
                                       value="<?php echo $address_fields['zip_code']; ?>"/>
                            </div>
                        </td>
                        <td class="<?php echo $required; ?>" class="wpuf-submit" style="display:none;">
                            <input type="submit" class="wpuf-btn" name="submit" id="wpuf-account-update-billing_address"
                                   value="<?php _e('Update Billing Address', 'wp-user-frontend'); ?>"/>
                        </td>
                    </tr>

                </table>
                <div class="clear"></div>
            </form>

        <?php
        }
    }


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

                $msg = '<div class="wpuf-success">' . __( 'Billing address is updated.', 'wp-user-frontend' ) . '</div>';

                echo $msg;
                exit();
            }
        }
    }

}
