<?php

/**
 * Add necessary metas for taxonomy restriction in pro version
 *
 * @return void
 */
function wpuf_upgrade_2_7_taxonomy_restriction() {

    wpuf_set_all_terms_as_allowed();

}

function wpuf_upgrade_2_7_unset_oembed_cache() {
	$post_types = get_post_types();
	unset($post_types['oembed_cache']);
}

function wpuf_upgrade_2_7_fallback_cost_migration() {
    $args = array(
        'post_type'     => 'wpuf_forms',
        'post_status'   => 'publish',
    );

    $allforms = get_posts($args);

    if ( $allforms ) {
        foreach ($allforms as $form) {

            $old_form = new WPUF_Form( $form->ID );
            $old_form_settings = $old_form->get_settings();

            unset( $old_form_settings['fallback_ppp_enable'] );
            unset( $old_form_settings['fallback_ppp_cost'] );

            $old_form_settings['fallback_ppp_enable'] = isset( $old_form_settings['fallback_ppp_enable'] ) ? $old_form_settings['fallback_ppp_enable'] : false;
            $old_form_settings['fallback_ppp_cost'] = isset( $old_form_settings['fallback_ppp_cost'] ) ? $old_form_settings['fallback_ppp_cost'] : 1;

            update_post_meta( $form->ID, 'wpuf_form_settings', $old_form_settings );
        }
    }

}

wpuf_upgrade_2_7_taxonomy_restriction();
wpuf_upgrade_2_7_unset_oembed_cache();
wpuf_upgrade_2_7_fallback_cost_migration();