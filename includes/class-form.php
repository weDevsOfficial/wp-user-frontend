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

	public function get_settings() {
		$form_settings = wpuf_get_form_settings( $this->id );
		return $form_settings;

	}

	public function guest_post() {
		return 'false';
	}

	public function is_charging_enabled() {
		$settings = $this->get_settings();
	
		if ( isset( $settings['payment_options']) && $settings['payment_options'] == 'true' ) {
			return true;
		}

		return false;
	}

	public function is_enabled_pay_per_post() {
		$settings = $this->get_settings();

		if ( isset( $settings['enable_pay_per_post']) && $settings['enable_pay_per_post'] == 'true' ) {
			return true;
		}

		return false;
	}

	public function is_enabled_force_pack() {
		$settings = $this->get_settings();

		if ( isset( $settings['force_pack_purchase']) && $settings['force_pack_purchase'] == 'true' ) {
			return true;
		}

		return false;
	}

	public function get_pay_per_post_cost() {
		$settings = $this->get_settings();
		
		if ( isset( $settings['pay_per_post_cost']) && $settings['pay_per_post_cost'] > 0 ) {
			return $settings['pay_per_post_cost'];
		}

		return 0;
	}

}