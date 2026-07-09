import { useMemo, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    SortableContext,
    verticalListSortingStrategy,
    sortableKeyboardCoordinates,
} from '@dnd-kit/sortable';
import { STORE_NAME } from '../../store';
import { filterCanvasRender } from '../../extensions/hooks';
import SortableField from './SortableField';
import EmptyState from './EmptyState';

export default function BuilderCanvas() {
    const { formFields, settings } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            formFields: store.getFormFields(),
            settings: store.getSettings(),
        };
    }, [] );

    const { moveField } = useDispatch( STORE_NAME );

    const sensors = useSensors(
        useSensor( PointerSensor, {
            activationConstraint: { distance: 5 },
        } ),
        useSensor( KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        } )
    );

    const fieldIds = useMemo( () => {
        return formFields.map( ( field ) => String( field.id ) );
    }, [ formFields ] );

    const labelType = settings.label_position || 'above';

    const handleDragEnd = useCallback( ( event ) => {
        const { active, over } = event;

        if ( ! over || active.id === over.id ) {
            return;
        }

        const fromIndex = formFields.findIndex( ( f ) => String( f.id ) === active.id );
        const toIndex = formFields.findIndex( ( f ) => String( f.id ) === over.id );

        if ( fromIndex !== -1 && toIndex !== -1 ) {
            moveField( fromIndex, toIndex );
        }
    }, [ formFields, moveField ] );

    // Apply Pro canvas render filter
    const canvasClass = filterCanvasRender( '' );

    if ( ! formFields.length ) {
        return <EmptyState />;
    }

    return (
        <DndContext
            sensors={ sensors }
            collisionDetection={ closestCenter }
            onDragEnd={ handleDragEnd }
        >
            <SortableContext items={ fieldIds } strategy={ verticalListSortingStrategy }>
                <div id="form-preview-stage" className="wpuf-h-[70vh]">
                    <ul className={ `wpuf-form sortable-list wpuf-py-8 form-label-${ labelType } ${ canvasClass }` }>
                        { formFields.map( ( field, index ) => (
                            <SortableField
                                key={ field.id }
                                field={ field }
                                index={ index }
                            />
                        ) ) }
                    </ul>
                </div>
            </SortableContext>
        </DndContext>
    );
}
