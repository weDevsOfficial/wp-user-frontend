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
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
