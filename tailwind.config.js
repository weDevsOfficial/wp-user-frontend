const colors = require('tailwindcss/colors');

import {
    scopedPreflightStyles,
    isolateInsideOfContainer,
} from 'tailwindcss-scoped-preflight';

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './assets/**/*.{js,jsx,ts,tsx,vue,html}',
        './includes/Admin/**/*.php',
        './includes/Free/Free_Loader.php',
        './includes/Admin/template-parts/*.php',
        './admin/form-builder/views/*.php',
        // Vue cleanup: old Vue component PHP templates deleted
        // './admin/form-builder/assets/js/**/*.php',
        './admin/form-builder/src/**/*.{js,jsx}',
        './admin/forms-list/src/**/*.{js,jsx}',
        './templates/**/*.php',
        'wpuf-functions.php',
        './src/**/*.{js,css}',
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
        scopedPreflightStyles( {
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
                    '.wpuf-form-template-modal'
                ], {}
            ),
        } ),
    ],
    daisyui: {
        themes: []
    },
}
