/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: ["./assets/**/*.{js,jsx,ts,tsx,vue,html}", "./includes/Admin/views/*.php"],
    theme: {
        extend: {},
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('daisyui'),
    ],
    daisyui: {
        themes: [],
    },
}
