import SettingHelpText from './SettingHelpText';

/**
 * Radio button input for field settings.
 * Replaces Vue field-radio component.
 */
export default function RadioInput( { optionField, value, onChange, builderClassNames } ) {
    const options = optionField.options || {};
    const optionEntries = Object.entries( options );
    const isInline = !! optionField.inline;

    return (
        <div className="panel-field-opt panel-field-opt-radio">
            <div className="wpuf-flex">
                <label className="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium">
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>

            { isInline ? (
                <div className="wpuf-flex">
                    { optionEntries.map( ( [ key, label ], index ) => (
                        <div key={ key } className="wpuf-items-center">
                            <label
                                className={ `wpuf-block text-sm/6 wpuf-font-medium wpuf-text-gray-900 !wpuf-mb-0${ index !== 0 ? ' wpuf-ml-8' : '' }` }
                            >
                                <input
                                    type="radio"
                                    value={ key }
                                    checked={ value === key }
                                    onChange={ () => onChange( key ) }
                                    className={ builderClassNames( 'radio' ) }
                                />
                                { label }
                            </label>
                        </div>
                    ) ) }
                </div>
            ) : (
                optionEntries.map( ( [ key, label ], index ) => (
                    <div
                        key={ key }
                        className={ `wpuf-flex wpuf-items-center${ index < optionEntries.length - 1 ? ' wpuf-mb-3' : '' }` }
                    >
                        <label className="!wpuf-mb-0">
                            <input
                                type="radio"
                                value={ key }
                                checked={ value === key }
                                onChange={ () => onChange( key ) }
                                className={ builderClassNames( 'radio' ) }
                            />
                            { label }
                        </label>
                    </div>
                ) )
            ) }
        </div>
    );
}
