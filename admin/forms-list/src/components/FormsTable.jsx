/**
 * FormsTable component — renders the forms data table with columns and rows.
 *
 * @since WPUF_SINCE
 */
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';

import { CHECKBOX_CLASSES, STATUS_BADGE_CLASSES } from '../utils/constants';
import ShortcodeCopy from './ShortcodeCopy';
import ActionMenu from './ActionMenu';

const STATUS_LABELS = {
    publish: 'Published',
    pending: 'Pending Review',
    private: 'Private',
    draft: 'Draft',
};

const FormsTable = ( {
    forms,
    currentTab,
    selectedForms,
    selectAllChecked,
    onSelectAll,
    onSelectForm,
    onAction,
    postType,
    getShortcode,
    copiedKey,
    onCopyShortcode,
    menuItems,
} ) => {
    const indeterminate = selectedForms.length > 0 && selectedForms.length < forms.length;

    const defaultColumns = useMemo( () => [
        {
            key: 'form_name',
            label: __( 'Form Name', 'wp-user-frontend' ),
            thClassName: 'wpuf-py-3.5 wpuf-pl-4 wpuf-pr-3 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 sm:wpuf-pl-6',
            render: ( form ) => (
                <td key="form_name" className="wpuf-py-4 wpuf-pl-4 wpuf-pr-3 wpuf-text-sm wpuf-font-medium wpuf-text-gray-900 sm:wpuf-pl-6">
                    <input
                        type="checkbox"
                        value={ form.ID }
                        checked={ selectedForms.includes( form.ID ) }
                        onChange={ () => onSelectForm( form.ID ) }
                        className={ CHECKBOX_CLASSES }
                    />
                    <span
                        onClick={ () => onAction( 'edit', form.ID ) }
                        className="hover:wpuf-cursor-pointer"
                    >
                        { form.post_title }
                    </span>
                    { form.form_status === 'draft' && (
                        <span className="wpuf-text-gray-400">
                            { ' ' }&mdash; { __( 'Draft', 'wp-user-frontend' ) }
                        </span>
                    ) }
                </td>
            ),
            renderHeader: () => (
                <th key="form_name" scope="col" className="wpuf-py-3.5 wpuf-pl-4 wpuf-pr-3 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900 sm:wpuf-pl-6">
                    <input
                        type="checkbox"
                        checked={ selectAllChecked }
                        onChange={ onSelectAll }
                        ref={ ( el ) => {
                            if ( el ) {
                                el.indeterminate = indeterminate;
                            }
                        } }
                        className={ CHECKBOX_CLASSES }
                    />
                    { __( 'Form Name', 'wp-user-frontend' ) }
                </th>
            ),
        },
        {
            key: 'post_type',
            label: __( 'Post Type', 'wp-user-frontend' ),
            thClassName: 'wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900',
            render: ( form ) => (
                <td key="post_type" className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-text-gray-500">
                    { form.settings_post_type }
                </td>
            ),
        },
        {
            key: 'post_status',
            label: __( 'Post Status', 'wp-user-frontend' ),
            thClassName: 'wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900',
            render: ( form ) => (
                <td key="post_status" className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm">
                    <span
                        className={
                            'wpuf-inline-flex wpuf-items-center wpuf-py-[2px] wpuf-px-[12px] wpuf-rounded-[5px] wpuf-text-xs wpuf-font-medium wpuf-border ' +
                            ( STATUS_BADGE_CLASSES[ form.post_status ] || STATUS_BADGE_CLASSES.draft )
                        }
                    >
                        { STATUS_LABELS[ form.post_status ] || form.post_status }
                    </span>
                </td>
            ),
        },
        {
            key: 'shortcode',
            label: __( 'Shortcode', 'wp-user-frontend' ),
            thClassName: 'wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900',
            render: ( form ) => (
                <td key="shortcode" className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500">
                    <ShortcodeCopy
                        shortcode={ getShortcode( form.ID ) }
                        copiedKey={ `shortcode-${ form.ID }` }
                        currentCopiedKey={ copiedKey }
                        onCopy={ onCopyShortcode }
                    />
                </td>
            ),
        },
        {
            key: 'guest_post',
            label: __( 'Guest Post', 'wp-user-frontend' ),
            thClassName: 'wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900',
            render: ( form ) => (
                <td key="guest_post" className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500">
                    { form.settings_guest_post ? (
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" className="wpuf-size-4 wpuf-w-6">
                            <path fill="#059669" fillRule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clipRule="evenodd" />
                        </svg>
                    ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" className="wpuf-size-4 wpuf-w-6">
                            <path fill="#ef4444" d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z" />
                        </svg>
                    ) }
                </td>
            ),
        },
        {
            key: 'menu',
            label: '',
            thClassName: 'wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900',
            renderHeader: () => (
                <th key="menu" scope="col" className="wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900">
                    <span className="wpuf-sr-only">Menu</span>
                </th>
            ),
            render: ( form ) => (
                <td key="menu" className="wpuf-whitespace-nowrap wpuf-px-3 wpuf-py-4 wpuf-text-sm wpuf-font-medium wpuf-text-gray-500 wpuf-text-right">
                    <ActionMenu
                        items={ menuItems }
                        onAction={ ( action ) => onAction( action, form.ID ) }
                    />
                </td>
            ),
        },
    ], [ selectAllChecked, indeterminate, selectedForms, onSelectAll, onSelectForm, onAction, getShortcode, copiedKey, onCopyShortcode, menuItems ] );

    const columns = applyFilters( 'wpuf.formsList.tableColumns', defaultColumns, postType );

    return (
        <div className="wpuf-flow-root">
            <div className="wpuf--mx-4 wpuf--my-2 sm:wpuf--mx-6 lg:wpuf--mx-8">
                <div className="wpuf-inline-block wpuf-min-w-full wpuf-py-2 wpuf-align-middle sm:wpuf-px-6 lg:wpuf-px-8">
                    <div className="wpuf-shadow wpuf-border wpuf-border-gray-200 sm:wpuf-rounded-lg">
                        <table className="wpuf-min-w-full wpuf-divide-y wpuf-divide-gray-200">
                            <thead>
                                <tr>
                                    { columns.map( ( col ) =>
                                        col.renderHeader
                                            ? col.renderHeader()
                                            : (
                                                <th
                                                    key={ col.key }
                                                    scope="col"
                                                    className={ col.thClassName || 'wpuf-px-3 wpuf-py-3.5 wpuf-text-left wpuf-text-sm wpuf-font-semibold wpuf-text-gray-900' }
                                                >
                                                    { col.label }
                                                </th>
                                            )
                                    ) }
                                </tr>
                            </thead>
                            <tbody className="wpuf-divide-y wpuf-divide-gray-200">
                                { forms.map( ( form ) => (
                                    <tr key={ form.ID } className="wpuf-relative wpuf-group">
                                        { columns.map( ( col ) => col.render( form ) ) }
                                    </tr>
                                ) ) }
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default FormsTable;
