/** @type {import('tailwindcss').Config} */
import {
    scopedPreflightStyles,
    isolateInsideOfContainer,
} from 'tailwindcss-scoped-preflight';

module.exports = {
    prefix: 'wpuf-',
    content: [
        '../../src/js/user-directory/**/*.js',
        '../../src/js/user-directory/**/*.jsx',
        './views/**/*.php',
    ],
    safelist: [
        // Responsive classes used in components
        'sm:wpuf-pl-6',
        'sm:wpuf-pr-6',
        'md:wpuf-inline-block',
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
    ],
    theme: {
        extend: {
            colors: {
                primary: '#9333ea',
                primaryHover: '#7c3aed',
            },
        },
    },
    plugins: [
        scopedPreflightStyles({
            isolationStrategy: isolateInsideOfContainer(
                [
                    '.wpuf-user-directory',
                    '.wpuf-user-directory-setup',
                    '.wpuf-user-listing',
                    '.wpuf-user-profile',
                ], {}
            ),
        }),
        require('@tailwindcss/forms'),
    ],
};
