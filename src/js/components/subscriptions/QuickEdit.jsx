/**
 * DESCRIPTION: Quick Edit modal for subscription plan name and date
 * DESCRIPTION: Allows quick editing without leaving the list view
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { ExclamationCircleIcon } from '@heroicons/react/20/solid';
import UpdateButton from './UpdateButton';

const QuickEdit = () => {
	const [ title, setTitle ] = useState( '' );
	const [ date, setDate ] = useState( '' );

	// Get data from stores
	const isQuickEdit = useSelect( ( select ) => select( 'wpuf/subscriptions-quick-edit' ).isQuickEdit(), [] );
	const { item, errors, isUpdating, updateError } = useSelect( ( select ) => {
		const store = select( 'wpuf/subscriptions' );
		return {
			item: store.getItem(),
			errors: store.getErrors() || {},
			isUpdating: store.isUpdating(),
			updateError: store.getUpdateError(),
		};
	}, [] );

	const { setItem, setError, resetErrors, updateItem } = useDispatch( 'wpuf/subscriptions' );
	const { setQuickEditStatus } = useDispatch( 'wpuf/subscriptions-quick-edit' );
	const { addNotice } = useDispatch( 'wpuf/subscriptions-notice' );

	// Initialize form data when item changes
	useEffect( () => {
		if ( item ) {
			setTitle( item.post_title || '' );
			setDate( item.post_date || '' );
		}
	}, [ item ] );

	// Format date for datetime-local input (YYYY-MM-DDTHH:mm)
	const formatDateTimeForInput = useCallback( ( dateStr ) => {
		if ( ! dateStr ) return '';
		const date = new Date( dateStr );
		const year = date.getFullYear();
		const month = String( date.getMonth() + 1 ).padStart( 2, '0' );
		const day = String( date.getDate() ).padStart( 2, '0' );
		const hours = String( date.getHours() ).padStart( 2, '0' );
		const minutes = String( date.getMinutes() ).padStart( 2, '0' );
		return `${ year }-${ month }-${ day }T${ hours }:${ minutes }`;
	}, [] );

	// Format date for API (YYYY-MM-DD HH:mm:ss)
	const formatDateTimeForAPI = useCallback( ( dateStr ) => {
		if ( ! dateStr ) return '';
		const date = new Date( dateStr );
		const year = date.getFullYear();
		const month = String( date.getMonth() + 1 ).padStart( 2, '0' );
		const day = String( date.getDate() ).padStart( 2, '0' );
		const hours = String( date.getHours() ).padStart( 2, '0' );
		const minutes = String( date.getMinutes() ).padStart( 2, '0' );
		const seconds = String( date.getSeconds() ).padStart( 2, '0' );
		return `${ year }-${ month }-${ day } ${ hours }:${ minutes }:${ seconds }`;
	}, [] );

	// Common update function with status
	const updateWithStatus = useCallback( ( newStatus ) => {
		resetErrors();

		// Validate plan name
		if ( ! title || title.trim() === '' ) {
			setError( 'planName', __( 'This field is required', 'wp-user-frontend' ) );
			return;
		}

		if ( title.includes( '#' ) ) {
			setError( 'planName', __( '# is not supported in plan name', 'wp-user-frontend' ) );
			return;
		}

		// Update the item with new values
		const updatedItem = {
			...item,
			post_title: title,
			post_date: formatDateTimeForAPI( date ),
			post_status: newStatus,
		};

		setItem( updatedItem );

		// Call updateItem action
		updateItem().then( ( result ) => {
			if ( result.success ) {
				addNotice( {
					content: result.message || __( 'Subscription updated successfully', 'wp-user-frontend' ),
					type: 'success',
				} );
				setQuickEditStatus( false );
				// Refresh the list after a short delay
				setTimeout( () => {
					window.location.reload();
				}, 1000 );
			} else {
				setError( 'fetch', result.message || __( 'An error occurred while updating', 'wp-user-frontend' ) );
			}
		} );
	}, [ item, title, date, setItem, setError, resetErrors, updateItem, setQuickEditStatus, addNotice, formatDateTimeForAPI ] );

	// Handle publish
	const handlePublish = useCallback( () => {
		updateWithStatus( 'publish' );
	}, [ updateWithStatus ] );

	// Handle save as draft
	const handleSaveDraft = useCallback( () => {
		updateWithStatus( 'draft' );
	}, [ updateWithStatus ] );

	// Handle cancel
	const handleCancel = useCallback( () => {
		setQuickEditStatus( false );
		resetErrors();
	}, [ setQuickEditStatus, resetErrors ] );

	if ( ! isQuickEdit ) {
		return null;
	}

	return (
		<>
			{/* Backdrop */}
			<div className="wpuf-fixed wpuf-inset-0 wpuf-z-10 wpuf-bg-black wpuf-bg-opacity-50" />

			{/* Modal */}
			<div className="wpuf-fixed wpuf-inset-0 wpuf-z-50 wpuf-flex wpuf-items-center wpuf-justify-center wpuf-p-4">
				<div className="wpuf-mx-auto wpuf-w-full wpuf-max-w-lg wpuf-rounded-lg wpuf-bg-white wpuf-shadow-xl wpuf-p-6">
					{/* Plan Name Field */}
					<div className="wpuf-px-2">
						<label htmlFor="plan-name" className="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
							{ __( 'Plan name', 'wp-user-frontend' ) }
						</label>
						<div className="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
							<input
								type="text"
								id="plan-name"
								value={ title }
								onChange={ ( e ) => setTitle( e.target.value ) }
								className={ `wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1.5 wpuf-pl-3 wpuf-pr-10 wpuf-shadow-sm focus:wpuf-outline-none focus:wpuf-ring-1 sm:wpuf-text-sm ${
									errors?.planName
										? '!wpuf-border-red-500 wpuf-ring-red-300 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500 wpuf-border-2'
										: 'wpuf-ring-gray-300 focus:wpuf-ring-blue-500 wpuf-border-gray-300'
								}` }
								aria-invalid={ errors?.planName ? 'true' : 'false' }
							/>
							{ errors?.planName && (
								<div className="wpuf-pointer-events-none wpuf-absolute wpuf-inset-y-0 wpuf-right-0 wpuf-flex wpuf-items-center wpuf-pr-3">
									<ExclamationCircleIcon className="wpuf-h-5 wpuf-w-5 wpuf-text-red-500" aria-hidden="true" />
								</div>
							) }
						</div>
						{ errors?.planName && (
							<p className="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="plan-name-error">
								{ errors?.planName?.message }
							</p>
						) }
					</div>

					{/* Date Field */}
					<div className="wpuf-px-2 wpuf-mt-4">
						<label htmlFor="post-date" className="wpuf-block wpuf-text-sm wpuf-font-medium wpuf-leading-6 wpuf-text-gray-900">
							{ __( 'Date', 'wp-user-frontend' ) }
						</label>
						<div className="wpuf-relative wpuf-mt-2 wpuf-rounded-md wpuf-shadow-sm">
							<input
								type="datetime-local"
								id="post-date"
								value={ formatDateTimeForInput( date ) }
								onChange={ ( e ) => setDate( e.target.value ) }
								className={ `wpuf-w-full wpuf-rounded-md wpuf-bg-white wpuf-py-1.5 wpuf-pl-3 wpuf-pr-3 wpuf-shadow-sm focus:wpuf-outline-none focus:wpuf-ring-1 sm:wpuf-text-sm ${
									errors?.date
										? '!wpuf-border-red-500 wpuf-ring-red-300 placeholder:wpuf-text-red-300 !wpuf-text-red-900 focus:wpuf-ring-red-500 wpuf-border-2'
										: 'wpuf-ring-gray-300 focus:wpuf-ring-blue-500 wpuf-border-gray-300'
								}` }
								aria-invalid={ errors?.date ? 'true' : 'false' }
							/>
						</div>
						{ errors?.date && (
							<p className="wpuf-mt-2 wpuf-text-sm wpuf-text-red-600" id="date-error">
								{ __( 'Not a valid date', 'wp-user-frontend' ) }
							</p>
						) }
					</div>

					{/* Update Error */}
					{ updateError && updateError.status && (
						<div className="wpuf-px-2 wpuf-mt-4">
							<p className="wpuf-mt-2 wpuf-text-xs wpuf-text-red-600">{ updateError.message }</p>
						</div>
					) }

					{/* Actions */}
					<div className="wpuf-mt-6 wpuf-flex wpuf-flex-row-reverse wpuf-gap-3">
						<UpdateButton
							isUpdating={ isUpdating }
							onPublish={ handlePublish }
							onSaveDraft={ handleSaveDraft }
						/>
						<button
							type="button"
							onClick={ handleCancel }
							disabled={ isUpdating }
							className={ `wpuf-rounded-lg wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50${
								isUpdating ? ' wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
							}` }
						>
							{ __( 'Cancel', 'wp-user-frontend' ) }
						</button>
					</div>
				</div>
			</div>
		</>
	);
};

export default QuickEdit;
