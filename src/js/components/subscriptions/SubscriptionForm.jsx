/**
 * DESCRIPTION: Subscription form component for creating/editing subscriptions
 * DESCRIPTION: Refactored to use SubscriptionDetails, stores, and new component architecture
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import SidebarMenu from './SidebarMenu';
import SubscriptionDetails from './SubscriptionDetails';
import UpdateButton from './UpdateButton';
import UnsavedChanges from './UnsavedChanges';

const SubscriptionForm = ( { mode = 'add-new', subscriptionId = null, onNavigateToList } ) => {
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ currentTab, setCurrentTab ] = useState( 'subscription_details' );
	const [ currentSubscriptionStatus, setCurrentSubscriptionStatus ] = useState( 'all' );
	const [ allCount, setAllCount ] = useState( { all: 0 } );

	// Store selectors
	const { subscription, isUpdating, isDirty, isUnsavedPopupOpen } = useSelect(
		( select ) => ( {
			subscription: select( 'wpuf/subscriptions' ).getItem(),
			isUpdating: select( 'wpuf/subscriptions' ).isUpdating(),
			isDirty: select( 'wpuf/subscriptions' ).isDirty(),
			isUnsavedPopupOpen: select( 'wpuf/subscriptions' ).isUnsavedPopupOpen(),
		} ),
		[]
	);

	console.log('[SubscriptionForm] subscription from store:', subscription);

	// Store actions
	const {
		setItem,
		setItemCopy,
		setIsDirty,
		setIsUnsavedPopupOpen,
		modifyItem,
		setBlankItem,
		updateItem: storeUpdateItem,
	} = useDispatch( 'wpuf/subscriptions' );

	// Fetch subscription data if in edit mode
	useEffect( () => {
		console.log('[SubscriptionForm] useEffect triggered, mode:', mode, 'subscriptionId:', subscriptionId);
		if ( mode === 'edit' && subscriptionId ) {
			// Fetch the subscription via API
			apiFetch( {
				path: `/wpuf/v1/wpuf_subscription/${ subscriptionId }`,
				method: 'GET',
			} )
				.then( ( data ) => {
					setItem( data );
					setItemCopy( JSON.parse( JSON.stringify( data ) ) );
				} )
				.catch( ( err ) => {
					setError( err.message );
				} );
		} else if ( mode === 'add-new' ) {
			// Initialize blank item for new subscription
			console.log('[SubscriptionForm] calling setBlankItem');
			setBlankItem();
		}
	}, [ mode, subscriptionId, setItem, setItemCopy, setBlankItem ] );

	// Handle field changes
	const handleFieldChange = ( field, value ) => {
		switch ( field.db_type ) {
			case 'post':
				modifyItem( field.db_key, value );
				break;

			case 'meta':
				modifyItem( field.db_key, value, null );
				break;

			case 'meta_serialized':
				modifyItem( field.db_key, value, field.serialize_key );
				break;

			default:
				break;
		}
	};

	// Handle save (publish)
	const handlePublish = async () => {
		setIsSaving( true );
		setError( null );

		try {
			// Set post_status to publish
			modifyItem( 'post_status', 'publish' );

			const result = await storeUpdateItem();

			if ( result?.success ) {
				setIsDirty( false );
				onNavigateToList?.();
			} else {
				setError( result?.message || __( 'Failed to save subscription', 'wp-user-frontend' ) );
			}
		} catch ( err ) {
			setError( err.message );
		} finally {
			setIsSaving( false );
		}
	};

	// Handle save as draft
	const handleSaveDraft = async () => {
		setIsSaving( true );
		setError( null );

		try {
			// Set post_status to draft before saving
			modifyItem( 'post_status', 'draft' );

			const result = await storeUpdateItem();

			if ( result?.success ) {
				setIsDirty( false );
				onNavigateToList?.();
			} else {
				setError( result?.message || __( 'Failed to save subscription', 'wp-user-frontend' ) );
			}
		} catch ( err ) {
			setError( err.message );
		} finally {
			setIsSaving( false );
		}
	};

	// Handle cancel - check for unsaved changes
	const handleCancel = () => {
		if ( isDirty ) {
			setIsUnsavedPopupOpen( true );
		} else {
			onNavigateToList?.();
		}
	};

	// Discard unsaved changes and navigate
	const handleDiscardChanges = () => {
		setIsDirty( false );
		setIsUnsavedPopupOpen( false );
		onNavigateToList?.();
	};

	// Continue editing (close popup)
	const handleContinueEditing = () => {
		setIsUnsavedPopupOpen( false );
	};

	if ( ! subscription ) {
		return (
			<div className="wpuf-p-8 wpuf-text-center">
				<p>{ __( 'Loading subscription...', 'wp-user-frontend' ) }</p>
			</div>
		);
	}

	return (
		<div className={ `wpuf-flex wpuf-pt-[40px] wpuf-px-[20px] ${ isUnsavedPopupOpen ? 'wpuf-blur' : '' }` }>
			{/* Left Sidebar */}
			<div className="wpuf-basis-1/5 wpuf-border-r-2 wpuf-border-gray-200">
				<SidebarMenu
					currentSubscriptionStatus={ currentSubscriptionStatus }
					allCount={ allCount }
					onCheckIsDirty={ setCurrentSubscriptionStatus }
					isUnsavedPopupOpen={ isUnsavedPopupOpen }
				/>
			</div>

			{/* Main Content */}
			<div className="wpuf-basis-4/5 wpuf-px-12">
				{/* Header */}
				<h3 className="wpuf-text-lg wpuf-font-bold wpuf-mb-0">
					{ mode === 'edit'
						? __( 'Edit Subscription', 'wp-user-frontend' )
						: __( 'New Subscription', 'wp-user-frontend' ) }
				</h3>

				{/* Error message */}
				{ error && (
					<div className="wpuf-p-4 wpuf-mb-4 wpuf-bg-red-100 wpuf-border wpuf-border-red-400 wpuf-text-red-700 wpuf-rounded">
						{ error }
					</div>
				) }

				{/* Subscription details with tabs */}
				<SubscriptionDetails
					subscription={ subscription }
					onFieldChange={ handleFieldChange }
					currentTab={ currentTab }
					onTabChange={ setCurrentTab }
				/>

				{/* Action buttons */}
				<div className="wpuf-flex wpuf-flex-row-reverse wpuf-mt-8 wpuf-text-end">
					<UpdateButton
						buttonText={ mode === 'edit' ? __( 'Update', 'wp-user-frontend' ) : __( 'Save', 'wp-user-frontend' ) }
						isUpdating={ isUpdating || isSaving }
						onPublish={ handlePublish }
						onSaveDraft={ handleSaveDraft }
					/>
					<button
						type="button"
						onClick={ handleCancel }
						className="wpuf-mr-[10px] wpuf-rounded-md wpuf-bg-white wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-shadow-sm wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50"
					>
						{ __( 'Cancel', 'wp-user-frontend' ) }
					</button>
				</div>

				{/* Unsaved changes popup */}
				{ isUnsavedPopupOpen && (
					<UnsavedChanges
						onDiscard={ handleDiscardChanges }
						onContinue={ handleContinueEditing }
					/>
				) }
			</div>
		</div>
	);
};

export default SubscriptionForm;
