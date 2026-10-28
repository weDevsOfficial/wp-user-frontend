/**
 * DESCRIPTION: One subscription's data and actions, shared by the card and the list row
 * DESCRIPTION: Edit, quick edit, publish / draft, trash, restore, delete, subscribers count
 *
 * @since WPUF_SINCE
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { Link2, Receipt } from 'lucide-react';
import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { pencil, trash, check, closeSmall } from '@wordpress/icons';
import { decodeEntities } from '@wordpress/html-entities';
import { notify } from '@wpuf/components';
import { useSubscriptionActions } from './useSubscriptionActions';
import { useSubscriptionNavigation } from './useSubscriptionNavigation';
import { fetchSubscribers } from '../api/subscription';
import { statusOf } from '../labels';
import { subscriptionsData } from '../boot';
import { writeText } from '../../forms-list/hooks/useClipboard';
import { openRoute } from '../../../app/client';

/**
 * Where a pack is listed and bought (boot data of the subscriptions screen).
 *
 * @param {Object} subscription Pack.
 *
 * @return {Object} { shortcode, buyLink, plansUrl, onPlansPage }
 */
export const packLinks = ( subscription ) => {
	const boot = subscriptionsData();
	const plans = boot.plansPage || {};
	const id = Number( subscription.ID );
	const include = plans.include || [];
	const exclude = plans.exclude || [];

	return {
		// The plans shortcode limited to this pack (`include`, Pack_Display).
		shortcode: `[wpuf_sub_pack include="${ id }"]`,
		// The plan card's link for a logged-in buyer (Pack_Display: action=wpuf_pay).
		buyLink: boot.paymentPageUrl ? addQueryArgs( boot.paymentPageUrl, { action: 'wpuf_pay', type: 'pack', pack_id: id } ) : '',
		plansUrl: plans.url || '',
		plansEdit: plans.edit || '',
		onPlansPage: !! plans.url && ( ! include.length || include.includes( id ) ) && ! exclude.includes( id ),
	};
};

/**
 * @param {Object}   subscription The pack.
 * @param {Function} [onEdit]     ( id ) => void, opens the edit form.
 * @param {Function} [onChanged]  Called after a status change or delete.
 *
 * @return {Object} Values and handlers for one pack view.
 */
