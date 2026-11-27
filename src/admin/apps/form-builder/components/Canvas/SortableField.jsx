import { useDraggable } from '@dnd-kit/core';
import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { STORE_NAME } from '../../store';
import { applyFilters } from '@wordpress/hooks';
import { filterBuilderCssClasses } from '../../utils/canvasHelpers';
import FieldPreview from './FieldPreview';
import FieldActions from './FieldActions';
import ColumnField from './ColumnField';
import RepeatField from './RepeatField';
import { Indicator } from '../Dnd/DropList';
import { isFailedToValidate } from '../../utils/globalHelpers';
import { getFieldValidators } from '../../extensions/registry';

const TOP_LEVEL = { type: 'top' };

/**
 * One field on the canvas.
 *
 * `container` says which list the field lives in, so its actions edit that list:
 * `{ type: 'top' }` (default), `{ type: 'column', columnFieldId, column }` or
 * `{ type: 'repeat', repeatFieldId }`.
 *
 * It is a drag source of the builder's one DndContext (components/Dnd,
 * design.md D16), started from the move handle in its action bar, and a row of
 * its list for the drop hit test (`data-dnd-item`, `data-index`).
 */
export default function SortableField( { field, index, container = TOP_LEVEL, indicator = null } ) {
    const {
        attributes,
        listeners,
        setNodeRef,
        isDragging,
    } = useDraggable( {
        id: 'field:' + field.id,
        data: { kind: 'field', fieldId: field.id, template: field.template, label: field.label },
    } );

    // The source stays in place, dimmed, while the placeholder shows the drop point.
    const style = isDragging ? { opacity: 0.4 } : undefined;

    const isColumnOrRepeat = field.input_type === 'column_field' || field.input_type === 'repeat';

    const fieldSettings = useSelect( ( select ) => select( STORE_NAME ).getFieldSettings(), [] );
    const formLength = useSelect( ( select ) => select( STORE_NAME ).getFormFields().length, [] );
    const { openFieldSettings, moveField } = useDispatch( STORE_NAME );

    // A click on the field opens its options (the right column), as its Edit
    // button does; the action buttons and fields that fail validation (Edit
    // disabled) are left alone, and an inner field selects itself, not its parent.
    const handleSelect = useCallback( ( event ) => {
        if ( event.defaultPrevented || event.target.closest( '.field-buttons, .wpuf-column-field-control-buttons, .wpuf-repeat-field-control-buttons' ) ) {
            return;
        }

        event.preventDefault();

        if ( isFailedToValidate( field.template, fieldSettings, getFieldValidators() ) ) {
            return;
        }

        openFieldSettings( field.id );

        if ( 'top' === container.type ) {
            event.currentTarget.focus( { preventScroll: true } );
        }
    }, [ field.id, field.template, fieldSettings, openFieldSettings, container.type ] );

    // Alt+Up / Alt+Down move the selected top-level field one place (FlyForms).
    const handleKeyDown = useCallback( ( event ) => {
        if ( 'top' !== container.type || ! event.altKey || event.target !== event.currentTarget ) {
            return;
        }

        const delta = 'ArrowUp' === event.key ? -1 : ( 'ArrowDown' === event.key ? 1 : 0 );

        if ( ! delta || index + delta < 0 || index + delta >= formLength ) {
            return;
        }

        event.preventDefault();
        moveField( index, index + delta );
    }, [ container.type, index, formLength, moveField ] );

    const isHidden = field.template === 'custom_hidden_field';
    // Classes that hide a field on the frontend are dropped on the stage; Pro
    // and add-ons can adjust the rest.
    const filteredCss = applyFilters( 'wpuf.formBuilder.fieldCssClasses', filterBuilderCssClasses( field.css ), field );

    const isEditing = useSelect(
        ( select ) => parseInt( select( STORE_NAME ).getEditingFieldId() ) === parseInt( field.id ),
        [ field.id ]
    );
    // Inner fields use develop's column / repeat item classes and a named hover
    // group, so hovering the parent does not reveal every inner action bar.
    const variant = 'top' === container.type ? 'top' : container.type;

    const classNames = ( 'column' === variant ? [
        'relative m-0! p-0! group/column-inner rounded-lg transition duration-150 wpuf-ease-out column-field-items wpuf-el hover:bg-primary/5',
        field.name,
        field.css,
        'form-field-' + field.template,
        field.width ? 'field-size-' + field.width : '',
        isHidden ? 'hidden-field' : '',
        isEditing ? 'bg-primary/5 ring-2 ring-primary/30' : '',
    ] : 'repeat' === variant ? [
        'relative m-0! p-0! group/repeat-inner rounded-lg transition duration-150 wpuf-ease-out repeat-field-items wpuf-el hover:bg-primary/5',
        field.name,
        'form-field-' + field.template,
        field.width ? 'field-size-' + field.width : '',
        isEditing ? 'bg-primary/5 ring-2 ring-primary/30' : '',
    ] : [
        'field-items',
        'wpuf-el',
        field.name,
        filteredCss,
        'form-field-' + field.template,
        field.width ? 'field-size-' + field.width : '',
        isHidden ? 'hidden-field' : '',
        'relative group rounded-lg hover:bg-primary/5! transition duration-150 wpuf-ease-out m-0! p-0!',
    ] ).filter( Boolean ).join( ' ' );

    return (
        <li
            ref={ setNodeRef }
            style={ style }
            className={ classNames }
            data-dnd-item=""
            data-index={ index }
            data-source={ 'top' === variant ? 'stage' : variant + '-field-stage' }
            onClick={ handleSelect }
            onKeyDown={ handleKeyDown }
            tabIndex={ 'top' === variant ? -1 : undefined }
        >
            { indicator && <Indicator edge={ indicator.edge } blocked={ indicator.blocked } /> }
            { ! isColumnOrRepeat && (
                <FieldPreview field={ field } variant={ variant } />
            ) }

            { field.input_type === 'column_field' && (
                <ColumnField field={ field } />
            ) }

            { field.input_type === 'repeat' && (
                <RepeatField field={ field } />
            ) }

            <FieldActions field={ field } index={ index } container={ container } dragListeners={ listeners } dragAttributes={ attributes } variant={ variant } />
        </li>
    );
}
