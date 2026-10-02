import HelpTextIcon from './HelpTextIcon';
import TextEditor from '../TextEditor';

/**
 * Rich text (wysiwyg) field — wraps the WordPress TinyMCE editor so wysiwyg
 * settings (guest mail body, gateway instructions, …) get a real editor, like
 * the legacy screen, instead of a plain textarea.
 *
 * Email body fields carry their available dynamic merge tags in `desc` as HTML
 * (e.g. `You may use: <code>{username}</code> …`). The legacy screen showed that
 * below the editor, so we render the raw `desc` HTML there — not a stripped
 * tooltip — to keep the dynamic-value hints visible and in sync.
 */
export default function WysiwygField( { field, name, value, onChange } ) {
    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">{ field.label }</label>
                ) }
                { ! field.desc && field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className="wpuf-mt-1 wpuf-wysiwyg-wrap">
                <TextEditor
                    id={ name }
                    value={ value || field.default || '' }
                    onChange={ ( content ) => onChange( name, content ) }
                />
            </div>
            { field.desc && (
                <div
                    className="wpuf-mt-2 wpuf-text-sm wpuf-text-gray-500 wpuf-long-help"
                    // eslint-disable-next-line react/no-danger
                    dangerouslySetInnerHTML={ { __html: field.desc } }
                />
            ) }
        </>
    );
}
