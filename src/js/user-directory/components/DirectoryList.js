/**
 * User Directory Free - Directory List Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React, {useState, useEffect, useRef} from 'react';
import { __ } from '@wordpress/i18n';

// Pro Badge Component - uses the same SVG as modules page
const ProBadge = ({ className = '' }) => (
    <img 
        src={(window.wpuf_ud_free?.asset_url || window.wpuf_ud?.asset_url || window.wpuf_admin_script?.asset_url || '') + '/images/pro-badge.svg'} 
        alt="Pro" 
        className={className}
        style={{ width: '39px', height: '22px', display: 'inline-block', verticalAlign: 'middle' }}
    />
);

const DirectoryList = ( {directories, currentPage, totalPages, onPageChange, fetchDirectories, className = '', onRequestDelete, deletingId, onEdit, config = {}} ) => {
    const [copiedId, setCopiedId] = useState( null );
    const [openMenuId, setOpenMenuId] = useState(null);
    const menuRefs = useRef({});

    const handleCopy = ( shortcode, id ) => {
        navigator.clipboard.writeText( shortcode );
        setCopiedId( id );
        setTimeout( () => setCopiedId( null ), 1500 );
    };

    const handleMenuToggle = (id) => {
        setOpenMenuId(openMenuId === id ? null : id);
    };

    useEffect(() => {
        if (openMenuId === null) return;
        const handleClickOutside = (event) => {
            const menuNode = menuRefs.current[openMenuId];
            if (menuNode && !menuNode.contains(event.target)) {
                setOpenMenuId(null);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [openMenuId]);

    const renderPagination = () => {
        if (totalPages <= 1) return null;
        const range = [];
        const delta = 2;
        for (
            let i = Math.max( 1, currentPage - delta );
            i <= Math.min( totalPages, currentPage + delta );
            i++
        ) {
            range.push( i );
        }
        return (
            <div className="wpuf-flex wpuf-items-center wpuf-justify-center wpuf-mt-8">
                <nav className="wpuf-flex wpuf-items-center wpuf-w-full">
                    <button
                        onClick={() => onPageChange( currentPage - 1 )}
                        disabled={currentPage === 1}
                        className={`wpuf-mr-3 wpuf-rounded-md wpuf-inline-flex wpuf-items-center wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-text-primary ${currentPage === 1 ? 'wpuf-cursor-not-allowed wpuf-opacity-50' : ''}`}
                    >
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path
                                d="M7.70711 14.7071C7.31658 15.0976 6.68342 15.0976 6.2929 14.7071L2.29289 10.7071C1.90237 10.3166 1.90237 9.68342 2.29289 9.29289L6.29289 5.29289C6.68342 4.90237 7.31658 4.90237 7.70711 5.29289C8.09763 5.68342 8.09763 6.31658 7.70711 6.70711L5.41421 9L17 9C17.5523 9 18 9.44771 18 10C18 10.5523 17.5523 11 17 11L5.41421 11L7.70711 13.2929C8.09763 13.6834 8.09763 14.3166 7.70711 14.7071Z"
                                fill="#94A3B8"/>
                        </svg>
                        &nbsp;Previous
                    </button>
                    <div className="wpuf-flex wpuf-items-center">
                        {range.map( page => (
                            <span
                                key={page}
                                onClick={() => onPageChange( page )}
                                className={`wpuf-inline-flex wpuf-items-center wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-cursor-pointer wpuf-mx-1 wpuf-border-t-2 ${page === currentPage ? 'wpuf-text-primary wpuf-border-primary' : 'wpuf-text-gray-500 wpuf-border-transparent'} hover:wpuf-border-primary wpuf-transition-all`}
                            >
                                {page}
                            </span>
                        ) )}
                    </div>
                    <button
                        onClick={() => onPageChange( currentPage + 1 )}
                        disabled={currentPage === totalPages}
                        className={`wpuf-ml-3 wpuf-rounded-md wpuf-inline-flex wpuf-items-center wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-text-primary ${currentPage === totalPages ? 'wpuf-cursor-not-allowed wpuf-opacity-50' : ''}`}
                    >
                        Next&nbsp;
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path
                                d="M12.2929 5.29289C12.6834 4.90237 13.3166 4.90237 13.7071 5.29289L17.7071 9.29289C18.0976 9.68342 18.0976 10.3166 17.7071 10.7071L13.7071 14.7071C13.3166 15.0976 12.6834 15.0976 12.2929 14.7071C11.9024 14.31658 11.9024 13.6834 12.2929 13.2929L14.5858 11H3C2.44772 11 2 10.5523 2 10C2 9.44772 2.44772 9 3 9H14.5858L12.2929 6.70711C11.9024 6.31658 11.9024 5.68342 12.2929 5.29289Z"
                                fill="#94A3B8"/>
                        </svg>
                    </button>
                </nav>
            </div>
        );
    };

    return (
        <>
            <div className={`wpuf-bg-white wpuf-border wpuf-border-gray-200 wpuf-shadow wpuf-rounded-[8px] ${className}`}>
                <table className="wpuf-min-w-full wpuf-divide-y wpuf-divide-gray-200">
                    <thead>
                        <tr>
                            <th className="wpuf-py-3.5 wpuf-pl-4 wpuf-pr-3 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 sm:wpuf-pl-6">{__('Directory Name', 'wp-user-frontend')}</th>
                            <th className="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{__('ID', 'wp-user-frontend')}</th>
                            <th className="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{__('Members', 'wp-user-frontend')}</th>
                            <th className="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">{__('Shortcode', 'wp-user-frontend')}</th>
                            <th className="wpuf-px-3 wpuf-py-3.5 wpuf-text-right wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody className="wpuf-divide-y wpuf-divide-gray-200">
                    {directories.map( dir => {
                        // Helper to escape shortcode attribute values
                        const escapeShortcodeAttr = (val) => {
                            if (typeof val !== 'string') val = String(val);
                            return val.replace(/"/g, '&quot;').replace(/\[/g, '&#91;').replace(/\]/g, '&#93;');
                        };

                        let post_content = {};
                        try {
                            if (dir.post_content && typeof dir.post_content === 'string' && dir.post_content.trim() !== '') {
                                post_content = JSON.parse(dir.post_content);
                            }
                        } catch (e) {
                            post_content = {};
                        }
                        // Supported attributes and their mapping to post_content keys
                        // Handle field name mapping between frontend storage and shortcode attributes
                        const defaultTabs = post_content?.default_tabs || post_content?.profile_tabs;
                        const galleryImgSize = post_content?.gallery_img_size || post_content?.profile_size;
                        let profilePermalink = post_content?.profile_base || post_content?.profile_permalink;
                        
                        // Handle legacy 'user' value by mapping it to 'username'
                        if (profilePermalink === 'user') {
                            profilePermalink = 'username';
                        }
                        
                        // Convert array to comma-separated string if needed
                        const tabsValue = Array.isArray(defaultTabs) ? defaultTabs.join(',') : defaultTabs;
                        
                        const shortcodeAttrs = {
                            id: dir.ID,
                            roles: post_content?.roles,
                            per_page: post_content?.per_page,
                            directory_layout: post_content?.directory_layout,
                            profile_layout: post_content?.profile_layout,
                            default_tabs: tabsValue,
                            gallery_img_size: galleryImgSize,
                            avatar_size: post_content?.avatar_size,
                            profile_permalink: profilePermalink,
                            orderby: post_content?.orderby,
                            order: post_content?.order,
                        };
                        // Build full shortcode string with all attributes (for copying)
                        const fullShortcode = '[wpuf_user_listing' +
                            Object.entries(shortcodeAttrs)
                                .filter(([key, val]) => val !== undefined && val !== null && val !== '')
                                .map(([key, val]) => ` ${key}="${escapeShortcodeAttr(val)}"`)
                                .join('') +
                            ']';
                        // Build simple ID-based shortcode for display
                        const displayShortcode = `[wpuf_user_listing id="${dir.ID}"]`;
                        return (
                            <tr key={dir.ID} className="wpuf-group">
                                <td className="wpuf-text-sm wpuf-font-medium wpuf-text-gray-900 sm:wpuf-pl-6" title={'ID: ' + dir.ID}>
                                    <span className="hover:wpuf-cursor-pointer hover:wpuf-text-primary" onClick={() => { onEdit && onEdit(dir);}} >{dir.post_title}</span>
                                </td>
                                <td className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500">{dir.ID}</td>
                                <td className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-400">
                                    <span className="wpuf-italic">—</span>
                                </td>
                                <td className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500">
                                    <div className="wpuf-flex wpuf-items-center">
                                        <code
                                            className="wpuf-mr-2 wpuf-bg-gray-50 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-shadow-sm wpuf-py-[5px] wpuf-px-[10px]"
                                            title={`Copy: ${displayShortcode}\n\nThis shortcode uses stored settings. Full equivalent:\n${fullShortcode}`}
                                        >
                                            {displayShortcode}
                                        </code>
                                        {copiedId === dir.ID ? (
                                            <span className="wpuf-ml-2 wpuf-text-green-600">Copied!</span>
                                        ) : (
                                            <button
                                                onClick={() => handleCopy( displayShortcode, dir.ID )}
                                                className="wpuf-text-gray-500 hover:wpuf-text-gray-700 wpuf-focus:outline-none"
                                                title="Copy shortcode"
                                            >
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                    <path
                                                        d="M13.125 14.375V17.1875C13.125 17.7053 12.7053 18.125 12.1875 18.125H4.0625C3.54473 18.125 3.125 17.7053 3.125 17.1875V6.5625C3.125 6.04473 3.54473 5.625 4.0625 5.625H5.625C6.05089 5.625 6.46849 5.6605 6.875 5.7287M13.125 14.375H15.9375C16.4553 14.375 16.875 13.9553 16.875 13.4375V9.375C16.875 5.65876 14.1721 2.5738 10.625 1.9787C10.2185 1.9105 9.80089 1.875 9.375 1.875H7.8125C7.29473 1.875 6.875 2.29473 6.875 2.8125V5.7287M13.125 14.375H7.8125C7.29473 14.375 6.875 13.9553 6.875 13.4375V5.7287M16.875 11.25V9.6875C16.875 8.1342 15.6158 6.875 14.0625 6.875H12.8125C12.2947 6.875 11.875 6.45527 11.875 5.9375V4.6875C11.875 3.1342 10.6158 1.875 9.0625 1.875H8.125"
                                                        stroke="#6B7280" strokeWidth="1.5" strokeLinecap="round"
                                                        strokeLinejoin="round"/>
                                                </svg>
                                            </button>
                                        )}
                                    </div>
                                </td>
                                <td className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-right">
                                    <div className="wpuf-relative">
                                        <button
                                            className="wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-rounded-md wpuf-p-2 wpuf-text-gray-400 hover:wpuf-bg-gray-100 focus:wpuf-outline-none"
                                            onClick={() => handleMenuToggle(dir.ID)}
                                            aria-haspopup="true"
                                            aria-expanded={openMenuId === dir.ID}
                                        >
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M5 12H5.01M12 12H12.01M19 12H19.01" stroke="#94A3B8" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"/>
                                            </svg>
                                        </button>
                                        {openMenuId === dir.ID && (
                                            <div className="wpuf-absolute wpuf-right-0 wpuf-mt-2 wpuf-w-40 wpuf-origin-top-right wpuf-rounded-md wpuf-bg-white wpuf-shadow-lg wpuf-ring-1 wpuf-ring-black wpuf-ring-opacity-5 wpuf-z-10" ref={el => menuRefs.current[dir.ID] = el}>
                                                <button className="wpuf-block wpuf-w-full wpuf-text-left wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-700 hover:wpuf-bg-gray-100" onClick={() => { onEdit && onEdit(dir); setOpenMenuId(null); }}>{__('Edit', 'wp-user-frontend')}</button>
                                                <div className="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-gray-400 wpuf-cursor-not-allowed wpuf-bg-gray-50">
                                                    <span>{__('Duplicate', 'wp-user-frontend')}</span>
                                                    <ProBadge />
                                                </div>
                                                <button className="wpuf-block wpuf-w-full wpuf-text-left wpuf-px-4 wpuf-py-2 wpuf-text-sm wpuf-text-red-600 hover:wpuf-bg-gray-100" onClick={() => onRequestDelete(dir)} disabled={deletingId === dir.ID}>{deletingId === dir.ID ? __('Deleting...', 'wp-user-frontend') : __('Delete', 'wp-user-frontend')}</button>
                                            </div>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        );
                    } )}
                    </tbody>
                </table>
            </div>
            {renderPagination()}
        </>
    );
};

export default DirectoryList;
