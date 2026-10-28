/**
 * User Frontend > Transactions: admin app route `#/transactions`
 * (Admin\Screens\Transactions), on `wpuf/v1/admin/transactions`.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { WpufProviders } from '@wpuf/components';

import { registerScreen } from '../../app/client';
import TransactionsPage from './TransactionsPage';

registerScreen( 'transactions', [ 'wpuf-transactions-root' ], ( element, context ) => {
    const root = createRoot( element );

    root.render(
        <WpufProviders host>
            <TransactionsPage context={ context } />
        </WpufProviders>
    );

    return () => root.unmount();
} );
