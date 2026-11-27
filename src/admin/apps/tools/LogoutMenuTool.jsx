/**
 * Add Logout to Menu: the logout URL to copy, and on a classic theme a menu
 * picker that adds the link (wpuf_add_logout_to_menu()). A block theme gets
 * the Site Editor steps instead, as on the classic page.
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, Notice, Select, TextInput, notify } from '@wpuf/components';
import { Check, Copy, LogOut } from 'lucide-react';

import { copyText, runTool } from './api';
import ToolCard from './ToolCard';

/**
 * @param {Object} props
 * @param {Object} props.tools window.wpufTools.
 */
export default function LogoutMenuTool( { tools } ) {
    const menus = tools.menus || [];
    const [ menu, setMenu ] = useState( '' );
    const [ label, setLabel ] = useState( __( 'Logout', 'wp-user-frontend' ) );
    const [ copied, setCopied ] = useState( false );
    const [ busy, setBusy ] = useState( false );

    const copy = () => copyText( tools.logoutUrl ).then( () => {
        setCopied( true );
        setTimeout( () => setCopied( false ), 2000 );
    } );

    const add = async () => {
        if ( ! menu ) {
            notify( __( 'Please select a menu to add the logout link.', 'wp-user-frontend' ), 'error' );

            return;
        }

        setBusy( true );

        try {
            const result = await runTool( 'logout-menu', { menu_id: Number( menu ), label } );

            notify( result.message );
        } catch ( error ) {
            notify( error.message || __( 'Failed to add logout link to the menu.', 'wp-user-frontend' ), 'error' );
        } finally {
            setBusy( false );
        }
    };

    return (
        <ToolCard icon={ LogOut } title={ __( 'Add Logout to Menu', 'wp-user-frontend' ) } description={ __( 'Add a logout link to your navigation menu so users can easily log out from the frontend.', 'wp-user-frontend' ) }>
            <div className="grid gap-6 xl:grid-cols-2">
                <div>
                    <label htmlFor="wpuf-logout-url" className="mb-1 block text-xs font-medium text-gray-700">{ __( 'Logout URL (copy this):', 'wp-user-frontend' ) }</label>
                    <div className="flex gap-2">
                        <TextInput id="wpuf-logout-url" value={ tools.logoutUrl } readOnly onFocus={ ( event ) => event.target.select() } className="min-w-0 flex-1" />
                        <Button variant="secondary" onClick={ copy }>
                            { copied ? <Check size={ 16 } aria-hidden="true" /> : <Copy size={ 16 } aria-hidden="true" /> }
                            { copied ? __( 'Copied!', 'wp-user-frontend' ) : __( 'Copy', 'wp-user-frontend' ) }
                        </Button>
                    </div>
                    <p className="m-0 mt-2 text-xs text-gray-500">{ __( 'Note: The logout URL contains a security nonce that may expire. For dynamic logout URLs, consider using a shortcode or widget.', 'wp-user-frontend' ) }</p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <Button variant="secondary" onClick={ () => window.location.assign( tools.menusUrl ) }>{ __( 'Go to Menus', 'wp-user-frontend' ) }</Button>
                        { tools.isBlockTheme && (
                            <Button variant="secondary" onClick={ () => window.location.assign( tools.siteEditorUrl ) }>{ __( 'Go to Site Editor Navigation', 'wp-user-frontend' ) }</Button>
                        ) }
                    </div>
                </div>

                <div>
                    { tools.isBlockTheme && (
                        <Notice tone="warning" title={ __( 'Block Theme Detected (FSE)', 'wp-user-frontend' ) }>
                            <p className="m-0">{ __( 'Your theme uses the Full Site Editor. To add a logout link to your navigation:', 'wp-user-frontend' ) }</p>
                            <ol className="m-0 mt-2 ps-5" style={ { listStyleType: 'decimal' } }>
                                <li>{ __( 'Go to Appearance > Editor > Navigation', 'wp-user-frontend' ) }</li>
                                <li>{ __( 'Click the + button to add a new item', 'wp-user-frontend' ) }</li>
                                <li>{ __( 'Select "Custom Link"', 'wp-user-frontend' ) }</li>
                                <li>{ __( 'Use the URL and label below', 'wp-user-frontend' ) }</li>
                            </ol>
                        </Notice>
                    ) }

                    { ! tools.isBlockTheme && menus.length > 0 && (
                        <>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label htmlFor="wpuf-logout-menu" className="mb-1 block text-xs font-medium text-gray-700">{ __( 'Select Menu', 'wp-user-frontend' ) }</label>
                                    <Select
                                        id="wpuf-logout-menu"
                                        value={ menu }
                                        onChange={ setMenu }
                                        placeholder={ __( 'Select a menu', 'wp-user-frontend' ) }
                                        options={ menus.map( ( item ) => ( { value: String( item.id ), label: item.name } ) ) }
                                    />
                                </div>
                                <div>
                                    <label htmlFor="wpuf-logout-label" className="mb-1 block text-xs font-medium text-gray-700">{ __( 'Menu Label', 'wp-user-frontend' ) }</label>
                                    <TextInput id="wpuf-logout-label" value={ label } onChange={ setLabel } />
                                </div>
                            </div>
                            <Button className="mt-4" busy={ busy } onClick={ add }>{ __( 'Add Logout to Menu', 'wp-user-frontend' ) }</Button>
                        </>
                    ) }

                    { ! tools.isBlockTheme && ! menus.length && (
                        <p className="m-0 text-sm text-gray-500">{ __( 'No menus found. Create a menu first, then come back to add the logout link.', 'wp-user-frontend' ) }</p>
                    ) }
                </div>
            </div>

        </ToolCard>
    );
}
