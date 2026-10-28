/**
 * Tab bar on plugin-ui Tabs (the panels stay with the screen; keyboard
 * arrows and roles from plugin-ui / Base UI), sized like the develop tabs:
 * - variant "underline" (plugin-ui `line`; forms list status tabs,
 *   subscription details): py-4 px-1, 14px/500, 2px primary underline,
 *   32px right margin per tab, optional count pill;
 * - variant "segmented" (plugin-ui `default`; builder header): gray-100
 *   track p-2, tabs py-2 px-4 16px with an 8px right margin, active white
 *   with a soft shadow;
 * - variant "toolbar" (FlyHR list cards, forms lists since 4.2): 44px tabs,
 *   px-4 14px/500, count as "(n)" in light gray, active primary with a 2px
 *   line 8px below the tab (on the card's toolbar border).
 */
import { Tabs as PuiTabs, TabsList, TabsTrigger, cn } from '@wedevs/plugin-ui';

/**
 * @param {Object}   props
 * @param {Array}    props.tabs      [{ id, label, count?, disabled? }]
 * @param {string}   props.value     Active tab id.
 * @param {Function} props.onChange  ( id ) => void
 * @param {string}   [props.variant] underline|segmented|section (develop subscriptions form: padded tabs over a full-width line)|toolbar|pill (FlyHR page tab bar: icon, label, (count))
 * @param {string}   [props.label]   aria-label of the tab list.
 */
export default function Tabs( { tabs, value, onChange, variant = 'underline', label, className } ) {
    const segmented = 'segmented' === variant;
    const section = 'section' === variant;
    const toolbar = 'toolbar' === variant;
    const pill = 'pill' === variant;

    return (
        <PuiTabs value={ value } onValueChange={ ( next ) => onChange( next ) }>
            <TabsList
                variant={ segmented || pill ? 'default' : 'line' }
                aria-label={ label }
                className={ cn(
                    'h-auto group-data-horizontal/tabs:h-auto justify-start',
                    segmented && 'w-max gap-0 rounded-lg bg-gray-100 p-1',
                    section && 'w-full gap-0 p-0 rounded-none bg-transparent border-0 border-b border-solid border-gray-200 text-sm font-medium text-center text-gray-500',
                    toolbar && 'w-auto min-w-0 max-w-full gap-0 p-0 -mb-2 pb-2 rounded-none bg-transparent items-stretch overflow-x-auto',
                    pill && 'inline-flex h-auto! w-fit max-w-full items-center gap-1 overflow-x-auto rounded-lg border border-solid border-gray-200 bg-gray-50! p-1',
                    ! segmented && ! section && ! toolbar && ! pill && 'w-auto gap-0 p-0 rounded-none bg-transparent',
                    className
                ) }
            >
                { tabs.map( ( tab ) => (
                    <TabsTrigger
                        key={ tab.id }
                        value={ tab.id }
                        disabled={ tab.disabled }
                        className={ cn(
                            // Develop spaces tabs with a right margin on each (also the last one).
                            'flex-none h-auto gap-0 cursor-pointer shadow-none',
                            segmented && 'mr-1 last:mr-0 rounded-md border-0 px-3 py-1.5 text-sm font-medium text-gray-500 data-active:bg-white data-active:text-gray-800 data-active:drop-shadow-xs enabled:hover:bg-white enabled:hover:text-gray-800',
                            section && 'me-2 mb-[-1px] rounded-t-lg rounded-b-none border-0 border-solid px-3 py-2.5 text-sm font-medium text-gray-500 data-active:border-b-2 data-active:border-b-primary data-active:text-primary enabled:hover:border-b-2 enabled:hover:border-b-primary enabled:hover:text-primary',
                            toolbar && 'relative h-10 gap-1.5 overflow-visible rounded-none data-[slot]:border-0! data-[slot]:border-b-0! bg-transparent px-4 text-sm font-medium text-gray-500 shadow-none! outline-none! ring-0! focus:shadow-none! focus-visible:ring-0! focus-visible:outline-none! focus-visible:text-gray-900 after:hidden data-active:bg-transparent data-active:text-primary enabled:hover:text-gray-900! data-active:hover:text-primary!',
                            pill && 'group inline-flex h-auto! shrink-0 items-center gap-2 rounded-md! data-[slot]:border-0! data-[slot]:border-b-0! bg-transparent after:hidden! px-3 py-1.5 text-sm font-medium text-gray-500 ring-1 ring-transparent transition-all outline-none! focus:shadow-none! enabled:hover:text-gray-900! data-active:bg-white! data-active:text-primary! data-active:shadow-sm! data-active:ring-primary/40!',
                            ! segmented && ! section && ! toolbar && ! pill && 'mr-8 rounded-none border-0 border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 data-active:border-b-primary data-active:text-primary enabled:hover:border-b-primary enabled:hover:text-primary'
                        ) }
                    >
                        { pill && tab.icon }
                        { toolbar || pill ? <span>{ tab.label }</span> : tab.label }
                        { undefined !== tab.count && ( pill ? (
                            <span className="font-normal text-gray-400 group-data-active:text-primary/70">{ '(' + tab.count + ')' }</span>
                        ) : toolbar ? (
                            <span className="font-normal text-[#a5a5aa]">{ '(' + tab.count + ')' }</span>
                        ) : (
                            <span className="ml-3 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-900">{ tab.count }</span>
                        ) ) }
                        { toolbar && tab.id === value && (
                            <span aria-hidden="true" className="absolute inset-x-0 -bottom-2 h-0.5 bg-primary" />
                        ) }
                    </TabsTrigger>
                ) ) }
            </TabsList>
        </PuiTabs>
    );
}
