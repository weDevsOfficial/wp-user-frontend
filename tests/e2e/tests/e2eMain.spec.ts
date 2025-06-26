import { test } from '@playwright/test';
import resetSite from './resetSite.spec';
import loginAndSetupTest from './loginAndSetupTest.spec';
import postFormTest from './postFormTest.spec';
import regFormTestPro from './regFormTestPro.spec';
import postFormSettingsTest from './postFormSettingsTest.spec';
import regFormSettingsTestPro from './regFormSettingsTestPro.spec';

if (process.env.CI == 'false') {
    test.describe(resetSite);
}
test.describe(loginAndSetupTest);
test.describe(postFormTest);
test.describe(regFormTestPro);
test.describe(postFormSettingsTest);
test.describe(regFormSettingsTestPro);