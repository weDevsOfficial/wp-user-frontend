<?php

function wpuf_upgrade_4_0_4_migration() {
	$args = [
		'post_type'   => 'wpuf_input',
		'numberposts' => -1,
	];

	$input_fields = get_posts( $args );

	if ( empty( $input_fields ) ) {
		return;
	}

	foreach ( $input_fields as $field ) {
		if ( empty( $field->post_content ) ) {
			continue;
		}

		$content = maybe_unserialize( $field->post_content );

		if ( ! empty( $content['input_type'] ) && 'column_field' === $content['input_type'] && ! isset( $content['wpuf_visibility'] ) ) {
			$content['wpuf_visibility'] = [
				'selected' => 'everyone',
				'choices'  => [],
			];

			$field->post_content = maybe_serialize( $content );

			wp_update_post( $field );
		}
	}
}

wpuf_upgrade_4_0_4_migration();
