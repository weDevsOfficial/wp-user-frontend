/**
 * DESCRIPTION: ContentHeader component for Subscriptions page
 * DESCRIPTION: Displays title and "Add Subscription" button
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wpuf/components';

const ContentHeader = ( { currentSubscriptionStatus = 'all', allCount = {}, onAddSubscription } ) => {
	// Same rule as the Vue ContentHeader: no header button in Trash, nor on an
	// empty "All" list (the empty state carries its own Add button).
	const shouldShowButton = ! (
		currentSubscriptionStatus === 'trash' ||
		( currentSubscriptionStatus === 'all' && 0 === ( allCount.all || 0 ) )
	);

	return (
		<div className="flex items-center justify-between mt-6 leading-none">
			<h3 className="text-2xl font-bold leading-8 text-gray-900 my-0">
				{ __( 'Subscriptions', 'wp-user-frontend' ) }
			</h3>

			<div className="flex justify-end h-max">
				{ shouldShowButton && onAddSubscription && (
					<Button size="lg" onClick={ onAddSubscription }>
						<svg className="size-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path fillRule="evenodd" clipRule="evenodd" d="M12 21.6C17.3019 21.6 21.6 17.3019 21.6 12C21.6 6.69807 17.3019 2.4 12 2.4C6.69806 2.4 2.39999 6.69807 2.39999 12C2.39999 17.3019 6.69806 21.6 12 21.6ZM13.2 8.4C13.2 7.73726 12.6627 7.2 12 7.2C11.3372 7.2 10.8 7.73726 10.8 8.4V10.8H8.39999C7.73725 10.8 7.19999 11.3373 7.19999 12C7.19999 12.6627 7.73725 13.2 8.39999 13.2H10.8V15.6C10.8 16.2627 11.3372 16.8 12 16.8C12.6627 16.8 13.2 16.2627 13.2 15.6V13.2H15.6C16.2627 13.2 16.8 12.6627 16.8 12C16.8 11.3373 16.2627 10.8 15.6 10.8H13.2V8.4Z" fill="#FFF"/>
						</svg>
						{ __( 'Add Subscription', 'wp-user-frontend' ) }
					</Button>
				) }
			</div>
		</div>
	);
};

export default ContentHeader;
