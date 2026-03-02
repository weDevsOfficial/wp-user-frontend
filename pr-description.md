## Summary

Redesigns the multistep form progress indicators with a modern, clean look. The **Step by Step** type now displays numbered circles connected by lines, replacing the old arrow-shaped tab design. The **Progressbar** type now shows a "Step X of Y" label with a percentage and a smooth horizontal fill bar, replacing the jQuery UI progressbar dependency. Also includes minor UI polish for the account page, dashboard, and subscription templates.

## Breaking Changes

- The jQuery UI progressbar dependency (`jquery-ui-progressbar`) has been removed. Any custom CSS targeting `.ui-progressbar`, `.ui-widget-header`, or `.wpuf-progress-percentage` inside `.wpuf-multistep-progressbar` will no longer apply.
- The step wizard markup changed from `<ul><li>` elements to `<div>` based structure. Custom CSS targeting `ul.wpuf-step-wizard li` will no longer match.

## Technical Notes

- Multistep form initialization and color customization now fully handled by the Pro plugin via the `wpuf_form_fields_top` action hook (`Fields_Manager::step_start_form_top()`)
- Removed duplicate multistep rendering logic from both `Render_Form` classes in the free plugin
- New CSS classes for the progressbar type: `.wpuf-progressbar-header`, `.wpuf-progressbar-track`, `.wpuf-progressbar-fill`
- New CSS classes for the step-by-step type: `.wpuf-step-wizard`, `.wpuf-step-item`, `.wpuf-step-circle`, `.wpuf-step-label`, `.wpuf-step-line`
- Form color settings (`ms_bgcolor`, `ms_active_bgcolor`, `ms_ac_txt_color`) continue to work — they now target the new class selectors via inline styles
