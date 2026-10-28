import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { AiFormBuilderPage, AI_MOCK_KEY } from '../pages/aiFormBuilder';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users } from '../utils/testData';
import { BUILDER_URL } from '../utils/builderUrl';

let browser: Browser;
let context: BrowserContext;
let page: Page;
let ai: AiFormBuilderPage;

test.beforeAll(async () => {
    // HEADED=1 shows the browser (this spec launches its own).
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
    page = await context.newPage();

    const login = new BasicLoginPage(page);
    await login.basicLogin(Users.adminUsername, Users.adminPassword);

    ai = new AiFormBuilderPage(page);
    ai.configureMock();
});

test.afterAll(async () => {
    ai?.restore();
    await browser.close();
});

/**----------------------------------AI FORM BUILDER----------------------------------**
 *
 * Provider calls are answered by tests/e2e/wp/wpuf-ai-mock.php (mu-plugin, mapped
 * by .wp-env.json) because the stored OpenAI key is the mock key.
 *
 * @TestScenario : [WPUF > Post Forms > AI Form Builder]
 * @Test_AI0001 : Without a provider key the list shows the "AI Provider Not Configured" modal
 * @Test_AI0002 : Input stage: heading, counter, prompt templates fill the description, Generate needs text
 * @Test_AI0003 : Generate: processing stage, then the chat summary and the preview of the generated fields
 * @Test_AI0004 : A chat change shows Accept / Reject; Accept keeps it and saves a checkpoint
 * @Test_AI0005 : Reject puts the previous fields back
 * @Test_AI0006 : Restore to a checkpoint brings back the accepted state
 * @Test_AI0007 : A question is answered without a provider request
 * @Test_AI0008 : Regenerate: Cancel stays, Leave & Regenerate returns to an empty input stage
 * @Test_AI0009 : Edit with Builder creates the form (fields as previewed) and opens the builder
 * @Test_AI0010 : A provider error shows the "Oops..." dialog and returns to the input stage
 * @Test_AI0011 : A request that is not about forms shows the assistant's refusal in the "Oops..." dialog
 * @Test_AI0012 : Registration form from the registration list (Pro)
 * @Test_AI0013 : Free only: Pro fields open the "Pro feature detected" dialog; Continue opens the builder
 * @Test_AI0014 : Chat text is rendered as text (no markup from the AI reply)
 * @Test_AI0015 : Integration post form (WooCommerce): its prompts, sent to the API, kept on Regenerate
 * @Test_AI0016 : Integration registration form (Dokan, Pro): Pro prompts, profile form created
 * @Test_AI0017 : Key removed after the builder opened: Generate shows the "Oops..." dialog, no provider request
 * @Test_AI0018 : Registration list without a key (Pro): "AI Provider Not Configured", Go to Settings lands on AI Settings
 * @Test_AI0019 : Settings: Test Connection with the stored key reports success
 * @Test_AI0020 : Settings: Google "Fetch latest models" lists the provider's models
 * @Test_AI0021 : Builder: "AI Generate Options" on a dropdown field imports the generated options
 **/

