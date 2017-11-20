<?php

/**
 * Add necessary metas for taxonomy restriction in pro version
 *
 * @return void
 */
function wpuf_upgrade_2_7_taxonomy_restriction() {

    if ( class_exists( 'WP_User_Frontend_Pro' ) ) {
        $subscriptions  = WPUF_Subscription::init()->get_subscriptions();
        $allowed_term = array();

        foreach ( $subscriptions as $pack ) {
            if ( ! metadata_exists( 'post', $pack->ID , '_sub_allowed_term_ids' ) ) {
                $cts = get_taxonomies(array('_builtin'=>true), 'objects'); ?>
                <?php foreach ($cts as $ct) { 
                    if ( is_taxonomy_hierarchical( $ct->name ) ) {
                        $tax_terms = get_terms ( array(
                            'taxonomy' => $ct->name,
                            'hide_empty' => false,
                        ) );
                        foreach ($tax_terms as $tax_term) {
                            $allowed_term[] = $tax_term->term_id;
                        }
                    }
                }

                $cts = get_taxonomies(array('_builtin'=>false), 'objects'); ?>
                <?php foreach ($cts as $ct) { 
                    if ( is_taxonomy_hierarchical( $ct->name ) ) {
                        $tax_terms = get_terms ( array(
                            'taxonomy' => $ct->name,
                            'hide_empty' => false,
                        ) );
                        foreach ($tax_terms as $tax_term) {
                            $allowed_term[] = $tax_term->term_id;
                        }
                    }
                }

                update_post_meta( $pack->ID, '_sub_allowed_term_ids', $allowed_term ); 
            }
        }       
    }

}

wpuf_upgrade_2_7_taxonomy_restriction();