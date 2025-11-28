import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

const StepTabs = ({ formData, setFormData, config }) => {
    // Initialize tabs from config or use defaults
    const defaultTabs = config?.profile_tabs || {
        about: __('About', 'wp-user-frontend'),
        posts: __('Posts', 'wp-user-frontend'),
        comments: __('Comments', 'wp-user-frontend'),
    };

    // Helper to ensure we have a valid array for enabled tabs
    const getEnabledTabsArray = (profileTabs) => {
        if (Array.isArray(profileTabs)) {
            return profileTabs;
        }
        if (profileTabs && typeof profileTabs === 'object') {
            // If it's an object (from Pro), extract keys that are enabled
            return Object.keys(profileTabs).filter(key => profileTabs[key]);
        }
        return ['about']; // Default
    };

    const getOrderArray = (order) => {
        if (Array.isArray(order)) {
            return order;
        }
        return [];
    };

    // State for draggable tabs
    const [allTabs, setAllTabs] = useState(() => {
        const savedOrder = getOrderArray(formData.profile_tabs_order);
        const enabledTabs = getEnabledTabsArray(formData.profile_tabs);
        
        // Create tabs array
        const tabsMap = {};
        Object.keys(defaultTabs).forEach(key => {
            tabsMap[key] = {
                key,
                label: typeof defaultTabs[key] === 'string' 
                    ? defaultTabs[key] 
                    : defaultTabs[key]?.label || key,
                enabled: enabledTabs.includes(key)
            };
        });
        
        // Order tabs based on saved order
        const orderedTabs = [];
        if (savedOrder.length > 0) {
            savedOrder.forEach(key => {
                if (tabsMap[key]) {
                    orderedTabs.push(tabsMap[key]);
                    delete tabsMap[key];
                }
            });
        }
        
        // Add remaining tabs
        Object.values(tabsMap).forEach(tab => orderedTabs.push(tab));
        
        return orderedTabs;
    });

    const [enabledTabs] = useState(() => getEnabledTabsArray(formData.profile_tabs));
    const [draggedItem, setDraggedItem] = useState(null);
    const [dragOverItem, setDragOverItem] = useState(null);
    const [hoveredTab, setHoveredTab] = useState(null);
    const [hoveredContentType, setHoveredContentType] = useState(null);

    // Drag and drop handlers
    const handleDragStart = (e, index) => {
        setDraggedItem(index);
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleDragEnd = (e) => {
        e.preventDefault();
        setDraggedItem(null);
        setDragOverItem(null);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    };

    const handleDragEnter = (e, index) => {
        if (draggedItem !== index) {
            setDragOverItem(index);
        }
    };

    const handleDragLeave = (e) => {
        if (e.target === e.currentTarget) {
            setDragOverItem(null);
        }
    };

    const handleDrop = (e, dropIndex) => {
        e.stopPropagation();
        e.preventDefault();
        
        if (draggedItem !== null && draggedItem !== dropIndex) {
            const newTabs = [...allTabs];
            const draggedTab = newTabs[draggedItem];
            
            newTabs.splice(draggedItem, 1);
            newTabs.splice(dropIndex, 0, draggedTab);
            
            setAllTabs(newTabs);
            
            // Update form data with new order only (toggle is Pro feature)
            const completeOrder = newTabs.map(t => t.key);
            setFormData(prev => ({
                ...prev,
                profile_tabs_order: completeOrder
            }));
        }
        
        setDraggedItem(null);
        setDragOverItem(null);
    };

    return (
        <div className="wpuf-w-full wpuf-bg-white wpuf-max-w-3xl wpuf-mx-auto">
            <h2 className="wpuf-text-center"
                style={{
                    fontWeight: 400,
                    fontSize: '20px',
                    lineHeight: '36px',
                    letterSpacing: '0%',
                    textAlign: 'center',
                    color: '#000000'
                }}
            >
                {__('Profile Content', 'wp-user-frontend')}
            </h2>
            <p className="wpuf-text-center wpuf-mt-2 wpuf-mb-2"
                style={{
                    fontWeight: 400,
                    fontSize: '14px',
                    lineHeight: '20px',
                    letterSpacing: '0%',
                    textAlign: 'center',
                    color: '#64748B'
                }}
            >{__('Customize user tab layout and content for an organized profile experience', 'wp-user-frontend')}</p>

            {/* Tabs Section */}
            <div className="wpuf-mt-8">
                <h3 className="wpuf-text-lg wpuf-font-normal wpuf-text-gray-900 wpuf-mb-2">
                    {__('Customize Profile Tabs', 'wp-user-frontend')}
                </h3>
                <p className="wpuf-text-sm wpuf-text-gray-500 wpuf-mb-4">
                    {__('Easily manage your profile tabs. Drag to reorder, toggle visibility on the front end, and customize tab names using the edit icon', 'wp-user-frontend')}
                </p>
                
                {/* Draggable Tabs */}
                <div className="wpuf-space-y-2">
                    {allTabs.map((tab, index) => {
                        const isEnabled = enabledTabs.includes(tab.key);
                        const isDragging = draggedItem === index;
                        const isDragOver = dragOverItem === index;
                        const isHovered = hoveredTab === tab.key;
                        
                        return (
                            <div
                                key={tab.key}
                                className={`
                                    wpuf-flex wpuf-items-center wpuf-justify-between 
                                    wpuf-bg-white wpuf-border wpuf-rounded-md 
                                    wpuf-px-4 wpuf-py-4 wpuf-h-[52px] wpuf-w-full
                                    wpuf-transition-all wpuf-cursor-move
                                    ${isDragging ? 'wpuf-opacity-50 wpuf-scale-[0.98]' : ''}
                                    ${isDragOver ? 'wpuf-border-emerald-500 wpuf-bg-emerald-50' : 'wpuf-border-gray-200'}
                                `}
                                draggable={true}
                                onDragStart={(e) => handleDragStart(e, index)}
                                onDragEnd={handleDragEnd}
                                onDragOver={handleDragOver}
                                onDragEnter={(e) => handleDragEnter(e, index)}
                                onDragLeave={handleDragLeave}
                                onDrop={(e) => handleDrop(e, index)}
                                onMouseEnter={() => setHoveredTab(tab.key)}
                                onMouseLeave={() => setHoveredTab(null)}
                            >
                                <div className="wpuf-flex wpuf-items-center wpuf-flex-1">
                                    {/* Drag Handle */}
                                    <svg className="wpuf-w-5 wpuf-h-5 wpuf-mr-4 wpuf-cursor-move" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.125 5.625H16.875M3.125 10H16.875M3.125 14.375H16.875" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                    
                                    {/* Tab Name */}
                                    <span className="wpuf-text-[15px] wpuf-text-gray-700 wpuf-font-medium wpuf-min-w-[120px]">
                                        {tab.label}
                                    </span>
                                </div>
                                
                                <div className="wpuf-flex wpuf-items-center wpuf-gap-3 wpuf-relative">
                                    {/* Pro Badge on hover - positioned in center above both icons */}
                                    {isHovered && (
                                        <div 
                                            className="wpuf-absolute wpuf-z-10"
                                            style={{ 
                                                top: '-28px', 
                                                left: '50%', 
                                                transform: 'translateX(-50%)' 
                                            }}
                                        >
                                            <img 
                                                src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                                alt="Pro" 
                                                style={{ width: '32px', height: '18px' }}
                                            />
                                        </div>
                                    )}
                                    
                                    {/* Edit Button - Pro Only (same icon as Pro version) */}
                                    <svg 
                                        className="wpuf-w-[18px] wpuf-h-[18px] wpuf-cursor-not-allowed wpuf-opacity-50"
                                        viewBox="0 0 18 18"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path d="M15.8898 2.11019L16.4201 1.57986V1.57986L15.8898 2.11019ZM4.41667 16.5296V17.2796C4.61558 17.2796 4.80634 17.2005 4.947 17.0599L4.41667 16.5296ZM1.5 16.5296H0.75C0.75 16.9438 1.08579 17.2796 1.5 17.2796L1.5 16.5296ZM1.5 13.5537L0.96967 13.0233C0.829018 13.164 0.75 13.3548 0.75 13.5537H1.5ZM12.9435 2.11019L13.4738 2.64052C13.9945 2.11983 14.8387 2.11983 15.3594 2.64052L15.8898 2.11019L16.4201 1.57986C15.3136 0.473379 13.5196 0.473379 12.4132 1.57986L12.9435 2.11019ZM15.8898 2.11019L15.3594 2.64052C15.8801 3.16122 15.8801 4.00544 15.3594 4.52614L15.8898 5.05647L16.4201 5.5868C17.5266 4.48032 17.5266 2.68635 16.4201 1.57986L15.8898 2.11019ZM15.8898 5.05647L15.3594 4.52614L3.88634 15.9992L4.41667 16.5296L4.947 17.0599L16.4201 5.5868L15.8898 5.05647ZM4.41667 16.5296V15.7796H1.5V16.5296V17.2796H4.41667V16.5296ZM12.9435 2.11019L12.4132 1.57986L0.96967 13.0233L1.5 13.5537L2.03033 14.084L13.4738 2.64052L12.9435 2.11019ZM1.5 13.5537H0.75V16.5296H1.5H2.25V13.5537H1.5ZM11.6935 3.36019L11.1632 3.89052L14.1094 6.8368L14.6398 6.30647L15.1701 5.77614L12.2238 2.82986L11.6935 3.36019Z" fill="#6B7280"/>
                                    </svg>
                                    
                                    {/* Toggle Switch - Pro Only, always ON by default in free (same style as Pro version) */}
                                    <div 
                                        className="wpuf-relative wpuf-inline-block wpuf-transition-colors wpuf-cursor-not-allowed wpuf-opacity-50"
                                        style={{
                                            width: '36px',
                                            height: '16px',
                                            borderRadius: '8px',
                                            backgroundColor: '#059669',
                                        }}
                                    >
                                        <span 
                                            className="wpuf-absolute wpuf-transition-all"
                                            style={{
                                                width: '20px',
                                                height: '20px',
                                                borderRadius: '50%',
                                                backgroundColor: '#FFFFFF',
                                                border: '1px solid #D1D5DB',
                                                boxShadow: '0px 1px 2px 0px #0000000F, 0px 1px 3px 0px #0000001A',
                                                top: '-2px',
                                                left: '16px',
                                            }}
                                        />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Configure About Tab Section - Pro Feature Preview */}
            <div className="wpuf-mt-8">
                <div className="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-mb-2">
                    <h1
                        style={{
                            fontWeight: 400,
                            fontSize: '18px',
                            lineHeight: '28px',
                            letterSpacing: '0%',
                            color: '#000000'
                        }}
                    >
                        {__('Configure About Tab', 'wp-user-frontend')}
                    </h1>
                </div>
                
                <p className="wpuf-mb-6"
                    style={{
                        fontWeight: 400,
                        fontSize: '14px',
                        lineHeight: '20px',
                        letterSpacing: '0%',
                        color: '#64748B'
                    }}
                >
                    {__('Define content for About tab by selecting fields, post types, or files. Map custom meta—including WPUF fields—to showcase user data such as skills, achievements, media, and other key information.', 'wp-user-frontend')}
                </p>
                
                {/* Add Content Button - Disabled with Pro Badge on hover */}
                <div 
                    className="wpuf-relative wpuf-inline-block"
                    onMouseEnter={() => setHoveredContentType('add-content')}
                    onMouseLeave={() => setHoveredContentType(null)}
                >
                    <button
                        type="button"
                        className={`wpuf-text-white wpuf-font-medium wpuf-transition-colors wpuf-flex wpuf-items-center wpuf-cursor-not-allowed ${hoveredContentType === 'add-content' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                        style={{
                            width: '162px',
                            height: '42px',
                            borderRadius: '6px',
                            paddingTop: '9px',
                            paddingRight: '17px',
                            paddingBottom: '9px',
                            paddingLeft: '15px',
                            backgroundColor: '#059669',
                            gap: '12px',
                            border: hoveredContentType === 'add-content' ? '2px dashed #059669' : 'none',
                        }}
                        disabled={true}
                    >
                        <svg className="wpuf-w-4 wpuf-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span style={{
                            fontWeight: 500,
                            fontSize: '16px',
                            lineHeight: '24px',
                            letterSpacing: '0%',
                            color: '#FFFFFF'
                        }}>
                            {__('Add Content', 'wp-user-frontend')}
                        </span>
                    </button>
                    
                    {/* Pro Badge on hover */}
                    {hoveredContentType === 'add-content' && (
                        <div className="wpuf-absolute" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                            <img 
                                src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                alt="Pro" 
                                style={{ width: '32px', height: '18px' }}
                            />
                        </div>
                    )}
                </div>
                
                {/* Content Type Buttons Preview - Disabled with Pro Badges on hover */}
                <div className="wpuf-mt-4 wpuf-p-4 wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-lg" style={{ width: '590px' }}>
                    <div className="wpuf-flex wpuf-gap-2 wpuf-items-center wpuf-justify-center">
                        {/* Meta Field */}
                        <div 
                            className="wpuf-relative"
                            onMouseEnter={() => setHoveredContentType('meta-field')}
                            onMouseLeave={() => setHoveredContentType(null)}
                        >
                            {hoveredContentType === 'meta-field' && (
                                <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                    <img 
                                        src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                        alt="Pro" 
                                        style={{ width: '32px', height: '18px' }}
                                    />
                                </div>
                            )}
                            <button
                                type="button"
                                className={`wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-w-[102px] wpuf-h-[68px] wpuf-gap-1 wpuf-p-3 wpuf-bg-gray-50 wpuf-text-gray-400 wpuf-cursor-not-allowed ${hoveredContentType === 'meta-field' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                                style={{
                                    border: hoveredContentType === 'meta-field' ? '2px dashed #9CA3AF' : '1px solid #E5E7EB'
                                }}
                                disabled={true}
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.25 14.0625H14.0625M14.0625 14.0625H16.875M14.0625 14.0625V11.25M14.0625 14.0625V16.875M5 8.75H6.875C7.91053 8.75 8.75 7.91053 8.75 6.875V5C8.75 3.96447 7.91053 3.125 6.875 3.125H5C3.96447 3.125 3.125 3.96447 3.125 5V6.875C3.125 7.91053 3.96447 8.75 5 8.75ZM5 16.875H6.875C7.91053 16.875 8.75 16.0355 8.75 15V13.125C8.75 12.0895 7.91053 11.25 6.875 11.25H5C3.96447 11.25 3.125 12.0895 3.125 13.125V15C3.125 16.0355 3.96447 16.875 5 16.875ZM13.125 8.75H15C16.0355 8.75 16.875 7.91053 16.875 6.875V5C16.875 3.96447 16.0355 3.125 15 3.125H13.125C12.0895 3.125 11.25 3.96447 11.25 5V6.875C11.25 7.91053 12.0895 8.75 13.125 8.75Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                                <span className="wpuf-text-sm wpuf-font-normal wpuf-leading-5 wpuf-text-gray-400">
                                    {__('Meta Field', 'wp-user-frontend')}
                                </span>
                            </button>
                        </div>
                        
                        {/* Section */}
                        <div 
                            className="wpuf-relative"
                            onMouseEnter={() => setHoveredContentType('section')}
                            onMouseLeave={() => setHoveredContentType(null)}
                        >
                            {hoveredContentType === 'section' && (
                                <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                    <img 
                                        src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                        alt="Pro" 
                                        style={{ width: '32px', height: '18px' }}
                                    />
                                </div>
                            )}
                            <button
                                type="button"
                                className={`wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-w-[102px] wpuf-h-[68px] wpuf-gap-1 wpuf-p-3 wpuf-bg-gray-50 wpuf-text-gray-400 wpuf-cursor-not-allowed ${hoveredContentType === 'section' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                                style={{
                                    border: hoveredContentType === 'section' ? '2px dashed #9CA3AF' : '1px solid #E5E7EB'
                                }}
                                disabled={true}
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.125 10H16.875M3.125 13.125H16.875M3.125 16.25H16.875M4.6875 3.75H15.3125C16.1754 3.75 16.875 4.44956 16.875 5.3125C16.875 6.17544 16.1754 6.875 15.3125 6.875H4.6875C3.82456 6.875 3.125 6.17544 3.125 5.3125C3.125 4.44956 3.82456 3.75 4.6875 3.75Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                                <span className="wpuf-text-sm wpuf-font-normal wpuf-leading-5 wpuf-text-gray-400">
                                    {__('Section', 'wp-user-frontend')}
                                </span>
                            </button>
                        </div>
                        
                        {/* Post Type */}
                        <div 
                            className="wpuf-relative"
                            onMouseEnter={() => setHoveredContentType('post-type')}
                            onMouseLeave={() => setHoveredContentType(null)}
                        >
                            {hoveredContentType === 'post-type' && (
                                <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                    <img 
                                        src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                        alt="Pro" 
                                        style={{ width: '32px', height: '18px' }}
                                    />
                                </div>
                            )}
                            <button
                                type="button"
                                className={`wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-w-[102px] wpuf-h-[68px] wpuf-gap-1 wpuf-p-3 wpuf-bg-gray-50 wpuf-text-gray-400 wpuf-cursor-not-allowed ${hoveredContentType === 'post-type' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                                style={{
                                    border: hoveredContentType === 'post-type' ? '2px dashed #9CA3AF' : '1px solid #E5E7EB'
                                }}
                                disabled={true}
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.8333 16.6666H4.16667C3.24619 16.6666 2.5 15.9204 2.5 14.9999L2.5 4.99992C2.5 4.07944 3.24619 3.33325 4.16667 3.33325L12.5 3.33325C13.4205 3.33325 14.1667 4.07944 14.1667 4.99992V5.83325M15.8333 16.6666C14.9129 16.6666 14.1667 15.9204 14.1667 14.9999L14.1667 5.83325M15.8333 16.6666C16.7538 16.6666 17.5 15.9204 17.5 14.9999V7.49992C17.5 6.57944 16.7538 5.83325 15.8333 5.83325L14.1667 5.83325M10.8333 3.33325L7.5 3.33325M5.83333 13.3333H10.8333M5.83333 6.66659H10.8333V9.99992H5.83333V6.66659Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                                <span className="wpuf-text-sm wpuf-font-normal wpuf-leading-5 wpuf-text-gray-400">
                                    {__('Post Type', 'wp-user-frontend')}
                                </span>
                            </button>
                        </div>
                        
                        {/* Comment */}
                        <div 
                            className="wpuf-relative"
                            onMouseEnter={() => setHoveredContentType('comment')}
                            onMouseLeave={() => setHoveredContentType(null)}
                        >
                            {hoveredContentType === 'comment' && (
                                <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                    <img 
                                        src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                        alt="Pro" 
                                        style={{ width: '32px', height: '18px' }}
                                    />
                                </div>
                            )}
                            <button
                                type="button"
                                className={`wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-w-[102px] wpuf-h-[68px] wpuf-gap-1 wpuf-p-3 wpuf-bg-gray-50 wpuf-text-gray-400 wpuf-cursor-not-allowed ${hoveredContentType === 'comment' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                                style={{
                                    border: hoveredContentType === 'comment' ? '2px dashed #9CA3AF' : '1px solid #E5E7EB'
                                }}
                                disabled={true}
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.1875 10C7.1875 10.1726 7.04759 10.3125 6.875 10.3125C6.70241 10.3125 6.5625 10.1726 6.5625 10C6.5625 9.82741 6.70241 9.6875 6.875 9.6875C7.04759 9.6875 7.1875 9.82741 7.1875 10ZM7.1875 10H6.875M10.3125 10C10.3125 10.1726 10.1726 10.3125 10 10.3125C9.82741 10.3125 9.6875 10.1726 9.6875 10C9.6875 9.82741 9.82741 9.6875 10 9.6875C10.1726 9.6875 10.3125 9.82741 10.3125 10ZM10.3125 10H10M13.4375 10C13.4375 10.1726 13.2976 10.3125 13.125 10.3125C12.9524 10.3125 12.8125 10.1726 12.8125 10C12.8125 9.82741 12.9524 9.6875 13.125 9.6875C13.2976 9.6875 13.4375 9.82741 13.4375 10ZM13.4375 10H13.125M17.5 10C17.5 13.797 14.1421 16.875 10 16.875C9.26044 16.875 8.54588 16.7769 7.87098 16.5941C7.05847 17.1649 6.06834 17.5 5 17.5C4.83398 17.5 4.6698 17.4919 4.50806 17.4761C4.375 17.4631 4.24316 17.4449 4.11316 17.4216C4.5161 16.9461 4.80231 16.3689 4.92824 15.734C5.00378 15.3531 4.81725 14.9832 4.53903 14.7124C3.27475 13.4818 2.5 11.8238 2.5 10C2.5 6.20304 5.85786 3.125 10 3.125C14.1421 3.125 17.5 6.20304 17.5 10Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                                <span className="wpuf-text-sm wpuf-font-normal wpuf-leading-5 wpuf-text-gray-400">
                                    {__('Comment', 'wp-user-frontend')}
                                </span>
                            </button>
                        </div>
                        
                        {/* Image/File */}
                        <div 
                            className="wpuf-relative"
                            onMouseEnter={() => setHoveredContentType('image-file')}
                            onMouseLeave={() => setHoveredContentType(null)}
                        >
                            {hoveredContentType === 'image-file' && (
                                <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                    <img 
                                        src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
                                        alt="Pro" 
                                        style={{ width: '32px', height: '18px' }}
                                    />
                                </div>
                            )}
                            <button
                                type="button"
                                className={`wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-w-[102px] wpuf-h-[68px] wpuf-gap-1 wpuf-p-3 wpuf-bg-gray-50 wpuf-text-gray-400 wpuf-cursor-not-allowed ${hoveredContentType === 'image-file' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                                style={{
                                    border: hoveredContentType === 'image-file' ? '2px dashed #9CA3AF' : '1px solid #E5E7EB'
                                }}
                                disabled={true}
                            >
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.6668 3.33341H5.00016C4.55814 3.33341 4.13421 3.50901 3.82165 3.82157C3.50909 4.13413 3.3335 4.55805 3.3335 5.00008V13.3334M3.3335 13.3334V15.0001C3.3335 15.4421 3.50909 15.866 3.82165 16.1786C4.13421 16.4912 4.55814 16.6667 5.00016 16.6667H15.0002C15.4422 16.6667 15.8661 16.4912 16.1787 16.1786C16.4912 15.866 16.6668 15.4421 16.6668 15.0001V11.6667M3.3335 13.3334L7.15516 9.51175C7.46771 9.1993 7.89156 9.02377 8.3335 9.02377C8.77544 9.02377 9.19928 9.1993 9.51183 9.51175L11.6668 11.6667M16.6668 8.33341V11.6667M16.6668 11.6667L15.3452 10.3451C15.0326 10.0326 14.6088 9.8571 14.1668 9.8571C13.7249 9.8571 13.301 10.0326 12.9885 10.3451L11.6668 11.6667M11.6668 11.6667L13.3335 13.3334M15.0002 3.33341H18.3335M16.6668 1.66675V5.00008M11.6668 6.66675H11.6752" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                                <span className="wpuf-text-sm wpuf-font-normal wpuf-leading-5 wpuf-text-gray-400">
                                    {__('Image/File', 'wp-user-frontend')}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default StepTabs;
