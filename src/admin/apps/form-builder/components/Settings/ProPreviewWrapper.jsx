import { __ } from '@wordpress/i18n';
import SettingsField from './SettingsField';
import { builderData } from '../../utils/boot';

/**
 * Renders pro_preview fields inside a disabled overlay with "Upgrade to PRO" button.
 *
 * Mirrors the Vue template in post-form-settings.php lines 206-222: develop
 * rendered these disabled with an empty name, so they were never saved. Here
 * the inputs sit in a disabled fieldset and changes are dropped.
 */
const ignoreChange = () => {};

export default function ProPreviewWrapper( { proPreview, settings } ) {
    if ( ! proPreview || ! proPreview.fields ) {
        return null;
    }

    const proLink = builderData().pro_link || 'https://wedevs.com/wp-user-frontend-pro/pricing/';

    return (
        <div className="p-4 relative rounded-sm border border-transparent hover:border-primary border-dashed group/pro-item wpuf-transition-all opacity-50 hover:opacity-100">
            <a
                className="wpuf-btn-primary absolute top-[50%] left-[50%] -translate-y-[50%] -translate-x-[50%] z-30 opacity-0 group-hover/pro-item:opacity-100 wpuf-transition-all"
                target="_blank"
                rel="noopener noreferrer"
                href={ proLink }
            >
                { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
            </a>
            <div className="z-20 absolute top-0 left-0 w-full h-full shadow-xs bg-emerald-50 group-hover/pro-item:opacity-50 opacity-0" />
            <fieldset disabled className="m-0 p-0 border-0">
                { Object.entries( proPreview.fields ).map( ( [ fieldName, fieldDef ] ) => (
                    <SettingsField
                        key={ fieldName }
                        slotKey={ fieldName }
                        field={ fieldDef }
                        name={ fieldName }
                        value={ fieldDef.value !== undefined ? fieldDef.value : '' }
                        onChange={ ignoreChange }
                        settings={ settings }
                    />
                ) ) }
            </fieldset>
        </div>
    );
}
