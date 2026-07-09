import { useState } from '@wordpress/element';
import ProBadge from './ProBadge';

/**
 * Collapsible accordion row. Matches the WPUF Redesign Figma Email tab: a
 * divider-separated row with an optional 40×40 leading icon square, a title +
 * description, and a trailing chevron. Used to group the Email tab's settings
 * (Guest Email + the Pro email types).
 */
export default function Accordion( { title, desc, icon = null, isPro = false, defaultOpen = false, children } ) {
    const [ open, setOpen ] = useState( defaultOpen );

    return (
        <div className="wpuf-border-b wpuf-border-gray-200">
            <button
                type="button"
                onClick={ () => setOpen( ( v ) => ! v ) }
                className="wpuf-flex wpuf-w-full wpuf-items-center wpuf-gap-3 wpuf-py-4 wpuf-text-left"
            >
                { icon && <span className="wpuf-flex wpuf-shrink-0 wpuf-items-center">{ icon }</span> }
                <span className="wpuf-min-w-0 wpuf-flex-1">
                    <span className="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-text-base wpuf-font-semibold wpuf-text-gray-900">
                        { title }
                        { isPro && <ProBadge /> }
                    </span>
                    { desc && <span className="wpuf-mt-0.5 wpuf-block wpuf-text-sm wpuf-text-gray-500">{ desc }</span> }
                </span>
                <svg
                    className={ `wpuf-h-5 wpuf-w-5 wpuf-shrink-0 wpuf-text-gray-400 wpuf-transition-transform ${ open ? 'wpuf-rotate-180' : '' }` }
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clipRule="evenodd" />
                </svg>
            </button>
            { open && <div className="wpuf-pb-4">{ children }</div> }
        </div>
    );
}
