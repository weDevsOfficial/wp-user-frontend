/**
 * DESCRIPTION: SubscriptionDetails component with tab navigation
 * DESCRIPTION: Renders tabbed interface for subscription sections
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import SubscriptionSubsection from './SubscriptionSubsection';

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

	// Initialize dependent fields in store on mount
	useEffect( () => {
		// Populate the field dependency store with dependent fields from PHP
		if ( dependentFields && dispatch ) {
			dispatch.addDependentFields( dependentFields );
		}
	}, [ dependentFields, dispatch ] );

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
		</>
	);
};

export default SubscriptionDetails;
