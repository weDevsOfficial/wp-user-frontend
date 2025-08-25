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
                primary: '#059669', // emerald-600
            },
        },
    },
    plugins: [],
}
