import { test } from '@playwright/test';
import loginAndSetupTests from './loginAndSetupTests.spec';
import postFormsTestsPro from './postFormsTestsPro.spec';
import registrationFormsTestsPro from './registrationFormsTestsPro.spec';
import postFormGeneralSettingsTestsPro from './postFormSettingsTestPro.spec';
import * as fs from 'fs';
import resetWordpressSite from './resetWordpressSite.spec';
import regFormSettingsTestPro from './regFormSettingsTestPro.spec';

if (process.env.CI == 'false') {
    test.describe(resetWordpressSite);
}
test.describe(loginAndSetupTests);
test.describe(postFormsTestsPro);
test.describe(registrationFormsTestsPro);
test.describe(postFormGeneralSettingsTestsPro);
test.describe(regFormSettingsTestPro);