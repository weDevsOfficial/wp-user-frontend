/**
 * Quick links under a setting that points at something the user edits
 * elsewhere: the page or form a select picks ("Edit", "Add new") and the
 * place to get an API key. Keeps the user on the settings screen instead of
 * hunting for the page or form in the admin menu.
 */
import { __ } from '@wordpress/i18n';
import { inApp } from '../../../../../../app/client';
import { settingsData } from '../../../../boot';

// Setting name => what its value points at.
export const QUICK_LINKS = {
    edit_page_id: 'page',
    account_page: 'page',
    reg_override_page: 'page',
    login_page: 'page',
    redirect_after_login_page: 'page',
    subscription_page: 'page',
    payment_page: 'page',
    default_post_form: 'post_form',
    post_submission_form: 'post_form',
};

// Where each AI provider issues API keys.
const API_KEY_URLS = {
    openai: 'https://platform.openai.com/api-keys',
    anthropic: 'https://console.anthropic.com/settings/keys',
    google: 'https://aistudio.google.com/app/apikey',
};

/**
 * The admin URL from the settings boot, else from the admin-ajax global.
 *
 * @return {string} Admin URL with a trailing slash.
 */
export const adminUrl = () => settingsData().admin_url || ( window.ajaxurl || '' ).replace( 'admin-ajax.php', '' );

/**
 * A form's links: in the admin app its builder routes (the editor, the
 * builder's Settings tab on its first section, a new form), else the
 * classic builder pages.
 *
 * @param {string} type post | profile
 *
 * @return {Function} ( id ) => links.
 */
const formLinks = ( type ) => ( id ) => {
    const route = 'profile' === type ? 'registration-forms' : 'post-forms';
    const page = 'profile' === type ? 'wpuf-profile-forms' : 'wpuf-post-forms';
    const app = `${ adminUrl() }admin.php?page=wp-user-frontend#/${ route }`;

    if ( inApp() ) {
        return [
            id && { label: __( 'Edit form', 'wp-user-frontend' ), href: `${ app }/${ id }/edit` },
            id && { label: __( 'Form settings', 'wp-user-frontend' ), href: `${ app }/${ id }/edit?tab=settings&section=general` },
            { label: __( 'Add new form', 'wp-user-frontend' ), href: `${ app }/new` },
        ];
    }

    return [
        id && { label: __( 'Edit form', 'wp-user-frontend' ), href: `${ adminUrl() }admin.php?page=${ page }&action=edit&id=${ id }` },
        { label: __( 'Add new form', 'wp-user-frontend' ), href: `${ adminUrl() }admin.php?page=${ page }&action=add-new` },
    ];
};

const LINKS = {
    page: ( id ) => [
        id && { label: __( 'Edit page', 'wp-user-frontend' ), href: `${ adminUrl() }post.php?post=${ id }&action=edit` },
        { label: __( 'Add new page', 'wp-user-frontend' ), href: `${ adminUrl() }post-new.php?post_type=page` },
    ],
    post_form: formLinks( 'post' ),
    profile_form: formLinks( 'profile' ),
};

/**
 * @param {Object}  props
 * @param {string}  props.kind     page | post_form | profile_form
 * @param {string}  [props.value]  Selected id.
 * @param {boolean} [props.addNew] Show the "Add new" link (default true; a list of rows shows it once).
 */
export default function QuickLinks( { kind, value, addNew = true } ) {
    const build = LINKS[ kind ];

    if ( ! build || ! adminUrl() ) {
        return null;
    }

    const id = parseInt( value, 10 ) > 0 ? parseInt( value, 10 ) : 0;
    const links = build( id ).filter( Boolean ).filter( ( link, index, all ) => addNew || index < all.length - 1 );

    if ( ! links.length ) {
        return null;
    }

    return (
        <div className="mt-2 flex flex-wrap gap-x-4 text-xs">
            { links.map( ( link ) => (
                <a key={ link.href } href={ link.href } target="_blank" rel="noopener noreferrer" className="text-primary no-underline hover:underline">
                    { link.label } ↗
                </a>
            ) ) }
        </div>
    );
}

/**
 * "Get your API key" for an AI provider.
 *
 * @param {Object} props
 * @param {string} props.provider openai | anthropic | google
 */
export function ApiKeyLink( { provider } ) {
    const href = API_KEY_URLS[ provider ];

    if ( ! href ) {
        return null;
    }

    return (
        <div className="mt-2 text-xs">
            <a href={ href } target="_blank" rel="noopener noreferrer" className="text-primary no-underline hover:underline">
                { __( 'Get your API key', 'wp-user-frontend' ) } ↗
            </a>
        </div>
    );
}
