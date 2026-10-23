/**
 * Payment settings that must be complete before they are saved: with payments
 * on, a payment page, at least one gateway, and the keys of each ticked
 * gateway. Saved half-done, checkout led nowhere or failed for buyers.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const isOn = ( value ) => [ true, 1, 'on', 'yes', 'true', '1' ].includes( value );
const blank = ( value ) => null === value || undefined === value || '' === String( value ).trim() || '0' === String( value ) || '-1' === String( value );

/**
 * Problems of the payment section, for the save dialog.
 *
 * @param {Object} payment Values of the wpuf_payment section.
 * @return {string[]} Messages (plain text).
 */
export function paymentProblems( payment ) {
    if ( ! payment || ! isOn( payment.enable_payment ) ) {
        return [];
    }

    const problems = [];
    const gateways = payment.active_gateways && 'object' === typeof payment.active_gateways
        ? Object.keys( payment.active_gateways ).filter( ( key ) => payment.active_gateways[ key ] )
        : [];

    if ( blank( payment.payment_page ) ) {
        problems.push( __( 'Payment Page: choose the page buyers pay on.', 'wp-user-frontend' ) );
    }

    if ( ! gateways.length ) {
        problems.push( __( 'Payment Gateways: tick at least one gateway.', 'wp-user-frontend' ) );
    }

    if ( gateways.includes( 'paypal' ) && ( blank( payment.paypal_client_id ) || blank( payment.paypal_client_secret ) ) ) {
        problems.push( __( 'PayPal: enter the Client ID and Client Secret, or untick PayPal.', 'wp-user-frontend' ) );
    }

    if ( gateways.includes( 'stripe' ) && ( blank( payment.stripe_api_key ) || blank( payment.stripe_api_secret ) ) ) {
        problems.push( __( 'Credit Card (Stripe): enter the Publishable Key and Secret Key, or untick Credit Card.', 'wp-user-frontend' ) );
    }

    return problems;
}
