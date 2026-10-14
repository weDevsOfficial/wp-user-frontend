/**
 * Generated assets: what `pnpm build` (and `grunt release`) must produce.
 * Shared by verify-build.mjs and sanitize-dist.mjs.
 */
// Output of `pnpm build`. React bundles also need their `.asset.php`
// (dependencies + content hash, read by includes/Assets.php).
export const react = [ 'form-builder', 'forms-list-react', 'settings-react', 'subscriptions', 'ai-form-builder', 'admin-runtime', 'admin-ui', 'admin-app', 'registration-promo', 'onboarding', 'welcome', 'help', 'tools', 'transactions', 'coupons-promo' ];
export const frontend = [ 'runtime', 'forms', 'account-react' ];
export const built = [
    ...react.flatMap( ( name ) => [ `assets/js/react/${ name }.js`, `assets/js/react/${ name }.asset.php` ] ),
    ...frontend.flatMap( ( name ) => [ `assets/js/frontend/${ name }.js`, `assets/js/frontend/${ name }.asset.php`, `assets/css/frontend/${ name }.css`, `assets/css/frontend/${ name }-rtl.css` ] ),
    'assets/css/frontend/account-sections.css',
    'assets/js/account.min.js',
    'assets/js/frontend-form.min.js',
    'assets/js/frontend-subscriptions.min.js',
    'assets/js/upload.min.js',
    'assets/js/wpuf-user-directory-free.js',
    'assets/js/wpuf-user-directory-free.asset.php',
    'assets/css/admin.css',
    'assets/css/admin/forms-react.css',
    'assets/css/admin/forms-react-rtl.css',
    'assets/css/admin/forms-list-react.css',
    'assets/css/admin/forms-list-react-rtl.css',
    'assets/css/admin/onboarding-react.css',
    'assets/css/admin/onboarding-react-rtl.css',
    'assets/css/admin/pages-react.css',
    'assets/css/admin/pages-react-rtl.css',
    'assets/css/ai-form-builder-react.css',
    'assets/css/ai-form-builder-react-rtl.css',
    'assets/css/elementor-frontend-forms.css',
    'assets/css/frontend-forms.css',
    'assets/css/frontend/account.css',
    'assets/css/frontend-subscriptions.min.css',
    'assets/css/settings-react.css',
    'assets/css/settings-react-rtl.css',
    'assets/css/subscriptions.css',
    'assets/css/subscriptions-rtl.css',
    'assets/css/wpuf-form-builder.css',
];
export const release = [ 'languages/wp-user-frontend.pot' ];

