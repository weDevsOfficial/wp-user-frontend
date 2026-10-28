DESCRIPTION: SlotFill slots on WPUF admin React screens: names, fill props, and how pro/modules/third parties add UI.
Read before injecting UI into the builder, forms list, subscriptions or settings React screens.

# Slots (target)

Slots are added with the admin platform (branch `feature/react-admin-revamp`). Until a slot exists in `src/admin/shared/filters.ts` (`WPUF_SLOTS`), treat it as planned.

## Usage
```js
import { Fill } from '@wordpress/components';
import { registerPlugin } from '@wordpress/plugins';

registerPlugin( 'my-plugin-wpuf-bulk', {
    scope: 'wpuf-form-builder',
    render: () => (
        <Fill name="wpuf-form-builder-option-data-actions">
            { ( { field, form } ) => <MyButton field={ field } /> }
        </Fill>
    ),
} );
```
Each screen renders `<PluginArea scope="wpuf-<screen>" />` once.

## Builder

| Slot | Fill props | Replaces (retired Vue hook) |
|---|---|---|
| `wpuf-form-builder-field-options-after` | `{ field, form }` | `wpuf_builder_field_options` |
| `wpuf-form-builder-option-data-actions` | `{ field, form }` | `wpuf_field_option_data_actions` |
| `wpuf-form-builder-option-data-after` | `{ field, form }` | `wpuf_field_option_data_after` |
| `wpuf-form-builder-canvas-submit-area` | `{ form }` | `wpuf_form_builder_template_builder_stage_submit_area` |
| `wpuf-form-builder-canvas-bottom` | `{ form }` | `wpuf_form_builder_template_builder_stage_bottom_area` |
| `wpuf-form-builder-settings-<tab>` | `{ form, settings, update }` | settings tab content hooks |

Plain-PHP hook output is rendered into the same places by the bridge (`docs/hooks/php.md`); a slot is the native React way.

## Rules
- Slot names are frozen once released, like hook names.
- Fill content uses WPUF `shared/ui` components so it matches buttons, spacing and states.
- No slot passes data the current user may not see.
