/**
 * What the login, lost password and reset password forms share: Pro's
 * layout data, the action-link wrappers, the post lock, the password input
 * with its show / hide button and the field message.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { cx, icons } from '@wpuf/frontend-kit';

const { AlertCircle, Eye, EyeOff } = icons;

/**
 * Pro's layout and custom texts, if any: in the boot data (`pro`, Pro's
 * Login_Form_Settings::add_react_data()), or from an older Pro the
 * `wpufLoginForm` its jQuery script localizes.
 *
 * @param {Object} data Boot data.
 * @return {Object|null} `{ layout, texts }`
 */
export const proLogin = ( data ) => ( data && data.pro ) || ( 'undefined' !== typeof window && window.wpufLoginForm ) || null;

/** Pro's wrapActionLinks(): action links outside the form sit in `span.wpuf-lost-password-wrapper`. */
export function wrapActionLinks( root ) {
    root.querySelectorAll( 'a' ).forEach( ( link ) => {
        if ( link.closest( 'form' ) || link.parentElement.classList.contains( 'wpuf-lost-password-wrapper' ) ) {
            return;
        }

        if ( /lostpassword|action=login|\/login\/|register/.test( link.href ) ) {
            const wrapper = document.createElement( 'span' );

            wrapper.className = 'wpuf-lost-password-wrapper';
            link.parentNode.insertBefore( wrapper, link );
            wrapper.appendChild( link );
        }
    } );
}

/**
 * The lock while the browser posts the form (a full page request): set on
 * submit, released when the page comes back from the browser cache or,
 * if the browser never left the page, after 20 s.
 *
 * @return {Array} [ busy, setBusy ]
 */
export function usePostLock() {
    const [ busy, setBusy ] = useState( false );

    // Back from the browser cache (bfcache) after a redirect: unlock.
    useEffect( () => {
        const unlock = () => setBusy( false );

        window.addEventListener( 'pageshow', unlock );

        return () => window.removeEventListener( 'pageshow', unlock );
    }, [] );

    // Still on this page long after posting (the browser stopped or cancelled
    // the request): unlock, so the form never stays stuck.
    useEffect( () => {
        if ( ! busy ) {
            return undefined;
        }

        const timer = window.setTimeout( () => setBusy( false ), 20000 );

        return () => window.clearTimeout( timer );
    }, [ busy ] );

    return [ busy, setBusy ];
}

/**
 * The React forms' field message (kit Field: the alert icon and the text).
 *
 * @param {Object} props
 * @param {string} props.id      Element id (the input's aria-describedby).
 * @param {string} props.message Message, nothing when empty.
 */
export function FieldError( { id, message } ) {
    if ( ! message ) {
        return null;
    }

    return (
        <span className="wpuf-error-msg" id={ id } role="alert" aria-live="assertive">
            <AlertCircle aria-hidden="true" />
            { message }
        </span>
    );
}

/**
 * A password input with the show / hide button over its end (login.css).
 *
 * @param {Object}   props
 * @param {Object}   props.inputRef     Ref of the input.
 * @param {string}   props.name         Field name.
 * @param {string}   props.id           Input id.
 * @param {string}   props.autoComplete Autocomplete hint.
 * @param {string}   [props.placeholder]
 * @param {boolean}  props.busy         Posting (the input is read only).
 * @param {string}   [props.error]      Field message (marks the input invalid).
 * @param {string}   props.errorId      Id of the field message.
 * @param {Function} props.onInput
 */
export function PasswordInput( { inputRef, name, id, autoComplete, placeholder, busy, error, errorId, onInput } ) {
    const [ shown, setShown ] = useState( false );

    return (
        <span className="wpuf-login-password">
            <input
                ref={ inputRef }
                type={ shown ? 'text' : 'password' }
                name={ name }
                id={ id }
                className="input"
                size="20"
                autoComplete={ autoComplete }
                placeholder={ placeholder || undefined }
                readOnly={ busy }
                aria-invalid={ error ? 'true' : undefined }
                aria-describedby={ error ? errorId : undefined }
                onInput={ onInput }
            />
            <button
                type="button"
                className="wpuf-login-password__toggle"
                aria-label={ shown ? __( 'Hide password', 'wp-user-frontend' ) : __( 'Show password', 'wp-user-frontend' ) }
                aria-pressed={ shown }
                aria-controls={ id }
                onClick={ () => setShown( ( current ) => ! current ) }
            >
                { shown ? <EyeOff aria-hidden="true" /> : <Eye aria-hidden="true" /> }
            </button>
        </span>
    );
}

/**
 * The submit button: a button (Pro's layout sheets style input and button
 * alike; it posts the same wp-submit field), so the spinner sits inside it
 * next to the label and the centred button does not move while the page
 * posts. Pro's login script renames `input[type=submit]`, not a button.
 *
 * @param {Object}  props
 * @param {string}  props.text     Label.
 * @param {string}  props.busyText Label while posting.
 * @param {boolean} props.busy     Posting.
 */
export function SubmitButton( { text, busyText, busy } ) {
    return (
        <button
            type="submit"
            name="wp-submit"
            id="wp-submit"
            value={ text }
            className={ cx( busy && 'is-busy' ) || undefined }
            aria-disabled={ busy ? 'true' : undefined }
        >
            { busy && <span className="wpuf-login-spinner" aria-hidden="true" /> }
            { busy ? busyText : text }
        </button>
    );
}

/** The page the form posts from, as wp_nonce_field()'s `_wp_http_referer`. */
export const referer = () => window.location.pathname + window.location.search;
