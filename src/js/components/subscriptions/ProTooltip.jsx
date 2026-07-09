/**
 * DESCRIPTION: ProTooltip component for upgrade prompts
 * DESCRIPTION: Shows tooltip with upgrade link on hover for Pro features
 */
import { __ } from '@wordpress/i18n';

const ProTooltip = ( { isPro = true } ) => {
	const wpufSubscriptions = window.wpufSubscriptions || {};

	// Don't show tooltip if Pro is active or if not a Pro feature
	if ( ! isPro || wpufSubscriptions.isProActive ) {
		return null;
	}

	return (
		<div
			role="tooltip"
			className="wpuf-hidden wpuf-group-hover:wpuf-block wpuf-absolute wpuf-z-50 wpuf-w-64 wpuf-rounded-md wpuf-bg-gray-900 wpuf-px-3 wpuf-py-2 wpuf-text-xs wpuf-text-white wpuf-shadow-lg wpuf-left-0 wpuf-top-full wpuf-mt-1"
		>
			<div className="wpuf-relative">
				<p className="wpuf-m-0 wpuf-mb-2">
					{ __( 'This feature is available in Pro version', 'wp-user-frontend' ) }
				</p>
				<a
					href={ wpufSubscriptions.upgradeUrl || '#' }
					target="_blank"
					rel="noopener noreferrer"
					className="wpuf-text-emerald-400 wpuf-hover:wpuf-text-emerald-300 wpuf-font-medium wpuf-underline"
				>
					{ __( 'Upgrade to Pro', 'wp-user-frontend' ) }
					&rarr;
				</a>
				{/* Arrow */}
				<div className="wpuf-absolute -wpuf-top-1 wpuf-left-4 wpuf-w-2 wpuf-h-2 wpuf-rotate-45 wpuf-bg-gray-900" />
			</div>
		</div>
	);
};

export default ProTooltip;
