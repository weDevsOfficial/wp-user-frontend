# WP User Frontend - Filter Hooks

This document contains all the filter hooks available in the WP User Frontend plugin.

## wpuf_account_unauthorized

**Type**: Filter

**Parameters**:
- `$msg` *(string)*: The unauthorized message to be displayed. (required)

**Return Value**: *(string)* - The modified unauthorized message

**Category**: User Management

**Description**: Filters the message displayed when a user is unauthorized to access certain account features. This hook allows developers to customize the unauthorized access message.

## wpuf_my_account_tab_links

**Type**: Filter

**Parameters**:
- `$tabs` *(array)*: Array of account section tabs. (required)

**Return Value**: *(array)* - Modified array of account section tabs

**Category**: User Management

**Description**: Filters the account section tabs displayed in the user dashboard. This hook allows developers to add, remove, or modify the tabs shown in the user account area.

## wpuf_dashboard_query

**Type**: Filter

**Parameters**:
- `$args` *(array)*: WordPress query arguments for dashboard posts. (required)

**Return Value**: *(array)* - Modified query arguments

**Category**: User Management

**Description**: Filters the query arguments used to fetch posts in the user dashboard. This hook allows developers to modify the query parameters for customizing which posts are displayed in the dashboard. 