import { Browser, BrowserContext, Page, test, expect, chromium } from '@playwright/test';
import { BasicLoginPage } from '../pages/basicLogin';
import { Base } from '../pages/base';
import { configureSpecFailFast } from '../utils/specFailFast';
import { Users, Urls } from '../utils/testData';
import { wpCli } from '../utils/wpEnvCli';

let browser: Browser;
let context: BrowserContext;
let page: Page;

let formId = 0;
let blockPage = { id: 0, url: '' };
let shortcodePage = { id: 0, url: '' };

const FORM_TITLE = 'Block E2E Form';
const BLOCK_PAGE_TITLE = 'Block E2E Page';
const SHORTCODE_PAGE_TITLE = 'Block E2E Shortcode Page';
const BLOCK_ID = 'wpuf-pf-e2e';
// Block attributes the scoped <style> must apply (label colour, button background).
const LABEL_COLOR = '#c0392b';
const BUTTON_BG = '#2c3e50';

// wp-env prints status lines around wp-cli output: pick the last numeric line.
const lastNumber = (out: string): number => Number(out.trim().split(/\s+/).filter((l) => /^\d+$/.test(l)).pop() || 0);

/**
 * Run PHP inside WordPress core only (wpCli skips plugins), passed as base64 so
 * shell quoting never breaks the code.
 */
function corePhp(code: string): string {
    const b64 = Buffer.from(code, 'utf-8').toString('base64');
    return wpCli(`eval "eval(base64_decode('${b64}'));"`);
}

/** Delete pages / forms with this title (reruns start clean). */
function deleteByTitle(postType: string, title: string): void {
    const ids = wpCli(`post list --post_type=${postType} --title="${title}" --field=ID --post_status=any`).trim();
    for (const id of ids.split(/\s+/).filter((v) => /^\d+$/.test(v))) {
        wpCli(`post delete ${id} --force`);
    }
}

/** A post form with a required title and a required content field. */
function seedForm(): number {
    deleteByTitle('wpuf_forms', FORM_TITLE);

    return lastNumber(corePhp(`
        $form_id = wp_insert_post( [ 'post_type' => 'wpuf_forms', 'post_status' => 'publish', 'post_title' => '${FORM_TITLE}' ] );
        update_post_meta( $form_id, 'wpuf_form_settings', [
            'post_type'        => 'post',
            'post_status'      => 'publish',
            'default_cat'      => '-1',
            'guest_post'       => 'false',
            'redirect_to'      => 'same',
            'message'          => 'Block E2E post saved.',
            'submit_text'      => 'Submit Block Post',
            'edit_post_status' => 'publish',
            'edit_redirect_to' => 'same',
            'edit_message'     => 'Post updated.',
            'form_template'    => '',
        ] );
        $fields = [
            [ 'input_type' => 'text', 'template' => 'post_title', 'required' => 'yes', 'label' => 'Post Title', 'name' => 'post_title', 'is_meta' => 'no', 'help' => 'Block help text', 'css' => '', 'placeholder' => '', 'default' => '', 'size' => '40', 'wpuf_cond' => [] ],
            [ 'input_type' => 'textarea', 'template' => 'post_content', 'required' => 'yes', 'label' => 'Post Content', 'name' => 'post_content', 'is_meta' => 'no', 'help' => '', 'css' => '', 'rows' => '5', 'cols' => '25', 'placeholder' => '', 'default' => '', 'rich' => 'no', 'insert_image' => 'no', 'wpuf_cond' => [] ],
        ];
        foreach ( $fields as $order => $field ) {
            wp_insert_post( [ 'post_type' => 'wpuf_input', 'post_status' => 'publish', 'post_parent' => $form_id, 'menu_order' => $order, 'post_content' => addslashes( serialize( $field ) ) ] );
        }
        echo "\\n" . $form_id;
    `));
}

/** A published page with this content; returns its id and URL. */
function seedPage(title: string, content: string): { id: number; url: string } {
    deleteByTitle('page', title);
    const id = lastNumber(corePhp(`
        echo "\\n" . wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => '${title}', 'post_content' => base64_decode( '${Buffer.from(content, 'utf-8').toString('base64')}' ) ] );
    `));
    const url = wpCli(`post url ${id}`).trim().split(/\s+/).pop() as string;

    return { id, url };
}

/** Posts with this exact title (any status). */
function postCount(title: string): number {
    const ids = wpCli(`post list --post_type=post --title="${title}" --field=ID --post_status=any`).trim();

    return ids.split(/\s+/).filter((v) => /^\d+$/.test(v)).length;
}

test.beforeAll(async () => {
    browser = await chromium.launch({ headless: ! process.env.HEADED });
    context = await browser.newContext();
    page = await context.newPage();

    formId = seedForm();
    const attrs = JSON.stringify({ blockId: BLOCK_ID, formId, labelColor: LABEL_COLOR, buttonBackgroundColor: BUTTON_BG });
    blockPage = seedPage(BLOCK_PAGE_TITLE, `<!-- wp:wpuf/post-form ${attrs} /-->`);
    shortcodePage = seedPage(SHORTCODE_PAGE_TITLE, `[wpuf_form id="${formId}"]`);

    await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
});

test.afterAll(async () => {
    for (const title of ['Block E2E post', 'Block E2E empty']) {
        const ids = wpCli(`post list --post_type=post --title="${title}" --field=ID --post_status=any`).trim();
        for (const id of ids.split(/\s+/).filter((v) => /^\d+$/.test(v))) {
            wpCli(`post delete ${id} --force`);
        }
    }
    deleteByTitle('page', BLOCK_PAGE_TITLE);
    deleteByTitle('page', SHORTCODE_PAGE_TITLE);
    deleteByTitle('wpuf_forms', FORM_TITLE);
    await browser?.close();
});

