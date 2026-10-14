/**
 * Rich text editor of the admin screens: the TipTap editor of the frontend
 * forms (`src/frontend/kit/fields/editor`), with Visual / Code tabs and
 * WordPress' "Add Media", in place of TinyMCE.
 *
 * Same contract and stored shapes as WpEditor (it replaces it where it is
 * used): `format` "html" reports TinyMCE's getContent() shape (paragraphs as
 * <p>), "wp" the wpautop-free text a PHP wp_editor() form submits. Nothing
 * is reported until a real edit, so opening a screen never rewrites a value.
 *
 * Data safety: right after loading, the editor checks that the stored HTML
 * comes back unchanged from it (html.js roundTrips()). Content it cannot
 * keep 1:1 (scripts, unknown markup, block comments...) and toolbars with a
 * button it does not have open in the WordPress editor (WpEditor) instead,
 * as before. Without `wp.editor` there is nothing to fall back to and the
 * editor stays (the Code tab edits the raw HTML).
 *
 * @since WPUF_SINCE
 */
import { Suspense, lazy, useEffect, useRef, useState } from '@wordpress/element';
import { useInstanceId } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { cn } from '@wedevs/plugin-ui';
import { ImagePlus } from 'lucide-react';

import { SelectModeContext } from '../../../frontend/kit/components/Select';
import WpEditor, { FULL_TOOLBAR, TEENY_TOOLBAR } from './WpEditor';

const TipTapEditor = lazy( () => import( /* webpackChunkName: "wpuf-rich-editor" */ '../../../frontend/kit/fields/editor/TipTapEditor' ) );

/** Every TinyMCE button name the TipTap toolbar draws (Form_Schema::TIPTAP_BUTTONS). */
const KNOWN = [
    '|', 'formatselect', 'bold', 'italic', 'underline', 'strikethrough', 'blockquote', 'bullist', 'numlist',
    'alignleft', 'aligncenter', 'alignright', 'alignjustify', 'link', 'unlink', 'undo', 'redo', 'fullscreen',
    'hr', 'wp_more', 'forecolor', 'pastetext', 'removeformat', 'charmap', 'outdent', 'indent',
    'wp_adv', 'wp_help', 'spellchecker',
];

const wpEditor = () => ( 'undefined' !== typeof window && window.wp && window.wp.editor ) || null;
const autop = ( html ) => ( wpEditor() && 'function' === typeof wpEditor().autop && String( html || '' ).trim() ? wpEditor().autop( html ) : String( html || '' ) );
const removep = ( html ) => ( wpEditor() && 'function' === typeof wpEditor().removep ? wpEditor().removep( html ) : String( html || '' ) );

/** The HTML WordPress' media modal inserts for a picked file ("Insert into post"). */
function openMedia( onInsert ) {
    const media = window.wp && window.wp.media;

    if ( ! media ) {
        return;
    }

    const frame = media( { frame: 'post', state: 'insert', multiple: false } );

    frame.on( 'insert', () => {
        const state = frame.state();

        state.get( 'selection' ).each( ( attachment ) => {
            const display = state.display( attachment ).toJSON();
            const data = attachment.toJSON();
            const html = 'image' === data.type && media.string && media.string.image
                ? media.string.image( display, data )
                : media.string && media.string.link
                    ? media.string.link( { ...display, linkUrl: display.linkUrl || data.url, title: data.title }, data )
                    : `<a href="${ data.url }">${ data.title || data.filename }</a>`;

            onInsert( html );
        } );
    } );

    frame.open();
}

/**
 * @param {Object}   props
 * @param {string}   [props.id]           Editor id suffix (element id `wpuf-editor-<id>`).
 * @param {string}   [props.value]        HTML.
 * @param {Function} [props.onChange]     ( html: string ) => void
 * @param {boolean}  [props.teeny]        Minimal toolbar.
 * @param {string}   [props.toolbar]      Toolbar buttons (TinyMCE names), overrides teeny / full.
 * @param {Object}   [props.tinymce]      TinyMCE settings (kept for the fallback editor).
 * @param {boolean}  [props.mediaButtons] "Add Media" button (default true).
 * @param {number}   [props.rows]         Code tab rows.
 * @param {string}   [props.format]       html|wp.
 * @param {string}   [props.label]        Accessible name of the editing area.
 */
