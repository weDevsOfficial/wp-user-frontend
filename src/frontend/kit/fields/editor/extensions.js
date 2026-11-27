/**
 * TipTap schema pieces that keep stored post content as it is.
 *
 * - WpufAttrs: every HTML attribute a node or mark does not model itself
 *   (class, style, id, width, data-*, ...) travels in `wpufAttrs` and is
 *   written back unchanged. Alignment, indent and text colour edit the
 *   `style` in it, the way TinyMCE writes them.
 * - RawBlock / RawInline: elements the editor has no node for (tables,
 *   embeds, divs, figures, media, form controls) are kept as locked chips
 *   holding their exact HTML. The chip shows the escaped source, never the
 *   rendered markup, so stored scripts or handlers cannot run in the editor.
 * - Marker: `<!--more-->` / `<!--nextpage-->` (see html.js).
 * - Simple marks for inline tags the starter kit lacks (sub, sup, small,
 *   mark, abbr, cite, q, kbd, ins, font, ...) and every `span`.
 *
 * @since WPUF_SINCE
 */
import { Extension, Mark, Node, mergeAttributes } from '@tiptap/core';

/** Read all attributes of an element except the ones the node models. */
const collect = ( element, skip = [] ) => {
    const out = {};

    Array.from( element.attributes || [] ).forEach( ( attr ) => {
        if ( ! skip.includes( attr.name ) ) {
            out[ attr.name ] = attr.value;
        }
    } );

    return Object.keys( out ).length ? out : null;
};

const attrsSpec = ( skip ) => ( {
    wpufAttrs: {
        default: null,
        parseHTML: ( element ) => collect( element, skip ),
        // TipTap's attribute merge rewrites style values (`#ff0000` becomes `rgb(255, 0, 0)`);
        // the exact text rides along in data-wpuf-style and html.js serialize() puts it back.
        renderHTML: ( attributes ) => {
            const attrs = attributes.wpufAttrs || {};

            return attrs.style ? { ...attrs, 'data-wpuf-style': attrs.style } : attrs;
        },
    },
} );

export const WpufAttrs = Extension.create( {
    name: 'wpufAttrs',

    addGlobalAttributes() {
        return [
            { types: [ 'paragraph', 'heading', 'blockquote', 'bulletList', 'listItem', 'horizontalRule', 'codeBlock', 'hardBreak' ], attributes: attrsSpec( [] ) },
            { types: [ 'orderedList' ], attributes: attrsSpec( [ 'start', 'type' ] ) },
            { types: [ 'image' ], attributes: attrsSpec( [ 'src', 'alt', 'title' ] ) },
            { types: [ 'link' ], attributes: attrsSpec( [ 'href', 'target', 'rel', 'class' ] ) },
        ];
    },
} );

/** Style string helpers (TinyMCE writes `text-align: center;`, `padding-left: 40px;`, `color: #ff0000;`). */
export const readStyle = ( style ) => {
    const out = {};

    String( style || '' ).split( ';' ).forEach( ( part ) => {
        const index = part.indexOf( ':' );

        if ( index > 0 ) {
            out[ part.slice( 0, index ).trim().toLowerCase() ] = part.slice( index + 1 ).trim();
        }
    } );

    return out;
};

export const writeStyle = ( map ) => Object.keys( map ).filter( ( key ) => '' !== map[ key ] && null !== map[ key ] && undefined !== map[ key ] ).map( ( key ) => `${ key }: ${ map[ key ] };` ).join( ' ' );

/** `wpufAttrs` with one style property set (or removed with a null value). */
export const withStyle = ( attrs, property, value ) => {
    const next = { ...( attrs || {} ) };
    const map = readStyle( next.style );

    if ( null === value || '' === value ) {
        delete map[ property ];
    } else {
        map[ property ] = value;
    }

    const style = writeStyle( map );

    if ( style ) {
        next.style = style;
    } else {
        delete next.style;
    }

    return Object.keys( next ).length ? next : null;
};

const BLOCK_TAGS = [ 'table', 'div', 'figure', 'section', 'article', 'aside', 'header', 'footer', 'nav', 'main', 'details', 'dl', 'form', 'fieldset', 'address', 'center', 'hgroup', 'menu', 'style' ];
const INLINE_TAGS = [ 'iframe', 'video', 'audio', 'svg', 'embed', 'object', 'picture', 'canvas', 'input', 'button', 'select', 'textarea', 'script', 'noscript', 'math', 'map', 'img[usemap]' ];

const toDom = ( html ) => {
    const template = document.createElement( 'template' );
    template.innerHTML = html;

    return template.content.firstElementChild || template.content.firstChild || document.createTextNode( '' );
};

/** The locked chip in the editor: a label and the escaped start of the stored HTML. */
const chipView = ( label ) => ( { node } ) => {
    const dom = document.createElement( node.isInline ? 'span' : 'div' );
    dom.className = 'wpuf-tiptap-raw';
    dom.contentEditable = 'false';
    const tag = document.createElement( 'span' );
    tag.className = 'wpuf-tiptap-raw__label';
    tag.textContent = label;
    const code = document.createElement( 'code' );
    const html = String( node.attrs.html || '' );
    code.textContent = html.length > 120 ? html.slice( 0, 120 ) + '…' : html;
    dom.append( tag, code );
    dom.title = html;

    return { dom };
};

