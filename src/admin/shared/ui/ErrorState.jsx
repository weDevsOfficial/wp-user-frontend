/**
 * Error state for a screen part that failed to load: title, message and an
 * optional retry button (shared Button). role="alert".
 */
import { __ } from '@wordpress/i18n';
import { cn } from '@wedevs/plugin-ui';

import Button from './Button';

/**
 * @param {Object}   props
 * @param {*}        [props.title]   Title (default "Something went wrong").
 * @param {*}        [props.message] Details (e.g. the normalized API error message).
 * @param {Function} [props.onRetry] Show a "Try again" button.
 */
export default function ErrorState( { title, message, onRetry, className } ) {
    return (
        <div data-wpuf-ui="" role="alert" className={ cn( 'flex flex-col items-center text-center bg-white px-6 py-16', className ) }>
            <span className="flex size-14 items-center justify-center rounded-full bg-red-50" aria-hidden="true">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#DC2626" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <h2 className="m-0 mt-6 text-lg text-gray-800">{ title || __( 'Something went wrong', 'wp-user-frontend' ) }</h2>
            { message && <p className="m-0 mt-3 max-w-md text-sm text-gray-500">{ message }</p> }
            { onRetry && <Button variant="secondary" className="mt-8" onClick={ onRetry }>{ __( 'Try again', 'wp-user-frontend' ) }</Button> }
        </div>
    );
}
