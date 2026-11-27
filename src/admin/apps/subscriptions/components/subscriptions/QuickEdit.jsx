/**
 * DESCRIPTION: Quick Edit modal for subscription plan name and date
 * DESCRIPTION: Allows quick editing without leaving the list view
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { Button, DateTime, Modal, TextInput, notify } from '@wpuf/components';
import UpdateButton from './UpdateButton';
import { subscriptionsData } from '../../boot';

const QuickEdit = () => {
	const [ title, setTitle ] = useState( '' );
	const [ date, setDate ] = useState( '' );

	const isQuickEdit = useSelect( ( select ) => select( 'wpuf/subscriptions-quick-edit' ).isQuickEdit(), [] );
	const { item, errors, isUpdating, updateError, params } = useSelect( ( select ) => {
		const store = select( 'wpuf/subscriptions' );
		return {
			item: store.getItem(),
			errors: store.getErrors() || {},
			isUpdating: store.isUpdating(),
			updateError: store.getUpdateError(),
			params: select( 'wpuf/subscriptions-router' ).getQueryParams(),
		};
	}, [] );

	const { setItem, setError, resetErrors, setUpdateError, updateItem, fetchItems, fetchCounts } = useDispatch( 'wpuf/subscriptions' );
	const { setQuickEditStatus } = useDispatch( 'wpuf/subscriptions-quick-edit' );

	// Initialize form data when the edited item changes
	useEffect( () => {
		if ( item ) {
			setTitle( item.post_title || '' );
			setDate( item.post_date || '' );
		}
	}, [ item ] );

	const close = () => {
		setQuickEditStatus( false );
		resetErrors();
		setUpdateError( { status: false, message: '' } );
	};

	// develop QuickEdit updateSubscription: validate the plan name, save, toast, close.
	const updateWithStatus = ( newStatus ) => {
		resetErrors();
		setUpdateError( { status: false, message: '' } );

		if ( ! title || title.trim() === '' ) {
			setError( 'planName', __( 'This field is required', 'wp-user-frontend' ) );
			return;
		}

		if ( title.includes( '#' ) ) {
			setError( 'planName', __( '# is not supported in plan name', 'wp-user-frontend' ) );
			return;
		}

		setItem( {
			...item,
			post_title: title,
			post_date: date,
			post_status: newStatus,
		} );

		updateItem().then( ( result ) => {
			if ( result?.success ) {
				notify( result.message || __( 'Subscription updated successfully', 'wp-user-frontend' ), 'success' );
				close();
				// develop updated the card in place; reload this list page (no page reload).
				const status = params.post_status || 'all';
				const perPage = parseInt( subscriptionsData().perPage || 10, 10 );
				const page = Math.max( 1, parseInt( params.p, 10 ) || 1 );
				fetchItems( status, ( page - 1 ) * perPage );
				fetchCounts();
				return;
			}

			setUpdateError( { status: true, message: result?.message || __( 'An error occurred while updating', 'wp-user-frontend' ) } );
		}, () => {
			setUpdateError( { status: true, message: __( 'An error occurred while updating', 'wp-user-frontend' ) } );
		} );
	};

	return (
		<Modal open={ isQuickEdit } onClose={ close } title={ __( 'Quick Edit', 'wp-user-frontend' ) } hideTitle>
			{/* Plan Name Field */}
			<div className="px-2">
				<label htmlFor="plan-name" className="block text-sm font-medium leading-6 text-gray-900">
					{ __( 'Plan name', 'wp-user-frontend' ) }
				</label>
				<div className="relative mt-2">
					<TextInput
						id="plan-name"
						value={ title }
						onChange={ setTitle }
						aria-invalid={ errors?.planName ? 'true' : undefined }
						aria-describedby={ errors?.planName ? 'plan-name-error' : undefined }
						className={ errors?.planName ? 'border-red-500 text-red-900 focus-visible:border-red-500' : '' }
					/>
				</div>
				{ errors?.planName && (
					<p className="mt-2 mb-0 text-sm text-red-600" id="plan-name-error">
						{ errors.planName.message }
					</p>
				) }
			</div>

			{/* Date Field */}
			<div className="px-2 mt-4">
				<label htmlFor="post-date" className="block text-sm font-medium leading-6 text-gray-900">
					{ __( 'Publish date', 'wp-user-frontend' ) }
				</label>
				<div className="relative mt-2">
					<DateTime id="post-date" withTime value={ date } onChange={ setDate } className={ errors?.date ? 'border-red-500' : '' } />
				</div>
				{ errors?.date && (
					<p className="mt-2 mb-0 text-sm text-red-600" id="date-error">
						{ __( 'Not a valid date', 'wp-user-frontend' ) }
					</p>
				) }
			</div>

			{/* Update error (develop: under the fields, inside the panel) */}
			{ updateError && updateError.status && (
				<div className="px-2 mt-4">
					<p className="mt-2 mb-0 text-xs text-red-600" role="alert">{ updateError.message }</p>
				</div>
			) }

			{/* Actions */}
			<div className="mt-8 flex flex-row-reverse gap-4">
				<UpdateButton
					isUpdating={ isUpdating }
					onPublish={ () => updateWithStatus( 'publish' ) }
					onSaveDraft={ () => updateWithStatus( 'draft' ) }
				/>
				<Button variant="secondary" onClick={ close } disabled={ isUpdating }>
					{ __( 'Cancel', 'wp-user-frontend' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default QuickEdit;
