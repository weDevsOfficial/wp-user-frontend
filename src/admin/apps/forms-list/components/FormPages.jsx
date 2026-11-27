/**
 * "Used on" cell of the forms lists: the posts and pages that show the form
 * (shortcode, block or Elementor widget; `wpuf/v1/admin/forms/pages`). One
 * page shows inline; several open a dialog with View / Edit for each; none
 * shows a muted "Not on a page" (the row menu's "Add to a new page" opens
 * WordPress's new page screen with the shortcode in it, newPageUrl()).
 *
 * @since WPUF_SINCE
 */
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __, _n, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { Badge, Button, Modal, SkeletonBar } from '@wpuf/components';
import { ExternalLink, FileText, Plus } from 'lucide-react';

import { store } from '../store';
import { adminScript } from '../boot';

const STATUS = {
    draft: __( 'Draft', 'wp-user-frontend' ),
    pending: __( 'Pending', 'wp-user-frontend' ),
    private: __( 'Private', 'wp-user-frontend' ),
    future: __( 'Scheduled', 'wp-user-frontend' ),
};

const LINK = 'inline-flex items-center gap-1 text-[13px] font-medium text-gray-900 no-underline hover:text-primary hover:underline focus-visible:text-primary';

/**
 * WordPress's new page screen with the form's shortcode in it (core
 * `post-new.php?post_type=page&post_title=&content=`): nothing is created
 * until the admin publishes.
 *
 * @param {Object} form      Form row (post_title).
 * @param {string} shortcode Shortcode.
 *
 * @return {string} URL.
 */
export const newPageUrl = ( form, shortcode ) => addQueryArgs( `${ adminScript().admin_url }post-new.php`, {
    post_type: 'page',
    post_title: form.post_title,
    // The new page screen escapes quotes in prefilled content (`&quot;`),
    // which breaks the attributes: send values without quotes (valid
    // shortcode syntax while a value has no spaces).
    content: shortcode.replace( /"([^"\s]*)"/g, '$1' ),
} );

/**
 * @param {Object} props
 * @param {Object} props.form      Form row (ID, post_title).
 * @param {string} props.shortcode Shortcode a new page gets.
 */
