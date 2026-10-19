/**
 * What the login, lost password and reset password forms share. They are
 * built like the registration form: the kit's form markup
 * (`form.wpuf-form-add > ul.wpuf-form`, kit Field rows, the submit row), so
 * forms.css gives them the same look, and they send their fields to the
 * REST routes (LoginController), which run Simple_Login's own checks and
 * hooks. Pro's login settings come in the boot data (`pro`: layout, texts,
 * colours).
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Field, ServerHtml, cx, icons } from '@wpuf/frontend-kit';

const { Eye, EyeOff } = icons;

/**
 * Pro's layout, custom texts and colours, if any: in the boot data (`pro`,
 * Pro's Login_Form_Settings::add_boot_data()), or from an older Pro the
 * `wpufLoginForm` its jQuery script localizes.
 *
 * @param {Object} data Boot data.
 * @return {Object|null} `{ layout, texts, style }`
 */
export const proLogin = ( data ) => ( data && data.pro ) || ( 'undefined' !== typeof window && window.wpufLoginForm ) || null;

/**
 * The texts of a screen: the free defaults, then, on the login screen only,
 * each text Pro has a value for (Pro customises the login form; the lost and
 * reset password screens keep their own labels, as Pro's classic script did).
 *
 * @param {Object} data Boot data.
 * @return {Object} Texts.
 */
export function textsOf( data ) {
    const texts = { ...( data.texts || {} ) };

    if ( data.view && 'login' !== data.view ) {
        return texts;
    }

    const pro = proLogin( data );

    Object.entries( ( pro && pro.texts ) || {} ).forEach( ( [ key, value ] ) => {
        if ( value ) {
            texts[ key ] = value;
        }
    } );

    return texts;
}

/** The fields of a form as the classic post sent them (checked boxes only, every hidden input). */
const fieldsOf = ( form ) => {
    const out = {};

    new window.FormData( form ).forEach( ( value, name ) => {
        if ( 'string' !== typeof value ) {
            return;
        }

        if ( name.endsWith( '[]' ) ) {
            const key = name.slice( 0, -2 );

            out[ key ] = [ ...( out[ key ] || [] ), value ];
        } else {
            out[ name ] = value;
        }
    } );

    return out;
};

/**
 * Send a login form to its REST route: the busy state, the server's
 * errors, and on failure the captchas reset (a token works once).
 *
 * @param {Function} send     api call taking the fields.
 * @param {Function} onDone   Called with the response.
 * @return {Object} `{ busy, errors, setErrors, submit( form ) }`
 */
export function useLoginSubmit( send, onDone ) {
    const [ busy, setBusy ] = useState( false );
    const [ errors, setErrors ] = useState( [] );
    const sending = useRef( false );

    const submit = async ( form ) => {
        if ( sending.current ) {
            return;
        }

        sending.current = true;
        setBusy( true );
        setErrors( [] );

        try {
            await onDone( await send( fieldsOf( form ) ) );
            sending.current = false;
            setBusy( false );
        } catch ( e ) {
            const list = e.data && Array.isArray( e.data.errors ) && e.data.errors.length ? e.data.errors : [ e.message ];

            setErrors( list );
            resetCaptchas( form );
            sending.current = false;
            setBusy( false );
        }
    };

    return { busy, errors, setErrors, submit };
}

/** A failed attempt used the captcha token: show a fresh challenge. */
function resetCaptchas( form ) {
    if ( window.grecaptcha && 'function' === typeof window.grecaptcha.reset && form.querySelector( '.g-recaptcha' ) ) {
        try {
            window.grecaptcha.reset();
        } catch ( e ) {}
    }

    if ( window.turnstile && 'function' === typeof window.turnstile.reset && form.querySelector( '.wpuf-turnstile, .cf-turnstile' ) ) {
        try {
            window.turnstile.reset();
        } catch ( e ) {}
    }
}

/** Go to the server's next page (it is on this site: the routes validate it). */
export const go = ( response ) => {
    window.location.assign( response && response.redirect ? response.redirect : window.location.href );

    // Keep the busy state until the browser leaves the page.
    return new Promise( () => {} );
};

/**
 * The form frame: Pro's heading (the `login_message` filter) and the
 * notices inside the card, then the rows.
 *
 * @param {Object}   props
 * @param {string}   props.id       Form id (loginform, lostpasswordform, resetpasswordform).
 * @param {string}   props.name     Form name.
 * @param {Object}   props.data     Boot data.
 * @param {string[]} props.errors   The server's errors of the last attempt.
 * @param {string}   [props.notice] A message of this page (lost password sent).
 * @param {boolean}  props.busy     Sending.
 * @param {Function} props.onSubmit Called with the form element.
 */
