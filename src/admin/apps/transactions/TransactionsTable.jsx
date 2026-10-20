/**
 * Payments table (FlyHR list-table structure, as the forms lists): select
 * column, sortable ID and Date, a status badge, the tax part of the amount
 * and a row menu.
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { ActionMenu, Checkbox, SortHeader } from '@wpuf/components';
import { Check, Eye, Trash2, X } from 'lucide-react';

import StatusBadge from './StatusBadge';

const TH = 'px-2 font-normal';
const TD = 'px-2 py-2.5 align-middle text-[13px]';

/**
 * A link when the URL is there, else the text.
 *
 * @param {Object} props
 * @param {string} [props.url]   URL.
 * @param {*}      props.children Text.
 */
const MaybeLink = ( { url, children } ) => ( url
    ? <a href={ url } className="text-gray-900 no-underline hover:text-primary hover:underline">{ children }</a>
    : <span className="text-gray-900">{ children }</span> );

/**
 * @param {Object}   props
 * @param {Object[]} props.rows       Rows.
 * @param {string[]} props.selected   Selected row keys.
 * @param {Function} props.onSelect   ( keys ) => void
 * @param {Object}   props.sort       { orderby, order }.
 * @param {Function} props.onSort     ( { orderby, order } ) => void
 * @param {Function} props.onView     ( row ) => void
 * @param {Function} props.onAction   ( action, rows ) => void
 * @param {boolean}  props.canManage  Accept / reject / delete allowed.
 */
