/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: ['./assets/**/*.{js,jsx,ts,tsx,vue,html}', './includes/Admin/views/*.php', './admin/form-builder/views/*.php'],
    theme: {
        extend: {
            colors: {
                primary: '#166534',
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
