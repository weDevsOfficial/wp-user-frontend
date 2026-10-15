/**
 * Plan summary (was develop's edit-page InfoCard.vue: plan name, payment,
 * recurring flag and subscribers). The payment is built as text (currency
 * symbols arrive as HTML entities), not injected as HTML.
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { __, _n, sprintf } from '@wordpress/i18n';
import { RefreshCw, Users } from 'lucide-react';
import { fetchSubscribers } from '../../api/subscription';
import { subscriptionsData } from '../../boot';

const STATUS = {
	publish: { label: __( 'Published', 'wp-user-frontend' ), className: 'bg-primary/10 text-primary' },
	draft: { label: __( 'Draft', 'wp-user-frontend' ), className: 'bg-gray-100 text-gray-600' },
	pending: { label: __( 'Pending', 'wp-user-frontend' ), className: 'bg-amber-50 text-amber-700' },
	future: { label: __( 'Future', 'wp-user-frontend' ), className: 'bg-sky-50 text-sky-700' },
	private: { label: __( 'Private', 'wp-user-frontend' ), className: 'bg-orange-50 text-orange-700' },
	trash: { label: __( 'Trash', 'wp-user-frontend' ), className: 'bg-red-50 text-red-700' },
};

// Any other status reads as its own word (capitalised, as the list's pill), never "Draft".
const statusOf = ( postStatus ) => STATUS[ postStatus ] || {
	label: postStatus ? postStatus.charAt( 0 ).toUpperCase() + postStatus.slice( 1 ) : __( 'Draft', 'wp-user-frontend' ),
	className: 'bg-gray-100 text-gray-600',
};

/**
 * Plan summary card above the form (new and edit): the plan name and status
 * on the left, the price and period on the right, recurring and subscribers
 * underneath. Reads the item being edited, so it follows the fields live.
 *
 * @param {Object} props
 * @param {Object} props.subscription Subscription loaded for the form.
 * @param {string} [props.mode]       add-new|edit.
 */
const InfoCard = ( { subscription, mode = 'edit' } ) => {
	const live = useSelect( ( select ) => select( 'wpuf/subscriptions' ).getItem(), [] ) || subscription;
	const [ subscribers, setSubscribers ] = useState( subscription.subscribers ?? 0 );
	const meta = live.meta_value || {};
	// The form writes the `_`-prefixed meta keys (field db_key); loaded items also carry the plain ones.
	const pick = ( key ) => ( undefined !== meta[ '_' + key ] ? meta[ '_' + key ] : meta[ key ] );
	const recurringValue = pick( 'recurring_pay' );
	const isRecurring = 'on' === recurringValue || 'yes' === recurringValue || true === recurringValue;
	const symbol = decodeEntities( subscriptionsData().currencySymbol || '$' );
	const isEdit = 'edit' === mode && subscription.ID;

	useEffect( () => {
		if ( ! isEdit ) {
			return;
		}

		fetchSubscribers( subscription.ID )
			.then( ( response ) => setSubscribers( response.subscribers ) )
			.catch( () => {} );
	}, [ isEdit, subscription.ID ] );

	const amount = '' === pick( 'billing_amount' ) || undefined === pick( 'billing_amount' ) ? 0 : pick( 'billing_amount' );
	const isFree = ! isRecurring && 0 === parseFloat( amount );
	const cycle = parseInt( meta._billing_cycle_number, 10 );
	const period = ! pick( 'cycle_period' ) ? __( 'day', 'wp-user-frontend' ) : pick( 'cycle_period' );
	/* translators: 1: number of periods, 2: period (day, week, month, year) */
	const every = 0 === cycle || 1 === cycle || isNaN( cycle ) ? '/' + period : sprintf( __( '/ %1$s %2$ss', 'wp-user-frontend' ), cycle, period );
	const status = isEdit ? statusOf( live.post_status ) : { label: __( 'New', 'wp-user-frontend' ), className: 'bg-gray-100 text-gray-600' };
	const title = live.post_title || __( 'Untitled plan', 'wp-user-frontend' );

	return (
		<div className="mt-4 rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm" data-plan-summary="">
			<div className="flex flex-wrap items-start justify-between gap-4 px-6 pt-5 pb-4">
				<div className="min-w-0">
					<div className="flex flex-wrap items-center gap-2.5">
						<h4 className="m-0 truncate text-xl font-semibold leading-7 text-gray-900" title={ isEdit ? 'id: ' + subscription.ID : undefined }>{ title }</h4>
						<span className={ `rounded-full px-2.5 py-0.5 text-xs font-medium ${ status.className }` }>{ status.label }</span>
					</div>
					<p className="m-0 mt-1 text-sm text-gray-500">
						{ isRecurring ? __( 'Recurring payment', 'wp-user-frontend' ) : __( 'One-time payment', 'wp-user-frontend' ) }
					</p>
				</div>
				<div className="text-end">
					<span className="text-2xl font-semibold leading-8 tracking-tight text-gray-900">
						{ isFree ? __( 'Free', 'wp-user-frontend' ) : symbol + amount }
					</span>
					{ isRecurring && ! isFree && <span className="ms-1 text-base text-gray-400">{ every }</span> }
				</div>
			</div>
			<div className="flex flex-wrap items-center justify-between gap-3 border-0 border-t border-solid border-gray-200 px-6 py-3 text-sm text-gray-600">
				<span className="inline-flex items-center gap-2">
					<RefreshCw size={ 16 } strokeWidth={ 2 } className={ isRecurring ? 'text-primary' : 'text-gray-400' } aria-hidden="true" />
					{ isRecurring ? __( 'Renews automatically', 'wp-user-frontend' ) : __( 'Does not renew', 'wp-user-frontend' ) }
				</span>
				{ isEdit && (
					<span className="inline-flex items-center gap-2">
						<Users size={ 16 } strokeWidth={ 2 } className="text-gray-400" aria-hidden="true" />
						{ /* translators: %d: number of subscribers */ }
						{ sprintf( _n( '%d subscriber', '%d subscribers', subscribers, 'wp-user-frontend' ), subscribers ) }
					</span>
				) }
			</div>
		</div>
	);
};

export default InfoCard;
