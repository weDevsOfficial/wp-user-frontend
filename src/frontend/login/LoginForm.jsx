/**
 * The `[wpuf-login]` form, built like the registration form (kit Field
 * rows in `form.wpuf-form-add > ul.wpuf-form`, the same look) and sent to
 * `wpuf/v1/login` (LoginController), which logs in through
 * Simple_Login::sign_in(): the form nonce, the captchas, the
 * `wpuf_process_login_errors` / `authenticate` / `wpuf_login_redirect`
 * filters run as for the classic post. On success the browser goes where
 * the server says; on failure the messages show at the top of the card,
 * the captcha resets and the form stays filled in.
 *
 * Kept from the classic template: the field names and ids (`log`, `pwd`,
 * `rememberme`, `#loginform`, `#wp-submit`), the hidden fields, and what
 * hooks print (`login_message`, the captchas, `wpuf_login_form_bottom`,
 * the action links), shown in place. Pro's custom texts and layout 7 help
 * lines come in the boot data.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ServerHtml, api } from '@wpuf/frontend-kit';

import { ActionLinks, LoginFrame, PasswordRow, SubmitRow, TextRow, go, referer, textsOf, useLoginSubmit } from './parts';

export default function LoginForm( { data, layout = '' } ) {
    const texts = textsOf( data );
    const [ fieldErrors, setFieldErrors ] = useState( {} );
    const { busy, errors, setErrors, submit } = useLoginSubmit( api.logIn, go );
    const userRef = useRef( null );
    const passRef = useRef( null );
    // Pro's layout 7 shows a help line under each field.
    const help = ( text, fallback ) => ( 'layout7' === layout ? text || fallback : '' );

    const onSubmit = ( form ) => {
        // The checks sign_in() runs first, with its messages.
        const next = {};

        if ( ! userRef.current.value.trim() ) {
            next.log = __( 'Username is required.', 'wp-user-frontend' );
        }

        if ( ! passRef.current.value ) {
            next.pwd = __( 'Password is required.', 'wp-user-frontend' );
        }

        setFieldErrors( next );

        if ( Object.keys( next ).length ) {
            setErrors( [] );
            ( next.log ? userRef : passRef ).current.focus();

            return;
        }

        submit( form );
    };

    const clear = ( name ) => () => fieldErrors[ name ] && setFieldErrors( ( current ) => ( { ...current, [ name ]: '' } ) );

    return (
        <>
            <LoginFrame id="loginform" name="loginform" data={ data } errors={ errors } busy={ busy } onSubmit={ onSubmit }>
                <TextRow
                    field={ { name: 'log', label: texts.usernameLabel, placeholder: texts.usernamePlaceholder, help: help( texts.usernameHelp, __( 'Please enter your username or email address', 'wp-user-frontend' ) ) } }
                    id="wpuf-user_login"
                    inputRef={ userRef }
                    autoComplete="username"
                    error={ fieldErrors.log }
                    onInput={ clear( 'log' ) }
                />
                <PasswordRow
                    field={ { name: 'pwd', label: texts.passwordLabel, placeholder: texts.passwordPlaceholder, help: help( texts.passwordHelp, __( 'Please enter your password', 'wp-user-frontend' ) ) } }
                    id="wpuf-user_pass"
                    inputRef={ passRef }
                    autoComplete="current-password"
                    error={ fieldErrors.pwd }
                    onInput={ clear( 'pwd' ) }
                />

                { data.captcha && (
                    <li className="wpuf-el wpuf-login-captcha">
                        <ServerHtml html={ data.captcha } slug="login_captcha" className="wpuf-slot-inline" />
                    </li>
                ) }

                <li className="wpuf-el wpuf-remember-forgot-wrapper">
                    <span className="wpuf-remember-me">
                        <input name="rememberme" type="checkbox" id="wpuf-rememberme" value="forever" />
                        <label htmlFor="wpuf-rememberme">{ texts.rememberMe }</label>
                    </span>
                    <span className="wpuf-lost-password">
                        <a href={ data.lost_password }>{ texts.lostPassword }</a>
                    </span>
                </li>

                <SubmitRow text={ texts.buttonText } busyText={ __( 'Logging in…', 'wp-user-frontend' ) } busy={ busy }>
                    <input type="hidden" name="redirect_to" value={ data.redirect_to || '' } />
                    <input type="hidden" name="wpuf_login" value="true" />
                    <input type="hidden" name="action" value="login" />
                    <input type="hidden" id="wpuf-login-nonce" name="wpuf-login-nonce" value={ data.nonce } />
                    <input type="hidden" name="_wp_http_referer" value={ referer() } />
                </SubmitRow>

                { data.bottom && (
                    <li className="wpuf-el wpuf-login-bottom">
                        <ServerHtml html={ data.bottom } slug="login_form_bottom" className="wpuf-slot-inline" />
                    </li>
                ) }
            </LoginFrame>

            <ActionLinks html={ data.links } slug="login_links" />
        </>
    );
}
