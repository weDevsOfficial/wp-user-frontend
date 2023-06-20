require('dotenv').config();

import { expect, Page } from '@playwright/test';
import { selectors } from './selectors';
import { testData } from '../utils/testData'


//import { TestData } from '../tests/testdata';

 

export class subscriptionSetup {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }


/*******************************************/
/******* @Subscription Create *************/
/*****************************************/ 

    //BlankForm
    async createNewSubscription() {
        
    };
    



/*********************************************/
/******* @Subscription Validate *************/
/*******************************************/ 

    async validateCreatedSubscription() {
        
    };


}