/**
 * Submissions page of one post form (`#/post-forms/:id/submissions`): the
 * posts it created (the `_wpuf_form_id` meta), FlyForms' responses page
 * shape on the forms list's own tokens: back link and form name, then the
 * list card with status tabs (counts), search, the table and the footer
 * pagination. Opened from the forms list's Submissions count.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { request, restPath } from '@wpuf/api';
import { Button, EmptyState, ErrorState, Pagination, Tabs } from '@wpuf/components';
import { ArrowLeft, Inbox, MessageSquare, SearchX } from 'lucide-react';
import { Skeleton } from '@wedevs/plugin-ui';
import { openRoute } from '../../../app/client';
import { foldMenu, unfoldMenu } from '../../form-builder/skeleton';
import SearchBar from './SearchBar';
import TableSkeleton from './TableSkeleton';
import { STATUS_BADGE_CLASSES } from '../utils/constants';

const TH = 'px-4 font-normal';
const TD = 'px-4 py-2.5 align-middle text-[13px]';
const LINK = 'font-medium text-primary no-underline hover:underline focus:text-primary';

const STATUS_TABS = () => [
    { id: 'any', label: __( 'All', 'wp-user-frontend' ) },
    { id: 'publish', label: __( 'Published', 'wp-user-frontend' ) },
    { id: 'pending', label: __( 'Pending Review', 'wp-user-frontend' ) },
    { id: 'draft', label: __( 'Draft', 'wp-user-frontend' ) },
    { id: 'future', label: __( 'Scheduled', 'wp-user-frontend' ) },
    { id: 'private', label: __( 'Private', 'wp-user-frontend' ) },
];

const BADGE = {
    ...STATUS_BADGE_CLASSES,
    future: 'bg-blue-50 border-blue-200 text-blue-800',
};

// Tones of the extra columns' badges (`wpuf_form_submissions_columns`).
const TONE = {
    green: 'bg-emerald-50 border-emerald-200 text-emerald-800',
    yellow: 'bg-yellow-100 border-yellow-200 text-yellow-800',
    red: 'bg-red-50 border-red-200 text-red-700',
    blue: 'bg-blue-50 border-blue-200 text-blue-800',
    orange: 'bg-orange-50 border-orange-200 text-orange-800',
    gray: 'bg-gray-100 border-gray-200 text-gray-700',
};

const formsUrl = () => ( window.wpuf_admin_script || {} ).admin_url + 'admin.php?page=wpuf-post-forms';

/**
 * @param {Object} props
 * @param {number} props.formId Form ID (route param).
 */
