import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import Tooltip from '../common/Tooltip';

const StepAdvanced = ({ formData, setFormData, config }) => {
    // Hover states for Pro features
    const [hoveredOption, setHoveredOption] = useState(null);
    
    // Free version options
    const freeAvatarSizes = ['192']; // Only 192 is free
    const freeSortOptions = ['ID']; // Only User ID is free
    const freeGallerySizes = ['thumbnail']; // Only thumbnail is free
    
    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        
        if (name === 'users_per_page') {
            setFormData(prev => ({
                ...prev,
                users_per_page: value,
                max_item_per_page: value
            }));
        } else if (name === 'max_item') {
            // Convert empty string to null, handle -1 specially, and ensure numeric values
            let numericValue;
            if (value === '' || value === null || value === undefined) {
                numericValue = null;
            } else {
                numericValue = parseInt(value);
                // Handle NaN case
                if (isNaN(numericValue)) {
                    numericValue = null;
                }
            }
            setFormData(prev => ({
                ...prev,
                max_item: numericValue
            }));
        } else if (name === 'default_sort_by') {
            // Only allow free sort options
            if (freeSortOptions.includes(value)) {
                setFormData(prev => ({
                    ...prev,
                    default_sort_by: value,
                    orderby: value
                }));
            }
        } else if (name === 'default_sort_order') {
            setFormData(prev => ({
                ...prev,
                default_sort_order: value,
                order: value.toUpperCase()
            }));
        } else if (name === 'avatar_size') {
            // Only allow free avatar sizes
            if (freeAvatarSizes.includes(value)) {
                setFormData(prev => ({ ...prev, [name]: value }));
            }
        } else if (name === 'profile_size') {
            // Only allow free gallery sizes
            if (freeGallerySizes.includes(value)) {
                setFormData(prev => ({ ...prev, [name]: value }));
            }
        } else if (name === 'profile_base') {
            // Profile permalink is all free
            setFormData(prev => ({ ...prev, [name]: value }));
        } else if (type === 'checkbox') {
            setFormData(prev => ({ ...prev, [name]: checked }));
        } else {
            setFormData(prev => ({ ...prev, [name]: value }));
        }
    };

    // Common input style for select dropdowns
    const inputStyle = {
        maxWidth: '793px',
        width: '100%',
        height: '42px',
        borderRadius: '6px',
        borderWidth: '1px',
        paddingTop: '9px',
        paddingRight: '37px',
        paddingBottom: '9px',
        paddingLeft: '13px',
        backgroundColor: '#FFFFFF',
        borderColor: '#CBD5E1',
        borderStyle: 'solid',
        opacity: 1,
        boxSizing: 'border-box',
        fontSize: '16px',
        lineHeight: '1',
        display: 'flex',
        alignItems: 'center',
        backgroundImage: 'url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' stroke=\'%23374151\' viewBox=\'0 0 24 24\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 9l-7 7-7-7\'/%3E%3C/svg%3E")',
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'right 13px center',
        backgroundSize: '16px',
        appearance: 'none',
        WebkitAppearance: 'none',
        MozAppearance: 'none'
    };

    // Number input style (shows native spinners like Pro)
    const numberInputStyle = {
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
        boxSizing: 'border-box',
        fontSize: '16px'
    };

    // Pro badge component
    const ProBadge = ({ small = false }) => (
        <img 
            src={(config?.asset_url || window.wpuf_ud_free?.asset_url || '') + '/images/pro-badge.svg'} 
            alt="Pro" 
            style={{ width: small ? '30px' : '32px', height: small ? '17px' : '18px' }}
        />
    );

    // Sort options with Pro status
    const sortOptions = [
        { value: 'ID', label: __('User ID', 'wp-user-frontend'), isFree: true },
        { value: 'display_name', label: __('Display Name', 'wp-user-frontend'), isFree: false },
        { value: 'user_registered', label: __('Registration Date', 'wp-user-frontend'), isFree: false },
        { value: 'user_login', label: __('Username', 'wp-user-frontend'), isFree: false },
        { value: 'user_email', label: __('Email', 'wp-user-frontend'), isFree: false },
        { value: 'post_count', label: __('Post Count', 'wp-user-frontend'), isFree: false },
    ];

    // Avatar sizes with Pro status
    const avatarSizes = [
        { value: '32', label: '32×32', isFree: false },
        { value: '48', label: '48×48', isFree: false },
        { value: '80', label: '80×80', isFree: false },
        { value: '128', label: '128×128', isFree: false },
        { value: '160', label: '160×160', isFree: false },
        { value: '192', label: '192×192', isFree: true },
        { value: '265', label: '265×265', isFree: false },
    ];

    // Gallery image sizes with Pro status
    const gallerySizes = [
        { value: 'thumbnail', label: __('Thumbnail', 'wp-user-frontend'), isFree: true },
        { value: 'medium', label: __('Medium', 'wp-user-frontend'), isFree: false },
        { value: 'large', label: __('Large', 'wp-user-frontend'), isFree: false },
        { value: 'full', label: __('Full', 'wp-user-frontend'), isFree: false },
    ];

    // Social profiles options (all Pro)
    const socialProfiles = [
        { value: 'facebook', label: 'Facebook' },
        { value: 'twitter', label: 'X (Twitter)' },
        { value: 'linkedin', label: 'LinkedIn' },
        { value: 'instagram', label: 'Instagram' },
    ];

    // Profile permalink options (all free)
    const profileBases = [
        { value: 'username', label: __('Username', 'wp-user-frontend') },
        { value: 'user_id', label: __('User ID', 'wp-user-frontend') },
    ];

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
                {__('Advanced Control', 'wp-user-frontend')}
            </h2>
            <p className="wpuf-text-center wpuf-mt-2"
                style={{
                    fontWeight: 400,
                    fontSize: '14px',
                    lineHeight: '20px',
                    letterSpacing: '0%',
                    textAlign: 'center',
                    color: '#64748B'
                }}
            >{__('Fine-tune your directory settings with customizable options for sorting, pagination, and display preferences', 'wp-user-frontend')}</p>
            
            <div className="wpuf-mt-8">
                {/* Users Per Page - Available in Free */}
                <div className="wpuf-mt-[25px]">
                    <label htmlFor="users_per_page" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Users Per Page', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Set how many users will be displayed per page in this directory', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <input
                        type="number"
                        id="users_per_page"
                        name="users_per_page"
                        min="1"
                        max="100"
                        className="wpuf-block wpuf-min-w-full wpuf-m-0 wpuf-leading-none wpuf-text-gray-700 placeholder:wpuf-text-gray-400 wpuf-max-w-full focus:wpuf-ring-transparent"
                        style={numberInputStyle}
                        value={formData.users_per_page || formData.max_item_per_page || 12}
                        onChange={handleChange}
                    />
                </div>

                {/* Number of Users in Directory - Available in Free */}
                <div className="wpuf-mt-[25px]">
                    <label htmlFor="max_item" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Number of Users in Directory', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Set the maximum number of users to display in this directory. Enter -1 or leave empty to show all users.', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <input
                        type="number"
                        id="max_item"
                        name="max_item"
                        className="wpuf-block wpuf-min-w-full wpuf-m-0 wpuf-leading-none wpuf-text-gray-700 placeholder:wpuf-text-gray-400 wpuf-max-w-full focus:wpuf-ring-transparent"
                        style={numberInputStyle}
                        value={formData.max_item || ''}
                        onChange={handleChange}
                        placeholder={__('Enter -1 for all users', 'wp-user-frontend')}
                    />
                </div>

                {/* Default Sort By - User ID is free, rest are Pro */}
                <div className="wpuf-mt-[25px]">
                    <label htmlFor="default_sort_by" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Default Sort By', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Select the field by which users will be sorted by default', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <div className="wpuf-flex wpuf-flex-wrap wpuf-gap-2">
                        {sortOptions.map(option => {
                            const isSelected = (formData.default_sort_by || formData.orderby || 'ID') === option.value;
                            const isHovered = hoveredOption === `sort-${option.value}`;
                            
                            return (
                                <div
                                    key={option.value}
                                    className="wpuf-relative"
                                    onMouseEnter={() => !option.isFree && setHoveredOption(`sort-${option.value}`)}
                                    onMouseLeave={() => setHoveredOption(null)}
                                >
                                    {/* Pro Badge on hover */}
                                    {!option.isFree && isHovered && (
                                        <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                            <ProBadge small />
                                        </div>
                                    )}
                                    <button
                                        type="button"
                                        onClick={() => option.isFree && handleChange({ target: { name: 'default_sort_by', value: option.value } })}
                                        className={`wpuf-px-4 wpuf-py-2 wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-transition-all ${
                                            !option.isFree 
                                                ? `wpuf-cursor-not-allowed ${isHovered ? 'wpuf-opacity-80' : 'wpuf-opacity-60'} wpuf-bg-gray-50 wpuf-text-gray-400`
                                                : isSelected 
                                                    ? 'wpuf-bg-emerald-50 wpuf-text-emerald-600 wpuf-border-emerald-500' 
                                                    : 'wpuf-bg-white wpuf-text-gray-700 wpuf-border-gray-300 hover:wpuf-border-gray-400'
                                        }`}
                                        style={{
                                            border: !option.isFree && isHovered ? '2px dashed #9CA3AF' : '1px solid',
                                            borderColor: !option.isFree ? '#E5E7EB' : (isSelected ? '#059669' : '#D1D5DB'),
                                            minWidth: '100px'
                                        }}
                                        disabled={!option.isFree}
                                    >
                                        {option.label}
                                    </button>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Default Sort Order - Free */}
                <div className="wpuf-mt-[25px]">
                    <label htmlFor="default_sort_order" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Default Sort Order', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Choose whether to sort in ascending (A-Z, oldest first) or descending (Z-A, newest first) order', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <select
                        id="default_sort_order"
                        name="default_sort_order"
                        className="wpuf-flex-1 wpuf-text-gray-700 wpuf-font-normal focus:wpuf-ring-transparent wpuf-border"
                        style={inputStyle}
                        value={formData.default_sort_order || formData.order || 'desc'}
                        onChange={handleChange}
                    >
                        <option value="asc">{__('ASC', 'wp-user-frontend')}</option>
                        <option value="desc">{__('DESC', 'wp-user-frontend')}</option>
                    </select>
                </div>

                {/* Profile Permalink Structure - All Free */}
                <div className="wpuf-mt-[25px]">
                    <label htmlFor="profile_base" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Profile Permalink Structure', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Set how profile links are generated: use username (e.g., john-doe) for readable URLs or user ID (e.g., 123) for system-based links', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <select
                        id="profile_base"
                        name="profile_base"
                        className="wpuf-block wpuf-min-w-full wpuf-text-gray-700 wpuf-font-normal focus:wpuf-ring-transparent wpuf-border"
                        style={inputStyle}
                        value={formData.profile_base || 'username'}
                        onChange={handleChange}
                    >
                        {profileBases.map(option => (
                            <option key={option.value} value={option.value}>{option.label}</option>
                        ))}
                    </select>
                </div>

                {/* Select Social Profiles - All Pro */}
                <div className="wpuf-mt-[25px]">
                    <label className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Select Social Profiles', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Select the social media fields to display on user profiles', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <div 
                        className="wpuf-relative"
                        onMouseEnter={() => setHoveredOption('social-profiles')}
                        onMouseLeave={() => setHoveredOption(null)}
                    >
                        {/* Pro Badge on hover */}
                        {hoveredOption === 'social-profiles' && (
                            <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                <ProBadge />
                            </div>
                        )}
                        <div 
                            className={`wpuf-flex wpuf-flex-wrap wpuf-gap-2 wpuf-p-3 wpuf-rounded-lg wpuf-cursor-not-allowed ${hoveredOption === 'social-profiles' ? 'wpuf-opacity-80' : 'wpuf-opacity-60'}`}
                            style={{
                                border: hoveredOption === 'social-profiles' ? '2px dashed #9CA3AF' : '1px solid #E5E7EB',
                                backgroundColor: '#F9FAFB'
                            }}
                        >
                            {socialProfiles.map(profile => (
                                <span 
                                    key={profile.value}
                                    className="wpuf-px-3 wpuf-py-1 wpuf-bg-gray-200 wpuf-text-gray-400 wpuf-rounded-md wpuf-text-sm"
                                >
                                    {profile.label}
                                </span>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Profile Gallery Image Size - Thumbnail is free, rest are Pro */}
                <div className="wpuf-mt-[25px]">
                    <label className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Profile Gallery Image Size', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Select the default size for profile gallery images', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <div className="wpuf-flex wpuf-flex-wrap wpuf-gap-2">
                        {gallerySizes.map(option => {
                            const isSelected = (formData.profile_size || 'thumbnail') === option.value;
                            const isHovered = hoveredOption === `gallery-${option.value}`;
                            
                            return (
                                <div
                                    key={option.value}
                                    className="wpuf-relative"
                                    onMouseEnter={() => !option.isFree && setHoveredOption(`gallery-${option.value}`)}
                                    onMouseLeave={() => setHoveredOption(null)}
                                >
                                    {/* Pro Badge on hover */}
                                    {!option.isFree && isHovered && (
                                        <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                            <ProBadge small />
                                        </div>
                                    )}
                                    <button
                                        type="button"
                                        onClick={() => option.isFree && handleChange({ target: { name: 'profile_size', value: option.value } })}
                                        className={`wpuf-px-4 wpuf-py-2 wpuf-rounded-lg wpuf-text-sm wpuf-font-medium wpuf-transition-all ${
                                            !option.isFree 
                                                ? `wpuf-cursor-not-allowed ${isHovered ? 'wpuf-opacity-80' : 'wpuf-opacity-60'} wpuf-bg-gray-50 wpuf-text-gray-400`
                                                : isSelected 
                                                    ? 'wpuf-bg-emerald-50 wpuf-text-emerald-600 wpuf-border-emerald-500' 
                                                    : 'wpuf-bg-white wpuf-text-gray-700 wpuf-border-gray-300 hover:wpuf-border-gray-400'
                                        }`}
                                        style={{
                                            border: !option.isFree && isHovered ? '2px dashed #9CA3AF' : '1px solid',
                                            borderColor: !option.isFree ? '#E5E7EB' : (isSelected ? '#059669' : '#D1D5DB'),
                                            minWidth: '100px'
                                        }}
                                        disabled={!option.isFree}
                                    >
                                        {option.label}
                                    </button>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Avatar Size - Only 192 is free */}
                <div className="wpuf-mt-[25px]">
                    <label className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                        {__('Avatar Size', 'wp-user-frontend')}
                        <Tooltip className="wpuf-ml-2" content={__('Set the avatar size to display in directory layouts', 'wp-user-frontend')}>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                        </Tooltip>
                    </label>
                    <div className="wpuf-flex wpuf-flex-wrap wpuf-gap-2">
                        {avatarSizes.map(size => {
                            const isSelected = (formData.avatar_size || '192') === size.value;
                            const isHovered = hoveredOption === `avatar-${size.value}`;
                            
                            return (
                                <div
                                    key={size.value}
                                    className="wpuf-relative"
                                    onMouseEnter={() => !size.isFree && setHoveredOption(`avatar-${size.value}`)}
                                    onMouseLeave={() => setHoveredOption(null)}
                                >
                                    {/* Pro Badge on hover */}
                                    {!size.isFree && isHovered && (
                                        <div className="wpuf-absolute wpuf-z-10" style={{ top: '-24px', left: '50%', transform: 'translateX(-50%)' }}>
                                            <ProBadge small />
                                        </div>
                                    )}
                                    <label
                                        className={`wpuf-relative wpuf-flex wpuf-flex-col wpuf-items-center wpuf-justify-center wpuf-w-[103px] wpuf-h-[68px] wpuf-rounded-lg wpuf-transition-all wpuf-p-3 ${
                                            !size.isFree 
                                                ? `wpuf-cursor-not-allowed ${isHovered ? 'wpuf-opacity-80' : 'wpuf-opacity-60'} wpuf-bg-gray-50`
                                                : isSelected 
                                                    ? 'wpuf-bg-emerald-50 wpuf-text-emerald-600 wpuf-cursor-pointer' 
                                                    : 'wpuf-bg-white wpuf-text-gray-500 hover:wpuf-border-gray-400 wpuf-cursor-pointer'
                                        }`}
                                        style={{
                                            border: !size.isFree && isHovered ? '2px dashed #9CA3AF' : '1px solid',
                                            borderColor: !size.isFree ? '#E5E7EB' : (isSelected ? '#059669' : '#D1D5DB'),
                                        }}
                                    >
                                        <input
                                            type="radio"
                                            name="avatar_size"
                                            value={size.value}
                                            checked={isSelected}
                                            onChange={handleChange}
                                            className="wpuf-sr-only"
                                            disabled={!size.isFree}
                                        />
                                        {/* Checkmark for selected free items */}
                                        {isSelected && size.isFree && (
                                            <span className="wpuf-absolute wpuf-top-2 wpuf-right-2">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect width="20" height="20" rx="10" fill="#059669"/>
                                                    <path d="M7.5 9.5L9.5 11.5L13 8" stroke="white" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                                </svg>
                                            </span>
                                        )}
                                        {/* Avatar Icon */}
                                        <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg" className="wpuf-mb-1">
                                            <path 
                                                d="M13.5676 14.604C12.4264 13.0979 10.6183 12.125 8.58301 12.125C6.54768 12.125 4.73958 13.0979 3.59839 14.604M13.5676 14.604C15.1109 13.2303 16.083 11.2287 16.083 9C16.083 4.85786 12.7251 1.5 8.58301 1.5C4.44087 1.5 1.08301 4.85786 1.08301 9C1.08301 11.2287 2.05509 13.2303 3.59839 14.604M13.5676 14.604C12.2426 15.7834 10.4965 16.5 8.58301 16.5C6.66954 16.5 4.92343 15.7834 3.59839 14.604M11.083 7.125C11.083 8.50571 9.96372 9.625 8.58301 9.625C7.2023 9.625 6.08301 8.50571 6.08301 7.125C6.08301 5.74429 7.2023 4.625 8.58301 4.625C9.96372 4.625 11.083 5.74429 11.083 7.125Z" 
                                                stroke={!size.isFree ? "#9CA3AF" : isSelected ? "#059669" : "#9CA3AF"} 
                                                strokeWidth="1.5" 
                                                strokeLinecap="round" 
                                                strokeLinejoin="round"
                                            />
                                        </svg>
                                        <div className={`wpuf-text-xs wpuf-text-center wpuf-leading-tight ${!size.isFree ? 'wpuf-text-gray-400' : ''}`}>
                                            {size.label}
                                        </div>
                                    </label>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default StepAdvanced;
