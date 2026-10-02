import { __ } from '@wordpress/i18n';

/**
 * Read-only placeholder for fields that are Pro-only or rendered by a custom PHP
 * callback with non-standard (often nested) storage. It never calls onChange, so
 * the React save never writes a value for these fields — their stored data is
 * left exactly as the legacy screen / Pro manages it.
 */
export default function ProNote( { field } ) {
    const isPro = !! ( window.wpuf_settings || {} ).is_pro;

    // When Pro is active these fields are either handled by a dedicated React
    // component or are non-functional section headers — never show the
    // "configured in Pro" upsell (it's wrong + confusing). Render the label only,
    // or nothing.
    if ( isPro && ! field.help_text ) {
        return field.label
            ? <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2 wpuf-block">{ field.label }</label>
            : null;
    }

    return (
        <div>
            { field.label && (
                <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2 wpuf-block">{ field.label }</label>
            ) }
            <p className="wpuf-rounded-md wpuf-border wpuf-border-dashed wpuf-border-gray-300 wpuf-bg-gray-50 wpuf-px-3 wpuf-py-2 wpuf-text-xs wpuf-text-gray-500">
                { field.help_text || __( 'This option is configured in WP User Frontend Pro.', 'wp-user-frontend' ) }
            </p>
        </div>
    );
}
