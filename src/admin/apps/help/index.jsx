/**
 * User Frontend > Help: admin app route `#/help` (Admin\Screens\Help), on the
 * shared components. Content from Admin\Help_Content via `window.wpufHelp`.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { WpufProviders } from '@wpuf/components';

import { registerScreen } from '../../app/client';
import HelpPage from './HelpPage';

registerScreen( 'help', [ 'wpuf-help-root' ], ( element, context ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <HelpPage context={ context } />
        </WpufProviders>
    );

    return () => root.unmount();
} );
