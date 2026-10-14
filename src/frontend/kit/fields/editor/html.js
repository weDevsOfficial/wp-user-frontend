/**
 * HTML in and out of the TipTap editor, in the shape the classic editor
 * stored, so existing content opens unchanged and new content is saved
 * the way the legacy form saved it.
 *
 * The legacy form posted TinyMCE's getContent() (frontend-form.js appends
 * it after the serialized textarea), with WordPress' TinyMCE plugin
 * (wp-includes/js/tinymce/plugins/wordpress): wpautop on load, `<p>`
 * paragraphs, an empty line as `<p>&nbsp;</p>`, list items and `<pre>`
 * without an inner paragraph / code element, `<!--more-->` and
 * `<!--nextpage-->` comments. The marks (underline as a styled span,
 * strikethrough as `<del>`) are in extensions.js.
 *
 * - load(): wpautop, then the more / page break comments become marker
 *   elements (ProseMirror drops comments).
 * - serialize(): the editor's HTML in the legacy shape.
 * - roundTrips(): whether the editor gives back the content it was given
 *   (normalised DOM trees). When it does not, the field opens in TinyMCE.
 *
 * @since WPUF_SINCE
 */

const MARKER = /<!--\s*(more|nextpage)([^>]*?)-->/gi;

/** WordPress' autop when its `editor` script is on the page (TinyMCE's plugin runs it on every load). */
export function autop( html ) {
    const value = String( html || '' );
    const fn = window.wp && window.wp.editor && window.wp.editor.autop;

    return value.trim() && 'function' === typeof fn ? fn( value ) : value;
}

export function load( html ) {
    return autop( html ).replace( MARKER, ( match, kind, rest ) => {
        const text = String( rest || '' ).trim();

        return `<hr data-wpuf-marker="${ kind.toLowerCase() }"${ text ? ` data-wpuf-marker-text="${ text.replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ) }"` : '' }>`;
    } );
}

/** TipTap writes `<li><p>text</p><ul>…</ul></li>`; TinyMCE stored `<li>text<ul>…</ul></li>`. */
const unwrapListItem = ( li ) => {
    const kids = Array.from( li.childNodes ).filter( ( node ) => ! ( 3 === node.nodeType && ! node.nodeValue.trim() ) );
    const first = kids[ 0 ];

    if ( kids.length > 1 && first && 1 === first.nodeType && 'p' === first.tagName.toLowerCase() && ! first.attributes.length && kids.slice( 1 ).every( ( node ) => 1 === node.nodeType && [ 'ul', 'ol' ].includes( node.tagName.toLowerCase() ) ) ) {
        while ( first.firstChild ) {
            li.insertBefore( first.firstChild, first );
        }

        first.remove();
    }
};

const unwrapSingle = ( parent, childTag ) => {
    const kids = Array.from( parent.childNodes ).filter( ( node ) => ! ( 3 === node.nodeType && ! node.nodeValue.trim() ) );

    if ( 1 === kids.length && 1 === kids[ 0 ].nodeType && kids[ 0 ].tagName.toLowerCase() === childTag && ! kids[ 0 ].attributes.length ) {
        const only = kids[ 0 ];

        while ( only.firstChild ) {
            parent.insertBefore( only.firstChild, only );
        }

        only.remove();
    }
};

/** The editor's HTML as the legacy form stored it. */
export function serialize( html ) {
    const doc = new window.DOMParser().parseFromString( `<body>${ String( html || '' ) }</body>`, 'text/html' );
    const body = doc.body;

    body.querySelectorAll( '[data-wpuf-style]' ).forEach( ( element ) => {
        element.setAttribute( 'style', element.getAttribute( 'data-wpuf-style' ) );
        element.removeAttribute( 'data-wpuf-style' );
    } );

    // WordPress' emoji script may have swapped a character for an image (TinyMCE's wpemoji plugin undoes it too).
    body.querySelectorAll( 'img.emoji[alt], img.wp-smiley[alt]' ).forEach( ( img ) => img.replaceWith( doc.createTextNode( img.getAttribute( 'alt' ) ) ) );

    body.querySelectorAll( 'li' ).forEach( ( li ) => {
        unwrapSingle( li, 'p' );
        unwrapListItem( li );
    } );
    body.querySelectorAll( 'pre' ).forEach( ( pre ) => unwrapSingle( pre, 'code' ) );

    body.querySelectorAll( 'p' ).forEach( ( p ) => {
        if ( ! p.childNodes.length ) {
            p.innerHTML = '&nbsp;';
        }
    } );

    body.querySelectorAll( 'hr[data-wpuf-marker]' ).forEach( ( hr ) => {
        const text = hr.getAttribute( 'data-wpuf-marker-text' );
        hr.replaceWith( doc.createComment( `${ hr.getAttribute( 'data-wpuf-marker' ) }${ text ? ' ' + text : '' }` ) );
    } );

    const blocks = Array.from( body.childNodes ).map( ( node ) => {
        if ( 8 === node.nodeType ) {
            return `<!--${ node.nodeValue }-->`;
        }

        return 1 === node.nodeType ? node.outerHTML : node.nodeValue;
    } );
    const out = blocks.join( '\n' ).trim();

    // A document with only an empty paragraph is no content (the legacy form posted '').
    return /^<p>(&nbsp;|\s|<br\s*\/?>)*<\/p>$/.test( out ) ? '' : out;
}

