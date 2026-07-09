import { __ } from '@wordpress/i18n';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';
import TextEditorPreview from './TextEditorPreview';

export default function PostContentPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            { field.insert_image === 'yes' && (
                <div className="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
                    <a className={ `wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 ${ builderClassNames( 'upload_btn' ) }` } href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" className="wpuf-size-5">
                            <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                        </svg>
                        { __( 'Insert Photo', 'wp-user-frontend' ) }
                    </a>
                </div>
            ) }

            { field.insert_image === 'yes' && <br /> }

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
