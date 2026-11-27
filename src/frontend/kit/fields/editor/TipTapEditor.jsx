/**
 * The modern rich editor of the React forms (TipTap). Its toolbar is the
 * TinyMCE toolbar the schema computed (`text_editor_control`,
 * `wpuf_textarea_editor_args`, `teeny_mce_buttons`, `mce_buttons(_2)`):
 * every button name maps to a control here, `wp_adv` shows the second row,
 * `|` is a separator. The PHP side sends a field here only when every
 * button is known (Form_Schema::editor_engine()).
 *
 * Stored content goes through html.js (wpautop, more / page break tags)
 * and the lossless schema in extensions.js. Right after loading, the
 * editor checks that it gives back what it was given; when it does not,
 * `onFallback()` lets the field open in TinyMCE instead.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { EditorContent, useEditor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import {
    AlignCenter, AlignJustify, AlignLeft, AlignRight, Baseline, Bold, CircleHelp, Eraser, IndentDecrease, IndentIncrease,
    Italic, Link2, Link2Off, List, ListOrdered, Maximize2, Minimize2, Minus, Omega, Quote, Redo2, ClipboardType,
    SeparatorHorizontal, Strikethrough, Underline as UnderlineIcon, Undo2, Ellipsis,
} from 'lucide-react';
import { LegacyStrike, LegacyUnderline, Marker, RawBlock, RawInline, SimpleMarks, WpufAttrs, readStyle, withStyle } from './extensions';
import Select from '../../components/Select';
import { explainRoundTrip, hasForeignComments, load, roundTrips, serialize } from './html';

/** TinyMCE's default text colour map (textcolor plugin). */
const COLORS = [ '000000', '993300', '333300', '003300', '003366', '000080', '333399', '333333', '800000', 'FF6600', '808000', '008000', '008080', '0000FF', '666699', '808080', 'FF0000', 'FF9900', '99CC00', '339966', '33CCCC', '3366FF', '800080', '999999', 'FF00FF', 'FFCC00', 'FFFF00', '00FF00', '00FFFF', '00CCFF', '993366', 'FFFFFF', 'FF99CC', 'FFCC99', 'FFFF99', 'CCFFCC', 'CCFFFF', '99CCFF', 'CC99FF' ];

/** A short special character set (TinyMCE's charmap, most used). */
const CHARS = '&©®™°±×÷¼½¾¹²³µ¶§•…–—‘’“”«»‹›€£¥¢¤¡¿†‡‰′″←↑→↓↔≈≠≤≥∞√∑∏∂∫αβγδπΩ♠♣♥♦✓✗'.split( '' );

const ALIGN_NODES = [ 'paragraph', 'heading', 'listItem' ];

/** Align like WordPress' TinyMCE formats: `text-align` on the text block, an `alignX` class on a selected image. */
function align( editor, value ) {
    const { state } = editor;
    const { selection } = state;
    const tr = state.tr;

    if ( selection.node && 'image' === selection.node.type.name ) {
        const node = selection.node;
        const attrs = { ...( node.attrs.wpufAttrs || {} ) };
        const classes = String( attrs.class || '' ).split( /\s+/ ).filter( Boolean );
        const had = classes.includes( `align${ value }` );
        const rest = classes.filter( ( name ) => ! /^align(left|center|right|none)$/.test( name ) );

        if ( ! had && 'justify' !== value ) {
            rest.push( `align${ value }` );
        }

        if ( rest.length ) {
            attrs.class = rest.join( ' ' );
        } else {
            delete attrs.class;
        }

        tr.setNodeMarkup( selection.from, undefined, { ...node.attrs, wpufAttrs: Object.keys( attrs ).length ? attrs : null } );
        editor.view.dispatch( tr );
        editor.commands.focus();

        return;
    }

    const done = new Set();

    state.doc.nodesBetween( selection.from, selection.to, ( node, pos ) => {
        if ( ! ALIGN_NODES.includes( node.type.name ) ) {
            return true;
        }

        // A list item's own paragraph follows the item (TinyMCE aligns the li).
        if ( 'paragraph' === node.type.name && state.doc.resolve( pos ).parent.type.name === 'listItem' ) {
            return false;
        }

        if ( done.has( pos ) ) {
            return false;
        }

        done.add( pos );
        const current = readStyle( node.attrs.wpufAttrs?.style )[ 'text-align' ] || '';
        tr.setNodeMarkup( pos, undefined, { ...node.attrs, wpufAttrs: withStyle( node.attrs.wpufAttrs, 'text-align', current === value ? null : value ) } );

        return 'listItem' !== node.type.name;
    } );

    editor.view.dispatch( tr );
    editor.commands.focus();
}

