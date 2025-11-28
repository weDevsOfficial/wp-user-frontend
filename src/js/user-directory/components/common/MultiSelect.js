import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

    const MultiSelect = ({ options, value, onChange, placeholder, sortable = false, searchable = true, selectedLabel = 'items' }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [dragIndex, setDragIndex] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const dropdownRef = useRef(null);
    const searchInputRef = useRef(null);

    // Helper function to get display text from option value
    const getOptionDisplayText = (key) => {
        const option = options[key];
        if (typeof option === 'string') {
            return option;
        }
        if (option && typeof option === 'object' && option.label) {
            return option.label;
        }
        return key;
    };

    // Helper function to get icon from option value with dynamic coloring
    const getOptionIcon = (key, isSelected = false) => {
        const option = options[key];
        if (option && typeof option === 'object' && option.icon) {
            // Clone the icon and modify its fill color if selected
            if (isSelected) {
                return React.cloneElement(option.icon, {
                    children: React.cloneElement(option.icon.props.children, {
                        fill: '#059669' // Emerald-600
                    })
                });
            }
            // For unselected state, add transition styles
            return React.cloneElement(option.icon, {
                children: React.cloneElement(option.icon.props.children, {
                    style: {
                        transition: 'fill 0.2s ease-in-out'
                    }
                })
            });
        }
        return null;
    };

    // Close dropdown on outside click
    useEffect(() => {
        const handleClick = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setIsOpen(false);
                setSearchTerm('');
            }
        };
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, []);

    // Focus search input when dropdown opens
    useEffect(() => {
        if (isOpen && searchable && searchInputRef.current) {
            setTimeout(() => {
                searchInputRef.current?.focus();
            }, 100);
        }
    }, [isOpen, searchable]);

    // Handle select/deselect with 'all' logic
    const handleSelect = (key) => {
        if (key === 'all') {
            onChange(['all']);
            setIsOpen(false);
            setSearchTerm('');
            return;
        }
        let newValue = value.filter((k) => k !== 'all');
        if (!newValue.includes(key)) {
            newValue = [...newValue, key];
        }
        onChange(newValue);
        setIsOpen(false);
        setSearchTerm('');
    };
    const handleRemove = (key) => {
        if (key === 'all') {
            onChange([]);
        } else {
            const newValue = value.filter((k) => k !== key);
            onChange(newValue);
        }
    };

    // Drag and drop handlers (only if sortable)
    const handleDragStart = (index) => sortable && setDragIndex(index);
    const handleDragOver = (index) => {
        if (!sortable || dragIndex === null || dragIndex === index) return;
        const newValue = [...value];
        const [removed] = newValue.splice(dragIndex, 1);
        newValue.splice(index, 0, removed);
        setDragIndex(index);
        onChange(newValue);
    };
    const handleDragEnd = () => sortable && setDragIndex(null);

    // Filter options based on search term and already selected values
    const getFilteredOptions = () => {
        let filtered = Object.keys(options).filter((key) => !value.includes(key));

        if (searchable && searchTerm.trim()) {
            const searchLower = searchTerm.toLowerCase();
            filtered = filtered.filter((key) => {
                const displayText = getOptionDisplayText(key);
                return displayText.toLowerCase().includes(searchLower);
            });
        }

        return filtered;
    };

    const availableOptions = getFilteredOptions();

    // Get display text for selected values
    const getDisplayText = () => {
        if (value.length === 0) {
            return placeholder;
        }
        // Show count if more than 3 items selected
        if (value.length > 3) {
            return `${value.length} ${selectedLabel} selected`;
        }
        // Show first 3 items if 3 or fewer selected
        return value.slice(0, 3).map((k) => getOptionDisplayText(k)).join(', ');
    };

    // Handle search input change
    const handleSearchChange = (e) => {
        setSearchTerm(e.target.value);
    };

    // Handle search input keydown
    const handleSearchKeyDown = (e) => {
        if (e.key === 'Escape') {
            setIsOpen(false);
            setSearchTerm('');
        } else if (e.key === 'Enter' && availableOptions.length > 0) {
            handleSelect(availableOptions[0]);
        }
    };

    return (
        <div className="wpuf-ud-block wpuf-relative" ref={dropdownRef}>
            <button
                type="button"
                className="wpuf-flex wpuf-w-full wpuf-min-w-full wpuf-text-gray-700 wpuf-font-normal wpuf-leading-none focus:wpuf-ring-transparent wpuf-text-left wpuf-items-center wpuf-justify-between"
                style={{
                    maxWidth: '793px',
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
                    boxSizing: 'border-box'
                }}
                aria-haspopup="listbox"
                aria-expanded={isOpen}
                onClick={() => setIsOpen((open) => !open)}
            >
                <span
                    className={value.length === 0 ? 'wpuf-text-gray-400' : 'wpuf-text-gray-700'}
                    style={{
                        fontSize: '16px',
                        display: 'block',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                        maxWidth: 'calc(100% - 24px)'
                    }}
                >
                    {getDisplayText()}
                </span>
                <svg className="wpuf-w-4 wpuf-h-4 wpuf-ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            {isOpen && (
                <ul className="wpuf-absolute wpuf-z-10 wpuf-mt-1 wpuf-w-full wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-[6px] wpuf-shadow-lg wpuf-max-h-60 wpuf-overflow-auto" role="listbox">
                    {/* Search input */}
                    {searchable && (
                        <li className="wpuf-sticky wpuf-top-0 wpuf-bg-white wpuf-border-b wpuf-border-gray-200 wpuf-p-2">
                            <input
                                ref={searchInputRef}
                                type="text"
                                value={searchTerm}
                                onChange={handleSearchChange}
                                onKeyDown={handleSearchKeyDown}
                                placeholder={__('Search options...', 'wp-user-frontend')}
                                className="wpuf-w-full wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-border wpuf-border-gray-300 wpuf-rounded-[4px] focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-blue-500 focus:wpuf-border-transparent"
                            />
                        </li>
                    )}
                    {availableOptions.length === 0 && (
                        <li className="wpuf-p-3 wpuf-text-gray-400 wpuf-text-sm !wpuf-mb-0">
                            {searchTerm.trim() ? __('No matching options', 'wp-user-frontend') : __('No more options', 'wp-user-frontend')}
                        </li>
                    )}
                    {availableOptions.map((key) => (
                        <li
                            key={key}
                            className="wpuf-cursor-pointer wpuf-p-3 !wpuf-mb-0 hover:wpuf-bg-gray-100 wpuf-text-base wpuf-text-left"
                            role="option"
                            aria-selected={false}
                            tabIndex={0}
                            onClick={() => handleSelect(key)}
                            onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { handleSelect(key); } }}
                        >
                            <span className="wpuf-flex wpuf-items-center">
                                {getOptionIcon(key)}
                                {getOptionDisplayText(key)}
                            </span>
                        </li>
                    ))}
                </ul>
            )}
            {/* Pills for selected items */}
            <div className="wpuf-flex wpuf-flex-wrap wpuf-gap-2 wpuf-mt-3">
                {value.map((key, idx) => (
                    <div
                        key={key}
                        className={
                            'wpuf-group/item wpuf-flex wpuf-items-center wpuf-bg-gray-50 wpuf-border wpuf-border-gray-200 wpuf-rounded-[5px] wpuf-px-3 wpuf-py-1 wpuf-text-base wpuf-shadow-sm ' +
                            (sortable ? 'wpuf-cursor-move' : '') +
                            ' wpuf-transition-colors wpuf-duration-150 hover:wpuf-border-emerald-600 hover:wpuf-bg-emerald-50 [&:hover_svg_path]:wpuf-fill-emerald-600'
                        }
                        draggable={sortable}
                        onDragStart={sortable ? () => handleDragStart(idx) : undefined}
                        onDragOver={sortable ? (e) => { e.preventDefault(); handleDragOver(idx); } : undefined}
                        onDragEnd={sortable ? handleDragEnd : undefined}
                        tabIndex={0}
                        aria-label={sortable ? __('Drag to reorder', 'wp-user-frontend') : undefined}
                    >
                        <span className="wpuf-text-gray-800 wpuf-flex wpuf-items-center">
                            {getOptionIcon(key)}
                            {getOptionDisplayText(key)}
                        </span>
                        <button
                            type="button"
                            className="wpuf-ml-1 wpuf-text-green-600 wpuf-text-xs hover:wpuf-bg-green-50 wpuf-opacity-0 group-hover/item:wpuf-opacity-100 wpuf-transition-opacity wpuf-duration-150 wpuf-w-4 wpuf-h-4 wpuf-flex wpuf-items-center wpuf-justify-center"
                            aria-label={__('Remove', 'wp-user-frontend')}
                            onClick={() => handleRemove(key)}
                        >
                            ×
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default MultiSelect;
