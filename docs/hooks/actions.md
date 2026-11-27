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

## wpuf_login_form_bottom

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: User Management

**Description**: Fires at the bottom of the login form. This hook allows developers to add custom content, links, or functionality below the login form fields.

## wpuf_account_posts_head_col

**Type**: Action

**Parameters**:
- `$args` *(array)*: Arguments containing query parameters and settings. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires in the header column of the user's posts listing in the dashboard. This hook allows developers to add custom columns or content to the posts table header.

## wpuf_account_posts_row_col

**Type**: Action

**Parameters**:
- `$args` *(array)*: Arguments containing query parameters and settings. (required)
- `$post` *(WP_Post)*: The current post object being displayed. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires in each row column of the user's posts listing in the dashboard. This hook allows developers to add custom content or columns to each post row in the dashboard table.

## wpuf_account_posts_top

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The user ID. (required)
- `$post_type_obj` *(object)*: The post type object. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires at the top of the user's posts listing in the dashboard. This hook allows developers to add custom content or functionality above the posts table.

## wpuf_account_posts_nopost

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The user ID. (required)
- `$post_type_obj` *(object)*: The post type object. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when a user has no posts to display in the dashboard. This hook allows developers to add custom content or functionality when the posts list is empty.

## wsa_form_top_{form_id}

**Type**: Action

**Parameters**:
- `$form` *(array)*: The form configuration array. (required)

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires at the top of a specific settings form identified by the form ID. This hook allows developers to add custom content or functionality above specific admin forms.

## wpuf_paypal_subscription_cancelled

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The user ID whose subscription was cancelled. (required)
- `$subscription_id` *(string)*: The PayPal subscription ID. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires when a PayPal subscription is cancelled. This hook allows developers to perform additional actions when a PayPal subscription is cancelled, such as logging, notifications, or custom processing.

## wpuf_account_content_{current_section}

**Type**: Action

**Parameters**:
- `$sections` *(array)*: Array of available account sections. (required)
- `$current_section` *(string)*: The current section being displayed. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when rendering the content for a specific account section. This hook allows developers to add custom content or functionality to specific account sections like dashboard, posts, subscription, edit-profile, etc.

## wpuf_form_builder_template_builder_stage_submit_area

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires in the submit area of the form builder template. This hook allows developers to add custom content or functionality to the form builder's submit area.

## wpuf_form_builder_template_builder_stage_bottom_area

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires in the bottom area of the form builder template. This hook allows developers to add custom content or functionality to the form builder's bottom area.

## wpuf_builder_field_options

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires in the field options area of the form builder. This hook allows developers to add custom field options or functionality to the form builder.

## wpuf_before_subscription_listing

**Type**: Action

**Parameters**:
- `$packs` *(array)*: Array of subscription packs. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires before the subscription listing is displayed. This hook allows developers to add custom content or functionality before the subscription packs are listed.

## wpuf_after_subscription_listing

**Type**: Action

**Parameters**:
- `$packs` *(array)*: Array of subscription packs. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires after the subscription listing is displayed. This hook allows developers to add custom content or functionality after the subscription packs are listed.

## dokan_dashboard_content_before

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Integration Hooks

**Description**: Fires before the Dokan dashboard content is displayed. This hook allows developers to add custom content or functionality before the Dokan dashboard content.

## dokan_dashboard_support_content_before

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Integration Hooks

**Description**: Fires before the Dokan dashboard support content is displayed. This hook allows developers to add custom content or functionality before the Dokan support content.

## dokan_dashboard_content_after

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Integration Hooks

**Description**: Fires after the Dokan dashboard content is displayed. This hook allows developers to add custom content or functionality after the Dokan dashboard content.

## dokan_dashboard_support_content_after

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Integration Hooks

**Description**: Fires after the Dokan dashboard support content is displayed. This hook allows developers to add custom content or functionality after the Dokan support content.

## lostpassword_form

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: User Management

**Description**: Fires in the lost password form. This hook allows developers to add custom content or functionality to the lost password form.

## wpuf_reg_form_bottom

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: User Management

**Description**: Fires at the bottom of the registration form. This hook allows developers to add custom content, links, or functionality below the registration form fields.

## resetpassword_form

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: User Management

