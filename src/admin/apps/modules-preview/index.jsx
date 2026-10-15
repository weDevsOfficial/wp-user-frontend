/**
 * User Frontend > Modules without Pro: the admin app route #/modules
 * (Admin\Screens\ModulesPreview), laid out like Pro's modules screen: a
 * header card with the upgrade, status tabs and a search inside the list
 * card, module cards with artwork, name, description, a status pill and the
 * switch; the Pro modules on their own tab, locked, with an Upgrade.
 * Switches call `wpuf/v1/admin/free-modules/{module}`; a switched module
 * applies on the next page load, like the classic page.
 *
 * @since WPUF_SINCE
 */
import { createRoot, useEffect, useMemo, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import {
    Button,
    EmptyState,
    PageFooter,
    PageHeader,
    PageShell,
    Tabs,
    TextInput,
    Toggle,
    WpufProviders,
    dialogs,
    notify,
} from '@wpuf/components';
import { Boxes, Check, Crown, ExternalLink, Lock, Search } from 'lucide-react';

import { registerScreen } from '../../app/client';

const data = () => window.wpufModulesPreview || {};
const openUpgrade = () => window.open( data().upgradeUrl, '_blank', 'noopener,noreferrer' );

const PERKS = [
    __( '20+ premium modules', 'wp-user-frontend' ),
    __( 'Social login, user directory, private messages', 'wp-user-frontend' ),
    __( 'Stripe, Mailchimp, Zapier and more', 'wp-user-frontend' ),
    __( '14 days refund policy', 'wp-user-frontend' ),
];

/**
 * Small rounded status pill (as on Pro's modules screen).
 *
 * @param {Object} props
 * @param {string} props.tone     success|neutral|warning.
 * @param {*}      props.children Text.
 *
 * @return {JSX.Element} Pill.
 */
function Pill( { tone, children } ) {
    const tones = {
        success: 'bg-emerald-50 text-emerald-700',
        neutral: 'bg-gray-100 text-gray-600',
        warning: 'bg-amber-100 text-amber-800',
    };

    return <span className={ `inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ${ tones[ tone ] || tones.neutral }` }>{ children }</span>;
}

/**
 * One module card.
 *
 * @param {Object}   props
 * @param {Object}   props.item     Module.
 * @param {boolean}  props.locked   A Pro module.
 * @param {boolean}  props.busy     Request in flight for this module.
 * @param {Function} props.onToggle ( item, next ) => void
 * @param {Function} props.onLocked ( item ) => void
 *
 * @return {JSX.Element} Card.
 */
function ModuleCard( { item, locked, busy, onToggle, onLocked } ) {
    return (
        <li className="m-0 flex flex-col gap-4 rounded-lg border border-solid border-gray-200 bg-white p-5 shadow-sm" data-module={ item.id }>
            <div className="flex gap-4">
                { item.thumbnail ? (
                    <img src={ item.thumbnail } alt="" width={ 50 } height={ 50 } className={ `h-[50px] w-[50px] shrink-0 rounded-md object-contain ${ locked ? 'opacity-70 grayscale-[30%]' : '' }` } />
                ) : (
                    <span aria-hidden="true" className="flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-500">
                        <Boxes size={ 22 } />
                    </span>
                ) }
                <div className="min-w-0">
                    <h2 className="m-0 text-sm font-semibold leading-5 text-gray-900">
                        { item.docs ? (
                            <a href={ item.docs } target="_blank" rel="noreferrer noopener" className="text-gray-900 no-underline hover:text-primary hover:underline">{ item.name }</a>
                        ) : item.name }
                    </h2>
                    <p className="m-0 mt-1.5 text-xs leading-5 text-gray-500">{ item.description }</p>
                </div>
            </div>

            <div className="mt-auto flex items-center justify-between gap-3 border-0 border-t border-solid border-gray-200 pt-3">
                { locked ? (
                    <Pill tone="warning">
                        <Crown size={ 12 } aria-hidden="true" />
                        { __( 'Pro', 'wp-user-frontend' ) }
                    </Pill>
                ) : (
                    <Pill tone={ item.active ? 'success' : 'neutral' }>
                        { item.active ? __( 'Active', 'wp-user-frontend' ) : __( 'Inactive', 'wp-user-frontend' ) }
                    </Pill>
                ) }

                { locked ? (
                    <button
                        type="button"
                        onClick={ () => onLocked( item ) }
                        className="inline-flex h-8 shrink-0 cursor-pointer items-center gap-1 rounded-md border border-solid border-amber-300 bg-white px-3 text-xs font-semibold text-amber-800 hover:bg-amber-50"
                    >
                        <Lock size={ 13 } aria-hidden="true" />
                        { __( 'Upgrade', 'wp-user-frontend' ) }
                    </button>
                ) : (
                    <Toggle
                        value={ item.active }
                        checkedValue={ true }
                        uncheckedValue={ false }
                        disabled={ busy }
                        aria-label={
                            /* translators: %s: module name */
                            sprintf( __( 'Turn %s on or off', 'wp-user-frontend' ), item.name )
                        }
                        onChange={ ( next ) => onToggle( item, next ) }
                    />
                ) }
            </div>
        </li>
    );
}

function ModulesPreview() {
    const boot = data();
    const [ free, setFree ] = useState( () => ( Array.isArray( boot.free ) ? boot.free : [] ) );
    const pro = Array.isArray( boot.pro ) ? boot.pro : [];
    const [ filter, setFilter ] = useState( 'all' );
    const [ search, setSearch ] = useState( '' );
    const [ query, setQuery ] = useState( '' );
    const [ busyId, setBusyId ] = useState( null );

    // Debounce the search box (300 ms, like the other lists).
    useEffect( () => {
        const id = window.setTimeout( () => setQuery( search ), 300 );

        return () => window.clearTimeout( id );
    }, [ search ] );

    const activeCount = free.filter( ( item ) => item.active ).length;

    const visible = useMemo( () => {
        const needle = query.trim().toLowerCase();
        const list = 'pro' === filter ? pro : free;

        return list.filter( ( item ) => {
            if ( 'active' === filter && ! item.active ) {
                return false;
            }
            if ( 'inactive' === filter && item.active ) {
                return false;
            }

            return ! needle || item.name.toLowerCase().includes( needle ) || item.description.toLowerCase().includes( needle );
        } );
    }, [ free, pro, filter, query ] );

    const proVisible = useMemo( () => {
        const needle = query.trim().toLowerCase();

        return pro.filter( ( item ) => ! needle || item.name.toLowerCase().includes( needle ) || item.description.toLowerCase().includes( needle ) );
    }, [ pro, query ] );

    const onToggle = ( item, next ) => {
        setBusyId( item.id );
        apiFetch( {
            path: '/wpuf/v1/admin/free-modules/' + encodeURIComponent( item.id ),
            method: 'POST',
            data: { status: next ? 'active' : 'inactive' },
        } )
            .then( () => {
                setFree( ( list ) => list.map( ( entry ) => ( entry.id === item.id ? { ...entry, active: !! next } : entry ) ) );
                notify(
                    next
                        /* translators: %s: module name */
                        ? sprintf( __( '%s activated. Reload the page to use it.', 'wp-user-frontend' ), item.name )
                        /* translators: %s: module name */
                        : sprintf( __( '%s deactivated.', 'wp-user-frontend' ), item.name ),
                    'success'
                );
            } )
            .catch( ( error ) => notify( ( error && error.message ) || __( 'Could not change the module.', 'wp-user-frontend' ), 'error' ) )
            .finally( () => setBusyId( null ) );
    };

    const onLocked = async ( item ) => {
        const ok = await dialogs.confirm( {
            title: sprintf(
                /* translators: %s: module name */
                __( 'Unlock %s', 'wp-user-frontend' ),
                item.name
            ),
            message: sprintf(
                /* translators: %s: module name */
                __( '%s is part of WP User Frontend Pro. Upgrade to turn it on.', 'wp-user-frontend' ),
                item.name
            ),
            icon: 'info',
            tone: 'primary',
            confirmText: __( 'Upgrade to Pro', 'wp-user-frontend' ),
        } );

        if ( ok ) {
            openUpgrade();
        }
    };

    const tabs = [
        { id: 'all', label: __( 'My Modules', 'wp-user-frontend' ), count: free.length },
        { id: 'active', label: __( 'Active', 'wp-user-frontend' ), count: activeCount },
        { id: 'inactive', label: __( 'Inactive', 'wp-user-frontend' ), count: free.length - activeCount },
        { id: 'pro', label: __( 'Pro Modules', 'wp-user-frontend' ), count: pro.length },
    ];

    return (
        <PageShell>
            <PageHeader utm="wpuf-modules" helpUrl={ boot.docsUrl } helpLabel={ __( 'Learn more about modules', 'wp-user-frontend' ) } />

            <div className="space-y-6 pt-6 pb-10">
                { /* Header card: title and the way to the Pro modules. */ }
                <header className="flex flex-wrap items-center justify-between gap-6 rounded-[10px] border border-solid border-gray-200 bg-white p-6 shadow-sm">
                    <div className="min-w-0 max-w-xl">
                        <h2 className="m-0 text-2xl! font-bold leading-8 text-gray-900">{ __( 'Modules', 'wp-user-frontend' ) }</h2>
                        <p className="m-0 mt-1 text-sm text-gray-500">
                            { __( 'Turn a module on or off. Turning one off keeps its settings and data.', 'wp-user-frontend' ) }
                        </p>
                        <ul className="m-0 mt-4 grid list-none grid-cols-1 gap-2 p-0 sm:grid-cols-2">
                            { PERKS.map( ( perk ) => (
                                <li key={ perk } className="m-0 flex items-center gap-2 text-sm text-gray-700">
                                    <Check size={ 16 } aria-hidden="true" className="shrink-0 text-primary" />
                                    { perk }
                                </li>
                            ) ) }
                        </ul>
                    </div>
                    <div className="flex flex-wrap items-center gap-3">
                        <Pill tone="neutral">{ __( 'Free version', 'wp-user-frontend' ) }</Pill>
                        <Button onClick={ openUpgrade }>
                            <Crown size={ 14 } aria-hidden="true" />
                            { __( 'Upgrade to Pro', 'wp-user-frontend' ) }
                            <ExternalLink size={ 14 } aria-hidden="true" />
                        </Button>
                    </div>
                </header>

                <div className="overflow-hidden rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-0 border-b border-solid border-gray-200 px-4 pt-3 pb-2">
                        <Tabs variant="pill" label={ __( 'Module status', 'wp-user-frontend' ) } value={ filter } onChange={ setFilter } tabs={ tabs } />
                        <div className="relative w-60">
                            <Search size={ 16 } aria-hidden="true" className="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-gray-400" />
                            <TextInput
                                type="search"
                                className="pl-9!"
                                value={ search }
                                onChange={ ( value ) => setSearch( value ) }
                                placeholder={ __( 'Search', 'wp-user-frontend' ) }
                                aria-label={ __( 'Search modules', 'wp-user-frontend' ) }
                            />
                        </div>
                    </div>

                    { ! visible.length && ! ( 'all' === filter && proVisible.length ) && (
                        <EmptyState size="card" title={ __( 'No modules match.', 'wp-user-frontend' ) } description={ __( 'Try a different search or tab.', 'wp-user-frontend' ) } />
                    ) }

                    { visible.length > 0 && (
                        <ul className="m-0 grid list-none grid-cols-1 gap-4 p-4 md:grid-cols-2 xl:grid-cols-3 min-[1441px]:grid-cols-4">
                            { visible.map( ( item ) => (
                                <ModuleCard key={ item.id } item={ item } locked={ 'pro' === filter } busy={ busyId === item.id } onToggle={ onToggle } onLocked={ onLocked } />
                            ) ) }
                        </ul>
                    ) }

                    { /* The old page listed the Pro modules under the free ones: so does "My Modules". */ }
                    { 'all' === filter && proVisible.length > 0 && (
                        <section className="border-0 border-t border-solid border-gray-200">
                            <h3 className="m-0 flex items-center gap-2 px-4 pt-4 text-sm font-semibold text-gray-900">
                                { __( 'Pro Modules', 'wp-user-frontend' ) }
                                <span className="text-xs font-normal text-gray-500">{ __( '(Upgrade to Pro to unlock)', 'wp-user-frontend' ) }</span>
                            </h3>
                            <ul className="m-0 grid list-none grid-cols-1 gap-4 p-4 md:grid-cols-2 xl:grid-cols-3 min-[1441px]:grid-cols-4">
                                { proVisible.map( ( item ) => (
                                    <ModuleCard key={ item.id } item={ item } locked busy={ false } onToggle={ onToggle } onLocked={ onLocked } />
                                ) ) }
                            </ul>
                        </section>
                    ) }
                </div>
            </div>
            <PageFooter />
        </PageShell>
    );
}

registerScreen( 'modules-preview', [ 'wpuf-modules-preview' ], ( element ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <ModulesPreview />
        </WpufProviders>
    );

    return () => root.unmount();
} );
