# WP User Frontend - Hook List

This document contains all the hooks (actions and filters) created in the WP User Frontend plugin.

## Action Hooks

| Hook | Type | Documented |
|------|------|------------|
| before_appsero_license_section | action | Yes |
| after_appsero_license_section | action | Yes |
| wpuf_gateway_bank_order_submit | action | Yes |
| wpuf_login_form_bottom | action | No |
| wpuf_account_posts_head_col | action | No |
| wpuf_account_posts_row_col | action | No |
| wpuf_account_posts_top | action | No |
| wpuf_account_posts_nopost | action | No |
| wsa_form_top_{form_id} | action | No |
| wsa_form_bottom_{form_id} | action | No |
| wpuf_paypal_subscription_cancelled | action | No |
| wpuf_account_content_{current_section} | action | No |
| wpuf_form_builder_template_builder_stage_submit_area | action | No |
| wpuf_form_builder_template_builder_stage_bottom_area | action | No |
| wpuf_builder_field_options | action | No |
| wpuf_before_subscription_listing | action | No |
| wpuf_after_subscription_listing | action | No |
| dokan_dashboard_content_before | action | No |
| dokan_dashboard_support_content_before | action | No |
| dokan_dashboard_content_after | action | No |
| dokan_dashboard_support_content_after | action | No |
| lostpassword_form | action | No |
| wpuf_reg_form_bottom | action | No |
| resetpassword_form | action | No |
| wpuf_dashboard_top | action | No |
| wpuf_dashboard_nopost | action | No |
| wpuf_dashboard_bottom | action | No |
| wpuf_loaded | action | No |
| wpuf_cancel_subscription_{gateway} | action | No |
| wpuf_update_subscription_pack | action | No |
| wpuf_account_content_subscription | action | No |
| wpuf_upload_file_init | action | No |
| wpuf_admin_menu_top | action | No |
| wpuf_admin_menu | action | No |
| wpuf_admin_menu_bottom | action | No |
| wpuf_load_post_forms | action | No |
| wpuf_before_form_render | action | No |
| wpuf_add_post_form_top | action | No |
| wpuf_edit_post_form_top | action | No |
| wpuf_add_post_form_bottom | action | No |
| wpuf_edit_post_form_bottom | action | No |
| wpuf_after_form_render | action | No |
| wpuf_render_form_{input_type} | action | No |
| wpuf_render_pro_{input_type} | action | No |
| wpuf_before_register_scripts | action | No |
| wpuf_after_register_scripts | action | No |
| lostpassword_post | action | No |
| wpuf_edit_post_after_update | action | No |
| wpuf_add_post_after_insert | action | No |
| register_post | action | No |
| tml_new_user_registered | action | No |
| wpuf_conditional_field_render_hook | action | No |
| wpuf-form-builder-tabs-{form_type} | action | No |
| wpuf_form_builder_settings_tabs_{form_type} | action | No |
| wpuf-form-builder-tab-contents-{form_type} | action | No |
| wpuf-form-builder-settings-tabs-{form_type} | action | No |
| wpuf-form-builder-settings-tab-contents-{form_type} | action | No |
| wpuf_form_setting | action | No |
| wpuf_form_setting_payment | action | No |
| wpuf_form_submission_restriction | action | No |
| wpuf_before_post_form_settings_field | action | No |
| wpuf_before_post_form_settings_field_{field_key} | action | No |
| wpuf_after_post_form_settings_field_{field_key} | action | No |
| wpuf_after_post_form_settings_field | action | No |
| wpuf_submit_btn | action | No |
| wpuf_form_fields_top | action | No |
| wpuf_radio_field_after_label | action | No |
| WPUF_multidropdown_field_after_label | action | No |
| wpuf_before_update_subscription_single_row | action | No |
| wpuf_after_update_subscription_single_row | action | No |
| wpuf_before_update_subscription_pack | action | No |
| wpuf_before_update_subscription_pack_meta | action | No |

## Filter Hooks

| Hook | Type | Documented |
|------|------|------------|
| wpuf_account_unauthorized | filter | Yes |
| wpuf_my_account_tab_links | filter | Yes |
| wpuf_dashboard_query | filter | Yes |
| wpuf_no_image | filter | No |
| wpuf_preview_link_separator | filter | No |
| login_message | filter | No |
| wpuf_account_edit_profile_content | filter | No |
| wpuf_payment_amount | filter | No |
| wpuf_mail_bank_admin_subject | filter | No |
| wpuf_mail_bank_admin_body | filter | No |
| wpuf_mail_bank_user_subject | filter | No |
| wpuf_mail_bank_user_body | filter | No |
| wpuf_get_subscription_meta | filter | No |
| wpuf_posts_type | filter | No |
| wpuf_subscription_packs | filter | No |
| wpuf_subscription_cycle_label | filter | No |
| wpuf_ppp_notice | filter | No |
| wpuf_pack_notice | filter | No |
| appsero_endpoint | filter | No |
| appsero_is_local | filter | No |
| wpuf_update_post_validate | filter | No |
| wpuf_add_post_validate | filter | No |
| wpuf_update_post_args | filter | No |
| wpuf_add_post_args | filter | No |
| wpuf_edit_post_redirect | filter | No |
| wpuf_add_post_redirect | filter | No |
| wpuf_textarea_editor_args | filter | No |
| wpuf_taxonomy_checklist_args | filter | No |
| wpuf_styles_to_register | filter | No |
| wpuf_scripts_to_register | filter | No |
| retrieve_password_title | filter | No |
| retrieve_password_message | filter | No |
| widget_title | filter | No |
| widget_text_content | filter | No |
| widget_text | filter | No |
| login_link_separator | filter | No |
| wpuf_text_field_option_settings | filter | No |
| wpuf_upload_response_image_size | filter | No |
| wpuf_upload_response_image_type | filter | No |
| wpuf_column_field_option_settings | filter | No |
| wpuf_field_get_js_settings | filter | No |
| wpuf-form-builder-common-taxonomy-fields-properties | filter | No |
| wpuf-form-builder-common-text-fields-properties | filter | No |
| wpuf_show_post_status | filter | No |
| wpuf_get_post_types | filter | No |
| wpuf_front_post_edit_link | filter | No |
| list_cats | filter | No |
| the_category | filter | No |
| wpuf_allowed_extensions | filter | No |
| wpuf_use_default_avatar | filter | No |
| wpuf_admin_role | filter | No |
| wpuf_custom_field_render | filter | No |
| wpuf-get-form-fields | filter | No |
| wpuf_get_post_form_templates | filter | No |
| wpuf_get_pro_form_previews | filter | No |
| wpuf_account_sections | filter | No |
| wpuf_currencies | filter | No |
| wpuf_price_format | filter | No |
| wpuf_raw_price | filter | No |
| wpuf_formatted_price | filter | No |
| wpuf_price_trim_zeros | filter | No |
| wpuf_format_price | filter | No |
| wpuf_email_header | filter | No |
| wpuf_email_footer | filter | No |
| wpuf_email_style | filter | No |
| wpuf_post_forms_list_table_post_statuses | filter | No |

## Notes

- This list was generated by scanning all PHP files in the wp-user-frontend project
- Action hooks are triggered using `do_action()`
- Filter hooks are triggered using `apply_filters()`
- All hooks are marked as "No" in the documented column initially
- As each hook is documented, the status should be updated to "Yes"
