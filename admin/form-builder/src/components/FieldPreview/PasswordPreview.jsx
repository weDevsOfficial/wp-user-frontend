import { __ } from '@wordpress/i18n';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function PasswordPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );
    const repeatPass = field.repeat_pass === 'yes';

    return (
        <div className="wpuf-fields">
            <input
                type="password"
                placeholder={ field.placeholder || '' }
                defaultValue=""
                size={ field.size }
                className={ builderClassNames( 'textfield' ) }
                readOnly
            />
            <HelpText text={ field.help } />

            { repeatPass && (
                <div className="wpuf-mt-6">
                    <label className="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900 wpuf-mb-2">
                        { field.re_pass_label || __( 'Confirm Password', 'wp-user-frontend' ) }
                        { field.required === 'yes' && <span className="required"> *</span> }
                    </label>
                    <input
                        type="password"
                        placeholder={ field.placeholder || '' }
                        defaultValue=""
                        size={ field.size }
                        className={ builderClassNames( 'textfield' ) }
                        readOnly
                    />
                </div>
            ) }

            { field.pass_strength === 'yes' && (
                <div className="wpuf-mt-4">
                    <div
                        className="wpuf-p-2 wpuf-text-sm wpuf-text-gray-500 wpuf-border wpuf-border-gray-300 wpuf-rounded"
                        style={ { display: 'block' } }
                    >
                        { __( 'Strength indicator', 'wp-user-frontend' ) }
                    </div>
                </div>
            ) }
        </div>
    );
}
