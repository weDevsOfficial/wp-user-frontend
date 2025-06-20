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
    userPassword: process.env.QA_NEW_PASSWORD ? process.env.QA_NEW_PASSWORD : 'Test@1234',
};

/**----------------------------------*/
/**-------DATA_SET: Post Form-------*/
/**--------------------------------*/
const PostForm: {
    //Post Form Title
    pfPostName1: string;
    pfPostName2: string;
    pfPostName3: string;
    pfPostName4: string;
    //Post Form Data
    pfTitle: string;
    pfPostDescription: string;
    pfExcerpt: string;
    pfTags: string;
} = {
    //Post Form Titles
    pfPostName1: faker.lorem.sentence(1),
    pfPostName2: faker.lorem.sentence(1),
    pfPostName3: faker.lorem.sentence(1),
    pfPostName4: faker.lorem.sentence(1),
    //Post Form Data
    pfTitle: faker.lorem.words(1),
    pfPostDescription: faker.lorem.sentence(4),
    pfExcerpt: faker.lorem.word(3),
    pfTags: faker.lorem.word(),
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



/**------------------------------------------*/
/**-------DATA_SET: Subscription Pack-------*/
/**----------------------------------------*/
const SubscriptionPack: {
    //Basics
    subscriptionPackName: string;
    subscriptionPackDescription: string;
    SubscriptionPackPrice: string;
    SubscriptionPackExpiration: number;

    //Posting Restrictions
    //Featured
    subscriptionFeaturedCount: number;
} = {
    //Basics
    subscriptionPackName: faker.commerce.productName(),
    subscriptionPackDescription: faker.commerce.productDescription(),
    SubscriptionPackPrice: faker.commerce.price(),
    SubscriptionPackExpiration: faker.number.int({ min: 1, max: 365 }),

    //Posting Restrictions
    //Featured
    subscriptionFeaturedCount: 2,
};

/**------------------------------------------*/
/**-------DATA_SET: Tokens/API Keys--------*/
/**----------------------------------------*/
const Tokens: {
    WPUF_PRO_LICENSE_KEY?: string;
} = {
    WPUF_PRO_LICENSE_KEY: process.env.WPUF_PRO_LICENSE_KEY,
};



/**------------------------------*/
/**-------Export DATA_SET-------*/
/**----------------------------*/
export {
    Urls,
    Users,
    PostForm,
    RegistrationForm,
    SubscriptionPack,
    Tokens,
};
