import { __ } from '@wordpress/i18n';
import { Select, TextInput } from '@wpuf/components';
import {
    getOperatorsForType,
    isEmptyOperator,
    isDropdownType,
    getFieldOptions,
    conditionInputType,
    conditionFieldType,
} from './conditionalUtils';
import { builderData } from '../../utils/boot';

/**
 * One condition rule: field, operator and value stacked full width, then the
 * green + / - buttons (develop's field-conditional-logic row, task 4.4d), on
 * the shared Select / TextInput wrappers.
 *
 * Row edits follow develop's Vue component:
 * - picking a field resets the value to the field's first option (or ''),
 *   selects the first operator for its input type and stores the field's
 *   input / field type; the option title is kept, except for a taxonomy field,
 *   which gets its first term's name (the frontend compares a tag-style
 *   taxonomy input with `option_title`; develop left it stale: fix, 4.4d);
 * - changing the operator keeps the value on screen (the stored value is ''
 *   for "has any / no value", see buildCondArrays);
 * - typing a value changes only `option`; picking one also its title.
 *
 * @param {Object}   props
 * @param {Object}   props.condition       { name, operator, option, option_title, input_type, field_type }
 * @param {number}   props.index
 * @param {Array}    props.availableFields Fields a rule can depend on.
 * @param {Array}    [props.taxonomies]    Hierarchical taxonomy names.
 * @param {Object}   props.wpPostTypes     `wpuf_form_builder.wp_post_types` (taxonomy terms).
 * @param {Function} props.onChange        ( index, row ) => void
 * @param {Function} [props.onAdd]         () => void; shows the + button.
 * @param {Function} props.onRemove        ( index ) => void
 * @param {boolean}  [props.canRemove]     false hides the - button (callers without the last-row alert).
 */
export default function ConditionRow( {
    condition,
    index,
    availableFields,
    taxonomies = [],
    wpPostTypes,
    onChange,
    onAdd,
    onRemove,
    canRemove = true,
} ) {
    const assetUrl = builderData().asset_url || '';
    const optionsOf = ( name ) => getFieldOptions( availableFields.find( ( item ) => item.name === name ), wpPostTypes, taxonomies );
    const operators = getOperatorsForType( condition.input_type );
    const fieldOptions = isDropdownType( condition.input_type ) ? optionsOf( condition.name ) : [];
    const disabled = isEmptyOperator( condition.operator );

    function handleFieldChange( name ) {
        const field = availableFields.find( ( item ) => item.name === name );
        const inputType = field ? conditionInputType( field ) : undefined;
        const first = optionsOf( name )[ 0 ];
        const isTaxonomy = field && 'taxonomy' === field.template;

        onChange( index, {
            ...condition,
            name,
            input_type: inputType,
            field_type: field ? conditionFieldType( field ) : undefined,
            operator: ( getOperatorsForType( inputType )[ 0 ] || {} ).value ?? '',
            option: first ? first.raw : '',
            option_title: isTaxonomy && first ? first.label : condition.option_title,
        } );
    }

    function handleOptionPick( picked ) {
        const option = fieldOptions.find( ( item ) => item.value === picked );

        onChange( index, {
            ...condition,
            option: option ? option.raw : picked,
            option_title: option ? option.label : '',
        } );
    }

    return (
        <li className="mb-1.5">
            <div className="cond-field mb-2">
                <Select
                    options={ [
                        { value: '', label: __( '- Select -', 'wp-user-frontend' ) },
                        ...availableFields.map( ( item ) => ( { value: item.name, label: item.label } ) ),
                    ] }
                    value={ condition.name ?? '' }
                    onChange={ handleFieldChange }
                    aria-label={ __( 'Field', 'wp-user-frontend' ) }
                />
            </div>

            <div className="cond-operator mb-2">
                <Select
                    options={ operators }
                    value={ condition.operator }
                    placeholder=""
                    onChange={ ( operator ) => onChange( index, { ...condition, operator } ) }
                    aria-label={ __( 'Operator', 'wp-user-frontend' ) }
                />
            </div>

            <div className="cond-option">
                { isDropdownType( condition.input_type ) ? (
                    <Select
                        options={ fieldOptions }
                        value={ condition.option }
                        placeholder=""
                        disabled={ disabled }
                        onChange={ handleOptionPick }
                        aria-label={ __( 'Value', 'wp-user-frontend' ) }
                    />
                ) : (
                    <TextInput
                        className="w-full"
                        value={ condition.option ?? '' }
                        disabled={ disabled }
                        onChange={ ( option ) => onChange( index, { ...condition, option } ) }
                        aria-label={ __( 'Value', 'wp-user-frontend' ) }
                    />
                ) }
            </div>

            <div className="cond-action-btns flex my-2">
                { onAdd && (
                    <button type="button" className="wpuf-repeater-add p-0 border-0 bg-transparent cursor-pointer" onClick={ onAdd } aria-label={ __( 'Add condition', 'wp-user-frontend' ) }>
                        <img src={ `${ assetUrl }/images/plus-circle-green.svg` } alt="" />
                    </button>
                ) }
                { canRemove && (
                    <button type="button" className="wpuf-repeater-remove p-0 border-0 bg-transparent cursor-pointer" onClick={ () => onRemove( index ) } aria-label={ __( 'Remove condition', 'wp-user-frontend' ) }>
                        <img src={ `${ assetUrl }/images/minus-circle-green.svg` } alt="" />
                    </button>
                ) }
            </div>
        </li>
    );
}
