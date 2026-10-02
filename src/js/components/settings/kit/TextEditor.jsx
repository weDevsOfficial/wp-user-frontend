import { useEffect, useRef } from '@wordpress/element';
import { SETTING_CLASS_NAMES } from './SettingsField';

/**
 * WordPress TinyMCE editor wrapper. Ported from the Form Builder kit
 * (admin/form-builder/src/common/TextEditor.jsx). Requires wp.editor, which the
 * settings page enqueues via wp_enqueue_editor().
 */
export default function TextEditor( { id, value = '', onChange, teeny = false } ) {
    const editorId = `wpuf-editor-${ id }`;
    const initialized = useRef( false );

    useEffect( () => {
        const timeout = setTimeout( () => {
            if ( typeof window.wp !== 'undefined' && window.wp.editor && ! initialized.current ) {
                window.wp.editor.initialize( editorId, {
                    tinymce: {
                        wpautop: true,
                        plugins: 'charmap colorpicker hr lists paste tabfocus textcolor wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: teeny
                            ? 'bold,italic,underline,link'
                            : 'formatselect,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,charmap,outdent,indent,undo,redo',
                        setup: ( editor ) => {
                            editor.on( 'change keyup', () => {
                                if ( typeof onChange === 'function' ) {
                                    onChange( editor.getContent() );
                                }
                            } );
                        },
                    },
                    quicktags: true,
                    mediaButtons: true,
                } );
                initialized.current = true;
            }
        }, 100 );

        return () => {
            clearTimeout( timeout );
            if ( typeof window.wp !== 'undefined' && window.wp.editor && initialized.current ) {
                window.wp.editor.remove( editorId );
                initialized.current = false;
            }
        };
    }, [ editorId ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // The styled fallback matches the kit TextareaField, so even when TinyMCE
    // can't initialise (e.g. a dynamically-shown gateway panel) the field still
    // looks and behaves like our component instead of a bare browser textarea.
    return (
        <textarea
            id={ editorId }
            className={ `wpuf-text-editor ${ SETTING_CLASS_NAMES.textarea } wpuf-min-h-[140px]` }
            defaultValue={ value }
            onChange={ ( e ) => typeof onChange === 'function' && onChange( e.target.value ) }
        />
    );
}
