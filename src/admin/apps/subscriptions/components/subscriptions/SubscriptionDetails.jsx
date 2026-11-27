/**
 * DESCRIPTION: SubscriptionDetails component with tab navigation
 * DESCRIPTION: Renders tabbed interface for subscription sections
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { Tabs } from '@wpuf/components';
import SubscriptionSubsection from './SubscriptionSubsection';
import { SubscriptionTabContent } from '../../slots';
import { CreditCard, FileText, Settings2 } from 'lucide-react';
import { subscriptionsData } from '../../boot';

// Section tab icons (FlyHR page tab bar).
const TAB_ICONS = {
	subscription_details: FileText,
	payment_settings: CreditCard,
	advanced_configuration: Settings2,
};

const SubscriptionDetails = ( { subscription, onFieldChange, currentTab: externalCurrentTab, onTabChange: externalOnTabChange } ) => {
	const wpufSubscriptions = subscriptionsData();
	const sections = wpufSubscriptions.sections || [];
	const subSections = wpufSubscriptions.subSections || {};
	const fields = wpufSubscriptions.fields || {};
	const dependentFields = wpufSubscriptions.dependentFields || {};

	// Use internal state if no external control provided
	const [ internalCurrentTab, setInternalCurrentTab ] = useState( 'subscription_details' );

	const currentTab = externalCurrentTab !== undefined ? externalCurrentTab : internalCurrentTab;
	const setCurrentTab = externalOnTabChange !== undefined ? externalOnTabChange : setInternalCurrentTab;

	const dispatch = useDispatch( 'wpuf/subscriptions-field-dependency' );

	// Initialize dependent fields in store and calculate initial visibility
	useEffect( () => {
		if ( ! dependentFields || ! dispatch ) {
			return;
		}

		dispatch.addDependentFields( dependentFields );

		// Build initial modifier statuses from the subscription data and field definitions
		const allFields = wpufSubscriptions.fields || {};
		const initialStatuses = {};

		for ( const modifierKey in dependentFields ) {
			// Find the field definition for this modifier key across all sections/subsections
			let fieldDef = null;

			for ( const sectionId in allFields ) {
				for ( const subSectionId in allFields[ sectionId ] ) {
					if ( allFields[ sectionId ][ subSectionId ]?.[ modifierKey ] ) {
						fieldDef = allFields[ sectionId ][ subSectionId ][ modifierKey ];
						break;
					}
				}
				if ( fieldDef ) {
					break;
				}
			}

			if ( ! fieldDef ) {
				initialStatuses[ modifierKey ] = false;
				continue;
			}

			// Read the current value from the subscription
			let rawValue = fieldDef.default || '';

			if ( subscription ) {
				if ( fieldDef.db_type === 'meta' ) {
					rawValue = subscription.meta_value?.[ fieldDef.db_key ] ?? fieldDef.default ?? '';
				} else if ( fieldDef.db_type === 'post' ) {
					rawValue = subscription[ fieldDef.db_key ] ?? fieldDef.default ?? '';
				}
			}

			// Convert to boolean the same way SubscriptionField does
			initialStatuses[ modifierKey ] = rawValue === 'on' || rawValue === 'yes' || rawValue === true;
		}

		dispatch.initializeFieldVisibility( initialStatuses );
	}, [ dependentFields, dispatch, subscription ] );

	return (
		<>
			{/* Tab Navigation: FlyHR page tab bar */}
			<div className="mt-6">
				<Tabs
					variant="pill"
					label={ __( 'Subscription sections', 'wp-user-frontend' ) }
					value={ currentTab }
					onChange={ setCurrentTab }
					tabs={ sections.map( ( section ) => {
						const Icon = TAB_ICONS[ section.id ] || Settings2;
						return { id: section.id, label: section.title, icon: <Icon size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> };
					} ) }
				/>
			</div>

			{/* Subsections for current tab — Object.values handles PHP arrays that lost their sequential keys */}
			{ subSections[ currentTab ] &&
				( Array.isArray( subSections[ currentTab ] )
					? subSections[ currentTab ]
					: Object.values( subSections[ currentTab ] )
				).map( ( subSection ) => (
					<SubscriptionSubsection
						key={ subSection.id }
						subSection={ subSection }
						fields={ fields[ currentTab ]?.[ subSection.id ] }
						subscription={ subscription }
						onFieldChange={ onFieldChange }
					/>
				) )}

			{/* Extension slot: Pro and third-party plugins can add content per tab */}
			<SubscriptionTabContent.Slot
				fillProps={ { currentTab, subscription, onFieldChange } }
			/>
		</>
	);
};

export default SubscriptionDetails;
