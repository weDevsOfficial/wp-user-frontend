import { Fragment } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '../../store';
import { filterCanvasRender } from '../../extensions/hooks';
import { TOP } from '../../utils/dndTree';
import { usePlaceholder } from '../Dnd/dropTarget';
import { Indicator, dropListProps, indicatorFor } from '../Dnd/DropList';
import SortableField from './SortableField';
import InsertPoint from './InsertPoint';
import EmptyState from './EmptyState';
import HiddenFieldsList from './HiddenFieldsList';
import { BUILDER_SLOTS, BuilderSlot } from '../../slots';

/**
 * The stage: the form's top level fields as one drop list of the builder's
 * DndContext (components/Dnd, design.md D16). Palette fields and canvas fields
 * dropped here go to the indicator's position, as develop's sortable stage
 * (the whole stage area takes drops; below the rows means the end).
 */
export default function BuilderCanvas() {
    const { formFields, settings } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            formFields: store.getFormFields(),
            settings: store.getSettings(),
        };
    }, [] );

    const placeholder = usePlaceholder( TOP );
    const labelType = settings.label_position || 'above';

    // Apply Pro canvas render filter
    const canvasClass = filterCanvasRender( '' );

    if ( ! formFields.length ) {
        return (
            <div id="form-preview-stage" className={ `relative rounded-lg transition-colors ${ placeholder ? 'bg-primary/5' : '' }` } { ...dropListProps( TOP, 0 ) }>
                { placeholder && <Indicator blocked={ placeholder.blocked } /> }
                <EmptyState />
            </div>
        );
    }

    return (
        <div id="form-preview-stage" className="h-[70vh]" { ...dropListProps( TOP, formFields.length ) }>
            <ul data-dnd-rows="" className={ `wpuf-form sortable-list py-4 form-label-${ labelType } ${ canvasClass }` }>
                { formFields.map( ( field, index ) => (
                    <Fragment key={ field.id }>
                        { /* "+" on the seam between two shown fields (FlyForms): add a field
                           here. Hidden fields have no stage row (Hidden Fields list), so
                           they get no seam and the gap stays even around them. */ }
                        { ! placeholder && 'custom_hidden_field' !== field.template && formFields.slice( 0, index ).some( ( prev ) => 'custom_hidden_field' !== prev.template )
                            ? <InsertPoint index={ index } />
                            : null }
                        <SortableField
                            field={ field }
                            index={ index }
                            indicator={ indicatorFor( placeholder, index, formFields.length ) }
                        />
                    </Fragment>
                ) ) }
                { /* And one after the last shown field, to add at the end. */ }
                { ! placeholder && formFields.some( ( field ) => 'custom_hidden_field' !== field.template )
                    ? <InsertPoint index={ formFields.length } />
                    : null }
            </ul>
            <BuilderSlot name={ BUILDER_SLOTS.CANVAS_SUBMIT_AREA } fillProps={ { fields: formFields } } />
            <HiddenFieldsList />
            <BuilderSlot name={ BUILDER_SLOTS.CANVAS_BOTTOM } fillProps={ { fields: formFields } } />
        </div>
    );
}
