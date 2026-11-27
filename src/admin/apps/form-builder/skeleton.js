/**
 * What a builder route shows while the form loads: the builder's own shape
 * (header card with the form name, ID and buttons, the field canvas, the
 * Add Fields panel) as shimmering placeholders, under a thin green progress
 * bar. Plain DOM with inline styles, because it shows before React and the
 * builder sheet do; reduced motion keeps it still.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const STYLE_ID = 'wpuf-builder-skeleton-style';

const CSS = `
.wpuf-builder-skeleton .wpuf-sk { position: relative; overflow: hidden; background: #eef2f1; border-radius: 6px; }
.wpuf-builder-skeleton .wpuf-sk::after { content: ''; position: absolute; inset: 0; transform: translateX( -100% ); background: linear-gradient( 90deg, transparent, rgba( 255, 255, 255, .75 ), transparent ); animation: wpuf-sk-shimmer 1.4s cubic-bezier( .4, 0, .2, 1 ) infinite; }
.wpuf-builder-skeleton .wpuf-sk-bar { position: absolute; top: 0; left: 0; height: 3px; width: 35%; border-radius: 9999px; background: linear-gradient( 90deg, #10b981, #059669 ); animation: wpuf-sk-progress 1.2s cubic-bezier( .65, 0, .35, 1 ) infinite; }
.wpuf-builder-skeleton { animation: wpuf-sk-in 240ms cubic-bezier( .16, 1, .3, 1 ) both; }
@keyframes wpuf-sk-shimmer { to { transform: translateX( 100% ); } }
@keyframes wpuf-sk-progress { 0% { left: -35%; } 100% { left: 100%; } }
@keyframes wpuf-sk-in { from { opacity: 0; transform: translateY( 6px ); } }
@media ( prefers-reduced-motion: reduce ) {
    .wpuf-builder-skeleton, .wpuf-builder-skeleton .wpuf-sk::after, .wpuf-builder-skeleton .wpuf-sk-bar { animation: none; }
    .wpuf-builder-skeleton .wpuf-sk-bar { left: 0; width: 100%; opacity: .4; }
}
`;

/**
 * An element with inline styles and children.
 *
 * @param {Object} style    Inline style.
 * @param {Array}  children Child nodes.
 * @param {string} cls      Class name.
 *
 * @return {HTMLElement} Element.
 */
const box = ( style = {}, children = [], cls = '' ) => {
    const node = document.createElement( 'div' );

    Object.assign( node.style, style );

    if ( cls ) {
        node.className = cls;
    }

    node.append( ...children );

    return node;
};

/** A shimmering placeholder of a size. */
const bone = ( width, height, extra = {} ) => box( { width, height, flex: '0 0 auto', ...extra }, [], 'wpuf-sk' );

const card = ( style, children ) => box( { background: '#fff', border: '1px solid #e5e7eb', borderRadius: '8px', ...style }, children );

/**
 * Fold the WordPress menu to its icon rail for the builder, marking that the
 * builder folded it (FormBuilder unfolds it on leave only then).
 */
export function foldMenu() {
    const body = document.body;

    if ( body.classList.contains( 'folded' ) ) {
        return;
    }

    body.classList.add( 'folded' );
    body.dataset.wpufBuilderFolded = '1';
}

/**
 * Undo foldMenu() when the builder folded the menu.
 */
export function unfoldMenu() {
    const body = document.body;

    if ( '1' !== body.dataset.wpufBuilderFolded ) {
        return;
    }

    body.classList.remove( 'folded' );
    delete body.dataset.wpufBuilderFolded;
}

/**
 * The skeleton element (its status text is for screen readers).
 *
 * @return {HTMLElement} Skeleton.
 */
