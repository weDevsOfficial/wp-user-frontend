<?php

namespace WeDevs\Wpuf\Admin\Forms\Post\Templates;

/**
 * Press Release post form template preview
 */
class Pro_Form_Preview_Press_Release {
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
        $this->title    = __( 'News/Press Release Submission', 'wp-user-frontend' );
        $this->image    = WPUF_ASSET_URI . '/images/templates/press-release.svg';
        $this->pro_icon = WPUF_ASSET_URI . '/images/templates/crown.svg';
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