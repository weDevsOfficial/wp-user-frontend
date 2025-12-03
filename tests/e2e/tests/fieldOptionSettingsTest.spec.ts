import { Browser, BrowserContext, Page, test, chromium } from "@playwright/test";
import { faker } from '@faker-js/faker';
import { FieldOptionSettingsPage } from '../pages/fieldOptionSettings';
import { BasicLoginPage } from '../pages/basicLogin';
import { Users, FieldOptionsTestData } from '../utils/testData';
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

test.describe('Field Options Settings Tests', () => {

    configureSpecFailFast();

    /**----------------------------------FIELD OPTIONS SETTINGS----------------------------------**
     *
     * @TestScenario : [Complete Field Options Configuration and Validation - All 61+ Options]
     * @Test_FOS0001 : Admin is creating a test form for field options testing
     * @Test_FOS0002 : Admin is configuring Field Label option
     * @Test_FOS0003 : Admin is validating Field Label option in frontend
     * @Test_FOS0004 : Admin is configuring Meta Key option
     * @Test_FOS0005 : Admin is validating Meta Key option in backend
     * @Test_FOS0006 : Admin is configuring Help Text option
     * @Test_FOS0007 : Admin is validating Help Text option in frontend
     * @Test_FOS0008 : Admin is configuring Placeholder Text option
     * @Test_FOS0009 : Admin is validating Placeholder Text option in frontend
     * @Test_FOS0010 : Admin is configuring Default Value option
     * @Test_FOS0011 : Admin is validating Default Value option in frontend
     * @Test_FOS0012 : Admin is configuring Required field option
     * @Test_FOS0013 : Admin is validating Required field option in frontend
     * @Test_FOS0014 : Admin is configuring CSS Class Name option
     * @Test_FOS0015 : Admin is validating CSS Class Name option in frontend
     * @Test_FOS0016 : Admin is configuring Field Size option
     * @Test_FOS0017 : Admin is validating Field Size option in frontend
     * @Test_FOS0018 : Admin is configuring Read Only option
     * @Test_FOS0019 : Admin is validating Read Only option in frontend
     * @Test_FOS0020 : Admin is configuring Show Data in Post option
     * @Test_FOS0021 : Admin is validating Show Data in Post option in backend
     * @Test_FOS0022 : Admin is configuring Hide Field Label option
     * @Test_FOS0023 : Admin is validating Hide Field Label option in frontend
     * @Test_FOS0024 : Admin is configuring Visibility option
     * @Test_FOS0025 : Admin is validating Visibility option in frontend
     * @Test_FOS0026 : Admin is configuring Content Restriction option for min character length
     * @Test_FOS0027 : Admin is validating Content Restriction option for min character length in FE
     * @Test_FOS0028 : Admin is configuring Content Restriction option for max character length
     * @Test_FOS0029 : Admin is validating Content Restriction option for max character length in FE
     * @Test_FOS0030 : Admin is configuring Content Restriction option for min word length
     * @Test_FOS0031 : Admin is validating Content Restriction option for min word length in FE
     * @Test_FOS0032 : Admin is configuring Content Restriction option for max word length
     * @Test_FOS0033 : Admin is validating Content Restriction option for max word length in FE
     * @Test_FOS0034 : Admin is configuring Conditional Logic option
     * @Test_FOS0035 : Admin is validating Conditional Logic option in frontend
     * @Test_FOS0036 : Admin is configuring Rich Text Editor option
     * @Test_FOS0037 : Admin is validating Rich Text Editor option in frontend
     * @Test_FOS0038 : Admin is configuring Dropdown Options
     * @Test_FOS0039 : Admin is validating Dropdown Options in frontend
     * @Test_FOS0040 : Admin is configuring Select Text option
     * @Test_FOS0041 : Admin is validating Select Text option in frontend
     * @Test_FOS0042 : Admin is configuring Category type option - text
     * @Test_FOS0043 : Admin is validating Category type option - text
     * @Test_FOS0044 : Admin is configuring Category type option - checkbox
     * @Test_FOS0045 : Admin is validating Category type option - checkbox
     * @Test_FOS0046 : Admin is configuring Category type option - multiselect
     * @Test_FOS0047 : Admin is validating Category type option - multiselect
     * @Test_FOS0048 : Admin is configuring selection type option - exclude
     * @Test_FOS0049 : Admin is validating selection type option - exclude
     * @Test_FOS0050 : Admin is configuring selection type option - include
     * @Test_FOS0051 : Admin is validating selection type option - include
     * @Test_FOS0052 : Admin is configuring Show in inline list Options
     * @Test_FOS0053 : Admin is validating in line list Options in frontend
     * @Test_FOS0054 : Admin is configuring Time Format option and interval
     * @Test_FOS0055 : Admin is validating Time Format option and interval in frontend
     * @Test_FOS0056 : Admin is configuring Max Files number
     * @Test_FOS0057 : Admin is validating Max Files option in frontend
     * @Test_FOS0058 : Admin is configuring Max Image Size option
     * @Test_FOS0059 : Admin is validating Max Image Size option in frontend
     * @Test_FOS0060 : Admin is configuring Button Text option (using Image Upload field)
     * @Test_FOS0061 : Admin is validating Button Text option in frontend
     * @Test_FOS0062 : Admin is configuring Default Country option
     * @Test_FOS0063 : Admin is validating Default Country option in frontend
     * @Test_FOS0064 : Admin is configuring Hide Countries option
     * @Test_FOS0065 : Admin is validating Hide Countries option in frontend
     * @Test_FOS0066 : Admin is configuring Only Show Countries option
     * @Test_FOS0067 : Admin is validating Only Show Countries option in frontend
     * @Test_FOS0068 : Admin is configuring Show Address Line 2 required option
     * @Test_FOS0069 : Admin is validating Show Address Line 2 required option in frontend
     * @Test_FOS0070 : Admin is configuring Show Address Line 2 default option
     * @Test_FOS0071 : Admin is validating Show Address Line 2 default option in frontend
     * @Test_FOS0072 : Admin is configuring Show Address Line 2 placeholder option
     * @Test_FOS0073 : Admin is validating Show Address Line 2 placeholder option in frontend
     * @Test_FOS0074 : Admin is configuring Show Icons option
     * @Test_FOS0075 : Admin is validating Show Icons option in frontend
     * @Test_FOS0076 : Admin is configuring Min Value option
     * @Test_FOS0077 : Admin is configuring Max Value option
     * @Test_FOS0078 : Admin is configuring Step option
     * @Test_FOS0079 : Admin is validating Numeric value options in frontend
     * @Test_FOS0080 : Admin is validating Max Value option in frontend
     * @Test_FOS0081 : Admin is validating Step option in frontend
     * @Test_FOS0082 : Admin is creating another test form for other field options testing
     * @Test_FOS0083 : Admin is configuring Date Format option
     * @Test_FOS0084 : Admin is configuring Enable Time Input option
     * @Test_FOS0085 : Admin is configuring Min Date range option
     * @Test_FOS0086 : Admin is configuring Max Date range option
     * @Test_FOS0087 : Admin is configuring Publish Time Field option
     * @Test_FOS0088 : Admin is validating Date Format option in frontend
     * @Test_FOS0089 : Admin is validating Time Input option in frontend
     * @Test_FOS0090 : Admin is validating Min Date range option in frontend
     * @Test_FOS0091 : Admin is validating Max Date range option in frontend
     * @Test_FOS0092 : Admin is validating Publish Time option in frontend
     * @Test_FOS0093 : Admin is creating another test form for other field options testing
     * @Test_FOS0094 : Admin is configuring Hide Field Label option
     * @Test_FOS0095 : Admin is configuring Show Data in Post option
     * @Test_FOS0096 : Admin is validating Hide Field Label option in frontend
     * @Test_FOS0097 : Admin is validating Hide Data in Post option in frontend
     * @Test_FOS0098 : Admin is configuring Visibility option hidden
     * @Test_FOS0099 : Admin is validating Visibility option, hidden, in frontend
     * @Test_FOS0100 : Admin is configuring Visibility option subscription only
     * @Test_FOS0101 : Admin is validating Visibility option, subscription only, in frontend
     * @Test_FOS0102 : Admin is configuring Visibility option logged in only
     * @Test_FOS0103 : Admin is validating Visibility option, logged in only, in frontend
     * @Test_FOS0104 : Admin is configuring Open in New Window option
     * @Test_FOS0105 : Admin is validating Open in New Window option in frontend
     * 
     */

    let formName: string;
    let formId: string;

    test('FOS0001 : Admin is creating a test form for field options testing', { tag: ['@Lite'] }, async () => {
        formName = faker.word.words(2);
        await test.step("Login as an admin for accessing dashboard", async () => {
            await new BasicLoginPage(page).basicLogin(Users.adminUsername, Users.adminPassword);
        })
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Create a new test form for field options testing", async () => {
            await fieldOptionsPage.createTestForm(formName);
        })
        await test.step("Add FOS fields", async () => {
            await fieldOptionsPage.addFOSFields();
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
        })
        await test.step("Get form id", async () => {
            formId = await fieldOptionsPage.getFormId();
        })
    });

    // Configuration Tests
    test('FOS0002 : Admin is configuring Field Label option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure field label", async () => {
            await fieldOptionsPage.configureFieldLabel(FieldOptionsTestData.fieldLabelTest.label);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
        })
    });

    test('FOS0003 : Admin is validating Field Label option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate field label in frontend", async () => {
            await fieldOptionsPage.validateFieldLabel(FieldOptionsTestData.fieldLabelTest.expectedLabel);
            console.log('✅ Field Label option validated successfully in frontend');
        })
    });

    test('FOS0004 : Admin is configuring Meta Key option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure meta key", async () => {
            await fieldOptionsPage.configureMetaKey(FieldOptionsTestData.metaKeyTest.metaKey);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Meta Key option configured successfully');
        })
    });

    test('FOS0005 : Admin is validating Meta Key option in backend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Validate meta key in backend", async () => {
            await fieldOptionsPage.validateMetaKey(FieldOptionsTestData.metaKeyTest.expectedMetaKey);
            console.log('✅ Meta Key option validated successfully in backend');
        })
    });

    test('FOS0006 : Admin is configuring Help Text option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure help text", async () => {
            await fieldOptionsPage.configureHelpText(FieldOptionsTestData.helpTextTest.helpText);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Help Text option configured successfully');
        })
    });

    test('FOS0007 : Admin is validating Help Text option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate help text in frontend", async () => {
            await fieldOptionsPage.validateHelpText(FieldOptionsTestData.helpTextTest.expectedHelpText);
            console.log('✅ Help Text option validated successfully in frontend');
        })
    });

    test('FOS0008 : Admin is configuring Placeholder Text option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure placeholder text", async () => {
            await fieldOptionsPage.configurePlaceholderText(FieldOptionsTestData.placeholderTest.placeholderText);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Placeholder Text option configured successfully');
        })
    });

    test('FOS0009 : Admin is validating Placeholder Text option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate placeholder text in frontend", async () => {
            await fieldOptionsPage.validatePlaceholderText(FieldOptionsTestData.placeholderTest.expectedPlaceholder);
            console.log('✅ Placeholder Text option validated successfully in frontend');
        })
    });

    test('FOS0010 : Admin is configuring Default Value option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure default value", async () => {
            await fieldOptionsPage.configureDefaultValue(FieldOptionsTestData.defaultValueTest.defaultValue);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Default Value option configured successfully');
        })
    });

    test('FOS0011 : Admin is validating Default Value option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate default value in frontend", async () => {
            await fieldOptionsPage.validateDefaultValue(FieldOptionsTestData.fieldLabelTest.expectedLabel, FieldOptionsTestData.defaultValueTest.expectedDefault);
            console.log('✅ Default Value option validated successfully in frontend');
        })
    });

    test('FOS0012 : Admin is configuring Required field option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure required field", async () => {
            await fieldOptionsPage.configureRequired(FieldOptionsTestData.requiredTest.required);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Required field option configured successfully');
        })
    });

    test('FOS0013 : Admin is validating Required field option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate required field in frontend", async () => {
            await fieldOptionsPage.validateRequiredField(FieldOptionsTestData.fieldLabelTest.expectedLabel);
            console.log('✅ Required field option validated successfully in frontend');
        })
    });

    test('FOS0014 : Admin is configuring CSS Class Name option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure CSS class name", async () => {
            await fieldOptionsPage.configureCssClassName(FieldOptionsTestData.cssClassTest.cssClassName);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ CSS Class Name option configured successfully');
        })
    });

    test('FOS0015 : Admin is validating CSS Class Name option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate CSS class in frontend", async () => {
            await fieldOptionsPage.validateCssClass(FieldOptionsTestData.fieldLabelTest.expectedLabel, FieldOptionsTestData.cssClassTest.expectedClass);
            console.log('✅ CSS Class Name option validated successfully in frontend');
        })
    });

    test('FOS0016 : Admin is configuring Field Size option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure field size", async () => {
            await fieldOptionsPage.configureFieldSize(FieldOptionsTestData.fieldSizeTest.fieldSize);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Field Size option configured successfully');
        })
    });

    test('FOS0017 : Admin is validating Field Size option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Validate field size in frontend", async () => {
            await fieldOptionsPage.validateFieldSize(FieldOptionsTestData.fieldSizeTest.expectedSize);
            console.log('✅ Field Size option validated successfully in frontend');
        })
    });

    test('FOS0018 : Admin is configuring Read Only option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit embed field options", async () => {
            await fieldOptionsPage.editFieldOptions('embed');
        })
        await test.step("Configure read only", async () => {
            await fieldOptionsPage.configureReadOnly(FieldOptionsTestData.readOnlyTest.readOnly);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Read Only option configured successfully');
        })
    });

    test('FOS0019 : Admin is validating Read Only option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate read only in frontend", async () => {
            await fieldOptionsPage.validateReadOnly('embed', FieldOptionsTestData.readOnlyTest.expectedReadOnly);
            console.log('✅ Read Only option validated successfully in frontend');
        })
    });

    test('FOS0020 : Admin is configuring Show Data in Post option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure show data in post", async () => {
            await fieldOptionsPage.configureShowDataInPost(FieldOptionsTestData.showDataInPostTest.showDataInPost);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Show Data in Post option configured successfully');
        })
    });

    test('FOS0021 : Admin is validating Show Data in Post option in backend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Validate show data in post in backend", async () => {
            await fieldOptionsPage.validateShowDataInPost(FieldOptionsTestData.showDataInPostTest.expectedShowData);
            console.log('✅ Show Data in Post option validated successfully in backend');
        })
    });

    test('FOS0022 : Admin is configuring Hide Field Label option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure hide field label", async () => {
            await fieldOptionsPage.configureHideFieldLabel(FieldOptionsTestData.hideFieldLabelTest.hideFieldLabel);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Hide Field Label option configured successfully');
        })
    });

    test('FOS0023 : Admin is validating Hide Field Label option in backend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Validate hide field label in backend", async () => {
            await fieldOptionsPage.validateHideFieldLabel(FieldOptionsTestData.hideFieldLabelTest.expectedHideLabel);
            console.log('✅ Hide Field Label option validated successfully in backend');
        })
    });

    test('FOS0024 : Admin is configuring Visibility option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure visibility for logged in user only", async () => {
            await fieldOptionsPage.configureVisibility(FieldOptionsTestData.visibilityTest.visibility);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Visibility option configured successfully');
        })
    });

    test('FOS0025 : Admin is validating Visibility option in backend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Validate visibility in backend", async () => {
            await fieldOptionsPage.validateVisibility(FieldOptionsTestData.visibilityTest.expectedVisibility);
            console.log('✅ Visibility option validated successfully in backend');
        })
    });

    test('FOS0026 : Admin is configuring Content Restriction option for min character length', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('textarea');
        })
        await test.step("Configure content restriction", async () => {
            await fieldOptionsPage.configureContentRestriction(FieldOptionsTestData.contentRestrictionTest.restrictionTypeMin, FieldOptionsTestData.contentRestrictionTest.restrictionByChar, FieldOptionsTestData.contentRestrictionTest.length);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Content Restriction option configured successfully');
        })
    });

    test('FOS0027 : Admin is validating Content Restriction option for min character length in FE', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        //await page.pause();
        await test.step("Validate content restriction in frontend for min character", async () => {
            await fieldOptionsPage.validateContentRestrictionMinChar();
            console.log('✅ Content Restriction option validated successfully in frontend minimum character length');
        })
    });

    test('FOS0028 : Admin is configuring Content Restriction option for max character length', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('textarea');
        })
        await test.step("Configure content restriction", async () => {
            await fieldOptionsPage.configureContentRestriction(FieldOptionsTestData.contentRestrictionTest.restrictionTypeMax, FieldOptionsTestData.contentRestrictionTest.restrictionByChar, FieldOptionsTestData.contentRestrictionTest.length);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Content Restriction option configured successfully');
        })
    });

    test('FOS0029 : Admin is validating Content Restriction option for max character length in FE', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate content restriction in frontend for max character", async () => {
            await fieldOptionsPage.validateContentRestrictionMaxChar();
            console.log('✅ Content Restriction option validated successfully in frontend maximum character length');
        })
    });

    test('FOS0030 : Admin is configuring Content Restriction option for min word length', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('textarea');
        })
        await test.step("Configure content restriction", async () => {
            await fieldOptionsPage.configureContentRestriction(FieldOptionsTestData.contentRestrictionTest.restrictionTypeMin, FieldOptionsTestData.contentRestrictionTest.restrictionByWord, FieldOptionsTestData.contentRestrictionTest.length);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Content Restriction option configured successfully');
        })
    });

    test('FOS0031 : Admin is validating Content Restriction option for min word length in FE', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate content restriction in frontend for min character", async () => {
            await fieldOptionsPage.validateContentRestrictionMinWord();
            console.log('✅ Content Restriction option validated successfully in frontend minimum word length');
        })
    });

    test('FOS0032 : Admin is configuring Content Restriction option for max word length', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('textarea');
        })
        await test.step("Configure content restriction", async () => {
            await fieldOptionsPage.configureContentRestriction(FieldOptionsTestData.contentRestrictionTest.restrictionTypeMax, FieldOptionsTestData.contentRestrictionTest.restrictionByWord, FieldOptionsTestData.contentRestrictionTest.length);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Content Restriction option configured successfully');
        })
    });

    test('FOS0033 : Admin is validating Content Restriction option for max word length in FE', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate content restriction in frontend for max word", async () => {
            await fieldOptionsPage.validateContentRestrictionMaxWord();
            console.log('✅ Content Restriction option validated successfully in frontend minimum character length');
        })
    });

    test('FOS0034 : Admin is configuring Conditional Logic option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('text');
        })
        await test.step("Configure conditional logic", async () => {
            await fieldOptionsPage.configureConditionalLogic(FieldOptionsTestData.conditionalLogicTest.conditionalLogic);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Conditional Logic option configured successfully');
        })
    });

    test('FOS0035 : Admin is validating Conditional Logic option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate conditional logic in frontend", async () => {
            await fieldOptionsPage.validateConditionalLogic();
            console.log('✅ Conditional Logic option validated successfully in frontend');
        })
    });

    // === TEXTAREA FIELD OPTIONS ===

    test('FOS0036 : Admin is configuring Rich Text Editor option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit textarea field options", async () => {
            await fieldOptionsPage.editFieldOptions('textarea');
        })
        await test.step("Configure rich text editor", async () => {
            await fieldOptionsPage.configureRichText(FieldOptionsTestData.richTextTest.richText);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Rich Text Editor option configured successfully');
        })
    });

    test('FOS0037 : Admin is validating Rich Text Editor option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit text field options", async () => {
            await fieldOptionsPage.editFieldOptions('textarea');
        })
        await test.step("Validate rich text editor in frontend", async () => {
            await fieldOptionsPage.validateRichText(FieldOptionsTestData.richTextTest.expectedRichText);
            console.log('✅ Rich Text Editor option validated successfully in frontend');
        })
    });

    // === DROPDOWN FIELD OPTIONS ===

    test('FOS0038 : Admin is configuring Dropdown Options', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('dropdown');
        })
        await test.step("Configure dropdown options", async () => {
            await fieldOptionsPage.configureDropdownOptions(FieldOptionsTestData.dropdownOptionsTest.options1, FieldOptionsTestData.dropdownOptionsTest.options2);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Dropdown Options configured successfully');
        })
    });

    test('FOS0039 : Admin is validating Dropdown Options in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate dropdown options in frontend", async () => {
            await fieldOptionsPage.validateDropdownOptions(FieldOptionsTestData.dropdownOptionsTest.expectedOptions1, FieldOptionsTestData.dropdownOptionsTest.expectedOptions2);
            console.log('✅ Options, Label & Values, clear selection validated successfully in frontend');
        })
    });

    test('FOS0040 : Admin is configuring Select Text option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('dropdown');
        })
        await test.step("Configure select text", async () => {
            await fieldOptionsPage.configureSelectText(FieldOptionsTestData.selectTextTest.selectText);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Select Text option configured successfully');
        })
    });

    test('FOS0041 : Admin is validating Select Text option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate select text in frontend", async () => {
            await fieldOptionsPage.validateSelectText('dropdown', FieldOptionsTestData.selectTextTest.expectedSelectText);
            console.log('✅ Select Text option validated successfully in frontend');
        })
    });

    test('FOS0042 : Admin is configuring Category type option - text', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('taxonomy');
        })
        await test.step("Configure category type - text", async () => {
            await fieldOptionsPage.configureCategoryType(FieldOptionsTestData.categoryTypeTest.type[0]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Category Type option configured successfully');
        })
    });

    test('FOS0043 : Admin is validating Category type option - text', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate category type in frontend - text", async () => {
            await fieldOptionsPage.validateCategorytypeSelection(FieldOptionsTestData.categoryTypeTest.expectedType[0]);
            console.log('✅ Category Type Selection option validated successfully in frontend');
        })
    });

    test('FOS0044 : Admin is configuring Category type option - checkbox', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('taxonomy');
        })
        await test.step("Configure category type - checkbox", async () => {
            await fieldOptionsPage.configureCategoryType(FieldOptionsTestData.categoryTypeTest.type[1]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Category Type option configured successfully');
        })
    });

    test('FOS0045 : Admin is validating Category type option - checkbox', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate category type in frontend - checkbox", async () => {
            await fieldOptionsPage.validateCategorytypeSelection(FieldOptionsTestData.categoryTypeTest.expectedType[1]);
            console.log('✅ Category Type Selection option validated successfully in frontend');
        })
    });

    test('FOS0046 : Admin is configuring Category type option - multiselect', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('taxonomy');
        })
        await test.step("Configure category type - multiselect", async () => {
            await fieldOptionsPage.configureCategoryType(FieldOptionsTestData.categoryTypeTest.type[2]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Category Type option configured successfully');
        })
    });

    test('FOS0047 : Admin is validating Category type option - multiselect', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate category type in frontend - multiselect", async () => {
            await fieldOptionsPage.validateCategorytypeSelection(FieldOptionsTestData.categoryTypeTest.expectedType[2]);
            console.log('✅ Category Type Selection option validated successfully in frontend');
        })
    });

    test('FOS0048 : Admin is configuring selection type option - exclude', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('taxonomy');
        })
        await test.step("Configure categoty selection type - exclude", async () => {
            await fieldOptionsPage.configureCategoryType_Terms(FieldOptionsTestData.selectionType.type[0]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Category Type option configured successfully');
        })
    });

    test('FOS0049 : Admin is validating selection type option - exclude', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate category selection type in frontend - exclude", async () => {
            await fieldOptionsPage.validateSelectionType(["uncategorized", "music", "science"], FieldOptionsTestData.selectionType.type[0]);
            console.log('✅ Category Type Selection option validated successfully in frontend');
        })
    });

    test('FOS0050 : Admin is configuring selection type option - include', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit dropdown field options", async () => {
            await fieldOptionsPage.editFieldOptions('taxonomy');
        })
        await test.step("Configure category selection type - include", async () => {
            await fieldOptionsPage.configureCategoryType_Terms(FieldOptionsTestData.selectionType.type[1]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Category Type option configured successfully');
        })
    });

    test('FOS0051 : Admin is validating selection type option - include', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate category selection type in frontend - include", async () => {
            await fieldOptionsPage.validateSelectionType(["uncategorized", "music", "science"], FieldOptionsTestData.selectionType.type[1]);
            console.log('✅ Category Type Selection option validated successfully in frontend');
        })
    });

    // === In line list FIELD OPTIONS ===

    test('FOS0052 : Admin is configuring Show in inline list Options', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit Show in inline list field option", async () => {
            await fieldOptionsPage.editFieldOptions('radio');
        })
        await test.step("Configure in line list options", async () => {
            await fieldOptionsPage.configureInLineListOptions();
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Radio Options configured successfully');
        })
    });

    test('FOS0053 : Admin is validating in line list Options in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate in line list options in frontend", async () => {
            await fieldOptionsPage.validateInLineListOptions();
            console.log('✅ Radio Options validated successfully in frontend');
        })
    });

    test('FOS0054 : Admin is configuring Time Format option and interval', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit date field options", async () => {
            await fieldOptionsPage.editFieldOptions('time_field');
        })
        await test.step("Configure time format and interval", async () => {
            await fieldOptionsPage.configureTimeField(FieldOptionsTestData.timeFieldInterval.interval);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Time Format option configured successfully');
        })
    });

    test('FOS0055 : Admin is validating Time Format and interval in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate time format and interval in frontend", async () => {
            await fieldOptionsPage.validateTimeInterval(FieldOptionsTestData.timeFieldInterval.expectedInterval);
            console.log('✅ Time Format option validated successfully in frontend');
        })
    });

    // === FILE UPLOAD OPTIONS (PRO) ===

    test('FOS0056 : Admin is configuring Max Files number', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit file upload field options", async () => {
            await fieldOptionsPage.editFieldOptions('file_upload');
        })
        await test.step("Configure max files", async () => {
            await fieldOptionsPage.configureMaxFiles(FieldOptionsTestData.maxFilesTest.maxFiles);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Max Files option configured successfully');
        })
    });

    test('FOS0057 : Admin is validating Max Files option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate max files in frontend", async () => {
            await fieldOptionsPage.validateMaxFiles(FieldOptionsTestData.maxFilesTest.expectedMaxFiles);
            console.log('✅ Max Files option validated successfully in frontend');
        })
    });


    // === IMAGE UPLOAD OPTIONS ===

    test('FOS0058 : Admin is configuring Max Image Size option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit image upload field options", async () => {
            await fieldOptionsPage.editFieldOptions('image_upload');
        })
        await test.step("Configure max image size", async () => {
            await fieldOptionsPage.configureMaxImageSize(FieldOptionsTestData.maxImageSizeTest.maxImageSize);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Max Image Size option configured successfully');
        })
    });

    test('FOS0059 : Admin is validating Max Image Size option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate max image size in frontend", async () => {
            await fieldOptionsPage.validateMaxImageSize(FieldOptionsTestData.maxImageSizeTest.expectedMaxImageSize);
            console.log('✅ Max Image Size option validated successfully in frontend');
        })
    });

    test('FOS0060 : Admin is configuring Button Text option (using Image Upload field)', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit image upload field options", async () => {
            await fieldOptionsPage.editFieldOptions('image_upload');
        })
        await test.step("Configure image upload button text", async () => {
            await fieldOptionsPage.configureImageUploadButtonText(FieldOptionsTestData.imageButtonTextTest.buttonText);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Button Text option configured successfully');
        })
    });

    test('FOS0061 : Admin is validating Button Text option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate image upload button text in frontend", async () => {
            await fieldOptionsPage.validateImageUploadButtonText('image_upload', FieldOptionsTestData.imageButtonTextTest.expectedButtonText);
            console.log('✅ Button Text option validated successfully in frontend');
        })
    });

    test('FOS0062 : Admin is configuring Default Country option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit country field options", async () => {
            await fieldOptionsPage.editFieldOptions('country_list');
        })
        await test.step("Configure default country", async () => {
            await fieldOptionsPage.configureDefaultCountry(FieldOptionsTestData.defaultCountryTest.defaultCountry);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Default Country option configured successfully');
        })
    });

    test('FOS0063 : Admin is validating Default Country option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate default country in frontend", async () => {
            await fieldOptionsPage.validateDefaultCountry(FieldOptionsTestData.defaultCountryTest.expectedCountry);
            console.log('✅ Default Country option validated successfully in frontend');
        })
    });

    test('FOS0064 : Admin is configuring Hide Countries option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit country field options", async () => {
            await fieldOptionsPage.editFieldOptions('country_list');
        })
        await test.step("Configure default country", async () => {
            await fieldOptionsPage.configureHiddenCountry(FieldOptionsTestData.hiddenCountryTest.hiddenCountry);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Default Country option configured successfully');
        })
    });

    test('FOS0065 : Admin is validating Default Country option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate default country in frontend", async () => {
            await fieldOptionsPage.validateHiddenCountry(FieldOptionsTestData.hiddenCountryTest.expectedHiddenCountry);
            console.log('✅ Default Country option validated successfully in frontend');
        })
    });

    test('FOS0066 : Admin is configuring Only Show Countries option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit phone field options", async () => {
            await fieldOptionsPage.editFieldOptions('country_list');
        })
        await test.step("Configure only show countries", async () => {
            await fieldOptionsPage.configureOnlyShowCountry(FieldOptionsTestData.onlyShowCountryTest.onlyShowCountry);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Default Country option configured successfully');
        })
    });

    test('FOS0067 : Admin is validating Only Show Countries option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate only show countries in frontend", async () => {
            await fieldOptionsPage.validateOnlyShowCountry(FieldOptionsTestData.onlyShowCountryTest.expectedOnlyShowCountry);
            console.log('✅ Default Country option validated successfully in frontend');
        })
    });

    // === ADDRESS FIELD OPTIONS (PRO) ===

    test('FOS0068 : Admin is configuring Show Address Line 2 required option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit address field options", async () => {
            await fieldOptionsPage.editFieldOptions('address');
        })
        await test.step("Configure show address line 2", async () => {
            await fieldOptionsPage.configureShowAddressLine2Required(FieldOptionsTestData.showAddressLine2Test.required);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Show Address Line 2 option configured successfully');
        })
    });

    test('FOS0069 : Admin is validating Show Address Line 2 option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate show address line 2 in frontend", async () => {
            await fieldOptionsPage.validateShowAddressLine2Required(FieldOptionsTestData.showAddressLine2Test.required);
            console.log('✅ Show Address Line 2 option validated successfully in frontend');
        })
    });

    test('FOS0070 : Admin is configuring Show Address Line 2 default option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit address field options", async () => {
            await fieldOptionsPage.editFieldOptions('address');
        })
        await test.step("Configure show address line 2", async () => {
            await fieldOptionsPage.configureShowAddressLine2Default(FieldOptionsTestData.showAddressLine2Test.default);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Show Address Line 2 option configured successfully');
        })
    });

    test('FOS0071 : Admin is validating Show Address Line 2 default option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate show address line 2 in frontend", async () => {
            await fieldOptionsPage.validateShowAddressLine2Default(FieldOptionsTestData.showAddressLine2Test.default);
            console.log('✅ Show Address Line 2 option validated successfully in frontend');
        })
    });

    test('FOS0072 : Admin is configuring Show Address Line 2 place holder option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit address field options", async () => {
            await fieldOptionsPage.editFieldOptions('address');
        })
        await test.step("Configure show address line 2", async () => {
            await fieldOptionsPage.configureShowAddressLine2PlaceHolder(FieldOptionsTestData.showAddressLine2Test.placeHolder);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Show Address Line 2 option configured successfully');
        })
    });

    test('FOS0073 : Admin is validating Show Address Line 2 place holder option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate show address line 2 in frontend", async () => {
            await fieldOptionsPage.validateShowAddressLine2PlaceHolder(FieldOptionsTestData.showAddressLine2Test.placeHolder);
            console.log('✅ Show Address Line 2 option validated successfully in frontend');
        })
    });

    test('FOS0074 : Admin is configuring Show Icons option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit terms & conditions field options", async () => {
            await fieldOptionsPage.editFieldOptions('email');
        })
        await test.step("Configure show Icons", async () => {
            await fieldOptionsPage.configureShowIcons();
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Show Icons option configured successfully');
        })
    });

    test('FOS0075 : Admin is validating Show Icons option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate show Icons in frontend", async () => {
            await fieldOptionsPage.validateShowIcons();
            console.log('✅ Show Icons option validated successfully in frontend');
        })
    });

    // === NUMERIC FIELD OPTIONS (PRO) ===

    test('FOS0076 : Admin is configuring Min Value option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit numeric field options", async () => {
            await fieldOptionsPage.editFieldOptions('numeric');
        })
        await test.step("Configure min value", async () => {
            await fieldOptionsPage.configureMinValue(FieldOptionsTestData.minValueTest.minValue);
        })
    });

    test('FOS0077 : Admin is configuring Max Value option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure max value", async () => {
            await fieldOptionsPage.configureMaxValue(FieldOptionsTestData.maxValueTest.maxValue);
        })
    });

    test('FOS0078 : Admin is configuring Step option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure step size", async () => {
            await fieldOptionsPage.configureStep(FieldOptionsTestData.stepTest.step);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Step Size option configured successfully');
        })
    });

    test('FOS0079 : Admin is validating Numeric value options in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate min value in frontend", async () => {
            await fieldOptionsPage.validateMinValue(FieldOptionsTestData.minValueTest.expectedMin);
            console.log('✅ Min Value option validated successfully in frontend');
        })
    });

    test('FOS0080 : Admin is validating Max Value option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate max value in frontend", async () => {
            await fieldOptionsPage.validateMaxValue(FieldOptionsTestData.maxValueTest.expectedMax);
            console.log('✅ Max Value option validated successfully in frontend');
        })
    });

    test('FOS0081 : Admin is validating Step option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate step size in frontend", async () => {
            await fieldOptionsPage.validateStep(FieldOptionsTestData.stepTest.expectedStep);
            console.log('✅ Step option validated successfully in frontend');
        })
    });

    //--------------------------------------------------------------------------------------------------
    test('FOS0082 : Admin is creating another test form for other field options testing', { tag: ['@Lite'] }, async () => {
        formName = faker.word.words(2);
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Create a new test form for field options testing", async () => {
            await fieldOptionsPage.createTestForm(formName);
        })
        await test.step("Add more FOS fields", async () => {
            await fieldOptionsPage.addMoreFOSFields();
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
        })
        await test.step("Get form id", async () => {
            formId = await fieldOptionsPage.getFormId();
        })
    });

    // === DATE/TIME FIELD OPTIONS (PRO) ===

    test('FOS0083 : Admin is configuring Date Format option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit date field options", async () => {
            await fieldOptionsPage.editFieldOptions('date');
        })
        await test.step("Configure date format", async () => {
            await fieldOptionsPage.configureDateFormat(FieldOptionsTestData.dateFormatTest.dateFormat);
        })
    });

    test('FOS0084 : Admin is configuring Enable Time Input option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure time format", async () => {
            await fieldOptionsPage.enableTimeInput();
        })
    });

    test('FOS0085 : Admin is configuring Min Date range option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure time format", async () => {
            await fieldOptionsPage.configureMinDate(FieldOptionsTestData.minDateTest.minDate);
        })
    });
    test('FOS0086 : Admin is configuring Max Date range option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure time format", async () => {
            await fieldOptionsPage.configureMaxDate(FieldOptionsTestData.maxDateTest.maxDate);
        })
    });

    test('FOS0087 : Admin is configuring Publish Time Field option', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure publish time field", async () => {
            await fieldOptionsPage.configureAsPublishTime();
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Is Time Field option configured successfully');
        })
    });

    test('FOS0088 : Admin is validating Date Format option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);

        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate date format in frontend", async () => {
            await fieldOptionsPage.validateDateFormat(FieldOptionsTestData.dateFormatTest.expectedFormat);
            console.log('✅ Date Format option validated successfully in frontend');
        })
    });

    test('FOS0089 : Admin is validating Time Input option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate time input in frontend", async () => {
            await fieldOptionsPage.validateTimeInput(FieldOptionsTestData.dateFormatTest.expectedFormat);
            console.log('✅ Time Input option validated successfully in frontend');
        })
    });

    test('FOS0090 : Admin is validating Min Date range option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate min date range in frontend", async () => {
            await fieldOptionsPage.validateMinDate(FieldOptionsTestData.minDateTest.expectedMinDate);
            console.log('✅ Min Date range option validated successfully in frontend');
        })
    });

    test('FOS0091 : Admin is validating Max Date range option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate max date range in frontend", async () => {
            await fieldOptionsPage.validateMaxDate(FieldOptionsTestData.maxDateTest.expectedMaxDate);
            console.log('✅ Max Date range option validated successfully in frontend');
        })
    });

    test('FOS0092 : Admin is validating Publish Time option in frontend', { tag: ['@Pro'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate publish time in frontend", async () => {
            await fieldOptionsPage.validatePublishTime();
            console.log('✅ Publish Time option validated successfully in frontend');
        })
    });

    //--------------------------------------------------------------------------------------------------
    test('FOS0093 : Admin is creating another test form for other field options testing', { tag: ['@Lite'] }, async () => {
        formName = faker.word.words(2);
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Create a new test form for field options testing", async () => {
            await fieldOptionsPage.createTestForm(formName);
        })
        await test.step("Add FOS fields again", async () => {
            await fieldOptionsPage.addFOSFieldsAgain();
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
        })
        await test.step("Get form id", async () => {
            formId = await fieldOptionsPage.getFormId();
        })
    });

    /**----------------------------------WEBSITE URL FIELD OPTIONS----------------------------------**/

    test('FOS0094 : Admin is configuring Hide Field Label option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit Website URL field options", async () => {
            await fieldOptionsPage.editFieldOptions('website_url');
        })
        await test.step("Configure hide field label", async () => {
            await fieldOptionsPage.configureHideFieldLabel(FieldOptionsTestData.hideFieldLabelTest.hideFieldLabel);
            console.log('✅ Hide Field Label option configured');
        })
    });

    test('FOS0095 : Admin is configuring Show Data in Post option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Configure show data in post", async () => {
            await fieldOptionsPage.configureShowDataInPost(FieldOptionsTestData.showDataInPostTest.showDataInPost);
            console.log('✅ Show Data in Post option configured');
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Visibility option configured and form saved');
        })
    });

    test('FOS0096 : Admin is validating Hide Field Label option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate hide field label configuration", async () => {
            await fieldOptionsPage.validateHiddenFieldLabel(FieldOptionsTestData.hideFieldLabelTest.expectedHideLabel);
            console.log('✅ Hide field label option validated successfully');
        })
    });

    test('FOS0097 : Admin is validating Hide Data in Post option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Validate hide data in post configuration", async () => {
            await fieldOptionsPage.validateHideDataInPost(FieldOptionsTestData.showDataInPostTest.expectedShowData);
            console.log('✅ Hide data in post option validated successfully');
        })
    });

    test('FOS0098 : Admin is configuring Visibility option hidden', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit Website URL field options", async () => {
            await fieldOptionsPage.editFieldOptions('website_url');
        })
        await test.step("Configure visibility", async () => {
            await fieldOptionsPage.configureVisibilityFrontend(FieldOptionsTestData.visibilityFrontendTest.visibilityFrontend[0]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Visibility option configured and form saved');
        })
    });

    test('FOS0099 : Admin is validating Visibility option, hidden, in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate visibility configuration", async () => {
            await fieldOptionsPage.validateVisibilityFrontend(FieldOptionsTestData.visibilityFrontendTest.expectedVisibilityFrontend[0]);
            console.log('✅ Visibility option validated in frontend');
        })
    });

    test('FOS0100 : Admin is configuring Visibility option subscription only', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit Website URL field options", async () => {
            await fieldOptionsPage.editFieldOptions('website_url');
        })
        await test.step("Configure visibility", async () => {
            await fieldOptionsPage.configureVisibilityFrontend(FieldOptionsTestData.visibilityFrontendTest.visibilityFrontend[1]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Visibility option configured and form saved');
        })
    });

    test('FOS0101 : Admin is validating Visibility option, subscription only, in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate visibility configuration", async () => {
            await fieldOptionsPage.validateVisibilityFrontend(FieldOptionsTestData.visibilityFrontendTest.expectedVisibilityFrontend[1]);
            console.log('✅ Visibility option validated in frontend');
        })
    });

    test('FOS0102 : Admin is configuring Visibility option logged in only', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit Website URL field options", async () => {
            await fieldOptionsPage.editFieldOptions('website_url');
        })
        await test.step("Configure visibility", async () => {
            await fieldOptionsPage.configureVisibilityFrontend(FieldOptionsTestData.visibilityFrontendTest.visibilityFrontend[2]);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Visibility option configured and form saved');
        })
    });

    test('FOS0103 : Admin is validating Visibility option, logged in only, in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate visibility configuration", async () => {
            await fieldOptionsPage.validateVisibilityFrontend(FieldOptionsTestData.visibilityFrontendTest.expectedVisibilityFrontend[2]);
            console.log('✅ Visibility option validated in frontend');
        })
    });

    test('FOS0104 : Admin is configuring Open in New Window option', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Access form", async () => {
            await fieldOptionsPage.accessForm(formId, formName);
        })
        await test.step("Edit Website URL field options", async () => {
            await fieldOptionsPage.editFieldOptions('website_url');
        })
        await test.step("Configure open in new window", async () => {
            await fieldOptionsPage.configureOpenInNewWindow(FieldOptionsTestData.websiteUrlNewWindowTest.openInNewWindow);
            console.log('✅ Open in New Window option configured');
        })
        await test.step("Configure show data in post", async () => {
            await fieldOptionsPage.configureShowDataInPost(true);
        })
        await test.step("Save form", async () => {
            await fieldOptionsPage.saveForm();
            console.log('✅ Visibility option configured and form saved');
        })
    });

    test('FOS0105 : Admin is validating Open in New Window option in frontend', { tag: ['@Lite'] }, async () => {
        const fieldOptionsPage = new FieldOptionSettingsPage(page);
        await test.step("Preview form", async () => {
            await fieldOptionsPage.previewForm(formId);
        })
        await test.step("Validate open in new window configuration", async () => {
            await fieldOptionsPage.validateOpenInNewWindow(FieldOptionsTestData.websiteUrlNewWindowTest.expectedNewWindow);
            console.log('✅ Open in new window option validated in frontend');
        })
    });

});

test.afterAll(async () => {
    // Close the browser
    await browser.close();
});
