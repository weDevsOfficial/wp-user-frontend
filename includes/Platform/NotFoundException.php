<?php
/**
 * Platform container: unknown service
 *
 * @package WP_User_Frontend
 * @since WPUF_SINCE
 */

namespace WeDevs\Wpuf\Platform;

/**
 * Thrown by Container::get() for an id that is not registered.
 *
 * @since WPUF_SINCE
 */
class NotFoundException extends \RuntimeException {
}
