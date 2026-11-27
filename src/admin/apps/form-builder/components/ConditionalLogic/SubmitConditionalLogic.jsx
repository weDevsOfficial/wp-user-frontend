import { useMemo, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Radio, Select, TextInput } from '@wpuf/components';
import { STORE_NAME } from '../../store';
import { HelpTextIcon } from '@wpuf/components';
import { OPERATORS, RULE_OPTIONS, isEmptyOperator } from './conditionalUtils';
import { builderData } from '../../utils/boot';

// Develop's submit-button-conditional-logic: fields a rule can use (top level).
const ALLOWED_TEMPLATES = [ 'radio_field', 'checkbox_field', 'dropdown_field', 'text_field', 'textarea_field', 'email_address', 'numeric_text_field' ];
const TEXT_TEMPLATES = [ 'text_field', 'textarea_field', 'email_address', 'numeric_text_field' ];
const INPUT_TYPES = {
    radio_field: 'radio',
    checkbox_field: 'checkbox',
    dropdown_field: 'select',
    text_field: 'text',
    textarea_field: 'textarea',
    email_address: 'email',
    numeric_text_field: 'numeric_text',
};

const blankRule = () => ( { name: '', operator: '=', option: '', input_type: '' } );

/**
 * Operators for a rule's field (develop get_cond_operators): choice fields get
 * is / is not / any / no selection, text fields the text set, numbers the
 * number set; no field picked yet shows the text set.
 *
 * @param {Object|undefined} field Form field.
 * @return {Array} Operators.
 */
function submitOperators( field ) {
    if ( ! field ) {
        return OPERATORS.text;
    }

    switch ( field.template ) {
        case 'radio_field':
        case 'dropdown_field':
        case 'checkbox_field':
            return OPERATORS.radio;
        case 'text_field':
        case 'textarea_field':
        case 'email_address':
            return OPERATORS.text;
        case 'numeric_text_field':
            return OPERATORS.number;
        default:
            return OPERATORS.others;
    }
}

/**
 * Conditional Logic on Submit Button (Pro post forms; develop's Vue
 * submit-button-conditional-logics printed on
 * `wpuf_after_post_form_settings_field_limit_message`), on the shared
 * wrappers (4.4e). Stores `submit_button_cond { condition_status, cond_logic,
 * conditions[ { name, operator, option, input_type } ] }`.
 *
 * As develop: picking a field selects its first operator, clears the value
 * and stores its input type; "has any / no value" operators clear the value;
 * new rules go to the end; the last rule cannot be removed. Unlike develop,
 * opening the tab writes nothing (develop's component rewrote stored rules on
 * mount; agreed deviation as for field conditions, 4.4d).
 *
 * @param {Object} props
 * @param {string} [props.label] Row label.
 */
