/**
 * The order summary: the item, Pro's tax rate, an applied coupon, the total
 * (the classic ids `wpuf_pay_page_cost|tax|total`), the summary hooks'
 * output in the classic order, and the coupon field (Pro, packs) over
 * Pro's REST routes `coupons/apply|cancel`; the coupon id posts with the form.
 *
 * @since WPUF_SINCE
 */
import { useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Card, Pill, ServerHtml, api, icons } from '@wpuf/frontend-kit';

const { Receipt, Tag, X } = icons;

function Coupon( { data, coupon, onCoupon } ) {
    const [ open, setOpen ] = useState( false );
    const [ code, setCode ] = useState( '' );
    const [ error, setError ] = useState( '' );
    const [ busy, setBusy ] = useState( false );
    const input = useRef( null );

    const apply = () => {
        const value = code.trim();

        if ( ! value ) {
            setError( __( 'Enter a coupon code.', 'wp-user-frontend' ) );
            input.current?.focus();

            return;
        }

        setBusy( true );
        setError( '' );

        api.applyCoupon( data.pack_id, value, data.restNonce )
            .then( ( response ) => {
                onCoupon( {
                    id: response.coupon_id,
                    code: value,
                    total: response.total_formatted || '',
                    discount: response.discount || '',
                    message: response.message || '',
                } );
                setOpen( false );
                setCode( '' );
            } )
            .catch( ( failure ) => {
                setError( failure.message );
                input.current?.focus();
            } )
            .finally( () => setBusy( false ) );
    };

    const remove = () => {
        setBusy( true );
        api.cancelCoupon( data.pack_id, data.restNonce )
            .catch( () => {} )
            .finally( () => {
                setBusy( false );
                onCoupon( null );
            } );
    };

    if ( coupon ) {
        return (
            <div className="wpuf-coupon-applied" role="status">
                <Pill tone="green" className="wpuf-coupon-applied-label">
                    <Tag size={ 14 } aria-hidden="true" />
                    { sprintf(
                        /* translators: %s: coupon code */
                        __( 'Coupon "%s" applied', 'wp-user-frontend' ),
                        coupon.code
                    ) }
                </Pill>
                <Button variant="ghost" size="sm" className="wpuf-coupon-remove" onClick={ remove } busy={ busy } aria-label={ __( 'Remove coupon', 'wp-user-frontend' ) }>
                    <X size={ 16 } aria-hidden="true" />
                </Button>
            </div>
        );
    }

    if ( ! open ) {
        return (
            <Button
                variant="link"
                className="wpuf-coupon-show"
                onClick={ () => {
                    setOpen( true );
                    window.setTimeout( () => input.current?.focus(), 0 );
                } }
            >
                <Tag size={ 14 } aria-hidden="true" />
                { __( 'Have a discount code?', 'wp-user-frontend' ) }
            </Button>
        );
    }

    return (
        <div className="wpuf-coupon-wrap">
            <label className="screen-reader-text" htmlFor="wpuf-coupon-code">{ __( 'Coupon code', 'wp-user-frontend' ) }</label>
            <div className="wpuf-coupon-row">
                <input
                    ref={ input }
                    id="wpuf-coupon-code"
                    type="text"
                    className="wpuf-coupon-field"
                    placeholder={ __( 'Coupon code', 'wp-user-frontend' ) }
                    value={ code }
                    autoComplete="off"
                    aria-invalid={ error ? 'true' : undefined }
                    aria-describedby={ error ? 'wpuf-coupon-error' : undefined }
                    onChange={ ( event ) => setCode( event.target.value ) }
                    onKeyDown={ ( event ) => {
                        // Enter applies the code, it never submits the payment.
                        if ( 'Enter' === event.key ) {
                            event.preventDefault();
                            apply();
                        } else if ( 'Escape' === event.key ) {
                            setOpen( false );
                        }
                    } }
                />
                <Button variant="primary" size="sm" busy={ busy } onClick={ apply }>{ __( 'Apply', 'wp-user-frontend' ) }</Button>
                <Button variant="ghost" size="sm" disabled={ busy } onClick={ () => {
                    setOpen( false );
                    setError( '' );
                } }>{ __( 'Cancel', 'wp-user-frontend' ) }</Button>
            </div>
            { error && <div className="wpuf-error-msg wpuf-coupon-error" id="wpuf-coupon-error" role="alert">{ error }</div> }
        </div>
    );
}

export default function OrderSummary( { data, totals, coupon, onCoupon, updating } ) {
    const slots = data.slots || {};
    const order = data.order;

    return (
        <Card
            className="wpuf-order-summary"
            title={ (
                <>
                    <Receipt size={ 18 } aria-hidden="true" />
                    { __( 'Order summary', 'wp-user-frontend' ) }
                </>
            ) }
        >

            <div className="wpuf-pack-inner">
                { slots.summary_before && <ServerHtml html={ slots.summary_before } slug="summary_before" /> }

                <div className="wpuf-order-row wpuf-order-item">
                    <span className="wpuf-order-label">{ order.title }</span>
                    <span className="wpuf-order-value" id="wpuf_pay_page_cost">{ order.subtotal }</span>
                </div>
                { order.pack && (
                    <div className="wpuf-order-subtitle">
                        { order.recurring ? __( 'Recurring payment', 'wp-user-frontend' ) : __( 'One time payment', 'wp-user-frontend' ) }
                    </div>
                ) }

                { slots.summary_after_item && <ServerHtml html={ slots.summary_after_item } slug="summary_after_item" /> }

                { coupon && coupon.discount && (
                    <div className="wpuf-order-row wpuf-order-discount">
                        <span className="wpuf-order-label">{ __( 'Discount', 'wp-user-frontend' ) }</span>
                        <span className="wpuf-order-value">−{ coupon.discount }</span>
                    </div>
                ) }

                { data.tax && (
                    <div className="wpuf-order-row wpuf-order-tax">
                        <span className="wpuf-order-label">{ __( 'Tax', 'wp-user-frontend' ) }</span>
                        <span className="wpuf-order-value" id="wpuf_pay_page_tax">{ totals.tax }</span>
                    </div>
                ) }

                { slots.summary_before_total && <ServerHtml html={ slots.summary_before_total } slug="summary_before_total" /> }

                <div className="wpuf-order-divider" />
                <div className={ `wpuf-order-row wpuf-order-total${ updating ? ' is-updating' : '' }` } aria-live="polite" aria-busy={ updating || undefined }>
                    <span className="wpuf-order-label">{ __( 'Total', 'wp-user-frontend' ) }</span>
                    <span className="wpuf-order-value" id="wpuf_pay_page_total">{ coupon && coupon.total ? coupon.total : totals.total }</span>
                </div>

                { slots.summary_after_total && <ServerHtml html={ slots.summary_after_total } slug="summary_after_total" /> }
            </div>

            { data.coupon && (
                <>
                    <Coupon data={ data } coupon={ coupon } onCoupon={ onCoupon } />
                    <input type="hidden" name="coupon_id" className="wpuf-coupon-id-field" value={ coupon ? coupon.id : '' } />
                    <input type="hidden" name="coupon_code" value={ coupon ? coupon.code : '' } />
                </>
            ) }

            { slots.after_summary && <ServerHtml html={ slots.after_summary } slug="after_summary" /> }
        </Card>
    );
}
