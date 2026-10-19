/**
 * One `[wpuf_dashboard]`, laid out like the FlyHR employee profile: the
 * header card (avatar, name, actions, meta chips), the status cards, what
 * needs attention, then the posts card (status tabs, search, the kit's
 * PostsTable, pagination) beside the plan, comments and payments cards.
 * Rows, columns, blocks and the settings behind them come from the server
 * (Dashboard_Service, Dashboard_Overview). Filters and page live in the
 * URL (`?status=`, `?q=`, `?type=`, `?pagenum=`), so a reload or a shared
 * link opens the same list.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Card, Notice, Pagination, PostsTable, SkeletonRows, confirmDialog, cx, icons } from '@wpuf/frontend-kit';
import ProfileHeader from './ProfileHeader';
import { Attention, CommentsCard, PaymentsCard, PlanCard, StatusCards } from './Overview';
import { store } from './store';

const { Search } = icons;

const PARAMS = { status: 'status', search: 'q', type: 'type' };

/**
 * The URL of a list state.
 *
 * @param {Object} filters status, search, type.
 * @param {number} page    Page number.
 * @return {string} URL.
 */
const urlFor = ( filters, page ) => {
    const url = new URL( window.location.href );

    url.searchParams.delete( 'msg' );

    Object.entries( PARAMS ).forEach( ( [ key, param ] ) => {
        if ( filters[ key ] ) {
            url.searchParams.set( param, filters[ key ] );
        } else {
            url.searchParams.delete( param );
        }
    } );

    if ( page > 1 ) {
        url.searchParams.set( 'pagenum', String( page ) );
    } else {
        url.searchParams.delete( 'pagenum' );
    }

    return url.toString();
};

/** The list state of the current URL. */
const stateOfLocation = () => {
    const params = new URLSearchParams( window.location.search );

    return {
        filters: { status: params.get( 'status' ) || '', search: params.get( 'q' ) || '', type: params.get( 'type' ) || '' },
        page: Math.max( 1, parseInt( params.get( 'pagenum' ) || '1', 10 ) || 1 ),
    };
};

/**
 * @param {Object} props
 * @param {string} props.mountKey Mount key (store slice).
 * @param {Object} props.signed   { atts, sig } for the REST routes.
 */
