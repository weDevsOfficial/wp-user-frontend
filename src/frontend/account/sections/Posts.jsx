/**
 * The posts of one type: the kit's PostsTable (status pill, the actions:
 * view, edit, pay, delete with a confirm), `?pagenum=` honoured and
 * written, the "deleted" notice after a delete.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Card, Notice, Pagination, PostsTable, SkeletonRows, confirmDialog } from '@wpuf/frontend-kit';
import { store } from '../store';

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
                    <PostsTable type={ type } items={ data.items } columns={ columns } deleting={ deleting } onDelete={ remove } busy={ state.loading } />
                ) }
                { data && <Pagination page={ data.page } pages={ data.pages } onChange={ changePage } hrefFor={ ( n ) => pageHref( pageUrl, type, n ) } /> }
            </Card>
        </div>
    );
}
