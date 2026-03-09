<?php
/**
 * DESCRIPTION: Elementor widget for displaying WPUF forms
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

class Widget extends Widget_Base {

    /**
     * Retrieve the widget name
     *
     * @since WPUF_SINCE
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'wpuf-form';
    }

    /**
     * Retrieve the widget title
     *
     * @since WPUF_SINCE
     *
     * @return string Widget title
     */
    public function get_title() {
        return __( 'User Frontend Form', 'wp-user-frontend' );
    }

    /**
     * Retrieve the widget icon
     *
     * @since WPUF_SINCE
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
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
     * Retrieve the list of styles the widget depends on.
     *
     * @since 4.0.0
     *
     * @return array
     */
    public function get_style_depends() {
        $depends = [ 'wpuf-elementor-frontend-forms' ];

        /**
         * Filters the list of style handles the widget depends on.
         *
         * @since WPUF_SINCE
         *
         * @param string[]        $depends Array of style handles.
         * @param \Elementor\Widget_Base $this  The widget instance.
         */
        return apply_filters( 'wpuf_elementor_widget_style_depends', $depends, $this );
    }

    /**
     * Retrieve the list of scripts the widget depends on.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_script_depends() {
        $depends = [];

        /**
         * Filters the list of script handles the widget depends on.
         *
         * @since WPUF_SINCE
         *
         * @param string[]        $depends Array of script handles.
         * @param \Elementor\Widget_Base $this  The widget instance.
         */
        return apply_filters( 'wpuf_elementor_widget_script_depends', $depends, $this );
    }

    /**
     * Register widget controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
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
        $this->register_label_style_controls();
        $this->register_help_text_style_controls();
        $this->register_input_style_controls();
        $this->register_placeholder_style_controls();
        $this->register_richtext_style_controls();
        $this->register_section_break_style_controls();
        $this->register_radio_checkbox_style_controls();
        $this->register_upload_button_style_controls();
        $this->register_submit_button_style_controls();

        /**
         * Fires after the widget has registered its style controls.
         *
         * Use this to add additional style sections (e.g. for Pro features like multistep).
         *
         * @since WPUF_SINCE
         *
         * @param \Elementor\Widget_Base $this The widget instance.
         */
        do_action( 'wpuf_elementor_widget_register_style_controls', $this );
    }

    /**
     * Retrieve all published WPUF forms
     *
     * @since WPUF_SINCE
     *
     * @return array Array of forms formatted as select options
     */
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

        /**
         * Filters the form options shown in the widget's form dropdown.
         *
         * @since WPUF_SINCE
         *
         * @param array           $options Associative array of form_id => form_title.
         * @param \Elementor\Widget_Base $this   The widget instance.
         */
        return apply_filters( 'wpuf_elementor_form_options', $options, $this );
    }

    /**
     * Register label style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
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
                    '{{WRAPPER}} .wpuf-form .wpuf-label, {{WRAPPER}} .wpuf-form .wpuf-form-sub-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .wpuf-form .wpuf-label, {{WRAPPER}} .wpuf-form .wpuf-form-sub-label',
            ]
        );

        $this->add_control(
            'label_asterisk_color',
            [
                'label'     => __( 'Asterisk Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-label .required, {{WRAPPER}} .wpuf-form .wpuf-form-sub-label .required' => 'color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'label_asterisk_size',
            [
                'label'      => __( 'Asterisk Size', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 30, 'step' => 1 ],
                    '%'  => [ 'min' => 0, 'max' => 30, 'step' => 1 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form .wpuf-label .required, {{WRAPPER}} .wpuf-form .wpuf-form-sub-label .required' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register help text style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_help_text_style_controls() {
        $this->start_controls_section(
            'section_help_text_style',
            [
                'label' => __( 'Help Text', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'help_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-help' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'help_text_typography',
                'selector' => '{{WRAPPER}} .wpuf-form .wpuf-help',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register input field style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_input_style_controls() {
        $input_selector = '{{WRAPPER}} .wpuf-form input[type="text"], {{WRAPPER}} .wpuf-form input[type="email"], {{WRAPPER}} .wpuf-form input[type="url"], {{WRAPPER}} .wpuf-form input[type="password"], {{WRAPPER}} .wpuf-form input[type="number"], {{WRAPPER}} .wpuf-form textarea, {{WRAPPER}} .wpuf-form select';

        $this->start_controls_section(
            'section_input_style',
            [
                'label' => __( 'Input & Textarea', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'input_alignment',
            [
                'label'   => __( 'Alignment', 'wp-user-frontend' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [ $input_selector => 'text-align: {{VALUE}};' ],
            ]
        );

        $this->start_controls_tabs( 'tabs_input_style' );

        $this->start_controls_tab( 'tab_input_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'input_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $input_selector => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $input_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [ 'name' => 'input_typography', 'selector' => $input_selector ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [ 'name' => 'input_border', 'selector' => $input_selector ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [ $input_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_responsive_control(
            'input_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [ $input_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'input_box_shadow',
                'selector' => $input_selector,
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_input_focus', [ 'label' => __( 'Focus', 'wp-user-frontend' ) ] );

        $this->add_control(
            'input_focus_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form input[type="text"]:focus, {{WRAPPER}} .wpuf-form input[type="email"]:focus, {{WRAPPER}} .wpuf-form input[type="url"]:focus, {{WRAPPER}} .wpuf-form input[type="password"]:focus, {{WRAPPER}} .wpuf-form input[type="number"]:focus, {{WRAPPER}} .wpuf-form textarea:focus, {{WRAPPER}} .wpuf-form select:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'input_focus_border',
                'selector' => '{{WRAPPER}} .wpuf-form input[type="text"]:focus, {{WRAPPER}} .wpuf-form input[type="email"]:focus, {{WRAPPER}} .wpuf-form input[type="url"]:focus, {{WRAPPER}} .wpuf-form input[type="password"]:focus, {{WRAPPER}} .wpuf-form input[type="number"]:focus, {{WRAPPER}} .wpuf-form textarea:focus, {{WRAPPER}} .wpuf-form select:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'input_focus_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-form input[type="text"]:focus, {{WRAPPER}} .wpuf-form input[type="email"]:focus, {{WRAPPER}} .wpuf-form input[type="url"]:focus, {{WRAPPER}} .wpuf-form input[type="password"]:focus, {{WRAPPER}} .wpuf-form input[type="number"]:focus, {{WRAPPER}} .wpuf-form textarea:focus, {{WRAPPER}} .wpuf-form select:focus',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register placeholder style controls.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_placeholder_style_controls() {
        $this->start_controls_section(
            'section_placeholder_style',
            [
                'label' => __( 'Placeholder', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $placeholder_selector = '{{WRAPPER}} .wpuf-form input::placeholder, {{WRAPPER}} .wpuf-form textarea::placeholder, {{WRAPPER}} .wpuf-form input::-webkit-input-placeholder, {{WRAPPER}} .wpuf-form textarea::-webkit-input-placeholder, {{WRAPPER}} .wpuf-form input::-moz-placeholder, {{WRAPPER}} .wpuf-form textarea::-moz-placeholder, {{WRAPPER}} .wpuf-form input:-ms-input-placeholder, {{WRAPPER}} .wpuf-form textarea:-ms-input-placeholder';

        $this->add_control(
            'placeholder_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $placeholder_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register rich text editor style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_richtext_style_controls() {
        $toolbar_selector = '{{WRAPPER}} .wpuf-form .mce-toolbar-grp';
        $insert_image_btn = '{{WRAPPER}} .wpuf-form .wpuf-insert-image';

        $this->start_controls_section(
            'section_richtext_style',
            [
                'label' => __( 'Rich Text Editor', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'richtext_border',
                'label'    => __( 'Border', 'wp-user-frontend' ),
                'selector' => '{{WRAPPER}} .wpuf-form .mce-tinymce',
            ]
        );

        $this->add_control(
            'richtext_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form .mce-tinymce' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_toolbar_heading',
            [
                'label'     => __( 'Toolbar', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'richtext_toolbar_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $toolbar_selector . ' .mce-btn button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_toolbar_icon_color',
            [
                'label'     => __( 'Icon Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $toolbar_selector . ' .mce-ico' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_toolbar_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $toolbar_selector => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_insert_image_heading',
            [
                'label'     => __( 'Insert Photo Button', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( 'tabs_insert_image_style' );

        $this->start_controls_tab( 'tab_insert_image_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'richtext_insert_image_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $insert_image_btn => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_insert_image_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $insert_image_btn => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'richtext_insert_image_border',
                'selector' => $insert_image_btn,
            ]
        );

        $this->add_control(
            'richtext_insert_image_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    $insert_image_btn => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'richtext_insert_image_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $insert_image_btn => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'richtext_insert_image_margin',
            [
                'label'      => __( 'Margin', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form #wpuf-insert-image-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_insert_image_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'richtext_insert_image_hover_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $insert_image_btn . ':hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_insert_image_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $insert_image_btn . ':hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'richtext_insert_image_hover_border_color',
            [
                'label'     => __( 'Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $insert_image_btn . ':hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register section break style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_section_break_style_controls() {
        $section_wrap  = '{{WRAPPER}} .wpuf-form .wpuf-section-wrap';
        $section_title = '{{WRAPPER}} .wpuf-form .wpuf-section-title';
        $section_desc  = '{{WRAPPER}} .wpuf-form .wpuf-section-details';

        $this->start_controls_section(
            'section_break_style',
            [
                'label' => __( 'Section Break', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'section_break_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $section_wrap => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'section_break_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $section_wrap => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'section_break_margin',
            [
                'label'      => __( 'Margin', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $section_wrap => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'section_break_border',
                'selector' => $section_wrap,
            ]
        );

        $this->add_control(
            'section_break_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    $section_wrap => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_break_title_heading',
            [
                'label'     => __( 'Title', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'section_break_title_color',
            [
                'label'     => __( 'Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $section_title => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'section_break_title_typography',
                'selector' => $section_title,
            ]
        );

        $this->add_responsive_control(
            'section_break_title_alignment',
            [
                'label'   => __( 'Alignment', 'wp-user-frontend' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [ $section_title => 'text-align: {{VALUE}};' ],
            ]
        );

        $this->add_responsive_control(
            'section_break_title_margin',
            [
                'label'      => __( 'Margin', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    $section_title => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'section_break_desc_heading',
            [
                'label'     => __( 'Description', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'section_break_desc_color',
            [
                'label'     => __( 'Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    $section_desc => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'section_break_desc_typography',
                'selector' => $section_desc,
            ]
        );

        $this->add_responsive_control(
            'section_break_desc_alignment',
            [
                'label'   => __( 'Alignment', 'wp-user-frontend' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [ $section_desc => 'text-align: {{VALUE}};' ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register radio and checkbox style controls.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_radio_checkbox_style_controls() {
        $this->start_controls_section(
            'section_radio_checkbox_style',
            [
                'label' => __( 'Radio & Checkbox', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'radio_checkbox_size',
            [
                'label'      => __( 'Size', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80, 'step' => 1 ] ],
                'default'    => [ 'size' => 18, 'unit' => 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form input[type="radio"], {{WRAPPER}} .wpuf-form input[type="checkbox"]' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'radio_checkbox_spacing',
            [
                'label'      => __( 'Spacing', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 30, 'step' => 1 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-form input[type="radio"], {{WRAPPER}} .wpuf-form input[type="checkbox"]' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'radio_checkbox_selector_color',
            [
                'label'     => __( 'Selector Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form input[type="radio"], {{WRAPPER}} .wpuf-form input[type="checkbox"]' => 'accent-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'radio_checkbox_options_text_color',
            [
                'label'     => __( 'Options Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form .wpuf-radio-block, {{WRAPPER}} .wpuf-form .wpuf-checkbox-block, {{WRAPPER}} .wpuf-form .wpuf-radio-inline, {{WRAPPER}} .wpuf-form .wpuf-checkbox-inline, {{WRAPPER}} .wpuf-form .wpuf-price-label, {{WRAPPER}} .wpuf-form .wpuf-fields[data-type="radio"] > label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register upload button style controls
     *
     * Styles the file/image upload buttons (e.g. "Select File", "Select Image") within WPUF forms.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_upload_button_style_controls() {
        $btn_selector = '{{WRAPPER}} .wpuf-form .file-selector';
        $align_selector = '{{WRAPPER}} .wpuf-form .wpuf-attachment-upload-filelist';

        $this->start_controls_section(
            'section_upload_button_style',
            [
                'label' => __( 'Upload Button', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'upload_button_width_type',
            [
                'label'   => __( 'Width', 'wp-user-frontend' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'full-width' => __( 'Full Width', 'wp-user-frontend' ),
                    'custom'     => __( 'Custom', 'wp-user-frontend' ),
                ],
            ]
        );

        $this->add_responsive_control(
            'upload_button_align',
            [
                'label'     => __( 'Alignment', 'wp-user-frontend' ),
                'type'      => Controls_Manager::CHOOSE,
                'default'   => 'left',
                'options'   => [
                    'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-right' ],
                ],
                'selectors' => [ $align_selector => 'text-align: {{VALUE}};' ],
                'condition' => [ 'upload_button_width_type' => 'custom' ],
            ]
        );

        $this->add_responsive_control(
            'upload_button_width',
            [
                'label'      => __( 'Width', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 1200, 'step' => 1 ] ],
                'selectors'  => [ $btn_selector => 'width: {{SIZE}}{{UNIT}};' ],
                'condition'  => [ 'upload_button_width_type' => 'custom' ],
            ]
        );

        $this->start_controls_tabs( 'tabs_upload_button_style' );

        $this->start_controls_tab( 'tab_upload_button_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'upload_button_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#6b7280',
                'selectors' => [ $btn_selector => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'upload_button_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ $btn_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'           => 'upload_button_typography',
                'selector'       => $btn_selector,
                'fields_options' => [
                    'font_weight' => [
                        'default' => '500',
                    ],
                    'font_size'   => [
                        'default' => [
                            'unit' => 'px',
                            'size' => '16',
                        ],
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'upload_button_border',
                'selector'       => $btn_selector,
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width'  => [
                        'default' => [
                            'top'    => '0',
                            'right'  => '0',
                            'bottom' => '0',
                            'left'   => '0',
                        ],
                    ],
                    'color'  => [
                        'default' => 'transparent',
                    ],
                ],
            ]
        );

        $this->add_control(
            'upload_button_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'    => '6',
                    'right'  => '6',
                    'bottom' => '6',
                    'left'   => '6',
                    'unit'   => 'px',
                ],
                'selectors'  => [ $btn_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_responsive_control(
            'upload_button_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'    => '12',
                    'right'  => '24',
                    'bottom' => '12',
                    'left'   => '24',
                    'unit'   => 'px',
                ],
                'selectors'  => [ $btn_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'           => 'upload_button_box_shadow',
                'selector'       => $btn_selector,
                'separator'      => 'before',
                'fields_options' => [
                    'box_shadow_type' => [
                        'default' => 'yes',
                    ],
                    'box_shadow'      => [
                        'default' => [
                            'horizontal' => 0,
                            'vertical'   => 1,
                            'blur'       => 3,
                            'spread'     => 0,
                            'color'      => 'rgba(0, 0, 0, 0.1)',
                        ],
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_upload_button_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'upload_button_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#4b5563',
                'selectors' => [ $btn_selector . ':hover' => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'upload_button_hover_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ $btn_selector . ':hover' => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'upload_button_hover_border_color',
            [
                'label'     => __( 'Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $btn_selector . ':hover' => 'border-color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register submit button style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_submit_button_style_controls() {
        $btn_selector = '{{WRAPPER}} .wpuf-form .wpuf-submit-button';

        $this->start_controls_section(
            'section_submit_button_style',
            [
                'label' => __( 'Submit Button', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'submit_button_width_type',
            [
                'label'   => __( 'Width', 'wp-user-frontend' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'full-width' => __( 'Full Width', 'wp-user-frontend' ),
                    'custom'     => __( 'Custom', 'wp-user-frontend' ),
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_align',
            [
                'label'     => __( 'Alignment', 'wp-user-frontend' ),
                'type'      => Controls_Manager::CHOOSE,
                'default'   => 'left',
                'options'   => [
                    'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-h-align-right' ],
                ],
                'selectors' => [ '{{WRAPPER}} .wpuf-form .wpuf-submit' => 'text-align: {{VALUE}};' ],
                'condition' => [ 'submit_button_width_type' => 'custom' ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_width',
            [
                'label'     => __( 'Width', 'wp-user-frontend' ),
                'type'      => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range'     => [ 'px' => [ 'min' => 0, 'max' => 1200, 'step' => 1 ] ],
                'selectors' => [ $btn_selector => 'width: {{SIZE}}{{UNIT}};' ],
                'condition' => [ 'submit_button_width_type' => 'custom' ],
            ]
        );

        $this->start_controls_tabs( 'tabs_submit_button_style' );

        $this->start_controls_tab( 'tab_submit_button_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'submit_button_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#6b7280',
                'selectors' => [ $btn_selector => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'submit_button_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ $btn_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'submit_button_typography',
                'selector' => $btn_selector,
                'fields_options' => [
                    'font_weight' => [
                        'default' => '500',
                    ],
                    'font_size' => [
                        'default' => [
                            'unit' => 'px',
                            'size' => '16',
                        ],
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'submit_button_border',
                'selector' => $btn_selector,
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top'    => '0',
                            'right'  => '0',
                            'bottom' => '0',
                            'left'   => '0',
                        ],
                    ],
                    'color' => [
                        'default' => 'transparent',
                    ],
                ],
            ]
        );

        $this->add_control(
            'submit_button_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'    => '6',
                    'right'  => '6',
                    'bottom' => '6',
                    'left'   => '6',
                    'unit'   => 'px',
                ],
                'selectors'  => [ $btn_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'    => '12',
                    'right'  => '24',
                    'bottom' => '12',
                    'left'   => '24',
                    'unit'   => 'px',
                ],
                'selectors'  => [ $btn_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'submit_button_box_shadow',
                'selector' => $btn_selector,
                'separator' => 'before',
                'fields_options' => [
                    'box_shadow_type' => [
                        'default' => 'yes',
                    ],
                    'box_shadow' => [
                        'default' => [
                            'horizontal' => 0,
                            'vertical'   => 1,
                            'blur'       => 3,
                            'spread'     => 0,
                            'color'      => 'rgba(0, 0, 0, 0.1)',
                        ],
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_submit_button_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'submit_button_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#4b5563',
                'selectors' => [ $btn_selector . ':hover' => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'submit_button_hover_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ $btn_selector . ':hover' => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'submit_button_hover_border_color',
            [
                'label'     => __( 'Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $btn_selector . ':hover' => 'border-color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
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
        $form_id  = isset( $settings['form_id'] ) ? $settings['form_id'] : null;

        if ( empty( $form_id ) ) {
            return;
        }

        $wrapper_classes = [ 'wpuf-elementor-widget-wrapper' ];

        if ( ! empty( $settings['submit_button_width_type'] ) && 'full-width' === $settings['submit_button_width_type'] ) {
            $wrapper_classes[] = 'wpuf-elementor-submit-full';
        }

        if ( ! empty( $settings['upload_button_width_type'] ) && 'full-width' === $settings['upload_button_width_type'] ) {
            $wrapper_classes[] = 'wpuf-elementor-upload-full';
        }

        /**
         * Filters the CSS classes applied to the widget wrapper.
         *
         * @since WPUF_SINCE
         *
         * @param string[] $wrapper_classes Array of class names.
         * @param array    $settings       Widget settings.
         * @param int|null $form_id        Selected form ID.
         */
        $wrapper_classes = apply_filters( 'wpuf_elementor_widget_wrapper_classes', $wrapper_classes, $settings, $form_id );

        $this->add_render_attribute( 'wrapper', 'class', $wrapper_classes );


        $wrapper_attributes = apply_filters( 'wpuf_elementor_widget_wrapper_attributes', [], $settings, $form_id );
        foreach ( $wrapper_attributes as $attr_key => $attr_value ) {
            if ( is_string( $attr_key ) && is_string( $attr_value ) ) {
                $this->add_render_attribute( 'wrapper', $attr_key, $attr_value );
            }
        }

        $shortcode_str = '[wpuf_form id="' . $form_id . '"]';
        $output        = do_shortcode( $shortcode_str );

        $is_elementor = class_exists( '\Elementor\Plugin' ) && (
            ( isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() )
            || ( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() )
        );

        echo '<div ' . wp_kses_post( $this->get_render_attribute_string( 'wrapper' ) ) . '>';
        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output

        // Initialize TinyMCE in Elementor preview
        if ( $is_elementor ) {
            ?>
            <script>
            (function() {
                // Suppress non-critical errors for fields that require external scripts
                // (Google Maps, barrating) that may not be loaded in Elementor preview
                var originalErrorHandler = window.onerror;
                window.onerror = function(msg, url, line, col, error) {
                    // Suppress Google Maps and barrating errors in Elementor preview
                    if (msg && (
                        msg.indexOf('google is not defined') !== -1 ||
                        msg.indexOf('barrating is not a function') !== -1 ||
                        msg.indexOf('$(...).barrating is not a function') !== -1
                    )) {
                        return true; // Suppress error
                    }
                    // Call original error handler for other errors
                    if (originalErrorHandler) {
                        return originalErrorHandler.apply(this, arguments);
                    }
                    return false;
                };

                var wrapper = document.querySelector('.wpuf-elementor-widget-wrapper');
                if (wrapper) {
                    var textareas = wrapper.querySelectorAll('textarea.wp-editor-area');

                    // Function to initialize TinyMCE for a textarea
                    function initializeTinyMCE(textarea) {
                        var editorId = textarea.id;

                        // Wait for wp.editor to be available
                        if (typeof wp === 'undefined' || typeof wp.editor === 'undefined') {
                            setTimeout(function() {
                                initializeTinyMCE(textarea);
                            }, 100);
                            return false;
                        }

                        if (typeof wp.editor.initialize !== 'function') {
                            return false;
                        }

                        // Check if already initialized
                        if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                            return true;
                        }

                        // Get editor settings - try to match WordPress default settings
                        // Check if there's a wp-editor-wrap parent to determine settings
                        var wrap = textarea.closest('.wp-editor-wrap');
                        var isTeeny = wrap && wrap.classList.contains('html-active') ? false : (wrap && wrap.classList.contains('tmce-active') ? false : true);

                        var editorSettings = {
                            tinymce: {
                                wpautop: true,
                                toolbar1: 'bold,italic,bullist,numlist,link',
                                toolbar2: '',
                                media_buttons: false,
                                resize: true
                            },
                            quicktags: false,
                            textarea_name: textarea.name || editorId
                        };

                        // If teeny mode, use simpler toolbar
                        if (isTeeny || textarea.closest('.wpuf-fields').querySelector('.mce-tinymce')) {
                            editorSettings.tinymce.toolbar1 = 'bold,italic,bullist,numlist';
                            editorSettings.teeny = true;
                        }

                        try {
                            wp.editor.initialize(editorId, editorSettings);
                            return true;
                        } catch(e) {
                            return false;
                        }
                    }

                    // Check existing instances and initialize if needed
                    textareas.forEach(function(textarea) {
                        var editorId = textarea.id;
                        if (typeof tinymce === 'undefined' || !tinymce.get(editorId)) {
                            initializeTinyMCE(textarea);
                        }
                    });

                    // Check after a delay to see if TinyMCE initializes or retry if needed
                    setTimeout(function() {
                        textareas.forEach(function(textarea) {
                            var editorId = textarea.id;
                            if (typeof tinymce === 'undefined' || !tinymce.get(editorId)) {
                                initializeTinyMCE(textarea);
                            }
                        });
                    }, 500);

                    // Also check when Elementor triggers content refresh
                    if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
                        elementorFrontend.hooks.addAction('frontend/element_ready/wpuf-form.default', function($scope) {
                            setTimeout(function() {
                                var textareas = $scope.find('textarea.wp-editor-area');
                                textareas.each(function(idx, textarea) {
                                    var editorId = textarea.id;
                                    if (typeof tinymce === 'undefined' || !tinymce.get(editorId)) {
                                        if (typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' && typeof wp.editor.initialize === 'function') {
                                            try {
                                                wp.editor.initialize(editorId, {
                                                    tinymce: {
                                                        wpautop: true,
                                                        toolbar1: 'bold,italic,bullist,numlist,link',
                                                        toolbar2: '',
                                                        media_buttons: false
                                                    },
                                                    quicktags: false
                                                });
                                            } catch(e) {
                                                // Silent fail
                                            }
                                        }
                                    }
                                });
                            }, 100);
                        });
                    }
                }

                // Restore original error handler after initialization
                setTimeout(function() {
                    if (originalErrorHandler) {
                        window.onerror = originalErrorHandler;
                    }
                }, 2000);
            })();
            </script>
            <?php
        }
        echo '</div>';

        /**
         * Fires after the widget has rendered its output.
         *
         * @since WPUF_SINCE
         *
         * @param \Elementor\Widget_Base $this The widget instance.
         */
        do_action( 'wpuf_elementor_widget_after_render', $this );
    }

    /**
     * Render the widget output in the editor.
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
