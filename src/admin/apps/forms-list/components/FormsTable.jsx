/**
 * FormsTable component — renders the forms data table with columns and rows.
 *
 * Columns are a filter contract (`wpuf.formsList.tableColumns`): each has a
 * `key`, `label`, `thClassName`, optional `renderHeader()` and
 * `render( form )` returning the row's `<td>`; Pro swaps columns on the
 * registration list. So the list stays a plain table (not DataTable),
 * in FlyHR's list-table structure (owner, 4.2), inside the list card:
 * uppercase 12px gray header row (40px), 72px rows with a hover tint, the
 * selection checkbox in its own first column (drawn by the table, outside
 * the filterable columns) and a vertical-dots actions menu.
 *
 * @since WPUF_SINCE
 */
import { useMemo } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { ActionMenu, Badge, Checkbox, SortHeader } from '@wpuf/components';

import { STATUS_TONES } from '../utils/constants';
import ShortcodeCopy from './ShortcodeCopy';
import { openRoute } from '../../../app/client';
import { adminScript } from '../boot';

const openSubmissions = ( form ) => openRoute(
    `/post-forms/${ form.ID }/submissions`,
    `${ adminScript().admin_url }admin.php?page=wp-user-frontend#/post-forms/${ form.ID }/submissions`
);

// FlyHR list-table cells: header text comes from the row (uppercase 12px gray).
const TH = 'px-2 font-normal';
const TD = 'px-2 py-2.5 align-middle text-[13px]';

const statusLabel = ( status ) => {
    const labels = {
        publish: __( 'Published', 'wp-user-frontend' ),
        pending: __( 'Pending Review', 'wp-user-frontend' ),
        private: __( 'Private', 'wp-user-frontend' ),
        draft: __( 'Draft', 'wp-user-frontend' ),
    };

    return labels[ status ] || status;
};

