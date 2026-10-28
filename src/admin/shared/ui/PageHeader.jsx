/**
 * Page header of the WPUF admin screens (develop look, src/admin/apps/subscriptions/components/Header.jsx):
 * logo, product title, plan + version badges, "Upgrade to PRO" (free only),
 * Headway changelog icon (`#wpuf-headway-icon`, the id the Headway widget
 * attaches to), "Submit Ideas" and "Support".
 * - variant "bleed" (forms lists, subscriptions): the white strip at the top
 *   of the gray page (FlyHR top bar), spanning the screen root's 20px side
 *   padding, 16px inside above and below, 2px bottom border;
 * - variant "card" (settings): white block, 20px padding, 2px bottom border.
 * Data comes from the boot payload (version, proVersion, plan, isPro, assetUrl).
 */
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { cn } from '@wedevs/plugin-ui';

import { useBoot } from '../hooks';
import { UPGRADE_URL } from './ProBadge';

export const SUPPORT_URL = 'https://wedevs.com/contact/?utm_source=wpuf-subscription';
export const IDEAS_URL = 'https://feedback.wedevs.com/b/user-frontend';

// Tailwind 3 values of develop's classes (ring-green-600/20, shadow-xs).
const BADGE = 'ml-2 inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs text-green-700 ring-1 ring-inset ring-[rgba(22,163,74,0.2)]';
const SHADOW = 'shadow-[0_1px_2px_0_rgba(0,0,0,0.05)]';
// develop's Ideas link: WordPress body text color (#3c434a), not the link color (measured on develop, 4.1a).
const OUTLINE = 'border border-solid border-gray-100 mr-3 text-center rounded-md px-3 py-1.5 text-[13px] font-semibold text-[#3c434a] no-underline hover:bg-slate-100 hover:text-[#3c434a] focus:bg-slate-100 focus:text-[#3c434a] focus:shadow-none';

/**
 * "pro-business" -> "Pro Business".
 *
 * @param {string} plan Plan slug.
 *
 * @return {string} Label.
 */
export function planLabel( plan ) {
    return String( plan || '' )
        .split( '-' )
        .filter( Boolean )
        .map( ( word ) => word.charAt( 0 ).toUpperCase() + word.slice( 1 ) )
        .join( ' ' );
}

/**
 * @param {Object} props
 * @param {string} [props.variant]    bleed|card
 * @param {string} [props.utm]        utm_source of the upgrade link.
 * @param {string} [props.supportUrl] Support link (screens differ).
 * @param {*}      [props.extra]      Extra links before the Headway icon (e.g. "Classic view").
 * @param {string} [props.helpUrl]    The screen's docs page: a help icon before the
 *                                    Headway icon (was the "Learn more" footer band, D26).
 * @param {string} [props.helpLabel]  Its accessible name / tooltip.
 */
export default function PageHeader( { variant = 'bleed', utm = 'wpuf-header', supportUrl = SUPPORT_URL, extra, helpUrl, helpLabel, className } ) {
    const boot = useBoot();
    const isPro = true === boot.isPro;
    const version = isPro && boot.proVersion ? boot.proVersion : boot.version;
    const plan = isPro ? planLabel( boot.plan ) : '';

    return (
        <div
            data-wpuf-ui=""
            data-wpuf-bleed={ 'card' === variant ? undefined : '' }
            className={ cn(
                'flex justify-between items-center border-0 border-b-2 border-solid border-gray-100',
                'card' === variant ? 'bg-white p-4' : 'w-[calc(100%+40px)] -ml-5 px-5 py-3 bg-white',
                className
            ) }
        >
            <div className="flex justify-start items-center">
                { boot.assetUrl && <img src={ boot.assetUrl + '/images/wpuf-icon-circle.svg' } alt={ __( 'WPUF Icon', 'wp-user-frontend' ) } className="w-9 h-auto mr-3" /> }
                <h2 className="m-0 text-lg leading-6 font-semibold text-[#1d2327]">{ isPro ? __( 'WP User Frontend Pro', 'wp-user-frontend' ) : __( 'WP User Frontend', 'wp-user-frontend' ) }</h2>
                { plan && <span className={ cn( BADGE, 'font-semibold' ) }>{ plan }</span> }
                { version && <span className={ cn( BADGE, 'font-medium' ) }>{ 'v' + version }</span> }
                { ! isPro && (
                    <a
                        href={ addQueryArgs( UPGRADE_URL, { utm_source: utm, utm_medium: 'wpuf-header' } ) }
                        target="_blank"
                        rel="noreferrer"
                        className="flex ml-4 rounded-md bg-primary px-3 py-2 text-[13px] font-semibold text-white no-underline hover:bg-[#10b981] hover:text-white focus:text-white focus:shadow-none"
                    >
                        { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
                    </a>
                ) }
            </div>
            <div className="flex justify-end items-center w-2/4">
                { extra }
                { helpUrl && (
                    <a
                        href={ helpUrl }
                        target="_blank"
                        rel="noreferrer"
                        title={ helpLabel || __( 'Documentation', 'wp-user-frontend' ) }
                        aria-label={ helpLabel || __( 'Documentation', 'wp-user-frontend' ) }
                        className={ cn( 'mr-4 inline-flex size-8 items-center justify-center rounded-full border border-solid border-gray-100 bg-white text-gray-500 no-underline hover:bg-slate-100 hover:text-gray-700 focus:text-gray-700 focus:shadow-none', SHADOW ) }
                    >
                        <svg className="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                            <circle cx="10" cy="10" r="7.5" />
                            <path d="M7.9 7.6a2.2 2.2 0 0 1 4.2.9c0 1.5-2.1 1.9-2.1 3.1M10 14.2h.01" />
                        </svg>
                    </a>
                ) }
                <span id="wpuf-headway-icon" className={ cn( 'border border-solid border-gray-100 mr-4 rounded-full p-1 hover:bg-slate-100', SHADOW ) }></span>
                <a className={ cn( OUTLINE, SHADOW, 'wpuf-feedback-link' ) } target="_blank" rel="noreferrer" href={ IDEAS_URL }>
                    { '💡 ' + __( 'Submit Ideas', 'wp-user-frontend' ) }
                </a>
                { supportUrl && (
                    <a
                        href={ supportUrl }
                        target="_blank"
                        rel="noreferrer"
                        className={ cn( 'rounded-md text-center bg-primary px-3 py-1.5 text-[13px] font-semibold text-white no-underline hover:bg-[#10b981] hover:text-white focus:bg-[#10b981] focus:text-white', SHADOW ) }
                    >
                        { /* develop: "Support ", two no-break spaces, then a space before the icon */ }
                        { __( 'Support', 'wp-user-frontend' ) + ' \u00a0\u00a0 ' }
                        <span className="dashicons dashicons-businessperson" aria-hidden="true"></span>
                    </a>
                ) }
            </div>
        </div>
    );
}
