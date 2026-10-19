/**
 * One `[wpuf_dashboard]` list: the author card on top ("<name>'s Dashboard",
 * avatar, bio, post count), then the kit's PostsTable in a card with
 * pagination (`?pagenum=`, as the classic list). Rows, columns and the settings
 * they follow come from Dashboard_Service.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Avatar, Card, Notice, Pagination, PostsTable, SkeletonRows, confirmDialog } from '@wpuf/frontend-kit';
import { store } from './store';

/**
 * The URL of a page of the list (`?pagenum=`, kept off for page 1).
 *
 * @param {number} n Page number.
 * @return {string} URL.
 */
const pageHref = ( n ) => {
    const url = new URL( window.location.href );

    url.searchParams.delete( 'msg' );

    if ( n > 1 ) {
        url.searchParams.set( 'pagenum', String( n ) );
    } else {
        url.searchParams.delete( 'pagenum' );
    }

    return url.toString();
};

const pageOfLocation = () => Math.max( 1, parseInt( new URLSearchParams( window.location.search ).get( 'pagenum' ) || '1', 10 ) || 1 );

/**
 * @param {Object} props
 * @param {string} props.mountKey Mount key (store slice).
 * @param {Object} props.signed   { atts, sig } for the REST routes.
 */
export default function Dashboard( { mountKey, signed } ) {
    const list = useSelect( ( select ) => select( store ).getList( mountKey ), [ mountKey ] );
    const { loadPage, deletePost, setError, setNotice } = useDispatch( store );
    const data = list.data;

    // Back / forward between pages of the list.
    useEffect( () => {
        const onPop = () => loadPage( mountKey, signed, pageOfLocation() );

        window.addEventListener( 'popstate', onPop );

        return () => window.removeEventListener( 'popstate', onPop );
    }, [ mountKey, signed, loadPage ] );

    const changePage = ( next ) => {
        window.history.pushState( {}, '', pageHref( next ) );
        loadPage( mountKey, signed, next );
    };

    const remove = async ( post ) => {
        const ok = await confirmDialog( {
            title: __( 'Are you sure to delete?', 'wp-user-frontend' ),
            text: post.title,
            confirmText: __( 'Delete', 'wp-user-frontend' ),
        } );

        if ( ok ) {
            deletePost( mountKey, signed, post.id );
        }
    };

    if ( ! data ) {
        return <SkeletonRows rows={ 5 } cols={ 4 } />;
    }

    const labels = data.labels.join( ', ' );
    /* translators: %1$d: number of posts, %2$s: post type labels */
    const count = data.show_count && labels ? sprintf( __( 'You have created %1$d (%2$s)', 'wp-user-frontend' ), data.total, labels ) : '';

    return (
        <div className="wpuf-dashboard">
            { /* The classic "Author Info" box, now the head of the list (Settings > Dashboard > Show user bio). */ }
            { data.author ? (
                <Card className="wpuf-author wpuf-dashboard__hero">
                    <Avatar url={ data.author.avatar } alt="" size={ 64 } />
                    <div className="wpuf-dashboard__hero-text">
                        <h2 className="wpuf-section-head__title page-head">{ data.title }</h2>
                        { data.author.bio && <p className="wpuf-muted wpuf-dashboard__author-bio">{ data.author.bio }</p> }
                        <a className="wpuf-dashboard__author-link" href={ data.author.url }>{ __( 'View your public posts', 'wp-user-frontend' ) }</a>
                    </div>
                    { count && <p className="wpuf-dashboard__count post_count">{ count }</p> }
                </Card>
            ) : (
                <header className="wpuf-section-head wpuf-dashboard__head">
                    <h2 className="wpuf-section-head__title page-head">{ data.title }</h2>
                    { count && <p className="wpuf-section-head__lead post_count">{ count }</p> }
                </header>
            ) }

            { list.notice && <Notice kind="success" onClose={ () => setNotice( mountKey, '' ) }>{ list.notice }</Notice> }
            { list.error && <Notice kind="error" onClose={ () => setError( mountKey, '' ) }>{ list.error }</Notice> }

            <Card className="wpuf-posts-card">
                { list.loading && ! data.items.length ? (
                    <SkeletonRows rows={ 5 } cols={ 4 } />
                ) : ! data.items.length ? (
                    <p className="wpuf-muted wpuf-empty">
                        { sprintf( /* translators: %s: post type labels */ __( 'No %s found', 'wp-user-frontend' ), labels ) }
                    </p>
                ) : (
                    <PostsTable
                        type={ data.post_types.join( ' ' ) }
                        items={ data.items }
                        columns={ data.columns }
                        deleting={ list.deleting }
                        onDelete={ remove }
                        busy={ list.loading }
                    />
                ) }
                <Pagination page={ data.page } pages={ data.pages } onChange={ changePage } hrefFor={ pageHref } />
            </Card>
        </div>
    );
}