export default function SubmitConditionalLogic( { label } ) {
    const { stored, formFields } = useSelect( ( select ) => ( {
        stored: select( STORE_NAME ).getSettings().submit_button_cond,
        formFields: select( STORE_NAME ).getFormFields(),
    } ), [] );
    const { updateFormSetting } = useDispatch( STORE_NAME );

    const fields = useMemo( () => formFields.filter( ( field ) => ALLOWED_TEMPLATES.includes( field.template ) && field.name && field.label ), [ formFields ] );
    const fieldOf = ( name ) => fields.find( ( field ) => field.name === name );

    // Seeded once (develop initializeFromSettings), then kept on screen.
    const [ state, setState ] = useState( () => ( {
        condition_status: ( stored && stored.condition_status ) || 'no',
        cond_logic: ( stored && stored.cond_logic ) || 'any',
        conditions: stored && Array.isArray( stored.conditions ) && stored.conditions.length
            ? stored.conditions.map( ( rule ) => ( { name: rule.name || '', operator: rule.operator || '=', option: rule.option || '', input_type: rule.input_type || '' } ) )
            : [ blankRule() ],
    } ) );

    const write = ( next ) => {
        setState( next );
        updateFormSetting( 'submit_button_cond', {
            condition_status: next.condition_status,
            cond_logic: next.cond_logic,
            conditions: next.conditions.map( ( rule ) => ( { name: rule.name, operator: rule.operator, option: rule.option, input_type: rule.input_type } ) ),
        } );
    };
    const setRule = ( index, rule ) => write( { ...state, conditions: state.conditions.map( ( item, i ) => ( i === index ? rule : item ) ) } );

    const pickField = ( index, name ) => {
        const field = fieldOf( name );
        const operators = submitOperators( field );

        setRule( index, {
            name,
            operator: operators.length ? operators[ 0 ].value : '=',
            option: '',
            input_type: field ? ( INPUT_TYPES[ field.template ] || field.template.replace( '_field', '' ) ) : '',
        } );
    };

    const assetUrl = builderData().asset_url || '';

    return (
        <div className="wpuf-submit-button-conditional-logic-container">
            <div className="my-4 wpuf-input-container">
                <div className="flex items-center">
                    <label className="flex text-sm text-gray-700 my-2">
                        { label || __( 'Conditional Logic on Submit Button', 'wp-user-frontend' ) }
                    </label>
                    <HelpTextIcon text={ __( 'Choose whether to apply conditions for submit button visibility', 'wp-user-frontend' ) } />
                </div>
                <Radio
                    name="wpuf-submit-cond-status"
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
                        <span className="mb-3 block">{ __( 'Show submit button when', 'wp-user-frontend' ) }</span>
                        <div className="flex items-center">
                            <div className="w-1/3">
                                <Select options={ RULE_OPTIONS } value={ state.cond_logic } onChange={ ( logic ) => write( { ...state, cond_logic: logic } ) } />
                            </div>
                            <span className="ml-3">{ __( 'of these rules are met', 'wp-user-frontend' ) }</span>
                        </div>
                    </div>

                    <div className="wpuf-conditional-logic-rules">
                        { state.conditions.map( ( rule, index ) => {
                            const field = fieldOf( rule.name );
                            const isText = ! field || TEXT_TEMPLATES.includes( field.template );
                            const disabled = isEmptyOperator( rule.operator );

                            return (
                                // eslint-disable-next-line react/no-array-index-key -- rules have no id
                                <div key={ index } className="wpuf-conditional-rule grid grid-cols-4 gap-4 mb-4 items-center">
                                    <div className="cond-field">
                                        <Select
                                            options={ [ { value: '', label: __( '- Select Field -', 'wp-user-frontend' ) }, ...fields.map( ( item ) => ( { value: item.name, label: item.label } ) ) ] }
                                            value={ rule.name }
                                            onChange={ ( name ) => pickField( index, name ) }
                                            aria-label={ __( 'Field', 'wp-user-frontend' ) }
                                        />
                                    </div>
                                    <div className="cond-operator">
                                        <Select
                                            options={ submitOperators( field ) }
                                            value={ rule.operator }
                                            placeholder=""
                                            onChange={ ( operator ) => setRule( index, { ...rule, operator, option: isEmptyOperator( operator ) ? '' : rule.option } ) }
                                            aria-label={ __( 'Operator', 'wp-user-frontend' ) }
                                        />
                                    </div>
                                    <div className="cond-option">
                                        { isText ? (
                                            <TextInput className="w-full" value={ rule.option } disabled={ disabled } onChange={ ( option ) => setRule( index, { ...rule, option } ) } aria-label={ __( 'Value', 'wp-user-frontend' ) } />
                                        ) : (
                                            <Select
                                                options={ [ { value: '', label: __( '- Select Option -', 'wp-user-frontend' ) }, ...Object.keys( field.options || {} ).map( ( key ) => ( { value: key, label: String( field.options[ key ] ) } ) ) ] }
                                                value={ rule.option }
                                                disabled={ disabled }
                                                onChange={ ( option ) => setRule( index, { ...rule, option } ) }
                                                aria-label={ __( 'Value', 'wp-user-frontend' ) }
                                            />
                                        ) }
                                    </div>
                                    <div className="flex gap-1">
                                        <button type="button" className="wpuf-repeater-add p-0 border-0 bg-transparent cursor-pointer rounded-full focus:outline-hidden focus:shadow-none focus-visible:ring-2 focus-visible:ring-primary/30" onClick={ () => write( { ...state, conditions: [ ...state.conditions, blankRule() ] } ) } aria-label={ __( 'Add Condition', 'wp-user-frontend' ) }>
                                            <img src={ `${ assetUrl }/images/plus-circle-green.svg` } alt="" />
                                        </button>
                                        { state.conditions.length > 1 && (
                                            <button type="button" className="wpuf-repeater-remove p-0 border-0 bg-transparent cursor-pointer rounded-full focus:outline-hidden focus:shadow-none focus-visible:ring-2 focus-visible:ring-primary/30" onClick={ () => write( { ...state, conditions: state.conditions.filter( ( _, i ) => i !== index ) } ) } aria-label={ __( 'Remove Condition', 'wp-user-frontend' ) }>
                                                <img src={ `${ assetUrl }/images/minus-circle-green.svg` } alt="" />
                                            </button>
                                        ) }
                                    </div>
                                </div>
                            );
                        } ) }
                    </div>
                    <p className="description text-sm text-gray-600 mt-2 mb-0">
                        { __( 'Submit button will be shown/hidden based on the above conditions.', 'wp-user-frontend' ) }
                    </p>
                </div>
            ) }
        </div>
    );
}
