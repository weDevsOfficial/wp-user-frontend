import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';
import TextEditorPreview from './TextEditorPreview';

export default function PostExcerptPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            { field.rich === 'no' ? (
                <textarea
                    rows={ field.rows || 5 }
                    cols={ field.cols || 25 }
                    placeholder={ field.placeholder || '' }
                    className={ builderClassNames( 'textareafield' ) }
                    defaultValue={ field.default || '' }
                    readOnly
                />
            ) : (
                <TextEditorPreview rich={ field.rich } defaultText={ field.default || '' } />
            ) }
            <HelpText text={ field.help } />
        </div>
    );
}
