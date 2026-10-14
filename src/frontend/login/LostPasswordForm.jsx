/**
 * The `[wpuf-login]` lost password screen (`?action=lostpassword`) in React,
 * with the classic template's markup (templates/lost-pass-form.php: ids,
 * names, classes), so the free and Pro login sheets style it as before. It
 * posts the same fields to the same URL: Simple_Login::process_reset_password()
 * sends the e-mail, redirects and reports errors exactly as for the classic
 * form. What the template printed through hooks (`lostpassword_form`, the
 * notices, the action links) comes captured in the boot data.
 *
 * Added on top of the classic form: the required check before posting (the
 * server's own message) and the post lock with a spinner in the button.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ServerHtml, cx } from '@wpuf/frontend-kit';

import { FieldError, SubmitButton, proLogin, referer, usePostLock, wrapActionLinks } from './parts';

export default function LostPasswordForm( { data } ) {
    const pro = proLogin( data );
    const texts = data.texts || {};
    const [ error, setError ] = useState( '' );
    const [ busy, setBusy ] = usePostLock();
    const linksRef = useRef( null );
    const userRef = useRef( null );

    useEffect( () => {
        if ( pro && linksRef.current ) {
            wrapActionLinks( linksRef.current );
        }
    }, [ pro ] );

    const onSubmit = ( event ) => {
        if ( busy ) {
            event.preventDefault();

            return;
        }

        // The check retrieve_password() runs first, with its message.
        if ( ! userRef.current.value.trim() ) {
            event.preventDefault();
            setError( __( 'Enter a username or e-mail address.', 'wp-user-frontend' ) );
            userRef.current.focus();

            return;
        }

        setError( '' );
        setBusy( true );
    };

    return (
        <>
            { data.notices && <ServerHtml html={ data.notices } slug="lostpassword_notices" className="wpuf-slot-inline" /> }

            <form
                name="lostpasswordform"
                id="lostpasswordform"
                className={ cx( busy && 'is-submitting' ) || undefined }
                method="post"
                noValidate
                aria-busy={ busy ? 'true' : undefined }
                onSubmit={ onSubmit }
            >
                <p>
                    <label htmlFor="wpuf-user_login">{ texts.usernameLabel }</label>
                    <input
                        ref={ userRef }
                        type="text"
                        name="user_login"
                        id="wpuf-user_login"
                        className="input"
                        size="20"
                        autoComplete="username"
                        autoCapitalize="none"
                        spellCheck="false"
                        readOnly={ busy }
                        aria-invalid={ error ? 'true' : undefined }
                        aria-describedby={ error ? 'wpuf-login-error-user_login' : undefined }
                        onInput={ () => error && setError( '' ) }
                    />
                    <FieldError id="wpuf-login-error-user_login" message={ error } />
                </p>

                { data.hook && <ServerHtml html={ data.hook } slug="lostpassword_form" className="wpuf-slot-inline" /> }

                <p className="submit">
                    <SubmitButton text={ texts.buttonText } busyText={ __( 'Sending…', 'wp-user-frontend' ) } busy={ busy } />
                    <input type="hidden" name="redirect_to" value={ data.redirect_to || '' } />
                    <input type="hidden" name="wpuf_reset_password" value="true" />
                    <input type="hidden" name="action" value="lostpassword" />
                    <input type="hidden" id="_wpnonce" name="_wpnonce" value={ data.nonce } />
                    <input type="hidden" name="_wp_http_referer" value={ referer() } />
                </p>
            </form>

            <div className="wpuf-action-links" ref={ linksRef }>
                { data.links && <ServerHtml html={ data.links } slug="lostpassword_links" className="wpuf-slot-inline" /> }
            </div>
        </>
    );
}
