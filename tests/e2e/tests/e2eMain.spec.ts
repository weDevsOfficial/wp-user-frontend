import { test } from '@playwright/test';
import * as fs from 'fs'; //Clear Cookie
import resetWordpressSite from './resetWordpressSite.spec';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTests from './postFormsTests.spec';
import registrationFormsTestsPro from './registrationFormsTestsPro.spec';
import postFormSettingsTest from './postFormSettingsTest.spec';

test.describe(resetWordpressSite);
test.describe(loginAndSetupTests);
test.describe(postFormsTests);
test.describe(registrationFormsTestsPro);
test.describe(postFormSettingsTest);