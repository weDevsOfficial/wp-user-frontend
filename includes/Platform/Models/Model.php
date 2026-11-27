<?php
/**
 * Base model
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Models;

/**
 * Plain data holder for a WPUF entity (FlyHR BaseModel pattern): an id plus
 * attributes, no storage logic. Stores build models (`DataStore::find()`,
 * `query()`); models never read or write the database themselves.
 *
 * @since WPUF_SINCE
 */
abstract class Model {

    /**
     * Id; 0 for an unsaved model.
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Attributes.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @since WPUF_SINCE
     *
     * @param int   $id   Id
     * @param array $data Attributes
     */
    public function __construct( $id = 0, array $data = [] ) {
        $this->id   = (int) $id;
        $this->data = $data;
    }

    /**
     * Id.
     *
     * @since WPUF_SINCE
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * One attribute.
     *
     * @since WPUF_SINCE
     *
     * @param string $key     Attribute
     * @param mixed  $default Value when missing
     *
     * @return mixed
     */
    public function get( $key, $default = null ) {
        return array_key_exists( $key, $this->data ) ? $this->data[ $key ] : $default;
    }

    /**
     * Set one attribute (in memory only).
     *
     * @since WPUF_SINCE
     *
     * @param string $key   Attribute
     * @param mixed  $value Value
     *
     * @return static
     */
    public function set( $key, $value ) {
        $this->data[ $key ] = $value;

        return $this;
    }

    /**
     * Id and attributes as an array.
     *
     * @since WPUF_SINCE
     *
     * @return array
     */
    public function to_array() {
        return array_merge( [ 'id' => $this->id ], $this->data );
    }
}
