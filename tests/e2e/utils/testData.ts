import { faker } from '@faker-js/faker';
/**---------------------------------*/
/**-------DATA_SET: Base Url-------*/
/**-------------------------------*/
const Urls: {
    baseUrl: string;
} = {
    //Main Site URL
    baseUrl: process.env.QA_BASE_URL ? process.env.QA_BASE_URL : 'http://localhost:8889',
};



/**-----------------------------------*/
/**-------DATA_SET: Test Users-------*/
/**---------------------------------*/
const Users: {
    //Admin Credentials
    adminUsername: string;
    adminPassword: string;

    //New User Credentials
    userName: string;
    userEmail: string;
    userFirstName: string;
    userLastName: string;
    userPassword: string;
} = {
    //Admin Login
    adminUsername: process.env.QA_ADMIN_USERNAME ? process.env.QA_ADMIN_USERNAME : 'admin',
    adminPassword: process.env.QA_ADMIN_PASSWORD ? process.env.QA_ADMIN_PASSWORD : 'password',

    //New User Credentials
    userName: 'Testuser0001',
    userEmail: 'Testuser0001@yopmail.com',
    userFirstName: 'Test',
    userLastName: 'User',
    userPassword: 'Testuser0001@yopmail.com',
};

/**-----------------------------------*/
/**-------DATA_SET: Post Form-------*/
/**---------------------------------*/
const PostForm: {
    //Post Form Data
    formName: string;
    title: string;
    description: string;
    excerpt: string;
    featuredImage: string;
    category: string;
    tags: string;
    text: string;
    textarea: string;
    dropdown: string;
    multiSelect: string;
    radio: string;
    checkbox: string;
    date: string;
    websiteUrl: string;
    emailAddress: string;
    imageUpload: string;
    repeatField: string;
    time: string;
    uploadFile: string;
    countryList: string;
    numeric: string;
    phoneNumber: string;
    addressLine1: string;
    addressLine2: string;
    city: string;
    zip: string;
    country: string;
    state: string;
    googleMaps: string;
    embed: string;
    ratings: string;
} = {
    formName: '',
    title: '',
    description: '',
    excerpt: '',
    featuredImage: 'uploadeditems/sample_image.PNG',
    category: 'Science',
    tags: 'AI',
    text: '',
    textarea: '',
    dropdown: 'Option',
    multiSelect: 'Option',
    radio: 'Option',
    checkbox: 'Option',
    websiteUrl: '',
    emailAddress: '',
    imageUpload: 'uploadeditems/sample_image.PNG',
    repeatField: '',
    date: '20/08/2024',
    time: '12:00 pm',
    uploadFile: 'uploadeditems/sample_image.PNG',
    countryList: 'Bangladesh',
    numeric: '111',
    phoneNumber: '',
    addressLine1: '',
    addressLine2: '',
    city: 'Capital Dhaka',
    zip: '1216',
    country: 'Bangladesh',
    state: 'Dhaka',
    googleMaps: '',
    embed: '',
    ratings: '',
};

const ProductForm: {
    //Post Form Data
    formName: string;
    title: string;
    description: string;
    excerpt: string;
    productImage: string;
    tags: string;
    regularPrice: string;
    salePrice: string;
    imageGallery1: string;
    imageGallery2: string;
    catalogVisibility: string;
    purchaseNote: string;
    brand: string;
    category: string;
    tag: string;
    color: string;
    visibility: string;
    shippingClass: string;
    type: string;
} = {
    formName: '',
    title: 'iPhone 16 Pro Max',
    description: '',
    excerpt: '',
    productImage: 'uploadeditems/iPhone_16_pro_max_1.jpg',
    category: 'Electronics',
    tags: 'Smartphone',
    regularPrice: '100',
    salePrice: '90',
    imageGallery1: 'uploadeditems/iPhone_16_pro_max_2.jpeg',
    imageGallery2: 'uploadeditems/iPhone_16_pro_max_3.png',
    catalogVisibility: 'visible',
    purchaseNote: '',
    brand: 'Apple',
    tag: 'Smartphone',
    color: 'Red',
    visibility: 'featured',
    shippingClass: '-1',
    type: 'external',
};

const DownloadsForm: {
    //Post Form Data
    formName: string;
    title: string;
    description: string;
    excerpt: string;
    downloadsImage: string;
    downloadableFiles: string;
    tags: string;
    regularPrice: string;
    purchaseNote: string;
    category: string;
} = {
    formName: '',
    title: 'WP User Frontend',
    description: '',
    excerpt: '',
    downloadsImage: 'uploadeditems/wp-user-frontend.png',
    downloadableFiles: 'uploadeditems/wpuf.zip',
    category: 'plugins',
    tags: 'wpuf',
    regularPrice: '100',
    purchaseNote: '',
};

