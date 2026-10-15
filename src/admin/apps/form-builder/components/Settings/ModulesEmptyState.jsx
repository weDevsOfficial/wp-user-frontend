import { __ } from '@wordpress/i18n';
import { Button } from '@wpuf/components';
import { adminUrl, builderData } from '../../utils/boot';

/**
 * Modules tab empty state — mirrors post-form-settings.php lines 234-305.
 *
 * Shows an illustration with "No modules activated" message.
 * When Pro is not active, adds an "Upgrade to PRO" hover overlay.
 * When Pro is active, shows a "Go To Module Page" button.
 */
export default function ModulesEmptyState( { isProActive } ) {
    const data = builderData();
    const proLink = data.pro_link || 'https://wedevs.com/wp-user-frontend-pro/pricing/';
    // (Named apart from the imported adminUrl(): reusing the name threw on render.)
    const admin = adminUrl() || data.admin_url || '';

    return (
        <div className="py-4 border-b border-gray-300 flex items-center justify-evenly flex-col h-[70vh] p-4 relative rounded-sm border border-transparent hover:border-primary border-dashed group/pro-item wpuf-transition-all opacity-50 hover:opacity-100">
            { ! isProActive && (
                <>
                    <Button
                        className="absolute top-[50%] left-[50%] -translate-y-[50%] -translate-x-[50%] z-30 opacity-0 group-hover/pro-item:opacity-100 wpuf-transition-all"
                        target="_blank"
                        rel="noopener noreferrer"
                        href={ proLink }
                    >
                        { __( 'Upgrade to PRO', 'wp-user-frontend' ) }
                    </Button>
                    <div className="z-20 absolute top-0 left-0 w-full h-full shadow-xs bg-emerald-50 group-hover/pro-item:opacity-50 opacity-0" />
                </>
            ) }
            <div className="flex flex-col items-center">
                <svg width="161" height="161" viewBox="0 0 161 161" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="80.5" cy="80.5" r="80.5" fill="#F3F3F4" />
                    <path d="M148.624 58.0946V148.936C148.624 155.477 143.323 160.777 136.783 160.777H26.075C19.5347 160.777 14.2344 155.477 14.2344 148.936V58.0946C14.2344 51.5543 19.5347 46.2539 26.075 46.2539H136.783C143.323 46.2403 148.624 51.5407 148.624 58.0946Z" fill="#DBEEE6" />
                    <path d="M27.082 156.987C22.0721 156.987 18 152.932 18 147.944V59.0423C18 54.0543 22.0721 50 27.082 50H135.918C140.928 50 145 54.0543 145 59.0423V147.958C145 152.946 140.928 157 135.918 157H27.082V156.987Z" fill="white" />
                    <path d="M74.8736 114H31V116H74.8736V114Z" fill="#059669" />
                    <rect x="31" y="77" width="26" height="26" rx="5" fill="#DBEEE6" />
                    <path d="M98 121.188H31V123.188H98V121.188Z" fill="#DBEEE6" />
                    <path d="M87.9881 128.336H31V130.336H87.9881V128.336Z" fill="#DBEEE6" />
                    <rect x="116" y="118.945" width="17" height="7.55556" rx="3.77778" fill="#059669" />
                    <g filter="url(#filter0_dd_modules)">
                        <circle cx="128.277" cy="122.722" r="4.72222" fill="white" />
                        <circle cx="128.277" cy="122.722" r="4.48611" stroke="#E2E8F0" strokeWidth="0.472222" />
                    </g>
                    <path d="M136.78 160.5H26.0859C19.6991 160.5 14.5 155.301 14.5 148.914V58.0859C14.5 51.6991 19.6991 46.5 26.0859 46.5H136.78C143.167 46.5 148.366 51.6991 148.366 58.0859V148.928C148.366 155.301 143.167 160.5 136.78 160.5Z" stroke="#059669" />
                    <path d="M30.7616 57.6777C30.7616 59.2719 29.4672 60.5663 27.873 60.5663C26.2788 60.5663 24.9844 59.2719 24.9844 57.6777C24.9844 56.0835 26.2788 54.7891 27.873 54.7891C29.4672 54.7891 30.7616 56.0835 30.7616 57.6777Z" fill="#DBEEE6" />
                    <path d="M38.1054 57.6777C38.1054 59.2719 36.8109 60.5663 35.2168 60.5663C33.6226 60.5663 32.3281 59.2719 32.3281 57.6777C32.3281 56.0835 33.6226 54.7891 35.2168 54.7891C36.8109 54.7891 38.1054 56.0835 38.1054 57.6777Z" fill="#DBEEE6" />
                    <path d="M45.4491 57.6777C45.4491 59.2719 44.1547 60.5663 42.5605 60.5663C40.9663 60.5663 39.6719 59.2719 39.6719 57.6777C39.6719 56.0835 40.9663 54.7891 42.5605 54.7891C44.1547 54.7891 45.4491 56.0835 45.4491 57.6777Z" fill="#DBEEE6" />
                    <defs>
                        <filter id="filter0_dd_modules" x="120.555" y="116" width="15.4453" height="15.4453" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
                            <feFlood floodOpacity="0" result="BackgroundImageFix" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="1" />
                            <feGaussianBlur stdDeviation="1" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.06 0" />
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow" />
                            <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
                            <feOffset dy="1" />
                            <feGaussianBlur stdDeviation="1.5" />
                            <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.1 0" />
                            <feBlend mode="normal" in2="effect1_dropShadow" result="effect2_dropShadow" />
                            <feBlend mode="normal" in="SourceGraphic" in2="effect2_dropShadow" result="shape" />
                        </filter>
                    </defs>
                </svg>
                <p className="text-lg text-gray-800 mt-9 mb-0">
                    { __( 'No modules have been activated yet.', 'wp-user-frontend' ) }
                </p>
                <p className="text-sm text-gray-500 mt-2">
                    { __( 'No modules have been activated yet.', 'wp-user-frontend' ) }
                </p>
                { isProActive && (
                    <Button
                        className="mt-4"
                        target="_blank"
                        rel="noopener noreferrer"
                        href={ `${ admin }admin.php?page=wpuf-modules` }
                    >
                        { __( 'Go To Module Page', 'wp-user-frontend' ) }
                    </Button>
                ) }
            </div>
        </div>
    );
}
