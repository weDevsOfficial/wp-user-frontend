/**
 * The frontend login form app: mounts on every `[data-wpuf-mount="login"]`
 * root the `[wpuf-login]` shortcode rendered, with the data the server put
 * in the root's boot script (Login_Schema). The boot data's `view` picks
 * the screen: the login form, the lost password form
 * (`?action=lostpassword`) or the reset password form (`?action=rp`).
 *
 * The forms have the registration form's markup. In free they follow the
 * theme (as the classic template did); with Pro the server marks the root
 * `wpuf-login-styled` and they get the registration form's card look, with
 * Pro's texts, layout 7 help lines and colours (boot data `pro.style`, set
 * here as the `--wpuf-login-*` variables login.css reads). The root is no
 * longer `#wpuf-login-form`, so Pro's classic layout sheets (all scoped to
 * that id) stay with the classic template.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { mountAll } from '@wpuf/frontend-kit';
import LoginForm from './LoginForm';
import { proLogin } from './parts';
import LostPasswordForm from './LostPasswordForm';
import ResetPasswordForm from './ResetPasswordForm';
import './login.css';

const VIEWS = {
    login: LoginForm,
    lostpassword: LostPasswordForm,
    resetpass: ResetPasswordForm,
};

// Pro's colour settings → the variables login.css reads. Every variable is
// set on the root (`initial` when Pro has no value), so the `:root` values
// Pro's classic sheet prints for the classic template never reach the app.
const STYLE_VARS = {
    form_width: '--wpuf-login-form-width',
    form_bg: '--wpuf-login-form-bg',
    form_border: '--wpuf-login-form-border',
    field_bg: '--wpuf-login-field-bg',
    field_border: '--wpuf-login-field-border',
    label_color: '--wpuf-login-label-color',
    input_text: '--wpuf-login-input-text',
    placeholder: '--wpuf-login-placeholder',
    help_text: '--wpuf-login-help-text',
    button_bg: '--wpuf-login-button-bg',
    button_border: '--wpuf-login-button-border',
    button_text: '--wpuf-login-button-text',
};

mountAll( 'login', ( root, data ) => {
    const pro = proLogin( data );
    const layout = ( pro && pro.layout ) || '';
    const style = ( pro && pro.style ) || {};

    if ( layout ) {
        root.classList.add( `wpuf-login-${ layout }` );
    }

    Object.entries( STYLE_VARS ).forEach( ( [ key, name ] ) => {
        const value = 'string' === typeof style[ key ] ? style[ key ].trim() : '';

        root.style.setProperty( name, value && ! /[;{}<>]/.test( value ) ? value : 'initial' );
    } );

    if ( [ 'left', 'right' ].includes( style.form_align ) ) {
        root.classList.add( `wpuf-login-align-${ style.form_align }` );
    }

    const View = VIEWS[ data.view ] || LoginForm;
    const styled = root.classList.contains( 'wpuf-login-styled' );

    createRoot( root ).render( <View data={ { ...data, styled } } layout={ layout } /> );
} );