/**------------------------------------------*/
/**-------DATA_SET: Registration Form-------*/
/**----------------------------------------*/
const rfFirstName = faker.person.firstName();
const rfLastName = faker.person.lastName();

const RegistrationForm: {
    //Registration Form Title
    rfPostName1: string;
    rfPostName2: string;
    rfPostName3: string;
    rfPostName4: string;
    //Registration Form Data
    rfFirstName: string;
    rfLastName: string;
    rfUsername: string;
    rfEmail: string;
    rfDisplayName: string;
    rfNickname: string;
    rfWebsite: string;
    rfBiographicalInfo: string;
    rfAvatar: string;
    rfProfilePhoto: string;
    rfXtwitter: string;
    rfFacebook: string;
    rfLinkedIn: string;
    rfInstagram: string;
} = {
    //Registration Form Titles
    rfPostName1: faker.lorem.sentence(2),
    rfPostName2: faker.lorem.sentence(2),
    rfPostName3: faker.lorem.sentence(2),
    rfPostName4: faker.lorem.sentence(2),
    //Registration Form Data
    rfFirstName: rfFirstName,
    rfLastName: rfLastName,
    rfUsername: faker.internet.displayName(),
    rfEmail: faker.internet.email(),
    rfDisplayName: faker.internet.displayName(),
    rfNickname: faker.internet.displayName(),
    rfWebsite: faker.internet.url(),
    rfBiographicalInfo: faker.lorem.sentence(5),
    rfAvatar: 'uploadeditems/avatar.png',
    rfProfilePhoto: 'uploadeditems/profile_photo.jpg',
    rfXtwitter: '',
    rfFacebook: '',
    rfLinkedIn: '',
    rfInstagram: '',
};


/**------------------------------------------*/
/**-------DATA_SET: Vendor Registration Forms-------*/
/**----------------------------------------*/
const VendorRegistrationForm: {
    // Dokan Vendor Registration Form
    dokanVendorFormName: string;
    dokanVendorPageTitle: string;
    dokanVendorEmail: string;
    dokanVendorFirstName: string;
    dokanVendorLastName: string;
    dokanShopName: string;
    dokanVendorPassword: string;
    dokanShopUrl: string;
    dokanVendorStoreLogo: string;
    dokanVendorStoreBanner: string;
    dokanVendorPhone: string;
    dokanVendorStreet1Address: string;
    dokanVendorStreet2Address: string;
    dokanVendorCity: string;
    dokanVendorState: string;
    dokanVendorZip: string;
    dokanVendorCountry: string;
    dokanVendorGoogleMaps: string;

    // WC Vendors Registration Form
    wcVendorFormName: string;
    wcVendorPageTitle: string;
    wcVendorEmail: string;
    wcVendorpaypalName: string;
    wcVendorShopName: string;
    wcVendorSellerInfo: string;
    wcVendorShortDescription: string;
    wcVendorPassword: string;
    wcVendorConfirmPassword: string;

    // WCFM Membership Registration Form
    wcfmMemberFormName: string;
    wcfmMemberPageTitle: string;
    wcfmMemberEmail: string;
    wcfmMemberPassword: string;
    wcfmMemberWebsite: string;
    description: string;
    wcfmMemberPhone: string;
    wcfmMemberAddress: string;
    wcfmMemberAddress2: string;
    wcfmMemberCity: string;
    wcfmMemberState: string;
    wcfmMemberZip: string;
    wcfmMemberCountry: string;
    wcfmMemberStoreName: string;
    wcfmMemberStoreLogo: string;
    wcfmMemberStoreBanner: string;
    wcfmMemberFacebook: string;
    wcfmMemberTwitter: string;
    wcfmMemberGoogle: string;
    wcfmMemberLiknkedin: string;
    wcfmMemberYoutube: string;
    wcfmMemberInstagram: string;
} = {
    // Dokan Vendor Registration Form
    dokanVendorFormName: 'Dokan Vendor Registration Form',
    dokanVendorPageTitle: 'Reg Vendor',
    dokanVendorEmail: '',
    dokanVendorFirstName: '',
    dokanVendorLastName: '',
    dokanShopName: '',
    dokanVendorPassword: '',
    dokanShopUrl: '',
    dokanVendorStoreLogo: 'uploadeditems/store_logo.png',
    dokanVendorStoreBanner: 'uploadeditems/store_banner.png',
    dokanVendorPhone: '',
    dokanVendorStreet1Address: 'Mirpur',
    dokanVendorStreet2Address: 'DOHS',
    dokanVendorCity: 'Capital Dhaka',
    dokanVendorState: 'Dhaka',
    dokanVendorZip: '1216',
    dokanVendorCountry: 'Bangladesh',
    dokanVendorGoogleMaps: 'Dhaka, Bangladesh',

    // WC Vendors Registration Form
    wcVendorFormName: 'WC Vendors Registration Form',
    wcVendorPageTitle: 'Reg WC Vendor',
    wcVendorEmail: '',
    wcVendorpaypalName: '',
    wcVendorShopName: '',
    wcVendorSellerInfo: '',
    wcVendorShortDescription: '',
    wcVendorPassword: '',
    wcVendorConfirmPassword: '',

    // WCFM Membership Registration Form
    wcfmMemberFormName: 'WCFM Membership Registration Form',
    wcfmMemberPageTitle: 'Reg Member',
    wcfmMemberEmail: '',
    wcfmMemberPassword: '',
    wcfmMemberWebsite: '',
    description: '',
    wcfmMemberPhone: '',
    wcfmMemberAddress: 'Mirpur',
    wcfmMemberAddress2: 'DOHS',
    wcfmMemberCity: 'Capital Dhaka',
    wcfmMemberState: 'Dhaka',
    wcfmMemberZip: '1216',
    wcfmMemberCountry: 'Bangladesh',
    wcfmMemberStoreName: '',
    wcfmMemberStoreLogo: 'uploadeditems/wcfm_logo.jpg',
    wcfmMemberStoreBanner: 'uploadeditems/wcfm_banner.jpg',
    wcfmMemberFacebook: '',
    wcfmMemberTwitter: '',
    wcfmMemberGoogle: '',
    wcfmMemberLiknkedin: '',
    wcfmMemberYoutube: '',
    wcfmMemberInstagram: '',
};

