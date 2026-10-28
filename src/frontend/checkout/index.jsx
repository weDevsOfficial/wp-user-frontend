/**
 * The checkout app: mounts on the pay page's `[data-wpuf-mount="checkout"]`
 * root with the boot data Checkout_Service::schema() printed (order,
 * gateways with their own forms, the checkout hooks' output, billing
 * address and coupon settings).
 *
 * The root renders synchronously (flushSync), so gateway scripts that look
 * for the pay form on DOM ready / window load find it, as on the classic page.
 *
 * @since WPUF_SINCE
 */
import { createRoot, flushSync } from '@wordpress/element';
import { mountAll } from '@wpuf/frontend-kit';
import Checkout from './Checkout';
import './checkout.css';

mountAll( 'checkout', ( root, data ) => {
    const app = createRoot( root );

    flushSync( () => app.render( <Checkout data={ data } /> ) );
} );
