import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';
import { isRichEditor } from '../../utils/fieldUtils';
import TextEditorPreview from './TextEditorPreview';

export default function TextareaPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            { ! isRichEditor( field.rich ) ? (
                <textarea
                    rows={ field.rows }
                    cols={ field.cols }
                    placeholder={ field.placeholder || '' }
                    className={ builderClassNames( 'textareafield' ) }
                    value={ field.default ?? '' }
                    readOnly
                />
            ) : (
                <TextEditorPreview rich={ field.rich } defaultText={ field.default ?? '' } />
            ) }
            <HelpText text={ field.help } />
        </div>
    );
}