function indent( editor, step ) {
    if ( editor.isActive( 'listItem' ) ) {
        return step > 0 ? editor.chain().focus().sinkListItem( 'listItem' ).run() : editor.chain().focus().liftListItem( 'listItem' ).run();
    }

    const { state } = editor;
    const tr = state.tr;

    state.doc.nodesBetween( state.selection.from, state.selection.to, ( node, pos ) => {
        if ( ! [ 'paragraph', 'heading' ].includes( node.type.name ) ) {
            return true;
        }

        const current = parseInt( readStyle( node.attrs.wpufAttrs?.style )[ 'padding-left' ], 10 ) || 0;
        const next = Math.max( 0, current + step * 40 );
        tr.setNodeMarkup( pos, undefined, { ...node.attrs, wpufAttrs: withStyle( node.attrs.wpufAttrs, 'padding-left', next ? `${ next }px` : null ) } );

        return false;
    } );

    editor.view.dispatch( tr );
    editor.commands.focus();

    return true;
}

function alignOf( editor ) {
    const { selection } = editor.state;

    if ( selection.node && 'image' === selection.node.type.name ) {
        const match = String( selection.node.attrs.wpufAttrs?.class || '' ).match( /\balign(left|center|right)\b/ );

        return match ? match[ 1 ] : '';
    }

    const { $from } = selection;

    for ( let depth = $from.depth; depth > 0; depth-- ) {
        const node = $from.node( depth );

        if ( 'listItem' === node.type.name || [ 'paragraph', 'heading' ].includes( node.type.name ) ) {
            const value = readStyle( node.attrs.wpufAttrs?.style )[ 'text-align' ];

            if ( value || 'listItem' === node.type.name ) {
                return value || '';
            }

            if ( depth > 1 && 'listItem' === $from.node( depth - 1 ).type.name ) {
                continue;
            }

            return '';
        }
    }

    return '';
}

function formatOf( editor ) {
    for ( let level = 1; level <= 6; level++ ) {
        if ( editor.isActive( 'heading', { level } ) ) {
            return `h${ level }`;
        }
    }

    return editor.isActive( 'codeBlock' ) ? 'pre' : 'p';
}

/** Insert a more / page break marker and put the caret on the line after it (a new line when none follows). */
function insertMarker( editor, kind ) {
    editor.chain().focus().insertContent( { type: 'wpufMarker', attrs: { kind } } ).run();

    const { state } = editor;
    const end = state.selection.to;
    const next = state.doc.resolve( end ).nodeAfter;

    if ( next && next.isTextblock ) {
        editor.commands.setTextSelection( end + 1 );
    } else {
        editor.chain().insertContentAt( end, { type: 'paragraph' } ).setTextSelection( end + 1 ).run();
    }
}

function Popover( { open, onClose, children, label } ) {
    const ref = useRef( null );

    useEffect( () => {
        if ( ! open ) {
            return undefined;
        }

        const away = ( event ) => {
            if ( ref.current && ! ref.current.contains( event.target ) ) {
                onClose();
            }
        };
        const key = ( event ) => 'Escape' === event.key && onClose();

        document.addEventListener( 'mousedown', away );
        document.addEventListener( 'keydown', key );

        return () => {
            document.removeEventListener( 'mousedown', away );
            document.removeEventListener( 'keydown', key );
        };
    }, [ open, onClose ] );

    return open ? <div ref={ ref } className="wpuf-tiptap-pop" role="dialog" aria-label={ label }>{ children }</div> : null;
}

