/**
 * DESCRIPTION: Displays a single subscription card in the card view
 * DESCRIPTION: The frontend plan card's details (summary, price, features) plus the admin parts
 * DESCRIPTION: (status, recurring, subscribers, actions menu)
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { Check, Lock, Repeat, Tag } from 'lucide-react';
import { ActionMenu, Badge, ConfirmDialog } from '@wpuf/components';
import { useSubscriptionItem } from '../../hooks/useSubscriptionItem';
import { SubscriptionBoxFooter } from '../../slots';

// Feature lines shown before "See N more features" (as the frontend plan card).
const FEATURES_SHOWN = 5;

// The pack summary as plain text (it is stored as post content and may hold markup).
const plainText = ( html ) => decodeEntities( String( html || '' ).replace( /<[^>]*>/g, ' ' ) ).replace( /\s+/g, ' ' ).trim();

const SubscriptionBox = ({ subscription, onEdit, onChanged }) => {
	const {
		billingAmount,
		cancelled,
		confirmProps,
		links,
		handleEdit,
		isPasswordProtected,
		isRecurringSub,
		isTrashed,
		menuItems,
		openSubscribers,
		status,
		subscribers,
	} = useSubscriptionItem(subscription, onEdit, onChanged);

	const [ showAll, setShowAll ] = useState( false );
	const summary = plainText( subscription.post_content );
	const features = Array.isArray( subscription.card_features ) ? subscription.card_features : [];
	const hidden = Math.max( 0, features.length - FEATURES_SHOWN );
	const shown = showAll ? features : features.slice( 0, FEATURES_SHOWN );

	return (
		<>
			<div className="flex flex-col text-sm bg-white border border-gray-200 rounded-[10px] shadow-sm relative">
				<div
					onClick={!isTrashed ? handleEdit : undefined}
					className={`border-b border-gray-200 bg-white px-5 pt-5 pb-4 rounded-t-[10px] ${!isTrashed ? 'cursor-pointer' : ''}`}
				>
					<div className="pr-8">
						<div className="flex items-center text-base text-gray-900 m-0 font-semibold" title={`id: ${subscription.ID}`}>
							{/* develop: `{{ title }} &nbsp;` (a space and a no-break space) */}
							{subscription.post_title}{' '}&nbsp;
							{isPasswordProtected && (
								<Lock size={ 16 } className="text-gray-400" aria-label={ __( 'Password protected', 'wp-user-frontend' ) } />
							)}
						</div>
						{ summary && (
							<span className="mt-1.5 line-clamp-2 block text-[13px] leading-5 text-gray-500">{ summary }</span>
						) }
						<p className="text-gray-900 text-2xl font-bold leading-8 mt-3 mb-0">{billingAmount}</p>
					</div>
				</div>

				{/* Quick menu (develop: horizontal dots, top right) */}
				<div className="absolute top-3 right-3">
					<ActionMenu items={menuItems} label={__('Actions', 'wp-user-frontend')} />
				</div>

				{/* Status Pill and Recurring Icon */}
				<div className="flex px-5 pt-4 justify-between items-center gap-2">
					<span className="flex flex-wrap items-center gap-2">
						<Badge tone={ status.tone }>{ status.label }</Badge>
						{ /* Published but left out of the plans page shortcode (include / exclude). */ }
						{ 'publish' === subscription.post_status && links.plansUrl && ! links.onPlansPage && (
							<span className="text-xs text-amber-700" title={ __( 'The [wpuf_sub_pack] shortcode on the plans page includes or excludes packs by ID.', 'wp-user-frontend' ) }>
								{ __( 'Not on the plans page', 'wp-user-frontend' ) }
							</span>
						) }
						<span className="text-xs text-gray-400">
							{ /* translators: %d: pack ID */ }
							{ sprintf( __( 'ID %d', 'wp-user-frontend' ), subscription.ID ) }
						</span>
					</span>
					{isRecurringSub && (
						<Repeat size={ 20 } className="text-gray-500" aria-label={ __( 'Recurring', 'wp-user-frontend' ) } />
					)}
				</div>

				{/* The plan's features, as the frontend plan card lists them */}
				<div className="flex-1 px-5 pt-4 pb-2">
					{ features.length > 0 && (
						<ul className="m-0 flex list-none flex-col gap-2 p-0">
							{ shown.map( ( feature, index ) => (
								<li key={ index } className="m-0 flex items-start gap-2.5 text-[13px] leading-5 text-gray-700">
									<Check size={ 16 } className="mt-0.5 shrink-0 text-primary" aria-hidden="true" />
									<span>{ feature }</span>
								</li>
							) ) }
						</ul>
					) }
					{ hidden > 0 && (
						<button
							type="button"
							onClick={ () => setShowAll( ! showAll ) }
							aria-expanded={ showAll }
							className="mt-2 cursor-pointer border-0 bg-transparent p-0 text-[13px] font-semibold text-gray-900 hover:text-primary focus-visible:text-primary"
						>
							{ showAll
								? __( 'Show fewer features', 'wp-user-frontend' )
								/* translators: %d: number of hidden features */
								: sprintf( _n( 'See %d more feature', 'See %d more features', hidden, 'wp-user-frontend' ), hidden ) }
						</button>
					) }
				</div>

				{/* Coupons for this pack (Pro): codes and the site-wide ones */}
				{ subscription.card_coupons && ( subscription.card_coupons.only.length > 0 || subscription.card_coupons.all > 0 ) && (
					<div className="mx-5 flex flex-wrap items-center gap-1.5 border-0 border-t border-solid border-gray-100 py-3 text-xs text-gray-500">
						<Tag size={ 14 } className="shrink-0 text-gray-400" aria-hidden="true" />
						{ subscription.card_coupons.only.map( ( coupon ) => (
							<a
								key={ coupon.id }
								href={ `admin.php?page=wp-user-frontend#/coupons?action=edit&id=${ coupon.id }` }
								className="rounded-full border border-solid border-gray-200 bg-gray-50 px-2 py-0.5 font-medium text-gray-700 no-underline hover:border-primary hover:text-primary"
							>
								{ coupon.code }
							</a>
						) ) }
						{ subscription.card_coupons.all > 0 && (
							<span>
								{ sprintf(
									/* translators: %d: number of coupons for every pack */
									_n( '+ %d coupon for all packs', '+ %d coupons for all packs', subscription.card_coupons.all, 'wp-user-frontend' ),
									subscription.card_coupons.all
								) }
							</span>
						) }
					</div>
				) }

				{/* Subscribers Count */}
				<div className="flex mx-5 py-4 border-0 border-t border-solid border-gray-100 justify-between items-center">
					<p className="text-gray-500 text-sm m-0">{__('Total Subscribers', 'wp-user-frontend')}</p>
					<span className="flex items-center gap-2">
					{ cancelled > 0 && (
						<span className="text-xs text-gray-400">
							{ /* translators: %d: number of cancelled subscribers */ }
							{ sprintf( __( '%d cancelled', 'wp-user-frontend' ), cancelled ) }
						</span>
					) }
					<a
						href="#"
						className="font-medium text-gray-900 no-underline hover:text-primary hover:underline"
						aria-label={__('View subscribers', 'wp-user-frontend')}
						onClick={(event) => {
							event.preventDefault();
							openSubscribers();
						}}
					>
						{subscribers}
					</a>
					</span>
				</div>

				{/* Extension slot: Pro and third-party plugins can add content to card footer */}
				<SubscriptionBoxFooter.Slot
					fillProps={ { subscription } }
				/>
			</div>

			{/* develop's Popup: trash / permanent delete confirmation */}
			<ConfirmDialog {...confirmProps} />
		</>
	);
};

export default SubscriptionBox;
