/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './assets/**/*.{js,jsx,ts,tsx,vue,html}',
        './includes/Admin/views/*.php',
        './includes/Admin/template-parts/*.php',
        './admin/form-builder/views/*.php',
        './admin/form-builder/assets/js/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#166534',
                primaryHover: '#15803d',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('daisyui'),
    ],
    daisyui: {
        themes: [],
    },
}