/**------------------------------------------*/
/**-------DATA_SET: Field Options Settings-------*/
/**----------------------------------------*/

// Simplified field options data - one test per unique option
const FieldOptionsTestData: {
    // Basic field option tests
    fieldLabelTest: { label: string; expectedLabel: string };
    metaKeyTest: { metaKey: string; expectedMetaKey: string };
    helpTextTest: { helpText: string; expectedHelpText: string };
    placeholderTest: { placeholderText: string; expectedPlaceholder: string };
    defaultValueTest: { defaultValue: string; expectedDefault: string };
    requiredTest: { required: boolean; expectedRequired: boolean };
    cssClassTest: { cssClassName: string; expectedClass: string };
    fieldSizeTest: { fieldSize: string; expectedSize: string };
    readOnlyTest: { readOnly: boolean; expectedReadOnly: string };
    showDataInPostTest: { showDataInPost: boolean; expectedShowData: boolean };
    hideFieldLabelTest: { hideFieldLabel: boolean; expectedHideLabel: boolean };
    visibilityTest: { visibility: string; expectedVisibility: string };
    visibilityFrontendTest: { visibilityFrontend: string[]; expectedVisibilityFrontend: string []};
    
    // Dropdown specific tests
    dropdownOptionsTest: { 
        options1: { label: string; value: string; };
        options2: { label: string; value: string; };
        expectedOptions1: { label: string; value: string };
        expectedOptions2: { label: string; value: string };
    };
    categoryTypeTest: { type: Array<string>; expectedType: Array<string> };
    selectionType: { type: Array<string>; expectedType: Array<string> };
    
    inlineDisplayTest: { inline: boolean; expectedInline: boolean };
    
    // Checkbox specific tests
    checkboxOptionsTest: {
        options: Array<{ label: string; value: string; selected: boolean }>;
        expectedOptions: Array<{ label: string; value: string }>;
    };
    selectedByDefaultTest: { selectedByDefault: boolean; expectedSelected: boolean };
    
    // Textarea specific tests
    richTextTest: { richText: boolean; expectedRichText: boolean };
    
    // Numeric specific tests (PRO)
    minMaxValueTest: { minValue: number; maxValue: number; expectedMin: number; expectedMax: number };
    stepTest: { step: number; expectedStep: number };
    
    // Date specific tests (PRO)
    dateFormatTest: { dateFormat: string; expectedFormat: string };
    minDateTest: { minDate: string; expectedMinDate: string };
    maxDateTest: { maxDate: string; expectedMaxDate: string };
    timeFormatTest: { timeFormat: string; expectedTimeFormat: string };
    timeFieldInterval: { interval: string; expectedInterval: string };
    
    // Website URL field tests
    websiteUrlNewWindowTest: { openInNewWindow: boolean; expectedNewWindow: boolean };
    
    // File upload specific tests (PRO)
    fileTypeRestrictionsTest: { allowedTypes: string; expectedTypes: string };
    
    // Image upload specific tests
    imageSizeRestrictionsTest: { maxFileSize: number; expectedImageSize: number };
    buttonTextTest: { buttonText: string; expectedButtonText: string };
    
    // Address specific tests (PRO)
    addressComponentsTest: { 
        showAddressLine2: boolean; 
        showCity: boolean; 
        showState: boolean; 
        showZip: boolean; 
        showCountry: boolean;
        expectedComponents: Array<string>;
    };
    
    // Country list specific tests (PRO)
    countryPriorityTest: { priorityCountries: string; expectedPriority: Array<string> };
    
    // Google Map specific tests (PRO)
    mapSettingsTest: { 
        defaultLocation: string; 
        zoom: number; 
        showAddress: boolean;
        expectedLocation: string;
        expectedZoom: number;
        expectedShowAddress: boolean;
    };
    
    // Advanced tests (PRO)
    contentRestrictionTest: { 
        restrictionTypeMin: string; 
        restrictionTypeMax: string; 
        restrictionByChar: string; 
        restrictionByWord: string;
        length: number;
    };
    conditionalLogicTest: { 
        conditionalLogic: boolean;
    };
    
    // Additional field-specific tests
    characterLimitTest: { minChars: number; maxChars: number; expectedMinChars: number; expectedMaxChars: number };
    wordLimitTest: { minWords: number; maxWords: number; expectedMinWords: number; expectedMaxWords: number };
    selectTextTest: { selectText: string; expectedSelectText: string };
    clearSelectionTest: { clearSelection: boolean; expectedClearSelection: boolean };
    radioSelectedByDefaultTest: { selectedByDefault: string; expectedSelectedByDefault: string };
    checkboxInlineDisplayTest: { inline: boolean; expectedInline: boolean };
    checkboxSelectedByDefaultTest: { selectedByDefault: Array<string>; expectedSelected: Array<string> };
    minValueTest: { minValue: number; expectedMin: number };
    maxValueTest: { maxValue: number; expectedMax: number };
    isTimeFieldTest: { isTimeField: boolean; expectedIsTimeField: boolean };
    
    // File upload additional tests
    maxFilesTest: { maxFiles: number; expectedMaxFiles: number };
    
    // Image upload tests
    maxImageSizeTest: { maxImageSize: number; expectedMaxImageSize: number };
    imageButtonTextTest: { buttonText: string; expectedButtonText: string };
    
    // Phone field tests
    phoneFormatTest: { format: string; expectedFormat: string };
    defaultCountryTest: { defaultCountry: string; expectedCountry: string };
    hiddenCountryTest: { hiddenCountry: Array<string>; expectedHiddenCountry: Array<string> };
    onlyShowCountryTest: { onlyShowCountry: Array<string>; expectedOnlyShowCountry: Array<string> };
    
    // Address field tests
    showAddressLine2Test: { required: boolean; default: string; placeHolder: string; };
    
} = {
    // Basic field option tests
    fieldLabelTest: { 
        label: 'Custom Field Label Test', 
        expectedLabel: 'Custom Field Label Test' 
    },
    metaKeyTest: { 
        metaKey: 'custom_meta_key_test', 
        expectedMetaKey: 'custom_meta_key_test' 
    },
    helpTextTest: { 
        helpText: 'This is a help text for testing field options', 
        expectedHelpText: 'This is a help text for testing field options' 
    },
    placeholderTest: { 
        placeholderText: 'Enter your test value here...', 
        expectedPlaceholder: 'Enter your test value here...' 
    },
    defaultValueTest: { 
        defaultValue: 'Default test value', 
        expectedDefault: 'Default test value' 
    },
    requiredTest: { 
        required: true, 
        expectedRequired: true 
    },
    cssClassTest: { 
        cssClassName: 'custom-test-class field-option-test', 
        expectedClass: 'custom-test-class field-option-test' 
    },
    fieldSizeTest: { 
        fieldSize: 'small', 
        expectedSize: 'small' 
    },
    readOnlyTest: { 
        readOnly: true, 
        expectedReadOnly: 'disabled' 
    },
    showDataInPostTest: { 
        showDataInPost: false, 
        expectedShowData: false 
    },
    hideFieldLabelTest: { 
        hideFieldLabel: true, 
        expectedHideLabel: true 
    },
    visibilityTest: { 
        visibility: 'logged_in_only', 
        expectedVisibility: 'logged_in_only' 
    },
    visibilityFrontendTest: { 
        visibilityFrontend: ['hidden', 'subscription_only', 'logged_in_only'],
        expectedVisibilityFrontend: ['hidden', 'subscription_only', 'logged_in_only'] 
    },
    // Dropdown specific tests
    dropdownOptionsTest: { 
        options1: {label: 'Test 1', value: 'Test 10' },
        options2: { label: 'Test 2', value: 'Test 20' },
        expectedOptions1: { label: 'Test 1', value: 'Test 10' },
        expectedOptions2: { label: 'Test 2', value: 'Test 20' },
    },
    categoryTypeTest: { 
        type: ['text', 'checkbox', 'multiselect'], 
        expectedType: ['text', 'checkbox', 'multiselect'] 
    },

    selectionType: {
        type: ['exclude', 'include'],
        expectedType: ['exclude', 'include']
    },
    
    inlineDisplayTest: { 
        inline: true, 
        expectedInline: true 
    },
    
    // Checkbox specific tests
    checkboxOptionsTest: {
        options: [
            { label: 'Check One', value: 'check_1', selected: true },
            { label: 'Check Two', value: 'check_2', selected: false },
            { label: 'Check Three', value: 'check_3', selected: true }
        ],
        expectedOptions: [
            { label: 'Check One', value: 'check_1' },
            { label: 'Check Two', value: 'check_2' },
            { label: 'Check Three', value: 'check_3' }
        ]
    },
    selectedByDefaultTest: { 
        selectedByDefault: true, 
        expectedSelected: true 
    },
    
    // Textarea specific tests
    richTextTest: { 
        richText: true, 
        expectedRichText: true 
    },
    
    // Numeric specific tests (PRO)
    minMaxValueTest: { 
        minValue: 10, 
        maxValue: 100, 
        expectedMin: 10, 
        expectedMax: 100 
    },
    stepTest: { 
        step: 5, 
        expectedStep: 5 
    },
    
    // Date specific tests (PRO)
    dateFormatTest: { 
        dateFormat: 'mm/yy/dd', 
        expectedFormat: 'mm/yy/dd' 
    },
    minDateTest: { 
        minDate: '01/01/1950', 
        expectedMinDate: '01/01/1950' 
    },
    maxDateTest: { 
        maxDate: '31/12/2030', 
        expectedMaxDate: '31/12/2030' 
    },
    timeFormatTest: { 
        timeFormat: 'H:i', 
        expectedTimeFormat: 'H:i' 
    },
    timeFieldInterval: { 
        interval: '23',
        expectedInterval: '23' 
    },
    
    // Website URL field tests
    websiteUrlNewWindowTest: { 
        openInNewWindow: true, 
        expectedNewWindow: true 
    },
    
    // File upload specific tests (PRO)
        fileTypeRestrictionsTest: { 
        allowedTypes: 'pdf,doc,docx,txt', 
        expectedTypes: 'pdf,doc,docx,txt' 
    },
    
    // Image upload specific tests
    imageSizeRestrictionsTest: { 
        maxFileSize: 5, 
        expectedImageSize: 5 
    },
    buttonTextTest: { 
        buttonText: 'Upload Your Image', 
        expectedButtonText: 'Upload Your Image' 
    },
    
    // Address specific tests (PRO)
    addressComponentsTest: { 
        showAddressLine2: true, 
        showCity: true, 
        showState: true, 
        showZip: true, 
        showCountry: true,
        expectedComponents: ['Address Line 2', 'City', 'State', 'ZIP', 'Country']
    },
    
    // Country list specific tests (PRO)
    countryPriorityTest: { 
        priorityCountries: 'US,CA,GB,AU', 
        expectedPriority: ['US', 'CA', 'GB', 'AU'] 
    },
    
    // Google Map specific tests (PRO)
    mapSettingsTest: { 
        defaultLocation: 'New York, NY, USA', 
        zoom: 12, 
        showAddress: true,
        expectedLocation: 'New York, NY, USA',
        expectedZoom: 12,
        expectedShowAddress: true
    },
    
    // Advanced tests (PRO)
    contentRestrictionTest: { 
        restrictionTypeMin: 'min', 
        restrictionTypeMax: 'max', 
        restrictionByChar: 'character',
        restrictionByWord: 'word',
        length: 10,
    },
    conditionalLogicTest: { 
        conditionalLogic: true, 
    },
    
    // Additional field-specific tests
    characterLimitTest: { 
        minChars: 10, 
        maxChars: 100, 
        expectedMinChars: 10, 
        expectedMaxChars: 100 
    },
    wordLimitTest: { 
        minWords: 5, 
        maxWords: 50, 
        expectedMinWords: 5, 
        expectedMaxWords: 50 
    },
    selectTextTest: { 
        selectText: 'Choose', 
        expectedSelectText: 'Choose' 
    },
    clearSelectionTest: { 
        clearSelection: true, 
        expectedClearSelection: true 
    },
    radioSelectedByDefaultTest: { 
        selectedByDefault: 'option_2', 
        expectedSelectedByDefault: 'option_2' 
    },
    checkboxInlineDisplayTest: { 
        inline: true, 
        expectedInline: true 
    },
    checkboxSelectedByDefaultTest: { 
        selectedByDefault: ['check_1', 'check_3'], 
        expectedSelected: ['check_1', 'check_3'] 
    },
    minValueTest: { 
        minValue: 5, 
        expectedMin: 5 
    },
    maxValueTest: { 
        maxValue: 50, 
        expectedMax: 50 
    },
    isTimeFieldTest: { 
        isTimeField: true, 
        expectedIsTimeField: true 
    },
    
    // File upload additional tests
    maxFilesTest: { maxFiles: 2, expectedMaxFiles: 2 },
    
    // Image upload tests
    maxImageSizeTest: { maxImageSize: 5, expectedMaxImageSize: 5 },
    imageButtonTextTest: { buttonText: 'Just Image', expectedButtonText: 'Just Image' },
    
    // Phone field tests
    phoneFormatTest: { format: 'international', expectedFormat: 'international' },
    defaultCountryTest: { defaultCountry: 'BD', expectedCountry: 'BD' },
    hiddenCountryTest: { hiddenCountry: ['US', 'CA', 'GB'], expectedHiddenCountry: ['US', 'CA', 'GB'] },
    onlyShowCountryTest: { onlyShowCountry: ['US', 'CA', 'GB'], expectedOnlyShowCountry: ['US', 'CA', 'GB'] },
    
    // Address field tests
    showAddressLine2Test: { required: true, default: 'Add Line', placeHolder: 'Add More Details',   },
    
};

