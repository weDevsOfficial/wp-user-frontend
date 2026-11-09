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

## wpuf_no_image

**Type**: Filter

**Parameters**:
- `$image_url` *(string)*: The default no-image URL. (required)

**Return Value**: *(string)* - The modified no-image URL

**Category**: User Management

**Description**: Filters the URL of the default "no image" placeholder used when posts don't have featured images. This hook allows developers to customize the default no-image placeholder.

## wpuf_preview_link_separator

**Type**: Filter

**Parameters**:
- `$separator` *(string)*: The default separator between preview links. (required)

**Return Value**: *(string)* - The modified separator

**Category**: User Management

**Description**: Filters the separator used between preview links in the user dashboard. This hook allows developers to customize the separator character or HTML between post preview links.

## login_message

**Type**: Filter

**Parameters**:
- `$message` *(string)*: The login form message. (required)

**Return Value**: *(string)* - The modified login message

**Category**: User Management

**Description**: Filters the message displayed at the top of the login form. This hook allows developers to customize the login form message or add custom content to the login form.

## wpuf_account_edit_profile_content

**Type**: Filter

**Parameters**:
- `$output` *(string)*: The edit profile content HTML. (required)

**Return Value**: *(string)* - The modified edit profile content

**Category**: User Management

**Description**: Filters the content displayed in the edit profile section. This hook allows developers to modify the edit profile form content or add custom functionality.

## wpuf_payment_amount

**Type**: Filter

**Parameters**:
- `$amount` *(float)*: The payment amount. (required)

**Return Value**: *(float)* - The modified payment amount

**Category**: Payment & Subscriptions

**Description**: Filters the payment amount before processing. This hook allows developers to modify payment amounts, add taxes, apply discounts, or perform other payment-related calculations.

## wpuf_mail_bank_admin_subject

**Type**: Filter

**Parameters**:
- `$subject` *(string)*: The email subject for bank payment admin notification. (required)

**Return Value**: *(string)* - The modified email subject

**Category**: Payment & Subscriptions

**Description**: Filters the subject line of the admin notification email for bank payments. This hook allows developers to customize the email subject for bank payment notifications.

## wpuf_mail_bank_admin_body

**Type**: Filter

**Parameters**:
- `$message` *(string)*: The email body for bank payment admin notification. (required)

**Return Value**: *(string)* - The modified email body

**Category**: Payment & Subscriptions

**Description**: Filters the body content of the admin notification email for bank payments. This hook allows developers to customize the email content for bank payment notifications.

## wpuf_mail_bank_user_subject

**Type**: Filter

**Parameters**:
- `$subject` *(string)*: The email subject for bank payment user notification. (required)

**Return Value**: *(string)* - The modified email subject

**Category**: Payment & Subscriptions

**Description**: Filters the subject line of the user notification email for bank payments. This hook allows developers to customize the email subject for bank payment user notifications.

## wpuf_mail_bank_user_body

**Type**: Filter

**Parameters**:
- `$message` *(string)*: The email body for bank payment user notification. (required)

**Return Value**: *(string)* - The modified email body

**Category**: Payment & Subscriptions

**Description**: Filters the body content of the user notification email for bank payments. This hook allows developers to customize the email content for bank payment user notifications.

## wpuf_get_subscription_meta

**Type**: Filter

**Parameters**:
- `$meta` *(array)*: The subscription meta data. (required)
- `$subscription_id` *(int)*: The subscription ID. (required)

**Return Value**: *(array)* - The modified subscription meta data

**Category**: Payment & Subscriptions

**Description**: Filters the subscription meta data when retrieving subscription information. This hook allows developers to modify or add custom meta data to subscriptions.

## wpuf_posts_type

**Type**: Filter

**Parameters**:
- `$post_types` *(array)*: Array of available post types. (required)

**Return Value**: *(array)* - Modified array of post types

**Category**: Post Management

**Description**: Filters the available post types for frontend posting. This hook allows developers to add, remove, or modify the post types that users can create via the frontend.

## wpuf_subscription_packs

**Type**: Filter

**Parameters**:
- `$contents` *(array)*: The subscription pack contents. (required)
- `$packs` *(array)*: Array of subscription packs. (required)

**Return Value**: *(array)* - Modified subscription pack contents

**Category**: Payment & Subscriptions

**Description**: Filters the subscription pack contents before display. This hook allows developers to modify the subscription pack information or add custom content.

