import { __ } from '@wordpress/i18n';
import { hasTurnstileApiKeys } from '../../utils/globalHelpers';

export default function TurnstilePreview( { field } ) {
    const data = window.wpuf_form_builder || {};
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

    return (
        <div className="wpuf-fields">
            <img
                className="wpuf-turnstile-placeholder"
                src={ imageSrc }
                alt=""
            />
        </div>
    );
}
