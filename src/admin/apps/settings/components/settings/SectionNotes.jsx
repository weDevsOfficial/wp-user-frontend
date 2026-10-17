/**
 * Notes at the top of a settings section about something that lives
 * elsewhere and decides whether the section works, each with the way there:
 * WordPress registration (Login / Registration), the per-form SMS switch
 * (SMS), the account page social logins return to (Social Login).
 *
 * @since WPUF_SINCE
 */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Button, Notice } from '@wpuf/components';
import { openRoute } from '../../../../app/client';
import { STORE_NAME } from '../../stores/settings/constants';
import { settingsData } from '../../boot';
import { openSettingsSection } from './navigate';

/**
 * @param {Object} props
 * @param {string} props.sectionId Section id.
 */
export default function SectionNotes( { sectionId } ) {
    const accountPage = useSelect( ( select ) => select( STORE_NAME ).getValue( 'wpuf_my_account', 'account_page' ), [] );
    const boot = settingsData();
    const adminUrl = boot.admin_url || '';

    if ( 'wpuf_profile' === sectionId && boot.registration && ! boot.registration.open ) {
        return (
            <Notice
                tone="warning"
                className="mb-6"
                title={ __( 'New user registration is turned off in WordPress', 'wp-user-frontend' ) }
                action={
                    <Button size="sm" variant="secondary" href={ boot.registration.url } target="_blank" rel="noopener noreferrer">
                        { __( 'Allow sign-ups', 'wp-user-frontend' ) }
                    </Button>
                }
            >
                { __( 'Registration forms show visitors "User registration disabled" until "Anyone can register" is turned on in the WordPress settings.', 'wp-user-frontend' ) }
            </Notice>
        );
    }

    if ( 'wpuf_sms' === sectionId ) {
        return (
            <Notice
                tone="info"
                className="mb-6"
                title={ __( 'SMS is sent per form', 'wp-user-frontend' ) }
                action={
                    <Button size="sm" variant="secondary" onClick={ () => openRoute( '/post-forms', `${ adminUrl }admin.php?page=wp-user-frontend#/post-forms` ) }>
                        { __( 'Open post forms', 'wp-user-frontend' ) }
                    </Button>
                }
            >
                { __( 'These gateway keys are used once a post form turns on "Mobile Message" in its SMS settings.', 'wp-user-frontend' ) }
            </Notice>
        );
    }

    if ( 'wpuf_social_api' === sectionId && ! ( parseInt( accountPage, 10 ) > 0 ) ) {
        return (
            <Notice
                tone="warning"
                className="mb-6"
                title={ __( 'Set an Account Page first', 'wp-user-frontend' ) }
                action={
                    <Button size="sm" variant="secondary" onClick={ () => openSettingsSection( 'wpuf_my_account' ) }>
                        { __( 'Pick the Account Page', 'wp-user-frontend' ) }
                    </Button>
                }
            >
                { __( 'Social logins return to the Account Page, and its address is the redirect URI the provider apps need.', 'wp-user-frontend' ) }
            </Notice>
        );
    }

    return null;
}
