/**
 * User Frontend > Tools: admin app route `#/tools` (Admin\Screens\Tools), on
 * the shared components and `wpuf/v1/admin/tools/*`.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { WpufProviders } from '@wpuf/components';

import { registerScreen } from '../../app/client';
import ToolsPage from './ToolsPage';

registerScreen( 'tools', [ 'wpuf-tools-root' ], ( element, context ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <ToolsPage context={ context } />
        </WpufProviders>
    );

    return () => root.unmount();
} );
