/**
 * SingleSelect Component
 *
 * A reusable single-select dropdown component with PRO badge support.
 * Used throughout the User Directory for dropdown selections.
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const SingleSelect = ({
    options = [],           // Array of { value, label, isFree?, group? }
    value,                  // Currently selected value
    onChange,               // Callback when selection changes: (value) => void
    placeholder = '',       // Placeholder text when no selection
    ProBadge = null,       // PRO badge component to show for non-free options
    maxWidth = '793px',    // Maximum width of dropdown
    className = ''         // Additional CSS classes
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);

    // Close dropdown on outside click
    useEffect(() => {
        const handleClick = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, []);

    // Get label for selected value
    const getSelectedLabel = () => {
        if (!value) return placeholder || __('Select...', 'wp-user-frontend');
        const option = options.find(opt => opt.value === value);
        return option ? option.label : placeholder || __('Select...', 'wp-user-frontend');
    };

    // Handle option selection
    const handleSelect = (option) => {
        // Only allow selection of free options if isFree is defined
        if (option.isFree === false) {
            return; // Don't select non-free options
        }

        onChange(option.value);
        setIsOpen(false);
    };

    // Group options by group property
    const groupedOptions = options.reduce((acc, option) => {
        const group = option.group || 'default';
        if (!acc[group]) acc[group] = [];
        acc[group].push(option);
        return acc;
    }, {});

    const hasGroups = Object.keys(groupedOptions).length > 1 || (Object.keys(groupedOptions).length === 1 && !groupedOptions.default);

    return (
        <div className={`wpuf-relative ${className}`} ref={dropdownRef}>
            {/* Selected Value Display */}
            <button
                type="button"
                className="wpuf-min-w-full wpuf-m-0 wpuf-leading-none wpuf-text-gray-700 wpuf-max-w-full wpuf-text-left wpuf-flex wpuf-items-center wpuf-justify-between"
                style={{
                    maxWidth: maxWidth,
                    width: '100%',
                    height: '42px',
                    borderRadius: '6px',
                    borderWidth: '1px',
                    paddingTop: '9px',
                    paddingRight: '13px',
                    paddingBottom: '9px',
                    paddingLeft: '13px',
                    backgroundColor: '#FFFFFF',
                    borderColor: '#CBD5E1',
                    borderStyle: 'solid',
                    opacity: 1,
                    boxSizing: 'border-box',
                    fontSize: '16px'
                }}
                onClick={() => setIsOpen(!isOpen)}
            >
                <span>{getSelectedLabel()}</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#374151" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {/* Dropdown Options */}
            {isOpen && (
                <div
                    className="wpuf-absolute wpuf-z-10 wpuf-mt-1 wpuf-w-full wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-[6px] wpuf-shadow-lg wpuf-max-h-60 wpuf-overflow-auto"
                    style={{ maxWidth: maxWidth }}
                >
                    <ul>
                        {hasGroups ? (
                            // Render grouped options
                            Object.entries(groupedOptions).map(([groupName, groupOptions], groupIndex) => (
                                <li key={groupName}>
                                    {groupName !== 'default' && (
                                        <div className="wpuf-px-3 wpuf-py-1 wpuf-text-xs wpuf-font-semibold wpuf-text-gray-500 wpuf-bg-gray-50">
                                            {groupName}
                                        </div>
                                    )}
                                    {groupOptions.map((option) => {
                                        const isSelected = value === option.value;
                                        const isFree = option.isFree !== false; // Default to true if not specified

                                        return (
                                            <li
                                                key={option.value}
                                                className={`wpuf-p-3 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-transition-colors ${
                                                    isFree
                                                        ? 'wpuf-cursor-pointer hover:wpuf-bg-gray-50'
                                                        : 'wpuf-cursor-not-allowed wpuf-bg-gray-50 wpuf-opacity-60'
                                                } ${isSelected ? 'wpuf-bg-emerald-50' : ''}`}
                                                onClick={() => handleSelect(option)}
                                            >
                                                <span className={`wpuf-font-medium ${isSelected ? 'wpuf-text-emerald-600' : isFree ? 'wpuf-text-gray-900' : 'wpuf-text-gray-400'}`}>
                                                    {option.label}
                                                </span>
                                                {!isFree && ProBadge && <ProBadge />}
                                            </li>
                                        );
                                    })}
                                </li>
                            ))
                        ) : (
                            // Render flat options
                            options.map((option) => {
                                const isSelected = value === option.value;
                                const isFree = option.isFree !== false; // Default to true if not specified

                                return (
                                    <li
                                        key={option.value}
                                        className={`wpuf-p-3 wpuf-flex wpuf-items-center wpuf-justify-between wpuf-transition-colors ${
                                            isFree
                                                ? 'wpuf-cursor-pointer hover:wpuf-bg-gray-50'
                                                : 'wpuf-cursor-not-allowed wpuf-bg-gray-50 wpuf-opacity-60'
                                        } ${isSelected ? 'wpuf-bg-emerald-50' : ''}`}
                                        onClick={() => handleSelect(option)}
                                    >
                                        <span className={`wpuf-font-medium ${isSelected ? 'wpuf-text-emerald-600' : isFree ? 'wpuf-text-gray-900' : 'wpuf-text-gray-400'}`}>
                                            {option.label}
                                        </span>
                                        {!isFree && ProBadge && <ProBadge />}
                                    </li>
                                );
                            })
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default SingleSelect;