function LinkForm( { editor, onClose } ) {
    const current = editor.getAttributes( 'link' );
    const [ href, setHref ] = useState( current.href || '' );
    const [ blank, setBlank ] = useState( '_blank' === current.target );

    const apply = ( event ) => {
        event.preventDefault();
        const url = href.trim();

        if ( ! url ) {
            editor.chain().focus().extendMarkRange( 'link' ).unsetLink().run();
        } else {
            editor.chain().focus().extendMarkRange( 'link' ).setLink( { href: url, target: blank ? '_blank' : null, rel: blank ? 'noopener' : null } ).run();
        }

        // Continue typing after the link, like TinyMCE (focus now: TipTap's focus() waits a frame).
        editor.commands.setTextSelection( editor.state.selection.to );
        editor.view.focus();
        onClose();
    };

    return (
        <div className="wpuf-tiptap-link">
            <input type="url" value={ href } onChange={ ( event ) => setHref( event.target.value ) } placeholder="https://" aria-label={ __( 'URL', 'wp-user-frontend' ) } autoFocus onKeyDown={ ( event ) => 'Enter' === event.key && apply( event ) } />
            <label><input type="checkbox" checked={ blank } onChange={ ( event ) => setBlank( event.target.checked ) } /> { __( 'Open in new tab', 'wp-user-frontend' ) }</label>
            <div className="wpuf-tiptap-link__actions">
                <button type="button" onClick={ apply }>{ __( 'Apply', 'wp-user-frontend' ) }</button>
                { current.href && <button type="button" onClick={ () => { editor.chain().focus().extendMarkRange( 'link' ).unsetLink().run(); editor.commands.setTextSelection( editor.state.selection.to ); onClose(); } }>{ __( 'Remove', 'wp-user-frontend' ) }</button> }
            </div>
        </div>
    );
}

function setColor( editor, color ) {
    const chain = editor.chain().focus();

    if ( ! color ) {
        const attrs = editor.getAttributes( 'wpuf_span' ).wpufAttrs;
        const rest = withStyle( attrs, 'color', null );

        return rest ? chain.setMark( 'wpuf_span', { wpufAttrs: rest } ).run() : chain.unsetMark( 'wpuf_span' ).run();
    }

    const attrs = editor.getAttributes( 'wpuf_span' ).wpufAttrs;

    return chain.setMark( 'wpuf_span', { wpufAttrs: withStyle( attrs, 'color', color ) } ).run();
}

