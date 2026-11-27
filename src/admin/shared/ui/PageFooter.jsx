/**
 * Page footer of the WPUF admin screens (FlyHR AppFooter, owner 2026-10-04,
 * design.md D26): a white band at the bottom of the gray page with the WP
 * User Frontend wordmark centered (20px, 70% opacity). Spans the screen
 * root's 20px side padding like PageHeader, 24px below the content (FlyHR's
 * panel padding); `mt-auto` keeps it at the bottom of a PageShell. WordPress's own #wpfooter is hidden on these screens.
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';
import { cn } from '@wedevs/plugin-ui';

import { useBoot } from '../hooks';

/**
 * @param {Object} props
 * @param {*}      [props.children] A small line under the logo (e.g. a classic view link).
 */
export default function PageFooter( { children, className } ) {
    const boot = useBoot();

    // The wrapper keeps FlyHR's 24px gap (its main panel's bottom padding)
    // between the content and the footer, and pushes it to the bottom. Layout
    // is inline: a screen's stylesheet only has the classes its own sources use.
    return (
        <div className={ cn( 'pt-6', className ) } style={ { marginTop: 'auto', paddingTop: '24px' } }>
            <footer
                data-wpuf-ui=""
                data-wpuf-bleed=""
                className="-ml-5 w-[calc(100%+40px)] flex flex-col flex-wrap items-center justify-center gap-2 bg-white px-5 py-6"
            >
                { boot.assetUrl && (
                    <img
                        src={ boot.assetUrl + '/images/onboarding-logo.svg' }
                        alt={ __( 'WP User Frontend', 'wp-user-frontend' ) }
                        className="h-5 w-auto opacity-70"
                        height="20"
                    />
                ) }
                { children && <p className="m-0 text-xs text-gray-500">{ children }</p> }
            </footer>
        </div>
    );
}

/**
 * Minimum height that makes an element reach the end of the page: the page is
 * as tall as the window or, when that is longer, the WordPress admin menu (a
 * long menu made the page run past `100vh` and left a gray gap below the
 * footer). Notices above the element take their part. Follows menu fold /
 * responsive changes (body class) and window resizes.
 *
 * @param {Object} ref Ref of the element.
 *
 * @return {number} Minimum height in px (0 until measured).
 */
export function usePageFillHeight( ref ) {
    const [ minHeight, setMinHeight ] = useState( 0 );

    useEffect( () => {
        const update = () => {
            if ( ! ref.current ) {
                return;
            }

            const menu = document.getElementById( 'adminmenuwrap' );
            const bar = document.getElementById( 'wpadminbar' );
            const pageHeight = Math.max( window.innerHeight, ( menu ? menu.offsetHeight : 0 ) + ( bar ? bar.offsetHeight : 0 ) );
            const top = ref.current.getBoundingClientRect().top + window.scrollY;

            setMinHeight( Math.max( 0, Math.floor( pageHeight - top ) ) );
        };

        update();
        window.addEventListener( 'resize', update );
        const observer = new window.MutationObserver( update );
        observer.observe( document.body, { attributes: true, attributeFilter: [ 'class' ] } );

        return () => {
            window.removeEventListener( 'resize', update );
            observer.disconnect();
        };
    }, [ ref ] );

    return minHeight;
}

/**
 * Full-height column for a screen (FlyHR shell): header, content, footer at
 * the bottom of the page even when the content is short. Used by every React
 * screen (forms lists, builder, subscriptions, settings).
 *
 * @param {Object} props
 * @param {*}      props.children Header, content and PageFooter.
 */
export function PageShell( { children, className } ) {
    const ref = useRef( null );
    const minHeight = usePageFillHeight( ref );

    return (
        <div ref={ ref } className={ className } style={ { display: 'flex', flexDirection: 'column', minHeight: minHeight ? `${ minHeight }px` : 'calc(100vh - 32px)' } }>
            { children }
        </div>
    );
}
