<?php
/**
 * Data store contract
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Contracts;

use WeDevs\Wpuf\Platform\Models\Model;

/**
 * Read side every WPUF entity store implements (FormStore, SubscriptionStore),
 * after FlyHR's DataStoreInterface. Writes stay store specific: each write path
 * (builder, classic metabox, REST, list actions) keeps develop's exact stored
 * values, which one generic create/update could not keep. Stores own all
 * storage access; REST controllers and screens read through them.
 *
 * @since WPUF_SINCE
 */
interface DataStore {

    /**
     * Whether the id is an entity of this store.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Id
     *
     * @return bool
     */
    public function exists( $id );

    /**
     * Raw stored data of one entity.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Id
     *
     * @return array|null Null when the id is not an entity of this store
     */
    public function read( $id );

    /**
     * One entity as a model.
     *
     * @since WPUF_SINCE
     *
     * @param int $id Id
     *
     * @return Model|null
     */
    public function find( $id );

    /**
     * Entities as models, newest first unless `orderby` / `order` say otherwise.
     *
     * @since WPUF_SINCE
     *
     * @param array $args `status` (string|string[]), `search`, `per_page` (-1 = all),
     *                    `page`, `orderby`, `order`, plus store specific keys
     *
     * @return Model[]
     */
    public function query( array $args = [] );

    /**
     * Number of entities matching the same args as query() (paging ignored).
     *
     * @since WPUF_SINCE
     *
     * @param array $args Query args
     *
     * @return int
     */
    public function count( array $args = [] );
}