export function builderSkeleton() {
    if ( ! document.getElementById( STYLE_ID ) ) {
        const style = document.createElement( 'style' );

        style.id = STYLE_ID;
        style.textContent = CSS;
        document.head.append( style );
    }

    const status = document.createElement( 'span' );

    status.textContent = __( 'Loading the form builder…', 'wp-user-frontend' );
    Object.assign( status.style, { position: 'absolute', width: '1px', height: '1px', overflow: 'hidden', clip: 'rect(0 0 0 0)', whiteSpace: 'nowrap' } );

    // The WordPress menu folds as the builder does (FormBuilder), so nothing
    // jumps when it mounts; FormBuilder takes it over and unfolds it on leave.
    foldMenu();

    const header = card( { position: 'relative', overflow: 'hidden', padding: '10px 16px', marginBottom: '12px' }, [
        box( {}, [], 'wpuf-sk-bar' ),
        box( { display: 'flex', alignItems: 'center', gap: '12px' }, [
            bone( '28px', '28px', { borderRadius: '9999px' } ),
            bone( '130px', '16px' ),
            bone( '64px', '28px' ),
            box( { flex: '1 1 auto' } ),
            box( { display: 'flex', gap: '4px', padding: '4px', background: '#f3f4f6', borderRadius: '8px' }, [
                bone( '96px', '28px', { background: '#fff' } ),
                bone( '72px', '28px', { background: 'transparent' } ),
            ] ),
            box( { flex: '1 1 auto' } ),
            bone( '92px', '36px' ),
            bone( '64px', '36px', { background: '#a7f3d0' } ),
        ] ),
    ] );

    const tiles = () => box( { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px', marginBottom: '20px' }, [
        bone( '100%', '40px' ),
        bone( '100%', '40px' ),
        bone( '100%', '40px' ),
        bone( '100%', '40px' ),
    ] );

    const fields = box( { flex: '0 0 300px', padding: '16px', borderRight: '1px solid #e5e7eb', background: 'rgba( 249, 250, 251, .6 )' }, [
        bone( '80px', '10px', { marginBottom: '16px' } ),
        bone( '100%', '32px', { marginBottom: '20px' } ),
        bone( '90px', '10px', { marginBottom: '12px' } ),
        tiles(),
        bone( '100px', '10px', { marginBottom: '12px' } ),
        tiles(),
    ] );

    const row = ( height = '36px' ) => box( { display: 'flex', alignItems: 'flex-start', gap: '40px', padding: '16px 20px' }, [
        bone( '110px', '14px', { marginTop: '11px' } ),
        bone( '100%', height, { flex: '1 1 auto' } ),
    ] );

    const canvas = box( { flex: '1 1 auto', padding: '20px 24px', minWidth: '0' }, [
        row(),
        row( '180px' ),
        row(),
        row( '72px' ),
    ] );

    const option = () => box( { marginBottom: '20px' }, [
        bone( '90px', '12px', { marginBottom: '8px' } ),
        bone( '100%', '36px' ),
    ] );

    const options = box( { flex: '0 0 300px', padding: '16px', borderLeft: '1px solid #e5e7eb' }, [
        bone( '90px', '10px', { marginBottom: '20px' } ),
        option(),
        option(),
        option(),
    ] );

    const skeleton = box( { position: 'relative', marginTop: '16px', marginRight: '16px' }, [
        status,
        header,
        card( { display: 'flex', height: 'calc( 100vh - 132px )', minHeight: '480px', overflow: 'hidden' }, [ fields, canvas, options ] ),
    ], 'wpuf-builder-skeleton wpuf-admin-app-loading' );

    skeleton.setAttribute( 'role', 'status' );
    skeleton.setAttribute( 'aria-live', 'polite' );
    skeleton.setAttribute( 'aria-busy', 'true' );

    return skeleton;
}

/**
 * The builder could not load: say why where the skeleton was.
 *
 * @param {string} message Error message.
 *
 * @return {HTMLElement} Message box.
 */
export function builderLoadError( message ) {
    const text = document.createElement( 'p' );

    text.textContent = message;
    Object.assign( text.style, { margin: '0', fontSize: '14px', color: '#991b1b' } );

    const node = card( { marginTop: '16px', marginRight: '16px', padding: '16px 20px', borderColor: '#fecaca', background: '#fef2f2' }, [ text ] );

    node.className = 'wpuf-admin-app-loading is-error';

    unfoldMenu();
    node.setAttribute( 'role', 'alert' );

    return node;
}
