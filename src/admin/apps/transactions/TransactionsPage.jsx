/**
 * Transactions page: totals, then the list card (FlyHR): status tabs and
 * filters (search, gateway, date range), the selection bar, the table and
 * the footer pagination. Tab, filters, sort and page are in the route query.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback, useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Button, DateTime, EmptyState, ErrorState, PageFooter, PageHeader, PageShell, Pagination, SearchInput, Select, Tabs, dialogs, notify } from '@wpuf/components';
import { Receipt, SearchX, X } from 'lucide-react';

import SummaryCards from './SummaryCards';
import { store } from './store';
import TableSkeleton from './TableSkeleton';
import TransactionDetails from './TransactionDetails';
import TransactionsTable from './TransactionsTable';

const data = () => window.wpufTransactions || {};


const CONFIRM = {
    accept: ( count ) => ( {
        tone: 'warning',
        icon: 'none',
        /* translators: %d: number of payments */
        title: sprintf( _n( 'Accept %d payment?', 'Accept %d payments?', count, 'wp-user-frontend' ), count ),
        message: __( 'The payment is marked completed and the subscription or post is given to the buyer, who gets the confirmation email.', 'wp-user-frontend' ),
        confirmText: __( 'Accept', 'wp-user-frontend' ),
    } ),
    reject: ( count ) => ( {
        tone: 'danger',
        /* translators: %d: number of payments */
        title: sprintf( _n( 'Reject %d payment?', 'Reject %d payments?', count, 'wp-user-frontend' ), count ),
        message: __( 'The pending bank payment is removed. This cannot be undone.', 'wp-user-frontend' ),
        confirmText: __( 'Reject', 'wp-user-frontend' ),
    } ),
    delete: ( count ) => ( {
        tone: 'danger',
        /* translators: %d: number of transactions */
        title: sprintf( _n( 'Delete %d transaction?', 'Delete %d transactions?', count, 'wp-user-frontend' ), count ),
        message: __( 'The payment record is deleted permanently. The buyer keeps what they bought.', 'wp-user-frontend' ),
        confirmText: __( 'Delete', 'wp-user-frontend' ),
    } ),
};

const DEFAULTS = { status: 'all', search: '', gateway: '', from: '', to: '', orderby: 'created', order: 'desc', page: 1 };

/**
 * @param {Object} props
 * @param {Object} [props.context] App route context.
 */
