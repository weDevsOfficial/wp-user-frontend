/**
 * WPUF design tokens for plugin-ui's ThemeProvider: the develop Vue look
 * (openspec design-tokens.md). Dark mode is not offered, so the dark tokens
 * are the light ones.
 */
export const wpufTokens = Object.freeze( {
    background: '#ffffff',
    foreground: '#111827',
    card: '#ffffff',
    cardForeground: '#111827',
    popover: '#ffffff',
    popoverForeground: '#111827',
    primary: '#059669',
    primaryForeground: '#ffffff',
    secondary: '#f3f4f6',
    secondaryForeground: '#374151',
    muted: '#f9fafb',
    mutedForeground: '#6b7280',
    accent: '#f9fafb',
    accentForeground: '#111827',
    destructive: '#dc2626',
    destructiveForeground: '#ffffff',
    success: '#15803d',
    successForeground: '#ffffff',
    warning: '#854d0e',
    warningForeground: '#ffffff',
    info: '#059669',
    infoForeground: '#ffffff',
    border: '#d1d5db',
    input: '#d1d5db',
    ring: '#059669',
    // plugin-ui: rounded-md = radius - 2px (controls 6px), rounded-xl = radius + 4px (cards 12px).
    radius: '0.5rem',
    // The app font (FlyHR's): Plus Jakarta Sans, bundled in assets/fonts and
    // declared in the screens' stylesheets (src/pui.css). The provider prints
    // tokens inline on its root, so this is what every `font-sans` inside a
    // screen and its portals resolves to.
    fontSans: '"Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
} );

export const wpufDarkTokens = wpufTokens;

/**
 * WPUF values that plugin-ui has no token for (design-tokens.md "custom"),
 * used by the shared/ui wrappers.
 */
export const wpufExtraTokens = Object.freeze( {
    primaryHover: '#10b981',
    label: '#374151',
    placeholder: '#9ca3af',
    borderSubtle: '#e5e7eb',
    pageBackground: '#ffffff',
} );
