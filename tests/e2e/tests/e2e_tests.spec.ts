require('dotenv').config();
import { test, expect, Page } from '@playwright/test';
import Login_Tests from './login_tests.spec';
import Post_Form_Tests from './post_form_tests.spec';
import Registration_Form_Tests from './reg_form_tests.spec';



import { faker } from '@faker-js/faker';
import * as fs from "fs"; //Clear Cookie





//Test Spec-1
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(Login_Tests);



//Test Spec-2
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(Post_Form_Tests);



//Test Spec-3
fs.writeFile('state.json', '{"cookies":[],"origins": []}', function () { });
test.describe(Registration_Form_Tests);

