import { faker } from '@faker-js/faker';
require('dotenv').config();



export interface Urls {
    baseUrl: string;
}
export interface Users {
    adminUsername: string;
    adminPassword: string;

    //New User Credentials
    userName: string;
    userEmail: string;
    userFirstName: string;
    userLastName: string;
    userPassword: string;
}
export interface PostForm {
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
}
export interface RegistrationForm {
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
}
export interface SubscriptionPack {
    //Post Form Title
    subscriptionPackName: string;
    subscriptionPackDescription: string;
    SubscriptionPackPrice: string;
    SubscriptionPackExpiration: number;
    subscriptionFeaturedCount: number;
}

export interface Data {
    urls: Urls;
    users: Users;
    postForms: PostForm;
    registrationForms: RegistrationForm;
    subscriptionPack: SubscriptionPack;
}




/********************************************/
/******* Generate Fake Data Function *******/
/******************************************/
//Function
const generateFakerData = () => {
    //Post Form Titles
    const pfPostName1: string = faker.lorem.sentence(2);
    const pfPostName2: string = faker.lorem.sentence(2);
    const pfPostName3: string = faker.lorem.sentence(2);
    const pfPostName4: string = faker.lorem.sentence(2);
    //Post Form Data
    const pfTitle: string = faker.lorem.words(2);
    const pfPostDescription: string = faker.lorem.sentence(4);
    const pfExcerpt: string = faker.lorem.word(3);
    const pfTags: string = faker.lorem.word();

    //-----------------------------------------------------------------//
    //Registration Form Titles
    const rfPostName1: string = faker.lorem.sentence(2);
    const rfPostName2: string = faker.lorem.sentence(2);
    const rfPostName3: string = faker.lorem.sentence(2);
    const rfPostName4: string = faker.lorem.sentence(2);
    //Registration Form Data
    //Generate random user details
    const rfFirstName: string = faker.name.firstName();
    const rfLastName: string = faker.name.lastName();
    const rfUsername: string = faker.internet.userName(rfFirstName, rfLastName);
    const rfEmail: string = faker.internet.email(rfFirstName, rfLastName);
    const rfPassword: string = faker.internet.password();

    return {
        //Post Form Titles
        pfPostName1,
        pfPostName2,
        pfPostName3,
        pfPostName4,
        //Post Form Data
        pfTitle,
        pfPostDescription,
        pfExcerpt,
        pfTags,

        //Reg Form Titles
        rfPostName1,
        rfPostName2,
        rfPostName3,
        rfPostName4,
        //Registration Form Data
        rfFirstName,
        rfLastName,
        rfUsername,
        rfEmail,
        rfPassword,
    };
};




/******************************/
/******* All Test Data *******/
/****************************/
//Data
export const testData: Data = {
    //Urls
    urls: {
        //Main URL
        baseUrl: process.env.QA_BASE_URL ? process.env.QA_BASE_URL : 'http://localhost:8889',
    },

    //Users
    users: {
        //Admin Login
        adminUsername: process.env.QA_ADMIN_USERNAME ? process.env.QA_ADMIN_USERNAME : 'admin',
        adminPassword: process.env.QA_ADMIN_PASSWORD ? process.env.QA_ADMIN_PASSWORD : 'password',

        //New User Credentials
        userName: process.env.QA_NEW_USERNAME ? process.env.QA_NEW_USERNAME : 'Testuser0001',
        userEmail: process.env.QA_NEW_USEREMAIL ? process.env.QA_NEW_USEREMAIL : 'Testuser0001@yopmail.com',
        userFirstName: process.env.QA_NEW_FIRSTNAME ? process.env.QA_NEW_FIRSTNAME : 'Test',
        userLastName: process.env.QA_NEW_LASTNAME ? process.env.QA_NEW_LASTNAME : 'User',
        userPassword: process.env.QA_NEW_PASSWORD ? process.env.QA_NEW_PASSWORD : 'Test@1234',
    },


    //Post Forms
    postForms: generateFakerData(),

    //Post Forms
    registrationForms: generateFakerData(),

    //
    subscriptionPack: {
        //Basics
        subscriptionPackName: faker.commerce.productName(),
        subscriptionPackDescription: faker.commerce.productDescription(),
        SubscriptionPackPrice: faker.commerce.price(),
        SubscriptionPackExpiration: faker.datatype.number({ min: 1, max: 365 }),

        //Posting Restrictions
        //Featured
        subscriptionFeaturedCount: 2,
    }

};
