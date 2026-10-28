import { __ } from '@wordpress/i18n';
import { settingsData } from '../../../boot';

/**
 * Wraps a Pro-only field (when Pro is inactive) in a disabled preview with an
 * "Upgrade to PRO" overlay on hover. Mirrors the Form Builder Settings kit's
 * ProPreviewWrapper design (dashed border, dimmed, emerald hover overlay).
 */
export default function ProPreviewWrapper( { children } ) {
    const wpuf = settingsData();
    const proLink = wpuf.upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/';

    return (
        <div className="relative rounded-sm border border-dashed border-transparent hover:border-primary group/pro-item transition-all opacity-60 hover:opacity-100">
            <a
                className="absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 z-30 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white! opacity-0 group-hover/pro-item:opacity-100 transition-all hover:bg-primaryHover"
                target="_blank"
                rel="noopener noreferrer"
                href={ proLink }
            >
                { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
            </a>
            <div className="absolute inset-0 z-20 rounded-sm bg-emerald-50 opacity-0 group-hover/pro-item:opacity-60 transition-all" />
            { /* Display only: not clickable, not reachable by keyboard (inert), and
                native controls disabled; the server ignores Pro previews too. */ }
            <fieldset disabled inert="" className="pointer-events-none m-0 min-w-0 border-0 p-0">{ children }</fieldset>
        </div>
    );
}
