/**
 * The rich textarea (post content, textarea with `rich` = yes or teeny):
 * WordPress' editor through `wp.editor`, with the settings the schema
 * computed the way the classic field does (`wpuf_textarea_editor_args`,
 * the toolbar from `text_editor_control`, teeny / full buttons through
 * `teeny_mce_buttons` / `mce_buttons`, the dark palette of layouts 2 and 5,
 * no quicktags, no media buttons). The classic markup: `div.wpuf-fields.
 * wpuf-rich-validation[data-type=rich]`, the "Insert Photo" button above
 * the editor when the field allows it. Falls back to a plain textarea when
 * wp.editor is not on the page.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Field from '../components/Field';
import { Counter, limitState } from './basic';
import { uploadFile } from '../api';

/** The editor's text (the classic limit counts TinyMCE's text, not the HTML). */
const stripTags = ( html ) => String( html || '' ).replace( /<[^>]*>/g, ' ' ).replace( /&nbsp;/g, ' ' ).replace( /\s+/g, ' ' ).trim();

const editorAvailable = () => 'function' === typeof window.wp?.editor?.initialize;

/** The classic "Insert Photo" button: uploads an image and inserts it at the cursor. */
function InsertImage( { formId, nonce, editorId } ) {
    const input = useRef( null );
    const [ busy, setBusy ] = useState( false );
    const [ message, setMessage ] = useState( '' );

    const insert = async ( files ) => {
        const file = files && files[ 0 ];

        if ( ! file ) {
            return;
        }

        setBusy( true );
        setMessage( '' );

        try {
            const result = await uploadFile( '/uploads', file, { form_id: formId, type: 'insert_image', wpuf_nonce: nonce } );
            const editor = window.tinymce && window.tinymce.get( editorId );
            const html = `<img src="${ String( result.url ).replace( /"/g, '&quot;' ) }" alt="${ String( result.title || '' ).replace( /"/g, '&quot;' ) }" />`;

            if ( editor ) {
                editor.insertContent( html );
                editor.fire( 'change' );
            }
        } catch ( e ) {
            setMessage( e.message );
        } finally {
            setBusy( false );

            if ( input.current ) {
                input.current.value = '';
            }
        }
    };

    return (
        <div id="wpuf-insert-image-container">
            <input ref={ input } type="file" accept=".jpg,.jpeg,.jfif,.gif,.png,.bmp,.webp" className="wpuf-file-input" tabIndex={ -1 } onChange={ ( event ) => insert( event.target.files ) } />
            <a
                className="wpuf-button wpuf-insert-image"
                id={ `wpuf-insert-image_${ formId }` }
                href="#insert-image"
                role="button"
                aria-busy={ busy ? 'true' : undefined }
                onClick={ ( event ) => {
                    event.preventDefault();
                    input.current && input.current.click();
                } }
            >
                <span className="wpuf-media-icon" />
                { __( 'Insert Photo', 'wp-user-frontend' ) }
            </a>
            { message && <div className="wpuf-error-msg" role="alert">{ message }</div> }
        </div>
    );
}

export default function RichTextField( { field, value, onChange, onBlur, formId, error, schema } ) {
    const id = `${ field.name }_${ formId }`;
    const latest = useRef( onChange );
    latest.current = onChange;

    const editor = field.editor || {};
    const available = editorAvailable();

    useEffect( () => {
        if ( ! available ) {
            return undefined;
        }

        const tinymce = { ...( editor.tinymce || {} ) };

        window.wp.editor.initialize( id, {
            tinymce: {
                wpautop: true,
                ...tinymce,
                setup: ( instance ) => {
                    instance.on( 'change keyup undo redo SetContent', () => latest.current( instance.getContent() ) );
                    instance.on( 'blur', () => onBlur && onBlur() );
                },
            },
            quicktags: !! editor.quicktags,
            mediaButtons: !! editor.media_buttons,
        } );

        return () => {
            window.wp.editor.remove( id );
        };
        // The editor mounts once per field; value changes come back through setup().
    }, [ id, available ] ); // eslint-disable-line react-hooks/exhaustive-deps

    const rich = 'yes' === field.rich || 'teeny' === field.rich;

    return (
        <Field
            field={ field }
            id={ id }
            error={ error }
            fieldsClass={ rich ? `wpuf-rich-validation wpuf_${ field.name }_${ formId }` : '' }
            data-type={ rich ? 'rich' : undefined }
            invalid={ limitState( field, stripTags( value ) ).violated }
        >
            { 'yes' === field.insert_image && available && <InsertImage formId={ formId } nonce={ schema?.nonces?.upload || '' } editorId={ id } /> }
            <textarea
                id={ id }
                className={ `textareafield wpuf_${ field.name }_${ formId }` }
                name={ field.name }
                rows={ editor.rows || field.rows || 5 }
                defaultValue={ value ?? '' }
                aria-required={ 'yes' === field.required || undefined }
                aria-invalid={ error ? 'true' : undefined }
                onChange={ available ? undefined : ( event ) => onChange( event.target.value ) }
                onBlur={ onBlur }
            />
            { 'post_content' === field.template && <Counter field={ field } value={ stripTags( value ) } /> }
        </Field>
    );
}
