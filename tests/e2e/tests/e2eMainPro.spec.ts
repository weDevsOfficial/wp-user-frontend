import { test } from '@playwright/test';
import loginAndSetupTest from './loginAndSetupTest.spec';
import postFormTestPro from './postFormTestPro.spec';
import regFormTestPro from './regFormTestPro.spec';
import postFormSettingsTestPro from './postFormSettingsTestPro.spec';
import resetSite from './resetSite.spec';
import regFormSettingsTestPro from './regFormSettingsTestPro.spec';

if (process.env.CI == 'false') {
    test.describe(resetSite);
}
test.describe(loginAndSetupTest);
test.describe(postFormTestPro);
test.describe(regFormTestPro);
test.describe(postFormSettingsTestPro);
test.describe(regFormSettingsTestPro);