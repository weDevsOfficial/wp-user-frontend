/**
 * GeneralSettings — renders the `wpuf_general` section in the Figma sub-group
 * layout: Basic / Security / Geolocation / Location Service, each with a heading,
 * a short description, and a top divider, plus the provider "option" cards.
 *
 * STORAGE SAFETY: the option cards never introduce a new setting key.
 *  - Security cards mirror the Payment-gateway card UX (corner checkbox + click
 *    the body to view that provider's fields):
 *      • Cloudflare checkbox  → writes the EXISTING `enable_turnstile` on/off.
 *      • reCAPTCHA checkbox   → DERIVED (checked when both keys are filled). WPUF
 *        has no global reCAPTCHA enable flag — it activates by filling the keys —
 *        so this checkbox is a read-only indicator, it stores nothing.
 *  - Geolocation / Location cards are single decorative selectors revealing the
 *    existing ipstack_key / gmap_api_key fields.
 *
 * The group descriptions are Figma placeholder copy — pure UX, no storage impact.
 * Any field not claimed by Security/Geo/Location falls into Basic, so no existing
 * setting is ever hidden.
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ProBadge from './ProBadge';
import { settingsData } from '../../../boot';

const SECURITY = [ 'recaptcha_public', 'recaptcha_private', 'enable_turnstile', 'turnstile_site_key', 'turnstile_secret_key' ];
const GEO = [ 'ipstack_key' ];
const LOCATION = [ 'gmap_api_key' ];

const Group = ( { title, desc, first, children } ) => (
    <div className={ first ? '' : 'mt-6 border-t border-gray-200 pt-6' }>
        <h4 className="mb-1 mt-0 text-sm font-semibold text-gray-900">
            { title }
        </h4>
        { desc ? <p className="mb-1 mt-0 max-w-2xl text-sm text-gray-500">{ desc }</p> : null }
        { children }
    </div>
);

const Check = () => (
    <svg className="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fillRule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 011.4-1.4l3.3 3.3 7.3-7.3a1 1 0 011.4 0z" clipRule="evenodd" />
    </svg>
);

/**
 * Provider option card — mirrors the Payment gateway card: corner checkbox
 * enables (when interactive), clicking the body selects/views it. When `pro`
 * is set (a pro-preview field in the free build) the card shows the Pro badge
 * + dashed upsell styling instead of the checkbox, matching the payment card.
 */
const OptionCard = ( { logo, label, viewed, checked, onView, onToggle, checkTitle, pro } ) => (
    <button
        type="button"
        data-settings-card={ label }
        onClick={ onView }
        title={ label }
        className={ `focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary/40 relative flex h-[80px] w-[160px] items-center justify-center rounded-lg border-2 bg-white transition ${
            pro
                ? 'border-dashed border-gray-300 bg-gray-50 opacity-90 hover:opacity-100'
                : viewed
                    ? 'border-primary'
                    : 'border-gray-200 hover:border-gray-300'
        }` }
    >
        { pro ? (
            <span className="absolute right-1.5 top-1.5"><ProBadge /></span>
        ) : (
            <span
                role="checkbox"
                aria-checked={ checked }
                title={ checkTitle }
                onClick={ ( e ) => {
                    e.stopPropagation();
                    if ( onToggle ) {
                        onToggle();
                    }
                } }
                className={ `absolute right-1.5 top-1.5 flex h-4 w-4 items-center justify-center rounded-sm border ${
                    onToggle ? 'cursor-pointer' : 'cursor-default'
                } ${ checked ? 'border-primary bg-primary text-white' : 'border-gray-300 bg-white' }` }
            >
                { checked && <Check /> }
            </span>
        ) }
        { logo ? (
            <img src={ logo } alt={ label } className="max-h-8 max-w-[96px] object-contain" />
        ) : (
            <span className="text-sm font-medium text-gray-700">{ label }</span>
        ) }
    </button>
);

const OptionLabel = ( { children } ) => (
    <span className="mb-2 block text-sm text-gray-700">{ children }</span>
);

