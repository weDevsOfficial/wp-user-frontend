/**
 * Dedicated Tailwind config for the React Settings screen.
 *
 * Compiled to a SEPARATE stylesheet (assets/css/settings-react.css) so it never
 * rebuilds or clobbers the shared form-builder / subscriptions CSS bundles.
 * Preflight is isolated to the settings mount only.
 */
const colors = require( 'tailwindcss/colors' );

const {
    scopedPreflightStyles,
    isolateInsideOfContainer,
} = require( 'tailwindcss-scoped-preflight' );

/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'wpuf-',
    content: [
        './src/js/**/*.{js,jsx}',
        './src/css/settings.css',
        './includes/Admin/Menu.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: colors.emerald[ 600 ],
                primaryHover: colors.emerald[ 500 ],
            },
        },
    },
    plugins: [
        // 'base' strategy gives raw checkboxes/radios/selects their proper native
        // styling (checkmark SVG, radio dot, dropdown arrow). Safe here because this
        // stylesheet is enqueued only on the settings page, scoped to the React app.
        require( '@tailwindcss/forms' )( { strategy: 'base' } ),
        scopedPreflightStyles( {
            isolationStrategy: isolateInsideOfContainer( [ '#wpuf-settings-root' ], {} ),
        } ),
    ],
};
