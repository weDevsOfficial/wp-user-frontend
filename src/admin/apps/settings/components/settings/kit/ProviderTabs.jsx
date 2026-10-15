import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ProBadge from './ProBadge';
import { settingsData } from '../../../boot';

/**
 * Provider selector cards + the selected provider's fields. Used for the SMS
 * gateways (Twilio / SMSGlobal / Nexmo / Clickatell) and Social Login providers
 * (Facebook / X / Google / LinkedIn / Instagram), matching the Figma redesign.
 *
 * Fields are grouped by name prefix. Fields that match no provider prefix
 * (e.g. the social "enabled" toggle) render above the cards as standalone.
 */
const ASSET = settingsData().asset_url || '';
const UPGRADE_URL =
    ( settingsData().upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/' ) +
    '?utm_source=wpuf-settings&utm_medium=provider-card';

// Logos keep their native aspect ratio, capped to a consistent box (max 36px
// tall, 120px wide) so wide wordmarks (Clickatell) and tall marks (Twilio) sit
// at the same visual scale as the Figma cards instead of being height-stretched.
const ProviderIcon = ( { provider } ) =>
    provider.icon ? (
        <img
            src={ `${ ASSET }/images/${ provider.icon }` }
            alt={ provider.label }
            className="max-h-9 max-w-[120px] object-contain"
        />
    ) : provider.iconSvg ? (
        <span
            className="flex h-9 items-center"
            // eslint-disable-next-line react/no-danger
            dangerouslySetInnerHTML={ { __html: provider.iconSvg } }
        />
    ) : null;

export default function ProviderTabs( { providers: allProviders, fields, renderField, values = {}, pro = false, search = '' } ) {
    const prefixesOf = ( p ) => ( Array.isArray( p.prefix ) ? p.prefix : [ p.prefix ] );
    const hasFields = ( p ) => fields.some( ( f ) => f.name && prefixesOf( p ).some( ( pre ) => f.name.indexOf( pre ) === 0 ) );

    const term = ( search || '' ).trim().toLowerCase();

    // A provider matches the search by its own label/key, or by any of its fields.
    const providerMatchesSearch = ( p ) => {
        if ( ! term ) {
            return true;
        }
        if ( ( p.label || '' ).toLowerCase().includes( term ) || ( p.key || '' ).toLowerCase().includes( term ) ) {
            return true;
        }
        const prefixes = prefixesOf( p );
        return fields.some( ( f ) =>
            f.name && prefixes.some( ( pre ) => f.name.indexOf( pre ) === 0 )
            && `${ f.label || '' } ${ f.desc || '' } ${ f.name }`.toLowerCase().includes( term )
        );
    };

    // Only show providers with registered fields (Instagram deprecated / GitHub
    // license-gated drop out), narrowed to the search match when searching.
    const providers = allProviders.filter( ( p ) => hasFields( p ) && providerMatchesSearch( p ) );

    const [ active, setActive ] = useState( providers[ 0 ] ? providers[ 0 ].key : '' );

    // While searching, force the matched provider active so its fields are shown
    // (don't rely on the user clicking the card).
    const activeKey = term ? ( providers[ 0 ] ? providers[ 0 ].key : '' ) : active;

    const matchProvider = ( field ) =>
        providers.find( ( p ) => {
            const prefixes = prefixesOf( p );
            return field.name && prefixes.some( ( pre ) => field.name.indexOf( pre ) === 0 );
        } );

    // A provider counts as "configured" (corner check on) when at least one of
    // its credential fields holds a value — derived, like the payment card's
    // enabled state, since social/SMS have no per-provider enable flag.
    const isConfigured = ( p ) => {
        const prefixes = Array.isArray( p.prefix ) ? p.prefix : [ p.prefix ];
        return fields.some( ( f ) => {
            if ( ! f.name || f.type === 'html' ) {
                return false;
            }
            if ( ! prefixes.some( ( pre ) => f.name.indexOf( pre ) === 0 ) ) {
                return false;
            }
            const v = values[ f.name ];
            return v !== undefined && v !== null && String( v ).trim() !== '';
        } );
    };

    // Non-provider fields (e.g. the global "Enable Social Login" toggle). While
    // searching a provider, hide standalone fields that don't match the term so
    // they don't appear as noise above the matched provider's card.
    const standalone = fields
        .filter( ( f ) => ! matchProvider( f ) )
        .filter( ( f ) => ! term || `${ f.label || '' } ${ f.desc || '' } ${ f.name || '' }`.toLowerCase().includes( term ) );
    const activeFields = fields.filter( ( f ) => {
        const p = matchProvider( f );
        return p && p.key === activeKey;
    } );

    return (
        <div>
            { standalone.map( renderField ) }

            <div className="mb-6 mt-4 flex flex-wrap gap-3">
                { providers.map( ( p ) => {
                    // Pro-only sections: every provider card is an upsell (matches the
                    // Pro-preview gateway cards — dashed, dimmed, Pro badge, non-selectable).
                    if ( pro ) {
                        return (
                            <a
                                key={ p.key }
                                href={ UPGRADE_URL }
                                target="_blank"
                                rel="noopener noreferrer"
                                title={ p.label }
                                className="relative flex h-[104px] w-[160px] flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-5 py-4 text-xs font-medium text-gray-500 opacity-80 hover:border-primary hover:opacity-100"
                            >
                                <span className="absolute right-2 top-2"><ProBadge /></span>
                                <span className="opacity-60"><ProviderIcon provider={ p } /></span>
                                <span>{ p.label }</span>
                            </a>
                        );
                    }

                    const isViewed = activeKey === p.key;
                    const configured = isConfigured( p );
                    return (
                        <button
                            type="button"
                            key={ p.key }
                            data-settings-card={ p.key }
                            onClick={ () => setActive( p.key ) }
                            className={ `focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary/40 relative flex h-[104px] w-[160px] flex-col items-center justify-center gap-2 rounded-lg border px-5 py-4 text-xs font-medium transition ${
                                isViewed
                                    ? 'border-primary ring-1 ring-primary text-gray-900'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                            }` }
                        >
                            <span
                                title={ configured ? __( 'Configured', 'wp-user-frontend' ) : __( 'Add credentials to enable', 'wp-user-frontend' ) }
                                className={ `absolute right-2 top-2 flex h-4 w-4 items-center justify-center rounded-sm border ${
                                    configured ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white'
                                }` }
                            >
                                { configured && (
                                    <svg className="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4l3.3 3.3 7.3-7.3a1 1 0 011.4 0z" clipRule="evenodd" />
                                    </svg>
                                ) }
                            </span>
                            <ProviderIcon provider={ p } />
                            <span className="text-gray-600">{ p.label }</span>
                        </button>
                    );
                } ) }
            </div>

            { activeFields.map( renderField ) }
        </div>
    );
}
