/**
 * The rich textarea (post content, textarea with `rich` = yes or teeny).
 *
 * Two editors, chosen per field by the schema (`editor.engine`, see
 * Form_Schema::editor_engine()):
 * - `tiptap`: the modern editor (editor/TipTapEditor.jsx, its own chunk),
 *   with the toolbar the classic filters computed and the content in the
 *   legacy shape (editor/html.js). When stored content does not survive a
 *   round trip through it, the field switches to TinyMCE (or, when
 *   TinyMCE is not on the page, to a plain HTML textarea) so nothing is
 *   lost.
 * - `tinymce`: WordPress' editor through `wp.editor`, as the classic field
 *   (`wpuf_textarea_editor_args`, teeny / full buttons, the dark palette of
 *   layouts 2 and 5, no quicktags, no media buttons).
 *
 * The classic markup stays: `div.wpuf-fields.wpuf-rich-validation[data-type=rich]`,
 * the "Insert Photo" button above the editor when the field allows it.
 *
 * @since WPUF_SINCE
 */
import { Suspense, lazy, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Field from '../components/Field';
import { Counter, limitState } from './basic';
import { uploadFile } from '../api';

const TipTapEditor = lazy( () => import( /* webpackChunkName: "wpuf-editor" */ './editor/TipTapEditor' ) );

/** The editor's text (the classic limit counts TinyMCE's text, not the HTML). */
const stripTags = ( html ) => String( html || '' ).replace( /<[^>]*>/g, ' ' ).replace( /&nbsp;/g, ' ' ).replace( /\s+/g, ' ' ).trim();

const editorAvailable = () => 'function' === typeof window.wp?.editor?.initialize && !! window.tinymce;

const escapeAttr = ( text ) => String( text || '' ).replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' );

/** The classic "Insert Photo" button: uploads an image and hands its tag to the editor. */
function InsertImage( { formId, nonce, onInsert } ) {
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
            onInsert( `<img src="${ escapeAttr( result.url ) }" alt="${ escapeAttr( result.title ) }" />` );
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
                <span className="wpuf-media-icon" aria-hidden="true">
                    <svg width="100%" height="100%" viewBox="0 0 20 20" fill="none" focusable="false">
                        <rect x="2.75" y="3.75" width="14.5" height="12.5" rx="2" stroke="currentColor" strokeWidth="1.5" />
                        <circle cx="7.25" cy="8" r="1.5" fill="currentColor" />
                        <path d="M3.5 14.5l4.25-4 3 2.75 2.5-2.25 3.25 3" stroke="currentColor" strokeWidth="1.5" strokeLinejoin="round" />
                    </svg>
                </span>
                { __( 'Insert Photo', 'wp-user-frontend' ) }
            </a>
            { message && <div className="wpuf-error-msg" role="alert">{ message }</div> }
        </div>
    );
}

/** WordPress' TinyMCE on the field's textarea. */
function useTinyMce( id, editor, active, onChange, onBlur ) {
    const latest = useRef( onChange );
    latest.current = onChange;

    useEffect( () => {
        if ( ! active ) {
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
    }, [ id, active ] ); // eslint-disable-line react-hooks/exhaustive-deps
}

export default function RichTextField( { field, value, onChange, onBlur, formId, error, schema } ) {
    const id = `${ field.name }_${ formId }`;
    const editor = field.editor || {};
    const rich = 'yes' === field.rich || 'teeny' === field.rich;
    const [ engine, setEngine ] = useState( () => {
        if ( ! rich ) {
            return 'plain';
        }

        if ( 'tiptap' === editor.engine ) {
            return 'tiptap';
        }

        return editorAvailable() ? 'tinymce' : 'plain';
    } );
    const tiptap = useRef( null );

    useTinyMce( id, editor, 'tinymce' === engine, onChange, onBlur );

    // Stored content TipTap cannot keep: TinyMCE when it is on the page, the raw HTML otherwise.
    const fallback = () => {
        tiptap.current = null;
        setEngine( editorAvailable() ? 'tinymce' : 'plain' );
    };

    const insertImage = ( html ) => {
        if ( 'tiptap' === engine && tiptap.current ) {
            tiptap.current.chain().focus().insertContent( html ).run();

            return;
        }

        const instance = window.tinymce && window.tinymce.get( id );

        if ( instance ) {
            instance.insertContent( html );
            instance.fire( 'change' );
        }
    };

    const canInsert = 'yes' === field.insert_image && ( 'tiptap' === engine || 'tinymce' === engine );

    return (
        <Field
            field={ field }
            id={ id }
            error={ error }
            fieldsClass={ rich ? `wpuf-rich-validation wpuf_${ field.name }_${ formId }` : '' }
            data-type={ rich ? 'rich' : undefined }
            data-engine={ rich ? engine : undefined }
            invalid={ limitState( field, stripTags( value ) ).violated }
        >
            { canInsert && <InsertImage formId={ formId } nonce={ schema?.nonces?.upload || '' } onInsert={ insertImage } /> }
            { 'tiptap' === engine ? (
                <>
                    <Suspense fallback={ <div className="wpuf-tiptap wpuf-tiptap--loading" aria-busy="true" /> }>
                        <TipTapEditor
                            id={ id }
                            field={ field }
                            value={ value ?? '' }
                            onChange={ onChange }
                            onBlur={ onBlur }
                            onFallback={ fallback }
                            onReady={ ( instance ) => {
                                tiptap.current = instance;
                            } }
                            invalid={ !! error }
                        />
                    </Suspense>
                    { /* The classic field's textarea, for code that reads the form's DOM. */ }
                    <textarea name={ field.name } value={ value ?? '' } readOnly hidden aria-hidden="true" tabIndex={ -1 } />
                </>
            ) : (
                <textarea
                    id={ id }
                    className={ `textareafield wpuf_${ field.name }_${ formId }` }
                    name={ field.name }
                    rows={ editor.rows || field.rows || 5 }
                    defaultValue={ value ?? '' }
                    aria-required={ 'yes' === field.required || undefined }
                    aria-invalid={ error ? 'true' : undefined }
                    onChange={ 'tinymce' === engine ? undefined : ( event ) => onChange( event.target.value ) }
                    onBlur={ onBlur }
                />
            ) }
            { 'post_content' === field.template && <Counter field={ field } value={ stripTags( value ) } /> }
        </Field>
    );
}
