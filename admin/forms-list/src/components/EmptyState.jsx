/**
 * EmptyState component — shown when there are no forms to display.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const AISvgIcon = () => (
    <svg className="wpuf-w-5 wpuf-h-5 wpuf-pr-1" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
);

const EmptyState = ( { type, onAddNew, onAIFormBuilder } ) => {
    if ( type === 'search' ) {
        return (
            <div className="wpuf-text-center">
                <h2 className="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8">
                    { __( 'No forms found matching your search!', 'wp-user-frontend' ) }
                </h2>
            </div>
        );
    }

    if ( type === 'tab-empty' ) {
        return (
            <div className="wpuf-grid wpuf-min-h-full wpuf-bg-white wpuf-px-6 wpuf-py-24 sm:wpuf-py-32 lg:wpuf-px-8">
                <div className="wpuf-flex wpuf-flex-col wpuf-items-center">
                    <h2 className="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8">
                        { __( 'No Items Here!', 'wp-user-frontend' ) }
                    </h2>
                </div>
            </div>
        );
    }

    // type === 'empty'
    const blankImg = window.wpuf_admin_script.asset_url + '/images/form-blank-state.svg';

    return (
        <div className="wpuf-grid wpuf-min-h-full wpuf-bg-white wpuf-px-6 wpuf-py-24 sm:wpuf-py-32 lg:wpuf-px-8">
            <div className="wpuf-flex wpuf-flex-col wpuf-items-center">
                <img src={ blankImg } alt="" />
                <h2 className="wpuf-text-lg wpuf-text-gray-800 wpuf-mt-8">
                    { __( 'No Post Forms Created Yet', 'wp-user-frontend' ) }
                </h2>
                <p className="wpuf-text-sm wpuf-text-gray-500 wpuf-mt-8 wpuf-mb-10">
                    { __( 'Start building a post form to let users submit content from the frontend.', 'wp-user-frontend' ) }
                </p>

                <div className="wpuf-flex wpuf-gap-3 wpuf-justify-center">
                    <button
                        type="button"
                        onClick={ onAIFormBuilder }
                        className="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white focus:wpuf-from-purple-700 focus:wpuf-to-blue-700 focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer wpuf-inline-flex wpuf-items-center"
                    >
                        <AISvgIcon />
                        { __( 'AI Form Builder', 'wp-user-frontend' ) }
                    </button>
                    <button
                        type="button"
                        onClick={ onAddNew }
                        className="new-wpuf-form wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer"
                    >
                        <span className="dashicons dashicons-plus-alt2"></span>
                        &nbsp;
                        { __( 'Add New ', 'wp-user-frontend' ) }
                    </button>
                </div>
            </div>
        </div>
    );
};

export default EmptyState;
