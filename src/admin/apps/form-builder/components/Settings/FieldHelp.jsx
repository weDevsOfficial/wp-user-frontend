/**
 * What a form setting depends on elsewhere, under the field: links to the
 * picked page (and a warning when it was trashed or unpublished), the Account
 * page post types, the subscription packs a "Mandatory Subscription" form
 * needs, and the payment setup checks (`wpuf/v1/admin/setup/subscriptions`).
 *
 * @since WPUF_SINCE
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { request, restPath } from '@wpuf/api';
import { Button, Notice } from '@wpuf/components';

import { STORE_NAME } from '../../store';
import { builderData } from '../../utils/boot';

// Form settings that pick a page.
const PAGE_FIELDS = [ 'page_id', 'edit_page_id', 'ppp_payment_success_page', 'reg_page_id', 'profile_page_id' ];

const LINK = 'text-primary no-underline hover:underline';

const adminUrl = () => builderData().admin_url || ( window.ajaxurl || '' ).replace( 'admin-ajax.php', '' );

const appUrl = ( route ) => `${ adminUrl() }admin.php?page=wp-user-frontend#${ route }`;

// One request per builder session for the payment checks.
let checksPromise = null;

const loadChecks = () => {
    if ( ! checksPromise ) {
        checksPromise = request( restPath( 'wpuf/v1', 'admin/setup/subscriptions' ) )
            .then( ( response ) => ( Array.isArray( response?.checks ) ? response.checks : [] ) )
            .catch( () => [] );
    }

    return checksPromise;
};

const Links = ( { links } ) => (
    <div className="mt-2 flex flex-wrap gap-x-4 text-xs">
        { links.filter( Boolean ).map( ( link ) => (
            <a key={ link.href } href={ link.href } target="_blank" rel="noopener noreferrer" className={ LINK }>
                { link.label } ↗
            </a>
        ) ) }
    </div>
);

const PageHelp = ( { field, value } ) => {
    const id = parseInt( value, 10 ) || 0;
    const options = field.options && 'object' === typeof field.options ? field.options : {};
    const missing = id > 0 && ! Object.prototype.hasOwnProperty.call( options, String( id ) );

    return (
        <>
            <Links
                links={ [
                    id > 0 && ! missing && { label: __( 'Edit page', 'wp-user-frontend' ), href: `${ adminUrl() }post.php?post=${ id }&action=edit` },
                    { label: __( 'Add new page', 'wp-user-frontend' ), href: `${ adminUrl() }post-new.php?post_type=page` },
                ] }
            />
            { missing && (
                <Notice tone="warning" className="mt-2">
                    { __( 'The selected page was trashed, unpublished or deleted, so users go nowhere. Pick another page or restore it.', 'wp-user-frontend' ) }{ ' ' }
                    <a href={ `${ adminUrl() }edit.php?post_status=trash&post_type=page` } target="_blank" rel="noopener noreferrer" className={ LINK }>
                        { __( 'Trashed pages', 'wp-user-frontend' ) }
                    </a>
                </Notice>
            ) }
        </>
    );
};

const AccountHelp = ( { value } ) => {
    const types = builderData().account_post_types;

    if ( ! value || ! Array.isArray( types ) || types.includes( value ) ) {
        return null;
    }

    return (
        <Notice tone="info" className="mt-2">
            { /* translators: %s: post type slug */ }
            { sprintf( __( 'Users will not see their "%s" posts on the Account page, which lists other post types.', 'wp-user-frontend' ), value ) }{ ' ' }
            <a href={ appUrl( '/settings?tab=wpuf_my_account' ) } target="_blank" rel="noopener noreferrer" className={ LINK }>
                { __( 'Account page settings', 'wp-user-frontend' ) }
            </a>
        </Notice>
    );
};

const PaymentHelp = () => {
    const [ checks, setChecks ] = useState( [] );

    useEffect( () => {
        let live = true;

        loadChecks().then( ( list ) => live && setChecks( list.filter( ( check ) => ! check.ok && 'info' !== check.severity ) ) );

        return () => {
            live = false;
        };
    }, [] );

    if ( ! checks.length ) {
        return null;
    }

    return (
        <Notice
            tone="warning"
            className="mt-3"
            title={ __( 'Payments are not ready yet', 'wp-user-frontend' ) }
            action={
                <Button size="sm" variant="secondary" href={ appUrl( '/settings?tab=wpuf_payment' ) } target="_blank" rel="noopener noreferrer">
                    { __( 'Open payment settings', 'wp-user-frontend' ) }
                </Button>
            }
        >
            <ul className="m-0 list-disc pl-4">
                { checks.map( ( check ) => <li key={ check.id } className="m-0">{ check.title }</li> ) }
            </ul>
        </Notice>
    );
};

const PackHelp = () => {
    const packs = builderData().subscriptions;

    if ( Array.isArray( packs ) ? packs.length : packs && Object.keys( packs ).length ) {
        return null;
    }

    return (
        <Notice
            tone="warning"
            className="mt-2"
            title={ __( 'No subscription packs yet', 'wp-user-frontend' ) }
            action={
                <Button size="sm" variant="secondary" href={ appUrl( '/subscriptions?action=new' ) } target="_blank" rel="noopener noreferrer">
                    { __( 'Create a pack', 'wp-user-frontend' ) }
                </Button>
            }
        >
            { __( 'Users must buy a pack before posting, but none is published.', 'wp-user-frontend' ) }
        </Notice>
    );
};

/**
 * @param {Object} props
 * @param {string} props.name  Setting name.
 * @param {Object} props.field Setting definition.
 * @param {*}      props.value Current value.
 */
export default function FieldHelp( { name, field, value } ) {
    const settings = useSelect( ( select ) => select( STORE_NAME ).getSettings() || {}, [] );
    const isPostForm = 'wpuf_forms' === builderData().form_type;

    if ( PAGE_FIELDS.includes( name ) && 'select' === field.type ) {
        return <PageHelp field={ field } value={ value } />;
    }

    if ( 'post_type' === name && isPostForm ) {
        return <AccountHelp value={ value } />;
    }

    if ( 'payment_options' === name && [ 'on', 'yes', true, 'true' ].includes( value ) ) {
        return <PaymentHelp />;
    }

    if ( 'choose_payment_option' === name && 'force_pack_purchase' === value && [ 'on', 'yes', true, 'true' ].includes( settings.payment_options ) ) {
        return <PackHelp />;
    }

    return null;
}