**Description**: Fires in the reset password form. This hook allows developers to add custom content or functionality to the reset password form.

## wpuf_dashboard_top

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The user ID. (required)
- `$post_type_obj` *(object)*: The post type object. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires at the top of the user dashboard. This hook allows developers to add custom content or functionality above the dashboard content.

## wpuf_dashboard_nopost

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The user ID. (required)
- `$post_type_obj` *(object)*: The post type object. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when a user has no posts to display in the dashboard. This hook allows developers to add custom content or functionality when the dashboard is empty.

## wpuf_dashboard_bottom

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The user ID. (required)
- `$post_type_obj` *(object)*: The post type object. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires at the bottom of the user dashboard. This hook allows developers to add custom content or functionality below the dashboard content.

## wpuf_loaded

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Utility Hooks

**Description**: Fires when the WP User Frontend plugin is fully loaded. This hook allows developers to perform actions after the plugin is initialized.

## wpuf_cancel_subscription_{gateway}

**Type**: Action

**Parameters**:
- `$_POST` *(array)*: The POST data containing cancellation information. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires when a subscription is cancelled for a specific payment gateway. This hook allows developers to perform additional actions when a subscription is cancelled, such as logging, notifications, or custom processing.

## wpuf_update_subscription_pack

**Type**: Action

**Parameters**:
- `$subscription_id` *(int)*: The subscription ID being updated. (required)
- `$post_data` *(array)*: The updated subscription data. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires when a subscription pack is updated. This hook allows developers to perform additional actions when a subscription pack is modified, such as logging, notifications, or custom processing.

## wpuf_account_content_subscription

**Type**: Action

**Parameters**:
- `$sections` *(array)*: Array of available account sections. (required)
- `$current_section` *(string)*: The current section being displayed. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when rendering the subscription content in the user account area. This hook allows developers to add custom content or functionality to the subscription section.

## wpuf_upload_file_init

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID where the file is being uploaded. (required)
- `$field_type` *(string)*: The type of field being uploaded. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when a file upload is initialized. This hook allows developers to perform additional actions when file uploads begin, such as validation, logging, or custom processing.

## wpuf_admin_menu_top

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires at the top of the admin menu creation process. This hook allows developers to add custom menu items or functionality before the main admin menu is built.

## wpuf_admin_menu

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires during the admin menu creation process. This hook allows developers to add custom menu items or functionality to the admin menu.

## wpuf_admin_menu_bottom

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires at the bottom of the admin menu creation process. This hook allows developers to add custom menu items or functionality after the main admin menu is built.

## wpuf_load_post_forms

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires when post forms are being loaded in the admin area. This hook allows developers to add custom functionality or scripts when post forms are initialized.

## wpuf_before_form_render

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID being rendered. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires before a form is rendered. This hook allows developers to add custom content or functionality before the form is displayed.

## wpuf_add_post_form_top

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires at the top of an add post form. This hook allows developers to add custom content or functionality above the add post form fields.

## wpuf_edit_post_form_top

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID. (required)
- `$post_id` *(int)*: The post ID being edited. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires at the top of an edit post form. This hook allows developers to add custom content or functionality above the edit post form fields.

## wpuf_add_post_form_bottom

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires at the bottom of an add post form. This hook allows developers to add custom content or functionality below the add post form fields.

## wpuf_edit_post_form_bottom

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID. (required)
- `$post_id` *(int)*: The post ID being edited. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires at the bottom of an edit post form. This hook allows developers to add custom content or functionality below the edit post form fields.

## wpuf_after_form_render

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID that was rendered. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after a form is rendered. This hook allows developers to add custom content or functionality after the form is displayed.

## wpuf_render_form_{input_type}

**Type**: Action

**Parameters**:
- `$form_field` *(array)*: The form field configuration. (required)
- `$form_id` *(int)*: The form ID. (required)
- `$post_id` *(int)*: The post ID (if editing). (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering a specific input type field in a form. This hook allows developers to add custom rendering logic for specific field types.

## wpuf_render_pro_{input_type}

**Type**: Action

**Parameters**:
- `$form_field` *(array)*: The form field configuration. (required)
- `$post_id` *(int)*: The post ID. (required)
- `$type` *(string)*: The field type. (required)
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)
- `$class_name` *(string)*: The class name. (required)
- `$instance` *(object)*: The form instance. (required)
- `$multiform_start` *(int)*: The multiform start value. (required)
- `$enable_multistep` *(bool)*: Whether multistep is enabled. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering a pro input type field in a form. This hook allows developers to add custom rendering logic for pro field types.

