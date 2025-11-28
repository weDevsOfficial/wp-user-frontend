/**
 * User Directory Free - Header Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React from 'react';
import { __ } from '@wordpress/i18n';

// Pro Badge Component
const ProBadge = ({ className = '' }) => (
    <span className={`wpuf-inline-flex wpuf-items-center wpuf-px-2 wpuf-py-0.5 wpuf-rounded wpuf-text-xs wpuf-font-medium wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-indigo-600 wpuf-text-white ${className}`}>
        PRO
    </span>
);

const Header = ({ utm = 'wpuf-header' }) => {
    // Get wpuf_admin_script from global
    const wpuf = window.wpuf_admin_script || {};
    const logoUrl = (wpuf.asset_url || '') + '/images/wpuf-icon-circle.svg';
    const upgradeUrl = (wpuf.upgradeUrl || 'https://wedevs.com/wp-user-frontend-pro/pricing/') + '?utm_source=' + utm + '&utm_medium=wpuf-header';
    const version = wpuf.version || '';

    return (
        <div className="!wpuf-w-[calc(100%+40px)] !wpuf-ml-[-20px] !wpuf-px-[20px] !wpuf-py-[20px] !wpuf-flex !wpuf-justify-between !wpuf-items-center !wpuf-border-b-2 !wpuf-border-gray-100">
            <div className="wpuf-flex wpuf-justify-start wpuf-items-center">
                <img src={logoUrl} alt="WPUF Icon" className="wpuf-w-12 wpuf-mr-4" />
                <h2 className="wpuf-text-2xl wpuf-leading-7 wpuf-font-bold wpuf-m-0">{__('WP User Frontend', 'wp-user-frontend')}</h2>
                {version && (
                    <span className="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-gray-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-gray-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-600/20">
                        v{version}
                    </span>
                )}
            </div>
            <div className="wpuf-flex wpuf-justify-end wpuf-items-center wpuf-gap-3">
                {/* Submit Ideas Link */}
                <a
                    className="wpuf-border wpuf-border-gray-100 wpuf-text-center wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-shadow-sm hover:wpuf-bg-slate-100 focus:wpuf-bg-slate-100 wpuf-no-underline wpuf-text-gray-700"
                    target="_blank"
                    rel="noopener noreferrer"
                    href="https://wpuf.canny.io/ideas"
                >
                    💡 {__('Submit Ideas', 'wp-user-frontend')}
                </a>
                
                {/* Upgrade to Pro Button */}
                <a
                    href={upgradeUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-indigo-600 wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-indigo-700 hover:wpuf-text-white wpuf-no-underline wpuf-flex wpuf-items-center wpuf-gap-2"
                >
                    <svg className="wpuf-w-4 wpuf-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    {__('Upgrade to Pro', 'wp-user-frontend')}
                </a>
            </div>
        </div>
    );
};

export default Header;
