import { __ } from '@wordpress/i18n';

/**
 * Wraps a Pro-only field (when Pro is inactive) in a disabled preview with an
 * "Upgrade to PRO" overlay on hover. Mirrors the Form Builder Settings kit's
 * ProPreviewWrapper design (dashed border, dimmed, emerald hover overlay).
 */
export default function ProPreviewWrapper( { children } ) {
    const wpuf = window.wpuf_settings || {};
    const proLink = wpuf.upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/';

    return (
        <div className="wpuf-relative wpuf-rounded wpuf-border wpuf-border-dashed wpuf-border-transparent hover:wpuf-border-sky-500 wpuf-group/pro-item wpuf-transition-all wpuf-opacity-60 hover:wpuf-opacity-100">
            <a
                className="wpuf-absolute wpuf-top-1/2 wpuf-left-1/2 wpuf--translate-y-1/2 wpuf--translate-x-1/2 wpuf-z-30 wpuf-rounded-md wpuf-bg-primary wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold !wpuf-text-white wpuf-opacity-0 group-hover/pro-item:wpuf-opacity-100 wpuf-transition-all hover:wpuf-bg-primaryHover"
                target="_blank"
                rel="noopener noreferrer"
                href={ proLink }
            >
                { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
            </a>
            <div className="wpuf-absolute wpuf-inset-0 wpuf-z-20 wpuf-rounded wpuf-bg-emerald-50 wpuf-opacity-0 group-hover/pro-item:wpuf-opacity-60 wpuf-transition-all" />
            <div className="wpuf-pointer-events-none">{ children }</div>
        </div>
    );
}