test.describe('AI Form Builder', () => {
    test('AI0001 : Without a provider key the list shows the "AI Provider Not Configured" modal', { tag: ['@Lite', '@Test_AI0001'] }, async () => {
        ai.configureMock('');
        await ai.openFromList('post');
        await expect(page.locator(ai.S.configModal)).toBeVisible();
        await expect(page.locator(ai.S.configModal)).toContainText('AI Provider Not Configured');

        // "Go to Settings" lands on the AI section of the React settings screen.
        await page.locator(ai.S.configModal).getByRole('button', { name: 'Go to Settings' }).click();
        await page.waitForURL(/page=(wpuf-settings|wp-user-frontend)/);
        await expect(page).toHaveURL(/tab=integrations&sub=wpuf_ai/);
        await expect(page.getByText('AI Provider', { exact: true }).first()).toBeVisible({ timeout: 30000 });
        ai.configureMock(AI_MOCK_KEY);
    });

    test('AI0002 : Input stage: heading, counter, prompt templates fill the description, Generate needs text', { tag: ['@Lite', '@Test_AI0002'] }, async () => {
        await ai.open();
        const description = page.locator(ai.S.description);
        const generate = page.locator(ai.S.generateButton);

        await expect(generate).toBeDisabled();
        await expect(page.getByText('0/300 Characters')).toBeVisible();

        const prompts = page.locator(ai.S.promptButtons);
        expect(await prompts.count(), 'post prompt templates are listed').toBeGreaterThan(0);
        await prompts.first().click();
        await expect(page.locator(ai.S.activePrompt)).toHaveCount(1);
        await expect(description).not.toHaveValue('');
        expect((await description.inputValue()).length).toBeLessThanOrEqual(300);
        await expect(generate).toBeEnabled();

        // Editing the text away from the prompt deselects it.
        await description.fill('A custom description');
        await expect(page.locator(ai.S.activePrompt)).toHaveCount(0);
        await expect(page.getByText('20/300 Characters')).toBeVisible();

        // The textarea stops at 300 characters.
        await description.fill('x'.repeat(320));
        await expect(page.getByText('300/300 Characters')).toBeVisible();
    });

    test('AI0003 : Generate: processing stage, then the chat summary and the preview of the generated fields', { tag: ['@Lite', '@Test_AI0003'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');

        await expect(page.locator(ai.S.previewTitle)).toHaveText('Mock Contact Form');
        expect(await ai.previewLabels()).toEqual(['Title', 'Message', 'Email', 'Topic']);
        await expect(page.locator(ai.S.userMessages).first()).toContainText('Create a contact form');
        await expect(page.locator(ai.S.aiMessages).last()).toContainText('Perfect! I\'ve created a "Mock Contact Form" form');
        await expect(page.locator(ai.S.aiMessages).last().locator('li')).toHaveCount(4);
        await expect(page.locator(ai.S.previewField('dropdown_field')).locator('option')).toHaveText(['- Select -', 'Sales', 'Support']);
    });

    test('AI0004 : A chat change shows Accept / Reject; Accept keeps it and saves a checkpoint', { tag: ['@Lite', '@Test_AI0004'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        await ai.chat('Add a website field');

        expect(await ai.previewLabels()).toEqual(['Title', 'Message', 'Email', 'Topic', 'Website']);
        await expect(page.locator(ai.S.acceptButton)).toBeVisible();
        await expect(page.locator(ai.S.chatInput)).toBeDisabled();

        await page.locator(ai.S.acceptButton).click();
        await expect(page.locator(ai.S.acceptedStatus)).toContainText('Changes accepted & checkpoint saved');
        await expect(page.locator(ai.S.restoreButton)).toBeVisible();
        await expect(page.locator(ai.S.chatInput)).toBeEnabled();
        expect(await ai.previewLabels()).toContain('Website');
    });

    test('AI0005 : Reject puts the previous fields back', { tag: ['@Lite', '@Test_AI0005'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        await ai.chat('Please remove the message field');

        expect(await ai.previewLabels()).toEqual(['Title', 'Email', 'Topic']);
        await page.locator(ai.S.rejectButton).click();
        expect(await ai.previewLabels()).toEqual(['Title', 'Message', 'Email', 'Topic']);
        await expect(page.locator(ai.S.rejectButton)).toHaveCount(0);
        await expect(page.locator(ai.S.chatInput)).toBeEnabled();
    });

    test('AI0006 : Restore to a checkpoint brings back the accepted state', { tag: ['@Lite', '@Test_AI0006'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        await ai.chat('Add a website field');
        await page.locator(ai.S.acceptButton).click();
        await ai.chat('Add another field please');
        await page.locator(ai.S.acceptButton).click();
        expect(await ai.previewLabels()).toEqual(['Title', 'Message', 'Email', 'Topic', 'Website', 'Extra Field']);

        await page.locator(ai.S.restoreButton).first().click();
        await expect(page.getByText('Form has been restored to the checkpoint.')).toBeVisible();
        await expect.poll(() => ai.previewLabels()).toEqual(['Title', 'Message', 'Email', 'Topic', 'Website']);
    });

    test('AI0007 : A question is answered without a provider request', { tag: ['@Lite', '@Test_AI0007'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        const before = ai.mockCalls();

        await ai.chat('Why is the email field required?');
        await expect(page.locator(ai.S.aiMessages).last()).toContainText('The fields in this form are designed');
        await expect(page.locator(ai.S.acceptButton)).toHaveCount(0);
        expect(ai.mockCalls(), 'no provider request for a question').toBe(before);
    });

    test('AI0008 : Regenerate: Cancel stays, Leave & Regenerate returns to an empty input stage', { tag: ['@Lite', '@Test_AI0008'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');

        await page.locator(ai.S.regenerateButton).click();
        await expect(page.locator(ai.S.regenerateDialog)).toBeVisible();
        await page.locator(ai.S.dialogAction).click(); // Cancel (green, as develop)
        await expect(page.locator(ai.S.regenerateDialog)).toHaveCount(0);
        await expect(page.locator(ai.S.successHeading)).toBeVisible();

        await page.locator(ai.S.regenerateButton).click();
        await page.locator(ai.S.dialogCancel).click(); // Leave & Regenerate
        await expect(page.locator(ai.S.inputHeading)).toBeVisible();
        await expect(page.locator(ai.S.description)).toHaveValue('');
    });

    test('AI0009 : Edit with Builder creates the form (fields as previewed) and opens the builder', { tag: ['@Lite', '@Test_AI0009'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        await ai.chat('Add a website field');
        await page.locator(ai.S.acceptButton).click();

        const formId = await ai.editInBuilder();
        expect(formId).toBeGreaterThan(0);
        await expect(page.getByRole('button', { name: 'Save', exact: true }).first()).toBeVisible({ timeout: 30000 });

        const form = ai.storedForm(formId);
        expect(form.post_type).toBe('wpuf_forms');
        expect(form.title).toBe('Mock Contact Form');
        expect(form.ai).toBe('1');
        const stored = ai.storedFields(formId);
        expect(stored.map((field) => field.label)).toEqual(['Title', 'Message', 'Email', 'Topic', 'Website']);
        expect(stored.map((field) => field.template)).toEqual(['post_title', 'post_content', 'email_address', 'dropdown_field', 'website_url']);
        expect(stored.find((field) => 'Email' === field.label)?.required).toBe('yes');
    });

    test('AI0010 : A provider error shows the "Oops..." dialog and returns to the input stage', { tag: ['@Lite', '@Test_AI0010'] }, async () => {
        await ai.open();
        await page.locator(ai.S.description).fill('mock-error please');
        await page.locator(ai.S.generateButton).click();

        await expect(page.locator(ai.S.errorDialog)).toBeVisible({ timeout: 30000 });
        await expect(page.locator(ai.S.errorDialog)).toContainText('Oops...');
        await expect(page.locator(ai.S.errorDialog)).toContainText('Mock provider is down');
        await page.locator(ai.S.dialogAction).click();
        await expect(page.locator(ai.S.inputHeading)).toBeVisible();
        await expect(page.locator(ai.S.description)).toHaveValue('mock-error please');
    });

    test('AI0011 : A request that is not about forms shows the assistant\'s refusal in the "Oops..." dialog', { tag: ['@Lite', '@Test_AI0011'] }, async () => {
        await ai.open();
        await page.locator(ai.S.description).fill('not-a-form: tell me a joke');
        await page.locator(ai.S.generateButton).click();

        await expect(page.locator(ai.S.errorDialog)).toBeVisible({ timeout: 30000 });
        await expect(page.locator(ai.S.errorDialog)).toContainText('I can only help with forms (mock).');
        await page.locator(ai.S.dialogAction).click();
        await expect(page.locator(ai.S.inputHeading)).toBeVisible();
    });

    test('AI0012 : Registration form from the registration list (Pro)', { tag: ['@Pro', '@Test_AI0012'] }, async () => {
        test.skip(!ai.proActive(), 'registration forms need Pro');

        await ai.open('profile');
        await ai.generate('Create a sign up form');
        await expect(page.locator(ai.S.previewTitle)).toHaveText('Mock Sign Up Form');

        const formId = await ai.editInBuilder();
        // The registration builder page, or its admin app route.
        expect(page.url()).toMatch(/page=wpuf-profile-forms|#\/registration-forms\//);
        const form = ai.storedForm(formId);
        expect(form.post_type).toBe('wpuf_profile');
        expect(ai.storedFields(formId).map((field) => field.template)).toEqual(['user_email', 'user_login', 'password', 'first_name']);
    });

    test('AI0013 : Free only: Pro fields open the "Pro feature detected" dialog; Continue opens the builder', { tag: ['@Lite', '@Test_AI0013'] }, async () => {
        test.skip(ai.proActive(), 'the dialog only shows without Pro');

        await ai.open();
        await ai.generate('Create a visit form with pro-fields');
        await page.locator(ai.S.editInBuilderButton).click();
        await expect(page.locator(ai.S.proFieldsDialog)).toBeVisible();
        await expect(page.locator(ai.S.proFieldItems)).toHaveText(['Phone Number', 'Date Picker']);

        await page.locator(ai.S.dialogCancel).click(); // Continue without Pro
        await page.waitForURL(BUILDER_URL, { timeout: 30000 });
    });

    test('AI0014 : Chat text is rendered as text (no markup from the AI reply)', { tag: ['@Lite', '@Test_AI0014'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        await ai.chat('<img src=x onerror="window.__wpufXss=1"> what is this?');

        await expect(page.locator(ai.S.userMessages).last()).toContainText('<img src=x');
        await expect(page.locator(`${ai.S.root} img[src="x"]`)).toHaveCount(0);
        expect(await page.evaluate(() => (window as unknown as { __wpufXss?: number }).__wpufXss)).toBeUndefined();
    });

    test('AI0015 : Integration post form (WooCommerce): its prompts, sent to the API, kept on Regenerate', { tag: ['@Lite', '@Test_AI0015'] }, async () => {
        test.skip(!ai.activeIntegrations('post').includes('woocommerce'), 'needs WooCommerce active');

        await ai.open();
        const regular = await ai.promptLabels();
        await ai.selectIntegration('WooCommerce');
        const woo = await ai.promptLabels();
        expect(woo.length, 'WooCommerce prompt templates').toBeGreaterThan(0);
        expect(woo).not.toEqual(regular);

        await ai.generate('Create a product submission form');
        await expect(page.locator(ai.S.previewTitle)).toHaveText('Mock Contact Form (woocommerce)');

        await page.locator(ai.S.regenerateButton).click();
        await page.locator(ai.S.dialogCancel).click(); // Leave & Regenerate
        await expect(page.locator(ai.S.inputHeading)).toBeVisible();
        await expect(page.locator(ai.S.integrationSelect)).toContainText('WooCommerce');
        expect(await ai.promptLabels()).toEqual(woo);
    });

    test('AI0016 : Integration registration form (Dokan, Pro): Pro prompts, profile form created', { tag: ['@Pro', '@Test_AI0016'] }, async () => {
        test.skip(!ai.proActive(), 'registration forms need Pro');
        test.skip(!ai.activeIntegrations('profile').includes('dokan'), 'needs Dokan active');

        await ai.open('profile');
        await ai.selectIntegration('Dokan');
        expect((await ai.promptLabels()).length, 'Pro adds the Dokan prompt templates').toBeGreaterThan(0);

        await ai.generate('Create a vendor registration form');
        await expect(page.locator(ai.S.previewTitle)).toHaveText('Mock Sign Up Form (dokan)');

        const formId = await ai.editInBuilder();
        expect(ai.storedForm(formId).post_type).toBe('wpuf_profile');
    });

    test('AI0017 : Key removed after the builder opened: Generate shows the "Oops..." dialog, no provider request', { tag: ['@Lite', '@Test_AI0017'] }, async () => {
        await ai.open();
        ai.configureMock('');

        try {
            await page.locator(ai.S.description).fill('Create a contact form');
            await page.locator(ai.S.generateButton).click();

            await expect(page.locator(ai.S.errorDialog)).toBeVisible({ timeout: 30000 });
            await expect(page.locator(ai.S.errorDialog)).toContainText('Oops...');
            await expect(page.locator(ai.S.errorDialog)).toContainText('No API key is set for the selected AI provider');
            expect(ai.mockCalls(), 'no provider request without a key').toBe(0);
            await page.locator(ai.S.dialogAction).click();
            await expect(page.locator(ai.S.inputHeading)).toBeVisible();
        } finally {
            ai.configureMock(AI_MOCK_KEY);
        }
    });

    test('AI0018 : Registration list without a key (Pro): "AI Provider Not Configured", Go to Settings lands on AI Settings', { tag: ['@Pro', '@Test_AI0018'] }, async () => {
        test.skip(!ai.proActive(), 'registration forms need Pro');
        ai.configureMock('');

        try {
            await ai.openFromList('profile');
            await expect(page.locator(ai.S.configModal)).toBeVisible();
            await expect(page.locator(ai.S.configModal)).toContainText('AI Provider Not Configured');
            await page.locator(ai.S.configModal).getByRole('button', { name: 'Go to Settings' }).click();
            await page.waitForURL(/page=(wpuf-settings|wp-user-frontend)/);
            await expect(page).toHaveURL(/tab=integrations&sub=wpuf_ai/);
            await expect(page.getByText('AI Provider', { exact: true }).first()).toBeVisible({ timeout: 30000 });
        } finally {
            ai.configureMock(AI_MOCK_KEY);
        }
    });

    test('AI0019 : Settings: Test Connection with the stored key reports success', { tag: ['@Lite', '@Test_AI0019'] }, async () => {
        await page.goto(`${ai.wpufSettingsPage}#wpuf_ai`);
        await page.locator(ai.S.testConnectionButton).click();
        await expect(page.getByText('OpenAI connection successful!')).toBeVisible({ timeout: 30000 });
        expect(ai.mockCalls(), 'one provider request').toBe(1);
    });

    test('AI0020 : Settings: Google "Fetch latest models" lists the provider\'s models', { tag: ['@Lite', '@Test_AI0020'] }, async () => {
        ai.configureMock(AI_MOCK_KEY, 'google');

        try {
            await page.goto(`${ai.wpufSettingsPage}#wpuf_ai`);
            await page.locator(ai.S.fetchModelsButton).click();
            await expect(page.locator(ai.S.fetchModelsButton)).toBeEnabled({ timeout: 30000 });
            expect(ai.modelIds()).toContain('gemini-mock-flash');
        } finally {
            ai.configureMock(AI_MOCK_KEY);
        }
    });

    test('AI0021 : Builder: "AI Generate Options" on a dropdown field imports the generated options', { tag: ['@Lite', '@Test_AI0021'] }, async () => {
        await ai.open();
        await ai.generate('Create a contact form');
        await ai.editInBuilder();

        await page.hover(ai.S.builderField('dropdown_field'));
        await page.locator(ai.S.builderFieldEdit('dropdown_field')).first().click();
        await page.locator(ai.S.optionsAiButton).click();
        await expect(page.locator(ai.S.optionsAiModal)).toBeVisible();
        await page.locator(ai.S.optionsAiPrompt).fill('Colours');
        await page.locator(ai.S.optionsAiGenerate).click();
        await expect(page.locator(ai.S.optionsAiList)).toContainText(['Mock Red'], { timeout: 30000 });
        await expect(page.locator(ai.S.optionsAiList)).toContainText('Mock Blue');
        await page.locator(ai.S.optionsAiImport).click();
        await expect(page.locator(ai.S.optionsAiModal)).toHaveCount(0);

        const labels = await page.locator('input[value="Mock Red"], input[value="Mock Green"], input[value="Mock Blue"]').count();
        expect(labels, 'the three options are in the option list').toBeGreaterThanOrEqual(3);
    });
});
