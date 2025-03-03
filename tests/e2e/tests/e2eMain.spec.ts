import { test } from '@playwright/test';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTests from './postFormsTests.spec';
import registrationFormsTestsLite from './registrationFormsTestsLite.spec';
// import subscriptionsTests from './subscription.spec';
import * as fs from "fs"; //Clear Cookie
import resetWordpressSite from './resetWordpressSite.spec';



//*---------------------------------------------------*/
//*---------------------Reset WP---------------------*/
//*-------------------------------------------------*/
/**
 * @description
 *  Test suite that resets the WordPress site before running tests.
 * 
 * @details
 *  - This spec resets the @WordPress site using the @WP_RESET_PLUGIN (Must needed) before running any tests.
 *  - To use the reset functionality, uncomment the necessary lines in this file.
 * 
 * @cleanup
 *  - Clears cookies and origins before running the reset test.
 */

// fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
// test.describe(resetWordpressSite);





//*-----------------------------------------------------*/
//*---------------------MAIN Tests---------------------*/
//*---------------------------------------------------*/
/**
 * @description
 *  Test suite for various core functionalities of the application, including login, post forms, and registration forms.
 * 
 * @details
 *  This collection of tests covers the following key areas:
 *  - **Login and Setup**: Tests the login process and environment setup, ensuring the system is ready for further tests.
 *  - **Post Forms**: Tests the functionality of post forms, ensuring that form submissions work correctly on the front end.
 *  - **Registration Forms (Lite)**: Tests the registration forms and their interaction with the front-end in the Lite version of the plugin.
 * 
 * Each of these test suites performs key validations for the relevant forms and ensures that the application functions as expected in each scenario.
 * 
 * @cleanup
 *  - Clears cookies and origins before running any of the tests to maintain test isolation and avoid session interference.
 */

fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(loginAndSetupTests);

fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(postFormsTests);

fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(registrationFormsTestsLite);