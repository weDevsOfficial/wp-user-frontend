/**
 * FormsListApp — root component for the Forms List page.
 *
 * @since WPUF_SINCE
 */
import Header from './components/Header';
import FormsList from './components/FormsList';

const FormsListApp = () => {
    const postType = window.wpuf_forms_list.post_type
        ? window.wpuf_forms_list.post_type
        : 'wpuf_forms';

    const formType = postType === 'wpuf_forms' ? 'post' : 'profile';
    const pageSlug = formType === 'post' ? 'wpuf-post-forms' : 'wpuf-profile-forms';
    const pageTitle = formType === 'post' ? 'Post Forms' : 'Profile Forms';

    return (
        <div>
            <Header utm="wpuf-form-builder" />
            <FormsList
                postType={ postType }
                formType={ formType }
                pageSlug={ pageSlug }
                pageTitle={ pageTitle }
            />
        </div>
    );
};

export default FormsListApp;
