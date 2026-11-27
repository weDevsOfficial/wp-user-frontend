DESCRIPTION: WPUF JavaScript hooks (wp.hooks filters and actions) on the admin React screens: names, where they fire, naming rules.
Read before adding a JS extension point or when pro/modules need to change an admin React screen.

# JS hooks

All WPUF JS hooks use the global `wp.hooks` (`import { applyFilters, doAction } from '@wordpress/hooks'`, externalised to `wp-hooks`). No private `createHooks()` instance.

## Naming
- Existing names are frozen: `wpuf.formBuilder.*`, `wpuf.formsList.*`, `wpuf.subscription.*`, `wpuf.userPosts.*`.
- New names: `wpuf.<area>.<thing>` in camelCase. Shared wrappers derive names from their namespace: `wpuf.<ns>.dataviews.fields|actions|data|view`, `wpuf.<ns>.filters`, `wpuf.<ns>.tabs`; commands as actions `wpuf.<screen>.refresh`.
- Every name is a constant in `src/admin/shared/filters.js`: `HOOKS` (filters and actions in one map), `SLOTS` (SlotFill names), `HOOK_NAMES`, plus `applyWpufFilters()` / `doWpufAction()`, which warn in development when a name is not in `HOOKS`. Runtime copy: `window.wpuf.filters` (`admin-runtime` entry). `src/admin/shared/filters.test.js` (`pnpm run test:shared`) fails when source code uses a name that is not listed. Add a new name there first; no string literals in screens.
- Pro registers at module scope in a script that depends on free's handle (not in `domReady`); free reads filters at render time.

## Existing hooks

| Hook | Kind | Fired by | Pro listener |
|---|---|---|---|
| `wpuf.formBuilder.canvasRender` | filter | free builder | |
| `wpuf.formBuilder.fieldDependencies` | filter | free builder | yes |
| `wpuf.formBuilder.fieldSettings` | filter | free builder | |
| `wpuf.formBuilder.integrations` | filter | free builder | |
| `wpuf.formBuilder.optionDataBulkAdd` | filter | free builder | yes |
| `wpuf.formBuilder.panelSections` | filter | free builder | yes |
| `wpuf.formBuilder.settingsFields` | filter | free builder | |
| `wpuf.formBuilder.settingsItems` | filter | free builder | |
| `wpuf.formBuilder.settingsTabs` | filter | free builder | |
| `wpuf.formBuilder.fieldCssClasses` | filter | free builder stage, `( classes, field )` on top-level fields after the hidden classes are dropped | yes |
| `wpuf.formBuilder.rootInit` | action | free builder | yes |
| `wpuf.formBuilder.beforeSave`, `wpuf.formBuilder.afterSave` | action | free builder | |
| `wpuf.formsList.getShortcode`, `.pageTitle`, `.tableColumns` | filter | free forms list | |
| `wpuf.formsList.shortcodeRender` | filter | free forms table, `( defaultRender, form, formType, ShortcodeCopy, copiedKey, onCopy )`; `ShortcodeCopy` takes `compact` | yes |
| `wpuf.formsList.aiFormBuilderAvailable` | filter | free forms list, `( true, formType )`; false hides the AI entry (header and empty state) | yes |
| `wpuf.formsList.init` | action | free forms list | yes |
| `wpuf.subscription.blankItem`, `.boxMenuItems`, `.itemBeforeSave`, `.validateFields` | filter | free subscriptions | |
| `wpuf.subscription.init`, `.formMounted`, `.formUnmounted`, `.beforeSave`, `.afterSave`, `.itemSaved`, `.itemsLoaded` | action | free subscriptions | |
| `wpuf.userPosts.publishDateLabel`, `.titleLabel` | filter | frontend (develop) | |

A filter that pro listens to but free never applies is a bug: free must apply it (B21, fixed in task 1.6).

## Registries on `window.wpuf` (kept)
`registerFieldPreview`, `registerFieldSettingInput`, `registerFieldValidator`, `storeName` and the other flat members stay. Target runtime adds `{ ui, components, utilities, hooks, stores, api, version }`.

## Retired Vue builder hooks
See `docs/hooks/migration-vue-to-react.md`. `window.wpuf_mixins` stays defined as an empty map and `wpuf_form_builder.event_hub` forwards `$on/$emit/$off` to actions `wpuf.formBuilder.event.<name>`, both with a one-time `@wordpress/deprecated` warning.

## Form builder slots (task 4.4g)
Fill with `wp.plugins.registerPlugin( 'my-plugin', { scope: 'wpuf-form-builder', render } )` and `<Fill name="...">` (`wp.components`). Names are also on `window.wpuf.builderSlots`.

| Slot | Where | fillProps |
|---|---|---|
| `wpuf-form-builder-field-options-after` | under the field options panel | `{ field }` |
| `wpuf-form-builder-option-data-actions` | option editor header, before Bulk Add | `{ field, options, setOptions }` |
| `wpuf-form-builder-option-data-after` | after the option rows | `{ field, options, setOptions }` |
| `wpuf-form-builder-canvas-submit-area` | after the stage rows | `{ fields }` |
| `wpuf-form-builder-canvas-bottom` | bottom of the stage | `{ fields }` |
| `wpuf-form-builder-settings-<tab>` | under a settings tab's panel (`general`, `payment_settings`, ...) | `{ tab, settings, updateSetting }` |

Retired Vue globals: `wpuf_form_builder.event_hub` (`$on` / `$emit` / `$off`) forwards to actions `wpuf.formBuilder.event.<name>`; `window.wpuf_mixins` stays defined. Both warn once.
