/**
 * FormsList component — main orchestrator for the forms list page.
 *
 * @since WPUF_SINCE
 */
import { useState, useEffect, useCallback, useMemo, useRef } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { request, restPath } from '@wpuf/api';
import { applyFilters } from '@wordpress/hooks';
import { AIConfigModal, ErrorState, Notice, Pagination, SearchInput, Tabs, notify, useConfirm } from '@wpuf/components';

import useFormsFetch, { PER_PAGE } from '../hooks/useFormsFetch';
import useClipboard from '../hooks/useClipboard';

import SelectionBar from './SelectionBar';
import FormsTable from './FormsTable';
import TableSkeleton from './TableSkeleton';
import EmptyState from './EmptyState';
import CreateButtons from './CreateButtons';
import TemplatePicker from './TemplatePicker';
import { inApp, openRoute } from '../../../app/client';
import { adminScript, formTemplates } from '../boot';

const FormsList = ( {
    postType = 'wpuf_forms',
    formType = 'post',
    pageSlug = 'wpuf-post-forms',
    pageTitle = 'Post Forms',
} ) => {
    const filteredPageTitle = applyFilters( 'wpuf.formsList.pageTitle', pageTitle, formType );
    // Pro hides the AI entry on the registration list when the free AI handler is missing.
    const aiFormBuilderAvailable = applyFilters( 'wpuf.formsList.aiFormBuilderAvailable', true, formType );

    const isPlainPermalink = wpuf_forms_list.is_plain_permalink;
    const permalinkUrl = wpuf_forms_list.permalink_settings_url;
    const aiConfigured = wpuf_forms_list.ai_configured || false;
    const aiSettingsUrl = wpuf_forms_list.ai_settings_url || '';
    const initialCounts = wpuf_forms_list.post_counts || {};

    const newFormUrl = adminScript().admin_url + 'admin.php?page=wpuf-' + formType + '-forms&action=add-new';
    // Route base of this list in the admin app (task 5d).
    const routeBase = 'profile' === formType ? '/registration-forms' : '/post-forms';

    // Newest first until a sortable header is clicked (server default).
    const [ sort, setSort ] = useState( null );
    const { forms, loading, error, pagination, counts, fetchForms } = useFormsFetch( { postType, sort } );
    // The page-load counts until the list has answered once, then the live ones.
    const postCounts = counts || initialCounts;
    const { copiedKey, copyToClipboard } = useClipboard();
    const [ confirm, confirmDialog ] = useConfirm();

    const [ currentTab, setCurrentTab ] = useState( 'any' );
    const [ searchTerm, setSearchTerm ] = useState( '' );
    const [ selectedForms, setSelectedForms ] = useState( [] );
    const [ perPage, setPerPage ] = useState( PER_PAGE );
    const [ showAIConfigModal, setShowAIConfigModal ] = useState( false );
    const [ showTemplates, setShowTemplates ] = useState( false );
    // Admin\Forms\Template_Picker; missing with an older Pro (it prints the PHP modal).
    const templateData = formTemplates() || null;

    const debounceTimerRef = useRef( null );

    // Fetch on mount and whenever the tab, the page size or the sort changes (page 1, current search).
    useEffect( () => {
        fetchForms( 1, currentTab, searchTerm, perPage );
    }, [ currentTab, perPage, sort ] );

    // Debounced search
    const handleSearchChange = useCallback( ( value ) => {
        setSearchTerm( value );

        if ( debounceTimerRef.current ) {
            clearTimeout( debounceTimerRef.current );
        }

        debounceTimerRef.current = setTimeout( () => {
            fetchForms( 1, currentTab, value, perPage );
        }, 500 );
    }, [ currentTab, perPage, fetchForms ] );

    // Cleanup debounce timer on unmount
    useEffect( () => {
        return () => {
            if ( debounceTimerRef.current ) {
                clearTimeout( debounceTimerRef.current );
            }
        };
    }, [] );

    // A new page of rows clears the selection.
    useEffect( () => {
        setSelectedForms( [] );
    }, [ forms ] );

    const selectAllChecked = forms.length > 0 && selectedForms.length === forms.length;

    const handleSelectAll = useCallback( () => {
        setSelectedForms( selectAllChecked ? [] : forms.map( ( form ) => form.ID ) );
    }, [ selectAllChecked, forms ] );

    const handleSelectForm = useCallback( ( formId ) => {
        setSelectedForms( ( prev ) => {
            if ( prev.includes( formId ) ) {
                return prev.filter( ( id ) => id !== formId );
            }
            return [ ...prev, formId ];
        } );
    }, [] );

    const handlePageChange = useCallback( ( page ) => {
        fetchForms( page, currentTab, searchTerm, perPage );
    }, [ currentTab, searchTerm, perPage, fetchForms ] );

    const retry = useCallback( () => {
        fetchForms( pagination.current_page, currentTab, searchTerm, perPage );
    }, [ pagination, currentTab, searchTerm, perPage, fetchForms ] );

    // Builder page URL (edit, when the admin app is off).
    const buildAdminUrl = useCallback( ( formId, action ) => {
        const params = new URLSearchParams( {
            page: pageSlug,
            id: formId.toString(),
            action,
        } );

        return `${ adminScript().admin_url }admin.php?${ params.toString() }`;
    }, [ pageSlug ] );

    // In the admin app the builder is a route of the same page.
    const editUrl = useCallback(
        ( formId ) => ( inApp() ? `#${ routeBase }/${ formId }/edit` : buildAdminUrl( formId, 'edit' ) ),
        [ buildAdminUrl, routeBase ]
    );

    // Row and bulk actions over the admin forms REST routes (same form store
    // calls as the classic list actions), then the list reloads in place: no
    // page load. A failed request shows its error and leaves the list as it is.
    // develop's list action notices (Admin_Form_Handler), now as toasts.
    const MESSAGES = {
        /* translators: %d: number of forms */
        trash: ( n ) => sprintf( _n( '%d form moved to the trash.', '%d forms moved to the trash.', n, 'wp-user-frontend' ), n ),
        /* translators: %d: number of forms */
        restore: ( n ) => sprintf( _n( '%d form restored from the trash.', '%d forms restored from the trash.', n, 'wp-user-frontend' ), n ),
        /* translators: %d: number of forms */
        delete: ( n ) => sprintf( _n( '%d form permanently deleted.', '%d forms permanently deleted.', n, 'wp-user-frontend' ), n ),
        duplicate: () => __( 'Form duplicated successfully.', 'wp-user-frontend' ),
    };

    const [ acting, setActing ] = useState( false );

    const runAction = useCallback( async ( action, ids ) => {
        if ( ! MESSAGES[ action ] || acting ) {
            return false;
        }

        setActing( true );

        let copyId = 0;

        try {
            for ( const id of ids ) {
                const body = await request(
                    restPath( 'wpuf/v1', 'delete' === action ? `/admin/forms/${ id }` : `/admin/forms/${ id }/${ action }` ),
                    { method: 'delete' === action ? 'DELETE' : 'POST' }
                );

                copyId = ( body && body.data && body.data.id ) || copyId;
            }
        } catch ( requestError ) {
            setActing( false );
            notify( ( requestError && requestError.message ) || __( 'Something went wrong. Please try again.', 'wp-user-frontend' ), 'error' );

            return false;
        }

        setActing( false );
        setSelectedForms( [] );
        // A copy: develop's notice linked it ("View form."), here the toast action does.
        notify(
            MESSAGES[ action ]( ids.length ),
            'success',
            'duplicate' === action && copyId ? {
                action: {
                    label: __( 'View form', 'wp-user-frontend' ),
                    onClick: () => openRoute( `${ routeBase }/${ copyId }/edit`, buildAdminUrl( copyId, 'edit' ) ),
                },
            } : undefined
        );

        // The page may be empty now (last rows trashed or deleted): step back one.
        const page = 'duplicate' !== action && ids.length >= forms.length && pagination.current_page > 1 ? pagination.current_page - 1 : pagination.current_page;

        fetchForms( page, currentTab, searchTerm, perPage );

        return true;
    }, [ acting, forms, pagination, currentTab, searchTerm, perPage, fetchForms, routeBase, buildAdminUrl ] ); // eslint-disable-line react-hooks/exhaustive-deps

    // Row action handler
    const handleAction = useCallback( async ( action, form ) => {
        // Edit: the builder route in the admin app (no page load), else its page.
        if ( 'edit' === action ) {
            openRoute( `${ routeBase }/${ form.ID }/edit`, buildAdminUrl( form.ID, 'edit' ) );

            return;
        }

        if ( 'delete' === action ) {
            const ok = await confirm( {
                title: __( 'Delete Permanently', 'wp-user-frontend' ),
                message: __( 'Are you sure you want to delete this form permanently? This action cannot be undone.', 'wp-user-frontend' ),
                confirmText: __( 'Delete Permanently', 'wp-user-frontend' ),
            } );

            if ( ! ok ) {
                return;
            }
        }

        await runAction( action, [ form.ID ] );
    }, [ buildAdminUrl, confirm, routeBase, runAction ] );

    // Bulk action handler (develop's bulk request to the server list action)
    const handleBulkAction = useCallback( async ( bulkAction ) => {
        if ( ! bulkAction || selectedForms.length === 0 ) {
            return;
        }

        if ( 'delete' === bulkAction ) {
            const ok = await confirm( {
                title: __( 'Delete Permanently', 'wp-user-frontend' ),
                message: __( 'Are you sure you want to delete the selected forms permanently? This action cannot be undone.', 'wp-user-frontend' ),
                confirmText: __( 'Delete Permanently', 'wp-user-frontend' ),
            } );

            if ( ! ok ) {
                return;
            }
        }

        await runAction( bulkAction, [ ...selectedForms ] );
    }, [ selectedForms, confirm, runAction ] );

    // Open the template picker, or go to the blank form when the screen has no templates.
    const openModal = useCallback( ( event ) => {
        event?.preventDefault();

        if ( templateData ) {
            setShowTemplates( true );
            return;
        }

        openRoute( `${ routeBase }/new`, newFormUrl );
    }, [ newFormUrl, routeBase, templateData ] );

    // AI Form Builder handler
    const openAIFormBuilder = useCallback( ( event ) => {
        event?.preventDefault();

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

        openRoute( `${ routeBase }/ai`, adminScript().admin_url + 'admin.php?' + params.toString() );
    }, [ aiConfigured, formType, routeBase ] );

    // Shortcode getter with Pro filter
    const getShortcode = useCallback( ( formId ) => {
        const shortcode = `[wpuf_form id="${ formId }"]`;
        return applyFilters( 'wpuf.formsList.getShortcode', shortcode, formId, formType );
    }, [ formType ] );

    // Copy shortcode handler (develop failed silently, e.g. on plain http)
    const handleCopyShortcode = useCallback( ( text, key ) => {
        copyToClipboard( text, key ).catch( () => {
            notify( __( 'Could not copy the shortcode. Please copy it manually.', 'wp-user-frontend' ), 'error' );
        } );
    }, [ copyToClipboard ] );

    // Menu items based on current tab (develop items)
    const menuItems = useMemo( () => {
        if ( currentTab === 'trash' ) {
            return [
                { key: 'restore', label: __( 'Restore', 'wp-user-frontend' ) },
                { key: 'delete', label: __( 'Delete Permanently', 'wp-user-frontend' ), destructive: true },
            ];
        }

        return [
            { key: 'edit', label: __( 'Edit', 'wp-user-frontend' ) },
            { key: 'duplicate', label: __( 'Duplicate', 'wp-user-frontend' ) },
            { key: 'trash', label: __( 'Trash', 'wp-user-frontend' ), destructive: true },
        ];
    }, [ currentTab ] );

    const tabs = Object.entries( postCounts ).map( ( [ key, value ] ) => ( {
        id: key === 'all' ? 'any' : key,
        label: value.label,
        count: value.count,
    } ) );

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

    const aiHandler = aiFormBuilderAvailable ? openAIFormBuilder : null;

    const closeTemplates = useCallback( () => setShowTemplates( false ), [] );
    // The AI card: the picker closes first, so the "not configured" dialog is on top.
    const pickAI = useCallback( ( event ) => {
        setShowTemplates( false );
        openAIFormBuilder( event );
    }, [ openAIFormBuilder ] );

    let content;

    if ( loading ) {
        content = <TableSkeleton />;
    } else if ( error ) {
        content = (
            <ErrorState
                title={ __( 'Could not load the forms', 'wp-user-frontend' ) }
                message={ error.message }
                onRetry={ retry }
            />
        );
    } else if ( forms.length === 0 ) {
        content = <EmptyState type={ getEmptyStateType() } formType={ formType } onAddNew={ openModal } onAIFormBuilder={ aiHandler } />;
    } else {
        content = (
            <>
                <FormsTable
                    forms={ forms }
                    selectedForms={ selectedForms }
                    selectAllChecked={ selectAllChecked }
                    onSelectAll={ handleSelectAll }
                    onSelectForm={ handleSelectForm }
                    onAction={ handleAction }
                    editUrl={ editUrl }
                    postType={ postType }
                    formType={ formType }
                    getShortcode={ getShortcode }
                    copiedKey={ copiedKey }
                    onCopyShortcode={ handleCopyShortcode }
                    menuItems={ menuItems }
                    sort={ sort }
                    onSort={ setSort }
                />

                <Pagination
                    variant="footer"
                    currentPage={ pagination.current_page }
                    total={ pagination.total_items }
                    perPage={ pagination.per_page }
                    onPageChange={ handlePageChange }
                    onPerPageChange={ setPerPage }
                />
            </>
        );
    }

    return (
        <div>
            { /* Permalink Notice */ }
            { isPlainPermalink && (
                <Notice
                    tone="warning"
                    className="mt-6"
                    title={ __( 'WordPress REST API Issue Detected', 'wp-user-frontend' ) }
                    action={
                        <a href={ permalinkUrl } className="font-medium text-amber-900 underline hover:text-amber-900">
                            { __( 'Go to Permalink Settings', 'wp-user-frontend' ) }
                        </a>
                    }
                >
                    { __( 'Your WordPress permalinks are set to "Plain" which may cause issues with fetching the forms. For better functionality, please consider changing your permalink structure.', 'wp-user-frontend' ) }
                </Notice>
            ) }

            { /* Page title and action buttons */ }
            <div className="flex justify-between items-center mt-6">
                <h3 className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">
                    { filteredPageTitle }
                </h3>
                <CreateButtons onAddNew={ openModal } onAIFormBuilder={ aiHandler } />
            </div>

            { /* List card (FlyHR): toolbar with status tabs + search, selection
                 bar, table or state, footer pagination. */ }
            <div className="mt-6 rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3 border-0 border-b border-solid border-gray-200 px-4 pt-3 pb-2">
                    <Tabs
                        variant="toolbar"
                        tabs={ tabs }
                        value={ currentTab }
                        onChange={ setCurrentTab }
                        label={ __( 'Form status', 'wp-user-frontend' ) }
                    />
                    <SearchInput
                        value={ searchTerm }
                        onChange={ handleSearchChange }
                        placeholder={ __( 'Search Forms', 'wp-user-frontend' ) }
                        className="wpuf-form-search-box"
                    />
                </div>

                { selectedForms.length > 0 && (
                    <SelectionBar
                        count={ selectedForms.length }
                        currentTab={ currentTab }
                        onAction={ handleBulkAction }
                        onClear={ () => setSelectedForms( [] ) }
                    />
                ) }

                { content }
            </div>

            { templateData && (
                <TemplatePicker
                    open={ showTemplates }
                    onClose={ closeTemplates }
                    data={ templateData }
                    onAI={ aiHandler ? pickAI : null }
                />
            ) }

            <AIConfigModal
                isOpen={ showAIConfigModal }
                onClose={ () => setShowAIConfigModal( false ) }
                onGoToSettings={ () => {
                    window.location.href = aiSettingsUrl;
                } }
            />

            { confirmDialog }
        </div>
    );
};

export default FormsList;
