/**
 * SearchBar component — controlled text input with search icon.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const SearchBar = ( { value, onChange } ) => {
    return (
        <div className="wpuf-form-search-box">
            <div className="wpuf-relative">
                <input
                    type="text"
                    value={ value }
                    onChange={ ( e ) => onChange( e.target.value ) }
                    placeholder={ __( 'Search Forms', 'wp-user-frontend' ) }
                    className="wpuf-block wpuf-min-w-full !wpuf-m-0 !wpuf-leading-none !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] wpuf-max-w-full focus:!wpuf-ring-transparent"
                />
                <span className="wpuf-absolute wpuf-top-0 wpuf-right-0 wpuf-p-[10px]">
                    <svg className="wpuf-h-5 wpuf-w-5 wpuf-text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fillRule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clipRule="evenodd" />
                    </svg>
                </span>
            </div>
        </div>
    );
};

export default SearchBar;
