import { __ } from '@wordpress/i18n';
import SettingsField from './SettingsField';

/**
 * Renders pro_preview fields inside a disabled overlay with "Upgrade to PRO" button.
 *
 * Mirrors the Vue template in post-form-settings.php lines 206-222.
 */
export default function ProPreviewWrapper( { proPreview, onChange, settings } ) {
    if ( ! proPreview || ! proPreview.fields ) {
        return null;
    }

    const proLink = window.wpuf_form_builder?.pro_link || 'https://wedevs.com/wp-user-frontend-pro/pricing/';

    return (
        <div className="wpuf-p-4 wpuf-relative wpuf-rounded wpuf-border wpuf-border-transparent hover:wpuf-border-sky-500 wpuf-border-dashed wpuf-group/pro-item wpuf-transition-all wpuf-opacity-50 hover:wpuf-opacity-100">
            <a
                className="wpuf-btn-primary wpuf-absolute wpuf-top-[50%] wpuf-left-[50%] wpuf--translate-y-[50%] wpuf--translate-x-[50%] wpuf-z-30 wpuf-opacity-0 group-hover/pro-item:wpuf-opacity-100 wpuf-transition-all"
                target="_blank"
                rel="noopener noreferrer"
                href={ proLink }
            >
                { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
            </a>
            <div className="wpuf-z-20 wpuf-absolute wpuf-top-0 wpuf-left-0 wpuf-w-full wpuf-h-full wpuf-shadow-sm wpuf-bg-emerald-50 group-hover/pro-item:wpuf-opacity-50 wpuf-opacity-0" />
            { Object.entries( proPreview.fields ).map( ( [ fieldName, fieldDef ] ) => (
                <SettingsField
                    key={ fieldName }
                    field={ fieldDef }
                    name={ fieldName }
                    value={ fieldDef.value !== undefined ? fieldDef.value : '' }
                    onChange={ onChange }
                    settings={ settings }
                />
            ) ) }
        </div>
    );
}
