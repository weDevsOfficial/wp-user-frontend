/**
 * Tools tab, in three sections: Setup (onboarding, page installation),
 * Navigation (logout link) and Danger zone (reset settings, delete content,
 * delete transactions: one row each, every one behind a confirm). Same
 * actions as the classic page, on `wpuf/v1/admin/tools/*`.
 *
 * @since WPUF_SINCE
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Notice, dialogs, notify } from '@wpuf/components';
import { FilePlus2, Rocket } from 'lucide-react';

import { runTool } from './api';
import { store } from './store';
import LogoutMenuTool from './LogoutMenuTool';
import ToolCard from './ToolCard';

const DELETE = [
    {
        type: 'wpuf_forms',
        title: __( 'Delete Post Forms', 'wp-user-frontend' ),
        text: __( 'Every post form, with its drafts and trash.', 'wp-user-frontend' ),
        confirm: __( 'Delete all post forms?', 'wp-user-frontend' ),
    },
    {
        type: 'wpuf_profile',
        title: __( 'Delete Registration Forms', 'wp-user-frontend' ),
        text: __( 'Every registration / profile form, with its drafts and trash.', 'wp-user-frontend' ),
        confirm: __( 'Delete all registration forms?', 'wp-user-frontend' ),
    },
    {
        type: 'wpuf_subscription',
        title: __( 'Delete Subscriptions', 'wp-user-frontend' ),
        text: __( 'Every subscription pack.', 'wp-user-frontend' ),
        confirm: __( 'Delete all subscription packs?', 'wp-user-frontend' ),
    },
    {
        type: 'wpuf_coupon',
        title: __( 'Delete Coupons', 'wp-user-frontend' ),
        text: __( 'Every coupon.', 'wp-user-frontend' ),
        confirm: __( 'Delete all coupons?', 'wp-user-frontend' ),
    },
];

/**
 * @param {Object} props
 * @param {*}      props.title       Title.
 * @param {*}      [props.description] Text.
 * @param {*}      props.children    Content.
 */
function Section( { title, description, children } ) {
    return (
        <section>
            <h2 className="m-0 text-sm font-semibold uppercase tracking-wide text-gray-500">{ title }</h2>
            { description && <p className="m-0 mt-1 text-sm text-gray-500">{ description }</p> }
            <div className="mt-3">{ children }</div>
        </section>
    );
}

/**
 * @param {Object} props
 * @param {*}      props.title    Title.
 * @param {*}      props.text     Text.
 * @param {*}      props.children Action.
 */
function DangerRow( { title, text, children } ) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-4 border-0 border-t border-solid border-red-100 px-5 py-4 first:border-t-0">
            <div className="min-w-0">
                <h3 className="m-0 text-sm font-semibold text-gray-900">{ title }</h3>
                <p className="m-0 mt-0.5 text-sm text-gray-500">{ text }</p>
            </div>
            { children }
        </div>
    );
}

/**
 * @param {Object} props
 * @param {Object} props.tools window.wpufTools.
 */
