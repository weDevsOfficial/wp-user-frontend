/**
 * Layout Card Component
 *
 * Reusable layout selection card with Pro badge and overlay
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

const LayoutCard = ({ layout, layoutKey, isSelected, isPro, onSelect, upgradeUrl, i18n }) => {
    const [imageError, setImageError] = useState(false);

    const handleClick = () => {
        if (!isPro) {
            onSelect(layoutKey);
        }
    };

    const baseClass = "wpuf-relative wpuf-bg-white wpuf-border-2 wpuf-rounded-xl wpuf-p-3 wpuf-transition-all wpuf-overflow-hidden";
    
    let stateClass = "";
    if (isSelected) {
        stateClass = "wpuf-border-indigo-600 wpuf-bg-indigo-50";
    } else if (isPro) {
        stateClass = "wpuf-border-gray-200 wpuf-cursor-not-allowed";
    } else {
        stateClass = "wpuf-border-gray-200 hover:wpuf-border-gray-300 hover:wpuf-shadow-md wpuf-cursor-pointer";
    }

    return (
        <div
            className={`${baseClass} ${stateClass} wpuf-group`}
            onClick={handleClick}
        >
            {/* Image */}
            {layout.image && !imageError ? (
                <img
                    src={layout.image}
                    alt={layout.name}
                    className={`wpuf-w-full wpuf-h-28 wpuf-object-cover wpuf-rounded-lg wpuf-mb-3 ${isPro ? 'wpuf-grayscale wpuf-opacity-60 group-hover:wpuf-grayscale-0 group-hover:wpuf-opacity-100 wpuf-transition-all' : ''}`}
                    onError={() => setImageError(true)}
                />
            ) : (
                <div className="wpuf-w-full wpuf-h-28 wpuf-bg-gradient-to-br wpuf-from-indigo-100 wpuf-to-purple-100 wpuf-rounded-lg wpuf-mb-3 wpuf-flex wpuf-items-center wpuf-justify-center">
                    <span className="wpuf-text-xs wpuf-font-semibold wpuf-text-indigo-600 wpuf-uppercase wpuf-tracking-wider">
                        {layout.name}
                    </span>
                </div>
            )}

            {/* Layout Name */}
            <p className="wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-text-center">
                {layout.name}
            </p>

            {/* Pro Badge */}
            {isPro && (
                <span className="wpuf-absolute wpuf-top-2 wpuf-right-2 wpuf-px-2 wpuf-py-1 wpuf-text-[10px] wpuf-font-bold wpuf-text-white wpuf-bg-gradient-to-r wpuf-from-amber-500 wpuf-to-amber-600 wpuf-rounded wpuf-uppercase wpuf-tracking-wide">
                    PRO
                </span>
            )}

            {/* Pro Overlay on Hover */}
            {isPro && (
                <div className="wpuf-absolute wpuf-inset-0 wpuf-bg-gray-900/80 wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-gap-3 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-transition-opacity wpuf-rounded-xl">
                    <span className="wpuf-text-white wpuf-text-sm wpuf-font-semibold">
                        {i18n?.pro_feature || __('Pro Feature', 'wp-user-frontend')}
                    </span>
                    <a
                        href={upgradeUrl || '#'}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="wpuf-px-4 wpuf-py-2 wpuf-bg-indigo-600 wpuf-text-white wpuf-text-xs wpuf-font-medium wpuf-rounded-md hover:wpuf-bg-indigo-700 wpuf-no-underline wpuf-transition-colors"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {i18n?.upgrade_to_pro || __('Upgrade to Pro', 'wp-user-frontend')}
                    </a>
                </div>
            )}

            {/* Selected Checkmark */}
            {isSelected && (
                <div className="wpuf-absolute wpuf-top-2 wpuf-left-2 wpuf-w-6 wpuf-h-6 wpuf-bg-indigo-600 wpuf-rounded-full wpuf-flex wpuf-items-center wpuf-justify-center">
                    <svg className="wpuf-w-4 wpuf-h-4 wpuf-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            )}
        </div>
    );
};

export default LayoutCard;
