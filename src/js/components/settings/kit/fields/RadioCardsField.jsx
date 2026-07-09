import { useCallback } from '@wordpress/element';
import HelpTextIcon from './HelpTextIcon';
import ProBadge from '../ProBadge';

const IS_PRO = ( window.wpuf_settings || {} ).is_pro;
const UPGRADE_URL =
    ( ( window.wpuf_settings || {} ).upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/' ) +
    '?utm_source=wpuf-settings&utm_medium=gateway-card';

/**
 * Card selector for the legacy `gateway_selector` type (payment / SMS gateways).
 * Each option value can be a string OR an object ({ admin_label, icon, … }).
 * Stores an ARRAY of the enabled gateway keys — same shape the legacy screen used.
 */
const labelOf = ( opt, key ) => {
    if ( typeof opt === 'string' ) {
        return opt;
    }
    if ( opt && typeof opt === 'object' ) {
        return opt.admin_label || opt.label || opt.name || key;
    }
    return key;
};

// Fallback inline SVG logos by gateway key, used when the schema option has no icon.
const GATEWAY_SVG = {
    paypal: '<svg width="26" height="26" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#003087" d="M7.08 21.34l.45-2.86-.99-.02H1.81L4.96 1.7a.26.26 0 0 1 .26-.22h7.96c2.64 0 4.46.55 5.42 1.63.45.51.73 1.04.88 1.63.15.62.15 1.36 0 2.26l-.01.07v.58l.45.26c.38.2.68.43.91.7.38.44.63 1 .73 1.66.11.68.07 1.49-.1 2.4-.2 1.06-.53 1.98-.97 2.73a5.5 5.5 0 0 1-1.54 1.69c-.59.42-1.29.73-2.08.94-.77.2-1.65.3-2.62.3h-.62c-.45 0-.88.16-1.22.45-.34.29-.56.69-.63 1.13l-.05.25-.79 5.01-.04.18c-.01.06-.03.09-.05.11a.14.14 0 0 1-.09.03H7.08z"/><path fill="#0070E0" d="M19.89 7.14c-.02.15-.05.31-.08.47-1 5.13-4.42 6.9-8.78 6.9H8.81a1.08 1.08 0 0 0-1.07.91l-1.14 7.22-.32 2.05a.57.57 0 0 0 .56.66h3.94c.47 0 .87-.34.94-.8l.04-.2.74-4.71.05-.26c.07-.47.47-.81.94-.81h.59c3.82 0 6.8-1.55 7.68-6.03.36-1.87.17-3.43-.79-4.53a3.8 3.8 0 0 0-1.08-.84z"/></svg>',
    bank: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>',
    stripe: '<svg width="46" height="20" viewBox="0 0 60 25" xmlns="http://www.w3.org/2000/svg"><path fill="#635BFF" d="M59.64 13.07c0-4.27-2.07-7.64-6.02-7.64s-6.35 3.37-6.35 7.61c0 5.02 2.84 7.55 6.89 7.55 1.98 0 3.47-.45 4.6-1.08v-3.32c-1.13.57-2.43.92-4.08.92-1.62 0-3.05-.57-3.23-2.53h8.14c0-.22.05-1.09.05-1.51zm-8.23-1.58c0-1.88 1.15-2.67 2.2-2.67 1.02 0 2.1.79 2.1 2.67h-4.3zM41.95 5.43c-1.63 0-2.68.77-3.26 1.3l-.22-1.03h-3.66v19.4l4.16-.88.02-4.71c.6.43 1.48 1.05 2.95 1.05 2.98 0 5.7-2.4 5.7-7.69-.01-4.84-2.76-7.44-5.69-7.44zm-1 11.44c-.99 0-1.57-.35-1.97-.79l-.02-6.22c.43-.48 1.03-.82 1.99-.82 1.52 0 2.57 1.71 2.57 3.9 0 2.25-1.04 3.93-2.57 3.93zM28.24 4.44l4.18-.9V.16l-4.18.89zM28.24 5.71h4.18v14.6h-4.18zM23.76 6.94l-.27-1.23h-3.59v14.6h4.16v-9.9c.98-1.29 2.65-1.05 3.16-.87V5.71c-.53-.2-2.48-.56-3.46 1.23zM15.16 2.09l-4.06.86-.02 13.32c0 2.46 1.85 4.27 4.31 4.27 1.36 0 2.36-.25 2.91-.55v-3.38c-.53.21-3.16.98-3.16-1.49V9.26h3.16V5.71h-3.16l.02-3.62zM4.21 9.76c0-.65.53-.9 1.41-.9 1.26 0 2.85.38 4.11 1.06V6c-1.37-.55-2.73-.76-4.11-.76C2.26 5.24 0 7 0 9.94c0 4.59 6.32 3.86 6.32 5.84 0 .77-.67 1.02-1.6 1.02-1.37 0-3.13-.56-4.52-1.32v3.96c1.54.66 3.1.94 4.52.94 3.44 0 5.81-1.7 5.81-4.68-.02-4.96-6.32-4.08-6.32-5.94z"/></svg>',
    openai: '<svg width="26" height="26" viewBox="0 0 24 24" fill="#412991" xmlns="http://www.w3.org/2000/svg"><path d="M22.28 9.82a5.98 5.98 0 0 0-.52-4.91 6.05 6.05 0 0 0-6.51-2.9A6.07 6.07 0 0 0 4.98 4.18a5.99 5.99 0 0 0-4 2.9 6.06 6.06 0 0 0 .75 7.1 5.98 5.98 0 0 0 .51 4.91 6.05 6.05 0 0 0 6.52 2.9 5.98 5.98 0 0 0 4.51 2.01 6.05 6.05 0 0 0 5.77-4.21 5.99 5.99 0 0 0 4-2.9 6.06 6.06 0 0 0-.76-7.07zM13.27 22.4a4.49 4.49 0 0 1-2.88-1.04l.14-.08 4.78-2.76a.79.79 0 0 0 .39-.68v-6.74l2.02 1.17a.07.07 0 0 1 .04.05v5.58a4.5 4.5 0 0 1-4.49 4.5zM3.6 18.27a4.47 4.47 0 0 1-.54-3.01l.14.08 4.78 2.76a.78.78 0 0 0 .78 0l5.84-3.37v2.33a.08.08 0 0 1-.03.06l-4.83 2.79a4.5 4.5 0 0 1-6.14-1.64zM2.34 7.9a4.49 4.49 0 0 1 2.34-1.97v5.68a.78.78 0 0 0 .39.68l5.81 3.35-2.02 1.17a.07.07 0 0 1-.07 0l-4.83-2.79A4.5 4.5 0 0 1 2.34 7.9zm16.6 3.86l-5.84-3.37 2.02-1.17a.07.07 0 0 1 .07 0l4.83 2.79a4.49 4.49 0 0 1-.68 8.1v-5.68a.78.78 0 0 0-.4-.67zm2.01-3.03l-.14-.08-4.78-2.76a.78.78 0 0 0-.78 0L9.41 9.26V6.93a.07.07 0 0 1 .03-.06l4.83-2.79a4.49 4.49 0 0 1 6.68 4.65zM8.31 12.86l-2.02-1.17a.07.07 0 0 1-.04-.05V6.06a4.49 4.49 0 0 1 7.37-3.45l-.14.08L8.7 5.45a.79.79 0 0 0-.39.68v6.73zm1.1-2.36L12 9.01l2.6 1.5v3l-2.6 1.5-2.6-1.5v-3z"/></svg>',
    anthropic: '<svg width="26" height="26" viewBox="0 0 24 24" fill="#D97757" xmlns="http://www.w3.org/2000/svg"><path d="M15.5 4h-3.06l5.5 16h3.06L15.5 4zM7.56 4L2 20h3.12l1.13-3.36h5.84L13.25 20h3.12L10.81 4H7.56zm-.32 9.86l1.9-5.66 1.92 5.66H7.24z"/></svg>',
    google: '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.3 9.14 5.38 12 5.38z"/></svg>',
};

const iconOf = ( opt, key ) => {
    if ( opt && typeof opt === 'object' && typeof opt.icon === 'string' && opt.icon ) {
        return opt.icon;
    }
    return GATEWAY_SVG[ key ] || '';
};

export default function RadioCardsField( { field, name, value, onChange, single = false } ) {
    const options = field.options || {};

    // Single-select stores a string; multi-select stores the legacy associative
    // shape { slug: slug }. Derive the selected key list for either case.
    const selected = single
        ? ( value ? [ value ] : ( field.default ? [ field.default ] : [] ) )
        : Array.isArray( value )
            ? value
            : ( value && typeof value === 'object' ? Object.keys( value ) : ( field.default || [] ) );

    const toggle = useCallback(
        ( key ) => {
            if ( single ) {
                onChange( name, key );
                return;
            }
            const set = new Set( selected );
            if ( set.has( key ) ) {
                set.delete( key );
            } else {
                set.add( key );
            }
            // Preserve the legacy associative shape { slug: slug } so PHP code
            // that does array_keys()/isset() on active gateways keeps working.
            const next = {};
            set.forEach( ( k ) => {
                next[ k ] = k;
            } );
            onChange( name, next );
        },
        [ name, onChange, selected, single ]
    );

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">{ field.label }</label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className="wpuf-mt-1 wpuf-flex wpuf-flex-wrap wpuf-gap-3">
                { Object.entries( options ).map( ( [ key, opt ] ) => {
                    const isOn = selected.includes( key );
                    const icon = iconOf( opt, key );
                    const isProPreview = opt && typeof opt === 'object' && opt.is_pro_preview && ! IS_PRO;

                    if ( isProPreview ) {
                        // Pro-only gateway (e.g. Credit Card / Stripe): upsell card.
                        return (
                            <a
                                key={ key }
                                href={ UPGRADE_URL }
                                target="_blank"
                                rel="noopener noreferrer"
                                title={ labelOf( opt, key ) }
                                className="wpuf-relative wpuf-flex wpuf-min-w-[150px] wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-gap-2 wpuf-rounded-lg wpuf-border wpuf-border-dashed wpuf-border-gray-300 wpuf-bg-gray-50 wpuf-px-5 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-opacity-80 hover:wpuf-border-sky-400 hover:wpuf-opacity-100"
                            >
                                <span className="wpuf-absolute wpuf-right-2 wpuf-top-2"><ProBadge /></span>
                                { icon ? (
                                    icon.startsWith( 'http' ) ? (
                                        <img src={ icon } alt="" className="wpuf-h-7 wpuf-object-contain wpuf-opacity-60" />
                                    ) : (
                                        // eslint-disable-next-line react/no-danger
                                        <span className="wpuf-opacity-60" dangerouslySetInnerHTML={ { __html: icon } } />
                                    )
                                ) : null }
                                <span>{ labelOf( opt, key ) }</span>
                            </a>
                        );
                    }

                    return (
                        <button
                            type="button"
                            key={ key }
                            onClick={ () => toggle( key ) }
                            className={ `wpuf-relative wpuf-flex wpuf-min-w-[150px] wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-gap-2 wpuf-rounded-lg wpuf-border wpuf-px-5 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-transition ${
                                isOn
                                    ? 'wpuf-border-primary wpuf-ring-1 wpuf-ring-primary wpuf-text-gray-900'
                                    : 'wpuf-border-gray-200 wpuf-text-gray-600 hover:wpuf-border-gray-300'
                            }` }
                        >
                            <span
                                className={ `wpuf-absolute wpuf-right-2 wpuf-top-2 wpuf-flex wpuf-h-4 wpuf-w-4 wpuf-items-center wpuf-justify-center wpuf-rounded wpuf-border ${
                                    isOn ? 'wpuf-border-primary wpuf-bg-primary wpuf-text-white' : 'wpuf-border-gray-300 wpuf-bg-white'
                                }` }
                            >
                                { isOn && (
                                    <svg className="wpuf-h-3 wpuf-w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4l3.3 3.3 7.3-7.3a1 1 0 011.4 0z" clipRule="evenodd" />
                                    </svg>
                                ) }
                            </span>
                            { icon ? (
                                icon.startsWith( 'http' ) ? (
                                    <img src={ icon } alt="" className="wpuf-h-8 wpuf-object-contain" />
                                ) : (
                                    <span
                                        className="wpuf-text-2xl"
                                        // eslint-disable-next-line react/no-danger
                                        dangerouslySetInnerHTML={ { __html: icon } }
                                    />
                                )
                            ) : null }
                            <span>{ labelOf( opt, key ) }</span>
                        </button>
                    );
                } ) }
            </div>
        </>
    );
}
