# WP User Frontend - Action Hooks

This document contains all the action hooks available in the WP User Frontend plugin.

## before_appsero_license_section

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires before the Appsero license section is displayed in the admin area. This hook allows developers to add content or perform actions before the license section is rendered.

## after_appsero_license_section

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires after the Appsero license section is displayed in the admin area. This hook allows developers to add content or perform actions after the license section is rendered.

## wpuf_gateway_bank_order_submit

**Type**: Action

**Parameters**:
- `$data` *(array)*: Order data containing payment information. (required)
- `$order_id` *(int)*: The order ID for the bank payment. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires when a bank order is submitted for payment processing. This hook allows developers to perform additional actions when a bank payment order is created, such as logging, notifications, or custom processing. 