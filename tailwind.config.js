const colors = require('tailwindcss/colors');

import {
    scopedPreflightStyles,
    isolateInsideOfContainer,
} from 'tailwindcss-scoped-preflight';

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        // Original paths (critical for form builder - keeps @tailwindcss/forms styles)
        './assets/**/*.{js,jsx,ts,tsx,vue,html}',
        './includes/Admin/**/*.php',
        './includes/Free/Free_Loader.php',
        './includes/Admin/template-parts/*.php',
        './admin/form-builder/views/*.php',
        './admin/form-builder/assets/js/**/*.php',
        './templates/**/*.php',
        'wpuf-functions.php',
        // New paths from upstream (for subscription templates)
        './templates/**/*.php',
        './src/**/*.{js,css}',
    ],
    theme: {
         extend: {
            colors: {
                primary: colors.emerald[600],
                primaryHover: colors.emerald[500],
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('daisyui'),
        scopedPreflightStyles( {
            isolationStrategy: isolateInsideOfContainer(
                [
                    '.wpuf_packs',
                    '#wpuf-subscription-page',
                    '#wpuf-form-builder',
                    '#wpuf-profile-forms-list-table-view',
                    '#wpuf-post-forms-list-table-view',
                    '.swal2-container',
                    '.wpuf-account-container'
                ], {}
            ),
        } ),
        require('@tailwindcss/forms'),
    ],
    daisyui: {
        themes: [],
    },
}
