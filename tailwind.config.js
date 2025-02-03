/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './assets/**/*.{js,jsx,ts,tsx,vue,html}',
        './includes/Admin/**/*.php',
        './includes/Admin/template-parts/*.php',
        './admin/form-builder/views/*.php',
        './admin/form-builder/assets/js/**/*.php',
        'wpuf-functions.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#16a34a',
                primaryHover: '#22c55e',
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