const FieldOptionsData: {
    
    // Common Field Options
    textFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        placeholderText: string;
        defaultValue: string;
        cssClassName: string;
        required: boolean;
        readOnly: boolean;
        fieldSize: string;
        showDataInPost: boolean;
        hideFieldLabel: boolean;
        visibility: string;
    };

    textareaFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        placeholderText: string;
        defaultValue: string;
        cssClassName: string;
        required: boolean;
        rows: number;
        cols: number;
        richText: boolean;
    };

    dropdownFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        multiple: boolean;
        options: Array<{
            label: string;
            value: string;
            selected: boolean;
        }>;
    };

    radioFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        inline: boolean;
        options: Array<{
            label: string;
            value: string;
            selected: boolean;
        }>;
    };

    checkboxFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        inline: boolean;
        options: Array<{
            label: string;
            value: string;
            selected: boolean;
        }>;
    };

    numericFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        placeholderText: string;
        defaultValue: string;
        cssClassName: string;
        required: boolean;
        minValue: number;
        maxValue: number;
        stepSize: number;
    };

    dateTimeFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        dateFormat: string;
        timeFormat: string;
        isTimeField: boolean;
    };

    fileUploadFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        allowedTypes: string;
        maxFileSize: number;
        maxFiles: number;
    };

    imageUploadFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        allowedTypes: string;
        maxFileSize: number;
    };

    phoneFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        placeholderText: string;
        cssClassName: string;
        required: boolean;
        format: string;
        defaultCountry: string;
    };

    addressFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        showAddressLine2: boolean;
        showCity: boolean;
        showState: boolean;
        showZip: boolean;
        showCountry: boolean;
    };

    googleMapFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        defaultLocation: string;
        zoom: number;
        showAddress: boolean;
    };

    countryListFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        selectType: string;
        priorityCountries: string;
    };

    reCaptchaFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        type: string;
        theme: string;
        size: string;
    };

    sectionBreakFieldOptions: {
        label: string;
        description: string;
        cssClassName: string;
    };

    customHtmlFieldOptions: {
        label: string;
        htmlContent: string;
        cssClassName: string;
    };

    termsConditionsFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        termsTitle: string;
        termsContent: string;
        showTermsTitle: boolean;
    };

    ratingsFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        maxRating: number;
        ratingText: string;
    };

    shortcodeFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        shortcodeContent: string;
    };

    actionHookFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        hookName: string;
    };

    embedFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        embedUrl: string;
        width: number;
        height: number;
    };

    repeatFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        columnCount: number;
        multiple: boolean;
    };

    columnFieldOptions: {
        label: string;
        columnCount: number;
        cssClassName: string;
    };

    qrCodeFieldOptions: {
        label: string;
        metaKey: string;
        cssClassName: string;
        qrText: string;
        qrSize: number;
    };

    stepStartFieldOptions: {
        stepTitle: string;
        stepDescription: string;
        cssClassName: string;
    };

    mathCaptchaFieldOptions: {
        label: string;
        metaKey: string;
        helpText: string;
        cssClassName: string;
        required: boolean;
        operationType: string;
    };
} = {
    
    // Common Field Options
    textFieldOptions: {
        label: 'Custom Text Field',
        metaKey: 'custom_text_field',
        helpText: 'Please enter your text here',
        placeholderText: 'Enter text...',
        defaultValue: 'Default text value',
        cssClassName: 'custom-text-class',
        required: true,
        readOnly: false,
        fieldSize: 'large',
        showDataInPost: true,
        hideFieldLabel: false,
        visibility: 'everyone',
    },

    textareaFieldOptions: {
        label: 'Custom Textarea Field',
        metaKey: 'custom_textarea_field',
        helpText: 'Please enter your detailed text here',
        placeholderText: 'Enter detailed text...',
        defaultValue: 'Default textarea value',
        cssClassName: 'custom-textarea-class',
        required: true,
        rows: 5,
        cols: 50,
        richText: false,
    },

    dropdownFieldOptions: {
        label: 'Custom Dropdown Field',
        metaKey: 'custom_dropdown_field',
        helpText: 'Please select an option',
        cssClassName: 'custom-dropdown-class',
        required: true,
        multiple: false,
        options: [
            { label: 'Option 1', value: 'option1', selected: false },
            { label: 'Option 2', value: 'option2', selected: true },
            { label: 'Option 3', value: 'option3', selected: false },
        ],
    },

    radioFieldOptions: {
        label: 'Custom Radio Field',
        metaKey: 'custom_radio_field',
        helpText: 'Please select one option',
        cssClassName: 'custom-radio-class',
        required: true,
        inline: true,
        options: [
            { label: 'Yes', value: 'yes', selected: false },
            { label: 'No', value: 'no', selected: true },
            { label: 'Maybe', value: 'maybe', selected: false },
        ],
    },

    checkboxFieldOptions: {
        label: 'Custom Checkbox Field',
        metaKey: 'custom_checkbox_field',
        helpText: 'Please select options',
        cssClassName: 'custom-checkbox-class',
        required: false,
        inline: false,
        options: [
            { label: 'Checkbox 1', value: 'check1', selected: true },
            { label: 'Checkbox 2', value: 'check2', selected: false },
            { label: 'Checkbox 3', value: 'check3', selected: true },
        ],
    },

    numericFieldOptions: {
        label: 'Custom Numeric Field',
        metaKey: 'custom_numeric_field',
        helpText: 'Please enter a number',
        placeholderText: 'Enter number...',
        defaultValue: '100',
        cssClassName: 'custom-numeric-class',
        required: true,
        minValue: 1,
        maxValue: 1000,
        stepSize: 1,
    },

    dateTimeFieldOptions: {
        label: 'Custom Date/Time Field',
        metaKey: 'custom_datetime_field',
        helpText: 'Please select date and time',
        cssClassName: 'custom-datetime-class',
        required: true,
        dateFormat: 'Y-m-d',
        timeFormat: 'H:i',
        isTimeField: true,
    },

    fileUploadFieldOptions: {
        label: 'Custom File Upload',
        metaKey: 'custom_file_upload',
        helpText: 'Please upload your file',
        cssClassName: 'custom-file-class',
        required: true,
        allowedTypes: 'pdf,doc,docx,txt',
        maxFileSize: 10,
        maxFiles: 3,
    },

    imageUploadFieldOptions: {
        label: 'Custom Image Upload',
        metaKey: 'custom_image_upload',
        helpText: 'Please upload your image',
        cssClassName: 'custom-image-class',
        required: true,
        allowedTypes: 'jpg,jpeg,png,gif',
        maxFileSize: 5,
    },

    phoneFieldOptions: {
        label: 'Custom Phone Field',
        metaKey: 'custom_phone_field',
        helpText: 'Please enter your phone number',
        placeholderText: 'Enter phone...',
        cssClassName: 'custom-phone-class',
        required: true,
        format: 'international',
        defaultCountry: 'US',
    },

    addressFieldOptions: {
        label: 'Custom Address Field',
        metaKey: 'custom_address_field',
        helpText: 'Please enter your address',
        cssClassName: 'custom-address-class',
        required: true,
        showAddressLine2: true,
        showCity: true,
        showState: true,
        showZip: true,
        showCountry: true,
    },

    googleMapFieldOptions: {
        label: 'Custom Google Map',
        metaKey: 'custom_google_map',
        helpText: 'Please select location',
        cssClassName: 'custom-map-class',
        required: false,
        defaultLocation: 'New York, NY',
        zoom: 15,
        showAddress: true,
    },

    countryListFieldOptions: {
        label: 'Custom Country List',
        metaKey: 'custom_country_list',
        helpText: 'Please select your country',
        cssClassName: 'custom-country-class',
        required: true,
        selectType: 'dropdown',
        priorityCountries: 'US,CA,GB',
    },

    reCaptchaFieldOptions: {
        label: 'Custom reCaptcha',
        metaKey: 'custom_recaptcha',
        helpText: 'Please verify you are human',
        cssClassName: 'custom-recaptcha-class',
        required: true,
        type: 'v2',
        theme: 'light',
        size: 'normal',
    },

    sectionBreakFieldOptions: {
        label: 'Custom Section Break',
        description: 'This is a custom section break for organizing the form',
        cssClassName: 'custom-section-class',
    },

    customHtmlFieldOptions: {
        label: 'Custom HTML Field',
        htmlContent: '<div class="custom-html"><p>This is custom HTML content</p></div>',
        cssClassName: 'custom-html-class',
    },

    termsConditionsFieldOptions: {
        label: 'Custom Terms & Conditions',
        metaKey: 'custom_terms_conditions',
        helpText: 'Please accept the terms',
        cssClassName: 'custom-terms-class',
        required: true,
        termsTitle: 'Terms and Conditions',
        termsContent: 'Please read and accept our terms and conditions...',
        showTermsTitle: true,
    },

    ratingsFieldOptions: {
        label: 'Custom Rating Field',
        metaKey: 'custom_rating_field',
        helpText: 'Please rate this',
        cssClassName: 'custom-rating-class',
        required: true,
        maxRating: 5,
        ratingText: 'Rate this item',
    },

    shortcodeFieldOptions: {
        label: 'Custom Shortcode',
        metaKey: 'custom_shortcode',
        helpText: 'This field displays shortcode content',
        cssClassName: 'custom-shortcode-class',
        shortcodeContent: '[wpuf_user_data field="display_name"]',
    },

    actionHookFieldOptions: {
        label: 'Custom Action Hook',
        metaKey: 'custom_action_hook',
        helpText: 'This field triggers custom actions',
        cssClassName: 'custom-hook-class',
        hookName: 'wpuf_custom_action_hook',
    },

    embedFieldOptions: {
        label: 'Custom Embed Field',
        metaKey: 'custom_embed_field',
        helpText: 'Please enter embed URL',
        cssClassName: 'custom-embed-class',
        embedUrl: 'https://www.youtube.com/watch?v=example',
        width: 560,
        height: 315,
    },

    repeatFieldOptions: {
        label: 'Custom Repeat Field',
        metaKey: 'custom_repeat_field',
        helpText: 'This field can be repeated',
        cssClassName: 'custom-repeat-class',
        required: false,
        columnCount: 2,
        multiple: true,
    },

    columnFieldOptions: {
        label: 'Custom Columns',
        columnCount: 3,
        cssClassName: 'custom-column-class',
    },

    qrCodeFieldOptions: {
        label: 'Custom QR Code',
        metaKey: 'custom_qr_code',
        cssClassName: 'custom-qr-class',
        qrText: 'https://example.com',
        qrSize: 200,
    },

    stepStartFieldOptions: {
        stepTitle: 'Step 1: Personal Information',
        stepDescription: 'Please fill in your personal details',
        cssClassName: 'custom-step-class',
    },

    mathCaptchaFieldOptions: {
        label: 'Custom Math Captcha',
        metaKey: 'custom_math_captcha',
        helpText: 'Please solve the math problem',
        cssClassName: 'custom-math-class',
        required: true,
        operationType: 'addition',
    },
};

/**------------------------------*/
/**-------Export DATA_SET-------*/
/**----------------------------*/
export {
    Urls,
    Users,
    PostForm,
    ProductForm,
    DownloadsForm,
    RegistrationForm,
    VendorRegistrationForm,
    FieldOptionsData,
    FieldOptionsTestData,
};
