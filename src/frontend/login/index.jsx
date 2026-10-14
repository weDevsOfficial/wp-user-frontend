/**
 * The frontend login form app: mounts on every `[data-wpuf-react="login"]`
 * root the `[wpuf-login]` shortcode rendered, with the data the server put
 * in the root's boot script (Simple_Login::react_login_data()).
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { mountAll } from '@wpuf/frontend-kit';
import LoginForm from './LoginForm';
import './login.css';

mountAll( 'login', ( root, data ) => {
    // The root is the classic `div.login#wpuf-login-form` itself (no wrapper
    // between it and the theme's content block), so the theme's layout rules
    // and Pro's `#wpuf-login-form` sheet size and place it as before.
    const pro = window.wpufLoginForm || null;
    const layout = ( pro && pro.layout ) || '';

    root.id = 'wpuf-login-form';
    root.classList.add( 'login' );

    if ( layout ) {
        root.classList.add( `wpuf-layout-${ layout.replace( 'layout', '' ) }` );
    }

    createRoot( root ).render( <LoginForm data={ data } layout={ layout } /> );
} );
