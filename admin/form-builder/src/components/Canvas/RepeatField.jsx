import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
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
import SortableField from './SortableField';

/**
 * Repeat field canvas component.
 * inner_fields is an ARRAY (NOT object).
 */
export default function RepeatField( { field } ) {
    const { editingFieldId } = useSelect( ( select ) => ( {
        editingFieldId: select( STORE_NAME ).getEditingFieldId(),
    } ), [] );

    const { moveField } = useDispatch( STORE_NAME );

    const sensors = useSensors(
        useSensor( PointerSensor, { activationConstraint: { distance: 5 } } )
    );

    const innerFields = Array.isArray( field.inner_fields ) ? field.inner_fields : [];
    const fieldIds = innerFields.map( ( f ) => String( f.id ) );
    const isEditing = parseInt( editingFieldId ) === parseInt( field.id );

    const handleDragEnd = useCallback( ( event ) => {
        const { active, over } = event;

        if ( ! over || active.id === over.id ) {
            return;
        }

        const fromIndex = innerFields.findIndex( ( f ) => String( f.id ) === active.id );
        const toIndex = innerFields.findIndex( ( f ) => String( f.id ) === over.id );

        if ( fromIndex !== -1 && toIndex !== -1 ) {
            // Repeat field reorder uses the same move logic
            moveField( fromIndex, toIndex );
        }
    }, [ innerFields, moveField ] );

    return (
        <div
            className={ `wpuf-p-4 wpuf-border wpuf-border-dashed wpuf-rounded-lg group-hover:wpuf-border-primary ${ isEditing ? 'wpuf-bg-green-50 wpuf-border-primary' : 'wpuf-border-transparent' }` }
        >
            <DndContext
                sensors={ sensors }
                collisionDetection={ closestCenter }
                onDragEnd={ handleDragEnd }
            >
                <SortableContext items={ fieldIds } strategy={ verticalListSortingStrategy }>
                    <ul className="wpuf-repeat-fields-sortable-list wpuf-min-h-[80px] wpuf-list-none wpuf-p-0 wpuf-m-0">
                        { innerFields.map( ( innerField, idx ) => (
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
    );
}