export default function RichEditor( props ) {
    const { id, value = '', onChange, teeny = false, toolbar, mediaButtons = true, rows = 10, format = 'html', className, label } = props;
    const instanceId = useInstanceId( RichEditor, 'wpuf-editor' );
    const editorId = id ? `wpuf-editor-${ id }` : instanceId;
    const buttons = String( toolbar || ( teeny ? TEENY_TOOLBAR : FULL_TOOLBAR ) ).split( ',' ).map( ( name ) => name.trim() ).filter( Boolean );
    const unknown = buttons.some( ( name ) => ! KNOWN.includes( name ) );
    const [ engine, setEngine ] = useState( unknown && wpEditor() ? 'wp' : 'tiptap' );
    const [ tab, setTab ] = useState( 'visual' );
    const [ mount, setMount ] = useState( 0 );
    const instance = useRef( null );
    const lastRef = useRef( value ?? '' );
    const onChangeRef = useRef( onChange );

    onChangeRef.current = onChange;

    const report = ( html ) => {
        if ( html === lastRef.current ) {
            return;
        }

        lastRef.current = html;
        onChangeRef.current?.( html );
    };

    // An outside value (reset, discard) reloads the editor unless it is what the editor
    // sent. While the editor has the focus the value is the user's own typing coming
    // back (a store may normalise it, e.g. line breaks): no reload, or the caret jumps.
    const wrap = useRef( null );

    useEffect( () => {
        const next = value ?? '';
        const typing = wrap.current && wrap.current.contains( document.activeElement );
        const same = ( a, b ) => String( a ).replace( /\r\n?/g, '\n' ).trim() === String( b ).replace( /\r\n?/g, '\n' ).trim();

        if ( next !== lastRef.current && ! typing && ! same( next, lastRef.current ) ) {
            lastRef.current = next;
            setMount( ( n ) => n + 1 );
        }
    }, [ value ] );

    if ( 'wp' === engine ) {
        return <WpEditor { ...props } />;
    }

    // The editor works in TinyMCE's shape (with <p>); "wp" values are wpautop'ed in and removep'ed out.
    const shown = 'wp' === format ? autop( lastRef.current ) : lastRef.current;
    const toStored = ( html ) => ( 'wp' === format ? removep( html ) : html );
    const field = {
        name: editorId,
        label: label || __( 'Content', 'wp-user-frontend' ),
        editor: { tinymce: { toolbar1: buttons.join( ',' ), toolbar2: '' } },
    };
    const canMedia = mediaButtons && !! ( window.wp && window.wp.media );
    const codeValue = 'wp' === format ? lastRef.current : removep( lastRef.current );

    return (
        <div ref={ wrap } data-wpuf-ui="" className={ cn( 'wpuf-rich-editor', className ) }>
            <div className="flex items-center justify-between gap-2 mb-2">
                <div>
                    { canMedia && 'visual' === tab && (
                        <button
                            type="button"
                            className="inline-flex items-center gap-1.5 h-8 px-3 text-[13px] font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30"
                            onClick={ () => openMedia( ( html ) => instance.current && instance.current.chain().focus().insertContent( html ).run() ) }
                        >
                            <ImagePlus className="size-4" aria-hidden="true" />
                            { __( 'Add Media', 'wp-user-frontend' ) }
                        </button>
                    ) }
                </div>
                <div className="inline-flex items-center gap-0.5 p-0.5 bg-gray-100 rounded-md" role="tablist" aria-label={ __( 'Editor mode', 'wp-user-frontend' ) }>
                    { [ [ 'visual', __( 'Visual', 'wp-user-frontend' ) ], [ 'code', __( 'Code', 'wp-user-frontend' ) ] ].map( ( [ key, text ] ) => (
                        <button
                            key={ key }
                            type="button"
                            role="tab"
                            aria-selected={ tab === key }
                            className={ cn( 'h-7 px-3 text-[13px] font-medium rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30', tab === key ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' ) }
                            onClick={ () => {
                                if ( key !== tab ) {
                                    setTab( key );
                                    // Back to Visual: the editor reloads what the Code tab holds.
                                    setMount( ( n ) => n + 1 );
                                }
                            } }
                        >
                            { text }
                        </button>
                    ) ) }
                </div>
            </div>
            { 'code' === tab ? (
                <textarea
                    id={ editorId }
                    rows={ rows }
                    spellCheck="false"
                    defaultValue={ codeValue }
                    // As WordPress' Text tab: wpautop-free HTML; "html" adds the paragraphs back.
                    onChange={ ( event ) => report( 'wp' === format ? event.target.value : autop( event.target.value ).trim() ) }
                    className="block w-full px-3 py-2 font-mono text-[13px] leading-5 text-gray-900 bg-white border border-solid border-gray-300 rounded-lg shadow-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/30"
                />
            ) : (
                <div className="wpuf-frontend wpuf-admin-tiptap">
                    { /* The admin keeps the browser's select for the paragraph menu (the frontend list needs the forms sheet). */ }
                    <SelectModeContext.Provider value="native">
                        <Suspense fallback={ <div className="wpuf-tiptap wpuf-tiptap--loading" aria-busy="true" /> }>
                            <TipTapEditor
                                key={ mount }
                                id={ editorId }
                                field={ field }
                                value={ shown }
                                onChange={ ( html ) => report( toStored( html ) ) }
                                onFallback={ () => wpEditor() && setEngine( 'wp' ) }
                                onReady={ ( editor ) => {
                                    instance.current = editor;
                                } }
                            />
                        </Suspense>
                    </SelectModeContext.Provider>
                </div>
            ) }
        </div>
    );
}
