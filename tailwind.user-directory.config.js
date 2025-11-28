/**
 * Tailwind CSS Configuration for User Directory Free
 *
 * Uses wpuf- prefix to avoid conflicts
 *
 * @package WPUF
 * @subpackage Free/User_Directory
 * @since 4.3.0
 */

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './src/js/user-directory/**/*.js',
        './src/js/user-directory/**/*.jsx',
        './includes/Free/User_Directory/views/**/*.php',
    ],
    safelist: [
        // Purple color classes for layout colors (with ! prefix)
        'wpuf-bg-purple-500',
        'wpuf-bg-purple-600',
        'wpuf-bg-purple-700',
        'wpuf-text-purple-600',
        'wpuf-text-purple-700',
        'wpuf-border-purple-600',
        'wpuf-border-purple-700',
        'wpuf-ring-purple-500',
        '!wpuf-bg-purple-500',
        '!wpuf-bg-purple-600',
        '!wpuf-bg-purple-700',
        '!wpuf-text-purple-600',
        '!wpuf-text-purple-700',
        '!wpuf-border-purple-600',
        '!wpuf-border-purple-700',
        '!wpuf-ring-purple-500',
        // Purple hover/focus states
        'hover:wpuf-text-purple-600',
        'hover:wpuf-bg-purple-700',
        'hover:wpuf-border-purple-600',
        'focus:wpuf-ring-purple-500',
        'hover:!wpuf-text-purple-600',
        'hover:!wpuf-bg-purple-700',
        'hover:!wpuf-border-purple-600',
        'focus:!wpuf-ring-purple-500',
        // Emerald color classes (default)
        '!wpuf-bg-emerald-500',
        '!wpuf-bg-emerald-600',
        '!wpuf-bg-emerald-700',
        '!wpuf-bg-emerald-50',
        '!wpuf-bg-emerald-100',
        '!wpuf-text-emerald-600',
        '!wpuf-text-emerald-700',
        '!wpuf-text-emerald-800',
        '!wpuf-border-emerald-500',
        '!wpuf-border-emerald-600',
        '!wpuf-border-emerald-700',
        '!wpuf-ring-emerald-500',
        '!hover:wpuf-text-emerald-600',
        '!hover:wpuf-bg-emerald-700',
        '!hover:wpuf-border-emerald-600',
        '!focus:wpuf-ring-emerald-500',
        'hover:!wpuf-bg-emerald-700',
        'hover:!wpuf-bg-emerald-100',
        'hover:!wpuf-bg-emerald-50',
        // Purple hover states with important
        'hover:!wpuf-bg-purple-700',
        'hover:!wpuf-bg-purple-100',
        'hover:!wpuf-bg-purple-50',
        'hover:!wpuf-text-purple-600',
        'hover:!wpuf-text-purple-700',
        // Gray color classes
        '!wpuf-bg-gray-800',
        '!wpuf-bg-gray-700',
        '!wpuf-text-gray-800',
        '!wpuf-text-gray-700',
        '!wpuf-border-gray-800',
        '!wpuf-border-gray-700',
        '!wpuf-ring-gray-800',
        '!hover:wpuf-text-gray-800',
        '!hover:wpuf-bg-gray-700',
        '!hover:wpuf-border-gray-800',
        '!focus:wpuf-ring-gray-800',
    ],
    theme: {
        extend: {
            animation: {
                'fade-in': 'fade-in 0.3s ease-out forwards',
                'spin': 'spin 1s linear infinite',
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [],
    // Important: This ensures our styles don't conflict with WordPress admin
    corePlugins: {
        preflight: false, // Disable base reset to avoid conflicts
    },
};
