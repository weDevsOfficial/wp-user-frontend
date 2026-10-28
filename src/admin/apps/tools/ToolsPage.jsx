/**
 * Tools page: title row and one card with the Tools / Import / Export /
 * Shortcodes tabs. The open tab is in the route query (`#/tools?tab=<id>`);
 * a `msg` there (from the classic handlers on the old URL) shows once.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Code, Download, Upload, Wrench } from 'lucide-react';
import { PageFooter, PageHeader, PageShell, Tabs, notify } from '@wpuf/components';

import ExportTab from './ExportTab';
import ImportTab from './ImportTab';
import ShortcodesTab from './ShortcodesTab';
import ToolsTab from './ToolsTab';

const data = () => window.wpufTools || {};

// Messages of the classic page's handlers (Admin_Tools::tool_page()).
const MESSAGES = {
    del_forms: [ __( 'All forms has been deleted', 'wp-user-frontend' ), 'success' ],
    settings_cleared: [ __( 'Settings has been cleared!', 'wp-user-frontend' ), 'success' ],
    del_trans: [ __( 'All transactions has been deleted!', 'wp-user-frontend' ), 'success' ],
    logout_menu_added: [ __( 'Logout link has been added to the menu successfully!', 'wp-user-frontend' ), 'success' ],
    logout_menu_error: [ __( 'Failed to add logout link to the menu.', 'wp-user-frontend' ), 'error' ],
    no_menu_selected: [ __( 'Please select a menu to add the logout link.', 'wp-user-frontend' ), 'error' ],
};

const TABS = [
    { id: 'tools', label: __( 'Tools', 'wp-user-frontend' ), icon: <Wrench size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> },
    { id: 'import', label: __( 'Import', 'wp-user-frontend' ), icon: <Upload size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> },
    { id: 'export', label: __( 'Export', 'wp-user-frontend' ), icon: <Download size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> },
    { id: 'shortcodes', label: __( 'Shortcodes', 'wp-user-frontend' ), icon: <Code size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> },
];

/**
 * @param {Object} props
 * @param {Object} [props.context] App route context.
 */
export default function ToolsPage( { context } ) {
    const tools = data();
    const query = context?.getQuery?.() || {};
    const [ tab, setTab ] = useState( TABS.some( ( item ) => item.id === query.tab ) ? query.tab : 'tools' );

    useEffect( () => {
        const message = MESSAGES[ query.msg ];

        if ( message ) {
            notify( message[ 0 ], message[ 1 ] );
            context?.setQuery?.( { tab } );
        }
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [] );

    const open = ( id ) => {
        setTab( id );
        context?.setQuery?.( { tab: id } );
    };

    return (
        <PageShell>
            <PageHeader utm="wpuf-tools" />

            <div className="wpuf-tools mt-6 pb-10">
                <h1 className="m-0 p-0 text-2xl font-bold leading-8 text-gray-900">{ __( 'Tools', 'wp-user-frontend' ) }</h1>
                <p className="m-0 mt-1 text-sm text-gray-500">{ __( 'Set up pages, move forms between sites, find shortcodes and clean up plugin data.', 'wp-user-frontend' ) }</p>

                { /* FlyHR page tab bar over the content card */ }
                <div className="mt-6">
                    <Tabs variant="pill" tabs={ TABS } value={ tab } onChange={ open } label={ __( 'Tools sections', 'wp-user-frontend' ) } />
                </div>
                <div className="mt-6 rounded-[10px] border border-solid border-gray-200 bg-white shadow-sm">
                    <div className="p-6">
                        { 'tools' === tab && <ToolsTab tools={ tools } /> }
                        { 'import' === tab && <ImportTab /> }
                        { 'export' === tab && <ExportTab /> }
                        { 'shortcodes' === tab && <ShortcodesTab isPro={ !! tools.isPro } upgradeUrl={ tools.upgradeUrl } /> }
                    </div>
                </div>
            </div>
            <PageFooter />
        </PageShell>
    );
}
