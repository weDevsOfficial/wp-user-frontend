<?php
/**
 * WooCommerce Attribute Label Customizations for WP User Frontend
 *
 * @package WPUF
 */

// Hook into WordPress init to register our filter
add_action( 'init', 'wpuf_init_woocommerce_attribute_labels' );

/**
 * Initialize WooCommerce attribute label filters
 */
function wpuf_init_woocommerce_attribute_labels() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }
    
    add_filter( 'woocommerce_attribute_label', 'wpuf_customize_woocommerce_attribute_labels', 20, 3 );
}

/**
 * Customize WooCommerce attribute labels based on form field labels
 *
 * @param string $label The attribute label
 * @param string $name The attribute name  
 * @param WC_Product|null $product The product object
 * @return string Modified label
 */
function wpuf_customize_woocommerce_attribute_labels( $label, $name, $product ) {
    // First check if we have stored labels for this specific product
    if ( $product && is_a( $product, 'WC_Product' ) ) {
        $taxonomy_labels = get_post_meta( $product->get_id(), '_wpuf_taxonomy_labels', true );
        
        if ( ! empty( $taxonomy_labels ) && isset( $taxonomy_labels[ $name ] ) ) {
            return $taxonomy_labels[ $name ];
        }
    }
    
    // If this is being displayed in admin or frontend without a product context,
    // or if no stored label exists, use proper default labels for known taxonomies
    $default_labels = [
        'product_shipping_class' => __( 'Product shipping class', 'wp-user-frontend' ),
        'product_visibility' => __( 'Product visibility', 'wp-user-frontend' ),
        'product_type' => __( 'Product Type', 'wp-user-frontend' ),
    ];
    
    if ( isset( $default_labels[ $name ] ) ) {
        return $default_labels[ $name ];
    }
    
    return $label;
}