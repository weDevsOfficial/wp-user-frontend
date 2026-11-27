import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function PostTitlePreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            <input
                type="text"
                placeholder={ field.placeholder || '' }
                value={ field.default ?? '' }
                size={ field.size }
                className={ builderClassNames( 'text' ) }
                readOnly
            />
            <HelpText text={ field.help } />
        </div>
    );
}
