/**
 * DESCRIPTION: ProBadge component for Pro feature indicators
 * DESCRIPTION: Displays a "Pro" badge for premium features
 */
import { __ } from '@wordpress/i18n';

const ProBadge = ( { isPro = true } ) => {
	const wpufSubscriptions = window.wpufSubscriptions || {};

	// Don't show badge if Pro is active or if not a Pro feature
	if ( ! isPro || wpufSubscriptions.isProActive ) {
		return null;
	}

	return (
		<span className="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-md wpuf-bg-emerald-100 wpuf-px-2 wpuf-py-0.5 wpuf-text-xs wpuf-font-medium wpuf-text-emerald-800 wpuf-ring-1 wpuf-ring-inset wpuf-ring-emerald-600/20">
			{ __( 'Pro', 'wp-user-frontend' ) }
		</span>
	);
};

export default ProBadge;
