<?php
/**
 * Form store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WeDevs\Wpuf\Platform\Contracts\DataStore;
use WeDevs\Wpuf\Platform\Models\Form;
use WP_Error;

/**
 * The one writer of forms (`wpuf_forms`, `wpuf_profile` posts, their
 * `wpuf_form_settings`, `notifications`, `integrations` and `wpuf_form_version`
 * meta, and their fields through FieldStore). The legacy entry points forward
 * here and keep their signatures, return values, hooks and stored bytes.
 *
 * @since WPUF_SINCE
 */
class FormStore implements DataStore {

    use QueriesPosts;

    /**
     * Field store.
     *
     * @var FieldStore
     */
    private $fields;

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     *
     * @param FieldStore $fields Field store
     */
    public function __construct( FieldStore $fields ) {
        $this->fields = $fields;
    }

    /**
     * Post types the form builder may save into (filter kept from the AJAX save).
     *
     * @since WPUF_SINCE
     *
     * @return string[]
     */
    public function post_types() {
        /**
         * Filters the post types the form builder may save into.
         *
         * @since 4.0.0
         *
         * @param string[] $post_types Form post types
         */
        return (array) apply_filters( 'wpuf_form_builder_save_post_types', [ 'wpuf_forms', 'wpuf_profile' ] );
    }

    /**
     * Meta keys form settings may be stored under (filter kept from the AJAX save).
     *
     * @since WPUF_SINCE
     *
     * @return string[]
     */
    public function settings_meta_keys() {
        /**
         * Filters the meta keys form settings may be stored under.
         *
         * @since 4.0.0
         *
         * @param string[] $meta_keys Meta keys
         */
        return (array) apply_filters( 'wpuf_form_builder_settings_meta_keys', [ 'wpuf_form_settings' ] );
    }

    /**
     * Whether a post is a form the builder may write.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Post id
     *
     * @return bool
     */
    public function is_form( $form_id ) {
        return in_array( get_post_type( absint( $form_id ) ), $this->post_types(), true );
    }

    /**
     * A form with its fields, settings, notifications and integrations.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return array|null
     */
    public function read( $form_id ) {
        if ( ! $this->is_form( $form_id ) ) {
            return null;
        }

        return [
            'post'          => get_post( $form_id ),
            'fields'        => $this->fields->read( $form_id ),
            'settings'      => wpuf_get_form_settings( $form_id ),
            'notifications' => wpuf_get_form_notifications( $form_id ),
            'integrations'  => get_post_meta( $form_id, 'integrations', true ),
        ];
    }

    /**
     * Whether the id is a builder form (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Id
     *
     * @return bool
     */
    public function exists( $id ) {
        return $this->is_form( $id );
    }

    /**
     * A form as a model (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id Form id
     *
     * @return Form|null
     */
    public function find( $id ) {
        $read = $this->read( $id );

        return null === $read ? null : Form::from_read( $read );
    }

    /**
     * Forms as models (DataStore). `post_type` narrows to one form type.
     *
     * @since WPUF_SINCE
     *
     * @param array $args Query args
     *
     * @return Form[]
     */
    public function query( array $args = [] ) {
        return array_values( array_filter( array_map( [ $this, 'find' ], $this->query_ids( $this->post_types(), $args ) ) ) );
    }

    /**
     * Number of forms matching the args (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param array $args Query args
     *
     * @return int
     */
    public function count( array $args = [] ) {
        return $this->count_posts( $this->post_types(), $args );
    }

