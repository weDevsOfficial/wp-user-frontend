/**
 * Toggle switch. The caller gives the stored shape: `checkedValue`
 * (default 'on') and `uncheckedValue` (default 'off'; pass undefined when the
 * key is absent while off).
 */
import { Switch, cn } from '@wedevs/plugin-ui';

import { isChecked } from './values';

/**
 * @param {Object}   props
 * @param {*}        [props.value]          Stored value.
 * @param {Function} [props.onChange]       ( value ) => void, with checkedValue/uncheckedValue.
 * @param {*}        [props.checkedValue]   Stored value when on.
 * @param {*}        [props.uncheckedValue] Stored value when off.
 * @param {*}        [props.label]          Label next to the switch.
 */
export default function Toggle( allProps ) {
    const { value, onChange, checkedValue = 'on', uncheckedValue: passedOff, label, id, disabled, className, ...props } = allProps;
    // An explicit `uncheckedValue={ undefined }` means the key is absent while off.
    const uncheckedValue = Object.prototype.hasOwnProperty.call( allProps, 'uncheckedValue' ) ? passedOff : 'off';

    const control = (
        <Switch
            id={ id }
            checked={ isChecked( value, checkedValue ) }
            onCheckedChange={ ( on ) => onChange?.( on ? checkedValue : uncheckedValue ) }
            disabled={ disabled }
            className={ cn( 'data-checked:bg-primary data-unchecked:bg-gray-200 cursor-pointer disabled:cursor-not-allowed', className ) }
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
