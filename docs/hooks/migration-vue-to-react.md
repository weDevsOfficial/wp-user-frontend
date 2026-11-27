DESCRIPTION: Migration guide from the retired Vue form builder hooks to their React replacements.
Read when a plugin used a Vue builder hook, or when a deprecation notice names one.

# Vue builder hooks -> React

The Vue/jQuery form builder is removed. Its hooks still fire through deprecation shims (`includes/Builder/HookDeprecations.php`) for at least one major release: when a callback outside WP User Frontend (free, Pro, Pro modules) is attached, the hook fires through `apply_filters_deprecated()` / `do_action_deprecated()` (a notice under `WP_DEBUG`) and admins see one dismissible notice naming the hook and the callback's plugin. Output of the template actions was Vue markup and is discarded. Move to the replacement below.

React slots are filled with `wp.plugins.registerPlugin( 'my-plugin', { scope: 'wpuf-form-builder', render } )` and `<Fill name="...">` from `wp.components`; slot names are also on `window.wpuf.builderSlots`.

| Retired hook | Replacement |
|---|---|
| `wpuf_form_builder_js_root_mixins`, `wpuf_form_builder_js_builder_stage_mixins`, `wpuf_form_builder_js_form_fields_mixins`, `wpuf_form_builder_js_field_options_mixins` | `window.wpuf.registerFieldPreview`, `registerFieldSettingInput`, `registerFieldValidator`; filters `wpuf.formBuilder.fieldSettings`, `wpuf.formBuilder.panelSections`, `wpuf.formBuilder.canvasRender` |
| `wpuf_form_builder_add_js_templates`, global `wpuf_mixins` | `registerFieldPreview` / `registerFieldSettingInput` from a script that depends on the builder handle (`wpuf_form_builder_js_deps` is retired too: the legacy `wpuf-form-builder-mixins` / `-components` / `wpuf-form-builder` / `-wpuf-forms` handles, Vue and Vuex are no longer registered by the free plugin, so the filter fires through `HookDeprecations` with a notice when outside code listens and its result is not used; the React bundle declares its own dependencies) |
| `wpuf_builder_field_options` | slot `wpuf-form-builder-field-options-after` |
| `wpuf_field_option_data_actions`, `wpuf_field_option_data_after` | filter `wpuf.formBuilder.optionDataBulkAdd`; slots `wpuf-form-builder-option-data-actions`, `wpuf-form-builder-option-data-after` |
| `wpuf_form_builder_template_builder_stage_submit_area`, `..._bottom_area` | slots `wpuf-form-builder-canvas-submit-area`, `wpuf-form-builder-canvas-bottom` |
| `wpuf-form-builder-tabs-{type}`, `wpuf_form_builder_settings_tabs_{type}`, `wpuf-form-builder-tab-contents-{type}`, `wpuf_post_form_tab`, `wpuf_profile_form_tab` | filter `wpuf.formBuilder.settingsTabs` + slot `wpuf-form-builder-settings-<tab>` (output still bridged, see below) |
| `wpuf_before|after_post_form_settings_field[_{key}]`, `wpuf_before|after_registration_form_settings_field[_{key}]` | slot `wpuf-form-builder-settings-<tab>` (output still bridged, see below) |
| `wpuf_form_builder.event_hub` (`$on`, `$emit`, `$off`) | actions `wpuf.formBuilder.event.<name>` (`addAction` / `doAction`) |

## Example: custom field preview
Before (Vue):
```php
add_filter( 'wpuf_form_builder_js_form_fields_mixins', function ( $mixins ) {
    $mixins[] = 'my_field_mixin';
    return $mixins;
} );
```
After (React):
```js
window.wpuf.registerFieldPreview( 'my_field', ( { field } ) => <MyPreview field={ field } /> );
```
Enqueue the script with the builder handle as a dependency on `wpuf_form_builder_enqueue_after_components`.

## Bridged view hooks: still rendered, now deprecated
The Vue builder's plain-PHP view hooks (settings rows `wpuf_before|after_*_form_settings_field[_{key}]` and the settings tab hooks above) keep firing with develop's arguments and order, and their output is still bridged into the React panels (`includes/Builder/HookBridge.php`, `docs/hooks/php.md`). Since they belonged to the Vue builder they are retired too: with an outside callback attached they fire through `do_action_deprecated()` and appear in the same admin notice. WPUF's own listeners are React parts and stay silent.

