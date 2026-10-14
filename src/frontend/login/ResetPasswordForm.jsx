/**
 * The `[wpuf-login]` reset password screen (`?action=rp&key=…&login=…`) in
 * React, with the classic template's ids, names and classes
 * (templates/reset-pass-form.php), so the login sheets style it as before.
 * It posts the same fields to the same URL (the key and login stay in it):
 * Simple_Login::process_reset_password() checks the key, saves the password
 * and redirects exactly as for the classic form. `resetpassword_form` and
 * the notices come captured in the boot data.
 *
 * The classic template put a `div` inside each `p` (the browser closed the
 * `p` before it) and an eye image per input; here each input sits in its
 * `p` with the login form's show / hide button. Added: the server's
 * required and match checks before posting, and the post lock.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ServerHtml, cx } from '@wpuf/frontend-kit';

import { FieldError, PasswordInput, SubmitButton, proLogin, referer, usePostLock } from './parts';

export default function ResetPasswordForm( { data, layout = '' } ) {
    const pro = proLogin( data );
    const texts = data.texts || {};
    const [ errors, setErrors ] = useState( {} );
    const [ busy, setBusy ] = usePostLock();
    const pass1Ref = useRef( null );
    const pass2Ref = useRef( null );

    const onSubmit = ( event ) => {
        if ( busy ) {
            event.preventDefault();

            return;
        }

        // The checks process_reset_password() runs, with its messages.
        const next = {};
        const pass1 = pass1Ref.current.value;
        const pass2 = pass2Ref.current.value;

        if ( ! pass1 ) {
            next.pass1 = __( 'Please enter your password.', 'wp-user-frontend' );
        }

        if ( ! pass2 ) {
            next.pass2 = __( 'Please enter your password.', 'wp-user-frontend' );
        } else if ( pass1 && pass1 !== pass2 ) {
            next.pass2 = __( 'Passwords do not match.', 'wp-user-frontend' );
        }

        setErrors( next );

        if ( Object.keys( next ).length ) {
            event.preventDefault();
            ( next.pass1 ? pass1Ref : pass2Ref ).current.focus();

            return;
        }

        setBusy( true );
    };

    const clear = ( name ) => () => errors[ name ] && setErrors( ( current ) => ( { ...current, [ name ]: '' } ) );
    // Pro's layout 7 script put the password help under the first password input.
    const help = 'layout7' === layout
        ? ( ( pro && pro.texts && pro.texts.passwordHelp ) || __( 'Please enter your password', 'wp-user-frontend' ) )
        : '';

    return (
        <>
            { data.notices && <ServerHtml html={ data.notices } slug="resetpassword_notices" className="wpuf-slot-inline" /> }

            <form
                name="resetpasswordform"
                id="resetpasswordform"
                className={ cx( busy && 'is-submitting' ) || undefined }
                method="post"
                noValidate
                aria-busy={ busy ? 'true' : undefined }
                onSubmit={ onSubmit }
            >
                <p>
                    <label htmlFor="wpuf-pass1">{ texts.pass1Label }</label>
                    <PasswordInput
                        inputRef={ pass1Ref }
                        name="pass1"
                        id="wpuf-pass1"
                        autoComplete="new-password"
                        busy={ busy }
                        error={ errors.pass1 }
                        errorId="wpuf-login-error-pass1"
                        onInput={ clear( 'pass1' ) }
                    />
                    { help && <span className="wpuf-help">{ help }</span> }
                    <FieldError id="wpuf-login-error-pass1" message={ errors.pass1 } />
                </p>

                <p>
                    <label htmlFor="wpuf-pass2">{ texts.pass2Label }</label>
                    <PasswordInput
                        inputRef={ pass2Ref }
                        name="pass2"
                        id="wpuf-pass2"
                        autoComplete="new-password"
                        busy={ busy }
                        error={ errors.pass2 }
                        errorId="wpuf-login-error-pass2"
                        onInput={ clear( 'pass2' ) }
                    />
                    <FieldError id="wpuf-login-error-pass2" message={ errors.pass2 } />
                </p>

                { data.hook && <ServerHtml html={ data.hook } slug="resetpassword_form" className="wpuf-slot-inline" /> }

                <p className="submit">
                    <SubmitButton text={ texts.buttonText } busyText={ __( 'Saving…', 'wp-user-frontend' ) } busy={ busy } />
                    <input type="hidden" name="key" value={ data.key || '' } />
                    <input type="hidden" name="login" id="user_login" value={ data.login || '' } />
                    <input type="hidden" name="wpuf_reset_password" value="true" />
                </p>

                <input type="hidden" id="_wpnonce" name="_wpnonce" value={ data.nonce } />
                <input type="hidden" name="_wp_http_referer" value={ referer() } />
            </form>
        </>
    );
}
