/**
 * DESCRIPTION: SubscriptionDetails component with tab navigation
 * DESCRIPTION: Renders tabbed interface for subscription sections
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import SubscriptionSubsection from './SubscriptionSubsection';
import { SubscriptionTabContent } from '../../slots';

const SubscriptionDetails = ( { subscription, onFieldChange, currentTab: externalCurrentTab, onTabChange: externalOnTabChange } ) => {
	const wpufSubscriptions = window.wpufSubscriptions || {};
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
			{/* Tab Navigation */}
			<div className="wpuf-mt-4 wpuf-text-sm wpuf-font-medium wpuf-text-center wpuf-text-gray-500 wpuf-border-b wpuf-border-gray-200">
				<ul className="wpuf-flex wpuf-flex-wrap wpuf--mb-px">
					{ sections.map( ( section ) => (
						<li key={ section.id } className="wpuf-mb-0 wpuf-me-2">
							<button
								type="button"
								onClick={ () => setCurrentTab( section.id ) }
								className={ `active:wpuf-shadow-none focus:wpuf-shadow-none wpuf-inline-block wpuf-p-4 wpuf-rounded-t-lg hover:wpuf-text-primary hover:wpuf-border-b-2 hover:wpuf-border-primary wpuf-transition-all ${
									currentTab === section.id
										? 'wpuf-border-b-2 wpuf-border-primary wpuf-text-primary'
										: ''
								}` }
							>
								{ section.title }
							</button>
						</li>
					) ) }
				</ul>
			</div>

			{/* Subsections for current tab */}
			{ subSections[ currentTab ] &&
				subSections[ currentTab ].map( ( subSection ) => (
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
