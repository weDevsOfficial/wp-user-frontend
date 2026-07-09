/**
 * Pagination component — previous/next buttons and page number range.
 *
 * @since WPUF_SINCE
 */
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const Pagination = ( { currentPage, totalPages, onPageChange } ) => {
    const paginationRange = useMemo( () => {
        if ( totalPages <= 1 ) {
            return [];
        }

        const delta = 2;
        const start = Math.max( 1, currentPage - delta );
        const end = Math.min( totalPages, currentPage + delta );
        const range = [];

        for ( let i = start; i <= end; i++ ) {
            range.push( i );
        }

        return range;
    }, [ currentPage, totalPages ] );

    if ( totalPages <= 1 ) {
        return null;
    }

    const changePage = ( page ) => {
        if ( page < 1 || page > totalPages || page === currentPage ) {
            return;
        }
        onPageChange( page );
    };

    return (
        <div className="wpuf-flex wpuf-items-center wpuf-justify-center wpuf-mt-20">
            <nav className="wpuf-flex wpuf-items-center wpuf-w-full">
                <div>
                    <button
                        onClick={ () => changePage( currentPage - 1 ) }
                        disabled={ currentPage === 1 }
                        className={
                            'wpuf-mr-3 wpuf-rounded-md wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-text-primary' +
                            ( currentPage === 1 ? ' wpuf-cursor-not-allowed wpuf-opacity-50' : '' )
                        }
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fillRule="evenodd" clipRule="evenodd" d="M7.70711 14.7071C7.31658 15.0976 6.68342 15.0976 6.2929 14.7071L2.29289 10.7071C1.90237 10.3166 1.90237 9.68342 2.29289 9.29289L6.29289 5.29289C6.68342 4.90237 7.31658 4.90237 7.70711 5.29289C8.09763 5.68342 8.09763 6.31658 7.70711 6.70711L5.41421 9L17 9C17.5523 9 18 9.44771 18 10C18 10.5523 17.5523 11 17 11L5.41421 11L7.70711 13.2929C8.09763 13.6834 8.09763 14.3166 7.70711 14.7071Z" fill="#94A3B8" />
                        </svg>
                        &nbsp;
                        { __( 'Previous', 'wp-user-frontend' ) }
                    </button>
                </div>

                <div className="wpuf-flex wpuf-items-center">
                    { paginationRange.map( ( page ) => (
                        <span
                            key={ page }
                            onClick={ () => changePage( page ) }
                            className={
                                'wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-cursor-pointer wpuf-mx-1 wpuf-border-t-2 hover:wpuf-border-primary wpuf-transition-all ' +
                                ( page === currentPage
                                    ? 'wpuf-text-primary wpuf-border-primary'
                                    : 'wpuf-text-gray-500 wpuf-border-transparent' )
                            }
                        >
                            { page }
                        </span>
                    ) ) }
                </div>

                <div>
                    <button
                        onClick={ () => changePage( currentPage + 1 ) }
                        disabled={ currentPage === totalPages }
                        className={
                            'wpuf-ml-3 wpuf-rounded-md wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-text-primary' +
                            ( currentPage === totalPages ? ' wpuf-cursor-not-allowed wpuf-opacity-50' : '' )
                        }
                    >
                        { __( 'Next', 'wp-user-frontend' ) }
                        &nbsp;
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fillRule="evenodd" clipRule="evenodd" d="M12.2929 5.29289C12.6834 4.90237 13.3166 4.90237 13.7071 5.29289L17.7071 9.29289C18.0976 9.68342 18.0976 10.3166 17.7071 10.7071L13.7071 14.7071C13.3166 15.0976 12.6834 15.0976 12.2929 14.7071C11.9024 14.3166 11.9024 13.6834 12.2929 13.2929L14.5858 11H3C2.44772 11 2 10.5523 2 10C2 9.44772 2.44772 9 3 9H14.5858L12.2929 6.70711C11.9024 6.31658 11.9024 5.68342 12.2929 5.29289Z" fill="#94A3B8" />
                        </svg>
                    </button>
                </div>
            </nav>
        </div>
    );
};

export default Pagination;
