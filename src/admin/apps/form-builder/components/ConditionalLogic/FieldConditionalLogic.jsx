import { useState, useMemo, useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Radio, Select } from '@wpuf/components';
import { STORE_NAME } from '../../store';
import { showOops } from '../Dnd/usePaletteDrop';
import ConditionRow from './ConditionRow';
import { RULE_OPTIONS, buildCondArrays, condRowsFromStored, condDependencies, hierarchicalTaxonomies } from './conditionalUtils';
import { builderData } from '../../utils/boot';

/**
 * Field option "Conditional Logic" (setting type `conditional-logic`, added
 * by Pro's Fields_Manager::add_conditional_field). Replaces develop's Vue
 * field-conditional-logic (task 4.4d, B14) with the same stored shape:
 * `wpuf_cond { condition_status, cond_logic, cond_field[], cond_operator[],
 * cond_option[], input_type[], field_type[], option_title[] }`.
 *
 * As develop:
 * - a field stored without `wpuf_cond` (Custom HTML, Shortcode, Terms and
 *   Conditions are created without one) shows develop's default: status No,
 *   All, one blank rule row. Develop's component wrote that default when the
 *   panel opened; here it is stored with the first change only;
 * - opening the panel writes nothing; Yes / No and All / Any change only
 *   their own key;
 * - the rule rows start from the stored rows that have a field and an
 *   operator (else one blank row) and every row edit rewrites the six arrays
 *   from the rows on screen.
 *
 * Props: { optionField, field, value, onChange } (SettingInput).
 */
export default function FieldConditionalLogic( props ) {
    const { field } = props;

    if ( ! field ) {
        return null;
    }

    // Rows are local state seeded once per field, as the Vue component's `created`.
    return <ConditionalLogicPanel key={ field.id } { ...props } />;
}

function ConditionalLogicPanel( { optionField, field, value: stored, onChange } ) {
    const formFields = useSelect( ( select ) => select( STORE_NAME ).getFormFields(), [] );
    // Develop's default for a field without `wpuf_cond` (its watcher's first write).
    const value = useMemo(
        () => ( stored && stored.condition_status ? stored : buildCondArrays( undefined, condRowsFromStored( undefined ) ) ),
        [ stored ]
    );
    const i18n = useSelect( ( select ) => select( STORE_NAME ).getI18n?.() || {}, [] );

    const data = builderData();
    const wpPostTypes = data.wp_post_types || {};
    const taxonomies = useMemo( () => hierarchicalTaxonomies( wpPostTypes ), [ wpPostTypes ] );
    const supported = useMemo( () => [ ...( data.wpuf_cond_supported_fields || [] ), ...taxonomies ], [ data.wpuf_cond_supported_fields, taxonomies ] );
    const dependencies = useMemo(
        () => condDependencies( formFields, supported, field.name ),
        [ formFields, supported, field.name ]
    );

    const [ conditions, setConditions ] = useState( () => condRowsFromStored( value, formFields ) );

    const writeRows = useCallback( ( rows ) => {
        setConditions( rows );
        onChange( buildCondArrays( value, rows ) );
    }, [ value, onChange ] );

    const changeRow = ( index, row ) => writeRows( conditions.map( ( item, i ) => ( i === index ? row : item ) ) );

    // Develop appends a new row at the end, whichever row's + was clicked.
    const addRow = () => writeRows( [ ...conditions, { name: '', operator: '', option: '', option_title: '', input_type: '', field_type: '' } ] );

    const removeRow = ( index ) => {
        if ( 1 === conditions.length ) {
            showOops( i18n.last_choice_warn_msg || __( 'This field must contain at least one choice', 'wp-user-frontend' ) );
            return;
        }

        writeRows( conditions.filter( ( _, i ) => i !== index ) );
    };

    const isEnabled = 'yes' === value.condition_status;

    return (
        <div className="panel-field-opt panel-field-opt-conditional-logic">
            <label className="wpuf-option-field-title text-gray-700 font-medium">
                { optionField.title || __( 'Conditional Logic', 'wp-user-frontend' ) }
            </label>

            <Radio
                name={ `wpuf-cond-status-${ field.id }` }
                options={ [
                    { value: 'yes', label: __( 'Yes', 'wp-user-frontend' ) },
                    { value: 'no', label: __( 'No', 'wp-user-frontend' ) },
                ] }
                value={ value.condition_status }
                onChange={ ( status ) => onChange( { ...value, condition_status: status } ) }
                inline
                className="mt-3 gap-x-8"
            />

            { isEnabled && (
                <div className="condiotional-logic-container">
                    <div className="text-sm leading-6 mb-4">
                        <span className="mb-3 block">{ __( 'Show this field when', 'wp-user-frontend' ) }</span>
                        <div className="flex items-center">
                            <div className="w-1/3">
                                <Select
                                    options={ RULE_OPTIONS }
                                    value={ value.cond_logic }
                                    onChange={ ( logic ) => onChange( { ...value, cond_logic: logic } ) }
                                />
                            </div>
                            <span className="ml-3">{ __( 'of these rules are met', 'wp-user-frontend' ) }</span>
                        </div>
                    </div>

                    <ul className="condiotional-logic-repeater">
                        { conditions.map( ( condition, index ) => (
                            <ConditionRow
                                // eslint-disable-next-line react/no-array-index-key -- rows have no id (develop's v-for index)
                                key={ index }
                                condition={ condition }
                                index={ index }
                                availableFields={ dependencies }
                                taxonomies={ taxonomies }
                                wpPostTypes={ wpPostTypes }
                                onChange={ changeRow }
                                onAdd={ addRow }
                                onRemove={ removeRow }
                            />
                        ) ) }
                    </ul>
                </div>
            ) }
        </div>
    );
}
