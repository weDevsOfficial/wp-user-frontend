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
    pf_postName1: string;
    pf_postName2: string;
    pf_postName3: string;
    pf_postName4: string;

    
}

export interface RegistrationForm {
    //Registration Form Title
    rf_postName1: string;
    rf_postName2: string;
    rf_postName3: string;
    rf_postName4: string;

    
}

export interface Data {
    urls: Urls;
    users: Users;
    postForms: PostForm;
    registrationForms: RegistrationForm;
}

const generateFakerData = () => {
    //Post Form Titles
    const pf_postName1: string = faker.lorem.sentence(2);
    const pf_postName2: string = faker.lorem.sentence(2);
    const pf_postName3: string = faker.lorem.sentence(2);
    const pf_postName4: string = faker.lorem.sentence(2);

    //Reg Form Titles
    const rf_postName1: string = faker.lorem.sentence(2);
    const rf_postName2: string = faker.lorem.sentence(2);
    const rf_postName3: string = faker.lorem.sentence(2);
    const rf_postName4: string = faker.lorem.sentence(2);

    return {
        //Post Form Titles
        pf_postName1,
        pf_postName2,
        pf_postName3,
        pf_postName4,

        //Reg Form Titles
        rf_postName1,
        rf_postName2,
        rf_postName3,
        rf_postName4,
    };
};



/******************************/
/******* All Test Data *******/
/****************************/
export const testData: Data = {
    //Urls
    urls: {
        //Main URL
        baseUrl: process.env.QA_BASE_URL || 'http://localhost:8889/wp-admin/',
    },

    //Users
    users: {
        //Admin Login
        adminUsername: process.env.QA_ADMIN_USERNAME || 'admin',
        adminPassword: process.env.QA_ADMIN_PASSWORD || 'password',
    },


    //Post Forms
    postForms: generateFakerData(),

    //Post Forms
    registrationForms: generateFakerData(),


};