export default function TransactionsPage( { context } ) {
    const boot = data();
    const [ query, setQueryState ] = useState( () => {
        const fromRoute = context?.getQuery?.() || {};
        const next = { ...DEFAULTS };

        Object.keys( DEFAULTS ).forEach( ( key ) => {
            if ( fromRoute[ key ] ) {
                next[ key ] = 'page' === key ? Math.max( 1, parseInt( fromRoute[ key ], 10 ) || 1 ) : String( fromRoute[ key ] );
            }
        } );

        return next;
    } );
    const [ search, setSearch ] = useState( query.search );
    const [ perPage, setPerPage ] = useState( boot.perPage || 20 );
    const { result, error, loading } = useSelect( ( select ) => ( {
        result: select( store ).getResult(),
        error: select( store ).getError(),
        loading: select( store ).isLoading(),
    } ), [] );
    const { fetchTransactions, runAction } = useDispatch( store );
    const [ selected, setSelected ] = useState( [] );
    const [ viewing, setViewing ] = useState( null );
    const [ busy, setBusy ] = useState( false );
    const perPageSent = useRef( false );

    const setQuery = useCallback( ( patch ) => {
        setQueryState( ( current ) => {
            const next = { ...current, ...patch };
            const inRoute = {};

            Object.keys( next ).forEach( ( key ) => {
                if ( next[ key ] !== DEFAULTS[ key ] ) {
                    inRoute[ key ] = next[ key ];
                }
            } );
            context?.setQuery?.( inRoute );

            return next;
        } );
        setSelected( [] );
    }, [ context ] );

    const load = useCallback( ( signal ) => {
        const params = { ...query, per_page: perPageSent.current ? perPage : undefined };

        return fetchTransactions( params, signal ).then( ( response ) => {
            if ( response ) {
                setPerPage( response.per_page );
            }
        } );
    }, [ query, perPage, fetchTransactions ] );

    useEffect( () => {
        const controller = new window.AbortController();

        load( controller.signal );

        return () => controller.abort();
    }, [ load ] );

    // Search as you type, after a pause.
    useEffect( () => {
        if ( search === query.search ) {
            return undefined;
        }

        const timer = setTimeout( () => setQuery( { search, page: 1 } ), 400 );

        return () => clearTimeout( timer );
    }, [ search, query.search, setQuery ] );

    const counts = result?.counts || {};
    const rows = useMemo( () => result?.items || [], [ result ] );
    const chosen = rows.filter( ( row ) => selected.includes( row.key ) );
    const orders = chosen.filter( ( row ) => 'order' === row.kind );
    const records = chosen.filter( ( row ) => 'transaction' === row.kind );
    const filtered = query.search || query.gateway || query.from || query.to;

    const tabs = [
        { id: 'all', label: __( 'All', 'wp-user-frontend' ), count: counts.all },
        { id: 'completed', label: __( 'Completed', 'wp-user-frontend' ), count: counts.completed },
        { id: 'pending', label: __( 'Pending', 'wp-user-frontend' ), count: counts.pending },
    ];

    const act = async ( action, targets ) => {
        if ( ! targets.length || ! ( await dialogs.confirm( CONFIRM[ action ]( targets.length ) ) ) ) {
            return;
        }

        setBusy( true );

        try {
            const response = await runAction( action, targets );

            notify( response.message, response.done ? 'success' : 'error' );
            setViewing( null );
            setSelected( [] );
            await load();
        } catch ( failure ) {
            notify( failure.message, 'error' );
        } finally {
            setBusy( false );
        }
    };

    const sortBy = ( next ) => setQuery( { ...next, page: 1 } );

    const clearFilters = () => {
        setSearch( '' );
        setQuery( { search: '', gateway: '', from: '', to: '', page: 1 } );
    };

    let content;

    if ( loading && ! result ) {
        content = <TableSkeleton />;
    } else if ( error ) {
        content = <ErrorState title={ __( 'Could not load the transactions', 'wp-user-frontend' ) } message={ error.message } onRetry={ () => load() } />;
    } else if ( ! rows.length ) {
        content = (
            <EmptyState
                size={ filtered ? 'card' : 'page' }
                icon={ filtered ? SearchX : Receipt }
                showPlus={ false }
                title={ filtered ? __( 'No payments match these filters', 'wp-user-frontend' ) : __( 'No transactions found.', 'wp-user-frontend' ) }
                description={ filtered ? '' : __( 'Payments for subscriptions and paid posts show up here.', 'wp-user-frontend' ) }
                actions={ filtered ? <Button variant="secondary" onClick={ clearFilters }>{ __( 'Clear filters', 'wp-user-frontend' ) }</Button> : null }
            />
        );
    } else {
        content = (
            <div className={ loading ? 'opacity-60 transition-opacity' : '' }>
                <TransactionsTable
                    rows={ rows }
                    selected={ selected }
                    onSelect={ setSelected }
                    sort={ { orderby: query.orderby, order: query.order } }
                    onSort={ sortBy }
                    onView={ setViewing }
                    onAction={ act }
                    canManage={ !! boot.canManage }
                />
                <Pagination
                    variant="footer"
                    currentPage={ query.page }
                    total={ result.total }
                    perPage={ perPage }
                    onPageChange={ ( page ) => setQuery( { page } ) }
                    onPerPageChange={ ( value ) => {
                        perPageSent.current = true;
                        setPerPage( value );
                        setQuery( { page: 1 } );
                    } }
                />
            </div>
        );
    }

    return (
        <PageShell>
            <PageHeader utm="wpuf-transactions" />

            <div className="wpuf-transactions mt-6 pb-10">
                <h1 className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">{ __( 'Transactions', 'wp-user-frontend' ) }</h1>
                <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Payments for subscriptions and paid posts. Bank payments wait here until you accept them.', 'wp-user-frontend' ) }</p>

                <SummaryCards counts={ counts } />

                <div className="mt-6 rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-0 border-b border-solid border-gray-200 px-4 pt-3 pb-2">
                        <Tabs variant="toolbar" tabs={ tabs } value={ query.status } onChange={ ( status ) => setQuery( { status, page: 1 } ) } label={ __( 'Payment status', 'wp-user-frontend' ) } />
                        <SearchInput
                            value={ search }
                            onChange={ setSearch }
                            placeholder={ __( 'Search payer, email or ID', 'wp-user-frontend' ) }
                            aria-label={ __( 'Search payments', 'wp-user-frontend' ) }
                            className="w-64"
                        />
                    </div>

                    <div className="flex flex-wrap items-center gap-3 border-0 border-b border-solid border-gray-200 px-4 py-3">
                        <label htmlFor="wpuf-tx-gateway" className="sr-only">{ __( 'Gateway', 'wp-user-frontend' ) }</label>
                        <Select
                            id="wpuf-tx-gateway"
                            value={ query.gateway }
                            onChange={ ( gateway ) => setQuery( { gateway, page: 1 } ) }
                            className="w-48"
                            options={ [ { value: '', label: __( 'All gateways', 'wp-user-frontend' ) }, ...( boot.gateways || [] ).map( ( gateway ) => ( { value: gateway, label: gateway } ) ) ] }
                        />
                        <DateTime id="wpuf-tx-from" value={ query.from } onChange={ ( from ) => setQuery( { from, page: 1 } ) } placeholder={ __( 'From date', 'wp-user-frontend' ) } className="w-44 min-w-0" />
                        <DateTime id="wpuf-tx-to" value={ query.to } onChange={ ( to ) => setQuery( { to, page: 1 } ) } placeholder={ __( 'To date', 'wp-user-frontend' ) } className="w-44 min-w-0" />
                        { filtered && (
                            <Button variant="link" onClick={ clearFilters }>
                                <X size={ 14 } aria-hidden="true" />
                                { __( 'Clear filters', 'wp-user-frontend' ) }
                            </Button>
                        ) }
                    </div>

                    { boot.canManage && chosen.length > 0 && (
                        <div className="flex flex-wrap items-center gap-3 border-0 border-b border-solid border-gray-200 bg-primary/5 px-4 py-2.5">
                            <span className="text-sm font-medium text-gray-900" aria-live="polite">
                                { sprintf(
                                    /* translators: %d: number of selected payments */
                                    _n( '%d selected', '%d selected', chosen.length, 'wp-user-frontend' ),
                                    chosen.length
                                ) }
                            </span>
                            <div className="flex items-center gap-2">
                                { orders.length > 0 && (
                                    <>
                                        <Button size="sm" variant="secondary" disabled={ busy } onClick={ () => act( 'accept', orders ) }>
                                            { /* translators: %d: number of pending payments */ }
                                            { sprintf( __( 'Accept (%d)', 'wp-user-frontend' ), orders.length ) }
                                        </Button>
                                        <Button size="sm" variant="danger-outline" disabled={ busy } onClick={ () => act( 'reject', orders ) }>
                                            { /* translators: %d: number of pending payments */ }
                                            { sprintf( __( 'Reject (%d)', 'wp-user-frontend' ), orders.length ) }
                                        </Button>
                                    </>
                                ) }
                                { records.length > 0 && (
                                    <Button size="sm" variant="danger-outline" disabled={ busy } onClick={ () => act( 'delete', records ) }>
                                        { /* translators: %d: number of completed payments */ }
                                        { sprintf( __( 'Delete (%d)', 'wp-user-frontend' ), records.length ) }
                                    </Button>
                                ) }
                            </div>
                            <button type="button" className="cursor-pointer border-0 bg-transparent p-0 text-sm text-gray-500 hover:text-gray-900" onClick={ () => setSelected( [] ) }>
                                { __( 'Clear', 'wp-user-frontend' ) }
                            </button>
                        </div>
                    ) }

                    { content }
                </div>
            </div>

            <TransactionDetails row={ viewing } onClose={ () => setViewing( null ) } onAction={ act } canManage={ !! boot.canManage } />
            <PageFooter />
        </PageShell>
    );
}
