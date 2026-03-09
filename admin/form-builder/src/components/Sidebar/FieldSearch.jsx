import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function FieldSearch( { onSearch } ) {
    const [ value, setValue ] = useState( '' );

    const handleChange = useCallback( ( e ) => {
        const val = e.target.value;
        setValue( val );
        onSearch( val );
    }, [ onSearch ] );

    const handleClear = useCallback( () => {
        setValue( '' );
        onSearch( '' );
    }, [ onSearch ] );

    return (
        <div className="wpuf-flex wpuf-rounded-lg wpuf-bg-white wpuf-outline wpuf--outline-1 wpuf--outline-offset-1 wpuf-outline-gray-300 wpuf-border wpuf-border-gray-200 wpuf-shadow wpuf-mb-8">
            <input
                type="text"
                name="search"
                value={ value }
                onChange={ handleChange }
                className="!wpuf-border-none !wpuf-rounded-md wpuf-block wpuf-min-w-0 wpuf-grow !wpuf-px-4 !wpuf-py-1.5 !wpuf-text-base wpuf-text-gray-900 placeholder:wpuf-text-gray-400 !wpuf-ring-transparent wpuf-shadow focus:!wpuf-shadow-none"
                placeholder={ __( 'Search Field', 'wp-user-frontend' ) }
            />
            <div className="wpuf-flex wpuf-py-1.5 wpuf-pr-1.5">
                <span className="wpuf-inline-flex wpuf-items-center wpuf-rounded wpuf-px-1 wpuf-font-sans wpuf-text-xs wpuf-text-gray-400">
                    { ! value ? (
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" className="wpuf-size-5">
                            <path strokeLinecap="round" strokeLinejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    ) : (
                        <svg
                            onClick={ handleClear }
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            className="wpuf-size-5 hover:wpuf-cursor-pointer wpuf-transition-all"
                            role="button"
                            tabIndex={ 0 }
                            onKeyDown={ ( e ) => e.key === 'Enter' && handleClear() }
                        >
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    ) }
                </span>
            </div>
        </div>
    );
}
