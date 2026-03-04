<?php
/**
 * DESCRIPTION: Elementor widget for displaying WPUF User Directory listings and profiles.
 *
 * @package WPUF\Elementor
 */

namespace WeDevs\Wpuf\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use WeDevs\Wpuf\Modules\User_Directory\User_Directory;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class User_Directory_Widget extends Widget_Base {
    /**
     * Retrieve the widget name
     *
     * @since WPUF_SINCE
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'wpuf-user-directory';
    }

    /**
     * Retrieve the widget title
     *
     * @since WPUF_SINCE
     *
     * @return string Widget title
     */
    public function get_title() {
        return __( 'User Directory', 'wp-user-frontend' );
    }

    /**
     * Retrieve the widget icon
     *
     * @since WPUF_SINCE
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-person';
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
        return [ 'wpuf', 'user', 'directory', 'listing', 'profile', 'member' ];
    }

    /**
     * Retrieve the list of styles the widget depends on
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_style_depends() {
        return [ 'wpuf-user-directory-frontend', 'wpuf-elementor-user-directory' ];
    }

    /**
     * Retrieve the list of scripts the widget depends on
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_script_depends() {
        return [ 'wpuf-user-directory-frontend', 'wpuf-ud-search-shortcode' ];
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
        // Directory Style Controls
        $this->register_search_bar_style_controls();
        $this->register_filter_controls_style_controls();
        $this->register_user_card_style_controls();
        $this->register_user_name_style_controls();
        $this->register_user_info_style_controls();
        $this->register_view_profile_button_style_controls();
        $this->register_pagination_style_controls();
        // Profile Style Controls
        $this->register_profile_name_style_controls();
        $this->register_profile_contact_style_controls();
        $this->register_profile_tab_style_controls();
        $this->register_profile_tab_content_style_controls();
        $this->register_profile_bio_style_controls();
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
        $directories = $this->get_directories();
        if ( empty( $directories ) ) {
            $this->add_control(
                'no_directory_notice',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => sprintf(
                        /* translators: %s: Admin URL to create a directory */
                        __( 'No user directory found. <a href="%s" target="_blank">Create one</a> first.', 'wp-user-frontend' ),
                        admin_url( 'admin.php?page=wpuf_userlisting' )
                    ),
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
        }
        $this->add_control(
            'directory_id',
            [
                'label'       => __( 'Directory', 'wp-user-frontend' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => '',
                'options'     => $this->get_directory_options(),
                'description' => __( 'Select a user directory to display.', 'wp-user-frontend' ),
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register search bar style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_search_bar_style_controls() {
        $this->start_controls_section(
            'section_search_bar_style',
            [
                'label' => __( 'Search Bar', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'search_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-search-wrapper' => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-ud-search-by'      => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'search_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-search-input' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-ud-search-by'    => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'search_placeholder_color',
            [
                'label'     => __( 'Placeholder Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-search-input::placeholder' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'search_border',
                'selector'       => '{{WRAPPER}} .wpuf-ud-search-wrapper, {{WRAPPER}} .wpuf-ud-search-by',
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
                            'unit'     => 'px',
                            'isLinked' => true,
                        ],
                        'selectors' => [
                            '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                        ],
                    ],
                    'color'  => [
                        'default' => '#D1D5DB',
                        'selectors' => [
                            '{{SELECTOR}}' => 'border-color: {{VALUE}} !important;',
                        ],
                    ],
                ],
            ]
        );
        $this->add_control(
            'search_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'      => '6',
                    'right'    => '6',
                    'bottom'   => '6',
                    'left'     => '6',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-search-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .wpuf-ud-search-by'      => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_responsive_control(
            'search_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'default'    => [
                    'top'      => '9',
                    'right'    => '15',
                    'bottom'   => '9',
                    'left'     => '17',
                    'unit'     => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-search-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register filter controls style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_filter_controls_style_controls() {
        $this->start_controls_section(
            'section_filter_controls_style',
            [
                'label' => __( 'Filter Controls', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        // Dropdowns heading
        $this->add_control(
            'filter_dropdowns_heading',
            [
                'label'     => __( 'Dropdowns', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
            ]
        );
        $this->add_control(
            'filter_dropdown_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-sort-by'    => 'background-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-ud-sort-order'  => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'filter_dropdown_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-sort-by'    => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-ud-sort-order'  => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'filter_dropdown_border',
                'selector'       => '{{WRAPPER}} .wpuf-ud-sort-by, {{WRAPPER}} .wpuf-ud-sort-order',
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
                            'unit'     => 'px',
                            'isLinked' => true,
                        ],
                        'selectors' => [
                            '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                        ],
                    ],
                    'color'  => [
                        'default' => '#D1D5DB',
                        'selectors' => [
                            '{{SELECTOR}}' => 'border-color: {{VALUE}} !important;',
                        ],
                    ],
                ],
            ]
        );
        $this->add_control(
            'filter_dropdown_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'      => '6',
                    'right'    => '6',
                    'bottom'   => '6',
                    'left'     => '6',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-sort-by'   => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} .wpuf-ud-sort-order' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        // Reset Button heading
        $this->add_control(
            'filter_reset_btn_heading',
            [
                'label'     => __( 'Reset Button', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'filter_reset_btn_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-reset-filters' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'filter_reset_btn_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-reset-filters' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'filter_reset_btn_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'      => '6',
                    'right'    => '6',
                    'bottom'   => '6',
                    'left'     => '6',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-reset-filters' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_responsive_control(
            'filter_reset_btn_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'default'    => [
                    'top'      => '8',
                    'right'    => '24',
                    'bottom'   => '8',
                    'left'     => '24',
                    'unit'     => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-reset-filters' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register user card style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_user_card_style_controls() {
        $this->start_controls_section(
            'section_user_card_style',
            [
                'label' => __( 'User Cards', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'card_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'           => 'card_border',
                'selector'       => '{{WRAPPER}} .wpuf-ud-list-layout-3 li',
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
                            'unit'     => 'px',
                            'isLinked' => true,
                        ],
                        'selectors' => [
                            '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                        ],
                    ],
                    'color'  => [
                        'default' => '#D1D5DB',
                        'selectors' => [
                            '{{SELECTOR}}' => 'border-color: {{VALUE}} !important;',
                        ],
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
                    'top'      => '8',
                    'right'    => '8',
                    'bottom'   => '8',
                    'left'     => '8',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-ud-list-layout-3 li',
            ]
        );
        $this->add_responsive_control(
            'card_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'default'    => [
                    'top'      => '24',
                    'right'    => '24',
                    'bottom'   => '24',
                    'left'     => '24',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
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
                'default'    => [ 'size' => 24, 'unit' => 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 ul' => 'gap: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register user name style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_user_name_style_controls() {
        $this->start_controls_section(
            'section_user_name_style',
            [
                'label' => __( 'User Name', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'user_name_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li h3' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'user_name_typography',
                'selector' => '{{WRAPPER}} .wpuf-ud-list-layout-3 li h3',
            ]
        );
        $this->add_responsive_control(
            'user_name_align',
            [
                'label'     => __( 'Alignment', 'wp-user-frontend' ),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'   => [ 'title' => __( 'Left', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => __( 'Center', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => __( 'Right', 'wp-user-frontend' ), 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li h3' => 'text-align: {{VALUE}} !important; width: 100% !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register user info style controls (email, website, phone)
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_user_info_style_controls() {
        $this->start_controls_section(
            'section_user_info_style',
            [
                'label' => __( 'User Info', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'user_info_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#4B5563',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li p'   => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li p a' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'user_info_hover_color',
            [
                'label'     => __( 'Link Hover Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li p a:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'user_info_typography',
                'selector' => '{{WRAPPER}} .wpuf-ud-list-layout-3 li p',
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register View Profile button style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_view_profile_button_style_controls() {
        $this->start_controls_section(
            'section_view_profile_btn_style',
            [
                'label' => __( 'View Profile Button', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'view_profile_btn_typography',
                'selector' => '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600',
            ]
        );
        $this->start_controls_tabs( 'tabs_view_profile_btn' );
        $this->start_controls_tab(
            'tab_view_profile_btn_normal',
            [ 'label' => __( 'Normal', 'wp-user-frontend' ) ]
        );
        $this->add_control(
            'view_profile_btn_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'view_profile_btn_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#9333EA',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'view_profile_btn_border',
                'selector' => '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600',
            ]
        );
        $this->end_controls_tab();
        $this->start_controls_tab(
            'tab_view_profile_btn_hover',
            [ 'label' => __( 'Hover', 'wp-user-frontend' ) ]
        );
        $this->add_control(
            'view_profile_btn_hover_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'view_profile_btn_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'view_profile_btn_hover_border_color',
            [
                'label'     => __( 'Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600:hover' => 'border-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control(
            'view_profile_btn_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'separator'  => 'before',
                'default'    => [
                    'top'      => '6',
                    'right'    => '6',
                    'bottom'   => '6',
                    'left'     => '6',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->add_responsive_control(
            'view_profile_btn_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'default'    => [
                    'top'      => '8',
                    'right'    => '16',
                    'bottom'   => '8',
                    'left'     => '16',
                    'unit'     => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-ud-list-layout-3 li a.wpuf-bg-purple-600' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register pagination style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_pagination_style_controls() {
        $this->start_controls_section(
            'section_pagination_style',
            [
                'label' => __( 'Pagination', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'pagination_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#374151',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-pagination-shortcode a'    => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-ud-pagination-shortcode span' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'pagination_active_color',
            [
                'label'     => __( 'Active Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-pagination-shortcode span[aria-current="page"]' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'pagination_active_border_color',
            [
                'label'     => __( 'Active Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-pagination-shortcode span[aria-current="page"]' => 'border-top-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'pagination_hover_color',
            [
                'label'     => __( 'Hover Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-pagination-shortcode a:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'pagination_hover_border_color',
            [
                'label'     => __( 'Hover Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-ud-pagination-shortcode a:hover' => 'border-top-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register profile name style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_profile_name_style_controls() {
        $this->start_controls_section(
            'section_profile_name_style',
            [
                'label' => __( 'Profile - Name', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'profile_name_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-profile-layout-2 h1' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'profile_name_typography',
                'selector' => '{{WRAPPER}} .wpuf-profile-layout-2 h1',
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register profile contact info style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_profile_contact_style_controls() {
        $this->start_controls_section(
            'section_profile_contact_style',
            [
                'label' => __( 'Profile - Contact Info', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'profile_contact_icon_color',
            [
                'label'     => __( 'Icon Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#6B7280',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-profile-layout-2 .wpuf-profile-header-overlap svg' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'profile_contact_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-profile-layout-2 .wpuf-profile-header-overlap span' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} .wpuf-profile-layout-2 .wpuf-profile-header-overlap a'    => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'profile_contact_typography',
                'selector' => '{{WRAPPER}} .wpuf-profile-layout-2 .wpuf-profile-header-overlap span',
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register profile tab navigation style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_profile_tab_style_controls() {
        $this->start_controls_section(
            'section_profile_tab_style',
            [
                'label' => __( 'Profile - Tab Navigation', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'profile_tab_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#6B7280',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-tab-button-2' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'profile_tab_active_color',
            [
                'label'     => __( 'Active Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-tab-button-2.active' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            'profile_tab_active_border_color',
            [
                'label'     => __( 'Active Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#059669',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-tab-button-2.active::after' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'profile_tab_typography',
                'selector' => '{{WRAPPER}} .wpuf-tab-button-2',
            ]
        );
        $this->add_control(
            'profile_tab_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => 'transparent',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-tab-button-2' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register profile tab content style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_profile_tab_content_style_controls() {
        $this->start_controls_section(
            'section_profile_tab_content_style',
            [
                'label' => __( 'Profile - Tab Content', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'profile_tab_content_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-tab-content-2' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_responsive_control(
            'profile_tab_content_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-tab-content-2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register profile bio text style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_profile_bio_style_controls() {
        $this->start_controls_section(
            'section_profile_bio_style',
            [
                'label' => __( 'Profile - Bio', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'profile_bio_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#9CA3AF',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-profile-layout-2 .wpuf-profile-header-overlap p' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'profile_bio_typography',
                'selector' => '{{WRAPPER}} .wpuf-profile-layout-2 .wpuf-profile-header-overlap p',
            ]
        );
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
        $settings     = $this->get_settings_for_display();
        $directory_id = ! empty( $settings['directory_id'] ) ? absint( $settings['directory_id'] ) : 0;
        $is_editor = $this->is_editor_render();
        if ( empty( $directory_id ) ) {
            if ( $is_editor ) {
                $this->render_empty_state();
            }
            return;
        }
        // Verify directory exists
        $post = get_post( $directory_id );
        if ( ! $post || User_Directory::POST_TYPE !== $post->post_type ) {
            if ( $is_editor ) {
                $this->render_empty_state( __( 'Selected directory no longer exists.', 'wp-user-frontend' ) );
            }
            return;
        }
        // Localize search script
        wp_localize_script(
            'wpuf-ud-search-shortcode',
            'wpufUserDirectorySearch',
            [
                'restUrl' => rest_url( 'wpuf/v1/user_directory/search' ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            ]
        );
        echo '<div class="wpuf-elementor-user-directory-wrapper">';
        // Directory section — render via shortcode
        echo do_shortcode( '[wpuf_user_listing id="' . $directory_id . '"]' );
        // Profile preview in editor only
        if ( $is_editor ) {
            $this->render_profile_preview( $directory_id );
        }
        echo '</div>';
    }

    /**
     * Render profile preview for the Elementor editor
     *
     * Shows the current logged-in admin's profile below the directory listing
     * so editors can preview both views simultaneously.
     *
     * @since WPUF_SINCE
     *
     * @param int $directory_id Directory post ID.
     *
     * @return void
     */
    protected function render_profile_preview( $directory_id ) {
        $current_user = wp_get_current_user();
        if ( ! $current_user || ! $current_user->exists() ) {
            return;
        }
        // Load directory settings
        $post     = get_post( $directory_id );
        $settings = [];
        if ( ! empty( $post->post_content ) ) {
            $settings = json_decode( $post->post_content, true ) ?: [];
        }
        $settings = wp_parse_args( $settings, User_Directory::get_default_settings() );
        // Build profile data matching what the Shortcode class provides
        $default_tabs = [ 'about', 'posts', 'comments', 'file' ];
        if ( ! empty( $settings['default_tabs'] ) && is_array( $settings['default_tabs'] ) ) {
            $default_tabs = $settings['default_tabs'];
        } elseif ( ! empty( $settings['default_tabs'] ) && is_string( $settings['default_tabs'] ) ) {
            $default_tabs = array_map( 'trim', explode( ',', $settings['default_tabs'] ) );
        }
        $profile_data = [
            'user'               => $current_user,
            'settings'           => $settings,
            'directory_id'       => $directory_id,
            'avatar_size'        => absint( $settings['avatar_size'] ?? 192 ),
            'back_url'           => '#',
            'show_avatar'        => ! empty( $settings['show_avatar'] ) || ! isset( $settings['show_avatar'] ),
            'enable_tabs'        => ! empty( $settings['enable_tabs'] ) || ! isset( $settings['enable_tabs'] ),
            'default_tabs'       => $default_tabs,
            'default_active_tab' => $settings['default_active_tab'] ?? 'about',
            'profile_size'       => $settings['profile_size'] ?? 'thumbnail',
        ];
        $profile_data['template_data'] = $profile_data;
        echo '<div class="wpuf-elementor-user-directory-profile-preview">';
        echo '<div class="wpuf-elementor-profile-separator">';
        echo '<span>' . esc_html__( 'Profile Preview', 'wp-user-frontend' ) . '</span>';
        echo '</div>';
        // Render profile template
        // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
        extract( $profile_data );
        $template_file = WPUF_UD_FREE_VIEWS . '/profile/layout-2.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        echo '</div>';
    }

    /**
     * Render empty state for Elementor editor
     *
     * @since WPUF_SINCE
     *
     * @param string $message Optional custom message.
     *
     * @return void
     */
    protected function render_empty_state( $message = '' ) {
        if ( empty( $message ) ) {
            $message = __( 'No directory selected.', 'wp-user-frontend' );
        }
        echo '<div class="wpuf-elementor-user-directory-wrapper">';
        echo '<div class="wpuf-elementor-ud-empty-state">';
        echo '<div class="wpuf-elementor-ud-empty-icon">';
        echo '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
        echo '<path d="M17 20H22V18C22 16.3431 20.6569 15 19 15C18.0444 15 17.1931 15.4468 16.6438 16.1429M17 20H7M17 20V18C17 17.3438 16.8736 16.717 16.6438 16.1429M7 20H2V18C2 16.3431 3.34315 15 5 15C5.95561 15 6.80686 15.4468 7.35625 16.1429M7 20V18C7 17.3438 7.12642 16.717 7.35625 16.1429M7.35625 16.1429C8.0935 14.301 9.89482 13 12 13C14.1052 13 15.9065 14.301 16.6438 16.1429M15 7C15 8.65685 13.6569 10 12 10C10.3431 10 9 8.65685 9 7C9 5.34315 10.3431 4 12 4C13.6569 4 15 5.34315 15 7ZM21 10C21 11.1046 20.1046 12 19 12C17.8954 12 17 11.1046 17 10C17 8.89543 17.8954 8 19 8C20.1046 8 21 8.89543 21 10ZM7 10C7 11.1046 6.10457 12 5 12C3.89543 12 3 11.1046 3 10C3 8.89543 3.89543 8 5 8C6.10457 8 7 8.89543 7 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>';
        echo '</svg>';
        echo '</div>';
        echo '<p class="wpuf-elementor-ud-empty-text">' . esc_html( $message ) . '</p>';
        echo '<p class="wpuf-elementor-ud-empty-hint">';
        echo wp_kses_post(
            sprintf(
                /* translators: %s: Admin URL to create a directory */
                __( '<a href="%s" target="_blank">Create a user directory</a> in WP User Frontend settings.', 'wp-user-frontend' ),
                admin_url( 'admin.php?page=wpuf_userlisting' )
            )
        );
        echo '</p>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Get available directories for the dropdown
     *
     * @since WPUF_SINCE
     *
     * @return array Array of WP_Post objects.
     */
    protected function get_directories() {
        return get_posts( [
            'post_type'      => User_Directory::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );
    }

    /**
     * Get directory options for the SELECT control
     *
     * @since WPUF_SINCE
     *
     * @return array Associative array of directory_id => title.
     */
    protected function get_directory_options() {
        $directories = $this->get_directories();
        $options     = [ '' => __( '— Select Directory —', 'wp-user-frontend' ) ];
        foreach ($directories as $directory) {
            $options[ $directory->ID ] = $directory->post_title;
        }
        return $options;
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
     * Empty to use server-side render() for both frontend and editor.
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function content_template() {}
}
