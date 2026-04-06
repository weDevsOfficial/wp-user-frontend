/**
 * PostCSS Configuration for User Directory Free
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

module.exports = {
    plugins: {
        tailwindcss: {
            config: './modules/user-directory/tailwind.config.js',
        },
        autoprefixer: {},
    },
};
