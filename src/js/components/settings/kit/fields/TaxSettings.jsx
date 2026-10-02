import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../../stores-react/settings/constants';
import SelectDropdown from './SelectDropdown';

/**
 * Tax settings — Base Country/State selector + the Tax Rates table. Replaces the
 * legacy jQuery callbacks (`wpuf_base_country_state`, `wpuf_tax_rates`) that
 * stored to their OWN options via a custom `init` POST handler the React REST
 * route never triggered.
 *
 * Countries, states, the base address and rate rows arrive in the `tax`
 * side-channel (`extra`); edits write back there and persist server-side via
 * `wpuf_settings_saved` (Pro Tax.php). One field renders the base selector, the
 * other the rate table — routed by field name.
 */
const KEY = 'tax';

function useTax() {
    const data = useSelect( ( select ) => select( STORE_NAME ).getExtraValue( KEY ) || {}, [] );
    const { setExtraValue } = useDispatch( STORE_NAME );
    const set = ( patch ) => setExtraValue( KEY, { ...data, ...patch } );
    return { data, set };
}

function BaseCountry() {
    const { data, set } = useTax();
    const countries = data.countries || {};
    const states = data.states || {};
    const base = data.base || {};

    const stateOptions = { '': __( '— Select —', 'wp-user-frontend' ), ...( states[ base.country ] || {} ) };

    return (
        <div className="wpuf-mt-2 wpuf-flex wpuf-flex-wrap wpuf-gap-4">
            <div className="wpuf-min-w-0 wpuf-flex-1">
                <SelectDropdown
                    field={ { label: __( 'Base Country', 'wp-user-frontend' ), options: { '': __( '— Select —', 'wp-user-frontend' ), ...countries } } }
                    name="tax_base_country"
                    value={ base.country || '' }
                    onChange={ ( n, val ) => set( { base: { country: val, state: '' } } ) }
                />
            </div>
            <div className="wpuf-min-w-0 wpuf-flex-1">
                <SelectDropdown
                    field={ { label: __( 'Base State', 'wp-user-frontend' ), options: stateOptions } }
                    name="tax_base_state"
                    value={ base.state || '' }
                    onChange={ ( n, val ) => set( { base: { ...base, state: val } } ) }
                />
            </div>
        </div>
    );
}

function Rates() {
    const { data, set } = useTax();
    const countries = data.countries || {};
    const states = data.states || {};
    const rates = Array.isArray( data.rates ) ? data.rates : [];

    const setRates = ( next ) => set( { rates: next } );
    const update = ( i, patch ) => setRates( rates.map( ( r, idx ) => ( idx === i ? { ...r, ...patch } : r ) ) );
    const add = () => setRates( [ ...rates, { country: '', state: '', rate: 0 } ] );
    const remove = ( i ) => setRates( rates.filter( ( _, idx ) => idx !== i ) );

    return (
        <div className="wpuf-mt-2">
            { rates.map( ( row, i ) => {
                const stateOptions = { country_wide: __( 'Country Wide', 'wp-user-frontend' ), ...( states[ row.country ] || {} ) };
                return (
                    <div key={ i } className="wpuf-mb-3 wpuf-flex wpuf-items-center wpuf-gap-3">
                        <div className="wpuf-min-w-0 wpuf-flex-1">
                            <SelectDropdown
                                field={ { options: { '': __( '— Country —', 'wp-user-frontend' ), ...countries } } }
                                name={ `tax_rate_country_${ i }` }
                                value={ row.country || '' }
                                onChange={ ( n, val ) => update( i, { country: val, state: '' } ) }
                            />
                        </div>
                        <div className="wpuf-min-w-0 wpuf-flex-1">
                            <SelectDropdown
                                field={ { options: stateOptions } }
                                name={ `tax_rate_state_${ i }` }
                                value={ row.state || '' }
                                onChange={ ( n, val ) => update( i, { state: val } ) }
                            />
                        </div>
                        <div className="wpuf-relative wpuf-w-24 wpuf-shrink-0">
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                max="100"
                                value={ row.rate || 0 }
                                onChange={ ( e ) => update( i, { rate: e.target.value } ) }
                                className="wpuf-no-spinner wpuf-w-full wpuf-rounded-md wpuf-border wpuf-border-gray-300 wpuf-py-2.5 wpuf-pl-3 wpuf-pr-7 wpuf-text-gray-700 wpuf-shadow-sm focus:wpuf-border-gray-300"
                            />
                            <span className="wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-3 wpuf-flex wpuf-items-center wpuf-text-sm wpuf-text-gray-400">%</span>
                        </div>
                        <button
                            type="button"
                            onClick={ () => remove( i ) }
                            className="wpuf-shrink-0 wpuf-text-sm wpuf-font-medium wpuf-text-red-600 hover:wpuf-text-red-700"
                        >
                            { __( 'Remove', 'wp-user-frontend' ) }
                        </button>
                    </div>
                );
            } ) }
            <button
                type="button"
                onClick={ add }
                className="wpuf-rounded-md wpuf-border !wpuf-border-gray-300 wpuf-bg-white wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-gray-50"
            >
                + { __( 'Add Rate', 'wp-user-frontend' ) }
            </button>
        </div>
    );
}

export default function TaxSettings( { field } ) {
    return field.name === 'wpuf_tax_rates' ? <Rates /> : <BaseCountry />;
}
