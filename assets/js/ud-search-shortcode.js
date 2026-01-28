/**
 * User Directory Search - Shortcode Version
 * Search manager for shortcode-based user directory layouts
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

(function(window, document) {
    'use strict';

    const SEARCH_DEBOUNCE = 300;
    let debounceTimeout = null;

    function fetchUsers({
        blockId = '',
        pageId = '',
        directoryId = '',
        layout = '',
        search = '',
        page = 1,
        orderby = '',
        order = '',
        roles = '',
        exclude_users = '',
        search_by = '',
        avatar_size = '',
        max_item = '',
        onSuccess = () => {},
        onError = () => {},
        apiUrl = (typeof wpufUserDirectorySearch !== 'undefined' && wpufUserDirectorySearch.restUrl) 
            ? wpufUserDirectorySearch.restUrl 
            : '/wp-json/wpuf/v1/user_directory/search',
    }) {
        const params = new URLSearchParams();
        if (blockId) params.append('block_id', blockId);
        if (pageId) params.append('page_id', pageId);
        if (directoryId) params.append('directory_id', directoryId);
        if (layout) params.append('directory_layout', layout);
        if (search) params.append('search', search);
        if (page && page > 1) params.append('page', page);
        if (orderby) params.append('orderby', orderby);
        if (order) params.append('order', order);
        if (roles && roles !== 'all') params.append('roles', roles);
        if (exclude_users) params.append('exclude_users', exclude_users);
        if (search_by) params.append('search_by', search_by);
        if (avatar_size) params.append('avatar_size', avatar_size);
        if (max_item) params.append('max_item', max_item);
        params.append('base_url', window.location.pathname);

        fetch(apiUrl + '?' + params.toString(), {
            credentials: 'same-origin',
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    onSuccess(data);
                } else {
                    onError(data);
                }
            })
            .catch(onError);
    }

    function initUserDirectorySearch(container, blockId, pageId) {
        let currentRequestId = 0;

        const listingDiv = document.querySelector('.wpuf-user-listing[data-block-id="' + blockId + '"]');
        const input = listingDiv ? listingDiv.querySelector('.wpuf-ud-search-input') : null;
        let userList = listingDiv ? (
            listingDiv.querySelector('.wpuf-ud-tbody') ||
            listingDiv.querySelector('ul[role="list"]')
        ) : null;

        const pagination = listingDiv ? listingDiv.querySelector('.wpuf-ud-pagination') : null;
        const liveRegion = listingDiv ? listingDiv.querySelector('[aria-live]') : null;

        const sortBySelect = listingDiv ? listingDiv.querySelector('.wpuf-ud-sort-by') : null;
        const sortOrderSelect = listingDiv ? listingDiv.querySelector('.wpuf-ud-sort-order') : null;
        const searchBySelect = listingDiv ? listingDiv.querySelector('.wpuf-ud-search-by') : null;
        const resetButton = listingDiv ? listingDiv.querySelector('.wpuf-ud-reset-filters') : null;

        const layout = listingDiv ? listingDiv.dataset.layout || '' : '';
        const directoryId = listingDiv ? listingDiv.dataset.directoryId || '' : '';
        const roles = listingDiv ? listingDiv.dataset.roles || '' : '';
        const excludeUsers = listingDiv ? listingDiv.dataset.excludeUsers || '' : '';
        const avatarSize = listingDiv ? listingDiv.dataset.avatarSize || '' : '';
        const maxItem = listingDiv ? listingDiv.dataset.maxItem || '' : '';

        if (!input || !userList) return;

        function showLoading() {
            if (listingDiv) {
                listingDiv.classList.add('wpuf-ud-loading');
            }

            if (userList) {
                let loadingOverlay = listingDiv.querySelector('.wpuf-ud-loading-overlay');
                if (!loadingOverlay) {
                    loadingOverlay = document.createElement('div');
                    loadingOverlay.className = 'wpuf-ud-loading-overlay';
                    loadingOverlay.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.8); display: flex; align-items: center; justify-content: center; z-index: 10;';

                    const spinner = document.createElement('div');
                    spinner.className = 'wpuf-ud-spinner';
                    spinner.style.cssText = 'width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #10b981; border-radius: 50%; animation: wpuf-spin 1s linear infinite;';

                    loadingOverlay.appendChild(spinner);

                    if (!document.querySelector('#wpuf-spinner-styles')) {
                        const style = document.createElement('style');
                        style.id = 'wpuf-spinner-styles';
                        style.textContent = '@keyframes wpuf-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }';
                        document.head.appendChild(style);
                    }

                    const listContainer = userList.closest('.wpuf-ud-list, .wpuf-table-responsive');
                    if (listContainer) {
                        listContainer.style.position = 'relative';
                        listContainer.appendChild(loadingOverlay);
                    }
                } else {
                    loadingOverlay.style.display = 'flex';
                }
            }
        }

        function hideLoading() {
            if (listingDiv) {
                listingDiv.classList.remove('wpuf-ud-loading');
            }

            const loadingOverlay = listingDiv ? listingDiv.querySelector('.wpuf-ud-loading-overlay') : null;
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        }

        function performSearch(searchTerm, page = 1) {
            const requestId = ++currentRequestId;

            const currentOrderby = sortBySelect ? sortBySelect.value : '';
            const currentOrder = sortOrderSelect ? sortOrderSelect.value : '';
            const searchByField = searchBySelect ? searchBySelect.value : '';

            showLoading();

            fetchUsers({
                blockId,
                pageId,
                directoryId,
                layout,
                search: searchTerm,
                page: page,
                orderby: currentOrderby,
                order: currentOrder,
                roles: roles,
                exclude_users: excludeUsers,
                search_by: searchByField,
                avatar_size: avatarSize,
                max_item: maxItem,
                onSuccess: (data) => {
                    if (requestId !== currentRequestId) {
                        return;
                    }

                    hideLoading();

                    try {
                        // If we have results, restore the user list container first
                        if (data.rows_html && data.rows_html.trim() !== '') {
                            // Remove "no results" message if it exists
                            const noResultsContainer = listingDiv.querySelector('.wpuf-no-users-container');
                            if (noResultsContainer) {
                                const listContainer = listingDiv.querySelector('.wpuf-ud-list');
                                if (listContainer) {
                                    // Restore the original list structure (matches layout-3.php template)
                                    listContainer.innerHTML = '<div><ul role="list" class="!wpuf-mx-auto !wpuf-grid !wpuf-max-w-2xl !wpuf-grid-cols-1 !wpuf-gap-x-6 !wpuf-gap-y-6 sm:!wpuf-grid-cols-2 lg:!wpuf-mx-0 lg:!wpuf-max-w-none lg:!wpuf-grid-cols-3"></ul></div>';
                                }
                                // Re-query the user list element
                                userList = listingDiv.querySelector('.wpuf-ud-tbody, ul[role="list"]');
                            }

                            // Now update the user list with new data
                            if (userList) {
                                userList.innerHTML = data.rows_html;
                            }
                        } else {
                            // No results - show "no users found" message
                            if (!data.usercount || data.usercount === 0) {
                                if (userList) {
                                    userList.innerHTML = '';
                                }

                                let noResultsContainer = listingDiv.querySelector('.wpuf-no-users-container');
                                if (!noResultsContainer) {
                                    noResultsContainer = document.createElement('div');
                                    noResultsContainer.className = 'wpuf-no-users-container';
                                    noResultsContainer.style.cssText = 'display: flex; align-items: center; justify-content: center; min-height: 400px;';

                                    const noResultsContent = document.createElement('div');
                                    noResultsContent.style.cssText = 'display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;';

                                    const iconContainer = document.createElement('div');
                                    iconContainer.style.cssText = 'background-color: #f3f4f6; border-radius: 50%; padding: 1rem; margin-bottom: 1rem;';
                                    iconContainer.innerHTML = '<svg style="width: 3rem; height: 3rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>';

                                    const heading = document.createElement('h3');
                                    heading.style.cssText = 'font-size: 1rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem;';
                                    heading.textContent = 'No users found matching your search criteria.';

                                    const description = document.createElement('p');
                                    description.style.cssText = 'font-size: 0.875rem; color: #6b7280;';
                                    description.textContent = 'Try adjusting your search or filter to find what you\'re looking for.';

                                    noResultsContent.appendChild(iconContainer);
                                    noResultsContent.appendChild(heading);
                                    noResultsContent.appendChild(description);
                                    noResultsContainer.appendChild(noResultsContent);

                                    const listContainer = listingDiv.querySelector('.wpuf-ud-list');
                                    if (listContainer) {
                                        listContainer.innerHTML = '';
                                        listContainer.appendChild(noResultsContainer);
                                    }
                                }
                                noResultsContainer.style.display = 'flex';
                            }
                        }

                        if (data.pagination_html && pagination) {
                            pagination.innerHTML = data.pagination_html;
                            attachPaginationListeners();
                        } else if (pagination) {
                            pagination.innerHTML = '';
                        }

                        if (liveRegion && data.announce) {
                            liveRegion.textContent = data.announce;
                        }

                        // Update browser URL
                        const url = new URL(window.location);

                        if (searchTerm) {
                            url.searchParams.set('search', searchTerm);
                        } else {
                            url.searchParams.delete('search');
                        }

                        if (currentOrderby) {
                            url.searchParams.set('orderby', currentOrderby);
                        } else {
                            url.searchParams.delete('orderby');
                        }

                        if (currentOrder) {
                            url.searchParams.set('order', currentOrder);
                        } else {
                            url.searchParams.delete('order');
                        }

                        // Remove udpage if present (legacy)
                        url.searchParams.delete('udpage');
                        
                        // Use clean /page/X/ URL structure like Pro
                        let basePath = url.pathname.replace(/\/page\/\d+\/?$/, '');
                        basePath = basePath.replace(/\/$/, '');
                        if (page > 1) {
                            url.pathname = basePath + '/page/' + page + '/';
                        } else {
                            url.pathname = basePath + '/';
                        }

                        window.history.replaceState({}, '', url);

                        const event = new CustomEvent('wpuf:ud:search:results', { detail: data });
                        container.dispatchEvent(event);
                    } catch (error) {
                        console.error('Error processing search results:', error);
                    }
                },
                onError: (err) => {
                    if (requestId !== currentRequestId) {
                        return;
                    }

                    hideLoading();

                    if (liveRegion) {
                        liveRegion.textContent = 'Search failed.';
                    }

                    const event = new CustomEvent('wpuf:ud:search:error', { detail: err });
                    container.dispatchEvent(event);
                }
            });
        }

        function attachPaginationListeners() {
            if (!pagination) return;

            const paginationLinks = pagination.querySelectorAll('a.wpuf-pagination-link');

            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const href = this.getAttribute('href');
                    const url = new URL(href, window.location.origin);

                    // Get page from URL path (clean URL) or legacy udpage param
                    let page = 1;
                    const pathMatch = url.pathname.match(/\/page\/(\d+)\/?$/);
                    if (pathMatch) {
                        page = pathMatch[1];
                    } else if (url.searchParams.get('udpage')) {
                        page = url.searchParams.get('udpage');
                    }

                    const currentSearch = input.value.trim();

                    const orderby = url.searchParams.get('orderby');
                    const order = url.searchParams.get('order');

                    if (orderby && sortBySelect) {
                        sortBySelect.value = orderby;
                    }
                    if (order && sortOrderSelect) {
                        sortOrderSelect.value = order;
                    }

                    performSearch(currentSearch, parseInt(page));
                });
            });
        }

        // Search input handler
        input.addEventListener('input', function(e) {
            const value = e.target.value.trim();
            if (debounceTimeout) clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                performSearch(value, 1);
            }, SEARCH_DEBOUNCE);
        });

        // Sort handlers
        if (sortBySelect) {
            sortBySelect.addEventListener('change', function() {
                const currentSearch = input.value.trim();
                performSearch(currentSearch, 1);
            });
        }

        if (sortOrderSelect) {
            sortOrderSelect.addEventListener('change', function() {
                const currentSearch = input.value.trim();
                performSearch(currentSearch, 1);
            });
        }

        if (searchBySelect) {
            searchBySelect.addEventListener('change', function() {
                const currentSearch = input.value.trim();
                if (currentSearch) {
                    performSearch(currentSearch, 1);
                }
            });
        }

        // Reset handler
        if (resetButton) {
            resetButton.addEventListener('click', function(e) {
                e.preventDefault();

                input.value = '';

                if (sortBySelect) {
                    const defaultSortBy = sortBySelect.getAttribute('data-default-value') || '';
                    sortBySelect.value = defaultSortBy;
                }
                if (sortOrderSelect) {
                    const defaultSortOrder = sortOrderSelect.getAttribute('data-default-value') || 'desc';
                    sortOrderSelect.value = defaultSortOrder;
                }
                if (searchBySelect) {
                    searchBySelect.value = '';
                }

                performSearch('', 1);
            });
        }

        // Initialize
        attachPaginationListeners();

        // Check URL params on load
        const urlParams = new URLSearchParams(window.location.search);
        const initialSearch = urlParams.get('search');
        const initialOrderby = urlParams.get('orderby');
        const initialOrder = urlParams.get('order');

        if (initialSearch && input.value !== initialSearch) {
            input.value = initialSearch;
        }

        if (initialOrderby && sortBySelect) {
            sortBySelect.value = initialOrderby;
        }

        if (initialOrder && sortOrderSelect) {
            sortOrderSelect.value = initialOrder;
        }

        // Get initial page from URL path or legacy udpage param
        let initialPage = 1;
        const pagePathMatch = window.location.pathname.match(/\/page\/(\d+)\/?$/);
        if (pagePathMatch) {
            initialPage = parseInt(pagePathMatch[1]);
        } else if (urlParams.get('udpage')) {
            initialPage = parseInt(urlParams.get('udpage'));
        }

        if (initialSearch || initialOrderby || initialOrder) {
            performSearch(initialSearch || '', initialPage);
        }
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        const listings = document.querySelectorAll('.wpuf-user-listing');

        listings.forEach((listing) => {
            const blockId = listing.dataset.blockId || '';
            const pageId = listing.dataset.pageId || '';

            // Initialize for any listing with a block ID (shortcode or block)
            if (blockId) {
                initUserDirectorySearch(listing, blockId, pageId);
            }
        });
    });

})(window, document);
