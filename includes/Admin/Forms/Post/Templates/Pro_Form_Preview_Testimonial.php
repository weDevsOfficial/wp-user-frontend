<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

/**
 * Testimonial submission form template preview
 *
 * @since 4.3.2
 */
class Pro_Form_Preview_Testimonial {

    /**
     * Template title
     *
     * @var string
     */
    public $title;

    /**
     * Form Template Image
     *
     * @var string
     */
    public $image;

    /**
     * @var string
     */
    private $pro_icon;

    public function __construct() {
        $this->title    = __( 'Testimonial Submission', 'wp-user-frontend' );
        $this->image    = WPUF_ASSET_URI . '/images/templates/testimonial.svg';
        $this->pro_icon = WPUF_ASSET_URI . '/images/templates/crown.svg';
    }

    /**
     * Get the template title
     *
     * @since 4.3.2
     *
     * @return string
     */
    public function get_title() {
        return $this->title ? $this->title : '';
    }

    /**
     * Get the template image
     *
     * @since 4.3.2
     *
     * @return string
     */
    public function get_image() {
        return $this->image ? $this->image : '';
    }

    /**
     * Get the pro icon
     *
     * @since 4.3.2
     *
     * @return string
     */
    public function get_pro_icon() {
        return $this->pro_icon ? $this->pro_icon : '';
    }
}
