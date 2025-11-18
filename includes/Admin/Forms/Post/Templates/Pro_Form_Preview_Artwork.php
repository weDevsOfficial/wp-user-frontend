<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

/**
 * Professional Artwork post form template preview
 */
class Pro_Form_Preview_Artwork {
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
        $this->title    = __( 'Professional Artwork Submission', 'wp-user-frontend' );
        $this->image    = WPUF_ASSET_URI . '/images/templates/artwork.svg';
        $this->pro_icon = WPUF_ASSET_URI . '/images/templates/pro-badge.svg';
    }

    /**
     * Get the template title
     *
     * @return string
     */
    public function get_title() {
        return $this->title ? $this->title : '';
    }

    /**
     * Get the template image
     *
     * @return string
     */
    public function get_image() {
        return $this->image ? $this->image : '';
    }

    /**
     * Get the pro icon
     *
     * @return string
     */
    public function get_pro_icon() {
        return $this->pro_icon ? $this->pro_icon : '';
    }
}