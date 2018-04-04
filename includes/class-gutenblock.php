<?php

if ( ! defined( 'ABSPATH' ) )
    exit;
/**
 * Adds WPUF Forms widget.
 */
class WPUF_Form_Block {

    public function __construct() {
        // load the preview information and form
        add_action( 'wp_head', array( $this, 'load_preview_data' ) );
        add_action( 'enqueue_block_editor_assets', array ( $this, 'load_block_assets' ) );
    }

    function load_block_assets() {
        $js_dir  = WPUF_ASSET_URI . '/js/admin/';
        $css_dir = WPUF_ASSET_URI . '/css/admin/';

        $block_logo = $thumbnail_logo = WPUF_ASSET_URI . '/images/icon-128x128.png';

        // Once we have Gutenberg block javascript, we can enqueue our assets
        wp_register_script(
            'wpuf_block',
            $js_dir . 'gutenblock.js',
            array( 'wp-blocks', 'wp-i18n', 'wp-element', 'underscore' )
        );

        wp_register_style(
            'wpuf-block-editor',
            $css_dir . 'gutenblock.css'
        );

        /**
         * we need to get our forms so that the block can build a dropdown
         * with the forms
         * */
        wp_enqueue_script( 'wpuf_block' );
        wp_enqueue_style( 'wpuf-block-editor' );

        $forms = array();
        $forms[] = array (
            'value' => '',
            'label' => __('-- Select a Form --', 'wpuf' ),
        );

        $all_forms = wpuf()->forms->get_forms( array( 'post_status' => 'publish' ) );

        foreach ( $all_forms['forms'] as $form ) {
            $forms[] = array (
                'value' => $form->id,
                'label' => $form->get_title(),
            );
        }

        wp_localize_script( 'wpuf_block', 'wpufblock', array(
            'forms'          => $forms,
            'siteUrl'        => get_site_url(),
            'block_logo'     => $block_logo,
            'thumbnail_logo' => $thumbnail_logo
        ) );
    }

    public function load_preview_data() {
        // check for preview and iframe get parameters
        if ( isset( $_GET[ 'wpuf_preview' ] ) && isset( $_GET[ 'wpuf_iframe' ] ) ) {
            ?>
            <style media="screen">
                #wpadminbar {
                    display: none;
                }
                header{
                    display: none;
                }
                .wpuf-form-add {
                    z-index: 9001;
                    position: fixed !important;
                    top: 0; left: 0;
                    width: 100vw;
                    height: 100vh;
                    background-color: #ffffff;
                    /* overflow-x: hidden; */
                }
            </style>
            <script type="text/javascript">
                jQuery( document ).ready( function() {
                    var frameEl = window.frameElement;
                    // get the form element
                    var $form = jQuery('.wpuf-form-add');
                    // get the height of the form
                    var height = $form.find( '.wpuf-form' ).outerHeight(true);

                    if ( frameEl ) {
                        frameEl.height = height + 50;
                    }
                });
            </script>
            <?php
        }
    }
}
