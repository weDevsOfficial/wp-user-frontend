import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Selectors } from './selectors';
import { Base } from './base';
import { faker } from '@faker-js/faker';
import { PostForm, Urls } from '../utils/testData';
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
        //CreateNewPostForm
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        //Start
        //Click Add Form
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

        
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
        //CreateNewPostForm
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        //Start
        //Click Add Form
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

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
        //CreateNewPostForm
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickpostFormsMenuOption);
        await this.page.reload();
        //Start
        //Click Add Form
        await this.validateAndClick(Selectors.postForms.createBlankForm_PF.clickPostAddForm);

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

    /******* @Create Post > FrontEnd **********/

    async createPostFE() {
        //Go to Accounts page - FrontEnd
        await this.navigateToURL(this.wpufPostSubmitPage);

        //Post Form process
        //Enter Post Title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, PostForm.title=faker.word.words(2));
        console.log(PostForm.title);
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(PostForm.description=faker.lorem.sentence(1));
        console.log(PostForm.description);
        //Enter Excerpt
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, PostForm.excerpt=faker.lorem.sentence(1));
        console.log(PostForm.excerpt);
        //Add Featured Photo
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, PostForm.featuredImage);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.assertionValidate(Selectors.postForms.postFormsFrontendCreate.uploads('1'));
        //Select Category
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, { label: PostForm.category });
        //Enter Tags
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, PostForm.tags);
        //Enter Text
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTextFormsFE, PostForm.text=faker.lorem.sentence(1));
        console.log(PostForm.text);
        //Enter Textarea
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTextareaFormsFE, PostForm.textarea=faker.lorem.sentence(1));
        console.log(PostForm.textarea);
        //Enter Dropdown
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postDropdownFormsFE, { value: PostForm.dropdown });
        //Enter Multi Select
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postMultiSelectFormsFE, { value: PostForm.multiSelect });
        //Enter Radio
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postRadioFormsFE);
        //Enter Checkbox
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postCheckboxFormsFE);
        //Enter Website URL
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postWebsiteUrlFormsFE, PostForm.websiteUrl=faker.internet.url());
        console.log(PostForm.websiteUrl);
        //Enter Email Address
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postEmailAddressFormsFE, PostForm.emailAddress=faker.internet.email());
        console.log(PostForm.emailAddress);
        //Enter Image Upload
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.postImageUploadFormsFE, PostForm.imageUpload);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.assertionValidate(Selectors.postForms.postFormsFrontendCreate.uploads('2'));
        //Enter Repeat Field
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postRepeatFieldFormsFE, PostForm.repeatField=faker.word.words(1));
        console.log(PostForm.repeatField);
        //Enter Date / Time
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.dateTimeSelect);
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.selectYear, { value: '2024' });
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.selectMonth, { value: '7' });
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postDateTimeFormsFE.selectDay);
        //Enter Time Field
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postTimeFieldFormsFE, { value: PostForm.time });
        //Enter File Upload
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.postFileUploadFormsFE, PostForm.uploadFile);
        await this.page.waitForLoadState('domcontentloaded', { timeout: 10000 });
        await this.assertionValidate(Selectors.postForms.postFormsFrontendCreate.uploads('3'));
        //Enter Country List
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postCountryListFormsFE, { value: 'BD' });
        //Enter Numeric Field
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postNumericFieldFormsFE, PostForm.numeric);
        //Enter Phone Field
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postPhoneFieldFormsFE.countryContainer);
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postPhoneFieldFormsFE.countrySelect);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postPhoneFieldFormsFE.phoneNumber, PostForm.phoneNumber=`016${faker.string.numeric(8)}`);
        PostForm.phoneNumber='+88'+PostForm.phoneNumber;
        console.log(PostForm.phoneNumber);
        //Enter Address Field
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.addressLine1, PostForm.addressLine1=faker.location.streetAddress());
        console.log(PostForm.addressLine1);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.addressLine2, PostForm.addressLine2=faker.location.secondaryAddress());
        console.log(PostForm.addressLine2);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.city, PostForm.city);
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.zip, PostForm.zip);
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.country, { value: 'BD' });
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postAddressFieldFormsFE.state, { value: 'BD-13' });
        //Enter Google Maps
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postGoogleMapsFormsFE, PostForm.googleMaps='Dhaka, Bangladesh');
        await this.page.keyboard.press('Enter');
        //Enter Embed
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postEmbedFormsFE, PostForm.embed=faker.internet.url());
        console.log(PostForm.embed);
        //Enter Terms and Conditions
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.postTermsAndConditionsFormsFE);
        //Enter Ratings
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.postRatingsFormsFE, { value: PostForm.ratings='5' });
        console.log(PostForm.ratings);
        // Math Captcha
        const operand1 = await this.page.innerText(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.operand1);
        const operand2 = await this.page.innerText(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.operand2);
        const operator = await this.page.innerText(Selectors.postForms.postFormsFrontendCreate.postMathCaptchaFormsFE.operator);
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
        const validatePostSubmitted = await this.page.innerText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(PostForm.title));
        expect(validatePostSubmitted).toContain(PostForm.title);
    }

    //Validate Entered Data
    async validateEnteredData() {
        //Validate Post Title
        const postTitle = await this.page.innerText(Selectors.postForms.postFormData.title(PostForm.title));
        expect(postTitle).toContain(PostForm.title);
        console.log("Post Title Validated");
        //Validate Post Description
        const postDescription = await this.page.innerText(Selectors.postForms.postFormData.description(PostForm.description));
        expect(postDescription).toContain(PostForm.description);
        console.log("Post Description Validated");
        //Validate Featured Image
        expect(await this.page.isVisible(Selectors.postForms.postFormData.featuredImage)).toBeTruthy();
        console.log("Featured Image Validated");
        //Validate Category
        const postCategory = await this.page.innerText(Selectors.postForms.postFormData.category);
        expect(postCategory).toContain(PostForm.category);
        console.log("Category Validated");
        //Validate Tags
        const postTags = await this.page.innerText(Selectors.postForms.postFormData.tags);
        expect(postTags).toContain(PostForm.tags);
        console.log("Tags Validated");
        //Validate Text
        const postText = await this.page.innerText(Selectors.postForms.postFormData.text);
        expect(postText).toContain(PostForm.text);
        console.log("Text Validated");
        //Validate Textarea
        const postTextarea = await this.page.innerText(Selectors.postForms.postFormData.textarea);
        expect(postTextarea).toContain(PostForm.textarea);
        console.log("Textarea Validated");
        //Validate Dropdown
        const postDropdown = await this.page.innerText(Selectors.postForms.postFormData.dropdown);
        expect(postDropdown).toContain(PostForm.dropdown);
        console.log("Dropdown Validated");
        //Validate Multi Select
        const postMultiSelect = await this.page.innerText(Selectors.postForms.postFormData.multiSelect);
        expect(postMultiSelect).toContain(PostForm.multiSelect);
        console.log("Multi Select Validated");
        //Validate Radio
        const postRadio = await this.page.innerText(Selectors.postForms.postFormData.radio);
        expect(postRadio).toContain(PostForm.radio);
        console.log("Radio Validated");
        //Validate Checkbox
        const postCheckbox = await this.page.innerText(Selectors.postForms.postFormData.checkbox);
        expect(postCheckbox).toContain(PostForm.checkbox);
        console.log("Checkbox Validated");
        //Validate Website URL
        const postWebsiteUrl = await this.page.innerText(Selectors.postForms.postFormData.websiteUrl);
        expect(postWebsiteUrl).toContain(PostForm.websiteUrl);
        console.log("Website URL Validated");
        //Validate Email Address
        const postEmailAddress = await this.page.innerText(Selectors.postForms.postFormData.emailAddress);
        expect(postEmailAddress).toContain(PostForm.emailAddress);
        console.log("Email Address Validated");
        //Validate Image Upload
        expect(await this.page.isVisible(Selectors.postForms.postFormData.imageUpload)).toBe(true);
        console.log("Image Upload Validated");
        //Validate Repeat Field
        const postRepeatField = await this.page.innerText(Selectors.postForms.postFormData.repeatField(PostForm.repeatField));
        expect(postRepeatField).toContain(PostForm.repeatField);
        console.log("Repeat Field Validated");
        //Validate Date / Time
        const postDateTime = await this.page.innerText(Selectors.postForms.postFormData.dateTime(PostForm.date));
        expect(postDateTime).toContain(PostForm.date);
        console.log("Date / Time Validated");
        //Validate Time Field
        const postTimeField = await this.page.innerText(Selectors.postForms.postFormData.timeField(PostForm.time));
        expect(postTimeField).toContain(PostForm.time);
        console.log("Time Field Validated");
        //Validate File Upload
        expect(await this.page.isVisible(Selectors.postForms.postFormData.fileUpload)).toBe(true);
        console.log("File Upload Validated");
        //Validate Country List
        const postCountryList = await this.page.innerText(Selectors.postForms.postFormData.countryList(PostForm.countryList));
        expect(postCountryList).toContain(PostForm.countryList);
        console.log("Country List Validated");
        //Validate Numeric Field
        const postNumericField = await this.page.innerText(Selectors.postForms.postFormData.numericField);
        expect(postNumericField).toContain(PostForm.numeric);
        console.log("Numeric Field Validated");
        //Validate Phone Field
        const postPhoneField = await this.page.innerText(Selectors.postForms.postFormData.phoneField(PostForm.phoneNumber));
        expect(postPhoneField).toContain(PostForm.phoneNumber);
        console.log("Phone Field Validated");
        //Validate Address Line 1
        const postAddressLine1 = await this.page.innerText(Selectors.postForms.postFormData.addressLine1(PostForm.addressLine1));
        expect(postAddressLine1).toContain(PostForm.addressLine1);
        console.log("Address Line 1 Validated");
        //Validate Address Line 2
        const postAddressLine2 = await this.page.innerText(Selectors.postForms.postFormData.addressLine2(PostForm.addressLine2));
        expect(postAddressLine2).toContain(PostForm.addressLine2);
        console.log("Address Line 2 Validated");
        //Validate City
        const postCity = await this.page.innerText(Selectors.postForms.postFormData.city(PostForm.city));
        expect(postCity).toContain(PostForm.city);
        console.log("City Validated");
        //Validate Zip
        const postZip = await this.page.innerText(Selectors.postForms.postFormData.zip(PostForm.zip));
        expect(postZip).toContain(PostForm.zip);
        console.log("Zip Validated");
        //Validate Country
        const postCountry = await this.page.innerText(Selectors.postForms.postFormData.country(PostForm.country));
        expect(postCountry).toContain(PostForm.country);
        console.log("Country Validated");
        //Validate State
        const postState = await this.page.innerText(Selectors.postForms.postFormData.state(PostForm.state));
        expect(postState).toContain(PostForm.state);
        console.log("State Validated");
        //Validate Embed
        const postEmbed = await this.page.innerText(Selectors.postForms.postFormData.embed);
        expect(postEmbed).toContain(PostForm.embed);
        console.log("Embed Validated");
        //Validate Ratings
        const postRatings = await this.page.innerText(Selectors.postForms.postFormData.ratings);
        expect(postRatings).toContain(PostForm.ratings);
        console.log("Ratings Validated");
        await this.navigateToURL(this.accountPage);
        await this.validateAndClick(Selectors.logout.basicLogout.signOutButton);
        console.log("Signed Out");
    }

    //Create Page with Shortcode
    async createPageWithShortcode(shortcode: string, pageTitle: string) {
        //Go to Pages page
        await this.navigateToURL(this.pagesPage);
        //Create New Page
        await this.validateAndClick(Selectors.postForms.createPageWithShortcode.addNewPage);
        await this.page.waitForTimeout(300);
        // Check if the Welcome Modal is visible
        await this.page.click(Selectors.postForms.createPageWithShortcode.closeWelcomeModal);
        
        // Check if the Choose Pattern Modal is visible
        await this.validateAndClick(Selectors.postForms.createPageWithShortcode.closePatternModal);

        await this.validateAndFillStrings(Selectors.postForms.createPageWithShortcode.addPageTitle, pageTitle);
        //Click Add Block Button
        await this.validateAndClick(Selectors.postForms.createPageWithShortcode.blockAddButton);
        //Search and Add Shortcode block
        await this.validateAndFillStrings(Selectors.postForms.createPageWithShortcode.blockSearchBox, 'Shortcode');
        await this.validateAndClick(Selectors.postForms.createPageWithShortcode.addShortCodeBlock);
        //Enter Shortcode
        await this.validateAndFillStrings(Selectors.postForms.createPageWithShortcode.enterShortcode, shortcode);
        //Click Publish Page
        await this.validateAndClick(Selectors.postForms.createPageWithShortcode.clickPublishPage);
        //Confirm Publish
        await this.validateAndClick(Selectors.postForms.createPageWithShortcode.confirmPublish);
        //Validate Page Created
        await this.assertionValidate(Selectors.postForms.createPageWithShortcode.validatePageCreated);
    }

    async createGuestPostFE() {
        let guestName:string;
        let guestEmail:string;
        //Go to Accounts page - FrontEnd
        await this.navigateToURL(Urls.baseUrl + '/guestpostform/');

        //Post Form process
        //Enter Guest Name
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.guestName, guestName=faker.person.fullName());
        console.log(guestName);
        //Enter Guest Email
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.guestEmail, guestEmail=faker.internet.email());
        console.log(guestEmail);
        //Enter Post Title
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTitleFormsFE, PostForm.title=faker.word.words(2));
        console.log(PostForm.title);
        //Enter Post Description
        await this.page.frameLocator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE1)
            .locator(Selectors.postForms.postFormsFrontendCreate.postDescriptionFormsFE2).fill(PostForm.description=faker.lorem.sentence(1));
        console.log(PostForm.description);
        //Enter Excerpt
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postExcerptFormsFE, PostForm.excerpt=faker.lorem.sentence(1));
        console.log(PostForm.excerpt);
        //Add Featured Photo
        await this.page.setInputFiles(Selectors.postForms.postFormsFrontendCreate.featuredPhotoFormsFE, PostForm.featuredImage);
        await this.page.waitForTimeout(500);
        //Select Category
        await this.page.selectOption(Selectors.postForms.postFormsFrontendCreate.categorySelectionFormsFE, { label: PostForm.category });
        //Enter Tags
        await this.validateAndFillStrings(Selectors.postForms.postFormsFrontendCreate.postTagsFormsFE, PostForm.tags);
        //Create Post
        await this.validateAndClick(Selectors.postForms.postFormsFrontendCreate.submitPostFormsFE);
    }

    //Validate Post Created
    async validateGuestPostCreated() {
        //Validate Post Submitted
        const validatePostSubmitted = await this.page.innerText(Selectors.postForms.postFormsFrontendCreate.validatePostSubmitted(PostForm.title));
        expect(validatePostSubmitted).toContain(PostForm.title);
    }
}