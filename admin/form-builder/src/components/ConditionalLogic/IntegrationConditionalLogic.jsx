import { useMemo, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import ConditionRow from './ConditionRow';
import { RULE_OPTIONS } from './conditionalUtils';

const ALLOWED_TEMPLATES = [
    'radio_field',
    'checkbox_field',
    'dropdown_field',
];

/**
 * Integration conditional logic (Pro).
 *
 * Used inside integration settings panels. Stores data at a configurable path
 * in the settings object (e.g., `settings.mailchimp.conditional_logic`).
 *
 * Uses the object-array format:
 * { condition_status, cond_logic, conditions: [{ name, operator, option }] }
 *
 * @param {Object}   props
 * @param {string}   props.integrationName - Integration identifier
 * @param {string}   props.settingsPath    - Dot-separated path in settings (e.g., "mailchimp.conditional_logic")
 * @param {Object}   props.currentSettings - Current conditional logic settings object
 * @param {string}   props.label           - Section heading
 * @param {Function} props.onUpdate        - Callback when settings change: (newSettings) => void
 */
export default function IntegrationConditionalLogic( {
    integrationName,
    settingsPath,
    currentSettings: propSettings,
    label,
    onUpdate,
} ) {
    const formFields = useSelect( ( select ) => select( STORE_NAME ).getFormFields(), [] );

    const currentSettings = propSettings || {
        condition_status: 'no',
        cond_logic: 'all',
        conditions: [ { name: '', operator: '=', option: '' } ],
    };

    const availableFields = useMemo( () => {
        const fields = [];

        formFields.forEach( ( field ) => {
            if (
                ALLOWED_TEMPLATES.includes( field.template ) &&
                field.name &&
                field.label
            ) {
                fields.push( field );
            }
        } );

        return fields;
    }, [ formFields ] );

    const conditions = currentSettings.conditions && currentSettings.conditions.length
        ? currentSettings.conditions
        : [ { name: '', operator: '=', option: '' } ];

    const isEnabled = currentSettings.condition_status === 'yes';

    const persist = useCallback( ( updates ) => {
        const newSettings = { ...currentSettings, ...updates };

        if ( onUpdate ) {
            onUpdate( newSettings );
        }
    }, [ currentSettings, onUpdate ] );

    function handleToggle( status ) {
        persist( { condition_status: status } );
    }

    function handleLogicChange( e ) {
        persist( { cond_logic: e.target.value } );
    }

    function handleConditionChange( index, updated ) {
        const newConditions = [ ...conditions ];
        newConditions[ index ] = {
            name: updated.name,
            operator: updated.operator,
            option: updated.option,
        };
        persist( { conditions: newConditions } );
    }

    function handleAddCondition() {
        persist( {
            conditions: [
                ...conditions,
                { name: '', operator: '=', option: '' },
            ],
        } );
    }

    function handleRemoveCondition( index ) {
        if ( conditions.length <= 1 ) {
            return;
        }
        persist( { conditions: conditions.filter( ( _, i ) => i !== index ) } );
    }

    return (
        <div className="wpuf-conditional-logic wpuf-border-t wpuf-border-gray-200 wpuf-pt-4 wpuf-mt-4">
            <h4 className="wpuf-text-sm wpuf-font-semibold wpuf-text-gray-700 wpuf-mb-3">
                { label || __( 'Conditional Logic', 'wp-user-frontend' ) }
            </h4>

            <div className="wpuf-flex wpuf-items-center wpuf-gap-4 wpuf-mb-3">
                <label className="wpuf-flex wpuf-items-center wpuf-gap-1 wpuf-text-sm wpuf-cursor-pointer">
                    <input
                        type="radio"
                        name={ `wpuf_integration_cond_${ integrationName }` }
                        value="yes"
                        checked={ isEnabled }
                        onChange={ () => handleToggle( 'yes' ) }
                    />
                    { __( 'Enable', 'wp-user-frontend' ) }
                </label>
                <label className="wpuf-flex wpuf-items-center wpuf-gap-1 wpuf-text-sm wpuf-cursor-pointer">
                    <input
                        type="radio"
                        name={ `wpuf_integration_cond_${ integrationName }` }
                        value="no"
                        checked={ ! isEnabled }
                        onChange={ () => handleToggle( 'no' ) }
                    />
                    { __( 'Disable', 'wp-user-frontend' ) }
                </label>
            </div>

            { isEnabled && (
                <div className="conditional-rules-wrap">
                    <div className="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-mb-3 wpuf-text-sm">
                        <span>{ __( 'Apply when', 'wp-user-frontend' ) }</span>
                        <select
                            className="wpuf-border wpuf-border-gray-300 wpuf-rounded wpuf-px-2 wpuf-py-1 wpuf-text-sm"
                            value={ currentSettings.cond_logic || 'all' }
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
