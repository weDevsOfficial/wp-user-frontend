/**
 * Rules for settings that depend on each other (shown on top of the form
 * settings). They mirror the submit engine: Admin\Forms\Form::is_charging_enabled()
 * and guest posting in Post_Form_Handler.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

/**
 * Same values wpuf_is_checkbox_or_toggle_on() accepts.
 *
 * @param {*} value Setting value.
 *
 * @return {boolean} On.
 */
const isOn = ( value ) => [ true, 'on', 'yes', 'true', '1', 1 ].includes( value );

// Pro fields Pricing_Field_Payment::calculate_pricing_cost() charges for.
const PRICING_FIELDS = [ 'pricing_checkbox', 'pricing_radio', 'pricing_dropdown', 'pricing_multiselect', 'price_field' ];

/**
 * The problems of the current settings, as notices with an action.
 *
 * @param {Object} settings Form settings (unsaved state).
 * @param {Object} data     Builder boot data.
 * @param {Array}  fields   Form fields (unsaved state).
 *
 * @return {Array<Object>} { key, title, message (with <b> tags), action: { label, set | section | href, external } }
 */
export function findRecommendations( settings, data, fields = [] ) {
    const found = [];
    const site = data.site_payments || {};
    const payments = isOn( settings.payment_options );
    const option = settings.choose_payment_option || '';
    const payPerPost = payments && 'enable_pay_per_post' === option;
    const forcePack = payments && 'force_pack_purchase' === option;
    const cost = parseFloat( settings.pay_per_post_cost );
    // Pro: Enable Pricing Fields Payment charges the pricing fields' total,
    // with or without Payment Options.
    const pricingOn = !! data.is_pro_active && isOn( settings.enable_pricing_payment );
    const hasPricing = ( fields || [] ).some( ( field ) => field && ( PRICING_FIELDS.includes( field.template ) || PRICING_FIELDS.includes( field.input_type ) ) );
    const charges = ( payPerPost ? cost > 0 : forcePack ) || ( pricingOn && hasPricing );

    // An empty Post Permission shows the form to logged-out visitors, but the
    // submit endpoint only accepts them on a Guest Post form.
    if ( ! settings.post_permission ) {
        found.push( {
            key: 'post_permission',
            title: __( 'Nobody chose who can post', 'wp-user-frontend' ),
            message: __( '<b>Post Permission</b> is not set, so visitors who are not logged in see the form but can not send it. Choose who can post.', 'wp-user-frontend' ),
            action: { label: __( 'Logged-in users only', 'wp-user-frontend' ), set: { post_permission: 'role_base' }, section: 'general' },
        } );
    }

    // Step Start fields only split the form when Multi-Step is on.
    const hasSteps = ( fields || [] ).some( ( field ) => field && ( 'step_start' === field.template || 'step_start' === field.input_type ) );

    if ( hasSteps && ! isOn( settings.enable_multistep ) ) {
        found.push( {
            key: 'multistep',
            title: __( 'The form is not split into steps', 'wp-user-frontend' ),
            message: __( 'The form has <b>Step Start</b> fields, but <b>Enable Multi-Step</b> is off, so visitors see one long page.', 'wp-user-frontend' ),
            action: { label: __( 'Turn on Multi-Step', 'wp-user-frontend' ), set: { enable_multistep: 'on' }, section: 'general' },
        } );
    }

    if ( data.is_pro_active && hasPricing && ! pricingOn ) {
        found.push( {
            key: 'pricing_payment',
            title: __( 'Pricing fields are not charged', 'wp-user-frontend' ),
            message: __( 'The form has <b>pricing fields</b>, but <b>Enable Pricing Fields Payment</b> is off, so members see a price and a total and are never asked to pay.', 'wp-user-frontend' ),
            action: { label: __( 'Turn on pricing payment', 'wp-user-frontend' ), set: { enable_pricing_payment: 'on' }, section: 'payment_settings' },
        } );
    }

    if ( pricingOn && ! hasPricing ) {
        found.push( {
            key: 'pricing_fields',
            title: __( 'Nothing to charge for', 'wp-user-frontend' ),
            message: __( '<b>Enable Pricing Fields Payment</b> is on, but the form has no pricing field, so every post is free. Add a pricing field or turn it off.', 'wp-user-frontend' ),
            action: { label: __( 'Turn off pricing payment', 'wp-user-frontend' ), set: { enable_pricing_payment: 'off' }, section: 'payment_settings' },
        } );
    }

    if ( payPerPost && ! ( cost > 0 ) ) {
        found.push( {
            key: 'cost',
            title: __( 'Posts are not charged', 'wp-user-frontend' ),
            message: __( '<b>Pay as you post</b> is on, but <b>Charge for each post</b> is empty or 0, so every post is free. Set a cost per post.', 'wp-user-frontend' ),
            action: { label: __( 'Go to Payment Settings', 'wp-user-frontend' ), section: 'payment_settings' },
        } );
    }

    if ( charges && 'guest_post' === settings.post_permission && ! isOn( settings.guest_details ) ) {
        found.push( {
            key: 'guest_details',
            title: __( 'Guests can not pay for their posts', 'wp-user-frontend' ),
            message: __( 'Without a name and email no account is made for the guest, so the post goes to the <b>Default Post Owner</b> and nobody can pay for it. Turn on <b>Require Name and Email address</b>.', 'wp-user-frontend' ),
            action: { label: __( 'Turn it on', 'wp-user-frontend' ), set: { guest_details: 'on' } },
        } );
    }

    if ( charges && ( ! site.enabled || ! site.gateways || ! site.payment_page ) ) {
        let title = __( 'Payments are off for the site', 'wp-user-frontend' );
        let message = __( 'This form charges, but <b>Enable Payments</b> is off in <b>Settings > Payments</b>, so nobody is asked to pay.', 'wp-user-frontend' );

        if ( site.enabled && ! site.gateways ) {
            title = __( 'No payment gateway', 'wp-user-frontend' );
            message = __( 'No gateway is ticked in <b>Settings > Payments</b>, so buyers can not pay.', 'wp-user-frontend' );
        } else if ( site.enabled && ! site.payment_page ) {
            title = __( 'No payment page', 'wp-user-frontend' );
            message = __( '<b>Payment Page</b> is not set in <b>Settings > Payments</b>, so buyers have nowhere to pay.', 'wp-user-frontend' );
        }

        found.push( {
            key: 'site_payments',
            title,
            message,
            action: { label: __( 'Open payment settings', 'wp-user-frontend' ), href: site.settings_url, external: true },
        } );
    }

    if ( forcePack && ! ( data.subscriptions || [] ).length ) {
        found.push( {
            key: 'plans',
            title: __( 'No plan to buy', 'wp-user-frontend' ),
            message: __( '<b>Mandatory Subscription</b> is the payment option, but there is no published plan, so nobody can post.', 'wp-user-frontend' ),
            action: { label: __( 'Create a plan', 'wp-user-frontend' ), href: site.plans_url, external: true },
        } );
    }

    return found;
}
