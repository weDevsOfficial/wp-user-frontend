/**
 * The frontend plans app: mounts on every `[data-wpuf-mount="plans"]` root
 * `[wpuf_sub_pack]` (view `packs`) or `[wpuf_sub_info]` (view `info`)
 * rendered, with the data the server put in the root's boot script
 * (Subscription_Schema).
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { SubscriptionCard, mountAll } from '@wpuf/frontend-kit';
import Plans from './Plans';
import './plans.css';

mountAll( 'plans', ( root, data ) => {
    createRoot( root ).render( 'info' === data.view ? <SubscriptionCard info={ data } /> : <Plans data={ data } /> );
} );
