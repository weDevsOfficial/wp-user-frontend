import { __ } from '@wordpress/i18n';
import { repeatContainer } from '../../utils/dndTree';
import { usePlaceholder } from '../Dnd/dropTarget';
import { Indicator, dropListProps, indicatorFor } from '../Dnd/DropList';
import SortableField from './SortableField';
import HelpText from '../FieldPreview/HelpText';
import SettingHelpText from '../FieldSettings/inputs/SettingHelpText';

/**
 * Repeat field canvas component, same layout as the Vue form-repeat_field
 * template: label, divider, inner fields (or an empty-state hint), the static
 * + / - controls and the help text.
 * inner_fields is an ARRAY (NOT object).
 *
 * The inner list is a drop list of the builder's DndContext (components/Dnd,
 * design.md D16): palette fields of the allowed types (utils/dndTree.js
 * ALLOWED_IN_REPEAT, develop's list) and reordering of its own fields.
 */
export default function RepeatField( { field } ) {
    const container = repeatContainer( field.id );
    const placeholder = usePlaceholder( container );
    const innerFields = Array.isArray( field.inner_fields ) ? field.inner_fields : [];

    return (
        // No wrapper around the container: develop's stage prints it straight in the row.
        <div className="wpuf-fields wpuf-repeat-field-builder-container">
            <label htmlFor={ field.name } className="wpuf-option-field-title text-gray-700 font-medium">
                { field.label } <SettingHelpText text={ field.help_text } />
            </label>
            <hr className="mt-4" />
            <ul
                { ...dropListProps( container, innerFields.length ) }
                className={ `wpuf-repeat-fields-sortable-list relative min-h-16 list-none p-0 m-0 transition-colors ${ placeholder ? ( placeholder.blocked ? 'bg-red-50' : 'bg-primary/5' ) : '' }` }
            >
                { placeholder && ! innerFields.length && <Indicator blocked={ placeholder.blocked } /> }
                { innerFields.length === 0 && (
                    <div className="flex flex-col items-center justify-center py-12 px-4 text-center">
                        <svg className="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vectorEffect="non-scaling-stroke" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 className="text-sm font-medium text-gray-900 mb-2">
                            { __( 'No fields added yet', 'wp-user-frontend' ) }
                        </h3>
                        <p className="text-xs text-gray-500 max-w-sm">
                            { __( 'Drag and drop fields from the sidebar to build your repeatable section.', 'wp-user-frontend' ) }
                        </p>
                    </div>
                ) }
                { innerFields.map( ( innerField, idx ) => (
                    <SortableField
                        key={ innerField.id }
                        field={ innerField }
                        index={ idx }
                        container={ { type: 'repeat', repeatFieldId: field.id } }
                        indicator={ indicatorFor( placeholder, idx, innerFields.length ) }
                    />
                ) ) }
            </ul>

            { /* Static + and - icons, as develop (no interactivity in the builder). */ }
            <div className="wpuf-repeat-controls p-4">
                <button type="button" className="mr-1 inline-flex size-7 items-center justify-center rounded-md border border-solid border-gray-200 bg-white text-sm text-gray-600 shadow-xs">+</button>
                <button type="button" className="inline-flex size-7 items-center justify-center rounded-md border border-solid border-gray-200 bg-white text-sm text-gray-600 shadow-xs">-</button>
            </div>

            <HelpText text={ field.help } />
        </div>
    );
}