    /**
     * Save a form as the builder does: publish with the title, fields, settings
     * (Pro-only notification settings dropped without Pro), notifications and,
     * when sent, integrations (null keeps the stored ones, task 1.10).
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param array $payload {
     *     @type string     $post_title        Form title
     *     @type array      $form_fields       Fields in order
     *     @type array      $form_settings     Settings
     *     @type string     $form_settings_key Settings meta key (allowlisted)
     *     @type array      $notifications     Notifications
     *     @type array|null $integrations      Integrations, null to keep
     * }
     *
     * @return array|WP_Error Saved fields, or an error for a non-form post or key
     */
    public function save( $form_id, $payload ) {
        $form_id = absint( $form_id );
        $payload = wp_parse_args(
            (array) $payload,
            [
                'post_title'        => '',
                'form_fields'       => [],
                'form_settings'     => [],
                'form_settings_key' => 'wpuf_form_settings',
                'notifications'     => [],
                'integrations'      => null,
            ]
        );

        if ( ! $this->is_form( $form_id ) ) {
            return new WP_Error( 'wpuf_form_invalid_form', __( 'Invalid form id', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        if ( ! in_array( $payload['form_settings_key'], $this->settings_meta_keys(), true ) ) {
            return new WP_Error( 'wpuf_form_invalid_settings', __( 'Invalid form settings', 'wp-user-frontend' ), [ 'status' => 400 ] );
        }

        /**
         * Before a form is saved through the form store.
         *
         * @since WPUF_SINCE
         *
         * @param int   $form_id Form id
         * @param array $payload Save payload
         */
        do_action( 'wpuf_before_form_store_save', $form_id, $payload );

        wp_update_post(
            [
                'ID'          => $form_id,
                'post_status' => 'publish',
                'post_title'  => $payload['post_title'],
            ]
        );

        $saved_fields = $this->fields->save( $form_id, (array) $payload['form_fields'] );

        update_post_meta( $form_id, $payload['form_settings_key'], Normalizers::form_settings( $payload['form_settings'] ) );
        $this->update_list_meta( $form_id, 'notifications', $payload['notifications'] );

        if ( null !== $payload['integrations'] ) {
            $this->update_list_meta( $form_id, 'integrations', $payload['integrations'] );
        }

        /**
         * After the form builder saved a form (Form_Settings_Cleanup listens).
         *
         * @since WPUF_SINCE
         *
         * @param int $form_id Form id
         */
        do_action( 'wpuf_form_builder_save_form', $form_id );

        /**
         * After a form is saved through the form store.
         *
         * @since WPUF_SINCE
         *
         * @param int   $form_id      Form id
         * @param array $saved_fields Saved fields
         * @param array $payload      Save payload
         */
        do_action( 'wpuf_after_form_store_save', $form_id, $saved_fields, $payload );

        return $saved_fields;
    }

    /**
     * Create a form (sample forms, templates, onboarding, installer, API).
     *
     * @since WPUF_SINCE
     *
     * @param array $args {
     *     @type string     $post_title     Title
     *     @type string     $post_type      'wpuf_forms' or 'wpuf_profile'
     *     @type string     $post_status    Status (default publish)
     *     @type int        $post_author    Author (optional)
     *     @type string     $comment_status Comment status (optional)
     *     @type string     $post_content   Content (optional)
     *     @type array      $fields         Fields in order
     *     @type bool       $unslash_fields Unslash fields before storing (default true)
     *     @type array|null $settings       Settings (null or empty: not stored)
     *     @type bool       $version        Store wpuf_form_version (default true)
     *     @type bool       $settings_first Store settings before fields (templates)
     *     @type bool       $store_empty_settings Store settings even when empty (templates)
     *     @type array      $meta           Extra post meta to store, key => value (AI form builder)
     *     @type array|null $notifications  Notifications to store as given (import); null: not stored
     * }
     *
     * @return int|WP_Error Form id
     */
    public function create( $args ) {
        $args = wp_parse_args(
            (array) $args,
            [
                'post_title'           => '',
                'post_type'            => 'wpuf_forms',
                'post_status'          => 'publish',
                'fields'               => [],
                'unslash_fields'       => true,
                'settings'             => null,
                'version'              => true,
                'settings_first'       => false,
                'store_empty_settings' => false,
                'meta'                 => [],
                'notifications'        => null,
            ]
        );

        $post = [
            'post_title'  => $args['post_title'],
            'post_type'   => $args['post_type'],
            'post_status' => $args['post_status'],
        ];

        foreach ( [ 'post_author', 'comment_status', 'ping_status', 'post_content' ] as $key ) {
            if ( isset( $args[ $key ] ) ) {
                $post[ $key ] = $args[ $key ];
            }
        }

        $form_id = wp_insert_post( $post, true );

        if ( is_wp_error( $form_id ) || ! $form_id ) {
            return is_wp_error( $form_id ) ? $form_id : new WP_Error( 'wpuf_form_not_created', __( 'The form could not be created.', 'wp-user-frontend' ) );
        }

        if ( $args['settings_first'] ) {
            $this->store_create_meta( $form_id, $args );
        }

        foreach ( (array) $args['fields'] as $order => $field ) {
            $field_id = $this->fields->write( $form_id, $field, 0, $order, (bool) $args['unslash_fields'] );

            // A field that could not be stored leaves no half-created form behind.
            if ( is_wp_error( $field_id ) || ! $field_id ) {
                $this->delete_permanently( $form_id );

                return new WP_Error(
                    'wpuf_form_field_not_created',
                    sprintf(
                        /* translators: 1: field position, 2: error message */
                        __( 'Failed to create field at position %1$d: %2$s', 'wp-user-frontend' ),
                        $order,
                        is_wp_error( $field_id ) ? $field_id->get_error_message() : __( 'unknown error', 'wp-user-frontend' )
                    )
                );
            }
        }

        if ( ! $args['settings_first'] ) {
            $this->store_create_meta( $form_id, $args );
        }

        return $form_id;
    }

    /**
     * Duplicate a form as a draft titled "Title (#new id)", with its fields,
     * settings, notifications, integrations and version.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Source form id
     *
     * @return int|null New form id (0 when it could not be created, null when
     *                  the source does not exist, as wpuf_duplicate_form())
     */
    public function duplicate( $form_id ) {
        $post = get_post( $form_id );

        if ( ! $post ) {
            return null;
        }

        $new_id = wp_insert_post(
            [
                'post_title'  => $post->post_title,
                'post_type'   => $post->post_type,
                'post_status' => 'draft',
            ]
        );

        $this->fields->copy( $form_id, $new_id );

        wp_update_post(
            [
                'ID'         => $new_id,
                'post_title' => $post->post_title . ' (#' . $new_id . ')',
            ]
        );

        if ( ! $new_id ) {
            return 0;
        }

        update_post_meta( $new_id, 'wpuf_form_settings', wpuf_get_form_settings( $form_id ) );
        update_post_meta( $new_id, 'notifications', wpuf_get_form_notifications( $form_id ) );

        foreach ( [ 'integrations', 'wpuf_form_version' ] as $meta_key ) {
            if ( metadata_exists( 'post', $form_id, $meta_key ) ) {
                update_post_meta( $new_id, $meta_key, get_post_meta( $form_id, $meta_key, true ) );
            }
        }

        return $new_id;
    }

    /**
     * Move a form to the trash, clearing stale trash meta first (as the forms
     * list did).
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return \WP_Post|false|null
     */
    public function trash( $form_id ) {
        delete_post_meta( $form_id, '_wp_trash_meta_status' );
        delete_post_meta( $form_id, '_wp_trash_meta_time' );
        delete_post_meta( $form_id, '_wp_desired_post_slug' );

        return wp_trash_post( $form_id );
    }

    /**
     * Publish a draft form (the forms list's Publish): a draft form on a page
     * shows visitors "publish your form" instead of the form. WordPress's
     * status hooks fire as on any post update.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return int|WP_Error Form id
     */
    public function publish( $form_id ) {
        return $this->update( $form_id, [ 'post_status' => 'publish' ] );
    }

    /**
     * Restore a trashed form to its status before the trash (as the forms list did).
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return int|WP_Error
     */
    public function restore( $form_id ) {
        return wp_update_post(
            [
                'ID'          => $form_id,
                'post_status' => get_post_meta( $form_id, '_wp_trash_meta_status', true ),
            ]
        );
    }

    /**
     * Delete a form for good; its field posts go only once the form is gone, so
     * a form that only reached the trash keeps its fields (task 1.12).
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return bool Whether the form was deleted
     */
    public function delete_permanently( $form_id ) {
        $form_id = absint( $form_id );

        if ( ! in_array( get_post_type( $form_id ), [ 'wpuf_forms', 'wpuf_profile' ], true ) ) {
            return false;
        }

        wp_delete_post( $form_id );

        if ( get_post( $form_id ) ) {
            return false;
        }

        $this->fields->delete_all( $form_id );

        return true;
    }

    /**
     * Update the post fields of a form (title, content, status, ...).
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param array $fields  wp_update_post() fields without the ID
     *
     * @return int|WP_Error Form id
     */
    public function update( $form_id, array $fields ) {
        unset( $fields['ID'] );

        return wp_update_post( array_merge( $fields, [ 'ID' => absint( $form_id ) ] ), true );
    }

    /**
     * Stored settings of a form (`wpuf_form_settings`), [] when none.
     *
     * @since WPUF_SINCE
     *
     * @param int $form_id Form id
     *
     * @return array
     */
    public function read_settings( $form_id ) {
        $settings = get_post_meta( $form_id, 'wpuf_form_settings', true );

        return is_array( $settings ) ? $settings : [];
    }

    /**
     * Store the settings of a form as given.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id  Form id
     * @param array $settings Settings
     *
     * @return void
     */
    public function write_settings( $form_id, array $settings ) {
        update_post_meta( $form_id, 'wpuf_form_settings', $settings );
    }

    /**
     * One meta value of a form.
     *
     * @since WPUF_SINCE
     *
     * @param int    $form_id Form id
     * @param string $key     Meta key
     *
     * @return mixed
     */
    public function read_meta( $form_id, $key ) {
        return get_post_meta( $form_id, $key, true );
    }

    /**
     * Store meta values of a form, key => value.
     *
     * @since WPUF_SINCE
     *
     * @param int   $form_id Form id
     * @param array $meta    Meta, key => value
     *
     * @return void
     */
    public function write_meta( $form_id, array $meta ) {
        foreach ( $meta as $key => $value ) {
            update_post_meta( $form_id, $key, $value );
        }
    }

    /**
     * Delete every form of a type for good, any status, trash included,
     * with their field posts (Tools > Delete Forms).
     *
     * @since WPUF_SINCE
     *
     * @param string $post_type wpuf_forms or wpuf_profile
     *
     * @return int Forms deleted
     */
    public function delete_all_of_type( $post_type ) {
        if ( ! in_array( $post_type, [ 'wpuf_forms', 'wpuf_profile' ], true ) ) {
            return 0;
        }

        $deleted = 0;

        foreach ( $this->query_ids( $post_type, [ 'status' => [ 'publish', 'draft', 'pending', 'trash' ] ] ) as $form_id ) {
            if ( $this->delete_permanently( $form_id ) ) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Legacy delete (wpuf_delete_form(), Form_Manager::delete()): delete or
     * trash the post and remove its field rows directly, as those functions did.
     *
     * @since WPUF_SINCE
     *
     * @param int  $form_id Form id
     * @param bool $force   Delete instead of trash
     *
     * @return void
     */
    public function delete( $form_id, $force = true ) {
        global $wpdb;

        wp_delete_post( $form_id, $force );

        // Field posts are children WordPress does not know about.
        $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- legacy behaviour kept as is.
            $wpdb->posts,
            [
                'post_parent' => $form_id,
                'post_type'   => 'wpuf_input',
            ]
        );
    }

    /**
     * The posts and pages that show forms: the `[wpuf_form id]` /
     * `[wpuf_profile id]` shortcodes and the `wpuf/post-form` block in post
     * content, and the `wpuf-form` Elementor widget in `_elementor_data`.
     * Trash, revisions and auto drafts are left out.
     *
     * @since WPUF_SINCE
     *
     * @param int[] $form_ids Form ids
     *
     * @return array form id => int[] post ids
     */
    public function embedding_posts( array $form_ids ) {
        global $wpdb;

        $form_ids = array_values( array_filter( array_map( 'absint', $form_ids ) ) );
        $found    = array_fill_keys( $form_ids, [] );

        if ( ! $form_ids ) {
            return $found;
        }

        $add = function ( $form_id, $post_id ) use ( &$found ) {
            $form_id = (int) $form_id;

            if ( isset( $found[ $form_id ] ) && ! in_array( (int) $post_id, $found[ $form_id ], true ) ) {
                $found[ $form_id ][] = (int) $post_id;
            }
        };

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one read for the forms list.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_content FROM {$wpdb->posts}
                WHERE post_status NOT IN ( 'trash', 'auto-draft', 'inherit' )
                AND post_type NOT IN ( 'revision', 'attachment', 'wpuf_forms', 'wpuf_profile', 'wpuf_input' )
                AND ( post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s )",
                '%' . $wpdb->esc_like( '[wpuf_form' ) . '%',
                '%' . $wpdb->esc_like( '[wpuf_profile' ) . '%',
                '%' . $wpdb->esc_like( 'wp:wpuf/post-form' ) . '%'
            )
        );

        foreach ( (array) $rows as $row ) {
            $content = (string) $row->post_content;

            if ( preg_match_all( '/\[wpuf_(?:form|profile)\b[^\]]*?\bid\s*=\s*["\']?(\d+)/', $content, $matches ) ) {
                foreach ( $matches[1] as $form_id ) {
                    $add( $form_id, $row->ID );
                }
            }

            if ( preg_match_all( '/<!--\s*wp:wpuf\/post-form\s+(\{.*?\})\s*\/?-->/s', $content, $blocks ) ) {
                foreach ( $blocks[1] as $json ) {
                    $attrs = json_decode( $json, true );

                    if ( ! empty( $attrs['formId'] ) ) {
                        $add( $attrs['formId'], $row->ID );
                    }
                }
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one read for the forms list.
        $meta = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT m.post_id, m.meta_value FROM {$wpdb->postmeta} m
                INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
                WHERE m.meta_key = '_elementor_data' AND m.meta_value LIKE %s
                AND p.post_status NOT IN ( 'trash', 'auto-draft', 'inherit' ) AND p.post_type <> 'revision'",
                '%' . $wpdb->esc_like( '"widgetType":"wpuf-form"' ) . '%'
            )
        );

        foreach ( (array) $meta as $row ) {
            $this->walk_elementor( json_decode( (string) $row->meta_value, true ), $row->post_id, $add );
        }

        return $found;
    }

    /**
     * Collect the form ids of `wpuf-form` widgets in Elementor data.
     *
     * @param mixed    $elements Elementor elements
     * @param int      $post_id  Post id
     * @param callable $add      ( form id, post id )
     *
     * @return void
     */
    private function walk_elementor( $elements, $post_id, $add ) {
        if ( ! is_array( $elements ) ) {
            return;
        }

        foreach ( $elements as $element ) {
            if ( ! is_array( $element ) ) {
                continue;
            }

            if ( isset( $element['widgetType'] ) && 'wpuf-form' === $element['widgetType'] && ! empty( $element['settings']['form_id'] ) ) {
                $add( $element['settings']['form_id'], $post_id );
            }

            if ( ! empty( $element['elements'] ) ) {
                $this->walk_elementor( $element['elements'], $post_id, $add );
            }
        }
    }

    /**
     * Store a list meta (notifications, integrations) unless an empty list would
     * only replace a stored empty value with `[]`.
     *
     * @param int    $form_id  Form id
     * @param string $meta_key Meta key
     * @param mixed  $value    Value
     *
     * @return void
     */
    private function update_list_meta( $form_id, $meta_key, $value ) {
        if ( Normalizers::is_empty_list_noop( $value, get_post_meta( $form_id, $meta_key, true ) ) ) {
            return;
        }

        update_post_meta( $form_id, $meta_key, $value );
    }

    /**
     * Settings and version meta for create().
     *
     * @param int   $form_id Form id
     * @param array $args    create() arguments
     *
     * @return void
     */
    private function store_create_meta( $form_id, $args ) {
        if ( ! empty( $args['settings'] ) || ( $args['store_empty_settings'] && null !== $args['settings'] ) ) {
            update_post_meta( $form_id, 'wpuf_form_settings', $args['settings'] );
        }

        if ( $args['version'] ) {
            update_post_meta( $form_id, 'wpuf_form_version', WPUF_VERSION );
        }

        if ( null !== $args['notifications'] ) {
            update_post_meta( $form_id, 'notifications', $args['notifications'] );
        }

        $this->write_meta( $form_id, (array) $args['meta'] );
    }
}
