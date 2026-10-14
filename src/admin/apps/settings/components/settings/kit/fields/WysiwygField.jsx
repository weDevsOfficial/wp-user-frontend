import { RichEditor } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { HelpTextIcon } from '@wpuf/components';

// Legacy screen: WeDevs_Settings_API::callback_wysiwyg() wp_editor( teeny, no
// media buttons, 10 rows ), i.e. WordPress core's teeny toolbar.
const LEGACY_TEENY_TOOLBAR = 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,fullscreen';
const LEGACY_TINYMCE = { plugins: 'colorpicker,lists,fullscreen,image,wordpress,wpeditimage,wplink', height: 100 };

/**
 * Rich text (wysiwyg) setting on the shared RichEditor (TipTap with the WordPress editor as fallback, 2026-10-14; WpEditor since 4.6a): the WordPress
 * TinyMCE editor as on the legacy screen (guest mail body, gateway
 * instructions). Same editor as the legacy wp_editor() (teeny toolbar, no
 * Add Media, 10 rows) and the same stored shape: what that form posted, the
 * content with its wpautop paragraphs removed (`format="wp"`).
 *
 * Email body fields carry their merge tags in `desc` as HTML (`You may use:
 * <code>{username}</code>`); the legacy screen showed that below the editor,
 * so it prints there instead of a stripped tooltip.
 */
export default function WysiwygField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ { label: field.label } }>
                { ! field.desc && field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </SettingLabel>
            <div className="mt-1 wpuf-wysiwyg-wrap">
                <RichEditor
                    id={ name }
                    value={ value || field.default || '' }
                    onChange={ ( content ) => onChange( name, content ) }
                    toolbar={ LEGACY_TEENY_TOOLBAR }
                    tinymce={ LEGACY_TINYMCE }
                    mediaButtons={ false }
                    rows={ 10 }
                label={ field.label }
                    format="wp"
                />
            </div>
            { field.desc && (
                <div
                    className="mt-2 text-sm text-gray-500 wpuf-long-help"
                    // eslint-disable-next-line react/no-danger
                    dangerouslySetInnerHTML={ { __html: field.desc } }
                />
            ) }
        </>
    );
}
