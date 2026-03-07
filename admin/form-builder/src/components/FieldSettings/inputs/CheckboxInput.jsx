import { useCallback } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { STORE_NAME } from '../../../store';
import SettingHelpText from './SettingHelpText';

/**
 * Checkbox input for field settings.
 * Replaces Vue field-checkbox component.
 *
 * Handles single-option checkboxes (is_single_opt) like required/read_only
 * where value toggles between the first option key and empty string.
 *
 * Also handles mutual exclusivity between required and read_only fields.
 */
export default function CheckboxInput( { optionField, field, value, onChange, builderClassNames } ) {
    const { updateField } = useDispatch( STORE_NAME );

    const isSingleOpt = !! optionField.is_single_opt;
    const options = optionField.options || {};
    const optionKeys = Object.keys( options );

    // For single-option checkboxes, compute checked state
    const isChecked = isSingleOpt
        ? value === optionKeys[ 0 ]
        : false;

    const handleSingleOptChange = useCallback( ( e ) => {
        const newValue = e.target.checked ? optionKeys[ 0 ] : '';
        onChange( newValue );

        // Mutual exclusivity: required <-> read_only
        if ( optionField.name === 'required' && e.target.checked ) {
            updateField( field.id, 'read_only', 'no' );
        } else if ( optionField.name === 'read_only' && e.target.checked ) {
            updateField( field.id, 'required', 'no' );
        }
    }, [ optionKeys, onChange, optionField.name, field.id, updateField ] );

    const handleMultiChange = useCallback( ( key, checked ) => {
        const current = Array.isArray( value ) ? [ ...value ] : [];

        if ( checked ) {
            if ( ! current.includes( key ) ) {
                current.push( key );
            }
        } else {
            const idx = current.indexOf( key );
            if ( idx > -1 ) {
                current.splice( idx, 1 );
            }
        }

        onChange( current );
    }, [ value, onChange ] );

    // Single option checkbox (toggle)
    if ( isSingleOpt ) {
        return (
            <div className="panel-field-opt panel-field-opt-checkbox wpuf-mb-6">
                <div className="wpuf-flex">
                    <label className="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0">
                        <input
                            type="checkbox"
                            className={ `${ builderClassNames( 'checkbox' ) } !wpuf-mr-2` }
                            checked={ isChecked }
                            onChange={ handleSingleOptChange }
                        />
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                </div>
            </div>
        );
    }

    // Multi-option checkboxes
    return (
        <div className="panel-field-opt panel-field-opt-checkbox wpuf-mb-6">
            <div className="wpuf-flex">
                { optionField.title && (
                    <label className="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>
            <ul className={ optionField.inline ? 'list-inline' : '' }>
                { optionKeys.map( ( key ) => (
                    <li key={ key }>
                        <label className="wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0">
                            <input
                                type="checkbox"
                                className={ `${ builderClassNames( 'checkbox' ) } !wpuf-mr-2` }
                                value={ key }
                                checked={ Array.isArray( value ) && value.includes( key ) }
                                onChange={ ( e ) => handleMultiChange( key, e.target.checked ) }
                            />
                            { options[ key ] }
                        </label>
                    </li>
                ) ) }
            </ul>
        </div>
    );
}
