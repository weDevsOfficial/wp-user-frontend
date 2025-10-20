const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './templates/**/*.php',
        './admin/form-builder/views/*.php',
        './src/**/*.{js,css}',
        './assets/js/**/*.{js,vue}',
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
    ],
}