## Not fired by develop either
`wpuf-form-builder-settings-tabs-{type}`, `wpuf-form-builder-settings-tab-contents-{type}`, `wpuf_form_setting`, `wpuf_form_setting_payment`, `wpuf_form_submission_restriction`, `wpuf_form_post_expiration`, `wpuf_post_form_tab_content`, `wpuf_profile_form_tab_content` (old builder view and settings partials develop never rendered), `wpuf-form-fields*` (old `class-field-manager.php`, not loaded), `wpuf_admin_template_post_{template}` (Pro's pre-Vue element printers, not loaded) and `wpuf_tec_validate_past_dates` (unused Events Calendar validator): the code is removed and the hooks are not fired, as before. `wpuf_setup_wizard_styles` belonged to the PHP setup wizard, replaced by the React onboarding (no Vue).

## Classic subscription pack editor: React screen, same hooks
The classic `wpuf_subscription` editor (post.php metaboxes) is gone; the post type has no WordPress screens any more (`show_ui` false): packs are managed on the React subscriptions screen only. Old `edit.php?post_type=wpuf_subscription`, `post-new.php?post_type=wpuf_subscription` and `post.php?post=N&action=edit` links redirect to `admin.php?page=wpuf_subscription` (list, `&action=new`, `&action=edit&id=N`). The classic form's markup hooks still fire, with the classic arguments, and what they print shows under "More options" on the React editor; their inputs are posted back on save: `wpuf_admin_subs_nav_tab`, `wpuf_admin_subs_nav_content`, `wpuf_admin_subscription_detail`, `wpuf_admin_subscription_post_restriction` (`SubscriptionStore::legacy_fields()`). For new pack fields prefer the React screen's filters: `wpuf_subscription_section_advanced` (and the other `wpuf_subscription_section_*` filters) for a section, `wpuf_subscriptions_fields` / `wpuf_subscription_payment_fields` for its fields, and save them on `wpuf_after_update_subscription_pack_meta` / `wpuf_update_subscription_pack` (both still fire from the REST save). Pro's taxonomy restriction and recurring payment rows already work that way. `Admin_Subscription::option_field()` / `lenght_type_option()` (classic editor markup, used only by Pro before 5.0.0) and the admin-ajax `delete_user_package()` are removed.

## admin-ajax actions -> REST
WPUF's own scripts call REST routes (`wpuf/v1/...`) instead of admin-ajax. The old handler methods stay as deprecated public methods, and every hook that fired inside a handler fires from the service the REST route calls, so code hooked on `wpuf_add_post_after_insert`, `wpuf_upload_file_init`, the login and registration hooks keeps working. What changes for outside code:

- A request to an old action (`admin-ajax.php?action=wpuf_submit_post` and the others below) gets `0`. Headless or custom clients move to the REST route.
- Callbacks on `wp_ajax_wpuf_*` / `wp_ajax_nopriv_wpuf_*` (for example a pre-check at a lower priority) no longer run. Use the documented WPUF hooks of that step instead (`wpuf_add_post_validate`, `wpuf_process_login_errors`, `wpuf_register_user_args`, `wpuf_update_profile_vars`).
- Pages cached with an older WPUF script post to admin-ajax: purge the page cache after the update.

| Old action | REST route (`wpuf/v1`) |
|---|---|
| `wpuf_submit_post` | `POST\|PUT forms/{id}/submissions[/{post}]` |
| `wpuf_draft_post` | `POST forms/{id}/drafts` |
| `wpuf_upload_file`, `wpuf_file_del`, `wpuf_insert_image` | `POST uploads`, `DELETE uploads/{id}` |
| `wpuf_make_media_embed_code` | `POST forms/{id}/embed` |
| `wpuf_get_child_cat` | `GET forms/{id}/child-terms` |
| `wpuf_ajax_tag_search` | `GET terms/suggest` |
| `wpuf_ajax_login`, `wpuf_lost_password`, `wpuf_ajax_logout` | `POST auth/login\|lost-password\|logout` |
| `wpuf_ajax_address`, `wpuf_update_billing_address` | `GET account/billing-address/states`, `PUT account/billing-address` |
| `wpuf_delete_user_package` | `DELETE admin/users/{id}/pack` |
| `wpuf_clear_schedule_lock` | `DELETE admin/posts/{id}/lock` |
| Pro `wpuf_submit_register`, `wpuf_update_profile` | `POST registration/{id}/submissions`, `PUT registration/{id}/profile` |
| Pro `wpuf_delete_avatar` | `DELETE profile/avatar` |
| Pro `wpuf_coupon_apply`, `wpuf_coupon_cancel` | `POST coupons/apply\|cancel` |
| Pro `wpuf_create_stripe_payment_intent` | `POST payments/stripe/intent` |
| Pro `wpuf_update_comment`, `wpuf_comment_status` | `PUT comments/{id}`, `POST comments/{id}/status` |

Still on admin-ajax: `wpuf_account_update_profile` and `wpuf_account_change_password` (older theme copies of the account templates post them) and Pro's `wpuf_pro_install_wp_user_frontend` (runs while free is missing).
