import { __ } from '@wordpress/i18n';
import { useState, useEffect, useRef } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

export default function UpdateButton({ buttonText, onUpdateSubscription }) {
    const [showDropdown, setShowDropdown] = useState(false);
    const dropdownRef = useRef(null);

    const { isUpdating, item } = useSelect((select) => {
        const store = select('wpuf/subscriptions');
        return {
            isUpdating: store.isUpdating(),
            item: store.getItem(),
        };
    }, []);

    const { setItem } = useDispatch('wpuf/subscriptions');

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        };

        if (showDropdown) {
            document.addEventListener('mousedown', handleClickOutside);
        }

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [showDropdown]);

    const handlePublish = () => {
        if (item) {
            const updatedItem = {
                ...item,
                post_status: 'publish',
            };
            setItem(updatedItem);
            onUpdateSubscription();
        }
        setShowDropdown(false);
    };

    const handleDraft = () => {
        if (item) {
            const updatedItem = {
                ...item,
                post_status: 'draft',
            };
            setItem(updatedItem);
            onUpdateSubscription();
        }
        setShowDropdown(false);
    };

    const displayText = buttonText || __('Update', 'wp-user-frontend');

    return (
        <div className="wpuf-relative" ref={dropdownRef}>
            <button
                disabled={isUpdating}
                onClick={() => setShowDropdown(!showDropdown)}
                className={`wpuf-peer wpuf-inline-flex wpuf-justify-between wpuf-items-center wpuf-cursor-pointer wpuf-bg-primary hover:wpuf-bg-primaryHover wpuf-text-white wpuf-font-medium wpuf-text-base wpuf-py-2 wpuf-px-5 wpuf-rounded-md min-w-[122px] ${
                    isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
                }`}
            >
                {displayText}
                <svg
                    className="wpuf-rotate-180 wpuf-w-3 wpuf-h-3 shrink-0 wpuf-ml-4"
                    data-accordion-icon=""
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 10 6"
                >
                    <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5 5 1 1 5"></path>
                </svg>
            </button>
            {showDropdown && (
                <div className="wpuf-cursor-pointer wpuf-w-44 wpuf-z-40 wpuf-bg-white wpuf-border border-[#DBDBDB] wpuf-absolute wpuf-z-10 wpuf-shadow wpuf-right-0 wpuf-rounded-md after:content-[''] before:content-[''] after:wpuf-absolute before:wpuf-absolute after:w-[13px] before:w-[70%] before:-right-[1px] after:h-[13px] before:wpuf-h-3 before:wpuf-mt-3 after:top-[-7px] before:wpuf--top-6 after:right-[1.4rem] after:z-[-1] after:wpuf-bg-white after:wpuf-border after:border-[#DBDBDB] after:!rotate-45 after:wpuf-border-r-0 after:wpuf-border-b-0">
                    <span
                        onClick={handlePublish}
                        className={`wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-primaryHover hover:wpuf-text-white wpuf-rounded-t-md ${
                            isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
                        }`}
                    >
                        {__('Publish', 'wp-user-frontend')}
                    </span>
                    <span
                        onClick={handleDraft}
                        className={`wpuf-flex wpuf-py-3 wpuf-items-center wpuf-px-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-primaryHover hover:wpuf-text-white wpuf-rounded-b-md ${
                            isUpdating ? 'wpuf-cursor-not-allowed wpuf-bg-gray-50' : ''
                        }`}
                    >
                        {__('Save as Draft', 'wp-user-frontend')}
                    </span>
                </div>
            )}
        </div>
    );
}

