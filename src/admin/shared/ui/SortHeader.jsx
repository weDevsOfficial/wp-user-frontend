/**
 * The sortable column header of the admin list tables: the column label as a
 * button with an arrow (both ways when not sorted), `aria-sort` on the cell.
 * `nextSort()` gives the sort after a click: a new column starts descending,
 * the sorted column flips.
 *
 * @since WPUF_SINCE
 */
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import { cn } from '@wedevs/plugin-ui';

/**
 * @param {Object} sort   { orderby, order }
 * @param {string} column Clicked column.
 *
 * @return {Object} { orderby, order }
 */
export function nextSort( sort, column ) {
    return {
        orderby: column,
        order: sort?.orderby === column && 'desc' === sort?.order ? 'asc' : 'desc',
    };
}

/**
 * @param {Object}   props
 * @param {string}   props.column      Column key the server sorts on.
 * @param {*}        props.label       Header text.
 * @param {Object}   props.sort        { orderby, order }.
 * @param {Function} props.onSort      ( nextSort ) => void
 * @param {string}   [props.className] Cell classes (the table's header cell look).
 *
 * @return {JSX.Element} Header cell.
 */
export default function SortHeader( { column, label, sort, onSort, className } ) {
    const active = sort?.orderby === column;
    const ascending = active && 'asc' === sort.order;
    let Icon = ArrowUpDown;

    if ( active ) {
        Icon = ascending ? ArrowUp : ArrowDown;
    }

    return (
        <th scope="col" className={ className } aria-sort={ active ? ( ascending ? 'ascending' : 'descending' ) : 'none' }>
            <button
                type="button"
                data-wpuf-ui=""
                data-sort={ column }
                onClick={ () => onSort( nextSort( sort, column ) ) }
                className={ cn( 'inline-flex cursor-pointer items-center gap-1 border-0 bg-transparent p-0 text-xs font-normal uppercase text-[#828282] hover:text-gray-900', active && 'text-gray-900' ) }
            >
                { label }
                <Icon size={ 12 } aria-hidden="true" />
            </button>
        </th>
    );
}
