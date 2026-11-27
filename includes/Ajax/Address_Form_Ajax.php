<?php
/**
 * Old name of Billing_Address (kept for code that still uses it)
 *
 * @package WP_User_Frontend
 */

namespace WeDevs\Wpuf\Ajax;

/**
 * @deprecated WPUF_SINCE Use \WeDevs\Wpuf\Frontend\Billing\Billing_Address. The billing
 *             address is saved over the `wpuf/v1/account/billing-address` REST route.
 */
class_alias( \WeDevs\Wpuf\Frontend\Billing\Billing_Address::class, __NAMESPACE__ . '\\Address_Form_Ajax' );
