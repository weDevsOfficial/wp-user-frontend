import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function TextFieldPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            <input
                type="text"
                placeholder={ field.placeholder || '' }
                defaultValue={ field.default || '' }
                size={ field.size }
                className={ builderClassNames( 'textfield' ) }
                readOnly
            />
            <HelpText text={ field.help } />
        </div>
    );
}