## wpuf_subscription_cycle_label

**Type**: Filter

**Parameters**:
- `$label` *(string)*: The subscription cycle label. (required)

**Return Value**: *(string)* - The modified subscription cycle label

**Category**: Payment & Subscriptions

**Description**: Filters the label displayed for subscription cycles. This hook allows developers to customize the text shown for subscription periods.

## wpuf_ppp_notice

**Type**: Filter

**Parameters**:
- `$text` *(string)*: The pay-per-post notice text. (required)
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: *(string)* - The modified pay-per-post notice text

**Category**: Payment & Subscriptions

**Description**: Filters the pay-per-post notice text displayed to users. This hook allows developers to customize the notice shown when users need to pay for posting.

## wpuf_pack_notice

**Type**: Filter

**Parameters**:
- `$text` *(string)*: The subscription pack notice text. (required)
- `$id` *(int)*: The subscription pack ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: *(string)* - The modified subscription pack notice text

**Category**: Payment & Subscriptions

**Description**: Filters the subscription pack notice text displayed to users. This hook allows developers to customize the notice shown for subscription packs. 

## wpuf_update_post_validate

**Type**: Filter

**Parameters**:
- `$error` *(string)*: Error message string. (required)

**Return Value**: *(string)* - Error message to display if validation fails, empty string if validation passes

**Category**: Form Processing

**Description**: Allows custom validation for post updates. Return an error message string if validation fails, or an empty string if validation passes. This hook is called before a post is updated via the frontend form.

## wpuf_add_post_validate

**Type**: Filter

**Parameters**:
- `$error` *(string)*: Error message string. (required)

**Return Value**: *(string)* - Error message to display if validation fails, empty string if validation passes

**Category**: Form Processing

**Description**: Allows custom validation for new post submissions. Return an error message string if validation fails, or an empty string if validation passes. This hook is called before a new post is created via the frontend form.

## wpuf_update_post_args

**Type**: Filter

**Parameters**:
- `$postarr` *(array)*: Array of post data to be updated. (required)
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)
- `$form_fields` *(array)*: The form fields array. (required)

**Return Value**: *(array)* - Modified post data array

**Category**: Form Processing

**Description**: Allows modification of post data before updating a post. This hook is called after validation but before the post is actually updated in the database.

## wpuf_add_post_args

**Type**: Filter

**Parameters**:
- `$postarr` *(array)*: Array of post data to be inserted. (required)
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)
- `$form_fields` *(array)*: The form fields array. (required)

**Return Value**: *(array)* - Modified post data array

**Category**: Form Processing

**Description**: Allows modification of post data before inserting a new post. This hook is called after validation but before the post is actually created in the database.

## wpuf_edit_post_redirect

**Type**: Filter

**Parameters**:
- `$response` *(array)*: Response array containing redirect information. (required)
- `$post_id` *(int)*: The post ID that was updated. (required)
- `$form_id` *(int)*: The form ID used for the update. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: *(array)* - Modified response array

**Category**: Form Processing

**Description**: Allows modification of the redirect response after a post is updated. This hook is called after the post update is completed and before the user is redirected.

## wpuf_add_post_redirect

**Type**: Filter

**Parameters**:
- `$response` *(array)*: Response array containing redirect information. (required)
- `$post_id` *(int)*: The post ID that was created. (required)
- `$form_id` *(int)*: The form ID used for the creation. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: *(array)* - Modified response array

**Category**: Form Processing

**Description**: Allows modification of the redirect response after a new post is created. This hook is called after the post creation is completed and before the user is redirected.

## wpuf_textarea_editor_args

**Type**: Filter

**Parameters**:
- `$editor_args` *(array)*: Array of arguments for the textarea editor. (required)

**Return Value**: *(array)* - Modified editor arguments array

**Category**: Form Processing

**Description**: Allows modification of the arguments passed to the textarea editor (TinyMCE) in form fields. This hook can be used to customize the editor settings, toolbar, or other configuration options.

## wpuf_taxonomy_checklist_args

**Type**: Filter

**Parameters**:
- `$args` *(array)*: Array of arguments for the taxonomy checklist. (required)

**Return Value**: *(array)* - Modified taxonomy checklist arguments array

**Category**: Form Processing

**Description**: Allows modification of the arguments used when rendering taxonomy checklists in forms. This hook can be used to customize how taxonomy terms are displayed and selected.

