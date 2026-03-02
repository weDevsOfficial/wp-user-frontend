/**
 * DESCRIPTION: Displays header with title and description for subscription list
 * DESCRIPTION: Shows different messages based on current subscription status
 */
import { __ } from '@wordpress/i18n';

const ListHeader = ( { message } ) => {
	// Get title from store - for now we'll pass it as prop or use simpler approach
	// The Vue version uses the store to get currentSubscriptionStatus
	const getStatusTitle = ( status ) => {
		switch ( status ) {
			case 'all':
				return __( 'All Subscriptions', 'wp-user-frontend' );
			case 'publish':
				return __( 'Published', 'wp-user-frontend' );
			case 'draft':
				return __( 'Drafts', 'wp-user-frontend' );
			case 'trash':
				return __( 'Trash', 'wp-user-frontend' );
			default:
				return __( 'Subscriptions', 'wp-user-frontend' );
		}
	};

	return (
		<>
			<h3 className="wpuf-text-lg wpuf-font-bold wpuf-m-0">{ getStatusTitle( message?.status || 'all' ) }</h3>
			<p className="wpuf-text-sm wpuf-text-gray-500 wpuf-mb-0">{ message?.text || __( 'Explore and manage all subscriptions in one place', 'wp-user-frontend' ) }</p>
		</>
	);
};

export default ListHeader;
