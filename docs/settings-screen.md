# WPUF Settings: how it works & how to add a setting

The settings screen (`WPUF > Settings`) is a **React app** served entirely over
the REST API. It is the only settings screen: the classic `WeDevs_Settings_API`
screen and its switch were removed. The options and keys are the ones the
classic screen used (`Lib/WeDevs_Settings_API.php` stays for its storage helpers and older Pro, it is no longer rendered).

> **Golden rule: storage parity.** Never rename, reshape, or drop an existing
> option key or value shape. Existing users' data must round-trip byte-identical
> on a no-op save. Add a setting by adding a schema field; do not invent new
> storage for something that already has a key.

---

## Architecture (one source of truth)

1. **Schema (PHP)**: `wpuf_settings_sections()` / `wpuf_settings_fields()` in
   `includes/functions/settings-options.php`, extended by Pro/modules through the
   `wpuf_settings_sections` / `wpuf_settings_fields` filters. This is the single
   source of truth for what settings exist.
2. **REST**: `includes/Platform/REST/Controllers/SettingsController.php` (`wpuf/v1/settings`): `GET` returns the
   filtered schema + values; `POST` saves each section back to its option with
   the field's sanitize. Gated by `current_user_can( wpuf_admin_role() )`.
3. **React**: `src/admin/apps/settings/index.jsx` + `src/admin/apps/settings/components/settings/*` render
   whatever the schema produces, dispatched by field `type` in `FieldRenderer.jsx`.

Because rendering is schema-driven, **a field registered via the filter appears
on the screen automatically**; no JS change needed for the common cases.

---

## Adding a setting (the 90% case)

Add one entry to the section's field array via `wpuf_settings_fields`:

```php
$fields['wpuf_general'][] = [
    'name'    => 'my_option',          // the storage key inside the wpuf_general option
    'label'   => __( 'My Option', 'wp-user-frontend' ),
    'desc'    => __( 'Shown as a help tooltip / inline hint.', 'wp-user-frontend' ),
    'type'    => 'text',               // see Supported types
    'default' => '',
];
```

That's it: it renders on the React screen and saves to
`wpuf_general['my_option']`.

### Supported `type`s

`text` · `url` · `password` · `number` · `textarea` · `wysiwyg` · `select` ·
`checkbox` · `toggle` · `multicheck` · `radio` · `radio_inline` · `color` ·
`file` · `html` · `gateway_selector` · `hidden` · `multiselect` · `pic-radio`

A `callback` on a field is ignored by the React screen (it routes by type or name).

### Conditional fields

Show a field only when another field has a value:

```php
'depends_on'       => 'enable_turnstile',         // show when this is "on"/truthy
// or an exact match:
'depends_on'       => 'authentication_type',
'depends_on_value' => 'basic_auth',
// or multiple AND conditions:
'depends_on'       => [ 'authentication_type' => 'jwt_auth', 'jwt_key_type' => 'passphrase' ],
```

React evaluates these reactively (`SettingsSection.jsx` → `dependencyMet`).

---

## The 10%: special renderers

A few fields need a custom React component because their storage is nested or
lives in their own option. These are routed in `FieldRenderer.jsx` /
`SettingsSection.jsx`:

| Field / pattern | React component | Storage |
|---|---|---|
| `ai_provider` | provider cards | `wpuf_ai` |
| `wpuf_login_form_layout` | image picker (`PicRadioField`) | `wpuf_profile` (flat) |
| `*_role_templates` | role-template repeater | own option `wpuf_role_based_email_templates` |
| `*_default_roles` | role multiselect | `wpuf_mails` |
| `profile_form_roles` | role → form table | nested `wpuf_profile['roles']` |
| `wpuf_base_country_state`, `wpuf_tax_rates` | tax UI | own options |
| `PROVIDER_SECTIONS` (SMS / Social) | provider cards | per-provider keys |

Each still carries its old `callback` (unused by React, kept for older Pro) and
React routes by field name. If you add another own-option feature, mirror the
existing pattern:

1. Inject the data on `wpuf_settings_rest_data` (adds to the `extra` payload).
2. Persist it on `wpuf_settings_saved` (reads `$extra`).
3. Render it with a component routed by field name in `FieldRenderer`.

See `includes/functions/settings-screen.php` (profile roles, AI keys) and Pro
`includes/Tax.php` / `modules/email-templates/email-templates.php` for examples.

---

## Tab layout (IA)

`wpuf_settings_ia()` maps sections onto the redesigned tabs/sub-tabs. Tabs
auto-drop sections that aren't registered. Sections not claimed by a tab are
appended as their own tab, so nothing is ever hidden. Filterable via
`wpuf_settings_ia`.

---

## How the React screen saves (the classic screen's stored shapes)

- The screen posts **only the fields the user changed** (`stores/settings/diff.js`)
  and only the `extra` keys that changed. Untouched values and own-option settings
  (tax, AI keys, role templates, profile roles) are never rewritten.
- Stored shapes as the legacy form posted them: multicheck `{ key: key }` or `''` when
  none is checked; an empty `wpuf_settings_multiselect` leaves the key out (readers fall
  back to the default); the JSON body is not unslashed.
- Fallback sanitize when a field has no `sanitize_callback`: text `sanitize_text_field`,
  textarea `wp_kses_post` (messages keep their HTML; `custom_css` uses
  `wp_strip_all_tags` so `>` survives), wysiwyg/html `wp_kses_post`, url `esc_url_raw`.
- Secrets (`wpuf_settings_password_preview` fields, AI keys) leave the server masked; the
  mask coming back keeps the stored value.
- A `color` field may carry `preview` (map) + `preview_by` (another field name): the
  picker shows `preview[ value of preview_by ]` when nothing is stored. Display only.
  Pro login colors use it to follow the chosen layout; their `std` stays `''`.

- `desc` reaches React filtered by `wp_kses_post`; plain text shows as a tooltip, markup
  (links) prints under the field. `html` rows show label + desc.
- The first save of a section stores every missing field default (legacy `std`), except
  html/hidden, secrets, multiselects, callback-rendered and Pro-preview fields.

## Legacy hooks on the React screen

- `wsa_form_top_{section}` and `wsa_form_bottom_{section}` fire for every section on each
  settings load (REST `GET /wpuf/v1/settings`), with the section array as before. Their
  output is shown at the top and bottom of the section after `wp_kses_post`, so form inputs
  printed there are not posted by the React save.
- `wpuf_settings_user_roles` (profile roles table) and `wpuf_get_tax_rates` (pro tax block)
  apply as on the legacy screen.

---

## Before you ship a settings change

- A no-op save (open a tab, hit Save without editing) must leave every `wpuf_*`
  option byte-identical. Snapshot the options before/after and diff.
- Run `composer phpcs` on changed PHP.
</content>
