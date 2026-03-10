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
    //Product Form Data
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
    //Downloads Form Data
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

// Field options test data - only includes properties that are actually used in tests
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
    visibilityFrontendTest: { visibilityFrontend: string[]; expectedVisibilityFrontend: string[] };
    
    // Dropdown specific tests
    dropdownOptionsTest: { 
        options1: { label: string; value: string };
        options2: { label: string; value: string };
        expectedOptions1: { label: string; value: string };
        expectedOptions2: { label: string; value: string };
    };
    categoryTypeTest: { type: Array<string>; expectedType: Array<string> };
    selectionType: { type: Array<string>; expectedType: Array<string> };
    selectTextTest: { selectText: string; expectedSelectText: string };
    
    // Textarea specific tests
    richTextTest: { richText: boolean; expectedRichText: boolean };
    
    // Numeric specific tests (PRO)
    stepTest: { step: number; expectedStep: number };
    minValueTest: { minValue: number; expectedMin: number };
    maxValueTest: { maxValue: number; expectedMax: number };
    
    // Date specific tests (PRO)
    dateFormatTest: { dateFormat: string; expectedFormat: string };
    minDateTest: { minDate: string; expectedMinDate: string };
    maxDateTest: { maxDate: string; expectedMaxDate: string };
    timeFieldInterval: { interval: string; expectedInterval: string };
    
    // Website URL field tests
    websiteUrlNewWindowTest: { openInNewWindow: boolean; expectedNewWindow: boolean };
    
    // File upload tests
    maxFilesTest: { maxFiles: number; expectedMaxFiles: number };
    
    // Image upload tests
    maxImageSizeTest: { maxImageSize: number; expectedMaxImageSize: number };
    imageButtonTextTest: { buttonText: string; expectedButtonText: string };
    
    // Phone field tests
    defaultCountryTest: { defaultCountry: string; expectedCountry: string };
    hiddenCountryTest: { hiddenCountry: Array<string>; expectedHiddenCountry: Array<string> };
    onlyShowCountryTest: { onlyShowCountry: Array<string>; expectedOnlyShowCountry: Array<string> };
    
    // Address field tests
    showAddressLine2Test: { required: boolean; default: string; placeHolder: string };
    
    // Advanced tests (PRO)
    contentRestrictionTest: { 
        restrictionTypeMin: string; 
        restrictionTypeMax: string; 
        restrictionByChar: string; 
        restrictionByWord: string;
        length: number;
    };
    conditionalLogicTest: { conditionalLogic: boolean };
    
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
        options1: { label: 'Test 1', value: 'Test 10' },
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
    selectTextTest: { 
        selectText: 'Choose', 
        expectedSelectText: 'Choose' 
    },
    
    // Textarea specific tests
    richTextTest: { 
        richText: true, 
        expectedRichText: true 
    },
    
    // Numeric specific tests (PRO)
    stepTest: { 
        step: 5, 
        expectedStep: 5 
    },
    minValueTest: { 
        minValue: 5, 
        expectedMin: 5 
    },
    maxValueTest: { 
        maxValue: 50, 
        expectedMax: 50 
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
    timeFieldInterval: { 
        interval: '23',
        expectedInterval: '23' 
    },
    
    // Website URL field tests
    websiteUrlNewWindowTest: { 
        openInNewWindow: true, 
        expectedNewWindow: true 
    },
    
    // File upload tests
    maxFilesTest: { maxFiles: 2, expectedMaxFiles: 2 },
    
    // Image upload tests
    maxImageSizeTest: { maxImageSize: 5, expectedMaxImageSize: 5 },
    imageButtonTextTest: { buttonText: 'Just Image', expectedButtonText: 'Just Image' },
    
    // Phone field tests
    defaultCountryTest: { defaultCountry: 'BD', expectedCountry: 'BD' },
    hiddenCountryTest: { hiddenCountry: ['US', 'CA', 'GB'], expectedHiddenCountry: ['US', 'CA', 'GB'] },
    onlyShowCountryTest: { onlyShowCountry: ['US', 'CA', 'GB'], expectedOnlyShowCountry: ['US', 'CA', 'GB'] },
    
    // Address field tests
    showAddressLine2Test: { required: true, default: 'Add Line', placeHolder: 'Add More Details' },
    
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
    FieldOptionsTestData,
};
