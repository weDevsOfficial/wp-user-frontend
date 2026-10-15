<?php
/**
 * User store
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform\Stores;

use WeDevs\Wpuf\Platform\Contracts\DataStore;

/**
 * The user meta the frontend forms and the account read: profile fields
 * saved by registration / profile forms (meta under the field name), the
 * profile photo (`wpuf_profile_photo`) and the cover photo
 * (`wpuf_cover_photo`), both attachment ids.
 *
 * @since WPUF_SINCE
 */
class UserStore implements DataStore {

    /**
     * Meta keys.
     */
    const PROFILE_PHOTO = 'wpuf_profile_photo';
    const COVER_PHOTO   = 'wpuf_cover_photo';

    /**
     * Whether a user exists.
     *
     * @since WPUF_SINCE
     *
     * @param int $id User id
     *
     * @return bool
     */
    public function exists( $id ) {
        return (bool) get_userdata( (int) $id );
    }

    /**
     * A user (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id User id
     *
     * @return \WP_User|false
     */
    public function read( $id ) {
        return get_userdata( (int) $id );
    }

    /**
     * A user (DataStore).
     *
     * @since WPUF_SINCE
     *
     * @param int $id User id
     *
     * @return \WP_User|false
     */
    public function find( $id ) {
        return $this->read( $id );
    }

    /**
     * Users (get_users() arguments).
     *
     * @since WPUF_SINCE
     *
     * @param array $args get_users() arguments
     *
     * @return \WP_User[]
     */
    public function query( array $args = [] ) {
        return get_users( $args );
    }

    /**
     * How many users match (get_users() arguments).
     *
     * @since WPUF_SINCE
     *
     * @param array $args get_users() arguments
     *
     * @return int
     */
    public function count( array $args = [] ) {
        return count( get_users( array_merge( $args, [ 'fields' => 'ID' ] ) ) );
    }

    /**
     * One user meta value as stored (get_user_meta() single).
     *
     * @since WPUF_SINCE
     *
     * @param int    $user_id User id
     * @param string $key     Meta key
     *
     * @return mixed
     */
    public function meta( $user_id, $key ) {
        return get_user_meta( $user_id, $key, true );
    }

    /**
     * The attachment id of a photo field: the profile photo field saves
     * under `wpuf_profile_photo`, avatar / cover fields under their name
     * (0 when none).
     *
     * @since WPUF_SINCE
     *
     * @param int    $user_id  User id
     * @param string $template Field template (avatar, profile_photo, cover_photo)
     * @param string $name     Field name
     *
     * @return int
     */
    public function photo_id( $user_id, $template, $name ) {
        return (int) $this->meta( $user_id, 'profile_photo' === $template ? self::PROFILE_PHOTO : $name );
    }

    /**
     * The cover photo attachment id the account header shows (0 when none).
     *
     * @since WPUF_SINCE
     *
     * @param int $user_id User id
     *
     * @return int
     */
    public function cover_id( $user_id ) {
        return (int) $this->meta( $user_id, self::COVER_PHOTO );
    }
}
