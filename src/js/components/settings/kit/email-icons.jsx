/**
 * Email accordion icons — the exact 40×40 icon groups exported from the WPUF
 * Redesign Figma (Email tab). Each SVG already carries the emerald (#059669)
 * glyph on its own light rounded square, so it is rendered as-is (no wrapper).
 */

const ICONS = {
    guest: 'email/guest.svg',
    subscription: 'email/subscription.svg',
    pending: 'email/pending.svg',
    denied: 'email/denied.svg',
    approved: 'email/approved.svg',
    activated: 'email/activated.svg',
    'approved-post': 'email/approved-post.svg',
};

const pick = ( title = '' ) => {
    const t = title.toLowerCase();
    if ( t.includes( 'guest' ) ) {
        return ICONS.guest;
    }
    if ( t.includes( 'subscription' ) ) {
        return ICONS.subscription;
    }
    if ( t.includes( 'pending' ) ) {
        return ICONS.pending;
    }
    if ( t.includes( 'denied' ) ) {
        return ICONS.denied;
    }
    if ( t.includes( 'activated' ) ) {
        return ICONS.activated;
    }
    if ( t.includes( 'post' ) ) {
        return ICONS[ 'approved-post' ];
    }
    if ( t.includes( 'approved' ) ) {
        return ICONS.approved;
    }
    return ICONS.guest;
};

export default function EmailIcon( { title } ) {
    const assetUrl = ( window.wpuf_settings || {} ).asset_url || '';
    return (
        <img
            src={ `${ assetUrl }/images/${ pick( title ) }` }
            alt=""
            className="wpuf-h-10 wpuf-w-10 wpuf-shrink-0"
            aria-hidden="true"
        />
    );
}
