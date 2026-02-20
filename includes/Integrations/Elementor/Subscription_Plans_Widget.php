<?php
/**
 * DESCRIPTION: Elementor widget for displaying WPUF subscription plans
 *
 * @package WPUF\Elementor
 */

namespace WeDevs\Wpuf\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Subscription_Plans_Widget extends Widget_Base {

	/**
	 * Retrieve the widget name
	 *
	 * @since WPUF_SINCE
	 *
	 * @return string Widget name
	 */
	public function get_name() {
		return 'wpuf-subscription-plans';
	}

	/**
	 * Retrieve the widget title
	 *
	 * @since WPUF_SINCE
	 *
	 * @return string Widget title
	 */
	public function get_title() {
		return __( 'Subscription Plans', 'wp-user-frontend' );
	}

	/**
	 * Retrieve the widget icon
	 *
	 * @since WPUF_SINCE
	 *
	 * @return string Widget icon
	 */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/**
	 * Retrieve the list of widget categories
	 *
	 * @since WPUF_SINCE
	 *
	 * @return array Widget categories
	 */
	public function get_categories() {
		return [ 'user-frontend' ];
	}

	/**
	 * Retrieve the list of keywords associated with the widget
	 *
	 * @since WPUF_SINCE
	 *
	 * @return array Widget keywords
	 */
	public function get_keywords() {
		return [ 'wpuf', 'subscription', 'plans', 'pricing', 'pack' ];
	}

	/**
	 * Retrieve the list of styles the widget depends on
	 *
	 * @since WPUF_SINCE
	 *
	 * @return array
	 */
	public function get_style_depends() {
		return [ 'wpuf-elementor-subscription-plans' ];
	}

	/**
	 * Retrieve the list of scripts the widget depends on
	 *
	 * @since WPUF_SINCE
	 *
	 * @return array
	 */
	public function get_script_depends() {
		return [ 'wpuf-elementor-subscription-plans' ];
	}