## wpuf_styles_to_register

**Type**: Filter

**Parameters**:
- `$styles` *(array)*: Array of CSS styles to register. (required)

**Return Value**: *(array)* - Modified styles array

**Category**: Utility Hooks

**Description**: Allows modification of the CSS styles that are registered and enqueued by WPUF. This hook can be used to add, remove, or modify stylesheets.

## wpuf_scripts_to_register

**Type**: Filter

**Parameters**:
- `$scripts` *(array)*: Array of JavaScript scripts to register. (required)

**Return Value**: *(array)* - Modified scripts array

**Category**: Utility Hooks

**Description**: Allows modification of the JavaScript files that are registered and enqueued by WPUF. This hook can be used to add, remove, or modify scripts.

## retrieve_password_title

**Type**: Filter

**Parameters**:
- `$title` *(string)*: The email subject title for password reset. (required)

**Return Value**: *(string)* - Modified email subject title

**Category**: User Management

**Description**: Allows modification of the email subject title sent when a user requests a password reset. This hook is used in the password reset functionality.

## retrieve_password_message

**Type**: Filter

**Parameters**:
- `$message` *(string)*: The email message content for password reset. (required)

**Return Value**: *(string)* - Modified email message content

**Category**: User Management

**Description**: Allows modification of the email message content sent when a user requests a password reset. This hook is used in the password reset functionality.

## widget_title

**Type**: Filter

**Parameters**:
- `$title` *(string)*: The widget title. (required)

**Return Value**: *(string)* - Modified widget title

**Category**: Utility Hooks

**Description**: Allows modification of widget titles displayed in WPUF widgets. This hook can be used to customize how widget titles are displayed.

## widget_text_content

**Type**: Filter

**Parameters**:
- `$content` *(string)*: The widget text content. (required)

**Return Value**: *(string)* - Modified widget text content

**Category**: Utility Hooks

**Description**: Allows modification of widget text content displayed in WPUF widgets. This hook can be used to customize the content of widgets.

## widget_text

**Type**: Filter

**Parameters**:
- `$text` *(string)*: The widget text. (required)

**Return Value**: *(string)* - Modified widget text

**Category**: Utility Hooks

**Description**: Allows modification of widget text displayed in WPUF widgets. This hook can be used to customize the text content of widgets.

## login_link_separator

**Type**: Filter

**Parameters**:
- `$separator` *(string)*: The separator between login links. (required)

**Return Value**: *(string)* - Modified separator

**Category**: User Management

**Description**: Allows modification of the separator used between login links in forms. This hook can be used to customize the visual separation between different login options.

## wpuf_text_field_option_settings

**Type**: Filter

**Parameters**:
- `$settings` *(array)*: Array of text field option settings. (required)

**Return Value**: *(array)* - Modified text field option settings array

**Category**: Form Processing

**Description**: Allows modification of the option settings available for text fields in the form builder. This hook can be used to add, remove, or modify text field configuration options.

## wpuf_upload_response_image_size

**Type**: Filter

**Parameters**:
- `$image_size` *(string)*: The image size for upload responses. (required)

**Return Value**: *(string)* - Modified image size

**Category**: Form Processing

**Description**: Allows modification of the image size used when displaying uploaded images in form responses. This hook can be used to customize how uploaded images are displayed. 

## wpuf_upload_response_image_type

**Type**: Filter

**Parameters**:
- `$image_type` *(string)*: The image type for upload responses. (required)
- `$form_id` *(int)*: The form ID. (required)
- `$field_type` *(string)*: The field type. (required)

**Return Value**: *(string)* - Modified image type

**Category**: Form Processing

**Description**: Allows modification of the image type used when displaying uploaded images in form responses. This hook can be used to customize how uploaded images are displayed (e.g., as links or direct images).

## wpuf_column_field_option_settings

**Type**: Filter

**Parameters**:
- `$options` *(array)*: Array of column field option settings. (required)

**Return Value**: *(array)* - Modified column field option settings array

**Category**: Form Processing

**Description**: Allows modification of the option settings available for column fields in the form builder. This hook can be used to add, remove, or modify column field configuration options.

## wpuf_field_get_js_settings

**Type**: Filter

**Parameters**:
- `$settings` *(array)*: Array of JavaScript settings for form fields. (required)

**Return Value**: *(array)* - Modified JavaScript settings array

**Category**: Form Processing

