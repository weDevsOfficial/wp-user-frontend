/**
 * Toast Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React, { useEffect } from 'react';

const Toast = ({ message, type = 'success', onClose, duration = 3000 }) => {
    useEffect(() => {
        const timer = setTimeout(() => {
            onClose();
        }, duration);
        
        return () => clearTimeout(timer);
    }, [duration, onClose]);

    const bgColor = type === 'success' ? 'wpuf-bg-emerald-50' : 'wpuf-bg-red-50';
    const textColor = type === 'success' ? 'wpuf-text-emerald-800' : 'wpuf-text-red-800';
    const borderColor = type === 'success' ? 'wpuf-border-emerald-200' : 'wpuf-border-red-200';
    const iconColor = type === 'success' ? 'wpuf-text-emerald-500' : 'wpuf-text-red-500';

    return (
        <div className="wpuf-fixed wpuf-bottom-24 wpuf-right-8 wpuf-z-50 wpuf-animate-fade-in">
            <div className={`wpuf-flex wpuf-items-center wpuf-gap-3 wpuf-px-4 wpuf-py-3 ${bgColor} ${borderColor} wpuf-border wpuf-rounded-lg wpuf-shadow-lg`}>
                {type === 'success' ? (
                    <svg className={`wpuf-w-5 wpuf-h-5 ${iconColor}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                ) : (
                    <svg className={`wpuf-w-5 wpuf-h-5 ${iconColor}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                )}
                <span className={`wpuf-text-sm wpuf-font-medium ${textColor}`}>{message}</span>
                <button
                    onClick={onClose}
                    className="wpuf-ml-2 wpuf-text-gray-400 hover:wpuf-text-gray-600"
                >
                    <svg className="wpuf-w-4 wpuf-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    );
};

export default Toast;