export default function TipTapEditor( { id, field, value, onChange, onBlur, onFallback, onReady, invalid } ) {
    const editorConfig = field.editor || {};
    const tinymce = editorConfig.tinymce || {};
    const rows = [ tinymce.toolbar1, tinymce.toolbar2 ].map( ( row ) => String( row || '' ).split( ',' ).map( ( name ) => name.trim() ).filter( Boolean ) );
    const hasAdv = rows[ 0 ].includes( 'wp_adv' );
    const [ adv, setAdv ] = useState( false );
    const [ full, setFull ] = useState( false );
    const [ pop, setPop ] = useState( '' );
    const [ , force ] = useState( 0 );
    const plain = useRef( false );
    const latest = useRef( onChange );
    latest.current = onChange;
    const checked = useRef( false );
    const initial = useRef( load( value ) );

    const editor = useEditor( {
        // The toolbar reads isActive() while rendering (TipTap 3 no longer re-renders on each change by default).
        shouldRerenderOnTransaction: true,
        extensions: [
            // TipTap 3's kit brings underline, link and a trailing paragraph: ours are the
            // TinyMCE-compatible marks and the configured Link below, and no extra <p> is added.
            StarterKit.configure( { heading: { levels: [ 1, 2, 3, 4, 5, 6 ] }, strike: false, underline: false, link: false, trailingNode: false } ),
            LegacyUnderline,
            LegacyStrike,
            Link.configure( { openOnClick: false, autolink: false, linkOnPaste: true, HTMLAttributes: { target: null, rel: null, class: null } } ),
            Image.extend( { inline: true, group: 'inline' } ).configure( { allowBase64: true } ),
            ...( field.placeholder ? [ Placeholder.configure( { placeholder: field.placeholder } ) ] : [] ),
            WpufAttrs,
            RawBlock,
            RawInline,
            Marker,
            ...SimpleMarks,
        ],
        content: initial.current,
        editable: ! tinymce.readonly,
        editorProps: {
            attributes: {
                id,
                class: 'wpuf-tiptap-content wp-exclude-emoji',
                role: 'textbox',
                'aria-multiline': 'true',
                'aria-label': field.label || field.name,
                'aria-required': 'yes' === field.required ? 'true' : 'false',
                'aria-invalid': invalid ? 'true' : 'false',
                spellcheck: 'true',
            },
            handlePaste: ( view, event ) => {
                if ( ! plain.current ) {
                    return false;
                }

                const text = event.clipboardData && event.clipboardData.getData( 'text/plain' );

                if ( ! text ) {
                    return false;
                }

                view.dispatch( view.state.tr.insertText( text ) );

                return true;
            },
        },
        onUpdate: ( { editor: instance } ) => latest.current( serialize( instance.getHTML() ) ),
        onSelectionUpdate: () => force( ( n ) => n + 1 ),
        onBlur: () => onBlur && onBlur(),
    } );

    // Stored content must come back unchanged; otherwise hand the field to TinyMCE.
    useEffect( () => {
        if ( ! editor || checked.current ) {
            return;
        }

        checked.current = true;
        const stored = String( value || '' );

        if ( stored.trim() && ( hasForeignComments( stored ) || ! roundTrips( stored, editor.getHTML() ) ) ) {
            if ( window.wpufEditorDebug ) {
                // eslint-disable-next-line no-console
                console.info( '[WPUF editor] TinyMCE fallback', explainRoundTrip( stored, editor.getHTML() ), JSON.stringify( editor.getJSON() ) );
            }

            onFallback && onFallback();

            return;
        }

        onReady && onReady( editor );
    }, [ editor ] ); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect( () => {
        if ( ! full ) {
            return undefined;
        }

        const key = ( event ) => 'Escape' === event.key && setFull( false );
        document.addEventListener( 'keydown', key );

        return () => document.removeEventListener( 'keydown', key );
    }, [ full ] );

    if ( ! editor ) {
        return null;
    }

    const btn = ( name, label, Icon, run, active = false, disabled = false ) => (
        <button
            key={ name }
            type="button"
            className={ `wpuf-tiptap-btn${ active ? ' is-active' : '' }` }
            title={ label }
            aria-label={ label }
            aria-pressed={ active }
            disabled={ disabled }
            onMouseDown={ ( event ) => event.preventDefault() }
            onClick={ run }
        >
            <Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
        </button>
    );

    const chain = () => editor.chain().focus();

    // A popover action ends with the caret back in the editor (the popover's own control had focus).
    const closePop = () => {
        editor.view.focus();
        setPop( '' );
    };
    const current = alignOf( editor );

    const control = ( name, index ) => {
        switch ( name ) {
            case '|':
                return <span key={ `sep-${ index }` } className="wpuf-tiptap-sep" aria-hidden="true" />;
            case 'formatselect':
                return (
                    <Select
                        key={ name }
                        size="sm"
                        className="wpuf-tiptap-format"
                        label={ __( 'Paragraph format', 'wp-user-frontend' ) }
                        value={ formatOf( editor ) }
                        showPlaceholder={ false }
                        returnFocus={ false }
                        options={ [
                            { value: 'p', label: __( 'Paragraph', 'wp-user-frontend' ) },
                            ...[ 1, 2, 3, 4, 5, 6 ].map( ( level ) => ( {
                                value: `h${ level }`,
                                /* translators: %d: heading level */
                                label: sprintf( __( 'Heading %d', 'wp-user-frontend' ), level ),
                            } ) ),
                            { value: 'pre', label: __( 'Preformatted', 'wp-user-frontend' ) },
                        ] }
                        onChange={ ( format ) => {
                            if ( 'p' === format ) {
                                chain().setParagraph().run();
                            } else if ( 'pre' === format ) {
                                chain().setCodeBlock().run();
                            } else {
                                chain().setHeading( { level: Number( format.slice( 1 ) ) } ).run();
                            }
                        } }
                    />
                );
            case 'bold':
                return btn( name, __( 'Bold', 'wp-user-frontend' ), Bold, () => chain().toggleBold().run(), editor.isActive( 'bold' ) );
            case 'italic':
                return btn( name, __( 'Italic', 'wp-user-frontend' ), Italic, () => chain().toggleItalic().run(), editor.isActive( 'italic' ) );
            case 'underline':
                return btn( name, __( 'Underline', 'wp-user-frontend' ), UnderlineIcon, () => chain().toggleUnderline().run(), editor.isActive( 'underline' ) );
            case 'strikethrough':
                return btn( name, __( 'Strikethrough', 'wp-user-frontend' ), Strikethrough, () => chain().toggleStrike().run(), editor.isActive( 'strike' ) );
            case 'blockquote':
                return btn( name, __( 'Blockquote', 'wp-user-frontend' ), Quote, () => chain().toggleBlockquote().run(), editor.isActive( 'blockquote' ) );
            case 'bullist':
                return btn( name, __( 'Bulleted list', 'wp-user-frontend' ), List, () => chain().toggleBulletList().run(), editor.isActive( 'bulletList' ) );
            case 'numlist':
                return btn( name, __( 'Numbered list', 'wp-user-frontend' ), ListOrdered, () => chain().toggleOrderedList().run(), editor.isActive( 'orderedList' ) );
            case 'alignleft':
                return btn( name, __( 'Align left', 'wp-user-frontend' ), AlignLeft, () => align( editor, 'left' ), 'left' === current );
            case 'aligncenter':
                return btn( name, __( 'Align center', 'wp-user-frontend' ), AlignCenter, () => align( editor, 'center' ), 'center' === current );
            case 'alignright':
                return btn( name, __( 'Align right', 'wp-user-frontend' ), AlignRight, () => align( editor, 'right' ), 'right' === current );
            case 'alignjustify':
                return btn( name, __( 'Justify', 'wp-user-frontend' ), AlignJustify, () => align( editor, 'justify' ), 'justify' === current );
            case 'link':
                return (
                    <span key={ name } className="wpuf-tiptap-popwrap">
                        { btn( name, __( 'Insert/edit link', 'wp-user-frontend' ), Link2, () => setPop( 'link' === pop ? '' : 'link' ), editor.isActive( 'link' ) ) }
                        <Popover open={ 'link' === pop } onClose={ () => setPop( '' ) } label={ __( 'Link', 'wp-user-frontend' ) }>
                            <LinkForm editor={ editor } onClose={ closePop } />
                        </Popover>
                    </span>
                );
            case 'unlink':
                return btn( name, __( 'Remove link', 'wp-user-frontend' ), Link2Off, () => chain().extendMarkRange( 'link' ).unsetLink().run(), false, ! editor.isActive( 'link' ) );
            case 'undo':
                return btn( name, __( 'Undo', 'wp-user-frontend' ), Undo2, () => chain().undo().run(), false, ! editor.can().undo() );
            case 'redo':
                return btn( name, __( 'Redo', 'wp-user-frontend' ), Redo2, () => chain().redo().run(), false, ! editor.can().redo() );
            case 'fullscreen':
                return btn( name, full ? __( 'Exit fullscreen', 'wp-user-frontend' ) : __( 'Fullscreen', 'wp-user-frontend' ), full ? Minimize2 : Maximize2, () => setFull( ! full ), full );
            case 'hr':
                return btn( name, __( 'Horizontal line', 'wp-user-frontend' ), Minus, () => chain().setHorizontalRule().run() );
            case 'wp_more':
                return btn( name, __( 'Insert Read More tag', 'wp-user-frontend' ), SeparatorHorizontal, () => insertMarker( editor, 'more' ) );
            case 'forecolor':
                return (
                    <span key={ name } className="wpuf-tiptap-popwrap">
                        { btn( name, __( 'Text color', 'wp-user-frontend' ), Baseline, () => setPop( 'color' === pop ? '' : 'color' ) ) }
                        <Popover open={ 'color' === pop } onClose={ () => setPop( '' ) } label={ __( 'Text color', 'wp-user-frontend' ) }>
                            <div className="wpuf-tiptap-colors">
                                { COLORS.map( ( hex ) => (
                                    <button key={ hex } type="button" className="wpuf-tiptap-color" style={ { background: `#${ hex }` } } title={ `#${ hex }` } aria-label={ `#${ hex }` } onMouseDown={ ( event ) => event.preventDefault() } onClick={ () => { setColor( editor, `#${ hex.toLowerCase() }` ); closePop(); } } />
                                ) ) }
                            </div>
                            <button type="button" className="wpuf-tiptap-nocolor" onClick={ () => { setColor( editor, null ); closePop(); } }>{ __( 'No color', 'wp-user-frontend' ) }</button>
                        </Popover>
                    </span>
                );
            case 'pastetext':
                return btn( name, __( 'Paste as text', 'wp-user-frontend' ), ClipboardType, () => { plain.current = ! plain.current; force( ( n ) => n + 1 ); }, plain.current );
            case 'removeformat':
                return btn( name, __( 'Clear formatting', 'wp-user-frontend' ), Eraser, () => chain().unsetAllMarks().run() );
            case 'charmap':
                return (
                    <span key={ name } className="wpuf-tiptap-popwrap">
                        { btn( name, __( 'Special character', 'wp-user-frontend' ), Omega, () => setPop( 'chars' === pop ? '' : 'chars' ) ) }
                        <Popover open={ 'chars' === pop } onClose={ () => setPop( '' ) } label={ __( 'Special character', 'wp-user-frontend' ) }>
                            <div className="wpuf-tiptap-chars">
                                { CHARS.map( ( char ) => (
                                    <button key={ char } type="button" onMouseDown={ ( event ) => event.preventDefault() } onClick={ () => { chain().insertContent( char ).run(); closePop(); } }>{ char }</button>
                                ) ) }
                            </div>
                        </Popover>
                    </span>
                );
            case 'outdent':
                return btn( name, __( 'Decrease indent', 'wp-user-frontend' ), IndentDecrease, () => indent( editor, -1 ) );
            case 'indent':
                return btn( name, __( 'Increase indent', 'wp-user-frontend' ), IndentIncrease, () => indent( editor, 1 ) );
            case 'wp_adv':
                return btn( name, __( 'Toolbar Toggle', 'wp-user-frontend' ), Ellipsis, () => setAdv( ! adv ), adv );
            case 'wp_help':
                return (
                    <span key={ name } className="wpuf-tiptap-popwrap">
                        { btn( name, __( 'Keyboard shortcuts', 'wp-user-frontend' ), CircleHelp, () => setPop( 'help' === pop ? '' : 'help' ) ) }
                        <Popover open={ 'help' === pop } onClose={ () => setPop( '' ) } label={ __( 'Keyboard shortcuts', 'wp-user-frontend' ) }>
                            <dl className="wpuf-tiptap-help">
                                <dt>Ctrl/⌘ + B</dt><dd>{ __( 'Bold', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + I</dt><dd>{ __( 'Italic', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + U</dt><dd>{ __( 'Underline', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + Z</dt><dd>{ __( 'Undo', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + Shift + Z</dt><dd>{ __( 'Redo', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + Shift + 8</dt><dd>{ __( 'Bulleted list', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + Shift + 7</dt><dd>{ __( 'Numbered list', 'wp-user-frontend' ) }</dd>
                                <dt>Ctrl/⌘ + Alt + 1-6</dt><dd>{ __( 'Heading', 'wp-user-frontend' ) }</dd>
                            </dl>
                        </Popover>
                    </span>
                );
            case 'spellchecker':
                // The browser's spell checker is on (spellcheck="true").
                return null;
            default:
                return null;
        }
    };

    const showSecond = rows[ 1 ].length > 0 && ( ! hasAdv || adv );

    return (
        <div className={ `wpuf-tiptap wp-exclude-emoji${ full ? ' is-fullscreen' : '' }${ tinymce.readonly ? ' is-readonly' : '' }` } data-engine="tiptap">
            { ! tinymce.readonly && (
                <div className="wpuf-tiptap-toolbar" role="toolbar" aria-label={ __( 'Formatting', 'wp-user-frontend' ) } aria-controls={ id }>
                    <div className="wpuf-tiptap-row">{ rows[ 0 ].map( control ) }</div>
                    { showSecond && <div className="wpuf-tiptap-row">{ rows[ 1 ].map( control ) }</div> }
                </div>
            ) }
            <EditorContent editor={ editor } className="wpuf-tiptap-body" style={ { '--wpuf-tiptap-rows': editorConfig.rows || 5 } } />
        </div>
    );
}

