/**
 * User Directory Free - Main App Component
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import Header from './components/common/Header';
import DirectoryList from './components/DirectoryList';
import DirectoryWizard from './components/DirectoryWizard';
import DeleteConfirmModal from './components/common/DeleteConfirmModal';

// Pro Badge Component - uses the same SVG as modules page
const ProBadge = ({ className = '' }) => (
    <img 
        src={(window.wpuf_ud_free?.asset_url || window.wpuf_ud?.asset_url || window.wpuf_admin_script?.asset_url || '') + '/images/pro-badge.svg'} 
        alt="Pro" 
        className={className}
        style={{ width: '39px', height: '22px', display: 'inline-block', verticalAlign: 'middle' }}
    />
);

const App = () => {
    const [view, setView] = useState('list'); // 'list' or 'wizard'
    const [directories, setDirectories] = useState([]);
    const [editDirectory, setEditDirectory] = useState(null);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [deleteModal, setDeleteModal] = useState({ open: false, dir: null });
    const [deletingId, setDeletingId] = useState(null);

    const config = window.wpuf_ud_free || window.wpuf_ud || {};
    const perPage = 10;

    // Fetch directories on mount and when page changes
    useEffect(() => {
        fetchDirectories(currentPage);
    }, [currentPage]);

    const fetchDirectories = async (page = 1) => {
        setLoading(true);
        try {
            const restUrl = config.rest_url || '/wp-json/';
            const url = `${restUrl}wpuf/v1/user_directory?page=${page}&per_page=${perPage}`;
            const response = await fetch(url, {
                headers: {
                    'X-WP-Nonce': config.rest_nonce || '',
                },
            });
            const data = await response.json();
            if (data && data.success && Array.isArray(data.result)) {
                setDirectories(data.result);
                if (data.pagination && data.pagination.total_pages) {
                    setTotalPages(data.pagination.total_pages);
                } else {
                    setTotalPages(1);
                }
            } else {
                setDirectories([]);
                setTotalPages(1);
            }
        } catch (error) {
            console.error('Error fetching directories:', error);
            setDirectories([]);
            setTotalPages(1);
        }
        setLoading(false);
    };

    const handlePageChange = (page) => {
        if (page < 1 || page > totalPages) return;
        setCurrentPage(page);
    };

    const handleCreate = () => {
        setEditDirectory(null);
        setView('wizard');
    };

    const handleEdit = (directory) => {
        setEditDirectory(directory);
        setView('wizard');
    };

    const handleClose = () => {
        setView('list');
        setEditDirectory(null);
        fetchDirectories(currentPage);
    };

    const handleRequestDelete = (dir) => {
        setDeleteModal({ open: true, dir });
    };

    const handleDelete = async () => {
        if (!deleteModal.dir) return;
        setDeletingId(deleteModal.dir.ID);
        try {
            const restUrl = config.rest_url || '/wp-json/';
            const response = await fetch(`${restUrl}wpuf/v1/user_directory/${deleteModal.dir.ID}`, {
                method: 'DELETE',
                headers: {
                    'X-WP-Nonce': config.rest_nonce || '',
                },
            });
            if (!response.ok) {
                alert('Server error: ' + response.status);
                setDeletingId(null);
                setDeleteModal({ open: false, dir: null });
                return;
            }
            const result = await response.json();
            if (result && result.success) {
                fetchDirectories(currentPage);
            } else {
                alert(result && result.message ? result.message : 'Failed to delete');
            }
        } catch (e) {
            alert('Failed to delete');
        }
        setDeletingId(null);
        setDeleteModal({ open: false, dir: null });
    };

    if (view === 'wizard') {
        return (
            <DirectoryWizard
                onClose={handleClose}
                initialData={editDirectory}
                config={config}
            />
        );
    }

    const hasReachedLimit = directories.length >= (config.free_directory_limit || 1);

    return (
        <>
            <Header />
            <div className="wpuf-w-[calc(100%+40px)] wpuf-ml-[-20px] wpuf-px-[20px] wpuf-my-10">
                <div className="wpuf-mb-8">
                    <div className="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-mb-6">
                        <h1 className="wpuf-text-2xl wpuf-font-bold wpuf-m-0">{__('User Directories', 'wp-user-frontend')}</h1>
                        <div className="wpuf-relative wpuf-inline-block wpuf-group">
                            <button
                                className={`wpuf-flex wpuf-items-center ${hasReachedLimit ? 'wpuf-bg-gray-100 wpuf-text-gray-400 wpuf-border wpuf-border-gray-300 wpuf-rounded-md wpuf-px-4 wpuf-py-2 wpuf-cursor-not-allowed' : 'new-wpuf-form wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer'}`}
                                onClick={handleCreate}
                                disabled={hasReachedLimit}
                            >
                                <span className="dashicons dashicons-plus-alt2"></span>
                                &nbsp;
                                {__('New Directory', 'wp-user-frontend')}
                            </button>
                            {/* Pro Badge on hover - vertically centered */}
                            {hasReachedLimit && (
                                <div className="wpuf-absolute wpuf-z-50 wpuf-opacity-0 group-hover:wpuf-opacity-100 wpuf-transition-opacity wpuf-duration-200" style={{ top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }}>
                                    <ProBadge />
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Limit Warning */}
                    {hasReachedLimit && (
                        <div className="wpuf-flex wpuf-items-start wpuf-gap-3 wpuf-p-4 wpuf-bg-amber-50 wpuf-border wpuf-border-amber-200 wpuf-rounded-lg wpuf-mt-6 wpuf-mb-6">
                            <svg className="wpuf-w-5 wpuf-h-5 wpuf-text-amber-600 wpuf-flex-shrink-0 wpuf-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p className="wpuf-font-semibold wpuf-text-amber-800 wpuf-m-0">
                                    {config.i18n?.directory_limit || __('You can only create 1 directory in the free version.', 'wp-user-frontend')}
                                </p>
                                <p className="wpuf-text-amber-700 wpuf-mt-1 wpuf-m-0">
                                    <a href={config.upgrade_url || 'https://wedevs.com/wp-user-frontend-pro/pricing/'} target="_blank" rel="noopener noreferrer" className="wpuf-text-emerald-600 wpuf-font-medium hover:wpuf-underline">
                                        {config.i18n?.upgrade_to_pro || __('Upgrade to Pro', 'wp-user-frontend')}
                                    </a> {__('to create unlimited directories.', 'wp-user-frontend')}
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {loading ? (
                    <div className="wpuf-flex wpuf-h-16 wpuf-items-center wpuf-justify-center">
                        <div className="wpuf-loader"></div>
                    </div>
                ) : directories.length === 0 ? (
                    <EmptyState onCreate={handleCreate} config={config} />
                ) : (
                    <DirectoryList
                        className="wpuf-animate-fadein"
                        directories={directories}
                        currentPage={currentPage}
                        totalPages={totalPages}
                        onPageChange={handlePageChange}
                        fetchDirectories={fetchDirectories}
                        onRequestDelete={handleRequestDelete}
                        deletingId={deletingId}
                        onEdit={handleEdit}
                        config={config}
                    />
                )}
            </div>

            <DeleteConfirmModal
                isOpen={deleteModal.open}
                onCancel={() => setDeleteModal({ open: false, dir: null })}
                onConfirm={handleDelete}
            />
        </>
    );
};

// Empty State Component - matches Pro design exactly
const EmptyState = ({ onCreate, config }) => {
    const wpuf = window.wpuf_ud_free || window.wpuf_ud || {};
    const assetUrl = wpuf.asset_url || '';
    
    return (
        <div className="wpuf-w-full wpuf-flex wpuf-flex-col wpuf-justify-center wpuf-items-center wpuf-h-[calc(100vh-12rem)]">
            <div className="wpuf-w-1/2 wpuf-text-center">
                <div className="wpuf-flex wpuf-justify-center wpuf-items-end wpuf-mb-8">
                    <img
                        src={assetUrl + '/images/user-directory/thumb-male-1.svg'}
                        alt="User Avatar"
                        className="wpuf-w-20 wpuf-h-20"
                    />
                    <img
                        src={assetUrl + '/images/user-directory/thumb-male-2.svg'}
                        alt="User Avatar"
                        className="wpuf-w-28 wpuf-mx-3"
                    />
                    <img
                        src={assetUrl + '/images/user-directory/thumb-male-3.svg'}
                        alt="User Avatar"
                        className="wpuf-w-20 wpuf-h-20"
                    />
                </div>
                <h1 className="wpuf-text-lg wpuf-font-semibold wpuf-font-gray-800">{__('No directories added yet', 'wp-user-frontend')}</h1>
                <p className="wpuf-mt-4 wpuf-text-sm wpuf-font-normal wpuf-color-gray-500">{__('Create directories to organize users and manage access.', 'wp-user-frontend')}</p>
            </div>
            <div className="wpuf-mt-8">
                <button
                    onClick={onCreate}
                    className="wpuf-btn-primary wpuf-flex"
                >
                    <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 5.79321V10.7932M10 10.7932V15.7932M10 10.7932H15M10 10.7932L5 10.7932"
                              stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                    <span className="wpuf-ml-2">
                        {__('Add New Directory', 'wp-user-frontend')}
                    </span>
                </button>
            </div>
            <div className="wpuf-mt-7">
                <a
                    href="https://wedevs.com/docs/wp-user-frontend-pro/modules/user-listing-profile/?utm_source=wpuf-ud&utm_medium=wpuf-ud-docs"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="wpuf-flex wpuf-items-center"
                >
                    <span className="wpuf-mr-2 wpuf-text-base wpuf-font-semibold wpuf-text-gray-700">
                        {__('Docs', 'wp-user-frontend')}
                    </span>
                    <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.25 4.54321L17.5 10.7932M17.5 10.7932L11.25 17.0432M17.5 10.7932H2.5" stroke="#4B5563" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    );
};

export default App;
