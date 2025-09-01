import * as dotenv from 'dotenv';
dotenv.config();
import { expect, request, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';
import { faker } from '@faker-js/faker';
import { DownloadsForm, PostForm, ProductForm, Urls } from '../utils/testData';
//import { TestData } from '../tests/testdata';

export class PostFormPage extends Base {

    constructor(page: Page) {
        super(page);
    }


    /**********************************/
    /******* @Post Forms *************/
    /********************************/

    //BlankForm
    async createBlankFormPostForm(newPostName: string) {

        //Visit Post Form Page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        };

        //Click Blank Form
        await this.page.waitForSelector(Selectors.postForms.createBlankForm_PF.clickBlankForm);
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickBlankForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.validateAndFillStrings(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

    }


    //PresetForm
    async createPresetPostForm(newPostName: string) {
        //Visit Post Form Page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        };

        //ClickPostForm
        //Templates 
        //Click Preset Form  
        await this.validateAndClick(Selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.validateAndFillStrings(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);

    }


    //PresetForm
    async createPresetPostFormWithGuestEnabled(newPostName: string) {
        //Visit Post Form Page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        };

        //ClickBlankForm
        //Templates 
        //Hover over - Blank Form
        // await this.page.waitForSelector(Selectors.postForms.createPreset_PF.hoverPresetForm);
        // await this.page.hover(Selectors.postForms.createPreset_PF.hoverPresetForm);
        //Click Preset Form  
        await this.validateAndClick(Selectors.postForms.createPreset_PF.clickPresetForm);

        //EnterName
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        //Enter Form Name
        await this.validateAndFillStrings(Selectors.postForms.createBlankForm_PF.enterNewFormName, newPostName);
        //Click Tick/Confirm button
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.confirmNewNameTickButton);
        //Click Form Editor again - to handle Shortcode tooltip
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditor);

        //Enabled Guest Post Submission
        //Click Form Settings
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditorSettings);
        //Click Submission Restrictions
        //await this.validateAndClick(Selectors.postForms.formSettings.clickSubmissionRestriction);
        //Enable Guest Post Submission
        await this.validateAndClick(Selectors.postForms.formSettings.setPostPermission);
        await this.validateAndClick(Selectors.postForms.formSettings.enableGuestPost);
        //Enter Guest Details
        await this.validateAndClick(Selectors.postForms.formSettings.enterGuestDetails);
        //Enter Name Label
        await this.validateAndFillStrings(Selectors.postForms.formSettings.enterNameLabel, 'Guest Name');
        //Enter Email Label
        await this.validateAndFillStrings(Selectors.postForms.formSettings.enterEmailLabel, 'Guest Email');
        //Save Form Settings
        await this.validateAndClick(Selectors.postForms.formSettings.saveFormSettings);

        //Return
        //Form Editor Page
        await this.validateAndClick(Selectors.postForms.formSettings.clickFormEditor);

        //Save Form Settings
        await this.validateAndClick(Selectors.postForms.formSettings.saveFormSettings);

    }

    //PresetForm
    async createProductPostForm() {
        //Visit Post Form Page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        };

        //ClickPostForm
        //Templates 
        //Click Product Form  
        await this.validateAndClick(Selectors.postForms.createProduct_PF.clickProductForm);

        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        // Save Form
        await this.validateAndClick(Selectors.postForms.formSettings.saveFormSettings);
        // Confirm Save
        await this.assertionValidate(Selectors.postForms.formSettings.validateFormSettingsSaved);

    }

    async createDownloadsPostForm() {
        //Visit Post Form Page
        await this.navigateToURL(this.wpufPostFormPage);

        // Wait for form list to load and click on the form
        try {
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        } catch (error) {
            await this.navigateToURL(this.wpufPostFormPage);
            await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);;
        };
        //ClickPostForm
        //Templates 
        //Click Product Form  
        await this.validateAndClick(Selectors.postForms.createDownloads_PF.clickDownloadsForm);
        //EnterName
        await this.page.reload();
        //Click Form Name Box
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.editNewFormName);
        // Save Form
        await this.validateAndClick(Selectors.postForms.formSettings.saveFormSettings);
        // Confirm Save
        await this.assertionValidate(Selectors.postForms.formSettings.validateFormSettingsSaved);

    }

    /******* @Create Post > FrontEnd **********/

    async createPostFE() {
        //Go to Accounts page - FrontEnd
        await this.navigateToURL(this.postHerePage);

        //Post Form process
        //Enter Post Title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, PostForm.title = faker.word.words(2));
        console.log(PostForm.title);
        await this.page.waitForTimeout(1000);
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(PostForm.description = faker.lorem.sentence(1));
        console.log(PostForm.description);
        //Enter Excerpt
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, PostForm.excerpt = faker.lorem.sentence(1));
        console.log(PostForm.excerpt);
        //Add Featured Photo
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, PostForm.featuredImage);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.assertionValidate(Selectors.postForms.postFormsFrontendCreate.uploads('1'));
        //Select Category
        await this.selectOptionWithLabel(Selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, PostForm.category);
        //Enter Tags
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, PostForm.tags);
        //Enter Text
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTextFormsFE, PostForm.text = faker.lorem.sentence(1));
        console.log(PostForm.text);
        //Enter Textarea
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTextareaFormsFE, PostForm.textarea = faker.lorem.sentence(1));
        console.log(PostForm.textarea);
        //Enter Dropdown
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postDropdownFormsFE, PostForm.dropdown);
        //Enter Multi Select
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postMultiSelectFormsFE, PostForm.multiSelect);
        //Enter Radio
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postRadioFormsFE);
        //Enter Checkbox
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postCheckboxFormsFE);
        //Enter Website URL
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postWebsiteUrlFormsFE, PostForm.websiteUrl = faker.internet.url());
        console.log(PostForm.websiteUrl);
        //Enter Email Address
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postEmailAddressFormsFE, PostForm.emailAddress = faker.internet.email());
        console.log(PostForm.emailAddress);
        //Enter Image Upload
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.postImageUploadFormsFE, PostForm.imageUpload);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.assertionValidate(Selectors.postForms.postFormsFrontendCreate.uploads('2'));
        //Enter Repeat Field
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postRepeatFieldFormsFE, PostForm.repeatField = this.generateWordWithMinLength(5));
        console.log(PostForm.repeatField);
        //Enter Date / Time
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.dateTimeSelect);
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.selectYear, '2024');
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.selectMonth, '7');
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.selectDay);
        //Enter Time Field
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postTimeFieldFormsFE, PostForm.time);
        //Enter File Upload
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.postFileUploadFormsFE, PostForm.uploadFile);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.assertionValidate(Selectors.postForms.postFormsFrontendCreate.uploads('3'));
        //Enter Country List
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postCountryListFormsFE, 'BD');
        //Enter Numeric Field
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postNumericFieldFormsFE, PostForm.numeric);
        //Enter Phone Field
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postPhoneFieldFormsFE.countryContainer);
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postPhoneFieldFormsFE.countrySelect);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postPhoneFieldFormsFE.phoneNumber, PostForm.phoneNumber = `016${faker.string.numeric(8)}`);
        PostForm.phoneNumber = '+88' + PostForm.phoneNumber;
        console.log(PostForm.phoneNumber);
        //Enter Address Field
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.addressLine1, PostForm.addressLine1 = faker.location.streetAddress());
        console.log(PostForm.addressLine1);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.addressLine2, PostForm.addressLine2 = faker.location.secondaryAddress());
        console.log(PostForm.addressLine2);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.city, PostForm.city);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.zip, PostForm.zip);
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.country, 'BD');
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.state, 'BD-13');
        //Enter Google Maps
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postGoogleMapsFormsFE, PostForm.googleMaps = 'Dhaka, Bangladesh');
        await this.page.keyboard.press('Enter');
        //Enter Embed
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postEmbedFormsFE, PostForm.embed = faker.internet.url());
        console.log(PostForm.embed);
        //Enter Terms and Conditions
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postTermsAndConditionsFormsFE);
        //Enter Ratings
        await this.selectOptionWithValue(Selectors.postForms.postFormsFrontendCreate.postRatingsFormsFE, PostForm.ratings = '5');
        console.log(PostForm.ratings);
        // Math Captcha
        const operand1 = await this.page.textContent(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.operand1);
        const operand2 = await this.page.textContent(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.operand2);
        const operator = await this.page.textContent(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.operator);
        let result: number;
        switch (operator) {
            case '+':
                result = Number(operand1) + Number(operand2);
                break;
            case '-':
                result = Number(operand1) - Number(operand2);
                break;
            case 'X':
                result = Number(operand1) * Number(operand2);
                break;
            case 'x':
                result = Number(operand1) * Number(operand2);
                break;
            case '/':
                result = Number(operand1) / Number(operand2);
                break;
            default:
                throw new Error('Invalid operator');
        }
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.mathCaptcha, result.toString());
        //Create Post
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.submitPostFormsFE);
    }

    //Validate Post Created
    async validatePostCreated() {
        //Validate Post Submitted
        await this.checkElementText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(PostForm.title), PostForm.title);
    }

    //Validate Entered Data
    async validateEnteredData() {
        //Validate Post Title
        await this.checkElementText(Selectors.postForms.postFormData.title(PostForm.title), PostForm.title);
        //Validate Post Description
        await this.checkElementText(Selectors.postForms.postFormData.description(PostForm.description), PostForm.description);
        //Validate Featured Image
        expect(await this.page.isVisible(Selectors.postForms.postFormData.featuredImage)).toBeTruthy();
        //Validate Category
        await this.checkElementText(Selectors.postForms.postFormData.category, PostForm.category);
        //Validate Tags
        await this.checkElementText(Selectors.postForms.postFormData.tags, PostForm.tags);
        //Validate Text
        await this.checkElementText(Selectors.postForms.postFormData.text, PostForm.text);
        //Validate Textarea
        await this.checkElementText(Selectors.postForms.postFormData.textarea, PostForm.textarea);
        //Validate Dropdown
        await this.checkElementText(Selectors.postForms.postFormData.dropdown, PostForm.dropdown);
        //Validate Multi Select
        await this.checkElementText(Selectors.postForms.postFormData.multiSelect, PostForm.multiSelect);
        //Validate Radio
        await this.checkElementText(Selectors.postForms.postFormData.radio, PostForm.radio);
        //Validate Checkbox
        await this.checkElementText(Selectors.postForms.postFormData.checkbox, PostForm.checkbox);
        //Validate Website URL
        await this.checkElementText(Selectors.postForms.postFormData.websiteUrl, PostForm.websiteUrl);
        //Validate Email Address
        await this.checkElementText(Selectors.postForms.postFormData.emailAddress, PostForm.emailAddress);
        //Validate Image Upload
        expect(await this.page.isVisible(Selectors.postForms.postFormData.imageUpload)).toBe(true);
        //Validate Repeat Field
        await this.checkElementText(Selectors.postForms.postFormData.repeatField(PostForm.repeatField), PostForm.repeatField);
        //Validate Date / Time
        await this.checkElementText(Selectors.postForms.postFormData.dateTime(PostForm.date), PostForm.date);
        //Validate Time Field
        await this.checkElementText(Selectors.postForms.postFormData.timeField(PostForm.time), PostForm.time);
        //Validate File Upload
        expect(await this.page.isVisible(Selectors.postForms.postFormData.fileUpload)).toBe(true);
        //Validate Country List
        await this.checkElementText(Selectors.postForms.postFormData.countryList(PostForm.countryList), PostForm.countryList);
        //Validate Numeric Field
        await this.checkElementText(Selectors.postForms.postFormData.numericField, PostForm.numeric);
        //Validate Phone Field
        await this.checkElementText(Selectors.postForms.postFormData.phoneField(PostForm.phoneNumber), PostForm.phoneNumber);
        //Validate Address Line 1
        await this.checkElementText(Selectors.postForms.postFormData.addressLine1(PostForm.addressLine1), PostForm.addressLine1);
        //Validate Address Line 2
        await this.checkElementText(Selectors.postForms.postFormData.addressLine2(PostForm.addressLine2), PostForm.addressLine2);
        //Validate City
        await this.checkElementText(Selectors.postForms.postFormData.city(PostForm.city), PostForm.city);
        //Validate Zip
        await this.checkElementText(Selectors.postForms.postFormData.zip(PostForm.zip), PostForm.zip);
        //Validate Country
        await this.checkElementText(Selectors.postForms.postFormData.country(PostForm.country), PostForm.country);
        //Validate State
        await this.checkElementText(Selectors.postForms.postFormData.state(PostForm.state), PostForm.state);
        //Validate Embed
        await this.checkElementText(Selectors.postForms.postFormData.embed, PostForm.embed);
        //Validate Ratings
        await this.checkElementText(Selectors.postForms.postFormData.ratings, PostForm.ratings);
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
        console.log("Signed Out");
    }

    //Create Page with Shortcode
    async createPageWithShortcode(shortcode: string, pageTitle: string) {

        // Get nonce for REST API authentication
        let nonce = await this.page.evaluate(() => {
            return (window as any).wpApiSettings?.nonce || '';
        });

        // If nonce not found, try to get it from the admin area
        if (!nonce) {
            // Navigate to admin dashboard to get nonce
            await this.navigateToURL(this.wpAdminPage);
            nonce = await this.page.evaluate(() => {
                return (window as any).wpApiSettings?.nonce || '';
            });
        }

        //console.log('REST API Nonce:', nonce);

        const storageState = await this.page.context().storageState();
        // Create a new request context with auth cookies and nonce
        const apiContext = await request.newContext({
            baseURL: Urls.baseUrl,
            storageState: storageState,
            extraHTTPHeaders: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce,
            },
            ignoreHTTPSErrors: true,
        });

        // Create page using REST API with auth session cookie and nonce
        const res = await apiContext.post('/wp-json/wp/v2/pages', {
            data: {
                title: pageTitle,
                content: shortcode,
                status: 'publish',
            },
        });

        // Debug: Log response details
        console.log('API Response Status:', res.status());
        //console.log('API Response Headers:', await res.headersArray());

        if (!res.ok()) {
            const errorBody = await res.text();
            console.log('API Error Response Body:', errorBody);
            throw new Error(`API request failed with status ${res.status()}: ${errorBody}`);
        }

        const pageData = await res.json();
        console.log('Page created:', pageData.link);

    }

    //Create Page with Shortcode general
    async createPageWithShortcodeGeneral(shortcode: string, pageTitle: string) {

        // Get nonce for REST API authentication
        let nonce = await this.page.evaluate(() => {
            return (window as any).wpApiSettings?.nonce || '';
        });

        // If nonce not found, try to get it from the admin area
        if (!nonce) {
            // Navigate to admin dashboard to get nonce
            await this.navigateToURL(this.wpAdminPage);
            nonce = await this.page.evaluate(() => {
                return (window as any).wpApiSettings?.nonce || '';
            });
        }

        //console.log('REST API Nonce:', nonce);

        const storageState = await this.page.context().storageState();
        // Create a new request context with auth cookies and nonce
        const apiContext = await request.newContext({
            baseURL: Urls.baseUrl,
            storageState: storageState,
            extraHTTPHeaders: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce,
            },
            ignoreHTTPSErrors: true,
        });

        // Create page using REST API with auth session cookie and nonce
        const res = await apiContext.post('/wp-json/wp/v2/pages', {
            data: {
                title: pageTitle,
                content: shortcode,
                status: 'publish',
            },
        });

        // Debug: Log response details
        console.log('API Response Status:', res.status());
        //console.log('API Response Headers:', await res.headersArray());

        if (!res.ok()) {
            const errorBody = await res.text();
            console.log('API Error Response Body:', errorBody);
            throw new Error(`API request failed with status ${res.status()}: ${errorBody}`);
        }

        const pageData = await res.json();
        console.log('Page created:', pageData.link);

    }

    async createGuestPostFE() {
        let guestName: string;
        let guestEmail: string;
        //Go to Accounts page - FrontEnd
        await this.navigateToURL(Urls.baseUrl + '/guestpostform/');

        //Post Form process
        //Enter Guest Name
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.guestName, guestName = faker.person.fullName());
        console.log(guestName);
        //Enter Guest Email
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.guestEmail, guestEmail = faker.internet.email());
        console.log(guestEmail);
        //Enter Post Title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, PostForm.title = faker.word.words(2));
        console.log(PostForm.title);
        await this.page.waitForTimeout(1000);
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(PostForm.description = faker.lorem.sentence(1));
        console.log(PostForm.description);
        //Enter Excerpt
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, PostForm.excerpt = faker.lorem.sentence(1));
        console.log(PostForm.excerpt);
        //Add Featured Photo
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, PostForm.featuredImage);
        await this.page.waitForTimeout(500);
        //Select Category
        await this.selectOptionWithLabel(Selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, PostForm.category);
        //Enter Tags
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, PostForm.tags);
        //Create Post
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.submitPostFormsFE);
    }

    //Validate Post Created
    async validateGuestPostCreated() {
        //Validate Post Submitted
        await this.checkElementText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(PostForm.title), PostForm.title);
    }

    async setupForWooProduct() {
        //Visit WOO Pages
        await this.navigateToURL(this.productBrandPage);
        //Click Add New Page
        await this.validateAndFillStrings(Selectors.postForms.productPostForm.addBrand, 'Apple');
        await this.validateAndClick(Selectors.postForms.productPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);
        //Visit WOO Pages
        await this.navigateToURL(this.productCategoryPage);
        //Check if the Welcome Modal is visible
        await this.validateAndFillStrings(Selectors.postForms.productPostForm.addCategory, 'Electronics');
        await this.validateAndClick(Selectors.postForms.productPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);
        //Visit WOO Pages
        await this.navigateToURL(this.productTagPage);
        //Check if the Welcome Modal is visible
        await this.validateAndFillStrings(Selectors.postForms.productPostForm.addTag, 'Smartphone');
        await this.validateAndClick(Selectors.postForms.productPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);
        //Visit WOO Pages
        await this.navigateToURL(this.productAttributePage);
        //Enter Page Title
        await this.validateAndFillStrings(Selectors.postForms.productPostForm.addAttribute, 'Color');
        await this.validateAndClick(Selectors.postForms.productPostForm.saveAttribute);
        await this.page.waitForTimeout(1000);
        //Configure Attribute Terms
        await this.validateAndClick(Selectors.postForms.productPostForm.configureAttributeTerms);
        await this.validateAndFillStrings(Selectors.postForms.productPostForm.addAttributeTerms, 'Red');
        await this.validateAndClick(Selectors.postForms.productPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);
        await this.validateAndFillStrings(Selectors.postForms.productPostForm.addAttributeTerms, 'Blue');
        await this.validateAndClick(Selectors.postForms.productPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);

    }

    async setupForEDDProduct() {
        await this.navigateToURL(this.eddCatPage);

        await this.validateAndFillStrings(Selectors.postForms.eddPostForm.addCategory, 'plugins');
        await this.validateAndClick(Selectors.postForms.eddPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);

        await this.navigateToURL(this.eddTagPage);

        await this.validateAndFillStrings(Selectors.postForms.eddPostForm.addTag, 'wpuf');
        await this.validateAndClick(Selectors.postForms.eddPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);

        await this.validateAndFillStrings(Selectors.postForms.eddPostForm.addTag, 'wpuf-pro');
        await this.validateAndClick(Selectors.postForms.eddPostForm.saveSubmit);
        await this.page.waitForTimeout(1000);
    }

    async createProductFE() {
        //Go to Accounts page - FrontEnd
        await this.navigateToURL(this.addProductPage);

        //Post Form process
        //Enter Product Name
        await this.validateAndFillStrings(Selectors.postForms.productFrontendCreate.productTitleFE, ProductForm.title);
        await this.selectOptionWithLabel(Selectors.postForms.productFrontendCreate.selectCategory, ProductForm.category);
        await this.page.waitForTimeout(1000);
        await this.page.frameLocator(Selectors.postForms.productFrontendCreate.productDescription1)
            .locator(Selectors.postForms.productFrontendCreate.productDescription2).fill(ProductForm.description = faker.lorem.sentence(1));
        await this.validateAndFillStrings(Selectors.postForms.productFrontendCreate.productExcerpt, ProductForm.excerpt = faker.lorem.sentence(1));
        await this.validateAndFillStrings(Selectors.postForms.productFrontendCreate.productRegularPrice, ProductForm.regularPrice);
        await this.validateAndFillStrings(Selectors.postForms.productFrontendCreate.productSalePrice, ProductForm.salePrice);
        await this.page.setInputFiles(Selectors.postForms.productFrontendCreate.productImage, ProductForm.productImage);
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.postForms.productFrontendCreate.uploads('1'));
        await this.page.setInputFiles(Selectors.postForms.productFrontendCreate.productImageGallery, ProductForm.imageGallery1);
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.postForms.productFrontendCreate.uploads('2'));
        await this.page.setInputFiles(Selectors.postForms.productFrontendCreate.productImageGallery, ProductForm.imageGallery2);
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.postForms.productFrontendCreate.uploads('3'));
        await this.selectOptionWithValue(Selectors.postForms.productFrontendCreate.catalogVisibility, ProductForm.catalogVisibility);
        await this.validateAndFillStrings(Selectors.postForms.productFrontendCreate.purchaseNote, ProductForm.purchaseNote = faker.lorem.sentence(1));
        await this.validateAndClick(Selectors.postForms.productFrontendCreate.enableReviews);
        await this.validateAndClick(Selectors.postForms.productFrontendCreate.downloadable);
        await this.page.waitForTimeout(1000);
        await this.selectOptionWithLabel(Selectors.postForms.productFrontendCreate.selectBrand, ProductForm.brand);
        await this.selectOptionWithLabel(Selectors.postForms.productFrontendCreate.selectType, ProductForm.type);
        await this.selectOptionWithLabel(Selectors.postForms.productFrontendCreate.selectVisibility, ProductForm.visibility);
        await this.page.waitForTimeout(1000);
        await this.selectOptionWithLabel(Selectors.postForms.productFrontendCreate.selectTag, ProductForm.tag);
        await this.selectOptionWithValue(Selectors.postForms.productFrontendCreate.selectShippingClass, ProductForm.shippingClass);
        await this.selectOptionWithLabel(Selectors.postForms.productFrontendCreate.selectColor, ProductForm.color);
        await this.validateAndClick(Selectors.postForms.productFrontendCreate.createProduct);
        await this.page.waitForTimeout(1000);
    }

    async createDownloadsFE() {
        //Go to Accounts page - FrontEnd
        await this.navigateToURL(this.addDownloadsPage);

        //Post Form process
        //Enter Product Name
        await this.validateAndFillStrings(Selectors.postForms.downloadsFrontendCreate.downloadsTitleFE, DownloadsForm.title);
        await this.selectOptionWithLabel(Selectors.postForms.downloadsFrontendCreate.downloadCategory, DownloadsForm.category);
        await this.page.waitForTimeout(1000);
        await this.page.frameLocator(Selectors.postForms.downloadsFrontendCreate.downloadsDescription1)
            .locator(Selectors.postForms.downloadsFrontendCreate.downloadsDescription2).fill(DownloadsForm.description = faker.lorem.sentence(1));
        await this.validateAndFillStrings(Selectors.postForms.downloadsFrontendCreate.downloadsExcerpt, DownloadsForm.excerpt = faker.lorem.sentence(1));
        await this.validateAndFillStrings(Selectors.postForms.downloadsFrontendCreate.downloadsRegularPrice, DownloadsForm.regularPrice);
        await this.page.setInputFiles(Selectors.postForms.downloadsFrontendCreate.downloadsImage, DownloadsForm.downloadsImage);
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.postForms.downloadsFrontendCreate.uploads('1'));
        await this.validateAndFillStrings(Selectors.postForms.downloadsFrontendCreate.purchaseNote, DownloadsForm.purchaseNote = faker.lorem.sentence(1));
        await this.page.setInputFiles(Selectors.postForms.downloadsFrontendCreate.downloadableFiles, DownloadsForm.downloadableFiles);
        await this.page.waitForTimeout(500);
        await this.assertionValidate(Selectors.postForms.downloadsFrontendCreate.uploads('2'));
        await this.waitForLoading();
        //await this.selectOptionWithLabel(Selectors.postForms.downloadsFrontendCreate.downloadsTag, DownloadsForm.tags);
        await this.validateAndClick(Selectors.postForms.downloadsFrontendCreate.createDownloads);
        await this.page.waitForTimeout(1000);
    }

    async validateProductCreated() {
        //Validate Product Submitted
        await this.checkElementText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(ProductForm.title), ProductForm.title);
    }

    async validateDownloadsCreated() {
        //Validate Product Submitted
        await this.checkElementText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(DownloadsForm.title), DownloadsForm.title);
    }

    async validateEnteredProductData() {
        //Validate Product Title
        await this.checkElementText(Selectors.postForms.productFormData.title(ProductForm.title), ProductForm.title);
        //Validate Product Description
        await this.checkElementText(Selectors.postForms.productFormData.description(ProductForm.description), ProductForm.description);
        //Validate Product Excerpt
        await this.checkElementText(Selectors.postForms.productFormData.excerpt, ProductForm.excerpt);
        // images
        await this.assertionValidate(Selectors.postForms.productFormData.featuredImage);
        await this.assertionValidate(Selectors.postForms.productFormData.galleryImage('1'));
        await this.assertionValidate(Selectors.postForms.productFormData.galleryImage('2'));
        await this.assertionValidate(Selectors.postForms.productFormData.galleryImage('3'));
        //Validate Product Regular Price
        await this.checkElementText(Selectors.postForms.productFormData.regularPrice, ProductForm.regularPrice);
        //Validate Product Sale Price
        await this.checkElementText(Selectors.postForms.productFormData.salePrice, ProductForm.salePrice);
        //Validate Product Featured Image
        expect(await this.page.isVisible(Selectors.postForms.productFormData.featuredImage)).toBeTruthy();
        //Validate Product Category
        await this.checkElementText(Selectors.postForms.productFormData.category, ProductForm.category);
        //Validate Product Tags
        await this.checkElementText(Selectors.postForms.productFormData.tags, ProductForm.tags);
        //Validate Product Brand
        await this.checkElementText(Selectors.postForms.productFormData.brand, ProductForm.brand);
        //Validate Product Reviews
        await this.assertionValidate(Selectors.postForms.productFormData.reviews);
    }

    async validateEnteredDownloadsData() {
        //Validate Product Title
        await this.checkElementText(Selectors.postForms.downloadsFormData.title(DownloadsForm.title), DownloadsForm.title);
        //Validate Product Description
        await this.checkElementText(Selectors.postForms.downloadsFormData.description(DownloadsForm.description), DownloadsForm.description);
        // images
        await this.assertionValidate(Selectors.postForms.downloadsFormData.downloadsImage);
        //Validate Product Purchase Button
        await this.assertionValidate(Selectors.postForms.downloadsFormData.purchaseButton);
    }

    async validateEnteredDownloadsDataBE() {
        await this.navigateToURL(this.downloadsPage);
        //Validate Product Title
        await this.validateAndClick(Selectors.postForms.downloadsFormData.titleBE(DownloadsForm.title));
        await this.page.waitForTimeout(200);
        try {
            await this.validateAndClick(Selectors.postForms.createPageWithShortcode.closeWelcomeModal);
        } catch (error) {
            await this.navigateToURL(this.downloadsPage);
            //Validate Product Title
            await this.validateAndClick(Selectors.postForms.downloadsFormData.titleBE(DownloadsForm.title));
            await this.page.waitForTimeout(200);
            await this.validateAndClick(Selectors.postForms.createPageWithShortcode.closeWelcomeModal);
        }
        //Validate Product Price
        await this.assertionValidate(Selectors.postForms.downloadsFormData.price(DownloadsForm.regularPrice));
        //Validate Product Excerpt
        await this.assertionValidate(Selectors.postForms.downloadsFormData.excerpt(DownloadsForm.excerpt));
        //Validate Product Category
        await this.validateAndClick(Selectors.postForms.downloadsFormData.clickDownload);
        await this.page.waitForTimeout(300);
        await this.validateAndClick(Selectors.postForms.downloadsFormData.clickCategory);
        await this.assertionValidate(Selectors.postForms.downloadsFormData.categoryBE(DownloadsForm.category));
        //Validate Product Tags
        //await this.validateAndClick(Selectors.postForms.downloadsFormData.clickTag);
        //await this.assertionValidate(Selectors.postForms.downloadsFormData.tagBE(DownloadsForm.tags));
    }
}