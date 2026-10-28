/**
 * DESCRIPTION: List view of the subscriptions: one table row per pack
 * DESCRIPTION: Same data and actions as the card (useSubscriptionItem), in the forms list table look
 *
 * @since WPUF_SINCE
 */
import { __, sprintf } from '@wordpress/i18n';
import { Lock, Repeat, Star } from 'lucide-react';
import { highlightOf } from '../../labels';
import { ActionMenu, Badge, ConfirmDialog } from '@wpuf/components';
import { useSubscriptionItem } from '../../hooks/useSubscriptionItem';
import { SubscriptionBoxFooter } from '../../slots';

// Forms list table cells (FlyHR list table): uppercase 12px gray header, 56px rows.
const TH = 'px-4 font-normal';
const TD = 'px-4 py-2.5 align-middle text-[13px]';

const SubscriptionRow = ( { subscription, onEdit, onChanged } ) => {
	const {
		billingAmount,
		cancelled,
		confirmProps,
		handleEdit,
		isPasswordProtected,
		isRecurringSub,
		isTrashed,
		menuItems,
		openSubscribers,
		status,
		subscribers,
	} = useSubscriptionItem( subscription, onEdit, onChanged );

	return (
		<tr className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
			<td className={ TD + ' text-gray-900' }>
				<div className="flex items-center gap-1.5" title={ `id: ${ subscription.ID }` }>
					{ isTrashed ? (
						<span className="font-medium text-gray-900">{ subscription.post_title }</span>
					) : (
						<button
							type="button"
							onClick={ handleEdit }
							className="cursor-pointer border-0 bg-transparent p-0 text-left text-[13px] font-medium text-gray-900 hover:text-primary hover:underline focus-visible:text-primary"
						>
							{ subscription.post_title }
						</button>
					) }
					{ isPasswordProtected && (
						<Lock size={ 14 } className="text-gray-400" aria-label={ __( 'Password protected', 'wp-user-frontend' ) } />
					) }
				</div>
				{ /* Extension slot: the card footer content, under the name in this view */ }
				<SubscriptionBoxFooter.Slot fillProps={ { subscription } } />
			</td>
			<td className={ TD + ' whitespace-nowrap text-gray-500' }>{ billingAmount }</td>
			<td className={ TD + ' whitespace-nowrap' }>
				<span className="inline-flex flex-wrap items-center gap-2">
					<Badge tone={ status.tone }>{ status.label }</Badge>
					{ highlightOf( subscription ) && (
						<Badge tone="info" title={ __( 'Highlighted on the plans page', 'wp-user-frontend' ) }>
							<Star size={ 12 } aria-hidden="true" />
							{ highlightOf( subscription ) }
						</Badge>
					) }
				</span>
			</td>
			<td className={ TD + ' whitespace-nowrap text-gray-500' }>
				{ isRecurringSub ? (
					<span className="inline-flex items-center gap-1.5">
						<Repeat size={ 16 } aria-hidden="true" />
						{ __( 'Recurring', 'wp-user-frontend' ) }
					</span>
				) : (
					__( 'One time', 'wp-user-frontend' )
				) }
			</td>
			<td className={ TD + ' whitespace-nowrap' }>
				<button
					type="button"
					onClick={ openSubscribers }
					className="cursor-pointer border-0 bg-transparent p-0 text-[13px] font-medium text-gray-900 hover:text-primary hover:underline focus-visible:text-primary"
					/* translators: %s: subscription name */
					aria-label={ sprintf( __( 'View subscribers of %s', 'wp-user-frontend' ), subscription.post_title ) }
				>
					{ subscribers }
				</button>
				{ cancelled > 0 && (
					<span className="ms-2 text-xs text-gray-400">
						{ /* translators: %d: number of cancelled subscribers */ }
						{ sprintf( __( '%d cancelled', 'wp-user-frontend' ), cancelled ) }
					</span>
				) }
			</td>
			<td className="px-4 align-middle">
				<div className="flex justify-end">
					<ActionMenu
						vertical
						/* translators: %s: subscription name */
						label={ sprintf( __( 'Actions for %s', 'wp-user-frontend' ), subscription.post_title ) }
						items={ menuItems }
					/>
				</div>
				<ConfirmDialog { ...confirmProps } />
			</td>
		</tr>
	);
};

const SubscriptionTable = ( { subscriptions, onEdit, onChanged } ) => (
	<div className="overflow-x-auto">
		<table className="w-full text-left border-collapse">
			<thead className="border-0 border-b border-solid border-gray-200 bg-white">
				<tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
					<th scope="col" className={ TH }>{ __( 'Name', 'wp-user-frontend' ) }</th>
					<th scope="col" className={ TH }>{ __( 'Price', 'wp-user-frontend' ) }</th>
					<th scope="col" className={ TH }>{ __( 'Status', 'wp-user-frontend' ) }</th>
					<th scope="col" className={ TH }>{ __( 'Billing', 'wp-user-frontend' ) }</th>
					<th scope="col" className={ TH }>{ __( 'Total Subscribers', 'wp-user-frontend' ) }</th>
					<th scope="col" className="w-20 px-4">
						<span className="sr-only">{ __( 'Actions', 'wp-user-frontend' ) }</span>
					</th>
				</tr>
			</thead>
			<tbody>
				{ subscriptions.map( ( subscription ) => (
					<SubscriptionRow
						key={ subscription.ID }
						subscription={ subscription }
						onEdit={ onEdit }
						onChanged={ onChanged }
					/>
				) ) }
			</tbody>
		</table>
	</div>
);

export default SubscriptionTable;
