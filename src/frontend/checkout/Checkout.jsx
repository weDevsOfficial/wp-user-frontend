/**
 * The checkout: billing address, payment method and order summary in one
 * real POST form (`form#wpuf-payment-gateway`, the classic field names), so
 * Payment::send_to_gateway() handles it as before and the gateways' own
 * scripts bound to that form (Pro Stripe) keep working. The gateways' forms
 * and the checkout hooks' output come from the server (ServerHtml).
 *
 * @since WPUF_SINCE
 */
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Card, Notice, ServerHtml, api, icons } from '@wpuf/frontend-kit';
import BillingAddress, { billingErrors } from './BillingAddress';
import OrderSummary from './OrderSummary';

const { CreditCard, Lock, ShieldCheck } = icons;

export default function Checkout( { data } ) {
    const root = useRef( null );
    const latest = useRef( 0 );
    const [ gateway, setGateway ] = useState( data.selected || '' );
    const [ address, setAddress ] = useState( data.billing ? data.billing.values : {} );
    const [ errors, setErrors ] = useState( {} );
    const [ formError, setFormError ] = useState( '' );
    const [ busy, setBusy ] = useState( false );
    const [ totals, setTotals ] = useState( { total: data.order.total, tax: data.tax ? data.tax.rate : '' } );
    const [ coupon, setCoupon ] = useState( null );
    const [ updating, setUpdating ] = useState( false );
    const [ focusError, setFocusError ] = useState( 0 );
    const slots = data.slots || {};
    const [ notice, setNotice ] = useState( data.notice || null );

    // Gateway scripts written for the classic page start once the form is on the page.
    useEffect( () => {
        const detail = { root: root.current };

        // jQuery runs the handlers in this call and passes their errors up: a gateway that
        // fails to start (e.g. Stripe without a key) must not take the checkout down with it.
        if ( window.jQuery ) {
            try {
                window.jQuery( document ).trigger( 'wpuf:checkout:ready', [ root.current ] );
            } catch ( error ) {
                window.console?.error( 'WPUF checkout: a wpuf:checkout:ready handler failed.', error );
            }
        }

        document.dispatchEvent( new window.CustomEvent( 'wpuf:checkout:ready', { detail } ) );
    }, [] );

    // After a blocked submit (as the forms app): the first field with an error
    // takes the focus and its row scrolls to the middle (clear of sticky headers).
    useEffect( () => {
        const first = focusError && root.current && root.current.querySelector( '.has-error input:not([type="hidden"]), .has-error .wpuf-select__trigger' );

        if ( ! first ) {
            return;
        }

        first.focus( { preventScroll: true } );

        const row = first.closest( 'li.wpuf-el' ) || first;
        const smooth = ! ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches );

        row.scrollIntoView( { block: 'center', behavior: smooth ? 'smooth' : 'auto' } );
    }, [ focusError ] );

    // Back from the gateway (bfcache): the button is usable again.
    useEffect( () => {
        const reset = ( event ) => event.persisted && setBusy( false );

        window.addEventListener( 'pageshow', reset );

        return () => window.removeEventListener( 'pageshow', reset );
    }, [] );

    // The address decides the tax: save it and read the item's tax and total
    // (PUT account/billing-address, as the classic page); a coupon is applied
    // again so its total includes the new tax. Latest answer wins.
    const refreshTotals = useCallback( ( next, current ) => {
        if ( ! data.tax ) {
            return;
        }

        const call = ++latest.current;
        const fields = data.billing.fields;
        const body = { type: data.type, id: String( data.id ) };

        Object.keys( fields ).forEach( ( key ) => {
            body[ fields[ key ].name ] = next[ key ] || '';
        } );

        setUpdating( true );

        api.saveBillingAddress( body )
            .then( ( response ) => {
                if ( call !== latest.current || ! response || undefined === response.tax ) {
                    return null;
                }

                setTotals( { total: response.cost, tax: response.tax } );

                return current ? api.applyCoupon( data.pack_id, current.code, data.restNonce ) : null;
            } )
            .then( ( applied ) => {
                if ( applied && call === latest.current ) {
                    setCoupon( ( previous ) => ( previous ? { ...previous, total: applied.total_formatted || previous.total, discount: applied.discount || previous.discount } : previous ) );
                }
            } )
            .catch( () => {} )
            .finally( () => call === latest.current && setUpdating( false ) );
    }, [ data ] );

    const timer = useRef( 0 );
    const changeAddress = ( key, picked ) => {
        // The select's "nothing picked" row is the classic -1.
        const value = '-1' === picked ? '' : picked;
        const next = { ...address, [ key ]: value };

        if ( 'country' === key ) {
            next.state = '';
        }

        setAddress( next );
        setErrors( ( previous ) => ( previous[ key ] ? { ...previous, [ key ]: '' } : previous ) );

        if ( [ 'country', 'state' ].includes( key ) ) {
            window.clearTimeout( timer.current );
            timer.current = window.setTimeout( () => refreshTotals( next, coupon ), 'country' === key ? 0 : 300 );
        }
    };

    // Checked before the submit event, so no handler (the gateway's either) runs on a bad address.
    const check = ( event ) => {
        const found = data.billing ? billingErrors( data.billing.fields, address ) : {};

        setErrors( found );

        if ( ! gateway ) {
            event.preventDefault();
            setFormError( __( 'Please choose a payment method.', 'wp-user-frontend' ) );

            return;
        }

        if ( Object.keys( found ).length ) {
            event.preventDefault();
            setFormError( __( 'Please fix the errors to proceed', 'wp-user-frontend' ) );
            setFocusError( ( count ) => count + 1 );

            return;
        }

        setFormError( '' );
    };

    // A gateway script that takes over (Stripe pays in place) prevents the
    // submit first: then it shows its own progress.
    const submit = ( event ) => {
        if ( ! event.nativeEvent.defaultPrevented ) {
            setBusy( true );
        }
    };

    return (
        <form ref={ root } id="wpuf-payment-gateway" action="" method="POST" onSubmit={ submit } noValidate>
            <div className="wpuf-checkout-wrap">
                { /* A gateway sent the customer back (Checkout_Service::return_with_error()). */ }
                { notice && (
                    <Notice kind="error" className="wpuf-checkout-notice" onClose={ () => setNotice( null ) }>
                        <strong>{ notice.message }</strong>
                        { notice.detail && <span className="wpuf-checkout-notice__detail">{ notice.detail }</span> }
                    </Notice>
                ) }
                { data.billing && (
                    <BillingAddress billing={ data.billing } values={ address } errors={ errors } onChange={ changeAddress } />
                ) }

                <div className="wpuf-checkout-columns">
                    <div className="wpuf-checkout-left">
                        { slots.before_gateways && <ServerHtml html={ slots.before_gateways } slug="before_gateways" /> }

                        <Card
                            className="wpuf-checkout-methods-card"
                            title={ (
                                <>
                                    <CreditCard size={ 18 } aria-hidden="true" />
                                    { __( 'Payment method', 'wp-user-frontend' ) }
                                </>
                            ) }
                        >
                            <fieldset className="wpuf-checkout-methods">
                                <legend className="screen-reader-text">{ __( 'Payment method', 'wp-user-frontend' ) }</legend>

                                <ul className="wpuf-payment-gateways">
                                    { data.gateways.map( ( item ) => (
                                        <li key={ item.id } className={ `wpuf-gateway-${ item.id }` }>
                                            <label className="wpuf-gateway-card" htmlFor={ `wpuf-gateway-${ item.id }` }>
                                                <span className="wpuf-gateway-card-main">
                                                    { item.icon && (
                                                        <span className="wpuf-gateway-icon">
                                                            <img src={ item.icon } alt={ item.label } />
                                                        </span>
                                                    ) }
                                                    <span className="wpuf-gateway-info">
                                                        <span className="wpuf-gateway-label">{ item.label }</span>
                                                        { item.instruction && <span className="wpuf-gateway-desc">{ item.instruction }</span> }
                                                    </span>
                                                    <input
                                                        id={ `wpuf-gateway-${ item.id }` }
                                                        name="wpuf_payment_method"
                                                        type="radio"
                                                        value={ item.id }
                                                        checked={ gateway === item.id }
                                                        onChange={ () => {
                                                            setGateway( item.id );
                                                            setFormError( '' );
                                                        } }
                                                    />
                                                </span>

                                                { /* Always on the page (Stripe mounts its card field once), shown when picked. */ }
                                                { item.form && (
                                                    <div className="wpuf-payment-instruction" style={ gateway === item.id ? undefined : { display: 'none' } }>
                                                        <ServerHtml html={ item.form } slug={ `gateway_${ item.id }` } />
                                                    </div>
                                                ) }
                                            </label>
                                        </li>
                                    ) ) }
                                </ul>
                            </fieldset>
                        </Card>

                        { slots.after_gateways && <ServerHtml html={ slots.after_gateways } slug="after_gateways" /> }
                    </div>

                    <div className="wpuf-checkout-right">
                        <OrderSummary
                            data={ data }
                            totals={ totals }
                            coupon={ coupon }
                            onCoupon={ setCoupon }
                            updating={ updating }
                        />
                    </div>
                </div>

                <input type="hidden" name="_wpnonce" value={ data.nonce } />
                <input type="hidden" name="_wp_http_referer" value={ data.referer || '' } />
                <input type="hidden" name="type" value={ data.type } />
                <input type="hidden" name="action" value="wpuf_pay" />
                <input type="hidden" name="wpuf_payment_submit" value="1" />
                { data.post_id > 0 && <input type="hidden" name="post_id" value={ data.post_id } /> }
                { data.pack_id > 0 && (
                    <>
                        <input type="hidden" name="pack_id" value={ data.pack_id } />
                        <input type="hidden" name="recurring_pay" value={ data.recurring } />
                        <input type="hidden" name="user_id" value={ data.user_id } />
                    </>
                ) }
                { data.billing && Object.keys( data.billing.fields ).map( ( key ) => (
                    <input key={ key } type="hidden" name={ `billing_address[${ data.billing.fields[ key ].name }]` } value={ address[ key ] || '' } />
                ) ) }
                { /* The classic page's item markers (scripts read them). */ }
                <div id="wpuf_type" hidden>{ data.type }</div>
                <div id="wpuf_id" hidden>{ data.id }</div>

                <div className="wpuf-checkout-footer">
                    { formError && <div className="wpuf-errors wpuf-checkout-errors" id="wpuf-checkout-errors" role="alert">{ formError }</div> }
                    <Button type="submit" variant="primary" className="wpuf-checkout-btn" onClick={ check } busy={ busy }>
                        { ! busy && <Lock size={ 16 } aria-hidden="true" /> }
                        { busy ? __( 'Processing…', 'wp-user-frontend' ) : __( 'Continue to Payment', 'wp-user-frontend' ) }
                    </Button>
                    <p className="wpuf-checkout-secure">
                        <ShieldCheck size={ 14 } aria-hidden="true" />
                        { __( 'Your payment details are handled by the payment provider.', 'wp-user-frontend' ) }
                    </p>
                </div>
            </div>
        </form>
    );
}
