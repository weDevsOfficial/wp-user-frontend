import { __ } from '@wordpress/i18n';
import { settingsData } from '../../../../boot';

/**
 * Read-only placeholder for fields that are Pro-only or rendered by a custom PHP
 * callback with non-standard (often nested) storage. It never calls onChange, so
 * the React save never writes a value for these fields — their stored data is
 * left exactly as the legacy screen / Pro manages it.
 */
export default function ProNote( { field } ) {
    const isPro = !! settingsData().is_pro;

    // When Pro is active these fields are either handled by a dedicated React
    // component or are non-functional section headers — never show the
    // "configured in Pro" upsell (it's wrong + confusing). Render the label only,
    // or nothing.
    if ( isPro && ! field.help_text ) {
        return field.label
            ? <label className="text-sm font-medium leading-5 text-gray-700 my-1.5 block">{ field.label }</label>
            : null;
    }

    return (
        <div>
            { field.label && (
                <label className="text-sm font-medium leading-5 text-gray-700 my-1.5 block">{ field.label }</label>
            ) }
            <p className="rounded-md border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-500">
                { field.help_text || __( 'This option is configured in WP User Frontend Pro.', 'wp-user-frontend' ) }
            </p>
        </div>
    );
}
