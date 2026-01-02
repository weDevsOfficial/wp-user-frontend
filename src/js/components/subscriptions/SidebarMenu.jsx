/**
 * DESCRIPTION: SidebarMenu component for Subscriptions page
 * DESCRIPTION: Displays navigation menu with status filters and counts
 */
import { __ } from '@wordpress/i18n';

const SidebarMenu = ( {
	currentSubscriptionStatus = 'all',
	allCount = {},
	onCheckIsDirty,
	isUnsavedPopupOpen = false,
} ) => {
	const statusItems = [
		{ key: 'all', label: __( 'All Subscriptions', 'wp-user-frontend' ) },
		{ key: 'publish', label: __( 'Published', 'wp-user-frontend' ) },
		{ key: 'draft', label: __( 'Drafts', 'wp-user-frontend' ) },
		{ key: 'trash', label: __( 'Trash', 'wp-user-frontend' ) },
		{ key: 'preferences', label: __( 'Preferences', 'wp-user-frontend' ) },
	];

	return (
		<div className={ isUnsavedPopupOpen ? 'wpuf-blur' : '' }>
			<div className="wpuf-flex wpuf-flex-col">
				<ul className="wpuf-space-y-2 wpuf-text-lg">
					{ statusItems.map( ( item ) => {
						const count = allCount[ item.key ] || 0;
						const isActive = currentSubscriptionStatus === item.key;

						return (
							<li
								key={ item.key }
								onClick={ () => onCheckIsDirty && onCheckIsDirty( item.key ) }
								className={
									'wpuf-justify-between wpuf-text-gray-700 hover:wpuf-text-primary hover:wpuf-bg-gray-50 group wpuf-flex wpuf-gap-x-3 wpuf-rounded-md wpuf-py-2 wpuf-px-[20px] wpuf-text-sm wpuf-leading-6 hover:wpuf-cursor-pointer' +
									( isActive ? ' wpuf-bg-gray-50 wpuf-text-primary' : '' )
								}
							>
								{ item.label }
								{ count > 0 && (
									<span
										className={
											'wpuf-text-sm wpuf-w-fit wpuf-px-2.5 wpuf-py-1 wpuf-rounded-full wpuf-w-max wpuf-h-max wpuf-border' +
											( isActive ? ' wpuf-border-primary' : '' )
										}
									>
										{ count }
									</span>
								) }
							</li>
						);
					} ) }
				</ul>
			</div>
		</div>
	);
};

export default SidebarMenu;
