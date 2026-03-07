import { useState, useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
    DndContext,
    closestCenter,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { STORE_NAME } from '../../store';
import { createField, isFieldSingleInstance, containsField } from '../../utils/fieldUtils';
import SortableField from './SortableField';

const RESTRICTED_IN_COLUMN = [ 'column_field', 'custom_hidden_field', 'step_start' ];

/**
 * Column field canvas component.
 * inner_fields is an OBJECT: { 'column-1': [], 'column-2': [], 'column-3': [] }
 *
 * Supports drag-and-drop from the sidebar panel via HTML5 native drag API,
 * mirroring Vue's jQuery UI draggable + connectToSortable behavior.
 */
export default function ColumnField( { field } ) {
    const { editingFieldId, fieldSettings, formFields } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            editingFieldId: store.getEditingFieldId(),
            fieldSettings: store.getFieldSettings(),
            formFields: store.getFormFields(),
        };
    }, [] );

    const { moveColumnField, addColumnField } = useDispatch( STORE_NAME );

    const [ dragOverColumn, setDragOverColumn ] = useState( null );

    const sensors = useSensors(
        useSensor( PointerSensor, { activationConstraint: { distance: 5 } } )
    );

    const columns = field.inner_fields || {};
    const numColumns = parseInt( field.columns ) || 3;

    const columnKeys = useMemo( () => {
        const keys = [];
        for ( let i = 1; i <= numColumns; i++ ) {
            keys.push( 'column-' + i );
        }
        return keys;
    }, [ numColumns ] );

    const handleDragEnd = useCallback( ( columnKey ) => ( event ) => {
        const { active, over } = event;

        if ( ! over || active.id === over.id ) {
            return;
        }

        const colFields = columns[ columnKey ] || [];
        const fromIndex = colFields.findIndex( ( f ) => String( f.id ) === active.id );
        const toIndex = colFields.findIndex( ( f ) => String( f.id ) === over.id );

        if ( fromIndex !== -1 && toIndex !== -1 ) {
            moveColumnField( field.id, columnKey, fromIndex, columnKey, toIndex );
        }
    }, [ field.id, columns, moveColumnField ] );

    const data = window.wpuf_form_builder || {};
    const singleObjects = data.wpuf_single_objects || [];

    const handleNativeDrop = useCallback( ( columnKey, e ) => {
        e.preventDefault();
        setDragOverColumn( null );

        const template = e.dataTransfer.getData( 'wpuf/field-template' );

        if ( ! template ) {
            return;
        }

        // Vue: isAllowedInColumnField check
        if ( RESTRICTED_IN_COLUMN.includes( template ) ) {
            if ( typeof window.Swal !== 'undefined' ) {
                window.Swal.fire( {
                    title: '<span class="wpuf-text-primary">Oops...</span>',
                    html: '<p class="wpuf-text-gray-500 wpuf-text-xl wpuf-m-0 wpuf-p-0">' + __( 'You cannot add this field as inner column field', 'wp-user-frontend' ) + '</p>',
                    imageUrl: ( data.asset_url || '' ) + '/images/oops.svg',
                    showCloseButton: true,
                    padding: '1rem',
                    width: '35rem',
                    customClass: {
                        confirmButton: '!wpuf-flex focus:!wpuf-shadow-none !wpuf-bg-primary',
                        closeButton: 'wpuf-absolute',
                    },
                } );
            }
            return;
        }

        // Vue: isSingleInstance + containsField check
        if ( isFieldSingleInstance( template, singleObjects ) && containsField( formFields, template ) ) {
            if ( typeof window.Swal !== 'undefined' ) {
                window.Swal.fire( {
                    title: '<span class="wpuf-text-primary">Oops...</span>',
                    html: '<p class="wpuf-text-gray-500 wpuf-text-xl wpuf-m-0 wpuf-p-0">' + __( 'You already have this field in the form', 'wp-user-frontend' ) + '</p>',
                    imageUrl: ( data.asset_url || '' ) + '/images/oops.svg',
                    showCloseButton: true,
                    padding: '1rem',
                    width: '35rem',
                    customClass: {
                        confirmButton: '!wpuf-flex focus:!wpuf-shadow-none !wpuf-bg-primary',
                        closeButton: 'wpuf-absolute',
                    },
                } );
            }
            return;
        }

        const newField = createField( template, fieldSettings, formFields );

        if ( ! newField ) {
            return;
        }

        const colFields = columns[ columnKey ] || [];
        addColumnField( field.id, columnKey, colFields.length, newField );
    }, [ field.id, columns, fieldSettings, formFields, singleObjects, addColumnField, data.asset_url ] );

    const handleNativeDragOver = useCallback( ( columnKey, e ) => {
        const hasFieldData = e.dataTransfer.types.includes( 'wpuf/field-template' );

        if ( hasFieldData ) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            setDragOverColumn( columnKey );
        }
    }, [] );

    const handleNativeDragLeave = useCallback( () => {
        setDragOverColumn( null );
    }, [] );

    return (
        <div
            className={ `has-columns-${ numColumns } wpuf-field-columns wpuf-flex md:wpuf-flex-row wpuf-gap-4 wpuf-p-4 wpuf-w-full wpuf-justify-between wpuf-rounded-t-md !wpuf-border-t !wpuf-border-r !wpuf-border-l !wpuf-border-dashed !wpuf-border-transparent group-hover:!wpuf-border-green-400 group-hover:wpuf-cursor-pointer` }
        >
            { columnKeys.map( ( columnKey ) => {
                const colFields = columns[ columnKey ] || [];
                const colFieldIds = colFields.map( ( f ) => String( f.id ) );
                const isDragOver = dragOverColumn === columnKey;

                return (
                    <div
                        key={ columnKey }
                        style={ { paddingRight: ( field.column_space || 0 ) + 'px' } }
                        className="wpuf-flex-1 wpuf-min-w-0 wpuf-min-h-full wpuf-column-inner-fields"
                    >
                        <div
                            data-column={ columnKey }
                            className={ `wpuf-border wpuf-border-dashed wpuf-border-green-400 wpuf-bg-green-50 wpuf-shadow-sm wpuf-rounded-md wpuf-p-1 wpuf-transition-colors ${ isDragOver ? 'wpuf-bg-green-100 wpuf-border-primary' : '' }` }
                            onDrop={ ( e ) => handleNativeDrop( columnKey, e ) }
                            onDragOver={ ( e ) => handleNativeDragOver( columnKey, e ) }
                            onDragLeave={ handleNativeDragLeave }
                        >
                            <DndContext
                                sensors={ sensors }
                                collisionDetection={ closestCenter }
                                onDragEnd={ handleDragEnd( columnKey ) }
                            >
                                <SortableContext items={ colFieldIds } strategy={ verticalListSortingStrategy }>
                                    <ul className="wpuf-column-fields-sortable-list wpuf-min-h-16 wpuf-list-none !wpuf-m-0 !wpuf-p-0">
                                        { colFields.map( ( innerField, idx ) => (
                                            <SortableField
                                                key={ innerField.id }
                                                field={ innerField }
                                                index={ idx }
                                            />
                                        ) ) }
                                    </ul>
                                </SortableContext>
                            </DndContext>
                        </div>
                    </div>
                );
            } ) }
        </div>
    );
}
