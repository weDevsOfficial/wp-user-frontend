import { __ } from '@wordpress/i18n';
import {
    getOperatorsForType,
    isEmptyOperator,
    isDropdownType,
    getFieldOptions,
    templateToInputType,
} from './conditionalUtils';

/**
 * Single condition row — field selector, operator selector, value input, remove button.
 *
 * @param {Object}   props
 * @param {Object}   props.condition        - { name, operator, option, input_type }
 * @param {number}   props.index
 * @param {Array}    props.availableFields  - fields that support conditional logic
 * @param {Function} props.onChange         - (index, updatedCondition) => void
 * @param {Function} props.onRemove        - (index) => void
 * @param {boolean}  props.canRemove       - whether the remove button is enabled
 */
export default function ConditionRow( {
    condition,
    index,
    availableFields,
    onChange,
    onRemove,
    canRemove,
} ) {
    const selectedField = availableFields.find( ( f ) => f.name === condition.name );
    const inputType = selectedField ? templateToInputType( selectedField.template ) : ( condition.input_type || '' );
    const operators = getOperatorsForType( inputType );
    const showDropdown = isDropdownType( inputType ) && selectedField;
    const fieldOptions = showDropdown ? getFieldOptions( selectedField ) : [];
    const disabled = isEmptyOperator( condition.operator );

    function handleFieldChange( e ) {
        const fieldName = e.target.value;
        const field = availableFields.find( ( f ) => f.name === fieldName );
        const newInputType = field ? templateToInputType( field.template ) : '';
        const newOperators = getOperatorsForType( newInputType );

        onChange( index, {
            ...condition,
            name: fieldName,
            input_type: newInputType,
            operator: newOperators.length > 0 ? newOperators[ 0 ].value : '=',
            option: '',
        } );
    }

    function handleOperatorChange( e ) {
        const operator = e.target.value;
        onChange( index, {
            ...condition,
            operator,
            option: isEmptyOperator( operator ) ? '' : condition.option,
        } );
    }

    function handleOptionChange( e ) {
        onChange( index, {
            ...condition,
            option: e.target.value,
        } );
    }

    return (
        <div className="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-mb-2">
            { /* Field selector */ }
            <select
                className="cond-field wpuf-flex-1 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-px-2 wpuf-py-1 wpuf-text-sm"
                value={ condition.name }
                onChange={ handleFieldChange }
            >
                <option value="">{ __( '- select -', 'wp-user-frontend' ) }</option>
                { availableFields.map( ( field ) => (
                    <option
                        key={ field.name }
                        value={ field.name }
                        data-type={ templateToInputType( field.template ) }
                    >
                        { field.label }
                    </option>
                ) ) }
            </select>

            { /* Operator selector */ }
            <select
                className="cond-operator wpuf-flex-1 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-px-2 wpuf-py-1 wpuf-text-sm"
                value={ condition.operator }
                onChange={ handleOperatorChange }
            >
                { operators.map( ( op ) => (
                    <option key={ op.value } value={ op.value }>
                        { op.label }
                    </option>
                ) ) }
            </select>

            { /* Value input — dropdown or text based on field type */ }
            { showDropdown ? (
                <select
                    className="cond-option wpuf-flex-1 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-px-2 wpuf-py-1 wpuf-text-sm"
                    value={ condition.option }
                    onChange={ handleOptionChange }
                    disabled={ disabled }
                >
                    <option value="">{ __( '- select -', 'wp-user-frontend' ) }</option>
                    { fieldOptions.map( ( opt ) => (
                        <option key={ opt.value } value={ opt.value }>
                            { opt.label }
                        </option>
                    ) ) }
                </select>
            ) : (
                <input
                    type="text"
                    className="cond-option wpuf-flex-1 wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-px-2 wpuf-py-1 wpuf-text-sm"
                    value={ condition.option }
                    onChange={ handleOptionChange }
                    disabled={ disabled }
                    placeholder={ disabled ? '' : __( 'Enter value', 'wp-user-frontend' ) }
                />
            ) }

            { /* Remove button */ }
            <button
                type="button"
                className="wpuf-text-red-500 hover:wpuf-text-red-700 wpuf-p-1"
                onClick={ () => onRemove( index ) }
                disabled={ ! canRemove }
                title={ __( 'Remove condition', 'wp-user-frontend' ) }
            >
                <svg className="wpuf-w-4 wpuf-h-4" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    );
}