export default function ToolsTab( { tools } ) {
    const [ busy, setBusy ] = useState( '' );
    // Page-load data first, then the current state: onboarding's Exit setup
    // comes here without a reload, so the globals can be a run behind.
    const entry = useSelect( ( select ) => select( store ).getOnboarding(), [] ) || tools.onboarding;
    const { loadOnboarding } = useDispatch( store );

    useEffect( () => {
        if ( tools.canManageSite ) {
            loadOnboarding();
        }
    }, [ tools.canManageSite, loadOnboarding ] );

    const run = async ( key, route, data, confirm ) => {
        if ( confirm && ! ( await dialogs.confirm( confirm ) ) ) {
            return;
        }

        setBusy( key );

        try {
            const result = await runTool( route, data );

            notify( result?.message || __( 'Done.', 'wp-user-frontend' ) );
        } catch ( error ) {
            notify( error.message, 'error' );
        } finally {
            setBusy( '' );
        }
    };

    const danger = ( title, message, confirmText ) => ( { title, message, confirmText, tone: 'danger' } );

    if ( ! tools.canManageSite ) {
        return <p className="m-0 text-sm text-gray-500">{ __( 'Only site administrators can use these tools. Import, Export and Shortcodes are available to you.', 'wp-user-frontend' ) }</p>;
    }

    return (
        <div className="space-y-8">
            <Section title={ __( 'Setup', 'wp-user-frontend' ) }>
                <div className="grid gap-4 xl:grid-cols-2">
                    { entry && (
                        <ToolCard icon={ Rocket } title={ __( 'Onboarding', 'wp-user-frontend' ) } description={ __( 'Walk through post forms, registration, the user directory, payments and the settings a frontend site needs. Nothing is changed until you save a step.', 'wp-user-frontend' ) }>
                            { entry.warning && (
                                <Notice tone="warning" className="mb-4" title={ __( 'Heads up:', 'wp-user-frontend' ) }>
                                    { entry.warning }
                                </Notice>
                            ) }
                            <Button onClick={ () => window.location.assign( entry.url ) }>{ entry.label }</Button>
                        </ToolCard>
                    ) }

                    <ToolCard icon={ FilePlus2 } title={ __( 'Page Installation', 'wp-user-frontend' ) } description={ __( 'Clicking this button will create required pages for the plugin. Note: It\'ll not delete/replace existing pages.', 'wp-user-frontend' ) }>
                        <Button busy={ 'pages' === busy } onClick={ () => run( 'pages', 'install-pages' ) }>{ __( 'Install WPUF Pages', 'wp-user-frontend' ) }</Button>
                    </ToolCard>
                </div>
            </Section>

            <Section title={ __( 'Navigation', 'wp-user-frontend' ) }>
                <LogoutMenuTool tools={ tools } />
            </Section>

            <Section title={ __( 'Danger zone', 'wp-user-frontend' ) } description={ __( 'These tools remove data permanently. Export your forms first if you may need them again.', 'wp-user-frontend' ) }>
                <div className="overflow-hidden rounded-[10px] border border-solid border-red-200 bg-white">
                    <DangerRow title={ __( 'Reset Settings', 'wp-user-frontend' ) } text={ __( 'Caution: This tool will delete all the plugin settings of WP User Frontend Pro', 'wp-user-frontend' ) }>
                        <Button
                            variant="destructive"
                            busy={ 'reset' === busy }
                            onClick={ () => run( 'reset', 'reset-settings', null, danger( __( 'Reset settings?', 'wp-user-frontend' ), __( 'The General, Dashboard, Login / Registration and Payment settings go back to their defaults. This cannot be undone.', 'wp-user-frontend' ), __( 'Reset Settings', 'wp-user-frontend' ) ) ) }
                        >
                            { __( 'Reset Settings', 'wp-user-frontend' ) }
                        </Button>
                    </DangerRow>

                    { DELETE.filter( ( item ) => ( tools.deletable || [] ).includes( item.type ) ).map( ( item ) => (
                        <DangerRow key={ item.type } title={ item.title } text={ item.text }>
                            <Button
                                variant="destructive"
                                busy={ item.type === busy }
                                onClick={ () => run( item.type, 'delete-forms', { type: item.type }, danger( item.confirm, __( 'Everything of this kind is deleted permanently, including drafts and trash. This cannot be undone.', 'wp-user-frontend' ), __( 'Delete', 'wp-user-frontend' ) ) ) }
                            >
                                { __( 'Delete', 'wp-user-frontend' ) }
                            </Button>
                        </DangerRow>
                    ) ) }

                    <DangerRow title={ __( 'Delete Transactions', 'wp-user-frontend' ) } text={ __( 'This tool will delete all the transactions from the transaction table.', 'wp-user-frontend' ) }>
                        <Button
                            variant="destructive"
                            busy={ 'transactions' === busy }
                            onClick={ () => run( 'transactions', 'clear-transactions', null, danger( __( 'Delete all transactions?', 'wp-user-frontend' ), __( 'The transaction table is emptied. This cannot be undone.', 'wp-user-frontend' ), __( 'Delete Transactions', 'wp-user-frontend' ) ) ) }
                        >
                            { __( 'Delete', 'wp-user-frontend' ) }
                        </Button>
                    </DangerRow>
                </div>
            </Section>
        </div>
    );
}
