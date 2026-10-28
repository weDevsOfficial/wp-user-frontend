<?php
/**
 * Form field store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

/**
 * The one writer of form fields: `wpuf_input` child posts of a form, one per
 * top-level field (column and repeat inner fields live inside their parent's
 * content), ordered by `menu_order`.
 *
 * @since WPUF_SINCE
 */
class FieldStore {

    /**
     * Fields of a form, as wpuf_get_form_fields() reads them.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return array
     */
    public function read( $form_id ) {
        return wpuf_get_form_fields( $form_id );
    }

    /**
     * Insert or update one field post.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id  Form id
     * @param array $field    Field settings
     * @param int   $field_id Existing field post id (0 inserts)
     * @param int   $order    Position
     * @param bool  $unslash  Unslash before storing (builder and API input);
     *                        form templates store their fields as given
     *
     * @return int|\WP_Error Field post id
     */
    public function write( $form_id, $field, $field_id = 0, $order = 0, $unslash = true ) {
        $args = [
            'post_type'    => 'wpuf_input',
            'post_parent'  => $form_id,
            'post_status'  => 'publish',
            'post_content' => maybe_serialize( $unslash ? wp_unslash( $field ) : $field ),
            'menu_order'   => $order,
        ];

        if ( $field_id ) {
            $args['ID'] = $field_id;

            return wp_update_post( $args );
        }

        return wp_insert_post( $args );
    }

    /**
     * Save a form's fields as the builder sends them: new fields (`is_new`) are
     * inserted, stored ones updated in place (not rewritten when unchanged), and
     * stored fields left out of the save are deleted, except custom taxonomy
     * fields the builder hides while Pro is inactive.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param array $fields  Fields in order
     *
     * @return array Saved fields, with their post id and `is_new` false
     */
    public function save( $form_id, $fields ) {
        $saved    = [];
        $kept_ids = [];
        $existing = get_children(
            [
                'post_parent' => $form_id,
                'post_status' => 'publish',
                'post_type'   => 'wpuf_input',
                'numberposts' => '-1',
                'orderby'     => 'menu_order',
                'order'       => 'ASC',
                'fields'      => 'ids',
            ]
        );

        $existing_ids = array_map( 'intval', $existing );

        foreach ( (array) $fields as $order => $field ) {
            $stored_id = isset( $field['id'] ) ? absint( $field['id'] ) : 0;

            // A field whose id is a stored field of this form is existing, also
            // when old data carries `is_new` (sample forms stored it): it keeps
            // its post instead of being inserted again.
            if ( $stored_id && in_array( $stored_id, $existing_ids, true ) ) {
                if ( ! empty( $field['is_new'] ) ) {
                    unset( $field['is_new'], $field['id'] );
                }

                $field_id = $this->update_existing( $stored_id, $field, $order );
            } elseif ( ! empty( $field['is_new'] ) ) {
                unset( $field['is_new'], $field['id'] );
                $field_id = $this->write( $form_id, $field, 0, $order );
            } else {
                // Only a field post of this form can be updated by id: any other
                // post id (a page, an order, another form's field) is never
                // touched, the field is inserted as a new one instead.
                $owned = $stored_id && 'wpuf_input' === get_post_type( $stored_id ) && (int) wp_get_post_parent_id( $stored_id ) === (int) $form_id;

                if ( ! $owned ) {
                    unset( $field['id'] );
                }

                $field_id = $this->write( $form_id, $field, $owned ? $stored_id : 0, $order );
            }

            $kept_ids[] = $field_id;

            $field['id']     = $field_id;
            $field['is_new'] = false;
            $saved[]         = $field;
        }

        $to_delete = array_diff( $existing, $kept_ids );
        $to_delete = array_diff( $to_delete, $this->hidden_pro_taxonomy_ids( $to_delete ) );

        foreach ( $to_delete as $field_id ) {
            wp_delete_post( $field_id, true );
        }

        return $saved;
    }

