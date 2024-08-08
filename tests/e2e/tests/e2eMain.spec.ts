import { test, expect, Page } from '@playwright/test';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTests from './postFormsTests.spec';
import registrationFormsTestsLite from './registrationFormsTestsLite.spec';
import subscriptionsTests from './subscription.spec';
import * as fs from "fs"; //Clear Cookie
import resetWordpressSite from './resetWordpressSite.spec';



//!----------------------------------------------------------///
//!------Run ONLY - if needed [WP-Reset plugin needed]------///
//!--------------------------------------------------------///
//*[Spec-0]: This spec resets your Wordpress Site - Comment out [LINE: 17,18,19] to USE.../
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(resetWordpressSite);

//!------------------------------------------------------///
//!-----------------------------------------------------///





//*-----------------------------------------------------*/
//*---------------------MAIN Tests---------------------*/
//*---------------------------------------------------*/
//*[Spec-1]: Test- Login and Setup.../
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(loginAndSetupTests);


//*[Spec-2]: Test- Post Forms + FrontEnd Case.../
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(postFormsTests);


//*[Spec-3]: Test- Registration Forms + FrontEnd Case.../
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(registrationFormsTestsLite);


//*[Spec-3]: Test- Subscription + FrontEnd Case.../
test.describe(subscriptionsTests);

//*---------------------------------------------------*/
//*--------------------------------------------------*/



