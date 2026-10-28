import { useCallback } from '@wordpress/element';
import { Checkbox } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Checkbox input for field settings (Vue field-checkbox), on the shared
 * Checkbox wrapper (4.4c). Stored shapes are develop's:
 * - single-option boxes (is_single_opt) store the option key or '';
 * - one box whose stored value is not a list is a boolean box (`read_only`),
 *   stored as true / false (the Vue v-model); 'true' / 'yes' read as on;
 * - several boxes store the list of ticked keys.
 * The store keeps `read_only` and `required` mutually exclusive.
 */
export default function CheckboxInput( { optionField, field, value, onChange } ) {
    const isSingleOpt = !! optionField.is_single_opt;
    const options = optionField.options || {};
    const optionKeys = Object.keys( options );
    const idBase = `wpuf-${ optionField.name }-${ field ? field.id : 'field' }`;

    const isBooleanBox = optionKeys.length === 1 && ! Array.isArray( value );
    const isBoxChecked = value === true || value === 'true' || value === 'yes';

    const handleMultiChange = useCallback( ( key, checked ) => {
        if ( isBooleanBox ) {
            onChange( checked );
            return;
        }

        const current = Array.isArray( value ) ? [ ...value ] : [];

        if ( checked && ! current.includes( key ) ) {
            current.push( key );
        } else if ( ! checked ) {
            const idx = current.indexOf( key );

            if ( idx > -1 ) {
                current.splice( idx, 1 );
            }
        }

        onChange( current );
    }, [ value, onChange, isBooleanBox ] );

    const title = optionField.title && (
        <div className="flex">
            <label className="wpuf-option-field-title text-gray-700 font-medium">
                { optionField.title }
                <SettingHelpText text={ optionField.help_text } />
            </label>
        </div>
    );

    // Single option checkbox (toggle): the title (when set) above, the option's
    // own text next to the box, like the Vue template.
    if ( isSingleOpt ) {
        const key = optionKeys[ 0 ];

        return (
            <div className="panel-field-opt panel-field-opt-checkbox mb-6">
                { title }
                <ul>
                    <li className="flex items-center gap-2">
                        <Checkbox
                            id={ `${ idBase }-${ key }` }
                            data-value={ key }
                            value={ value === key }
                            onChange={ ( on ) => onChange( on ? key : '' ) }
                            label={ options[ key ] }
                        />
                        { ! optionField.title && <SettingHelpText text={ optionField.help_text } /> }
                    </li>
                </ul>
            </div>
        );
    }

    return (
        <div className="panel-field-opt panel-field-opt-checkbox mb-6">
            { title }
            <ul className={ optionField.inline ? 'list-inline flex flex-wrap gap-x-6' : 'grid gap-2' }>
                { optionKeys.map( ( key ) => (
                    <li key={ key }>
                        <Checkbox
                            id={ `${ idBase }-${ key }` }
                            data-value={ key }
                            value={ isBooleanBox ? isBoxChecked : Array.isArray( value ) && value.includes( key ) }
                            onChange={ ( on ) => handleMultiChange( key, on ) }
                            label={ options[ key ] }
                        />
                    </li>
                ) ) }
            </ul>
        </div>
    );
}
