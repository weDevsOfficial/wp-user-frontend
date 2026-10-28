/**
 * A short status label, in the two looks the admin screens use:
 * - `status` (default): bordered, 5px corners, for table rows (payment,
 *   coupon, subscriber status);
 * - `pill`: round, no border, for cards and headers (module state, plan).
 *
 * @since WPUF_SINCE
 */
import { cn } from '@wedevs/plugin-ui';

const TONES = {
    status: {
        success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        warning: 'border-amber-200 bg-amber-50 text-amber-700',
        danger: 'border-red-200 bg-red-50 text-red-700',
        info: 'border-blue-200 bg-blue-50 text-blue-700',
        primary: 'border-primary/30 bg-primary/5 text-primary',
        neutral: 'border-gray-200 bg-gray-50 text-gray-600',
    },
    pill: {
        success: 'bg-emerald-50 text-emerald-700',
        warning: 'bg-amber-100 text-amber-800',
        danger: 'bg-red-50 text-red-700',
        info: 'bg-blue-50 text-blue-700',
        primary: 'bg-primary/10 text-primary',
        neutral: 'bg-gray-100 text-gray-600',
    },
};

const SHAPES = {
    status: 'rounded-[5px] border border-solid px-3 py-0.5',
    pill: 'rounded-full px-2.5 py-0.5',
};

/**
 * @param {Object} props
 * @param {string} [props.tone='neutral']   success|warning|danger|info|primary|neutral
 * @param {string} [props.variant='status'] status|pill
 * @param {string} [props.className]        Extra classes (layout only).
 * @param {*}      props.children           Label.
 *
 * @return {JSX.Element} Badge.
 */
export default function Badge( { tone = 'neutral', variant = 'status', className, children, ...rest } ) {
    const tones = TONES[ variant ] || TONES.status;

    return (
        <span data-wpuf-ui="" data-wpuf-tone={ tone } className={ cn( 'inline-flex items-center gap-1 whitespace-nowrap text-xs font-medium', SHAPES[ variant ] || SHAPES.status, tones[ tone ] || tones.neutral, className ) } { ...rest }>
            { children }
        </span>
    );
}