test.describe('Post Form block (wpuf/post-form)', () => {
    // Shared seeded form + pages: stop the file on the first failure.
    configureSpecFailFast();

    /**
     * @Test_Scenarios : [POST FORM BLOCK: the block keeps the classic render with its own styles; the shortcode is React]
     * @Test_BLK0001 : The block page renders the classic form inside the block wrapper (no React mount) with the block's scoped label colour and button background
     * @Test_BLK0002 : An empty submit from the block form is refused (required title / content) and creates no post
     * @Test_BLK0003 : A filled submit from the block form creates the post and shows the form's success message
     * @Test_BLK0004 : The block editor previews the block with the form (server-side render) and the inserter offers "Post Form"
     * @Test_BLK0005 : The same form through [wpuf_form] renders the React form (fields inside the React mount, no block wrapper)
     */

    test('BLK0001 : Block renders the classic form with its scoped styles', { tag: ['@Lite', '@Block'] }, async () => {
        await page.goto(blockPage.url);
        const wrapper = page.locator(`#${BLOCK_ID}`);
        await expect(wrapper).toBeVisible();
        await expect(page.locator('.wpuf-post-form-block')).toHaveCount(1);
        await expect(wrapper.locator('form.wpuf-form-add')).toBeVisible();
        await expect(wrapper.locator('[data-wpuf-mount]')).toHaveCount(0);
        await expect(wrapper.locator('input[name="post_title"]')).toBeVisible();
        await expect(wrapper.locator('textarea[name="post_content"]')).toBeVisible();

        await expect(wrapper.locator('.wpuf-label label, .wpuf-label').first()).toHaveCSS('color', 'rgb(192, 57, 43)');
        await expect(wrapper.locator('input[type="submit"], button[type="submit"]').first()).toHaveCSS('background-color', 'rgb(44, 62, 80)');
    });

    test('BLK0002 : Empty submit from the block form is refused', { tag: ['@Lite', '@Block', '@Negative'] }, async () => {
        await page.goto(blockPage.url);
        const wrapper = page.locator(`#${BLOCK_ID}`);
        const before = postCount('Block E2E empty');

        await wrapper.locator('input[type="submit"], button[type="submit"]').first().click();
        // Classic validation marks the required fields and keeps the visitor on the form.
        await expect(wrapper.locator('.wpuf-error-msg, .wpuf-error, .has-error').first()).toBeVisible({ timeout: 10000 });
        await expect(wrapper.locator('form.wpuf-form-add')).toBeVisible();
        expect(postCount('Block E2E empty')).toBe(before);
    });

    test('BLK0003 : Filled submit from the block form creates the post', { tag: ['@Lite', '@Block'] }, async () => {
        await page.goto(blockPage.url);
        const wrapper = page.locator(`#${BLOCK_ID}`);

        await wrapper.locator('input[name="post_title"]').fill('Block E2E post');
        await wrapper.locator('textarea[name="post_content"]').fill('Posted from the Post Form block.');
        await wrapper.locator('input[type="submit"], button[type="submit"]').first().click();

        await expect(page.getByText('Block E2E post saved.')).toBeVisible({ timeout: 20000 });
        await expect.poll(() => postCount('Block E2E post'), { timeout: 15000 }).toBe(1);
    });

    test('BLK0004 : Block editor previews the form and offers the block', { tag: ['@Lite', '@Block'] }, async () => {
        await page.goto(`${Urls.baseUrl}/wp-admin/post.php?post=${blockPage.id}&action=edit`);
        // No welcome guide / pattern modal in the way. Turning the preference off does not
        // close one that is already open (a new user's first editor visit), so close it too.
        await page.waitForFunction(() => !! (window as any).wp?.data?.dispatch, null, { timeout: 30000 });
        await page.evaluate(() => {
            const { dispatch } = (window as any).wp.data;
            try { dispatch('core/preferences').set('core/edit-post', 'welcomeGuide', false); } catch (e) { /* older WP */ }
            try { dispatch('core/preferences').set('core', 'enableChoosePatternModal', false); } catch (e) { /* older WP */ }
        });
        await new Base(page).dismissEditorModals();

        // The canvas is an iframe on block-based themes, inline otherwise.
        const iframe = page.frameLocator('iframe[name="editor-canvas"]');
        const inIframe = await page.locator('iframe[name="editor-canvas"]').count();
        const canvas = inIframe ? iframe.locator('body') : page.locator('.editor-styles-wrapper');
        await expect(canvas.locator('.wpuf-post-form-block, [data-type="wpuf/post-form"]').first()).toBeVisible({ timeout: 30000 });
        await expect(canvas.locator('form.wpuf-form-add input[name="post_title"]').first()).toBeVisible({ timeout: 30000 });

        // The inserter lists the block.
        await page.locator('button.editor-document-tools__inserter-toggle, button.edit-post-header-toolbar__inserter-toggle').first().click();
        await page.locator('.block-editor-inserter__search input, input[type="search"]').first().fill('Post Form');
        await expect(page.locator('.block-editor-block-types-list__item', { hasText: 'Post Form' }).first()).toBeVisible({ timeout: 15000 });
    });

    test('BLK0005 : The shortcode renders the same form on React', { tag: ['@Lite', '@Block'] }, async () => {
        await page.goto(shortcodePage.url);
        const mount = page.locator('[data-wpuf-mount]').first();
        await expect(mount).toBeAttached({ timeout: 15000 });
        // React draws the form inside its mount; no block wrapper on this page.
        await expect(mount.locator('input[name="post_title"]')).toBeVisible({ timeout: 15000 });
        await expect(page.locator('.wpuf-post-form-block')).toHaveCount(0);
    });
});
