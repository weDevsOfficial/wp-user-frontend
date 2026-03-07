/**
 * FormsList component — main orchestrator for the forms list page.
 *
 * @since WPUF_SINCE
 */
import { useState, useEffect, useCallback, useMemo, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

import useFormsFetch from '../hooks/useFormsFetch';
import useClipboard from '../hooks/useClipboard';

import StatusTabs from './StatusTabs';
import SearchBar from './SearchBar';
import BulkActions from './BulkActions';
import FormsTable from './FormsTable';
import Pagination from './Pagination';
import EmptyState from './EmptyState';
import AIConfigModal from './AIConfigModal';

const FormsList = ( {
    postType = 'wpuf_forms',
    formType = 'post',
    pageSlug = 'wpuf-post-forms',
    pageTitle = 'Post Forms',
} ) => {
    const filteredPageTitle = applyFilters( 'wpuf.formsList.pageTitle', pageTitle, formType );

    const isPlainPermalink = wpuf_forms_list.is_plain_permalink;
    const permalinkUrl = wpuf_forms_list.permalink_settings_url;
    const aiConfigured = wpuf_forms_list.ai_configured || false;
    const aiSettingsUrl = wpuf_forms_list.ai_settings_url || '';
    const postCounts = wpuf_forms_list.post_counts;

    const newFormUrl = window.wpuf_admin_script.admin_url + 'admin.php?page=wpuf-' + formType + '-forms&action=add-new';

    const { forms, loading, pagination, fetchForms } = useFormsFetch( { postType } );
    const { copiedKey, copyToClipboard } = useClipboard();

    const [ currentTab, setCurrentTab ] = useState( 'any' );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const [ selectedForms, setSelectedForms ] = useState( [] );
    const [ selectAllChecked, setSelectAllChecked ] = useState( false );
    const [ selectedBulkAction, setSelectedBulkAction ] = useState( '' );
    const [ showAIConfigModal, setShowAIConfigModal ] = useState( false );

    const debounceTimerRef = useRef( null );
    const isInitialMount = useRef( true );

    // Initial fetch
    useEffect( () => {
        fetchForms( 1, currentTab, searchTerm );
    }, [] );

    // Re-fetch when tab changes (skip initial mount)
    useEffect( () => {
        if ( isInitialMount.current ) {
            isInitialMount.current = false;
            return;
        }
        fetchForms( 1, currentTab, searchTerm );
    }, [ currentTab ] );

    // Debounced search
    const handleSearchChange = useCallback( ( value ) => {
        setSearchTerm( value );

        if ( debounceTimerRef.current ) {
            clearTimeout( debounceTimerRef.current );
        }

        debounceTimerRef.current = setTimeout( () => {
            fetchForms( 1, currentTab, value );
        }, 500 );
    }, [ currentTab, fetchForms ] );

    // Cleanup debounce timer on unmount
    useEffect( () => {
        return () => {
            if ( debounceTimerRef.current ) {
                clearTimeout( debounceTimerRef.current );
            }
        };
    }, [] );

    // Sync selectAllChecked with individual selections
    useEffect( () => {
        if ( forms.length > 0 && selectedForms.length === forms.length ) {
            setSelectAllChecked( true );
        } else {
            setSelectAllChecked( false );
        }
    }, [ selectedForms, forms ] );

    // Reset selections when forms change
    useEffect( () => {
        setSelectedForms( [] );
        setSelectAllChecked( false );
    }, [ forms ] );

    const handleSelectAll = useCallback( () => {
        if ( ! selectAllChecked ) {
            setSelectedForms( forms.map( ( form ) => form.ID ) );
        } else {
            setSelectedForms( [] );
        }
    }, [ selectAllChecked, forms ] );

    const handleSelectForm = useCallback( ( formId ) => {
        setSelectedForms( ( prev ) => {
            if ( prev.includes( formId ) ) {
                return prev.filter( ( id ) => id !== formId );
            }
            return [ ...prev, formId ];
        } );
    }, [] );

    const handleTabChange = useCallback( ( tab ) => {
        setCurrentTab( tab );
    }, [] );

    const handlePageChange = useCallback( ( page ) => {
        fetchForms( page, currentTab, searchTerm );
    }, [ currentTab, searchTerm, fetchForms ] );

    // Build admin URL helper
    const buildAdminUrl = useCallback( ( formId, action ) => {
        const params = new URLSearchParams( {
            page: pageSlug,
            id: formId.toString(),
            action,
        } );

        if ( action !== 'edit' ) {
            params.append( '_wpnonce', wpuf_forms_list.bulk_nonce );
        }

        return `${ window.wpuf_admin_script.admin_url }admin.php?${ params.toString() }`;
    }, [ pageSlug ] );

    // Row action handler
    const handleAction = useCallback( ( action, formId ) => {
        switch ( action ) {
            case 'edit':
                window.location.href = buildAdminUrl( formId, 'edit' );
                break;
            case 'duplicate':
                window.location.href = buildAdminUrl( formId, 'duplicate' );
                break;
            case 'trash':
                window.location.href = buildAdminUrl( formId, 'trash' );
                break;
            case 'restore':
                window.location.href = buildAdminUrl( formId, 'restore' );
                break;
            case 'delete':
                if ( ! window.confirm( __( 'Are you sure you want to delete this form permanently? This action cannot be undone.', 'wp-user-frontend' ) ) ) {
                    return;
                }
                window.location.href = buildAdminUrl( formId, 'delete' );
                break;
        }
    }, [ buildAdminUrl ] );

    // Bulk action handler
    const handleBulkAction = useCallback( () => {
        if ( ! selectedBulkAction || selectedForms.length === 0 ) {
            return;
        }

        const params = new URLSearchParams( {
            page: pageSlug,
            _wpnonce: wpuf_forms_list.bulk_nonce,
            _wp_http_referer: window.location.href,
            action: selectedBulkAction,
            action2: selectedBulkAction,
            bulk_action: 'Apply',
            paged: pagination.current_page.toString(),
        } );

        if ( searchTerm ) {
            params.append( 's', searchTerm );
        }

        if ( currentTab === 'trash' ) {
            params.append( 'post_status', 'trash' );
        }

        selectedForms.forEach( ( formId ) => {
            params.append( 'post[]', formId.toString() );
        } );

        window.location.href = `${ window.wpuf_admin_script.admin_url }admin.php?${ params.toString() }`;
    }, [ selectedBulkAction, selectedForms, pageSlug, searchTerm, currentTab, pagination ] );

    // Open jQuery template modal
    const openModal = useCallback( ( event ) => {
        event.preventDefault();

        if ( window.jQuery ) {
            const $ = window.jQuery;
            const $modal = $( '.wpuf-form-template-modal' );

            $modal.show().removeClass( 'wpuf-hidden' );
            $modal[ 0 ].offsetHeight;

            setTimeout( function () {
                $modal.addClass( 'wpuf-modal-show' );
            }, 10 );

            $( 'body' ).addClass( 'wpuf-modal-open' );
            $( 'body' ).css( 'overflow', 'hidden' );
            $( '#wpbody-content .wrap' ).hide();
        } else {
            window.location.href = newFormUrl;
        }
    }, [ newFormUrl ] );

    // AI Form Builder handler
    const openAIFormBuilder = useCallback( ( event ) => {
        event.preventDefault();

        if ( ! aiConfigured ) {
            setShowAIConfigModal( true );
            return;
        }

        const action = formType === 'profile' ? 'wpuf_profile_form_template' : 'post_form_template';

        const params = new URLSearchParams( {
            action,
            template: 'ai_form',
            _wpnonce: wpuf_forms_list.template_nonce,
        } );

        window.location.href = window.wpuf_admin_script.admin_url + 'admin.php?' + params.toString();
    }, [ aiConfigured, formType ] );

    // Shortcode getter with Pro filter
    const getShortcode = useCallback( ( formId ) => {
        const shortcode = `[wpuf_form id="${ formId }"]`;
        return applyFilters( 'wpuf.formsList.getShortcode', shortcode, formId, formType );
    }, [ formType ] );

    // Copy shortcode handler
    const handleCopyShortcode = useCallback( ( text, key ) => {
        copyToClipboard( text, key );
    }, [ copyToClipboard ] );

    // Menu items based on current tab
    const menuItems = useMemo( () => {
        if ( currentTab === 'trash' ) {
            return [
                {
                    label: __( 'Restore', 'wp-user-frontend' ),
                    action: 'restore',
                    className: '!wpuf-text-gray-900',
                    hoverClassName: 'hover:!wpuf-bg-primary hover:!wpuf-text-white',
                },
                {
                    label: __( 'Delete Permanently', 'wp-user-frontend' ),
                    action: 'delete',
                    className: '!wpuf-text-red-600',
                    hoverClassName: 'hover:!wpuf-bg-red-500 hover:!wpuf-text-white',
                },
            ];
        }

        return [
            {
                label: __( 'Edit', 'wp-user-frontend' ),
                action: 'edit',
                className: '!wpuf-text-gray-900',
                hoverClassName: 'hover:!wpuf-bg-primary hover:!wpuf-text-white',
            },
            {
                label: __( 'Duplicate', 'wp-user-frontend' ),
                action: 'duplicate',
                className: '!wpuf-text-gray-900',
                hoverClassName: 'hover:!wpuf-bg-primary hover:!wpuf-text-white',
            },
            {
                label: __( 'Trash', 'wp-user-frontend' ),
                action: 'trash',
                className: '!wpuf-text-red-600',
                hoverClassName: 'hover:!wpuf-bg-red-500 hover:!wpuf-text-white',
            },
        ];
    }, [ currentTab ] );

    // Determine empty state type
    const getEmptyStateType = () => {
        if ( searchTerm !== '' ) {
            return 'search';
        }
        if ( currentTab === 'any' ) {
            return 'empty';
        }
        return 'tab-empty';
    };

    return (
        <div>
            { /* Permalink Notice */ }
            { isPlainPermalink && (
                <div className="wpuf-bg-yellow-50 wpuf-border wpuf-border-yellow-200 wpuf-rounded-md wpuf-p-4 wpuf-mt-6">
                    <div className="wpuf-flex">
                        <div className="wpuf-flex-shrink-0">
                            <svg className="wpuf-h-5 wpuf-w-5 wpuf-text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="wpuf-ml-3">
                            <h3 className="wpuf-text-sm wpuf-font-medium wpuf-text-yellow-800">
                                { __( 'WordPress REST API Issue Detected', 'wp-user-frontend' ) }
                            </h3>
                            <div className="wpuf-mt-2 wpuf-text-sm wpuf-text-yellow-700">
                                <p>
                                    { __( 'Your WordPress permalinks are set to "Plain" which may cause issues with fetching the forms. For better functionality, please consider changing your permalink structure.', 'wp-user-frontend' ) }
                                </p>
                            </div>
                            <div className="wpuf-mt-4">
                                <div className="wpuf-flex">
                                    <a
                                        href={ permalinkUrl }
                                        className="wpuf-bg-yellow-50 wpuf-text-yellow-800 wpuf-rounded-md wpuf-border wpuf-border-yellow-300 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-medium hover:wpuf-bg-yellow-100 focus:wpuf-outline-none focus:wpuf-ring-2 focus:wpuf-ring-offset-2 focus:wpuf-ring-yellow-500"
                                    >
                                        { __( 'Go to Permalink Settings', 'wp-user-frontend' ) }
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            ) }

            { /* Page title and action buttons */ }
            <div className="wpuf-flex wpuf-justify-between wpuf-items-center wpuf-mt-9">
                <h3 className="wpuf-text-2xl wpuf-font-bold wpuf-m-0 wpuf-p-0 wpuf-leading-none">
                    { filteredPageTitle }
                </h3>
                <div className="wpuf-flex wpuf-gap-3">
                    <button
                        type="button"
                        onClick={ openAIFormBuilder }
                        className="wpuf-rounded-md wpuf-text-center wpuf-bg-gradient-to-r wpuf-from-purple-600 wpuf-to-blue-600 wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-from-purple-700 hover:wpuf-to-blue-700 hover:wpuf-text-white focus:wpuf-from-purple-700 focus:wpuf-to-blue-700 focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer wpuf-inline-flex wpuf-items-center"
                    >
                        <svg className="wpuf-w-5 wpuf-h-5 wpuf-pr-1" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8.17766 13.2532L7.5 15.625L6.82234 13.2532C6.4664 12.0074 5.4926 11.0336 4.24682 10.6777L1.875 10L4.24683 9.32234C5.4926 8.9664 6.4664 7.9926 6.82234 6.74682L7.5 4.375L8.17766 6.74683C8.5336 7.9926 9.5074 8.9664 10.7532 9.32234L13.125 10L10.7532 10.6777C9.5074 11.0336 8.5336 12.0074 8.17766 13.2532Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            <path d="M15.2157 7.26211L15 8.125L14.7843 7.26212C14.5324 6.25444 13.7456 5.46764 12.7379 5.21572L11.875 5L12.7379 4.78428C13.7456 4.53236 14.5324 3.74556 14.7843 2.73789L15 1.875L15.2157 2.73788C15.4676 3.74556 16.2544 4.53236 17.2621 4.78428L18.125 5L17.2621 5.21572C16.2544 5.46764 15.4676 6.25444 15.2157 7.26211Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                            <path d="M14.0785 17.1394L13.75 18.125L13.4215 17.1394C13.2348 16.5795 12.7955 16.1402 12.2356 15.9535L11.25 15.625L12.2356 15.2965C12.7955 15.1098 13.2348 14.6705 13.4215 14.1106L13.75 13.125L14.0785 14.1106C14.2652 14.6705 14.7045 15.1098 15.2644 15.2965L16.25 15.625L15.2644 15.9535C14.7045 16.1402 14.2652 16.5795 14.0785 17.1394Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                        </svg>
                        { __( 'AI Form Builder', 'wp-user-frontend' ) }
                    </button>
                    <button
                        type="button"
                        onClick={ openModal }
                        className="new-wpuf-form wpuf-rounded-md wpuf-text-center wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white wpuf-shadow-sm hover:wpuf-bg-primaryHover hover:wpuf-text-white focus:wpuf-bg-primaryHover focus:wpuf-text-white focus:wpuf-shadow-none hover:wpuf-cursor-pointer"
                    >
                        <span className="dashicons dashicons-plus-alt2"></span>
                        &nbsp;
                        { __( 'Add New ', 'wp-user-frontend' ) }
                    </button>
                </div>
            </div>

            { /* Status Tabs */ }
            <StatusTabs
                postCounts={ postCounts }
                currentTab={ currentTab }
                onTabChange={ handleTabChange }
            />

            { /* Bulk Actions & Search */ }
            <div className="wpuf-flex wpuf-justify-between wpuf-my-8">
                <BulkActions
                    currentTab={ currentTab }
                    selectedBulkAction={ selectedBulkAction }
                    onBulkActionChange={ setSelectedBulkAction }
                    onApply={ handleBulkAction }
                    disabled={ selectedForms.length === 0 }
                />
                <SearchBar
                    value={ searchTerm }
                    onChange={ handleSearchChange }
                />
            </div>

            { /* Loading State — matches Vue HollowDotsSpinner */ }
            { loading && (
                <>
                    <style>{ `
                        @keyframes hollow-dots-spinner-animation {
                            50% { transform: scale(1); opacity: 1; }
                            100% { opacity: 0; }
                        }
                        .wpuf-hollow-dots-spinner {
                            display: flex;
                            gap: 15px;
                        }
                        .wpuf-hollow-dots-spinner .wpuf-dot {
                            width: 20px;
                            height: 20px;
                            border: 3px solid #7DC442;
                            border-radius: 50%;
                            transform: scale(0);
                            animation: hollow-dots-spinner-animation 1s ease infinite 0ms;
                        }
                    ` }</style>
                    <div className="wpuf-flex wpuf-h-16 wpuf-items-center wpuf-justify-center">
                        <div className="wpuf-hollow-dots-spinner">
                            <div className="wpuf-dot" style={ { animationDelay: '0.3s' } }></div>
                            <div className="wpuf-dot" style={ { animationDelay: '0.6s' } }></div>
                            <div className="wpuf-dot" style={ { animationDelay: '0.9s' } }></div>
                        </div>
                    </div>
                </>
            ) }

            { /* Empty States */ }
            { ! loading && forms.length === 0 && (
                <EmptyState
                    type={ getEmptyStateType() }
                    onAddNew={ openModal }
                    onAIFormBuilder={ openAIFormBuilder }
                />
            ) }

            { /* Forms Table */ }
            { ! loading && forms.length > 0 && (
                <>
                    <FormsTable
                        forms={ forms }
                        currentTab={ currentTab }
                        selectedForms={ selectedForms }
                        selectAllChecked={ selectAllChecked }
                        onSelectAll={ handleSelectAll }
                        onSelectForm={ handleSelectForm }
                        onAction={ handleAction }
                        postType={ postType }
                        getShortcode={ getShortcode }
                        copiedKey={ copiedKey }
                        onCopyShortcode={ handleCopyShortcode }
                        menuItems={ menuItems }
                    />

                    <Pagination
                        currentPage={ pagination.current_page }
                        totalPages={ pagination.total_pages }
                        onPageChange={ handlePageChange }
                    />
                </>
            ) }

            { /* AI Config Modal */ }
            <AIConfigModal
                isOpen={ showAIConfigModal }
                onClose={ () => setShowAIConfigModal( false ) }
                onGoToSettings={ () => { window.location.href = aiSettingsUrl; } }
            />
        </div>
    );
};

export default FormsList;
