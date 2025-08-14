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
    type: 'simple',
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
    rfXtwitter: faker.word.words(1),
    rfFacebook: faker.word.words(1),
    rfLinkedIn: faker.word.words(1),
    rfInstagram: faker.word.words(1),
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
    wcfmMemberStoreLogo: 'uploadeditems/store_logo.png',
    wcfmMemberStoreBanner: 'uploadeditems/store_banner.jpg',
    wcfmMemberFacebook: '',
    wcfmMemberTwitter: '',
    wcfmMemberGoogle: '',
    wcfmMemberLiknkedin: '',
    wcfmMemberYoutube: '',
    wcfmMemberInstagram: '',
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
};
