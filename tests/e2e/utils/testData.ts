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
    userName: process.env.QA_NEW_USERNAME ? process.env.QA_NEW_USERNAME : 'Testuser0001',
    userEmail: process.env.QA_NEW_USEREMAIL ? process.env.QA_NEW_USEREMAIL : 'Testuser0001@yopmail.com',
    userFirstName: process.env.QA_NEW_FIRSTNAME ? process.env.QA_NEW_FIRSTNAME : 'Test',
    userLastName: process.env.QA_NEW_LASTNAME ? process.env.QA_NEW_LASTNAME : 'User',
    userPassword: process.env.QA_NEW_PASSWORD ? process.env.QA_NEW_PASSWORD : 'Testuser0001@yopmail.com',
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
    category: 'Technology',
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
    numeric: '',
    phoneNumber: '',
    addressLine1: '',
    addressLine2: '',
    city: 'Dhaka',
    zip: '1216',
    country: 'Bangladesh',
    state: 'Dhaka',
    googleMaps: '',
    embed: '',
    ratings: '',
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
    rfPassword: string;
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
    rfPassword: faker.internet.password()
};


/**------------------------------*/
/**-------Export DATA_SET-------*/
/**----------------------------*/
export {
    Urls,
    Users,
    PostForm,
    RegistrationForm,
};
