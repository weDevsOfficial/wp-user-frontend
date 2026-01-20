<?php

namespace WeDevs\Wpuf\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class Widget extends Widget_Base {

    public function get_name() {
        return 'wpuf-form';
    }

    public function get_title() {
        return __( 'User Frontend Form', 'wp-user-frontend' );
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'wp-user-frontend' ),
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label'   => __( 'Select Form', 'wp-user-frontend' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_wpuf_forms(),
            ]
        );

        $this->end_controls_section();

        // Style Tab
        $this->register_container_style_controls();
        $this->register_label_style_controls();
        $this->register_input_style_controls();
        $this->register_submit_button_style_controls();
    }

    protected function get_wpuf_forms() {
        $forms = get_posts( [
            'post_type'      => 'wpuf_forms',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ] );

        $options = [ '' => __( 'Select a Form', 'wp-user-frontend' ) ];

        if ( ! empty( $forms ) ) {
            foreach ( $forms as $form ) {
                $options[ $form->ID ] = $form->post_title;
            }
        }

        return $options;
    }

    protected function register_container_style_controls() {
        $this->start_controls_section(
            'section_container_style',
            [
                'label' => __( 'Form Container', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'container_background',
                'label'    => __( 'Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-elementor-widget-wrapper',
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-elementor-widget-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_margin',
            [
                'label'      => __( 'Margin', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-elementor-widget-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'container_border',
                'selector' => '{{WRAPPER}} .wpuf-elementor-widget-wrapper',
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-elementor-widget-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'container_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-elementor-widget-wrapper',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_label_style_controls() {
        $this->start_controls_section(
            'section_label_style',
            [
                'label' => __( 'Labels', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .wpuf-form .wpuf-label',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_input_style_controls() {
        $this->start_controls_section(
            'section_input_style',
            [
                'label' => __( 'Input Fields', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'input_typography',
                'selector' => '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'input_border',
                'selector' => '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select',
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function register_submit_button_style_controls() {
        $this->start_controls_section(
            'section_submit_button_style',
            [
                'label' => __( 'Submit Button', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'tabs_submit_button_style' );

        $this->start_controls_tab(
            'tab_submit_button_normal',
            [
                'label' => __( 'Normal', 'wp-user-frontend' ),
            ]
        );

        $this->add_control(
            'submit_button_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-submit-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submit_button_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-submit-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'submit_button_typography',
                'selector' => '{{WRAPPER}} .wpuf-form .wpuf-submit-button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_submit_button_hover',
            [
                'label' => __( 'Hover', 'wp-user-frontend' ),
            ]
        );

        $this->add_control(
            'submit_button_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-submit-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'submit_button_hover_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-submit-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'submit_button_border',
                'selector' => '{{WRAPPER}} .wpuf-form .wpuf-submit-button',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'submit_button_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form .wpuf-submit-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form .wpuf-submit-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $form_id  = isset( $settings['form_id'] ) ? $settings['form_id'] : null;

        if ( empty( $form_id ) ) {
            return;
        }

        $shortcode_str = '[wpuf_form id="' . $form_id . '"]';
        $output        = do_shortcode( $shortcode_str );

        echo '<div class="wpuf-elementor-widget-wrapper">';
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output
        echo '</div>';
    }

    /**
     * Render the widget output in the editor.
     *
     * Written as a Backbone JavaScript template. When empty, Elementor
     * will fall back to using the render() method for both frontend
     * and editor preview.
     *
     * @since 4.2.7
     *
     * @access protected
     */
    protected function content_template() {}
}