## wpuf_before_register_scripts

**Type**: Action

**Parameters**:
- `$scripts` *(array)*: Array of scripts to be registered. (required)
- `$styles` *(array)*: Array of styles to be registered. (required)

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires before scripts and styles are registered. This hook allows developers to modify the scripts and styles arrays before they are registered.

## wpuf_after_register_scripts

**Type**: Action

**Parameters**:
- `$scripts` *(array)*: Array of registered scripts. (required)
- `$styles` *(array)*: Array of registered styles. (required)

**Return Value**: None

**Category**: Admin Functions

**Description**: Fires after scripts and styles are registered. This hook allows developers to perform additional actions after scripts and styles are registered.

## lostpassword_post

**Type**: Action

**Parameters**:
- `$errors` *(WP_Error)*: The errors object containing any validation errors. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when the lost password form is submitted. This hook allows developers to perform additional actions when a user requests a password reset.

## wpuf_edit_post_after_update

**Type**: Action

**Parameters**:
- `$post_id` *(int)*: The post ID that was updated. (required)
- `$form_id` *(int)*: The form ID used for the update. (required)
- `$form_settings` *(array)*: The form settings array. (required)
- `$form_fields` *(array)*: The form fields array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after a post is updated via the frontend form. This hook allows developers to perform additional actions when a post is updated, such as logging, notifications, or custom processing.

## wpuf_add_post_after_insert

**Type**: Action

**Parameters**:
- `$post_id` *(int)*: The post ID that was created. (required)
- `$form_id` *(int)*: The form ID used for the creation. (required)
- `$form_settings` *(array)*: The form settings array. (required)
- `$meta_vars` *(array)*: The meta variables array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after a post is inserted via the frontend form. This hook allows developers to perform additional actions when a post is created, such as logging, notifications, or custom processing.

## register_post

**Type**: Action

**Parameters**:
- `$username` *(string)*: The username being registered. (required)
- `$email` *(string)*: The email address being registered. (required)
- `$errors` *(WP_Error)*: The errors object containing any validation errors. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when a user registration is attempted. This hook allows developers to perform additional actions during user registration, such as validation, logging, or custom processing.

## tml_new_user_registered

**Type**: Action

**Parameters**:
- `$user_id` *(int)*: The ID of the newly registered user. (required)
- `$user_pass` *(string)*: The user's password. (required)

**Return Value**: None

**Category**: User Management

**Description**: Fires when a new user is successfully registered. This hook allows developers to perform additional actions when a new user is created, such as welcome emails, notifications, or custom processing.

## wpuf_conditional_field_render_hook

**Type**: Action

**Parameters**:
- `$field_id` *(string)*: The field ID. (required)
- `$con_fields` *(array)*: The conditional fields array. (required)
- `$obj` *(string)*: The object class name. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when conditional fields are being rendered. This hook allows developers to add custom logic for conditional field rendering.

## wpuf-form-builder-tabs-{form_type}

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering tabs in the form builder for a specific form type. This hook allows developers to add custom tabs to the form builder interface.

## wpuf_form_builder_settings_tabs_{form_type}

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering settings tabs in the form builder for a specific form type. This hook allows developers to add custom settings tabs to the form builder interface.

## wpuf-form-builder-tab-contents-{form_type}

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering tab contents in the form builder for a specific form type. This hook allows developers to add custom tab content to the form builder interface.

## wpuf-form-builder-settings-tabs-{form_type}

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering settings tabs in the form builder for a specific form type. This hook allows developers to add custom settings tabs to the form builder interface.

## wpuf-form-builder-settings-tab-contents-{form_type}

**Type**: Action

**Parameters**: None

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering settings tab contents in the form builder for a specific form type. This hook allows developers to add custom settings tab content to the form builder interface.

## wpuf_form_setting

**Type**: Action