export const RawBlock = Node.create( {
    name: 'wpufRawBlock',
    group: 'block',
    atom: true,
    selectable: true,
    draggable: false,

    addAttributes() {
        return { html: { default: '', parseHTML: ( element ) => element.outerHTML, renderHTML: () => ( {} ) } };
    },

    parseHTML() {
        return BLOCK_TAGS.map( ( tag ) => ( { tag, priority: 70 } ) );
    },

    renderHTML( { node } ) {
        return toDom( node.attrs.html );
    },

    addNodeView() {
        return chipView( 'HTML' );
    },
} );

export const RawInline = Node.create( {
    name: 'wpufRawInline',
    group: 'inline',
    inline: true,
    atom: true,
    selectable: true,

    addAttributes() {
        return { html: { default: '', parseHTML: ( element ) => element.outerHTML, renderHTML: () => ( {} ) } };
    },

    parseHTML() {
        return INLINE_TAGS.map( ( tag ) => ( { tag, priority: 70 } ) );
    },

    renderHTML( { node } ) {
        return toDom( node.attrs.html );
    },

    addNodeView() {
        return chipView( 'HTML' );
    },
} );

export const Marker = Node.create( {
    name: 'wpufMarker',
    group: 'block',
    atom: true,
    selectable: true,

    addAttributes() {
        return {
            kind: { default: 'more', parseHTML: ( element ) => element.getAttribute( 'data-wpuf-marker' ), renderHTML: ( attributes ) => ( { 'data-wpuf-marker': attributes.kind } ) },
            text: { default: null, parseHTML: ( element ) => element.getAttribute( 'data-wpuf-marker-text' ), renderHTML: ( attributes ) => ( attributes.text ? { 'data-wpuf-marker-text': attributes.text } : {} ) },
        };
    },

    parseHTML() {
        return [ { tag: 'hr[data-wpuf-marker]', priority: 100 } ];
    },

    renderHTML( { HTMLAttributes } ) {
        return [ 'hr', HTMLAttributes ];
    },

    addNodeView() {
        return ( { node } ) => {
            const dom = document.createElement( 'div' );
            dom.className = 'wpuf-tiptap-marker';
            dom.contentEditable = 'false';
            dom.dataset.kind = node.attrs.kind;
            dom.textContent = 'nextpage' === node.attrs.kind ? 'Page break' : 'More';

            return { dom };
        };
    },
} );

const simpleMark = ( tag ) => Mark.create( {
    name: `wpuf_${ tag }`,

    addAttributes() {
        return attrsSpec( [] );
    },

    parseHTML() {
        return [ { tag } ];
    },

    renderHTML( { HTMLAttributes } ) {
        return [ tag, mergeAttributes( HTMLAttributes ), 0 ];
    },
} );

export const SIMPLE_MARK_TAGS = [ 'span', 'sub', 'sup', 'small', 'mark', 'abbr', 'cite', 'q', 'kbd', 'ins', 'font', 'var', 'samp', 'dfn', 'bdi', 'bdo', 'time', 'big', 'tt', 'acronym' ];

export const SimpleMarks = SIMPLE_MARK_TAGS.map( simpleMark );

const onlyStyle = ( element, property, word ) => {
    if ( 1 !== element.attributes.length || ! element.hasAttribute( 'style' ) ) {
        return false;
    }

    const map = readStyle( element.getAttribute( 'style' ) );
    const keys = Object.keys( map );

    return 1 === keys.length && property === keys[ 0 ] && map[ property ].toLowerCase() === word;
};

/** Underline as WordPress' TinyMCE writes it: `<span style="text-decoration: underline;">` (reads `<u>` too). */
export const LegacyUnderline = Mark.create( {
    name: 'underline',

    parseHTML() {
        return [
            { tag: 'u' },
            { tag: 'span', priority: 80, getAttrs: ( element ) => ( onlyStyle( element, 'text-decoration', 'underline' ) ? {} : false ) },
        ];
    },

    renderHTML() {
        return [ 'span', { style: 'text-decoration: underline;' }, 0 ];
    },

    addCommands() {
        return { toggleUnderline: () => ( { commands } ) => commands.toggleMark( this.name ) };
    },

    addKeyboardShortcuts() {
        return { 'Mod-u': () => this.editor.commands.toggleUnderline() };
    },
} );

/** Strikethrough as WordPress' TinyMCE writes it (`strikethrough: {inline: "del"}`); reads `<s>` / `<strike>` too. */
export const LegacyStrike = Mark.create( {
    name: 'strike',

    addAttributes() {
        return attrsSpec( [] );
    },

    parseHTML() {
        return [ { tag: 'del' }, { tag: 's' }, { tag: 'strike' } ];
    },

    renderHTML( { HTMLAttributes } ) {
        return [ 'del', mergeAttributes( HTMLAttributes ), 0 ];
    },

    addCommands() {
        return { toggleStrike: () => ( { commands } ) => commands.toggleMark( this.name ) };
    },

    addKeyboardShortcuts() {
        return { 'Mod-Shift-s': () => this.editor.commands.toggleStrike() };
    },
} );
