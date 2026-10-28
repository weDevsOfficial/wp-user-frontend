/**
 * The user's posts table, shared by the account Posts section and the
 * `[wpuf_dashboard]` list: thumbnail, title (+ Featured), the optional
 * dashboard columns (category, meta fields, excerpt), status pill, date,
 * payment, and the row actions (view, edit, delete). Columns and rows come
 * from the server (Account_Service::post_row(), Dashboard_Service).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Button, Pill } from './primitives';
import { Eye, Pencil, Trash2 } from '../icons';

const TONE = {
    publish: 'green',
    pending: 'amber',
    draft: 'gray',
    future: 'blue',
    private: 'gray',
};

/**
 * The classic payment column: no payment -> Not Applicable, unpaid -> Pay Now, paid -> Completed.
 *
 * @param {Object} props
 * @param {Object} props.post Row.
 */
function Payment( { post } ) {
    if ( ! post.payment_status ) {
        return <span className="wpuf-muted">{ __( 'Not Applicable', 'wp-user-frontend' ) }</span>;
    }

    if ( 'completed' === post.payment_status ) {
        return <Pill tone="green">{ __( 'Completed', 'wp-user-frontend' ) }</Pill>;
    }

    return post.pay_url
        ? <Button variant="secondary" size="sm" href={ post.pay_url }>{ __( 'Pay Now', 'wp-user-frontend' ) }</Button>
        : <Pill tone="amber">{ __( 'Pending', 'wp-user-frontend' ) }</Pill>;
}

/**
 * @param {Object}   props
 * @param {string}   props.type       Post type(s), as classes (the classic `items-table <type>`).
 * @param {Object[]} props.items      Rows.
 * @param {Object}   props.columns    { thumbnail, payment, category, excerpt, meta: [ { key, label } ] }.
 * @param {number}   [props.deleting] ID of the row being deleted.
 * @param {Function} [props.onDelete] Called with the row.
 * @param {boolean}  [props.busy]     A page is loading.
 */
export default function PostsTable( { type = 'post', items, columns = {}, deleting = 0, onDelete, busy = false } ) {
    const meta = columns.meta || [];

    return (
        <div className="wpuf-table-wrap" aria-busy={ busy || undefined }>
            <table className={ `items-table ${ type } wpuf-table` }>
                <thead>
                    <tr className="items-list-header">
                        { columns.thumbnail && <th scope="col">{ __( 'Featured Image', 'wp-user-frontend' ) }</th> }
                        <th scope="col">{ __( 'Title', 'wp-user-frontend' ) }</th>
                        { columns.category && <th scope="col">{ __( 'Category', 'wp-user-frontend' ) }</th> }
                        { meta.map( ( column ) => <th scope="col" key={ column.key }>{ column.label }</th> ) }
                        { columns.excerpt && <th scope="col">{ __( 'Excerpt', 'wp-user-frontend' ) }</th> }
                        <th scope="col">{ __( 'Status', 'wp-user-frontend' ) }</th>
                        <th scope="col">{ __( 'Date', 'wp-user-frontend' ) }</th>
                        { columns.payment && <th scope="col">{ __( 'Payment', 'wp-user-frontend' ) }</th> }
                        <th scope="col" className="wpuf-table__actions">{ __( 'Options', 'wp-user-frontend' ) }</th>
                    </tr>
                </thead>
                <tbody>
                    { items.map( ( post ) => (
                        <tr key={ post.id }>
                            { columns.thumbnail && (
                                <td data-label={ __( 'Featured Image', 'wp-user-frontend' ) }>
                                    { /* Linked to the post like the classic table's picture. */ }
                                    { post.thumbnail && post.permalink ? (
                                        <a href={ post.permalink } tabIndex={ -1 } aria-hidden="true"><img className="wpuf-table__thumb" src={ post.thumbnail } alt="" /></a>
                                    ) : post.thumbnail ? <img className="wpuf-table__thumb" src={ post.thumbnail } alt="" /> : <span className="wpuf-table__thumb wpuf-table__thumb--empty" /> }
                                </td>
                            ) }
                            <td data-label={ __( 'Title', 'wp-user-frontend' ) } className="wpuf-table__title">
                                { post.permalink ? <a href={ post.permalink }>{ post.title }</a> : post.title }
                                { post.featured && <Pill tone="amber">{ __( 'Featured', 'wp-user-frontend' ) }</Pill> }
                            </td>
                            { columns.category && (
                                <td data-label={ __( 'Category', 'wp-user-frontend' ) } className="wpuf-table__terms">
                                    { ( post.terms || [] ).map( ( term, index ) => (
                                        <span key={ term.name + index }>
                                            { index > 0 && ', ' }
                                            { term.url ? <a href={ term.url }>{ term.name }</a> : term.name }
                                        </span>
                                    ) ) }
                                </td>
                            ) }
                            { meta.map( ( column ) => (
                                <td data-label={ column.label } key={ column.key }>{ post.meta?.[ column.key ] || '' }</td>
                            ) ) }
                            { columns.excerpt && (
                                <td data-label={ __( 'Excerpt', 'wp-user-frontend' ) } className="wpuf-table__excerpt">{ post.excerpt || '' }</td>
                            ) }
                            <td data-label={ __( 'Status', 'wp-user-frontend' ) }>
                                <Pill tone={ TONE[ post.status ] || 'gray' }>{ post.status_label }</Pill>
                            </td>
                            <td data-label={ __( 'Date', 'wp-user-frontend' ) } className="wpuf-table__nowrap">{ post.date.slice( 0, 10 ) }</td>
                            { columns.payment && (
                                <td data-label={ __( 'Payment', 'wp-user-frontend' ) } className="wpuf-table__nowrap"><Payment post={ post } /></td>
                            ) }
                            <td data-label={ __( 'Options', 'wp-user-frontend' ) } className="wpuf-table__actions">
                                <div className="wpuf-row-actions">
                                    { ( post.permalink || post.preview_url ) && (
                                        <Button variant="ghost" size="sm" href={ post.permalink || post.preview_url } target="_blank" rel="noopener noreferrer" title={ __( 'View', 'wp-user-frontend' ) }>
                                            <Eye size={ 16 } aria-hidden="true" />
                                            <span className="wpuf-sr-only">{ __( 'View', 'wp-user-frontend' ) }</span>
                                        </Button>
                                    ) }
                                    { post.edit_url && (
                                        <Button variant="ghost" size="sm" href={ post.edit_url } title={ __( 'Edit', 'wp-user-frontend' ) }>
                                            <Pencil size={ 16 } aria-hidden="true" />
                                            <span className="wpuf-sr-only">{ __( 'Edit', 'wp-user-frontend' ) }</span>
                                        </Button>
                                    ) }
                                    { post.can_delete && onDelete && (
                                        <Button variant="ghost" size="sm" className="wpuf-row-actions__delete" busy={ deleting === post.id } onClick={ () => onDelete( post ) } title={ __( 'Delete', 'wp-user-frontend' ) }>
                                            <Trash2 size={ 16 } aria-hidden="true" />
                                            <span className="wpuf-sr-only">{ __( 'Delete', 'wp-user-frontend' ) }</span>
                                        </Button>
                                    ) }
                                </div>
                            </td>
                        </tr>
                    ) ) }
                </tbody>
            </table>
        </div>
    );
}
