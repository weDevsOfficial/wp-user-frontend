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
    payment_success: 'page',
    bank_success: 'page',
    paypal_error_page: 'page',
    default_post_form: 'post_form',
    post_submission_form: 'post_form',
    edit_profile_form: 'profile_form',
    default_post_owner: 'user',
};

// Where to get the keys a setting asks for: shown under the first key field
// of each service (setting => [ url, label ]).
export const KEY_LINKS = {
    recaptcha_public: [ 'https://www.google.com/recaptcha/admin', __( 'Get reCAPTCHA keys', 'wp-user-frontend' ) ],
    turnstile_site_key: [ 'https://dash.cloudflare.com/?to=/:account/turnstile', __( 'Get Turnstile keys', 'wp-user-frontend' ) ],
    ipstack_key: [ 'https://ipstack.com/dashboard', __( 'Get your ipstack key', 'wp-user-frontend' ) ],
    gmap_api_key: [ 'https://console.cloud.google.com/google/maps-apis/credentials', __( 'Get a Google Maps API key', 'wp-user-frontend' ) ],
    paypal_client_id: [ 'https://developer.paypal.com/dashboard/applications', __( 'Get PayPal API credentials', 'wp-user-frontend' ) ],
    paypal_api_username: [ 'https://developer.paypal.com/api/nvp-soap/apiCredentials/', __( 'How to get PayPal NVP credentials', 'wp-user-frontend' ) ],
    stripe_api_secret: [ 'https://dashboard.stripe.com/apikeys', __( 'Get Stripe API keys', 'wp-user-frontend' ) ],
    stripe_signing_secret: [ 'https://dashboard.stripe.com/webhooks', __( 'Open Stripe webhooks', 'wp-user-frontend' ) ],
    twilio_sid: [ 'https://console.twilio.com/', __( 'Get Twilio credentials', 'wp-user-frontend' ) ],
    nexmo_api_key: [ 'https://dashboard.nexmo.com/', __( 'Get Vonage API credentials', 'wp-user-frontend' ) ],
    clickatell_api_id: [ 'https://portal.clickatell.com/', __( 'Get Clickatell credentials', 'wp-user-frontend' ) ],
    smsglobal_api_key: [ 'https://mxt.smsglobal.com/integrations', __( 'Get SMSGlobal API keys', 'wp-user-frontend' ) ],
    fb_app_id: [ 'https://developers.facebook.com/apps/', __( 'Create a Facebook app', 'wp-user-frontend' ) ],
    instagram_app_id: [ 'https://developers.facebook.com/apps/', __( 'Create an Instagram app', 'wp-user-frontend' ) ],
    twitter_app_id: [ 'https://developer.x.com/en/portal/dashboard', __( 'Create an X app', 'wp-user-frontend' ) ],
    google_app_id: [ 'https://console.cloud.google.com/apis/credentials', __( 'Create Google OAuth credentials', 'wp-user-frontend' ) ],
    linkedin_app_id: [ 'https://www.linkedin.com/developers/apps', __( 'Create a LinkedIn app', 'wp-user-frontend' ) ],
    github_app_id: [ 'https://github.com/settings/developers', __( 'Create a GitHub OAuth app', 'wp-user-frontend' ) ],
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
    user: ( id ) => [
        id && { label: __( 'Edit user', 'wp-user-frontend' ), href: `${ adminUrl() }user-edit.php?user_id=${ id }` },
        { label: __( 'Add new user', 'wp-user-frontend' ), href: `${ adminUrl() }user-new.php` },
    ],
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
 * "Get your keys" under a setting that asks for a third-party key (KEY_LINKS).
 *
 * @param {Object} props
 * @param {string} props.name Setting name.
 */
export function KeyLink( { name } ) {
    const link = KEY_LINKS[ name ];

    if ( ! link ) {
        return null;
    }

    return (
        <div className="mt-2 text-xs">
            <a href={ link[ 0 ] } target="_blank" rel="noopener noreferrer" className="text-primary no-underline hover:underline">
                { link[ 1 ] } ↗
            </a>
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
