/**
 * Entry point for the React Settings app.
 *
 * Renders the redesigned WPUF settings screen. Reads/writes settings through
 * the wpuf/v1/settings REST endpoint, which persists to the exact same option
 * keys + field names the legacy settings screen used (storage parity).
 */
import { createRoot } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState, useCallback, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Save } from 'lucide-react';

import { Button, Notice, PageFooter, PageHeader, PageShell, SkeletonBar, Tabs, WpufProviders } from '@wpuf/components';
import SettingsNav from './components/settings/SettingsNav';
import SettingsSection, { PROVIDER_SECTIONS } from './components/settings/SettingsSection';
import { stripTags } from './components/settings/utils';
import UnsavedChanges from './components/settings/UnsavedChanges';
import MessageModal from './components/settings/MessageModal';
import ErrorBoundary from './components/settings/ErrorBoundary';
import ModuleOff from './components/settings/ModuleOff';
import { OPEN_SECTION_EVENT } from './components/settings/navigate';

// Register the store.
import './stores/settings';
import { STORE_NAME } from './stores/settings/constants';
import { addRouteGuard, getRouteQuery, inApp, registerScreen, setRouteQuery } from '../../app/client';

// REST calls go through `@wpuf/api` (api/settings.js), which wraps core's
// window.wp.apiFetch: REST root and nonce come from core, no extra middleware.

