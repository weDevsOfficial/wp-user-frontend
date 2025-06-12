import { test } from '@playwright/test';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTestsPro from './postFormsTestsPro.spec';
import registrationFormsTestsPro from './registrationFormsTestsPro.spec';
import postFormGeneralSettingsTestsPro from './postFormSettingsTestPro.spec';
// import subscriptionsTests from './subscription.spec';
import * as fs from 'fs'; //Clear Cookie
import resetWordpressSite from './resetWordpressSite.spec';

test.describe(resetWordpressSite);
test.describe(loginAndSetupTests);
test.describe(postFormsTestsPro);
test.describe(registrationFormsTestsPro);
test.describe(postFormGeneralSettingsTestsPro);