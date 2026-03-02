/**
 * DESCRIPTION: Pagination component for subscription list
 * DESCRIPTION: Handles page navigation with configurable buttons
 */
import { useState, useMemo } from '@wordpress/element';

const Pagination = ( { currentPage, count, maxVisibleButtons = 3, perPage, onChangePage } ) => {
	const [ currentPg, setCurrentPg ] = useState( currentPage );

	const totalPages = Math.ceil( count / perPage );

	const isInFirstPage = currentPg === 1;
	const isInLastPage = currentPg === totalPages;

	const startPage = useMemo( () => {
		if ( currentPg === 1 || totalPages <= maxVisibleButtons ) {
			return 1;
		}
		if ( currentPg === totalPages ) {
			return totalPages - maxVisibleButtons;
		}
		return currentPg - 1;
	}, [ currentPg, totalPages, maxVisibleButtons ] );

	const startNumber = ( currentPg - 1 ) * perPage + 1;
	const endNumber = Math.min( currentPg * perPage, count );

	const pages = useMemo( () => {
		const range = [];
		for (
			let i = startPage;
			i <= Math.min( startPage + maxVisibleButtons - 1, totalPages );
			i++
		) {
			range.push( {
				name: i,
				isDisabled: i === currentPg,
			} );
		}
		return range;
	}, [ startPage, maxVisibleButtons, totalPages, currentPg ] );

	const goToFirstPage = () => {
		setCurrentPg( 1 );
		onChangePage( 1 );
	};

	const goToLastPage = () => {
		setCurrentPg( totalPages );
		onChangePage( totalPages );
	};

	const goToPage = ( page ) => {
		setCurrentPg( page );
		onChangePage( page );
	};

	return (
		<div className="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-border-t wpuf-border-gray-200 wpuf-bg-white wpuf-py-3 wpuf-px-6 wpuf-mt-16">
			<div className="wpuf-flex wpuf-flex-1 wpuf-items-center wpuf-justify-between">
				<div>
					<p className="wpuf-text-sm wpuf-text-gray-700">
						Showing
						<span className="wpuf-font-medium"> { startNumber } </span>
						to
						<span className="wpuf-font-medium"> { endNumber } </span>
						of
						<span className="wpuf-font-medium"> { count } </span>
						results
					</p>
				</div>
				{ count > perPage && (
					<nav className="isolate wpuf-inline-flex wpuf--space-x-px wpuf-rounded-md wpuf-shadow-sm" aria-label="Pagination">
						<button
							onClick={ goToFirstPage }
							disabled={ isInFirstPage }
							className={ `wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-rounded-l-md wpuf-px-2 wpuf-py-2 wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0 ${ isInFirstPage ? 'wpuf-bg-gray-50 wpuf-cursor-not-allowed' : '' }` }
						>
							<span className="wpuf-sr-only">Previous</span>
							<svg className="wpuf-h-5 wpuf-w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fillRule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clipRule="evenodd" />
							</svg>
						</button>
						{ pages.map( ( page ) => (
							<button
								key={ page.name }
								onClick={ () => goToPage( page.name ) }
								className={ `wpuf-relative wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0 wpuf-inline-flex ${ currentPg === page.name ? 'wpuf-bg-primary wpuf-text-white hover:wpuf-bg-primaryHover' : '' }` }
							>
								{ page.name }
							</button>
						) ) }
						<button
							onClick={ goToLastPage }
							disabled={ isInLastPage }
							className={ `wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-rounded-r-md wpuf-px-2 wpuf-py-2 wpuf-text-gray-400 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-bg-gray-50 focus:wpuf-z-20 focus:outline-offset-0 ${ isInLastPage ? 'wpuf-bg-gray-50 wpuf-cursor-not-allowed' : '' }` }
						>
							<span className="wpuf-sr-only">Next</span>
							<svg className="wpuf-h-5 wpuf-w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
								<path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
							</svg>
						</button>
					</nav>
				) }
			</div>
		</div>
	);
};

export default Pagination;
