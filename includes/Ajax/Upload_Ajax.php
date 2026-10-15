<?php
/**
 * Old name of Upload_Handler (kept for code that still uses it)
 *
 * @package WP_User_Frontend
 */

namespace WeDevs\Wpuf\Ajax;

/**
 * @deprecated WPUF_SINCE Use \WeDevs\Wpuf\Frontend\Forms\Upload_Handler. Requests go through the
 *             `wpuf/v1` REST routes; the class name kept "Ajax" from before.
 */
class_alias( \WeDevs\Wpuf\Frontend\Forms\Upload_Handler::class, __NAMESPACE__ . '\\Upload_Ajax' );
