import { useMemo } from '@wordpress/element';
import { columnContainer } from '../../utils/dndTree';
import { usePlaceholder } from '../Dnd/dropTarget';
import { Indicator, dropListProps, indicatorFor } from '../Dnd/DropList';
import SortableField from './SortableField';
import InsertPoint from './InsertPoint';

/**
 * One cell of a column field: a drop list of the builder's DndContext
 * (components/Dnd, design.md D16). Palette fields (develop's rules), fields
 * of any cell and stage fields can be dropped in; the cell lights up while
 * it is the drop target (red for a refused palette field). A "+" adds a
 * field to the cell: centred in an empty cell, under the fields otherwise,
 * shown while the cell is hovered.
 */
function ColumnCell( { field, columnKey } ) {
    const container = columnContainer( field.id, columnKey );
    const placeholder = usePlaceholder( container );
    const colFields = ( field.inner_fields || {} )[ columnKey ] || [];
    let tint = '';

    if ( placeholder ) {
        tint = placeholder.blocked ? 'bg-red-50! border-red-400!' : 'bg-primary/10! border-primary!';
    }

    return (
        <div
            style={ { paddingRight: ( field.column_space || 0 ) + 'px' } }
            className="flex-1 min-w-0 min-h-full wpuf-column-inner-fields"
        >
            <div
                data-column={ columnKey }
                className={ `group/cell relative border border-dashed border-gray-300 bg-gray-50/60 rounded-lg p-1 transition-colors ${ tint }` }
            >
                <ul { ...dropListProps( container, colFields.length ) } className="wpuf-column-fields-sortable-list relative min-h-16 list-none m-0! p-0!">
                    { placeholder && ! colFields.length && <Indicator blocked={ placeholder.blocked } /> }
                    { colFields.map( ( innerField, idx ) => (
                        <SortableField
                            key={ innerField.id }
                            field={ innerField }
                            index={ idx }
                            container={ { type: 'column', columnFieldId: field.id, column: columnKey } }
                            indicator={ indicatorFor( placeholder, idx, colFields.length ) }
                        />
                    ) ) }
                </ul>
                { placeholder ? null : <InsertPoint index={ colFields.length } container={ container } variant={ colFields.length ? 'cell' : 'empty' } /> }
            </div>
        </div>
    );
}

/**
 * Column field canvas component.
 * inner_fields is an OBJECT: { 'column-1': [], 'column-2': [], 'column-3': [] }
 */
export default function ColumnField( { field } ) {
    const numColumns = parseInt( field.columns ) || 3;

    const columnKeys = useMemo( () => {
        const keys = [];
        for ( let i = 1; i <= numColumns; i++ ) {
            keys.push( 'column-' + i );
        }
        return keys;
    }, [ numColumns ] );

    return (
        <div
            className={ `has-columns-${ numColumns } wpuf-field-columns flex md:flex-row gap-4 p-4! w-full justify-between rounded-t-md border-t! border-r! border-l! border-dashed! border-transparent! group-hover:border-primary/50! group-hover:cursor-pointer` }
        >
            { columnKeys.map( ( columnKey ) => (
                <ColumnCell key={ columnKey } field={ field } columnKey={ columnKey } />
            ) ) }
        </div>
    );
}
