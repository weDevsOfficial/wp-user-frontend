# WP User Frontend - Hook List

This document contains all the hooks (actions and filters) created in the WP User Frontend plugin.

## Action Hooks

| # | Hook | Type | Documented |
|---|------|------|------------|
| 1 | before_appsero_license_section | action | Yes |
| 2 | after_appsero_license_section | action | Yes |
| 3 | wpuf_gateway_bank_order_submit | action | Yes |
| 4 | wpuf_login_form_bottom | action | Yes |
| 5 | wpuf_account_posts_head_col | action | Yes |
| 6 | wpuf_account_posts_row_col | action | Yes |
| 7 | wpuf_account_posts_top | action | Yes |
| 8 | wpuf_account_posts_nopost | action | Yes |
| 9 | wsa_form_top_{form_id} | action | Yes |
| 10 | wsa_form_bottom_{form_id} | action | Yes |
| 11 | wpuf_paypal_subscription_cancelled | action | Yes |
| 12 | wpuf_account_content_{current_section} | action | Yes |
| 13 | wpuf_form_builder_template_builder_stage_submit_area | action | Yes |
| 14 | wpuf_form_builder_template_builder_stage_bottom_area | action | Yes |
| 15 | wpuf_builder_field_options | action | Yes |
| 16 | wpuf_before_subscription_listing | action | Yes |
| 17 | wpuf_after_subscription_listing | action | Yes |
| 18 | dokan_dashboard_content_before | action | Yes |
| 19 | dokan_dashboard_support_content_before | action | Yes |
| 20 | dokan_dashboard_content_after | action | Yes |
| 21 | dokan_dashboard_support_content_after | action | Yes |
| 22 | lostpassword_form | action | Yes |
| 23 | wpuf_reg_form_bottom | action | Yes |
| 24 | resetpassword_form | action | Yes |
| 25 | wpuf_dashboard_top | action | Yes |
| 26 | wpuf_dashboard_nopost | action | Yes |
| 27 | wpuf_dashboard_bottom | action | Yes |
| 28 | wpuf_loaded | action | Yes |
| 29 | wpuf_cancel_subscription_{gateway} | action | Yes |
| 30 | wpuf_update_subscription_pack | action | Yes |
| 31 | wpuf_account_content_subscription | action | Yes |
| 32 | wpuf_upload_file_init | action | Yes |
| 33 | wpuf_admin_menu_top | action | Yes |
| 34 | wpuf_admin_menu | action | Yes |
| 35 | wpuf_admin_menu_bottom | action | Yes |
| 36 | wpuf_load_post_forms | action | Yes |
| 37 | wpuf_before_form_render | action | Yes |
| 38 | wpuf_add_post_form_top | action | Yes |
| 39 | wpuf_edit_post_form_top | action | Yes |
| 40 | wpuf_add_post_form_bottom | action | Yes |
| 41 | wpuf_edit_post_form_bottom | action | Yes |
| 42 | wpuf_after_form_render | action | Yes |
| 43 | wpuf_render_form_{input_type} | action | Yes |
| 44 | wpuf_render_pro_{input_type} | action | Yes |
| 45 | wpuf_before_register_scripts | action | Yes |
| 46 | wpuf_after_register_scripts | action | Yes |
| 47 | lostpassword_post | action | Yes |
| 48 | wpuf_edit_post_after_update | action | Yes |
| 49 | wpuf_add_post_after_insert | action | Yes |
| 50 | register_post | action | Yes |
| 51 | tml_new_user_registered | action | Yes |
| 52 | wpuf_conditional_field_render_hook | action | Yes |
| 53 | wpuf-form-builder-tabs-{form_type} | action | Yes |
| 54 | wpuf_form_builder_settings_tabs_{form_type} | action | Yes |
| 55 | wpuf-form-builder-tab-contents-{form_type} | action | Yes |
| 56 | wpuf-form-builder-settings-tabs-{form_type} | action | Yes |
| 57 | wpuf-form-builder-settings-tab-contents-{form_type} | action | Yes |
| 58 | wpuf_form_setting | action | Yes |
| 59 | wpuf_form_setting_payment | action | Yes |
| 60 | wpuf_form_submission_restriction | action | Yes |
| 61 | wpuf_before_post_form_settings_field | action | Yes |
| 62 | wpuf_before_post_form_settings_field_{field_key} | action | Yes |
| 63 | wpuf_after_post_form_settings_field_{field_key} | action | Yes |
| 64 | wpuf_after_post_form_settings_field | action | Yes |
| 65 | wpuf_submit_btn | action | Yes |
| 66 | wpuf_form_fields_top | action | Yes |
| 67 | wpuf_radio_field_after_label | action | Yes |
| 68 | WPUF_multidropdown_field_after_label | action | Yes |
| 69 | wpuf_before_update_subscription_single_row | action | Yes |
| 70 | wpuf_after_update_subscription_single_row | action | Yes |
| 71 | wpuf_before_update_subscription_pack | action | Yes |
| 72 | wpuf_before_update_subscription_pack_meta | action | Yes |