const SubmissionsPage = ( { formId } ) => {
    const [ status, setStatus ] = useState( 'any' );
    const [ search, setSearch ] = useState( '' );
    const [ query, setQuery ] = useState( '' );
    const [ page, setPage ] = useState( 1 );
    const [ perPage, setPerPage ] = useState( 10 );
    const [ data, setData ] = useState( null );
    const [ loading, setLoading ] = useState( true );
    const [ error, setError ] = useState( null );
    const [ attempt, setAttempt ] = useState( 0 );

    // Submissions is the builder's third tab: the WordPress menu folds to its
    // icon rail like on Form Editor and Settings, and opens again on leave
    // unless the user had it folded already.
    useEffect( () => {
        foldMenu();

        return unfoldMenu;
    }, [] );

    // Search waits for typing to pause, like the forms list.
    useEffect( () => {
        const id = window.setTimeout( () => {
            setQuery( search.trim() );
            setPage( 1 );
        }, 300 );

        return () => window.clearTimeout( id );
    }, [ search ] );

    useEffect( () => {
        let live = true;

        setLoading( true );
        setError( null );
        request( restPath( 'wpuf/v1', `/wpuf_form/${ formId }/submissions`, { page, per_page: perPage, status, s: query } ) )
            .then( ( response ) => live && setData( response ) )
            .catch( ( failure ) => live && setError( failure || {} ) )
            .finally( () => live && setLoading( false ) );

        return () => {
            live = false;
        };
    }, [ formId, page, perPage, status, query, attempt ] );

    const counts = ( data && data.counts ) || {};
    // Counts only once known (no "(0)" flash while the first page loads).
    const tabs = STATUS_TABS().map( ( tab ) => ( data ? { ...tab, count: counts[ tab.id ] || 0 } : tab ) );
    const form = ( data && data.form ) || null;
    const items = ( data && data.items ) || [];
    const filtered = '' !== query || 'any' !== status;
    const taxColumns = ( data && data.columns ) || [];
    const hasComments = !! ( data && data.comments );
    const extraColumns = ( data && data.extra_columns ) || [];

    const back = ( event ) => {
        event.preventDefault();
        openRoute( '/post-forms', formsUrl() );
    };

    let content;

    if ( loading && ! data ) {
        content = <TableSkeleton checkbox={ false } />;
    } else if ( error ) {
        content = (
            <ErrorState
                title={ __( 'Could not load the submissions', 'wp-user-frontend' ) }
                message={ error.message }
                onRetry={ () => setAttempt( attempt + 1 ) }
            />
        );
    } else if ( ! items.length ) {
        content = filtered ? (
            <EmptyState
                size="card"
                icon={ SearchX }
                title={ __( 'No submission matches those filters', 'wp-user-frontend' ) }
                description={ sprintf(
                    /* translators: %d: number of submissions of the form */
                    _n( 'This form has %d submission in all.', 'This form has %d submissions in all.', counts.any || 0, 'wp-user-frontend' ),
                    counts.any || 0
                ) }
                actions={
                    <Button variant="secondary" onClick={ () => {
                        setSearch( '' );
                        setQuery( '' );
                        setStatus( 'any' );
                        setPage( 1 );
                    } }>
                        { __( 'Clear filters', 'wp-user-frontend' ) }
                    </Button>
                }
            />
        ) : (
            <EmptyState
                size="card"
                icon={ Inbox }
                title={ __( 'No submissions yet', 'wp-user-frontend' ) }
                description={ __( 'Add the form to a page; posts submitted through it appear here.', 'wp-user-frontend' ) }
            />
        );
    } else {
        content = (
            <>
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead className="border-0 border-b border-solid border-gray-200 bg-white">
                            <tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
                                <th className={ TH }>{ __( 'Title', 'wp-user-frontend' ) }</th>
                                <th className={ TH }>{ __( 'Author', 'wp-user-frontend' ) }</th>
                                { taxColumns.map( ( column ) => <th key={ column.key } className={ TH }>{ column.label }</th> ) }
                                { hasComments ? (
                                    <th className={ TH }>
                                        <MessageSquare className="size-4 align-middle" aria-hidden="true" />
                                        <span className="sr-only">{ __( 'Comments', 'wp-user-frontend' ) }</span>
                                    </th>
                                ) : null }
                                { extraColumns.map( ( column ) => <th key={ column.key } className={ TH }>{ column.label }</th> ) }
                                <th className={ TH }>{ __( 'Status', 'wp-user-frontend' ) }</th>
                                <th className={ TH }>{ __( 'Date', 'wp-user-frontend' ) }</th>
                                <th className={ TH }><span className="sr-only">{ __( 'Actions', 'wp-user-frontend' ) }</span></th>
                            </tr>
                        </thead>
                        <tbody className={ loading ? 'opacity-60' : '' }>
                            { items.map( ( item ) => (
                                <tr key={ item.id } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
                                    <td className={ TD + ' font-medium text-gray-900' }>
                                        { item.edit_url
                                            ? <a href={ item.edit_url } className="font-medium text-gray-900 no-underline hover:text-primary hover:underline focus:text-primary">{ item.title || __( '(no title)', 'wp-user-frontend' ) }</a>
                                            : ( item.title || __( '(no title)', 'wp-user-frontend' ) ) }
                                    </td>
                                    <td className={ TD + ' text-gray-500' }>{ item.author }</td>
                                    { taxColumns.map( ( column ) => {
                                        const names = ( item.terms && item.terms[ column.key ] ) || [];

                                        return (
                                            <td key={ column.key } className={ TD + ' max-w-48 text-gray-500' }>
                                                { names.length ? names.join( ', ' ) : <span aria-hidden="true">&mdash;</span> }
                                            </td>
                                        );
                                    } ) }
                                    { hasComments ? <td className={ TD + ' text-gray-500' }>{ item.comments }</td> : null }
                                    { extraColumns.map( ( column ) => {
                                        const value = item.extra && item.extra[ column.key ];

                                        return (
                                            <td key={ column.key } className={ TD }>
                                                { value && value.text ? (
                                                    <span title={ value.title || '' } className={ 'inline-flex max-w-56 items-center truncate py-[2px] px-2 rounded-[5px] text-xs font-medium border border-solid ' + ( TONE[ value.tone ] || TONE.gray ) }>
                                                        { value.text }
                                                    </span>
                                                ) : <span className="text-gray-400" aria-hidden="true">&mdash;</span> }
                                            </td>
                                        );
                                    } ) }
                                    <td className={ TD }>
                                        <span className={ 'inline-flex items-center py-[2px] px-2 rounded-[5px] text-xs font-medium border border-solid ' + ( BADGE[ item.status ] || BADGE.draft ) }>
                                            { item.status_label }
                                        </span>
                                    </td>
                                    <td className={ TD + ' whitespace-nowrap text-gray-500' }>
                                        <span className="block text-gray-900">{ item.date_label }</span>
                                        { /* translators: 1: date, 2: time */ }
                                        { sprintf( __( '%1$s at %2$s', 'wp-user-frontend' ), item.date, item.time ) }
                                    </td>
                                    <td className={ TD + ' whitespace-nowrap text-right' }>
                                        <span className="inline-flex items-center gap-4">
                                            { item.edit_url ? <a href={ item.edit_url } className={ LINK }>{ __( 'Edit', 'wp-user-frontend' ) }</a> : null }
                                            { item.view_url ? <a href={ item.view_url } className={ LINK } target="_blank" rel="noopener noreferrer">{ __( 'View', 'wp-user-frontend' ) }</a> : null }
                                        </span>
                                    </td>
                                </tr>
                            ) ) }
                        </tbody>
                    </table>
                </div>

                <Pagination
                    variant="footer"
                    currentPage={ data.page }
                    total={ data.total }
                    perPage={ data.per_page }
                    onPageChange={ setPage }
                    onPerPageChange={ ( next ) => {
                        setPerPage( next );
                        setPage( 1 );
                    } }
                />
            </>
        );
    }

    return (
        <div>
            { /* The builder's header card (icon, form name, ID, the form's tabs), so
                 Form Editor / Settings / Submissions read as one screen. */ }
            <div className="mt-4 rounded-lg bg-white px-4 py-2.5 ring-1 ring-gray-200">
                <div className="flex items-center justify-between gap-4">
                    <div className="flex min-w-0 flex-1 items-center">
                        <a href="#/post-forms" onClick={ back } className="mr-2 inline-flex size-7 shrink-0 items-center justify-center rounded-md text-gray-500 no-underline hover:bg-gray-100 hover:text-gray-900 focus:text-gray-900" aria-label={ __( 'Back to forms', 'wp-user-frontend' ) } title={ __( 'Back to forms', 'wp-user-frontend' ) }>
                            <ArrowLeft className="size-4" aria-hidden="true" />
                        </a>
                        <img src={ `${ ( window.wpuf_admin_script || {} ).asset_url || '' }/images/wpuf-icon-circle.svg` } alt="" className="mr-1.5 size-7 shrink-0" />
                        { form
                            ? <span className="truncate px-2 text-sm font-medium text-gray-900">{ form.title }</span>
                            : <Skeleton className="mx-2 h-4 w-40 rounded-md bg-gray-200" /> }
                        { form ? (
                            <span className="ml-3 shrink-0 rounded-md border border-solid border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs font-medium leading-none text-gray-600">#{ form.id }</span>
                        ) : null }
                    </div>
                    <div className="flex shrink-0 items-center gap-1 rounded-lg bg-gray-100 p-1" role="tablist">
                        <a href={ `#/post-forms/${ formId }/edit` } role="tab" aria-selected={ false } className="rounded-md px-3 py-1.5 text-sm font-medium leading-5 text-gray-500 no-underline hover:text-gray-900 focus:text-gray-900 focus:shadow-none">
                            { __( 'Form Editor', 'wp-user-frontend' ) }
                        </a>
                        <a href={ `#/post-forms/${ formId }/edit?tab=settings` } role="tab" aria-selected={ false } className="rounded-md px-3 py-1.5 text-sm font-medium leading-5 text-gray-500 no-underline hover:text-gray-900 focus:text-gray-900 focus:shadow-none">
                            { __( 'Settings', 'wp-user-frontend' ) }
                        </a>
                        <span role="tab" aria-selected="true" className="rounded-md bg-white px-3 py-1.5 text-sm font-medium leading-5 text-gray-900 shadow-xs">
                            { __( 'Submissions', 'wp-user-frontend' ) }
                        </span>
                    </div>
                    <div className="flex-1" />
                </div>
            </div>

            <div className="mt-4 rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3 border-0 border-b border-solid border-gray-200 px-4 pt-3 pb-2">
                    <Tabs
                        variant="toolbar"
                        tabs={ tabs }
                        value={ status }
                        onChange={ ( next ) => {
                            setStatus( next );
                            setPage( 1 );
                        } }
                        label={ __( 'Submission status', 'wp-user-frontend' ) }
                    />
                    <SearchBar
                        value={ search }
                        onChange={ setSearch }
                        placeholder={ __( 'Search submissions', 'wp-user-frontend' ) }
                    />
                </div>

                { content }
            </div>
        </div>
    );
};

export default SubmissionsPage;
