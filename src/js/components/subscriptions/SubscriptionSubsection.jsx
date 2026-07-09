/**
 * DESCRIPTION: SubscriptionSubsection component for collapsible form sections
 * DESCRIPTION: Renders a collapsible section with fields inside
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import SubscriptionField from './SubscriptionField';
import ProBadge from './ProBadge';
import ProTooltip from './ProTooltip';
import { SubscriptionAfterSubsection } from '../../slots';

const SubscriptionSubsection = ( { subSection, fields, subscription, onFieldChange } ) => {
	// Some sections should be open by default (first subsection of each section)
	const openTabs = [ 'overview', 'content_limit', 'payment_details' ];
	const shouldBeOpen = openTabs.includes( subSection.id );

	// Start closed (opposite of shouldBeOpen)
	const [ isClosed, setIsClosed ] = useState( ! shouldBeOpen );

	const wpufSubscriptions = window.wpufSubscriptions || {};

	return (
		<div className="wpuf-border wpuf-border-gray-200 wpuf-rounded-xl wpuf-mt-4 wpuf-mb-4">
			{/* Header */}
			<h2 className="wpuf-m-0">
				<button
					type="button"
					onClick={ () => setIsClosed( ! isClosed ) }
					className={ `wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-full wpuf-p-4 wpuf-font-medium rtl:wpuf-text-right wpuf-text-gray-500 wpuf-bg-gray-100 wpuf-gap-3 ${ isClosed ? 'wpuf-rounded-xl' : 'wpuf-rounded-t-xl' }` }
				>
					<span className="wpuf-flex">
						{ subSection.label }
						{ subSection.sub_label && (
							<span className="wpuf-relative wpuf-m-0 wpuf-p-0 wpuf-ml-2 wpuf-mt-[1px] wpuf-italic wpuf-text-[11px] wpuf-text-gray-400">
								{ subSection.sub_label }
							</span>
						) }
						{ subSection.is_pro && (
							<span className="pro-icon-title wpuf-relative wpuf-group wpuf-ml-2">
								<ProBadge />
								<ProTooltip />
							</span>
						) }
					</span>
					<svg
						className={ `wpuf-w-3 wpuf-h-3 shrink-0 ${ isClosed ? 'wpuf-rotate-90' : 'wpuf-rotate-180' }` }
														data-accordion-icon
														aria-hidden="true"
														xmlns="http://www.w3.org/2000/svg"
														fill="none"
														viewBox="0 0 10 6"
					>
						<path
							stroke="currentColor"
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth="2"
							d="M9 5 5 1 1 5"
						/>
					</svg>
				</button>
			</h2>

			{/* Fields */}
			{ ! isClosed && (
				<>
					{ Object.entries( fields || {} ).map( ( [ fieldKey, field ] ) => (
						<SubscriptionField
							key={ fieldKey }
							field={ field }
							fieldId={ fieldKey }
							subscription={ subscription }
							onFieldChange={ onFieldChange }
						/>
					) ) }

					{/* Extension slot: Pro and third-party plugins can add UI after subsection fields */}
					<SubscriptionAfterSubsection.Slot
						fillProps={ { subSection, subscription, onFieldChange } }
					/>

					{/* Notice */}
					{ subSection.notice && (
						<div className="wpuf-rounded-b-xl wpuf-bg-yellow-50 wpuf-p-4">
							<div className="wpuf-flex wpuf-items-center">
								<div className="wpuf-flex-shrink-0">
									<svg
										className="wpuf-h-5 wpuf-w-5 wpuf-text-yellow-400"
										viewBox="0 0 20 20"
										fill="currentColor"
														aria-hidden="true"
									>
										<path
											fillRule="evenodd"
											d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
											clipRule="evenodd"
										/>
									</svg>
								</div>
								<div className="wpuf-ml-3">
									<div className="wpuf-mt-2 wpuf-text-sm wpuf-text-yellow-700">
										<p dangerouslySetInnerHTML={ { __html: subSection.notice.message } } />
									</div>
								</div>
							</div>
						</div>
					) }
				</>
			) }
		</div>
	);
};

export default SubscriptionSubsection;
