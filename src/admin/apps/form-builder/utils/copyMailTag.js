import { __ } from '@wordpress/i18n';

// "Copied!" tooltip over the clicked tag (develop showed a Bootstrap tooltip).
const COPIED_CLASSES = [
    'relative',
    'after:content-[attr(data-original-title)]',
    'after:absolute',
    'after:bottom-full',
    'after:left-1/2',
    'after:-translate-x-1/2',
    'after:mb-1',
    'after:px-2',
    'after:py-1',
    'after:rounded',
    'after:bg-gray-900',
    'after:text-white',
    'after:text-xs',
    'after:whitespace-nowrap',
];

/**
 * Click handler for a settings help block: a click on a mail template tag
 * (`span[data-clipboard-text]`, e.g. `{post_title}` under the notification
 * e-mail) copies the tag and shows "Copied!" for a second, as develop's
 * Clipboard.js binding on `.wpuf-long-help span[data-clipboard-text]` did.
 *
 * @since WPUF_SINCE
 *
 * @param {MouseEvent} event Click event.
 */
export default function copyMailTag( event ) {
    const tag = event.target && event.target.closest ? event.target.closest( 'span[data-clipboard-text]' ) : null;

    if ( ! tag ) {
        return;
    }

    const text = tag.getAttribute( 'data-clipboard-text' ) || '';

    if ( window.navigator.clipboard ) {
        window.navigator.clipboard.writeText( text ).catch( () => {} );
    }

    tag.setAttribute( 'data-original-title', __( 'Copied!', 'wp-user-frontend' ) );
    tag.classList.add( ...COPIED_CLASSES );

    setTimeout( () => {
        tag.removeAttribute( 'data-original-title' );
        tag.classList.remove( ...COPIED_CLASSES );
    }, 1000 );
}
