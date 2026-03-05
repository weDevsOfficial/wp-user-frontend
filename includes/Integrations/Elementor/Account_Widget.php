<?php
/**
 * DESCRIPTION: Elementor widget for displaying the WPUF User Account dashboard
 *
 * @package WPUF\Elementor
 */

namespace WeDevs\Wpuf\Integrations\Elementor;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class Account_Widget extends Widget_Base {

    /**
     * Retrieve the widget name
     *
     * @since WPUF_SINCE
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'wpuf-account';
    }

    /**
     * Retrieve the widget title
     *
     * @since WPUF_SINCE
     *
     * @return string Widget title
     */
    public function get_title() {
        return __( 'User Account', 'wp-user-frontend' );
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
        return [ 'wpuf', 'account', 'dashboard', 'profile', 'user' ];
    }

    /**
     * Retrieve the list of styles the widget depends on
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function get_style_depends() {
        return [ 'wpuf-account', 'wpuf-elementor-account' ];
    }

    /**
     * Register widget controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_controls() {
        // Content Tab
        $this->register_content_controls();

        // Style Tab
        $this->register_container_style_controls();
        $this->register_sidebar_style_controls();
        $this->register_profile_section_style_controls();
        $this->register_edit_profile_btn_style_controls();
        $this->register_nav_style_controls();
        $this->register_logout_style_controls();
        $this->register_content_area_style_controls();
        $this->register_form_style_controls();
        $this->register_table_style_controls();
        $this->register_subscription_card_style_controls();
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
            'account_widget_notice',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => __( 'This widget displays the full WPUF User Account dashboard, including the sidebar navigation, profile section, and all account content tabs (Dashboard, Posts, Subscription, Edit Profile, Billing Address).', 'wp-user-frontend' ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register layout container style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_container_style_controls() {
        $this->start_controls_section(
            'section_container_style',
            [
                'label' => __( 'Layout Container', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'container_background',
                'label'    => __( 'Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-account-container',
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '16',
                    'right'    => '16',
                    'bottom'   => '16',
                    'left'     => '16',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_gap',
            [
                'label'      => __( 'Gap Between Sidebar and Content', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 100 ],
                    'em' => [ 'min' => 0, 'max' => 10 ],
                ],
                'default'    => [
                    'size' => 48,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-container' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'container_border',
                'selector' => '{{WRAPPER}} .wpuf-account-container',
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'container_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-account-container',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register sidebar style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_sidebar_style_controls() {
        $this->start_controls_section(
            'section_sidebar_style',
            [
                'label' => __( 'Sidebar', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'sidebar_background',
                'label'    => __( 'Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-account-sidebar',
            ]
        );

        $this->add_responsive_control(
            'sidebar_width',
            [
                'label'      => __( 'Width', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [ 'min' => 150, 'max' => 600 ],
                    '%'  => [ 'min' => 10, 'max' => 60 ],
                ],
                'default'    => [
                    'size' => 260,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-sidebar' => 'width: {{SIZE}}{{UNIT}}; flex: 0 0 {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sidebar_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '0',
                    'right'    => '0',
                    'bottom'   => '0',
                    'left'     => '0',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-sidebar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'sidebar_border',
                'selector' => '{{WRAPPER}} .wpuf-account-sidebar',
            ]
        );

        $this->add_control(
            'sidebar_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-sidebar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'sidebar_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-account-sidebar',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register profile section style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_profile_section_style_controls() {
        $this->start_controls_section(
            'section_profile_style',
            [
                'label' => __( 'Profile Card', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'profile_section_background',
                'label'    => __( 'Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-profile-section',
            ]
        );

        $this->add_responsive_control(
            'profile_section_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-profile-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'profile_section_border',
                'selector' => '{{WRAPPER}} .wpuf-profile-section',
            ]
        );

        $this->add_control(
            'profile_section_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-profile-section' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'profile_heading_avatar',
            [
                'label'     => __( 'Avatar', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'avatar_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'      => '50',
                    'right'    => '50',
                    'bottom'   => '50',
                    'left'     => '50',
                    'unit'     => '%',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-profile-avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'avatar_margin_bottom',
            [
                'label'      => __( 'Margin Bottom', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
                'default'    => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-profile-avatar' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'profile_heading_name',
            [
                'label'     => __( 'Display Name', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'profile_name_color',
            [
                'label'     => __( 'Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-profile-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'profile_name_typography',
                'selector' => '{{WRAPPER}} .wpuf-profile-name',
            ]
        );

        $this->add_responsive_control(
            'profile_name_margin_bottom',
            [
                'label'      => __( 'Margin Bottom', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'default'    => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-profile-name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'profile_heading_role',
            [
                'label'     => __( 'Role Text', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'profile_role_color',
            [
                'label'     => __( 'Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#9CA3AF',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-profile-role' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'profile_role_typography',
                'selector' => '{{WRAPPER}} .wpuf-profile-role',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register edit profile button style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_edit_profile_btn_style_controls() {
        $btn_selector = '{{WRAPPER}} .wpuf-edit-profile-btn';

        $this->start_controls_section(
            'section_edit_profile_btn_style',
            [
                'label' => __( 'Edit Profile Button', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'tabs_edit_profile_btn' );

        $this->start_controls_tab( 'tab_edit_profile_btn_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'edit_profile_btn_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [ $btn_selector => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'edit_profile_btn_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#FFFFFF',
                'selectors' => [ $btn_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'edit_profile_btn_typography',
                'selector' => $btn_selector,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'edit_profile_btn_border',
                'selector' => $btn_selector,
            ]
        );

        $this->add_control(
            'edit_profile_btn_border_radius',
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
                    $btn_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'edit_profile_btn_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '10',
                    'right'    => '24',
                    'bottom'   => '10',
                    'left'     => '24',
                    'unit'     => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    $btn_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'edit_profile_btn_box_shadow',
                'selector' => $btn_selector,
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_edit_profile_btn_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'edit_profile_btn_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $btn_selector . ':hover' => 'background-color: {{VALUE}} !important;' ],
            ]
        );

        $this->add_control(
            'edit_profile_btn_hover_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $btn_selector . ':hover' => 'color: {{VALUE}} !important;' ],
            ]
        );

        $this->add_control(
            'edit_profile_btn_hover_border_color',
            [
                'label'     => __( 'Border Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $btn_selector . ':hover' => 'border-color: {{VALUE}} !important;' ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register navigation menu style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_nav_style_controls() {
        $item_selector        = '{{WRAPPER}} .wpuf-account-nav-item';
        $item_active_selector = '{{WRAPPER}} .wpuf-account-nav-item.active';

        $this->start_controls_section(
            'section_nav_style',
            [
                'label' => __( 'Navigation Menu', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'nav_items_gap',
            [
                'label'      => __( 'Items Gap', 'wp-user-frontend' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-nav ul' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'nav_heading_normal',
            [
                'label'     => __( 'Nav Item', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( 'tabs_nav_item' );

        $this->start_controls_tab( 'tab_nav_item_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'nav_item_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => 'transparent',
                'selectors' => [ $item_selector => 'background-color: {{VALUE}} !important;' ],
            ]
        );

        $this->add_control(
            'nav_item_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#374151',
                'selectors' => [ $item_selector => 'color: {{VALUE}} !important;' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'nav_item_typography',
                'selector' => $item_selector,
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_nav_item_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'nav_item_hover_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $item_selector . ':hover' => 'background-color: {{VALUE}} !important;' ],
            ]
        );

        $this->add_control(
            'nav_item_hover_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $item_selector . ':hover' => 'color: {{VALUE}} !important;' ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_nav_item_active', [ 'label' => __( 'Active', 'wp-user-frontend' ) ] );

        $this->add_control(
            'nav_item_active_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#F3F4F6',
                'selectors' => [ $item_active_selector => 'background-color: {{VALUE}} !important;' ],
            ]
        );

        $this->add_control(
            'nav_item_active_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [ $item_active_selector => 'color: {{VALUE}} !important;' ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'nav_item_border_radius',
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
                    $item_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'nav_item_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '12',
                    'right'    => '16',
                    'bottom'   => '12',
                    'left'     => '16',
                    'unit'     => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    $item_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register logout section style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_logout_style_controls() {
        $link_selector = '{{WRAPPER}} .wpuf-logout-link';

        $this->start_controls_section(
            'section_logout_style',
            [
                'label' => __( 'Logout', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'logout_section_background',
                'label'    => __( 'Section Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-logout-section',
            ]
        );

        $this->add_responsive_control(
            'logout_section_padding',
            [
                'label'      => __( 'Section Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '0',
                    'right'    => '0',
                    'bottom'   => '0',
                    'left'     => '0',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-logout-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs( 'tabs_logout_link' );

        $this->start_controls_tab( 'tab_logout_link_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'logout_link_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#DC2626',
                'selectors' => [ $link_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'logout_link_typography',
                'selector' => $link_selector,
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_logout_link_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'logout_link_hover_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ $link_selector . ':hover' => 'color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register main content area style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_content_area_style_controls() {
        $this->start_controls_section(
            'section_content_area_style',
            [
                'label' => __( 'Content Area', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'content_area_background',
                'label'    => __( 'Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-account-content',
            ]
        );

        $this->add_responsive_control(
            'content_area_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '24',
                    'right'    => '24',
                    'bottom'   => '24',
                    'left'     => '24',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'content_area_border',
                'selector' => '{{WRAPPER}} .wpuf-account-content',
            ]
        );

        $this->add_control(
            'content_area_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'content_area_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-account-content',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register form style controls (Edit Profile / Billing Address sections)
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_form_style_controls() {
        $input_selector = '{{WRAPPER}} .wpuf-form-input, {{WRAPPER}} .wpuf-edit-profile-form input[type="text"], {{WRAPPER}} .wpuf-edit-profile-form input[type="email"], {{WRAPPER}} .wpuf-edit-profile-form input[type="password"], {{WRAPPER}} .wpuf-edit-profile-form input[type="number"], {{WRAPPER}} .wpuf-edit-profile-form select, {{WRAPPER}} .wpuf-edit-profile-form textarea';

        $this->start_controls_section(
            'section_form_style',
            [
                'label' => __( 'Forms', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_label_color',
            [
                'label'     => __( 'Label Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_label_typography',
                'label'    => __( 'Label Typography', 'wp-user-frontend' ),
                'selector' => '{{WRAPPER}} .wpuf-form-label',
            ]
        );

        $this->add_control(
            'form_heading_input',
            [
                'label'     => __( 'Input Fields', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( 'tabs_form_input' );

        $this->start_controls_tab( 'tab_form_input_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'form_input_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#FFFFFF',
                'selectors' => [ $input_selector => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'form_input_text_color',
            [
                'label'     => __( 'Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [ $input_selector => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_input_typography',
                'selector' => $input_selector,
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_input_border',
                'selector' => $input_selector,
            ]
        );

        $this->add_control(
            'form_input_border_radius',
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
                    $input_selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_input_padding',
            [
                'label'      => __( 'Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '9',
                    'right'    => '13',
                    'bottom'   => '9',
                    'left'     => '13',
                    'unit'     => 'px',
                    'isLinked' => false,
                ],
                'selectors'  => [
                    $input_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_form_input_focus', [ 'label' => __( 'Focus', 'wp-user-frontend' ) ] );

        $this->add_control(
            'form_input_focus_bg_color',
            [
                'label'     => __( 'Background Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-form-input:focus, {{WRAPPER}} .wpuf-edit-profile-form input[type="text"]:focus, {{WRAPPER}} .wpuf-edit-profile-form input[type="email"]:focus, {{WRAPPER}} .wpuf-edit-profile-form input[type="password"]:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_input_focus_border',
                'selector' => '{{WRAPPER}} .wpuf-form-input:focus, {{WRAPPER}} .wpuf-edit-profile-form input[type="text"]:focus, {{WRAPPER}} .wpuf-edit-profile-form input[type="email"]:focus, {{WRAPPER}} .wpuf-edit-profile-form input[type="password"]:focus',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'form_heading_buttons',
            [
                'label'     => __( 'Buttons', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs( 'tabs_form_primary_btn' );

        $this->start_controls_tab( 'tab_form_primary_btn_normal', [ 'label' => __( 'Normal', 'wp-user-frontend' ) ] );

        $this->add_control(
            'form_primary_btn_bg_color',
            [
                'label'     => __( 'Primary Button Background', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#99A7B2',
                'selectors' => [ '{{WRAPPER}} .wpuf-btn-primary' => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'form_primary_btn_text_color',
            [
                'label'     => __( 'Primary Button Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#FFFFFF',
                'selectors' => [ '{{WRAPPER}} .wpuf-btn-primary' => 'color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab( 'tab_form_primary_btn_hover', [ 'label' => __( 'Hover', 'wp-user-frontend' ) ] );

        $this->add_control(
            'form_primary_btn_hover_bg_color',
            [
                'label'     => __( 'Primary Button Background', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wpuf-btn-primary:hover' => 'background-color: {{VALUE}};' ],
            ]
        );

        $this->add_control(
            'form_primary_btn_hover_text_color',
            [
                'label'     => __( 'Primary Button Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .wpuf-btn-primary:hover' => 'color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_btn_typography',
                'label'     => __( 'Button Typography', 'wp-user-frontend' ),
                'selector'  => '{{WRAPPER}} .wpuf-btn-primary, {{WRAPPER}} .wpuf-btn-secondary',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'form_btn_border_radius',
            [
                'label'      => __( 'Button Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-btn-primary, {{WRAPPER}} .wpuf-btn-secondary' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_btn_padding',
            [
                'label'      => __( 'Button Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-btn-primary, {{WRAPPER}} .wpuf-btn-secondary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register posts table style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_table_style_controls() {
        $this->start_controls_section(
            'section_table_style',
            [
                'label' => __( 'Posts Table', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_heading_color',
            [
                'label'     => __( 'Column Heading Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-account-content .wpuf-font-semibold' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'table_row_background',
                'label'    => __( 'Row Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-account-content .wpuf-border-b',
            ]
        );

        $this->add_control(
            'table_row_text_color',
            [
                'label'     => __( 'Row Text Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#374151',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-account-content .wpuf-text-gray-700' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .wpuf-account-content .wpuf-text-gray-600' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_edit_link_color',
            [
                'label'     => __( 'Edit Link Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-posts-edit' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_delete_link_color',
            [
                'label'     => __( 'Delete Link Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-posts-delete' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'table_border',
                'label'     => __( 'Table Border', 'wp-user-frontend' ),
                'selector'  => '{{WRAPPER}} .wpuf-account-content .wpuf-border, {{WRAPPER}} .wpuf-account-content .wpuf-border-b',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'table_cell_padding',
            [
                'label'      => __( 'Cell Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-account-content .wpuf-px-4' => 'padding-left: {{LEFT}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}};',
                    '{{WRAPPER}} .wpuf-account-content .wpuf-py-4' => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register subscription card style controls
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function register_subscription_card_style_controls() {
        $this->start_controls_section(
            'section_subscription_card_style',
            [
                'label' => __( 'Subscription Card', 'wp-user-frontend' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'sub_card_background',
                'label'    => __( 'Card Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-subscription-card',
            ]
        );

        $this->add_responsive_control(
            'sub_card_padding',
            [
                'label'      => __( 'Card Padding', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'default'    => [
                    'top'      => '24',
                    'right'    => '24',
                    'bottom'   => '24',
                    'left'     => '24',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-subscription-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'sub_card_border',
                'selector' => '{{WRAPPER}} .wpuf-subscription-card',
            ]
        );

        $this->add_control(
            'sub_card_border_radius',
            [
                'label'      => __( 'Border Radius', 'wp-user-frontend' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'default'    => [
                    'top'      => '24',
                    'right'    => '24',
                    'bottom'   => '24',
                    'left'     => '24',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .wpuf-subscription-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'sub_card_box_shadow',
                'selector' => '{{WRAPPER}} .wpuf-subscription-card',
            ]
        );

        $this->add_control(
            'sub_card_heading_header',
            [
                'label'     => __( 'Card Header', 'wp-user-frontend' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'sub_card_header_background',
                'label'    => __( 'Header Background', 'wp-user-frontend' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .wpuf-subscription-card-header',
            ]
        );

        $this->add_control(
            'sub_card_name_color',
            [
                'label'     => __( 'Plan Name Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#1F2A30',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-subscription-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'sub_card_name_typography',
                'label'    => __( 'Plan Name Typography', 'wp-user-frontend' ),
                'selector' => '{{WRAPPER}} .wpuf-subscription-name',
            ]
        );

        $this->add_control(
            'sub_card_price_color',
            [
                'label'     => __( 'Price Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#1F2A30',
                'selectors' => [
                    '{{WRAPPER}} .wpuf-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'sub_card_price_typography',
                'label'    => __( 'Price Typography', 'wp-user-frontend' ),
                'selector' => '{{WRAPPER}} .wpuf-price',
            ]
        );

        $this->add_control(
            'sub_card_status_color',
            [
                'label'     => __( 'Status Badge Color', 'wp-user-frontend' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wpuf-subscription-status' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'sub_card_status_background',
                'label'    => __( 'Status Badge Background', 'wp-user-frontend' ),
                'types'    => [ 'classic' ],
                'selector' => '{{WRAPPER}} .wpuf-subscription-status',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Check if the widget is rendering in the Elementor editor or preview context
     *
     * @since WPUF_SINCE
     *
     * @return bool True when rendering inside the editor or preview, false otherwise.
     */
    protected function is_editor_render() {
        return class_exists( '\Elementor\Plugin' ) && (
            ( isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() )
            || ( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() )
        );
    }

    /**
     * Render the widget output on the frontend
     *
     * @since WPUF_SINCE
     *
     * @return void
     */
    protected function render() {
        if ( $this->is_editor_render() ) {
            wp_enqueue_style( 'wpuf-account' );
            wp_enqueue_style( 'wpuf-elementor-account' );
            wp_enqueue_script( 'wpuf-account' );
        }

        echo '<div class="wpuf-elementor-account-wrapper">';
        echo do_shortcode( '[wpuf_account]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output

        /**
         * Fires after the account widget has rendered its output.
         *
         * @since WPUF_SINCE
         *
         * @param \Elementor\Widget_Base $this The widget instance.
         */
        do_action( 'wpuf_elementor_account_widget_after_render', $this );
        echo '</div>';
    }

    /**
     * Render the widget output in the editor.
     *
     * Intentionally empty — Elementor falls back to render() for live preview,
     * which is correct since the admin is logged in and the real account page renders.
     *
     * @since WPUF_SINCE
     */
    protected function content_template() {}
}
