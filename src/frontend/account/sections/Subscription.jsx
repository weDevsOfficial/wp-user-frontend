/**
 * The account page's Subscription section: the kit's SubscriptionCard with
 * the data of GET /account/subscription (Subscription_Schema::info()).
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Notice, SkeletonLines, SubscriptionCard, api } from '@wpuf/frontend-kit';

export default function Subscription() {
    const [ state, setState ] = useState( { info: null, error: '' } );

    useEffect( () => {
        let cancelled = false;

        api.getSubscription()
            .then( ( info ) => ! cancelled && setState( { info, error: '' } ) )
            .catch( ( e ) => ! cancelled && setState( { info: null, error: e.message } ) );

        return () => {
            cancelled = true;
        };
    }, [] );

    return (
        <div className="wpuf-account-subscription">
            <header className="wpuf-section-head">
                <h2 className="wpuf-section-head__title">{ __( 'Subscription', 'wp-user-frontend' ) }</h2>
                <p className="wpuf-section-head__lead">{ __( 'View your current subscription status, billing details, and renewal information.', 'wp-user-frontend' ) }</p>
            </header>
            { state.error && <Notice kind="error">{ state.error }</Notice> }
            { ! state.error && ! state.info && <div className="wpuf-card"><SkeletonLines lines={ 5 } /></div> }
            { state.info && <SubscriptionCard info={ state.info } /> }
        </div>
    );
}
