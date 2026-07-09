import { __ } from '@wordpress/i18n';
import { useFieldClasses } from '../../hooks/useFieldClasses';
import HelpText from './HelpText';

export default function ImageUploadPreview( { field } ) {
    const { builderClassNames } = useFieldClasses( field );

    return (
        <div className="wpuf-fields">
            <div>
                <div className="wpuf-attachment-upload-filelist" data-type="file" data-required="yes">
                    <a className={ `wpuf-inline-flex wpuf-items-center wpuf-gap-x-1.5 ${ builderClassNames( 'upload_btn' ) }` } href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" className="wpuf-size-5">
                            <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
                        </svg>
                        { field.button_label || __( 'Select Image', 'wp-user-frontend' ) }
                    </a>
                </div>
            </div>
            <HelpText text={ field.help } />
        </div>
    );
}