export default function TransactionsTable( { rows, selected, onSelect, sort, onSort, onView, onAction, canManage } ) {
    const all = rows.length > 0 && rows.every( ( row ) => selected.includes( row.key ) );
    const some = selected.length > 0 && ! all;

    const toggle = ( key ) => onSelect( selected.includes( key ) ? selected.filter( ( item ) => item !== key ) : [ ...selected, key ] );

    const menu = ( row ) => {
        const items = [ { key: 'view', icon: <Eye size={ 16 } aria-hidden="true" />, label: __( 'View details', 'wp-user-frontend' ), onClick: () => onView( row ) } ];

        if ( canManage && 'order' === row.kind ) {
            items.push( { key: 'accept', icon: <Check size={ 16 } aria-hidden="true" />, label: __( 'Accept', 'wp-user-frontend' ), onClick: () => onAction( 'accept', [ row ] ) } );
            items.push( { key: 'reject', icon: <X size={ 16 } aria-hidden="true" />, label: __( 'Reject', 'wp-user-frontend' ), onClick: () => onAction( 'reject', [ row ] ), destructive: true } );
        }

        if ( canManage && 'transaction' === row.kind ) {
            items.push( { key: 'delete', icon: <Trash2 size={ 16 } aria-hidden="true" />, label: __( 'Delete', 'wp-user-frontend' ), onClick: () => onAction( 'delete', [ row ] ), destructive: true } );
        }

        return items;
    };

    return (
        <div className="overflow-x-auto">
            <table className="w-full border-collapse text-left">
                <thead className="border-0 border-b border-solid border-gray-200 bg-white">
                    <tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
                        { /* Selecting is only for the bulk actions, which need canManage. */ }
                        { canManage && (
                            <th scope="col" className="w-10 px-4">
                                <Checkbox
                                    value={ all }
                                    indeterminate={ some }
                                    onChange={ () => onSelect( all ? [] : rows.map( ( row ) => row.key ) ) }
                                    aria-label={ __( 'Select all', 'wp-user-frontend' ) }
                                    className="align-middle"
                                />
                            </th>
                        ) }
                        <SortHeader className={ TH } column="id" label={ __( 'ID', 'wp-user-frontend' ) } sort={ sort } onSort={ onSort } />
                        <th scope="col" className={ TH }>{ __( 'Status', 'wp-user-frontend' ) }</th>
                        <th scope="col" className={ TH }>{ __( 'User', 'wp-user-frontend' ) }</th>
                        <th scope="col" className={ TH }>{ __( 'Item', 'wp-user-frontend' ) }</th>
                        <th scope="col" className={ TH }>{ __( 'Tax', 'wp-user-frontend' ) }</th>
                        <th scope="col" className={ TH }>{ __( 'Amount', 'wp-user-frontend' ) }</th>
                        <th scope="col" className={ TH }>{ __( 'Gateway', 'wp-user-frontend' ) }</th>
                        <th scope="col" className={ TH }>{ __( 'Payer', 'wp-user-frontend' ) }</th>
                        <SortHeader className={ TH } column="created" label={ __( 'Date', 'wp-user-frontend' ) } sort={ sort } onSort={ onSort } />
                        <th scope="col" className="w-20 px-4"><span className="sr-only">{ __( 'Actions', 'wp-user-frontend' ) }</span></th>
                    </tr>
                </thead>
                <tbody>
                    { rows.map( ( row ) => (
                        <tr key={ row.key } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
                            { canManage && (
                                <td className="w-10 px-4 align-middle">
                                    <Checkbox
                                        value={ selected.includes( row.key ) }
                                        onChange={ () => toggle( row.key ) }
                                        /* translators: %d: payment ID */
                                        aria-label={ sprintf( __( 'Select payment %d', 'wp-user-frontend' ), row.id ) }
                                        className="align-middle"
                                    />
                                </td>
                            ) }
                            <td className={ TD + ' whitespace-nowrap' }>
                                <button type="button" onClick={ () => onView( row ) } className="cursor-pointer border-0 bg-transparent p-0 text-sm font-medium text-gray-900 hover:text-primary hover:underline">
                                    #{ row.id }
                                </button>
                                { row.transaction_id && <span className="block text-xs text-gray-400">{ row.transaction_id }</span> }
                            </td>
                            <td className={ TD + ' whitespace-nowrap' }><StatusBadge status={ row.status } /></td>
                            <td className={ TD }>
                                { row.user ? <MaybeLink url={ row.user.url }>{ row.user.name }</MaybeLink> : <span className="text-gray-400">–</span> }
                            </td>
                            <td className={ TD }>
                                { row.pack && <MaybeLink url={ row.pack.url }>{ row.pack.title }</MaybeLink> }
                                { row.post && <MaybeLink url={ row.post.url }>{ row.post.title }</MaybeLink> }
                                { ! row.pack && ! row.post && <span className="text-gray-400">–</span> }
                                { row.pack && <span className="block text-xs text-gray-400">{ __( 'Subscription', 'wp-user-frontend' ) }</span> }
                                { row.post && <span className="block text-xs text-gray-400">{ __( 'Post', 'wp-user-frontend' ) }</span> }
                            </td>
                            { /* The tax part of the amount, as the classic table's Tax column. */ }
                            <td className={ TD + ' whitespace-nowrap text-gray-500' }>{ parseFloat( row.tax ) > 0 ? row.amounts.tax : '–' }</td>
                            <td className={ TD + ' whitespace-nowrap' }>
                                <span className="font-medium text-gray-900">{ row.amounts.cost }</span>
                                { row.coupon && <span className="block text-xs text-gray-400">{ row.coupon.code }</span> }
                            </td>
                            <td className={ TD + ' whitespace-nowrap text-gray-500' }>
                                <span className="inline-flex items-center gap-2">
                                    { row.gateway_logo && <img src={ row.gateway_logo } alt="" className="h-4 w-5 object-contain" /> }
                                    { row.gateway_label || row.gateway || '–' }
                                </span>
                            </td>
                            <td className={ TD }>
                                <span className="text-gray-900">{ row.payer || '–' }</span>
                                { row.payer_email && <span className="block text-xs text-gray-400">{ row.payer_email }</span> }
                            </td>
                            <td className={ TD + ' whitespace-nowrap text-gray-500' }>{ row.date || '–' }</td>
                            <td className="px-4 align-middle">
                                <div className="flex justify-end">
                                    <ActionMenu
                                        vertical
                                        /* translators: %d: payment ID */
                                        label={ sprintf( __( 'Actions for payment %d', 'wp-user-frontend' ), row.id ) }
                                        items={ menu( row ) }
                                    />
                                </div>
                            </td>
                        </tr>
                    ) ) }
                </tbody>
            </table>
        </div>
    );
}
