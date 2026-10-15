/**
 * The posts of one type: the FlyHR table idiom, status pill, the actions
 * (view, edit, pay, delete with a confirm), `?pagenum=` honoured and
 * written, the "deleted" notice after a delete.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Button, Card, Notice, Pagination, Pill, SkeletonRows, confirmDialog, icons } from '@wpuf/frontend-kit';
import { store } from '../store';

const { Eye, Pencil, Trash2 } = icons;

const TONE = {
    publish: 'green',
    pending: 'amber',
    draft: 'gray',
    future: 'blue',
    private: 'gray',
};

/**
 * A page link of the table that also works with plain permalinks
 * (`?page_id=12` already in the account page URL).
 *
 * @param {string} pageUrl Account page URL.
 * @param {string} type    Post type (section).
 * @param {number} n       Page number.
 * @return {string} URL.
 */
const pageHref = ( pageUrl, type, n ) => {
    const url = new URL( pageUrl, window.location.href );

    url.searchParams.set( 'section', type );
    url.searchParams.set( 'pagenum', String( n ) );

    return url.toString();
};

/**
 * @param {Object} props
 * @param {string} props.type    Post type.
 * @param {string} props.label   Section label.
 * @param {string} props.pageUrl Account page URL.
 */
export default function Posts( { type, label, pageUrl } ) {
    // `?pagenum=` from the store's location, so back / forward pages too.
    const { page, state } = useSelect( ( select ) => ( {
        page: select( store ).getPage(),
        state: select( store ).getPosts( type ),
    } ), [ type ] );
    const { loadPosts, deletePost, navigate, setPostsError } = useDispatch( store );
    const [ notice, setNotice ] = useState( () => ( 'deleted' === new URLSearchParams( window.location.search ).get( 'msg' ) ? __( 'Item Deleted successfully!', 'wp-user-frontend' ) : '' ) );
    const [ deleting, setDeleting ] = useState( 0 );

    useEffect( () => {
        loadPosts( type, page );
    }, [ type, page, loadPosts ] );

    const changePage = ( next ) => {
        const url = new URL( window.location.href );

        url.searchParams.set( 'section', type );

        if ( next > 1 ) {
            url.searchParams.set( 'pagenum', String( next ) );
        } else {
            url.searchParams.delete( 'pagenum' );
        }

        navigate( url.toString() );
    };

    const remove = async ( post ) => {
        const ok = await confirmDialog( {
            title: __( 'Are you sure to delete?', 'wp-user-frontend' ),
            text: post.title,
            confirmText: __( 'Delete', 'wp-user-frontend' ),
        } );

        if ( ! ok ) {
            return;
        }

        setDeleting( post.id );

        if ( await deletePost( type, post.id, page ) ) {
            setNotice( __( 'Item Deleted successfully!', 'wp-user-frontend' ) );
        }

        setDeleting( 0 );
    };

    const data = state.data;
    const columns = data?.columns || {};

    return (
        <div className="wpuf-posts-section">
            <header className="wpuf-section-head">
                { /* translators: %s: post type label */ }
                <h2 className="wpuf-section-head__title">{ sprintf( __( 'My %s', 'wp-user-frontend' ), data?.post_type?.label || label ) }</h2>
                { data && (
                    <p className="wpuf-section-head__lead">
                        { sprintf( /* translators: %s: number of items */ data.total === 1 ? __( '%s item', 'wp-user-frontend' ) : __( '%s items', 'wp-user-frontend' ), data.total ) }
                    </p>
                ) }
            </header>

            { notice && <Notice kind="success" onClose={ () => setNotice( '' ) }>{ notice }</Notice> }
            { state.error && <Notice kind="error" onClose={ () => setPostsError( type, '' ) }>{ state.error }</Notice> }

            <Card className="wpuf-posts-card">
                { state.loading && ! data ? (
                    <SkeletonRows rows={ 5 } cols={ 4 } />
                ) : ! data || ! data.items.length ? (
                    <p className="wpuf-muted wpuf-empty">
                        { /* translators: %s: post type label */ }
                        { sprintf( __( 'No %s found', 'wp-user-frontend' ), data?.post_type?.label || label ) }
                    </p>
                ) : (
                    <div className="wpuf-table-wrap" aria-busy={ state.loading || undefined }>
                        <table className={ `items-table ${ type } wpuf-table` }>
                            <thead>
                                <tr className="items-list-header">
                                    { columns.thumbnail && <th scope="col">{ __( 'Featured Image', 'wp-user-frontend' ) }</th> }
                                    <th scope="col">{ __( 'Title', 'wp-user-frontend' ) }</th>
                                    <th scope="col">{ __( 'Status', 'wp-user-frontend' ) }</th>
                                    <th scope="col">{ __( 'Date', 'wp-user-frontend' ) }</th>
                                    { columns.payment && <th scope="col">{ __( 'Payment', 'wp-user-frontend' ) }</th> }
                                    <th scope="col" className="wpuf-table__actions">{ __( 'Options', 'wp-user-frontend' ) }</th>
                                </tr>
                            </thead>
                            <tbody>
                                { data.items.map( ( post ) => (
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
                                        <td data-label={ __( 'Status', 'wp-user-frontend' ) }>
                                            <Pill tone={ TONE[ post.status ] || 'gray' }>{ post.status_label }</Pill>
                                        </td>
                                        <td data-label={ __( 'Date', 'wp-user-frontend' ) }>{ post.date.slice( 0, 10 ) }</td>
                                        { columns.payment && (
                                            <td data-label={ __( 'Payment', 'wp-user-frontend' ) }>
                                                { /* The classic column: no payment -> Not Applicable, unpaid -> Pay Now, paid -> Completed. */ }
                                                { ! post.payment_status ? (
                                                    <span className="wpuf-muted">{ __( 'Not Applicable', 'wp-user-frontend' ) }</span>
                                                ) : 'completed' === post.payment_status ? (
                                                    <Pill tone="green">{ __( 'Completed', 'wp-user-frontend' ) }</Pill>
                                                ) : post.pay_url ? (
                                                    <Button variant="secondary" size="sm" href={ post.pay_url }>{ __( 'Pay Now', 'wp-user-frontend' ) }</Button>
                                                ) : (
                                                    <Pill tone="amber">{ __( 'Pending', 'wp-user-frontend' ) }</Pill>
                                                ) }
                                            </td>
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
                                                { post.can_delete && (
                                                    <Button variant="ghost" size="sm" className="wpuf-row-actions__delete" busy={ deleting === post.id } onClick={ () => remove( post ) } title={ __( 'Delete', 'wp-user-frontend' ) }>
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
                ) }
                { data && <Pagination page={ data.page } pages={ data.pages } onChange={ changePage } hrefFor={ ( n ) => pageHref( pageUrl, type, n ) } /> }
            </Card>
        </div>
    );
}
