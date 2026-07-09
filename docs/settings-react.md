# WPUF Settings — How it works & how to add a setting

The settings screen (`WPUF > Settings`) is a **React app** served entirely over
the REST API. The legacy `WeDevs_Settings_API` screen still exists as a
**fallback** (see _Legacy fallback_ below). Both read and write the **same
options and keys**, so they stay in sync.

> **Golden rule — storage parity.** Never rename, reshape, or drop an existing
> option key or value shape. Existing users' data must round-trip byte-identical
> on a no-op save. Add a setting by adding a schema field; do not invent new
> storage for something that already has a key.

---

## Architecture (one source of truth)

1. **Schema (PHP)** — `wpuf_settings_sections()` / `wpuf_settings_fields()` in
   `includes/functions/settings-options.php`, extended by Pro/modules through the
   `wpuf_settings_sections` / `wpuf_settings_fields` filters. This is the single
   source of truth for what settings exist.
2. **REST** — `includes/Api/Settings.php` (`wpuf/v1/settings`): `GET` returns the
   filtered schema + values; `POST` saves each section back to its option with
   the field's sanitize. Gated by `current_user_can( wpuf_admin_role() )`.
3. **React** — `src/js/settings.jsx` + `src/js/components/settings/*` render
   whatever the schema produces, dispatched by field `type` in `FieldRenderer.jsx`.

Because rendering is schema-driven, **a field registered via the filter appears
in both screens automatically** — no JS change needed for the common cases.

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

That's it — it renders in React **and** the legacy screen, and saves to
`wpuf_general['my_option']`.

### Supported `type`s (work in both screens)

`text` · `url` · `password` · `number` · `textarea` · `wysiwyg` · `select` ·
`checkbox` · `toggle` · `multicheck` · `radio` · `radio_inline` · `color` ·
`file` · `html` · `gateway_selector` · `hidden`

> **`multiselect` and `pic-radio` render in React but NOT in the legacy screen.**
> If you use them, also provide a `callback` (the legacy renders the callback;
> React routes by type/name). A WP_DEBUG guard
> (`wpuf_settings_legacy_compat_check`) logs a warning for any field the legacy
> screen can't render — watch your debug log.

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

## The 10% — special renderers

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

Each keeps the **legacy callback intact** (so the classic screen still works) and
React routes by field name. If you add another own-option feature, mirror the
existing pattern:

1. Inject the data on `wpuf_settings_rest_data` (adds to the `extra` payload).
2. Persist it on `wpuf_settings_saved` (reads `$extra`).
3. Render it with a component routed by field name in `FieldRenderer`.

See `includes/functions/settings-react.php` (profile roles, AI keys) and Pro
`includes/Tax.php` / `modules/email-templates/email-templates.php` for examples.

---

## Tab layout (IA)

`wpuf_settings_react_ia()` maps sections onto the redesigned tabs/sub-tabs. Tabs
auto-drop sections that aren't registered. Sections not claimed by a tab are
appended as their own tab, so nothing is ever hidden. Filterable via
`wpuf_settings_react_ia`.

---

## Legacy fallback

If the React screen ever misbehaves, switch to the classic screen:

- **Per-request (no DB):** add `?wpuf_settings_ui=legacy` to the settings URL.
- **Persistent:** the option `wpuf_settings_ui_mode` (`react` default | `legacy`),
  toggled by the "Classic view" / "Switch to new settings" links (nonce-protected).
- **Hard override:** `define( 'WPUF_LEGACY_SETTINGS', true );` or the
  `wpuf_use_legacy_settings` filter.

Both screens share storage, so switching never loses data.

---

## Before you ship a settings change

- A no-op save (open a tab, hit Save without editing) must leave every `wpuf_*`
  option byte-identical. Snapshot the options before/after and diff.
- Check the WP_DEBUG log for the legacy-compat warning.
- Run `composer phpcs` on changed PHP.
</content>