/** Whether the content has an HTML comment the editor cannot keep (block editor markup). */
export function hasForeignComments( html ) {
    return /<!--(?!\s*(?:more|nextpage)\b)/i.test( String( html || '' ) );
}

const ALIAS = { b: 'strong', i: 'em', strike: 'del', s: 'del' };
const RAW_INLINE = [ 'iframe', 'video', 'audio', 'svg', 'embed', 'object', 'picture', 'canvas', 'input', 'button', 'select', 'textarea', 'script', 'noscript', 'math', 'map' ];

function styleText( value ) {
    return String( value || '' )
        .split( ';' )
        .map( ( part ) => part.trim() )
        .filter( Boolean )
        .map( ( part ) => {
            const index = part.indexOf( ':' );

            return index < 0 ? part.toLowerCase() : `${ part.slice( 0, index ).trim().toLowerCase() }:${ part.slice( index + 1 ).trim().replace( /\s+/g, ' ' ).toLowerCase() }`;
        } )
        .sort()
        .join( ';' );
}

/** A canonical string of a DOM tree: tag aliases, sorted attributes, collapsed whitespace, editor wrappers unwrapped. */
function canon( node ) {
    if ( 3 === node.nodeType ) {
        return node.nodeValue.replace( /[\s ]+/g, ' ' );
    }

    if ( 8 === node.nodeType ) {
        return `<!--${ node.nodeValue.trim() }-->`;
    }

    if ( 1 !== node.nodeType ) {
        return '';
    }

    let tag = node.tagName.toLowerCase();
    tag = ALIAS[ tag ] || tag;
    let list = Array.from( node.attributes ).map( ( attr ) => [ attr.name, attr.value ] );

    // TinyMCE stores underline as a styled span; an old `<u>` is the same mark.
    if ( 'u' === tag && ! list.length ) {
        tag = 'span';
        list = [ [ 'style', 'text-decoration: underline' ] ];
    }

    const attrs = list
        .map( ( [ name, value ] ) => [ name.toLowerCase(), 'style' === name.toLowerCase() ? styleText( value ) : value.trim() ] )
        .filter( ( [ name, value ] ) => ! ( [ 'style', 'class' ].includes( name ) && '' === value ) )
        .sort( ( a, b ) => ( a[ 0 ] < b[ 0 ] ? -1 : 1 ) )
        .map( ( [ name, value ] ) => `${ name }="${ value }"` )
        .join( ' ' );

    const inner = Array.from( node.childNodes ).map( canon ).join( '' ).replace( /\s+/g, ' ' ).trim();

    return `<${ tag }${ attrs ? ' ' + attrs : '' }>${ inner }</${ tag }>`;
}

function tree( html ) {
    const doc = new window.DOMParser().parseFromString( `<body>${ String( html || '' ) }</body>`, 'text/html' );
    const body = doc.body;

    body.querySelectorAll( 'li' ).forEach( ( li ) => {
        unwrapSingle( li, 'p' );
        unwrapListItem( li );
    } );
    body.querySelectorAll( 'pre' ).forEach( ( pre ) => unwrapSingle( pre, 'code' ) );

    body.querySelectorAll( 'p' ).forEach( ( p ) => {
        const elements = Array.from( p.children );
        const text = p.textContent.replace( /[\s ]/g, '' );

        // Empty paragraphs carry nothing; a paragraph around a lone comment or embed is autop's wrapper.
        if ( ! p.attributes.length && ! text && ! elements.length && ! Array.from( p.childNodes ).some( ( node ) => 8 === node.nodeType ) ) {
            p.remove();

            return;
        }

        const lone = Array.from( p.childNodes ).filter( ( node ) => ! ( 3 === node.nodeType && ! node.nodeValue.trim() ) );

        if ( ! p.attributes.length && 1 === lone.length && ( 8 === lone[ 0 ].nodeType || ( 1 === lone[ 0 ].nodeType && RAW_INLINE.includes( lone[ 0 ].tagName.toLowerCase() ) ) ) ) {
            p.replaceWith( lone[ 0 ] );
        }
    } );

    return Array.from( body.childNodes ).map( canon ).join( '' ).replace( /\s+/g, ' ' ).replace( />\s+</g, '><' ).replace( /<br><\/br> /g, '<br></br>' ).replace( / ?(<\/?(?:ul|ol|li|p|div|table|thead|tbody|tfoot|tr|td|th|blockquote|h[1-6]|pre|figure|hr)[ >]) ?/g, '$1' ).trim();
}

/** The two compared trees (for `window.wpufEditorDebug`). */
export function explainRoundTrip( stored, editorHtml ) {
    return { stored: tree( autop( stored ) ), editor: tree( serialize( editorHtml ) ) };
}

/** Whether `stored` (as loaded) comes back from the editor as the same content. */
export function roundTrips( stored, editorHtml ) {
    const pair = explainRoundTrip( stored, editorHtml );

    return pair.stored === pair.editor;
}
