# Form Fields (`includes/Fields/`)

All form field types live here. Read this before adding or modifying a field.

## The Contract

Every field extends `Field_Contract` (abstract class, not an interface) in the `WeDevs\Wpuf\Fields` namespace.

Expected subclass responsibilities:
- Declare `$name`, `$input_type`, `$icon`, and `$template` (these identify the field in the form builder)
- Implement `render()` to output the field's frontend HTML
- Implement `get_options_settings()` to declare builder sidebar options
- Implement `get_field_props()` with sensible defaults
- Use `common_field()` and related static helpers for shared builder options (restrictions, CSS class, etc.)

## Naming

- File: `Form_Field_<Name>.php`
- Class: `Form_Field_<Name>`
- Keep names evergreen — no `New`, `Improved`, `V2`.

## Pro Fields

- `Form_Field_Pro.php` and `Form_Pro_Upgrade_Fields.php` are the Pro-only pathway. Don't reference Pro-specific fields from free code without `class_exists('WP_User_Frontend_Pro')` guards.

## Registration

Fields are instantiated and registered through `wpuf()->fields`. Check `includes/Fields_Manager.php` (or equivalent service) before adding a new field to confirm the registration pattern still matches.

## Rendering Path

Rendered HTML flows through `Frontend_Render_Form` / `Render_Form`. Breaking a field's output contract will break every form using that field — test with a real form before committing.

## When Editing

1. Read the full existing field file (many share behavior via traits like `Form_Field_Post_Trait`).
2. Run `composer phpcs` on the changed file.
3. Smoke-test in the form builder UI — field must appear, be draggable, save settings, and render on the frontend.