## Filter Hooks

| # | Hook | Type | Documented |
|---|------|------|------------|
| 1 | wpuf_account_unauthorized | filter | Yes |
| 2 | wpuf_my_account_tab_links | filter | Yes |
| 3 | wpuf_dashboard_query | filter | Yes |
| 4 | wpuf_no_image | filter | Yes |
| 5 | wpuf_preview_link_separator | filter | Yes |
| 6 | login_message | filter | Yes |
| 7 | wpuf_account_edit_profile_content | filter | Yes |
| 8 | wpuf_payment_amount | filter | Yes |
| 9 | wpuf_mail_bank_admin_subject | filter | Yes |
| 10 | wpuf_mail_bank_admin_body | filter | Yes |
| 11 | wpuf_mail_bank_user_subject | filter | Yes |
| 12 | wpuf_mail_bank_user_body | filter | Yes |
| 13 | wpuf_get_subscription_meta | filter | Yes |
| 14 | wpuf_posts_type | filter | Yes |
| 15 | wpuf_subscription_packs | filter | Yes |
| 16 | wpuf_subscription_cycle_label | filter | Yes |
| 17 | wpuf_ppp_notice | filter | Yes |
| 18 | wpuf_pack_notice | filter | Yes |
| 19 | appsero_endpoint | filter | No |
| 20 | appsero_is_local | filter | No |
| 21 | wpuf_update_post_validate | filter | Yes |
| 22 | wpuf_add_post_validate | filter | Yes |
| 23 | wpuf_update_post_args | filter | Yes |
| 24 | wpuf_add_post_args | filter | Yes |
| 25 | wpuf_edit_post_redirect | filter | Yes |
| 26 | wpuf_add_post_redirect | filter | Yes |
| 27 | wpuf_textarea_editor_args | filter | Yes |
| 28 | wpuf_taxonomy_checklist_args | filter | Yes |
| 29 | wpuf_styles_to_register | filter | Yes |
| 30 | wpuf_scripts_to_register | filter | Yes |
| 31 | retrieve_password_title | filter | Yes |
| 32 | retrieve_password_message | filter | Yes |
| 33 | widget_title | filter | Yes |
| 34 | widget_text_content | filter | Yes |
| 35 | widget_text | filter | Yes |
| 36 | login_link_separator | filter | Yes |
| 37 | wpuf_text_field_option_settings | filter | Yes |
| 38 | wpuf_upload_response_image_size | filter | Yes |
| 39 | wpuf_upload_response_image_type | filter | Yes |
| 40 | wpuf_column_field_option_settings | filter | Yes |
| 41 | wpuf_field_get_js_settings | filter | Yes |
| 42 | wpuf-form-builder-common-taxonomy-fields-properties | filter | Yes |
| 43 | wpuf-form-builder-common-text-fields-properties | filter | Yes |
| 44 | wpuf_show_post_status | filter | Yes |
| 45 | wpuf_get_post_types | filter | Yes |
| 46 | wpuf_front_post_edit_link | filter | Yes |
| 47 | list_cats | filter | Yes |
| 48 | the_category | filter | Yes |
| 49 | wpuf_allowed_extensions | filter | Yes |
| 50 | wpuf_use_default_avatar | filter | Yes |
| 51 | wpuf_admin_role | filter | Yes |
| 52 | wpuf_custom_field_render | filter | Yes |
| 53 | wpuf-get-form-fields | filter | Yes |
| 54 | wpuf_get_post_form_templates | filter | Yes |
| 55 | wpuf_get_pro_form_previews | filter | Yes |
| 56 | wpuf_account_sections | filter | Yes |
| 57 | wpuf_currencies | filter | Yes |
| 58 | wpuf_price_format | filter | Yes |
| 59 | wpuf_raw_price | filter | Yes |
| 60 | wpuf_formatted_price | filter | Yes |
| 61 | wpuf_price_trim_zeros | filter | Yes |
| 62 | wpuf_format_price | filter | Yes |
| 63 | wpuf_email_header | filter | Yes |
| 64 | wpuf_email_footer | filter | Yes |
| 65 | wpuf_email_style | filter | Yes |
| 66 | wpuf_post_forms_list_table_post_statuses | filter | Yes |

## Notes

- This list was generated by scanning all PHP files in the wp-user-frontend project
- Action hooks are triggered using `do_action()`
- Filter hooks are triggered using `apply_filters()`
