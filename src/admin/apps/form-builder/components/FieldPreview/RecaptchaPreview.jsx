import { __ } from '@wordpress/i18n';
import { hasRecaptchaApiKeys } from '../../utils/globalHelpers';
import { builderData } from '../../utils/boot';

export default function RecaptchaPreview( { field } ) {
    const data = builderData();
    const assetUrl = data.asset_url || '';

    if ( ! hasRecaptchaApiKeys() ) {
        const fieldSettings = data.field_settings || {};
        const validatorMsg = fieldSettings.recaptcha?.validator?.msg || __( 'Please configure reCAPTCHA API keys.', 'wp-user-frontend' );

        return (
            <div className="wpuf-fields">
                <p dangerouslySetInnerHTML={ { __html: validatorMsg } } />
            </div>
        );
    }

    if ( field.recaptcha_type === 'invisible_recaptcha' ) {
        return (
            <div className="wpuf-fields">
                <p>{ __( 'Invisible reCaptcha', 'wp-user-frontend' ) }</p>
            </div>
        );
    }

    return (
        <div className="wpuf-fields">
            <img
                className="wpuf-recaptcha-placeholder"
                src={ `${ assetUrl }/images/recaptcha-placeholder.png` }
                alt=""
            />
        </div>
    );
}
