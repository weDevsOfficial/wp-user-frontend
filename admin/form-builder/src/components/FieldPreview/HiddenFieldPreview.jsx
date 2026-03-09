import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function HiddenFieldPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            <input
                type="text"
                placeholder={ field.placeholder || '' }
                defaultValue={ field.default || '' }
                size={ field.size }
                className={ builderClassNames( 'text_hidden' ) }
                readOnly
            />
            <HelpText text={ field.help } />
        </div>
    );
}
