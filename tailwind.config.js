const colors = require('tailwindcss/colors');

const { scopedPreflightStyles, isolateInsideOfContainer } = require('tailwindcss-scoped-preflight');

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        // Sources only: generated bundles live next to the hand-written JS in
        // assets/js, and scanning them made the CSS depend on build order and
        // on stale files left on disk.
        './assets/**/*.js',
        '!./assets/js/**/*.min.js',
        '!./assets/js/screens/**',
        '!./assets/js/blocks/**',
        '!./assets/js/wpuf-user-directory-free.js',
        '!./assets/vendor/**',
        './includes/Admin/**/*.php',
        './includes/Free/Free_Loader.php',
        './admin/form-builder/views/*.php',
        // Class lists of removed Vue admin views. The frontend sheets built from
        // this config (account, frontend subscriptions) still rely on some of
        // them (measured 2026-10-07: dropping them removes classes the user
        // directory and field notices use), so they stay.
        './tools/admin-css/src/legacy/*.classes.txt',
        './src/admin/apps/form-builder/**/*.{js,jsx}',
        './src/admin/apps/forms-list/**/*.{js,jsx}',
        './templates/**/*.php',
        'wpuf-functions.php',
        './src/**/*.{js,jsx,css}',
        // Free User Directory module templates
        './modules/user-directory/**/*.php',
        './modules/user-directory/views/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: colors.emerald[600],
                primaryHover: colors.emerald[500],
            },
            spacing: {
                '1.75': '7px',
                '3.75': '15px',
                '4.5': '18px',
                '13': '52px',
            },
            fontSize: {
                '2xs': '13px',
            },
            minWidth: {
                'btn-cancel': '101px',
                'btn-save': '158px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms')({ strategy: 'class' }),
        require('daisyui'),
        scopedPreflightStyles({
            isolationStrategy: isolateInsideOfContainer(
                [
                    '.wpuf_packs',
                    '#wpuf-subscription-page',
                    '#wpuf-form-builder',
                    '#wpuf-profile-forms-list-table-view',
                    '#wpuf-post-forms-list-table-view',
                    '#wpuf-ai-form-builder',
                    '.wpuf-ai-form-wrapper',
                    '.swal2-container',
                    '.wpuf-account-container',
                ], {}
            ),
        } ),
    ],
    daisyui: {
        themes: []
    },
}
