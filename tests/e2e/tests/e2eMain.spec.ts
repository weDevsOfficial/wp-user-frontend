import { test, expect, Page } from '@playwright/test';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTests from './postFormsTests.spec';
import registrationFormsTestsLite from './registrationFormsTestsLite.spec';
import resetWordpressSite from './resetWordpressSite.spec';


import * as fs from "fs"; //Clear Cookie


//Run ONLY - if needed
//This Suite resets your Wordpress Site - [Plugin needed: WP Reset] --> [Spec-0]
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(resetWordpressSite);


//Test- Login and Setup --> [Spec-1]
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(loginAndSetupTests);



//Test- Post Forms + FrontEnd Case --> [Spec-2]
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(postFormsTests);



//Test- Post Forms + FrontEnd Case --> [Spec-3]
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(registrationFormsTestsLite);


