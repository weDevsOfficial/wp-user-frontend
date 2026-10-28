import { useMemo, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { Radio, Select } from '@wpuf/components';
import { STORE_NAME } from '../../store';
import { HelpTextIcon } from '@wpuf/components';
import { OPERATORS, RULE_OPTIONS, isEmptyOperator, readPath, writePath } from './conditionalUtils';

// Develop's integration-conditional-logic: only choice fields can be rules,
// all with the choice operators and an option list for the value.
const ALLOWED_TEMPLATES = [ 'radio_field', 'checkbox_field', 'dropdown_field' ];

const blankRule = () => ( { name: '', operator: '=', option: '' } );

/**
 * Value options of a choice field, as develop's get_cond_options (object
 * options = key => label).
 *
 * @param {Object|undefined} field Form field.
 * @return {Array} Options.
 */
function optionsOf( field ) {
    if ( ! field || ! field.options || 'object' !== typeof field.options ) {
        return [];
    }

    if ( Array.isArray( field.options ) ) {
        return field.options
            .filter( ( option ) => option && 'object' === typeof option && option.label && undefined !== option.value )
            .map( ( option ) => ( { value: String( option.value ), label: String( option.label ) } ) );
    }

    return Object.keys( field.options ).map( ( key ) => {
        const option = field.options[ key ];

        return option && 'object' === typeof option
            ? { value: String( option.value ), label: String( option.label ) }
            : { value: key, label: String( option ) };
    } );
}

/**
 * Conditional logic of a Pro integration (develop's Vue
 * `integration-conditional-logic`, which the Mailchimp module printed on
 * `wpuf_after_registration_form_settings_field_enable_double_optin`), on the
 * shared wrappers (4.5b). Stores `{ condition_status, cond_logic,
 * conditions[ { name, operator, option } ] }` at `settingsPath` of the form
 * settings (only `{ condition_status: 'no' }` while off), as develop's save did.
 *
 * As develop: picking a field keeps a valid operator and clears the value;
 * "any / no selection" clear the value; the add button sits on the last rule,
 * the remove button shows while there is more than one rule. Unlike develop,
 * opening the tab writes nothing (owner decision 7).
 *
 * @param {Object} props
 * @param {string} props.integrationName Integration name as develop printed it (e.g. `mailchimp`).
 * @param {string} props.settingsPath    Dot path in the form settings.
 * @param {string} [props.label]         Row label.
 */
export default function IntegrationConditionalLogic( { integrationName, settingsPath, label } ) {
    const { settings, formFields } = useSelect( ( select ) => ( {
        settings: select( STORE_NAME ).getSettings(),
        formFields: select( STORE_NAME ).getFormFields(),
    } ), [] );
    const { updateFormSetting } = useDispatch( STORE_NAME );

    const fields = useMemo( () => formFields.filter( ( field ) => ALLOWED_TEMPLATES.includes( field.template ) && field.name && field.label ), [ formFields ] );
    const fieldOf = ( name ) => fields.find( ( field ) => field.name === name );

    // Seeded once (develop initializeFromSettings), then kept on screen.
    const [ state, setState ] = useState( () => {
        const stored = readPath( settings, settingsPath ) || {};

        return {
            condition_status: stored.condition_status || 'no',
            cond_logic: stored.cond_logic || 'all',
            conditions: Array.isArray( stored.conditions ) && stored.conditions.length
                ? stored.conditions.map( ( rule ) => ( { name: rule.name || '', operator: rule.operator || '=', option: rule.option || '' } ) )
                : [ blankRule() ],
        };
    } );

    // Develop's save posted hidden inputs: rule match and rules only while the
    // conditions are on, so "No" stored the status alone (the rules stay on
    // screen for this session, as develop's component kept them).
    const write = ( next ) => {
        setState( next );
        const [ key, value ] = writePath( settings, settingsPath, 'yes' === next.condition_status
            ? {
                condition_status: 'yes',
                cond_logic: next.cond_logic,
                conditions: next.conditions.map( ( rule ) => ( { name: rule.name, operator: rule.operator, option: rule.option } ) ),
            }
            : { condition_status: next.condition_status } );
        updateFormSetting( key, value );
    };
    const setRule = ( index, rule ) => write( { ...state, conditions: state.conditions.map( ( item, i ) => ( i === index ? rule : item ) ) } );

    const pickField = ( index, name ) => {
        const rule = state.conditions[ index ];
        const valid = OPERATORS.radio.some( ( operator ) => operator.value === rule.operator );

        setRule( index, { name, operator: valid ? rule.operator : OPERATORS.radio[ 0 ].value, option: '' } );
    };

    const title = integrationName.charAt( 0 ).toUpperCase() + integrationName.slice( 1 );
    const iconClass = 'size-6 border border-solid border-gray-400 rounded-2xl p-1';

    return (
        <div className="wpuf-integration-conditional-logic-container">
            <div className="my-4 wpuf-input-container">
                <div className="flex items-center">
                    <label className="flex text-sm text-gray-700 my-2">
                        { label || __( 'Conditional Logic', 'wp-user-frontend' ) }
                        <HelpTextIcon text={ sprintf(
                            /* translators: %s: integration name, e.g. mailchimp */
                            __( 'Choose whether to apply conditions for %s integration', 'wp-user-frontend' ),
                            integrationName
                        ) } />
                    </label>
                </div>
                <Radio
                    name={ `wpuf-integration-cond-${ integrationName }` }
                    options={ [
                        { value: 'yes', label: __( 'Yes', 'wp-user-frontend' ) },
                        { value: 'no', label: __( 'No', 'wp-user-frontend' ) },
                    ] }
                    value={ state.condition_status }
                    onChange={ ( status ) => write( { ...state, condition_status: status } ) }
                    inline
                    className="gap-x-8"
                />
            </div>

            { 'yes' === state.condition_status && (
                <div className="wpuf-conditional-logic-settings my-4">
                    <div className="text-sm leading-6 mb-4">
                        <span className="mb-3 block">
                            { sprintf(
                                /* translators: %s: integration name, e.g. mailchimp */
                                __( 'Apply %s integration when', 'wp-user-frontend' ),
                                integrationName
                            ) }
                        </span>
                        <div className="flex items-center">
                            <div className="w-1/3">
                                <Select options={ RULE_OPTIONS } value={ state.cond_logic } onChange={ ( logic ) => write( { ...state, cond_logic: logic } ) } aria-label={ __( 'Rule match', 'wp-user-frontend' ) } />
                            </div>
                            <span className="ml-3">{ __( 'of these rules are met', 'wp-user-frontend' ) }</span>
                        </div>
                    </div>

                    <div className="wpuf-conditional-logic-rules">
                        { state.conditions.map( ( rule, index ) => {
                            const field = fieldOf( rule.name );

                            return (
                                // eslint-disable-next-line react/no-array-index-key -- rules have no id
                                <div key={ index } className="wpuf-conditional-rule grid grid-cols-7 gap-4 mb-4">
                                    <div className="cond-field col-span-2">
                                        <Select
                                            options={ [ { value: '', label: __( '- Select Field -', 'wp-user-frontend' ) }, ...fields.map( ( item ) => ( { value: item.name, label: item.label } ) ) ] }
                                            value={ rule.name }
                                            onChange={ ( name ) => pickField( index, name ) }
                                            aria-label={ __( 'Field', 'wp-user-frontend' ) }
                                        />
                                    </div>
                                    <div className="cond-operator col-span-2">
                                        <Select
                                            options={ OPERATORS.radio }
                                            value={ rule.operator }
                                            placeholder=""
                                            onChange={ ( operator ) => setRule( index, { ...rule, operator, option: isEmptyOperator( operator ) ? '' : rule.option } ) }
                                            aria-label={ __( 'Operator', 'wp-user-frontend' ) }
                                        />
                                    </div>
                                    <div className="cond-option relative col-span-2">
                                        <Select
                                            options={ [ { value: '', label: __( '- Select Option -', 'wp-user-frontend' ) }, ...optionsOf( field ) ] }
                                            value={ rule.option }
                                            disabled={ isEmptyOperator( rule.operator ) }
                                            onChange={ ( option ) => setRule( index, { ...rule, option } ) }
                                            aria-label={ __( 'Value', 'wp-user-frontend' ) }
                                        />
                                    </div>
                                    <div className="cond-action-btns flex items-center my-2">
                                        { state.conditions.length > 1 && (
                                            <button type="button" className="wpuf-repeater-remove p-0 mr-2 border-0 bg-transparent text-gray-700 cursor-pointer rounded-full hover:text-primary focus:outline-hidden focus:shadow-none focus-visible:ring-2 focus-visible:ring-primary/30" onClick={ () => write( { ...state, conditions: state.conditions.filter( ( _, i ) => i !== index ) } ) } aria-label={ __( 'Remove Condition', 'wp-user-frontend' ) }>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className={ iconClass } aria-hidden="true">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14" />
                                                </svg>
                                            </button>
                                        ) }
                                        { state.conditions.length === index + 1 && (
                                            <button type="button" className="wpuf-repeater-add p-0 border-0 bg-transparent text-gray-700 cursor-pointer rounded-full hover:text-primary focus:outline-hidden focus:shadow-none focus-visible:ring-2 focus-visible:ring-primary/30" onClick={ () => write( { ...state, conditions: [ ...state.conditions, blankRule() ] } ) } aria-label={ __( 'Add Condition', 'wp-user-frontend' ) }>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className={ `ml-1 ${ iconClass }` } aria-hidden="true">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </button>
                                        ) }
                                    </div>
                                </div>
                            );
                        } ) }
                    </div>

                    <div className="description text-sm text-gray-600 mt-2">
                        { sprintf(
                            /* translators: %s: integration name, e.g. Mailchimp */
                            __( '%s integration will run if the above conditions are met.', 'wp-user-frontend' ),
                            title
                        ) }
                    </div>
                </div>
            ) }
        </div>
    );
}