**Description**: Allows modification of the JavaScript settings passed to form fields. This hook can be used to customize the JavaScript behavior and configuration of form fields.

## wpuf-form-builder-common-taxonomy-fields-properties

**Type**: Filter

**Parameters**:
- `$properties` *(array)*: Array of common taxonomy field properties. (required)

**Return Value**: *(array)* - Modified taxonomy field properties array

**Category**: Form Processing

**Description**: Allows modification of the common properties used for taxonomy fields in the form builder. This hook can be used to customize the default properties for taxonomy fields.

## wpuf-form-builder-common-text-fields-properties

**Type**: Filter

**Parameters**:
- `$properties` *(array)*: Array of common text field properties. (required)

**Return Value**: *(array)* - Modified text field properties array

**Category**: Form Processing

**Description**: Allows modification of the common properties used for text fields in the form builder. This hook can be used to customize the default properties for text fields.

## wpuf_show_post_status

**Type**: Filter

**Parameters**:
- `$show` *(bool)*: Whether to show post status. (required)

**Return Value**: *(bool)* - Modified show post status value

**Category**: Post Management

**Description**: Allows modification of whether post status should be displayed. This hook can be used to control the visibility of post status in various contexts.

## wpuf_get_post_types

**Type**: Filter

**Parameters**:
- `$post_types` *(array)*: Array of registered post types. (required)

**Return Value**: *(array)* - Modified post types array

**Category**: Post Management

**Description**: Allows modification of the list of post types available in WPUF. This hook can be used to add, remove, or modify the post types that are available for frontend posting.

## wpuf_front_post_edit_link

**Type**: Filter

**Parameters**:
- `$edit_link` *(string)*: The frontend post edit link. (required)

**Return Value**: *(string)* - Modified edit link

**Category**: Post Management

**Description**: Allows modification of the frontend post edit link. This hook can be used to customize the URL used for editing posts from the frontend.

## list_cats

**Type**: Filter

**Parameters**:
- `$output` *(string)*: The category list output. (required)

**Return Value**: *(string)* - Modified category list output

**Category**: Post Management

**Description**: Allows modification of the category list output. This hook can be used to customize how categories are displayed in lists.

## the_category

**Type**: Filter

**Parameters**:
- `$output` *(string)*: The category output. (required)

**Return Value**: *(string)* - Modified category output

**Category**: Post Management

**Description**: Allows modification of the category output. This hook can be used to customize how categories are displayed.

## wpuf_allowed_extensions

**Type**: Filter

**Parameters**:
- `$extensions` *(array)*: Array of allowed file extensions. (required)

**Return Value**: *(array)* - Modified extensions array

**Category**: Form Processing

**Description**: Allows modification of the allowed file extensions for file uploads. This hook can be used to add, remove, or modify the file types that users can upload through forms.

## wpuf_use_default_avatar

**Type**: Filter

**Parameters**:
- `$use_default` *(bool)*: Whether to use default avatar. (required)

**Return Value**: *(bool)* - Modified use default avatar value

**Category**: User Management

**Description**: Allows modification of whether to use the default avatar. This hook can be used to control when the default avatar should be used instead of a custom user avatar.

## wpuf_admin_role

**Type**: Filter

**Parameters**:
- `$role` *(string)*: The admin role. (required)

**Return Value**: *(string)* - Modified admin role

**Category**: Admin Functions

**Description**: Allows modification of the admin role used for WPUF administrative functions. This hook can be used to customize which user role has administrative privileges.

## wpuf_custom_field_render

**Type**: Filter

**Parameters**:
- `$output` *(string)*: The custom field render output. (required)

**Return Value**: *(string)* - Modified custom field render output

**Category**: Form Processing

**Description**: Allows modification of the custom field render output. This hook can be used to customize how custom fields are rendered in forms.

## wpuf-get-form-fields

**Type**: Filter

**Parameters**:
- `$fields` *(array)*: Array of form fields. (required)

**Return Value**: *(array)* - Modified form fields array

**Category**: Form Processing

**Description**: Allows modification of the form fields available in the form builder. This hook can be used to add, remove, or modify the fields that can be used in forms.

## wpuf_get_post_form_templates

**Type**: Filter

**Parameters**:
- `$templates` *(array)*: Array of post form templates. (required)

**Return Value**: *(array)* - Modified templates array

**Category**: Form Processing

**Description**: Allows modification of the post form templates available in the form builder. This hook can be used to add, remove, or modify the templates that can be used for creating post forms.

