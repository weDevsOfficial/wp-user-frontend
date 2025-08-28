<?php

namespace WeDevs\Wpuf\Integrations;

/**
 * WooCommerce Gallery Sync Integration
 * 
 * Ensures product gallery images are properly synced between WPUF and WooCommerce
 */
class WPUF_WooCommerce_Gallery_Sync {
    
    public function __construct() {
        // Hook into WPUF post insert/update actions
        add_action( 'wpuf_add_post_after_insert', [ $this, 'sync_product_gallery' ], 20, 4 );
        add_action( 'wpuf_edit_post_after_update', [ $this, 'sync_product_gallery' ], 20, 4 );
        
        // Also hook into WordPress save_post for products
        add_action( 'save_post_product', [ $this, 'sync_gallery_on_save' ], 20 );
    }
    
    /**
     * Sync product gallery after WPUF form submission
     *
     * @param int   $post_id
     * @param int   $form_id
     * @param array $form_settings
     * @param array $meta_vars
     */
    public function sync_product_gallery( $post_id, $form_id, $form_settings, $meta_vars = [] ) {
        // Only process if it's a product
        if ( get_post_type( $post_id ) !== 'product' ) {
            return;
        }
        
        $this->sync_gallery_images( $post_id );
    }
    
    /**
     * Sync gallery when product is saved
     *
     * @param int $post_id
     */
    public function sync_gallery_on_save( $post_id ) {
        // Avoid infinite loops
        remove_action( 'save_post_product', [ $this, 'sync_gallery_on_save' ], 20 );
        
        $this->sync_gallery_images( $post_id );
        
        // Re-add the action
        add_action( 'save_post_product', [ $this, 'sync_gallery_on_save' ], 20 );
    }
    
    /**
     * Sync gallery images from _product_image to _product_image_gallery
     *
     * @param int $post_id
     */
    private function sync_gallery_images( $post_id ) {
        $images = get_post_meta( $post_id, '_product_image', true );
        
        if ( ! empty( $images ) ) {
            // Handle serialized data
            if ( is_string( $images ) && is_serialized( $images ) ) {
                $images = maybe_unserialize( $images );
            }
            
            // Ensure we have an array
            if ( ! is_array( $images ) ) {
                $images = [ $images ];
            }
            
            // Filter out empty values
            $images = array_filter( $images, function( $img ) {
                return ! empty( $img ) && ( is_numeric( $img ) || is_string( $img ) );
            });
            
            // Update WooCommerce gallery meta
            if ( ! empty( $images ) ) {
                update_post_meta( $post_id, '_product_image_gallery', implode( ',', $images ) );
            }
        }
    }
}