    /**
     * Update a stored field, leaving its stored content alone when the builder
     * sent it back unchanged (an untouched save keeps stored values): only a new
     * position is written then.
     *
     * @param int   $field_id Field post id
     * @param array $field    Submitted field
     * @param int   $order    Position
     *
     * @return int|\WP_Error Field post id
     */
    private function update_existing( $field_id, $field, $order ) {
        $stored = maybe_unserialize( get_post_field( 'post_content', $field_id ) );

        // A stored `is_new` (sample forms) is a builder marker: rewrite in place without it.
        if ( is_array( $stored ) && ! empty( $stored['is_new'] ) ) {
            unset( $field['is_new'], $field['id'] );
        } elseif ( is_array( $stored ) && $this->same_field( wp_unslash( $field ), $stored ) ) {
            if ( (int) get_post_field( 'menu_order', $field_id ) !== (int) $order ) {
                wp_update_post(
                    [
                        'ID'         => $field_id,
                        'menu_order' => $order,
                    ]
                );
            }

            return $field_id;
        }

        return $this->write( get_post_field( 'post_parent', $field_id ), $field, $field_id, $order );
    }

    /**
     * Whether a submitted field holds the stored values: the builder's own
     * tracking keys (`id`, `is_new`) and keys it added with an empty value are
     * not stored data.
     *
     * @param array $submitted Submitted field (unslashed)
     * @param array $stored    Stored field
     *
     * @return bool
     */
    private function same_field( $submitted, $stored ) {
        unset( $submitted['id'], $submitted['is_new'], $stored['id'], $stored['is_new'] );

        foreach ( $submitted as $key => $value ) {
            if ( ! array_key_exists( $key, $stored ) && ( '' === $value || [] === $value || null === $value ) ) {
                unset( $submitted[ $key ] );
            }
        }

        return $submitted === $stored;
    }

    /**
     * Copy every field of one form to another, in order.
     *
     * @since WPUF_SINCE
     *
     * @param int $from_form_id Source form id
     * @param int $to_form_id   Target form id
     *
     * @return void
     */
    public function copy( $from_form_id, $to_form_id ) {
        foreach ( $this->read( $from_form_id ) as $field ) {
            $this->write( $to_form_id, $field );
        }
    }

    /**
     * Rewrite a form's fields by position: the stored field posts (any status,
     * by menu_order) are updated in place, extra fields inserted, surplus
     * posts deleted. Fields are stored as given (no unslash), as the AI form
     * builder writes them.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param array $fields  Fields in order
     *
     * @return void
     */
    public function replace( $form_id, array $fields ) {
        $existing = get_children(
            [
                'post_parent' => $form_id,
                'post_type'   => 'wpuf_input',
                'post_status' => 'any',
                'numberposts' => -1,
                'orderby'     => 'menu_order',
                'order'       => 'ASC',
                'fields'      => 'ids',
            ]
        );
        $existing = array_values( array_map( 'intval', $existing ) );
        $fields   = array_values( $fields );

        foreach ( $fields as $order => $field ) {
            $this->write( $form_id, $field, isset( $existing[ $order ] ) ? $existing[ $order ] : 0, $order, false );
        }

        foreach ( array_slice( $existing, count( $fields ) ) as $field_id ) {
            wp_delete_post( $field_id, true );
        }
    }

    /**
     * Delete every field post of a form (any status).
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return void
     */
    public function delete_all( $form_id ) {
        $field_ids = get_children(
            [
                'post_parent' => $form_id,
                'post_type'   => 'wpuf_input',
                'post_status' => 'any',
                'numberposts' => -1,
                'fields'      => 'ids',
            ]
        );

        foreach ( $field_ids as $field_id ) {
            wp_delete_post( $field_id, true );
        }
    }

    /**
     * Field post ids holding a custom taxonomy field the builder hides while Pro
     * is inactive (its absence from a save is not a removal, task 1.11).
     *
     * @since WPUF_SINCE
     *
     * @param int[] $field_ids Field post ids
     *
     * @return int[]
     */
    public function hidden_pro_taxonomy_ids( $field_ids ) {
        if ( empty( $field_ids ) || wpuf_is_pro_active() ) {
            return [];
        }

        $free_taxonomies = wpuf_get_free_taxonomies();
        $hidden          = [];

        foreach ( $field_ids as $field_id ) {
            $field = maybe_unserialize( get_post_field( 'post_content', $field_id ) );

            if (
                is_array( $field )
                && isset( $field['input_type'], $field['name'] )
                && 'taxonomy' === $field['input_type']
                && ! in_array( $field['name'], $free_taxonomies, true )
            ) {
                $hidden[] = $field_id;
            }
        }

        return $hidden;
    }
}
