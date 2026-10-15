/**
 * The frontend login form app: mounts on every `[data-wpuf-mount="login"]`
 * root the `[wpuf-login]` shortcode rendered, with the data the server put
 * in the root's boot script (Simple_Login::login_schema()). The boot
 * data's `view` picks the screen: the login form, the lost password form
 * (`?action=lostpassword`) or the reset password form (`?action=rp`).
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

mountAll( 'login', ( root, data ) => {
    // The root is the classic `div.login#wpuf-login-form` itself (no wrapper
    // between it and the theme's content block), so the theme's layout rules
    // and Pro's `#wpuf-login-form` sheet size and place it as before.
    const pro = proLogin( data );
    const layout = ( pro && pro.layout ) || '';

    root.id = 'wpuf-login-form';
    root.classList.add( 'login' );

    if ( layout ) {
        root.classList.add( `wpuf-layout-${ layout.replace( 'layout', '' ) }` );
    }

    const View = VIEWS[ data.view ] || LoginForm;

    createRoot( root ).render( <View data={ data } layout={ layout } /> );
} );
