/**
 * Slots of the React form builder (backward-compatibility.md "Vue-only hooks",
 * task 4.4g): Pro and third-party code fill them with
 * `registerPlugin( name, { scope: 'wpuf-form-builder', render } )` and
 * `<Fill name="...">` from @wordpress/components. They replace the retired Vue
 * template hooks (docs/hooks/migration-vue-to-react.md).
 */
import { Slot } from '@wordpress/components';

export const BUILDER_SLOTS = Object.freeze( {
    // fillProps { field } — under a field's options (was wpuf_builder_field_options).
    FIELD_OPTIONS_AFTER: 'wpuf-form-builder-field-options-after',
    // fillProps { field, options, setOptions } — option editor header / after the
    // rows (was wpuf_field_option_data_actions / _after).
    OPTION_DATA_ACTIONS: 'wpuf-form-builder-option-data-actions',
    OPTION_DATA_AFTER: 'wpuf-form-builder-option-data-after',
    // fillProps { fields } — after the stage rows / at the bottom of the stage
    // (was wpuf_form_builder_template_builder_stage_submit_area / _bottom_area).
    CANVAS_SUBMIT_AREA: 'wpuf-form-builder-canvas-submit-area',
    CANVAS_BOTTOM: 'wpuf-form-builder-canvas-bottom',
} );

/**
 * Slot name of a settings tab's panel: `wpuf-form-builder-settings-<tab>`
 * (fillProps { tab, settings, updateSetting }).
 *
 * @param {string} tab Settings tab key.
 * @return {string} Slot name.
 */
export const settingsSlotName = ( tab ) => `wpuf-form-builder-settings-${ tab }`;

/**
 * A named builder slot (renders nothing until something fills it).
 *
 * @param {Object} props
 * @param {string} props.name      Slot name.
 * @param {Object} [props.fillProps] Props for the fills.
 */
export function BuilderSlot( { name, fillProps } ) {
    return <Slot name={ name } fillProps={ fillProps } />;
}
