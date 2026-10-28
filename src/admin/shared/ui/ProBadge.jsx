/**
 * "Pro" badge linking to the upgrade page (settings kit behaviour): hidden
 * when Pro is active, local image (assets/images/pro-badge.svg, 39x22),
 * opens in a new tab with UTM source per screen.
 */
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

import { useBoot } from '../hooks';

export const UPGRADE_URL = 'https://wedevs.com/wp-user-frontend-pro/pricing/';

/**
 * @param {Object} props
 * @param {string} [props.utm]        utm_source (screen key).
 * @param {string} [props.upgradeUrl] Override the upgrade URL.
 * @param {boolean} [props.link]      false: the image only (develop subscriptions badge,
 *                                    whose hover tooltip carries the upgrade link).
 * @param {string}  [props.size]      md (39x22, default) | sm (16px tall: builder tiles).
 */
export default function ProBadge( { utm = 'wpuf-admin', upgradeUrl, link = true, size = 'md' } ) {
    const boot = useBoot();

    if ( boot.isPro ) {
        return null;
    }

    const image = 'sm' === size
        ? <img src={ ( boot.assetUrl || '' ) + '/images/pro-badge.svg' } alt={ __( 'Pro', 'wp-user-frontend' ) } className="inline-block align-middle h-4 w-auto max-w-none" />
        : <img src={ ( boot.assetUrl || '' ) + '/images/pro-badge.svg' } alt={ __( 'Pro', 'wp-user-frontend' ) } width="39" height="22" className="inline-block align-middle w-[39px] h-[22px] max-w-none" />;

    if ( ! link ) {
        return <span data-wpuf-ui="" className="inline-block align-middle leading-none">{ image }</span>;
    }

    const href = addQueryArgs( upgradeUrl || UPGRADE_URL, { utm_source: utm, utm_medium: 'pro-badge' } );

    return (
        <a data-wpuf-ui="" href={ href } target="_blank" rel="noopener noreferrer" className="inline-block align-middle leading-none">
            { image }
        </a>
    );
}
