/**
 * The `[wpuf-login]` form in React, with the classic template's markup
 * (templates/login-form.php: ids, names, classes; the mount root is the
 * `div.login#wpuf-login-form`, index.jsx), so the free and Pro
 * login sheets (layouts 1-7, colours) style it as before. It posts to the
 * same URL as the classic form: Simple_Login::process_login() logs in,
 * redirects and reports errors exactly as for the classic form.
 *
 * Pro's layout script (wpufLoginForm: layout, texts) changed the classic
 * markup after load; here the same layout class, texts, layout 7 help and
 * action-link wrappers are rendered directly, so that script finds nothing
 * left to change.
 *
 * Added on top of the classic form: required checks before posting (the
 * server's own messages), a show / hide password button, autocomplete
 * hints, and a lock with a spinner in the button while the page posts
 * (released after 20 s if the browser never left the page).
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ServerHtml, cx } from '@wpuf/frontend-kit';

import { FieldError, PasswordInput, SubmitButton, proLogin, referer, usePostLock, wrapActionLinks } from './parts';

export default function LoginForm( { data, layout = '' } ) {
    const pro = proLogin( data );
    // Pro only replaces a text it has a value for (applyCustomTexts()).
    const texts = { ...( data.texts || {} ) };

    Object.entries( ( pro && pro.texts ) || {} ).forEach( ( [ key, value ] ) => {
        if ( value ) {
            texts[ key ] = value;
        }
    } );

    const [ errors, setErrors ] = useState( {} );
    const [ busy, setBusy ] = usePostLock();
    const linksRef = useRef( null );
    const userRef = useRef( null );
    const passRef = useRef( null );

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

        // The checks process_login() runs first, with its messages.
        const next = {};

        if ( ! userRef.current.value.trim() ) {
            next.log = __( 'Username is required.', 'wp-user-frontend' );
        }

        if ( ! passRef.current.value ) {
            next.pwd = __( 'Password is required.', 'wp-user-frontend' );
        }

        setErrors( next );

        if ( Object.keys( next ).length ) {
            event.preventDefault();
            ( next.log ? userRef : passRef ).current.focus();

            return;
        }

        // The browser posts the form (a full page request, as before); the
        // fields stay readable but locked until the next page loads.
        setBusy( true );
    };

    const help = ( text ) => ( 'layout7' === layout && text ? <span className="wpuf-help">{ text }</span> : null );
    const fieldError = ( name ) => <FieldError id={ `wpuf-login-error-${ name }` } message={ errors[ name ] } />;

    return (
        <>
            { data.message && <ServerHtml html={ data.message } slug="login_message" className="wpuf-slot-inline" /> }
            { data.notices && <ServerHtml html={ data.notices } slug="login_notices" className="wpuf-slot-inline" /> }

            <form
                name="loginform"
                className={ cx( 'wpuf-login-form', busy && 'is-submitting' ) }
                id="loginform"
                action={ data.action_url }
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
                        name="log"
                        id="wpuf-user_login"
                        className="input"
                        size="20"
                        autoComplete="username"
                        autoCapitalize="none"
                        spellCheck="false"
                        placeholder={ texts.usernamePlaceholder || undefined }
                        readOnly={ busy }
                        aria-invalid={ errors.log ? 'true' : undefined }
                        aria-describedby={ errors.log ? 'wpuf-login-error-log' : undefined }
                        onInput={ () => errors.log && setErrors( ( current ) => ( { ...current, log: '' } ) ) }
                    />
                    { help( texts.usernameHelp || __( 'Please enter your username or email address', 'wp-user-frontend' ) ) }
                    { fieldError( 'log' ) }
                </p>
                <p>
                    <label htmlFor="wpuf-user_pass">{ texts.passwordLabel }</label>
                    <PasswordInput
                        inputRef={ passRef }
                        name="pwd"
                        id="wpuf-user_pass"
                        autoComplete="current-password"
                        placeholder={ texts.passwordPlaceholder }
                        busy={ busy }
                        error={ errors.pwd }
                        errorId="wpuf-login-error-pwd"
                        onInput={ () => errors.pwd && setErrors( ( current ) => ( { ...current, pwd: '' } ) ) }
                    />
                    { help( texts.passwordHelp || __( 'Please enter your password', 'wp-user-frontend' ) ) }
                    { fieldError( 'pwd' ) }
                </p>

                { data.captcha && <ServerHtml html={ data.captcha } slug="login_captcha" className="wpuf-slot-inline" /> }

                { /* The spaces the classic template has between its tags (without a Pro layout
                     the parts are inline: "Remember Me Lost Password", not run together). */ }
                <p className="forgetmenot wpuf-remember-forgot-wrapper">
                    <span className="wpuf-remember-me">
                        <input name="rememberme" type="checkbox" id="wpuf-rememberme" value="forever" />{ ' ' }
                        <label htmlFor="wpuf-rememberme">{ texts.rememberMe }</label>
                    </span>{ ' ' }
                    <span className="wpuf-lost-password">
                        <a href={ data.lost_password }>{ texts.lostPassword }</a>
                    </span>
                </p>

                <p className="submit">
                    <SubmitButton text={ texts.buttonText } busyText={ __( 'Logging in…', 'wp-user-frontend' ) } busy={ busy } />
                    <input type="hidden" name="redirect_to" value={ data.redirect_to || '' } />
                    <input type="hidden" name="wpuf_login" value="true" />
                    <input type="hidden" name="action" value="login" />
                    <input type="hidden" id="wpuf-login-nonce" name="wpuf-login-nonce" value={ data.nonce } />
                    <input type="hidden" name="_wp_http_referer" value={ referer() } />
                </p>
                { data.bottom && (
                    <p>
                        <ServerHtml html={ data.bottom } slug="login_form_bottom" className="wpuf-slot-inline" />
                    </p>
                ) }
            </form>

            <div className="wpuf-action-links" ref={ linksRef }>
                { data.links && <ServerHtml html={ data.links } slug="login_links" className="wpuf-slot-inline" /> }
            </div>
        </>
    );
}
