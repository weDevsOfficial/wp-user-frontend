/**
 * Tooltip on hover and keyboard focus (replaces the daisyUI `wpuf-tooltip`
 * data-tip, shown on top by default). HelpTip is the "i" icon with a tip.
 */
import { Tooltip as PuiTooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@wedevs/plugin-ui';

/**
 * @param {Object} props
 * @param {*}      props.content  Tip text.
 * @param {*}      props.children The element the tip belongs to.
 * @param {string} [props.side]   top|bottom|left|right (default top).
 */
export default function Tooltip( { content, children, side = 'top' } ) {
    if ( ! content ) {
        return children;
    }

    return (
        <TooltipProvider delay={ 100 }>
            <PuiTooltip>
                <TooltipTrigger render={ <span className="inline-flex" tabIndex={ 0 } /> }>{ children }</TooltipTrigger>
                <TooltipContent side={ side } className="max-w-xs bg-gray-900 text-white text-xs rounded-md px-2 py-1">
                    { content }
                </TooltipContent>
            </PuiTooltip>
        </TooltipProvider>
    );
}

/**
 * @param {Object} props
 * @param {*}      props.text Tip text; nothing renders without it.
 */
export function HelpTip( { text } ) {
    if ( ! text ) {
        return null;
    }

    return (
        <Tooltip content={ text }>
            <svg className="ml-2 size-4 text-gray-400" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" aria-label={ String( text ) }>
                <path strokeLinecap="round" strokeLinejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
        </Tooltip>
    );
}
