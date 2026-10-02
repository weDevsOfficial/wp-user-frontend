import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { filterBuilderCssClasses } from '../../utils/canvasHelpers';
import FieldPreview from './FieldPreview';
import FieldActions from './FieldActions';
import ColumnField from './ColumnField';
import RepeatField from './RepeatField';

export default function SortableField( { field, index } ) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable( { id: String( field.id ) } );

    const style = {
        transform: CSS.Transform.toString( transform ),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const isColumnOrRepeat = field.input_type === 'column_field' || field.input_type === 'repeat';
    const isHidden = field.template === 'custom_hidden_field';
    const filteredCss = filterBuilderCssClasses( field.css );

    const classNames = [
        'field-items',
        'wpuf-el',
        field.name,
        filteredCss,
        'form-field-' + field.template,
        field.width ? 'field-size-' + field.width : '',
        isHidden ? 'hidden-field' : '',
        'wpuf-group wpuf-rounded-lg hover:!wpuf-bg-green-50 wpuf-transition wpuf-duration-150 wpuf-ease-out !wpuf-m-0 !wpuf-p-0 wpuf-overflow-hidden',
    ].filter( Boolean ).join( ' ' );

    return (
        <li
            ref={ setNodeRef }
            style={ style }
            className={ classNames }
            data-index={ index }
            data-source="stage"
        >
            { ! isColumnOrRepeat && (
                <FieldPreview field={ field } />
            ) }

            { field.input_type === 'column_field' && (
                <ColumnField field={ field } />
            ) }

            { field.input_type === 'repeat' && (
                <RepeatField field={ field } />
            ) }

            <FieldActions field={ field } index={ index } dragListeners={ listeners } dragAttributes={ attributes } />
        </li>
    );
}
