import { useEffect, useRef } from '@wordpress/element';

/**
 * WordPress TinyMCE editor wrapper.
 * Used by PostContent and PostExcerpt preview components.
 *
 * @param {Object} props
 * @param {string} props.id       Unique editor ID
 * @param {string} props.value    Editor content
 * @param {Function} props.onChange Content change handler
 * @param {boolean}  props.teeny  Use minimal toolbar (default: false)
 */
export default function TextEditor( { id, value = '', onChange, teeny = false } ) {
    const editorId = `wpuf-editor-${ id }`;
    const initialized = useRef( false );

    useEffect( () => {
        // wp.editor requires the textarea to be in the DOM
        const timeout = setTimeout( () => {
            if (
                typeof window.wp !== 'undefined' &&
                window.wp.editor &&
                ! initialized.current
            ) {
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
            if (
                typeof window.wp !== 'undefined' &&
                window.wp.editor &&
                initialized.current
            ) {
                window.wp.editor.remove( editorId );
                initialized.current = false;
            }
        };
    }, [ editorId ] );

    return (
        <textarea
            id={ editorId }
            className="wpuf-text-editor"
            defaultValue={ value }
        />
    );
}
