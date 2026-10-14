/**
 * Server-produced markup inside a React tree: the slots a form's printing
 * hooks produce and the account sections served as PHP output
 * (frontend-react-architecture.md 4.3, 5.4). The HTML comes from our own
 * REST routes or the boot data the server rendered, never from user input.
 * Inline scripts are re-created so they run (the browser does not run
 * scripts inserted through innerHTML), and a DOM event tells other scripts
 * the section is on the page.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef } from '@wordpress/element';

const runScripts = ( root ) => {
    root.querySelectorAll( 'script' ).forEach( ( old ) => {
        const script = document.createElement( 'script' );

        Array.from( old.attributes ).forEach( ( attribute ) => script.setAttribute( attribute.name, attribute.value ) );
        script.text = old.text;
        old.parentNode.replaceChild( script, old );
    } );
};

const inline = ( id, code ) => {
    if ( ! code || document.getElementById( id ) ) {
        return;
    }

    const script = document.createElement( 'script' );

    script.id = id;
    script.text = code;
    document.body.appendChild( script );
};

/**
 * Load the scripts and sheets a server section needs and the page lacks
 * (`assets` of GET /account/sections/{slug}), in order, the way WordPress
 * prints them: localized data, translations, inline before, the file,
 * inline after. WordPress' element ids (`{handle}-js`, `{handle}-css`) tell
 * what is on the page already.
 *
 * @param {{scripts?: Array, styles?: Array}} assets
 *
 * @return {Promise<void>}
 */
export async function loadAssets( assets = {} ) {
    ( assets.styles || [] ).forEach( ( style ) => {
        if ( style.src && ! document.getElementById( `${ style.handle }-css` ) ) {
            const link = document.createElement( 'link' );

            link.id = `${ style.handle }-css`;
            link.rel = 'stylesheet';
            link.href = style.src;
            document.head.appendChild( link );
        }

        if ( style.after && ! document.getElementById( `${ style.handle }-inline-css` ) ) {
            const tag = document.createElement( 'style' );

            tag.id = `${ style.handle }-inline-css`;
            tag.textContent = style.after;
            document.head.appendChild( tag );
        }
    } );

    for ( const script of assets.scripts || [] ) {
        const id = `${ script.handle }-js`;

        if ( document.getElementById( id ) ) {
            continue;
        }

        inline( `${ id }-extra`, script.data );
        inline( `${ id }-translations`, script.translations );
        inline( `${ id }-before`, script.before );

        if ( script.src ) {
            await new Promise( ( resolve ) => {
                const tag = document.createElement( 'script' );

                tag.id = id;
                tag.src = script.src;
                tag.async = false;
                tag.onload = resolve;
                tag.onerror = resolve;
                document.body.appendChild( tag );
            } );
        }

        inline( `${ id }-after`, script.after );
    }
}

/**
 * @param {Object} props
 * @param {string} props.html      Markup.
 * @param {string} [props.as]      Wrapper element (div by default; 'li' inside a form list).
 * @param {string} [props.slug]    Account section slug, sent with the DOM event.
 * @param {string} [props.className]
 */
export default function ServerHtml( { html = '', as: Tag = 'div', slug = '', className = '' } ) {
    const ref = useRef( null );

    useEffect( () => {
        const root = ref.current;

        if ( ! root ) {
            return;
        }

        root.innerHTML = html; // eslint-disable-line no-unsanitized/property -- server markup from our own routes.
        runScripts( root );

        if ( window.jQuery ) {
            window.jQuery( document ).trigger( 'wpuf:frontend:html', [ root, slug ] );
        }

        document.dispatchEvent( new window.CustomEvent( 'wpuf:frontend:html', { detail: { root, slug } } ) );
    }, [ html, slug ] );

    return <Tag ref={ ref } className={ className || undefined } data-wpuf-slot={ slug || undefined } />;
}
