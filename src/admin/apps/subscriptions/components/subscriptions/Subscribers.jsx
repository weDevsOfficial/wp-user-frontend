/**
 * Subscribers of one pack (`action=subscribers&id=N`): the React view of the
 * classic Subscribers list table (WPUF > Subscriptions > a pack's "Total
 * Subscribers" count). Same columns, the All / Completed / Cancel tabs with
 * counts, sorting and paging, read from
 * `wpuf/v1/admin/subscriptions/{id}/subscribers`. The table is the
 * Transactions list's (header, sort buttons, status badge, gateway mark,
 * skeleton), so the two payment lists read the same.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Badge, Button, EmptyState, ErrorState, Pagination, SortHeader, Tabs } from '@wpuf/components';
import { ArrowLeft, Users } from 'lucide-react';
import TableSkeleton from '../../../forms-list/components/TableSkeleton';
import { fetchPackSubscribers } from '../../api/subscription';
import { useSubscriptionNavigation } from '../../hooks';

// Transactions table tokens (transactions/TransactionsTable.jsx).
const TH = 'px-2 font-normal';
const TD = 'px-2 py-2.5 align-middle text-[13px]';

const STATUS_TABS = () => [
	{ id: 'all', label: __( 'All', 'wp-user-frontend' ) },
	{ id: 'Completed', label: __( 'Completed', 'wp-user-frontend' ) },
	{ id: 'Cancel', label: __( 'Cancel', 'wp-user-frontend' ) },
];

// Badge tones (as the payments list); Cancel in the danger tone.
const TONE = {
	Completed: 'success',
	Cancel: 'danger',
};

const LABEL = {
	Completed: __( 'Completed', 'wp-user-frontend' ),
	Cancel: __( 'Cancel', 'wp-user-frontend' ),
};

/**
 * @param {Object} props
 * @param {number} props.subscriptionId Pack ID (route param).
 */
