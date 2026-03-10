import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { PostFormSettingsPage } from '../pages/postFormSettings';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, Urls } from '../utils/testData';
import { SettingsSetupPage } from '../pages/settingsSetup';
import { BasicLogoutPage } from '../pages/basicLogout';
import { configureSpecFailFast } from '../utils/specFailFast';

let browser: Browser;
let context: BrowserContext;
let page: Page;

test.beforeAll(async () => {
    // Launch browser
    browser = await chromium.launch();

    // Create a single context
    context = await browser.newContext();

    // Create a single page
    page = await context.newPage();
});

test.describe('Post Form Settings Tests', () => {

    configureSpecFailFast();
    
    /**----------------------------------POST FORM SETTINGS----------------------------------**
     *
     * @TestScenario : [Post Form Settings]
     * @Test_PFS0001 : Admin is changing post type
     * @Test_PFS0002 : Admin is validating post type
     * @Test_PFS0003 : Admin is validating post type from FE
     * @Test_PFS0004 : Admin is setting the default category
     * @Test_PFS0005 : Admin is validating default category from FE
     * @Test_PFS0006 : Admin is setting successful post redirection to same page
     * @Test_PFS0007 : Admin is checking post redirection to same page
     * @Test_PFS0008 : Admin is setting successful post redirection to newly created post
     * @Test_PFS0009 : Admin is checking post redirection to newly created post
     * @Test_PFS0010 : Admin is setting successful post redirection to another page
     * @Test_PFS0011 : Admin is checking post redirection to another page
     * @Test_PFS0012 : Admin is setting successful post redirection to a url
     * @Test_PFS0013 : Admin is checking post redirection to a url
     * @Test_PFS0014 : Admin is setting post submission status to draft
     * @Test_PFS0015 : Admin is validating post submission status to draft - list
     * @Test_PFS0016 : Admin is checking post submission status to draft - FE
     * @Test_PFS0017 : Admin is setting post submission status to pending
     * @Test_PFS0018 : Admin is validating post submission status to draft - list
     * @Test_PFS0019 : Admin is checking post submission status to pending
     * @Test_PFS0020 : Admin is setting post submission status to private
     * @Test_PFS0021 : Admin is validating post submission status to draft - list
     * @Test_PFS0022 : Admin is checking post submission status to private
     * @Test_PFS0023 : Admin is setting post submission status to publish
     * @Test_PFS0024 : Admin is validating post submission status to draft - list
     * @Test_PFS0025 : Admin is checking post submission status to publish
     * @Test_PFS0026 : Admin is enabling post saving as draft
     * @Test_PFS0027 : Admin is saving post as draft
     * @Test_PFS0028 : Admin is changing submit button text
     * @Test_PFS0029 : Admin is enabling multi-step form
     * @Test_PFS0030 : Admin is validating multi-step progressbar
     * @Test_PFS0031 : Admin is validating multi-step by step
     * @Test_PFS0032 : Admin is disabling multi-step form
     * @Test_PFS0033 : Admin is setting post update status to draft
     * @Test_PFS0034 : Admin is validating post update status
     * @Test_PFS0035 : Admin is setting post update status to pending review
     * @Test_PFS0036 : Admin is validating post update status
     * @Test_PFS0037 : Admin is setting post update status to private
     * @Test_PFS0038 : Admin is validating post update status to private
     * @Test_PFS0039 : Admin is setting post update status to no change
     * @Test_PFS0040 : Admin is validating post update status to no change
     * @Test_PFS0041 : Admin is setting post update status to published
     * @Test_PFS0042 : Admin is validating post update status to published
     * @Test_PFS0043 : Admin is setting successful redirection to updated post
     * @Test_PFS0044 : Admin is validating successful redirection to updated post
     * @Test_PFS0045 : Admin is setting successful redirection to same page
     * @Test_PFS0046 : Admin is validating successful redirection to same page
     * @Test_PFS0047 : Admin is setting successful redirection to a page
     * @Test_PFS0048 : Admin is validating successful redirection to a page
     * @Test_PFS0049 : Admin is setting successful redirection to custom URL
     * @Test_PFS0050 : Admin is validating successful redirection to custom URL
     * @Test_PFS0051 : Admin is setting post update message
     * @Test_PFS0052 : Admin is validating post update message in form
     * @Test_PFS0053 : Admin is setting lock user editing after time
     * @Test_PFS0054 : Admin is setting update post button text
     * @Test_PFS0055 : Admin is enabling form title showing
     * @Test_PFS0056 : Admin is showing form description
     * @Test_PFS0057 : Admin is enabling pay per post
     * @Test_PFS0058 : Admin is creating post with payment
     * @Test_PFS0059 : Admin is accepting payment for post
     * @Test_PFS0060 : Admin is validating paid post is live
     * @Test_PFS0061 : Admin is disabling pay per post
     * @Test_PFS0062 : Admin is enabling new post notification
     * @Test_PFS0063 : Admin is validating new post notification settings enabled
     * @Test_PFS0064 : Admin is modifying notification email
     * @Test_PFS0065 : Admin is modifying notification subject
     * @Test_PFS0066 : Admin is modifying notification body with template tags
     * @Test_PFS0067 : Admin is clicking and validating template tags for notification
     * @Test_PFS0068 : Admin is setting multiple notification emails
     * @Test_PFS0069 : Admin is submitting post and validating notification from FE
     * @Test_PFS0070 : Admin is disabling new post notification
     * @Test_PFS0071 : Admin is enabling updated post notification
     * @Test_PFS0072 : Admin is validating updated post notification settings enabled
     * @Test_PFS0073 : Admin is modifying updated post notification email
     * @Test_PFS0074 : Admin is modifying updated post notification subject
     * @Test_PFS0075 : Admin is modifying updated post notification body with template tags
     * @Test_PFS0076 : Admin is clicking and validating template tags for updated post notification
     * @Test_PFS0077 : Admin is setting multiple updated post notification emails
     * @Test_PFS0078 : Admin is submitting post and validating updated post notification from FE
     * @Test_PFS0079 : Admin is disabling updated post notification
     * @Test_PFS0080 : Admin is enabling user comment
     * @Test_PFS0081 : User is validating user comment is enabled
     * @Test_PFS0082 : Admin is disabling user comment
     * @Test_PFS0083 : User is validating user comment is disabled
     * @Test_PFS0084 : Admin is limiting form entries
     * @Test_PFS0085 : Admin is validating limit form entries
     * @Test_PFS0086 : Admin is unlimiting form entries
     * @Test_PFS0087 : Admin is enabling conditional logic for any condition
     * @Test_PFS0088 : Admin is validating conditional logic for any condition
     * @Test_PFS0089 : Admin is enabling conditional logic for all condition
     * @Test_PFS0090 : Admin is validating conditional logic for all condition
     * @Test_PFS0091 : Admin is disabling conditional logic
     * @Test_PFS0092 : Admin is enabling post expiration
     * @Test_PFS0093 : Admin is setting post permission role based
     * @Test_PFS0094 : Admin is validating post permission restriction
     */

    let formName: string;
    let postTitle: string;
    let postContent: string;
    let postExcerpt: string;
    const category = 'Music';
    const emailAddress = faker.internet.email();
    const emailSubject = 'New post submitted';
    const emailUpdatedSubject = `Post updated`;
    const emailBody = `Hi Admin,
            A new post has been submitted to {sitename}.
            Details:
            Title: {post_title}
            Author: {author} ({author_email})
            Content: {post_content}
            Excerpt: {post_excerpt}
            Category: {category}
            Tags: {tags}
            Review URL: {editlink}
            Public URL: {permalink}
            Best regards,
            Team {sitename}`;

    const emailUpdatedBody = `Hi Admin,
            Post updated.
            Details:
            Title: {post_title}
            Author: {author} ({author_email})
            Content: {post_content}
            Excerpt: {post_excerpt}
            Category: {category}
            Tags: {tags}
            Review URL: {editlink}
            Public URL: {permalink}
            Best regards,
            Team {sitename}`;
    const multipleEmails = `${faker.internet.email()}, ${faker.internet.email()}, ${faker.internet.email()}`;

    test('PFS0001 : Admin is changing post type', { tag: ['@Lite'] }, async () => {
        formName = 'PF Settings';
        await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
        const postFormSettings = new PostFormSettingsPage(page);
        // Create a new post form
        await postFormSettings.createPostForm(formName);
        const settingsSetup = new SettingsSetupPage(page);
        await settingsSetup.changeSettingsSetDefaultPostForm(formName);
        // Change post type to 'page'
        await postFormSettings.changePostType('page', formName);
    });

    test('PFS0002 : Admin is validating post type', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        // Validate that the post type shows correctly in the list
        await postFormSettings.validatePostTypeInList('page', formName);
    });

    test('PFS0003 : Admin is validating post type from FE', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        // Submit a post and validate it appears as a page
        await postFormSettings.validatePostTypeFE(postTitle, postContent, postExcerpt);
    });

    test('PFS0004 : Admin is setting the default category', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        // Set default category
        await postFormSettings.setDefaultCategory(category, formName);
    });

    test('PFS0005 : Admin is validating default category from FE', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        // Submit a post and validate its category
        await postFormSettings.submitAndValidateCategory(postTitle, postContent, postExcerpt, category);
    });

    test('PFS0006 : Admin is setting successful post redirection to same page', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostRedirectionToSamePage(formName, 'same', 'Post published successfully');
    });

    test('PFS0007 : Admin is checking post redirection to same page', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateRedirectionToSamePage(postTitle, postContent, postExcerpt, 'Post published successfully');
    });

    test('PFS0008 : Admin is setting successful post redirection to newly created post', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostRedirectionToPost(formName, 'post');
    });

    test('PFS0009 : Admin is checking post redirection to newly created post', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateRedirectionToPost(postTitle, postContent, postExcerpt);
    });

    test('PFS0010 : Admin is setting successful post redirection to another page', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostRedirectionToPage(formName, 'page', 'Thank You');
    });

    test('PFS0011 : Admin is checking post redirection to another page', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateRedirectionToPage(postTitle, postContent, postExcerpt, 'Thank You');
    });

    test('PFS0012 : Admin is setting successful post redirection to a url', { tag: ['@Lite'] }, async () => {
        const redirectUrl = Urls.baseUrl + '/thank-you/';
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostRedirectionToUrl(formName, 'url', redirectUrl);
    });

    test('PFS0013 : Admin is checking post redirection to a url', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const expectedUrl = Urls.baseUrl + '/thank-you/';
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateRedirectionToUrl(postTitle, postContent, postExcerpt, expectedUrl);
        await postFormSettings.setPostRedirectionToPost(formName, 'post');
    });

    test('PFS0014 : Admin is setting post submission status to draft', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostSubmissionStatus(formName, 'draft');
    });

    test('PFS0015 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        // Validate that the post type shows correctly in the list
        await postFormSettings.validatePostSubmissionStatusInList('Draft', formName);
    });

    test('PFS0016 : Admin is checking post submission status to draft - FE', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Offline');
    });

    test('PFS0017 : Admin is setting post submission status to pending', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostSubmissionStatus(formName, 'pending');
    });

    test('PFS0018 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        // Validate that the post type shows correctly in the list
        await postFormSettings.validatePostSubmissionStatusInList('Pending Review', formName);
    });

    test('PFS0019 : Admin is checking post submission status to pending', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Awaiting Approval');
    });

    test('PFS0020 : Admin is setting post submission status to private', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostSubmissionStatus(formName, 'private');
    });

    test('PFS0021 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        // Validate that the post type shows correctly in the list
        await postFormSettings.validatePostSubmissionStatusInList('Private', formName);
    });

    test('PFS0022 : Admin is checking post submission status to private', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Private');
    });

    test('PFS0023 : Admin is setting post submission status to publish', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostSubmissionStatus(formName, 'publish');
    });

    test('PFS0024 : Admin is validating post submission status to draft - list', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        // Validate that the post type shows correctly in the list
        await postFormSettings.validatePostSubmissionStatusInList('Published', formName);
    });

    test('PFS0025 : Admin is checking post submission status to publish', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateSubmittedPostStatusFE(postTitle, postContent, postExcerpt, 'Live');
    });

    test('PFS0026 : Admin is enabling post saving as draft', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostSavingAsDraft(formName);
    });

    test('PFS0027 : Admin is saving post as draft', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.savingPostAsDraft(postTitle, postContent, postExcerpt, 'Offline');
    });

    test('PFS0028 : Admin is changing submit button text', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.changeSubmitButtonText(formName, 'Publish');
    });

    test('PFS0029 : Admin is enabling multi-step form', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enableMultiStep(formName);
    });

    test('PFS0030 : Admin is validating multi-step progressbar', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateMultiStepProgessbar(formName);
    });

    test('PFS0031 : Admin is validating multi-step by step', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateMultiStepByStep(formName);
    });

    test('PFS0032 : Admin is disabling multi-step form', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.disableMultiStep(formName);
    });

    test('PFS0033 : Admin is setting post update status to draft', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostUpdateStatus(formName, 'draft');
    });

    test('PFS0034 : Admin is validating post update status for draft', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Offline');
    });
    test('PFS0035 : Admin is setting post update status to pending review', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostUpdateStatus(formName, 'pending');
    });

    test('PFS0036 : Admin is validating post update status for pending review', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Awaiting Approval');
        await postFormSettings.pendingToLive(postTitle,'Live');
    });

    test('PFS0037 : Admin is setting post update status to private', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostUpdateStatus(formName, 'private');
    });

    test('PFS0038 : Admin is validating post update status for private', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Private');
    });

    test('PFS0039 : Admin is setting post update status to no change', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostUpdateStatus(formName, '_nochange');
    });

    test('PFS0040 : Admin is validating post update status for no change', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Private');
    });

    test('PFS0041 : Admin is setting post update status to published', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostUpdateStatus(formName, 'publish');
    });

    test('PFS0042 : Admin is validating post update status for published', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePostUpdateStatusInForm(postTitle, postContent, postExcerpt, 'Live');
    });

    test('PFS0043 : Admin is setting successful redirection to updated post', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setUpdatePostRedirectionToUpdatedPost(formName);
    });

    test('PFS0044 : Admin is validating successful redirection to updated post', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateUpdatePostRedirectionToPost(postTitle, postContent, postExcerpt);
    });

    test('PFS0045 : Admin is setting successful redirection to same page', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setUpdatePostRedirectionToSamePage(formName, 'Post updated successfully');
    });

    test('PFS0046 : Admin is validating successful redirection to same page', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateUpdatePostRedirectionToSamePage(postTitle, postContent, postExcerpt, 'Post updated successfully');
    });

    test('PFS0047 : Admin is setting successful redirection to a page', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setUpdatePostRedirectionToPage(formName, 'Thank You');
    });

    test('PFS0048 : Admin is validating successful redirection to a page', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateUpdatePostRedirectionToPage(postTitle, postContent, postExcerpt, 'Thank You');
    });

    test('PFS0049 : Admin is setting successful redirection to custom URL', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        const customUrl = Urls.baseUrl + '/thank-you/';
        await postFormSettings.setUpdatePostRedirectionToCustomUrl(formName, customUrl);
    });

    test('PFS0050 : Admin is validating successful redirection to custom URL', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const customUrl = Urls.baseUrl + '/thank-you/';
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateUpdatePostRedirectionToUrl(postTitle, postContent, postExcerpt, customUrl);
        await postFormSettings.setUpdatePostRedirectionToSamePage(formName, 'Post updated successfully');
    });

    test('PFS0051 : Admin is setting post update message', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        const customMessage = 'Post has been updated successfully!';
        await postFormSettings.setPostUpdateMessage(formName, customMessage);
    });

    test('PFS0052 : Admin is validating post update message in form', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        const customMessage = 'Post has been updated successfully!';
        await postFormSettings.validatePostUpdateMessageInForm(postTitle, postContent, postExcerpt, customMessage);
    });

    test('PFS0053 : Admin is setting lock user editing after time', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setLockUserEditingAfter(formName, '24');
    });

    test('PFS0054 : Admin is setting update post button text', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setUpdatePostButtonText(formName, 'Save Changes');
    });

    test('PFS0055 : Admin is enabling and validating form title showing', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enableFormTitleShowing(formName);
    });

    test('PFS0056 : Admin is setting and validating form description showing', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.showFormDescription(formName);
    });

    test('PFS0057 : Admin is enabling pay per post', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enablePayPerPost(formName, '2.00', 'Order Received');
    });

    test('PFS0058 : Admin is creating post with payment', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.createPostWithPayment(postTitle, postContent, postExcerpt, '2.00', 'Order Received');
    });

    test('PFS0059 : Admin is accepting payment for post', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.acceptPayment();
    });

    test('PFS0060 : Admin is validating paid post is live', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePayPerPost(postTitle);
    });

    test('PFS0061 : Admin is disabling pay per post', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.disablePayPerPost(formName);
    });

    test('PFS0062 : Admin is enabling new post notification', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enableNewPostNotification(formName);
    });

    test('PFS0063 : Admin is validating new post notification settings enabled', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateNotificationSettingsEnabled(formName);
    });

    test('PFS0064 : Admin is modifying notification email', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.modifyNotificationEmail(formName, emailAddress);
    });

    test('PFS0065 : Admin is modifying notification subject', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.modifyNotificationSubject(formName, emailSubject);
    });

    test('PFS0066 : Admin is modifying notification body with template tags', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.modifyNotificationBodyWithTemplateTags(formName, emailBody);
    });

    test('PFS0067 : Admin is clicking and validating template tags for notification', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        const templateTags = ['{post_title}', '{post_content}',];
        //, '{tags}', '{category}', '{author}', '{author_email}', '{author_bio}', '{sitename}', '{siteurl}', '{permalink}', '{editlink}'
        await postFormSettings.clickTemplateTagsForNotification(formName, templateTags);
    });

    test('PFS0068 : Admin is setting multiple notification emails', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setMultipleNotificationEmails(formName, multipleEmails);
    });

    test('PFS0069 : Admin is submitting post and validating notification from FE', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.submitPostAndValidateNotificationFE(postTitle, postContent, postExcerpt, emailSubject, multipleEmails);
    });

    test('PFS0070 : Admin is disabling new post notification', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.disableNewPostNotification(formName);
    });

    test('PFS0071 : Admin is enabling updated post notification', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enableUpdatedPostNotification(formName);
    });

    test('PFS0072 : Admin is validating Updated post notification settings enabled', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateUpdatedNotificationSettingsEnabled(formName);
    });

    test('PFS0073 : Admin is modifying Updated post notification email', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.modifyUpdatedNotificationEmail(formName, emailAddress);
    });

    test('PFS0074 : Admin is modifying Updated post notification subject', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.modifyUpdatedNotificationSubject(formName, emailUpdatedSubject);
    });

    test('PFS0075 : Admin is modifying Updated post notification body with template tags', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.modifyUpdatedNotificationBodyWithTemplateTags(formName, emailUpdatedBody);
    });

    test('PFS0076 : Admin is clicking and validating template tags for Updated post notification', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        const templateTags = ['{post_title}', '{post_content}'];
        //, '{tags}', '{category}', '{author}', '{author_email}', '{author_bio}', '{sitename}', '{siteurl}', '{permalink}', '{editlink}'
        await postFormSettings.clickTemplateTagsForUpdatedNotification(formName, templateTags);
    });

    test('PFS0077 : Admin is setting multiple Updated post notification emails', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setMultipleUpdatedNotificationEmails(formName, multipleEmails);
    });

    test('PFS0078 : Admin is submitting post and validating Updated post notification from FE', { tag: ['@Pro'] }, async () => {
        const previousPostTitle = postTitle;
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.submitPostAndValidateUpdatedNotificationFE(previousPostTitle, postTitle, postContent, postExcerpt, emailUpdatedSubject, multipleEmails);
    });

    test('PFS0079 : Admin is disabling updated post notification', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.disableUpdatedPostNotification(formName);
    });

    test('PFS0080 : Admin is enabling user comment', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.settingUserComment(formName, 'open', postTitle, postContent, postExcerpt);
        const BasicLogout = new BasicLogoutPage(page);
        await BasicLogout.logOut();
    });

    test('PFS0081 : User is validating user comment is enabled', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        const BasicLogin = new BasicLoginPage(page);

        //New User created Login
        await BasicLogin.basicLogin(Users.userEmail, Users.userPassword);
        await postFormSettings.validateUserCommentEnabled(postTitle);
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
    });

    test('PFS0082 : Admin is disabling user comment', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.settingUserComment(formName, 'closed', postTitle, postContent, postExcerpt);
        const BasicLogout = new BasicLogoutPage(page);
        await BasicLogout.logOut();
    });

    test('PFS0083 : User is validating user comment is disabled', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        const BasicLogin = new BasicLoginPage(page);

        //New User created Login
        await BasicLogin.basicLogin(Users.userEmail, Users.userPassword);
        await postFormSettings.validateUserCommentDisabled(postTitle);
        await BasicLogin.basicLogin(Users.adminUsername, Users.adminPassword);
    });

    test('PFS0084 : Admin is limiting form entries', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.limitFormEntries(formName);
    });

    test('PFS0085 : Admin is validating limit form entries', { tag: ['@Lite'] }, async () => {
        postTitle = faker.word.words(3);
        postContent = faker.lorem.paragraph();
        postExcerpt = postContent;
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateLimitFormEntries(postTitle, postContent, postExcerpt);
    });

    test('PFS0086 : Admin is unlimiting form entries', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.unlimitFormEntries(formName);
    });

    test('PFS0087 : Admin is enabling conditional logic for any condition', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enableConditionalLogicForAnyCondition(formName);
    });

    test('PFS0088 : Admin is validating conditional logic for any condition', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateConditionalLogicForAnyCondition();
    });

    test('PFS0089 : Admin is enabling conditional logic for all condition', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.conditionalLogicForAllConditions(formName);
    });

    test('PFS0090 : Admin is validating conditional logic for all condition', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validateConditionalLogicForAllCondition();
    });

    test('PFS0091 : Admin is disabling conditional logic', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.disableConditionalLogic(formName);
    });

    test('PFS0092 : Admin is enabling post expiration', { tag: ['@Pro'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.enablePostExpiration(formName);
    });

    test('PFS0093 : Admin is setting post permission role based', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.setPostPermissionRoleBased(formName);
    });

    test('PFS0094 : Admin is validating post permission restriction', { tag: ['@Lite'] }, async () => {
        const postFormSettings = new PostFormSettingsPage(page);
        await postFormSettings.validatePostPermissionRoleBased(formName);
    });

});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});