const FormsTable = ( {
    forms,
    selectedForms,
    selectAllChecked,
    onSelectAll,
    onSelectForm,
    onAction,
    editUrl,
    postType,
    formType,
    getShortcode,
    copiedKey,
    onCopyShortcode,
    menuItems,
    sort,
    onSort,
} ) => {
    const indeterminate = selectedForms.length > 0 && selectedForms.length < forms.length;

    const defaultColumns = useMemo( () => [
        {
            key: 'form_name',
            sortable: 'title',
            label: __( 'Form Name', 'wp-user-frontend' ),
            thClassName: TH,
            render: ( form ) => (
                <td key="form_name" className={ TD + ' text-gray-900' }>
                    <a
                        href={ editUrl( form.ID ) }
                        className="font-medium text-gray-900 no-underline hover:text-primary hover:underline focus:text-primary"
                    >
                        { form.post_title }
                    </a>
                    { form.form_status === 'draft' && (
                        <span className="text-gray-400">
                            { ' ' }&mdash; { __( 'Draft', 'wp-user-frontend' ) }
                        </span>
                    ) }
                </td>
            ),
        },
        {
            key: 'post_type',
            label: __( 'Post Type', 'wp-user-frontend' ),
            thClassName: TH,
            render: ( form ) => (
                <td key="post_type" className={ TD + ' whitespace-nowrap text-gray-500' }>
                    { form.settings_post_type }
                </td>
            ),
        },
        {
            key: 'post_status',
            label: __( 'Post Status', 'wp-user-frontend' ),
            thClassName: TH,
            render: ( form ) => (
                <td key="post_status" className={ TD + ' whitespace-nowrap' }>
                    <Badge tone={ STATUS_TONES[ form.post_status ] || 'neutral' }>
                        { statusLabel( form.post_status ) }
                    </Badge>
                </td>
            ),
        },
        {
            key: 'submissions',
            label: __( 'Submissions', 'wp-user-frontend' ),
            thClassName: TH,
            render: ( form ) => {
                const total = Number( form.post_count ) || 0;
                const pending = Number( form.pending_count ) || 0;

                return (
                    <td key="submissions" className={ TD + ' whitespace-nowrap text-gray-500' }>
                        { total ? (
                            <span className="inline-flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={ () => openSubmissions( form ) }
                                    className="cursor-pointer border-0 bg-transparent p-0 text-[13px] font-medium text-gray-900 hover:text-primary hover:underline focus-visible:text-primary"
                                    /* translators: %s: form name */
                                    title={ sprintf( __( 'View posts submitted through %s', 'wp-user-frontend' ), form.post_title ) }
                                >
                                    { total }
                                </button>
                                { pending ? (
                                    <button
                                        type="button"
                                        onClick={ () => openSubmissions( form ) }
                                        className="cursor-pointer border-0 bg-transparent p-0"
                                    >
                                        <Badge tone="warning" className="px-2">
                                            { /* translators: %d: number of posts waiting for review */ }
                                            { sprintf( __( '%d pending', 'wp-user-frontend' ), pending ) }
                                        </Badge>
                                    </button>
                                ) : null }
                            </span>
                        ) : (
                            <span>0</span>
                        ) }
                    </td>
                );
            },
        },
        {
            key: 'shortcode',
            label: __( 'Shortcode', 'wp-user-frontend' ),
            thClassName: TH,
            render: ( form ) => (
                <td key="shortcode" className={ TD + ' whitespace-nowrap font-medium text-gray-500' }>
                    { /* Pro swaps in the registration + edit profile pair on profile forms. */ }
                    { applyFilters(
                        'wpuf.formsList.shortcodeRender',
                        <ShortcodeCopy
                            shortcode={ getShortcode( form.ID ) }
                            copiedKey={ `shortcode-${ form.ID }` }
                            currentCopiedKey={ copiedKey }
                            onCopy={ onCopyShortcode }
                        />,
                        form,
                        formType,
                        ShortcodeCopy,
                        copiedKey,
                        onCopyShortcode
                    ) }
                </td>
            ),
        },
        {
            key: 'guest_post',
            label: __( 'Guest Post', 'wp-user-frontend' ),
            thClassName: TH,
            render: ( form ) => (
                <td key="guest_post" className={ TD + ' whitespace-nowrap text-gray-500' }>
                    { form.settings_guest_post ? (
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" className="size-4 w-6" role="img" aria-label={ __( 'Yes', 'wp-user-frontend' ) }>
                            <path fill="#059669" fillRule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clipRule="evenodd" />
                        </svg>
                    ) : (
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" className="size-4 w-6" role="img" aria-label={ __( 'No', 'wp-user-frontend' ) }>
                            <path fill="#ef4444" d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z" />
                        </svg>
                    ) }
                </td>
            ),
        },
        {
            key: 'menu',
            label: '',
            thClassName: 'w-20 px-4',
            renderHeader: () => (
                <th key="menu" scope="col" className="w-20 px-4">
                    <span className="sr-only">{ __( 'Actions', 'wp-user-frontend' ) }</span>
                </th>
            ),
            render: ( form ) => (
                <td key="menu" className="px-4 align-middle">
                    <div className="flex justify-end">
                        <ActionMenu
                            vertical
                            /* translators: %s: form name */
                            label={ sprintf( __( 'Actions for %s', 'wp-user-frontend' ), form.post_title ) }
                            items={ menuItems.map( ( item ) => ( {
                                ...item,
                                onClick: () => onAction( item.key, form ),
                            } ) ) }
                        />
                    </div>
                </td>
            ),
        },
    ], [ onAction, editUrl, formType, getShortcode, copiedKey, onCopyShortcode, menuItems ] );

    // Submissions count posts made with a post form; registration forms make users.
    const baseColumns = 'wpuf_profile' === postType ? defaultColumns.filter( ( col ) => 'submissions' !== col.key ) : defaultColumns;
    const columns = applyFilters( 'wpuf.formsList.tableColumns', baseColumns, postType );

    return (
        <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
                <thead className="border-0 border-b border-solid border-gray-200 bg-white">
                    <tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
                        <th scope="col" className="w-10 px-4">
                            <Checkbox
                                value={ selectAllChecked }
                                onChange={ onSelectAll }
                                indeterminate={ indeterminate }
                                aria-label={ __( 'Select all', 'wp-user-frontend' ) }
                                className="align-middle"
                            />
                        </th>
                        { columns.map( ( col ) => {
                            if ( col.renderHeader ) {
                                return col.renderHeader();
                            }

                            // A column sorts when it names the server column (`sortable`).
                            return col.sortable && onSort ? (
                                <SortHeader key={ col.key } className={ col.thClassName || TH } column={ col.sortable } label={ col.label } sort={ sort || {} } onSort={ onSort } />
                            ) : (
                                <th key={ col.key } scope="col" className={ col.thClassName || TH }>
                                    { col.label }
                                </th>
                            );
                        } ) }
                    </tr>
                </thead>
                <tbody>
                    { forms.map( ( form ) => (
                        <tr key={ form.ID } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
                            <td className="w-10 px-4 align-middle">
                                <Checkbox
                                    value={ selectedForms.includes( form.ID ) }
                                    onChange={ () => onSelectForm( form.ID ) }
                                    /* translators: %s: form name */
                                    aria-label={ sprintf( __( 'Select %s', 'wp-user-frontend' ), form.post_title ) }
                                    className="align-middle"
                                />
                            </td>
                            { columns.map( ( col ) => col.render( form ) ) }
                        </tr>
                    ) ) }
                </tbody>
            </table>
        </div>
    );
};

export default FormsTable;
