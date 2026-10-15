/**
 * reCAPTCHA (v2 checkbox, invisible) and Cloudflare Turnstile: the widget
 * script loads on demand with the site key from the schema, the token is
 * posted under the name the server validates (`g-recaptcha-response`,
 * `cf-turnstile-response`).
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const loadScript = ( src, ready ) => new Promise( ( resolve ) => {
    if ( ready() ) {
        resolve();

        return;
    }

    let script = document.querySelector( `script[src^="${ src }"]` );

    if ( ! script ) {
        script = document.createElement( 'script' );
        script.src = src;
        script.async = true;
        script.defer = true;
        document.head.appendChild( script );
    }

    const wait = () => ( ready() ? resolve() : window.setTimeout( wait, 50 ) );

    wait();
} );

/**
 * reCAPTCHA as Form_Field_reCaptcha prints it: `li.wpuf-el.recaptcha`
 * (no label and no spacing when invisible), "reCaptcha API key is
 * missing." without a key, else `div.wpuf-fields.wpuf_<name>_<form> >
 * div#recaptcha.g-recaptcha` rendered explicitly; the token is the value.
 */
export function RecaptchaField( { field, onChange, formId, schema } ) {
    const ref = useRef( null );
    const key = schema?.needs?.recaptcha_key || '';
    const invisible = 'invisible_recaptcha' === field.recaptcha_type;

    useEffect( () => {
        if ( ! key || ! ref.current ) {
            return;
        }

        loadScript( 'https://www.google.com/recaptcha/api.js?render=explicit', () => window.grecaptcha && window.grecaptcha.render ).then( () => {
            if ( ref.current && ! ref.current.dataset.widget ) {
                ref.current.dataset.widget = String( window.grecaptcha.render( ref.current, {
                    sitekey: key,
                    theme: field.recaptcha_theme || 'light',
                    size: invisible ? 'invisible' : 'normal',
                    callback: ( token ) => onChange( token ),
                    'expired-callback': () => onChange( '' ),
                } ) );

                if ( invisible ) {
                    window.grecaptcha.execute( Number( ref.current.dataset.widget ) );
                }
            }
        } );
    }, [ key, invisible ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // A failed submit used the token (`wpuf:captcha:reset` on the form): clear it,
    // show a fresh challenge, and for the invisible widget ask for a new token.
    useEffect( () => {
        const form = ref.current && ref.current.closest( 'form' );

        if ( ! form ) {
            return undefined;
        }

        const reset = () => {
            const widget = ref.current && ref.current.dataset.widget;

            if ( ! window.grecaptcha || undefined === widget ) {
                return;
            }

            onChange( '' );

            try {
                window.grecaptcha.reset( Number( widget ) );

                if ( invisible ) {
                    window.grecaptcha.execute( Number( widget ) );
                }
            } catch ( e ) {}
        };

        form.addEventListener( 'wpuf:captcha:reset', reset );

        return () => form.removeEventListener( 'wpuf:captcha:reset', reset );
    }, [ invisible ] ); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <li className={ `wpuf-el ${ field.name } ${ field.css || '' }`.trim() } data-label={ field.label } style={ invisible ? { margin: 0, padding: 0 } : undefined }>
            { ! invisible && (
                <div className="wpuf-label">
                    <label htmlFor="recaptcha_0">{ field.label }</label>
                </div>
            ) }
            { key ? (
                <div className={ `wpuf-fields wpuf_${ field.name }_${ formId }` }>
                    <div id="recaptcha" ref={ ref } className="g-recaptcha" data-theme={ field.recaptcha_theme || 'light' } />
                </div>
            ) : __( 'reCaptcha API key is missing.', 'wp-user-frontend' ) }
        </li>
    );
}

/**
 * Cloudflare Turnstile as Form_Field_Cloudflare_Turnstile prints it:
 * nothing without the setting and a key, else `li.wpuf-el >
 * div#wpuf-turnstile.wpuf-fields.wpuf-turnstile` (hidden when invisible).
 */
export function TurnstileField( { field, onChange, schema } ) {
    const ref = useRef( null );
    const key = schema?.needs?.turnstile_key || '';
    const action = 'non_interactive' === field.turnstile_type ? 'non-interactive' : field.turnstile_type || 'non-interactive';

    useEffect( () => {
        if ( ! key || ! ref.current ) {
            return;
        }

        loadScript( 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit', () => window.turnstile && window.turnstile.render ).then( () => {
            if ( ref.current && ! ref.current.dataset.widget ) {
                ref.current.dataset.widget = window.turnstile.render( ref.current, {
                    sitekey: key,
                    theme: field.turnstile_theme || 'light',
                    size: field.turnstile_size || 'normal',
                    callback: ( token ) => onChange( token ),
                    'expired-callback': () => onChange( '' ),
                } );
            }
        } );
    }, [ key ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // A failed submit used the token: a fresh challenge (Turnstile runs it again itself).
    useEffect( () => {
        const form = ref.current && ref.current.closest( 'form' );

        if ( ! form ) {
            return undefined;
        }

        const reset = () => {
            const widget = ref.current && ref.current.dataset.widget;

            if ( ! window.turnstile || ! widget ) {
                return;
            }

            onChange( '' );

            try {
                window.turnstile.reset( widget );
            } catch ( e ) {}
        };

        form.addEventListener( 'wpuf:captcha:reset', reset );

        return () => form.removeEventListener( 'wpuf:captcha:reset', reset );
    }, [ key ] ); // eslint-disable-line react-hooks/exhaustive-deps

    if ( ! key ) {
        return null;
    }

    return (
        <li className={ `wpuf-el ${ field.name } ${ field.css || '' }`.trim() } data-label={ field.label }>
            <div ref={ ref } id="wpuf-turnstile" className="wpuf-fields wpuf-turnstile" style={ 'invisible' === action ? { display: 'none' } : undefined } />
        </li>
    );
}