export function LoginFrame( { id, name, data, errors, notice = '', busy, onSubmit, children } ) {
    const showServer = ! errors.length && ! notice && data.notices;

    return (
        <form
            name={ name }
            id={ id }
            // Pro's design (the root is `wpuf-login-styled`): the registration form's
            // classes, so forms.css styles it; free follows the theme.
            className={ cx( data.styled && 'wpuf-form-add wpuf-form-layout1', 'wpuf-login-form', busy && 'is-submitting' ) }
            method="post"
            noValidate
            aria-busy={ busy ? 'true' : undefined }
            onSubmit={ ( event ) => {
                event.preventDefault();
                onSubmit( event.currentTarget );
            } }
        >
            { data.message && <ServerHtml html={ data.message } slug="login_message" className="wpuf-login-message" /> }

            <div className="wpuf-login-notices" aria-live="polite">
                { showServer && <ServerHtml html={ data.notices } slug={ `${ id }_notices` } className="wpuf-slot-inline" /> }
                { notice && <div className="wpuf-message" role="status">{ notice }</div> }
                { errors.length > 0 && (
                    <div className="wpuf-error" role="alert">
                        <div className="wpuf-login-error-lines">
                            { errors.map( ( error, index ) => <ServerHtml key={ index } html={ error } slug={ `${ id }_error` } className="wpuf-login-error-line" /> ) }
                        </div>
                    </div>
                ) }
            </div>

            <ul className={ cx( 'wpuf-login-fields', data.styled && 'wpuf-form form-label-above' ) } inert={ busy ? '' : undefined }>
                { children }
            </ul>
        </form>
    );
}

/**
 * A text input row (kit Field).
 *
 * @param {Object} props
 * @param {Object} props.field   `{ name, label, help, placeholder, required }`.
 * @param {string} props.id      Input id.
 * @param {string} [props.error] Field message.
 */
export function TextRow( { field, id, error, inputRef, autoComplete, onInput, type = 'text' } ) {
    return (
        <Field field={ { required: 'yes', ...field } } id={ id } error={ error }>
            <input
                ref={ inputRef }
                type={ type }
                name={ field.name }
                id={ id }
                className="textfield input"
                size="20"
                autoComplete={ autoComplete }
                autoCapitalize="none"
                spellCheck="false"
                placeholder={ field.placeholder || undefined }
                aria-required="true"
                aria-invalid={ error ? 'true' : undefined }
                onInput={ onInput }
            />
        </Field>
    );
}

/**
 * A password row (kit Field) with the show / hide button over the input's end.
 *
 * @param {Object} props
 * @param {Object} props.field   `{ name, label, help, placeholder }`.
 * @param {string} props.id      Input id.
 * @param {string} [props.error] Field message.
 */
export function PasswordRow( { field, id, error, inputRef, autoComplete, onInput } ) {
    const [ shown, setShown ] = useState( false );

    return (
        <Field field={ { required: 'yes', ...field } } id={ id } error={ error }>
            <span className="wpuf-login-password">
                <input
                    ref={ inputRef }
                    type={ shown ? 'text' : 'password' }
                    name={ field.name }
                    id={ id }
                    className="password input"
                    size="20"
                    autoComplete={ autoComplete }
                    placeholder={ field.placeholder || undefined }
                    aria-required="true"
                    aria-invalid={ error ? 'true' : undefined }
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
        </Field>
    );
}

/**
 * The submit row (the registration form's `li.wpuf-submit`): one wide
 * button with a spinner while sending. `wp-submit` stays its id and name.
 *
 * @param {Object}  props
 * @param {string}  props.text     Label.
 * @param {string}  props.busyText Label while sending.
 * @param {boolean} props.busy     Sending.
 */
export function SubmitRow( { text, busyText, busy, children } ) {
    return (
        <li className="wpuf-submit">
            <button
                type="submit"
                name="wp-submit"
                id="wp-submit"
                value={ text }
                className={ cx( 'wpuf-submit-button', busy && 'is-busy' ) }
                aria-disabled={ busy ? 'true' : undefined }
            >
                { busy && <span className="wpuf-login-spinner" aria-hidden="true" /> }
                { busy ? busyText : text }
            </button>
            { children }
        </li>
    );
}

/** The links under the form (register, log in, lost password: the action links). */
export function ActionLinks( { html, slug } ) {
    return html ? <ServerHtml html={ html } slug={ slug } className="wpuf-action-links" /> : null;
}

/** The page the form is on, as wp_nonce_field()'s `_wp_http_referer`. */
export const referer = () => window.location.pathname + window.location.search;
