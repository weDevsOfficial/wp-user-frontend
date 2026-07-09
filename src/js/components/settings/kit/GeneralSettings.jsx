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

const SECURITY = [ 'recaptcha_public', 'recaptcha_private', 'enable_turnstile', 'turnstile_site_key', 'turnstile_secret_key' ];
const GEO = [ 'ipstack_key' ];
const LOCATION = [ 'gmap_api_key' ];

const Group = ( { title, desc, first, children } ) => (
    <div className={ first ? '' : 'wpuf-mt-8 wpuf-border-t wpuf-border-gray-200 wpuf-pt-8' }>
        <h4 className="wpuf-mb-1 wpuf-mt-0 wpuf-text-base wpuf-font-semibold wpuf-text-gray-900">
            { title }
        </h4>
        { desc ? <p className="wpuf-mb-4 wpuf-mt-0 wpuf-max-w-2xl wpuf-text-sm wpuf-text-gray-500">{ desc }</p> : null }
        { children }
    </div>
);

const Check = () => (
    <svg className="wpuf-h-3 wpuf-w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
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
        onClick={ onView }
        title={ label }
        className={ `wpuf-relative wpuf-flex wpuf-h-[80px] wpuf-w-[160px] wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-border-2 wpuf-bg-white wpuf-transition ${
            pro
                ? 'wpuf-border-dashed wpuf-border-gray-300 wpuf-bg-gray-50 wpuf-opacity-90 hover:wpuf-opacity-100'
                : viewed
                    ? 'wpuf-border-primary'
                    : 'wpuf-border-gray-200 hover:wpuf-border-gray-300'
        }` }
    >
        { pro ? (
            <span className="wpuf-absolute wpuf-right-1.5 wpuf-top-1.5"><ProBadge /></span>
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
                className={ `wpuf-absolute wpuf-right-1.5 wpuf-top-1.5 wpuf-flex wpuf-h-4 wpuf-w-4 wpuf-items-center wpuf-justify-center wpuf-rounded wpuf-border ${
                    onToggle ? 'wpuf-cursor-pointer' : 'wpuf-cursor-default'
                } ${ checked ? 'wpuf-border-primary wpuf-bg-primary wpuf-text-white' : 'wpuf-border-gray-300 wpuf-bg-white' }` }
            >
                { checked && <Check /> }
            </span>
        ) }
        { logo ? (
            <img src={ logo } alt={ label } className="wpuf-max-h-8 wpuf-max-w-[96px] wpuf-object-contain" />
        ) : (
            <span className="wpuf-text-sm wpuf-font-medium wpuf-text-gray-700">{ label }</span>
        ) }
    </button>
);

const OptionLabel = ( { children } ) => (
    <span className="wpuf-mb-2 wpuf-block wpuf-text-sm wpuf-text-gray-700">{ children }</span>
);

export default function GeneralSettings( { fields, renderField, values = {}, onField } ) {
    const wpuf = window.wpuf_settings || {};
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
                    <div className="wpuf-mb-4 wpuf-flex wpuf-gap-3">
                        <OptionCard
                            logo={ assetUrl ? `${ assetUrl }/images/recaptcha.png` : '' }
                            label="reCAPTCHA"
                            viewed={ securityView === 'recaptcha' }
                            checked={ recaptchaOn }
                            onView={ () => setSecurityView( 'recaptcha' ) }
                            checkTitle={ __( 'Active when the reCAPTCHA keys are filled', 'wp-user-frontend' ) }
                        />
                        <OptionCard
                            logo={ assetUrl ? `${ assetUrl }/images/cloudflare.png` : '' }
                            label="Cloudflare"
                            viewed={ securityView === 'cloudflare' }
                            checked={ turnstileOn }
                            onView={ () => setSecurityView( 'cloudflare' ) }
                            onToggle={ toggleTurnstile }
                            checkTitle={ __( 'Enable Cloudflare Turnstile', 'wp-user-frontend' ) }
                        />
                    </div>
                    { securityView === 'recaptcha'
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
                    <div className="wpuf-mb-4 wpuf-flex wpuf-gap-3">
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
                    <div className="wpuf-mb-4 wpuf-flex wpuf-gap-3">
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
