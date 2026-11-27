/**
 * A full-page state card of the admin app (not found, licence required, ...),
 * in the kit EmptyState's look. Such a route loads no screen sheet (nor React),
 * so the card is plain DOM and brings its few rules itself, once.
 *
 * @since WPUF_SINCE
 */

const CSS = `
.wpuf-app-state{max-width:560px;margin:48px auto;padding:48px 32px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 2px rgba(0,0,0,.05);text-align:center;color:#111827;font-family:"Plus Jakarta Sans",ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
.wpuf-app-state__icon{display:flex;align-items:center;justify-content:center;width:56px;height:56px;margin:0 auto 20px;border-radius:9999px;background:#ecfdf5;color:#059669}
.wpuf-app-state h2{margin:0 0 8px;font-size:18px;line-height:28px;font-weight:600;color:#111827}
.wpuf-app-state p{margin:0 auto 24px;max-width:380px;font-size:14px;line-height:22px;color:#6b7280}
.wpuf-app-state__actions{display:flex;flex-wrap:wrap;justify-content:center;gap:12px}
.wpuf-app-state__actions a{display:inline-flex;align-items:center;height:36px;padding:0 16px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;font-size:14px;font-weight:500;text-decoration:none;box-shadow:none}
.wpuf-app-state__actions a:hover,.wpuf-app-state__actions a:focus{background:#f9fafb;color:#374151}
.wpuf-app-state__actions a.is-primary{border-color:transparent;background:#059669;color:#fff}
.wpuf-app-state__actions a.is-primary:hover,.wpuf-app-state__actions a.is-primary:focus{background:#10b981;color:#fff}`;

// lucide icons (the kit's set) as markup.
const ICONS = {
    compass: '<circle cx="12" cy="12" r="10"/><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"/>',
    lock: '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
};

/**
 * Show a state card in a container.
 *
 * @param {HTMLElement} root            Container.
 * @param {Object}      card
 * @param {string}      [card.icon]     compass | lock.
 * @param {string}      card.title      Heading.
 * @param {string}      [card.text]     Explanation.
 * @param {Array}       [card.actions]  { label, href, primary, back } list (back: history.back()).
 * @param {string}      [card.className] Extra class on the card.
 */
export default function renderStateCard( root, { icon = 'compass', title = '', text = '', actions = [], className = '' } = {} ) {
    if ( ! document.getElementById( 'wpuf-app-state-css' ) ) {
        const style = document.createElement( 'style' );

        style.id = 'wpuf-app-state-css';
        style.textContent = CSS;
        document.head.append( style );
    }

    const box = document.createElement( 'div' );

    box.className = `wpuf-app-state ${ className }`.trim();
    box.setAttribute( 'role', 'status' );
    box.innerHTML = `<div class="wpuf-app-state__icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ ICONS[ icon ] || ICONS.compass }</svg></div><h2></h2><p></p><div class="wpuf-app-state__actions"></div>`;
    // Texts go in as text.
    box.querySelector( 'h2' ).textContent = title;
    box.querySelector( 'p' ).textContent = text;

    if ( ! text ) {
        box.querySelector( 'p' ).remove();
    }

    const bar = box.querySelector( '.wpuf-app-state__actions' );

    actions.forEach( ( action ) => {
        const link = document.createElement( 'a' );

        link.href = action.back ? '#' : action.href || '#';
        link.textContent = action.label || '';

        if ( action.primary ) {
            link.className = 'is-primary';
        }

        if ( action.back ) {
            link.addEventListener( 'click', ( event ) => {
                event.preventDefault();
                window.history.back();
            } );
        }

        bar.append( link );
    } );

    root.append( box );
}
