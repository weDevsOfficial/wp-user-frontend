import { __ } from '@wordpress/i18n';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';
import { isRichEditor } from '../../utils/fieldUtils';
import TextEditorPreview from './TextEditorPreview';

export default function PostContentPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            { field.insert_image === 'yes' && (
                <div className="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
                    <a className={ `inline-flex items-center gap-x-1.5 ${ builderClassNames( 'upload_btn' ) }` } href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" className="size-4">
                            <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                        </svg>
                        { __( 'Insert Photo', 'wp-user-frontend' ) }
                    </a>
                </div>
            ) }

            { field.insert_image === 'yes' && <br /> }

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

            <HelpText text={ field.help } className="wpuf-help" />
        </div>
    );
}
