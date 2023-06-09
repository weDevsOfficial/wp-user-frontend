require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import loginTests from './loginTests.spec';
import postFormTests from './postFormTests.spec';
import registrationFormTestsLite from './registrationFormTestsLite.spec';
 


import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie




//Test Spec-1
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(loginTests);



//Test Spec-2
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(postFormTests);


//Test Spec-3
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(registrationFormTestsLite);