export default function Dashboard( { mountKey, signed } ) {
    const list = useSelect( ( select ) => select( store ).getList( mountKey ), [ mountKey ] );
    const { loadPage, filter, setFilters, deletePost, setError, setNotice, setPhotos } = useDispatch( store );
    const [ search, setSearch ] = useState( list.filters.search );
    const typing = useRef( 0 );
    const data = list.data;
    const overview = list.overview || {};
    const blocks = overview.blocks || {};

    // Back / forward between list states.
    useEffect( () => {
        const onPop = () => {
            const { filters, page } = stateOfLocation();

            setFilters( mountKey, filters );
            setSearch( filters.search );
            loadPage( mountKey, signed, page );
        };

        window.addEventListener( 'popstate', onPop );

        return () => window.removeEventListener( 'popstate', onPop );
    }, [ mountKey, signed, loadPage, setFilters ] );

    const applyFilter = ( change ) => {
        const next = { ...list.filters, ...change };

        window.history.pushState( {}, '', urlFor( next, 1 ) );
        filter( mountKey, signed, change );
    };

    const changePage = ( next ) => {
        window.history.pushState( {}, '', urlFor( list.filters, next ) );
        loadPage( mountKey, signed, next );
    };

    const onSearch = ( value ) => {
        setSearch( value );
        window.clearTimeout( typing.current );
        typing.current = window.setTimeout( () => applyFilter( { search: value.trim() } ), 400 );
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
    const stats = overview.stats || [];
    const filtered = !! ( list.filters.status || list.filters.search || list.filters.type );
    const side = [
        overview.plan && <PlanCard key="plan" plan={ overview.plan } />,
        overview.comments && <CommentsCard key="comments" comments={ overview.comments } />,
        overview.payments && <PaymentsCard key="payments" payments={ overview.payments } />,
    ].filter( Boolean );
    const statusTabs = [ { status: '', label: __( 'All', 'wp-user-frontend' ) }, ...stats ];

    return (
        <div className="wpuf-dashboard">
            { overview.profile && (
                <ProfileHeader
                    profile={ overview.profile }
                    actions={ overview.actions }
                    total={ data.total }
                    labels={ labels }
                    showCount={ data.show_count }
                    onPhotos={ ( fresh ) => setPhotos( mountKey, fresh ) }
                />
            ) }

            { blocks.status_cards && stats.length > 0 && (
                <StatusCards stats={ stats } active={ list.filters.status } onSelect={ ( status ) => applyFilter( { status } ) } />
            ) }

            { ( overview.attention || [] ).length > 0 && <Attention items={ overview.attention } /> }

            { list.notice && <Notice kind="success" onClose={ () => setNotice( mountKey, '' ) }>{ list.notice }</Notice> }
            { list.error && <Notice kind="error" onClose={ () => setError( mountKey, '' ) }>{ list.error }</Notice> }

            <div className={ cx( 'wpuf-dashboard__body', side.length > 0 && 'has-side' ) }>
                <Card className="wpuf-posts-card wpuf-dashboard-posts">
                    <header className="wpuf-dashboard-posts__head">
                        <h3 className="wpuf-dashboard-posts__title">{ sprintf( /* translators: %s: post type labels */ __( 'My %s', 'wp-user-frontend' ), labels ) }</h3>
                        { blocks.table_filters && (
                            <label className="wpuf-dashboard-search">
                                <Search size={ 16 } aria-hidden="true" />
                                <span className="wpuf-sr-only">{ __( 'Search posts', 'wp-user-frontend' ) }</span>
                                <input type="search" value={ search } placeholder={ __( 'Search posts', 'wp-user-frontend' ) } onChange={ ( event ) => onSearch( event.target.value ) } />
                            </label>
                        ) }
                    </header>

                    { blocks.table_filters && ( stats.length > 0 || data.post_types.length > 1 ) && (
                        <div className="wpuf-dashboard-tabs">
                            { stats.length > 0 && (
                                <div className="wpuf-dashboard-tabs__group" role="group" aria-label={ __( 'Status', 'wp-user-frontend' ) }>
                                    { statusTabs.map( ( tab ) => (
                                        <button
                                            key={ tab.status || 'all' }
                                            type="button"
                                            aria-pressed={ list.filters.status === tab.status }
                                            className={ cx( 'wpuf-dashboard-tabs__tab', list.filters.status === tab.status && 'is-active' ) }
                                            onClick={ () => applyFilter( { status: tab.status } ) }
                                        >
                                            { tab.label }
                                            { undefined !== tab.count && <span className="wpuf-dashboard-tabs__count">{ tab.count }</span> }
                                        </button>
                                    ) ) }
                                </div>
                            ) }
                            { data.post_types.length > 1 && (
                                <select
                                    className="wpuf-dashboard-tabs__type"
                                    value={ list.filters.type }
                                    onChange={ ( event ) => applyFilter( { type: event.target.value } ) }
                                    aria-label={ __( 'Post type', 'wp-user-frontend' ) }
                                >
                                    <option value="">{ __( 'All types', 'wp-user-frontend' ) }</option>
                                    { data.post_types.map( ( type, index ) => <option key={ type } value={ type }>{ data.labels[ index ] || type }</option> ) }
                                </select>
                            ) }
                        </div>
                    ) }

                    { list.loading && ! data.items.length ? (
                        <SkeletonRows rows={ 5 } cols={ 4 } />
                    ) : ! data.items.length ? (
                        <p className="wpuf-muted wpuf-empty">
                            { filtered
                                ? __( 'No posts match these filters.', 'wp-user-frontend' )
                                : sprintf( /* translators: %s: post type labels */ __( 'No %s found', 'wp-user-frontend' ), labels ) }
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
                    <Pagination page={ data.page } pages={ data.pages } onChange={ changePage } hrefFor={ ( n ) => urlFor( list.filters, n ) } />
                </Card>

                { side.length > 0 && <aside className="wpuf-dashboard__side">{ side }</aside> }
            </div>
        </div>
    );
}
