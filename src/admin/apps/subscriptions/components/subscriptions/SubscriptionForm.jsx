/**
 * DESCRIPTION: Subscription form component for creating/editing subscriptions
 * DESCRIPTION: Refactored to use URL-based navigation via router
 */
import { useState, useEffect, useRef } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { doAction } from '@wordpress/hooks';
import { Button, ClassicFields, ErrorState, Skeleton, classicFieldValues, notify } from '@wpuf/components';
import SubscriptionDetails from './SubscriptionDetails';
import InfoCard from './InfoCard';
import UpdateButton from './UpdateButton';
import { SubscriptionFormFooter } from '../../slots';
import { useSubscriptionData, useSubscriptionActions, useSubscriptionNavigation } from '../../hooks';
import { fetchLegacyFields, fetchSubscription } from '../../api/subscription';
import { subscriptionsData } from '../../boot';

// Loading: the form's shape (title, tabs, two sections), no layout jump.
const FormSkeleton = () => (
	<div className="px-12" aria-busy="true">
		<div className="w-48"><Skeleton /></div>
		<div className="mt-6 flex gap-6 border-b border-gray-200 pb-3">
			{ [ 0, 1, 2 ].map( ( index ) => <div key={ index } className="w-32"><Skeleton /></div> ) }
		</div>
		{ [ 0, 1 ].map( ( index ) => (
			<div key={ index } className="border border-gray-200 rounded-xl mt-4 mb-4 p-4"><Skeleton lines={ 3 } /></div>
		) ) }
	</div>
);

// Tab of the first field with a validation error (develop jumps nowhere; the
// error would stay hidden on another tab).
const tabOfField = ( errorKey ) => {
	const fields = subscriptionsData().fields || {};

	for ( const tab in fields ) {
		for ( const sub in fields[ tab ] ) {
			for ( const key in fields[ tab ][ sub ] ) {
				if ( key === errorKey || fields[ tab ][ sub ][ key ]?.id === errorKey ) {
					return { tab, name: fields[ tab ][ sub ][ key ].name };
				}
			}
		}
	}

	return null;
};

