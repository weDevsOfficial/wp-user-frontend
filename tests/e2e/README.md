# WP User FrontEnd E2E Tests (Playwright)

Automated e2e tests for WPUF plugin for Lite & Pro Version.

## Table of contents

- [Pre-requisites](#pre-requisites)

  - [Install Node.js](#install-node.js)
  - [Install NVM](#install-nvm)
  - [Install Docker](#install-docker)
    &nbsp;
- [Running tests](#running-tests)

  - [Prep work for running tests](#prep-work-for-running-tests)
  - [How to run tests](#how-to-run-tests)

## Pre-requisites

### Install Node.js

Follow instructions on the [node.js site](https://nodejs.org/en/download/) to install Node.js.

### Install NVM

Follow instructions in the [NVM repository](https://github.com/nvm-sh/nvm) to install NVM.

### Install Docker

Follow instructions on the [Docker Desktop](https://docs.docker.com/docker-for-mac/install/) to install Docker.

## Running tests

### Prep work for running tests

Run the following in a terminal/command line window to install dependencies.

    cd test/e2e

```
npm i
```

Install browser:

```
npx playwright install chromium
```



### How to run tests in Local Setup

## Initial Setup:

    Update .env-example file to set your LocalSite, Admin credentials and NewUserCreation credentials

## Recommended step

    After cloning the repo,

    > [Plugins] Highly Recommended:
    (Manually install the plugins beforehand)
    
        • WPUF-Lite
        • WP-Reset 
        
    > While WP-Reset plugin is active
    > Comment out the marked sections to run "resetWordpressSite" and reset your LocalSite (Clean setup is highly recommended)
    

![image](https://github.com/Rat01047/wp-user-frontend/assets/95366111/02b59b95-4f17-417f-9b15-3d9a410fdafb)

    



## Running all tests

    npx playwright test e2eMain.spec.ts

Running a specific test file

    npx playwright test loginAndSetupTests.spec.ts

Running a specific test file in headed form

    npx playwright test --headed

Run all tests against a specific project

    npx playwright test --project=chromium

View report

    npx playwright show-report

Run in debug mode with [Playwright Inspector](https://playwright.dev/docs/debug)

    npx playwright test --debug

Ask for help

    npx playwright test --help
