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

wpuf_upgrade_2_7_taxonomy_restriction();
wpuf_upgrade_2_7_unset_oembed_cache();