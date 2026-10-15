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

## Classic subscription pack editor hooks: no longer fired
The classic `wpuf_subscription` editor (post.php metaboxes) is gone; the post type has no WordPress screens any more (`show_ui` false): packs are managed on the React subscriptions screen only, old `edit.php?post_type=wpuf_subscription` / `post.php?post=N&action=edit` links show WordPress's permission message. Its markup hooks have nowhere to print and are not fired any more: `wpuf_admin_subs_nav_tab`, `wpuf_admin_subs_nav_content`, `wpuf_admin_subscription_detail`, `wpuf_admin_subscription_post_restriction`. Add pack fields with the React screen's filters instead: `wpuf_subscription_section_advanced` (and the other `wpuf_subscription_section_*` filters) for a section, `wpuf_subscriptions_fields` / `wpuf_subscription_payment_fields` for its fields, and save them on `wpuf_after_update_subscription_pack_meta` / `wpuf_update_subscription_pack` (both still fire from the REST save). Pro's taxonomy restriction and recurring payment rows already work that way. `Admin_Subscription::option_field()` / `lenght_type_option()` stay for older Pro versions.
