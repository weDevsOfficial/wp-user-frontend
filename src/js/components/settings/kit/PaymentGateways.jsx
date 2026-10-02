import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ProBadge from './ProBadge';

const IS_PRO = ( window.wpuf_settings || {} ).is_pro;
const UPGRADE_URL =
    ( ( window.wpuf_settings || {} ).upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/' ) +
    '?utm_source=wpuf-settings&utm_medium=gateway-card';

// Fields that have no gateway id in their name but belong to one (legacy map).
const FIELD_GATEWAY = { failed_retry: 'paypal' };

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
};

const iconOf = ( opt, key ) => {
    if ( opt && typeof opt === 'object' && typeof opt.icon === 'string' && opt.icon ) {
        return opt.icon;
    }
    return GATEWAY_SVG[ key ] || '';
};

/**
 * Payment gateway selector — matches the legacy payment screen behaviour:
 *  - the corner CHECKBOX enables/disables a gateway (multi-select, stored in
 *    `active_gateways` as the legacy `{ slug: slug }` shape);
 *  - clicking the CARD shows only that gateway's settings below.
 */
export default function PaymentGateways( { fields, renderField, gatewayValue, onGatewayChange } ) {
    const agField = fields.find( ( f ) => f.name === 'active_gateways' );
    const options = ( agField && agField.options ) || {};
    const gids = Object.keys( options );

    const enabled = gatewayValue && typeof gatewayValue === 'object' ? Object.keys( gatewayValue ) : [];

    const gatewayOf = ( field ) => {
        if ( FIELD_GATEWAY[ field.name ] ) {
            return FIELD_GATEWAY[ field.name ];
        }
        const found = gids.find( ( gid ) => field.name && field.name.indexOf( gid ) !== -1 );
        return found || 'common';
    };

    const [ viewed, setViewed ] = useState( enabled[ 0 ] || gids[ 0 ] || '' );

    const commonFields = fields.filter( ( f ) => f.name !== 'active_gateways' && gatewayOf( f ) === 'common' );
    const viewedFields = fields.filter( ( f ) => f.name !== 'active_gateways' && gatewayOf( f ) === viewed );

    const toggleEnable = ( gid ) => {
        const set = new Set( enabled );
        if ( set.has( gid ) ) {
            set.delete( gid );
        } else {
            set.add( gid );
        }
        const next = {};
        set.forEach( ( g ) => {
            next[ g ] = g;
        } );
        onGatewayChange( next );
    };

    return (
        <div>
            { commonFields.map( renderField ) }

            <div className="wpuf-mt-6">
                { agField && agField.label ? (
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2 wpuf-block">{ agField.label }</label>
                ) : null }
                <div className="wpuf-mt-1 wpuf-flex wpuf-flex-wrap wpuf-gap-3">
                    { gids.map( ( gid ) => {
                        const opt = options[ gid ];
                        const icon = iconOf( opt, gid );
                        const isProPreview = opt && typeof opt === 'object' && opt.is_pro_preview && ! IS_PRO;

                        if ( isProPreview ) {
                            return (
                                <a
                                    key={ gid }
                                    href={ UPGRADE_URL }
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    title={ labelOf( opt, gid ) }
                                    className="wpuf-relative wpuf-flex wpuf-min-w-[150px] wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-gap-2 wpuf-rounded-lg wpuf-border wpuf-border-dashed wpuf-border-gray-300 wpuf-bg-gray-50 wpuf-px-5 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-opacity-80 hover:wpuf-border-sky-400 hover:wpuf-opacity-100"
                                >
                                    <span className="wpuf-absolute wpuf-right-2 wpuf-top-2"><ProBadge /></span>
                                    { icon && ( icon.startsWith( 'http' )
                                        ? <img src={ icon } alt="" className="wpuf-h-7 wpuf-object-contain wpuf-opacity-60" />
                                        // eslint-disable-next-line react/no-danger
                                        : <span className="wpuf-opacity-60" dangerouslySetInnerHTML={ { __html: icon } } /> ) }
                                    <span>{ labelOf( opt, gid ) }</span>
                                </a>
                            );
                        }

                        const isEnabled = enabled.includes( gid );
                        const isViewed = viewed === gid;
                        return (
                            <button
                                type="button"
                                key={ gid }
                                onClick={ () => setViewed( gid ) }
                                className={ `wpuf-relative wpuf-flex wpuf-min-w-[150px] wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-gap-2 wpuf-rounded-lg wpuf-border wpuf-px-5 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-transition ${
                                    isViewed
                                        ? 'wpuf-border-primary wpuf-ring-1 wpuf-ring-primary wpuf-text-gray-900'
                                        : 'wpuf-border-gray-200 wpuf-text-gray-600 hover:wpuf-border-gray-300'
                                }` }
                            >
                                <span
                                    role="checkbox"
                                    aria-checked={ isEnabled }
                                    onClick={ ( e ) => {
                                        e.stopPropagation();
                                        toggleEnable( gid );
                                    } }
                                    className={ `wpuf-absolute wpuf-right-2 wpuf-top-2 wpuf-flex wpuf-h-4 wpuf-w-4 wpuf-cursor-pointer wpuf-items-center wpuf-justify-center wpuf-rounded wpuf-border ${
                                        isEnabled ? 'wpuf-border-primary wpuf-bg-primary wpuf-text-white' : 'wpuf-border-gray-300 wpuf-bg-white'
                                    }` }
                                >
                                    { isEnabled && (
                                        <svg className="wpuf-h-3 wpuf-w-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4l3.3 3.3 7.3-7.3a1 1 0 011.4 0z" clipRule="evenodd" />
                                        </svg>
                                    ) }
                                </span>
                                { icon && ( icon.startsWith( 'http' )
                                    ? <img src={ icon } alt="" className="wpuf-h-8 wpuf-object-contain" />
                                    // eslint-disable-next-line react/no-danger
                                    : <span className="wpuf-text-2xl" dangerouslySetInnerHTML={ { __html: icon } } /> ) }
                                <span>{ labelOf( opt, gid ) }</span>
                            </button>
                        );
                    } ) }
                </div>
                { agField && agField.desc ? (
                    <p className="wpuf-mt-1 wpuf-text-xs wpuf-text-gray-500">{ agField.desc }</p>
                ) : null }
            </div>

            <div className="wpuf-mt-2">
                { viewedFields.length
                    ? viewedFields.map( renderField )
                    : <p className="wpuf-mt-4 wpuf-text-sm wpuf-text-gray-400">{ __( 'No settings for this gateway.', 'wp-user-frontend' ) }</p> }
            </div>
        </div>
    );
}
