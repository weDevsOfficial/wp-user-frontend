/**
 * The `[wpuf-login]` lost password screen (`?action=lostpassword`), built
 * like the registration form and sent to `wpuf/v1/login/lost-password`
 * (LoginController → Simple_Login::request_reset(): the form nonce,
 * `lostpassword_post`, the reset e-mail). The answer is the same whether an
 * account matches or not, and shows in the card; the form stays for another
 * try. What the template printed through hooks (`lostpassword_form`, the
 * notices, the action links) comes captured in the boot data.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ServerHtml, api } from '@wpuf/frontend-kit';

import { ActionLinks, LoginFrame, SubmitRow, TextRow, referer, textsOf, useLoginSubmit } from './parts';

export default function LostPasswordForm( { data } ) {
    const texts = textsOf( data );
    const [ fieldError, setFieldError ] = useState( '' );
    const [ notice, setNotice ] = useState( '' );
    const userRef = useRef( null );
    const { busy, errors, setErrors, submit } = useLoginSubmit( api.requestPasswordReset, ( response ) => {
        setNotice( ( response && response.message ) || '' );
        userRef.current.value = '';
    } );

    const onSubmit = ( form ) => {
        setNotice( '' );

        // The check retrieve_password() runs first, with its message.
        if ( ! userRef.current.value.trim() ) {
            setErrors( [] );
            setFieldError( __( 'Enter a username or e-mail address.', 'wp-user-frontend' ) );
            userRef.current.focus();

            return;
        }

        setFieldError( '' );
        submit( form );
    };

    return (
        <>
            <LoginFrame id="lostpasswordform" name="lostpasswordform" data={ data } errors={ errors } notice={ notice } busy={ busy } onSubmit={ onSubmit }>
                <TextRow
                    field={ { name: 'user_login', label: texts.usernameLabel } }
                    id="wpuf-user_login"
                    inputRef={ userRef }
                    autoComplete="username"
                    error={ fieldError }
                    onInput={ () => fieldError && setFieldError( '' ) }
                />

                { data.hook && (
                    <li className="wpuf-el wpuf-login-hook">
                        <ServerHtml html={ data.hook } slug="lostpassword_form" className="wpuf-slot-inline" />
                    </li>
                ) }

                <SubmitRow text={ texts.buttonText } busyText={ __( 'Sending…', 'wp-user-frontend' ) } busy={ busy }>
                    <input type="hidden" name="redirect_to" value={ data.redirect_to || '' } />
                    <input type="hidden" name="wpuf_reset_password" value="true" />
                    <input type="hidden" name="action" value="lostpassword" />
                    <input type="hidden" id="_wpnonce" name="_wpnonce" value={ data.nonce } />
                    <input type="hidden" name="_wp_http_referer" value={ referer() } />
                </SubmitRow>
            </LoginFrame>

            <ActionLinks html={ data.links } slug="lostpassword_links" />
        </>
    );
}
