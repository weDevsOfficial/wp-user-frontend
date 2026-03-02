const colors = require('tailwindcss/colors');

const { scopedPreflightStyles, isolateInsideOfContainer } = require('tailwindcss-scoped-preflight');

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './assets/**/*.{js,jsx,ts,tsx,vue,html}',
        './includes/Admin/**/*.php',
        './includes/Free/Free_Loader.php',
        './includes/Admin/template-parts/*.php',
        './admin/form-builder/views/*.php',
        './admin/form-builder/assets/js/**/*.php',
        './templates/**/*.php',
        'wpuf-functions.php',
        // New upstream / react paths
        './src/**/*.{js,css}',
        './assets/js/components-react/**/*.{js,jsx}',
        './assets/js/subscriptions-react.jsx',
        './src/js/components-react/**/*.{js,jsx}',
        './src/js/subscriptions-react.jsx',
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
                    '.wpuf-form-template-modal'
                ], {}
            ),
        } ),
    ],
    daisyui: {
        themes: []
    },
}
