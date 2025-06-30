import * as dotenv from 'dotenv';
dotenv.config();
import { expect, type Page } from '@playwright/test';
import { Base } from './base';
import { Urls } from '../utils/testData';
import { Selectors } from './selectors';
import { FieldOptionsCommonPage } from '../pages/fieldOptionsCommon';

export class RegFormSettingsPage extends Base {
    constructor(page: Page) {
        super(page);
    }

    async settingNewlyRegisteredUserRole(formName: string, role: string) {
        // Go to form edit page
        await Promise.all([this.page.goto(this.wpufPostFormPage)]);
        await this.waitForLoading();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
    }

    async validateNewlyRegisteredUserRole(formName: string, role: string) {
        // Go to form edit page
        await Promise.all([this.page.goto(this.wpufPostFormPage)]);
        await this.waitForLoading();

        // Click on the form
        await this.validateAndClick(Selectors.postFormSettings.clickForm(formName));
    }
}