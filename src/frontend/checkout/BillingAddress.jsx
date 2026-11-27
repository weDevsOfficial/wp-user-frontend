/**
 * The checkout's billing address: the fields of Settings > Payments >
 * Billing address (required / optional / hidden) as the kit's form fields
 * (TextField, SelectField in a Card), posted under the classic input names
 * (`wpuf_biiling_*`); the states load for the picked country.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Card, SelectField, TextField, api, icons } from '@wpuf/frontend-kit';

const { MapPin } = icons;

const FORM_ID = 'checkout';

const LABELS = {
    country: () => __( 'Country', 'wp-user-frontend' ),
    state: () => __( 'State/Province/Region', 'wp-user-frontend' ),
    add_line_1: () => __( 'Address Line 1', 'wp-user-frontend' ),
    add_line_2: () => __( 'Address Line 2', 'wp-user-frontend' ),
    city: () => __( 'City', 'wp-user-frontend' ),
    zip_code: () => __( 'Postal Code/ZIP', 'wp-user-frontend' ),
};

/**
 * The required fields left empty.
 *
 * @param {Object} fields key => { mode }
 * @param {Object} values key => value
 *
 * @return {Object} key => message
 */
export const billingErrors = ( fields, values ) => {
    const errors = {};

    Object.keys( fields ).forEach( ( key ) => {
        if ( 'required' === fields[ key ].mode && ! String( values[ key ] || '' ).trim() ) {
            errors[ key ] = __( 'This field is required', 'wp-user-frontend' );
        }
    } );

    return errors;
};

export default function BillingAddress( { billing, values, errors, onChange } ) {
    const [ states, setStates ] = useState( billing.states || [] );
    const [ loading, setLoading ] = useState( false );
    const country = values.country || '';
    const first = useRef( true );

    // States of the picked country (the saved country's list came with the page).
    useEffect( () => {
        if ( first.current ) {
            first.current = false;

            return;
        }

        let live = true;

        setLoading( true );
        api.getBillingStates( country )
            .then( ( response ) => live && setStates( response && response.list ? response.list : [] ) )
            .catch( () => live && setStates( [] ) )
            .finally( () => live && setLoading( false ) );

        return () => {
            live = false;
        };
    }, [ country ] );

    // The kit field settings for one address field.
    const settings = ( key, extra = {} ) => ( {
        name: billing.fields[ key ].input,
        label: LABELS[ key ](),
        required: 'required' === billing.fields[ key ].mode ? 'yes' : 'no',
        css: `wpuf-billing-${ key.replace( /_/g, '-' ) }`,
        ...extra,
    } );

    const shown = ( key ) => 'hidden' !== billing.fields[ key ].mode;

    const text = ( key ) => shown( key ) && (
        <TextField
            key={ key }
            field={ settings( key ) }
            formId={ FORM_ID }
            value={ values[ key ] || '' }
            error={ errors[ key ] }
            onChange={ ( value ) => onChange( key, value ) }
        />
    );

    return (
        <Card
            className="wpuf-checkout-billing"
            title={ (
                <>
                    <MapPin size={ 18 } aria-hidden="true" />
                    { __( 'Billing Address', 'wp-user-frontend' ) }
                </>
            ) }
        >
            <div className="wpuf-bill_addr-inner">
                <ul className="wpuf-form form-label-above wpuf-checkout-address" id="wpuf-ajax-address-form">
                    { shown( 'country' ) && (
                        <SelectField
                            field={ settings( 'country', { options: billing.countries, first: __( 'Choose a country', 'wp-user-frontend' ) } ) }
                            formId={ FORM_ID }
                            selectClass="wpuf_biiling_country"
                            value={ country }
                            error={ errors.country }
                            onChange={ ( value ) => onChange( 'country', value ) }
                        />
                    ) }
                    { shown( 'state' ) && ( states.length || loading ? (
                        <SelectField
                            field={ settings( 'state', {
                                options: states,
                                first: loading ? __( 'Loading…', 'wp-user-frontend' ) : __( 'Choose a state', 'wp-user-frontend' ),
                                read_only: loading ? 'yes' : 'no',
                            } ) }
                            formId={ FORM_ID }
                            selectClass="wpuf_biiling_state"
                            value={ values.state || '' }
                            error={ errors.state }
                            onChange={ ( value ) => onChange( 'state', value ) }
                        />
                    ) : (
                        // No list for this country: the state as text, as the classic field.
                        text( 'state' )
                    ) ) }
                    { text( 'add_line_1' ) }
                    { text( 'add_line_2' ) }
                    { text( 'city' ) }
                    { text( 'zip_code' ) }
                </ul>
            </div>
        </Card>
    );
}
