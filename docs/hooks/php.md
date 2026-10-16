DESCRIPTION: Rules for WPUF PHP hooks (actions and filters): who fires them, how to add, change, bridge and retire one.
Read before adding, moving or removing a do_action / apply_filters call, or when an admin screen moves to React.

# PHP hooks

Full list with argument counts and call sites: `docs/hooks/php-index.md` (generated). Runtime list per admin screen: `tests/contracts/runtime-recorder.php`.

## Who fires a hook

WPUF fires every hook it owns. Third parties (and WPUF's own pro/modules) only attach listeners with `add_action` / `add_filter`. If WPUF stops calling a hook, every listener goes silent with no error. A third party cannot fire it for us: it has no access to our arguments, our render point or our save flow.

So: **no WPUF hook may stop firing.** When a PHP-rendered admin screen becomes a React screen, the React path still calls the same PHP hooks, at the equivalent point, with the same arguments.

## Adding a hook
- Name: `wpuf_` prefix, snake_case, `wpuf_{context}_{action}`. New REST/store hooks follow the Dokan pattern: `wpuf_rest_prepare_{resource}`, `wpuf_rest_{resource}_query`, `wpuf_rest_insert_{resource}`, `wpuf_before|after_{store}_save`.
- Docblock above the call with `@since WPUF_SINCE` and every `@param`.
- Pass the object/ids the listener needs; do not pass data the current user may not see.
- Regenerate the index: `php tests/contracts/static-hooks.php --md . > docs/hooks/php-index.md`.

## Changing a hook
- Never change a name, argument order, argument type or return contract.
- Add arguments only at the end, never remove one.
- Never move a call to a later/earlier point in the request where listeners would see different state.

## Retiring a hook
Only with owner approval, and only the WordPress way:
1. Keep firing it through `apply_filters_deprecated( $name, $args, WPUF_SINCE, $replacement )` / `do_action_deprecated(...)`.
2. If a non-WPUF callback is attached, show admins a one-time dismissible notice naming the callback and linking to the migration guide.
3. Document old -> new in `docs/hooks/migration-vue-to-react.md` and the changelog ("Deprecated").
4. Keep the shim for at least one major release; announce removal in the changelog first.

## Plain-PHP settings hooks on React screens (bridge)

These printed HTML into the old builder/settings markup. On React screens:

| Hooks | Fired by (target) | WPUF listeners (ported to React parts) |
|---|---|---|
| `wpuf_before\|after_post_form_settings_field[_{key}]` | builder HookBridge, per field, same args | pro `Post_Form.php:83` (`_limit_message`, submit-button conditions) |
| `wpuf_before\|after_registration_form_settings_field[_{key}]` | same | Mailchimp `wpuf-mailchimp.php:75` (`_enable_double_optin`, conditions) |
| `wpuf_form_builder_settings_tabs_{type}`, `wpuf-form-builder-tabs-{type}`, `wpuf-form-builder-tab-contents-{type}`, `wpuf_post_form_tab`, `wpuf_profile_form_tab` | builder HookBridge | free `Admin_Form.php:158`, pro `Profile_Form.php:48` |
| `wsa_form_top_{section}`, `wsa_form_bottom_{section}` | React settings: fired on each settings load, output shown kses'd at the section top/bottom (task 1.16) | none in WPUF |
| filters `wpuf_settings_user_roles`, `wpuf_get_tax_rates` | React settings: profile roles table, pro tax block (task 1.16) | pro `Admin/Feature_Lock.php:38` |

Bridge rules: output of third-party listeners is captured and shown inside the matching React panel or next to the matching field; its inputs keep their names and are saved with the form in the same shape the old screen posted. Output that cannot work there (scripts, Vue markup) is replaced by a notice in that panel.

## Hooks and handles added or changed on the branch
| Hook / handle | Kind | Where | Note |
|---|---|---|---|
| `wpuf_forms_list_post_types` | filter | `Platform/REST/Controllers/FormListController::get_items` | post types the forms list REST lists (default `wpuf_forms`, `wpuf_profile`; others get 400) |
| `wpuf_form_builder_save_post_types`, `wpuf_form_builder_settings_meta_keys` | filter | `Platform/Stores/FormStore` and `Builder/FormSave` (builder save via `FormsController`) | builder save allowlists (security, task 1.19) |
| `wpuf_subscription_single_row_fields` | filter | `Platform/Stores/SubscriptionStore::update_single_row` (called by `Platform/REST/Controllers/SubscriptionController`) | fields a single-row edit may change |
| `wpuf_before_update_subscription_pack` | action | `Platform/Stores/SubscriptionStore` (REST save via `SubscriptionController`) | now fires **once**, after a successful insert, with the saved pack id (was twice: old id, new id) |
| `wpuf_update_subscription_pack` | action | `Platform/Stores/SubscriptionStore` (REST save via `SubscriptionController`) | fired once after `wpuf_after_update_subscription_pack_meta`, args `( $id, $pack_data )` with classic field names (meta keys without the leading `_`), as the classic pack screen fired it on develop |
| `wpuf-admin-subscriptions` | script handle | registered in `Assets.php`, enqueued by `Admin/Subscriptions/Pack_Screen` | no-file alias of `wpuf-subscriptions-screen`, enqueued with it, so data localized on the old handle still prints |
| `window.wpuf_single_objects` | JS global | builder (`wpuf-form-builder-screen`, `Admin/Forms/Admin_Form_Builder`) | printed again as on develop (also under `wpuf_form_builder`) |
| `wpuf_tec_form_fields` | filter | `Integrations/Events_Calendar/Templates/Event_Form_Template::get_form_fields()` (called by its constructor) | runs wherever the template registry is built (forms list, create from template, onboarding), not on the builder page |
| handles whose file is gone | script/style | `Assets::existing_src()` | registered with `src` false, so dependents and localizations still work |

## Enqueue hooks on the builder
`wpuf_form_builder_enqueue_style`, `wpuf_form_builder_enqueue_after_mixins`, `wpuf_form_builder_enqueue_after_components`, `wpuf_form_builder_enqueue_after_main_instance` keep firing once per builder page load, in this order, around the React builder scripts.

## Checks
- `php tests/contracts/static-hooks.php <free> <pro>`: call sites and argument counts; compare with develop.
- Runtime: install `tests/contracts/runtime-recorder.php` as a mu-plugin on a develop site and a branch site, crawl the admin screens, then `php tests/contracts/diff.php <develop-dir> <branch-dir>`: 0 hooks may be lost.
- Contract tests (PHPUnit, target): a fixture plugin listens to every frozen hook and asserts it fires with the same arguments and its result takes effect.
