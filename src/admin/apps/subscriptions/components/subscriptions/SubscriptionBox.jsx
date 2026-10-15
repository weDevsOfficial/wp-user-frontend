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
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path fillRule="evenodd" clipRule="evenodd" d="M5.99999 10.8V8.4C5.99999 5.08629 8.68628 2.4 12 2.4C15.3137 2.4 18 5.08629 18 8.4V10.8C19.3255 10.8 20.4 11.8745 20.4 13.2V19.2C20.4 20.5255 19.3255 21.6 18 21.6H5.99999C4.67451 21.6 3.59999 20.5255 3.59999 19.2V13.2C3.59999 11.8745 4.67451 10.8 5.99999 10.8ZM15.6 8.4V10.8H8.39999V8.4C8.39999 6.41178 10.0118 4.8 12 4.8C13.9882 4.8 15.6 6.41178 15.6 8.4Z" fill="#a0aec0" />
								</svg>
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
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M20 19C20 19.5523 20.4477 20 21 20C21.5523 20 22 19.5523 22 19L20 19ZM21 15.375L22 15.375L22 14.375H21V15.375ZM12 21L12 22L12 21ZM4.06195 13.0013C3.99361 12.4532 3.49394 12.0644 2.9459 12.1327C2.39786 12.201 2.00898 12.7007 2.07732 13.2488L4.06195 13.0013ZM20.3458 15.375L20.3458 14.375L20.3458 14.375L20.3458 15.375ZM17.375 14.375C16.8227 14.375 16.375 14.8227 16.375 15.375C16.375 15.9273 16.8227 16.375 17.375 16.375L17.375 14.375ZM4.00001 5.00002C4.00001 4.44773 3.55229 4.00002 3.00001 4.00002C2.44772 4.00002 2.00001 4.44773 2.00001 5.00002L4.00001 5.00002ZM3.00001 8.62502L2.00001 8.62502L2.00001 9.62502H3.00001V8.62502ZM3.65421 8.62502L3.65421 9.62502L3.65421 9.62502L3.65421 8.62502ZM12 3.00002L12 2.00002L12 3.00002ZM6.62501 9.62502C7.17729 9.62502 7.62501 9.1773 7.62501 8.62502C7.62501 8.07273 7.17729 7.62502 6.62501 7.62502L6.62501 9.62502ZM19.9381 10.9988C20.0064 11.5468 20.5061 11.9357 21.0541 11.8673C21.6022 11.799 21.991 11.2993 21.9227 10.7513L19.9381 10.9988ZM12.8552 9.58595C13.1788 10.0335 13.804 10.134 14.2515 9.81034C14.699 9.48673 14.7995 8.86159 14.4759 8.41404L12.8552 9.58595ZM12.5 7C12.5 6.44771 12.0523 6 11.5 6C10.9477 6 10.5 6.44771 10.5 7H12.5ZM10.5 17C10.5 17.5523 10.9477 18 11.5 18C12.0523 18 12.5 17.5523 12.5 17L10.5 17ZM10.1448 14.414C9.82121 13.9665 9.19606 13.866 8.74852 14.1896C8.30098 14.5133 8.20051 15.1384 8.52412 15.5859L10.1448 14.414ZM22 19L22 15.375L20 15.375L20 19L22 19ZM12 20C7.92115 20 4.55392 16.9466 4.06195 13.0013L2.07732 13.2488C2.69257 18.1827 6.89973 22 12 22L12 20ZM19.4189 14.9998C18.2313 17.9335 15.3558 20 12 20L12 22C16.1983 22 19.79 19.4132 21.2727 15.7502L19.4189 14.9998ZM21 14.375H20.3458V16.375H21V14.375ZM20.3458 14.375L17.375 14.375L17.375 16.375L20.3458 16.375L20.3458 14.375ZM2.00001 5.00002L2.00001 8.62502L4.00001 8.62502L4.00001 5.00002L2.00001 5.00002ZM4.58115 9.00023C5.76867 6.06656 8.6442 4.00002 12 4.00002L12 2.00002C7.80171 2.00002 4.21 4.58686 2.72728 8.2498L4.58115 9.00023ZM3.00001 9.62502H3.65421V7.62502H3.00001V9.62502ZM3.65421 9.62502L6.62501 9.62502L6.62501 7.62502L3.65421 7.62502L3.65421 9.62502ZM12 4.00002C16.0789 4.00001 19.4461 7.05347 19.9381 10.9988L21.9227 10.7513C21.3074 5.81736 17.1003 2.00001 12 2.00002L12 4.00002ZM11.5 11C10.4518 11 10 10.3556 10 10H8C8 11.8535 9.78676 13 11.5 13V11ZM10 10C10 9.64441 10.4518 9 11.5 9V7C9.78676 7 8 8.14644 8 10H10ZM11.5 9C12.1534 9 12.6379 9.28548 12.8552 9.58595L14.4759 8.41404C13.8286 7.51891 12.6973 7 11.5 7V9ZM11.5 13C12.5482 13 13 13.6444 13 14H15C15 12.1464 13.2132 11 11.5 11V13ZM10.5 7V8H12.5V7H10.5ZM10.5 16L10.5 17L12.5 17L12.5 16L10.5 16ZM11.5 15C10.8466 15 10.3621 14.7145 10.1448 14.414L8.52412 15.5859C9.17138 16.4811 10.3027 17 11.5 17L11.5 15ZM13 14C13 14.3556 12.5482 15 11.5 15V17C13.2132 17 15 15.8535 15 14H13Z" fill="rgb(107 114 128)" />
						</svg>
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
