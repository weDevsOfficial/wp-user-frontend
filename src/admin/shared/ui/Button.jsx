/**
 * The one button of the WPUF admin screens (design-tokens.md §Buttons).
 *
 * Variants: primary, secondary, danger-outline (secondary with red text and border:
 * bulk delete / reject / disconnect), link (tertiary), destructive, icon.
 * Sizes: xs (28px, inline row actions), sm (32px, 6px 12px, 13px/500), md (36px, 8px 16px, 14px/500), lg (40px, 20px sides, page actions, as FlyHR).
 * `href` renders a link with the same look (Preview, Edit with Builder, upgrade links).
 * No outer margin: spacing comes from the group gap or the container.
 * Disabled / busy buttons do not change on hover (develop daisyUI buttons).
 */
import { forwardRef } from '@wordpress/element';
import { Button as PuiButton, Spinner, cn } from '@wedevs/plugin-ui';

const VARIANTS = {
    primary: { pui: 'default', className: 'bg-primary text-primary-foreground border-transparent enabled:hover:bg-[#10b981] enabled:active:bg-[#10b981]' },
    secondary: { pui: 'outline', className: 'bg-white text-gray-700 border border-gray-300 enabled:hover:bg-gray-50 enabled:hover:text-gray-700' },
    'danger-outline': { pui: 'outline', className: 'bg-white border border-red-600 text-red-600 enabled:hover:border-red-600 enabled:hover:bg-red-50 enabled:hover:text-red-600' },
    link: { pui: 'link', className: 'text-primary enabled:hover:underline p-0 h-auto border-0' },
    destructive: { pui: 'destructive', className: 'bg-red-600 text-white border-transparent enabled:hover:bg-red-700' },
    icon: { pui: 'ghost', className: 'text-gray-500 enabled:hover:text-gray-700 enabled:hover:bg-gray-100' },
};

const SIZES = {
    xs: 'h-7 px-2.5 py-1 text-xs font-medium gap-1 rounded-md',
    sm: 'h-8 px-3 py-1.5 text-[13px] font-medium gap-1.5 rounded-md',
    md: 'h-9 px-4 py-2 text-sm font-medium gap-2 rounded-md',
    // Page-level actions (FlyHR page header buttons).
    lg: 'h-10 px-5 py-2 text-sm font-medium leading-5 gap-2 rounded-md shadow-sm',
};

const ICON_SIZES = { xs: 'size-7 p-0 rounded-md', sm: 'size-8 p-0 rounded-md', md: 'size-9 p-0 rounded-md' };

/**
 * @param {Object}  props
 * @param {string}  [props.variant='primary'] primary|secondary|danger-outline|link|destructive|icon
 * @param {string}  [props.size='md']         xs|sm|md|lg
 * @param {boolean} [props.busy]              Show a spinner and block clicks.
 * @param {string}  [props.href]              Render a link (a disabled or busy one is a button).
 * @param {string}  [props.className]         Extra classes (layout only).
 */
// forwardRef: menus and popovers can use a Button as their trigger (ActionMenu `trigger`).
const Button = forwardRef( function Button( { variant = 'primary', size = 'md', busy = false, disabled, className, children, href, ...props }, ref ) {
    const look = VARIANTS[ variant ] || VARIANTS.primary;
    const sizing = 'icon' === variant ? ICON_SIZES[ size ] || ICON_SIZES.md : SIZES[ size ] || SIZES.md;
    const element = href && ! disabled && ! busy
        ? { render: <a href={ href } />, nativeButton: false }
        : { type: 'button' };

    return (
        <PuiButton
            ref={ ref }
            { ...element }
            variant={ look.pui }
            disabled={ disabled || busy }
            aria-busy={ busy || undefined }
            className={ cn( 'shadow-none cursor-pointer no-underline disabled:opacity-60 disabled:cursor-not-allowed', sizing, look.className, className ) }
            { ...props }
        >
            { busy && <Spinner className="size-4" /> }
            { children }
        </PuiButton>
    );
} );

export default Button;
