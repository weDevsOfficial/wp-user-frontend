/**
 * `@wpuf/*` and `@wedevs/plugin-ui` imports -> the shared WPUF admin layer on
 * window.wpuf (design.md D24, Dokan model). Used by the screen bundles here
 * and copied into Pro's webpack config (keep both the same). The shared
 * entries (admin-runtime, admin-ui) bundle these modules instead.
 */
const MAP = {
    '@wedevs/plugin-ui': { global: [ 'wpuf', 'ui' ], handle: 'wpuf-admin-ui' },
    '@wpuf/components': { global: [ 'wpuf', 'components' ], handle: 'wpuf-admin-ui' },
    '@wpuf/filters': { global: [ 'wpuf', 'filters' ], handle: 'wpuf-admin-runtime' },
    '@wpuf/api': { global: [ 'wpuf', 'api' ], handle: 'wpuf-admin-runtime' },
    '@wpuf/hooks': { global: [ 'wpuf', 'hooks' ], handle: 'wpuf-admin-runtime' },
    '@wpuf/utilities': { global: [ 'wpuf', 'utilities' ], handle: 'wpuf-admin-runtime' },
    // plugin-ui re-exports recharts: one copy on the page, so its React context
    // (chart size, scales) is the one plugin-ui's ChartContainer fills.
    recharts: { global: [ 'wpuf', 'ui', 'recharts' ], handle: 'wpuf-admin-ui' },
};

// The frontend apps (webpack.frontend.config.js): the kit and the icons come
// from the runtime bundle, published on window.wpuf.frontend.
const FRONTEND_MAP = {
    '@wpuf/frontend-kit': { global: [ 'wpuf', 'frontend' ], handle: 'wpuf-frontend-runtime' },
    'lucide-react': { global: [ 'wpuf', 'frontend', 'icons' ], handle: 'wpuf-frontend-runtime' },
};

module.exports = {
    MAP,
    FRONTEND_MAP,
    // DependencyExtractionWebpackPlugin options.
    requestToExternal: ( request ) => ( MAP[ request ] ? MAP[ request ].global : undefined ),
    requestToHandle: ( request ) => ( MAP[ request ] ? MAP[ request ].handle : undefined ),
    frontendRequestToExternal: ( request ) => ( FRONTEND_MAP[ request ] ? FRONTEND_MAP[ request ].global : undefined ),
    frontendRequestToHandle: ( request ) => ( FRONTEND_MAP[ request ] ? FRONTEND_MAP[ request ].handle : undefined ),
};
