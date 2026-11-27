/**
 * DESCRIPTION: develop's status sidebar: All / Published / Drafts / Trash with counts
 * DESCRIPTION: Stays beside the list and the editor; a click (also on the current status
 * DESCRIPTION: from the editor) goes to that status' list
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { Tabs } from '@wpuf/components';

/**
 * @param {Object}   props
 * @param {string}   props.status   Current status (all, publish, draft, trash).
 * @param {Object}   props.counts   Counts per status.
 * @param {boolean}  props.isList   True on the list (the editor re-opens the list on any click).
 * @param {Function} props.onSelect ( status ) => void
 */
const StatusSidebar = ( { status, counts, isList, onSelect } ) => {
	// develop shows a count pill only when the status has packs.
	const countOf = ( id ) => ( counts && counts[ id ] > 0 ? counts[ id ] : undefined );
	const tabs = [
		{ id: 'all', label: __( 'All Subscriptions', 'wp-user-frontend' ), count: countOf( 'all' ) },
		{ id: 'publish', label: __( 'Published', 'wp-user-frontend' ), count: countOf( 'publish' ) },
		{ id: 'draft', label: __( 'Drafts', 'wp-user-frontend' ), count: countOf( 'draft' ) },
		{ id: 'trash', label: __( 'Trash', 'wp-user-frontend' ), count: countOf( 'trash' ) },
	];

	// develop (measured): 240px column, rows 48px clear of a 2px divider, first row level with the title.
	return (
		<nav className="shrink-0 lg:w-60 lg:border-0 lg:border-r-2 lg:border-solid lg:border-gray-200 lg:pr-12 lg:pt-3">
			<Tabs
				variant="vertical"
				tabs={ tabs }
				value={ status }
				onChange={ onSelect }
				// The current status' own row does not change the value: from the editor it still goes back.
				onTabClick={ ( id ) => ! isList && id === status && onSelect( id ) }
				label={ __( 'Subscription status', 'wp-user-frontend' ) }
			/>
		</nav>
	);
};

export default StatusSidebar;
