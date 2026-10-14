import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { RichEditor } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { HelpTextIcon } from '@wpuf/components';

/**
 * A mail body's help as PHP writes it, "<text><br><strong>You may use:</strong>
 * <code>{tag}</code> ..." (tags sometimes split over several lines with <br>):
 * the text and the tags, or null when the help has no such list (it is then
 * printed as it is).
 *
 * @param {string} desc Help HTML.
 *
 * @return {{ intro: string, tags: string[] }|null} Parts.
 */
export function mergeTags( desc ) {
    const match = /^([\s\S]*?)(?:<br\s*\/?>\s*)?<strong>\s*You may use:?\s*<\/strong>([\s\S]*)$/i.exec( String( desc || '' ) );

    if ( ! match ) {
        return null;
    }

    const tags = Array.from( match[ 2 ].matchAll( /<code>\s*([^<]+?)\s*<\/code>/gi ), ( found ) => found[ 1 ] );
    const rest = match[ 2 ].replace( /<code>[^<]*<\/code>|<br\s*\/?>|\s/gi, '' );

    // Anything besides the tags (a sentence, a link) keeps the original help.
    return tags.length && ! rest ? { intro: match[ 1 ].trim(), tags } : null;
}

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
    const editor = useRef( null );
    const parts = mergeTags( field.desc );

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
                    apiRef={ editor }
                    format="wp"
                />
            </div>
            { parts && (
                <div className="mt-2 text-sm text-gray-500 wpuf-long-help">
                    { parts.intro && (
                        // eslint-disable-next-line react/no-danger
                        <div dangerouslySetInnerHTML={ { __html: parts.intro } } />
                    ) }
                    { /* One line of merge tags; a click puts the tag at the editor's cursor. */ }
                    <div className="mt-1.5 flex flex-wrap items-center gap-1.5">
                        <span className="font-medium text-gray-700">{ __( 'You may use:', 'wp-user-frontend' ) }</span>
                        { parts.tags.map( ( tag ) => (
                            <button
                                key={ tag }
                                type="button"
                                className="inline-flex h-6 items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 font-mono text-xs text-emerald-800 hover:border-emerald-400 hover:bg-emerald-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                                title={ __( 'Insert at the cursor', 'wp-user-frontend' ) }
                                onMouseDown={ ( event ) => event.preventDefault() }
                                onClick={ () => editor.current && editor.current.insert( tag ) }
                            >
                                { tag }
                            </button>
                        ) ) }
                    </div>
                </div>
            ) }
            { field.desc && ! parts && (
                <div
                    className="mt-2 text-sm text-gray-500 wpuf-long-help"
                    // eslint-disable-next-line react/no-danger
                    dangerouslySetInnerHTML={ { __html: field.desc } }
                />
            ) }
        </>
    );
}
