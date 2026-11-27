/**
 * WordPress rich text editor (TinyMCE + Quicktags through `wp.editor`, as is).
 * The screen must call `wp_enqueue_editor()`; without `wp.editor` a plain
 * textarea with the WPUF control look is shown and still reports changes.
 *
 * - onChange gets the HTML after real edits, from the Visual tab (TinyMCE
 *   change/keyup/input/undo/redo) and from the Text tab (textarea input);
 *   loading the content does not count as an edit. Both tabs report the same
 *   shape, chosen by `format` to match what develop stored for the field:
 *   "html" = TinyMCE getContent() (paragraphs as <p>, the React builder and
 *   settings copies), "wp" = what a PHP wp_editor() form submits (wpautop
 *   paragraphs removed, `wp.editor.removep`).
 * - A new `value` from outside (reset, discard) is pushed into the editor
 *   unless it is the value the editor just reported.
 * - The editor is created after mount and removed on unmount (dialogs,
 *   accordions, StrictMode double mount); the latest onChange is always used.
 */
import { useEffect, useRef } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import { cn } from '@wedevs/plugin-ui';

export const FULL_TOOLBAR = 'formatselect,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,charmap,outdent,indent,undo,redo';
export const TEENY_TOOLBAR = 'bold,italic,underline,link';
const PLUGINS = 'charmap colorpicker hr lists paste tabfocus textcolor wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern';

const wpEditor = () => ( 'undefined' !== typeof window && window.wp && window.wp.editor ) || null;

/**
 * @param {Object}   props
 * @param {string}   [props.id]           Editor id suffix (element id `wpuf-editor-<id>`); unique per mount when omitted.
 * @param {string}   [props.value]        HTML.
 * @param {Function} [props.onChange]     ( html: string ) => void
 * @param {boolean}  [props.teeny]        Minimal toolbar.
 * @param {string}   [props.toolbar]      TinyMCE toolbar1 (overrides teeny / full), e.g. develop's wp_editor teeny buttons.
 * @param {Object}   [props.tinymce]      Extra TinyMCE settings (plugins, autoresize...), e.g. what develop passed to wp_editor().
 * @param {boolean}  [props.mediaButtons] "Add Media" button (default true).
 * @param {number}   [props.rows]         Textarea rows (Text tab and fallback).
 * @param {string}   [props.format]       html|wp (see above).
 */
export default function WpEditor( { id, value = '', onChange, teeny = false, toolbar, tinymce, mediaButtons = true, rows = 8, format = 'html', className } ) {
    const instanceId = useInstanceId( WpEditor, 'wpuf-editor' );
    const editorId = id ? `wpuf-editor-${ id }` : instanceId;
    const onChangeRef = useRef( onChange );
    const lastRef = useRef( value ?? '' );
    const hasEditor = !! wpEditor();

    onChangeRef.current = onChange;

    const report = ( html ) => {
        if ( html === lastRef.current ) {
            return;
        }
        lastRef.current = html;
        onChangeRef.current?.( html );
    };

    useEffect( () => {
        const api = wpEditor();
        const textarea = document.getElementById( editorId );

        if ( ! api || ! textarea ) {
            return undefined;
        }

        let started = false;
        // Text tab holds the wpautop-free form; "html" adds the paragraphs back.
        const onText = () => report( 'wp' === format ? textarea.value : api.autop( textarea.value ).trim() );

        // wp.editor.initialize() appends the editor container inside
        // .wp-editor-tools (a jQuery chain in wp-admin/js/editor.js), unlike PHP
        // wp_editor(), so the absolutely placed Visual / Code tabs ended up under
        // the editor. Move it next to the tools before TinyMCE creates its iframe
        // (moving an iframe later reloads it).
        const $ = window.jQuery;
        const fixLayout = ( event, init ) => {
            if ( ! init || init.selector !== `#${ editorId }` ) {
                return;
            }
            const wrap = document.getElementById( `wp-${ editorId }-wrap` );
            const container = wrap && wrap.querySelector( '.wp-editor-tools > .wp-editor-container' );

            if ( container ) {
                wrap.appendChild( container );
            }
        };

        // wp.editor needs the textarea attached and laid out: start on the next tick.
        const timer = setTimeout( () => {
            textarea.value = lastRef.current;
            if ( $ ) {
                $( document ).on( 'wp-before-tinymce-init', fixLayout );
            }
            api.initialize( editorId, {
                tinymce: {
                    wpautop: true,
                    // As PHP wp_editor() with wpautop: no line breaks between blocks.
                    indent: false,
                    plugins: PLUGINS,
                    toolbar1: toolbar || ( teeny ? TEENY_TOOLBAR : FULL_TOOLBAR ),
                    ...tinymce,
                    setup: ( editor ) => {
                        let ready = false;

                        editor.on( 'init', () => {
                            ready = true;
                        } );
                        editor.on( 'change keyup input undo redo', () => {
                            if ( ready ) {
                                report( 'wp' === format ? api.removep( editor.getContent() ) : editor.getContent() );
                            }
                        } );
                    },
                },
                quicktags: true,
                mediaButtons,
            } );
            if ( $ ) {
                $( document ).off( 'wp-before-tinymce-init', fixLayout );
            }
            textarea.addEventListener( 'input', onText );
            started = true;
        }, 0 );

        return () => {
            clearTimeout( timer );
            if ( started ) {
                textarea.removeEventListener( 'input', onText );
                api.remove( editorId );
            }
        };
    }, [ editorId, teeny, toolbar, mediaButtons, format ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // Outside value (reset/discard): push it in unless the editor sent it.
    useEffect( () => {
        const next = value ?? '';

        if ( next === lastRef.current ) {
            return;
        }
        lastRef.current = next;

        const textarea = document.getElementById( editorId );
        const mce = window.tinymce && window.tinymce.get( editorId );

        if ( mce && ! mce.isHidden() ) {
            mce.setContent( next );
        }
        if ( textarea ) {
            textarea.value = next;
        }
    }, [ value, editorId ] );

    if ( ! hasEditor ) {
        return (
            <textarea
                data-wpuf-ui=""
                id={ editorId }
                rows={ rows }
                defaultValue={ value ?? '' }
                onChange={ ( event ) => report( event.target.value ) }
                className={ cn( 'block w-full px-3 py-2 text-sm text-gray-900 bg-white border border-solid border-gray-300 rounded-md shadow-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/30', className ) }
            />
        );
    }

    return (
        <div data-wpuf-ui="" className={ cn( 'wpuf-wp-editor', className ) }>
            <textarea id={ editorId } name={ editorId } rows={ rows } className="wp-editor-area" defaultValue={ value ?? '' } />
        </div>
    );
}