**Parameters**:
- `$form_settings` *(array)*: The form settings array. (required)
- `$post` *(WP_Post)*: The form post object. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering form settings. This hook allows developers to add custom form settings or functionality.

## wpuf_form_setting_payment

**Type**: Action

**Parameters**:
- `$form_settings` *(array)*: The form settings array. (required)
- `$post` *(WP_Post)*: The form post object. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering payment form settings. This hook allows developers to add custom payment settings or functionality.

## wpuf_form_submission_restriction

**Type**: Action

**Parameters**:
- `$form_settings` *(array)*: The form settings array. (required)
- `$post` *(WP_Post)*: The form post object. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering form submission restriction settings. This hook allows developers to add custom submission restriction settings or functionality.

## wpuf_before_post_form_settings_field

**Type**: Action

**Parameters**:
- `$field` *(array)*: The field configuration. (required)
- `$value` *(mixed)*: The field value. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires before rendering a post form settings field. This hook allows developers to add custom content or functionality before form settings fields.

## wpuf_before_post_form_settings_field_{field_key}

**Type**: Action

**Parameters**:
- `$field` *(array)*: The field configuration. (required)
- `$value` *(mixed)*: The field value. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires before rendering a specific post form settings field identified by the field key. This hook allows developers to add custom content or functionality before specific form settings fields.

## wpuf_after_post_form_settings_field_{field_key}

**Type**: Action

**Parameters**:
- `$field` *(array)*: The field configuration. (required)
- `$value` *(mixed)*: The field value. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after rendering a specific post form settings field identified by the field key. This hook allows developers to add custom content or functionality after specific form settings fields.

## wpuf_after_post_form_settings_field

**Type**: Action

**Parameters**:
- `$field` *(array)*: The field configuration. (required)
- `$value` *(mixed)*: The field value. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after rendering a post form settings field. This hook allows developers to add custom content or functionality after form settings fields.

## wpuf_submit_btn

**Type**: Action

**Parameters**:
- `$form_id` *(int)*: The form ID. (required)
- `$form_settings` *(array)*: The form settings array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires when rendering the submit button for a form. This hook allows developers to add custom content or functionality to the submit button area.

## wpuf_form_fields_top

**Type**: Action

**Parameters**:
- `$form` *(array)*: The form configuration. (required)
- `$form_fields` *(array)*: The form fields array. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires at the top of the form fields area. This hook allows developers to add custom content or functionality above the form fields.

## wpuf_radio_field_after_label

**Type**: Action

**Parameters**:
- `$field_settings` *(array)*: The radio field settings. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after the label of a radio field is rendered. This hook allows developers to add custom content or functionality after radio field labels.

## WPUF_multidropdown_field_after_label

**Type**: Action

**Parameters**:
- `$field_settings` *(array)*: The multidropdown field settings. (required)

**Return Value**: None

**Category**: Form Processing

**Description**: Fires after the label of a multidropdown field is rendered. This hook allows developers to add custom content or functionality after multidropdown field labels.

## wpuf_before_update_subscription_single_row

**Type**: Action

**Parameters**:
- `$id` *(int)*: The subscription ID. (required)
- `$request` *(array)*: The request data. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires before updating a single subscription row. This hook allows developers to perform additional actions before a subscription is updated.

## wpuf_after_update_subscription_single_row

**Type**: Action

**Parameters**:
- `$id` *(int)*: The subscription ID. (required)
- `$request` *(array)*: The request data. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires after updating a single subscription row. This hook allows developers to perform additional actions after a subscription is updated.

## wpuf_before_update_subscription_pack

**Type**: Action

**Parameters**:
- `$id` *(int)*: The subscription pack ID. (required)
- `$request` *(array)*: The request data. (required)
- `$post_arr` *(array)*: The post array. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires before updating a subscription pack. This hook allows developers to perform additional actions before a subscription pack is updated.

## wpuf_before_update_subscription_pack_meta

**Type**: Action

**Parameters**:
- `$id` *(int)*: The subscription pack ID. (required)
- `$request` *(array)*: The request data. (required)

**Return Value**: None

**Category**: Payment & Subscriptions

**Description**: Fires before updating subscription pack meta data. This hook allows developers to perform additional actions before subscription pack meta is updated. 