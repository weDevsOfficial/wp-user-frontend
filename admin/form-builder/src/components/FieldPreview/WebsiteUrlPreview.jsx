import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function WebsiteUrlPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            <input
                type="url"
                placeholder={ field.placeholder || '' }
                defaultValue={ field.default || '' }
                size={ field.size }
                className={ builderClassNames( 'url' ) }
                readOnly
            />
            <HelpText text={ field.help } />
        </div>
    );
}
