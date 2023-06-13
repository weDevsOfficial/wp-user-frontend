require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTests from './postFormsTests.spec';
import registrationFormsTestsLite from './registrationFormsTestsLite.spec';
 


import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie





//Test Spec-1
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(loginAndSetupTests);



//Test Spec-2
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(postFormsTests);



//Test Spec-3
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(registrationFormsTestsLite);


