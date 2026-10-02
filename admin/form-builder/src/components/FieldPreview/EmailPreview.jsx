import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function EmailPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            <input
                type="email"
                placeholder={ field.placeholder || '' }
                defaultValue={ field.default || '' }
                size={ field.size }
                className={ builderClassNames( 'text' ) }
                readOnly
            />
            <HelpText text={ field.help } />
        </div>
    );
}