const SubscriptionForm = ( { mode = 'add-new', subscriptionId = null } ) => {
	const [ loadError, setLoadError ] = useState( null );
	const [ loadKey, setLoadKey ] = useState( 0 );
	const [ currentTab, setCurrentTab ] = useState( 'subscription_details' );
	// Other plugins' fields of the classic pack form (their PHP hooks still run).
	const [ classic, setClassic ] = useState( { rows: '', html: '' } );
	const classicForm = useRef( null );

	useEffect( () => {
		setClassic( { rows: '', html: '' } );
		fetchLegacyFields( 'edit' === mode ? subscriptionId : 0 )
			.then( ( data ) => setClassic( { rows: data.rows || '', html: data.html || '' } ) )
			.catch( () => {} );
	}, [ mode, subscriptionId ] );

	// Navigation hook
	const { goToList } = useSubscriptionNavigation();

	// Store selectors using custom hook
	const { subscription, isUpdating, isDirty } = useSubscriptionData();

	// Store actions using custom hook
	const {
		setItem,
		setItemCopy,
		setIsDirty,
		setIsUnsavedPopupOpen,
		modifyItem,
		setBlankItem,
		validateFields,
		updateItem: storeUpdateItem,
		populateTaxonomyRestrictionData,
	} = useSubscriptionActions();
	const subscriptionsStore = useSelect( ( select ) => select( 'wpuf/subscriptions' ), [] );

	// Fetch subscription data if in edit mode
	useEffect( () => {
		setLoadError( null );

		if ( mode === 'edit' && subscriptionId ) {
			setItem( null );
			fetchSubscription( subscriptionId )
				.then( ( data ) => {
					if ( data.success && data.subscription ) {
						setItem( data.subscription );
						setItemCopy( JSON.parse( JSON.stringify( data.subscription ) ) );
						populateTaxonomyRestrictionData( data.subscription );
						doAction( 'wpuf.subscription.formMounted', data.subscription, mode );
					} else {
						setLoadError( data.message || __( 'Subscription not found', 'wp-user-frontend' ) );
					}
				} )
				.catch( ( err ) => {
					setLoadError( err.message || __( 'Failed to load subscription', 'wp-user-frontend' ) );
				} );
		} else if ( mode === 'add-new' ) {
			// Initialize blank item for new subscription
			setBlankItem();
			doAction( 'wpuf.subscription.formMounted', null, mode );
		}

		return () => {
			doAction( 'wpuf.subscription.formUnmounted' );
		};
	}, [ mode, subscriptionId, loadKey, setItem, setItemCopy, setBlankItem ] );

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

		// develop processInput: the plan name also writes the slug.
		if ( 'post_title' === field.db_key ) {
			modifyItem( 'post_name', String( value ).replace( /\s+/g, '-' ).toLowerCase() );
		}
	};

	// develop updateSubscription: validate, save, toast; back to the list on success.
	const save = async ( status ) => {
		modifyItem( 'post_status', status );

		// Dispatched actions return a Promise in @wordpress/data.
		if ( ! ( await validateFields() ) ) {
			const errors = subscriptionsStore.getErrors() || {};
			const first = tabOfField( Object.keys( errors )[ 0 ] );

			if ( first ) {
				setCurrentTab( first.tab );
				setTimeout( () => document.getElementById( first.name )?.focus(), 50 );
			}
			return;
		}

		doAction( 'wpuf.subscription.beforeSave', subscription, mode );

		let result = null;
		try {
			result = await storeUpdateItem( classicFieldValues( classicForm.current ) );
		} catch ( err ) {
			result = { success: false, message: err.message };
		}

		if ( result?.success ) {
			notify( result.message, 'success' );
			setIsDirty( false );
			doAction( 'wpuf.subscription.afterSave', result, mode );
			goToList();
			return;
		}

		// Edits stay in the form.
		notify( result?.message || __( 'Failed to save subscription', 'wp-user-frontend' ), 'danger' );
	};

	// Handle cancel - check for unsaved changes
	const handleCancel = () => {
		if ( isDirty ) {
			setIsUnsavedPopupOpen( true );
		} else {
			goToList();
		}
	};

	if ( loadError ) {
		return (
			<div className="px-12">
				<ErrorState message={ loadError } onRetry={ () => setLoadKey( loadKey + 1 ) } />
			</div>
		);
	}

	if ( ! subscription || ( 'edit' === mode && String( subscription.ID ) !== String( subscriptionId ) ) ) {
		return <FormSkeleton />;
	}

	return (
		<div>
			{/* Title row with the actions on the right (FlyHR page header) */}
			<div className="flex flex-wrap items-center justify-between gap-4">
				<h3 className="text-base font-semibold m-0">
					{ mode === 'edit'
						? __( 'Edit Subscription', 'wp-user-frontend' )
						: __( 'New Subscription', 'wp-user-frontend' ) }
				</h3>
				<div className="flex items-center gap-3">
					<Button variant="secondary" size="lg" onClick={ handleCancel } disabled={ isUpdating }>
						{ __( 'Cancel', 'wp-user-frontend' ) }
					</Button>
					<UpdateButton
						buttonText={ mode === 'edit' ? __( 'Update', 'wp-user-frontend' ) : __( 'Save', 'wp-user-frontend' ) }
						isUpdating={ isUpdating }
						onPublish={ () => save( 'publish' ) }
						onSaveDraft={ () => save( 'draft' ) }
						publishDisabled={ 'edit' === mode && ! isDirty && 'publish' === subscription?.post_status }
						draftDisabled={ 'edit' === mode && ! isDirty && 'draft' === subscription?.post_status }
					/>
				</div>
			</div>

			<InfoCard subscription={ subscription } mode={ mode } />

			{/* Subscription details with tabs */}
			<SubscriptionDetails
				subscription={ subscription }
				onFieldChange={ handleFieldChange }
				currentTab={ currentTab }
				onTabChange={ setCurrentTab }
			/>

			{ ( classic.rows || classic.html ) && (
				<div className="border border-gray-200 rounded-xl mt-4 mb-4 p-4">
					<h4 className="m-0 mb-1 text-sm font-semibold text-gray-900">{ __( 'More options', 'wp-user-frontend' ) }</h4>
					<p className="m-0 mb-3 text-xs text-gray-500">{ __( 'Fields other plugins add to subscriptions.', 'wp-user-frontend' ) }</p>
					<ClassicFields ref={ classicForm } rows={ classic.rows } html={ classic.html } onChange={ () => setIsDirty( true ) } />
				</div>
			) }

			{/* Extension slot: Pro and third-party plugins can add UI below form fields */}
			<SubscriptionFormFooter.Slot
				fillProps={ { subscription, mode, onFieldChange: handleFieldChange } }
			/>

		</div>
	);
};

export default SubscriptionForm;
