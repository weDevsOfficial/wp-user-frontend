/**
 * Loading state of the forms table: the kit's TableSkeleton with the forms
 * table's columns (title, shortcode, date, status).
 *
 * @since WPUF_SINCE
 */
import { TableSkeleton as KitTableSkeleton } from '@wpuf/components';

const TableSkeleton = ( { rows = 6, checkbox = true } ) => <KitTableSkeleton rows={ rows } checkbox={ checkbox } />;

export default TableSkeleton;
