/**
 * Builder header chip: where the form is shown (the forms list's "Used on"
 * data, `wpuf/v1/admin/forms/pages`). One page links to it, several open a
 * dialog in the forms table's look, none offers WordPress's new page screen
 * with the shortcode in it (core `post-new.php?post_type=page&content=`).
 * Also the notice for a registration form while WordPress registration is off.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { request, restPath } from '@wpuf/api';
import { Badge, Button, Modal, Notice } from '@wpuf/components';
import { ExternalLink, FileText, Plus } from 'lucide-react';

import { builderData } from '../../utils/boot';

const CHIP = 'ml-3 inline-flex shrink-0 items-center gap-1.5 rounded-md border border-solid border-gray-200 bg-gray-50 px-2.5 py-1.5 text-xs font-medium leading-none text-gray-600 no-underline hover:border-gray-300 hover:text-gray-900 cursor-pointer';

const adminUrl = () => builderData().admin_url || '';

// The new page screen escapes quotes in prefilled content: values without quotes.
const newPageUrl = ( title, shortcode ) => addQueryArgs( `${ adminUrl() }post-new.php`, {
    post_type: 'page',
    post_title: title,
    content: shortcode.replace( /"([^"\s]*)"/g, '$1' ),
} );

/**
 * @param {Object} props
 * @param {Object} props.post      Form post (ID, post_title).
 * @param {string} props.shortcode Shortcode a new page gets.
 */
export function FormUsage( { post, shortcode } ) {
    const [ pages, setPages ] = useState( undefined );
    const [ open, setOpen ] = useState( false );

    useEffect( () => {
        let live = true;

        if ( ! post || ! post.ID ) {
            return undefined;
        }

        request( restPath( 'wpuf/v1', '/admin/forms/pages' ), { query: { ids: [ post.ID ] } } )
            .then( ( data ) => live && setPages( ( data && data.pages && data.pages[ post.ID ] ) || [] ) )
            .catch( () => live && setPages( null ) );

        return () => {
            live = false;
        };
    }, [ post && post.ID ] ); // eslint-disable-line react-hooks/exhaustive-deps

    if ( ! Array.isArray( pages ) ) {
        return null;
    }

    if ( ! pages.length ) {
        return (
            <a
                className={ CHIP }
                href={ newPageUrl( post.post_title, shortcode ) }
                target="_blank"
                rel="noopener noreferrer"
                title={ __( 'This form is not on any page yet. Open a new page with its shortcode.', 'wp-user-frontend' ) }
            >
                <Plus size={ 12 } aria-hidden="true" />
                { __( 'Add to a page', 'wp-user-frontend' ) }
            </a>
        );
    }

    if ( 1 === pages.length ) {
        const page = pages[ 0 ];

        return (
            <a className={ CHIP } href={ page.view || page.edit } target="_blank" rel="noopener noreferrer" title={ page.title }>
                <FileText size={ 12 } aria-hidden="true" />
                <span className="max-w-[140px] truncate">{ page.title }</span>
                <ExternalLink size={ 12 } aria-hidden="true" />
            </a>
        );
    }

    return (
        <>
            <button type="button" className={ CHIP } onClick={ () => setOpen( true ) }>
                <FileText size={ 12 } aria-hidden="true" />
                { /* translators: %d: number of pages */ }
                { sprintf( _n( 'On %d page', 'On %d pages', pages.length, 'wp-user-frontend' ), pages.length ) }
            </button>
            <Modal
                open={ open }
                onClose={ () => setOpen( false ) }
                /* translators: %s: form name */
                title={ sprintf( __( 'Pages showing %s', 'wp-user-frontend' ), post.post_title ) }
                className="sm:max-w-2xl"
            >
                <div data-wpuf-ui="" className="overflow-hidden rounded-[10px] border border-solid border-gray-200">
                    <table className="w-full border-collapse text-left">
                        <thead className="border-0 border-b border-solid border-gray-200 bg-white">
                            <tr className="h-10 text-xs font-normal uppercase leading-[1.4] text-[#828282]">
                                <th scope="col" className="px-4 font-normal">{ __( 'Title', 'wp-user-frontend' ) }</th>
                                <th scope="col" className="px-2 font-normal">{ __( 'Type', 'wp-user-frontend' ) }</th>
                                <th scope="col" className="px-4 font-normal"><span className="sr-only">{ __( 'Actions', 'wp-user-frontend' ) }</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            { pages.map( ( page ) => (
                                <tr key={ page.id } className="h-14 border-0 border-b border-solid border-gray-200 bg-white last:border-b-0 hover:bg-gray-50">
                                    <td className="max-w-[260px] px-4 py-2.5 align-middle text-[13px] font-medium text-gray-900">
                                        <span className="block truncate" title={ page.title }>{ page.title }</span>
                                        { 'publish' !== page.status && <Badge tone="neutral">{ page.status }</Badge> }
                                    </td>
                                    <td className="whitespace-nowrap px-2 py-2.5 align-middle text-[13px] text-gray-500">{ page.type }</td>
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
                    <Button size="sm" href={ newPageUrl( post.post_title, shortcode ) } target="_blank" rel="noopener noreferrer">
                        <Plus size={ 14 } aria-hidden="true" />
                        { __( 'Add to a new page', 'wp-user-frontend' ) }
                    </Button>
                </div>
            </Modal>
        </>
    );
}

/**
 * Registration forms while WordPress registration is off: visitors see
 * "User registration disabled" instead of the form.
 */
export function RegistrationClosedNotice() {
    const data = builderData();

    if ( 'wpuf_profile' !== data.form_type || false !== data.registration_open ) {
        return null;
    }

    return (
        <Notice
            tone="warning"
            className="mx-4 mt-3"
            title={ __( 'New user registration is turned off in WordPress', 'wp-user-frontend' ) }
            action={ data.registration_url ? (
                <Button size="sm" variant="secondary" href={ data.registration_url } target="_blank" rel="noopener noreferrer">
                    { __( 'Allow sign-ups', 'wp-user-frontend' ) }
                </Button>
            ) : null }
        >
            { __( 'Visitors see "User registration disabled" instead of this form. Turn on "Anyone can register" in the WordPress settings.', 'wp-user-frontend' ) }
        </Notice>
    );
}
