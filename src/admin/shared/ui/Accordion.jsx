/**
 * Collapsible section (settings accordion behaviour): header with title,
 * description, optional icon and badge, chevron that turns; the content is
 * unmounted while closed. Uncontrolled (defaultOpen) or controlled
 * (open + onToggle).
 * Variant "card": develop's subscription subsection (bordered white rounded box,
 * gray header, small chevron, description as an italic sub-label).
 */
import { cn } from '@wedevs/plugin-ui';
import { useId, useState } from '@wordpress/element';
import { ChevronDown } from 'lucide-react';

/**
 * @param {Object}   props
 * @param {*}        props.title         Title.
 * @param {*}        [props.description] Text under the title.
 * @param {*}        [props.icon]        Icon before the title.
 * @param {*}        [props.badge]       After the title (e.g. <ProBadge />).
 * @param {boolean}  [props.defaultOpen] Open at first render.
 * @param {boolean}  [props.open]        Controlled open state.
 * @param {Function} [props.onToggle]    ( open ) => void
 * @param {string}   [props.variant]     default|card
 */
export default function Accordion( { title, description, icon, badge, defaultOpen = false, open: controlled, onToggle, variant = 'default', className, children } ) {
    const [ own, setOwn ] = useState( defaultOpen );
    const open = undefined === controlled ? own : controlled;
    const panelId = useId();

    const toggle = () => {
        if ( undefined === controlled ) {
            setOwn( ! open );
        }
        onToggle?.( ! open );
    };

    if ( 'card' === variant ) {
        return (
            <div data-wpuf-ui="" className={ cn( 'border border-gray-200 rounded-[10px] mt-6 mb-6 bg-white shadow-sm', className ) }>
                <h2 className="m-0">
                    <button
                        type="button"
                        aria-expanded={ open }
                        aria-controls={ panelId }
                        onClick={ toggle }
                        className={ cn( 'flex items-center justify-between w-full px-6 py-4 text-base! leading-6 font-semibold text-left rtl:text-right text-gray-900 bg-white gap-3 border-0 border-solid cursor-pointer focus-visible:ring-2 focus-visible:ring-primary/30', open ? 'rounded-t-[10px] border-b border-gray-200' : 'rounded-[10px]' ) }
                    >
                        <span className="flex min-w-0 items-center gap-3">
                            { icon && <span className="flex size-8 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">{ icon }</span> }
                            <span className="flex min-w-0 flex-col">
                                <span className="flex items-center">{ title }{ badge }</span>
                                { description && <span className="mt-0.5 text-[13px] font-normal text-gray-500">{ description }</span> }
                            </span>
                        </span>
                        <ChevronDown className={ cn( 'size-4 shrink-0 text-gray-400 transition-transform', ! open && '-rotate-90' ) } aria-hidden="true" />
                    </button>
                </h2>
                { open && <div id={ panelId }>{ children }</div> }
            </div>
        );
    }

    return (
        <div data-wpuf-ui="" className={ cn( 'border-b border-gray-200', className ) }>
            <button
                type="button"
                aria-expanded={ open }
                aria-controls={ panelId }
                onClick={ toggle }
                className="flex w-full items-center gap-3 py-3 text-left bg-transparent border-0 cursor-pointer focus-visible:ring-2 focus-visible:ring-primary/30"
            >
                { icon && <span className="flex shrink-0 items-center">{ icon }</span> }
                <span className="min-w-0 flex-1">
                    <span className="flex items-center gap-2 text-sm font-semibold text-gray-900">
                        { title }
                        { badge }
                    </span>
                    { description && <span className="mt-0.5 block text-[13px] text-gray-500">{ description }</span> }
                </span>
                <ChevronDown className={ cn( 'size-5 shrink-0 text-gray-400 transition-transform', open && 'rotate-180' ) } aria-hidden="true" />
            </button>
            { open && <div id={ panelId } className="pb-4">{ children }</div> }
        </div>
    );
}
