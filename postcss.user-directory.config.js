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
            config: './tailwind.user-directory.config.js',
        },
        autoprefixer: {},
    },
};
