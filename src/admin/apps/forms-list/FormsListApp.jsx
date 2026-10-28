/**
 * FormsListApp — root component for the Forms List page.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import { WpufProviders, PageFooter, PageHeader, PageShell } from '@wpuf/components';
import FormsList from './components/FormsList';
import SubmissionsPage from './components/SubmissionsPage';

/**
 * @param {Object} props
 * @param {number} [props.submissionsOf] Form ID: show that post form's Submissions page.
 */
const FormsListApp = ( { submissionsOf = 0 } ) => {
    const postType = window.wpuf_forms_list.post_type
        ? window.wpuf_forms_list.post_type
        : 'wpuf_forms';

    const formType = postType === 'wpuf_forms' ? 'post' : 'profile';
    const pageSlug = formType === 'post' ? 'wpuf-post-forms' : 'wpuf-profile-forms';
    const pageTitle = formType === 'post'
        ? __( 'Post Forms', 'wp-user-frontend' )
        : __( 'Profile Forms', 'wp-user-frontend' );

    // The docs links of the former "Learn more" footer bands (list views, D26).
    const help = formType === 'post'
        ? {
            url: 'https://wedevs.com/docs/wp-user-frontend-pro/posting-forms/?utm_source=wpuf-footer-help&utm_medium=text-link&utm_campaign=learn-more-frontend-posting',
            label: __( 'Learn more about Frontend Posting', 'wp-user-frontend' ),
        }
        : {
            url: 'https://wedevs.com/docs/wp-user-frontend-pro/registration-profile-forms/',
            label: __( 'Learn more about Registration Forms', 'wp-user-frontend' ),
        };

    // `host`: the screen keeps its own layout markup (title row, table), design.md D25.
    return (
        <WpufProviders host>
            <PageShell>
                { /* The Submissions page has the builder's header card instead. */ }
                { submissionsOf ? null : <PageHeader utm="wpuf-form-builder" helpUrl={ help.url } helpLabel={ help.label } /> }
                { submissionsOf ? (
                    <SubmissionsPage formId={ submissionsOf } />
                ) : (
                    <FormsList
                        postType={ postType }
                        formType={ formType }
                        pageSlug={ pageSlug }
                        pageTitle={ pageTitle }
                    />
                ) }
                { submissionsOf ? null : <PageFooter /> }
            </PageShell>
        </WpufProviders>
    );
};

export default FormsListApp;
