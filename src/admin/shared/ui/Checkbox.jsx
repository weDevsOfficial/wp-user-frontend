/**
 * Checkbox. The caller gives the stored shape: `checkedValue` (default true)
 * and `uncheckedValue` (default false; `''`, `'off'` or undefined elsewhere).
 */
import { Checkbox as PuiCheckbox, cn } from '@wedevs/plugin-ui';

import { isChecked } from './values';

/**
 * @param {Object}   props
 * @param {*}        [props.value]          Stored value.
 * @param {Function} [props.onChange]       ( value ) => void
 * @param {*}        [props.checkedValue]   Stored value when ticked.
 * @param {*}        [props.uncheckedValue] Stored value when not ticked.
 * @param {*}        [props.label]          Label.
 */
export default function Checkbox( allProps ) {
    const { value, onChange, checkedValue = true, uncheckedValue: passedOff, label, id, disabled, className, ...props } = allProps;
    // An explicit `uncheckedValue={ undefined }` means the key is absent while off.
    const uncheckedValue = Object.prototype.hasOwnProperty.call( allProps, 'uncheckedValue' ) ? passedOff : false;

    const control = (
        <PuiCheckbox
            id={ id }
            checked={ isChecked( value, checkedValue ) }
            onCheckedChange={ ( on ) => onChange?.( on ? checkedValue : uncheckedValue ) }
            disabled={ disabled }
            className={ cn( 'border-gray-300 data-checked:bg-primary data-checked:border-primary data-checked:text-white cursor-pointer disabled:cursor-not-allowed', className ) }
            { ...props }
        />
    );

    if ( ! label ) {
        return control;
    }

    return (
        <label data-wpuf-ui="" htmlFor={ id } className="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
            { control }
            <span>{ label }</span>
        </label>
    );
}