const Subscribers = ( { subscriptionId } ) => {
	const { goToList } = useSubscriptionNavigation();
	const [ status, setStatus ] = useState( 'all' );
	const [ sort, setSort ] = useState( { orderby: 'id', order: 'desc' } );
	const [ page, setPage ] = useState( 1 );
	const [ perPage, setPerPage ] = useState( 20 );
	const [ data, setData ] = useState( null );
	const [ loading, setLoading ] = useState( true );
	const [ error, setError ] = useState( null );
	const [ attempt, setAttempt ] = useState( 0 );

	useEffect( () => {
		let live = true;

		setLoading( true );
		setError( null );
		fetchPackSubscribers( subscriptionId, { status, orderby: sort.orderby, order: sort.order, page, per_page: perPage } )
			.then( ( response ) => live && setData( response ) )
			.catch( ( failure ) => live && setError( failure || {} ) )
			.finally( () => live && setLoading( false ) );

		return () => {
			live = false;
		};
	}, [ subscriptionId, status, sort, page, perPage, attempt ] );

	const counts = ( data && data.counts ) || {};
	const tabs = STATUS_TABS().map( ( tab ) => ( data ? { ...tab, count: counts[ tab.id ] || 0 } : tab ) );
	const items = ( data && data.items ) || [];
	const pack = ( data && data.pack ) || null;

	// The kit's sortable header; a new sort goes back to page 1.
	const sortable = ( key, label, extra = '' ) => (
		<SortHeader
			className={ TH + extra }
			column={ key }
			label={ label }
			sort={ sort }
			onSort={ ( next ) => {
				setSort( next );
				setPage( 1 );
			} }
		/>
	);


	const dash = <span className="text-gray-400">–</span>;

	let content;

	if ( loading && ! data ) {
		content = <TableSkeleton checkbox={ false } />;
	} else if ( error ) {
		content = (
			<ErrorState
				title={ __( 'Could not load the subscribers', 'wp-user-frontend' ) }
				message={ error.message }
				onRetry={ () => setAttempt( attempt + 1 ) }
			/>
		);
	} else if ( ! items.length ) {
		content = (
			<EmptyState
				size="card"
				icon={ Users }
				showPlus={ false }
				title={ 'all' === status ? __( 'No subscribers yet', 'wp-user-frontend' ) : __( 'No subscribers with this status', 'wp-user-frontend' ) }
				description={ 'all' === status ? __( 'Users who take this subscription appear here.', 'wp-user-frontend' ) : '' }
				actions={ 'all' !== status ? (
					<Button variant="secondary" onClick={ () => {
						setStatus( 'all' );
						setPage( 1 );
					} }>
						{ __( 'Show all', 'wp-user-frontend' ) }
					</Button>
				) : null }
			/>
		);
	} else {
		content = (
			<>
				<div className="overflow-x-auto">
					<table className="w-full border-collapse text-left">
						<thead className="border-0 border-b border-solid border-gray-200 bg-white">
							<tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
								{ /* No select column here: the first column takes its 16px inset. */ }
								{ sortable( 'user_id', __( 'User ID', 'wp-user-frontend' ), ' pl-4' ) }
								<th scope="col" className={ TH }>{ __( 'User Name', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Subscription ID', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Status', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Gateway', 'wp-user-frontend' ) }</th>
								<th scope="col" className={ TH }>{ __( 'Transaction ID', 'wp-user-frontend' ) }</th>
								{ sortable( 'starts_from', __( 'Starts from', 'wp-user-frontend' ) ) }
								{ sortable( 'expire', __( 'Expire date', 'wp-user-frontend' ) ) }
							</tr>
						</thead>
						<tbody className={ loading ? 'opacity-60' : '' }>
							{ items.map( ( item ) => (
								<tr key={ item.id } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
									<td className={ TD + ' whitespace-nowrap pl-4 text-sm font-medium text-gray-900' }>#{ item.user_id }</td>
									<td className={ TD }>
										{ item.edit_url
											? <a href={ item.edit_url } className="text-gray-900 no-underline hover:text-primary hover:underline">{ item.name || dash }</a>
											: <span className="text-gray-900">{ item.name || dash }</span> }
									</td>
									<td className={ TD + ' text-gray-500' }>{ item.subscription_id || dash }</td>
									<td className={ TD + ' whitespace-nowrap' }>
										{ item.status ? (
											<Badge tone={ TONE[ item.status ] || 'neutral' }>
												{ LABEL[ item.status ] || item.status }
											</Badge>
										) : dash }
									</td>
									<td className={ TD + ' whitespace-nowrap text-gray-500' }>
										<span className="inline-flex items-center gap-2">
											{ item.gateway_logo && <img src={ item.gateway_logo } alt="" className="h-4 w-5 object-contain" /> }
											{ item.gateway || '–' }
										</span>
									</td>
									<td className={ TD + ' text-gray-500' }>{ item.transaction_id || dash }</td>
									<td className={ TD + ' whitespace-nowrap text-gray-500' }>{ item.starts_from || dash }</td>
									<td className={ TD + ' whitespace-nowrap pr-4 text-gray-500' }>{ item.expire || dash }</td>
								</tr>
							) ) }
						</tbody>
					</table>
				</div>

				<Pagination
					variant="footer"
					currentPage={ page }
					total={ data.total }
					perPage={ perPage }
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
			<div className="flex items-center gap-2">
				<a
					href="#"
					onClick={ ( event ) => {
						event.preventDefault();
						goToList();
					} }
					className="inline-flex size-7 shrink-0 items-center justify-center rounded-md text-gray-500 no-underline hover:bg-gray-100 hover:text-gray-900 focus:text-gray-900"
					aria-label={ __( 'Back to subscriptions', 'wp-user-frontend' ) }
					title={ __( 'Back to subscriptions', 'wp-user-frontend' ) }
				>
					<ArrowLeft className="size-4" aria-hidden="true" />
				</a>
				<h2 className="m-0 text-lg font-semibold text-gray-900">
					{ __( 'Subscribers', 'wp-user-frontend' ) }
					{ pack ? <span className="ml-2 font-normal text-gray-500">{ pack.title }</span> : null }
				</h2>
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
						label={ __( 'Subscriber status', 'wp-user-frontend' ) }
					/>
				</div>
				{ content }
			</div>
		</div>
	);
};

export default Subscribers;