export default function FormPages( { form, shortcode } ) {
    const pages = useSelect( ( select ) => select( store ).getPages( form.ID ), [ form.ID ] );
    const [ open, setOpen ] = useState( false );

    if ( undefined === pages ) {
        return <SkeletonBar className="h-3.5 w-24" />;
    }

    if ( null === pages ) {
        return <span className="text-gray-400">&mdash;</span>;
    }

    if ( ! pages.length ) {
        return <span className="text-gray-400">{ __( 'Not on a page', 'wp-user-frontend' ) }</span>;
    }

    // A draft form on a page shows visitors an error instead of the form.
    const draftNote = 'publish' !== form.form_status ? (
        <span className="mt-1 block text-xs text-amber-700" title={ __( 'Visitors see "Please make sure you\'ve published your form." Publish the form from its menu.', 'wp-user-frontend' ) }>
            { __( 'Draft: not shown to visitors', 'wp-user-frontend' ) }
        </span>
    ) : null;

    if ( 1 === pages.length ) {
        const page = pages[ 0 ];

        return (
            <div>
                <div className="flex items-center gap-2">
                    <a
                        href={ page.view || page.edit }
                        target="_blank"
                        rel="noopener noreferrer"
                        className={ LINK }
                        /* translators: 1: post type label, 2: title */
                        title={ sprintf( __( '%1$s: %2$s', 'wp-user-frontend' ), page.type, page.title ) }
                    >
                        <span className="max-w-[180px] truncate">{ page.title }</span>
                        <ExternalLink size={ 12 } className="shrink-0 text-gray-400" aria-hidden="true" />
                    </a>
                    { STATUS[ page.status ] && <Badge tone="neutral">{ STATUS[ page.status ] }</Badge> }
                    { page.edit && page.view && (
                        <a href={ page.edit } target="_blank" rel="noopener noreferrer" className="text-xs text-gray-500 no-underline hover:text-primary hover:underline">
                            { __( 'Edit', 'wp-user-frontend' ) }
                        </a>
                    ) }
                </div>
                { draftNote }
            </div>
        );
    }

    return (
        <>
            <button
                type="button"
                onClick={ () => setOpen( true ) }
                className="inline-flex cursor-pointer items-center gap-1 border-0 bg-transparent p-0 text-[13px] font-medium text-gray-900 hover:text-primary hover:underline focus-visible:text-primary"
            >
                <FileText size={ 14 } className="text-gray-400" aria-hidden="true" />
                { /* translators: %d: number of pages */ }
                { sprintf( _n( '%d page', '%d pages', pages.length, 'wp-user-frontend' ), pages.length ) }
            </button>
            { draftNote }
            <Modal
                open={ open }
                onClose={ () => setOpen( false ) }
                /* translators: %s: form name */
                title={ sprintf( __( 'Pages showing %s', 'wp-user-frontend' ), form.post_title ) }
                className="sm:max-w-2xl"
            >
                { /* The forms table's look (FormsTable): uppercase gray header, 56px rows. */ }
                <div data-wpuf-ui="" className="overflow-hidden rounded-[10px] border border-solid border-gray-200">
                    <table className="w-full border-collapse text-left">
                        <thead className="border-0 border-b border-solid border-gray-200 bg-white">
                            <tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
                                <th scope="col" className="px-4 font-normal">{ __( 'Title', 'wp-user-frontend' ) }</th>
                                <th scope="col" className="px-2 font-normal">{ __( 'Type', 'wp-user-frontend' ) }</th>
                                <th scope="col" className="px-2 font-normal">{ __( 'Status', 'wp-user-frontend' ) }</th>
                                <th scope="col" className="px-4 font-normal">
                                    <span className="sr-only">{ __( 'Actions', 'wp-user-frontend' ) }</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            { pages.map( ( page ) => (
                                <tr key={ page.id } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
                                    <td className="max-w-[220px] px-4 py-2.5 align-middle text-[13px] font-medium text-gray-900">
                                        <span className="block truncate" title={ page.title }>{ page.title }</span>
                                    </td>
                                    <td className="whitespace-nowrap px-2 py-2.5 align-middle text-[13px] text-gray-500">{ page.type }</td>
                                    <td className="whitespace-nowrap px-2 py-2.5 align-middle">
                                        <Badge tone={ 'publish' === page.status ? 'primary' : 'neutral' }>
                                            { STATUS[ page.status ] || __( 'Published', 'wp-user-frontend' ) }
                                        </Badge>
                                    </td>
                                    <td className="whitespace-nowrap px-4 py-2.5 align-middle">
                                        <div className="flex justify-end gap-2">
                                            { page.view && (
                                                <Button size="xs" variant="secondary" href={ page.view } target="_blank" rel="noopener noreferrer">
                                                    { __( 'View', 'wp-user-frontend' ) }
                                                    <ExternalLink size={ 12 } aria-hidden="true" />
                                                </Button>
                                            ) }
                                            { page.edit && (
                                                <Button size="xs" variant="secondary" href={ page.edit } target="_blank" rel="noopener noreferrer">
                                                    { __( 'Edit', 'wp-user-frontend' ) }
                                                </Button>
                                            ) }
                                        </div>
                                    </td>
                                </tr>
                            ) ) }
                        </tbody>
                    </table>
                </div>
                <div className="mt-4 flex justify-end">
                    <Button size="sm" href={ newPageUrl( form, shortcode ) } target="_blank" rel="noopener noreferrer">
                        <Plus size={ 14 } aria-hidden="true" />
                        { __( 'Add to a new page', 'wp-user-frontend' ) }
                    </Button>
                </div>
            </Modal>
        </>
    );
}