const SettingsApp = () => {
    const { ia, sections, fields, activeTab, isLoading, isSaving, isDirty, error, search } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            ia: store.getIa(),
            sections: store.getSections(),
            fields: store.getFields(),
            activeTab: store.getActiveTab(),
            isLoading: store.isLoading(),
            isSaving: store.isSaving(),
            isDirty: store.isDirty(),
            error: store.getError(),
            search: store.getSearch(),
        };
    }, [] );

    const { setActiveTab, setSearch, save, discard, loadSettings, setError } = useDispatch( STORE_NAME );

    const boot = window.wpuf_settings || {};
    const [ pendingTab, setPendingTab ] = useState( null );
    const legacyOnly = Array.isArray( boot.legacy_only ) ? boot.legacy_only : [];
    const [ activeSub, setActiveSub ] = useState( null );
    const [ justSaved, setJustSaved ] = useState( false );
    const savedTimer = useRef( null );

    // Explicit "Saved" confirmation: driven by the save() thunk's boolean result,
    // not inferred from isSaving/error transitions.
    const handleSave = useCallback( async () => {
        const ok = await save();
        if ( ok ) {
            setJustSaved( true );
            if ( savedTimer.current ) {
                clearTimeout( savedTimer.current );
            }
            savedTimer.current = setTimeout( () => setJustSaved( false ), 2500 );
        }
    }, [ save ] );

    useEffect( () => () => {
        if ( savedTimer.current ) {
            clearTimeout( savedTimer.current );
        }
    }, [] );

    const sectionTitle = ( id ) => {
        const s = sections.find( ( x ) => x.id === id );
        return s ? stripTags( s.title ) : id;
    };

    const didInit = useRef( false );

    // Load the settings payload once on mount.
    useEffect( () => {
        loadSettings();
    }, [ loadSettings ] );

    // Restore the active tab + sub-tab from the URL once the IA loads (so a
    // refresh / shared link lands on the same tab); fall back to the first tab.
    useEffect( () => {
        if ( didInit.current || ! ia.length ) {
            return;
        }
        const query = getRouteQuery();
        let urlTab = query.tab || null;
        let urlSub = query.sub || null;

        // Links from the classic screen point at a section, by hash
        // (`#wpuf_ai`, `#/ai`) or as `?tab=wpuf_payment`: open the tab that
        // holds that section, on that section. In the admin app the old
        // fragment arrives as the route's `hash` query.
        const hash = ( inApp() ? query.hash || '' : window.location.hash ).replace( /^#?\/?/, '' );
        const target = ( urlTab && ! ia.find( ( t ) => t.id === urlTab ) && urlTab ) || ( ! urlTab && hash ) || '';
        if ( target ) {
            const section = target.startsWith( 'wpuf_' ) || target === 'n8n' ? target : `wpuf_${ target }`;
            const owner = ia.find( ( t ) => Array.isArray( t.sections ) && t.sections.includes( section ) );
            if ( owner ) {
                urlTab = owner.id;
                urlSub = section;
            } else if ( ia.find( ( t ) => t.id === target ) ) {
                urlTab = target;
            }
        }

        const restoredTab = ( urlTab && ia.find( ( t ) => t.id === urlTab ) ) || ia[ 0 ];
        setActiveTab( restoredTab.id );

        // Only restore the sub-tab if it actually belongs to the restored tab,
        // so a stale/hand-edited `?sub=` doesn't show an empty panel.
        if ( urlSub && Array.isArray( restoredTab.sections ) && restoredTab.sections.includes( urlSub ) ) {
            setActiveSub( urlSub );
        }
        didInit.current = true;
    }, [ ia, setActiveTab ] );

    // Reflect the active tab + sub-tab in the URL (replaceState — no reload, no
    // history spam) so the selection survives a refresh.
    useEffect( () => {
        if ( ! didInit.current || ! activeTab ) {
            return;
        }
        setRouteQuery( { tab: activeTab, sub: activeSub || null, hash: null } );
    }, [ activeTab, activeSub ] );

    // In the admin app, leaving the route with unsaved edits asks first (the
    // same dialog as switching tabs).
    const dirtyRef = useRef( isDirty );
    dirtyRef.current = isDirty;
    const [ pendingLeave, setPendingLeave ] = useState( null );

    useEffect(
        () => addRouteGuard( () => ( dirtyRef.current ? new Promise( ( resolve ) => setPendingLeave( { resolve } ) ) : true ) ),
        []
    );

    // Warn before leaving the page with unsaved changes.
    useEffect( () => {
        const handler = ( e ) => {
            if ( isDirty ) {
                e.preventDefault();
                e.returnValue = '';
            }
        };
        window.addEventListener( 'beforeunload', handler );
        return () => window.removeEventListener( 'beforeunload', handler );
    }, [ isDirty ] );

    // Intercept tab switches when there are unsaved edits.
    const handleSelectTab = useCallback(
        ( tabId ) => {
            // Clicking a tab exits an active search to that tab.
            setSearch( '' );
            if ( tabId === activeTab ) {
                return;
            }
            if ( isDirty ) {
                setPendingTab( tabId );
                return;
            }
            // Real user tab switch — start the new tab at its first sub-tab.
            setActiveSub( null );
            setActiveTab( tabId );
        },
        [ activeTab, isDirty, setActiveTab, setSearch ]
    );

    // A note or search result asked for a section: open its tab on it.
    useEffect( () => {
        const open = ( event ) => {
            const section = event.detail && event.detail.section;
            const owner = ia.find( ( t ) => ( t.sections || [] ).includes( section ) || ( t.off || [] ).some( ( o ) => o.id === section ) );

            if ( ! owner ) {
                return;
            }

            setSearch( '' );
            setActiveTab( owner.id );
            setActiveSub( owner.subtabs ? section : null );
            window.scrollTo( { top: 0, behavior: 'smooth' } );
        };

        window.addEventListener( OPEN_SECTION_EVENT, open );

        return () => window.removeEventListener( OPEN_SECTION_EVENT, open );
    }, [ ia, setActiveTab, setSearch ] );

    const handleDiscard = useCallback( () => {
        discard();
        if ( pendingTab ) {
            setActiveSub( null );
            setActiveTab( pendingTab );
        }
        setPendingTab( null );
    }, [ discard, pendingTab, setActiveTab ] );

    const currentTab = ia.find( ( t ) => t.id === activeTab ) || ia[ 0 ];
    // Sections of switched-off Pro modules (IA `off`): a sub-tab / tab with a notice.
    const offSections = ( currentTab && currentTab.off ) || [];
    const subtabIds = currentTab ? [ ...( currentTab.sections || [] ), ...offSections.map( ( o ) => o.id ) ] : [];
    const currentSub = activeSub || subtabIds[ 0 ];
    const offShown = ! ( search && search.trim() ) && offSections.find( ( o ) => o.id === ( currentTab.subtabs ? currentSub : subtabIds[ 0 ] ) );
    // The tab that holds a section, for the search results' "Tab › Section" line.
    const ownerTitle = ( sid ) => {
        const owner = ia.find( ( t ) => ( t.sections || [] ).includes( sid ) );

        return owner ? owner.title : '';
    };

    // Global search: when a term is entered, search across EVERY section (not just
    // the active tab), so e.g. "social" finds the Social Login fields under
    // Login & Registration. Each SettingsSection renders null when it has no
    // matching field, so only sections with hits appear.
    const searching = !! ( search && search.trim() );
    const searchTerm = searching ? search.trim().toLowerCase() : '';
    const allSectionIds = [ ...new Set( ia.flatMap( ( t ) => t.sections || [] ) ) ];
    const renderedSections = searching
        ? allSectionIds
        : ( currentTab
            ? ( currentTab.sections || [] ).filter(
                ( sectionId ) => ! offShown && ( ! currentTab.subtabs || currentSub === sectionId )
            )
            : [] );

    // Whether any section has a field / title matching the search (coarse — used
    // only to decide between rendering results vs the empty state).
    const searchHasResults = ! searching || allSectionIds.some( ( sid ) => {
        const secFields = fields[ sid ] || [];
        if ( secFields.some( ( f ) =>
            `${ f.label || '' } ${ f.desc || '' } ${ f.name || '' }`.toLowerCase().includes( searchTerm )
        ) ) {
            return true;
        }
        // Provider sections can match on the provider's display name/key (e.g.
        // "facebook" / "vonage") even when no field label contains it — mirror
        // SettingsSection/ProviderTabs so the empty state isn't shown falsely.
        const providers = PROVIDER_SECTIONS[ sid ];
        if ( providers && providers.some( ( p ) =>
            ( p.label || '' ).toLowerCase().includes( searchTerm ) || ( p.key || '' ).toLowerCase().includes( searchTerm )
        ) ) {
            return true;
        }
        const sec = sections.find( ( s ) => s.id === sid );
        return sec && stripTags( sec.title ).toLowerCase().includes( searchTerm );
    } );

    return (
        <PageShell className="wpuf-settings-screen">
            <PageHeader
                utm="wpuf-settings"
                supportUrl={ boot.support_url || undefined }
            />

            { isLoading ? (
                <div className="pt-6 pb-6">
                    <div role="status" aria-busy="true" className="flex gap-8 rounded-lg border border-gray-200 bg-white p-8 shadow-xs">
                        {/* Left nav skeleton */}
                        <div className="w-[280px] shrink-0 [&>:not([hidden])~:not([hidden])]:mt-3 [&>:not([hidden])~:not([hidden])]:mb-0">
                            <SkeletonBar className="h-9 bg-gray-100" />
                            { Array.from( { length: 8 } ).map( ( _, i ) => (
                                <SkeletonBar key={ i } className="h-9 bg-gray-100" />
                            ) ) }
                        </div>
                        {/* Content skeleton */}
                        <div className="min-w-0 flex-1 border-l border-gray-200 pl-8">
                            <SkeletonBar className="h-7 w-44 rounded-sm" />
                            <div className="my-8 border-b border-gray-200" />
                            <div className="[&>:not([hidden])~:not([hidden])]:mt-6 [&>:not([hidden])~:not([hidden])]:mb-0">
                                { Array.from( { length: 5 } ).map( ( _, i ) => (
                                    <div key={ i }>
                                        <SkeletonBar className="mb-2 h-4 w-32 rounded-sm bg-gray-100" />
                                        <SkeletonBar className="h-9 w-full bg-gray-100" />
                                    </div>
                                ) ) }
                            </div>
                        </div>
                    </div>
                </div>
            ) : (
            <div className="pt-6 pb-6">
            { /* Settings of other plugins the React screen cannot show (D12). */ }
            { legacyOnly.length > 0 && (
                <Notice
                    role="status"
                    data-settings-notice="classic-only"
                    tone="warning"
                    className="mb-6"
                    title={ __( 'Some settings added by other plugins cannot be edited on this screen:', 'wp-user-frontend' ) }
                >
                    { legacyOnly.join( ', ' ) }
                </Notice>
            ) }

            { error ? (
                <MessageModal
                    title={ __( 'Couldn’t save settings', 'wp-user-frontend' ) }
                    message={ error }
                    tone="error"
                    onClose={ () => setError( null ) }
                />
            ) : null }

            { /* Title row over both columns (FlyHR settings: a fixed page title; the
               section name is the card title). */ }
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h2 className="mt-0 mb-0 text-2xl! font-bold leading-8 text-gray-900">
                    { searching
                        ? __( 'Search results', 'wp-user-frontend' )
                        : __( 'Settings', 'wp-user-frontend' ) }
                </h2>
            </div>

            <div className="flex items-start gap-6">
                <SettingsNav
                    ia={ ia }
                    activeTab={ activeTab }
                    onSelect={ handleSelectTab }
                    search={ search }
                    onSearch={ setSearch }
                />

                <div className="min-w-0 flex-1 max-w-full">

                    { ! searching && currentTab && currentTab.notice ? (
                        <Notice tone="warning" className="mb-8">
                            { currentTab.notice }
                        </Notice>
                    ) : null }

                    { ! searching && currentTab && currentTab.subtabs && subtabIds.length > 1 ? (
                        <div className="mb-8" data-settings-subtabs="">
                            <Tabs
                                variant="pill"
                                label={ currentTab.title }
                                tabs={ subtabIds.map( ( sid ) => {
                                    const off = offSections.find( ( o ) => o.id === sid );

                                    return { id: sid, label: off ? off.title : sectionTitle( sid ) };
                                } ) }
                                value={ currentSub }
                                onChange={ setActiveSub }
                            />
                        </div>
                    ) : null }

                    { offShown ? <ModuleOff title={ offShown.title } /> : null }

                    { searching && ! searchHasResults ? (
                        <div className="flex flex-col items-center justify-center py-16 text-center">
                            <svg className="mb-4 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            <p className="m-0 text-base font-medium text-gray-700">
                                { __( 'No settings found', 'wp-user-frontend' ) }
                            </p>
                            <p className="mt-1 mb-0 text-sm text-gray-400">
                                { __( 'Try a different keyword.', 'wp-user-frontend' ) }
                            </p>
                            <Button size="sm" variant="secondary" className="mt-4" onClick={ () => setSearch( '' ) }>
                                { __( 'Clear search', 'wp-user-frontend' ) }
                            </Button>
                        </div>
                    ) : (
                        renderedSections.map( ( sectionId ) => (
                            <ErrorBoundary key={ sectionId }>
                                <SettingsSection
                                    sectionId={ sectionId }
                                    tabTitle={ ! searching && currentTab && ! currentTab.subtabs ? currentTab.title : null }
                                    searchTab={ searching ? ownerTitle( sectionId ) : null }
                                />
                            </ErrorBoundary>
                        ) )
                    ) }
                </div>
            </div>
            { /* Save / Cancel: a sticky card at the end of the content, as FlyHR's
                Add Employee form (owner 2026-10-06): it follows the window bottom
                while the section is taller than the window and sits above the
                logo footer, never over it. */ }
            <div className="wpuf-settings-actions sticky bottom-0 z-10 mt-6 flex items-center justify-end gap-3 rounded-[10px] border border-gray-200 bg-white/95 px-6 py-4 shadow-[0_-1px_3px_rgba(0,0,0,0.06)] backdrop-blur">
                { justSaved ? (
                    <span className="me-auto flex items-center gap-1 text-xs font-medium text-emerald-600">
                        <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" strokeWidth="2.5" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        { __( 'Saved', 'wp-user-frontend' ) }
                    </span>
                ) : isDirty ? (
                    <span className="me-auto text-xs text-gray-400">
                        { __( 'Unsaved changes', 'wp-user-frontend' ) }
                    </span>
                ) : null }
                <Button
                    variant="secondary"
                    size="lg"
                    disabled={ ! isDirty || isSaving }
                    onClick={ () => discard() }
                >
                    { __( 'Cancel', 'wp-user-frontend' ) }
                </Button>
                <Button
                    size="lg"
                    disabled={ isSaving || ! isDirty }
                    onClick={ handleSave }
                >
                    <Save size={ 16 } strokeWidth={ 2 } aria-hidden="true" />
                    { isSaving ? __( 'Saving…', 'wp-user-frontend' ) : __( 'Save', 'wp-user-frontend' ) }
                </Button>
            </div>
            </div>
            ) }

            <PageFooter />


            { pendingTab && (
                <UnsavedChanges
                    onDiscard={ handleDiscard }
                    onContinue={ () => setPendingTab( null ) }
                />
            ) }

            { pendingLeave && (
                <UnsavedChanges
                    onDiscard={ () => {
                        discard();
                        pendingLeave.resolve( true );
                        setPendingLeave( null );
                    } }
                    onContinue={ () => {
                        pendingLeave.resolve( false );
                        setPendingLeave( null );
                    } }
                />
            ) }
        </PageShell>
    );
};

// On its own page it mounts into #wpuf-settings-root; in the admin app the
// shell mounts it on the settings route (task 5d).
registerScreen( 'settings', [ 'wpuf-settings-root' ], ( container ) => {
    const root = createRoot( container );

    root.render(
        <WpufProviders host>
            <SettingsApp />
        </WpufProviders>
    );

    return () => root.unmount();
} );
