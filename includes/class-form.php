<?php

class WPUF_Form {

    /**
     * The form ID
     * 
     * @var integer
     */
    public $id;

    public function __construct( $form ) {
        
        if ( is_numeric( $form ) ) {
            
            $this->id   = $form;
            $this->data = get_post( $form );

        } elseif ( is_a( $form, 'WP_Post' )) {
            
            $this->id   = $form->ID;
            $this->data = $form;
        }
    }

    /**
     * Get the form settings
     *
     * @return boolean
     */
    public function get_settings() {
        $form_settings = wpuf_get_form_settings( $this->id );
        return $form_settings;

    }

    /**
     * Get guest post settings 
     *
     * @return boolean
     */
    public function guest_post() {
        $settings = $this->get_settings();
        if ( isset( $form_settings['guest_post'] ) && $form_settings['guest_post'] == 'true' ) {
            return true;
        }

        return false;
    }

    /**
     * Check if payment is enabled 
     *
     * @return boolean
     */
    public function is_charging_enabled() {
        $settings = $this->get_settings();
    
        if ( isset( $settings['payment_options'] ) && $settings['payment_options'] == 'true' ) {
            return true;
        }

        return false;
    }

    /**
     * Check if pay per post is enabled 
     *
     * @return boolean
     */
    public function is_enabled_pay_per_post() {
        $settings = $this->get_settings();

        if ( isset( $settings['enable_pay_per_post'] ) && $settings['enable_pay_per_post'] == 'true' ) {
            return true;
        }

        return false;
    }

    /**
     * Check if subscription pack is forced 
     *
     * @return boolean
     */
    public function is_enabled_force_pack() {
        $settings = $this->get_settings();

        if ( isset( $settings['force_pack_purchase'] ) && $settings['force_pack_purchase'] == 'true' ) {
            return true;
        }

        return false;
    }

    /**
     * Get pay per cost amount 
     *
     * @return integer
     */
    public function get_pay_per_post_cost() {
        $settings = $this->get_settings();
        
        if ( isset( $settings['pay_per_post_cost'] ) && $settings['pay_per_post_cost'] > 0 ) {
            return $settings['pay_per_post_cost'];
        }

        return 0;
    }

    /**
     * Check if fallback cost after subscription pack expiration is enabled 
     *
     * @return boolean
     */
    public function is_enabled_fallback_cost() {
        $settings = $this->get_settings();

        if ( isset( $settings['fallback_ppp_enable'] ) && $settings['fallback_ppp_enable'] == 'true' ) {
            return true;
        }

        return false;
    }

    /**
     * Get the fallback cost amount 
     *
     * @return integer
     */
    public function get_subs_fallback_cost() {
        $settings = $this->get_settings();
        
        if ( isset( $settings['fallback_ppp_cost'] ) && $settings['fallback_ppp_cost'] > 0 ) {
            return $settings['fallback_ppp_cost'];
        }

        return 0;
    }

}