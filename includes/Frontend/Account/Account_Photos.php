<?php
/**
 * Account photos
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Frontend\Account;

use WeDevs\Wpuf\Frontend\Forms\Upload_Service;
use WeDevs\Wpuf\Platform\Stores\Stores;
use WP_Error;

/**
 * The current user's profile photo and cover photo on the account page:
 * checks the file, stores it as an attachment through the upload service
 * and keeps the user meta in the user store (`wpuf_profile_photo`,
 * `wpuf_cover_photo`, the meta the profile and cover photo fields write).
 * A replaced or removed photo is deleted when the user owns it.
 *
 * @since WPUF_SINCE
 */
class Account_Photos {

    /**
     * Photo kinds.
     */
    const PROFILE = 'profile_photo';
    const COVER   = 'cover_photo';

    /**
     * @var Upload_Service
     */
    protected $uploads;

    /**
     * @since WPUF_SINCE
     *
     * @param Upload_Service $uploads Upload service
     */
    public function __construct( Upload_Service $uploads ) {
        $this->uploads = $uploads;
    }

    /**
     * Store an uploaded photo for the current user.
     *
     * @since WPUF_SINCE
     *
     * @param array  $file The upload (`$_FILES` shape)
     * @param string $kind profile_photo|cover_photo
     *
     * @return int|WP_Error The attachment id
     */
    public function store( array $file, $kind ) {
        if ( empty( $file['name'] ) ) {
            return $this->error( 'no_file', __( 'No file was uploaded.', 'wp-user-frontend' ) );
        }

        $rules     = $this->rules( $kind );
        $extension = strtolower( pathinfo( (string) $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $extension, (array) $rules['extensions'], true ) ) {
            return $this->error( 'bad_type', $rules['bad_type'] );
        }

        if ( (int) ( isset( $file['size'] ) ? $file['size'] : 0 ) > $rules['max_size'] ) {
            return $this->error( 'too_large', $rules['too_large'] );
        }

        $user_id = get_current_user_id();
        // Not a form upload: the photo rules above decide (image types, size; the
        // route checks Pro and login), so the form field binding of upload() does not apply.
        $attach_id = $this->uploads->store_file( $file );

        if ( is_wp_error( $attach_id ) ) {
            return $attach_id;
        }

        $previous = $this->photo_id( $kind, $user_id );

        $this->set_photo( $kind, $user_id, $attach_id );
        wp_update_post(
            [
                'ID'          => $attach_id,
                'post_author' => $user_id,
            ]
        );

        if ( $previous && $previous !== $attach_id ) {
            $this->delete_owned( $previous, $user_id );
        }

        return $attach_id;
    }

    /**
     * Remove a photo of the current user.
     *
     * @since WPUF_SINCE
     *
     * @param string $kind profile_photo|cover_photo
     *
     * @return void
     */
    public function remove( $kind ) {
        $user_id  = get_current_user_id();
        $previous = $this->photo_id( $kind, $user_id );

        $this->set_photo( $kind, $user_id, 0 );

        if ( $previous ) {
            $this->delete_owned( $previous, $user_id );
        }
    }

    /**
     * Allowed extensions, size limit and messages of a photo kind.
     *
     * @param string $kind profile_photo|cover_photo
     *
     * @return array
     */
    protected function rules( $kind ) {
        if ( self::COVER === $kind ) {
            return [
                /**
                 * Filters the file extensions allowed for the account cover photo.
                 *
                 * @since WPUF_SINCE
                 *
                 * @param string[] $extensions Extensions
                 */
                'extensions' => apply_filters( 'wpuf_field_cover_photo_allowed_extensions', [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ] ),
                /**
                 * Filters the largest account cover photo, in bytes.
                 *
                 * @since WPUF_SINCE
                 *
                 * @param int $size Bytes (default 5 MB)
                 */
                'max_size'   => (int) apply_filters( 'wpuf_cover_photo_max_size', 5 * MB_IN_BYTES ),
                'bad_type'   => __( 'This file type is not allowed for a cover photo.', 'wp-user-frontend' ),
                'too_large'  => __( 'The cover photo must be 5 MB or smaller.', 'wp-user-frontend' ),
            ];
        }

        return [
            /** This filter is documented in includes/functions/users.php */
            'extensions' => apply_filters( 'wpuf_field_profile_photo_allowed_extensions', [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ] ),
            /** This filter is documented in includes/functions/users.php */
            'max_size'   => (int) apply_filters( 'wpuf_profile_photo_max_size', 2 * MB_IN_BYTES ),
            'bad_type'   => __( 'This file type is not allowed for a profile photo.', 'wp-user-frontend' ),
            'too_large'  => __( 'The profile photo must be 2 MB or smaller.', 'wp-user-frontend' ),
        ];
    }

    /**
     * @param string $kind    profile_photo|cover_photo
     * @param int    $user_id User id
     *
     * @return int
     */
    protected function photo_id( $kind, $user_id ) {
        return self::COVER === $kind ? Stores::users()->cover_id( $user_id ) : Stores::users()->profile_photo_id( $user_id );
    }

    /**
     * @param string $kind          profile_photo|cover_photo
     * @param int    $user_id       User id
     * @param int    $attachment_id Attachment id, 0 to remove
     *
     * @return void
     */
    protected function set_photo( $kind, $user_id, $attachment_id ) {
        if ( self::COVER === $kind ) {
            Stores::users()->set_cover_photo( $user_id, $attachment_id );
        } else {
            Stores::users()->set_profile_photo( $user_id, $attachment_id );
        }
    }

    /**
     * Delete an attachment the user uploaded (never someone else's).
     *
     * @param int $attachment_id Attachment id
     * @param int $user_id       User id
     *
     * @return void
     */
    protected function delete_owned( $attachment_id, $user_id ) {
        if ( (int) get_post_field( 'post_author', $attachment_id ) === (int) $user_id ) {
            wp_delete_attachment( $attachment_id, true );
        }
    }

    /**
     * @param string $reason  Code suffix
     * @param string $message Message
     *
     * @return WP_Error
     */
    protected function error( $reason, $message ) {
        return new WP_Error( 'wpuf_account_' . $reason, $message, [ 'status' => 400 ] );
    }
}
