/**
 * Loading state of the payments table: the kit's TableSkeleton with the
 * payments columns (id, status, user, gateway, amount, date).
 *
 * @since WPUF_SINCE
 */
import { TableSkeleton as KitTableSkeleton } from '@wpuf/components';

const COLUMNS = [ 'h-3.5 w-16', 'h-5 w-20 rounded-full', 'h-3.5 flex-1', 'hidden h-3.5 w-24 md:block', 'hidden h-3.5 w-24 md:block' ];

export default function TableSkeleton( { rows = 6 } ) {
    return <KitTableSkeleton rows={ rows } columns={ COLUMNS } />;
}