export default function GeneralSettings( { fields, renderField, values = {}, onField } ) {
    const wpuf = settingsData();
    const assetUrl = wpuf.asset_url || '';
    const isPro = !! wpuf.is_pro;
    const [ securityView, setSecurityView ] = useState( 'recaptcha' );

    const byName = ( name ) => fields.find( ( f ) => f.name === name );

    // A pro-preview field is upsell-only in the free build.
    const fieldIsPro = ( name ) => {
        const f = byName( name );
        return ! isPro && !! ( f && f.is_pro_preview );
    };

    const renderByName = ( name ) => {
        const f = byName( name );
        return f ? renderField( f ) : null;
    };
    const isClaimed = ( name ) =>
        SECURITY.includes( name ) || GEO.includes( name ) || LOCATION.includes( name );

    const basicFields = fields.filter( ( f ) => ! isClaimed( f.name ) );
    const hasSecurity = SECURITY.some( ( n ) => byName( n ) );
    const hasGeo = GEO.some( ( n ) => byName( n ) );
    const hasLocation = LOCATION.some( ( n ) => byName( n ) );

    // reCAPTCHA is "on" when both keys are filled (no global enable flag exists).
    const recaptchaOn = !! ( values.recaptcha_public && values.recaptcha_private );
    // Cloudflare uses the existing enable_turnstile option.
    const turnstileOn = values.enable_turnstile === 'on' || values.enable_turnstile === 1 || values.enable_turnstile === true;
    const toggleTurnstile = () => onField && onField( 'enable_turnstile', turnstileOn ? 'off' : 'on' );

    // A search may leave only one provider's fields: show that provider's card
    // view (searching "turnstile" opened an empty reCAPTCHA panel).
    const hasRecaptcha = !! ( byName( 'recaptcha_public' ) || byName( 'recaptcha_private' ) );
    const hasTurnstile = !! ( byName( 'enable_turnstile' ) || byName( 'turnstile_site_key' ) || byName( 'turnstile_secret_key' ) );
    let view = securityView;

    if ( 'recaptcha' === view && ! hasRecaptcha && hasTurnstile ) {
        view = 'cloudflare';
    } else if ( 'cloudflare' === view && ! hasTurnstile && hasRecaptcha ) {
        view = 'recaptcha';
    }

    return (
        <div>
            { basicFields.length ? (
                <Group
                    title={ __( 'Basic Setting', 'wp-user-frontend' ) }
                    desc={ __( 'Configure how user-created posts behave — admin access, post edit links, scripts and custom CSS.', 'wp-user-frontend' ) }
                    first
                >
                    { basicFields.map( renderField ) }
                </Group>
            ) : null }

            { hasSecurity ? (
                <Group
                    title={ __( 'Security', 'wp-user-frontend' ) }
                    desc={ __( 'Protect your forms from spam and abuse with reCAPTCHA or Cloudflare Turnstile.', 'wp-user-frontend' ) }
                    first={ ! basicFields.length }
                >
                    <OptionLabel>{ __( 'Security Option', 'wp-user-frontend' ) }</OptionLabel>
                    <div className="mb-4 flex gap-3">
                        <OptionCard
                            logo={ assetUrl ? `${ assetUrl }/images/recaptcha.png` : '' }
                            label="reCAPTCHA"
                            viewed={ view === 'recaptcha' }
                            checked={ recaptchaOn }
                            onView={ () => setSecurityView( 'recaptcha' ) }
                            checkTitle={ __( 'Active when the reCAPTCHA keys are filled', 'wp-user-frontend' ) }
                        />
                        <OptionCard
                            logo={ assetUrl ? `${ assetUrl }/images/cloudflare.png` : '' }
                            label="Cloudflare"
                            viewed={ view === 'cloudflare' }
                            checked={ turnstileOn }
                            onView={ () => setSecurityView( 'cloudflare' ) }
                            onToggle={ toggleTurnstile }
                            checkTitle={ __( 'Enable Cloudflare Turnstile', 'wp-user-frontend' ) }
                        />
                    </div>
                    { view === 'recaptcha'
                        ? [ renderByName( 'recaptcha_public' ), renderByName( 'recaptcha_private' ) ]
                        // enable_turnstile is driven by the card checkbox above, so only the keys render here.
                        : [ renderByName( 'turnstile_site_key' ), renderByName( 'turnstile_secret_key' ) ] }
                </Group>
            ) : null }

            { hasGeo ? (
                <Group
                    title={ __( 'Geolocation', 'wp-user-frontend' ) }
                    desc={ __( 'Resolve visitor location data using an ipstack API key.', 'wp-user-frontend' ) }
                >
                    <OptionLabel>{ __( 'Geolocation Option', 'wp-user-frontend' ) }</OptionLabel>
                    <div className="mb-4 flex gap-3">
                        <OptionCard
                            logo={ assetUrl ? `${ assetUrl }/images/ipstack.png` : '' }
                            label="ipstack"
                            viewed
                            checked={ !! values.ipstack_key }
                            pro={ fieldIsPro( 'ipstack_key' ) }
                        />
                    </div>
                    { GEO.map( renderByName ) }
                </Group>
            ) : null }

            { hasLocation ? (
                <Group
                    title={ __( 'Location Service', 'wp-user-frontend' ) }
                    desc={ __( 'Render maps and address fields using a Google Maps API key.', 'wp-user-frontend' ) }
                >
                    <OptionLabel>{ __( 'Location Option', 'wp-user-frontend' ) }</OptionLabel>
                    <div className="mb-4 flex gap-3">
                        <OptionCard
                            logo={ assetUrl ? `${ assetUrl }/images/google-maps.png` : '' }
                            label="Google Maps"
                            viewed
                            checked={ !! values.gmap_api_key }
                            pro={ fieldIsPro( 'gmap_api_key' ) }
                        />
                    </div>
                    { LOCATION.map( renderByName ) }
                </Group>
            ) : null }
        </div>
    );
}
