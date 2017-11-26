<?php

/**
 * Add necessary metas for taxonomy restriction in pro version
 *
 * @return void
 */
function wpuf_upgrade_2_7_taxonomy_restriction() {

    wpuf_set_all_terms_as_allowed();

}

wpuf_upgrade_2_7_taxonomy_restriction();