	/**
	 * Register widget controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_controls() {
		// Content Controls
		$this->register_content_controls();

		// Style Controls
		$this->register_container_style_controls();
		$this->register_plan_name_style_controls();
		$this->register_price_style_controls();
		$this->register_trial_description_style_controls();
		$this->register_features_list_style_controls();
		$this->register_button_style_controls();
	}

	/**
	 * Register content controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_content_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content', 'wp-user-frontend' ),
			]
		);

		$this->add_control(
			'plans_per_row',
			[
				'label'   => __( 'Plans Per Row', 'wp-user-frontend' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => [
					'1' => __( '1', 'wp-user-frontend' ),
					'2' => __( '2', 'wp-user-frontend' ),
					'3' => __( '3', 'wp-user-frontend' ),
					'4' => __( '4', 'wp-user-frontend' ),
				],
			]
		);

		$this->add_control(
			'order_by',
			[
				'label'       => __( 'Order By', 'wp-user-frontend' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'custom',
				'description' => __( 'Choose how to sort the subscription plans. Custom Order uses the sort order set in each plan\'s settings.', 'wp-user-frontend' ),
				'options'     => [
					'custom'     => __( 'Custom Order', 'wp-user-frontend' ),
					'id'         => __( 'Plan ID', 'wp-user-frontend' ),
					'price_asc'  => __( 'Price (Low to High)', 'wp-user-frontend' ),
					'price_desc' => __( 'Price (High to Low)', 'wp-user-frontend' ),
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register container style controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_container_style_controls() {
		$this->start_controls_section(
			'section_container_style',
			[
				'label' => __( 'Container', 'wp-user-frontend' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label'      => __( 'Container Width', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [ 'min' => 100, 'max' => 1500 ],
					'%'  => [ 'min' => 10, 'max' => 100 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-subscription-plans-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'container_bg_color',
			[
				'label'     => __( 'Background Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-card' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'          => 'card_border',
				'selector'      => '{{WRAPPER}} .wpuf-sub-card',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '1',
							'right'    => '1',
							'bottom'   => '1',
							'left'     => '1',
							'isLinked' => true,
						],
					],
					'color'  => [
						'default' => '#e5e7eb',
					],
				],
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label'      => __( 'Border Radius', 'wp-user-frontend' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '12',
					'right'    => '12',
					'bottom'   => '12',
					'left'     => '12',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .wpuf-sub-card',
			]
		);

		$this->add_responsive_control(
			'card_padding',
			[
				'label'      => __( 'Card Padding', 'wp-user-frontend' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'      => '24',
					'right'    => '24',
					'bottom'   => '24',
					'left'     => '24',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'card_margin',
			[
				'label'      => __( 'Card Margin', 'wp-user-frontend' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'cards_gap',
			[
				'label'      => __( 'Gap Between Cards', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
				'default'    => [
					'size' => '20',
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-subscription-plans-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register plan name style controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_plan_name_style_controls() {
		$this->start_controls_section(
			'section_plan_name_style',
			[
				'label' => __( 'Plan Name', 'wp-user-frontend' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'plan_name_color',
			[
				'label'     => __( 'Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-plan-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'plan_name_typography',
				'selector' => '{{WRAPPER}} .wpuf-sub-plan-name',
				'fields_options' => [
					'font_size' => [
						'default' => [
							'size' => '24',
							'unit' => 'px',
						],
					],
					'font_weight' => [
						'default' => '700',
					],
				],
			]
		);

		$this->add_responsive_control(
			'plan_name_align',
			[
				'label'     => __( 'Text Alignment', 'wp-user-frontend' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-plan-name' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'plan_name_margin_bottom',
			[
				'label'      => __( 'Margin Bottom', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [
					'size' => '16',
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-plan-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register price style controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_price_style_controls() {
		$this->start_controls_section(
			'section_price_style',
			[
				'label' => __( 'Price', 'wp-user-frontend' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'price_color',
			[
				'label'     => __( 'Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'currency_color',
			[
				'label'     => __( 'Currency Symbol Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6b7280',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-currency' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'billing_cycle_color',
			[
				'label'     => __( 'Billing Cycle Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#6b7280',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-billing-cycle' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} .wpuf-sub-price',
				'fields_options' => [
					'font_size' => [
						'default' => [
							'size' => '36',
							'unit' => 'px',
						],
					],
					'font_weight' => [
						'default' => '700',
					],
				],
			]
		);

		$this->add_responsive_control(
			'price_align',
			[
				'label'     => __( 'Text Alignment', 'wp-user-frontend' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-price-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'price_margin_bottom',
			[
				'label'      => __( 'Margin Bottom', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [
					'size' => '20',
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-price-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register trial description style controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_trial_description_style_controls() {
		$this->start_controls_section(
			'section_trial_style',
			[
				'label' => __( 'Trial Description', 'wp-user-frontend' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'trial_color',
			[
				'label'     => __( 'Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#059669',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-trial-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'trial_typography',
				'selector' => '{{WRAPPER}} .wpuf-sub-trial-description',
				'fields_options' => [
					'font_size' => [
						'default' => [
							'size' => '14',
							'unit' => 'px',
						],
					],
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register features list style controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_features_list_style_controls() {
		$this->start_controls_section(
			'section_features_style',
			[
				'label' => __( 'Features List', 'wp-user-frontend' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'features_color',
			[
				'label'     => __( 'Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4b5563',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-features-list' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Icon Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#10b981',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-feature-icon' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => __( 'Icon Size', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 50 ] ],
				'default'    => [
					'size' => '20',
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-feature-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'features_typography',
				'selector' => '{{WRAPPER}} .wpuf-sub-features-list',
				'fields_options' => [
					'font_size' => [
						'default' => [
							'size' => '14',
							'unit' => 'px',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'features_line_spacing',
			[
				'label'      => __( 'Line Spacing', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'default'    => [
					'size' => '12',
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-feature-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'features_margin_bottom',
			[
				'label'      => __( 'Margin Bottom', 'wp-user-frontend' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-features-list' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Register button style controls
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function register_button_style_controls() {
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => __( 'Subscribe Button', 'wp-user-frontend' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'button_typography',
				'selector' => '{{WRAPPER}} .wpuf-sub-button',
				'fields_options' => [
					'font_size' => [
						'default' => [
							'size' => '16',
							'unit' => 'px',
						],
					],
					'font_weight' => [
						'default' => '600',
					],
				],
			]
		);

		$this->add_control(
			'button_align',
			[
				'label'     => __( 'Alignment', 'wp-user-frontend' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-left' ],
					'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-center' ],
					'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-button-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab( 'tab_button_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

		$this->add_control(
			'button_text_color',
			[
				'label'     => __( 'Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_bg_color',
			[
				'label'     => __( 'Background Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'button_border',
				'selector' => '{{WRAPPER}} .wpuf-sub-button',
				'fields_options' => [
					'border' => [
						'default' => 'solid',
					],
					'width'  => [
						'default' => [
							'top'      => '1',
							'right'    => '1',
							'bottom'   => '1',
							'left'     => '1',
							'isLinked' => true,
						],
					],
					'color'  => [
						'default' => 'transparent',
					],
				],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label'      => __( 'Border Radius', 'wp-user-frontend' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '5',
					'right'    => '5',
					'bottom'   => '5',
					'left'     => '5',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label'      => __( 'Padding', 'wp-user-frontend' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'default'    => [
					'top'      => '5',
					'right'    => '20',
					'bottom'   => '5',
					'left'     => '20',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wpuf-sub-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_button_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

		$this->add_control(
			'button_hover_text_color',
			[
				'label'     => __( 'Text Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_bg_color',
			[
				'label'     => __( 'Background Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => __( 'Border Color', 'wp-user-frontend' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wpuf-sub-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Retrieve subscription plans based on widget settings
	 *
	 * @since WPUF_SINCE
	 *
	 * @param array $settings Widget settings.
	 * @return array Array of subscription packs
	 */
	protected function get_subscription_plans( $settings ) {
		$args = [
			'post_type'      => 'wpuf_subscription',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		$order_by = isset( $settings['order_by'] ) ? $settings['order_by'] : 'custom';

		switch ( $order_by ) {
			case 'id':
				$args['orderby'] = 'ID';
				$args['order']   = 'ASC';
				break;
			case 'price_asc':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_billing_amount';
				$args['order']    = 'ASC';
				break;
			case 'price_desc':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_billing_amount';
				$args['order']    = 'DESC';
				break;
			case 'custom':
			default:
				$args['meta_key'] = '_sort_order';
				$args['orderby']  = [ 'meta_value_num' => 'ASC', 'title' => 'ASC' ];
				break;
		}

		$packs = get_posts( $args );

		if ( ! empty( $packs ) ) {
			foreach ( $packs as $key => $post ) {
				$packs[ $key ]->meta_value = wpuf()->subscription->get_subscription_meta( $post->ID, $post );
			}
		}

		return $packs;
	}

	/**
	 * Get the CSS class for plans per row
	 *
	 * @since WPUF_SINCE
	 *
	 * @param string $plans_per_row Number of plans per row.
	 * @return string CSS class
	 */
	protected function get_plans_per_row_class( $plans_per_row ) {
		return 'wpuf-sub-plans-' . $plans_per_row;
	}

	/**
	 * Render the widget output on the frontend
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$packs = $this->get_subscription_plans( $settings );

		$is_elementor_editor = $this->is_editor_render();

		// Show empty state in editor if no plans
		if ( $is_elementor_editor && empty( $packs ) ) {
			$this->render_empty_state();
			return;
		}

		// Show nothing in frontend if no plans
		if ( ! $is_elementor_editor && empty( $packs ) ) {
			return;
		}

		$plans_per_row = isset( $settings['plans_per_row'] ) ? $settings['plans_per_row'] : '3';
		$grid_class     = $this->get_plans_per_row_class( $plans_per_row );

		$this->add_render_attribute(
			'wrapper',
			[
				'class' => [ 'wpuf-subscription-plans-wrapper', $grid_class ],
			]
		);

		$user_id          = get_current_user_id();
		$current_pack     = wpuf()->subscription->get_user_pack( $user_id );
		$current_pack_id  = isset( $current_pack['pack_id'] ) ? $current_pack['pack_id'] : '';
		$current_pack_status = isset( $current_pack['status'] ) ? $current_pack['status'] : '';

		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';
		echo '<div class="wpuf-subscription-plans-grid">';

		foreach ( $packs as $pack ) :
			$this->render_plan_card( $pack, $current_pack_id, $current_pack_status );
		endforeach;

		echo '</div>';
		echo '</div>';

		if ( $is_elementor_editor ) {
			$this->render_toggle_script();
		}
	}

	/**
	 * Render empty state for Elementor editor
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function render_empty_state() {
		echo '<div class="wpuf-subscription-plans-wrapper">';
		echo '<div class="wpuf-subscription-empty-state">';
		echo '<div class="wpuf-subscription-empty-icon">';
		echo '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
		echo '<path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM15.5 11C16.33 11 17 10.33 17 9.5C17 8.67 16.33 8 15.5 8C14.67 8 14 8.67 14 9.5C14 10.33 14.67 11 15.5 11ZM8.5 11C9.33 11 10 10.33 10 9.5C10 8.67 9.33 8 8.5 8C7.67 8 7 8.67 7 9.5C7 10.33 7.67 11 8.5 11ZM12 18C14.33 18 16.31 16.28 16.89 14H7.11C7.69 16.28 9.67 18 12 18Z" fill="currentColor"/>';
		echo '</svg>';
		echo '</div>';
		echo '<p class="wpuf-subscription-empty-text">' . esc_html__( 'No subscription plans available.', 'wp-user-frontend' ) . '</p>';
		echo '<p class="wpuf-subscription-empty-hint">' . esc_html__( 'Create a subscription plan in WPUF settings.', 'wp-user-frontend' ) . '</p>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render a single plan card
	 *
	 * @since WPUF_SINCE
	 *
	 * @param \WP_Post $pack              The subscription plan post object.
	 * @param string   $current_pack_id   Current active pack ID.
	 * @param string   $current_pack_status Current pack status.
	 * @return void
	 */
	protected function render_plan_card( $pack, $current_pack_id, $current_pack_status ) {
		$settings               = $this->get_settings_for_display();
		$billing_amount         = isset( $pack->meta_value['billing_amount'] ) ? floatval( $pack->meta_value['billing_amount'] ) : 0;
		$recurring_pay          = isset( $pack->meta_value['recurring_pay'] ) ? $pack->meta_value['recurring_pay'] : 'no';
		$trial_status           = isset( $pack->meta_value['trial_status'] ) ? $pack->meta_value['trial_status'] : 'no';
		$billing_cycle_number   = isset( $pack->meta_value['billing_cycle_number'] ) ? $pack->meta_value['billing_cycle_number'] : 1;
		$cycle_period           = isset( $pack->meta_value['cycle_period'] ) ? $pack->meta_value['cycle_period'] : 'month';
		$trial_duration         = isset( $pack->meta_value['trial_duration'] ) ? $pack->meta_value['trial_duration'] : 0;
		$trial_duration_type    = isset( $pack->meta_value['trial_duration_type'] ) ? $pack->meta_value['trial_duration_type'] : 'day';

		$this->add_render_attribute(
			'card-' . $pack->ID,
			[
				'class' => [ 'wpuf-sub-card', 'wpuf-sub-card-' . $pack->ID ],
			]
		);

		echo '<div ' . $this->get_render_attribute_string( 'card-' . $pack->ID ) . '>';

		// Plan Name
		echo '<h3 class="wpuf-sub-plan-name">' . esc_html( $pack->post_title ) . '</h3>';

		// Description
		if ( ! empty( $pack->post_content ) ) {
			echo '<div class="wpuf-sub-description">' . wp_kses_post( wpautop( $pack->post_content ) ) . '</div>';
		}

		// Price Section
		echo '<div class="wpuf-sub-price-wrapper">';
		if ( $billing_amount > 0 ) {
			$currency_symbol = wpuf_get_currency( 'symbol' );
			$amount_formatted = number_format( $billing_amount, 2, '.', '' );

			echo '<div class="wpuf-sub-price">';
			echo '<span class="wpuf-sub-currency">' . esc_html( $currency_symbol ) . '</span>';
			echo '<span class="wpuf-sub-amount">' . esc_html( $amount_formatted ) . '</span>';

			if ( $recurring_pay === 'yes' ) {
				$cycle_label = wpuf()->subscription->get_cycle_label( $cycle_period, $billing_cycle_number );
				$billing_text = sprintf( __( 'Every %s %s', 'wp-user-frontend' ), $billing_cycle_number, $cycle_label );
				echo '<span class="wpuf-sub-billing-cycle"> ' . esc_html( $billing_text ) . '</span>';
			}
			echo '</div>';
		} else {
			echo '<div class="wpuf-sub-price">' . esc_html__( 'Free', 'wp-user-frontend' ) . '</div>';
		}
		echo '</div>';

		// Trial Description
		if ( $billing_amount > 0 && $recurring_pay === 'yes' && $trial_status === 'yes' && $trial_duration > 0 ) {
			$duration = _n( $trial_duration_type, $trial_duration_type . 's', $trial_duration, 'wp-user-frontend' );
			$trial_text = sprintf( __( 'Trial available for first %1$s %2$s', 'wp-user-frontend' ), $trial_duration, $duration );
			echo '<div class="wpuf-sub-trial-description">' . esc_html( $trial_text ) . '</div>';
		}

		// Button
		$this->render_button( $pack, $current_pack_id, $current_pack_status, $billing_amount );

		// Features List
		$this->render_features_list( $pack );

		echo '</div>';
	}

	/**
	 * Render the subscribe button
	 *
	 * @since WPUF_SINCE
	 *
	 * @param \WP_Post $pack              The subscription plan post object.
	 * @param string   $current_pack_id   Current active pack ID.
	 * @param string   $current_pack_status Current pack status.
	 * @param float    $billing_amount    The billing amount.
	 * @return void
	 */
	protected function render_button( $pack, $current_pack_id, $current_pack_status, $billing_amount ) {
		$user_id = get_current_user_id();

		$is_completed = ( $current_pack_id == $pack->ID && 'completed' === $current_pack_status );

		// Get button labels from WPUF settings
		if ( ! is_user_logged_in() ) {
			$button_text = wpuf_get_option( 'logged_out_label', 'wpuf_subscription_settings', '' );
			$button_text = $button_text ? $button_text : __( 'Sign Up', 'wp-user-frontend' );
		} elseif ( $billing_amount === 0.0 || $billing_amount === '0.00' ) {
			$button_text = wpuf_get_option( 'free_label', 'wpuf_subscription_settings', '' );
			$button_text = $button_text ? $button_text : __( 'Free', 'wp-user-frontend' );
		} else {
			$button_text = wpuf_get_option( 'logged_in_label', 'wpuf_subscription_settings', '' );
			$button_text = $button_text ? $button_text : __( 'Buy Now', 'wp-user-frontend' );
		}

		// Build button URL
		if ( ! is_user_logged_in() ) {
			$query_args = [
				'action'  => 'register',
				'type'    => 'wpuf_sub',
				'pack_id' => $pack->ID,
			];
			$button_url = wp_registration_url();
		} else {
			$query_args = [
				'action'  => 'wpuf_pay',
				'type'    => 'pack',
				'pack_id' => $pack->ID,
			];
			$button_url = get_permalink( wpuf_get_option( 'payment_page', 'wpuf_payment' ) );
		}

		$button_url = add_query_arg( $query_args, $button_url );

		$this->add_render_attribute(
			'button-' . $pack->ID,
			[
				'class' => [ 'wpuf-sub-button', 'wpuf-sub-button-' . $pack->ID ],
				'href'  => esc_url( $button_url ),
			]
		);

		if ( $is_completed ) {
			$this->add_render_attribute( 'button-' . $pack->ID, 'href', 'javascript:void(0)' );
			$this->add_render_attribute( 'button-' . $pack->ID, 'class', 'wpuf-sub-button-disabled' );
		}

		echo '<div class="wpuf-sub-button-wrapper">';
		echo '<a ' . $this->get_render_attribute_string( 'button-' . $pack->ID ) . '>' . esc_html( $button_text ) . '</a>';
		echo '</div>';
	}

	/**
	 * Render the features list for a plan
	 *
	 * @since WPUF_SINCE
	 *
	 * @param \WP_Post $pack The subscription plan post object.
	 * @return void
	 */
	protected function render_features_list( $pack ) {
		$features_list = [];

		// Check if custom features are set
		if ( isset( $pack->meta_value['features'] ) && is_array( $pack->meta_value['features'] ) && ! empty( $pack->meta_value['features'] ) ) {
			foreach ( $pack->meta_value['features'] as $feature ) {
				$features_list[] = esc_html( $feature );
			}
		} else {
			// Build features from subscription settings
			$post_type_limits = isset( $pack->meta_value['_post_type_name'] ) ? maybe_unserialize( $pack->meta_value['_post_type_name'] ) : [];
			$additional_cpt = isset( $pack->meta_value['additional_cpt_options'] ) ? maybe_unserialize( $pack->meta_value['additional_cpt_options'] ) : [];
			$all_limits = array_merge( (array) $post_type_limits, (array) $additional_cpt );

			// Posts limit
			if ( isset( $all_limits['post'] ) && '0' !== $all_limits['post'] ) {
				if ( '-1' === $all_limits['post'] ) {
					$features_list[] = __( 'Unlimited posts', 'wp-user-frontend' );
				} else {
					$features_list[] = sprintf( __( '%d posts allowed', 'wp-user-frontend' ), intval( $all_limits['post'] ) );
				}
			}

			// Pages limit
			if ( isset( $all_limits['page'] ) && '0' !== $all_limits['page'] ) {
				if ( '-1' === $all_limits['page'] ) {
					$features_list[] = __( 'Unlimited pages', 'wp-user-frontend' );
				} else {
					$features_list[] = sprintf( __( '%d pages allowed', 'wp-user-frontend' ), intval( $all_limits['page'] ) );
				}
			}

			// Featured items limit
			if ( isset( $pack->meta_value['_total_feature_item'] ) && '0' !== $pack->meta_value['_total_feature_item'] ) {
				if ( '-1' === $pack->meta_value['_total_feature_item'] ) {
					$features_list[] = __( 'Unlimited featured items', 'wp-user-frontend' );
				} else {
					$features_list[] = sprintf( __( '%d featured items', 'wp-user-frontend' ), intval( $pack->meta_value['_total_feature_item'] ) );
				}
			}

			// Recurring payment
			if ( isset( $pack->meta_value['recurring_pay'] ) && 'yes' === $pack->meta_value['recurring_pay'] ) {
				$features_list[] = __( 'Recurring subscription', 'wp-user-frontend' );
			}

			// Trial period
			if ( isset( $pack->meta_value['trial_status'] ) && 'yes' === $pack->meta_value['trial_status'] ) {
				$trial_duration = isset( $pack->meta_value['trial_duration'] ) ? intval( $pack->meta_value['trial_duration'] ) : 0;
				$trial_type = isset( $pack->meta_value['trial_duration_type'] ) ? $pack->meta_value['trial_duration_type'] : 'day';

				if ( $trial_duration > 0 ) {
					$features_list[] = sprintf( __( '%d %s free trial', 'wp-user-frontend' ), $trial_duration, $trial_type . ( $trial_duration > 1 ? 's' : '' ) );
				} else {
					$features_list[] = __( 'Free trial available', 'wp-user-frontend' );
				}
			}

			// If no features found, show a basic feature
			if ( empty( $features_list ) ) {
				$features_list[] = __( 'Full website access', 'wp-user-frontend' );
			}
		}

		// Render features list
		if ( ! empty( $features_list ) ) {
			$features_count = count( $features_list );
			$initial_display_count = 5;
			$pack_id = $pack->ID;

			echo '<ul class="wpuf-sub-features-list" id="wpuf-sub-features-list-' . esc_attr( $pack_id ) . '">';

			foreach ( $features_list as $index => $feature ) {
				$is_hidden = $index >= $initial_display_count ? ' wpuf-sub-feature-hidden' : '';

				echo '<li class="wpuf-sub-feature-item' . esc_attr( $is_hidden ) . '">';
				echo '<svg class="wpuf-sub-feature-icon" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">';
				echo '<path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />';
				echo '</svg>';
				echo '<span>' . esc_html( $feature ) . '</span>';
				echo '</li>';
			}

			echo '</ul>';

			// Show See More/Less button if features exceed initial display count
			if ( $features_count > $initial_display_count ) {
				$hidden_count = $features_count - $initial_display_count;

				echo '<div class="wpuf-sub-features-toggle-wrapper">';
				echo '<button type="button" class="wpuf-sub-features-toggle wpuf-sub-features-see-more" data-pack-id="' . esc_attr( $pack_id ) . '" data-expanded="false">';
				printf( esc_html__( 'See %d more features', 'wp-user-frontend' ), intval( $hidden_count ) );
				echo '</button>';
				echo '<button type="button" class="wpuf-sub-features-toggle wpuf-sub-features-see-less" data-pack-id="' . esc_attr( $pack_id ) . '" data-expanded="true" style="display: none;">';
				esc_html_e( 'See less', 'wp-user-frontend' );
				echo '</button>';
				echo '</div>';
			}
		}
	}

	/**
	 * Render toggle script for expandable features
	 *
	 * @since WPUF_SINCE
	 *
	 * @return void
	 */
	protected function render_toggle_script() {
		?>
		<script>
		(function() {
			// Handle feature toggle buttons
			document.addEventListener('click', function(e) {
				if (e.target.classList.contains('wpuf-sub-features-toggle')) {
					e.preventDefault();
					var button = e.target;
					var packId = button.getAttribute('data-pack-id');
					var isExpanded = button.getAttribute('data-expanded') === 'true';
					var featuresList = document.getElementById('wpuf-sub-features-list-' + packId);
					var seeMoreBtn = featuresList.parentElement.querySelector('.wpuf-sub-features-see-more');
					var seeLessBtn = featuresList.parentElement.querySelector('.wpuf-sub-features-see-less');
					var hiddenItems = featuresList.querySelectorAll('.wpuf-sub-feature-hidden');

					if (isExpanded) {
						// Collapse
						hiddenItems.forEach(function(item) {
							item.style.display = 'none';
						});
						seeMoreBtn.style.display = '';
						seeLessBtn.style.display = 'none';
						button.setAttribute('data-expanded', 'false');
					} else {
						// Expand
						hiddenItems.forEach(function(item) {
							item.style.display = 'flex';
						});
						seeMoreBtn.style.display = 'none';
						seeLessBtn.style.display = '';
						seeLessBtn.setAttribute('data-expanded', 'true');
					}
				}
			});
		})();
		</script>
		<?php
	}

	/**
	 * Check if rendering in Elementor editor
	 *
	 * @since WPUF_SINCE
	 *
	 * @return bool
	 */
	protected function is_editor_render() {
		return class_exists( '\Elementor\Plugin' ) && (
			( isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() )
			|| ( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() )
		);
	}

	/**
	 * Render the widget output in the editor
	 *
	 * When empty, Elementor will fall back to using the render() method for
	 * both frontend and editor preview.
	 *
	 * @since WPUF_SINCE
	 *
	 * @access protected
	 */
	protected function content_template() {}
}
