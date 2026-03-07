/**
 * BulkActions component — bulk action dropdown and apply button.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const BulkActions = ( {
    currentTab,
    selectedBulkAction,
    onBulkActionChange,
    onApply,
    disabled,
} ) => {
    const isDisabled = ! selectedBulkAction || disabled;

    return (
        <div className="wpuf-flex">
            <select
                value={ selectedBulkAction }
                onChange={ ( e ) => onBulkActionChange( e.target.value ) }
                className="wpuf-block wpuf-w-full wpuf-min-w-full !wpuf-py-[10px] !wpuf-px-[14px] wpuf-text-gray-700 wpuf-font-normal !wpuf-leading-none !wpuf-shadow-sm wpuf-border !wpuf-border-gray-300 !wpuf-rounded-[6px] focus:!wpuf-ring-transparent focus:checked:!wpuf-ring-transparent hover:checked:!wpuf-ring-transparent hover:wpuf-text-gray-700 !wpuf-text-base !leading-6"
            >
                <option value="">{ __( 'Bulk actions', 'wp-user-frontend' ) }</option>
                { currentTab !== 'trash' && (
                    <option value="trash">{ __( 'Move to trash', 'wp-user-frontend' ) }</option>
                ) }
                { currentTab === 'trash' && (
                    <option value="restore">{ __( 'Restore', 'wp-user-frontend' ) }</option>
                ) }
                { currentTab === 'trash' && (
                    <option value="delete">{ __( 'Delete Permanently', 'wp-user-frontend' ) }</option>
                ) }
            </select>
            <button
                onClick={ onApply }
                disabled={ isDisabled }
                className={
                    'wpuf-ml-4 wpuf-inline-flex wpuf-items-center wpuf-justify-center wpuf-rounded-md wpuf-border wpuf-border-transparent wpuf-bg-primary wpuf-px-3 wpuf-py-2 wpuf-text-sm wpuf-font-semibold wpuf-text-white hover:wpuf-bg-primaryHover focus:wpuf-bg-primaryHover focus:wpuf-text-white' +
                    ( isDisabled ? ' wpuf-opacity-50 wpuf-cursor-not-allowed' : '' )
                }
            >
                { __( 'Apply', 'wp-user-frontend' ) }
            </button>
        </div>
    );
};

export default BulkActions;
