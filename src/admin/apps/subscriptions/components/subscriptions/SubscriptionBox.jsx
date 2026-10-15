/**
 * DESCRIPTION: Displays a single subscription card in the list view
 * DESCRIPTION: Shows subscription details with actions menu
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback, useMemo } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { pencil, trash, check, closeSmall } from '@wordpress/icons';
import { decodeEntities } from '@wordpress/html-entities';
import { Lock, Repeat } from 'lucide-react';
import { ActionMenu, Badge, ConfirmDialog, notify } from '@wpuf/components';
import { useSubscriptionActions, useSubscriptionNavigation } from '../../hooks';
import { SubscriptionBoxFooter } from '../../slots';
import { fetchSubscribers } from '../../api/subscription';
import { statusOf } from '../../labels';

const SubscriptionBox = ({ subscription, onEdit, onChanged }) => {
	// 'trash' | 'delete' while develop's confirm popup is open.
	const [confirming, setConfirming] = useState(null);
	const [busy, setBusy] = useState(false);
	const [subscribers, setSubscribers] = useState(0);

	// These selectors need state argument, so use useSelect directly
	const { getReadableBillingAmount, isRecurring } = useSelect((select) => select('wpuf/subscriptions'), []);
	// Store actions using custom hook
	const { setItem, updateItem, deleteItem } = useSubscriptionActions();
	// Quick edit store actions
	const { setQuickEditStatus } = useDispatch('wpuf/subscriptions-quick-edit');

	const isPasswordProtected = useMemo(() => {
		return subscription.post_password !== '';
	}, [subscription.post_password]);

	const status = statusOf( subscription.post_status );

	// Plain text like develop; currency symbols arrive as HTML entities (&#36;).
	const billingAmount = useMemo(() => {
		return decodeEntities(getReadableBillingAmount(subscription));
	}, [subscription, getReadableBillingAmount]);

	const isRecurringSub = useMemo(() => {
		return isRecurring(subscription);
	}, [subscription, isRecurring]);

	// The pack's Subscribers view in this app (replaces the classic list table).
	const { goToSubscribers } = useSubscriptionNavigation();

	// Fetch subscribers count
	useEffect(() => {
		fetchSubscribers(subscription.ID)
			.then((response) => {
				setSubscribers(response.subscribers);
			})
			.catch(() => {
				// Silently fail on error
			});
	}, [subscription.ID]);

	const handleEdit = useCallback(() => {
		setItem(subscription);
		// Use the callback prop if provided, otherwise fall back to URL navigation
		if (onEdit) {
			onEdit(subscription.ID);
		}
	}, [subscription, setItem, onEdit]);

	const handleQuickEdit = useCallback(() => {
		setItem(subscription);
		setQuickEditStatus(true);
	}, [subscription, setItem, setQuickEditStatus]);

	// develop's processPromiseResult: toast with the server message, then the
	// list and counts reload (no page reload); failures keep the popup open.
	const finish = useCallback((result) => {
		setBusy(false);
		if (result && result.success) {
			notify(result.message, 'success');
			setConfirming(null);
			setItem(null);
			onChanged?.();
			return;
		}
		notify((result && result.message) || __('Something went wrong', 'wp-user-frontend'), 'danger');
	}, [setItem, onChanged]);

	const changeStatus = useCallback((newStatus) => {
		setItem({
			...subscription,
			edit_single_row: true,
			edit_row_name: 'post_status',
			edit_row_value: newStatus,
		});
		setBusy(true);
		updateItem().then(finish, () => finish(null));
	}, [subscription, setItem, updateItem, finish]);

	const handleToggleStatus = useCallback(() => {
		changeStatus(subscription.post_status === 'draft' ? 'publish' : 'draft');
	}, [subscription.post_status, changeStatus]);

	const handleRestore = useCallback(() => {
		changeStatus('draft');
	}, [changeStatus]);

	const handleConfirm = useCallback(() => {
		if ('trash' === confirming) {
			changeStatus('trash');
			return;
		}
		setBusy(true);
		deleteItem(subscription.ID).then(finish, () => finish(null));
	}, [confirming, changeStatus, deleteItem, subscription.ID, finish]);

	const getMenuItems = () => {
		const items = [];
		if (subscription.post_status !== 'trash') {
			items.push({
				action: 'edit',
				icon: pencil,
				title: __('Edit', 'wp-user-frontend'),
				onClick: handleEdit,
			});
			items.push({
				action: 'quick-edit',
				icon: pencil,
				title: __('Quick Edit', 'wp-user-frontend'),
				onClick: handleQuickEdit,
			});
			items.push({
				action: subscription.post_status === 'publish' ? 'draft' : 'publish',
				icon: subscription.post_status === 'publish' ? closeSmall : check,
				title: subscription.post_status === 'publish' ? __('Draft', 'wp-user-frontend') : __('Publish', 'wp-user-frontend'),
				onClick: handleToggleStatus,
			});
			items.push({
				action: 'trash',
				icon: trash,
				title: __('Trash', 'wp-user-frontend'),
				onClick: () => setConfirming('trash'),
			});
		} else {
			items.push({
				action: 'restore',
				icon: check,
				title: __('Restore', 'wp-user-frontend'),
				onClick: handleRestore,
			});
			items.push({
				action: 'delete',
				icon: trash,
				title: __('Delete Permanently', 'wp-user-frontend'),
				onClick: () => setConfirming('delete'),
			});
		}
		return applyFilters( 'wpuf.subscription.boxMenuItems', items, subscription );
	};

	// The filter keeps its item shape ({ icon, title, onClick }); built-in items
	// carry an `action`, which picks the menu's icon.
	const menuItems = getMenuItems().map((item, index) => ({
		key: `${index}-${item.title}`,
		action: item.action,
		label: item.title,
		onClick: item.onClick,
		disabled: busy,
	}));

	return (
		<>
			<div className="text-sm justify-between bg-white border border-gray-200 rounded-[10px] shadow-sm relative">
				<div
					onClick={subscription.post_status !== 'trash' ? handleEdit : undefined}
					className={`flex justify-between border-b border-gray-200 bg-white px-5 py-4 rounded-t-[10px] ${subscription.post_status !== 'trash' ? 'cursor-pointer' : ''}`}
				>
					<div>
						<div className="flex items-center text-gray-900 m-0 font-semibold" title={`id: ${subscription.ID}`}>
							{/* develop: `{{ title }} &nbsp;` (a space and a no-break space) */}
							{subscription.post_title}{' '}&nbsp;
							{isPasswordProtected && (
								<Lock size={ 16 } className="text-gray-400" aria-label={ __( 'Password protected', 'wp-user-frontend' ) } />
							)}
						</div>
						<p className="text-gray-500 text-[13px] mt-1 mb-0">{billingAmount}</p>
					</div>
				</div>

				{/* Quick menu (develop: horizontal dots, top right) */}
				<div className="absolute top-3 right-3">
					<ActionMenu items={menuItems} label={__('Actions', 'wp-user-frontend')} />
				</div>

			{/* Status Pill and Recurring Icon */}
				<div className="flex px-5 py-3 justify-between items-center">
					<Badge tone={ status.tone }>{ status.label }</Badge>
					{isRecurringSub && (
						<Repeat size={ 20 } className="text-gray-500" aria-label={ __( 'Recurring', 'wp-user-frontend' ) } />
					)}
				</div>

				{/* Subscribers Count */}
				<div className="flex px-6 pb-6 justify-between items-center">
					<p className="text-gray-500 text-sm m-0">{__('Total Subscribers', 'wp-user-frontend')}</p>
					<a
						href="#"
						className="text-gray-500"
						aria-label={__('View subscribers', 'wp-user-frontend')}
						onClick={(event) => {
							event.preventDefault();
							goToSubscribers(subscription.ID);
						}}
					>
						{subscribers}
					</a>
				</div>

				{/* Extension slot: Pro and third-party plugins can add content to card footer */}
				<SubscriptionBoxFooter.Slot
					fillProps={ { subscription } }
				/>
			</div>

			{/* develop's Popup: trash / permanent delete confirmation */}
			<ConfirmDialog
				variant="panel"
				open={null !== confirming}
				tone="danger"
				busy={busy}
				title={'delete' === confirming ? __('Delete Subscription', 'wp-user-frontend') : __('Trash Subscription', 'wp-user-frontend')}
				message={'delete' === confirming
					? __('Are you sure you want to delete this subscription? This action cannot be undone.', 'wp-user-frontend')
					: __('This subscription will be moved to the trash. Are you sure?', 'wp-user-frontend')}
				confirmText={'delete' === confirming ? __('Delete', 'wp-user-frontend') : __('Trash', 'wp-user-frontend')}
				cancelText={__('Cancel', 'wp-user-frontend')}
				onConfirm={handleConfirm}
				onCancel={() => setConfirming(null)}
			/>
		</>
	);
};

export default SubscriptionBox;
