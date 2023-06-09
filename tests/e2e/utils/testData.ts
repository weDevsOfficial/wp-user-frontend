import { faker } from '@faker-js/faker';
require('dotenv').config();

export interface Urls {
    baseUrl: string;
}

export interface Users {
    adminUsername: string;
    adminPassword: string;
}

export interface PostForm {
    //Post Form Title
    pfPostName1: string;
    pfPostName2: string;
    pfPostName3: string;
    pfPostName4: string;

    
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

export interface Data {
    urls: Urls;
    users: Users;
    postForms: PostForm;
    registrationForms: RegistrationForm;
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

    //Registration Form Titles
    const rfPostName1: string = faker.lorem.sentence(2);
    const rfPostName2: string = faker.lorem.sentence(2);
    const rfPostName3: string = faker.lorem.sentence(2);
    const rfPostName4: string = faker.lorem.sentence(2);

    //Registration Form Data
    // Generate random user details
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
        baseUrl: process.env.QA_BASE_URL ? process.env.QA_BASE_URL: 'http://localhost:8889',
    },

    //Users
    users: {
        //Admin Login
        adminUsername: process.env.QA_ADMIN_USERNAME ? process.env.QA_ADMIN_USERNAME: 'admin',
        adminPassword: process.env.QA_ADMIN_PASSWORD ? process.env.QA_ADMIN_PASSWORD: 'password',
    },


    //Post Forms
    postForms: generateFakerData(),

    //Post Forms
    registrationForms: generateFakerData(),


};