export function useSubscriptionItem( subscription, onEdit, onChanged ) {
	// 'trash' | 'delete' while develop's confirm popup is open.
	const [ confirming, setConfirming ] = useState( null );
	const [ busy, setBusy ] = useState( false );
	// The list sends the counts (no request per card); fetched otherwise.
	const listed = subscription.subscriber_counts || null;
	const [ subscribers, setSubscribers ] = useState( listed ? listed.total : 0 );
	const cancelled = listed ? listed.cancelled : 0;

	// These selectors need state argument, so use useSelect directly
	const { getReadableBillingAmount, isRecurring } = useSelect( ( select ) => select( 'wpuf/subscriptions' ), [] );
	// Store actions using custom hook
	const { setItem, updateItem, deleteItem } = useSubscriptionActions();
	// Quick edit store actions
	const { setQuickEditStatus } = useDispatch( 'wpuf/subscriptions-quick-edit' );
	// The pack's Subscribers view in this app (replaces the classic list table).
	const { goToSubscribers } = useSubscriptionNavigation();

	const isPasswordProtected = subscription.post_password !== '';
	const isTrashed = subscription.post_status === 'trash';
	const status = statusOf( subscription.post_status );

	// Plain text like develop; currency symbols arrive as HTML entities (&#36;).
	const billingAmount = useMemo( () => {
		return decodeEntities( getReadableBillingAmount( subscription ) );
	}, [ subscription, getReadableBillingAmount ] );

	const isRecurringSub = useMemo( () => {
		return isRecurring( subscription );
	}, [ subscription, isRecurring ] );

	// Fetch subscribers count
	useEffect( () => {
		if ( listed ) {
			setSubscribers( listed.total );
			return;
		}

		fetchSubscribers( subscription.ID )
			.then( ( response ) => {
				setSubscribers( response.subscribers );
			} )
			.catch( () => {
				// Silently fail on error
			} );
	}, [ subscription.ID, listed ] );

	const handleEdit = useCallback( () => {
		setItem( subscription );
		// Use the callback prop if provided, otherwise fall back to URL navigation
		if ( onEdit ) {
			onEdit( subscription.ID );
		}
	}, [ subscription, setItem, onEdit ] );

	const handleQuickEdit = useCallback( () => {
		setItem( subscription );
		setQuickEditStatus( true );
	}, [ subscription, setItem, setQuickEditStatus ] );

	const openSubscribers = useCallback( () => {
		goToSubscribers( subscription.ID );
	}, [ goToSubscribers, subscription.ID ] );

	// develop's processPromiseResult: toast with the server message, then the
	// list and counts reload (no page reload); failures keep the popup open.
	const finish = useCallback( ( result ) => {
		setBusy( false );
		if ( result && result.success ) {
			notify( result.message, 'success' );
			setConfirming( null );
			setItem( null );
			onChanged?.();
			return;
		}
		notify( ( result && result.message ) || __( 'Something went wrong', 'wp-user-frontend' ), 'danger' );
	}, [ setItem, onChanged ] );

	const changeStatus = useCallback( ( newStatus ) => {
		setItem( {
			...subscription,
			edit_single_row: true,
			edit_row_name: 'post_status',
			edit_row_value: newStatus,
		} );
		setBusy( true );
		updateItem().then( finish, () => finish( null ) );
	}, [ subscription, setItem, updateItem, finish ] );

	const handleToggleStatus = useCallback( () => {
		changeStatus( subscription.post_status === 'draft' ? 'publish' : 'draft' );
	}, [ subscription.post_status, changeStatus ] );

	const handleRestore = useCallback( () => {
		changeStatus( 'draft' );
	}, [ changeStatus ] );

	const handleConfirm = useCallback( () => {
		if ( 'trash' === confirming ) {
			changeStatus( 'trash' );
			return;
		}
		setBusy( true );
		deleteItem( subscription.ID ).then( finish, () => finish( null ) );
	}, [ confirming, changeStatus, deleteItem, subscription.ID, finish ] );

	const links = packLinks( subscription );

	const copy = ( text, done ) => {
		writeText( text ).then(
			() => notify( done, 'success' ),
			() => notify( __( 'Could not copy. Please copy it manually.', 'wp-user-frontend' ), 'danger' )
		);
	};

	const getMenuItems = () => {
		const items = [];
		if ( ! isTrashed ) {
			items.push( {
				action: 'edit',
				icon: pencil,
				title: __( 'Edit', 'wp-user-frontend' ),
				onClick: handleEdit,
			} );
			items.push( {
				action: 'quick-edit',
				icon: pencil,
				title: __( 'Quick Edit', 'wp-user-frontend' ),
				onClick: handleQuickEdit,
			} );
			items.push( {
				action: subscription.post_status === 'publish' ? 'draft' : 'publish',
				icon: subscription.post_status === 'publish' ? closeSmall : check,
				title: subscription.post_status === 'publish' ? __( 'Draft', 'wp-user-frontend' ) : __( 'Publish', 'wp-user-frontend' ),
				onClick: handleToggleStatus,
			} );
			// Where the pack is shown and bought (published packs only).
			if ( 'publish' === subscription.post_status && links.plansUrl ) {
				items.push( {
					action: 'view',
					title: __( 'View plans page', 'wp-user-frontend' ),
					onClick: () => window.open( links.plansUrl, '_blank', 'noopener' ),
				} );
			}
			items.push( {
				action: 'copy',
				title: __( 'Copy shortcode', 'wp-user-frontend' ),
				onClick: () => copy( links.shortcode, __( 'Shortcode copied: it lists only this pack.', 'wp-user-frontend' ) ),
			} );
			if ( 'publish' === subscription.post_status && links.buyLink ) {
				items.push( {
					action: 'copy-link',
					menuIcon: <Link2 size={ 16 } strokeWidth={ 2 } className="shrink-0" aria-hidden="true" />,
					title: __( 'Copy buy link', 'wp-user-frontend' ),
					onClick: () => copy( links.buyLink, __( 'Buy link copied.', 'wp-user-frontend' ) ),
				} );
			}
			items.push( {
				action: 'payments',
				menuIcon: <Receipt size={ 16 } strokeWidth={ 2 } className="shrink-0" aria-hidden="true" />,
				title: __( 'View payments', 'wp-user-frontend' ),
				onClick: () => openRoute( `/transactions?pack_id=${ subscription.ID }`, `admin.php?page=wp-user-frontend#/transactions?pack_id=${ subscription.ID }` ),
			} );
			items.push( {
				action: 'trash',
				icon: trash,
				title: __( 'Trash', 'wp-user-frontend' ),
				onClick: () => setConfirming( 'trash' ),
			} );
		} else {
			items.push( {
				action: 'restore',
				icon: check,
				title: __( 'Restore', 'wp-user-frontend' ),
				onClick: handleRestore,
			} );
			items.push( {
				action: 'delete',
				icon: trash,
				title: __( 'Delete Permanently', 'wp-user-frontend' ),
				onClick: () => setConfirming( 'delete' ),
			} );
		}
		return applyFilters( 'wpuf.subscription.boxMenuItems', items, subscription );
	};

	// The filter keeps its item shape ({ icon, title, onClick }); built-in items
	// carry an `action`, which is the key and picks the menu's icon (as the
	// forms list menu: pencil, zap, send, trash...). Trash / delete in red.
	const menuItems = getMenuItems().map( ( item, index ) => ( {
		key: item.action || `${ index }-${ item.title }`,
		action: item.action,
		label: item.title,
		onClick: item.onClick,
		destructive: 'trash' === item.action || 'delete' === item.action,
		// Actions the shared menu has no icon for bring their own (lucide).
		icon: item.menuIcon,
		disabled: busy,
	} ) );

	// develop's Popup: trash / permanent delete confirmation (ConfirmDialog props).
	const confirmProps = {
		variant: 'panel',
		open: null !== confirming,
		tone: 'danger',
		busy,
		title: 'delete' === confirming ? __( 'Delete Subscription', 'wp-user-frontend' ) : __( 'Trash Subscription', 'wp-user-frontend' ),
		message: ( 'delete' === confirming
			? __( 'Are you sure you want to delete this subscription? This action cannot be undone.', 'wp-user-frontend' )
			: __( 'This subscription will be moved to the trash. Are you sure?', 'wp-user-frontend' ) )
			+ ( subscribers > 0
				? ' ' + sprintf(
					/* translators: %d: number of users on the pack */
					_n( '%d user is on this pack. Check the subscribers first.', '%d users are on this pack. Check the subscribers first.', subscribers, 'wp-user-frontend' ),
					subscribers
				)
				: '' ),
		confirmText: 'delete' === confirming ? __( 'Delete', 'wp-user-frontend' ) : __( 'Trash', 'wp-user-frontend' ),
		cancelText: __( 'Cancel', 'wp-user-frontend' ),
		onConfirm: handleConfirm,
		onCancel: () => setConfirming( null ),
	};

	return {
		billingAmount,
		cancelled,
		links,
		confirmProps,
		handleEdit,
		isPasswordProtected,
		isRecurringSub,
		isTrashed,
		menuItems,
		openSubscribers,
		status,
		subscribers,
	};
}

export default useSubscriptionItem;
