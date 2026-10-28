/**
 * The button of the AI actions (AI Form Builder on the forms lists, AI
 * generated options in the builder): white, with the AI gradient (the
 * yellow / magenta / blue of the AI icon) on its border, sparkle icon and
 * label, so every AI entry point looks the same.
 *
 * @since WPUF_SINCE
 */
import { useInstanceId } from '@wordpress/compose';
import { cn } from '@wedevs/plugin-ui';

const GRADIENT = 'linear-gradient(135deg, #FFB800 0%, #D500FF 38%, #0082FF 100%)';
const TEXT_GRADIENT = 'linear-gradient(135deg, #B100E8 0%, #0070E0 100%)';

/** The sparkles of the AI icon, stroked with the gradient. */
function Sparkles( { size, id } ) {
    return (
        <svg width={ size } height={ size } viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false" className="shrink-0">
            <defs>
                <linearGradient id={ id } x1="2" y1="2" x2="18" y2="18" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stopColor="#FFB800" />
                    <stop offset="0.38" stopColor="#D500FF" />
                    <stop offset="1" stopColor="#0082FF" />
                </linearGradient>
            </defs>
            <g stroke={ `url(#${ id })` } strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
                <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" />
                <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" />
                <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" />
            </g>
        </svg>
    );
}

/**
 * @param {Object}   props
 * @param {string}   props.children  Label.
 * @param {Function} props.onClick
 * @param {string}   [props.size]    'lg' (page actions, 40px) or 'sm' (inline, 28px).
 * @param {string}   [props.title]
 * @param {string}   [props.className]
 */
export default function AiButton( { children, onClick, size = 'lg', title, className, ...rest } ) {
    const gradientId = useInstanceId( AiButton, 'wpuf-ai-gradient' );
    const small = 'sm' === size;

    return (
        <button
            type="button"
            title={ title }
            onClick={ onClick }
            // The gradient border: a 1px gradient frame around a white face.
            style={ { backgroundImage: GRADIENT } }
            className={ cn( 'group inline-flex shrink-0 rounded-md p-px shadow-xs transition-shadow hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fuchsia-400/50 cursor-pointer border-0', className ) }
            { ...rest }
        >
            <span className={ cn( 'inline-flex items-center gap-1.5 rounded-[5px] bg-white transition-colors group-hover:bg-fuchsia-50/60', small ? 'h-[26px] px-2 text-xs' : 'h-[38px] px-4 text-sm' ) }>
                <Sparkles size={ small ? 14 : 16 } id={ gradientId } />
                <span className="font-semibold" style={ { backgroundImage: TEXT_GRADIENT, WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent' } }>{ children }</span>
            </span>
        </button>
    );
}
