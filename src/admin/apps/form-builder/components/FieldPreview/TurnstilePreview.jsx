import { __ } from '@wordpress/i18n';
import { hasTurnstileApiKeys } from '../../utils/globalHelpers';
import { builderData } from '../../utils/boot';

export default function TurnstilePreview( { field } ) {
    const data = builderData();
    const assetUrl = data.asset_url || '';

    if ( ! hasTurnstileApiKeys() ) {
        const fieldSettings = data.field_settings || {};
        const validatorMsg = fieldSettings.cloudflare_turnstile?.validator?.msg || __( 'Please configure Cloudflare Turnstile API keys.', 'wp-user-frontend' );

        return (
            <div className="wpuf-fields">
                <p dangerouslySetInnerHTML={ { __html: validatorMsg } } />
            </div>
        );
    }

    const theme = field.turnstile_theme === 'dark' ? 'dark' : 'light';
    const size = field.turnstile_size === 'compact' ? '-compact' : '';
    const imageSrc = `${ assetUrl }/images/cloudflare-placeholder-${ theme }${ size }.png`;

    // Keys set but the Cloudflare switch (Settings > General) off: the site hides the field.
    const off = false === data.turnstile_enabled;

    return (
        <div className="wpuf-fields">
            <img
                className="wpuf-turnstile-placeholder"
                src={ imageSrc }
                alt=""
            />
            { off && (
                <p className="mt-2 mb-0 text-xs text-amber-700">
                    { __( 'Cloudflare Turnstile is turned off in Settings, so this field is hidden on your site.', 'wp-user-frontend' ) }{ ' ' }
                    <a
                        href={ `${ data.admin_url || '' }admin.php?page=wp-user-frontend#/settings?tab=wpuf_general` }
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-primary no-underline hover:underline"
                    >
                        { __( 'Turn on Turnstile', 'wp-user-frontend' ) }
                    </a>
                </p>
            ) }
        </div>
    );
}
