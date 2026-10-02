/**
 * Pro badge — clickable pro-badge.svg image linking to the upgrade page.
 * Matches the User Directory free module's ProBadge exactly (src/js/
 * user-directory/App.js). Hidden when Pro is active.
 */
export default function ProBadge( { utm = 'wpuf-settings' } ) {
    const wpuf = window.wpuf_settings || {};

    if ( wpuf.is_pro ) {
        return null;
    }

    const assetUrl = wpuf.asset_url || '';
    const upgradeUrl =
        ( wpuf.upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/' ) +
        '?utm_source=' + utm + '&utm_medium=pro-badge';

    return (
        <a
            href={ upgradeUrl }
            target="_blank"
            rel="noopener noreferrer"
            style={ { display: 'inline-block', verticalAlign: 'middle' } }
        >
            <img
                src={ assetUrl + '/images/pro-badge.svg' }
                alt="Pro"
                style={ { width: '39px', height: '22px', display: 'inline-block', verticalAlign: 'middle' } }
            />
        </a>
    );
}
