<?php
/**
 * Easy Digital Downloads post form template preview
 */
class WPUF_Pro_Form_Preview_EDD {
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

    public function __construct() {
        $this->title    = __( 'EDD Download', 'wp-user-frontend' );
        $this->image    = WPUF_ASSET_URI . '/images/templates/edd.png';
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
