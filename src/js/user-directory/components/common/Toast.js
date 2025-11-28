/**
 * Toast Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';

const Toast = ({ message, type = 'success', duration = 3000, onClose }) => {
    const [isVisible, setIsVisible] = useState(true);
    const [isLeaving, setIsLeaving] = useState(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            setIsLeaving(true);
            setTimeout(() => {
                setIsVisible(false);
                onClose && onClose();
            }, 300);
        }, duration);

        return () => clearTimeout(timer);
    }, [duration, onClose]);

    if (!isVisible) return null;

    const typeStyles = {
        success: 'wpuf-bg-green-700',
        error: 'wpuf-bg-red-500',
        warning: 'wpuf-bg-yellow-500',
        info: 'wpuf-bg-blue-500'
    };

    const icons = {
        success: (
            <svg className="wpuf-w-5 wpuf-h-5 wpuf-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
            </svg>
        ),
        error: (
            <svg className="wpuf-w-5 wpuf-h-5 wpuf-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        ),
        warning: (
            <svg className="wpuf-w-5 wpuf-h-5 wpuf-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        ),
        info: (
            <svg className="wpuf-w-5 wpuf-h-5 wpuf-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        )
    };

    const toastElement = (
        <div className={`wpuf-fixed wpuf-top-8 wpuf-right-8 wpuf-z-[99999] wpuf-transition-all wpuf-duration-300 wpuf-transform ${isLeaving ? 'wpuf-translate-x-full wpuf-opacity-0' : 'wpuf-translate-x-0 wpuf-opacity-100'}`}>
            <div className={`${typeStyles[type]} wpuf-rounded-lg wpuf-shadow-xl wpuf-p-4 wpuf-min-w-[300px] wpuf-max-w-[400px] wpuf-m-4`}>
                <div className="wpuf-flex wpuf-items-center">
                    <div className="wpuf-flex-shrink-0">
                        {icons[type]}
                    </div>
                    <div className="wpuf-ml-3 wpuf-flex-1">
                        <p className="wpuf-text-sm wpuf-font-medium wpuf-text-white">
                            {message}
                        </p>
                    </div>
                    <button
                        onClick={() => {
                            setIsLeaving(true);
                            setTimeout(() => {
                                setIsVisible(false);
                                onClose && onClose();
                            }, 300);
                        }}
                        className="wpuf-ml-4 wpuf-flex-shrink-0 wpuf-inline-flex wpuf-text-white hover:wpuf-text-gray-200 focus:wpuf-outline-none"
                    >
                        <svg className="wpuf-w-4 wpuf-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    );

    // Render the toast using a portal to ensure it appears above everything else
    return createPortal(toastElement, document.body);
};

export default Toast;
