/**
 * Develop's AI form builder icons (inline SVG, same paths).
 *
 * @since WPUF_SINCE
 */

/**
 * WPUF logo badge (header and AI messages).
 *
 * @param {Object} props
 * @param {string} [props.className] Classes.
 */
export function LogoBadge( { className } ) {
    return (
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" className={ className } aria-hidden="true">
            <circle cx="18" cy="18" r="18" fill="#10B981" />
            <path d="M19.8822 18.2098V20.3821C19.8822 21.6281 18.8677 22.6426 17.6217 22.6426C16.3757 22.6426 15.3612 21.6281 15.3612 20.3821V18.2098H12.8747V20.3821C12.8747 23.001 15.0029 25.1292 17.6217 25.1292C20.2406 25.1292 22.3688 23.001 22.3688 20.3821V18.2098H19.8822Z" fill="white" />
            <path d="M15.368 11H9L9.74982 13.4865H15.368V11Z" fill="white" />
            <path d="M23.8896 11H19.8924V13.4865H23.8896C24.2315 13.4865 24.5127 13.7622 24.5127 14.1096C24.5127 14.4514 24.237 14.7271 23.8896 14.7271H19.8924V17.2136H23.8896C25.6043 17.2136 26.9992 15.8187 26.9992 14.104C26.9992 12.3949 25.6043 11 23.8896 11Z" fill="white" />
            <path d="M15.3767 14.7296H10.2548L11.0046 17.2161H15.3767V14.7296Z" fill="white" />
        </svg>
    );
}

/**
 * "Generate Form" sparkles.
 */
export function SparklesIcon() {
    return (
        <svg className="size-5" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/**
 * "Regenerate" arrows.
 */
export function RegenerateIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M13.3523 7.79032H17.5128V7.78884M2.48682 16.3703V12.2098M2.48682 12.2098L6.64735 12.2098M2.48682 12.2098L5.13756 14.8622C5.963 15.6892 7.01055 16.3166 8.22034 16.6408C11.8879 17.6235 15.6577 15.447 16.6405 11.7794M3.35898 8.22068C4.3417 4.5531 8.11152 2.37659 11.7791 3.35932C12.9889 3.68348 14.0365 4.31091 14.8619 5.1379L17.5128 7.78884M17.5128 3.62982V7.78884" stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/**
 * "Edit with Builder" pencil.
 */
export function PencilIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M16.8898 3.11019L17.4201 2.57986V2.57986L16.8898 3.11019ZM5.41667 17.5296V18.2796C5.61558 18.2796 5.80634 18.2005 5.947 18.0599L5.41667 17.5296ZM2.5 17.5296H1.75C1.75 17.9438 2.08579 18.2796 2.5 18.2796V17.5296ZM2.5 14.5537L1.96967 14.0233C1.82902 14.164 1.75 14.3548 1.75 14.5537H2.5ZM13.9435 3.11019L14.4738 3.64052C14.9945 3.11983 15.8387 3.11983 16.3594 3.64052L16.8898 3.11019L17.4201 2.57986C16.3136 1.47338 14.5196 1.47338 13.4132 2.57986L13.9435 3.11019ZM16.8898 3.11019L16.3594 3.64052C16.8801 4.16122 16.8801 5.00544 16.3594 5.52614L16.8898 6.05647L17.4201 6.5868C18.5266 5.48032 18.5266 3.68635 17.4201 2.57986L16.8898 3.11019ZM16.8898 6.05647L16.3594 5.52614L4.88634 16.9992L5.41667 17.5296L5.947 18.0599L17.4201 6.5868L16.8898 6.05647ZM5.41667 17.5296V16.7796H2.5V17.5296V18.2796H5.41667V17.5296ZM13.9435 3.11019L13.4132 2.57986L1.96967 14.0233L2.5 14.5537L3.03033 15.084L14.4738 3.64052L13.9435 3.11019ZM2.5 14.5537H1.75V17.5296H2.5H3.25V14.5537H2.5ZM12.6935 4.36019L12.1632 4.89052L15.1094 7.8368L15.6398 7.30647L16.1701 6.77614L13.2238 3.82986L12.6935 4.36019Z" fill="white" />
        </svg>
    );
}

/**
 * Chat send arrow.
 */
export function SendIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M3.99972 10L1.2688 1.12451C7.88393 3.04617 14.0276 6.07601 19.4855 9.99974C14.0276 13.9235 7.884 16.9535 1.26889 18.8752L3.99972 10ZM3.99972 10L11.5 10" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/**
 * Checkpoint restore arrow.
 */
export function RestoreIcon() {
    return (
        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M3 10C3 13.866 6.13401 17 10 17C13.866 17 17 13.866 17 10C17 6.13401 13.866 3 10 3C7.07421 3 4.56316 4.77516 3.52779 7.28M3 3V7.5H7.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/**
 * The three dots shown while the assistant replies.
 */
export function TypingDots() {
    return (
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" className="inline-block" role="img" aria-label="…">
            <path d="M16 12C17.1046 12 18 11.1046 18 10C18 8.89543 17.1046 8 16 8C14.8954 8 14 8.89543 14 10C14 11.1046 14.8954 12 16 12Z" fill="#6EE7B7" />
            <path d="M10 12C11.1046 12 12 11.1046 12 10C12 8.89543 11.1046 8 10 8C8.89543 8 8 8.89543 8 10C8 11.1046 8.89543 12 10 12Z" fill="#34D399" />
            <path d="M6 10C6 11.1046 5.10457 12 4 12C2.89543 12 2 11.1046 2 10C2 8.89543 2.89543 8 4 8C5.10457 8 6 8.89543 6 10Z" fill="#059669" />
        </svg>
    );
}

/**
 * Select caret.
 */
export function CaretIcon() {
    return (
        <svg className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-500" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

/**
 * Small round spinner (Tailwind animate-spin).
 *
 * @param {Object} props
 * @param {string} [props.className] Classes.
 */
export function Spinner( { className = 'size-4 text-gray-500' } ) {
    return (
        <svg className={ `animate-spin ${ className }` } xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
    );
}
