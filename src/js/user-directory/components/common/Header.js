/**
 * User Directory Free - Header Component
 * Matches the Post Forms page header design
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React, { useEffect } from 'react';
import { __ } from '@wordpress/i18n';

const Header = ({ utm = 'wpuf-user-directory' }) => {
    // Get wpuf_admin_script from global
    const wpuf = window.wpuf_admin_script || {};
    const logoUrl = (wpuf.asset_url || '') + '/images/wpuf-icon-circle.svg';
    const upgradeUrl = (wpuf.upgradeUrl || 'https://wedevs.com/wp-user-frontend-pro/pricing/') + '?utm_source=' + utm + '&utm_medium=wpuf-header';
    const supportUrl = wpuf.support_url || 'https://wedevs.com/account/tickets/';
    const version = wpuf.version || '';
    const isProActive = wpuf.isProActive || false;

    // Initialize Headway widget on mount
    useEffect(() => {
        // Headway configuration if available
        if (window.Headway) {
            window.Headway.init({
                selector: '#wpuf-user-directory-headway-icon',
                account: 'xGYQ1y',
            });
        }
    }, []);

    return (
        <div className="wpuf-w-[calc(100%+40px)] wpuf-ml-[-20px] wpuf-px-[20px] wpuf-flex wpuf-mt-4 wpuf-justify-between wpuf-items-center wpuf-border-b-2 wpuf-border-gray-100 wpuf-pb-4">
            <div className="wpuf-flex wpuf-justify-start wpuf-items-center">
                <img src={logoUrl} alt="WPUF Icon" className="wpuf-w-12 wpuf-mr-4" />
                <h2 className="wpuf-text-2xl wpuf-leading-7 wpuf-font-bold wpuf-m-0">{__('WP User Frontend', 'wp-user-frontend')}</h2>
                {version && (
                    <span className="wpuf-ml-2 wpuf-inline-flex wpuf-items-center wpuf-rounded-full wpuf-bg-green-100 wpuf-px-2 wpuf-py-1 wpuf-text-xs wpuf-font-medium wpuf-text-green-700 wpuf-ring-1 wpuf-ring-inset wpuf-ring-green-600/20">
                        v{version}
                    </span>
                )}
                {!isProActive && (
                    <a
                        href={upgradeUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="wpuf-btn-primary wpuf-flex wpuf-ml-4 wpuf-p-2 wpuf-no-underline"
                    >
                        {__('Upgrade to PRO', 'wp-user-frontend')}
                    </a>
                )}
            </div>
            <div className="wpuf-flex wpuf-justify-end wpuf-items-center wpuf-w-2/4">
                {/* Headway Icon */}
                <span
                    id="wpuf-user-directory-headway-icon"
                    className="wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-rounded-full wpuf-p-1 wpuf-shadow-sm hover:wpuf-bg-slate-100 focus-visible:wpuf-outline focus-visible:wpuf-outline-2 focus-visible:wpuf-outline-offset-2"
                ></span>
                
                {/* Submit Ideas Link */}
                <a
                    className="wpuf-border wpuf-border-gray-100 wpuf-mr-[16px] wpuf-canny-link wpuf-text-center wpuf-rounded-md wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-shadow-sm hover:wpuf-bg-slate-100 focus:wpuf-bg-slate-100 wpuf-no-underline wpuf-text-gray-700"
                    target="_blank"
                    rel="noopener noreferrer"
                    href="https://wpuf.canny.io/ideas"
                >
                    💡 {__('Submit Ideas', 'wp-user-frontend')}
                </a>
                
                {/* Support Button */}
                <a
                    href={supportUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white wpuf-no-underline"
                >
                    {__('Support', 'wp-user-frontend')}
                    &nbsp;&nbsp;
                    <span className="dashicons dashicons-businessman"></span>
                </a>
            </div>
        </div>
    );
};

export default Header;
