/**
 * The `[wpuf-login]` reset password screen (`?action=rp`, from the e-mail
 * link), built like the registration form and sent to
 * `wpuf/v1/login/reset-password` (LoginController →
 * Simple_Login::apply_reset(): the form nonce, the reset key,
 * `validate_password_reset`, `wpuf_customer_reset_password`). On success
 * the browser goes to the login form with the "password reset" notice. A
 * bad or expired link never reaches this form: the server shows the lost
 * password form with the notice instead.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ServerHtml, api } from '@wpuf/frontend-kit';

import { LoginFrame, PasswordRow, SubmitRow, go, proLogin, referer, textsOf, useLoginSubmit } from './parts';

export default function ResetPasswordForm( { data, layout = '' } ) {
    const pro = proLogin( data );
    const texts = textsOf( data );
    const [ fieldErrors, setFieldErrors ] = useState( {} );
    const { busy, errors, setErrors, submit } = useLoginSubmit( api.resetPassword, go );
    const pass1Ref = useRef( null );
    const pass2Ref = useRef( null );

    const onSubmit = ( form ) => {
        // The checks apply_reset() runs, with its messages.
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

        setFieldErrors( next );

        if ( Object.keys( next ).length ) {
            setErrors( [] );
            ( next.pass1 ? pass1Ref : pass2Ref ).current.focus();

            return;
        }

        submit( form );
    };

    const clear = ( name ) => () => fieldErrors[ name ] && setFieldErrors( ( current ) => ( { ...current, [ name ]: '' } ) );
    // Pro's layout 7 shows the password help under the first password input.
    const help = 'layout7' === layout ? ( ( pro && pro.texts && pro.texts.passwordHelp ) || __( 'Please enter your password', 'wp-user-frontend' ) ) : '';

    return (
        <LoginFrame id="resetpasswordform" name="resetpasswordform" data={ data } errors={ errors } busy={ busy } onSubmit={ onSubmit }>
            <PasswordRow
                field={ { name: 'pass1', label: texts.pass1Label, help } }
                id="wpuf-pass1"
                inputRef={ pass1Ref }
                autoComplete="new-password"
                error={ fieldErrors.pass1 }
                onInput={ clear( 'pass1' ) }
            />
            <PasswordRow
                field={ { name: 'pass2', label: texts.pass2Label } }
                id="wpuf-pass2"
                inputRef={ pass2Ref }
                autoComplete="new-password"
                error={ fieldErrors.pass2 }
                onInput={ clear( 'pass2' ) }
            />

            { data.hook && (
                <li className="wpuf-el wpuf-login-hook">
                    <ServerHtml html={ data.hook } slug="resetpassword_form" className="wpuf-slot-inline" />
                </li>
            ) }

            <SubmitRow text={ texts.buttonText } busyText={ __( 'Saving…', 'wp-user-frontend' ) } busy={ busy }>
                <input type="hidden" name="key" value={ data.key || '' } />
                <input type="hidden" name="login" id="user_login" value={ data.login || '' } />
                <input type="hidden" name="wpuf_reset_password" value="true" />
                <input type="hidden" id="_wpnonce" name="_wpnonce" value={ data.nonce } />
                <input type="hidden" name="_wp_http_referer" value={ referer() } />
            </SubmitRow>
        </LoginFrame>
    );
}