## wpuf_get_pro_form_previews

**Type**: Filter

**Parameters**:
- `$previews` *(array)*: Array of pro form previews. (required)

**Return Value**: *(array)* - Modified previews array

**Category**: Form Processing

**Description**: Allows modification of the pro form previews available in the form builder. This hook can be used to add, remove, or modify the preview templates for pro features.

## wpuf_account_sections

**Type**: Filter

**Parameters**:
- `$sections` *(array)*: Array of account sections. (required)

**Return Value**: *(array)* - Modified account sections array

**Category**: User Management

**Description**: Allows modification of the account sections displayed in the user dashboard. This hook can be used to add, remove, or modify the sections shown in the user account area.

## wpuf_currencies

**Type**: Filter

**Parameters**:
- `$currencies` *(array)*: Array of available currencies. (required)

**Return Value**: *(array)* - Modified currencies array

**Category**: Payment & Subscriptions

**Description**: Allows modification of the available currencies for payments. This hook can be used to add, remove, or modify the currencies that are available for payment processing.

## wpuf_price_format

**Type**: Filter

**Parameters**:
- `$format` *(string)*: The price format. (required)

**Return Value**: *(string)* - Modified price format

**Category**: Payment & Subscriptions

**Description**: Allows modification of the price format used for displaying prices. This hook can be used to customize how prices are formatted and displayed.

## wpuf_raw_price

**Type**: Filter

**Parameters**:
- `$price` *(float)*: The raw price value. (required)

**Return Value**: *(float)* - Modified raw price value

**Category**: Payment & Subscriptions

**Description**: Allows modification of the raw price value before formatting. This hook can be used to adjust price values before they are formatted for display.

## wpuf_formatted_price

**Type**: Filter

**Parameters**:
- `$formatted_price` *(string)*: The formatted price string. (required)

**Return Value**: *(string)* - Modified formatted price string

**Category**: Payment & Subscriptions

**Description**: Allows modification of the formatted price string. This hook can be used to customize the final formatted price display.

## wpuf_price_trim_zeros

**Type**: Filter

**Parameters**:
- `$trim` *(bool)*: Whether to trim zeros from prices. (required)

**Return Value**: *(bool)* - Modified trim zeros value

**Category**: Payment & Subscriptions

**Description**: Allows modification of whether to trim trailing zeros from price displays. This hook can be used to control the precision of price formatting.

## wpuf_format_price

**Type**: Filter

**Parameters**:
- `$formatted_price` *(string)*: The formatted price string. (required)

**Return Value**: *(string)* - Modified formatted price string

**Category**: Payment & Subscriptions

**Description**: Allows modification of the final formatted price string. This hook can be used to customize the complete price formatting process. 

## wpuf_email_header

**Type**: Filter

**Parameters**:
- `$header` *(string)*: The email header HTML content. (required)
- `$subject` *(string)*: The email subject. (required)

**Return Value**: *(string)* - Modified email header HTML content

**Category**: Utility Hooks

**Description**: Allows modification of the email header HTML content used in WPUF email templates. This hook can be used to customize the header section of emails sent by the plugin, such as notification emails and guest post verification emails.

## wpuf_email_footer

**Type**: Filter

**Parameters**:
- `$footer` *(string)*: The email footer HTML content. (required)

**Return Value**: *(string)* - Modified email footer HTML content

**Category**: Utility Hooks

**Description**: Allows modification of the email footer HTML content used in WPUF email templates. This hook can be used to customize the footer section of emails sent by the plugin, such as notification emails and guest post verification emails.

## wpuf_email_style

**Type**: Filter

**Parameters**:
- `$css` *(string)*: The email CSS styles. (required)

**Return Value**: *(string)* - Modified email CSS styles

**Category**: Utility Hooks

**Description**: Allows modification of the CSS styles used in WPUF email templates. This hook can be used to customize the styling of emails sent by the plugin, such as notification emails and guest post verification emails.

## wpuf_post_forms_list_table_post_statuses

**Type**: Filter

**Parameters**:
- `$post_statuses` *(array)*: Array of post statuses for the forms list table. (required)

**Return Value**: *(array)* - Modified post statuses array

**Category**: Admin Functions

**Description**: Allows modification of the post statuses displayed in the WPUF post forms list table. This hook can be used to add, remove, or modify the status options available in the admin forms listing page. 