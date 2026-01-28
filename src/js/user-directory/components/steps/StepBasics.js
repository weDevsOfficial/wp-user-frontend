import React, { useState, useEffect, useRef } from 'react';
import { __ } from '@wordpress/i18n';
import Tooltip from '../common/Tooltip';
import MultiSelect from '../common/MultiSelect';

const StepBasics = ({ formData, setFormData, config }) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [isSearching, setIsSearching] = useState(false);
    const [showUserDropdown, setShowUserDropdown] = useState(false);
    const searchTimeoutRef = useRef(null);
    const userDropdownRef = useRef(null);

    // Prepare options: always include 'all' as the first option
    const roleOptions = {
        all: __('All', 'wp-user-frontend'),
        ...(typeof wpuf_ud_free !== 'undefined' && wpuf_ud_free.roles 
            ? wpuf_ud_free.roles 
            : (typeof wpuf_ud !== 'undefined' && wpuf_ud.roles ? wpuf_ud.roles : {}))
    };

    // Ensure roles is always an array
    const rolesValue = Array.isArray(formData.roles)
        ? formData.roles
        : formData.roles
            ? [formData.roles]
            : ['all'];

    // Ensure excluded_users is always an array
    const excludedUsers = Array.isArray(formData.excluded_users)
        ? formData.excluded_users
        : [];

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    // Search users via WordPress REST API
    const searchUsers = async (search) => {
        if (!search || search.length < 2) {
            setSearchResults([]);
            return;
        }

        setIsSearching(true);
        
        try {
            const response = await wp.apiFetch({
                path: `/wp/v2/users?search=${encodeURIComponent(search)}&per_page=10`
            });
            
            setSearchResults(response.map(user => ({
                id: user.id,
                name: user.name,
                username: user.slug,
                avatar: user.avatar_urls && user.avatar_urls['48'] || ''
            })));
        } catch (error) {
            console.error('Error searching users:', error);
            setSearchResults([]);
        } finally {
            setIsSearching(false);
        }
    };

    // Handle search input change with debounce
    const handleSearchChange = (e) => {
        const value = e.target.value;
        setSearchTerm(value);
        
        // Clear previous timeout
        if (searchTimeoutRef.current) {
            clearTimeout(searchTimeoutRef.current);
        }
        
        // Set new timeout for debounced search
        searchTimeoutRef.current = setTimeout(() => {
            searchUsers(value);
        }, 300);
    };

    // Add user to excluded list
    const addExcludedUser = (user) => {
        const newExcludedUsers = [...excludedUsers, user];
        setFormData(prev => ({ ...prev, excluded_users: newExcludedUsers }));
        setSearchTerm('');
        setSearchResults([]);
        setShowUserDropdown(false);
    };

    // Remove user from excluded list
    const removeExcludedUser = (userId) => {
        const newExcludedUsers = excludedUsers.filter(user => user.id !== userId);
        setFormData(prev => ({ ...prev, excluded_users: newExcludedUsers }));
    };

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (userDropdownRef.current && !userDropdownRef.current.contains(event.target)) {
                setShowUserDropdown(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

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
            >{__('Directory Basics', 'wp-user-frontend')}</h2>
            <p className="wpuf-text-center wpuf-mt-2"
                style={{
                    
                    fontWeight: 400,
                    fontSize: '14px',
                    lineHeight: '20px',
                    letterSpacing: '0%',
                    textAlign: 'center',
                    color: '#64748B'
                }}
            >{__('Set the foundation of your user directory by giving it a name and choosing who you want to include. This helps define the purpose and audience of your directory', 'wp-user-frontend')}</p>
            <div className="wpuf-mt-12">
                <label htmlFor="directory_title" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                    {__('Directory Title', 'wp-user-frontend')}
                    <Tooltip className="wpuf-ml-2" content={__('Give your User Directory a clear name. This will help you identify it later when managing multiple directories', 'wp-user-frontend')}>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                    </Tooltip>
                </label>
                <input
                    type="text"
                    id="directory_title"
                    name="directory_title"
                    className="wpuf-block wpuf-min-w-full wpuf-m-0 wpuf-leading-none wpuf-text-gray-700 placeholder:wpuf-text-gray-400 wpuf-max-w-full focus:wpuf-ring-transparent"
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
                        boxSizing: 'border-box',
                        fontSize: '16px'
                    }}
                    value={formData.directory_title || ''}
                    onChange={handleChange}
                    placeholder={__('Directory Title', 'wp-user-frontend')}
                />
            </div>
            <div className="wpuf-mt-[25px]">
                <label htmlFor="roles" className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                    {__('Who do you want to include', 'wp-user-frontend')}
                    <Tooltip className="wpuf-ml-2" content={__('Select which user roles to include in this directory. Only users assigned to these roles will be displayed', 'wp-user-frontend')}>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                    </Tooltip>
                </label>
                <MultiSelect
                    selectedLabel="roles"
                    options={roleOptions}
                    value={rolesValue}
                    onChange={roles => setFormData(prev => ({ ...prev, roles }))}
                    placeholder={__('Select roles...', 'wp-user-frontend')}
                    sortable={false}
                />
            </div>
            
            {/* Exclude Users Section - Free Feature */}
            <div className="wpuf-mt-[25px]">
                <label className="wpuf-flex wpuf-text-left wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 wpuf-mb-3">
                    {__('Exclude specific users', 'wp-user-frontend')}
                    <Tooltip className="wpuf-ml-2" content={__('Manually remove certain users from this directory, even if their roles are included', 'wp-user-frontend')}>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"></path></svg>
                    </Tooltip>
                </label>
                
                <div className="wpuf-relative" ref={userDropdownRef}>
                    {/* Search Input */}
                    <input
                        type="text"
                        className="wpuf-block wpuf-min-w-full wpuf-m-0 wpuf-leading-none wpuf-text-gray-700 placeholder:wpuf-text-gray-400 wpuf-max-w-full focus:wpuf-ring-transparent"
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
                            boxSizing: 'border-box',
                            fontSize: '16px'
                        }}
                        placeholder={__('Search users by name, username or email...', 'wp-user-frontend')}
                        value={searchTerm}
                        onChange={handleSearchChange}
                        onFocus={() => setShowUserDropdown(true)}
                    />
                    
                    {/* Search Results Dropdown */}
                    {showUserDropdown && searchTerm && (
                        <div className="wpuf-absolute wpuf-z-10 wpuf-mt-1 wpuf-w-full wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-rounded-[6px] wpuf-shadow-lg wpuf-max-h-60 wpuf-overflow-auto">
                            {isSearching && (
                                <div className="wpuf-p-3 wpuf-text-gray-500 wpuf-text-sm">
                                    {__('Searching...', 'wp-user-frontend')}
                                </div>
                            )}
                            
                            {!isSearching && searchResults.length === 0 && searchTerm.length >= 2 && (
                                <div className="wpuf-p-3 wpuf-text-gray-500 wpuf-text-sm">
                                    {__('No users found', 'wp-user-frontend')}
                                </div>
                            )}
                            
                            {!isSearching && searchResults.length > 0 && (
                                <ul>
                                    {searchResults.map(user => {
                                        const isExcluded = excludedUsers.some(u => u.id === user.id);
                                        if (isExcluded) return null;
                                        
                                        return (
                                            <li
                                                key={user.id}
                                                className="wpuf-cursor-pointer wpuf-p-3 hover:wpuf-bg-gray-50 wpuf-flex wpuf-items-center wpuf-gap-3"
                                                onClick={() => addExcludedUser(user)}
                                            >
                                                {user.avatar && (
                                                    <img 
                                                        src={user.avatar} 
                                                        alt={user.name}
                                                        className="wpuf-w-8 wpuf-h-8 wpuf-rounded-full"
                                                    />
                                                )}
                                                <div>
                                                    <div className="wpuf-font-medium wpuf-text-gray-900">{user.name}</div>
                                                    <div className="wpuf-text-sm wpuf-text-gray-500">@{user.username}</div>
                                                </div>
                                            </li>
                                        );
                                    })}
                                </ul>
                            )}
                        </div>
                    )}
                    
                    {/* Selected Excluded Users */}
                    {excludedUsers.length > 0 && (
                        <div className="wpuf-flex wpuf-flex-wrap wpuf-gap-2 wpuf-mt-3">
                            {excludedUsers.map(user => (
                                <div
                                    key={user.id}
                                    className="wpuf-group/item wpuf-flex wpuf-items-center wpuf-bg-red-50 wpuf-border wpuf-border-red-200 wpuf-rounded-[5px] wpuf-px-3 wpuf-py-1 wpuf-text-base wpuf-text-red-700"
                                >
                                    <span className="wpuf-flex wpuf-items-center wpuf-gap-2">
                                        {user.avatar && (
                                            <img 
                                                src={user.avatar} 
                                                alt={user.name}
                                                className="wpuf-w-5 wpuf-h-5 wpuf-rounded-full"
                                            />
                                        )}
                                        {user.name}
                                    </span>
                                    <button
                                        type="button"
                                        className="wpuf-ml-2 wpuf-text-red-600 wpuf-text-lg hover:wpuf-text-red-800"
                                        onClick={() => removeExcludedUser(user.id)}
                                    >
                                        ×
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default StepBasics;
