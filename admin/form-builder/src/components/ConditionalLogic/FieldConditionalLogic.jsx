import { useState, useMemo, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import ConditionRow from './ConditionRow';
import { RULE_OPTIONS, templateToInputType, isEmptyOperator } from './conditionalUtils';

/**
 * Field-level conditional logic panel (Pro).
 *
 * Reads/writes the field's `wpuf_cond` property using the parallel-array format:
 * { condition_status, cond_logic, cond_field[], cond_operator[], cond_option[],
 *   input_type[], field_type[], option_title[] }
 */
export default function FieldConditionalLogic() {
    const { editingField, formFields } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            editingField: store.getEditingField(),
            formFields: store.getFormFields(),
        };
    }, [] );

    const { updateField } = useDispatch( STORE_NAME );

    const wpufCond = editingField?.wpuf_cond || {
        condition_status: 'no',
        cond_logic: 'all',
        cond_field: [],
        cond_operator: [ '=' ],
        cond_option: [ '' ],
    };

    const data = window.wpuf_form_builder || {};
    const supportedFields = data.wpuf_cond_supported_fields || [];
    const wpPostTypes = data.wp_post_types || {};

    // Build hierarchical taxonomies list
    const hierarchicalTaxonomies = useMemo( () => {
        const taxList = [];
        Object.values( wpPostTypes ).forEach( ( taxonomies ) => {
            Object.entries( taxonomies ).forEach( ( [ taxonomy, props ] ) => {
                if ( props.hierarchical ) {
                    taxList.push( taxonomy );
                }
            } );
        } );
        return taxList;
    }, [ wpPostTypes ] );

    const allSupportedFields = useMemo( () => {
        return [ ...supportedFields, ...hierarchicalTaxonomies ];
    }, [ supportedFields, hierarchicalTaxonomies ] );

    // Build dependency fields (all eligible fields except the currently editing one)
    const availableFields = useMemo( () => {
        const deps = [];

        formFields.forEach( ( field ) => {
            if ( field.template === 'column_field' && field.inner_fields ) {
                Object.values( field.inner_fields ).forEach( ( colFields ) => {
                    ( colFields || [] ).forEach( ( innerField ) => {
                        const matchKey = innerField.template === 'taxonomy' ? innerField.name : innerField.template;
                        if (
                            allSupportedFields.includes( matchKey ) &&
                            innerField.name &&
                            innerField.label &&
                            innerField.name !== editingField?.name
                        ) {
                            deps.push( innerField );
                        }
                    } );
                } );
            } else if ( field.template === 'repeat_field' && Array.isArray( field.inner_fields ) ) {
                field.inner_fields.forEach( ( innerField ) => {
                    const matchKey = innerField.template === 'taxonomy' ? innerField.name : innerField.template;
                    if (
                        allSupportedFields.includes( matchKey ) &&
                        innerField.name &&
                        innerField.label &&
                        innerField.name !== editingField?.name
                    ) {
                        deps.push( innerField );
                    }
                } );
            } else {
                const matchKey = field.template === 'taxonomy' ? field.name : field.template;
                if (
                    allSupportedFields.includes( matchKey ) &&
                    field.name &&
                    field.label &&
                    field.name !== editingField?.name
                ) {
                    deps.push( field );
                }
            }
        } );

        return deps;
    }, [ formFields, allSupportedFields, editingField?.name ] );

    // Convert parallel arrays to condition objects for ConditionRow
    const conditions = useMemo( () => {
        const condFields = wpufCond.cond_field || [];

        if ( ! condFields.length ) {
            return [ { name: '', operator: '=', option: '', input_type: '' } ];
        }

        return condFields.map( ( name, i ) => ( {
            name: name || '',
            operator: ( wpufCond.cond_operator || [] )[ i ] || '=',
            option: ( wpufCond.cond_option || [] )[ i ] || '',
            input_type: ( wpufCond.input_type || [] )[ i ] || '',
        } ) );
    }, [ wpufCond ] );

    const isEnabled = wpufCond.condition_status === 'yes';

    // Persist updated wpuf_cond back to store
    const persistCond = useCallback( ( updates ) => {
        if ( ! editingField ) {
            return;
        }
        updateField( editingField.id, 'wpuf_cond', {
            ...wpufCond,
            ...updates,
        } );
    }, [ editingField, wpufCond, updateField ] );

    function handleToggle( status ) {
        persistCond( { condition_status: status } );
    }

    function handleLogicChange( e ) {
        persistCond( { cond_logic: e.target.value } );
    }

    function handleConditionChange( index, updated ) {
        const newCondFields = [ ...( wpufCond.cond_field || [] ) ];
        const newCondOperators = [ ...( wpufCond.cond_operator || [] ) ];
        const newCondOptions = [ ...( wpufCond.cond_option || [] ) ];
        const newInputTypes = [ ...( wpufCond.input_type || [] ) ];
        const newFieldTypes = [ ...( wpufCond.field_type || [] ) ];

        newCondFields[ index ] = updated.name;
        newCondOperators[ index ] = updated.operator;
        newCondOptions[ index ] = isEmptyOperator( updated.operator ) ? '' : updated.option;
        newInputTypes[ index ] = updated.input_type;
        newFieldTypes[ index ] = updated.input_type;

        persistCond( {
            cond_field: newCondFields,
            cond_operator: newCondOperators,
            cond_option: newCondOptions,
            input_type: newInputTypes,
            field_type: newFieldTypes,
        } );
    }

    function handleAddCondition() {
        persistCond( {
            cond_field: [ ...( wpufCond.cond_field || [] ), '' ],
            cond_operator: [ ...( wpufCond.cond_operator || [] ), '=' ],
            cond_option: [ ...( wpufCond.cond_option || [] ), '' ],
            input_type: [ ...( wpufCond.input_type || [] ), '' ],
            field_type: [ ...( wpufCond.field_type || [] ), '' ],
        } );
    }

    function handleRemoveCondition( index ) {
        if ( conditions.length <= 1 ) {
            return;
        }

        const remove = ( arr ) => ( arr || [] ).filter( ( _, i ) => i !== index );

        persistCond( {
            cond_field: remove( wpufCond.cond_field ),
            cond_operator: remove( wpufCond.cond_operator ),
            cond_option: remove( wpufCond.cond_option ),
            input_type: remove( wpufCond.input_type ),
            field_type: remove( wpufCond.field_type ),
            option_title: remove( wpufCond.option_title ),
        } );
    }

    if ( ! editingField ) {
        return null;
    }

    return (
        <div className="wpuf-conditional-logic wpuf-border-t wpuf-border-gray-200 wpuf-pt-4 wpuf-mt-4">
            <h4 className="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-700 wpuf-mb-3">
                { __( 'Conditional Logic', 'wp-user-frontend' ) }
            </h4>

            { /* Enable / Disable */ }
            <div className="wpuf-flex wpuf-items-center wpuf-gap-4 wpuf-mb-3">
                <label className="wpuf-flex wpuf-items-center wpuf-gap-1 wpuf-text-sm wpuf-cursor-pointer">
                    <input
                        type="radio"
                        name={ `wpuf_cond_status_${ editingField.id }` }
                        value="yes"
                        checked={ isEnabled }
                        onChange={ () => handleToggle( 'yes' ) }
                    />
                    { __( 'Enable', 'wp-user-frontend' ) }
                </label>
                <label className="wpuf-flex wpuf-items-center wpuf-gap-1 wpuf-text-sm wpuf-cursor-pointer">
                    <input
                        type="radio"
                        name={ `wpuf_cond_status_${ editingField.id }` }
                        value="no"
                        checked={ ! isEnabled }
                        onChange={ () => handleToggle( 'no' ) }
                    />
                    { __( 'Disable', 'wp-user-frontend' ) }
                </label>
            </div>

            { isEnabled && (
                <div className="conditional-rules-wrap">
                    { /* Logic selector */ }
                    <div className="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-mb-3 wpuf-text-sm">
                        <span>{ __( 'Show this field when', 'wp-user-frontend' ) }</span>
                        <select
                            className="wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-px-2 wpuf-py-1 wpuf-text-sm"
                            value={ wpufCond.cond_logic || 'all' }
                            onChange={ handleLogicChange }
                        >
                            { RULE_OPTIONS.map( ( opt ) => (
                                <option key={ opt.value } value={ opt.value }>
                                    { opt.label }
                                </option>
                            ) ) }
                        </select>
                        <span>{ __( 'of these rules match', 'wp-user-frontend' ) }</span>
                    </div>

                    { /* Condition rows */ }
                    { conditions.map( ( condition, i ) => (
                        <ConditionRow
                            key={ i }
                            condition={ condition }
                            index={ i }
                            availableFields={ availableFields }
                            onChange={ handleConditionChange }
                            onRemove={ handleRemoveCondition }
                            canRemove={ conditions.length > 1 }
                        />
                    ) ) }

                    { /* Add condition */ }
                    <button
                        type="button"
                        className="wpuf-text-sm wpuf-text-blue-600 hover:wpuf-text-blue-800 wpuf-mt-1"
                        onClick={ handleAddCondition }
                    >
                        + { __( 'Add condition', 'wp-user-frontend' ) }
                    </button>
                </div>
            ) }
        </div>
    );
}
