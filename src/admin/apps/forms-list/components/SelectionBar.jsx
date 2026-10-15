/**
 * Bulk actions for the selected rows (FlyHR list-card selection bar, in place
 * of develop's "Bulk actions" dropdown + Apply): "N selected", one button per
 * action of the tab (develop's actions: Move to trash; on Trash, Restore and
 * Delete Permanently) and Clear. Shown only while rows are selected.
 *
 * @since WPUF_SINCE
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { Button } from '@wpuf/components';

const SelectionBar = ( { count, currentTab, onAction, onClear } ) => {
    const actions = 'trash' === currentTab
        ? [
            { key: 'restore', label: __( 'Restore', 'wp-user-frontend' ) },
            { key: 'delete', label: __( 'Delete Permanently', 'wp-user-frontend' ), danger: true },
        ]
        : [ { key: 'trash', label: __( 'Move to trash', 'wp-user-frontend' ), danger: true } ];

    return (
        <div className="flex flex-wrap items-center gap-3 border-0 border-b border-solid border-gray-200 bg-primary/5 px-4 py-2.5">
            <span className="text-sm font-medium text-gray-900" aria-live="polite">
                { sprintf(
                    /* translators: %d: number of selected forms */
                    _n( '%d selected', '%d selected', count, 'wp-user-frontend' ),
                    count
                ) }
            </span>
            <div className="flex items-center gap-2">
                { actions.map( ( action ) => (
                    <Button
                        key={ action.key }
                        size="sm"
                        variant={ action.danger ? 'danger-outline' : 'secondary' }
                        onClick={ () => onAction( action.key ) }
                    >
                        { action.label }
                    </Button>
                ) ) }
            </div>
            <button
                type="button"
                className="p-0 bg-transparent border-0 cursor-pointer text-sm text-gray-500 hover:text-gray-900"
                onClick={ onClear }
            >
                { __( 'Clear', 'wp-user-frontend' ) }
            </button>
        </div>
    );
};

export default SelectionBar;
